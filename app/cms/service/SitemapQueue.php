<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\CmsContext;
use app\cms\compat\Runtime;
use app\cms\repository\CmsRepository;

/** Durable cooperative Sitemap jobs. Intermediate records are private, never live entries. */
final class SitemapQueue extends SitemapService
{
    private const BATCH = 200;
    private const TERMINAL = ['complete','failed','superseded'];

    public function __construct(CmsRepository $repository, GeneratedFileStore $store)
    {
        parent::__construct($repository, $store->root());
        $this->store = $store;
    }

    public function enqueue(array $languages, array $formats, ?array $automatic = null): array
    {
        $units = [];
        foreach ($languages as $lang) {
            $site = $this->site($lang, 'pc');
            foreach (!empty($site->global['web_mobile_domain_open']) ? ['pc','mobile'] : ['pc'] as $device) {
                $selected = $device === 'pc' ? $site : $this->site($lang, $device);
                $enabled = array_values(array_filter($formats, fn($f) => ($f !== 'ai' || $device === 'pc') && $this->formatEnabled($this->settings($selected), $f)));
                if (!$enabled) continue;
                $units[] = ['lang'=>$lang,'device'=>$device,'origin'=>$selected->origin(),'base'=>(string)($selected->global['web_cmspath']??''),
                    'signature'=>$this->sitemapSignature($selected),'formats'=>$enabled,'phase'=>'home','cursor'=>null,'scanned'=>0,
                    'chunks'=>0,'links'=>0,'format_index'=>0,'piece_sequence'=>0];
            }
        }
        $job = ['version'=>1,'id'=>bin2hex(random_bytes(16)),'state'=>'pending','phase'=>'collect','languages'=>$languages,'formats'=>$formats,
            'created_at'=>time(),'updated_at'=>time(),'unit'=>0,'units'=>$units,'files'=>[],'failures'=>[],'automatic'=>$automatic];
        return $this->store->locked(function () use ($job): array {
            $manifest = $this->manifest();
            $revisions = [];
            foreach ($job['units'] as $unit) foreach ($unit['formats'] as $format) {
                $target = hash('sha256', $unit['lang'].':'.$unit['device'].':'.$format);
                $revisions[$target] = $job['id'];
            }
            $job['target_revisions'] = $revisions;
            $manifest['queue_revisions'] = array_replace($manifest['queue_revisions']??[], $revisions);
            $manifest['queue_jobs'][] = $job['id'];
            $this->store->writeBatch(['manifest.json'=>$manifest,'job-'.$job['id'].'.json'=>$job]);
            return $this->summaryOf($job) + ['queued'=>true,'message'=>'已加入后台生成队列'];
        });
    }

    public function summaries(array $allowed): array
    {
        $result = [];
        foreach (array_slice(array_reverse($this->manifest()['queue_jobs']??[]), 0, 20) as $id) {
            $job = $this->store->read('job-'.$id.'.json');
            if ($job && !array_diff($job['languages'], $allowed)) $result[] = $this->summaryOf($job);
        }
        return $result;
    }

    public function assertIdle(string $lang): void
    {
        foreach ($this->manifest()['queue_jobs']??[] as $id) {
            $job = $this->store->read('job-'.$id.'.json');
            if ($job && !in_array($job['state'], self::TERMINAL, true) && ($lang === '' || in_array($lang, $job['languages'], true))) {
                throw new \InvalidArgumentException('当前语言仍有待完成的地图任务，请等待任务结束后清理');
            }
        }
    }

