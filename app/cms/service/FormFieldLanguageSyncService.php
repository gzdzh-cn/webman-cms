<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/**
 * New guestbook/free-form fields retain the upstream canonical attr_<main ID>.
 * Based on EyouCMS GuestbookAttribute and FormLogic::syn_add_language_attribute.
 * 易优CMS 版权所有 2016-2028 海南赞赞网络科技有限公司，并保留所有权利。
 * https://www.eyoucms.com — retain the upstream license for commercial use.
 * The caller owns the transaction and calls lock() before any business-row lock.
 */
final class FormFieldLanguageSyncService
{
    private EnterpriseLanguageSyncService $sync;
    private array $languages = [];

    public function __construct(private CmsRepository $repository)
    {
        $this->sync = new EnterpriseLanguageSyncService($repository);
    }

    public function lock(): void
    {
        $this->sync->lock();
        $this->languages = $this->repository->table('language')->order('id asc')->lock(true)->select()->toArray();
    }

    /** Repeated calls reuse complete bindings and never overwrite translations. */
    public function created(int $id): array
    {
        $r = $this->repository;
        $source = $r->table('guestbook_attribute')->where('attr_id', $id)->lock(true)->find();
        if (!$source || !in_array((int)$source['form_type'], [0, 1], true) || !empty($source['is_del'])) throw new InvalidArgumentException('新增表单字段不存在或已回收');
        $policy = $this->sync->policy((string)$source['lang']);
        if (!$policy['shared'] || $source['lang'] !== $policy['main']) return [$source['lang'] => $id];
        (new LanguageAccessService($r))->assertLanguages(array_column($this->languages,'mark'));
        $group = (int)$source['form_type'] === 1 ? 'form_attribute' : 'guestbook_attribute';
        $aliases = $this->aliases($id);
        if ($aliases && (count($aliases) !== 1 || $aliases[0]['attr_group'] !== $group || $aliases[0]['lang'] !== $source['lang'])) throw new InvalidArgumentException('字段语言绑定冲突，请先修复映射');
        $name = $aliases[0]['attr_name'] ?? 'attr_' . $id;
        $definitions = $r->table('language_attribute')->where(['attr_group' => $group, 'attr_name' => $name])->lock(true)->select()->toArray();
        if (count($definitions) > 1 || ($definitions && !empty($definitions[0]['is_del']))) throw new InvalidArgumentException('字段语言定义重复或已回收');

        // Validate every owner and existing peer before inserting any metadata or clone.
        $owners = $this->owners($source);
        $bindings = $r->table('language_attr')->where(['attr_group' => $group, 'attr_name' => $name])->lock(true)->select()->toArray();
        $targets = [];
        foreach ($bindings as $binding) {
            $lang = (string)$binding['lang'];
            if (!isset($owners[$lang]) || isset($targets[$lang]) || !ctype_digit((string)$binding['attr_value']) || (int)$binding['attr_value'] < 1) throw new InvalidArgumentException('字段语言组存在重复或无效映射');
            $field = $r->table('guestbook_attribute')->where('attr_id', (int)$binding['attr_value'])->lock(true)->find();
            if (!$field || $field['lang'] !== $lang || (int)$field['form_type'] !== (int)$source['form_type'] || (int)$field['typeid'] !== $owners[$lang] || !empty($field['is_del']) || count($this->aliases((int)$field['attr_id'])) !== 1) throw new InvalidArgumentException('字段与所属表单的语言组不一致或已回收');
            $targets[$lang] = (int)$field['attr_id'];
        }
        if (isset($targets[$source['lang']]) && $targets[$source['lang']] !== $id) throw new InvalidArgumentException('字段语言变量已被其他记录占用');
        if (!$definitions) $r->table('language_attribute')->insert(['attr_title' => $source['attr_name'], 'attr_group' => $group, 'attr_name' => $name, 'add_time' => time(), 'update_time' => time()]);
        foreach ($this->languages as $language) {
            $lang = $language['mark'];
            if (isset($targets[$lang])) continue;
            $newId = $id;
            if ($lang !== $source['lang']) {
                $copy = $source; unset($copy['attr_id']);
                $copy['lang'] = $lang; $copy['typeid'] = $owners[$lang];
                $newId = (int)$r->table('guestbook_attribute')->insertGetId($copy);
            }
            $r->table('language_attr')->insert(['attr_group' => $group, 'attr_name' => $name, 'attr_value' => (string)$newId, 'lang' => $lang, 'add_time' => time(), 'update_time' => time()]);
            $targets[$lang] = $newId;
        }
        return $targets;
    }

    private function aliases(int $id): array
    {
        return $this->repository->table('language_attr')->whereIn('attr_group', ['guestbook_attribute', 'form_attribute'])->where('attr_value', (string)$id)->lock(true)->select()->toArray();
    }

    private function owners(array $source): array
    {
        $r = $this->repository; $table = (int)$source['form_type'] === 1 ? 'form' : 'arctype'; $primary = $table === 'form' ? 'form_id' : 'id';
        $aliases = $r->table('language_attr')->where(['attr_group' => $table, 'attr_value' => (string)$source['typeid']])->lock(true)->select()->toArray();
        if (count($aliases) !== 1 || $aliases[0]['lang'] !== $source['lang']) throw new InvalidArgumentException('所属表单语言绑定缺失或重复，请先同步所属表单');
        $name = $aliases[0]['attr_name'];
        $definition = $r->table('language_attribute')->where(['attr_group' => $table, 'attr_name' => $name])->lock(true)->select()->toArray();
        if (count($definition) !== 1 || !empty($definition[0]['is_del'])) throw new InvalidArgumentException('所属表单语言定义缺失或重复');
        $bindings = $r->table('language_attr')->where(['attr_group' => $table, 'attr_name' => $name])->lock(true)->select()->toArray();
        $registered = array_column($this->languages, 'mark'); $owners = [];
        foreach ($bindings as $binding) {
            $lang = (string)$binding['lang'];
            if (!in_array($lang, $registered, true) || isset($owners[$lang]) || !ctype_digit((string)$binding['attr_value']) || (int)$binding['attr_value'] < 1) throw new InvalidArgumentException('所属表单语言组存在重复或无效映射');
            $id = (int)$binding['attr_value']; $owner = $r->table($table)->where($primary, $id)->lock(true)->find();
            $aliases = $r->table('language_attr')->where(['attr_group' => $table, 'attr_value' => (string)$id])->lock(true)->select()->toArray();
            if (!$owner || $owner['lang'] !== $lang || !empty($owner['is_del']) || ($table === 'arctype' && (int)$owner['current_channel'] !== 8) || count($aliases) !== 1) throw new InvalidArgumentException('目标语言所属表单缺失、已回收或绑定冲突，请先同步所属表单');
            $owners[$lang] = $id;
        }
        foreach ($registered as $lang) if (!isset($owners[$lang])) throw new InvalidArgumentException('目标语言【' . $lang . '】所属表单缺少有效绑定，请先同步所属表单');
        if (($owners[$source['lang']] ?? 0) !== (int)$source['typeid']) throw new InvalidArgumentException('所属表单语言组不一致');
        return $owners;
    }
}
