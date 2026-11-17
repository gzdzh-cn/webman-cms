<?php
declare(strict_types=1);
namespace app\cms\maintenance;

use app\cms\config\{DeploymentConfig, Entry, Environment};
use app\cms\support\EmbeddedResources;

/** Only managed deployment paths; never follows links or changes ownership. */
final class DeploymentPermissions
{
    private bool $fix = false;
    private array $changes = [];
    private int $checked = 0;
    private float $lastProgress = 0;
    private array $ignoredYaml = [];

    public function __construct(private readonly ?\Closure $progress = null) {}

    public function run(bool $fix = false): array
    {
        $this->fix = $fix; $this->changes = []; $this->checked = 0;
        $this->lastProgress = microtime(true);
        try {
            $root = Entry::directory();
            $this->ignoredYaml = array_map(static fn(string $file) => $root.'/'.$file, EmbeddedResources::YAML);
            $this->inspect($root, 'parent');
            // Restore traversal of an existing private config tree before file
            // discovery; do not create it when the required YAML is missing.
            if ($this->exists($root.'/config')) $this->tree($root.'/config', 'private');
            // Repair .env before parsing it to discover the selected YAML. Embedded
            // configuration must not inspect or mutate ignored external files.
            if (!EmbeddedResources::yaml()) {
                $env = Environment::path();
                if ($this->exists($env)) $this->inspect($env, 'secret_file');
                Environment::initialize();
                $yaml = DeploymentConfig::file();
                // Reuse the deployment boundary check before chmod of a selected
                // configuration file inside a public or temporary directory.
                \app\cms\Paths::directory('site_root');
                if ($this->exists($yaml)) $this->inspect($yaml, 'secret_file');
                elseif (EmbeddedResources::binary()) throw new \RuntimeException('二进制缺少 YAML 配置：'.$yaml.'；查找 config.yaml、config/config.yaml、config/manifest/config.yaml，或 CMS_CONFIG_FILE 指定文件');
            }
            foreach (['config', 'runtime'] as $relative) {
                $path = $root.'/'.$relative;
                $this->directory($path, 0700);
                if ($relative !== 'config') $this->tree($path, 'private');
            }
            if ($this->exists($root.'/public')) $this->inspect($root.'/public', 'parent');
            foreach (['uploads', 'public/html'] as $relative) {
                $path = $root.'/'.$relative;
                $this->directory($path, 0755);
                $this->tree($path, 'writable');
            }
            foreach (['template'=>'template', \app\cms\Paths::PUBLIC_ASSETS=>\app\cms\Paths::PUBLIC_ASSETS] as $embedded=>$relative) {
                if ($embedded===\app\cms\Paths::PUBLIC_ASSETS ? EmbeddedResources::hasAssets() : EmbeddedResources::has($embedded)) continue;
                $path = $root.'/'.$relative;
                if (!$this->exists($path)) throw new \RuntimeException('外置资源目录不存在：'.$path.'；请部署资源，程序不会创建空资源目录');
                $this->tree($path, 'resource');
            }
            $errors = !$fix && $this->changes ? ['存在需要修复的权限；执行 cms:permissions --fix，或由二进制 start/restart 自动整理'] : [];
        } catch (\Throwable $error) { $errors = [$error->getMessage()]; }
        return ['ok'=>!$errors, 'mode'=>$fix?'fix':'read_only', 'checked'=>$this->checked,
            'changed'=>$fix?count($this->changes):0, 'changes'=>$this->changes, 'errors'=>$errors,
            'ownership_changed'=>false];
    }

    private function exists(string $path): bool
    {
        Entry::assertPath($path);
        clearstatcache(true, $path);
        return @lstat($path) !== false;
    }

    private function directory(string $path, int $mode): void
    {
        if ($this->exists($path)) return;
        if (!$this->fix) {
            $this->changes[] = ['path'=>$path, 'from'=>null, 'to'=>sprintf('%04o', $mode), 'action'=>'mkdir'];
            return;
        }
        $parent = dirname($path);
        if (!$this->exists($parent)) $this->directory($parent, 0755);
        // An owned 0555 release directory can be made writable when a required
        // child is absent. Existing, fully provisioned read-only roots are valid.
        $this->inspect($parent, 'writable_parent');
        if (!@mkdir($path, $mode) && !is_dir($path)) $this->failure($path, '无法创建必要目录');
        $this->inspect($path, $mode === 0700 ? 'private_dir' : 'writable_dir');
        $this->changes[] = ['path'=>$path, 'from'=>null, 'to'=>sprintf('%04o', $mode), 'action'=>'mkdir'];
    }

