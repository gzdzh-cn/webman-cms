<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\CmsContext;
use app\cms\ThemeCatalog;
use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Theme selection changes only the selected language's configuration. */
class ThemeService
{
    public function __construct(private ?CmsRepository $repository = null)
    {
        $this->repository ??= new CmsRepository();
    }

    public function listing(array $input = []): array
    {
        $scope = $this->scope($input);
        $record = $this->configuration($scope['lang']);
        $current = ThemeCatalog::configured($record['value'] ?? null);
        $requirements = $this->requirements($scope['lang']);
        $themes = [];
        foreach (ThemeCatalog::all() as $theme) {
            $check = $this->inspect($theme['key'], $scope['lang'], $requirements);
            $theme += $this->dialect($theme['key']);
            $theme['current'] = $theme['key'] === $current;
            $theme['valid'] = !$check['missing'];
            $theme['missing'] = $check['missing'];
            $theme['required_templates'] = $check['required_templates'];
            $theme['preview'] = [];
            foreach (['pc', 'mobile'] as $device) {
                $theme['preview'][$device] = '/app/admin/filemanager/preview?' . http_build_query(['lang' => $scope['lang'], 'theme' => $theme['key'], 'device' => $device]);
            }
            $themes[] = $theme;
        }
        return ['lang' => $scope['lang'], 'languages' => $scope['languages'], 'current' => $current,
            'stored_theme' => $record['value'] ?? null, 'template_mode' => \app\cms\template\TemplateMode::current(), 'themes' => $themes];
    }

    public function activate(array $input): array
    {
        $scope = $this->scope($input);
        if (!isset($input['theme']) || !is_string($input['theme'])) throw new InvalidArgumentException('请选择网站模板');
        $theme = ThemeCatalog::canonical($input['theme']);
        // Validate the theme root before acquiring database locks, then check
        // again inside the transaction. Missing pages remain diagnostics.
        $root = ThemeCatalog::root($theme);
        $issues = \app\cms\secure\ThemeInspection::inspect($root, 'template/' . $theme)['blocking_issues'];
        if ($issues) throw new InvalidArgumentException('模板包含不支持的内容：' . implode('；', $issues));
        return $this->repository->connection()->transaction(function () use ($scope, $theme): array {
            $language = $this->repository->table('language')->where('mark', $scope['lang'])->lock(true)->find();
            if (!$language || (int)$language['id'] !== $scope['_language_id']) throw new InvalidArgumentException('语言已删除或重新创建，请刷新后重试');
            $record = $this->configuration($scope['lang'], true);
            $root = ThemeCatalog::root($theme);
            $issues = \app\cms\secure\ThemeInspection::inspect($root, 'template/' . $theme)['blocking_issues'];
            if ($issues) throw new InvalidArgumentException('模板包含不支持的内容：' . implode('；', $issues));
            $check = $this->inspect($theme, $scope['lang'], $this->requirements($scope['lang'], true));
            $dialect = $this->dialect($theme);
            if (!$dialect['mode_compatible']) {
                $mode = \app\cms\template\TemplateMode::current() === 'webman' ? 'Webman' : 'EyouCMS 兼容';
                $unsupported = $dialect['dialect'] === 'mixed' ? '混合标签模板' : $dialect['dialect_label'].' 模板';
                throw new InvalidArgumentException('当前为 '.$mode.' 模式，不支持 '.$unsupported);
            }
            $changed = !$record || (string)$record['value'] !== $theme;
            if ($record) {
                $this->repository->table('config')->where('id', $record['id'])->update(['value' => $theme, 'update_time' => time()]);
            } else {
                $this->repository->table('config')->insert(['name' => 'web_tpl_theme', 'value' => $theme, 'inc_type' => 'web',
                    'desc' => '网站模板', 'lang' => $scope['lang'], 'is_del' => 0, 'update_time' => time()]);
            }
            // A failed publication must throw before commit, preserving the
            // original configuration. The admin boundary skips duplicate clear.
            $this->publish();
            return ['lang' => $scope['lang'], 'theme' => $theme, 'changed' => $changed, 'required_templates' => $check['required_templates']];
        });
    }

    private function scope(array $input): array
    {
        $lang = $input['lang'] ?? CmsContext::current()->lang;
        if (!is_string($lang) || !preg_match('/^[A-Za-z0-9_-]{2,50}$/D', $lang)) throw new InvalidArgumentException('语言不存在');
        $languages = $this->repository->table('language')->field('id,mark,title,status')->order('sort_order asc,id asc')->select()->toArray();
        foreach ($languages as $language) if ($language['mark'] === $lang) {
            return ['lang' => $lang, '_language_id' => (int)$language['id'], 'languages' => $languages];
        }
        throw new InvalidArgumentException('语言不存在');
    }

    private function configuration(string $lang, bool $lock = false): ?array
    {
        $query = $this->repository->table('config')->where(['name' => 'web_tpl_theme', 'lang' => $lang, 'is_del' => 0])->order('id asc');
        if ($lock) $query->lock(true);
        $records = $query->select()->toArray();
        if (count($records) > 1) throw new InvalidArgumentException('当前语言存在重复模板配置，请先处理配置冲突');
        return $records[0] ?? null;
    }

