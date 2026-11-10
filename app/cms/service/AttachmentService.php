<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use app\cms\UploadPaths;
use InvalidArgumentException;

class AttachmentService
{
    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }

    public function prepare(string $module, array $input, ?array $old = null): array
    {
        if (!in_array($module, ['download_file', 'media_file'], true)) { throw new InvalidArgumentException('附件类型无效'); }
        $data = $input + ($old ?? []); $aid = (int)($data['aid'] ?? 0);
        $archive = $this->repository->table('archives')->where(['aid' => $aid, 'is_del' => 0])->find();
        $model = $archive ? $this->repository->table('channeltype')->where('id', $archive['channel'])->find() : null;
        if (!$archive || ($model['nid'] ?? '') !== ($module === 'download_file' ? 'download' : 'media')) { throw new InvalidArgumentException('请选择对应下载或视频模型的有效文档'); }
        if ($old && (int)$old['aid'] !== $aid) { throw new InvalidArgumentException('不能将已有附件转移至其他文档'); }
        $url = trim((string)($data['file_url'] ?? ''));
        if ($url === '' || preg_match('/[\x00-\x1f]/', $url)) { throw new InvalidArgumentException('附件地址无效'); }
        $remote = preg_match('#^https?://#i', $url) === 1;
        $path = null; $extension = strtolower(pathinfo((string)parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        if (in_array($extension, ['php','phtml','phar','ini','env','htaccess','sh','exe','dll'], true)) { throw new InvalidArgumentException('附件格式不允许'); }
        if (!$remote) {
            if (!preg_match('#^/(?:uploads?|public/uploads?|template)/#', $url)) { throw new InvalidArgumentException('本地附件必须位于网站资源目录'); }
            $relative = ltrim($url, '/'); $folder = explode('/', $relative, 2)[0];
            $root = realpath($folder === 'template' ? base_path($folder) : public_path('uploads'));
            $path = realpath($folder === 'template' ? base_path($relative) : UploadPaths::physical($url));
            $contained = $root && $path && str_starts_with($path, $root . DIRECTORY_SEPARATOR);
            if (!$contained || !is_file($path)) { throw new InvalidArgumentException('本地附件文件不存在'); }
        }
        if ($module === 'media_file' && $extension !== '' && !in_array($extension, ['mp4','m4v','m3u8','webm','ogg','ogv','mov','flv','avi','mkv','mp3','wav'], true)) { throw new InvalidArgumentException('视频附件格式不支持'); }
        $data = $this->repository->filter($module, $input);
        unset($data['playcount'], $data['downcount'], $data['md5file'], $data['uhash']);
        if (!$old && $module === 'download_file') $data['downcount'] = (new InitialCounterService($this->repository))->draw('other_arcdownload', (string)($archive['lang'] ?? 'cn'));
        $data['aid'] = $aid; $data['title'] = $archive['title']; $data['file_url'] = $url; $data['is_remote'] = $remote ? 1 : 0;
        $data['file_ext'] = $extension; $data['file_name'] = $data['file_name'] ?? basename(rawurldecode((string)parse_url($url, PHP_URL_PATH)));
        $data['file_size'] = $path ? (string)filesize($path) : (string)($data['file_size'] ?? $old['file_size'] ?? '');
        $data['file_mime'] = $path ? ((new \finfo(FILEINFO_MIME_TYPE))->file($path) ?: '') : (string)($data['file_mime'] ?? $old['file_mime'] ?? '');
        if (!$old || $old['file_url'] !== $url) { $data['md5file'] = $path ? md5_file($path) : ''; $data['uhash'] = $data['md5file'] ?: md5($url . random_bytes(16)); }
        if ($module === 'media_file') { $data['gratis'] = 1; $data['file_title'] = $data['file_title'] ?? $data['file_name']; }
        return $data;
    }

    public function refresh(string $module, array $aids): void
    {
        if ($module !== 'media_file') { return; }
        foreach (array_unique($aids) as $aid) {
            $files = $this->repository->table('media_file')->where('aid', $aid);
            $this->repository->table('media_content')->where('aid', $aid)->update(['total_video' => (clone $files)->count(), 'total_duration' => (clone $files)->sum('file_time'), 'update_time' => time()]);
        }
    }

    public function replace(int $aid, string $model, mixed $files): void
    {
        $module = match ($model) { 'download' => 'download_file', 'media' => 'media_file', default => throw new InvalidArgumentException('此文档不支持下载或视频附件') };
        if (is_string($files)) { try { $files = json_decode($files, true, 512, JSON_THROW_ON_ERROR); } catch (\JsonException $error) { throw new InvalidArgumentException('附件列表格式错误'); } }
        if (!is_array($files)) { throw new InvalidArgumentException('附件列表格式错误'); }
        $service = new EnterpriseDataService($this->repository); $keep = [];
        foreach (array_values($files) as $position => $file) {
            if (!is_array($file)) { throw new InvalidArgumentException('附件列表格式错误'); }
            if (!empty($file['file_id']) && !$this->repository->table($module)->where(['file_id' => (int)$file['file_id'], 'aid' => $aid])->find()) { throw new InvalidArgumentException('附件不属于当前文档'); }
            $file['aid'] = $aid; $file['sort_order'] = $position + 1;
            $keep[] = $service->save($module, $file);
        }
        $query = $this->repository->table($module)->where('aid', $aid);
        if ($keep) { $query->whereNotIn('file_id', $keep); }
        $query->delete(); $this->refresh($module, [$aid]);
    }
}
