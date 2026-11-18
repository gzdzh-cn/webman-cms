<?php
/** Presentation only. Controller keys and business routes remain authorization identities. */
$page = static fn(string $controller, string $title): array => [
    'key' => 'plugin\\admin\\app\\controller\\' . $controller . 'Controller', 'title' => $title,
];
$module = static fn(string $id, string $title, array $pages, string $icon = 'layui-icon-file'): array => compact('id', 'title', 'pages', 'icon');
return [
    ['id' => 'content', 'key' => 'cms-content', 'title' => '内容管理', 'icon' => 'layui-icon-file', 'modules' => [
        $module('arctype', '栏目管理', [$page('Arctype', '栏目管理')], 'layui-icon-template-1') + ['shortcut' => true],
        $module('archive', '文章管理', [$page('Archive', '文章管理')]) + ['shortcut' => true],
        $module('contenttools', '内容扩展', [$page('Productparameter', '产品参数'), $page('Tags', 'TAG标签'), $page('Faq', '常见问题'), $page('Specialnode', '专题管理')], 'layui-icon-app'),
        $module('recycle', '回收站', [$page('Recycle', '回收站')], 'layui-icon-delete'),
    ]],
    ['id' => 'site', 'key' => 'cms-site', 'title' => '网站运营', 'icon' => 'layui-icon-website', 'modules' => [
        $module('ad', '广告管理', [$page('Ad', '广告列表'), $page('Adposition', '广告位置')], 'layui-icon-carousel') + ['shortcut' => true],
        $module('links', '友情链接', [$page('Links', '链接列表'), $page('Linksgroup', '链接分组')], 'layui-icon-link'),
        $module('navigation', '网站导航', [$page('Navigation', '导航链接'), $page('Navigationposition', '导航位置')]),
        $module('seo', 'SEO设置', [$page('Seo', 'URL设置'), $page('Staticgenerate', '静态生成') + ['auxiliary' => true, 'return_key' => $page('Seo', '')['key']], $page('Sitemap', 'Sitemap'), $page('Robots', 'robots.txt 管理') + ['permission_key' => $page('Seo', '')['key'], 'id' => 'cms-page-robots', 'href' => '/web-admin/robots/index'], $page('Searchword', '搜索词')], 'layui-icon-set') + ['shortcut' => true],
    ]],
    ['id' => 'forms', 'key' => 'cms-forms', 'title' => '表单互动', 'icon' => 'layui-icon-form', 'modules' => [
        $module('guestbook', '留言管理', [$page('Guestbook', '留言管理')], 'layui-icon-dialogue') + ['shortcut' => true],
        $module('form', '自由表单', [$page('Form', '表单管理'), $page('Formfield', '表单字段')]),
        $module('notification', '消息通知', [$page('Smslog', '短信记录'), $page('Smstemplate', '短信模板'), $page('Smsconfig', '短信配置'), $page('Emailtemplate', '邮件模板')], 'layui-icon-notice'),
    ]],
    ['id' => 'assets', 'key' => 'cms-assets', 'title' => '素材与模板', 'icon' => 'layui-icon-picture', 'modules' => [
        $module('assets', '素材库', [$page('Upload', '附件管理'), $page('Downloadfile', '下载文件'), $page('Mediafile', '视频文件')], 'layui-icon-picture'),
        $module('filemanager', '模板管理', [$page('Filemanager', '模板管理')], 'layui-icon-template'),
    ]],
    ['id' => 'settings', 'key' => 'cms-settings', 'title' => '站点设置', 'icon' => 'layui-icon-set', 'modules' => [
        $module('websiteconfig', '基本设置', [$page('Websiteconfig', '网站信息') + ['sections' => [['id' => 'web', 'title' => '网站信息'], ['id' => 'core', 'title' => '核心设置'], ['id' => 'attachment', 'title' => '附件扩展'], ['id'=>'image','title'=>'图片设置'], ['id' => 'api', 'title' => '接口API']]], $page('Globalvariable', '自定义变量')], 'layui-icon-set') + ['shortcut' => true],
        $module('language', '多语言', [$page('Language', '语言列表') + ['sections' => [['id'=>'list','title'=>'语言列表'],['id'=>'config','title'=>'功能配置']]], $page('Languagepack', '本地语言包') + ['auxiliary'=>true,'return_key'=>$page('Language','')['key']]], 'layui-icon-website'),
        $module('citysite', '城市分站', [$page('Citysite', '城市分站')], 'layui-icon-location'),
        $module('channeltype', '内容模型', [$page('Channeltype', '模型管理'), $page('Channelfield', '自定义字段')], 'layui-icon-template-1'),
    ]],
    ['id' => 'system', 'key' => 'common', 'title' => '系统管理', 'icon' => 'layui-icon-set', 'modules' => [
        $module('plugins', '插件市场', [$page('Plugin', '插件市场')], 'layui-icon-app'),
        $module('admin', '账号权限', [$page('Admin', '管理员'), $page('Role', '角色权限')], 'layui-icon-user'),
        $module('systemtools', '系统维护', [$page('Rule', '菜单配置'), $page('Operationlog', '操作日志'), $page('Config', '后台外观')], 'layui-icon-set'),
        $module('account', '个人资料', [$page('Account', '个人资料')], 'layui-icon-username') + ['sidebar' => false],
    ]],
];
