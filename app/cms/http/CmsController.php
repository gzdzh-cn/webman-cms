<?php
namespace app\cms\http;

use app\cms\AssetResolver;
use app\cms\CmsContext;
use app\cms\CityContext;
use app\cms\Captcha;
use app\cms\DocumentActions;
use app\cms\AjaxActions;
use app\cms\service\FormService;
use app\cms\compat\Runtime;
use app\cms\compat\logic\FieldLogic;
use app\cms\template\TemplateRenderer;
use support\Request;
use support\Response;
use support\think\Db;

/** Front controller for copied EyouCMS templates and their original URLs. */
 class CmsController
{
    private function context(Request $request, string $controller, string $action = 'index', bool $deferCity = false): CmsContext
    {
        $context = CmsContext::current();
        $context->request = $request;
        $context->controller = $controller;
        $context->action = $action;
        $context->parameters = array_replace($request->all(), $context->parameters);
        Runtime::boot($context);
        if ($deferCity) $context->memo['city.defer_common'] = true;
        if (!$context->global) $context->load();
        if ($policy = \app\cms\SitePolicy::response($context)) throw new \app\cms\compat\exception\HttpResponseException($policy);
        if (!$deferCity) {
            unset($context->memo['city.defer_common']);
            CityContext::select($context);
        }
        $context->setConfig('tpl_theme', trim(substr($context->theme, 0, strrpos($context->theme, '/') ?: 0), '/'));
        return $context;
    }

    public function dispatch(Request $request): Response
    {
        $controller = strtolower((string)$request->input('c', 'Index'));
        $action = strtolower((string)$request->input('a', 'index'));
        $module = strtolower((string)$request->input('m', 'home'));
        if ($module === 'user' && $controller === 'users' && $action === 'login') {
            return response('会员中心已关闭，会员及支付功能未提供', 403, ['Content-Type'=>'text/plain; charset=utf-8', 'Cache-Control'=>'no-store']);
        }
        if (!in_array($module, ['home', 'api'], true)) return $this->missing();
        if ($module === 'api' && $controller === 'uiset') return response('可视化编辑功能已关闭', 404);
        if ($module === 'api' && $controller === 'sitemap' && $action === 'ai_content_index') {
            if (!in_array($request->method(), ['GET', 'HEAD'], true)) return response('Method Not Allowed', 405, ['Allow' => 'GET, HEAD']);
            $response = (new \app\cms\service\AiContentIndexService())->live($this->context($request, 'Sitemap', $action));
            return $request->method() === 'HEAD' ? $response->withBody('') : $response;
        }
        if ($module === 'api' || $controller === 'ajax') return $this->ajax($request);
        if ($action === 'gbook_submit') return $this->submit($request);
        if ($controller === 'search') return in_array($action, ['index', 'lists'], true) ? $this->search($request, $action) : $this->missing();
        if ($controller === 'tags') return $this->tags($request);
        if (in_array($action, ['downfile', 'download_file', 'custom_download_file', 'download_media_file'], true)) return $this->download($request);
        if (in_array($action, ['pay_video_url', 'check_auth'], true)) return DocumentActions::video($request, $this->context($request, 'View', $action));
        if ($controller === 'view' && $action === 'play') return $this->play($request);
        if ($action === 'view' && in_array($controller, ['article', 'product', 'images', 'download', 'media', 'special'], true)) {
            $aid = $request->input('aid', 0);
            // Download reads an integer aid before its model, unlike filename-capable models.
            if ($controller === 'download' && !($aid = (int)$aid)) return $this->missing();
            return $this->viewPage($request, $aid, true);
        }
        if ($controller === 'view' || $action === 'view') return $this->view($request, $request->input('aid', 0));
        if ($controller === 'lists' || $action === 'lists') return $this->lists($request);
        if ($controller === 'index' && $action === 'index') return $this->index($request);
        if ($action === 'index' && Db::name('channeltype')->where('ctl_name', $controller)->find()) return $this->lists($request);
        return $this->missing();
    }

    public function index(Request $request): Response
    {
        $context = $this->context($request, 'Index');
        $context->field = ['seo_title' => $context->global['web_title'] ?? '', 'seo_keywords' => $context->global['web_keywords'] ?? '', 'seo_description' => $context->global['web_description'] ?? ''];
        if ($generated = \app\cms\service\StaticGenerationService::response($request, $context)) return $generated;
        $template = !empty($context->memo['cms.static.generating'])
            ? (string)($context->global['seo_html_templet'] ?? 'index.htm') : 'index';
        return $this->render($context, $template);
    }

