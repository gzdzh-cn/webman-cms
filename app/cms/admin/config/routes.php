<?php
/** Frozen legacy HTTP surface; explicit actions include inherited CRUD methods. */
$routes = array (
  'plugin\\admin\\app\\controller\\AccountController' => 
  array (
    'path' => '/web-admin/account',
    'actions' => 
    array (
      0 => 'captcha',
      1 => 'delete',
      2 => 'index',
      3 => 'info',
      4 => 'insert',
      5 => 'login',
      6 => 'logout',
      7 => 'password',
      8 => 'select',
      9 => 'update',
    ),
  ),
  'plugin\\admin\\app\\controller\\AdController' => 
  array (
    'path' => '/web-admin/ad',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\AdminController' => 
  array (
    'path' => '/web-admin/admin',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'insert',
      3 => 'select',
      4 => 'update',
    ),
  ),
  'plugin\\admin\\app\\controller\\AdpositionController' => 
  array (
    'path' => '/web-admin/adposition',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\ArchiveController' => 
  array (
    'path' => '/web-admin/archive',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'getCommonTags',
      2 => 'getContent',
      3 => 'index',
      4 => 'insert',
      5 => 'move',
      6 => 'select',
      7 => 'singlePage',
      8 => 'update',
      9 => 'updateStatus',
      10 => 'view',
    ),
  ),
  'plugin\\admin\\app\\controller\\ArctypeController' => 
  array (
    'path' => '/web-admin/arctype',
    'actions' => 
    array (
      0 => 'advanced',
      1 => 'base',
      2 => 'delete',
      3 => 'getTemplates',
      4 => 'index',
      5 => 'insert',
      6 => 'select',
      7 => 'update',
    ),
  ),
  'plugin\\admin\\app\\controller\\ChannelfieldController' => 
  array (
    'path' => '/web-admin/channelfield',
    'actions' => 
    array (
      0 => 'ajaxGetRegionData',
      1 => 'categoryFields',
      2 => 'delete',
      3 => 'getArctypeList',
      4 => 'getFieldTypeList',
      5 => 'index',
      6 => 'insert',
      7 => 'select',
      8 => 'tagCall',
      9 => 'update',
    ),
  ),
  'plugin\\admin\\app\\controller\\ChanneltypeController' => 
  array (
    'path' => '/web-admin/channeltype',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'insert',
      3 => 'select',
      4 => 'update',
    ),
  ),
  'plugin\\admin\\app\\controller\\CitysiteController' => 
  array (
    'path' => '/web-admin/citysite',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'saveSettings',
      4 => 'select',
      5 => 'settings',
    ),
  ),
  'plugin\\admin\\app\\controller\\CmsDataController' => 
  array (
    'path' => '/web-admin/cmsdata',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\ConfigController' => 
  array (
    'path' => '/web-admin/config',
    'actions' => 
    array (
      0 => 'get',
      1 => 'index',
      2 => 'update',
    ),
  ),
  'plugin\\admin\\app\\controller\\DevController' => 
  array (
    'path' => '/web-admin/dev',
    'actions' => 
    array (
      0 => 'formBuild',
    ),
  ),
  'plugin\\admin\\app\\controller\\DictController' => 
  array (
    'path' => '/web-admin/dict',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'get',
      2 => 'index',
      3 => 'insert',
      4 => 'select',
      5 => 'update',
    ),
  ),
  'plugin\\admin\\app\\controller\\DownloadfileController' => 
  array (
    'path' => '/web-admin/downloadfile',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\EmailtemplateController' => 
  array (
    'path' => '/web-admin/emailtemplate',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\FaqController' => 
  array (
    'path' => '/web-admin/faq',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'detail',
      2 => 'index',
      3 => 'save',
      4 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\FilemanagerController' => 
  array (
    'path' => '/web-admin/filemanager',
    'actions' => 
    array (
      0 => 'activate',
      1 => 'edit',
      2 => 'index',
      3 => 'newfile',
      4 => 'preview',
      5 => 'read',
      6 => 'replace_img',
      7 => 'select',
      8 => 'themes',
    ),
  ),
  'plugin\\admin\\app\\controller\\FormController' => 
  array (
    'path' => '/web-admin/form',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\FormfieldController' => 
  array (
    'path' => '/web-admin/formfield',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\GeneratedModuleController' => 
  array (
    'path' => '/web-admin/generatedmodule',
    'actions' => 
    array (
    ),
  ),
  'plugin\\admin\\app\\controller\\GlobalvariableController' => 
  array (
    'path' => '/web-admin/globalvariable',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'groupDelete',
      2 => 'groupSave',
      3 => 'groups',
      4 => 'index',
      5 => 'save',
      6 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\GuestbookController' => 
  array (
    'path' => '/web-admin/guestbook',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\IndexController' => 
  array (
    'path' => '/web-admin/index',
    'actions' => 
    array (
      0 => 'dashboard',
      1 => 'featuremap',
      2 => 'index',
      'serverInfo',
      'restartInfo', 'restartStart', 'restartStatus',
    ),
  ),
  'plugin\\admin\\app\\controller\\InstallController' => 
  array (
    'path' => '/web-admin/install',
    'actions' => 
    array (
      0 => 'step1',
      1 => 'step2',
    ),
  ),
  'plugin\\admin\\app\\controller\\LanguageController' => 
  array (
    'path' => '/web-admin/language',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'reset',
      3 => 'save',
      4 => 'select',
      5 => 'sync',
      'state',
      'toggle',
      'switch',
      'settings',
      'preview',
      'jobs',
      'associations',
      'associate',
      'restore',
    ),
  ),
  'plugin\\admin\\app\\controller\\LanguagepackController' => 
  array (
    'path' => '/web-admin/languagepack',
    'actions' => 
    array (
      0 => 'batch',
      1 => 'delete',
      2 => 'import',
      3 => 'index',
      4 => 'remoteCheck',
      5 => 'remoteList',
      6 => 'remoteSingle',
      7 => 'save',
      8 => 'select',
      'preview',
      'export',
      'supplement',
      'rebuild',
    ),
  ),
  'plugin\\admin\\app\\controller\\LinksController' => 
  array (
    'path' => '/web-admin/links',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\LinksgroupController' => 
  array (
    'path' => '/web-admin/linksgroup',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\MediafileController' => 
  array (
    'path' => '/web-admin/mediafile',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\NavigationController' => 
  array (
    'path' => '/web-admin/navigation',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\NavigationpositionController' => 
  array (
    'path' => '/web-admin/navigationposition',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\OperationlogController' => 
  array (
    'path' => '/web-admin/operationlog',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\PluginController' => 
  array (
    'path' => '/web-admin/plugin',
    'actions' => 
    array (
      'index', 'select', 'detail', 'add', 'save', 'enable', 'disable', 'delete', 'legacyPreview', 'legacyImport',
    ),
  ),
  'plugin\\admin\\app\\controller\\ProductparameterController' => 
  array (
    'path' => '/web-admin/productparameter',
    'actions' => 
    array (
      0 => 'index',
      1 => 'metadata',
      2 => 'product',
      3 => 'purge',
      4 => 'recycle',
      5 => 'restore',
      6 => 'saveDefinition',
      7 => 'saveGroup',
      8 => 'saveProduct',
    ),
  ),
  'plugin\\admin\\app\\controller\\RecycleController' => 
  array (
    'path' => '/web-admin/recycle',
    'actions' => 
    array (
      0 => 'index',
      1 => 'purge',
      2 => 'restore',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\RoleController' => 
  array (
    'path' => '/web-admin/role',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'insert',
      3 => 'rules',
      4 => 'select',
      5 => 'update',
      'languageScope',
    ),
  ),
  'plugin\\admin\\app\\controller\\RuleController' => 
  array (
    'path' => '/web-admin/rule',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'get',
      2 => 'index',
      3 => 'insert',
      4 => 'permission',
      5 => 'select',
      6 => 'update',
    ),
  ),
  'plugin\\admin\\app\\controller\\SearchwordController' => 
  array (
    'path' => '/web-admin/searchword',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\SeoController' => 
  array (
    'path' => '/web-admin/seo',
    'actions' => 
    array (
      0 => 'index',
      1 => 'save',
      2 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\SitemapController' => 
  array (
    'path' => '/web-admin/sitemap',
    'actions' => 
    array (
      5 => 'baiduPreview',
      6 => 'baiduSave',
      7 => 'baiduPush',
      8 => 'baiduSchedule',
      0 => 'clean',
      1 => 'generate',
      2 => 'index',
      3 => 'save',
      4 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\SmsconfigController' => 
  array (
    'path' => '/web-admin/smsconfig',
    'actions' => 
    array (
      0 => 'index',
      1 => 'save',
      2 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\SmslogController' => 
  array (
    'path' => '/web-admin/smslog',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\SmstemplateController' => 
  array (
    'path' => '/web-admin/smstemplate',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\SpecialnodeController' => 
  array (
    'path' => '/web-admin/specialnode',
    'actions' => 
    array (
      0 => 'choices',
      1 => 'delete',
      2 => 'index',
      3 => 'save',
      4 => 'select',
      5 => 'topics',
    ),
  ),
  'plugin\\admin\\app\\controller\\StaticgenerateController' => 
  array (
    'path' => '/web-admin/staticgenerate',
    'actions' => 
    array (
      0 => 'clean',
      1 => 'index',
      2 => 'select',
      3 => 'start',
      4 => 'status',
      5 => 'step',
    ),
  ),
  'plugin\\admin\\app\\controller\\TableController' => 
  array (
    'path' => '/web-admin/table',
    'actions' => 
    array (
      0 => 'create',
      1 => 'crud',
      2 => 'delete',
      3 => 'drop',
      4 => 'index',
      5 => 'insert',
      6 => 'modify',
      7 => 'schema',
      8 => 'select',
      9 => 'show',
      10 => 'types',
      11 => 'update',
      12 => 'view',
    ),
  ),
  'plugin\\admin\\app\\controller\\TagsController' => 
  array (
    'path' => '/web-admin/tags',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\UisetController' => 
  array (
    'path' => '/web-admin/uiset',
    'actions' => 
    array (
      0 => 'clear',
      1 => 'index',
      2 => 'save',
      3 => 'select',
    ),
  ),
  'plugin\\admin\\app\\controller\\UploadController' => 
  array (
    'path' => '/web-admin/upload',
    'actions' => 
    array (
      0 => 'attachment',
      1 => 'avatar',
      2 => 'delete',
      3 => 'file',
      4 => 'image',
      5 => 'index',
      6 => 'insert',
      7 => 'select',
      8 => 'update',
    ),
  ),
  'plugin\\admin\\app\\controller\\UserController' => 
  array (
    'path' => '/web-admin/user',
    'actions' => 
    array (
      0 => 'delete',
      1 => 'index',
      2 => 'insert',
      3 => 'select',
      4 => 'update',
    ),
  ),
  'plugin\\admin\\app\\controller\\WebsiteconfigController' => 
  array (
    'path' => '/web-admin/websiteconfig',
    'actions' => 
    array (
      0 => 'configure',
      1 => 'delete',
      2 => 'index',
      3 => 'save',
      4 => 'select',
      5 => 'settings',
    ),
  ),
);

foreach (["TagsController"=>["tools","batchPreview","batchRun","relations","saveRelations"],"SearchwordController"=>["tools","batchPreview","batchRun","searchSettings","saveSearchSettings","export"],"GuestbookController"=>["tools","export"],"CitysiteController"=>["migrationPreview","migrateSettings"],"ArchiveController"=>["batch","rebackdraft","resubmit","copyPlan","copy"],"ArctypeController"=>["batch","batchPreview","batchRun","batchStatus"]] as $short=>$actions) { $key="plugin\\admin\\app\\controller\\".$short; $routes[$key]["actions"]=array_values(array_unique(array_merge($routes[$key]["actions"],$actions))); }
$routes['plugin\\admin\\app\\controller\\DatabasebackupController']=['path'=>'/web-admin/databasebackup','actions'=>['index','select','create','preview','restore','status','tasks','cleanupPreview','cleanup']];
$routes['plugin\\admin\\app\\controller\\RobotsController']=['path'=>'/web-admin/robots','actions'=>['index','select','save','revisions','restore']];
$routes['plugin\\admin\\app\\controller\\UpdateController']=['path'=>'/web-admin/update','actions'=>['check','start','status','source']];
return $routes;
