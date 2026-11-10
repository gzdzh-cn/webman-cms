<?php
namespace app\cms\admin\controller;

use app\cms\repository\CmsRepository;
use app\cms\service\CategoryService;
use app\cms\service\ContentService;
use app\cms\service\CustomVariableService;
use app\cms\service\FormFieldService;
use support\Request;
use support\Response;

class RecycleController extends \plugin\admin\app\controller\Base
{
    public function index(): Response { return \app\cms\admin\support\AdminView::raw('cms/recycle'); }

    public function select(Request $request): Response
    {
        $type = (string)$request->get('type', 'archives');
        if ($type === 'customvar') return json((new CustomVariableService())->list($request->get(), true));
        if ($type === 'gbookattr') {
            try { return json((new FormFieldService())->list($request->get())); }
            catch (\InvalidArgumentException $error) { return $this->json(1, $error->getMessage()); }
        }
        if (!in_array($type, ['arctype', 'archives'], true)) return $this->json(1, '不支持的回收站类型');
        $table = $type;
        $query = (new CmsRepository())->table($table)->where(['is_del' => 1, 'lang' => \app\cms\CmsContext::current()->lang]);
        $count = (clone $query)->count();
        $data = $query->order('update_time desc')->page(max(1, (int)$request->get('page', 1)), 20)->select()->toArray();
        return json(['code' => 0, 'msg' => 'ok', 'count' => $count, 'data' => $data]);
    }

    public function restore(Request $request): Response { return $this->change($request, false); }
    public function purge(Request $request): Response { return $this->change($request, true); }

    private function change(Request $request, bool $purge): Response
    {
        if ($request->method() !== 'POST') { return response('Method Not Allowed', 405); }
        $type = (string)$request->post('type', 'archives');
        if (!in_array($type, ['arctype', 'archives', 'customvar', 'gbookattr'], true)) return $this->json(1, '不支持的回收站类型');
        if ($type === 'gbookattr') {
            try {
                $inputType = $request->post('form_type');
                if ($inputType !== null && !in_array($inputType, [0, 1, '0', '1'], true)) return $this->json(1, '表单类型无效');
                $fields = new FormFieldService();
                if ($purge) $fields->purge((array)$request->post('ids', []), $inputType === null ? null : (int)$inputType);
                else $fields->restore((array)$request->post('ids', []), $inputType === null ? null : (int)$inputType);
                return $this->json(0, $purge ? '已彻底删除表单字段及历史值' : '已恢复表单字段及原有历史值');
            } catch (\InvalidArgumentException $error) { return $this->json(1, $error->getMessage()); }
        }
        if ($type === 'customvar') {
            try {
                $variables = new CustomVariableService();
                if ($purge) $variables->purge((array)$request->post('ids', []));
                else $variables->restore((array)$request->post('ids', []));
                return $this->json(0, $purge ? '已彻底删除同名变量的全部语言记录' : '已恢复同名变量的全部语言记录');
            } catch (\InvalidArgumentException $error) { return $this->json(1, $error->getMessage()); }
        }
        $ids = array_filter(array_unique(array_map('intval', (array)$request->post('ids', []))));
        if (!$ids) { return $this->json(1, '请选择记录'); }
        $service = $type === 'arctype' ? new CategoryService() : new ContentService();
        try {
            if ($purge) { $service->purge($ids); } else { $service->recycle($ids, true); }
            return $this->json(0, $purge ? '已彻底删除' : '已恢复');
        } catch (\InvalidArgumentException $error) { return $this->json(1, $error->getMessage()); }
    }
}
