<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\CmsContext;
use app\cms\compat\Runtime;
use app\cms\compat\Cache;
use app\cms\repository\CmsRepository;
use app\controller\CmsController;
use support\Request;
use support\Response;

/** Resumable generation through the real front controller; unchanged templates and owned public HTML publication. */
class StaticGenerationService
{
    protected CmsRepository $repository;
    protected GeneratedFileStore $store;
    public function __construct(?CmsRepository $repository = null, ?string $directory = null)
    {
        // Admin and worker entry points have not passed through the public bootstrap.
        Runtime::boot(CmsContext::current());
        $this->repository = $repository ?? new CmsRepository();
        $this->store = new GeneratedFileStore($directory ?? 'static');
    }

    public function listing(array $input = []): array
    {
        $allowed = (new LanguageAccessService($this->repository))->allowedLanguages();
        $languages = array_values(array_filter($this->languages(),static fn($language)=>in_array($language['mark'],$allowed,true)));
        $lang = (string)($input['lang'] ?? CmsContext::current()->lang);
        if (!in_array($lang, array_column($languages, 'mark'), true)) throw new \InvalidArgumentException('请选择有效语言');
        $categories = $this->repository->table('arctype')->where(['lang' => $lang, 'is_del' => 0, 'status' => 1])->field('id,parent_id,typename,current_channel')->order('sort_order asc,id asc')->select()->toArray();
        $manifest = $this->store->read('manifest.json', ['entries' => []]);
        $jobs = [];
        foreach (array_slice(array_reverse($manifest['jobs'] ?? []), 0, 20) as $id) {
            try { $jobs[] = $this->summary($this->job(['job'=>$id])); } catch (\RuntimeException|\InvalidArgumentException) {}
        }
        return ['lang' => $lang, 'languages' => $languages, 'categories' => $categories, 'generated_pages' => count(array_filter($manifest['entries'],static fn($entry)=>in_array($entry['lang'],$allowed,true))), 'jobs' => $jobs];
    }

    public function start(array $input, int $owner = 0): array
    {
        return \app\cms\maintenance\MaintenanceGate::shared(fn()=> $this->startInternal($input,$owner));
    }

    private function startInternal(array $input, int $owner = 0): array
    {
        $options = $this->options($input);
        (new LanguageAccessService($this->repository))->assertLanguages($options['languages']);
        $tasks = [];
        foreach ($options['languages'] as $lang) foreach ($options['devices'] as $device) {
            $site = $this->site($lang, $device);
            if ((int)$site->getConfig('ey_config.seo_pseudo') !== 2) throw new \InvalidArgumentException('语言 ' . $lang . ' 尚未启用生成静态模式，请先保存SEO设置');
            foreach ($this->plan($site, $options) as $task) $tasks[] = $task;
        }
        if (!$tasks) throw new \InvalidArgumentException('当前选择没有可生成的页面');
        if (count($tasks) > 1000000) throw new \InvalidArgumentException('生成任务过大，请按语言或栏目分批');
        return $this->createJob($tasks, $options, $owner);
    }

    protected function createJob(array $tasks, array $options, int $owner): array
    {
        $identities = [];
        foreach ($tasks as $task) {
            $key = $this->entryKey($task['lang'], $task['device'], $task['url']);
            $identity = [$task['kind'], $task['id'], $task['page']];
            if (isset($identities[$key]) && $identities[$key] !== $identity) throw new \InvalidArgumentException('多个页面使用相同URL，请先修正栏目与文档命名规则：' . $task['url']);
            $identities[$key] = $identity;
        }
        $id = bin2hex(random_bytes(16));
        $job = ['id' => $id, 'owner' => $owner, 'state' => 'pending', 'created_at' => time(), 'updated_at' => time(), 'options' => $options,
            'tasks' => $tasks, 'cursor' => 0, 'succeeded' => 0, 'failed' => 0, 'failures' => [], 'recent' => []];
        return $this->store->locked(function () use ($id, $job) {
            $manifest = $this->manifest(); $manifest['jobs'][] = $id;
            $this->store->writeBatch(['job-' . $id . '.json'=>$job, 'manifest.json'=>$manifest]);
            return $this->summary($job);
        });
    }

    public function status(array $input): array
    {
        return $this->summary($this->job($input));
    }

    public function step(array $input): array
    {
        return \app\cms\maintenance\MaintenanceGate::shared(fn()=> $this->stepInternal($input));
    }

