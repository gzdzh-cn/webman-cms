<?php
declare(strict_types=1);
namespace app\cms\install;

use PDO;
use app\cms\config\Environment;
use app\cms\database\DatabaseSettings;
use RuntimeException;
use support\Request;
use plugin\admin\app\model\Admin;

/** Durable, task-scoped handoff from the installer to the newly started site. */
final class InstallerHandoff
{
    private const RECORD = 'config/cms-install-handoff.json';
    private const LOCK = 'config/cms-install-handoff.lock';
    private string $root;

    public function __construct(?string $root = null) { $this->root = rtrim($root ?? \app\cms\Paths::root(), '/'); }

    /** Called only after the environment and the installation lock have been saved. */
    public function prepare(array $journal, bool $archivePending = false, ?int $completedAt = null): array
    {
        $launch = false;
        $view = $this->locked(function (?array &$record) use ($journal, $archivePending, $completedAt, &$launch): array {
            if ($record) {
                if (!hash_equals($record['task_id'], (string)$journal['id'])) throw new RuntimeException('存在其他安装的重启记录，请核对后继续');
                $this->assertEnvironment($record);
                $record['archive_pending'] = $archivePending;
                return $this->view($record);
            }
            $version = in_array($journal['config_storage'] ?? null, ['php-database-v1','yaml-database-v1'], true) ? 2 : 1;
            $env = $this->environment($version);
            $pdo = $this->connection($env);
            $prefix = $this->prefix($env);
            $query = $pdo->prepare('SELECT value FROM `'.$prefix.'backend_options` WHERE name=?');
            $query->execute(['cms_site_signature']);
            if (!hash_equals((string)$journal['site_signature'], (string)$query->fetchColumn())) throw new RuntimeException('当前环境配置不属于本次安装');
            $query = $pdo->prepare('SELECT id,password,status FROM `'.$prefix.'backend_admins` WHERE id=?');
            $query->execute([(int)($journal['admin_id'] ?? 0)]);
            $admin = $query->fetch(PDO::FETCH_ASSOC);
            if (!$admin || (int)$admin['status'] !== 0) throw new RuntimeException('本次安装管理员无效');
            $master = $this->master();
            $record = [
                'version'=>$version, 'task_id'=>$journal['id'], 'site_signature'=>$journal['site_signature'],
                'owner_hash'=>$journal['browser_owner_hash'] ?? null, 'admin_id'=>(int)$admin['id'],
                'password_fingerprint'=>hash('sha256', $admin['password']), 'completed_at'=>$completedAt ?? time(),
                'environment_sha256'=>$version === 1 ? hash_file('sha256', \app\cms\config\Environment::path()) : null,
                'configuration_sources_sha256'=>$version === 2 ? $this->sourcesFingerprint() : null,
                'effective_configuration_sha256'=>$version === 2 ? $this->effectiveFingerprint($env) : null,
                'template_mode'=>$journal['template_mode'] ?? null,
                'old_master'=>$master, 'restart_status'=>$master ? 'scheduled' : 'not_running',
                'attempt'=>1, 'scheduled_at'=>time(), 'archive_pending'=>$archivePending, 'grant_status'=>'available',
                'probe_secret'=>bin2hex(random_bytes(32)),
                'listen'=>($version === 2 ? $this->runtimeListen() : \app\cms\config\DeploymentConfig::listener(getenv('WEBMAN_LISTEN') ?: 8787)),
                'overrides'=>array_filter(['WEBMAN_LISTEN'=>getenv('WEBMAN_LISTEN'), 'WEBMAN_WORKERS'=>getenv('WEBMAN_WORKERS')], static fn($value)=>$value !== false),
            ];
            $launch = $master > 0;
            return $this->view($record);
        });
        if ($launch) $this->launch();
        return $this->currentView() ?? $view;
    }

