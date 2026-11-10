<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Eyou guestbook/free-form submission, without bootstrapping the original application. */
class FormService
{
    public function __construct(private ?CmsRepository $repository = null)
    {
        $this->repository ??= new CmsRepository();
    }

    /** Context: session, ip, source (1 PC/2 mobile), lang, captcha_enabled, files. */
    public function submit(array $input, array $context = []): array
    {
        $lang = (string)($context['lang'] ?? 'cn'); $uploaded = [];
        try {
            $typeid = (int)($input['typeid'] ?? 0); $formType = (int)($input['form_type'] ?? 0);
            if ($typeid <= 0 || !in_array($formType, [0, 1], true)) { throw new InvalidArgumentException($this->message('gbook11', $lang, '提交参数有误')); }
            $session = $context['session'] ?? null; $tokenName = '__token__';
            foreach ($input as $key => $value) { if (preg_match('/^__token__[A-Za-z0-9_]{0,128}$/', $key)) { $tokenName = $key; } }
            $expected = $session?->get($tokenName);
            if (!is_string($expected) || $expected === '' || !is_string($input[$tokenName] ?? null) || !hash_equals($expected, $input[$tokenName])) {
                $session?->forget($tokenName);
                throw new InvalidArgumentException('令牌数据无效，请刷新页面重试');
            }
            $settings = $this->repository->table('setting')->where(['inc_type' => 'channel_guestbook', 'lang' => $lang])->column('value', 'name');
            $interval = is_numeric($settings['channel_guestbook_interval'] ?? null) ? max(0, (int)$settings['channel_guestbook_interval']) : 60;
            $owner = $formType === 1
                ? $this->repository->table('form')->where(['form_id' => $typeid, 'status' => 1, 'lang' => $lang])->find()
                : $this->repository->table('arctype')->where(['id' => $typeid, 'current_channel' => 8, 'is_del' => 0, 'status' => 1, 'lang' => $lang])->find();
            if (!$owner) { throw new InvalidArgumentException('留言栏目或表单不存在或已关闭'); }
            $attributes = $this->repository->table('guestbook_attribute')->where(['typeid' => $typeid, 'form_type' => $formType, 'lang' => $lang, 'is_del' => 0])->order('sort_order asc,attr_id asc')->select()->toArray();
            $allowed = array_column($attributes, null, 'attr_id');
            foreach ($input as $key => $value) {
                if (preg_match('/^attr_(\d+)$/', $key, $match) && !isset($allowed[(int)$match[1]])) { throw new InvalidArgumentException('提交字段不属于当前表单'); }
            }
            $files = $context['files'] ?? [];
            foreach ($attributes as $attribute) {
                $fieldName = 'attr_' . $attribute['attr_id'];
                if (!empty($files[$fieldName])) {
                    if (!in_array((int)$attribute['attr_input_type'], [5, 8, 11], true)) { throw new InvalidArgumentException('此字段不允许上传文件'); }
                    $paths = $this->storeFiles($files[$fieldName], (int)$attribute['attr_input_type'], $uploaded);
                    $input[$fieldName] = implode(',', $paths);
                }
            }
            $values = [];
            foreach ($attributes as $attribute) {
                $key = 'attr_' . $attribute['attr_id']; $value = $input[$key] ?? '';
                if (is_array($value)) {
                    foreach ($value as $part) { if (!is_scalar($part)) { throw new InvalidArgumentException('表单字段格式错误'); } }
                    $value = implode(PHP_EOL, $value);
                }
                if (!is_scalar($value) && $value !== null) { throw new InvalidArgumentException('表单字段格式错误'); }
                $value = trim((string)$value);
                if ((int)$attribute['required'] === 1 && $value === '') { throw new InvalidArgumentException(sprintf($this->message('gbook3', $lang, '%s不能为空！'), $attribute['attr_name'])); }
                if ($value !== '' && !$this->validFormat((int)$attribute['validate_type'], $value)) { throw new InvalidArgumentException(sprintf($this->message('gbook4', $lang, '%s格式不正确！'), $attribute['attr_name'])); }
                if ($value !== '' && (int)$attribute['attr_input_type'] === 10) {
                    $date = is_numeric($value) ? (int)$value : strtotime($value);
                    if ($date === false) { throw new InvalidArgumentException(sprintf($this->message('gbook4', $lang, '%s格式不正确！'), $attribute['attr_name'])); }
                    $value = (string)$date;
                }
                if ($value !== '' && (int)$attribute['attr_input_type'] === 9 && preg_match('/^\d+(?:[,，\s]+\d+)*$/u', $value)) {
                    $ids = preg_split('/[,，\s]+/u', $value);
                    $regions = $this->repository->table('region')->whereIn('id', $ids)->column('name', 'id');
                    $value = implode(' ', array_map(fn($id) => $regions[$id] ?? '', $ids));
                }
                if (mb_strlen($value) > 100000) { throw new InvalidArgumentException('表单内容过长'); }
                if (array_key_exists($key, $input) || (int)$attribute['required'] === 1) { $values[(int)$attribute['attr_id']] = $value; }
            }
            if (!empty($context['captcha_enabled'])) {
                $captcha = (string)($input['vertify'] ?? ''); $stored = $session?->get('captcha.guestbook.' . $tokenName);
                $valid = isset($context['captcha_check']) && is_callable($context['captcha_check'])
                    ? (bool)$context['captcha_check']($captcha, $tokenName)
                    : (is_string($stored) && $stored !== '' && strcasecmp($stored, $captcha) === 0);
                if (!$valid) { throw new InvalidArgumentException($this->message($captcha === '' ? 'gbook5' : 'gbook6', $lang, '验证码不正确')); }
            }
            $ip = substr((string)($context['ip'] ?? ''), 0, 255);
            $result = $this->repository->transaction(function (CmsRepository $repository) use ($input, $context, $lang, $typeid, $formType, $owner, $values, $attributes, $ip, $interval) {
                // Lock one form owner to make the interval check and insert atomic across workers.
                $repository->table($formType === 1 ? 'form' : 'arctype')->where($formType === 1 ? 'form_id' : 'id', $typeid)->lock(true)->find();
                if ($interval > 0 && $repository->table('guestbook')->where(['typeid' => $typeid, 'form_type' => $formType, 'ip' => $ip])->where('add_time', '>', time() - $interval)->find()) { throw new InvalidArgumentException(sprintf($this->message('gbook2', $lang, '同一个IP在%s秒之内不能重复提交！'), $interval)); }
                $this->verifyRealCodes($attributes, $input, $lang);
                $formData = ['typeid' => $typeid, 'lang' => $lang];
                foreach ($values as $attrId => $value) { $formData['attr_' . $attrId] = $value; }
                $hash = md5(serialize($formData));
                $duplicate = $repository->table('guestbook')->where(['md5data' => $hash, 'typeid' => $typeid, 'form_type' => $formType, 'lang' => $lang])->find();
                if ($duplicate) {
                    $aid = (int)$duplicate['aid'];
                    $repository->table('guestbook')->where('aid', $aid)->update(['is_read' => 0, 'add_time' => time(), 'update_time' => time()]);
                } else {
                    $aid = (int)$repository->table('guestbook')->insertGetId(['typeid' => $typeid, 'form_type' => $formType, 'channel' => $formType === 1 ? 0 : 8, 'md5data' => $hash, 'ip' => $ip, 'source' => (int)($context['source'] ?? 1) === 2 ? 2 : 1, 'lang' => $lang, 'add_time' => time(), 'update_time' => time(), 'examine' => $formType === 1 && !empty($owner['open_examine']) ? 0 : 1]);
                    foreach ($values as $attrId => $value) { $repository->table('guestbook_attr')->insert(['aid' => $aid, 'form_type' => $formType, 'attr_id' => $attrId, 'attr_value' => $value, 'lang' => $lang, 'add_time' => time(), 'update_time' => time()]); }
                    if ($formType === 0) { $repository->table('arctype')->where('id', $typeid)->update(['total_arc' => $repository->table('guestbook')->where(['typeid' => $typeid, 'form_type' => 0])->count()]); }
                }
                return ['aid' => $aid, 'duplicate' => (bool)$duplicate];
            });
            $session?->forget($tokenName); $session?->forget('captcha.guestbook.' . $tokenName);
            if (!$result['duplicate']) {
                $authorization = ['typeid' => $typeid, 'form_type' => $formType, 'time' => time()];
                $session?->set('cms.form.notify.' . $result['aid'], $authorization);
                $session?->set('cms.form.sms_notify.' . $result['aid'], $authorization + ['lang' => $lang]);
                $session?->set('cms.form.sms_notify.latest', $result['aid']);
            }
            $url = trim((string)($settings['channel_guestbook_gourl'] ?? ''));
            if ($url === '') { $url = str_replace(['"', "'", ';', "\r", "\n"], '', urldecode(trim((string)($input['gourl'] ?? '')))); }
            if ($url !== '' && !preg_match('#^(?:/(?!/)|https?://)#i', $url)) { $url = ''; }
            return ['code' => 1, 'msg' => $this->message('gbook1', $lang, '操作成功'), 'data' => $result['duplicate'] ? '' : implode('|', ['gbook_submit', $typeid, $result['aid'], $formType]), 'url' => $url, 'wait' => max(1, (int)($settings['channel_guestbook_time'] ?? 5))];
        } catch (InvalidArgumentException $error) {
            foreach ($uploaded as $path) { if (is_file($path)) { unlink($path); } }
            return ['code' => 0, 'msg' => $error->getMessage(), 'data' => '', 'url' => '', 'wait' => 3];
        } catch (\Throwable $error) {
            foreach ($uploaded as $path) { if (is_file($path)) { unlink($path); } }
            throw $error;
        }
    }