    public function lists(Request $request, mixed $tid = null): Response
    {
        $context = $this->context($request, 'Lists');
        $identifier = $tid ?? $context->input('param.tid', '');
        $query = Db::name('arctype')->where('is_del', 0)->where('status', 1)->where('lang', $context->lang);
        $row = ctype_digit((string)$identifier) ? $query->where('id', (int)$identifier)->find() : $query->where('dirname', (string)$identifier)->find();
        if (!$row) return $this->missing();
        $context->tid = (int)$row['id'];
        $context->parameters['tid'] = $context->tid;
        if ((int)$row['is_part'] === 1 && !empty($row['typelink'])) return redirect(htmlspecialchars_decode($row['typelink']));
        $field = Runtime::model('Arctype')->getInfo($context->tid);
        if ((int)$row['current_channel'] === 6) {
            $single = Runtime::model('Archives')->readContentFirst($context->tid);
            if ($single) {
                if ((int)$single['arcrank'] === -1) {
                    // Original success() filters Referer using the application defaults
                    // and PHP7's htmlspecialchars flags; an explicit empty value stays empty.
                    $target = \app\cms\compat\Request::instance()->server('HTTP_REFERER', null, [
                        'strip_sql', static fn($value) => htmlspecialchars($value, ENT_COMPAT, 'UTF-8'),
                    ]);
                    if ($target === null) {
                        // Single/lists delegates to Lists without changing the original request MCA.
                        $module = strtolower((string)$request->input('m', 'home'));
                        $controller = \app\cms\routing\Loader::parseName((string)$request->input('c', 'Lists'), 1);
                        $action = strtolower((string)$request->input('a', 'index'));
                        $target = url($module . '/' . $controller . '/' . $action);
                    }
                    return \app\cms\JumpResponse::make($request, $context, ['code' => 1, 'msg' => '待审核稿件，你没有权限阅读！', 'url' => $target, 'wait' => 1]);
                }
                $single = (new FieldLogic())->getChannelFieldList($single, 6);
                $categoryFields = array_intersect_key($field, array_flip(['templist', 'dirpath', 'diy_dirpath', 'typeid', 'rulelist']));
                $field = array_replace($field, $single, $categoryFields);
            }
        }
        $field = (new FieldLogic())->getTableFieldList($field, $context->getConfig('global.arctype_channel_id'));
        $field['has_children'] = Runtime::model('Arctype')->hasChildren($context->tid);
        $field['seo_title'] = set_typeseotitle($field['typename'], $field['seo_title'] ?? '', []);
        $field['seo_keywords'] = site_seo_handle($field['seo_keywords'] ?? '', $context->siteInfo);
        $field['seo_description'] = site_seo_handle($field['seo_description'] ?? '', $context->siteInfo);
        $field['pageurl'] = $field['typeurl'];
        get_list_only_pageurl($field['pageurl'], $context->tid, $row['rulelist']);
        $field['pageurl_m'] = pc_to_mobile_url($field['pageurl'], $context->tid);
        $field['mobile_domain'] = '';
        if (!empty($context->global['web_mobile_domain_open']) && !empty($context->global['web_mobile_domain'])) {
            $field['mobile_domain'] = $context->global['web_mobile_domain'] . '.' . \app\cms\compat\Request::instance()->rootDomain();
        }
        foreach ($field as $key => $value) if (!str_starts_with($key, 'type')) $field['type' . $key] ??= $value;
        $context->field = $field;
        if ($denied = $this->readAccess($context, false)) return $denied;
        if ($generated = \app\cms\service\StaticGenerationService::response($request, $context)) return $generated;
        $template = $row['templist'] ?: 'lists_' . $field['nid'] . '.htm';
        return $this->render($context, $template);
    }

    public function view(Request $request, mixed $id = 0): Response
    {
        return $this->viewPage($request, $id);
    }

