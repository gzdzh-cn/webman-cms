<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;

/** Language metadata participates in the very same transaction as CMS rows. */
final class LanguageStateStore
{
    private CmsRepository $repository;
    public function __construct(private string $scope, ?CmsRepository $repository = null)
    {
        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9:_.-]{0,95}$/D', $scope)) throw new \InvalidArgumentException('语言状态范围无效');
        $this->repository = $repository ?? new CmsRepository();
    }
    public function read(string $name, array $default = []): array
    {
        $this->validate($name);
        $row = $this->repository->table('cms_state_records')->where(['scope'=>$this->scope,'name'=>$name])->find();
        if (!$row) return $default;
        if (!hash_equals((string)$row['sha256'], hash('sha256', (string)$row['payload']))) throw new \RuntimeException('语言状态校验失败');
        $value = json_decode((string)$row['payload'], true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($value)) throw new \RuntimeException('语言状态格式无效');
        return $value;
    }
    public function write(string $name, array $value): void
    {
        $this->validate($name);
        $payload = json_encode($value, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
        $table = $this->repository->fullName('cms_state_records');
        $this->repository->connection()->execute('INSERT INTO `'.$table.'` (`scope`,`name`,`payload`,`sha256`,`updated_at`) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE `payload`=VALUES(`payload`),`sha256`=VALUES(`sha256`),`updated_at`=VALUES(`updated_at`)', [$this->scope,$name,$payload,hash('sha256',$payload),time()]);
    }
    public function remove(string $name): void { $this->validate($name); $this->repository->table('cms_state_records')->where(['scope'=>$this->scope,'name'=>$name])->delete(); }
    public function names(): array { return $this->repository->table('cms_state_records')->where('scope',$this->scope)->order('name asc')->column('name'); }
    public function locked(callable $operation): mixed
    {
        $key = 'cms-language-'.substr(hash('sha256',(string)config('think-orm.connections.mysql.database').'|'.$this->repository->fullName('cms_state_records').'|'.$this->scope),0,48);
        $row = $this->repository->connection()->query('SELECT GET_LOCK(?,10) AS acquired',[$key]);
        if ((int)($row[0]['acquired']??0)!==1) throw new \RuntimeException('语言操作正在执行，请稍后重试');
        try { return $operation($this); }
        finally { $this->repository->connection()->query('SELECT RELEASE_LOCK(?)',[$key]); }
    }
    private function validate(string $name): void { if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_.:-]{0,190}$/D',$name)) throw new \InvalidArgumentException('语言状态名称无效'); }
}
