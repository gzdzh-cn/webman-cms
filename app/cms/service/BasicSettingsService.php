<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Enterprise settings retain System/web, web2, basic and smtp configuration keys. */
class BasicSettingsService
{
    public const IMAGE_EXTENSIONS = ['jpg','jpeg','gif','bmp','ico','png','webp','avif'];
    public const MEDIA_EXTENSIONS = ['mp3','wav','ogg','mp4','webm','mov'];
    public const DOCUMENT_EXTENSIONS = ['pdf','txt','csv','docx','xlsx','pptx'];
    public const DEFAULT_FILES = 'pdf|txt|csv|docx|xlsx|pptx';

    public function __construct(protected ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }

    /** Field group is the original inc_type, distinct from its presentation section. */
    public static function definitions(): array
    {
        $flag = [['value'=>'0','label'=>'关闭'],['value'=>'1','label'=>'开启']];
        $field = static fn(string $name,string $label,string $type='text',string $default='',string $group='web',bool $global=false,array $extra=[]):array => compact('name','label','type','default','group','global')+$extra;
        return [
            ['id'=>'web','title'=>'网站信息','fields'=>[
                $field('web_status','站点状态','radio','0','web',false,['options'=>[['value'=>'0','label'=>'开启'],['value'=>'1','label'=>'关闭']]]),
                $field('web_status_mode','关闭展示方式','radio','0','web',false,['options'=>[['value'=>'0','label'=>'文案'],['value'=>'1','label'=>'跳转链接'],['value'=>'2','label'=>'本地HTML']]]),
                $field('web_status_text','关闭提示','textarea','网站暂时关闭，维护中……'),
                $field('web_status_url','关闭跳转链接','text',''),
                $field('web_status_tpl','关闭HTML文件','text','public/close.html','web',false,['help'=>'项目内已有的HTML/HTM静态文件；留空使用默认关闭页面，不执行PHP。']),
                $field('web_name','网站名称'),$field('web_logo','电脑端LOGO','image'),$field('web_mobile_logo','手机端LOGO','image'),
                $field('web_ico','地址栏图标','image','','web',false,['help'=>'ICO图片地址，模板通过原web_ico变量读取。']),
                $field('web_basehost','网站根网址','text','','web',false,['help'=>'填写网站公开根网址；不填协议时使用https，去除末尾斜线。']),
                $field('web_copyright','版权信息','textarea'),$field('web_recordnum','ICP备案号'),$field('web_garecordnum','公安备案号'),
                $field('web_title','网站标题'),$field('web_keywords','网站关键词'),$field('web_description','网站描述','textarea'),
                $field('web_thirdcode_pc','电脑端第三方代码','textarea','','web',false,['help'=>'仅由有保存权限的管理员填写，模板按原变量输出。']),
                $field('web_thirdcode_wap','手机端第三方代码','textarea'),
            ]],
            ['id'=>'core','title'=>'核心设置','fields'=>[
                $field('web_cmsmode','网站模式','radio','1','web',true,['options'=>[['value'=>'1','label'=>'运营模式'],['value'=>'2','label'=>'开发模式'],['value'=>'3','label'=>'Redis 缓存模式']],'help'=>'保存时读取最新 .env 和 config/cms_cache.php 并验证 Redis 连接和读写能力，失败不切换。优先级为 .env 的 CMS_REDIS_*、进程环境、PHP 配置；修改文件参数后可直接保存，无需重启服务。切入或切出时需要重新登录，不会切换 SEO URL 模式。']),
                $field(ErrorDisplaySettings::KEY,'前台错误显示','select','0','seo',true,['options'=>[['value'=>'0','label'=>'关闭，显示友好错误页面'],['value'=>'1','label'=>'开启，显示原生异常详情']],'help'=>'保存后立即生效，无需重启。原生异常会显示文件路径、SQL语句和调用堆栈，仅在排查问题时开启，完成后请关闭。']),
                $field('web_is_https','HTTPS链接','radio','0','web',true,['options'=>$flag]),
                $field('web_mobile_domain_open','手机站域名','radio','0','web',true,['options'=>$flag]),
                $field('web_mobile_domain','手机站子域名','text','m','web',true,['help'=>'填写单个子域名前缀，如m；不能与主站子域名相同。']),
                $field('other_pcwapjs','终端自动跳转','radio','0','other',true,['options'=>[['value'=>'0','label'=>'自动跳转'],['value'=>'1','label'=>'关闭跳转']]]),
                $field('absolute_path_open','绝对链接','radio','0','web',true,['options'=>$flag]),
                $field('web_stypeid_open','副栏目','radio','0','web',true,['options'=>$flag]),
                $field('web_recycle_switch','回收站','readonly','启用','web',true,['readOnly'=>true,'help'=>'当前文档和栏目统一先进入回收站，彻底删除在回收站单独操作。']),
                $field('cms_template_mode','模板标签模式','readonly','','web',true,['readOnly'=>true,'help'=>'安装时确定；如需更改，请通过重新安装选择。']),
                $field('web_tpl_theme','当前模板','readonly','','web',true,['readOnly'=>true,'help'=>'通过模板管理校验并切换。']),
                $field('web_cmspath','安装目录','readonly','','web',true,['readOnly'=>true,'help'=>'由部署环境CMS_BASE_PATH控制。']),
            ]],
            ['id'=>'attachment','title'=>'附件扩展','fields'=>[
                $field('basic_indexname','面包屑首页名','text','首页','basic'),$field('list_symbol','面包屑间隔符','text','>','basic',true),
                $field('image_type','上传图片格式','text',implode('|',self::IMAGE_EXTENSIONS),'basic',true,['help'=>'用|分隔；可选 jpg、jpeg、gif、bmp、ico、png、webp、avif，需与图片内容一致。']),
                $field('file_type','上传文档格式','text',self::DEFAULT_FILES,'basic',true,['help'=>'用|分隔；仅支持 pdf、txt、csv、docx、xlsx、pptx。历史配置中的其他格式不生效，请保存前移除。']),
                $field('media_type','上传音视频格式','text','mp3|wav|ogg|mp4|webm|mov','basic',true,['help'=>'用|分隔；仅支持 mp3、wav、ogg、mp4、webm、mov。历史配置中的其他格式不生效，请保存前移除。']),
                $field('file_size','单个附件上限(MB)','number','50','basic',true,['min'=>1,'max'=>1024,'help'=>'适用于后台附件及使用该上传接口的编辑器；前台表单遵循表单字段规则，还受HTTP服务器接收上限约束。']),
                $field('file_uploadname_open','附件名称','readonly','随机重命名','basic',true,['readOnly'=>true,'help'=>'出于安全考虑，所有新上传文件均使用随机名称；原文件名仅作为显示信息保存。']),
                $field('basic_img_auto_wh','内容图片移动端自适应','radio','1','basic',true,['options'=>$flag]),
                $field('basic_img_title','内容图片追加title','radio','1','basic',true,['options'=>$flag]),
                $field('basic_img_alt','内容图片追加alt','radio','1','basic',true,['options'=>$flag]),
                $field('basic_img_alt_force','alt强制同步title','radio','0','basic',true,['options'=>$flag]),
                $field('other_arcclick','文档默认点击数','text','500|1000','other',true,['help'=>'非负整数或最小值|最大值；仅初始化未显式提供点击数的新文档。']),
                $field('other_arcdownload','文件默认下载数','text','100|500','other',true,['help'=>'非负整数或最小值|最大值；仅初始化新下载附件。']),
                $field('current_editor','内容编辑器','readonly','TinyMCE','editor',true,['readOnly'=>true,'help'=>'当前后台使用TinyMCE；未接入原Ueditor/CKEditor切换和地图插件。']),
                $field('editor_remote_img_local','远程图片与外链处理','readonly','保留当前内容','editor',true,['readOnly'=>true,'help'=>'未启用自动下载远程图片、清除外链及外链白名单处理；原配置保留，不显示无效开关。']),
            ]],
            ['id'=>'image','title'=>'图片设置','help'=>'水印仅处理本次新上传的图片，不批量修改已有文件或头像。静态上传仍先遵循原1170×1170等比缩小上限；动图保留原文件。模板缩略图参数优先于以下默认值，配置变化后生成新版本，不删除旧图。','fields'=>[
                $field('is_mark','新上传图片水印','radio','0','water',true,['options'=>$flag]),
                $field('mark_type','水印类型','select','text','water',true,['options'=>[['value'=>'text','label'=>'文字水印'],['value'=>'img','label'=>'图片水印']]]),
                $field('mark_width','水印最小图片宽度(px)','number','300','water',true,['min'=>0,'max'=>10000]),
                $field('mark_height','水印最小图片高度(px)','number','300','water',true,['min'=>0,'max'=>10000,'help'=>'缩小后的宽度和高度均超过阈值时才添加水印；动画图片保留原文件，不添加水印。']),
                $field('mark_txt','水印文字','text','','water',true),
                $field('mark_img','本地水印图片','image','','water',true,['help'=>'选择已有上传图片；只允许本站uploads内PNG/JPEG/WebP/GIF图，不下载外链。透明底原图使用文字水印；图片水印保留其透明图，不强行合成。']),
                $field('mark_txt_size','水印字号','number','30','water',true,['min'=>6,'max'=>160]),
                $field('mark_txt_color','水印文字颜色','text','#000000','water',true,['help'=>'填写六位颜色，如#000000。']),
                $field('mark_degree','水印不透明度(%)','number','80','water',true,['min'=>0,'max'=>100]),
                $field('mark_quality','水印JPEG质量','number','90','water',true,['min'=>1,'max'=>100]),
                $field('mark_sel','水印位置','select','9','water',true,['options'=>array_map(static fn($value,$label)=>compact('value','label'),['1','2','3','4','5','6','7','8','9'],['左上','上中','右上','左中','居中','右中','左下','下中','右下'])]),
                $field('is_thumb_mark','模板缩略图水印','radio','0','water',true,['options'=>$flag,'help'=>'仅影响之后生成的缩略图；不修改已有原图。']),
                $field('thumb_open','自动生成模板缩略图','radio','0','thumb',true,['options'=>$flag]),
                $field('thumb_width','默认缩略图宽度(px)','number','300','thumb',true,['min'=>0,'max'=>4096]),
                $field('thumb_height','默认缩略图高度(px)','number','300','thumb',true,['min'=>0,'max'=>4096,'help'=>'单边可填0以使用原图尺寸；模板显式指定的宽高优先。']),
                $field('thumb_mode','缩略图处理方式','select','2','thumb',true,['options'=>[['value'=>'1','label'=>'固定尺寸拉伸'],['value'=>'2','label'=>'等比留白'],['value'=>'3','label'=>'居中裁剪']]]),
                $field('thumb_color','缩略图留白颜色','text','#ffffff','thumb',true),
            ]],
            ['id'=>'api','title'=>'接口API','help'=>'企业表单邮件、短信及地图接口。支付、会员与小程序业务不在企业官网范围内。','fields'=>[
                $field('smtp_server','SMTP服务器','text','','smtp',true),$field('smtp_port','SMTP端口','number','25','smtp',true,['min'=>1,'max'=>65535]),
                $field('smtp_user','SMTP账号/发件邮箱','text','','smtp',true),
                $field('smtp_pwd','SMTP密码/授权码','password','','smtp',true,['help'=>'留空保留已保存密钥。接口不会回显密钥。']),
                $field('smtp_from_eamil','留言通知收件邮箱','text','','smtp',true,['help'=>'多个邮箱用逗号分隔；通知场景开关沿用邮件模板管理。']),
            ]],
        ];
    }