    private function stepInternal(array $input): array
    {
        if (CmsContext::current()->request !== null || !in_array(PHP_SAPI, ['cli','micro'], true)) throw new \RuntimeException('静态任务仅允许后台进程执行，请查询持久进度');
        $batch = max(1, min(10, (int)($input['batch'] ?? 1)));
        return $this->store->locked(function () use ($input, $batch): array {
            $job = $this->job($input);
            if ($job['state'] === 'complete') return $this->summary($job);
            $manifest = $this->manifest(); $job['state'] = 'running';
            for ($i = 0; $i < $batch && $job['cursor'] < count($job['tasks']); $i++) {
                $task = $job['tasks'][$job['cursor']];
                if (!in_array($task['lang'], array_column($this->languages(),'mark'), true)) { $job['state']='paused_language'; $job['recent'][]='语言未启用，任务已暂停'; break; }
                try {
                    if ((isset($task['compile_version']) && $task['compile_version'] !== \app\cms\template\TemplateCapabilities::COMPILE_VERSION)
                        || ($task['template_mode'] ?? 'eyou') !== \app\cms\template\TemplateMode::current()
                        || (isset($task['dialect_version']) && $task['dialect_version'] !== \app\cms\template\TemplateDialect::VERSION)
                        || ($task['site_signature'] ?? 'legacy') !== \app\cms\template\TemplateMode::siteSignature()) throw new \RuntimeException('任务模式、编译版本或站点签名不同，请重新创建任务');
                    if (!isset($task['static_directory'])) {
                        $currentSite = $this->site($task['lang'], $task['device'], $task['origin'] ?? null, $task['base'] ?? null);
                        $task += $this->publicationSettings($currentSite);
                    }
                    $this->assertPublicDestinationFree($task);
                    $rendered = $this->render($task);
                    $response = $rendered['response'];
                    $body = $response->rawBody();
                    if ($response->getStatusCode() !== 200 || !$response->getHeader('X-CMS-Template') || !str_contains(strtolower((string)$response->getHeader('Content-Type')), 'text/html') || trim($body) === '') throw new \RuntimeException('页面未成功渲染为模板HTML（HTTP ' . $response->getStatusCode() . '）');
                    if (preg_match('/data-cms-error\b|name=["\']cms-error["\']/i', $body)) throw new \RuntimeException('页面包含错误标记，拒绝写入');
                    $site = $rendered['context'];
                    if ($this->signature($site) !== $task['signature']) throw new \RuntimeException('SEO或模板配置已变化，请重新创建任务');
                    $key = $this->entryKey($task['lang'], $task['device'], $task['url']);
                    $existing = $manifest['entries'][$key] ?? null;
                    if ($existing && [$existing['kind'], $existing['id'], $existing['page']] !== [$task['kind'], $task['id'], $task['page']]) throw new \RuntimeException('生成URL与既有页面冲突，未覆盖');
                    $last = $task['kind'] === 'category' && $task['page'] === 1 ? max(1, (int)($rendered['last_page'] ?? 1)) : 1;
                    if ($last > 1000000) throw new \RuntimeException('分页数量异常，请检查模板分页配置');
                    $nextTasks = [];
                    // Upstream mobile route initialization may change request-local
                    // SEO mode while rendering. Plan pages from persisted settings.
                    $paginationSite = $last > 1 ? $this->site($task['lang'], $task['device'], $task['origin'] ?? null, $task['base'] ?? null) : $site;
                    if ($task['kind'] === 'category' && $task['page'] === 1) for ($page = 2; $page <= $last; $page++) {
                        $next = $task; $next['page'] = $page; $next['url'] = $this->pageUrl($paginationSite, $task['row'], $page);
                        $nextKey = $this->entryKey($next['lang'], $next['device'], $next['url']);
                        foreach (array_merge($job['tasks'], $nextTasks) as $queued) if ($nextKey === $this->entryKey($queued['lang'], $queued['device'], $queued['url']) && [$next['kind'], $next['id'], $next['page']] !== [$queued['kind'], $queued['id'], $queued['page']]) throw new \RuntimeException('分页URL与任务页面冲突，未发布当前栏目');
                        $previousEntry = $manifest['entries'][$nextKey] ?? null;
                        if ($previousEntry && [$next['kind'], $next['id'], $next['page']] !== [$previousEntry['kind'], $previousEntry['id'], $previousEntry['page']]) throw new \RuntimeException('分页URL与既有页面冲突，未发布当前栏目');
                        $nextTasks[] = $next;
                    }
                    $entry = $this->store->put($body, 'html') + ['url' => $task['url'], 'lang' => $task['lang'], 'device' => $task['device'], 'signature' => $task['signature'],
                        'kind' => $task['kind'], 'id' => $task['id'], 'page' => $task['page'], 'source_time' => max((int)($task['row']['update_time'] ?? 0), (int)($task['row']['add_time'] ?? 0)), 'parent_id' => (int)($task['row']['parent_id'] ?? 0), 'template' => $response->getHeader('X-CMS-Template'), 'generated_at' => time(), 'job' => $job['id']];
                    $entry += $this->publisher()->publish($task, $body, function () use ($task): bool {
                        $current = $this->site($task['lang'], $task['device'], $task['origin'] ?? null, $task['base'] ?? null);
                        return $this->signature($current) === $task['signature'] && $this->taskIsCurrent($task, $current);
                    });
                    // A directory-visibility change gives the same page a new
                    // URL identity. Retire its superseded URL, preserving home aliases.
                    if ($task['kind'] !== 'home') foreach ($manifest['entries'] as $oldKey => $oldEntry) {
                        if ($oldKey !== $key && [$oldEntry['lang'], $oldEntry['device'], $oldEntry['kind'], $oldEntry['id'], $oldEntry['page']]
                            === [$task['lang'], $task['device'], $task['kind'], $task['id'], $task['page']]) unset($manifest['entries'][$oldKey]);
                    }
                    // Retire an older separately rendered index alias only after the
                    // canonical publication succeeded. Resolution now shares its SHA.
                    if ($task['kind'] === 'home') foreach ($manifest['entries'] as $oldKey => $oldEntry) {
                        if ($oldKey !== $key && ($oldEntry['kind'] ?? '') === 'home'
                            && $oldEntry['lang'] === $task['lang'] && $oldEntry['device'] === $task['device']
                            && $oldEntry['url'] === rtrim($task['url'], '/') . '/index.html'
                            && ($oldEntry['public_file'] ?? null) === ($entry['public_file'] ?? null)) unset($manifest['entries'][$oldKey]);
                    }
                    $manifest['version'] = 2;
                    $manifest['entries'][$key] = $entry; $manifest['objects'][$entry['object']] = $entry;
                    array_push($job['tasks'], ...$nextTasks);
                    $job['succeeded']++; $recent = $task['url'];
                } catch (\Throwable $error) {
                    $job['failed']++;
                    $job['failures'][] = ['kind' => $task['kind'], 'id' => $task['id'], 'page' => $task['page'], 'lang' => $task['lang'], 'device' => $task['device'], 'url' => $task['url'], 'message' => $error->getMessage()];
                    $recent = $task['url'] . '：' . $error->getMessage();
                }
                $job['cursor']++; $job['recent'][] = $recent; $job['recent'] = array_slice($job['recent'], -10);
                $job['updated_at'] = time();
                // Persist each completed page, so a later page failure cannot erase progress.
                $this->store->writeBatch(['manifest.json'=>$manifest, 'job-' . $job['id'] . '.json'=>$job]);
            }
            if ($job['cursor'] >= count($job['tasks'])) $job['state'] = 'complete';
            $job['updated_at'] = time(); $this->store->write('job-' . $job['id'] . '.json', $job);
            return $this->summary($job);
        });
    }