    public function status(string $owner): array
    {
        return $this->locked(function (?array &$record) use ($owner): array {
            if ($record === null) {
                $journal = $this->completedJournal($owner);
                return ['task_id'=>$journal['id'], 'restart_status'=>'failed', 'login_available'=>false,
                    'message'=>'安装已完成，自动重启尚未安排，请点击“重试自动重启”继续。', 'archive_pending'=>false];
            }
            $this->assertOwner($record, $owner);
            $this->refreshReady($record);
            return $this->view($record);
        });
    }

    /** Old workers must not load the newly installed session through their old database connection. */
    public function awaitingRestart(): bool
    {
        $record=$this->read();
        return $record && in_array($record['restart_status'],['scheduled','restarting'],true)
            && (int)$record['old_master']>0 && function_exists('posix_getppid')
            && posix_getppid()===(int)$record['old_master'];
    }

    /** Internal read/ready probe; never grants a browser login or a restart. */
    public function probe(string $secret): array
    {
        return $this->locked(function (?array &$record) use ($secret): array {
            if (!$record || $secret === '' || !hash_equals($record['probe_secret'], $secret)) throw new RuntimeException('安装任务无效');
            $this->refreshReady($record);
            return ['restart_status'=>$record['restart_status']];
        });
    }

    public function retry(string $owner): array
    {
        if ($this->read() === null) {
            $journal = $this->completedJournal($owner);
            return $this->prepare($journal, false, $journal['updated_at']);
        }
        $launch = false;
        $view = $this->locked(function (?array &$record) use ($owner, &$launch): array {
            $this->assertOwner($record, $owner);
            if (time() > $record['completed_at'] + 1800) throw new RuntimeException('安装交接已过期，请由服务器管理员检查服务');
            $this->refreshReady($record);
            if ($record['restart_status'] !== 'failed') return $this->view($record);
            $master = $this->master();
            // Never run restart against a replacement master which may still be starting.
            if (!$master || $master !== $record['old_master']) throw new RuntimeException('进程已变化，请先重新检查服务；不要重复重启');
            $this->assertEnvironment($record);
            $record['restart_status'] = 'scheduled';
            $record['scheduled_at'] = time();
            unset($record['failure'], $record['started_at']);
            ++$record['attempt'];
            $launch = true;
            return $this->view($record);
        });
        if ($launch) $this->launch();
        return $this->currentView() ?? $view;
    }

    public function login(Request $request, string $owner): array
    {
        return $this->locked(function (?array &$record) use ($request, $owner): array {
            $this->assertOwner($record, $owner);
            $this->refreshReady($record);
            if ($record['restart_status'] !== 'ready') throw new RuntimeException('服务尚未就绪，请等待重启完成');
            $this->assertRuntime($record);
            $admin = Admin::find($record['admin_id']);
            if (!$admin || (int)$admin->status !== 0 || !hash_equals($record['password_fingerprint'], hash('sha256', $admin->password))) {
                throw new RuntimeException('管理员已变更或停用，请使用正常登录入口');
            }
            $logged = $request->session()->get('admin');
            if (is_array($logged) && (int)($logged['id'] ?? 0) === $record['admin_id']
                && hash_equals(md5($admin->password), (string)($logged['password'] ?? ''))
                && (new \app\cms\security\AdminSessionRegistry())->valid($request->sessionId(), $logged)
                && ($request->session()->get('cms_install_authenticated_task') === $record['task_id'])) {
                // Recovery after a lost response uses the already-authenticated session.
                $record['grant_status'] = 'consumed';
                return ['login_url'=>'/web-admin'];
            }
            if ($record['grant_status'] !== 'available') throw new RuntimeException('首次登录资格已使用，请使用正常登录入口');
            if (!$this->grantAvailable($record)) throw new RuntimeException('首次登录资格已过期，请使用正常登录入口');
            // Save a reservation before establishing a session. A crash can never reissue it.
            $record['grant_status'] = 'issuing';
            $this->write($record);
            $request->session()->set('cms_install_authenticated_task', $record['task_id']);
            try { \app\cms\admin\support\AdminLoginSession::establish($request, $admin); }
            catch (\Throwable $error) {
                // Known failures happen before issuing the new session cookie.
                // An interrupted process, in contrast, leaves the reservation closed.
                $record['grant_status']='available';$this->write($record);throw $error;
            }
            $record['grant_status'] = 'consumed';
            $record['consumed_at'] = time();
            return ['login_url'=>'/web-admin'];
        });
    }