    public function read(array $input=[]): array
    {
        $scope=$this->scope($input);return $this->presentation($scope,$this->records(array_column($scope['languages'],'mark')));
    }

    public function save(array $input): array
    {
        $selected=$this->scope($input);$id=$input['group']??null;
        if(!is_string($id)||!in_array($id,['web','core','attachment','image','api'],true))throw new InvalidArgumentException('请选择有效的设置分组');
        if($id==='core' && array_key_exists('web_cmsmode',$input)){if(!is_scalar($input['web_cmsmode']))throw new InvalidArgumentException('网站模式无效');\app\cms\cache\CachePolicy::prepareTransition((string)$input['web_cmsmode']);}
        if($id==='image')foreach(['language','config']as$table){$engine=$this->repository->connection()->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?',[$this->repository->fullName($table)]);if(strtolower((string)($engine[0]['ENGINE']??''))!=='innodb')throw new InvalidArgumentException('图片配置保存要求相关表使用InnoDB：'.$table);}
        return $this->configurationChange(fn():array=>$this->repository->connection()->transaction(function()use($selected,$input,$id):array{
            $scope=$this->scope(['lang'=>$selected['lang']],true);
            if($scope['id']!==$selected['id']||$scope['main_id']!==$selected['main_id'])throw new InvalidArgumentException('语言已删除或重新创建，请刷新后重试');
            $records=$this->records(array_column($scope['languages'],'mark'),true);$definitions=array_column(self::definitions(),null,'id');$fields=array_column($definitions[$id]['fields'],null,'name');
            $oldMode=\app\cms\cache\CachePolicy::validate($records[$scope['main']]['web_cmsmode']['value']??'1');$newMode=$oldMode;
            $data=$input;unset($data['lang'],$data['group']);
            if($id==='web'&&array_key_exists('web_logo_local',$data)){if(array_key_exists('web_logo',$data))throw new InvalidArgumentException('LOGO参数重复');$data['web_logo']=$data['web_logo_local'];unset($data['web_logo_local']);}
            if(!$data)throw new InvalidArgumentException('没有提交设置字段');
            if(isset($data['web_cmsmode']) && $oldMode!==trim((string)$data['web_cmsmode']))\app\cms\cache\CachePolicy::assertTransactional($this->repository);
            foreach($data as$name=>$value){
                if(!isset($fields[$name])||!empty($fields[$name]['readOnly'])||$fields[$name]['type']==='readonly')throw new InvalidArgumentException('不支持修改的设置字段：'.$name);
                $field=$fields[$name];if($field['global']&&$scope['lang']!==$scope['main'])throw new InvalidArgumentException('共享设置请切换到主语言修改');
                $value=$this->normalize($field,$value);
                if($name==='web_cmsmode')$newMode=$value;
                if($name==='smtp_pwd'&&$value==='')continue;
                (new LanguageAccessService($this->repository))->assertLanguages($field['global']?array_column($scope['languages'],'mark'):[$scope['lang']]);
                foreach($field['global']?$scope['languages']:[$scope['selected']] as$language){
                    $lang=$language['mark'];$old=$records[$lang][$name]??null;
                    if($old&&$this->scalar($old['value'])===$value&&$old['inc_type']===$field['group'])continue;
                    $payload=['value'=>$value,'inc_type'=>$field['group'],'is_del'=>0,'update_time'=>time()];
                    if($old)$this->repository->table('config')->where('id',$old['id'])->update($payload);
                    else $this->repository->table('config')->insert($payload+['name'=>$name,'lang'=>$lang,'desc'=>$field['label']]);
                }
            }
            $next=$this->records(array_column($scope['languages'],'mark'));$this->validateCombined($scope,$next,$id);
            $reset=$oldMode!==$newMode&&($oldMode==='3'||$newMode==='3');
            if($oldMode!==$newMode)\app\cms\cache\CachePolicy::rotate($reset,$this->repository);
            $this->publish();$result=$this->presentation($scope,$next);$result['session_reset_required']=$reset;return $result;
        }));
    }

    protected function configurationChange(callable $operation): array { return (new PublicStaticPublisher())->configurationChange($operation); }
    protected function publish(): void { \app\cms\compat\Cache::clear(); }

    private function scope(array $input,bool $lock=false):array
    {
        $query=$this->repository->table('language')->order('id asc');if($lock)$query->lock(true);$languages=$query->select()->toArray();
        if(!$languages)throw new InvalidArgumentException('请先配置网站语言');$lang=$input['lang']??\app\cms\CmsContext::current()->lang;
        (new LanguageAccessService($this->repository))->assertLanguages([(string)$lang]);
        if(!is_string($lang))throw new InvalidArgumentException('语言不存在');
        foreach($languages as$row)if($row['mark']===$lang)return ['lang'=>$lang,'id'=>(int)$row['id'],'selected'=>$row,'main'=>$languages[0]['mark'],'main_id'=>(int)$languages[0]['id'],'languages'=>$languages];
        throw new InvalidArgumentException('语言不存在');
    }
    private function records(array $languages,bool $lock=false):array
    {
        $names=[];foreach(self::definitions() as$section)foreach($section['fields'] as$field)if($field['type']!=='readonly'||$field['name']==='web_tpl_theme')$names[]=$field['name'];
        $query=$this->repository->table('config')->whereIn('lang',$languages)->whereIn('name',$names)->where('is_del',0)->order('id asc');if($lock)$query->lock(true);$records=[];
        foreach($query->select()->toArray() as$row){if(isset($records[$row['lang']][$row['name']]))throw new InvalidArgumentException('存在重复设置：'.$row['name']);$this->scalar($row['value']);$records[$row['lang']][$row['name']]=$row;}return $records;
    }
    private function presentation(array $scope,array $records):array
    {
        $groups=self::definitions();foreach($groups as&$group)foreach($group['fields'] as&$field){$lang=$field['global']?$scope['main']:$scope['lang'];$value=isset($records[$lang][$field['name']])?$this->scalar($records[$lang][$field['name']]['value']):$field['default'];
            if($field['name']==='file_uploadname_open')$value='随机重命名';
            if($field['name']==='cms_template_mode')$value=\app\cms\template\TemplateMode::label();
            if($field['name']==='web_cmspath')$value=rtrim((string)\app\cms\config\SiteSettings::get('base_path'),'/');
            // Legacy separators are HTML entities; input.value expects the actual symbol.
            if($field['name']==='list_symbol')$value=html_entity_decode($value,ENT_QUOTES|ENT_HTML5,'UTF-8');
            $field['readOnly']=!empty($field['readOnly'])||($field['global']&&$scope['lang']!==$scope['main']);$field['readonly']=$field['readOnly'];$field['copy']=$group['id']==='web';
            if($field['copy'])$field['tag']=\app\cms\template\TemplateMode::tag('global',['name'=>$field['name']]);
            if($field['global'])$field['help']=($field['help']??'').' 共享设置，仅主语言可修改并同步到所有已注册语言。';
            if($field['type']==='password'){$field['configured']=$value!=='';$value='';}$field['value']=$value;unset($field['default']);
        }unset($field,$group);return ['lang'=>$scope['lang'],'main_lang'=>$scope['main'],'languages'=>$scope['languages'],'groups'=>$groups];
    }
    private function scalar(mixed $value):string
    {
        if(is_string($value)&&preg_match('/^(a|s|i|b|d|O):|^N;$/',$value)){$decoded=@unserialize($value,['allowed_classes'=>false]);if($decoded!==false||$value==='b:0;')$value=$decoded;}
        if(!is_scalar($value)&&$value!==null)throw new InvalidArgumentException('设置值必须是文本或数字');return (string)$value;
    }
    private function normalize(array $field,mixed $value):string
    {
        if(!is_scalar($value)&&$value!==null)throw new InvalidArgumentException($field['label'].'格式错误');$value=trim((string)$value);$name=$field['name'];
        if(str_contains($value,"\0")||strlen($value)>100000)throw new InvalidArgumentException($field['label'].'内容过长或格式错误');
        if(isset($field['options'])&&!in_array($value,array_column($field['options'],'value'),true))throw new InvalidArgumentException($field['label'].'选项无效');
        if($field['type']==='number'){if(!preg_match('/^\d+$/D',$value)||(int)$value<$field['min']||(int)$value>$field['max'])throw new InvalidArgumentException($field['label'].'超出允许范围');return (string)(int)$value;}
        if(in_array($name,['image_type','file_type','media_type'],true))return self::extensions($value,$name);
        if(in_array($name,['other_arcclick','other_arcdownload'],true))return InitialCounterService::canonicalRange($value);
        if(in_array($name,['mark_txt_color','thumb_color'],true)&&!preg_match('/^#[0-9a-fA-F]{6}$/D',$value))throw new InvalidArgumentException($field['label'].'必须为六位十六进制颜色');
        if($name==='mark_txt'&&mb_strlen($value)>100)throw new InvalidArgumentException('水印文字不能超过100个字符');
        if($name==='mark_img'&&$value!=='')ImageUploadPolicyService::watermarkFile($value);
        if($name==='web_basehost'&&$value!==''){$value=rtrim($value,'/');if(!preg_match('#^https?://#i',$value))$value='https://'.$value;$parts=parse_url($value);if(!$parts||!filter_var($value,FILTER_VALIDATE_URL)||!in_array(strtolower($parts['scheme']??''),['http','https'],true)||isset($parts['user'])||isset($parts['pass'])||isset($parts['query'])||isset($parts['fragment']))throw new InvalidArgumentException('网站根网址格式错误');}
        if($name==='web_status_url'&&$value!==''&&(preg_match('/[\\\\\x00-\x20\x7f]/',$value)||(!preg_match('#^/(?!/)[^\x00-\x20]*$#D',$value)&&!filter_var($value,FILTER_VALIDATE_URL))))throw new InvalidArgumentException('关闭跳转链接格式错误');
        if($name==='web_status_url'&&preg_match('#^[a-z]+:#i',$value)&&!preg_match('#^https?://#i',$value))throw new InvalidArgumentException('关闭跳转链接仅支持HTTP/HTTPS');
        if($name==='web_status_tpl'&&$value!==''){$value=ltrim($value,'/');if(!preg_match('#^[\w/-]+\.html?$#iD',$value)||preg_match('#(?:^|/)\.\.(?:/|$)#',$value))throw new InvalidArgumentException('关闭页面必须是项目内HTML/HTM文件');}
        if($name==='web_mobile_domain'&&$value!==''&&(!preg_match('/^(?!-)[a-z0-9-]{1,63}(?<!-)$/iD',$value)||strtolower($value)==='www'))throw new InvalidArgumentException('手机站子域名格式错误');
        if(in_array($name,['web_logo','web_mobile_logo','web_ico'],true)&&$value!==''&&!preg_match('#^/(?!/)[^\x00-\x20]*$#D',$value)&&!preg_match('#^https?://[^\x00-\x20]+$#iD',$value))throw new InvalidArgumentException('图片地址格式错误');
        if($name==='web_ico'&&$value!==''&&!preg_match('/\.ico(?:[?#]|$)/i',$value))throw new InvalidArgumentException('地址栏图标必须是ICO图片');
        if($name==='web_keywords')$value=str_replace('，',',',$value);
        if($name==='web_description')$value=str_replace(["\r","\n"],'',$value);
        if(str_starts_with($name,'web_')&&!in_array($name,['web_thirdcode_pc','web_thirdcode_wap'],true))$value=preg_replace('#<script\b[^>]*>.*?</script\s*>#is','',$value);
        if($name==='smtp_server'&&$value!==''&&!preg_match('/^(?:[a-z0-9-]+\.)*[a-z0-9-]+$/iD',$value))throw new InvalidArgumentException('SMTP服务器应为域名或IP');
        if($name==='smtp_user'&&$value!==''&&!filter_var($value,FILTER_VALIDATE_EMAIL))throw new InvalidArgumentException('SMTP发件邮箱格式错误');
        if($name==='smtp_from_eamil'){$emails=array_values(array_unique(array_filter(array_map('trim',explode(',',str_replace('，',',',$value))))));foreach($emails as$email)if(!filter_var($email,FILTER_VALIDATE_EMAIL))throw new InvalidArgumentException('收件邮箱格式错误');$value=implode(',',$emails);}
        return $value;
    }
    public static function extensions(string $value,string $name):string
    {
        $extensions=array_values(array_unique(array_filter(array_map(static fn($part)=>strtolower(trim($part)),explode('|',$value)),static fn($part)=>$part!=='')));
        if(!$extensions)throw new InvalidArgumentException('至少保留一种上传格式');foreach($extensions as$ext){
            if(!preg_match('/^[a-z0-9][a-z0-9-]{0,15}$/D',$ext)||preg_match('/^(?:php|phar|asp|jsp|perl|cgi|asa|pht|shtml|htm|js|svg|exe|com|bat|cmd|sh|htaccess|userini)/i',$ext))throw new InvalidArgumentException('不支持上传格式：'.$ext);
            if(($name==='image_type'&&!in_array($ext,self::IMAGE_EXTENSIONS,true))||($name==='file_type'&&!in_array($ext,self::DOCUMENT_EXTENSIONS,true))||($name==='media_type'&&!in_array($ext,self::MEDIA_EXTENSIONS,true)))throw new InvalidArgumentException('不支持上传格式：'.$ext);
        }return implode('|',$extensions);
    }
    private function validateCombined(array $scope,array $records,string $id):void
    {
        $values=[];foreach($records[$scope['lang']]??[] as$name=>$row)$values[$name]=$this->scalar($row['value']);
        if($id==='image')ImageUploadPolicyService::validateSettings($values);
        if($id==='web'&&($values['web_status']??'0')==='1'&&($values['web_status_mode']??'0')==='1'&&($values['web_status_url']??'')==='')throw new InvalidArgumentException('关闭展示选择跳转时请填写链接');
        if($id==='core'&&($values['web_mobile_domain_open']??'0')==='1'){$domain=$values['web_mobile_domain']??'m';if($domain==='')throw new InvalidArgumentException('开启手机站域名时请填写子域名');$host=(string)parse_url($values['web_basehost']??'',PHP_URL_HOST);if($host!==''&&strtolower(explode('.',$host)[0])===strtolower($domain))throw new InvalidArgumentException('手机站域名不能与主站域名相同');}
    }
}
