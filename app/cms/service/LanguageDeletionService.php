<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/**
 * Enterprise subset of EyouCMS Language::afterDel / delLangData.
 * 易优CMS 版权所有 2016-2028 海南赞赞网络科技有限公司，并保留所有权利。
 * https://www.eyoucms.com — retain the upstream license for commercial use.
 * Related rows are removed in one transaction; shared assets are retained.
 */
final class LanguageDeletionService
{
    private const LANGUAGE_TABLES = [
        'language_attr', 'language_pack', 'arcrank', 'config', 'setting', 'config_attribute',
        'guestbook_attr', 'guestbook_attribute', 'guestbook', 'product_attribute',
        'shop_product_attribute', 'shop_product_attrlist', 'special_node', 'form',
        'ad', 'ad_position', 'links', 'links_group', 'ui_config', 'taglist', 'tagindex',
        'smtp_tpl', 'smtp_record', 'sms_template', 'sms_log', 'language_archives_copy_log',
    ];
    private const DOCUMENT_TABLES = [
        'article_content', 'download_content', 'download_file', 'images_content', 'images_upload',
        'media_content', 'media_file', 'product_attr', 'product_content', 'product_custom_param',
        'product_img', 'shop_product_attr', 'single_content', 'special_content',
    ];

    public function __construct(private ?CmsRepository $repository = null, private ?UiLanguageDeletionCache $uiCache = null)
    {
        $this->repository ??= new CmsRepository();
        $this->uiCache ??= new UiLanguageDeletionCache($this->repository);
    }

    public function delete(array $ids, bool $confirmed = false): array
    {
        if (!$confirmed) throw new InvalidArgumentException('请明确确认永久删除该语言及其企业网站数据');
        foreach ($ids as $id) if ((!is_int($id) && (!is_string($id) || !ctype_digit($id))) || (int)$id <= 0) throw new InvalidArgumentException('语言参数有误');
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) throw new InvalidArgumentException('请选择要删除的语言');
        $result = $this->uiCache->run([], function (callable $prepareCache) use ($ids): array {
            // Keep post-commit generic cache invalidation outside the UI file
            // compensator: its failure cannot roll back committed DB writes.
            return $this->repository->connection()->transaction(function () use ($ids, $prepareCache): array {
                $r = $this->repository;
                // Use the same first lock as structure creation and language copying.
                $languages = $r->table('language')->order('id asc')->lock(true)->select()->toArray();
                $targets = array_values(array_filter($languages, static fn(array $row): bool => in_array((int)$row['id'], $ids, true)));
                if (count($targets) !== count($ids)) throw new InvalidArgumentException('语言不存在，请刷新后重试');
                foreach ($targets as $row) {
                    if ((int)$row['id'] === (int)$languages[0]['id'] || !empty($row['is_home_default']) || !empty($row['is_admin_default'])) {
                        throw new InvalidArgumentException('禁止删除主体语言、前台默认语言或后台默认语言');
                    }
                }
                $marks = array_column($targets, 'mark');
                (new LanguageAccessService($r))->assertLanguages($marks);
                if ($r->table('guestbook')->whereIn('lang', $marks)->where('users_id', '>', 0)->lock(true)->find()) {
                    throw new InvalidArgumentException('目标语言包含会员留言，已取消删除');
                }
                $categories = $r->table('arctype')->whereIn('lang', $marks)->lock(true)->select()->toArray();
                $types = array_map('intval', array_column($categories, 'id'));
                $documents = $r->table('archives')->whereIn('lang', $marks)->lock(true)->select()->toArray();
                $aids = array_map('intval', array_column($documents, 'aid'));
                $models = array_column($r->table('channeltype')->select()->toArray(), null, 'id');
                foreach ($categories as $category) {
                    $model = $models[$category['current_channel']] ?? null;
                    if (!$model || ((int)$model['id'] !== 8 && !$this->enterpriseModel($model))) {
                        throw new InvalidArgumentException('目标语言包含非企业栏目模型，请先处理');
                    }
                }
                $bodyTables = self::DOCUMENT_TABLES;
                foreach ($documents as $document) {
                    $model = $models[$document['channel']] ?? null;
                    if (!$model || !$this->enterpriseModel($model) || !empty($document['users_id']) || !empty($document['merchant_id'])) {
                        throw new InvalidArgumentException('目标语言包含非企业模型、会员投稿或商城数据，请先处理');
                    }
                    if (!in_array((int)$document['typeid'], array_map('intval', $types), true)) throw new InvalidArgumentException('目标语言文档与栏目归属不一致，请先修复');
                    $r->identifier($model['table']);
                    $bodyTables[] = $model['table'] . '_content';
                }
                $bodyTables = array_values(array_unique($bodyTables));
                $writeTables = array_values(array_unique(array_merge(self::LANGUAGE_TABLES, $bodyTables, ['language', 'arctype', 'archives', 'channelfield_bind'])));
                // Refuse partial commits on an imported MyISAM/missing custom table.
                foreach ($writeTables as $table) {
                    $engine = $r->connection()->query('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$r->fullName($table)]);
                    if (strtolower((string)($engine[0]['ENGINE'] ?? '')) !== 'innodb') throw new InvalidArgumentException('删除语言要求全部相关表存在并使用InnoDB事务引擎');
                }
                $this->assertNoForeignReferences($r, $marks, $types, $aids);
                $this->assertNoExcludedBusiness($r, $marks, $aids);
                $deleted = [];
                foreach ($bodyTables as $table) $deleted[$table] = $aids ? $r->table($table)->whereIn('aid', $aids)->delete() : 0;
                $deleted['channelfield_bind'] = $types ? $r->table('channelfield_bind')->whereIn('typeid', $types)->delete() : 0;
                foreach (self::LANGUAGE_TABLES as $table) $deleted[$table] = $r->table($table)->whereIn('lang', $marks)->delete();
                // These are copy bookkeeping references, not surviving translations.
                if ($aids) {
                    $deleted['language_archives_copy_log'] += $r->table('language_archives_copy_log')->whereIn('oldid', $aids)->delete();
                    $deleted['language_archives_copy_log'] += $r->table('language_archives_copy_log')->whereIn('newid', $aids)->delete();
                }
                $deleted['archives'] = $r->table('archives')->whereIn('lang', $marks)->delete();
                $deleted['arctype'] = $r->table('arctype')->whereIn('lang', $marks)->delete();
                $deleted['language'] = $r->table('language')->whereIn('id', $ids)->delete();
                $remaining = $r->table('language')->order('id asc')->select()->toArray();
                if (!$r->table('language')->where('status', 1)->find()) $r->table('language')->where('is_home_default', 1)->update(['status' => 1, 'update_time' => time()]);
                foreach ($remaining as $language) {
                    $where = ['lang' => $language['mark'], 'name' => 'system_langnum'];
                    $old = $r->table('config')->where($where)->find();
                    $value = ['value' => (string)count($remaining), 'is_del' => 0, 'update_time' => time()];
                    if ($old) $r->table('config')->where('id', $old['id'])->update($value);
                    else $r->table('config')->insert($where + $value + ['inc_type' => 'system']);
                }
                $prepareCache($marks);
                return ['languages' => $marks, 'deleted' => $deleted, 'remaining_languages' => count($remaining)];
            });
        });
        try {
            \app\cms\compat\Cache::clear();
            $result['cache_invalidated'] = true;
        } catch (\Throwable $error) {
            $result['cache_invalidated'] = false;
            $result['cache_warning'] = '语言及其企业数据已删除；公共缓存刷新失败，请检查运行目录权限并清除缓存后再访问网站';
            try {
                \support\Log::warning('CMS language deletion committed but cache invalidation failed', ['error_type' => get_class($error)]);
            } catch (\Throwable $loggingError) {
                // A full/unwritable runtime may also prevent logging. Preserve
                // the committed result and warning sent to the administrator.
            }
        }
        return $result;
    }