    public function clean(array $input): array
    {
        $lang = $input['lang'] ?? ''; $device = $input['device'] ?? '';
        if (!is_string($lang) || ($lang !== '' && !preg_match('/^[A-Za-z0-9_-]{2,50}$/D', $lang)) || !in_array($device, ['', 'pc', 'mobile'], true)) throw new \InvalidArgumentException('清理范围无效');
        (new LanguageAccessService($this->repository))->assertLanguages($lang!==''?[$lang]:array_column((new LanguagePolicy($this->repository))->all(),'mark'));
        return $this->store->locked(function () use ($lang, $device): array {
            $manifest = $this->manifest(); $removed = 0; $protected = []; $withdrawn = [];
            foreach ($manifest['jobs'] as $id) {
                $job = $this->store->read('job-' . $id . '.json');
                if ($job && $job['state'] !== 'complete' && ($lang === '' || in_array($lang, $job['options']['languages'], true)) && ($device === '' || in_array($device, $job['options']['devices'], true))) throw new \InvalidArgumentException('当前范围仍有待完成生成任务，请等待任务结束后清理');
            }
            foreach ($manifest['entries'] as $key => $entry) if (($lang === '' || $entry['lang'] === $lang) && ($device === '' || $entry['device'] === $device)) { $withdrawn[] = $entry; unset($manifest['entries'][$key]); $removed++; }
            $protected = $this->withdrawUnreferenced($withdrawn, $manifest['entries']);
            $this->store->transaction(function() use (&$manifest, &$protected, $lang): void {
                $used = array_column($manifest['entries'], 'object');
                foreach ($manifest['objects'] as $object => $entry) if (($lang === '' || $entry['lang'] === $lang) && !in_array($object, $used, true)) {
                    if ($this->store->remove($entry)) unset($manifest['objects'][$object]); else $protected[] = $object;
                }
                $this->store->write('manifest.json', $manifest);
            });
            return ['removed_pages' => $removed, 'modified_files_preserved' => $protected];
        });
    }

    /** Post-commit invalidation is explicit, including changes within the same second. */
    public function invalidatePublished(array $input): array
    {
        $lang=$input['lang']??'';
        if(!is_string($lang)||($lang!==''&&!preg_match('/^[A-Za-z0-9_-]{2,50}$/D',$lang)))throw new \InvalidArgumentException('失效语言无效');
        (new LanguageAccessService($this->repository))->assertLanguages($lang!==''?[$lang]:array_column((new LanguagePolicy($this->repository))->all(),'mark'));
        $ids=[];
        foreach(['category_ids','archive_ids']as$name){
            $values=$input[$name]??[];if(!is_array($values))throw new \InvalidArgumentException('失效页面范围无效');
            foreach($values as$value)if(!ctype_digit((string)$value)||(int)$value<1)throw new \InvalidArgumentException('失效页面ID无效');
            $ids[$name]=array_map('intval',$values);
        }
        return $this->store->locked(function()use($input,$lang,$ids){
            $manifest=$this->manifest();$removed=0;$withdrawn=[];
            foreach($manifest['entries']as$key=>$entry){
                if($lang!==''&&$entry['lang']!==$lang)continue;
                $matches=match($entry['kind']){'home'=>!empty($input['home']??true),'category'=>in_array((int)$entry['id'],$ids['category_ids'],true),'document'=>in_array((int)$entry['id'],$ids['archive_ids'],true),default=>false};
                if($matches){$withdrawn[]=$entry;unset($manifest['entries'][$key]);$removed++;}
            }
            $protected=$this->withdrawUnreferenced($withdrawn,$manifest['entries']);
            $this->store->write('manifest.json',$manifest);return ['removed_pages'=>$removed,'modified_files_preserved'=>$protected];
        });
    }