    private function viewPage(Request $request, mixed $id = 0, bool $modelEntry = false): Response
    {
        $context = $this->context($request, 'View');
        $id = $id ?: $context->input('param.aid', 0);
        $query = Db::name('archives')->where('is_del', 0)->where('status', 1)->where('arcrank', $modelEntry ? '>=' : '>', -1)->where('lang', $context->lang);
        if (!ctype_digit((string)$id) && $dirname = (string)$context->input('param.dirname/s', '')) {
            $type = Db::name('arctype')->where(['dirname' => $dirname, 'lang' => $context->lang, 'is_del' => 0])->find();
            if (!$type) return $this->missing();
            $types = (int)$context->getConfig('ey_config.seo_rewrite_format') === 1
                ? Db::name('arctype')->where('id|topid', $type['id'])->column('id') : [$type['id']];
            $query->whereIn('typeid', $types);
        }
        $archive = ctype_digit((string)$id) ? $query->where('aid', (int)$id)->find() : $query->where('htmlfilename', (string)$id)->find();
        if (!$archive) return $this->missing();
        if ($modelEntry && (int)$archive['arcrank'] === -1) {
            return \app\cms\JumpResponse::make($request, $context, ['code' => 1, 'msg' => '待审核稿件，你没有权限阅读！', 'url' => ($context->global['web_cmspath'] ?? '') . '/', 'wait' => 1]);
        }
        return $this->renderArchive($request, $context, $archive);
    }

    /** Internal entry from the authenticated, data-scoped backend preview action. */
    public function previewArchive(Request $request, array $archive = []): Response
    {
        // Conventional controller routing must never turn this method into a
        // public draft-preview switch, even when a caller knows the document ID.
        if ($request->plugin !== 'admin'
            || $request->controller !== \plugin\admin\app\controller\ArchiveController::class
            || $request->action !== 'view'
            || !function_exists('admin_id') || !admin_id()
            || !\plugin\admin\api\Auth::canAccess($request->controller, $request->action)) {
            return response('Forbidden', 403);
        }
        if (empty($archive['aid']) || !empty($archive['is_del']) || empty($archive['lang'])) return $this->missing();
        $previous = CmsContext::current();
        $context = new CmsContext($request);
        $context->parameters['lang'] = $archive['lang'];
        CmsContext::set($context);
        try {
            $context = $this->context($request, 'View');
            if ($context->lang !== $archive['lang']) return $this->missing();
            $response = $this->renderArchive($request, $context, $archive);
            return $response->withHeaders(['Cache-Control' => 'private, no-store', 'X-Robots-Tag' => 'noindex, nofollow']);
        } finally {
            CmsContext::set($previous);
        }
    }

    private function renderArchive(Request $request, CmsContext $context, array $archive): Response
    {
        $context->aid = (int)$archive['aid'];
        $context->tid = (int)$archive['typeid'];
        $context->parameters = array_replace($context->parameters, ['tid' => $context->tid, 'aid' => $context->aid]);
        if ($archive['is_jump'] && $archive['jumplinks']) return redirect(htmlspecialchars_decode($archive['jumplinks']));
        $type = Runtime::model('Arctype')->getInfo($context->tid);
        if (!$type) return $this->missing();
        $type = (new FieldLogic())->getTableFieldList($type, $context->getConfig('global.arctype_channel_id'));
        $type['has_children'] = Runtime::model('Arctype')->hasChildren($context->tid);
        foreach ($type as $key => $value) if (!str_starts_with($key, 'type')) $type['type' . $key] ??= $value;
        $model = Db::name('channeltype')->where('id', $archive['channel'])->find();
        if (!$model || in_array($model['nid'], ['single', 'guestbook', 'ask'], true)) return $this->missing();
        $document = Runtime::model($model['ctl_name'])->getInfo($context->aid);
        $field = array_replace($type, $document ?: $archive);
        // Original View exposes the inherited template through field variables too.
        if (empty($field['tempview'])) $field['tempview'] = $type['tempview'];
        $field['arcurl'] = arcurl('home/' . $model['ctl_name'] . '/view', $field, true, true);
        $field['pageurl'] = $field['arcurl'];
        $field['pageurl_m'] = pc_to_mobile_url($field['pageurl'], $context->tid, $context->aid);
        $field['mobile_domain'] = '';
        if (!empty($context->global['web_mobile_domain_open']) && !empty($context->global['web_mobile_domain'])) {
            $field['mobile_domain'] = $context->global['web_mobile_domain'] . '.' . \app\cms\compat\Request::instance()->rootDomain();
        }
        $field['seo_title'] = set_arcseotitle($field['title'], $field['seo_title'] ?? '', $type['typename'], $context->tid, []);
        $field['seo_description'] = checkStrHtml($field['seo_description'] ?? '');
        $field['tags'] = $field['tags']['tag_arr'] ?? ($field['tags'] ?? '');
        $field['litpic'] = handle_subdir_pic($field['litpic']);
        $field = view_logic($context->aid, $archive['channel'], $field, true);
        $field = (new FieldLogic())->getChannelFieldList($field, $archive['channel']);
        if ($context->device === 'mobile' && !empty($field['content_ey_m'])) $field['content'] = $field['content_ey_m'];
        if (isset($field['content']) && !empty($field['editor_ai_create'])) {
            $field['content'] .= '<div class="ai-statement">此内容由AI生成</div>';
        }
        $context->field = $field;
        if ($denied = $this->readAccess($context, true)) return $denied;
        if ($generated = \app\cms\service\StaticGenerationService::response($request, $context)) return $generated;
        $template = $field['tempview'] ?: 'view_' . $model['nid'] . '.htm';
        return $this->render($context, $template, ['eyou' => array_replace($context->variables()['eyou'], ['type' => $type])]);
    }

