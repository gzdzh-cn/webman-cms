<?php
declare(strict_types=1);
namespace app\cms\template;

use app\cms\CmsContext;
use plugin\admin\app\model\Option;

/** Site-wide trusted backend setting. Never read a dialect from HTTP parameters. */
final class TemplateMode
{
    public const VERSION = TemplateDialect::VERSION;
    public static function current(?CmsContext $context = null): string
    {
        $context ??= \Webman\Context::get('cms.context');
        if ($context instanceof CmsContext && isset($context->memo['template_mode'])) return self::validate($context->memo['template_mode']);
        $rows = Option::query()->where('name', 'cms_template_mode')->limit(2)->pluck('value')->all();
        if (count($rows) > 1) throw new \RuntimeException('模板模式配置重复，请修复后台配置');
        $mode = self::validate($rows[0] ?? 'eyou');
        if ($context instanceof CmsContext) $context->memo['template_mode'] = $mode;
        return $mode;
    }
    public static function siteSignature(?CmsContext $context = null): string
    {
        $context ??= \Webman\Context::get('cms.context');
        if ($context instanceof CmsContext && isset($context->memo['cms_site_signature'])) return $context->memo['cms_site_signature'];
        $rows = Option::query()->where('name', 'cms_site_signature')->limit(2)->pluck('value')->all();
        if (count($rows) > 1) throw new \RuntimeException('站点签名配置重复');
        $value = $rows[0] ?? 'legacy';
        if (!is_string($value) || ($value !== 'legacy' && !preg_match('/^[a-f0-9]{24,128}$/D', $value))) throw new \RuntimeException('站点签名配置无效');
        if ($context instanceof CmsContext) $context->memo['cms_site_signature'] = $value;
        return $value;
    }
    public static function validate(mixed $value): string
    {
        if (!is_string($value) || !in_array($value, ['webman', 'eyou'], true)) throw new \RuntimeException('模板模式配置无效');
        return $value;
    }
    public static function label(?CmsContext $context = null): string { return self::current($context) === 'eyou' ? 'EyouCMS 兼容模式' : 'Webman 模式'; }
    public static function tag(string $name, array $attributes = []): string
    {
        $parts = [];
        foreach ($attributes as $key => $value) {
            if (!preg_match('/^[A-Za-z][\w-]*$/D', (string)$key) || preg_match('/[\x00\r\n\x27]/', (string)$value)) throw new \InvalidArgumentException('标签示例参数无效');
            $parts[] = $key."='".$value."'";
        }
        return '{'.self::current().':'.$name.($parts ? ' '.implode(' ', $parts) : '').' /}';
    }
    public static function systemRoot(?CmsContext $context = null): string
    {
        return base_path('app/view/cms/system').(self::current($context) === 'webman' ? '/webman' : '');
    }
}