    public static function response(Request $request, CmsContext $context): ?Response
    {
        if ($policy = \app\cms\SitePolicy::response($context)) return $policy;
        if (!in_array($request->method(), ['GET', 'HEAD'], true) || (int)$context->getConfig('ey_config.seo_pseudo') !== 2 || !empty($context->memo['cms.static.generating']) || $context->getConfig('city_switch_on') || $context->site !== '' || strtolower((string)$request->input('m', 'home')) !== 'home' || $request->input('uiset') !== null) return null;
        $service = new self();
        try { $url = GeneratedFileStore::urlKey($request->uri(), $context->origin(), (string)($context->global['web_cmspath'] ?? '')); }
        catch (\InvalidArgumentException) { return null; }
        $manifest = $service->manifest();
        $requestedKey = $service->entryKey($context->lang, $context->device, $url);
        $entry = $manifest['entries'][$requestedKey] ?? null;
        if (!array_key_exists($requestedKey, $manifest['entries']) && in_array(trim((string)($context->global['seo_html_position'] ?? ''), ' /'), ['', 'index.html', '../index.html'], true)) {
            // Resolve only the current directory homepage's index.html alias.
            // It reuses one owned publication, including its SHA and signature;
            // custom filenames and ungenerated pages keep their original rules.
            $parts = parse_url($url);
            $path = $parts['path'] ?? '';
            if (str_ends_with($path, '/index.html')) {
                try {
                    $home = GeneratedFileStore::urlKey((string)($context->global['lang_url'] ?? (($context->global['web_cmspath'] ?? '') . '/')), $context->origin(), (string)($context->global['web_cmspath'] ?? ''));
                    $homePath = (string)parse_url($home, PHP_URL_PATH);
                    $directory = substr($path, 0, -strlen('index.html'));
                    $query = isset($parts['query']) ? '?' . $parts['query'] : '';
                    if (rtrim($directory, '/') === rtrim($homePath, '/') && $homePath . $query === $home) {
                        $candidate = $manifest['entries'][$service->entryKey($context->lang, $context->device, $home)] ?? null;
                        if (($candidate['kind'] ?? '') === 'home') $entry = $candidate;
                    }
                } catch (\InvalidArgumentException) { /* Foreign or unsafe home URL: no alias. */ }
            }
        }
        if (!$entry || ($entry['signature'] ?? '') !== $service->signature($context)) return null;
        if (!$service->publicEntry($entry, $context)) return null;
        $bytes = isset($entry['public_file']) ? $service->publisher()->bytes($entry) : $service->store->bytes($entry); if ($bytes === null) return null;
        return response($request->method() === 'HEAD' ? '' : $bytes, 200, ['Content-Type' => 'text/html; charset=utf-8', 'X-CMS-Static' => 'generated', 'X-CMS-Template' => $entry['template'], 'Last-Modified' => gmdate('D, d M Y H:i:s', $entry['generated_at']) . ' GMT', 'Vary' => 'User-Agent, Cookie', 'X-Content-Type-Options' => 'nosniff']);
    }

    /** Post-commit only: failures are warnings and never undo a successful content save. */
    public function afterContentSave(Request $request, int $aid, bool $editing): array
    {
        try {
            (new SitemapService($this->repository))->scheduleAutomatic($request);
            $archive = $this->repository->table('archives')->where('aid', $aid)->find();
            if (!$archive || !empty($archive['is_del']) || (int)$archive['status'] !== 1 || (int)$archive['arcrank'] !== 0) return ['skipped' => true, 'reason' => '当前内容不是公开页面'];
            $site = $this->site((string)$archive['lang'], 'pc');
            if ((int)$site->getConfig('ey_config.seo_pseudo') !== 2) return ['skipped' => true, 'reason' => '当前语言采用动态或伪静态，数据缓存已由保存流程失效'];
            $type = $this->repository->table('arctype')->where('id', $archive['typeid'])->find();
            if (!$type) return ['skipped' => true, 'reason' => '栏目不存在'];
            $prefix = $editing ? 'seo_uphtml_editafter_' : 'seo_uphtml_after_';
            $options = $this->options(['lang' => $archive['lang'], 'devices' => ['pc'], 'scope' => 'documents', 'start_id' => $aid, 'end_id' => $aid]);
            $tasks = $this->plan($site, $options);
            if ((int)$archive['channel'] === 6) $tasks = array_merge($tasks, $this->plan($site, array_replace($options, ['scope' => 'categories', 'category_ids' => [(int)$archive['typeid']]])));
            if (!empty($site->global[$prefix . 'home'])) $tasks = array_merge($tasks, $this->plan($site, array_replace($options, ['scope' => 'home'])));
            if (!empty($site->global[$prefix . 'channel'])) {
                $types = array_column($this->repository->table('arctype')->where(['lang' => $archive['lang'], 'is_del' => 0, 'status' => 1])->select()->toArray(), null, 'id');
                $ids = []; $id = (int)$archive['typeid'];
                while ($id && isset($types[$id]) && !in_array($id, $ids, true)) { $ids[] = $id; $id = (int)$types[$id]['parent_id']; }
                $tasks = array_merge($tasks, $this->plan($site, array_replace($options, ['scope' => 'categories', 'category_ids' => $ids])));
            }
            if (!empty($site->global[$prefix . 'pernext'])) foreach (['<' => 'aid desc', '>' => 'aid asc'] as $operator => $order) {
                $neighbor = $this->repository->table('archives')->where(['lang' => $archive['lang'], 'typeid' => $archive['typeid'], 'status' => 1, 'is_del' => 0, 'arcrank' => 0])->where('aid', $operator, $aid)->order($order)->find();
                if ($neighbor) $tasks = array_merge($tasks, $this->plan($site, array_replace($options, ['start_id' => (int)$neighbor['aid'], 'end_id' => (int)$neighbor['aid']])));
            }
            $unique = []; foreach ($tasks as $task) $unique[$this->entryKey($task['lang'], $task['device'], $task['url'])] = $task;
            if (!$unique) return ['skipped' => true, 'reason' => '没有可生成的公开页面'];
            $job = $this->createJob(array_values($unique), $options + ['automatic' => true], function_exists('admin_id') ? (int)admin_id() : 0);
            return ['job' => $job, 'queued' => true, 'warning' => ''];
        } catch (\Throwable $error) { return ['warning' => '内容已保存，静态生成未完成：' . $error->getMessage()]; }
    }

