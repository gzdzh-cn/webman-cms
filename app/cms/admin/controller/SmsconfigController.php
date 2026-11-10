<?php
namespace app\cms\admin\controller;

use app\cms\service\SmsSettingsService;
use support\Request;
use support\Response;

class SmsconfigController extends \plugin\admin\app\controller\Base
{
    public function index(Request $request): Response
    {
        $languages = (new \app\cms\repository\CmsRepository())->table('language')->field('mark,title')->order('id')->select()->toArray();
        return \app\cms\admin\support\AdminView::raw('cms/sms-config', ['languages' => $languages]);
    }
    public function select(Request $request): Response { return $this->json(0, 'ok', (new SmsSettingsService())->read((string)$request->get('lang', 'cn'))); }
    public function save(Request $request): Response
    {
        if ($request->method() !== 'POST') return response('Method Not Allowed', 405);
        try { (new SmsSettingsService())->save($request->post()); return $this->json(0, '保存成功'); }
        catch (\InvalidArgumentException $error) { return $this->json(1, $error->getMessage()); }
    }
}
