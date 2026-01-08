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

            // 解析 {taglib name="..."} 导入语法（注意：think-template 3.x 是花括号语法，不是 <taglib />）
            'taglib_load'     => true,

            // 预加载标签库：你已把目录调整为 app/taglib/engine/Eyou.php
            'taglib_pre_load' => '\\app\\taglib\\engine\\Eyou',
        ]);
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