    public function advance(): array
    {
        return $this->store->locked(function (): array {
            $manifest = $this->manifest();
            $enabled = array_column($this->languages(), 'mark');
            // Convert each original automatic record under the same store lock.
            foreach ($manifest['automatic']??[] as $key=>$task) {
                if ($task['state'] !== 'pending' || !in_array($task['lang'], $enabled, true)) continue;
                $existing = isset($task['job']) ? $this->store->read('job-'.$task['job'].'.json') : [];
                if ($existing && ($existing['automatic']['revision']??null) === $task['revision']) {
                    // Reconcile records created by an older worker before atomic receipts.
                    if (in_array($existing['state'], self::TERMINAL, true)) {
                        $this->automaticReceipt($existing);
                        return $this->summaryOf($existing);
                    }
                    continue;
                }
                try {
                    $site = $this->site($task['lang'], 'pc', $task['origin'], $task['base']);
                    if (($task['template_mode']??'eyou') !== \app\cms\template\TemplateMode::current($site)
                        || (isset($task['dialect_version']) && $task['dialect_version'] !== \app\cms\template\TemplateDialect::VERSION)
                        || ($task['site_signature']??'legacy') !== \app\cms\template\TemplateMode::siteSignature($site)) throw new \RuntimeException('地图任务身份已变化，请重新创建');
                    $settings = $this->settings($site);
                    if (empty($settings['sitemap_auto'])) {
                        $manifest['automatic'][$key]['state'] = 'complete';
                        $manifest['automatic'][$key]['result'] = ['complete'=>true,'skipped'=>true];
                        $this->store->write('manifest.json', $manifest);
                        return ['skipped'=>true];
                    }
                    $formats = array_values(array_filter(['xml','txt','html','ai'], fn($f) => $this->formatEnabled($settings, $f)));
                    $queued = $this->store->transaction(function () use ($task, $formats, $key): array {
                        $queued = $this->enqueue([$task['lang']], $formats, ['key'=>$key,'revision'=>$task['revision']]);
                        $manifest = $this->manifest();
                        $manifest['automatic'][$key]['job'] = $queued['id'];
                        $this->store->write('manifest.json', $manifest);
                        return $queued;
                    });
                    return $queued;
                } catch (\Throwable) {
                    $manifest['automatic'][$key]['state'] = 'failed';
                    $manifest['automatic'][$key]['result'] = ['complete'=>false,'failures'=>[['lang'=>$task['lang'],'message'=>'地图任务初始化失败，请核对语言、主题和配置']]];
                    $this->store->write('manifest.json', $manifest);
                    return ['failed'=>true];
                }
            }
            foreach ($manifest['queue_jobs']??[] as $id) {
                $job = $this->store->read('job-'.$id.'.json');
                if (!$job || in_array($job['state'], self::TERMINAL, true)) continue;
                if (array_diff($job['languages'], $enabled)) {
                    if ($job['state'] !== 'paused_language') {
                        $job['state'] = 'paused_language';
                        $this->store->write('job-'.$id.'.json', $job);
                    }
                    continue;
                }
                try {
                    $this->store->transaction(function () use (&$job, $manifest, $id): void {
                        if (($job['version']??null) !== 1) throw new \RuntimeException('地图任务版本不受支持，请使用对应版本处理');
                        if (!$this->currentRevision($job, $manifest)) $job['state'] = 'superseded';
                        else { $job['state'] = 'running'; $this->advanceUnit($job); }
                        $job['updated_at'] = time();
                        $this->store->write('job-'.$id.'.json', $job);
                        $this->automaticReceipt($job);
                    });
                } catch (\Throwable $error) {
                    // Re-read committed progress after a failed batch transaction.
                    $job = $this->store->read('job-'.$id.'.json', $job);
                    $job['state'] = 'failed';
                    $job['failures'][] = ['lang'=>$job['units'][$job['unit']]['lang']??($job['languages'][0]??''),
                        'phase'=>$job['phase'],'message'=>'地图生成失败，原有效结果已保留；请核对主题、配置及数据','error_type'=>get_class($error)];
                    $job['updated_at'] = time();
                    $this->store->transaction(function () use ($id, $job): void {
                        $this->store->write('job-'.$id.'.json', $job);
                        $this->automaticReceipt($job);
                    });
                }
                return $this->summaryOf($job);
            }
            return [];
        });
    }

    private function advanceUnit(array &$job): void
    {
        if ($job['unit'] >= count($job['units'])) { $this->publish($job); return; }
        $unit =& $job['units'][$job['unit']];
        $site = $this->site($unit['lang'], $unit['device'], $unit['origin'], $unit['base']);
        if ($this->sitemapSignature($site) !== $unit['signature']) throw new \RuntimeException('地图配置已变化');
        $previous = CmsContext::current();
        CmsContext::set($site); Runtime::boot($site);
        try {
            if (in_array($unit['phase'], ['home','types','archives','tags'], true)) $this->collect($job, $unit, $site);
            else $this->renderUnit($job, $unit, $site);
            $this->store->write('job-'.$job['id'].'.json', $job);
        } finally { CmsContext::set($previous); Runtime::boot($previous); }
    }