    private function enterpriseModel(array $model): bool
    {
        return !in_array((int)$model['id'], [8, 51], true) && (in_array((string)$model['nid'], ['article', 'product', 'images', 'download', 'media', 'single', 'special', 'recruit'], true) || (empty($model['ifsystem']) && !in_array((string)$model['nid'], ['ask', 'guestbook'], true)));
    }

    private function assertNoForeignReferences(CmsRepository $r, array $marks, array $types, array $aids): void
    {
        if ($types && ($r->table('arctype')->whereNotIn('lang', $marks)->whereIn('parent_id', $types)->lock(true)->find()
            || $r->table('archives')->whereNotIn('lang', $marks)->whereIn('typeid', $types)->lock(true)->find())) {
            throw new InvalidArgumentException('其它语言仍关联待删除栏目，请先处理关联');
        }
        // Navigation and its positions are global in the original schema.
        // Never infer their language or remove a shared navigation entry.
        if ($types && $r->table('nav_list')->whereIn('type_id', $types)->lock(true)->find()) {
            throw new InvalidArgumentException('公共导航仍关联待删除栏目，请先调整导航');
        }
        if ($aids && ($r->table('archives')->whereNotIn('lang', $marks)->whereIn('joinaid', $aids)->lock(true)->find()
            || $r->table('taglist')->whereNotIn('lang', $marks)->whereIn('aid', $aids)->lock(true)->find())) {
            throw new InvalidArgumentException('其它语言仍关联待删除文档，请先处理关联');
        }
        foreach ($types as $type) {
            if ($r->table('archives')->whereNotIn('lang', $marks)->whereRaw('FIND_IN_SET(?,stypeid)', [$type])->lock(true)->find()
                || $r->table('special_node')->whereNotIn('lang', $marks)->whereRaw('FIND_IN_SET(?,typeid)', [$type])->lock(true)->find()) throw new InvalidArgumentException('其它语言仍引用待删除栏目，请先处理关联');
        }
        foreach ($aids as $aid) if ($r->table('special_node')->whereNotIn('lang', $marks)->whereRaw('FIND_IN_SET(?,aidlist)', [$aid])->lock(true)->find()) throw new InvalidArgumentException('其它语言专题仍引用待删除文档，请先处理关联');
        foreach (['product_attribute' => 'product_attr', 'shop_product_attribute' => 'shop_product_attr'] as $definitions => $values) {
            $attributes = $r->table($definitions)->whereIn('lang', $marks)->column('attr_id');
            if ($attributes && $r->table($values)->whereIn('attr_id', $attributes)->whereNotIn('aid', $aids)->lock(true)->find()) throw new InvalidArgumentException('保留文档仍引用目标语言产品参数，请先处理关联');
        }
        $fields = $r->table('guestbook_attribute')->whereIn('lang', $marks)->column('attr_id');
        if ($fields && $r->table('guestbook_attr')->whereIn('attr_id', $fields)->whereNotIn('lang', $marks)->lock(true)->find()) throw new InvalidArgumentException('其它语言留言仍引用目标语言字段，请先处理关联');
        foreach ([
            ['ad_position', 'id', 'ad', 'pid'],
            ['links_group', 'id', 'links', 'groupid'],
            ['shop_product_attrlist', 'list_id', 'archives', 'attrlist_id'],
            ['tagindex', 'id', 'taglist', 'tid'],
            ['guestbook', 'aid', 'guestbook_attr', 'aid'],
        ] as [$owner, $primary, $dependent, $foreign]) {
            $owners = $r->table($owner)->whereIn('lang', $marks)->column($primary);
            if ($owners && $r->table($dependent)->whereNotIn('lang', $marks)->whereIn($foreign, $owners)->lock(true)->find()) {
                throw new InvalidArgumentException('其它语言仍关联待删除数据，请先处理关联');
            }
        }
        // A column form and a free form use different owner ID spaces.
        $forms = $r->table('form')->whereIn('lang', $marks)->column('form_id');
        foreach (['guestbook', 'guestbook_attribute'] as $table) {
            foreach ([[0, $types], [1, $forms]] as [$kind, $owners]) {
                if ($owners && $r->table($table)->whereNotIn('lang', $marks)->where('form_type', $kind)->whereIn('typeid', $owners)->lock(true)->find()) {
                    throw new InvalidArgumentException('其它语言仍引用目标语言表单，请先处理关联');
                }
            }
        }
    }

