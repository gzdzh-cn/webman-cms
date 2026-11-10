<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\config\Environment;
use RuntimeException;

/** A fixed, site-local restart, coordinated separately from installer handoff. */
final class AdminRestartService
{
    private string $root;
    public function __construct(?string $root = null)
    {
        $this->root = realpath($root ?? base_path()) ?: throw new RuntimeException('项目路径无效');
    }

    public function start(int $adminId): array
    {
        foreach (['pcntl_fork','pcntl_exec','pcntl_waitpid','posix_setsid','posix_kill','posix_getppid','proc_open'] as $function) {
            if (!function_exists($function)) throw new RuntimeException('当前环境不支持后台重启，请由服务器管理员操作');
        }
        if ($adminId < 1) throw new RuntimeException('管理员身份无效');
        $master = $this->master();
        if (!$master || posix_getppid() !== $master) throw new RuntimeException('无法核验当前服务主进程，请由服务器管理员操作');
        $record = $this->locked(function (?array &$record) use ($adminId, $master): array {
            $this->expire($record);
            if ($record && in_array($record['status'], ['scheduled','restarting'], true)) throw new RuntimeException('已有重启任务正在执行，请稍候');
            $this->assertExecutorAvailable();
            if ($this->master() !== $master || posix_getppid() !== $master) throw new RuntimeException('主进程已变化，请刷新后重试');
            $record = ['id'=>bin2hex(random_bytes(16)), 'admin'=>$adminId, 'old_master'=>$master, 'status'=>'scheduled', 'created_at'=>time()];
            return $record;
        });
        $this->launch($record['id']);
        return $this->status($record['id'], $adminId);
    }

    public function status(string $id, int $adminId): array
    {
        return $this->locked(function (?array &$record) use ($id, $adminId): array {
            if (!$record || !preg_match('/^[a-f0-9]{32}$/D', $id) || !hash_equals($record['id'], $id) || $record['admin'] !== $adminId) throw new RuntimeException('重启任务不存在或已失效');
            $this->expire($record);
            if (in_array($record['status'], ['scheduled','restarting'], true)) {
                try {
                    $master = $this->master();
                    if ($master && $master !== $record['old_master'] && posix_getppid() === $master) {
                        $record['status'] = 'ready'; $record['ready_at'] = time();
                    }
                } catch (\Throwable) { /* A stale or unavailable process is never ready. */ }
            }
            return ['id'=>$record['id'], 'status'=>$record['status'], 'message'=>match ($record['status']) {
                'scheduled'=>'正在安排重启…', 'restarting'=>'程序正在重启，请稍候…', 'ready'=>'程序已重新启动', default=>$record['failure'] ?? '重启未完成，请检查服务日志'
            }];
        });
    }

    private function launch(string $id): void
    {
        try {
            $environment = Environment::processEnvironment();
            $pid = pcntl_fork();
            if ($pid < 0) throw new RuntimeException('无法启动独立重启进程');
            if ($pid === 0) {
                try {
                    foreach (get_resources('stream') as $stream) if (is_resource($stream) && $stream !== STDIN && $stream !== STDOUT && $stream !== STDERR) @fclose($stream);
                    pcntl_exec(PHP_BINARY, [$this->root.'/app/cms/maintenance/admin-restart.php', $id], $environment);
                    $this->failed($id, '无法启动独立重启进程，请检查 PHP 执行权限');
                } catch (\Throwable) { $this->failed($id, '无法启动独立重启进程，请检查 PHP 执行权限'); }
                finally { posix_kill(getmypid(), SIGKILL); exit(1); }
            }
            if (\Workerman\Worker::$globalEvent !== null) {
                $timer = null;
                $timer = \Workerman\Timer::add(0.2, static function () use ($pid, &$timer): void {
                    if (pcntl_waitpid($pid, $status, WNOHANG) !== 0) \Workerman\Timer::del($timer);
                });
            } else pcntl_waitpid($pid, $status);
        } catch (\Throwable) { $this->failed($id, '无法安排后台重启，请检查 PHP 进程权限'); }
    }

    /** Called by the detached CLI launcher, never with a user supplied command. */
    public function execute(string $id): void
    {
        $handle = $this->executorLock();
        if (!flock($handle, LOCK_EX | LOCK_NB)) { fclose($handle); return; }
        try {
            usleep(1500000);
            $record = $this->locked(function (?array &$record) use ($id): ?array {
                if (!$record || !hash_equals($record['id'], $id) || $record['status'] !== 'scheduled') return null;
                if ($this->master() !== $record['old_master']) throw new RuntimeException('主进程已变化，已取消重复重启');
                $record['status'] = 'restarting'; $record['started_at'] = time();
                return $record;
            });
            if (!$record) return;
            $log = $this->logFile();
            $process = proc_open([PHP_BINARY, $this->root.'/start.php', 'restart', '-d'],
                [0=>['file','/dev/null','r'],1=>['file',$log,'a'],2=>['file',$log,'a']], $pipes, $this->root, Environment::processEnvironment());
            if (!is_resource($process)) throw new RuntimeException('重启命令启动失败');
            $deadline = microtime(true) + 30;
            do { $state = proc_get_status($process); if (!$state['running']) break; usleep(100000); } while (microtime(true) < $deadline);
            if ($state['running']) { proc_terminate($process); proc_close($process); throw new RuntimeException('重启命令执行超时，请检查服务状态'); }
            $exit = $state['exitcode']; proc_close($process);
            if ($exit !== 0) throw new RuntimeException('重启命令执行失败，请查看服务日志');
            // A newly served status request confirms readiness. No port or URL is trusted from the request.
        } catch (\Throwable $error) { $this->failed($id, $error->getMessage()); }
        finally { flock($handle, LOCK_UN); fclose($handle); }
    }