    public function afterCategoryChange(Request $request, int $typeid, ?string $language = null): array
    {
        try {
            (new SitemapService($this->repository))->scheduleAutomatic($request);
            $type = $this->repository->table('arctype')->where('id', $typeid)->find();
            $lang = (string)($type['lang'] ?? $language ?? CmsContext::current()->lang);
            $site = $this->site($lang, 'pc');
            if ((int)$site->getConfig('ey_config.seo_pseudo') !== 2) return ['skipped' => true, 'reason' => '当前语言不是静态模式'];
            $types = array_column($this->repository->table('arctype')->where(['lang' => $lang, 'is_del' => 0, 'status' => 1])->select()->toArray(), null, 'id');
            $ids = []; $id = $type && isset($types[$typeid]) ? $typeid : (int)($type['parent_id'] ?? 0);
            if (!$type) foreach ($this->manifest()['entries'] as $entry) if ($entry['kind'] === 'category' && (int)$entry['id'] === $typeid) { $id = (int)($entry['parent_id'] ?? 0); break; }
            while ($id && isset($types[$id]) && !in_array($id, $ids, true)) { $ids[] = $id; $id = (int)$types[$id]['parent_id']; }
            $options = $this->options(['lang' => $lang, 'scope' => 'categories', 'category_ids' => $ids]);
            $tasks = $ids ? $this->plan($site, $options) : [];
            if (!empty($site->global['seo_uphtml_after_home']) || !empty($site->global['seo_uphtml_editafter_home'])) $tasks = array_merge($tasks, $this->plan($site, array_replace($options, ['scope' => 'home'])));
            if (!$tasks) return ['skipped' => true, 'reason' => '没有需要重新生成的有效页面'];
            $job = $this->createJob($tasks, $options + ['automatic' => true], function_exists('admin_id') ? (int)admin_id() : 0);
            return ['job' => $job, 'queued' => true, 'warning' => ''];
        } catch (\Throwable $error) { return ['warning' => '栏目已保存，静态生成未完成：' . $error->getMessage()]; }
    }

    protected function publicEntry(array $entry, CmsContext $context): bool
    {
        if (($entry['kind'] ?? '') === 'home') return !isset($context->global['seo_showmod']) || !empty($context->global['seo_showmod']);
        if (($entry['kind'] ?? '') === 'document') {
            $archive = $this->repository->table('archives')->where(['aid' => $entry['id'], 'lang' => $context->lang, 'is_del' => 0, 'status' => 1, 'arcrank' => 0])->find();
            if (!$archive || !empty($archive['is_jump']) || !in_array($archive['channel'], $context->getConfig('global.allow_release_channel', []))) return false;
            $id = $archive['typeid'];
        } elseif (($entry['kind'] ?? '') === 'category') $id = $entry['id']; else return false;
        $type = $this->repository->table('arctype')->where(['id' => $id, 'lang' => $context->lang, 'is_del' => 0, 'status' => 1])->find();
        if (!$type || !empty($type['typearcrank']) || ((int)$type['is_part'] === 1 && !empty($type['typelink']))) return false;
        if (isset($archive) && (int)$archive['channel'] !== (int)$type['current_channel']) return false;
        $source = $archive ?? $type;
        if (isset($entry['source_time']) && max((int)($source['update_time'] ?? 0), (int)($source['add_time'] ?? 0)) > (int)$entry['source_time']) return false;
        if ((int)$type['current_channel'] === 6) {
            $single = $this->repository->table('archives')->where(['typeid' => $id, 'lang' => $context->lang, 'is_del' => 0])->order('aid asc')->find();
            if ($single && ((int)$single['status'] !== 1 || (int)$single['arcrank'] !== 0)) return false;
        }
        return true;
    }

    protected function publisher(): PublicStaticPublisher { return new PublicStaticPublisher(); }