    private function validFormat(int $type, string $value): bool
    {
        return match ($type) {
            1, 6 => (bool)preg_match($this->repository->table('setting')->where('name', 'foreign_open_mobile_format')->value('value') ? '/^[\d\-\+]+$/' : '/^1[3-9]\d{9}$/', $value),
            2, 7 => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            default => true,
        };
    }

    private function message(string $name, string $lang, string $fallback): string
    {
        return (string)($this->repository->table('foreign_pack')->where(['name' => $name, 'lang' => $lang])->value('value') ?: $fallback);
    }

    private function verifyRealCodes(array $attributes, array $input, string $lang): void
    {
        foreach ($attributes as $attribute) {
            if (empty($attribute['real_validate'])) { continue; }
            $field = 'attr_' . $attribute['attr_id']; $value = trim((string)($input[$field] ?? ''));
            if ($value === '') { continue; }
            if (in_array((int)$attribute['validate_type'], [1, 6], true)) {
                $code = (string)($input['real_validate_phone_input'] ?? '');
                try { (new SmsService($this->repository))->consume($value, $code, $lang); }
                catch (InvalidArgumentException $error) { throw new InvalidArgumentException($this->message('gbook8', $lang, $error->getMessage())); }
            } elseif (in_array((int)$attribute['validate_type'], [2, 7], true)) {
                $code = (string)($input['real_validate_email_code'] ?? '');
                $record = $this->repository->table('smtp_record')->where(['source' => 40, 'email' => $value, 'code' => $code, 'status' => 0, 'lang' => $lang])->where('add_time', '>', time() - 1800)->order('record_id desc')->lock(true)->find();
                if ($code === '' || !$record) { throw new InvalidArgumentException($this->message('gbook24', $lang, '邮箱验证码不正确')); }
                $this->repository->table('smtp_record')->where('record_id', $record['record_id'])->update(['status' => 1, 'update_time' => time()]);
            }
        }
    }

    private function storeFiles(mixed $files, int $type, array &$uploaded): array
    {
        $files = is_array($files) ? $files : [$files]; $urls = [];
        foreach ($files as $file) {
            if (!$file || !$file->isValid()) { throw new InvalidArgumentException('上传文件无效'); }
            $extension = strtolower($file->getUploadExtension());
            $images = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'ico'];
            $documents = ['pdf', 'txt', 'zip', 'rar', '7z', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
            if (!in_array($extension, $type === 8 ? array_merge($images, $documents) : $images, true)) { throw new InvalidArgumentException('不支持该格式的文件上传'); }
            if ($file->getSize() > 10 * 1024 * 1024) { throw new InvalidArgumentException('上传文件不能超过10MB'); }
            $directory = 'uploads/' . ($type === 8 ? 'files' : 'allimg') . '/' . date('Ymd');
            $stored = SecureUploadService::store($file, $extension, 10 * 1024 * 1024, $directory);
            $uploaded[] = $stored['path']; $urls[] = $stored['url'];
        }
        return $urls;
    }
}
