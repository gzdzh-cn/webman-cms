<?php
declare(strict_types=1);
namespace app\cms\maintenance;

use app\cms\persistence\Connection;
use app\cms\persistence\Schema;
use app\cms\persistence\StateStore;
use PDO;

/** One-way, auditable import. Sources are never deleted or executed. */
final class StateMigration
{
    private PDO $pdo;
    private string $prefix;
    public function __construct(?PDO $pdo = null, ?string $prefix = null)
    {
        $this->pdo = $pdo ?? Connection::open();
        $this->prefix = $prefix ?? Connection::prefix();
        Schema::prefix($this->prefix);
    }

    public function run(?string $legacyRuntime = null, bool $apply = false): array
    {
        $root = rtrim($legacyRuntime ?? runtime_path(), '/');
        $this->safeDirectory($root);
        $schemaExists = Schema::exists($this->pdo, $this->prefix);
        $inventory = $this->inventory($root);
        $audit = $schemaExists ? new StateStore('migration:runtime-v1', $this->pdo, $this->prefix) : null;
        $pending = []; $skipped = 0;
        foreach ($inventory as $item) {
            $key = hash('sha256', $item['source']);
            $old = $audit?->read($key);
            if ($old) {
                if (($old['source_sha256'] ?? '') !== $item['source_sha256']) throw new \RuntimeException('已迁移来源发生变化，拒绝覆盖数据库：' . $item['source']);
                $skipped++; continue;
            }
            $this->assertTarget($item, $schemaExists);
            $pending[] = $item;
        }
        $report = ['version'=>1, 'dry_run'=>!$apply, 'source_files'=>count($inventory), 'pending'=>count($pending), 'already_imported'=>$skipped, 'objects'=>0, 'records'=>0, 'ui_entries'=>0, 'bytes'=>0, 'ui_conflicts'=>[]];
        foreach ($pending as $item) {
            $report['bytes'] += $item['bytes'];
            if ($item['kind'] === 'object') $report['objects']++;
            elseif ($item['kind'] === 'ui') {
                $report['ui_entries'] += count($item['entries']);
                foreach ($item['entries'] as $row) {
                    $previous = $this->uiRow($row);
                    if ($previous && ($previous['value'] !== $row['value'] || $previous['type'] !== $row['type'] || $previous['idcode'] !== $row['idcode'])) $report['ui_conflicts'][] = ['theme_style'=>$row['theme_style'],'page'=>$row['page'],'lang'=>$row['lang'],'name'=>$row['name'],'resolution'=>'legacy_file_wins'];
                }
            } else $report['records']++;
        }
        $markers = new \app\cms\persistence\LegacyMarkerStore($this->pdo, $this->prefix);
        $report['legacy_markers'] = $markers->importDirectory(\app\cms\Paths::legacyData('conf'), false);
        if (!$apply) return $report;
        Schema::install($this->pdo, $this->prefix);
        $audit = new StateStore('migration:runtime-v1', $this->pdo, $this->prefix);
        return $audit->locked(function () use ($audit, $pending, $report, $root): array {
            // Recheck source bytes and target rows after acquiring the migration lock.
            foreach ($pending as $item) {
                if (!is_file($root . '/' . $item['source']) || is_link($root . '/' . $item['source']) || hash_file('sha256', $root . '/' . $item['source']) !== $item['source_sha256']) throw new \RuntimeException('迁移来源在检查后变化：' . $item['source']);
                $this->assertTarget($item, true);
            }
            return $audit->transaction(function () use ($audit, $pending, $report): array {
                foreach ($pending as $item) {
                    $key = hash('sha256', $item['source']);
                    $old = $audit->read($key);
                    if ($old) {
                        if ($old['source_sha256'] !== $item['source_sha256']) throw new \RuntimeException('迁移来源冲突');
                        continue;
                    }
                    if ($item['kind'] === 'ui') {
                        $before = [];
                        foreach ($item['entries'] as $row) { $previous = $this->uiRow($row); if ($previous) $before[] = $previous; $this->writeUi($row); }
                        (new StateStore('backups:ui-migration', $this->pdo, $this->prefix))->write($key, ['source'=>$item['source'],'previous_rows'=>$before,'source_values'=>$item['entries']]);
                    } else {
                        $store = new StateStore($item['scope'], $this->pdo, $this->prefix);
                        if ($item['kind'] === 'object') {
                            $entry = $store->putObject($item['payload'], $item['extension']);
                            if ($entry['object'] !== $item['name'] || $entry['sha256'] !== $item['source_sha256']) throw new \RuntimeException('内容快照迁移校验失败');
                        } else $store->write($item['name'], $item['payload']);
                    }
                    $audit->write($key, ['source'=>$item['source'],'source_sha256'=>$item['source_sha256'],'bytes'=>$item['bytes'],'kind'=>$item['kind'],'scope'=>$item['scope']??'ui_config','name'=>$item['name']??'','imported_at'=>time()]);
                }
                $report['legacy_markers'] = (new \app\cms\persistence\LegacyMarkerStore($this->pdo, $this->prefix))->importDirectory(\app\cms\Paths::legacyData('conf'), true);
                $audit->write('complete', ['version'=>1,'completed_at'=>time(),'source_files'=>$report['source_files']]);
                return $report + ['applied'=>true];
            });
        });
    }

