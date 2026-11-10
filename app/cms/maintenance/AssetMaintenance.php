<?php
declare(strict_types=1);
namespace app\cms\maintenance;

use app\cms\Paths;
use InvalidArgumentException;
use RuntimeException;

/** Local asset verification needs neither Node.js nor a database connection. */
final class AssetMaintenance
{
    private const PUBLIC_ROOTS = [Paths::PUBLIC_ASSETS . '/'];
    private const PRIVATE_ROOTS = ['app/cms/resources/fonts/', 'app/cms/resources/defaults/', 'app/cms/resources/legacy-assets/', 'app/view/cms/legacy-template/'];
    private const EXECUTABLE = ['php','phtml','phar','php3','php4','php5','php7','php8','cgi','pl','sh'];
    private const MUTABLE_TARGET = Paths::COMMON_ASSETS . '/generated/lang/foreign_global.js';
    private string $root;

    public function __construct(?string $root = null)
    {
        $this->root = rtrim($root ?? base_path(), '/');
        if (!str_starts_with($this->root, '/') || !is_dir($this->root)) throw new InvalidArgumentException('资源项目目录必须是已存在的绝对路径');
        $this->noLinks($this->root);
    }

    /** check validates layout and presence; verify additionally reads every target SHA. */
    public function inspect(bool $verify = false): array
    {
        $file = $this->root . '/app/cms/resources/asset-manifest.json';
        $this->noLinks($file);
        if (!is_file($file)) throw new RuntimeException('资源清单不存在，请先恢复完整源码与资源包');
        $manifest = json_decode((string)file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        if (($manifest['format'] ?? null) !== 2 || !isset($manifest['files']) || !is_array($manifest['files']) || !array_is_list($manifest['files']) || !$manifest['files']) throw new RuntimeException('只接受 format=2 的新架构资源清单');
        $targets = []; $sources = []; $urls = []; $readAliases = []; $errors = []; $sourceBytes = 0;
        foreach ($manifest['files'] as $record) {
            if (!is_array($record)) throw new RuntimeException('资源清单项无效');
            $source = $this->relative($record['source'] ?? null);
            $target = $this->relative($record['target'] ?? null);
            $public = $record['public'] ?? null;
            $html = $record['html_allowed'] ?? null;
            $mutable = $record['mutable'] ?? false;
            if (!is_bool($mutable) || ($mutable && $target !== self::MUTABLE_TARGET)) throw new RuntimeException('可变资源只能是语言包生成文件：' . $target);
            if (!is_bool($public) || !is_bool($html) || !is_string($record['action'] ?? null) || !is_array($record['aliases'] ?? null)) throw new RuntimeException('资源分类或别名无效：' . $target);
            $roots = $public ? self::PUBLIC_ROOTS : self::PRIVATE_ROOTS;
            if (!$this->inRoots($target, $roots) || in_array(strtolower(pathinfo($target, PATHINFO_EXTENSION)), self::EXECUTABLE, true)) throw new RuntimeException('资源目标超出允许目录：' . $target);
            $size = $record['size'] ?? $record['bytes'] ?? null;
            $sourceSize = $record['source_size'] ?? null;
            if (!is_int($size) || $size < 0 || !is_int($sourceSize) || $sourceSize < 0 || !$this->sha($record['sha256'] ?? null) || !$this->sha($record['source_sha256'] ?? null)) throw new RuntimeException('资源大小或 SHA 无效：' . $target);
            $sourceIdentity = ['sha256'=>$record['source_sha256'], 'size'=>$sourceSize];
            if (isset($sources[$source]) && $sources[$source] !== $sourceIdentity) throw new RuntimeException('资源来源出现冲突记录：' . $source);
            $sources[$source] = $sourceIdentity;
            if (isset($record['size'], $record['bytes']) && $record['size'] !== $record['bytes']) throw new RuntimeException('资源大小字段冲突：' . $target);
            if ($public && in_array(strtolower(pathinfo($target, PATHINFO_EXTENSION)), ['html','htm'], true) && !$html) throw new RuntimeException('公开 HTML 缺少显式允许标记：' . $target);
            $url = $record['url'] ?? null;
            if (!$public && (($url !== null && $url !== '') || $record['aliases'])) throw new RuntimeException('私有资源不能配置公开 URL：' . $target);
            if (!is_array($record['read_aliases'] ?? [])) throw new RuntimeException('文件读取别名无效：' . $target);
            foreach ($record['read_aliases'] ?? [] as $alias) {
                if (!is_string($alias) || str_starts_with($alias, '//')) throw new RuntimeException('文件读取别名无效');
                $this->relative(ltrim($alias, '/'));
                if (isset($readAliases[$alias]) && $readAliases[$alias] !== $target) throw new RuntimeException('文件读取别名指向多个实体：' . $alias);
                $readAliases[$alias] = $target;
            }
            if ($public) {
                if (!is_string($url) || $url === '') throw new RuntimeException('公开资源缺少 URL：' . $target);
                $canonical = str_starts_with($target, 'public/') ? substr($target, 6) : '/' . $target;
                if ($url !== $canonical) throw new RuntimeException('资源规范 URL 与实体目录不符：' . $target);
                foreach (array_merge([$url], $record['aliases']) as $alias) {
                    $this->url($alias);
                    if (isset($urls[$alias]) && $urls[$alias] !== $target) throw new RuntimeException('资源 URL 指向多个实体：' . $alias);
                    $urls[$alias] = $target;
                }
            }
            $identity = ['sha256'=>$record['sha256'], 'size'=>$size, 'public'=>$public, 'html_allowed'=>$html, 'mutable'=>$mutable];
            if (isset($targets[$target]) && $targets[$target] !== $identity) throw new RuntimeException('资源实体出现冲突记录：' . $target);
            $targets[$target] = $identity;
            $sourceBytes += $sourceSize;
        }
        $bytes = 0; $mutableFiles = [];
        foreach ($targets as $target => $record) {
            $path = $this->root . '/' . $target;
            $this->noLinks($path);
            if (!is_file($path)) { $errors[] = ['target'=>$target, 'reason'=>'missing']; continue; }
            $bytes += filesize($path);
            if ($record['mutable']) {
                $actualSha = (string)hash_file('sha256', $path);
                $mutableFiles[] = ['target'=>$target, 'actual_size'=>filesize($path), 'actual_sha256'=>$actualSha, 'baseline_sha256'=>$record['sha256'], 'matches_baseline'=>filesize($path)===$record['size'] && hash_equals($record['sha256'], $actualSha)];
                continue;
            }
            if ($verify && (filesize($path) !== $record['size'] || !hash_equals($record['sha256'], (string)hash_file('sha256', $path)))) $errors[] = ['target'=>$target, 'reason'=>'size_or_sha_mismatch'];
        }
        return ['ok'=>!$errors, 'mode'=>$verify?'verify':'check', 'format'=>2, 'records'=>count($manifest['files']), 'unique_targets'=>count($targets), 'aliases_and_urls'=>count($urls), 'read_aliases'=>count($readAliases), 'source_bytes'=>$sourceBytes, 'target_bytes'=>$bytes, 'mutable_files'=>$mutableFiles, 'errors'=>$errors];
    }

    public function build(array $options, bool $apply = false): int
    {
        $args = [\app\cms\config\SiteSettings::get('asset_node_binary'), $this->root . '/app/cms/maintenance/build_assets.mjs'];
        foreach (['input', 'output'] as $name) if (!empty($options[$name])) { $this->absolute((string)$options[$name]); $args[]='--'.$name; $args[]=$options[$name]; }
        if ($apply) $args[]='--apply';
        return $this->process($args);
    }

    public function restore(array $options, bool $apply = false): int
    {
        if (empty($options['archive'])) throw new InvalidArgumentException('restore 需要 --archive 指定 format=2 资源包');
        $this->absolute((string)$options['archive']);
        $args = [\app\cms\config\SiteSettings::get('asset_python_binary'), $this->root . '/app/cms/maintenance/restore_assets.py', '--archive', $options['archive']];
        if (!empty($options['destination'])) { $this->absolute((string)$options['destination']); $args[]='--destination'; $args[]=$options['destination']; }
        if ($apply) $args[]='--apply';
        return $this->process($args);
    }

    private function process(array $args): int
    {
        if (!is_file($args[1])) throw new RuntimeException('资源维护工具不存在');
        $process = proc_open($args, [0=>STDIN, 1=>STDOUT, 2=>STDERR], $pipes, $this->root);
        if (!is_resource($process)) throw new RuntimeException('无法启动资源维护工具');
        return proc_close($process);
    }
    private function relative(mixed $path): string
    {
        if (!is_string($path) || $path === '' || str_starts_with($path, '/') || str_contains($path, '\\') || preg_match('/[\x00-\x1f\x7f]/', $path)) throw new RuntimeException('资源相对路径无效');
        foreach (explode('/', $path) as $part) if ($part === '' || $part === '.' || $part === '..' || str_starts_with($part, '.')) throw new RuntimeException('资源相对路径无效');
        return $path;
    }
    private function url(mixed $url): void
    {
        if (!is_string($url) || !str_starts_with($url, '/') || str_starts_with($url, '//') || strpbrk($url, "?#\\\r\n\0") !== false) throw new RuntimeException('资源 URL 无效');
        $decoded = $url;
        for ($i=0;$i<3;$i++) { $next=rawurldecode($decoded); if ($next===$decoded) break; $decoded=$next; }
        $this->relative(substr($decoded, 1));
    }
    private function inRoots(string $path, array $roots): bool { foreach ($roots as $root) if (str_starts_with($path, $root)) return true; return false; }
    private function sha(mixed $value): bool { return is_string($value) && preg_match('/^[a-f0-9]{64}$/D', $value) === 1; }
    private function absolute(string $path): void { if (!str_starts_with($path, '/')) throw new InvalidArgumentException('维护输入输出必须为绝对路径'); $this->noLinks($path); }
    private function noLinks(string $path): void
    {
        $current='';foreach(explode('/',ltrim($path,'/'))as$part){if($part===''||$part==='.'||$part==='..')throw new RuntimeException('维护路径无效');$current.='/'.$part;if(is_link($current))throw new RuntimeException('资源路径不能包含符号链接：'.$current);}
    }
}