    /** Original guest restrictions still apply when membership is disabled. */
    private function readAccess(CmsContext $context, bool $detail): ?Response
    {
        $field = $context->field;
        $limits = empty($field['page_limit']) ? [] : explode(',', (string)$field['page_limit']);
        $restricted = ($field['typearcrank'] ?? 0) > 0 && in_array($detail ? 2 : 1, $limits);
        if ($detail && ($field['arcrank'] ?? 0) > 0) $restricted = true;
        if (!$restricted) return null;
        $url = url('user/Users/login');
        // Ordinary Lists fields have no arcurl in the original default schema.
        return redirect($url . (stristr($url, '?') ? '&' : '?') . 'referurl=' . urlencode((string)($field['arcurl'] ?? '')));
    }

    public function search(Request $request, string $action = 'lists'): Response
    {
        $context = $this->context($request, 'Search', $action);
        $result = \app\cms\SearchActions::prepare($context, $action);
        if ($result['kind'] === 'abort') return $this->missing();
        if ($result['kind'] === 'error') return \app\cms\JumpResponse::make($request, $context, $result['result']);
        $context->field = $result['field'];
        // Search already applies the original action-specific variant priority.
        return $this->render($context, $result['template'], [], false);
    }

    public function tags(Request $request, int $id = 0): Response
    {
        $context = $this->context($request, 'Tags', $id || $request->input('tagid') || $request->input('tag') ? 'lists' : 'index');
        if ($context->action === 'index') {
            $context->field = ['seo_title' => '标签页_' . ($context->global['web_name'] ?? ''), 'seo_keywords' => '', 'seo_description' => '', 'pageurl' => tagurl('home/Tags/index')];
            return $this->render($context, 'index_tags');
        }
        $id = $id ?: (int)$context->input('param.tagid/d', 0);
        $query = Db::name('tagindex')->where('lang', $context->lang);
        $row = $id ? $query->where('id', $id)->find() : $query->where('tag', $context->input('param.tag/s', ''))->find();
        if (!$row) return $this->missing();
        $context->parameters['tagid'] = (int)$row['id'];
        $row['tagid'] = $row['id'];
        $row['seo_title'] = set_tagseotitle($row['tag'], $row['seo_title'] ?? '');
        $row['pageurl'] = tagurl('home/Tags/lists', ['tagid' => $row['id']]);
        $context->field = $row;
        return $this->render($context, 'lists_tags');
    }

    public function asset(Request $request): Response
    {
        $file = AssetResolver::resolve($request->path());
        return $file ? \app\cms\FileResponse::make($request, $file) : $this->missing();
    }