    /** Match the public controller's category/document template inheritance. */
    protected function requirements(string $lang, bool $lock = false): array
    {
        $models = $this->repository->table('channeltype')->select()->toArray();
        $models = array_column($models, null, 'id');
        $query = $this->repository->table('arctype')->where(['lang' => $lang, 'is_del' => 0]);
        if ($lock) $query->lock(true);
        $categories = array_column($query->select()->toArray(), null, 'id');
        $required = ['index' => ['首页']];
        foreach ($categories as $category) {
            if ((int)($category['status'] ?? 0) !== 1 || ((int)($category['is_part'] ?? 0) === 1 && !empty($category['typelink']))) continue;
            $model = $models[$category['current_channel']] ?? null;
            if (!$model) throw new InvalidArgumentException('栏目 #' . $category['id'] . ' 的模型不存在');
            $file = (string)(($category['templist'] ?? '') ?: 'lists_' . $model['nid']);
            $required[$file][] = '栏目 #' . $category['id'];
        }
        $query = $this->repository->table('archives')->where(['lang' => $lang, 'is_del' => 0, 'status' => 1])->where('arcrank', '>=', 0);
        if ($lock) $query->lock(true);
        foreach ($query->select()->toArray() as $archive) {
            if (!empty($archive['is_jump']) && !empty($archive['jumplinks'])) continue;
            $model = $models[$archive['channel']] ?? null;
            if (!$model || in_array($model['nid'], ['single', 'guestbook', 'ask'], true)) continue;
            $category = $categories[$archive['typeid']] ?? null;
            if (!$category) continue; // The public detail route cannot render an orphan.
            $file = (string)(($archive['tempview'] ?? '') ?: (($category['tempview'] ?? '') ?: 'view_' . $model['nid']));
            $required[$file][] = '文档 #' . $archive['aid'];
        }
        return $required;
    }

    protected function inspect(string $theme, string $lang, array $requirements): array
    {
        $missing = [];
        foreach (['pc', 'mobile'] as $device) {
            try { $root = ThemeCatalog::root($theme, $device); }
            catch (InvalidArgumentException $error) { $missing[] = $device . '：模板目录不存在'; continue; }
            foreach ($requirements as $template => $owners) {
                try {
                    $name = preg_replace('/\.html?$/i', '', $template);
                    if (!is_string($name) || !preg_match('#^[A-Za-z0-9_-][A-Za-z0-9_./-]*$#D', $name) || str_contains($name, '..')) throw new InvalidArgumentException('模板文件名无效');
                    $localized = $name . '_' . $lang . '.htm';
                    $name = $this->file($root, $localized) ? $localized : $name . '.htm';
                    $file = $this->file($root, $name);
                    if (!$file) throw new InvalidArgumentException('缺少 ' . $name);
                    if (trim((string)file_get_contents($file)) === '') {
                        // Public rendering permits an empty mobile page only
                        // when the corresponding nonempty desktop page exists.
                        if ($device !== 'mobile') throw new InvalidArgumentException($name . ' 为空');
                        $pc = ThemeCatalog::root($theme, 'pc');
                        $fallback = $this->file($pc, $name);
                        if ((!$fallback || trim((string)file_get_contents($fallback)) === '') && $name === $localized) $fallback = $this->file($pc, preg_replace('/\.html?$/i', '', $template) . '.htm');
                        if (!$fallback || trim((string)file_get_contents($fallback)) === '') throw new InvalidArgumentException($name . ' 为空且无有效 PC 回退');
                    }
                } catch (InvalidArgumentException $error) {
                    $missing[] = $device . '：' . $error->getMessage() . '（' . implode('、', array_slice($owners, 0, 3)) . '）';
                }
            }
        }
        return ['missing' => $missing, 'required_templates' => count($requirements)];
    }

    /** Inspect executable theme sources without modifying original theme files. */
    private function dialect(string $theme): array
    {
        $root = ThemeCatalog::root($theme); $seen = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isLink() || !$file->isFile() || !preg_match('/\.html?$/i', $file->getFilename())) continue;
            $value = \app\cms\template\TemplateDialect::detect((string)file_get_contents($file->getPathname()));
            if ($value === 'mixed') { $seen = ['eyou'=>true,'webman'=>true]; break; }
            if ($value !== 'unknown') $seen[$value] = true;
        }
        $dialect = count($seen) > 1 ? 'mixed' : (array_key_first($seen) ?? 'unknown');
        $mode = \app\cms\template\TemplateMode::current();
        return ['dialect'=>$dialect, 'dialect_label'=>['eyou'=>'EyouCMS','webman'=>'Webman','mixed'=>'混合','unknown'=>'未识别'][$dialect],
            'mode_compatible'=>$dialect === 'unknown' || $dialect === $mode];
    }

    private function file(string $root, string $relative): ?string
    {
        clearstatcache(true);
        $path = $root;
        foreach (explode('/', $relative) as $part) {
            if ($part === '' || $part === '.' || $part === '..') return null;
            $path .= '/' . $part;
            if (is_link($path)) return null;
        }
        $actual = realpath($path);
        return $actual && str_starts_with($actual, $root . DIRECTORY_SEPARATOR) && is_file($actual) ? $actual : null;
    }

    protected function publish(): void
    {
        \app\cms\compat\Cache::clear();
        CmsContext::current()->memo['cms.template_file_cache_published'] = true;
    }
}
