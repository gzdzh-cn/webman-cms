<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

class ContentService
{
    public function __construct(private ?CmsRepository $repository = null)
    {
        $this->repository ??= new CmsRepository();
    }

    public function save(array $input, int $adminId, ?int $aid = null): int
    {
        $input = self::normalizeInput($input);
        return $this->repository->transaction(function (CmsRepository $repository) use ($input, $adminId, $aid) {
            $existing = $aid ? $repository->table('archives')->where('aid', $aid)->find() : null;
            if ($aid && !$existing) { throw new InvalidArgumentException('文档不存在'); }
            $typeid = (int)($input['typeid'] ?? $existing['typeid'] ?? 0);
            $category = $repository->table('arctype')->where(['id' => $typeid, 'is_del' => 0])->find();
            if (!$category) { throw new InvalidArgumentException('栏目不存在'); }
            $model = $repository->table('channeltype')->where('id', $category['current_channel'])->find();
            if (!$model || (int)$model['id'] === 8 || (int)$model['id'] === 51) { throw new InvalidArgumentException('此模型不能作为普通文档保存'); }
            // Single-page writers (including saveSingle) lock the owning
            // category before its document, so generic and explicit saves agree.
            if ((int)$model['id'] === 6) {
                $lockedCategory = $repository->table('arctype')->where('id', $typeid)->lock(true)->find();
                if (!$lockedCategory || !empty($lockedCategory['is_del']) || (int)$lockedCategory['current_channel'] !== 6 || $lockedCategory['lang'] !== $category['lang']) throw new InvalidArgumentException('单页栏目状态已改变，请重新读取');
            }
            if ($aid) {
                $locked = $repository->table('archives')->where('aid', $aid)->lock(true)->find();
                if (!$locked || (int)$locked['typeid'] !== (int)$existing['typeid']) throw new InvalidArgumentException('文档归属已改变，请重新读取');
                $existing = $locked;
            }
            if ((int)$model['id'] === 6) {
                // Category addData already owns one blank single-page document.
                // Generic callers must update it just like saveSingle(), while
                // imported columns without a document can still create one.
                $singles = $repository->table('archives')->where(['typeid' => $typeid, 'is_del' => 0])->lock(true)->select()->toArray();
                if (count($singles) > 1 || ($aid && $singles && (int)$singles[0]['aid'] !== $aid)) throw new InvalidArgumentException('单页栏目存在多个或冲突的文档，请先修复数据');
                if (!$aid && $singles) { $existing = $singles[0]; $aid = (int)$existing['aid']; }
            }
            if ($existing && (int)$existing['channel'] !== (int)$model['id']) { throw new InvalidArgumentException('不能将文档移动到不同内容模型'); }
            if ($existing && $existing['lang'] !== $category['lang']) { throw new InvalidArgumentException('不能将文档移动到不同语言的栏目'); }
            $addonTable = $model['table'] . '_content';
            // Missing tables are an explicit schema error; never move addon fields into archives.
            $addonColumns = $repository->columns($addonTable);
            $newDocument = !$existing;
            $access = new ContentAccessService($repository);
            $access->assertCategory($category);
            if ($existing) $access->assertArchive($existing);
            // Check the locked old row, not an earlier controller read: editing
            // a title/body must not become an alternate review endpoint.
            $context=\app\cms\CmsContext::current();
            if ($existing && array_key_exists('arcrank',$input) && $context->module==='admin' && $context->request!==null) {
                \app\cms\admin\api\CmsWritePolicy::assertArchiveReviewChange((int)$existing['arcrank'],$input['arcrank'],(array)(admin()['roles']??[]));
            }
            $main = $repository->filter('archives', $input);
            if (!$aid) {
                foreach ($repository->columns('archives') as $name => $column) {
                    if (!array_key_exists($name, $main) && $column['Default'] === null && preg_match('/char|text/i', $column['Type'])) { $main[$name] = ''; }
                }
            }
            unset($main['admin_id'], $main['users_id'], $main['is_del'], $main['del_method'], $main['attrlist_id']);
            $main['channel'] = (int)$category['current_channel']; $main['typeid'] = $typeid; $main['lang'] = $category['lang'] ?: (new LanguagePolicy($this->repository))->main();
            $main['update_time'] = time();
            if (array_key_exists('add_time', $input)) $main['add_time'] = self::timestamp($input['add_time'], '发布时间');
            if (!$aid) {
                $main += ['admin_id' => $adminId, 'lang' => $category['lang'] ?: (new LanguagePolicy($this->repository))->main(), 'add_time' => time(), 'status' => 1, 'arcrank' => 0, 'sort_order' => 100];
                if (!array_key_exists('click', $input)) $main['click'] = (new InitialCounterService($repository))->draw('other_arcclick', $main['lang']);
            }
            $title = trim((string)($main['title'] ?? $existing['title'] ?? ''));
            if ($title === '') { throw new InvalidArgumentException('文档标题不能为空'); }
            $main['title'] = $title;
            if (array_key_exists('content', $input) && trim((string)($main['seo_description'] ?? $existing['seo_description'] ?? '')) === '') {
                $description = preg_replace('/\s+/u', ' ', strip_tags(htmlspecialchars_decode((string)$input['content'], ENT_QUOTES)));
                $main['seo_description'] = mb_substr(trim($description), 0, 200);
            }
            if (empty($model['is_repeat_title'])) {
                $duplicate = $repository->table('archives')->where(['title' => $title, 'channel' => (int)$model['id'], 'is_del' => 0]);
                if ($aid) { $duplicate->where('aid', '<>', $aid); }
                if ($duplicate->find()) { throw new InvalidArgumentException('同模型文档标题不能重复'); }
            }
            $fields = $repository->table('channelfield')->where(['channel_id' => (int)$model['id'], 'ifeditable' => 1, 'status' => 1])->select()->toArray();
            $addonInput = [];
            $existingAddon = $aid ? $repository->table($addonTable)->where('aid', $aid)->find() : null;
            $provenance = new HtmltextProvenanceService($repository);
            $submittedSources = HtmltextProvenanceService::inputMetadata($input);
            if (isset($input['__htmltext_format']) && $input['__htmltext_format'] !== 'raw-v1') throw new InvalidArgumentException('富文本提交格式无效');
            $newHtmltextSource = ($input['__htmltext_format'] ?? null) === 'raw-v1' ? 'raw' : 'unknown';
            $htmlFields = [];
            foreach ($fields as $field) if ($field['dtype'] === 'htmltext') $htmlFields[$field['name']] = !empty($field['ifmain']) ? 'archives' : $addonTable;
            foreach (['content','content_ey_m'] as $field) if (isset($addonColumns[$field])) $htmlFields[$field] = $addonTable;
            $htmlSources = [];
            foreach ($htmlFields as $field => $table) {
                if (!array_key_exists($field,$input)) continue;
                if (!is_scalar($input[$field]) && $input[$field] !== null) throw new InvalidArgumentException('富文本内容格式无效：'.$field);
                $input[$field] = (string)$input[$field];
                $before = (string)(($table === 'archives' ? $existing : $existingAddon)[$field] ?? '');
                if (isset($submittedSources[$field]) && !is_array($submittedSources[$field])) throw new InvalidArgumentException('富文本来源参数格式无效');
                $htmlSources[$field] = $newDocument ? $newHtmltextSource : $provenance->sourceForWrite($table,'aid',(int)$aid,$field,$before,$input[$field],$submittedSources[$field]??null);
            }
            foreach ($fields as $field) {
                $name = $field['name'];
                // System timestamps are normalized above; legacy model metadata
                // may call add_time an int and must not cast the original date string.
                if (in_array($name, ['add_time', 'update_time'], true)) continue;
                if (!array_key_exists($name, $input)) {
                    if (!empty($field['ifrequire'])) {
                        $previous = !empty($field['ifmain']) ? ($existing[$name] ?? '') : ($existingAddon[$name] ?? '');
                        if ($previous === '' || $previous === null) { throw new InvalidArgumentException($field['title'] . '不能为空'); }
                    }
                    continue;
                }
                $value = $this->fieldValue($field, $input[$name], $input);
                if (!empty($field['ifrequire']) && ($value === '' || $value === null)) { throw new InvalidArgumentException($field['title'] . '不能为空'); }
                if (empty($field['ifmain']) && isset($addonColumns[$name])) { $addonInput[$name] = $value; }
                elseif (!empty($field['ifmain']) && array_key_exists($name, $main)) { $main[$name] = $value; }
            }
            foreach (['content', 'content_ey_m'] as $name) {
                if (array_key_exists($name, $input) && isset($addonColumns[$name])) { $addonInput[$name] = (string)$input[$name]; }
            }
            if ($aid) { $repository->table('archives')->where('aid', $aid)->update($main); }
            else { $aid = (int)$repository->table('archives')->insertGetId($main); }
            $addon = $repository->filter($addonTable, $addonInput);
            $addon['aid'] = $aid;
            if (isset($addonColumns['typeid'])) { $addon['typeid'] = $typeid; }
            if (isset($addonColumns['update_time'])) { $addon['update_time'] = time(); }
            if ($repository->table($addonTable)->where('aid', $aid)->find()) {
                $repository->table($addonTable)->where('aid', $aid)->update($addon);
            } else {
                foreach (['content', 'content_ey_m'] as $text) { if (isset($addonColumns[$text])) { $addon[$text] ??= ''; } }
                if (isset($addonColumns['add_time'])) { $addon['add_time'] = time(); }
                $repository->table($addonTable)->insert($addon);
            }
            foreach ($htmlFields as $field => $table) {
                if (isset($htmlSources[$field])) $provenance->markTrusted($table,'aid',$aid,$field,(string)$input[$field],$htmlSources[$field]);
                elseif ($newDocument && $table === $addonTable && array_key_exists($field,$addon)) $provenance->markTrusted($table,'aid',$aid,$field,(string)$addon[$field],$newHtmltextSource);
            }
            if (array_key_exists('download_files', $input)) { (new AttachmentService($repository))->replace($aid, $model['nid'], $input['download_files']); }
            if (array_key_exists('media_files', $input)) { (new AttachmentService($repository))->replace($aid, $model['nid'], $input['media_files']); }
            if (array_key_exists('tags', $input)) { $this->saveTags($aid, $typeid, (string)$input['tags'], (int)($main['arcrank'] ?? $existing['arcrank'] ?? 0), $main['lang'] ?? $existing['lang'] ?? $category['lang']); }
            if (array_key_exists('litpic_list', $input)) { $this->saveImages($aid, $model['nid'], $input['litpic_list'], $title); }
            if (array_key_exists('product_parameters', $input) || array_key_exists('attrlist_id', $input)) {
                $parameters = array_key_exists('product_parameters', $input) ? $input['product_parameters'] : [];
                if (is_string($parameters)) { $parameters = json_decode($parameters, true, 32, JSON_THROW_ON_ERROR); }
                if (!is_array($parameters)) { throw new InvalidArgumentException('产品参数格式错误'); }
                if (array_key_exists('attrlist_id', $input)) {
                    if (array_key_exists('attrlist_id', $parameters)) {
                        $top = $input['attrlist_id']; $nested = $parameters['attrlist_id'];
                        if ((!is_int($top) && !is_string($top)) || (!is_int($nested) && !is_string($nested)) || !ctype_digit((string)$top) || !ctype_digit((string)$nested) || (int)$top !== (int)$nested) { throw new InvalidArgumentException('顶层与产品参数内的参数组冲突'); }
                    }
                    $parameters['attrlist_id'] = $input['attrlist_id'];
                }
                (new ProductParameterService($repository))->saveProduct($aid, $parameters, $category['lang']);
            }
            $this->refreshCount($typeid);
            if ($existing && (int)$existing['typeid'] !== $typeid) {
                $repository->table('taglist')->where('aid', $aid)->update(['typeid' => $typeid, 'update_time' => time()]);
                $this->refreshCount((int)$existing['typeid']);
            }
            return $aid;
        });
    }

