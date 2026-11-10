<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Original api/Ajax/SendMobileCode enterprise scenes, without member workflows. */
class FormSmsService
{
    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }

    public function send(array $input, array $context = []): array
    {
        try {
            $scene = (int)($input['scene'] ?? $input['source'] ?? 0); $session = $context['session'] ?? null; $lang = (string)($context['lang'] ?? 'cn');
            if (!in_array($scene, [7,11], true)) throw new InvalidArgumentException('会员及商城短信场景已禁用');
            $service = new SmsService($this->repository);
            if ($scene === 7) {
                $code = trim((string)($input['code'] ?? '')); $token = (string)($input['code_token'] ?? '');
                if ($code === '') throw new InvalidArgumentException('请输入图形验证码！');
                $stored = $session?->get('captcha.guestbook.' . $token); $check = $context['captcha_check'] ?? null;
                $valid = is_callable($check) ? (bool)$check($code, $token) : (is_string($stored) && $stored !== '' && strcasecmp($stored, $code) === 0);
                if (!$valid) throw new InvalidArgumentException('图片验证码错误');
                $mobile = trim((string)($input['phone'] ?? $input['mobile'] ?? ''));
                $response = $service->send(7, $mobile, ['code' => (string)random_int(1000, 9999)], $context);
                if ((int)$response['status'] !== 1) throw new InvalidArgumentException($response['msg']);
                $session?->forget('captcha.guestbook.' . $token);
            } else {
                $aid = (int)($input['aid'] ?? $session?->get('cms.form.sms_notify.latest') ?? 0);
                $authorization = $session?->get('cms.form.sms_notify.' . $aid);
                if (!is_array($authorization) || ($authorization['time'] ?? 0) < time() - 1800 || ($authorization['lang'] ?? '') !== $lang) throw new InvalidArgumentException('留言短信发送凭据无效');
                $type = (int)$authorization['form_type']; $tid = (int)$authorization['typeid'];
                $guestbook = $this->repository->table('guestbook')->where(['aid' => $aid, 'typeid' => $tid, 'form_type' => $type, 'lang' => $lang])->find();
                if (!$guestbook) throw new InvalidArgumentException('留言不存在');
                $config = $service->configuration($lang); $recipients = array_values(array_unique(array_filter(array_map('trim', explode(',', (string)($config['sms_test_mobile'] ?? ''))))));
                if (!$recipients || count($recipients) > 5) throw new InvalidArgumentException('请配置1至5个接收留言提醒的手机号');
                $owner = $this->repository->table($type === 1 ? 'form' : 'arctype')->where($type === 1 ? 'form_id' : 'id', $tid)->find();
                $content = (string)($owner[$type === 1 ? 'form_name' : 'typename'] ?? '留言表单');
                foreach ($recipients as $mobile) {
                    $response = $service->send(11, $mobile, ['content' => $content], $context + ['unique_id' => 'gbook:' . $aid]);
                    if ((int)$response['status'] !== 1) throw new InvalidArgumentException($response['msg']);
                }
                $session?->forget('cms.form.sms_notify.' . $aid);
                if ((int)$session?->get('cms.form.sms_notify.latest') === $aid) $session?->forget('cms.form.sms_notify.latest');
            }
            return ['code' => 1, 'msg' => '发送成功', 'data' => '', 'url' => '', 'wait' => 3];
        } catch (InvalidArgumentException $error) { return ['code' => 0, 'msg' => $error->getMessage(), 'data' => '', 'url' => '', 'wait' => 3]; }
        catch (\Throwable $error) { return ['code' => 0, 'msg' => '短信发送失败，请检查配置或稍后重试', 'data' => '', 'url' => '', 'wait' => 3]; }
    }
}
