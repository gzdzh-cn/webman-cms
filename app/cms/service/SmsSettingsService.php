<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** SMS account settings stored with the original Eyou configuration names. */
class SmsSettingsService
{
    public const FIELDS = ['sms_type', 'sms_appkey', 'sms_secretkey', 'sms_appid_tx', 'sms_appkey_tx', 'sms_nation_code', 'sms_test_mobile'];
    public const SECRETS = ['sms_secretkey', 'sms_appkey_tx'];
    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }
    public function read(string $lang): array
    {
        $data = array_intersect_key((new SmsService($this->repository))->configuration($lang), array_flip(self::FIELDS));
        foreach (self::SECRETS as $key) { $data[$key . '_configured'] = !empty($data[$key]); $data[$key] = ''; }
        return $data + ['sms_type' => '1', 'sms_nation_code' => '86'];
    }
    public function save(array $input): void
    {
        $lang = trim((string)($input['lang'] ?? 'cn'));
        if (!$this->repository->table('language')->where('mark', $lang)->find()) throw new InvalidArgumentException('语言不存在');
        $type = (int)($input['sms_type'] ?? 1);
        if (!in_array($type, [1,2], true)) throw new InvalidArgumentException('不支持的短信平台');
        $country = trim((string)($input['sms_nation_code'] ?? '86'));
        if (!preg_match('/^[1-9][0-9]{0,3}$/D', $country)) throw new InvalidArgumentException('国家区号格式不正确');
        $data = array_intersect_key($input, array_flip(self::FIELDS));
        foreach ($data as $key => &$value) {
            if (!is_scalar($value) || strlen((string)$value) > 1000) throw new InvalidArgumentException('短信配置格式不正确');
            $value = trim((string)$value);
        }
        unset($value);
        foreach (self::SECRETS as $key) if (($data[$key] ?? '') === '') unset($data[$key]);
        $merged = $data + (new SmsService($this->repository))->configuration($lang);
        foreach ($type === 1 ? ['sms_appkey','sms_secretkey'] : ['sms_appid_tx','sms_appkey_tx'] as $key) if (empty($merged[$key])) throw new InvalidArgumentException('请填写当前短信平台的账号和密钥');
        $mobileList = array_filter(array_map('trim', explode(',', (string)($merged['sms_test_mobile'] ?? ''))));
        if (count($mobileList) > 5) throw new InvalidArgumentException('留言通知最多设置5个手机号');
        foreach ($mobileList as $mobile) if (!preg_match($country === '86' ? '/^1[3-9][0-9]{9}$/D' : '/^\+?[0-9]{5,20}$/D', $mobile)) throw new InvalidArgumentException('留言通知手机号格式不正确');
        $data['sms_type'] = (string)$type; $data['sms_nation_code'] = $country;
        $this->repository->transaction(function ($r) use ($data,$lang) {
            foreach ($data as $name => $value) {
                $row = $r->table('config')->where(['name' => $name, 'lang' => $lang])->lock(true)->find();
                $payload = ['value' => $value, 'inc_type' => 'sms', 'is_del' => 0, 'update_time' => time()];
                if ($row) $r->table('config')->where('id', $row['id'])->update($payload);
                else $r->table('config')->insert($r->filter('config', $payload + ['name' => $name, 'lang' => $lang]));
            }
        });
    }
}
