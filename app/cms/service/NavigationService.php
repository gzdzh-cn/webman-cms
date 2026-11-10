<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Navigation positions are global in Eyou; linked categories carry language. */
class NavigationService
{
    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }

    public function prepare(array $data, ?array $old = null, ?string $language = null): array
    {
        $merged = $data + ($old ?? []); $type = (int)($merged['type_id'] ?? 0);
        if ($type > 0) {
            $category = $this->repository->table('arctype')->where(['id' => $type, 'is_del' => 0])->find();
            if (!$category || empty($category['status'])) throw new InvalidArgumentException('同步栏目不存在或已停用');
            if ($language !== null && $category['lang'] !== $language) throw new InvalidArgumentException('同步栏目与当前编辑语言不一致');
            $data['arctype_sync'] = 1; $data['nav_name'] = $category['typename'];
            $data['nav_url'] = $this->categoryUrl($category);
        } else {
            $data['arctype_sync'] = 0;
            if (trim((string)($merged['nav_name'] ?? '')) === '') throw new InvalidArgumentException('请填写导航名称');
            $url = htmlspecialchars_decode(trim((string)($merged['nav_url'] ?? '')), ENT_QUOTES);
            // A blank link remains valid for a parent menu used only as a heading.
            if ($url !== '' && preg_match('#^(?:javascript|data|vbscript):#i', $url)) throw new InvalidArgumentException('导航链接格式错误');
            $data['nav_url'] = $url;
        }
        return $data;
    }

    public function categoryUrl(array $category): string
    {
        if ((int)$category['is_part'] === 1 && $category['typelink'] !== '') return htmlspecialchars_decode($category['typelink'], ENT_QUOTES);
        return '/index.php?' . http_build_query(['m' => 'home', 'c' => 'Lists', 'a' => 'index', 'tid' => (int)$category['id'], 'lang' => $category['lang']]);
    }

    public function refreshCategory(int $id): void
    {
        $category = $this->repository->table('arctype')->where('id', $id)->find();
        if ($category) $this->repository->table('nav_list')->where(['type_id' => $id, 'arctype_sync' => 1, 'is_del' => 0])->update(['nav_name' => $category['typename'], 'nav_url' => $this->categoryUrl($category), 'update_time' => time()]);
    }
}