    private function inventory(string $root): array
    {
        $items = [];
        foreach (['static','sitemap','public-static'] as $scope) {
            $directory = $root . '/cms/generated/' . $scope;
            if (!is_dir($directory)) continue;
            $this->safeDirectory($directory);
            $records = []; $objects = [];
            foreach (new \DirectoryIterator($directory) as $file) {
                if ($file->isDot()) continue;
                $name = $file->getFilename();
                if ($file->isLink()) throw new \RuntimeException('迁移来源不能是链接');
                if ($name === '.DS_Store' && $file->isFile()) continue;
                if ($name === 'compiled' && $file->isDir()) continue;
                if ($name === 'store.lock' && $file->isFile()) continue;
                if ($name === 'objects' && $file->isDir()) {
                    foreach (new \DirectoryIterator($file->getPathname()) as $object) {
                        if ($object->isDot()) continue;
                        if ($object->getFilename() === '.DS_Store' && $object->isFile() && !$object->isLink()) continue;
                        if (!$object->isFile() || $object->isLink() || !preg_match('/^([a-f0-9]{64})\.(html|xml|txt)$/D', $object->getFilename(), $match)) throw new \RuntimeException('生成快照目录含未知文件');
                        $item = $this->source($root, $object->getPathname());
                        if ($item['source_sha256'] !== $match[1]) throw new \RuntimeException('生成快照SHA不匹配：' . $item['source']);
                        $item += ['kind'=>'object','scope'=>'generated:'.$scope,'name'=>$object->getFilename(),'extension'=>$match[2]];
                        $objects[$item['name']] = $item; $items[] = $item;
                    }
                    continue;
                }
                if (!$file->isFile() || !preg_match('/^(?:manifest|job-[a-f0-9]{32})\.json$/D', $name)) throw new \RuntimeException('生成状态目录含未知文件：'.$name);
                $item = $this->source($root, $file->getPathname()); $item['payload'] = $this->json($item['payload'], $item['source']);
                $item += ['kind'=>'record','scope'=>'generated:'.$scope,'name'=>$name];
                $records[$name] = $item['payload']; $items[] = $item;
            }
            $manifest = $records['manifest.json'] ?? [];
            foreach (array_merge(array_values($manifest['objects']??[]), array_values($manifest['entries']??[])) as $entry) {
                if (!is_array($entry) || !isset($entry['object'], $entry['sha256']) || !isset($objects[$entry['object']]) || $objects[$entry['object']]['source_sha256'] !== $entry['sha256']) throw new \RuntimeException('生成清单引用缺失或损坏的快照：'.$scope);
            }
            foreach ($manifest['jobs'] ?? [] as $id) if (!preg_match('/^[a-f0-9]{32}$/D', (string)$id) || !isset($records['job-'.$id.'.json'])) throw new \RuntimeException('生成清单引用缺失的任务：'.$scope);
            if ($scope === 'public-static') foreach ($manifest['files'] ?? [] as $file => $entry) {
                if (!preg_match('#^html/(?!.*(?:^|/)\.\.?/)[A-Za-z0-9_./%+-]+\.html?$#D', $file) || !is_array($entry) || !preg_match('/^[a-f0-9]{64}$/D', (string)($entry['sha256']??''))) throw new \RuntimeException('公开生成记录路径或hash无效，须先处理旧保存目录');
            }
        }
        $uiRoot = $root . '/cms/ui';
        foreach ($this->files($uiRoot) as $path) {
            if (!str_ends_with($path, '.inc.php')) throw new \RuntimeException('UI来源包含未知文件');
            $item = $this->source($root, $path); $relative = substr($path, strlen($uiRoot)+1);
            $style = str_replace('\\','/',dirname($relative)); $page = substr(basename($relative), 0, -8);
            if (!preg_match('#^[A-Za-z0-9_-]+(?:/[A-Za-z0-9_-]+)*$#D', $style) || !preg_match('/^[A-Za-z0-9_-]{1,64}$/D', $page)) throw new \RuntimeException('UI来源范围无效');
            $lines = explode("\n", $item['payload']);
            if (!isset($lines[1]) || !preg_match('/^<\?php\s+die\(/', $lines[0])) throw new \RuntimeException('UI来源不是已知的非执行JSON格式');
            $entries = $this->json($lines[1], $item['source']); $normalized = [];
            foreach ($entries as $key => $value) {
                if (!is_string($value) || strlen($value) > 65000) throw new \RuntimeException('UI来源值无效或超过数据库安全长度');
                $payload = $this->json($value, $item['source']);
                foreach (['id','type','page','lang','idcode'] as $field) if (!isset($payload[$field]) || !is_string($payload[$field])) throw new \RuntimeException('UI来源缺少范围字段：'.$field);
                if (!preg_match('/^[A-Za-z0-9_-]{1,50}$/D', $payload['id']) || !preg_match('/^[A-Za-z0-9_-]{2,50}$/D', $payload['lang']) || !preg_match('/^[A-Za-z0-9_-]{0,50}$/D', $payload['idcode']) || $payload['page'] !== $page || !in_array($payload['type'], \app\cms\service\UiConfigService::TYPES, true)) throw new \RuntimeException('UI来源字段或页面范围无效');
                $qualified = $payload['lang'].'_'.$payload['type'].'_'.$payload['id']; $unqualified = $payload['type'].'_'.$payload['id'];
                if ($key !== $qualified && $key !== $unqualified) throw new \RuntimeException('UI来源键不匹配');
                $language = $this->pdo->prepare('SELECT `id` FROM `'.$this->prefix.'language` WHERE `mark`=?');$language->execute([$payload['lang']]);
                if (!$language->fetchColumn()) throw new \RuntimeException('UI来源语言不存在，拒绝丢弃');
                $identity = $payload['lang'].'\0'.$payload['id'];
                $row = ['name'=>$payload['id'],'page'=>$page,'theme_style'=>$style,'lang'=>$payload['lang'],'type'=>$payload['type'],'idcode'=>$payload['idcode'],'value'=>$value];
                if (isset($normalized[$identity]) && $normalized[$identity]['type'] !== $row['type']) throw new \RuntimeException('UI来源同一标识存在多个类型，拒绝丢弃任一值');
                if (!isset($normalized[$identity]) || $key === $qualified) $normalized[$identity]=$row;
            }
            $item += ['kind'=>'ui','entries'=>array_values($normalized)];unset($item['payload']);$items[]=$item;
        }
        foreach ($this->files($root.'/backups') as $path) {
            $item=$this->source($root,$path);$item['payload']=$this->json($item['payload'],$item['source']);
            $relative=substr($path,strlen($root.'/backups/'));$directory=dirname($relative);
            $item+=['kind'=>'record','scope'=>'backups:'.($directory==='.'?'legacy':str_replace('/','.', $directory)),'name'=>basename($path)];$items[]=$item;
        }
        foreach ($this->files($root.'/login') as $path) {
            if(!preg_match('/^[a-f0-9]{32}\.limit$/D',basename($path)))throw new \RuntimeException('登录限流来源文件无效');
            $item=$this->source($root,$path);$item['payload']=$this->json($item['payload'],$item['source']);
            if(!isset($item['payload']['username'],$item['payload']['count'],$item['payload']['time'])||md5((string)$item['payload']['username']).'.limit'!==basename($path))throw new \RuntimeException('登录限流来源内容无效');
            $item+=['kind'=>'record','scope'=>'security:login','name'=>basename($path)];$items[]=$item;
        }
        return $items;
    }

