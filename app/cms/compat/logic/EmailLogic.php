<?php
namespace app\cms\compat\logic;

use app\cms\CmsContext;
use app\cms\repository\CmsRepository;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email;

/** Original EmailLogic constructor and enterprise send_email signature. */
class EmailLogic
{
    private array $config;
    public function __construct($smtp_config = [])
    {
        $this->config = $smtp_config ?: (new CmsRepository())->table('config')->where(['inc_type' => 'smtp', 'lang' => CmsContext::current()->lang, 'is_del' => 0])->column('value', 'name');
    }
    public function send_email($to = '', $subject = '', $data = '', $scene = 1, $library = 'phpmailer')
    {
        try {
            $scene = (int)$scene;
            if (!in_array($scene, [0,1,40], true)) return ['code' => 0, 'msg' => '会员及商城邮件场景已禁用'];
            $body = is_scalar($data) ? (string)$data : '';
            if ($scene > 0) {
                $template = (new CmsRepository())->table('smtp_tpl')->where(['send_scene' => $scene, 'lang' => CmsContext::current()->lang])->find();
                if (!$template) return ['code' => 0, 'msg' => '找不到相关邮件模板！'];
                if (empty($template['is_open'])) return ['code' => 0, 'msg' => '该功能待开放，请先启用邮件模板'];
                $subject = $subject ?: $template['tpl_title']; $body = (string)$template['tpl_content'];
                preg_match_all('/\$\{([^}]+)\}/', $body, $matches);
                foreach (array_unique($matches[1]) as $key) {
                    $value = is_array($data) ? ($data[$key] ?? '') : $data;
                    if (!is_scalar($value) && $value !== null) return ['code' => 0, 'msg' => '邮件模板参数格式不正确'];
                    $body = str_replace('${' . $key . '}', (string)$value, $body);
                }
            }
            if (empty($this->config['smtp_server']) || empty($this->config['smtp_user']) || empty($this->config['smtp_pwd'])) return ['code' => 0, 'msg' => '邮箱尚未配置，发送失败'];
            $to = $to ?: ($this->config['smtp_from_eamil'] ?? '');
            $recipients = array_values(array_filter(array_map('trim', explode(',', (string)$to))));
            if (!$recipients) return ['code' => 0, 'msg' => '收件邮箱不能为空'];
            foreach ($recipients as $recipient) if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) return ['code' => 0, 'msg' => 'Email邮箱格式不正确！'];
            $port = (int)($this->config['smtp_port'] ?? 25) ?: 25;
            $transport = new EsmtpTransport((string)$this->config['smtp_server'], $port, $port === 465);
            $transport->setUsername((string)$this->config['smtp_user']); $transport->setPassword((string)$this->config['smtp_pwd']);
            $transport->getStream()->setTimeout(10);
            $transport->send((new Email())->from((string)$this->config['smtp_user'])->to(...$recipients)->subject(htmlspecialchars_decode((string)$subject, ENT_QUOTES))->html($body));
            return ['code' => 1, 'msg' => '发送成功'];
        } catch (\Throwable $error) { return ['code' => 0, 'msg' => '发送失败，请检查邮箱配置或稍后重试']; }
    }
}
