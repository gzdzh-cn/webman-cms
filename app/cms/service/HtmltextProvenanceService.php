<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Encoding is provenance, never a property inferred from HTML-looking bytes. */
final class HtmltextProvenanceService
{
    public const SCOPE = 'htmltext-provenance-v1';
    private LanguageStateStore $store;
    public function __construct(private ?CmsRepository $repository = null)
    {
        $this->repository ??= new CmsRepository();
        $this->store = new LanguageStateStore(self::SCOPE, $this->repository);
    }

    private function identity(string $table, string $primaryKey, int $id, string $field): array
    {
        $this->repository->identifier($table); $this->repository->identifier($primaryKey); $this->repository->identifier($field);
        if ($id <= 0) throw new InvalidArgumentException('富文本记录身份无效');
        return ['table'=>$this->repository->fullName($table),'primary_key'=>$primaryKey,'id'=>$id,'field'=>$field];
    }
    private function name(array $identity): string { return hash('sha256', json_encode($identity, JSON_THROW_ON_ERROR)); }
    public static function decode(string $bytes): string { return htmlspecialchars_decode($bytes, ENT_COMPAT); }

    /** Installer-only: use its target PDO/transaction, never the still-running site's ORM. */
    public static function markInstalled(\PDO $pdo, string $cmsPrefix, string $strategy): array
    {
        if (!$pdo->inTransaction()) throw new InvalidArgumentException('安装富文本来源必须在数据库收尾事务内写入');
        if (!in_array($strategy,['source_full','builtin_demo','source_schema','empty'],true)) throw new InvalidArgumentException('安装数据来源策略无效');
        $identifier=static function(string $value):string {
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D',$value)) throw new InvalidArgumentException('安装富文本表名或字段名无效');
            return '`'.$value.'`';
        };
        $tableName=static fn(string $name):string=>$identifier($cmsPrefix.$name);
        $source=$strategy==='source_full'?'encoded':'raw';
        $columns=static function(string $table)use($pdo,$tableName):array {
            $rows=$pdo->query('SHOW FULL COLUMNS FROM '.$tableName($table))->fetchAll(\PDO::FETCH_ASSOC);
            return array_column($rows,null,'Field');
        };
        $models=$pdo->query('SELECT `id`,`table` FROM '.$tableName('channeltype'))->fetchAll(\PDO::FETCH_ASSOC);
        $existingTables=$pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
        $modelTables=[];
        foreach ($models as $model) {
            $identifier((string)$model['table']);
            // Guestbook/ask metadata is not an archives addon and has no *_content table.
            if (in_array($cmsPrefix.$model['table'].'_content',$existingTables,true)) $modelTables[(int)$model['id']]=$model['table'].'_content';
        }
        $fieldRows=$pdo->query("SELECT `channel_id`,`name`,`ifmain` FROM ".$tableName('channelfield')." WHERE `dtype`='htmltext'")->fetchAll(\PDO::FETCH_ASSOC);
        $fields=[];
        foreach ($fieldRows as $field) {
            $identifier((string)$field['name']); $channel=(int)$field['channel_id'];
            $table=$channel===-99?'arctype':(!empty($field['ifmain'])?'archives':($modelTables[$channel]??null));
            if ($table!==null) $fields[$table][]=$field['name'];
        }
        // Core body fields remain supported even where old channelfield metadata omits them.
        foreach (array_unique(array_values($modelTables)) as $table) {
            $schema=$columns($table);
            foreach (['content','content_ey_m'] as $field) if (isset($schema[$field])) $fields[$table][]=$field;
        }
        $state=$tableName('cms_state_records');
        $read=$pdo->prepare('SELECT `payload`,`sha256` FROM '.$state.' WHERE `scope`=? AND `name`=? FOR UPDATE');
        $write=$pdo->prepare('INSERT INTO '.$state.' (`scope`,`name`,`payload`,`sha256`,`updated_at`) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE `payload`=VALUES(`payload`),`sha256`=VALUES(`sha256`),`updated_at`=VALUES(`updated_at`)');
        $counts=['source'=>$source,'fields'=>0,'records'=>0,'written'=>0];
        foreach ($fields as $table=>$names) {
            $schema=$columns($table); $pk=$table==='arctype'?'id':'aid';
            if (!isset($schema[$pk])) throw new InvalidArgumentException('安装富文本表缺少主身份：'.$table);
            $names=array_values(array_filter(array_unique($names),static fn($name)=>isset($schema[$name])));
            if (!$names) continue;
            $counts['fields']+=count($names); $last=0;
            do {
                $select=$pdo->prepare('SELECT '.$identifier($pk).','.implode(',',array_map($identifier,$names)).' FROM '.$tableName($table).' WHERE '.$identifier($pk).'>? ORDER BY '.$identifier($pk).' ASC LIMIT 250');
                $select->execute([$last]); $rows=$select->fetchAll(\PDO::FETCH_ASSOC); $select->closeCursor();
                foreach ($rows as $row) {
                    $id=(int)$row[$pk];
                    if ($id<=$last) throw new InvalidArgumentException('安装富文本身份重复或无效：'.$table);
                    $last=$id; $counts['records']++;
                    foreach ($names as $field) {
                        $identity=['table'=>$cmsPrefix.$table,'primary_key'=>$pk,'id'=>$id,'field'=>$field];
                        $name=hash('sha256',json_encode($identity,JSON_THROW_ON_ERROR)); $sha=hash('sha256',(string)$row[$field]);
                        $read->execute([self::SCOPE,$name]); $stored=$read->fetch(\PDO::FETCH_ASSOC); $read->closeCursor(); $old=[];
                        if ($stored) {
                            if (!hash_equals($stored['sha256'],hash('sha256',$stored['payload']))) throw new InvalidArgumentException('安装富文本来源记录校验失败');
                            $old=json_decode($stored['payload'],true,32,JSON_THROW_ON_ERROR);
                            if (!is_array($old)) throw new InvalidArgumentException('安装富文本来源记录格式无效');
                        }
                        if ($old && array_intersect_key($old,$identity)===$identity && ($old['source']??null)===$source && ($old['content_sha256']??null)===$sha) continue;
                        $now=time(); $record=$identity+['version'=>1,'source'=>$source,'content_sha256'=>$sha,'revision'=>(int)($old['revision']??0)+1,'updated_at'=>$now];
                        $payload=json_encode($record,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
                        $write->execute([self::SCOPE,$name,$payload,hash('sha256',$payload),$now]); $counts['written']++;
                    }
                }
            } while (count($rows)===250);
        }
        return $counts;
    }

    /** Raw fields in the old response are unchanged; this is optional additive metadata. */
    public function describe(string $table, string $primaryKey, int $id, string $field, string $bytes): array
    {
        $identity = $this->identity($table,$primaryKey,$id,$field);
        $record = $this->store->read($this->name($identity));
        $sha = hash('sha256',$bytes); $source = 'unknown';
        if ($record && array_intersect_key($record,$identity) === $identity && ($record['version']??0) === 1
            && in_array($record['source']??null,['raw','encoded','unknown'],true)
            && is_string($record['content_sha256']??null) && hash_equals($record['content_sha256'],$sha)) $source = $record['source'];
        return ['source'=>$source,'sha256'=>$sha,'revision'=>(int)($record['revision']??0),
            'editor_value'=>$source==='encoded'?self::decode($bytes):$bytes,
            'raw_preview'=>$bytes,'encoded_preview'=>self::decode($bytes)];
    }

    /** Must run within the writer's existing transaction and row lock. */
    public function markTrusted(string $table, string $primaryKey, int $id, string $field, string $bytes, string $source): void
    {
        if (!in_array($source,['raw','encoded','unknown'],true)) throw new InvalidArgumentException('富文本来源无效');
        $identity = $this->identity($table,$primaryKey,$id,$field); $name = $this->name($identity);
        $old = $this->store->read($name);
        $sha = hash('sha256',$bytes);
        if ($old && array_intersect_key($old,$identity)===$identity && ($old['source']??null)===$source && ($old['content_sha256']??null)===$sha) return;
        $this->store->write($name,$identity+['version'=>1,'source'=>$source,'content_sha256'=>$sha,'revision'=>(int)($old['revision']??0)+1,'updated_at'=>time()]);
    }

    public function metadata(string $table, int $id, array $values, array $fields, string $primaryKey = 'aid'): array
    {
        $result=[];
        foreach (array_unique($fields) as $field) if (array_key_exists($field,$values) && is_scalar($values[$field])) {
            $result[$field]=$this->describe($table,$primaryKey,$id,$field,(string)$values[$field]);
        }
        return $result;
    }

    /** Metadata is a concurrency contract, not authority to mark arbitrary old content. */
    public static function inputMetadata(array $input): array
    {
        $value=$input['__htmltext']??[];
        if (is_string($value)) {
            if (strlen($value)>65536) throw new InvalidArgumentException('富文本来源参数过大');
            try { $value=json_decode($value,true,32,JSON_THROW_ON_ERROR); }
            catch (\JsonException $error) { throw new InvalidArgumentException('富文本来源参数格式无效',0,$error); }
        }
        if (!is_array($value)) throw new InvalidArgumentException('富文本来源参数格式无效');
        return $value;
    }

    /** Returns a source for the submitted bytes; omission is handled by the caller. */
    public function sourceForWrite(string $table, string $primaryKey, int $id, string $field, string $before, string $after, ?array $submission): string
    {
        $current=$this->describe($table,$primaryKey,$id,$field,$before);
        if ($submission===null) {
            // Legacy APIs can keep old bytes. A changed, unlabelled payload is unknown.
            return hash_equals($before,$after)?$current['source']:'unknown';
        }
        if (($submission['format']??null)!=='raw' || !is_string($submission['sha256']??null)
            || !hash_equals($current['sha256'],$submission['sha256']) || !is_int($submission['revision']??null)
            || $submission['revision']!==$current['revision'] || ($submission['source']??null)!==$current['source']) {
            throw new InvalidArgumentException('富文本内容或来源已变更，请重新读取后再保存：'.$field);
        }
        if ($current['source']==='unknown' && (!in_array($submission['interpretation']??null,['raw','encoded'],true) || ($submission['confirmed']??false)!==true)) {
            throw new InvalidArgumentException('请先核对两种预览并确认原正文来源：'.$field);
        }
        return 'raw';
    }

    /** For content synchronization, after copying values inside its transaction. */
    public function copyRecord(string $sourceTable, int $sourceId, string $targetTable, int $targetId, array $fields, string $primaryKey='aid'): void
    {
        $source=$this->repository->table($sourceTable)->where($primaryKey,$sourceId)->find();
        $target=$this->repository->table($targetTable)->where($primaryKey,$targetId)->find();
        if (!$source || !$target) throw new InvalidArgumentException('富文本复制记录不存在');
        foreach ($fields as $field) if (array_key_exists($field,$source) && array_key_exists($field,$target)) {
            $bytes=(string)$target[$field];
            $origin=hash_equals((string)$source[$field],$bytes)?$this->describe($sourceTable,$primaryKey,$sourceId,$field,(string)$source[$field])['source']:'unknown';
            $this->markTrusted($targetTable,$primaryKey,$targetId,$field,$bytes,$origin);
        }
    }

    /** Remove only exact identities when a business record is actually purged. */
    public function forgetRecord(string $table, string $primaryKey, int $id, array $fields): void
    {
        foreach (array_unique($fields) as $field) $this->store->remove($this->name($this->identity($table,$primaryKey,$id,$field)));
    }

    /** Unknown records keep the previous public behavior; no silent migration. */
    public function render(string $table, string $primaryKey, int $id, string $field, string $bytes): string
    {
        return match($this->describe($table,$primaryKey,$id,$field,$bytes)['source']) {
            'raw'=>$bytes, 'encoded'=>self::decode($bytes), default=>htmlspecialchars_decode($bytes),
        };
    }
}