    private function assertNoExcludedBusiness(CmsRepository $r, array $marks, array $aids): void
    {
        $prefix = substr($r->fullName('language'), 0, -strlen('language'));
        $tables = $r->connection()->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE='BASE TABLE'");
        foreach ($tables as $entry) {
            $full = (string)$entry['TABLE_NAME'];
            if (!str_starts_with($full, $prefix)) continue;
            $table = substr($full, strlen($prefix));
            if ($table === 'uploads') {
                if ($aids && $r->table($table)->whereIn('aid', $aids)->lock(true)->find()) throw new InvalidArgumentException('目标文档仍有关联上传记录，请先解除关联；上传文件保留');
                continue;
            }
            if (in_array($table, ['shop_product_attr', 'shop_product_attribute', 'shop_product_attrlist'], true)) continue;
            if (!preg_match('/^(?:shop_|users(?:_|$)|discount_|sharp_|product_(?:netdisk|spec_|users_))/', $table)
                && !in_array($table, ['article_order', 'article_pay', 'download_order', 'download_log', 'media_order', 'media_log', 'media_play_record'], true)) continue;
            $columns = $r->columns($table);
            if (isset($columns['lang']) && $r->table($table)->whereIn('lang', $marks)->lock(true)->find()) throw new InvalidArgumentException('目标语言包含会员或商城业务数据，已取消删除');
            foreach (['aid', 'product_id'] as $column) if ($aids && isset($columns[$column]) && $r->table($table)->whereIn($column, $aids)->lock(true)->find()) throw new InvalidArgumentException('目标文档关联会员或商城业务数据，已取消删除');
            if ($table === 'shop_coupon') foreach ($aids as $aid) if ($r->table($table)->whereRaw('FIND_IN_SET(?,product_id)', [$aid])->lock(true)->find()) throw new InvalidArgumentException('目标文档关联商城优惠券，已取消删除');
        }
    }
}
