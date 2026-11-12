<?php

namespace app\cms\admin\controller;

use Exception;
use app\cms\UploadPaths;
use plugin\admin\app\model\Upload;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;

/**
 * 附件管理
 */
class UploadController extends \plugin\admin\app\controller\Crud
{
    /**
     * @var Upload
     */
    protected $model = null;

    /**
     * 只返回当前管理员数据
     * @var string
     */
    protected $dataLimit = 'personal';

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Upload;
    }

    /**
     * 浏览
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        return \app\cms\admin\support\AdminView::raw('upload/index');
    }

    /**
     * 浏览附件
     * @return Response
     * @throws Throwable
     */
    public function attachment(): Response
    {
        return \app\cms\admin\support\AdminView::raw('upload/attachment');
    }

    /**
     * 查询附件
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        if (!empty($where['ext']) && is_string($where['ext'])) {
            $where['ext'] = ['in', explode(',', $where['ext'])];
        }
        if (!empty($where['name']) && is_string($where['name'])) {
            $where['name'] = ['like', "%{$where['name']}%"];
        }
        $query = $this->doSelect($where, $field, $order);
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 更新附件
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function update(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return \app\cms\admin\support\AdminView::raw('upload/update');
        }
        return parent::update($request);
    }

    /**
     * 添加附件
     * @param Request $request
     * @return Response
     * @throws Exception|Throwable
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return \app\cms\admin\support\AdminView::raw('upload/insert');
        }
        $file = current($request->file());
        if (!$file || !$file->isValid()) {
            return $this->json(1, '未找到文件');
        }
        $data = $this->receiveUpload($request, '/upload/files/' . date('Ymd'));
        if ($data instanceof Response) return $data;
        if(in_array(strtolower($data['ext']),['jpg','jpeg','png','gif','webp','bmp','avif'],true)){
            $data=$this->processImageUpload($data);
            if($data instanceof Response)return $data;
        }
        $upload = new Upload;
        $upload->admin_id = admin_id();
        $upload->name = $data['name'];
        [
            $upload->url,
            $upload->name,
            $_,
            $upload->file_size,
            $upload->mime_type,
            $upload->image_width,
            $upload->image_height,
            $upload->ext
        ] = array_values($data);
        $upload->category = $request->post('category');
        $upload->save();
        return $this->json(0, '上传成功', [
            'url' => $data['url'],
            'name' => $data['name'],
            'size' => $data['size'],
        ]);
    }

    /**
     * 上传文件
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function file(Request $request): Response
    {
        $file = current($request->file());
        if (!$file || !$file->isValid()) {
            return $this->json(1, '未找到文件');
        }
        $img_exts = [
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp',
            'bmp',
            'avif'
        ];
        if (in_array(strtolower($file->getUploadExtension()), $img_exts)) {
            return $this->image($request);
        }
        $data = $this->receiveUpload($request, '/upload/files/' . date('Ymd'));
        if ($data instanceof Response) return $data;
        return $this->json(0, '上传成功', [
            'url' => $data['url'],
            'name' => $data['name'],
            'size' => $data['size'],
        ]);
    }

    /**
     * 上传图片
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function image(Request $request): Response
    {
        $data = $this->receiveUpload($request, '/upload/img/' . date('Ymd'));
        if ($data instanceof Response) return $data;
        $data=$this->processImageUpload($data);
        if($data instanceof Response)return $data;
        return json([
            'code' => 0,
            'msg' => '上传成功',
            'data' => [
                'url' => $data['url'],
                'name' => $data['name'],
                'size' => $data['size'],
                'image_processing' => $data['image_processing'],
            ]
        ]);
    }

    private function processImageUpload(array $data): array|Response
    {
        $realpath = $data['realpath'];
        try {
            $processing=(new \app\cms\service\ImageUploadPolicyService())->process($realpath);
            clearstatcache(true,$realpath);
            $data['size']=filesize($realpath);
            $info=getimagesize($realpath);
            $data['image_with']=$info[0];$data['image_height']=$info[1];
            $data['image_processing']=$processing;
        } catch (Throwable $e) {
            if (is_file($realpath)) { unlink($realpath); }
            return json([
                'code' => 500,
                'msg' => '处理图片失败，请检查图片及水印设置'
            ]);
        }
        return $data;
    }

    /**
     * 上传头像
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function avatar(Request $request): Response
    {
        $file = current($request->file());
        if ($file && $file->isValid()) {
            $ext = strtolower($file->getUploadExtension());
            if (!in_array($ext, ['jpg', 'jpeg', 'gif', 'png'])) {
                return json(['code' => 2, 'msg' => '仅支持 jpg jpeg gif png格式']);
            }
            $month = date('Ym');
            $relative_path = 'upload/avatar/' . $month;
            $name = bin2hex(random_bytes(16));
            try {
                \app\cms\service\SecureUploadService::staged($file, $ext, 10 * 1024 * 1024, static function ($source) use ($month, $name, $ext) {
                    $directory = \app\cms\UploadPaths::ensureDirectory('uploads/avatar/' . $month);
                    $temp = dirname($source); $paths = []; $targets = []; $published = [];
                    foreach (['lg'=>300, 'md'=>120, ''=>60, 'sm'=>30] as $suffix=>$size) {
                        $part = $suffix === '' ? '' : '.' . $suffix;
                        $temporary = $temp . '/' . $name . $part . '.' . $ext;
                        $paths[$temporary] = $size; $targets[$temporary] = $directory . '/' . $name . $part . '.' . $ext;
                    }
                    try {
                        (new \app\cms\service\UploadedImageService())->squareThumbnails($source, $paths);
                        if (\app\cms\UploadPaths::ensureDirectory('uploads/avatar/' . $month) !== $directory) throw new \RuntimeException('上传目录已变化');
                        foreach ($targets as $temporary=>$target) {
                            $published[] = \app\cms\service\SecureUploadService::publish($temporary, 'uploads/avatar/' . $month, basename($target));
                        }
                    } catch (\Throwable $error) {
                        foreach ($published as $target) @unlink($target);
                        throw $error;
                    } finally { foreach ($targets as $temporary=>$target) @unlink($temporary); }
                });
            } catch (Throwable $error) { return $this->json(1, '处理图片发生错误'); }

            return json([
                'code' => 0,
                'msg' => '上传成功',
                'data' => [
                    'url' => "/web-admin/$relative_path/$name.md.$ext"
                ]
            ]);
        }
        return json(['code' => 1, 'msg' => 'file not found']);
    }

    /**
     * 删除附件
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function delete(Request $request): Response
    {
        $ids = $this->deleteInput($request);
        $primary_key = $this->model->getKeyName();
        $files = $this->model->whereIn($primary_key, $ids)->get()->toArray();
        $file_list = array_map(function ($item) {
            return $this->localUploadFile($item['url']);
        }, $files);
        $file_list = array_filter($file_list, function ($item) {
            return !empty($item);
        });
        $result = parent::delete($request);
        if (($res = json_decode($result->rawBody())) && $res->code === 0) {
            foreach ($file_list as $file) {
                @unlink($file);
            }
        }
        return $result;
    }

    /** Locate legacy upload URLs only inside the single physical upload root. */
    protected function localUploadFile(string $path): ?string
    {
        $prefix = rtrim((string)\app\cms\config\SiteSettings::get('base_path'), '/');
        if ($prefix !== '' && str_starts_with($path, $prefix . '/')) $path = substr($path, strlen($prefix));
        $path = preg_replace('#^/(?:web-admin|app/admin)/(?=(?:public/)?uploads?/)#', '/', $path);
        if (!preg_match('#^/(?:public/)?uploads?/#', $path)) return null;
        try {
            $root = realpath(\app\cms\Paths::publicRoot('uploads'));
            $resolved = realpath(UploadPaths::physical($path));
        } catch (\InvalidArgumentException $error) {
            return null;
        }
        return $root && $resolved && str_starts_with($resolved, $root . DIRECTORY_SEPARATOR) && is_file($resolved) ? $resolved : null;
    }

    /**
     * 获取上传数据
     * @param Request $request
     * @param $relative_dir
     * @return array
     * @throws BusinessException|\Random\RandomException
     */
    private function receiveUpload(Request $request, string $directory): array|Response
    {
        try { return $this->base($request, $directory); }
        catch (BusinessException $error) { return $this->json($error->getCode() ?: 1, $error->getMessage()); }
    }

    protected function base(Request $request, $relative_dir): array
    {
        $relative_dir = ltrim($relative_dir, '\\/');
        $relative_dir = preg_replace('#^upload/#', 'uploads/', $relative_dir);
        $file = current($request->file());
        if (!$file || !$file->isValid()) {
            throw new BusinessException('未找到上传文件', 400);
        }

        $ext = $file->getUploadExtension() ?: null;
        $mime_type = $file->getUploadMimeType();
        $file_name = $file->getUploadName();
        $file_size = $file->getSize();

        if (!$ext && $file_name === 'blob') {
            [$___image, $ext] = explode('/', $mime_type);
            unset($___image);
        }

        $ext = strtolower($ext);
        $policy=$this->uploadPolicy();
        try { $policy->validate($ext,$file_size,str_contains('/'.$relative_dir.'/','/img/')); }
        catch (\InvalidArgumentException $error) { throw new BusinessException($error->getMessage(),400); }
        try {
            $stored = \app\cms\service\SecureUploadService::store($file, $ext, max(1, $file_size), $relative_dir);
        } catch (\InvalidArgumentException | \RuntimeException $error) { throw new BusinessException($error->getMessage(), 400); }
        $full_path = $stored['path']; $mime_type = $stored['mime'];
        $image_with = $image_height = 0;
        if ($img_info = @getimagesize($full_path)) {
            [$image_with, $image_height] = $img_info;
            $mime_type = $img_info['mime'];
        }
        return [
            'url' => $stored['url'],
            'name' => $file_name,
            'realpath' => $full_path,
            'size' => $file_size,
            'mime_type' => $mime_type,
            'image_with' => $image_with,
            'image_height' => $image_height,
            'ext' => $ext,
        ];
    }

    protected function uploadPolicy(): \app\cms\service\CmsUploadPolicyService { return new \app\cms\service\CmsUploadPolicyService(); }

}
