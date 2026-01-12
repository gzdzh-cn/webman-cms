<?php
// app/core/library/think/view/driver/Think.php
namespace think\view\driver;

use think\Template;

class Think
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

            'taglib_load'     => true,
            'taglib_pre_load' => '\\think\\template\\taglib\\Eyou',
            // 禁用默认HTML转义，以便正确渲染HTML标签
            'default_filter'  => '',
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