    public function resolve(Request $request): Response
    {
        if ($file = AssetResolver::resolve($request->path())) return \app\cms\FileResponse::make($request, $file);
        // Eyou chooses language before Route::check adds path parameters.
        // Load against the original query before pathInfo can merge lang.
        $context = $this->context($request, 'Lists', 'index', true);
        $path = $context->routingPath();
        if ($path === '') return $this->dispatch($request);
        if ($response = $this->pathInfo($request, $path)) return $response;
        $routePath = $path;
        $languagePath = preg_replace('#^m/#', '', $routePath);
        $first = explode('/', $languagePath)[0];
        $languageQuery = Db::name('language')->where('mark', $first);
        if (!$context->getConfig('lang_switch_on')) $languageQuery->where('status', 1);
        if ($first !== '' && $languageQuery->find()) {
            if (trim(substr($languagePath, strlen($first)), '/') === '') {
                // A registered mark can also be a category directory. When a
                // query selects the default language, upstream may register
                // <tid> instead of <lang>/$; honor that actual rule first.
                $parameters = \app\cms\routing\Route::match($routePath . '/')
                    ?? \app\cms\routing\Route::match($routePath);
                if ($parameters) {
                    $context->parameters = array_replace($context->parameters, $parameters);
                    $request->setGet(array_replace($request->get(), $parameters));
                    return $this->dispatch($request);
                }
                return $this->index($request);
            }
        }
        if ($context->getConfig('city_switch_on')) {
            // routingPath canonicalizes the terminal slash; upstream's
            // explicit <site>/$ homepage rule still needs that slash to match.
            $parameters = \app\cms\routing\Route::match($routePath . '/')
                ?? \app\cms\routing\Route::match($routePath);
            if ($parameters) {
                // An existing category owns the original generic <tid> rule.
                // Only an otherwise missing city-only category may continue to
                // the documented convenience city-prefix extension below.
                $cityOnlyExtension = strtolower($parameters['c'] ?? '') === 'lists'
                    && !isset($parameters['site']) && isset($parameters['tid'])
                    && Db::name('citysite')->where('domain', $routePath)->find()
                    && !Db::name('arctype')->where(['dirname' => $parameters['tid'], 'lang' => $context->lang, 'is_del' => 0, 'status' => 1])->find();
                if (!$cityOnlyExtension) {
                    $context->parameters = array_replace($context->parameters, $parameters);
                    $request->setGet(array_replace($request->get(), $parameters));
                    return $this->dispatch($request);
                }
            }
        }
        if ($context->getConfig('city_switch_on')) {
            $cityPath = preg_replace('#^m/#', '', $routePath);
            $city = explode('/', $cityPath)[0];
            if ($city !== '' && Db::name('citysite')->where('domain', $city)->find()) {
                // Extension for city directory URLs not reached by the actual
                // upstream registry (e.g. dynamic SEO mode). Real matches above
                // always retain upstream route > query selection semantics.
                $context->memo['routing.extension'] = 'city-prefix';
                if (empty($context->memo['city.request_site']['explicit'])) {
                    $context->parameters['site'] = $city;
                    $request->setGet(array_replace($request->get(), ['site' => $city]));
                }
                if ($cityPath !== $routePath) {
                    $context->parameters['goto'] = 'mobile';
                    $request->setGet(array_replace($request->get(), ['goto' => 'mobile']));
                    $context->load();
                }
                $routePath = $path = trim(substr($cityPath, strlen($city)), '/');
                if ($path === '') return $this->index($request);
                if ($response = $this->pathInfo($request, $path)) return $response;
            }
        }
        if ($mapResponse = \app\cms\service\SitemapService::response($request, $context)) return $mapResponse;
        if ($generated = \app\cms\service\StaticGenerationService::response($request, $context)) return $generated;
        if (preg_match('/^sitemap(?:_([A-Za-z0-9_-]+))?\.(xml|txt|html)$/', $routePath, $map)) {
            if ($map[1] !== '' && $map[1] !== $context->lang) {
                $context->parameters['lang'] = $map[1];
                $context->load();
            }
            return \app\cms\Sitemap::response($context, $map[2]);
        }
        if ($parameters = \app\cms\StaticRoutes::match($routePath, $context)) {
            $context->parameters = array_replace($context->parameters, $parameters);
            $request->setGet(array_replace($request->get(), $parameters));
            return $this->dispatch($request);
        }
        if ($parameters = \app\cms\routing\Route::match($routePath)) {
            $context->parameters = array_replace($context->parameters, $parameters);
            $request->setGet(array_replace($request->get(), $parameters));
            return $this->dispatch($request);
        }
        if (preg_match('#^(?:search|sindex)(?:\.html)?/?$#', $path)) return $this->search($request);
        if (preg_match('#^tags(?:/(\d+)(?:_(\d+))?\.html)?/?$#', $path, $m)) {
            if (!empty($m[2])) $context->parameters['page'] = (int)$m[2];
            return $this->tags($request, (int)($m[1] ?? 0));
        }
        if (preg_match('#(?:^|/)(?:list|lists)_(\d+)(?:_(\d+))?(?:\.html|/)?$#', $path, $m)) {
            if (!empty($m[2])) $context->parameters['page'] = (int)$m[2];
            return $this->lists($request, $m[1]);
        }
        $last = basename($path);
        if (preg_match('/^(.+)\.html?$/', $last, $m)) {
            if (ctype_digit($m[1])) return $this->view($request, $m[1]);
            $category = Db::name('arctype')->where('dirname', $m[1])->where('lang', $context->lang)->find();
            if ($category) return $this->lists($request, $category['id']);
            return $this->view($request, $m[1]);
        }
        if ($last !== '') return $this->lists($request, $last);
        return $this->missing();
    }

