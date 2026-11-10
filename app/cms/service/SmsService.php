<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Shared enterprise SMS delivery/logging. HTTP captcha/session checks live in FormSmsService. */
class SmsService
{
    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }

    public function configuration(string $lang): array { return $this->repository->table('config')->where(['inc_type' => 'sms', 'lang' => $lang, 'is_del' => 0])->column('value', 'name'); }
    public function lifetime(): int { return max(1, min(86400, (int)\app\cms\CmsContext::current()->getConfig('global.mobile_default_time_out', config('cms_sms.code_lifetime', 1800)))); }

    public function send(int $scene, string $mobile, array $params = [], array $context = [], array $override = []): array
    {
        $lang = (string)($context['lang'] ?? 'cn'); $lock = null; $id = null;
        try {
            if (!in_array($scene, [7,11], true)) throw new InvalidArgumentException('会员及商城短信场景已禁用');
            $config = $override ?: $this->configuration($lang); $type = (int)($config['sms_type'] ?? 1);
            $template = $this->repository->table('sms_template')->where(['sms_type' => $type, 'send_scene' => $scene, 'lang' => $lang])->find();
            if (!$template || empty($template['sms_sign']) || empty($template['sms_tpl_code']) || empty($template['tpl_content'])) throw new InvalidArgumentException('尚未正确配置短信模板，请联系管理员！');
            if (empty($template['is_open'])) throw new InvalidArgumentException('模板类型已关闭，请先开启');
            $mobile = trim($mobile); $nation = (string)($params['nation_code'] ?? $config['sms_nation_code'] ?? '86');
            if (!preg_match('/^[1-9][0-9]{0,3}$/D', $nation)) throw new InvalidArgumentException('国家区号格式不正确');
            $config['sms_nation_code'] = $nation;
            $foreign = (bool)$this->repository->table('setting')->where('name', 'foreign_open_mobile_format')->value('value');
            if (!(bool)preg_match($foreign || $nation !== '86' ? '/^\+?[0-9]{5,20}$/D' : '/^1[3-9]\d{9}$/D', $mobile)) throw new InvalidArgumentException('接收手机号不正确');
            $code = $scene === 7 ? (string)($params['code'] ?? $params['content'] ?? '') : '';
            if ($scene === 7 && !preg_match('/^\d{4}$/D', $code)) throw new InvalidArgumentException('短信验证码格式错误');
            $parameters = []; $message = (string)$template['tpl_content'];
            preg_match_all('/\$\{([A-Za-z_][A-Za-z0-9_]*)\}/', $message, $variables);
            foreach (array_unique($variables[1]) as $name) {
                $value = $name === 'code' ? $code : ($params[$name] ?? ($name === 'content' ? $code : null));
                if ($value === null || !is_scalar($value)) throw new InvalidArgumentException('短信模板参数不完整');
                $parameters[$name] = (string)$value; $message = str_replace('${' . $name . '}', (string)$value, $message);
            }
            if ($type === 2 && !$parameters) {
                preg_match_all('/\{(\d+)\}/', $message, $slots);
                $values = $scene === 7 ? [$code] : array_values(array_diff_key($params, ['nation_code' => true]));
                foreach (array_unique($slots[1]) as $slot) {
                    $value = $values[(int)$slot - 1] ?? null;
                    if ($value === null) throw new InvalidArgumentException('短信模板参数不完整');
                    $parameters[(int)$slot - 1] = (string)$value; $message = str_replace('{' . $slot . '}', (string)$value, $message);
                }
                ksort($parameters);
            }
            $unique = (string)($context['unique_id'] ?? '');
            $lock = 'cms-sms-' . substr(hash('sha256', $lang . ':' . $scene . ':' . $mobile), 0, 56);
            $result = $this->repository->connection()->query('SELECT GET_LOCK(?, 5) acquired', [$lock]);
            if ((int)($result[0]['acquired'] ?? 0) !== 1) throw new InvalidArgumentException('短信发送处理中，请稍后重试');
            $logs = fn() => $this->repository->table('sms_log')->where(['source' => $scene, 'mobile' => $mobile, 'lang' => $lang]);
            if ($scene === 7 && $logs()->where(['status' => 1, 'is_use' => 0])->where('add_time', '>', time() - 120)->find()) throw new InvalidArgumentException('120秒内只能发送一次');
            if ($scene === 11 && $unique !== '' && $logs()->where(['unique_id' => $unique, 'status' => 1])->find()) return ['status' => 1, 'msg' => '留言短信已发送'];
            if ($logs()->where('add_time', '>', time() - 10)->find()) throw new InvalidArgumentException('发送过于频繁，请稍后重试');
            $ip = substr((string)($context['ip'] ?? ''), 0, 45);
            if ($scene === 7 && $ip !== '' && $this->repository->table('sms_log')->where(['source' => 7, 'ip' => $ip, 'lang' => $lang])->where('add_time', '>', time() - 3600)->count() >= 10) throw new InvalidArgumentException('当前请求发送次数过多，请稍后再试');
            $data = ['source' => $scene, 'sms_type' => $type, 'mobile' => $mobile, 'code' => $code, 'status' => 0, 'is_use' => 1, 'msg' => mb_substr($message, 0, 255), 'ip' => $ip, 'lang' => $lang, 'error_msg' => '', 'add_time' => time(), 'update_time' => time(), 'unique_id' => $unique];
            $id = (int)$this->repository->table('sms_log')->insertGetId($this->repository->filter('sms_log', $data));
            try { $response = (new SmsTransport())->send($type, $config, $mobile, $template, $parameters, $context); }
            catch (\Throwable $error) { $response = ['status' => -1, 'msg' => '短信网关请求失败，请检查配置或稍后重试']; }
            $success = (int)($response['status'] ?? 0) === 1;
            $this->repository->transaction(function ($r) use ($id,$success,$scene,$mobile,$lang,$response) {
                if ($success && $scene === 7) $r->table('sms_log')->where(['source' => 7, 'mobile' => $mobile, 'lang' => $lang])->where('id', '<>', $id)->update(['is_use' => 1, 'update_time' => time()]);
                $r->table('sms_log')->where('id', $id)->update(['status' => $success ? 1 : 0, 'is_use' => $success && $scene === 7 ? 0 : 1, 'error_msg' => $success ? '' : mb_substr((string)$response['msg'], 0, 1000), 'update_time' => time()]);
            });
            return ['status' => $success ? 1 : -1, 'msg' => $success ? '发送成功' : $response['msg']];
        } catch (InvalidArgumentException $error) { return ['status' => -1, 'msg' => $error->getMessage()]; }
        catch (\Throwable $error) { return ['status' => -1, 'msg' => '短信发送失败，请检查配置或稍后重试']; }
        finally { if ($lock) { try { $this->repository->connection()->query('SELECT RELEASE_LOCK(?)', [$lock]); } catch (\Throwable $ignored) {} } }
    }

    public function consume(string $mobile, string $code, string $lang): void
    {
        $record = $this->repository->table('sms_log')->where(['source' => 7, 'mobile' => $mobile, 'code' => $code, 'status' => 1, 'is_use' => 0, 'lang' => $lang])->where('add_time', '>', time() - $this->lifetime())->order('id desc')->lock(true)->find();
        if ($code === '' || !$record) throw new InvalidArgumentException('手机验证码不正确或已过期');
        $this->repository->table('sms_log')->where(['source' => 7, 'mobile' => $mobile, 'lang' => $lang, 'is_use' => 0])->update(['is_use' => 1, 'update_time' => time()]);
    }
}