    private function name(array $job, string $suffix): string { return 'job-'.$job['id'].'-u'.$job['unit'].'-'.$suffix.'.json'; }

    private function collect(array &$job, array &$unit, CmsContext $site): void
    {
        $settings = $this->settings($site); $links = [];
        if ($unit['phase'] === 'home') {
            $links[] = $this->link($this->absolute((string)($site->global['lang_url']??(($site->global['web_cmspath']??'').'/')), $site), $site->global['web_name']??'', time(), 'index', $settings);
            $unit['phase'] = 'types';
        } elseif ($unit['phase'] === 'types') {
            $q = $this->repository->table('arctype')->where(['lang'=>$site->lang,'is_del'=>0,'status'=>1]);
            if (!empty($settings['sitemap_not1'])) $q->where('is_hidden', 0);
            if (!empty($settings['sitemap_not2'])) $q->where('is_part', 0);
            if ($unit['cursor'] !== null) $q->whereRaw('(sort_order > ? OR (sort_order = ? AND id > ?))', [$unit['cursor'][0],$unit['cursor'][0],$unit['cursor'][1]]);
            $rows = $q->order('sort_order asc,id asc')->limit(self::BATCH)->select()->toArray();
            foreach ($rows as $row) {
                $unit['cursor'] = [(int)$row['sort_order'],(int)$row['id']];
                if (!empty($row['typearcrank'])) continue;
                $this->store->write($this->name($job, 'type-'.$row['id']), $row);
                $url = (int)($row['is_part']??0) === 1 && !empty($row['typelink']) ? $row['typelink'] : typeurl('home/Lists/index', $row, true, true);
                $links[] = $this->link($this->absolute($url, $site), $row['typename'], time(), 'list', $settings);
            }
            if (count($rows) < self::BATCH) { $unit['phase'] = 'archives'; $unit['cursor'] = null; }
        } elseif ($unit['phase'] === 'archives') {
            $remaining = max(0, (int)$settings['sitemap_archives_num'] - $unit['scanned']);
            $q = $this->repository->table('archives')->where(['lang'=>$site->lang,'status'=>1,'is_del'=>0,'arcrank'=>0])->whereIn('channel', $site->getConfig('global.allow_release_channel', []));
            if (!empty($settings['sitemap_not2'])) $q->where('is_jump', 0);
            if ($unit['cursor'] !== null) $q->where('aid', '<', $unit['cursor']);
            $rows = $remaining ? $q->order('aid desc')->limit(min(self::BATCH, $remaining))->select()->toArray() : [];
            foreach ($rows as $row) {
                $unit['cursor'] = (int)$row['aid']; $unit['scanned']++;
                $type = $this->store->read($this->name($job, 'type-'.$row['typeid']));
                if (!$type || (int)$type['current_channel'] !== (int)$row['channel']) continue;
                $url = !empty($row['is_jump']) && !empty($row['jumplinks']) ? $row['jumplinks'] : arcurl('home/View/index', array_replace($type, $row), true, true);
                $links[] = $this->link($this->absolute($url, $site), $row['title'], (int)($row['update_time']?:$row['add_time']), 'view', $settings);
            }
            if (count($rows) < self::BATCH || $unit['scanned'] >= (int)$settings['sitemap_archives_num']) { $unit['phase'] = 'tags'; $unit['cursor'] = null; $unit['scanned'] = 0; }
        } else {
            $remaining = max(0, (int)$settings['sitemap_tags_num'] - $unit['scanned']);
            $root = \app\cms\ThemeCatalog::root((string)($site->global['web_tpl_theme']??'default'), 'pc');
            $q = $this->repository->table('tagindex')->where('lang', $site->lang);
            if ($unit['cursor'] !== null) $q->whereRaw('(add_time < ? OR (add_time = ? AND id < ?))', [$unit['cursor'][0],$unit['cursor'][0],$unit['cursor'][1]]);
            $rows = $remaining && is_file($root.'/lists_tags.htm') ? $q->order('add_time desc,id desc')->limit(min(self::BATCH, $remaining))->select()->toArray() : [];
            foreach ($rows as $row) {
                $unit['cursor'] = [(int)$row['add_time'],(int)$row['id']]; $unit['scanned']++;
                $links[] = $this->link($this->absolute(tagurl('home/Tags/lists', ['tagid'=>$row['id']], true, true), $site), (string)($row['tag']??$row['tagname']??''), (int)(($row['update_time']??0)?:$row['add_time']), 'view', $settings) + ['tag'=>true];
            }
            if (count($rows) < self::BATCH || $unit['scanned'] >= (int)$settings['sitemap_tags_num']) { $unit['phase'] = 'render'; $unit['cursor'] = null; $job['phase'] = 'render'; }
        }
        $keys = [];
        foreach ($links as $link) {
            $link['mobile_mode'] = !empty($site->global['web_mobile_domain_open']) ? ($site->device === 'mobile' ? 'mobile' : 'pc') : (!empty($site->global['response_type']) ? 'htmladapt' : 'pc,mobile');
            if ($site->device === 'mobile') $link['url'] = pc_to_mobile_url($link['url']);
            if (!preg_match('#^https?://[^/]+(?:/|$)#i', $link['url']) || preg_match('/[\x00-\x20\x7f]/', $link['url'])) continue;
            $hash = hash('sha256', $link['url']); $name = $this->name($job, 'link-'.$hash);
            if (!$this->store->read($name)) { $keys[] = $hash; $unit['links']++; }
            $this->store->write($name, $link);
        }
        if ($keys) { $this->store->write($this->name($job, 'keys-'.$unit['chunks']), $keys); $unit['chunks']++; }
    }