    public function ajax(Request $request): Response
    {
        $context = $this->context($request, 'Ajax', (string)$request->input('a', ''));
        $action = strtolower($context->action);
        if ($action === 'get_token') {
            $name = preg_replace('/[^\w-]/', '', (string)$request->input('name', '__token__'));
            if (strlen($name) > 150 || !str_contains($name, '_token_') || strtolower((string)$request->header('x-requested-with', '')) !== 'xmlhttprequest') return $this->missing();
            $token = bin2hex(random_bytes(16));
            $request->session()->set($name, $token);
            return response($token, 200, ['Content-Type' => 'text/plain; charset=utf-8', 'Cache-Control' => 'no-store']);
        }
        if ($action === 'vertify') return Captcha::image($request, $context);
        if (in_array($action, ['arcclick', 'downcount'], true)) return AjaxActions::counters($request, $context, $action);
        if ($action === 'get_region') return AjaxActions::region($request);
        if ($action === 'arcpagelist') return AjaxActions::pagination($request, $context);
        if ($action === 'get_formreply_list') return AjaxActions::formReplies($request, $context);
        if ($action === 'get_download') return AjaxActions::download($request, $context);
        if ($action === 'sendmobilecode') {
            if ($request->method() !== 'POST') return response('Method Not Allowed', 405);
            return json((new \app\cms\service\FormSmsService())->send($request->post(), [
                'session' => $request->session(), 'lang' => $context->lang, 'ip' => $request->getRealIp(),
            ]), JSON_UNESCAPED_UNICODE);
        }
        if ($action === 'send_email') return json((new \app\cms\service\FormNotificationService())->send($request->all(), ['lang' => $context->lang, 'session' => $request->session()]), JSON_UNESCAPED_UNICODE);
        return $this->missing();
    }

    public function download(Request $request): Response
    {
        return DocumentActions::download($request, $this->context($request, 'View', (string)$request->input('a', 'downfile')));
    }

    public function submit(Request $request): Response
    {
        $context = $this->context($request, 'Lists', 'gbook_submit');
        if ($request->method() !== 'POST') return response('Method Not Allowed', 405);
        $result = (new FormService())->submit($request->post(), [
            'session' => $request->session(), 'ip' => $request->getRealIp(),
            'source' => $context->device === 'mobile' ? 2 : 1, 'lang' => $context->lang,
            'captcha_enabled' => (bool)$context->getConfig('captcha.guestbook.is_on', false),
            'files' => $request->file(),
        ]);
        return \app\cms\JumpResponse::make($request, $context, $result);
    }

