<?php
namespace app\cms\compat\logic;

/** Signature bridge for the original enterprise sendSms helper. */
class SmsLogic
{
    private array $sms_config;
    public function __construct($sms_config = []) { $this->sms_config = (array)$sms_config; }
    public function sendSms($source = null, $sender = null, $params = [], $unique_id = 0)
    {
        $context = \app\cms\CmsContext::current();
        $phones = array_values(array_unique(array_filter(array_map('trim', explode(',', (string)$sender)))));
        if (!$phones || count($phones) > 5) return ['status' => -1, 'msg' => '请提供1至5个接收手机号'];
        $service = new \app\cms\service\SmsService();
        foreach ($phones as $phone) {
            $result = $service->send((int)$source, $phone, (array)$params, ['lang' => $context->lang, 'ip' => $context->request?->getRealIp() ?? '', 'unique_id' => (string)$unique_id], $this->sms_config);
            if ((int)$result['status'] !== 1) return $result;
        }
        return ['status' => 1, 'msg' => '发送成功'];
    }
}
