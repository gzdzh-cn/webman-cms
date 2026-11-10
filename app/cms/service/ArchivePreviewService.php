<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\CmsContext;
use app\cms\compat\Runtime;
use app\cms\repository\CmsRepository;
use InvalidArgumentException;
use support\Request;

/** Resolve backend browsing through the same Eyou URL helpers as public tags. */
final class ArchivePreviewService
{
    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }

    public function archive(int $aid): array
    {
        $r = $this->repository;
        $archive = $r->table('archives')->where(['aid' => $aid, 'is_del' => 0])->find();
        if (!$archive) throw new InvalidArgumentException('文档不存在或已被回收');
        $model = $r->table('channeltype')->where('id', $archive['channel'])->find();
        if (!$model || in_array($model['nid'], ['single', 'guestbook', 'ask'], true)) throw new InvalidArgumentException('该模型不支持文档浏览');
        if (!$r->table('language')->where('mark', $archive['lang'])->find()) throw new InvalidArgumentException('文档语言不存在');
        if (!$r->table('arctype')->where(['id' => $archive['typeid'], 'lang' => $archive['lang'], 'is_del' => 0])->find()) throw new InvalidArgumentException('文档所属栏目不存在或已被回收');
        return $archive;
    }

    public function publicUrl(array $archive, Request $request): string
    {
        $previous = CmsContext::current();
        // Backend search/query parameters must not contaminate public URLs.
        $scheme = $previous->origin();
        $publicRequest = new Request('GET /index.php?lang=' . rawurlencode($archive['lang']) . " HTTP/1.1\r\nHost: " . $request->host() . "\r\nX-Forwarded-Proto: " . (str_starts_with($scheme, 'https:') ? 'https' : 'http') . "\r\n\r\n");
        $context = new CmsContext($publicRequest);
        $context->aid = (int)$archive['aid'];
        $context->tid = (int)$archive['typeid'];
        $context->controller = 'View';
        try {
            Runtime::boot($context);
            $context->load();
            if ($context->lang !== $archive['lang']) throw new InvalidArgumentException('文档语言当前不可访问');
            if (!empty($archive['is_jump']) && trim((string)$archive['jumplinks']) !== '') return $this->safeUrl((string)$archive['jumplinks'], $context);
            $category = $this->repository->table('arctype')->where(['id' => $archive['typeid'], 'lang' => $archive['lang'], 'is_del' => 0])->find();
            if (!$category) throw new InvalidArgumentException('文档所属栏目不存在或已被回收');
            // Category directory/static rules are needed by the original arcurl helper.
            return $this->safeUrl(get_arcurl(array_replace($category, $archive), false), $context);
        } finally {
            CmsContext::set($previous);
        }
    }

    private function safeUrl(string $url, CmsContext $context): string
    {
        $url = trim(htmlspecialchars_decode($url, ENT_QUOTES));
        if ($url === '' || preg_match('/[\x00-\x20\x7f\\\\]/', $url)) throw new InvalidArgumentException('文档浏览链接格式错误');
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if ($scheme !== null && $scheme !== false && !in_array(strtolower($scheme), ['http', 'https'], true)) throw new InvalidArgumentException('文档浏览链接协议不受支持');
        if ($scheme === false) throw new InvalidArgumentException('文档浏览链接格式错误');
        // Relative jump links belong to the public site, never /app/admin/archive/.
        if ($scheme === null && !str_starts_with($url, '/')) $url = rtrim((string)$context->global['web_cmspath'], '/') . '/' . $url;
        return $url;
    }
}
