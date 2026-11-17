<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\extension\{AttachmentStorage, Registry};
use app\cms\persistence\StateStore;
use InvalidArgumentException;

/** Site-local, revision guarded state. It never installs or evaluates PHP. */
final class PluginMarketService
{
    private StateStore $store;

    public function __construct(?StateStore $store = null)
    {
        $this->store=$store ?? new StateStore('extensions:v1');
    }

    public function select(string $query = '', string $status = ''): array
    {
        if (mb_strlen($query)>100 || !in_array($status,['','available','disabled','enabled'],true)) throw new InvalidArgumentException('查询条件无效');
        $result=[];
        foreach (Registry::catalogue() as $manifest) {
            if ($query!=='' && mb_stripos($manifest['name'].' '.$manifest['code'],$query)===false) continue;
            $item=$this->detail($manifest['code']);
            if ($status!=='' && $status!==$item['status']) continue;
            $result[]=$item;
        }
        return ['items'=>$result,'count'=>count($result)];
    }

    public function detail(string $code): array
    {
        $plugin=Registry::get($code);$state=$this->state($code);
        return $plugin->manifest()+['installed'=>$state['installed'],'enabled'=>$state['enabled'],
            'status'=>!$state['installed']?'available':($state['enabled']?'enabled':'disabled'),
            'revision'=>$state['revision'],'legacy_imported'=>$state['legacy_imported'],
            'config'=>$plugin->publicConfig($state['config'])];
    }

    public function change(string $code, string $action, int $revision, array $input = []): array
    {
        $plugin=Registry::get($code);
        return $this->store->locked(function () use ($plugin,$code,$action,$revision,$input): array {
            $state=$this->state($code);
            if ($revision!==$state['revision']) throw new InvalidArgumentException('插件已被其他操作修改，请刷新后重试');
            if ($action==='add') {
                if ($state['installed']) throw new InvalidArgumentException('插件已添加');
                $state['installed']=true;$state['enabled']=false;$state['config']=$plugin->defaults();$state['legacy_imported']=false;
            } else {
                if (!$state['installed']) throw new InvalidArgumentException('请先添加插件');
                switch ($action) {
                    case 'save':
                        $state['config']=$plugin->configure($input,$state['config']);
                        if ($state['enabled']) $plugin->assertReady($state['config']);
                        break;
                    case 'enable':
                        $plugin->assertReady($state['config']);
                        foreach (Registry::catalogue() as $other) if ($other['code']!==$code && array_intersect($other['capabilities'],$plugin->manifest()['capabilities']) && $this->state($other['code'])['enabled']) throw new InvalidArgumentException('同一能力已有启用插件，请先禁用');
                        $state['enabled']=true;break;
                    case 'disable': $plugin->disable($state['config']);$state['enabled']=false;break;
                    case 'delete':
                        if ($state['enabled']) throw new InvalidArgumentException('请先禁用插件，再删除配置');
                        $plugin->remove($state['config']);$state['installed']=false;$state['config']=[];$state['legacy_imported']=false;break;
                    default: throw new InvalidArgumentException('不支持的插件操作');
                }
            }
            $state['revision']++;$state['updated_at']=time();$this->store->write($code,$state);
            return $this->detail($code);
        });
    }

    public function legacyPreview(string $code): array
    {
        if ($code!=='AliyunOss') throw new InvalidArgumentException('此插件不支持旧配置导入');
        $legacy=$this->legacy();
        if ($legacy===null) return ['available'=>false,'message'=>'当前数据库没有旧 OSS 配置'];
        return ['available'=>true,'config'=>Registry::get($code)->publicConfig($legacy['config']),
            'source_digest'=>$legacy['digest'],'message'=>'导入后保持禁用并保留本地副本，不连接 OSS'];
    }