    private function linkChunk(array $job, int $chunk): array
    {
        $links = [];
        foreach ($this->store->read($this->name($job, 'keys-'.$chunk)) as $hash) $links[] = $this->store->read($this->name($job, 'link-'.$hash));
        return $links;
    }

    private function renderUnit(array &$job, array &$unit, CmsContext $site): void
    {
        $format = $unit['formats'][$unit['format_index']] ?? null;
        if ($format === null) { $job['unit']++; $job['phase'] = $job['unit'] < count($job['units']) ? 'collect' : 'publish'; return; }
        $unit['render'] ??= ['chunk'=>0,'parts'=>[['pieces'=>[],'links'=>0,'size'=>150]],'output'=>0,'assembled'=>false];
        $render =& $unit['render'];
        if (in_array($format, ['xml','txt'], true) && $render['chunk'] < $unit['chunks']) {
            $buffers = [];
            foreach ($this->linkChunk($job, $render['chunk']) as $link) {
                $node = $format === 'xml' ? $this->xmlNode($link) : $link['url']."\r\n";
                $index = count($render['parts']) - 1;
                if ($format === 'xml' && $render['parts'][$index]['links'] && ($render['parts'][$index]['links'] >= 50000 || $render['parts'][$index]['size'] + strlen($node) > 49 * 1024 * 1024)) {
                    $render['parts'][] = ['pieces'=>[],'links'=>0,'size'=>150]; $index++;
                }
                $buffers[$index] = ($buffers[$index]??'').$node;
                $render['parts'][$index]['links']++; $render['parts'][$index]['size'] += strlen($node);
            }
            foreach ($buffers as $index=>$bytes) {
                $sequence = $unit['piece_sequence']++;
                $this->store->write($this->name($job, 'piece-'.$sequence), ['bytes'=>$bytes]);
                $render['parts'][$index]['pieces'][] = $sequence;
            }
            $render['chunk']++;
            return;
        }
        if (in_array($format, ['html','ai'], true)) {
            $links = [];
            if ($format === 'html') for ($i = 0; $i < $unit['chunks']; $i++) array_push($links, ...$this->linkChunk($job, $i));
            foreach ($this->documents($site, $links, $format) as $document) $this->stageDocument($job, $unit, $site, $format, $document);
            $unit['format_index']++; unset($unit['render']); return;
        }
        $url = $this->urls($site)[$format]; $count = count($render['parts']);
        if ($render['output'] < $count) {
            $part = $render['parts'][$render['output']]; $body = '';
            foreach ($part['pieces'] as $sequence) $body .= $this->store->read($this->name($job, 'piece-'.$sequence))['bytes'];
            if ($format === 'xml') $body = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0">'.$body.'</urlset>';
            $outputUrl = $count > 1 ? substr($url, 0, -4).'-'.($render['output']+1).'.xml' : $url;
            $this->stageDocument($job, $unit, $site, $format, ['url'=>$outputUrl,'body'=>$body,'links'=>$part['links']]);
            $render['output']++; return;
        }
        if ($format === 'xml' && $count > 1 && !$render['assembled']) {
            $body = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            for ($i = 1; $i <= $count; $i++) $body .= '<sitemap><loc>'.$this->escape($site->origin().substr($url, 0, -4).'-'.$i.'.xml').'</loc><lastmod>'.date('Y-m-d').'</lastmod></sitemap>';
            $this->stageDocument($job, $unit, $site, $format, ['url'=>$url,'body'=>$body.'</sitemapindex>','links'=>$unit['links']]);
            $render['assembled'] = true; return;
        }
        $unit['format_index']++; unset($unit['render']);
    }

