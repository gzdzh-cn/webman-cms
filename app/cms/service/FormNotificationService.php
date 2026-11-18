<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;

/** Original Ajax/send_email enterprise scenes using real SMTP settings. */
class FormNotificationService
{
    public function __construct(private ?CmsRepository $repository = null)
    {
        $this->repository ??= new CmsRepository();
    }

    /** Context: session, lang, captcha_check(code, token), optional SMTP transport for testing. */
    public function send(array $input, array $context = []): array
    {
        $lang = (string)($context['lang'] ?? 'cn');
        try {
            $type = (string)($input['type'] ?? '');
            if (!in_array($type, ['gbook_submit', 'gbook_real_validate'], true)) { throw new InvalidArgumentException('不支持的邮件场景'); }
            $scene = $type === 'gbook_submit' ? 1 : 40;
            $template = $this->repository->table('smtp_tpl')->where(['send_scene' => $scene, 'lang' => $lang])->find();
            $config = $this->repository->table('config')->where(['inc_type' => 'smtp', 'lang' => $lang])->column('value', 'name');
            if (empty($template['is_open']) || empty($config['smtp_server']) || empty($config['smtp_user']) || empty($config['smtp_pwd']) || ($scene === 1 && empty($config['smtp_from_eamil']))) {
                throw new InvalidArgumentException('邮箱尚未配置，发送失败');
            }
            $session = $context['session'] ?? null;
            if ($scene === 1) {
                $aid = (int)($input['aid'] ?? 0); $typeid = (int)($input['tid'] ?? 0); $formType = (int)($input['form_type'] ?? 0);
                $authorization = $session?->get('cms.form.notify.' . $aid);
                if (!is_array($authorization) || ($authorization['time'] ?? 0) < time() - 1800 || (int)$authorization['typeid'] !== $typeid || (int)$authorization['form_type'] !== $formType) { throw new InvalidArgumentException('留言发送凭据无效'); }
                $guestbook = $this->repository->table('guestbook')->where(['aid' => $aid, 'typeid' => $typeid, 'form_type' => $formType, 'lang' => $lang])->find();
                if (!$guestbook) { throw new InvalidArgumentException('留言不存在'); }
                $owner = $this->repository->table($formType === 1 ? 'form' : 'arctype')->where($formType === 1 ? 'form_id' : 'id', $typeid)->find();
                $formName = $owner[$formType === 1 ? 'form_name' : 'typename'] ?? '';
                $fields = $this->repository->table('guestbook_attribute')->where(['typeid' => $typeid, 'form_type' => $formType, 'lang' => $lang])->order('attr_id asc')->select()->toArray();
                $values = $this->repository->table('guestbook_attr')->where(['aid' => $aid, 'form_type' => $formType])->column('attr_value', 'attr_id');
                $escape = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $webName = $this->repository->table('config')->where(['name' => 'web_name', 'lang' => $lang])->value('value') ?: '';
                $html = '<p>' . $escape($formName . '-' . $webName) . '</p><p>';
                foreach ($fields as $field) {
                    $value = (string)($values[$field['attr_id']] ?? '');
                    if ((int)$field['attr_input_type'] === 10 && ctype_digit($value)) { $value = date('Y-m-d H:i:s', (int)$value); }
                    $html .= $escape($field['attr_name']) . '：' . nl2br($escape($value)) . '<br/>';
                }
                $html .= '所属表单：' . $escape($formName) . '<br/>IP来源：' . $escape($guestbook['ip']) . '<br/>提交来源：' . ((int)$guestbook['source'] === 2 ? '手机端' : '电脑端') . '<br/>提交时间：' . date('Y-m-d H:i:s', (int)$guestbook['add_time']) . '</p>';
                $to = (string)$config['smtp_from_eamil'];
            } else {
                $captcha = (string)($input['code'] ?? ''); $token = (string)($input['code_token'] ?? '');
                $check = $context['captcha_check'] ?? null;
                $stored = $session?->get('captcha.gbook_real_validate.' . $token) ?? $session?->get('captcha.guestbook.' . $token);
                $valid = is_callable($check) ? $check($captcha, $token) : (is_string($stored) && $stored !== '' && strcasecmp($stored, $captcha) === 0);
                if ($captcha === '') { throw new InvalidArgumentException('请输入图形验证码！'); }
                if (!$valid) { throw new InvalidArgumentException('图形验证码错误！'); }
                $to = trim((string)($input['email'] ?? ''));
                if (!filter_var($to, FILTER_VALIDATE_EMAIL)) { throw new InvalidArgumentException('Email邮箱格式不正确！'); }
                $recent = $this->repository->table('smtp_record')->where(['email' => $to, 'source' => 40, 'lang' => $lang])->where('add_time', '>', time() - 60)->find();
                if ($recent) { throw new InvalidArgumentException('请60秒后再发送验证码'); }
                $code = (string)random_int(1000, 9999);
                $html = '<p>您的邮箱验证码为 ' . $code . ' ，该验证码30分钟内有效，请勿泄露于他人。</p>';
            }
            $body = str_replace('${content}', $html, (string)$template['tpl_content']);
            if ($body === '') { throw new InvalidArgumentException('找不到相关邮件模板！'); }
            $recipients = array_values(array_filter(array_map('trim', explode(',', $to))));
            foreach ($recipients as $recipient) { if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) { throw new InvalidArgumentException('Email邮箱格式不正确！'); } }
            if (!$recipients) { throw new InvalidArgumentException('收件邮箱不能为空'); }
            $transport = $context['transport'] ?? null;
            if (!$transport) {
                $port = (int)($config['smtp_port'] ?? 25) ?: 25;
                $transport = new EsmtpTransport((string)$config['smtp_server'], $port, $port === 465);
                $transport->setUsername((string)$config['smtp_user']); $transport->setPassword((string)$config['smtp_pwd']);
                $transport->getStream()->setTimeout(10);
            }
            $email = (new Email())->from((string)$config['smtp_user'])->to(...$recipients)->subject(htmlspecialchars_decode((string)$template['tpl_title'], ENT_QUOTES))->html($body);
            $transport->send($email);
            if ($scene === 40) {
                $this->repository->transaction(function (CmsRepository $repository) use ($to, $code, $lang) {
                    $repository->table('smtp_record')->where(['source' => 40, 'email' => $to, 'lang' => $lang])->update(['status' => 1, 'update_time' => time()]);
                    $repository->table('smtp_record')->insert(['source' => 40, 'email' => $to, 'code' => $code, 'status' => 0, 'lang' => $lang, 'add_time' => time(), 'update_time' => time()]);
                });
                $session?->forget('captcha.gbook_real_validate.' . $token); $session?->forget('captcha.guestbook.' . $token);
            } else { $session?->forget('cms.form.notify.' . $aid); }
            return ['code' => 1, 'msg' => '发送成功', 'data' => '', 'url' => '', 'wait' => 3];
        } catch (InvalidArgumentException $error) {
            return ['code' => 0, 'msg' => $error->getMessage(), 'data' => '', 'url' => '', 'wait' => 3];
        } catch (\Throwable $error) {
            // SMTP diagnostics can include account identifiers: keep them out of public responses.
            \support\Log::warning('CMS form email delivery failed', ['error_type' => get_class($error), 'file' => basename($error->getFile()), 'line' => $error->getLine()]);
            return ['code' => 0, 'msg' => '发送失败，请检查邮箱配置或稍后重试', 'data' => '', 'url' => '', 'wait' => 3];
        }
    }
}