    /** The detached launcher never loads application config or the old database. */
    private function launch(): void
    {
        $record = $this->read();
        try {
            if (\app\cms\support\ProcessLauncher::managed()) {
                \app\cms\support\ProcessLauncher::assertManaged();
                \Workerman\Timer::add(1.5,function()use($record){$this->execute($record['task_id'],$record['attempt']);},[],false);
                return;
            }
            foreach(['proc_open','pcntl_fork','pcntl_exec','pcntl_waitpid','posix_setsid','posix_kill'] as $function)if(!function_exists($function))throw new RuntimeException('当前环境无法创建独立重启进程');
            $environment=$this->processEnvironment($record);
            $pid=pcntl_fork();
            if($pid<0)throw new RuntimeException('无法启动独立重启进程');
            if($pid===0){
                // proc_open() can inherit Workerman's listening streams. Close
                // their child-side descriptors before exec, without touching
                // the serving parent's sockets or running its shutdown hooks.
                try{
                    foreach(get_resources('stream') as $stream){
                        if(is_resource($stream)&&$stream!==STDIN&&$stream!==STDOUT&&$stream!==STDERR)@fclose($stream);
                    }
                    $command=\app\cms\support\ProcessLauncher::command(['cms:restart-executor','install',$record['task_id'],(string)$record['attempt']]);
                    pcntl_exec(array_shift($command),$command,$environment);
                    $this->failed($record['task_id'],$record['attempt'],'独立重启进程执行失败，请检查 PHP 执行权限');
                }catch(\Throwable $error){$this->failed($record['task_id'],$record['attempt'],'独立重启进程执行失败，请检查 PHP 执行权限');}
                finally{posix_kill(getmypid(),SIGKILL);exit(1);}
            }
            // A failed launch may leave its HTTP worker alive. Reap only our
            // own short-lived launcher, without changing Workerman signals.
            if (\Workerman\Worker::$globalEvent !== null) {
                $timer = null;
                $timer = \Workerman\Timer::add(0.2, static function () use ($pid, &$timer): void {
                    if (pcntl_waitpid($pid, $status, WNOHANG) !== 0) \Workerman\Timer::del($timer);
                });
            } else pcntl_waitpid($pid, $status);
        } catch (\Throwable $error) { $this->failed($record['task_id'], $record['attempt'], '无法安排自动重启，请检查 PHP 进程权限后重试'); }
    }