    private function assertTarget(array $item, bool $schemaExists): void
    {
        if (!$schemaExists || $item['kind']==='ui') return;
        $store=new StateStore($item['scope'],$this->pdo,$this->prefix);
        if($item['kind']==='object'){
            $query=$this->pdo->prepare('SELECT o.`payload` FROM `'.$this->prefix.'cms_state_object_refs` r JOIN `'.$this->prefix.'cms_state_objects` o ON r.`sha256`=o.`sha256` WHERE r.`scope`=? AND r.`name`=?');$query->execute([$item['scope'],$item['name']]);$old=$query->fetchColumn();
            if($old!==false&&hash('sha256',$old)!==$item['source_sha256'])throw new \RuntimeException('数据库已有不同内容快照');
        }elseif($store->has($item['name'])&&$store->read($item['name'])!==$item['payload'])throw new \RuntimeException('数据库已有不同状态，拒绝覆盖：'.$item['scope'].'/'.$item['name']);
    }
    private function uiRow(array $row): ?array
    {
        $query=$this->pdo->prepare('SELECT * FROM `'.$this->prefix.'ui_config` WHERE `name`=? AND `page`=? AND `theme_style`=? AND `lang`=?');$query->execute([$row['name'],$row['page'],$row['theme_style'],$row['lang']]);return $query->fetch(PDO::FETCH_ASSOC)?:null;
    }
    private function writeUi(array $row): void
    {
        $previous=$this->uiRow($row);$row['md5key']=md5($row['name'].$row['page'].$row['theme_style'].$row['lang']);$row['update_time']=time();
        if($previous){$id=$previous['id'];$columns=array_keys($row);$query=$this->pdo->prepare('UPDATE `'.$this->prefix.'ui_config` SET '.implode(',',array_map(static fn($k)=>'`'.$k.'`=?',$columns)).' WHERE `id`=?');$query->execute([...array_values($row),$id]);}
        else{$row['add_time']=time();$query=$this->pdo->prepare('INSERT INTO `'.$this->prefix.'ui_config` (`'.implode('`,`',array_keys($row)).'`) VALUES ('.implode(',',array_fill(0,count($row),'?')).')');$query->execute(array_values($row));}
        $saved=$this->uiRow($row);foreach(['name','page','theme_style','lang','type','idcode','value','md5key']as$key)if(!isset($saved[$key])||(string)$saved[$key]!==$row[$key])throw new \RuntimeException('UI配置写入后校验失败');
    }
    private function source(string $root,string $path): array
    {
        if(is_link($path)||!is_file($path))throw new \RuntimeException('迁移来源文件无效');$bytes=file_get_contents($path);if(!is_string($bytes))throw new \RuntimeException('无法读取迁移来源');
        return ['source'=>substr($path,strlen($root)+1),'source_sha256'=>hash('sha256',$bytes),'bytes'=>strlen($bytes),'payload'=>$bytes];
    }
    private function json(string $bytes,string $source): array
    {
        try{$value=json_decode($bytes,true,512,JSON_THROW_ON_ERROR);}catch(\JsonException $error){throw new \RuntimeException('迁移来源JSON损坏：'.$source,0,$error);}if(!is_array($value))throw new \RuntimeException('迁移来源格式无效：'.$source);return $value;
    }
    private function files(string $directory): array
    {
        if(!file_exists($directory))return [];$this->safeDirectory($directory);$files=[];
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory,\FilesystemIterator::SKIP_DOTS),\RecursiveIteratorIterator::SELF_FIRST)as$file){
            if($file->isLink())throw new \RuntimeException('迁移来源不能包含链接');
            // Only Finder's ordinary metadata file is disposable; unknown data stays strict.
            if($file->getFilename()==='.DS_Store' && $file->isFile())continue;
            if($file->isFile())$files[]=$file->getPathname();elseif(!$file->isDir())throw new \RuntimeException('迁移来源包含特殊文件');
        }
        sort($files);return $files;
    }
    private function safeDirectory(string $directory): void
    {
        if(!str_starts_with($directory,'/')||str_contains($directory,"\0"))throw new \RuntimeException('迁移来源必须为绝对目录');$current='';foreach(explode('/',trim($directory,'/'))as$part){if($part===''||$part==='.'||$part==='..')throw new \RuntimeException('迁移来源路径无效');$current.='/'.$part;if(is_link($current)||(file_exists($current)&&!is_dir($current)))throw new \RuntimeException('迁移来源包含链接或非目录');}if(!is_dir($directory))throw new \RuntimeException('迁移来源目录不存在');
    }
}