    /** Original View/play page used by the dedicated video themes. */
    public function play(Request $request): Response
    {
        $context = $this->context($request, 'View', 'play');
        $aid = (int)$context->input('param.aid/d', 0);
        $fid = (int)$context->input('param.fid/d', 0);
        $archive = Db::name('archives')->where(['aid' => $aid, 'channel' => 5, 'is_del' => 0, 'status' => 1, 'lang' => $context->lang])->where('arcrank', '>', -1)->find();
        $file = Db::name('media_file')->where(['aid' => $aid, 'file_id' => $fid])->find();
        if (!$archive || !$file || trim((string)$file['file_url']) === '') return $this->missing();
        if (!\app\cms\compat\MediaAccess::allowsGuest($archive, $file)) {
            return \app\cms\JumpResponse::make($request, $context, ['code' => 0, 'msg' => '请先登录', 'wait' => 5]);
        }
        $context->aid = $aid; $context->tid = (int)$archive['typeid'];
        $context->parameters = array_replace($context->parameters, ['aid' => $aid, 'tid' => $context->tid, 'fid' => $fid]);
        $type = Runtime::model('Arctype')->getInfo($context->tid);
        if (!$type) return $this->missing();
        $field = array_replace($type, Runtime::model('Media')->getInfo($aid), ['fid' => $fid]);
        if (!empty($field['courseware'])) $field['courseware'] = get_default_pic($field['courseware'], true);
        $field['seo_title'] = set_arcseotitle($field['title'], $field['seo_title'] ?? '', $type['typename'], $context->tid);
        $field['seo_description'] = msubstr(checkStrHtml($field['seo_description'] ?? ''), 0, get_seo_description_length(), false);
        $context->field = (new FieldLogic())->getChannelFieldList($field, 5);
        // Render before recording a play so a missing template does not count.
        $response = $this->render($context, 'view_media_play');
        Db::name('media_file')->where('file_id', $fid)->inc('playcount')->update();
        return $response;
    }

    private function render(CmsContext $context, string $template, array $variables = [], bool $selectVariant = true): Response
    {
        $template = preg_replace('/\.html?$/i', '', $template);
        if ($selectVariant) {
            if ($context->getConfig('city_switch_on')) {
                $template = CityContext::template($context, $template);
            } else {
                $localized = $context->templateRoot() . $template . '_' . $context->lang . '.htm';
                if (is_file($localized)) $template .= '_' . $context->lang;
            }
        }
        $file = Runtime::resolveTemplate($template, $context->templateRoot());
        $headers = ['Content-Type' => 'text/html; charset=utf-8'];
        $renderContext = $context;
        if (trim((string)file_get_contents($file)) === '') {
            // Some official packages ship empty mobile page files. Render the
            // same theme's complete desktop page and disclose the fallback;
            // copied template files and deliberately empty include fragments
            // remain untouched.
            if ($context->device === 'mobile') {
                $fallback = clone $context;
                $fallback->device = 'pc';
                $fallback->theme = preg_replace('#(^|/)mobile$#', '${1}pc', $context->theme);
                $candidates = [$template];
                if (str_ends_with($template, '_' . $context->lang)) $candidates[] = substr($template, 0, -strlen('_' . $context->lang));
                foreach ($candidates as $candidate) {
                    try { $desktop = Runtime::resolveTemplate($candidate, $fallback->templateRoot()); }
                    catch (\RuntimeException) { continue; }
                    if (trim((string)file_get_contents($desktop)) === '') continue;
                    $renderContext = $fallback;
                    $template = $candidate;
                    $headers['X-CMS-Template-Fallback'] = 'empty-mobile-page';
                    break;
                }
            }
            if ($renderContext === $context) throw new \RuntimeException('Page template is empty: ' . $template);
        }
        $headers['X-CMS-Template'] = $renderContext->theme . '/' . $template . '.htm';
        if ($renderContext->controller === 'Search'
            && \app\cms\ThemeCatalog::isResponsive(\app\cms\ThemeCatalog::configured($renderContext->global['web_tpl_theme'] ?? null))) {
            $headers['X-Robots-Tag'] = 'noindex, follow';
        }
        try {
            $html = (new TemplateRenderer($renderContext))->render($template, $variables);
            if (preg_match('/<form\b[^>]*onsubmit\s*=\s*["\'][^"\']*\bsearchForm\s*\(/i', $html)) {
                $script = '<script src="' . htmlspecialchars(\app\cms\AssetManifest::url(($renderContext->global['web_cmspath'] ?? '') . '/public/static/cms/enterprise.js'), ENT_QUOTES, 'UTF-8') . '" defer></script>';
                $html = preg_match('/<\/body\s*>/i', $html) ? preg_replace('/<\/body\s*>/i', $script . '$0', $html, 1) : $html . $script;
            }
            $html = \app\cms\AssetCompatibility::versionScripts($html, $renderContext);
            $html = \app\cms\AssetCompatibility::mobileGalleryControls($html, $renderContext, $file);
            return response($html, 200, $headers);
        } finally {
            CmsContext::set($context);
            Runtime::boot($context);
        }
    }