    /** CLI entrypoint, invoked in a new session by the small detached launcher. */
    public function execute(string $task, int $attempt): void
    {
        $executor = \app\cms\Paths::config('cms-install-handoff-executor.lock');
        if (is_link($executor)) throw new RuntimeException('重启互斥路径无效');
        $handle = fopen($executor, 'c+b');
        if (!$handle || !chmod($executor, 0600)) throw new RuntimeException('无法创建重启互斥');
        if (!flock($handle, LOCK_EX|LOCK_NB)) { fclose($handle); return; }
        try {
            // Let the final response and session reach the browser before stopping workers.
            usleep(1500000);
            $record = $this->locked(function (?array &$record) use ($task, $attempt): ?array {
                if (!$record || !hash_equals($record['task_id'], $task) || $record['attempt'] !== $attempt || $record['restart_status'] !== 'scheduled') return null;
                $this->assertEnvironment($record);
                if ($this->master() !== $record['old_master']) throw new RuntimeException('主进程已变化，取消重复重启');
                $record['restart_status'] = 'restarting'; $record['started_at'] = time();
                return $record;
            });
            if (!$record) return;
            if (\app\cms\support\ProcessLauncher::managed()) {
                \app\cms\support\ProcessLauncher::assertManaged();
                if(!posix_kill($record['old_master'],SIGQUIT))throw new RuntimeException('无法停止当前服务');
                return;
            }
            $log = $this->logFile();
            $process = proc_open(\app\cms\support\ProcessLauncher::command(['restart','-d','-g']),
                [0=>['file','/dev/null','r'],1=>['file',$log,'a'],2=>['file',$log,'a']], $pipes, $this->root, $this->processEnvironment($record));
            if (!is_resource($process)) throw new RuntimeException('重启命令启动失败');
            $deadline = microtime(true) + \app\cms\service\AdminRestartService::restartTimeout();
            do { $state = proc_get_status($process); if (!$state['running']) break; usleep(100000); } while (microtime(true) < $deadline);
            if ($state['running']) { proc_terminate($process); proc_close($process); throw new RuntimeException('重启命令执行超时'); }
            $exit = $state['exitcode']; proc_close($process);
            if ($exit !== 0) throw new RuntimeException('重启命令执行失败');
            $url = $this->probeUrl($record['listen']);
            $deadline = microtime(true) + 55;
            do {
                $context = stream_context_create(['http'=>['timeout'=>2,'ignore_errors'=>true,'follow_location'=>0,
                    'header'=>"Accept: application/json\r\nX-CMS-Install-Probe: ".$record['probe_secret']."\r\nConnection: close\r\n"]]);
                $result = json_decode((string)@file_get_contents($url, false, $context), true);
                if (($result['data']['handoff']['restart_status'] ?? null) === 'ready') return;
                usleep(500000);
            } while (microtime(true) < $deadline);
            throw new RuntimeException('服务启动超时，请重新检查服务状态');
        } catch (\Throwable $error) { $this->failed($task, $attempt, $error->getMessage()); }
        finally { flock($handle, LOCK_UN); fclose($handle); }
    }

    private function refreshReady(array &$record): void
    {
        if (!in_array($record['restart_status'], ['scheduled','restarting','failed'], true)) return;
        try {
            $master = $this->master();
            if ($master && $master !== $record['old_master'] && function_exists('posix_getppid') && posix_getppid() === $master) {
                $this->assertRuntime($record);
                $record['restart_status'] = 'ready';
                $record['ready_at'] ??= time();
                unset($record['failure']);
            }
        } catch (\Throwable $error) { /* A live HTTP process with stale configuration is not ready. */ }
        if (($record['restart_status']==='scheduled'&&time()>($record['scheduled_at']??$record['completed_at'])+20)
            ||($record['restart_status']==='restarting'&&time()>($record['started_at']??0)+\app\cms\service\AdminRestartService::restartTimeout()+60)) {
            $record['restart_status']='failed';$record['failure']='自动重启未完成，请重新检查服务状态或重试';
        }
    }

