<?php
// app/taglib/engine/Eyou.php
namespace app\taglib\engine;

use think\template\TagLib;

/**
 * eyoucms 标签库（前缀：eyou）
 *
 * think-template 会使用类名的小写作为标签前缀（Eyou -> eyou），
 * 因此把原 Base 的实现迁移到这里，模板即可直接使用 {eyou:xxx}。
 */
class Eyou extends TagLib
{
    // 标签定义
    protected $tags = [
        // 全局变量
        'global'  => ['attr' => 'name', 'close' => 0, 'must' => 'name'],
        // 导航
        'channel' => ['attr' => 'typeid,row,currentstyle', 'close' => 1],
        // 文章列表
        'arclist' => ['attr' => 'typeid,row,titlelen,orderby', 'close' => 1],
        // 更多标签可以后续继续扩展...
    ];

    /**
     * 全局变量标签
     * 用法: {eyou:global name='web_name' /}
     */
    public function tagGlobal($tag, $content)
    {
        $name = $tag['name'] ?? '';
        if (empty($name)) {
            return '';
        }
        return "<?php echo \\app\\facade\\Eyou::getGlobal('{$name}'); ?>";
    }

    /**
     * 导航标签
     * 用法: {eyou:channel typeid='0' row='10' currentstyle='active'}...{/eyou:channel}
     */
    public function tagChannel($tag, $content)
    {
        $typeid        = $tag['typeid'] ?? 0;
        $row           = $tag['row'] ?? 10;
        $currentstyle  = $tag['currentstyle'] ?? '';

        $parseStr  = "<?php ";
        $parseStr .= "\$channels = \\app\\facade\\Eyou::getChannel({$typeid}, {$row}); ";
        $parseStr .= "if(!empty(\$channels)): ";
        $parseStr .= "foreach(\$channels as \$field): ";
        $parseStr .= "\$current = \\app\\facade\\Eyou::isCurrent(\$field['typeid'] ?? 0) ? '{$currentstyle}' : ''; ";
        $parseStr .= "?>";
        $parseStr .= $content;
        $parseStr .= "<?php endforeach; endif; ?>";

        return $parseStr;
    }

    /**
     * 文章列表标签
     * 用法: {eyou:arclist typeid='1' row='10' titlelen='30' orderby='a.add_time desc'}...{/eyou:arclist}
     */
    public function tagArclist($tag, $content)
    {
        $typeid    = $tag['typeid'] ?? 0;
        $row       = $tag['row'] ?? 10;
        $titlelen  = $tag['titlelen'] ?? 30;
        $orderby   = $tag['orderby'] ?? 'a.add_time desc';

        $parseStr  = "<?php ";
        $parseStr .= "\$arclist = \\app\\facade\\Eyou::getArclist({$typeid}, {$row}, '{$orderby}'); ";
        $parseStr .= "if(!empty(\$arclist)): ";
        $parseStr .= "foreach(\$arclist as \$field): ";
        $parseStr .= "\$field['title'] = mb_substr(\$field['title'] ?? '', 0, {$titlelen}, 'utf-8'); ";
        $parseStr .= "\$field['arcurl'] = \$field['arcurl'] ?? '#'; ";
        $parseStr .= "?>";
        $parseStr .= $content;
        $parseStr .= "<?php endforeach; endif; ?>";

        return $parseStr;
    }
}