    private function tree(string $path, string $policy): void
    {
        if (!$this->exists($path)) return; // A read-only check never creates paths.
        $stat = $this->inspect($path, $policy.'_dir');
        if (($stat['mode'] & 0170000) !== 0040000) $this->failure($path, '需要真实目录');
        $names = @scandir($path);
        if ($names === false) $this->failure($path, '目录不可读，无法检查权限');
        foreach ($names as $name) {
            if ($name === '.' || $name === '..') continue;
            $child = $path.'/'.$name;
            if (in_array($child, $this->ignoredYaml, true)) continue;
            Entry::assertPath($child);
            $stat = @lstat($child);
            // Files may expire concurrently in an already running cache.
            if (!$stat) continue;
            if (($stat['mode'] & 0170000) === 0040000) $this->tree($child, $policy);
            else $this->inspect($child, $policy.'_file');
        }
    }

    private function inspect(string $path, string $policy): array
    {
        Entry::assertPath($path);
        clearstatcache(true, $path);
        $stat = @lstat($path);
        if (!$stat) $this->failure($path, '文件或目录无法读取');
        $directory = str_ends_with($policy, '_dir') || in_array($policy, ['parent', 'writable_parent'], true);
        if (($stat['mode'] & 0170000) !== ($directory ? 0040000 : 0100000) || (!$directory && $stat['nlink'] !== 1)) {
            $this->failure($path, '拒绝链接、特殊文件或错误路径类型');
        }
        if ($policy === 'secret_file' && ($stat['size'] > 1048576 || in_array($path, [Entry::directory().'/webman', Entry::directory().'/start.php', \Phar::running(false)], true))) {
            $this->failure($path, '配置文件过大或错误指向程序入口');
        }
        $old = $stat['mode'] & 07777;
        $mode = match ($policy) {
            // Existing 0400/0640/0660 deployments remain valid. Remove access
            // for other users and execution bits rather than requiring 0600.
            'secret_file'=>($old & 0660) | 0400,
            'private_file'=>0600, 'private_dir'=>0700,
            'writable_file'=>0644, 'writable_dir'=>0755,
            'resource_file'=>($old & 0644) | 0400,
            'resource_dir', 'parent'=>($old & 0755) | 0500,
            'writable_parent'=>($old & 0755) | 0700,
            default=>throw new \LogicException('Unknown permission policy'),
        };
        ++$this->checked;
        if ($mode !== $old) {
            if ($this->fix) {
                // Check immediately before and after chmod. Trusted directory
                // owners remain required; PHP has no portable fchmod/no-follow.
                Entry::assertPath($path);
                $before = @lstat($path);
                if (!$before || !$this->same($stat, $before)) $this->failure($path, '文件在权限检查期间发生变化');
                if (!@chmod($path, $mode)) $this->failure($path, '权限自动修复失败');
                clearstatcache(true, $path);
                $after = @lstat($path);
                if (!$after || !$this->same($stat, $after) || ($after['mode'] & 07777) !== $mode) $this->failure($path, '权限修改后核验失败');
            }
            $this->changes[] = ['path'=>$path, 'from'=>sprintf('%04o', $old), 'to'=>sprintf('%04o', $mode), 'action'=>'chmod'];
        }
        if (($this->fix || $mode === $old) && (!is_readable($path) || ($directory && !is_executable($path)) ||
            (in_array($policy, ['private_dir','writable_dir','writable_parent','writable_file'], true) && !is_writable($path)))) {
            $this->failure($path, '当前运行账号仍无所需访问权限');
        }
        if ($this->progress && microtime(true)-$this->lastProgress >= 2) {
            ($this->progress)('已检查 '.$this->checked.' 项，已调整 '.count($this->changes).' 项');
            $this->lastProgress = microtime(true);
        }
        return $stat;
    }

    private function same(array $a, array $b): bool
    {
        return $a['dev']===$b['dev'] && $a['ino']===$b['ino'] && $a['uid']===$b['uid'] && $a['gid']===$b['gid'] && $a['nlink']===$b['nlink'];
    }

    private function failure(string $path, string $message): never
    {
        throw new \RuntimeException($message.'：'.$path.'；请由文件所有者处理，确认运行账号、ACL 和挂载读写状态（程序不自动 chown）');
    }
}