    public function legacyImport(string $code, int $revision, string $digest): array
    {
        if ($code!=='AliyunOss') throw new InvalidArgumentException('此插件不支持旧配置导入');
        return $this->store->locked(function () use ($code,$revision,$digest): array {
            $state=$this->state($code);
            if ($state['revision']!==$revision) throw new InvalidArgumentException('插件已被修改，请刷新后重试');
            if (!$state['installed'] || $state['enabled']) throw new InvalidArgumentException('请先添加插件并保持禁用，再导入旧配置');
            if ($state['legacy_imported']) throw new InvalidArgumentException('旧配置已经导入，请直接编辑插件配置');
            $legacy=$this->legacy();
            if (!$legacy || !preg_match('/^[a-f0-9]{64}$/D',$digest) || !hash_equals($legacy['digest'],$digest)) throw new InvalidArgumentException('旧配置已变化，请重新预览');
            $state['config']=$legacy['config'];$state['legacy_imported']=true;$state['revision']++;$state['updated_at']=time();
            $this->store->write($code,$state);return $this->detail($code);
        });
    }

    /** The only internal upload claim; never return it from a controller. */
    public function storageClaim(): ?array
    {
        return $this->store->locked(function (): ?array {
            foreach (Registry::catalogue() as $manifest) {
                if (!in_array('attachment_storage',$manifest['capabilities'],true)) continue;
                $state=$this->state($manifest['code']);
                if ($state['installed'] && $state['enabled'] && Registry::get($manifest['code']) instanceof AttachmentStorage) return ['code'=>$manifest['code'],'revision'=>$state['revision'],'config'=>$state['config']];
            }
            return null;
        });
    }

    public function assertClaim(array $claim): void
    {
        $this->store->locked(function () use ($claim): void {
            $state=$this->state($claim['code']);
            if (!$state['installed'] || !$state['enabled'] || $state['revision']!==$claim['revision']) throw new InvalidArgumentException('存储插件已修改或禁用，请重新上传；本地文件已保留');
        });
    }

    public function recordFailure(string $id, array $metadata): void
    {
        // Only code-owned metadata is accepted here, never config or exception messages.
        $allowed=['provider','object','bucket','endpoint','revision','sha256','local_path','stage','created_at'];
        $this->store->write('upload-failure:'.$id,array_intersect_key($metadata,array_flip($allowed)));
    }

    private function state(string $code): array
    {
        $plugin=Registry::get($code);
        $state=$this->store->read($code,['format'=>1,'revision'=>0,'installed'=>false,'enabled'=>false,'config'=>$plugin->defaults(),'legacy_imported'=>false]);
        if (($state['format']??null)!==1 || !is_int($state['revision']??null) || !is_bool($state['installed']??null) || !is_bool($state['enabled']??null) || !is_array($state['config']??null) || !is_bool($state['legacy_imported']??null)) throw new \RuntimeException('插件状态损坏，请检查私有数据库记录');
        return $state;
    }

    private function legacy(): ?array
    {
        $pdo=$this->store->connection();$table=$this->store->prefix().'weapp';
        $q=$pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=?');$q->execute([$table]);
        if ((int)$q->fetchColumn()===0) return null;
        // PDO deliberately bypasses both ORM SQL debug listeners.
        $q=$pdo->prepare('SELECT data FROM `'.$table.'` WHERE code=?');$q->execute(['AliyunOss']);$rows=$q->fetchAll(\PDO::FETCH_COLUMN);
        if (!$rows) return null;
        if (count($rows)!==1) throw new InvalidArgumentException('旧 OSS 配置重复，请先核对');
        try { $data=json_decode($rows[0],true,32,JSON_THROW_ON_ERROR); }
        catch (\Throwable) { throw new InvalidArgumentException('旧 OSS 配置损坏，无法导入'); }
        if (!is_array($data) || array_is_list($data)) throw new InvalidArgumentException('旧 OSS 配置格式无效');
        $input=array_intersect_key($data,array_flip(['key_id','key_secret','bucket','endpoint','domain']));
        $input['protocol']=match ((string)($data['tcp']??'2')) {'1'=>'http','3'=>'relative',default=>'https'};
        $input['keep_local']=true;$config=Registry::get('AliyunOss')->configure($input,[]);
        return ['config'=>$config,'digest'=>hash('sha256',$this->store->prefix().':'.$rows[0])];
    }
}