    private function assertRuntime(array $record): void
    {
        $this->assertEnvironment($record);
        $env = $this->environment((int)$record['version']);
        if ($record['version'] === 1) {
            foreach (['DB_HOST','DB_PORT','DB_NAME','DB_USER','DB_PASSWORD','DB_PREFIX','DB_TABLE_LAYOUT','CMS_THEME'] as $key) {
                if ((string)getenv($key) !== (string)($env[$key] ?? '')) throw new RuntimeException('服务尚未加载新配置');
            }
        }
        $db = (new Admin())->getConnection();
        if ($db->getDatabaseName() !== $env['DB_NAME'] || $db->getTablePrefix() !== $this->prefix($env).'backend_') throw new RuntimeException('后台数据库配置尚未就绪');
        if ($record['version'] === 2) {
            $prefix = $this->prefix($env);
            foreach (['mysql'=>$prefix, 'plugin.admin.mysql'=>$prefix.'backend_', 'plugin.admin.cms'=>$prefix] as $name=>$expectedPrefix) {
                $connection=\support\Db::connection($name);
                foreach (['host'=>'DB_HOST','port'=>'DB_PORT','database'=>'DB_NAME','username'=>'DB_USER','password'=>'DB_PASSWORD'] as $setting=>$key) {
                    if ((string)$connection->getConfig($setting) !== (string)($env[$key] ?? '')) throw new RuntimeException('服务数据库配置尚未就绪');
                }
                if ($connection->getTablePrefix() !== $expectedPrefix) throw new RuntimeException('服务数据库前缀尚未就绪');
            }
            foreach (['mysql'=>$prefix, 'plugin.admin.mysql'=>$prefix.'backend_'] as $name=>$expectedPrefix) {
                $connection=\support\think\Db::connect($name);
                foreach (['hostname'=>'DB_HOST','hostport'=>'DB_PORT','database'=>'DB_NAME','username'=>'DB_USER','password'=>'DB_PASSWORD'] as $setting=>$key) {
                    if ((string)$connection->getConfig($setting) !== (string)($env[$key] ?? '')) throw new RuntimeException('服务数据库配置尚未就绪');
                }
                if ((string)$connection->getConfig('prefix') !== $expectedPrefix) throw new RuntimeException('服务数据库前缀尚未就绪');
            }
            foreach (['host'=>'DB_HOST','port'=>'DB_PORT','database'=>'DB_NAME','username'=>'DB_USER','password'=>'DB_PASSWORD'] as $setting=>$key) {
                if ((string)$db->getConfig($setting) !== (string)($env[$key] ?? '')) throw new RuntimeException('后台数据库配置尚未就绪');
            }
            if (($record['template_mode'] ?? null) !== $db->table('options')->where('name','cms_template_mode')->value('value')) throw new RuntimeException('站点模板模式不匹配');
        }
        if (!hash_equals($record['site_signature'], (string)$db->table('options')->where('name','cms_site_signature')->value('value'))) throw new RuntimeException('站点签名不匹配');
    }

    private function assertEnvironment(array $record): void
    {
        if ($record['version'] === 1) {
            $file = \app\cms\config\Environment::path();
            if (is_link($file) || !is_file($file) || !hash_equals($record['environment_sha256'], (string)hash_file('sha256',$file))) throw new RuntimeException('安装后的环境配置已变化，请人工核对');
        } else {
            if (!hash_equals((string)($record['configuration_sources_sha256'] ?? ''), $this->sourcesFingerprint())
                || !hash_equals((string)($record['effective_configuration_sha256'] ?? ''), $this->effectiveFingerprint($this->environment(2)))) throw new RuntimeException('安装后的配置已变化，请人工核对');
        }
        if (!((new InstallerService($this->root))->status()['installed'] ?? false)) throw new RuntimeException('安装入口仍开启，不能自动重启或登录');
    }

    private function assertOwner(?array $record, string $owner): void
    {
        if (!$record || $owner === '' || empty($record['owner_hash']) || !hash_equals($record['owner_hash'], hash('sha256',$owner))) throw new RuntimeException('安装交接会话不存在或已失效，请使用正常登录入口');
    }

    /** Recover only a missing handoff for the original, recent completion session. */
    private function completedJournal(string $owner): array
    {
        $file = \app\cms\Paths::config('cms-installing.json');
        if (is_link($file) || !is_file($file)) throw new RuntimeException('安装交接会话不存在或已失效，请使用正常登录入口');
        $journal = json_decode((string)file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($journal) || ($journal['version'] ?? null) !== 3 || ($journal['phase'] ?? '') !== 'complete'
            || !is_string($journal['id'] ?? null) || !preg_match('/^[a-f0-9]{24}$/D', $journal['id'])
            || !is_string($journal['browser_owner_hash'] ?? null) || $owner === ''
            || !hash_equals($journal['browser_owner_hash'], hash('sha256', $owner))
            || !is_int($journal['updated_at'] ?? null) || $journal['updated_at'] > time()
            || time() > $journal['updated_at'] + 1800
            || !((new InstallerService($this->root))->status()['installed'] ?? false)) {
            throw new RuntimeException('安装交接会话不存在或已失效，请使用正常登录入口');
        }
        return $journal;
    }