    private function pathInfo(Request $request, string $path): ?Response
    {
        if (!preg_match('#^(?:index\.php/)?(home|api|user)/([A-Za-z][A-Za-z0-9_]*)/([A-Za-z_]+)(.*)$#i', $path, $m)) return null;
        $context = CmsContext::current();
        $explicitLanguage = (bool)$context->input('param.?lang');
        $context->parameters = array_replace($context->parameters, ['m' => $m[1], 'c' => $m[2], 'a' => $m[3]]);
        $extra = explode('/', trim(preg_replace('/\.html?$/', '', $m[4]), '/'));
        $pathParameters = [];
        for ($i = 0; $i + 1 < count($extra); $i += 2) $pathParameters[$extra[$i]] = $extra[$i + 1];
        $context->parameters = array_replace($context->parameters, $pathParameters);
        $request->setGet(array_replace($request->get(), $context->parameters));
        // Usability extension for generated pseudo-dynamic enterprise links:
        // upstream primes the default-language context before seeing /lang/X,
        // leaving an otherwise valid copied category unreachable. Rebuild only
        // this path-only dynamic2 entry; explicit query language, Index/API and
        // other SEO modes retain the upstream selection order.
        if (!$explicitLanguage && isset($pathParameters['lang'])
            && strtolower((string)$context->parameters['m']) === 'home'
            && in_array(strtolower((string)$context->parameters['a']), ['index', 'lists', 'view'], true)) {
            $controller = strtolower((string)$context->parameters['c']);
            // dispatch handles these names before model-controller lookup.
            // A custom model must not widen the extension to those endpoints.
            $publicController = !in_array($controller, ['index', 'search', 'tags', 'ajax'], true)
                && (in_array($controller, ['lists', 'view'], true)
                    || Db::name('channeltype')->where('ctl_name', $controller)
                        ->whereIn('id', array_merge($context->getConfig('global.allow_release_channel', []), [6]))->find());
            $language = $publicController
                ? (string)(Db::name('language')->where('mark', $pathParameters['lang'])->value('mark') ?: '') : '';
            if ($language !== '' && $language !== $context->lang) {
                // Match load()'s global, cross-group setting aggregation,
                // including serialized values and explicit NULL/empty values.
                $seo = ['seo_pseudo' => 1, 'seo_dynamic_format' => 1];
                foreach (Db::name('config')->where(['lang' => $language, 'is_del' => 0])->select()->toArray() as $setting) {
                    if (!array_key_exists($setting['name'], $seo)) continue;
                    $value = $setting['value'];
                    if (is_string($value) && preg_match('/^(a|s|i|b|d|O):|^N;$/', $value)) {
                        $decoded = @unserialize($value, ['allowed_classes' => false]);
                        if ($decoded !== false || $value === 'b:0;') $value = $decoded;
                    }
                    $seo[$setting['name']] = $value;
                }
                if ((int)$seo['seo_pseudo'] === 1 && (int)$seo['seo_dynamic_format'] === 2) {
                    // Middleware retains this object's response-cookie state.
                    // Reset its contents, including all memoized models/config,
                    // while preserving that identity and the merged request.
                    foreach (get_object_vars(new CmsContext($request)) as $name => $value) $context->$name = $value;
                    $context->memo['routing.extension'] = 'dynamic2-path-language';
                    Runtime::boot($context);
                }
            }
        }
        return $this->dispatch($request);
    }

    private function missing(): Response
    {
        $context = CmsContext::current();
        if ($context->global && \app\cms\ThemeCatalog::isResponsive(\app\cms\ThemeCatalog::configured($context->global['web_tpl_theme'] ?? null))
            && is_file($context->templateRoot() . '404.htm')) {
            $context->controller = 'Error';
            $context->action = 'notfound';
            $context->tid = $context->aid = 0;
            $context->field = [];
            return $this->render($context, '404', [], false)->withStatus(404)->withHeaders([
                'Cache-Control' => 'no-store', 'X-Robots-Tag' => 'noindex, follow',
            ]);
        }
        return \app\cms\ErrorPage::make(404);
    }
}
