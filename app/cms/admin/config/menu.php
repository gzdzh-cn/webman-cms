<?php

// Enterprise CMS menus. Framework user/demo tables are retained without public menu entries.
$menus = [
    ['title' => '内容管理', 'key' => 'cms-content', 'icon' => 'layui-icon-file', 'type' => 0, 'weight' => 1100, 'children' => [
        ['title' => '栏目管理', 'key' => \plugin\admin\app\controller\ArctypeController::class, 'href' => '/web-admin/arctype/index', 'type' => 1, 'weight' => 100, 'children' => [
            ['title' => '读取栏目自定义字段', 'key' => \plugin\admin\app\controller\ChannelfieldController::class . '@categoryFields', 'type' => 2, 'weight' => 1],
        ]],
        ['title' => '文章管理', 'key' => \plugin\admin\app\controller\ArchiveController::class, 'href' => '/web-admin/archive/index', 'type' => 1, 'weight' => 99, 'children' => [
            ['title' => '批量移动', 'key' => \plugin\admin\app\controller\ArchiveController::class . '@move', 'type' => 2, 'weight' => 1],
        ]],
        ['title' => '下载附件', 'key' => \plugin\admin\app\controller\DownloadfileController::class, 'href' => '/web-admin/downloadfile/index', 'type' => 1, 'weight' => 98],
        ['title' => '视频附件', 'key' => \plugin\admin\app\controller\MediafileController::class, 'href' => '/web-admin/mediafile/index', 'type' => 1, 'weight' => 98],
        ['title' => '内容模型', 'key' => \plugin\admin\app\controller\ChanneltypeController::class, 'href' => '/web-admin/channeltype/index', 'type' => 1, 'weight' => 98],
        ['title' => '自定义字段', 'key' => \plugin\admin\app\controller\ChannelfieldController::class, 'href' => '/web-admin/channelfield/index', 'type' => 1, 'weight' => 97],
        ['title' => 'TAG管理', 'key' => \plugin\admin\app\controller\TagsController::class, 'href' => '/web-admin/tags/index', 'type' => 1, 'weight' => 96],
        ['title' => '常见问题', 'key' => \plugin\admin\app\controller\FaqController::class, 'href' => '/web-admin/faq/index', 'type' => 1, 'weight' => 96, 'children' => [
            ['title' => '读取FAQ分组', 'key' => \plugin\admin\app\controller\FaqController::class . '@select', 'type' => 2, 'weight' => 4],
            ['title' => '读取FAQ问答', 'key' => \plugin\admin\app\controller\FaqController::class . '@detail', 'type' => 2, 'weight' => 3],
            ['title' => '保存FAQ分组及问答', 'key' => \plugin\admin\app\controller\FaqController::class . '@save', 'type' => 2, 'weight' => 2],
            ['title' => '永久删除FAQ分组及问答', 'key' => \plugin\admin\app\controller\FaqController::class . '@delete', 'type' => 2, 'weight' => 1],
        ]],
        ['title' => '专题节点', 'key' => \plugin\admin\app\controller\SpecialnodeController::class, 'href' => '/web-admin/specialnode/index', 'type' => 1, 'weight' => 96, 'children' => [
            ['title' => '读取专题列表', 'key' => \plugin\admin\app\controller\SpecialnodeController::class . '@topics', 'type' => 2, 'weight' => 5],
            ['title' => '读取专题节点', 'key' => \plugin\admin\app\controller\SpecialnodeController::class . '@select', 'type' => 2, 'weight' => 4],
            ['title' => '选择专题文档', 'key' => \plugin\admin\app\controller\SpecialnodeController::class . '@choices', 'type' => 2, 'weight' => 3],
            ['title' => '保存专题节点', 'key' => \plugin\admin\app\controller\SpecialnodeController::class . '@save', 'type' => 2, 'weight' => 2],
            ['title' => '永久删除专题节点', 'key' => \plugin\admin\app\controller\SpecialnodeController::class . '@delete', 'type' => 2, 'weight' => 1],
        ]],
        ['title' => '产品参数', 'key' => \plugin\admin\app\controller\ProductparameterController::class, 'href' => '/web-admin/productparameter/index', 'type' => 1, 'weight' => 96, 'children' => [
            ['title' => '读取参数定义及组', 'key' => \plugin\admin\app\controller\ProductparameterController::class . '@metadata', 'type' => 2, 'weight' => 8],
            ['title' => '读取产品参数值', 'key' => \plugin\admin\app\controller\ProductparameterController::class . '@product', 'type' => 2, 'weight' => 7],
            ['title' => '保存参数定义', 'key' => \plugin\admin\app\controller\ProductparameterController::class . '@saveDefinition', 'type' => 2, 'weight' => 6],
            ['title' => '保存新参数组', 'key' => \plugin\admin\app\controller\ProductparameterController::class . '@saveGroup', 'type' => 2, 'weight' => 5],
            ['title' => '保存产品参数值', 'key' => \plugin\admin\app\controller\ProductparameterController::class . '@saveProduct', 'type' => 2, 'weight' => 4],
            ['title' => '回收旧参数定义', 'key' => \plugin\admin\app\controller\ProductparameterController::class . '@recycle', 'type' => 2, 'weight' => 3],
            ['title' => '恢复旧参数定义', 'key' => \plugin\admin\app\controller\ProductparameterController::class . '@restore', 'type' => 2, 'weight' => 2],
            ['title' => '永久删除参数及值', 'key' => \plugin\admin\app\controller\ProductparameterController::class . '@purge', 'type' => 2, 'weight' => 1],
        ]],
        ['title' => '回收站', 'key' => \plugin\admin\app\controller\RecycleController::class, 'href' => '/web-admin/recycle/index', 'type' => 1, 'weight' => 95],
    ]],
    ['title' => '网站运营', 'key' => 'cms-site', 'icon' => 'layui-icon-website', 'type' => 0, 'weight' => 1000, 'children' => [
        ['title' => '网站设置', 'key' => \plugin\admin\app\controller\WebsiteconfigController::class, 'href' => '/web-admin/websiteconfig/index', 'type' => 1, 'weight' => 100],
        ['title' => '自定义变量', 'key' => \plugin\admin\app\controller\GlobalvariableController::class, 'href' => '/web-admin/globalvariable/index', 'type' => 1, 'weight' => 99],
        ['title' => '广告位置', 'key' => \plugin\admin\app\controller\AdpositionController::class, 'href' => '/web-admin/adposition/index', 'type' => 1, 'weight' => 98],
        ['title' => '广告轮播', 'key' => \plugin\admin\app\controller\AdController::class, 'href' => '/web-admin/ad/index', 'type' => 1, 'weight' => 97],
        ['title' => '友情链接分组', 'key' => \plugin\admin\app\controller\LinksgroupController::class, 'href' => '/web-admin/linksgroup/index', 'type' => 1, 'weight' => 96],
        ['title' => '友情链接', 'key' => \plugin\admin\app\controller\LinksController::class, 'href' => '/web-admin/links/index', 'type' => 1, 'weight' => 95],
        ['title' => '导航位置', 'key' => \plugin\admin\app\controller\NavigationpositionController::class, 'href' => '/web-admin/navigationposition/index', 'type' => 1, 'weight' => 94],
        ['title' => '自定义导航', 'key' => \plugin\admin\app\controller\NavigationController::class, 'href' => '/web-admin/navigation/index', 'type' => 1, 'weight' => 93],
        ['title' => '搜索词', 'key' => \plugin\admin\app\controller\SearchwordController::class, 'href' => '/web-admin/searchword/index', 'type' => 1, 'weight' => 92],
        ['title' => 'SEO设置', 'key' => \plugin\admin\app\controller\SeoController::class, 'href' => '/web-admin/seo/index', 'type' => 1, 'weight' => 91, 'children' => [
            ['title' => '读取SEO设置', 'key' => \plugin\admin\app\controller\SeoController::class . '@select', 'type' => 2, 'weight' => 2],
            ['title' => '保存SEO设置', 'key' => \plugin\admin\app\controller\SeoController::class . '@save', 'type' => 2, 'weight' => 1],
        ]],
        ['title' => '静态生成', 'key' => \plugin\admin\app\controller\StaticgenerateController::class, 'href' => '/web-admin/staticgenerate/index', 'type' => 1, 'weight' => 90, 'children' => [
            ['title' => '读取静态生成设置', 'key' => \plugin\admin\app\controller\StaticgenerateController::class . '@select', 'type' => 2, 'weight' => 6],
            ['title' => '读取生成进度', 'key' => \plugin\admin\app\controller\StaticgenerateController::class . '@status', 'type' => 2, 'weight' => 5],
            ['title' => '创建生成任务', 'key' => \plugin\admin\app\controller\StaticgenerateController::class . '@start', 'type' => 2, 'weight' => 4],
            ['title' => '执行生成任务', 'key' => \plugin\admin\app\controller\StaticgenerateController::class . '@step', 'type' => 2, 'weight' => 3],
            ['title' => '清理已生成页面', 'key' => \plugin\admin\app\controller\StaticgenerateController::class . '@clean', 'type' => 2, 'weight' => 2],
        ]],
        ['title' => 'Sitemap', 'key' => \plugin\admin\app\controller\SitemapController::class, 'href' => '/web-admin/sitemap/index', 'type' => 1, 'weight' => 89, 'children' => [
            ['title' => '保存百度推送Token', 'key' => \plugin\admin\app\controller\SitemapController::class . '@baiduSave', 'type' => 2, 'weight' => 6],
            ['title' => '管理百度定时推送', 'key' => \plugin\admin\app\controller\SitemapController::class . '@baiduSchedule', 'type' => 2, 'weight' => 4],
            ['title' => '执行百度推送', 'key' => \plugin\admin\app\controller\SitemapController::class . '@baiduPush', 'type' => 2, 'weight' => 5],
            ['title' => '读取Sitemap设置', 'key' => \plugin\admin\app\controller\SitemapController::class . '@select', 'type' => 2, 'weight' => 4],
            ['title' => '保存Sitemap设置', 'key' => \plugin\admin\app\controller\SitemapController::class . '@save', 'type' => 2, 'weight' => 3],
            ['title' => '生成Sitemap文件', 'key' => \plugin\admin\app\controller\SitemapController::class . '@generate', 'type' => 2, 'weight' => 2],
            ['title' => '清理Sitemap文件', 'key' => \plugin\admin\app\controller\SitemapController::class . '@clean', 'type' => 2, 'weight' => 1],
        ]],
        ['title' => '语言管理', 'key' => \plugin\admin\app\controller\LanguageController::class, 'href' => '/web-admin/language/index', 'type' => 1, 'weight' => 91, 'children' => [
            ['title' => '永久删除语言及企业数据', 'key' => \plugin\admin\app\controller\LanguageController::class . '@delete', 'type' => 2, 'weight' => 1],
        ]],
        ['title' => '语言包变量', 'key' => \plugin\admin\app\controller\LanguagepackController::class, 'href' => '/web-admin/languagepack/index', 'type' => 1, 'weight' => 90],
        ['title' => '城市分站', 'key' => \plugin\admin\app\controller\CitysiteController::class, 'href' => '/web-admin/citysite/index', 'type' => 1, 'weight' => 90],
        ['title' => '模板文件管理', 'key' => \plugin\admin\app\controller\FilemanagerController::class, 'href' => '/web-admin/filemanager/index', 'type' => 1, 'weight' => 89, 'children' => [
            ['title' => '读取模板目录', 'key' => \plugin\admin\app\controller\FilemanagerController::class . '@select', 'type' => 2, 'weight' => 5],
            ['title' => '读取模板源文件', 'key' => \plugin\admin\app\controller\FilemanagerController::class . '@read', 'type' => 2, 'weight' => 4],
            ['title' => '编辑模板源文件（含JS）', 'key' => \plugin\admin\app\controller\FilemanagerController::class . '@edit', 'type' => 2, 'weight' => 3],
            ['title' => '新建模板源文件（含JS）', 'key' => \plugin\admin\app\controller\FilemanagerController::class . '@newfile', 'type' => 2, 'weight' => 2],
            ['title' => '替换模板图片', 'key' => \plugin\admin\app\controller\FilemanagerController::class . '@replace_img', 'type' => 2, 'weight' => 1],
        ]],
    ]],
    ['title' => '表单留言', 'key' => 'cms-forms', 'icon' => 'layui-icon-form', 'type' => 0, 'weight' => 900, 'children' => [
        ['title' => '短信配置', 'key' => \plugin\admin\app\controller\SmsconfigController::class, 'href' => '/web-admin/smsconfig/index', 'type' => 1, 'weight' => 96],
        ['title' => '短信模板', 'key' => \plugin\admin\app\controller\SmstemplateController::class, 'href' => '/web-admin/smstemplate/index', 'type' => 1, 'weight' => 95],
        ['title' => '短信记录', 'key' => \plugin\admin\app\controller\SmslogController::class, 'href' => '/web-admin/smslog/index', 'type' => 1, 'weight' => 94],
        ['title' => '邮件模板', 'key' => \plugin\admin\app\controller\EmailtemplateController::class, 'href' => '/web-admin/emailtemplate/index', 'type' => 1, 'weight' => 97],
        ['title' => '表单管理', 'key' => \plugin\admin\app\controller\FormController::class, 'href' => '/web-admin/form/index', 'type' => 1, 'weight' => 100],
        ['title' => '表单字段', 'key' => \plugin\admin\app\controller\FormfieldController::class, 'href' => '/web-admin/formfield/index', 'type' => 1, 'weight' => 99],
        ['title' => '留言管理', 'key' => \plugin\admin\app\controller\GuestbookController::class, 'href' => '/web-admin/guestbook/index', 'type' => 1, 'weight' => 98],
    ]],
    ['title' => '系统管理', 'key' => 'common', 'icon' => 'layui-icon-set', 'type' => 0, 'weight' => 800, 'children' => [
        ['title' => '附件管理', 'key' => \plugin\admin\app\controller\UploadController::class, 'href' => '/web-admin/upload/index', 'type' => 1, 'weight' => 100],
        ['title' => '操作日志', 'key' => \plugin\admin\app\controller\OperationlogController::class, 'href' => '/web-admin/operationlog/index', 'type' => 1, 'weight' => 99],
        ['title' => '后台外观', 'key' => \plugin\admin\app\controller\ConfigController::class, 'href' => '/web-admin/config/index', 'type' => 1, 'weight' => 98],
        ['title' => '个人资料', 'key' => \plugin\admin\app\controller\AccountController::class, 'href' => '/web-admin/account/index', 'type' => 1, 'weight' => 97],
    ]],
    ['title' => '权限管理', 'key' => 'auth', 'icon' => 'layui-icon-vercode', 'type' => 0, 'weight' => 700, 'children' => [
        ['title' => '账户管理', 'key' => \plugin\admin\app\controller\AdminController::class, 'href' => '/web-admin/admin/index', 'type' => 1, 'weight' => 100],
        ['title' => '角色管理', 'key' => \plugin\admin\app\controller\RoleController::class, 'href' => '/web-admin/role/index', 'type' => 1, 'weight' => 99],
        ['title' => '菜单管理', 'key' => \plugin\admin\app\controller\RuleController::class, 'href' => '/web-admin/rule/index', 'type' => 1, 'weight' => 98],
    ]],
];

return \plugin\admin\app\common\Navigation::menuDefaults($menus);
