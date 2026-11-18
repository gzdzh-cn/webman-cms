<?php
declare(strict_types=1);
namespace app\cms\service;

use GuzzleHttp\Client;
use InvalidArgumentException;

/** Original Eyou Aliyun RPC 2017 and Tencent v5 provider contracts. */
class SmsTransport
{
    public function send(int $type, array $config, string $mobile, array $template, array $parameters, array $context = []): array
    {
        $request = $this->request($type, $config, $mobile, $template, $parameters);
        if (isset($context['transport']) && is_callable($context['transport'])) $data = ($context['transport'])($request);
        else {
            $gateway = trim((string)($config['sms_loopback_gateway'] ?? ''));
            if ($gateway !== '') {
                // A development test gateway is always a literal loopback address.
                $parts = parse_url($gateway);
                if (!$parts || ($parts['scheme'] ?? '') !== 'http' || ($parts['host'] ?? '') !== '127.0.0.1' || !isset($parts['port']) || $parts['port'] < 1 || $parts['port'] > 65535 || array_intersect(['user','pass','fragment','query'], array_keys($parts))) throw new InvalidArgumentException('本地短信测试网关地址无效');
                $query = parse_url($request['url'], PHP_URL_QUERY);
                $request['url'] = $gateway . ($query ? '?' . $query : '');
                $request['headers']['X-CMS-SMS-Provider'] = (string)$type;
            }
            $response = (new Client())->request('POST', $request['url'], ['headers' => $request['headers'], 'body' => $request['body'], 'timeout' => 5, 'connect_timeout' => 2, 'allow_redirects' => false, 'proxy' => '', 'http_errors' => false]);
            if ($response->getStatusCode() !== 200 || $response->getBody()->getSize() > 65536) return ['status' => -1, 'msg' => '短信网关请求失败'];
            $data = json_decode((string)$response->getBody(), true);
        }
        if (!is_array($data)) return ['status' => -1, 'msg' => '短信网关响应格式错误'];
        if (($type === 1 && ($data['Code'] ?? '') === 'OK') || ($type === 2 && isset($data['result']) && (int)$data['result'] === 0)) return ['status' => 1, 'msg' => '发送成功'];
        return ['status' => -1, 'msg' => '短信服务商拒绝发送'];
    }

    public function request(int $type, array $config, string $mobile, array $template, array $parameters): array
    {
        if ($type === 1) {
            if (empty($config['sms_appkey']) || empty($config['sms_secretkey'])) throw new InvalidArgumentException('请设置短信平台appkey和secretKey');
            $nation = (string)($config['sms_nation_code'] ?? '86');
            if ($nation !== '86' && !str_starts_with($mobile, '+')) $mobile = $nation . $mobile;
            $data = ['AccessKeyId' => (string)$config['sms_appkey'], 'Action' => 'SendSms', 'Format' => 'JSON', 'PhoneNumbers' => $mobile, 'RegionId' => 'cn-hangzhou', 'SignName' => $template['sms_sign'], 'SignatureMethod' => 'HMAC-SHA1', 'SignatureNonce' => bin2hex(random_bytes(16)), 'SignatureVersion' => '1.0', 'TemplateCode' => $template['sms_tpl_code'], 'TemplateParam' => json_encode((object)$parameters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'), 'Version' => '2017-05-25'];
            ksort($data); $canonical = http_build_query($data, '', '&', PHP_QUERY_RFC3986);
            $data['Signature'] = base64_encode(hash_hmac('sha1', 'POST&%2F&' . rawurlencode($canonical), (string)$config['sms_secretkey'] . '&', true));
            return ['provider' => 1, 'url' => 'https://dysmsapi.aliyuncs.com/', 'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'], 'body' => http_build_query($data, '', '&', PHP_QUERY_RFC3986)];
        }
        if ($type === 2) {
            if (empty($config['sms_appid_tx']) || empty($config['sms_appkey_tx'])) throw new InvalidArgumentException('请设置短信平台appid和appkey');
            $random = random_int(100000, 999999999); $now = time(); $nation = (string)($config['sms_nation_code'] ?? '86');
            $data = ['tel' => ['nationcode' => $nation, 'mobile' => $mobile], 'tpl_id' => (int)$template['sms_tpl_code'], 'sign' => (string)$template['sms_sign'], 'params' => array_values($parameters), 'sig' => hash('sha256', 'appkey=' . $config['sms_appkey_tx'] . '&random=' . $random . '&time=' . $now . '&mobile=' . $mobile), 'time' => $now, 'extend' => '', 'ext' => ''];
            return ['provider' => 2, 'url' => 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms?' . http_build_query(['sdkappid' => $config['sms_appid_tx'], 'random' => $random]), 'headers' => ['Content-Type' => 'application/json'], 'body' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)];
        }
        throw new InvalidArgumentException('不支持的短信平台');
    }
}