    private function expire(?array &$record): void
    {
        if ($record && in_array($record['status'], ['scheduled','restarting'], true) && time() > $record['created_at'] + 90) {
            $record['status']='failed'; $record['failure']='重启未在规定时间内完成，请检查服务状态';
        }
    }
    private function failed(string $id, string $message): void
    {
        try { $this->locked(function (?array &$record) use ($id, $message): void {
            if ($record && hash_equals($record['id'], $id) && in_array($record['status'], ['scheduled','restarting'], true)) { $record['status']='failed'; $record['failure']=$message; }
        }); } catch (\Throwable) {}
    }
    private function master(): int
    {
        $file = $this->root.'/runtime/webman.pid';
        if (is_link($this->root.'/runtime') || is_link($file)) throw new RuntimeException('进程 PID 路径无效');
        clearstatcache(true, $file);
        if (!is_file($file)) return 0;
        $pid = trim((string)file_get_contents($file));
        if (!ctype_digit($pid) || (int)$pid < 2) throw new RuntimeException('进程 PID 无效');
        if (!posix_kill((int)$pid, 0)) return 0;
        $process = proc_open(['/bin/ps','-p',$pid,'-o','command='], [0=>['file','/dev/null','r'],1=>['pipe','w'],2=>['file','/dev/null','a']], $pipes);
        if (!is_resource($process)) throw new RuntimeException('无法核验主进程身份');
        $command = trim((string)stream_get_contents($pipes[1])); fclose($pipes[1]); proc_close($process);
        if (!in_array($command, ['WorkerMan: master process  start_file='.$this->root.'/webman', 'WorkerMan: master process  start_file='.$this->root.'/start.php'], true)) throw new RuntimeException('PID 不属于当前项目的 Webman 主进程');
        return (int)$pid;
    }
    private function directory(string $part): string
    {
        $path = $this->root;
        foreach (explode('/', $part) as $segment) {
            $path .= '/'.$segment;
            if (is_link($path) || (file_exists($path) && !is_dir($path))) throw new RuntimeException('重启运行路径无效');
            if (!is_dir($path) && !mkdir($path, 0700) && !is_dir($path)) throw new RuntimeException('无法创建重启运行路径');
        }
        return $path;
    }
    private function logFile(): string
    {
        $file = $this->directory('runtime/logs').'/admin-restart.log';
        if (is_link($file) || (file_exists($file) && !is_file($file))) throw new RuntimeException('重启日志路径无效');
        return $file;
    }
    private function executorLock()
    {
        $file=$this->directory('runtime/locks').'/admin-restart-executor.lock';
        if (is_link($file)) throw new RuntimeException('重启互斥路径无效');
        $handle=fopen($file, 'c+b');
        if (!$handle || !chmod($file, 0600)) throw new RuntimeException('无法创建重启互斥');
        return $handle;
    }
    private function assertExecutorAvailable(): void
    {
        $handle=$this->executorLock();
        try { if (!flock($handle, LOCK_EX | LOCK_NB)) throw new RuntimeException('已有重启任务正在执行，请稍候'); flock($handle, LOCK_UN); }
        finally { fclose($handle); }
    }
    private function locked(callable $operation): mixed
    {
        $dir=$this->directory('runtime/locks'); $lock=$dir.'/admin-restart.lock'; $file=$dir.'/admin-restart.json';
        if (is_link($lock) || is_link($file)) throw new RuntimeException('重启任务路径无效');
        $handle=fopen($lock,'c+b');
        if (!$handle || !chmod($lock,0600) || !flock($handle,LOCK_EX)) throw new RuntimeException('无法锁定重启任务');
        try {
            $record=null;
            if (is_file($file)) { $record=json_decode((string)file_get_contents($file),true,512,JSON_THROW_ON_ERROR); if (!is_array($record) || !isset($record['id'],$record['status'],$record['admin'],$record['old_master'],$record['created_at'])) throw new RuntimeException('重启任务记录无效'); }
            $before=$record; $result=$operation($record);
            if ($record !== $before) {
                $temp=tempnam($dir,'.restart-');
                try { $bytes=json_encode($record,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR); if (!$temp || !chmod($temp,0600) || file_put_contents($temp,$bytes,LOCK_EX)!==strlen($bytes) || !rename($temp,$file)) throw new RuntimeException('无法保存重启任务'); }
                finally { if ($temp && is_file($temp)) unlink($temp); }
            }
            return $result;
        } finally { flock($handle,LOCK_UN); fclose($handle); }
    }
}