    private function grantAvailable(array $record): bool
    {
        return $record['restart_status'] === 'ready' && !empty($record['owner_hash']) && $record['grant_status'] === 'available'
            && time() <= min($record['completed_at']+1800, ($record['ready_at'] ?? 0)+600);
    }

    private function view(array $record): array
    {
        $messages = ['scheduled'=>'安装成功，正在安排重启服务…','restarting'=>'安装成功，正在重启服务…','ready'=>'服务已就绪，点击进入后台将自动登录。',
            'not_running'=>'安装成功，当前没有运行中的服务，请启动服务后正常登录。','failed'=>$record['failure'] ?? '自动重启失败，请检查服务后重试'];
        if($record['restart_status']==='ready'&&!$this->grantAvailable($record))$messages['ready']=$record['grant_status']==='consumed'
            ?'首次登录资格已使用；如已退出，请使用账号密码登录。':'首次登录资格已失效，请使用账号密码登录。';
        return ['task_id'=>$record['task_id'],'restart_status'=>$record['restart_status'],'login_available'=>$this->grantAvailable($record),
            'message'=>$messages[$record['restart_status']] ?? '正在检查服务状态','archive_pending'=>$record['archive_pending']];
    }

    private function currentView(): ?array { $record=$this->read(); return $record ? $this->view($record) : null; }

    private function failed(string $task, int $attempt, string $message): void
    {
        $this->locked(function (?array &$record) use ($task,$attempt,$message): void {
            if ($record && hash_equals($record['task_id'],$task) && $record['attempt']===$attempt && $record['restart_status']!=='ready') {
                $record['restart_status']='failed'; $record['failure']=$message;
            }
        });
    }