    protected function assertPublicDestinationFree(array $task): void
    {
        if (isset($task['kind'])) { $this->publisher()->assertWritable($task); return; }
        // Sitemap/XML/TXT remain in their existing private object store.
        $path = rawurldecode((string)parse_url($task['url'], PHP_URL_PATH));
        $base = (string)($task['base'] ?? '');
        if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) $path = substr($path, strlen($base));
        if (preg_match('#(?:^|/)index\.php$#i', $path)) return;
        $root = \app\cms\support\EmbeddedResources::realpath(\app\cms\Paths::publicRoot()); if (!$root) throw new \RuntimeException('公开目录不存在');
        $target = $root;
        foreach (array_filter(explode('/', trim($path, '/')), static fn($part) => $part !== '') as $part) {
            if ($part === '.' || $part === '..') throw new \RuntimeException('生成URL路径无效');
            $target .= '/' . $part;
            if (is_link($target)) throw new \RuntimeException('生成URL已由公开链接路径占用，未发布');
        }
        if (is_file($target) || (is_dir($target) && (file_exists($target . '/index.html') || is_link($target . '/index.html') || file_exists($target . '/index.htm') || is_link($target . '/index.htm')))) throw new \RuntimeException('生成URL已由公开文件占用，未覆盖原文件：' . $task['url']);
    }


    private function withdrawUnreferenced(array $removed, array $remaining): array
    {
        $files = array_filter(array_column($remaining, 'public_file'));
        return $this->publisher()->withdraw(array_filter($removed, static fn($entry) => !in_array($entry['public_file'] ?? null, $files, true)));
    }

    private function publicationSettings(CmsContext $site): array
    {
        return ['static_directory' => \app\cms\StaticUrl::directory($site->global), 'static_showdir' => (string)($site->global['seo_html_showdir'] ?? '1')];
    }

    /** A pending task must not resurrect an old URL after a move or directory edit. */
    private function taskIsCurrent(array $task, CmsContext $site): bool
    {
        if ((int)$site->getConfig('ey_config.seo_pseudo') !== 2 || !$this->publicEntry($task, $site)) return false;
        if ($task['kind'] === 'home') return true;
        if ($task['kind'] === 'category') {
            $row = $this->repository->table('arctype')->where('id', $task['id'])->find();
            return $row && $this->pageUrl($site, $row, $task['page']) === $task['url'];
        }
        $row = $this->repository->table('archives')->where('aid', $task['id'])->find();
        if (!$row) return false;
        $type = $this->repository->table('arctype')->where('id', $row['typeid'])->find();
        if (!$type) return false;
        $previous = CmsContext::current(); CmsContext::set($site); Runtime::boot($site);
        try { return GeneratedFileStore::urlKey(arcurl('home/View/index', array_replace($type, $row), true, true), $site->origin(), (string)($site->global['web_cmspath'] ?? '')) === $task['url']; }
        finally { CmsContext::set($previous); Runtime::boot($previous); }
    }

    protected function languages(): array { return (new LanguagePolicy($this->repository))->publicLanguages(); }

    protected function site(string $lang, string $device, ?string $origin = null, ?string $base = null): CmsContext
    {
        $previous = CmsContext::current();
        if ($origin === null && $previous->request === null) {
            $configured = $this->repository->table('config')->where(['name'=>'web_basehost','lang'=>$lang,'is_del'=>0])->value('value');
            $origin = \app\cms\config\SiteSettings::origin($configured);
        }
        $origin ??= $previous->origin(); $base ??= (string)($previous->global['web_cmspath'] ?? '');
        $request = $this->request($origin, $base . '/index.php?' . http_build_query(['lang' => $lang, 'goto' => $device]));
        $context = new CmsContext($request); CmsContext::set($context);
        try { $context->load(); Runtime::boot($context); if ($context->lang !== $lang) throw new \InvalidArgumentException('生成语言不可用'); return $context; }
        finally { CmsContext::set($previous); Runtime::boot($previous); }
    }

    protected function render(array $task): array
    {
        $previous = CmsContext::current();
        try {
            $site = $this->site($task['lang'], $task['device'], $task['origin'] ?? null, $task['base'] ?? null);
            $parts = parse_url($task['url']); parse_str($parts['query'] ?? '', $query);
            $query = array_replace($query, ['m' => 'home', 'c' => ['home' => 'Index', 'category' => 'Lists', 'document' => 'View'][$task['kind']], 'a' => 'index', 'lang' => $task['lang'], 'goto' => $task['device'], 'page' => $task['page']]);
            if ($task['kind'] === 'category') $query['tid'] = $task['id'];
            if ($task['kind'] === 'document') $query['aid'] = $task['id'];
            $site->request = $this->request($site->origin(), ($parts['path'] ?? '/') . '?' . http_build_query($query));
            $site->parameters = $query; $site->memo['cms.static.generating'] = true; CmsContext::set($site); Runtime::boot($site);
            $controller = new CmsController();
            $response = match ($task['kind']) { 'home' => $controller->index($site->request), 'category' => $controller->lists($site->request, $task['id']), 'document' => $controller->view($site->request, $task['id']) };
            $last = 1;
            if ($task['kind'] === 'category') {
                $key = 'eyou-TagList-lastPage_' . md5($task['id'] . '_');
                $last = max(1, (int)Cache::get($key, 1), (int)($site->memo['cms.static.last_page'] ?? 1));
            }
            return ['response' => $response, 'context' => $site, 'last_page' => $last];
        } finally { CmsContext::set($previous); Runtime::boot($previous); }
    }

    protected function plan(CmsContext $site, array $options): array
    {
        $previous = CmsContext::current(); CmsContext::set($site); Runtime::boot($site);
        try {
            $types = $this->repository->table('arctype')->where(['lang' => $site->lang, 'is_del' => 0, 'status' => 1])->order('sort_order asc,id asc')->select()->toArray();
            $types = array_column(array_filter($types, static fn($t) => empty($t['typearcrank']) && ((int)($t['is_part'] ?? 0) !== 1 || empty($t['typelink']))), null, 'id');
            $selected = $options['category_ids'];
            if ($selected && $options['include_children']) {
                do { $before = count($selected); foreach ($types as $row) if (in_array((int)$row['parent_id'], $selected, true)) $selected[] = (int)$row['id']; $selected = array_values(array_unique($selected)); } while (count($selected) > $before);
            }
            $archives = $this->repository->table('archives')->where(['lang' => $site->lang, 'status' => 1, 'is_del' => 0, 'arcrank' => 0])->order('aid asc')->select()->toArray();
            $allowed = $site->getConfig('global.allow_release_channel', []);
            $archives = array_filter($archives, static fn($a) => isset($types[$a['typeid']]) && in_array($a['channel'], $allowed) && empty($a['is_jump']) && (int)$types[$a['typeid']]['current_channel'] === (int)$a['channel'] && (!$selected || in_array((int)$a['typeid'], $selected, true)) && (!$options['start_id'] || (int)$a['aid'] >= $options['start_id']) && (!$options['end_id'] || (int)$a['aid'] <= $options['end_id']) && (!$options['since'] || max((int)$a['add_time'], (int)$a['update_time']) >= $options['since']));
            if ($options['mode'] === 'incremental') {
                $affected = array_values(array_unique(array_map(static fn($a) => (int)$a['typeid'], $archives)));
                do { $before = count($affected); foreach ($affected as $id) if (!empty($types[$id]['parent_id'])) $affected[] = (int)$types[$id]['parent_id']; $affected = array_values(array_unique($affected)); } while (count($affected) > $before);
                $selected = $affected ?: [-1];
            }
            $tasks = [];
            if (in_array($options['scope'], ['all', 'home'], true) && (!isset($site->global['seo_showmod']) || !empty($site->global['seo_showmod']))) {
                $home = (string)($site->global['lang_url'] ?? (($site->global['web_cmspath'] ?? '') . '/'));
                $tasks[] = $this->task($site, 'home', 0, $home, []);
                $position = trim((string)($site->global['seo_html_position'] ?? ''), '.');
                if ($position !== '' && preg_match('/\.html?$/i', $position)) {
                    $directory = ltrim(\app\cms\StaticUrl::directory($site->global), '/');
                    $position = ltrim($position, '/');
                    if (\app\cms\StaticUrl::visibleDirectory($site->global) === '' && str_starts_with($position, $directory . '/')) $position = substr($position, strlen($directory) + 1);
                    $alias = $this->task($site, 'home', 0, rtrim((string)($site->global['web_cmspath'] ?? ''), '/') . '/' . ltrim($position, '/'), []);
                    // A directory home and its index.html share one physical publication.
                    // Render once: separate renders may contain unique widget IDs.
                    $directoryAlias = rtrim($tasks[0]['url'], '/') . '/index.html';
                    if ($alias['url'] !== $tasks[0]['url'] && $alias['url'] !== $directoryAlias) $tasks[] = $alias;
                }
            }
            if (in_array($options['scope'], ['all', 'categories'], true)) foreach ($types as $row) if (!$selected || in_array((int)$row['id'], $selected, true)) $tasks[] = $this->task($site, 'category', (int)$row['id'], $this->pageUrl($site, $row, 1), $row);
            if (in_array($options['scope'], ['all', 'documents'], true)) foreach ($archives as $row) $tasks[] = $this->task($site, 'document', (int)$row['aid'], arcurl('home/View/index', array_replace($types[$row['typeid']], $row), true, true), $row);
            return $tasks;
        } finally { CmsContext::set($previous); Runtime::boot($previous); }
    }

    protected function pageUrl(CmsContext $site, array $row, int $page): string
    {
        $previous = CmsContext::current(); CmsContext::set($site); Runtime::boot($site);
        try { $url = typeurl('home/Lists/index', $row, true, true); get_list_only_pageurl($url, $row['id'], $row['rulelist'] ?? '', $page); return GeneratedFileStore::urlKey($url, $site->origin(), (string)($site->global['web_cmspath'] ?? '')); }
        finally { CmsContext::set($previous); Runtime::boot($previous); }
    }

    private function task(CmsContext $site, string $kind, int $id, string $url, array $row): array
    {
        return ['site_signature' => \app\cms\template\TemplateMode::siteSignature($site), 'template_mode' => \app\cms\template\TemplateMode::current($site), 'dialect_version' => \app\cms\template\TemplateDialect::VERSION, 'compile_version' => \app\cms\template\TemplateCapabilities::COMPILE_VERSION, 'kind' => $kind, 'id' => $id, 'lang' => $site->lang, 'device' => $site->device, 'origin' => $site->origin(), 'base' => (string)($site->global['web_cmspath'] ?? ''), 'page' => 1, 'url' => GeneratedFileStore::urlKey($url, $site->origin(), (string)($site->global['web_cmspath'] ?? '')), 'row' => $row, 'signature' => $this->signature($site)] + $this->publicationSettings($site);
    }

    protected function signature(CmsContext $site): string
    {
        // Custom global variables have arbitrary names. Include every loaded
        // value so their edits invalidate generated pages as website settings do.
        $settings = $site->global;
        ksort($settings); return hash('sha256', serialize([$site->origin(), $site->lang, $site->device, $site->theme, $settings, (new LanguagePolicy($this->repository))->fingerprint(), (new LanguageDictionary($this->repository))->revision($site->lang), \app\cms\template\TemplateMode::siteSignature($site), \app\cms\template\TemplateMode::current($site), \app\cms\template\TemplateDialect::VERSION]));
    }
    protected function request(string $origin, string $url): Request
    {
        if (!preg_match('#^https?://(?:[A-Za-z0-9.-]+|\[[A-Fa-f0-9:]+\])(?::[0-9]{1,5})?$#D', $origin) || preg_match('/[\r\n\x00]/', $url)) throw new \InvalidArgumentException('站点来源无效');
        $host = parse_url($origin, PHP_URL_HOST) . (parse_url($origin, PHP_URL_PORT) ? ':' . parse_url($origin, PHP_URL_PORT) : '');
        $request = new Request('GET ' . $url . " HTTP/1.1\r\nHost: " . $host . "\r\nAccept: text/html\r\nX-Forwarded-Proto: " . (parse_url($origin, PHP_URL_SCHEME) === 'https' ? 'https' : 'http') . "\r\n\r\n");
        $request->context['session'] = new GenerationSession();
        $request->context['sid'] = $request->context['session']->getId();
        return $request;
    }
    protected function options(array $input): array
    {
        $available = array_column($this->languages(), 'mark');
        $languages = $input['languages'] ?? [$input['lang'] ?? CmsContext::current()->lang];
        if (!is_array($languages) || !$languages || array_diff($languages, $available)) throw new \InvalidArgumentException('请选择有效语言');
        $devices = $input['devices'] ?? ['pc']; if (!is_array($devices) || !$devices || array_diff($devices, ['pc', 'mobile'])) throw new \InvalidArgumentException('请选择生成终端');
        $scope = $input['scope'] ?? 'all'; if (!in_array($scope, ['all', 'home', 'categories', 'documents'], true)) throw new \InvalidArgumentException('生成范围无效');
        $mode = $input['mode'] ?? 'all'; if (!in_array($mode, ['all', 'incremental'], true)) throw new \InvalidArgumentException('生成方式无效');
        $ids = $input['category_ids'] ?? []; if (is_string($ids)) $ids = preg_split('/[\s,]+/', trim($ids), -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($ids)) throw new \InvalidArgumentException('栏目选择无效');
        foreach ($ids as $id) if (!ctype_digit((string)$id) || (int)$id < 1) throw new \InvalidArgumentException('栏目ID无效');
        $since = $input['since'] ?? $input['seo_start_time'] ?? '';
        $since = $since === '' ? 0 : (is_numeric($since) ? (int)$since : strtotime((string)$since));
        if ($since === false || $since < 0 || ($mode === 'incremental' && !$since && empty($input['start_id']) && empty($input['seo_startid2']))) throw new \InvalidArgumentException('增量生成需要起始时间或文档ID');
        $start = $input['start_id'] ?? $input['seo_startid'] ?? $input['seo_startid2'] ?? 0; $end = $input['end_id'] ?? $input['seo_endid'] ?? 0;
        if (!ctype_digit((string)$start) || !ctype_digit((string)$end) || ($end && $start > $end)) throw new \InvalidArgumentException('文档ID范围无效');
        return ['languages' => array_values(array_unique($languages)), 'devices' => array_values(array_unique($devices)), 'scope' => $scope, 'mode' => $mode, 'category_ids' => array_values(array_unique(array_map('intval', $ids))), 'include_children' => !empty($input['include_children']), 'since' => $since, 'start_id' => (int)$start, 'end_id' => (int)$end];
    }
    protected function manifest(): array { return $this->store->read('manifest.json', ['version' => 1, 'entries' => [], 'objects' => [], 'jobs' => []]); }
    private function entryKey(string $lang, string $device, string $url): string { return hash('sha256', $lang . "\0" . $device . "\0" . $url); }
    private function job(array $input): array
    {
        $id=$input['job']??'';
        if(!is_string($id)||!preg_match('/^[a-f0-9]{32}$/D',$id))throw new \InvalidArgumentException('生成任务无效');
        $job=$this->store->read('job-'.$id.'.json');
        if(!$job)throw new \InvalidArgumentException('生成任务不存在');
        $marks=array_values(array_unique(array_merge($job['options']['languages']??[],array_column($job['tasks']??[],'lang'))));
        (new LanguageAccessService($this->repository))->assertLanguages($marks);
        return $job;
    }
    private function summary(array $job): array { $job['total'] = count($job['tasks'] ?? []); unset($job['tasks']); $job['done'] = $job['state'] === 'complete'; return $job; }

    /** Called by the dedicated worker. The global store lock arbitrates with HTTP steps. */
    public function advancePending(int $batch = 1): array
    {
        $results = [];
        foreach ($this->manifest()['jobs'] as $id) {
            $job = $this->store->read('job-' . $id . '.json');
            if (!$job || $job['state'] === 'complete') continue;
            $next = $job['tasks'][$job['cursor'] ?? 0] ?? null;
            if ($next && !in_array($next['lang'], array_column($this->languages(), 'mark'), true)) continue;
            $results[] = $this->step(['job' => $id, 'batch' => $batch]);
            break;
        }
        return $results;
    }
}
