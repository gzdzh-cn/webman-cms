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
        $this->root = realpath($root ?? \app\cms\Paths::root()) ?: throw new RuntimeException('项目路径无效');
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
        if (\app\cms\support\ProcessLauncher::managed()) {
            \app\cms\support\ProcessLauncher::assertManaged();
            \Workerman\Timer::add(1.5, function () use ($id): void { $this->execute($id); }, [], false);
            return;
        }
        try {
            $environment = Environment::processEnvironment();
            $pid = pcntl_fork();
            if ($pid < 0) throw new RuntimeException('无法启动独立重启进程');
            if ($pid === 0) {
                try {
                    foreach (get_resources('stream') as $stream) if (is_resource($stream) && $stream !== STDIN && $stream !== STDOUT && $stream !== STDERR) @fclose($stream);
                    $command=\app\cms\support\ProcessLauncher::command(['cms:restart-executor','admin',$id]);
                    pcntl_exec(array_shift($command), $command, $environment);
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
            $this->preflight();
            if (\app\cms\support\ProcessLauncher::managed()) {
                \app\cms\support\ProcessLauncher::assertManaged();
                if (!posix_kill($record['old_master'], SIGQUIT)) throw new RuntimeException('无法停止当前服务');
                return;
            }
            $log = $this->logFile();
            $process = proc_open(\app\cms\support\ProcessLauncher::command(['restart','-d','-g']),
                [0=>['file','/dev/null','r'],1=>['file',$log,'a'],2=>['file',$log,'a']], $pipes, $this->root, Environment::processEnvironment());
            if (!is_resource($process)) throw new RuntimeException('重启命令启动失败');
            $deadline = microtime(true) + $this->restartTimeout();
            do { $state = proc_get_status($process); if (!$state['running']) break; usleep(100000); } while (microtime(true) < $deadline);
            if ($state['running']) { proc_terminate($process); proc_close($process); throw new RuntimeException('重启命令执行超时，请检查服务状态'); }
            $exit = $state['exitcode']; proc_close($process);
            if ($exit !== 0) throw new RuntimeException('重启命令执行失败，请查看服务日志');
            // A newly served status request confirms readiness. No port or URL is trusted from the request.
        } catch (\Throwable $error) { $this->failed($id, $error->getMessage()); }
        finally { flock($handle, LOCK_UN); fclose($handle); }
    }

    /** A fresh process reads the next configuration before the old master receives a signal. */
    private function preflight(): void
    {
        $log=$this->logFile();
        // Fresh processes select the next startup's YAML, including a newly
        // added config.yaml with higher priority than this worker's snapshot.
        $commands=[['cms:deployment','--check']];
        if (\app\cms\support\EmbeddedResources::binary()) array_unshift($commands, ['cms:permissions','--fix']);
        foreach ($commands as $arguments) {
            $process=proc_open(\app\cms\support\ProcessLauncher::command($arguments),[0=>['file','/dev/null','r'],1=>['file',$log,'a'],2=>['file',$log,'a']],$pipes,$this->root,Environment::processEnvironment());
            if(!is_resource($process))throw new RuntimeException('无法执行重启前部署检查，现有进程已保留');
            $deadline=microtime(true)+30;
            do{$state=proc_get_status($process);if(!$state['running'])break;usleep(100000);}while(microtime(true)<$deadline);
            if($state['running']){proc_terminate($process);proc_close($process);throw new RuntimeException('重启前检查超时，现有进程已保留');}
            $exit=$state['exitcode'];proc_close($process);
            if($exit!==0)throw new RuntimeException('新配置、文件权限或资源检查失败，现有进程已保留，请查看重启日志');
        }
    }
    private function expire(?array &$record): void
    {
        if ($record && in_array($record['status'], ['scheduled','restarting'], true) && time() > $record['created_at'] + $this->restartTimeout() + 60) {
            $record['status']='failed'; $record['failure']='重启未在规定时间内完成，请检查服务状态';
        }
    }
    public static function restartTimeout(): int
    {
        return (int)\app\cms\config\DeploymentConfig::get('server.stop_timeout') + 15;
    }
    private function failed(string $id, string $message): void
    {
        try { $this->locked(function (?array &$record) use ($id, $message): void {
            if ($record && hash_equals($record['id'], $id) && in_array($record['status'], ['scheduled','restarting'], true)) { $record['status']='failed'; $record['failure']=$message; }
        }); } catch (\Throwable) {}
    }
    private function master(): int { return \app\cms\support\ProcessLauncher::master(); }
    private function directory(string $part): string
    {
        $relative=preg_replace('#^runtime/?#','',$part);$path=\app\cms\Paths::runtime($relative);
        \app\cms\Paths::ensureDirectory($path,0700);return$path;
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
        $store=new \app\cms\persistence\StateStore('maintenance:restart:'.substr(hash('sha256',$this->root),0,20));
        return $store->locked(function($store)use($operation){$record=$store->read('current')?:null;$before=$record;$result=$operation($record);if($record!==$before)$store->write('current',$record??[]);return$result;});
    }
}