    /** Match original addon aliases once, shared with the controller write policy. */
    public static function normalizeInput(array $input): array
    {
        foreach (['addonFieldExt', 'addonField'] as $key) { if (isset($input[$key]) && is_array($input[$key])) { $input += $input[$key]; } }
        return $input;
    }

    public static function timestamp(mixed $value, string $label): int
    {
        if (is_int($value) || (is_string($value) && preg_match('/^[0-9]{1,11}$/D', $value))) {
            $epoch = (int)$value;
            if ($epoch >= 0 && $epoch <= 253402300799) return $epoch;
        }
        if (is_string($value)) {
            foreach (['!Y-m-d H:i:s', '!Y-m-d\\TH:i:s', '!Y-m-d\\TH:i', '!Y-m-d'] as $format) {
                $date = \DateTimeImmutable::createFromFormat($format, $value);
                $errors = \DateTimeImmutable::getLastErrors();
                if ($date && ($errors === false || (!$errors['warning_count'] && !$errors['error_count'])) && $date->getTimestamp() >= 0) return $date->getTimestamp();
            }
        }
        throw new ContentValidationException([$label === '发布时间' ? 'add_time' : $label => $label . '格式错误']);
    }

    /** Move a selected batch atomically; original addon/attachment IDs and values survive. */
    public function move(array $ids, int $typeid, int $adminId): int
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn(int $id): bool => $id > 0)));
        if (!$ids || $typeid <= 0) { throw new InvalidArgumentException('请选择文档和目标栏目'); }
        return $this->repository->transaction(function (CmsRepository $repository) use ($ids, $typeid, $adminId) {
            $category = $repository->table('arctype')->where(['id' => $typeid, 'is_del' => 0])->lock(true)->find();
            if (!$category) { throw new InvalidArgumentException('目标栏目不存在'); }
            (new ContentAccessService($repository))->assertCategory($category);
            $items = $repository->table('archives')->whereIn('aid', $ids)->lock(true)->select()->toArray();
            foreach ($items as $item) (new ContentAccessService($repository))->assertArchive($item);
            if (count($items) !== count($ids)) { throw new InvalidArgumentException('部分文档不存在'); }
            foreach ($items as $item) {
                if (!empty($item['is_del'])) { throw new InvalidArgumentException('请先还原回收站内的文档'); }
                if ((int)$item['channel'] !== (int)$category['current_channel']) { throw new InvalidArgumentException('只能移动相同内容模型的文档'); }
                if ($item['lang'] !== $category['lang']) { throw new InvalidArgumentException('不能将文档移动到不同语言的栏目'); }
            }
            foreach ($items as $item) { $this->save(['typeid' => $typeid], $adminId, (int)$item['aid']); }
            return count($items);
        });
    }

    public function saveSingle(int $typeid, array $input, int $adminId): int
    {
        return $this->repository->transaction(function (CmsRepository $repository) use ($typeid, $input, $adminId) {
            $category = $repository->table('arctype')->where('id', $typeid)->lock(true)->find();
            if (!$category || (int)$category['current_channel'] !== 6) { throw new InvalidArgumentException('单页栏目不存在'); }
            (new ContentAccessService($repository))->assertCategory($category);
            $main = array_intersect_key($input, array_flip(['typename', 'litpic', 'seo_title', 'seo_keywords', 'seo_description', 'typelink']));
            if ($main) { $repository->table('arctype')->where('id', $typeid)->update($main + ['update_time' => time()]); }
            $archive = $repository->table('archives')->where(['typeid' => $typeid, 'is_del' => 0])->find();
            $input['title'] = $input['typename'] ?? $category['typename']; $input['typeid'] = $typeid;
            return $this->save($input, $adminId, $archive ? (int)$archive['aid'] : null);
        });
    }

    public function recycle(array $ids, bool $restore = false): void
    {
        $this->repository->transaction(function (CmsRepository $repository) use ($ids, $restore) {
            $items = $repository->table('archives')->whereIn('aid', $ids)->lock(true)->select()->toArray();
            foreach ($items as $item) {
                (new ContentAccessService($repository))->assertArchive($item);
                if ($restore && !$repository->table('arctype')->where(['id' => $item['typeid'], 'is_del' => 0])->find()) { throw new InvalidArgumentException('请先恢复文档所属栏目'); }
                $repository->table('archives')->where('aid', $item['aid'])->update(['is_del' => $restore ? 0 : 1, 'del_method' => $restore ? 0 : 1, 'update_time' => time()]);
                $this->refreshCount((int)$item['typeid']);
            }
        });
    }

    public function refreshCount(int $typeid): void
    {
        // Imported legacy schemas calculate counts without this optional cache column.
        if (!isset($this->repository->columns('arctype')['total_arc'])) return;
        $count = $this->repository->table('archives')->where(['typeid' => $typeid, 'is_del' => 0, 'status' => 1])->where('arcrank', '>=', 0)->count();
        $this->repository->table('arctype')->where('id', $typeid)->update(['total_arc' => $count]);
    }

    public function purge(array $ids): void
    {
        $this->repository->transaction(function (CmsRepository $repository) use ($ids) {
            $items = $repository->table('archives')->whereIn('aid', $ids)->lock(true)->select()->toArray();
            foreach ($items as $item) {
                (new ContentAccessService($repository))->assertArchive($item);
                if (empty($item['is_del'])) { throw new InvalidArgumentException('只能彻底删除回收站内的文档'); }
                $model = $repository->table('channeltype')->where('id', $item['channel'])->find();
                if (!$model) { throw new InvalidArgumentException('文档模型不存在'); }
                $aid = (int)$item['aid'];
                if ($model['nid'] === 'special') {
                    $engine = $repository->connection()->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$repository->fullName('special_node')]);
                    if (strtolower((string)($engine[0]['ENGINE'] ?? '')) !== 'innodb') { throw new InvalidArgumentException('专题节点表必须使用InnoDB事务引擎'); }
                }
                $htmlMain=[]; $htmlAddon=['content','content_ey_m'];
                foreach ($repository->table('channelfield')->where(['channel_id'=>(int)$item['channel'],'dtype'=>'htmltext'])->select()->toArray() as $field) {
                    if (!empty($field['ifmain'])) $htmlMain[]=$field['name']; else $htmlAddon[]=$field['name'];
                }
                $provenance=new HtmltextProvenanceService($repository);
                $provenance->forgetRecord('archives','aid',$aid,$htmlMain);
                $provenance->forgetRecord($model['table'].'_content','aid',$aid,$htmlAddon);
                if ($model['nid'] === 'product') { (new ProductParameterService($repository))->purgeProduct($aid, $item['lang']); }
                $tagIds = $repository->table('taglist')->where('aid', $aid)->column('tid');
                foreach (array_unique([$model['table'] . '_content', 'taglist', 'product_img', 'images_upload', 'download_file', 'media_file']) as $table) {
                    if (isset($repository->columns($table)['aid'])) { $repository->table($table)->where('aid', $aid)->delete(); }
                }
                // Original Special::afterDel removes only this topic's nodes. Keep
                // it in the same archive/body/tag transaction; recycle retains them.
                if ($model['nid'] === 'special') { $repository->table('special_node')->where('aid', $aid)->delete(); }
                $repository->table('archives')->where('aid', $aid)->delete();
                foreach ($tagIds as $tid) { $repository->table('tagindex')->where('id', $tid)->update(['total' => $repository->table('taglist')->where('tid', $tid)->count()]); }
                $this->refreshCount((int)$item['typeid']);
            }
        });
    }

    private function saveTags(int $aid, int $typeid, string $tags, int $arcrank, string $lang): void
    {
        $repository = $this->repository;
        $old = $repository->table('taglist')->where('aid', $aid)->column('tid');
        $repository->table('taglist')->where('aid', $aid)->delete();
        $seen = [];
        foreach (explode(',', str_replace('，', ',', strip_tags($tags))) as $tag) {
            $tag = trim($tag); $key = mb_strtolower($tag);
            if ($tag === '' || isset($seen[$key])) { continue; } $seen[$key] = true;
            $index = $repository->table('tagindex')->where(['tag' => $tag, 'lang' => $lang])->lock(true)->find();
            $tid = $index ? (int)$index['id'] : (int)$repository->table('tagindex')->insertGetId(['tag' => $tag, 'typeid' => $typeid, 'lang' => $lang, 'weekup' => time(), 'monthup' => time(), 'add_time' => time(), 'update_time' => time()]);
            $repository->table('taglist')->insert(['tid' => $tid, 'aid' => $aid, 'typeid' => $typeid, 'tag' => $tag, 'arcrank' => $arcrank, 'lang' => $lang, 'add_time' => time(), 'update_time' => time()]);
            $old[] = $tid;
        }
        foreach (array_unique($old) as $tid) {
            $repository->table('tagindex')->where('id', $tid)->update(['total' => $repository->table('taglist')->where('tid', $tid)->count(), 'update_time' => time()]);
        }
    }

    private function saveImages(int $aid, string $model, mixed $images, string $documentTitle): void
    {
        $table = $model === 'images' ? 'images_upload' : 'product_img';
        if (is_string($images)) { $images = json_decode($images, true, 512, JSON_THROW_ON_ERROR); }
        if (!is_array($images)) { throw new InvalidArgumentException('图片列表格式错误'); }
        $this->repository->table($table)->where('aid', $aid)->delete();
        foreach (array_values($images) as $position => $image) {
            if (!is_array($image) || trim((string)($image['image_url'] ?? '')) === '') { continue; }
            // Original ProductImg/ImagesUpload saveimg uses the parent document
            // title, never the caption. Owner, keys and order are server owned.
            $data = array_intersect_key($image, array_flip(['image_url', 'intro', 'width', 'height', 'filesize', 'mime', 'add_time']));
            $data = array_replace($data, ['aid' => $aid, 'title' => $documentTitle, 'sort_order' => $position + 1, 'update_time' => time()]);
            $data += ['add_time' => time()];
            $this->repository->table($table)->insert($this->repository->filter($table, $data));
        }
    }

    private function fieldValue(array $field, mixed $value, array $input): mixed
    {
        return match ($field['dtype']) {
            'int', 'switch' => (int)$value,
            'checkbox', 'checkboxs', 'files' => is_array($value) ? implode(',', array_filter($value, 'is_scalar')) : $value,
            'imgs' => is_array($value) ? serialize(array_map(fn($url, $index) => ['image_url' => $url, 'intro' => $input[$field['name'] . '_eyou_intro'][$index] ?? ''], $value, array_keys($value))) : $value,
            'img' => isset($input[$field['name'] . '_eyou_is_remote']) ? ($input[$field['name'] . ($input[$field['name'] . '_eyou_is_remote'] ? '_eyou_remote' : '_eyou_local')] ?? '') : $value,
            'datetime' => ($value === '' || $value === null) ? '' : self::timestamp($value, (string)$field['title']),
            default => is_array($value) ? implode(',', array_filter($value, 'is_scalar')) : $value,
        };
    }
}