    private function stageDocument(array &$job, array $unit, CmsContext $site, string $format, array $document): void
    {
        $this->assertPublicDestinationFree(['url'=>$document['url'],'base'=>$unit['base']]);
        $object = $this->store->put($document['body'], $format === 'ai' ? 'html' : $format);
        unset($document['body']);
        $job['files'][] = $object + $document + ['lang'=>$unit['lang'],'device'=>$unit['device'],'format'=>$format,'signature'=>$this->sitemapSignature($site),'generated_at'=>time()];
    }

    private function publish(array &$job): void
    {
        $manifest = $this->manifest();
        if (!$this->currentRevision($job, $manifest)) { $job['state'] = 'superseded'; return; }
        foreach ($job['units'] as $unit) {
            $site = $this->site($unit['lang'], $unit['device'], $unit['origin'], $unit['base']);
            if ($this->sitemapSignature($site) !== $unit['signature']) throw new \RuntimeException('发布前地图配置已变化');
        }
        $urls = array_column($job['files'], 'url');
        foreach ($manifest['entries'] as $key=>$entry) if (in_array($entry['lang'], $job['languages'], true) && in_array($entry['format'], $job['formats'], true) && !in_array($entry['url'], $urls, true)) unset($manifest['entries'][$key]);
        foreach ($job['files'] as $file) { $manifest['entries'][hash('sha256', $file['url'])] = $file; $manifest['objects'][$file['object']] = $file; }
        $this->store->write('manifest.json', $manifest);
        $job['state'] = 'complete'; $job['phase'] = 'complete';
        $this->store->write('job-'.$job['id'].'.json', $job);
    }

    private function currentRevision(array $job, array $manifest): bool
    {
        $automatic = $job['automatic'] ?? null;
        if ($automatic && ($manifest['automatic'][$automatic['key']]['revision']??null) !== $automatic['revision']) return false;
        foreach ($job['target_revisions']??[] as $target=>$revision) if (($manifest['queue_revisions'][$target]??null) !== $revision) return false;
        return true;
    }
    private function automaticReceipt(array $job): void
    {
        if (!$job['automatic']) return;
        $manifest = $this->manifest();
        if (!$this->currentRevision($job, $manifest)) return;
        $key = $job['automatic']['key'];
        $manifest['automatic'][$key]['phase'] = $job['phase'];
        if (in_array($job['state'], self::TERMINAL, true)) {
            $manifest['automatic'][$key]['state'] = $job['state'];
            $manifest['automatic'][$key]['finished_at'] = time();
            $manifest['automatic'][$key]['result'] = ['complete'=>$job['state']==='complete','failures'=>$job['failures']];
        }
        $this->store->write('manifest.json', $manifest);
    }
    private function summaryOf(array $job): array
    {
        return array_intersect_key($job, array_flip(['id','state','phase','languages','formats','created_at','updated_at','unit','failures'])) +
            ['units'=>count($job['units']),'links'=>array_sum(array_column($job['units'], 'links')),'files'=>array_map(static fn($file) => array_intersect_key($file, array_flip(['url','lang','format','links','bytes','generated_at'])), $job['files']), 'complete'=>$job['state']==='complete'];
    }
}
