<?php
// app/view/EyouTemplate.php
namespace app\view;

use think\Template;

class EyouTemplate
{
    protected Template $template;
    protected array $data = [];

    public function __construct()
    {
        $this->template = new Template([
            'view_path'    => base_path('template/pc/'),
            'cache_path'   => runtime_path('temp/'),
            'view_suffix'  => 'htm',
            'tpl_begin'    => '{',
            'tpl_end'      => '}',
            'taglib_begin' => '{',
            'taglib_end'   => '}',

            // think-template 3.x:
            // 1) 开启 <taglib ... /> 导入语法解析
            // 2) 同时预加载一次标签库，避免某些情况下未触发导入解析时标签库类不可用
            'taglib_load'     => true,
            'taglib_pre_load' => '\\app\\taglib\\eyou\\Eyou',
        ]);

        // 兜底：如果模板里没写 <taglib .../> 或解析失败，
        // 先做一个最小的字符串级预处理，把 {eyou:xxx} 统一改成 {base:xxx}。
        // 因为 taglib_pre_load 加载 \app\taglib\eyou\Base 后，其默认前缀是 "base"。
        // 这样至少能让 channel/arclist/global 跑通。
        // 注意：这只是兼容层，后续你可以再逐步替换成标准 <taglib name="eyou=..." /> 用法。
        $this->template->extend('/\{\s*eyou\s*:/i', function ($match) {
            return '{base:';
        });
        $this->template->extend('/\{\s*\/\s*eyou\s*:/i', function ($match) {
            return '{/base:';
        });
    }

    public function fetch(string $template, array $data = []): string
    {
        $this->data = array_merge($this->data, $data);

        ob_start();
        $this->template->fetch($template, $this->data);
        return (string) ob_get_clean();
    }

    public static function render(string $template, array $vars, ?string $app = null, ?string $plugin = null): string
    {
        $engine = new static();
        return $engine->fetch($template, $vars);
    }

    public function assign(string|array $name, mixed $value = ''): static
    {
        if (is_array($name)) {
            $this->data = array_merge($this->data, $name);
        } else {
            $this->data[$name] = $value;
        }
        return $this;
    }
}
