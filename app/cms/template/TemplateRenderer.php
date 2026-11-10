<?php
namespace app\cms\template;

use app\cms\CmsContext;
use app\cms\ThemeCatalog;
use app\cms\compat\Runtime;
use app\cms\compat\Template;

/** Isolated EyouCMS 1.7.7 rendering; the old framework is never booted. */
final class TemplateRenderer
{
    private array $options;
    public function __construct(private ?CmsContext $context=null, array $options=[])
    {
        Runtime::boot($context);
        $this->options=$options;
    }
    public function render(string $file,array $data=[]):string
    {
        $engine=$this->engine();
        $file=Runtime::resolveTemplate($file,$this->root(),$this->allowedRoot());
        $level=ob_get_level(); ob_start();
        try { $engine->fetch($file,$this->variables($data));return (string)ob_get_clean(); }
        catch(\Throwable $error) {while(ob_get_level()>$level)ob_end_clean();throw $error;}
    }
    public function renderString(string $content,array $data=[]):string
    {
        $level=ob_get_level();ob_start();
        try {$this->engine()->display($content,$this->variables($data));return (string)ob_get_clean();}
        catch(\Throwable $error) {while(ob_get_level()>$level)ob_end_clean();throw $error;}
    }
    private function root():string {return $this->options['template_root']??($this->context??Runtime::context())?->templateRoot()??ThemeCatalog::root('default', 'pc');}
    private function allowedRoot(): string { return $this->options['template_root'] ?? dirname(rtrim($this->root(), '/')); }
    private function variables(array $data):array
    {
        $values = array_replace(($this->context??Runtime::context())?->variables()??[], $data);
        $mode = $this->options['template_mode'] ?? TemplateMode::current($this->context);
        if ($mode === 'webman' && array_key_exists('webman', $values)) $values['eyou'] = $values['webman'];
        unset($values['webman']);
        return $values;
    }
    private function engine():Template
    {
        if($this->context)CmsContext::set($this->context);
        return new Template([
            'view_path'=>rtrim($this->root(),'/').'/', 'allowed_root'=>$this->allowedRoot(),
            'cache_path'=>rtrim($this->options['cache_path']??runtime_path('cms/templates'),'/').'/',
            'cache_prefix'=>'cms-'.($this->options['template_mode']??TemplateMode::current($this->context)).'-'.TemplateDialect::VERSION.'-'.substr(hash('sha256',$this->root()),0,12).'-',
            'template_mode'=>$this->options['template_mode']??TemplateMode::current($this->context),
            'view_suffix'=>'htm', 'tpl_cache'=>$this->options['tpl_cache']??\app\cms\cache\CachePolicy::isRedis(),
            'taglib_pre_load'=>'\\app\\cms\\compat\\template\\taglib\\Eyou',
            'taglib_build_in'=>'\\app\\cms\\compat\\template\\taglib\\Cx',
            'tpl_deny_func_list'=>Runtime::config('global.tpl_deny_func_list')??'echo,exit',
        ]);
    }
}