    private function master(): int { return \app\cms\support\ProcessLauncher::master(); }
    private function environment(int $version = 1): array
    {
        if ($version === 2) return DatabaseSettings::fresh(true);
        $file=\app\cms\config\Environment::path();
        if (is_link($file)||!is_file($file)) throw new RuntimeException('安装环境配置不存在');
        return \Dotenv\Dotenv::parse((string)file_get_contents($file));
    }
    private function prefix(array $env): string
    {
        $prefix=(string)($env['DB_PREFIX']??'');
        if (($env['DB_TABLE_LAYOUT']??'')!=='unified-backend-v1'||!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,31}$/D',$prefix)) throw new RuntimeException('安装后的数据表布局无效');
        return $prefix;
    }
    private function connection(array $env): PDO
    {
        return new PDO('mysql:host='.$env['DB_HOST'].';port='.$env['DB_PORT'].';dbname='.$env['DB_NAME'].';charset=utf8mb4', $env['DB_USER'], $env['DB_PASSWORD'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);
    }
    private function processEnvironment(array $record): array
    {
        if (($record['version'] ?? 1) === 2) return Environment::processEnvironment();
        $env=getenv();
        foreach (array_unique(array_merge(array_keys($this->environment()),['CMS_DB_PREFIX','ADMIN_DB_PREFIX'])) as $key) unset($env[$key]);
        return array_replace($env,$record['overrides']);
    }
    private function sourcesFingerprint(): string
    {
        Environment::initialize($this->root);
        $path = \app\cms\config\Environment::path();
        clearstatcache(true, $path);
        if (is_link($path) || (file_exists($path) && !is_file($path))) throw new RuntimeException('环境配置路径无效');
        $envFile = is_file($path) ? ['present'=>true, 'sha256'=>hash_file('sha256', $path)] : ['present'=>false];
        $keys = array_flip(['DB_HOST','DB_PORT','DB_NAME','DB_USER','DB_PASSWORD','DB_PREFIX','DB_TABLE_LAYOUT','CMS_DB_PREFIX','ADMIN_DB_PREFIX','WEBMAN_LISTEN','WEBMAN_WORKERS']);
        $overrides = array_intersect_key(Environment::processEnvironment(), $keys);
        ksort($overrides);
        $yaml=\app\cms\config\DeploymentConfig::discoverFile();
        $files=['yaml'=>is_file($yaml)?hash_file('sha256',$yaml):null];
        return hash('sha256', serialize([$envFile, $files, $overrides]));
    }
    private function effectiveFingerprint(array $settings): string
    {
        ksort($settings);
        return hash('sha256', serialize($settings));
    }
    private function runtimeListen(): string
    {
        // The loaded process configuration is the address of the running worker,
        // including a deployment's PHP default when no environment file exists.
        $listen = \config('process.webman.listen');
        if (!is_string($listen) || $listen === '') throw new RuntimeException('无法读取服务监听配置');
        return $listen;
    }
    private function probeUrl(string $listen): string
    {
        $listen=\app\cms\config\DeploymentConfig::listener($listen);
        $url=parse_url($listen);
        if (!$url || ($url['scheme']??'')!=='http' || empty($url['port'])) throw new RuntimeException('自动检查暂不支持当前监听方式，请重新检查服务');
        $host=$url['host']; if (in_array($host,['0.0.0.0','localhost'],true))$host='127.0.0.1'; if(in_array($host,['::','[::]'],true))$host='[::1]';
        if(!in_array($host,['127.0.0.1','[::1]'],true)) throw new RuntimeException('自动检查需要本机 HTTP 监听地址');
        return 'http://'.$host.':'.$url['port'].'/install/handoff/status';
    }
    private function logFile(): string
    {
        foreach (['runtime','runtime/logs'] as $part) { $path=\app\cms\Paths::logical($part);if(is_link($path))throw new RuntimeException('重启日志路径无效');if(!is_dir($path)&&!mkdir($path,0755,true)&&!is_dir($path))throw new RuntimeException('无法创建重启日志'); }
        $file=\app\cms\Paths::runtime('logs/installer-restart.log');if(is_link($file))throw new RuntimeException('重启日志文件无效');return $file;
    }
    private function read(): ?array
    {
        $file=\app\cms\Paths::config('cms-install-handoff.json');if(is_link($file))throw new RuntimeException('安装交接记录路径无效');if(!is_file($file))return null;
        $record=json_decode((string)file_get_contents($file),true,512,JSON_THROW_ON_ERROR);
        if(!is_array($record)||!in_array($record['version']??null,[1,2],true))throw new RuntimeException('安装交接记录损坏');return $record;
    }
    private function write(array $record): void
    {
        $file=\app\cms\Paths::config('cms-install-handoff.json');$tmp=tempnam(\app\cms\Paths::config(''),'.handoff-');
        try{if(!$tmp||!chmod($tmp,0600))throw new RuntimeException('无法保存安装交接记录');$bytes=json_encode($record,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
            if(file_put_contents($tmp,$bytes,LOCK_EX)!==strlen($bytes)||!rename($tmp,$file))throw new RuntimeException('无法保存安装交接记录');
        }finally{if($tmp&&is_file($tmp))unlink($tmp);}
    }
    private function locked(callable $operation): mixed
    {
        $file=\app\cms\Paths::config('cms-install-handoff.lock');if(is_link(\app\cms\Paths::config(''))||is_link($file))throw new RuntimeException('安装交接互斥路径无效');
        $handle=fopen($file,'c+b');if(!$handle||!chmod($file,0600)||!flock($handle,LOCK_EX))throw new RuntimeException('无法锁定安装交接任务');
        try{$record=$this->read();$before=$record;$result=$operation($record);if($record!==null&&$record!==$before)$this->write($record);return $result;}
        finally{flock($handle,LOCK_UN);fclose($handle);}
    }
}
