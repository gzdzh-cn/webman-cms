<?php
declare(strict_types=1);
namespace app\cms\admin\controller;

use app\cms\admin\support\{AdminView, Auth, LanguageAdminSecurity};
use app\cms\service\PluginMarketService;
use support\{Request, Response};

/** Reviewed catalogue only; historical download, installation and payment routes stay closed. */
class PluginController extends \plugin\admin\app\controller\Base
{
    public function index(Request $request): Response { return $this->dispatch($request,'index'); }
    public function select(Request $request): Response { return $this->dispatch($request,'select'); }
    public function detail(Request $request): Response { return $this->dispatch($request,'detail'); }
    public function add(Request $request): Response { return $this->dispatch($request,'add'); }
    public function save(Request $request): Response { return $this->dispatch($request,'save'); }
    public function enable(Request $request): Response { return $this->dispatch($request,'enable'); }
    public function disable(Request $request): Response { return $this->dispatch($request,'disable'); }
    public function delete(Request $request): Response { return $this->dispatch($request,'delete'); }
    public function legacyPreview(Request $request): Response { return $this->dispatch($request,'legacypreview'); }
    public function legacyImport(Request $request): Response { return $this->dispatch($request,'legacyimport'); }

    private function dispatch(Request $request, string $action): Response
    {
        if (!Auth::isSuperAdmin()) return json(['code'=>2,'msg'=>'仅超级管理员可管理插件','data'=>[]])->withStatus(403)->withHeader('Cache-Control','no-store');
        $read=in_array($action,['index','select','detail','legacypreview'],true);
        if ($request->method()!==($read?'GET':'POST')) return response('Method Not Allowed',405);
        try {
            if (!$read) LanguageAdminSecurity::assertWrite($request);
            if ($action==='index') return AdminView::raw('cms/plugins')->withHeader('Cache-Control','no-store');
            $service=new PluginMarketService();$input=$read?$request->get():$request->post();
            $code=$input['code']??'AliyunOss';
            if (!is_string($code)) throw new \InvalidArgumentException('插件标识无效');
            $data=match ($action) {
                'select'=>$service->select($this->string($input,'query'),$this->string($input,'status')),
                'detail'=>$service->detail($code),
                'legacypreview'=>$service->legacyPreview($code),
                'legacyimport'=>$service->legacyImport($code,$this->revision($input),$this->string($input,'source_digest')),
                default=>$service->change($code,$action,$this->revision($input),$action==='save'?$this->configuration($input):[]),
            };
            $data['csrf']=LanguageAdminSecurity::token($request);
            return json(['code'=>0,'msg'=>'ok','data'=>$data])->withHeader('Cache-Control','no-store');
        } catch (\InvalidArgumentException $error) {
            return json(['code'=>1,'msg'=>$error->getMessage(),'data'=>[]])->withHeader('Cache-Control','no-store');
        } catch (\Throwable) {
            return json(['code'=>1,'msg'=>'插件操作未完成，请检查数据库迁移及运行环境','data'=>[]])->withHeader('Cache-Control','no-store');
        }
    }

    private function string(array $input, string $key): string
    {
        $value=$input[$key]??'';
        if (!is_string($value) || strlen($value)>512) throw new \InvalidArgumentException('插件参数格式错误');
        return $value;
    }

    private function revision(array $input): int
    {
        $value=$input['revision']??null;
        if ((!is_int($value)&&!is_string($value)) || !preg_match('/^\d{1,10}$/D',(string)$value)) throw new \InvalidArgumentException('插件版本参数无效，请刷新后重试');
        return (int)$value;
    }

    private function configuration(array $input): array
    {
        $value=$input['config']??null;
        if (!is_array($value) || array_is_list($value)) throw new \InvalidArgumentException('插件配置格式错误');
        return $value;
    }
}
