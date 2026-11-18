<?php

namespace app\cms\admin\controller;

use support\Request;
use support\Response;
use plugin\admin\app\model\Archive;
use plugin\admin\app\model\Arctype;
use plugin\admin\app\model\Channeltype;
use plugin\admin\app\model\Channelfield;
use plugin\admin\app\model\Taglist;
use plugin\admin\app\model\Tagindex;
use plugin\admin\app\model\ProductImg;
use plugin\admin\app\logic\FieldLogic;
use plugin\admin\app\common\CmsUtil as Util;
use plugin\admin\app\common\Auth;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;
use support\Log;

/**
 * 内容管理 
 */
class ArchiveController extends \plugin\admin\app\controller\Crud
{



    
    /**
     * @var Archive
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Archive;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return \app\cms\admin\support\AdminView::render('archive/index');
    }

    /** Authenticated, read-only browsing; draft bodies never gain a public query bypass. */
    public function view(Request $request): Response
    {
        if ($request->method() !== 'GET') return response('Method Not Allowed', 405);
        $aid = $request->get('aid', '');
        if ((!is_string($aid) && !is_int($aid)) || !ctype_digit((string)$aid) || (int)$aid < 1) return response('文档ID格式错误', 400);
        try {
            $service = new \app\cms\service\ArchivePreviewService();
            $archive = $service->archive((int)$aid);
            if (!Auth::isSuperAdmin() && $this->dataLimit) {
                $allowed = $this->dataLimit === 'personal' ? [(int)admin_id()] : array_map('intval', Auth::getScopeAdminIds(true));
                if (!in_array((int)$archive[$this->dataLimitField], $allowed, true)) return response('无数据权限', 403);
            }
            $needsPreview = ((int)$archive['arcrank'] <= -1 || (int)$archive['status'] !== 1)
                && (empty($archive['is_jump']) || trim((string)$archive['jumplinks']) === '');
            $response = $needsPreview
                ? (new \app\controller\CmsController())->previewArchive($request, $archive)
                : redirect($service->publicUrl($archive, $request));
            return $response->withHeaders(['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
        } catch (\InvalidArgumentException $error) {
            return response($error->getMessage(), 404, ['Content-Type' => 'text/plain; charset=utf-8', 'Cache-Control' => 'private, no-store']);
        }
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ($request->method() !== 'POST') { return \app\cms\admin\support\AdminView::render('archive/insert'); }
        if ($denied = $this->productParameterWriteGuard($request)) { return $denied; }
        try {
            $aid = (new \app\cms\service\ContentService())->save($request->post(), (int)admin_id());
            return $this->contentSaved($request, $aid, false);
        } catch (\InvalidArgumentException|\JsonException $error) {
            if ($error instanceof \app\cms\service\ContentValidationException) return json(['code'=>1,'msg'=>$error->getMessage(),'fields'=>$error->fields]);
            return $this->json(1, $error->getMessage());
        }
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function update(Request $request): Response
    {
        if ($request->method() !== 'POST') { return \app\cms\admin\support\AdminView::render('archive/update'); }
        if ($denied = $this->productParameterWriteGuard($request)) { return $denied; }
        $aid = (int)$request->post('aid', 0);
        if ($aid <= 0) { return $this->json(1, 'ID不能为空'); }
        try {
            (new \app\cms\service\ContentService())->save($request->post(), (int)admin_id(), $aid);
            return $this->contentSaved($request, $aid, true);
        } catch (\InvalidArgumentException|\JsonException $error) {
            if ($error instanceof \app\cms\service\ContentValidationException) return json(['code'=>1,'msg'=>$error->getMessage(),'fields'=>$error->fields]);
            return $this->json(1, $error->getMessage());
        }
    }

    protected function productParameterRoles(): array { return (array)(admin()['roles'] ?? []); }

    /** Content permission and the independent parameter write action are both required. */
    protected function productParameterWriteGuard(Request $request): ?Response
    {
        $input = \app\cms\service\ContentService::normalizeInput($request->post());
        if (!array_key_exists('product_parameters', $input) && !array_key_exists('attrlist_id', $input)) { return null; }
        return \plugin\admin\api\CmsWritePolicy::authorize($request, \plugin\admin\app\controller\ProductparameterController::class, 'saveProduct', $this->productParameterRoles());
    }

    /** 批量移动文档 */
    public function move(Request $request): Response
    {
        if ($request->method() !== 'POST') { return response('Method Not Allowed', 405); }
        try {
            $ids = $request->post('aids', $request->post('aid', []));
            if (is_string($ids)) { $ids = explode(',', $ids); }
            if (!Auth::isSuperAdmin() && $this->dataLimit) {
                $owners = $this->model->whereIn('aid', (array)$ids)->pluck($this->dataLimitField)->toArray();
                $allowed = $this->dataLimit === 'personal' ? [(int)admin_id()] : Auth::getScopeAdminIds(true);
                if (array_diff($owners, $allowed)) { return $this->json(1, '无数据权限'); }
            }
            $count = (new \app\cms\service\ContentService())->move((array)$ids, (int)$request->post('typeid', 0), (int)admin_id());
            return $this->json(0, '移动成功', ['count' => $count]);
        } catch (\InvalidArgumentException $error) { return $this->json(1, $error->getMessage()); }
    }

    /** 移入回收站，保留正文、TAG和附件记录供恢复。 */
    public function delete(Request $request): Response
    {
        try {
            $recycled = (new \app\cms\service\RecyclePolicyService())->delete('archives', $this->deleteInput($request), \app\cms\service\RecyclePolicyService::expected($request->post('recycle_enabled')));
            return $this->json(0, $recycled ? '已移入回收站' : '已彻底删除');
        } catch (\InvalidArgumentException $error) { return $this->json(1, $error->getMessage()); }
    }
    
    /**
     * 单独更新状态字段（不触发标签删除逻辑）
     * @param Request $request
     * @return Response
     */
    public function updateStatus(Request $request): Response
    {
        if ($request->method() !== 'POST') return response('Method Not Allowed',405);
        try {
            $field=$request->post('field','');
            if (!is_string($field)) throw new \InvalidArgumentException('字段名格式错误');
            $result=(new \app\cms\service\ArchiveWorkflowService())->updateStatus((int)$request->post('aid',0),$field,$request->post('value'));
            return $this->workflowSaved($request,$result,'更新成功');
        } catch (\InvalidArgumentException $error) { return $this->json(1,$error->getMessage()); }
    }

    /** Batch review and safe boolean attributes reuse the existing updateStatus permission. */
    public function batch(Request $request): Response
    {
        if ($request->method() !== 'POST') return response('Method Not Allowed',405);
        try {
            $operation=$request->post('operation','');
            if (!is_string($operation) || !in_array($operation,['audit','pending','attributes'],true)) throw new \InvalidArgumentException('不支持的批量操作');
            $attributes=$request->post('attributes',[]);
            if (is_string($attributes)) $attributes=json_decode($attributes,true,32,JSON_THROW_ON_ERROR);
            if (!is_array($attributes)) throw new \InvalidArgumentException('属性格式错误');
            $result=(new \app\cms\service\ArchiveWorkflowService())->batch($this->workflowIds($request),$operation,$attributes);
            return $this->workflowSaved($request,$result,'操作成功');
        } catch (\InvalidArgumentException|\JsonException $error) { return $this->json(1,$error->getMessage()); }
    }

    public function rebackdraft(Request $request): Response
    {
        if ($request->method() !== 'POST') return response('Method Not Allowed',405);
        try {
            $reason=$request->post('reason','');
            if (!is_string($reason)) throw new \InvalidArgumentException('退回原因格式错误');
            $result=(new \app\cms\service\ArchiveWorkflowService())->batch($this->workflowIds($request),'return',[],$reason);
            return $this->workflowSaved($request,$result,'文档已退回');
        } catch (\InvalidArgumentException $error) { return $this->json(1,$error->getMessage()); }
    }

    public function resubmit(Request $request): Response
    {
        if ($request->method() !== 'POST') return response('Method Not Allowed',405);
        try {
            $result=(new \app\cms\service\ArchiveWorkflowService())->batch($this->workflowIds($request),'resubmit');
            return $this->workflowSaved($request,$result,'已重新提交审核');
        } catch (\InvalidArgumentException $error) { return $this->json(1,$error->getMessage()); }
    }

    protected function canReadCopySources(): bool
    {
        return \plugin\admin\api\Auth::canAccess(\plugin\admin\app\controller\ArchiveController::class,'select');
    }

    public function copyPlan(Request $request): Response
    {
        if ($request->method() !== 'POST') return response('Method Not Allowed',405);
        if (!$this->canReadCopySources()) return $this->json(1,'无文档读取权限');
        try {
            $result=(new \app\cms\service\ArchiveWorkflowService())->copyPlan($this->workflowIds($request),(int)$request->post('typeid',0),(int)admin_id());
            return $this->json(0,'请确认复制范围',$result);
        } catch (\InvalidArgumentException $error) { return $this->json(1,$error->getMessage()); }
    }

    public function copy(Request $request): Response
    {
        if ($request->method() !== 'POST') return response('Method Not Allowed',405);
        if (!$this->canReadCopySources()) return $this->json(1,'无文档读取权限');
        try {
            $token=$request->post('token','');
            if (!is_string($token)) throw new \InvalidArgumentException('复制凭据格式错误');
            $result=(new \app\cms\service\ArchiveWorkflowService())->executeCopy($token,(int)admin_id());
            return !empty($result['replayed']) ? $this->json(0,'复制已完成，未重复创建',$result) : $this->workflowSaved($request,$result,'复制成功');
        } catch (\InvalidArgumentException $error) { return $this->json(1,$error->getMessage()); }
    }

    private function workflowIds(Request $request): array
    {
        $ids=$request->post('aids',$request->post('aid',[]));
        if (is_string($ids)) $ids=explode(',',$ids);
        if (is_int($ids)) $ids=[$ids];
        if (!is_array($ids)) throw new \InvalidArgumentException('文档ID格式错误');
        return $ids;
    }

    /** Publishing runs after commit; a generation failure must not invite a duplicate write. */
    private function workflowSaved(Request $request,array $result,string $message): Response
    {
        $warnings=[];
        foreach ($result['aids']??[] as $aid) {
            try {
                $generation=(new \app\cms\service\StaticGenerationService())->afterContentSave($request,(int)$aid,!isset($result['mapping']));
                if (!empty($generation['warning'])) $warnings[]=$generation['warning'];
            } catch (\Throwable $error) {
                Log::warning('Archive workflow committed, generation needs retry: '.$error->getMessage());
                $warnings[]='文档已保存，静态生成未完成，请在静态任务中重试';
            }
        }
        $result['warnings']=array_values(array_unique($warnings));
        return $this->json(0,$warnings?$message.'；'.implode('；',$result['warnings']):$message,$result);
    }

    /**
     * 单页模型编辑页面
     * @param Request $request
     * @return Response
     */
    public function singlePage(Request $request): Response
    {
        if ($request->method() !== 'POST') { return \app\cms\admin\support\AdminView::render('archive/singlePage'); }
        try {
            $aid = (new \app\cms\service\ContentService())->saveSingle((int)$request->post('id', 0), $request->post(), (int)admin_id());
            return $this->contentSaved($request, $aid, true);
        } catch (\InvalidArgumentException $error) {
            return $this->json(1, $error->getMessage());
        }
    }

    /** Save has committed before publishing; generation errors cannot undo content. */
    private function contentSaved(Request $request, int $aid, bool $editing): Response
    {
        $generation = (new \app\cms\service\StaticGenerationService())->afterContentSave($request, $aid, $editing);
        $warning = (string)($generation['warning'] ?? '');
        return $this->json(0, $warning !== '' ? $warning : '操作成功', ['aid' => $aid, 'generation' => $generation]);
    }

    /**
     * 获取附加表内容
     * @param Request $request
     * @return Response
     */
    public function getContent(Request $request): Response
    {
        $repository = new \app\cms\repository\CmsRepository();
        $aid = (int)$request->get('aid', 0);
        $archive = $repository->table('archives')->where('aid', $aid)->find();
        if (!$archive) { return $this->json(1, '文档不存在'); }
        (new \app\cms\service\ContentAccessService($repository))->assertArchive($archive);
        $model = $repository->table('channeltype')->where('id', $archive['channel'])->find();
        if (!$model) { return $this->json(1, '模型不存在'); }
        $data = $repository->table($model['table'] . '_content')->where('aid', $aid)->find() ?: [];
        $definitions = $repository->table('channelfield')->where('channel_id', (int)$model['id'])->select()->toArray();
        foreach ($definitions as $field) {
            $key = $field['name'];
            $value = $data[$key] ?? null;
            // Only gallery fields are stored as serialized arrays. Text may legitimately contain PHP examples.
            if ($field['dtype'] === 'imgs' && empty($field['ifmain']) && is_string($value) && preg_match('/^a:\d+:/', $value)) {
                $decoded = @unserialize($value, ['allowed_classes' => false]);
                if (is_array($decoded)) { $data[$key] = $decoded; }
            }
        }
        $data['litpic_list'] = $repository->table($model['nid'] === 'images' ? 'images_upload' : 'product_img')->where('aid', $aid)->order('sort_order asc,img_id asc')->select()->toArray();
        if (in_array($model['nid'], ['download', 'media'], true)) { $data[$model['nid'] === 'download' ? 'download_files' : 'media_files'] = $repository->table($model['nid'] . '_file')->where('aid', $aid)->order('sort_order asc,file_id asc')->select()->toArray(); }
        $data['tags'] = implode(',', $repository->table('taglist')->where('aid', $aid)->column('tag'));
        $addonFields = ['content','content_ey_m']; $mainFields = [];
        foreach ($definitions as $field) { if ($field['dtype'] !== 'htmltext') continue; if (!empty($field['ifmain'])) $mainFields[]=$field['name']; else $addonFields[]=$field['name']; }
        $provenance = new \app\cms\service\HtmltextProvenanceService($repository);
        $data['__htmltext'] = $provenance->metadata($model['table'].'_content',$aid,$data,$addonFields)
            + $provenance->metadata('archives',$aid,$archive,$mainFields);
        return $this->json(0, 'ok', $data);
    }

    /**
     * 指定查询where条件,并没有真正的查询数据库操作
     * 重写父类方法，添加联查栏目表
     * @param array $where
     * @param string|null $field
     * @param string $order
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\support\Model
     */
    /**
     * 查询前置（重写父类方法，保留 exclude_channel 参数）
     * @param Request $request
     * @return array
     * @throws BusinessException
     */
    protected function selectInput(Request $request): array
    {
        $field = $request->get('field');
        $order = $request->get('order', 'asc');
        $format = $request->get('format', 'normal');
        $limit = (int)$request->get('limit', $format === 'tree' ? 1000 : 10);
        $limit = $limit <= 0 ? 10 : $limit;
        $order = $order === 'asc' ? 'asc' : 'desc';
        $where = $request->get();
        $page = (int)$request->get('page');
        $page = $page > 0 ? $page : 1;
        $table = Util::getTablePrefix() . $this->model->getTable();

        $allow_column = Util::db()->select("desc `$table`");
        if (!$allow_column) {
            throw new BusinessException('表不存在');
        }
        $allow_column = array_column($allow_column, 'Field', 'Field');
        if (!in_array($field, $allow_column)) {
            $field = null;
        }
        
        // 保留 exclude_channel 参数（不是表字段，但需要用于过滤）
        $excludeChannel = $where['exclude_channel'] ?? null;
        
        foreach ($where as $column => $value) {
            if (
                $value === '' || !isset($allow_column[$column]) ||
                is_array($value) && (empty($value) || !in_array($value[0], ['null', 'not null']) && !isset($value[1]))
            ) {
                unset($where[$column]);
            }
        }
        
        // 如果 exclude_channel 被过滤掉了，重新添加
        if ($excludeChannel !== null && !isset($where['exclude_channel'])) {
            $where['exclude_channel'] = $excludeChannel;
        }
        
        // 按照数据限制字段返回数据
        if ($this->dataLimit && !Auth::isSuperAdmin()) {
            if ($this->dataLimit === 'personal') {
                $where[$this->dataLimitField] = admin_id();
            } elseif ($this->dataLimit === 'auth') {
                $where[$this->dataLimitField] = ['in', implode(',', Auth::getScopeAdminIds(true))];
            }
        }
        return [$where, $format, $limit, $field, $order];
    }

    protected function doSelect(array $where, ?string $field = null, string $order = 'desc')
    {
        $model = $this->model->where('archives.is_del', 0);
        
        // 处理 exclude_channel 参数（排除指定的模型）
        $excludeChannel = null;
        if (isset($where['exclude_channel'])) {
            $excludeChannel = $where['exclude_channel'];
            unset($where['exclude_channel']); // 从 where 中移除，避免被当作普通条件处理
        }
        
        // 处理 where 条件
        foreach ($where as $column => $value) {
            // 如果字段名不包含表名，添加表名前缀
            if (strpos($column, '.') === false) {
                $column = 'archives.' . $column;
            }
            
            if (is_array($value)) {
                if ($value[0] === 'like' || $value[0] === 'not like') {
                    $model = $model->where($column, $value[0], "%$value[1]%");
                } elseif (in_array($value[0], ['>', '=', '<', '<>'])) {
                    $model = $model->where($column, $value[0], $value[1]);
                } elseif ($value[0] == 'in' && !empty($value[1])) {
                    $valArr = $value[1];
                    if (is_string($value[1])) {
                        $valArr = explode(",", trim($value[1]));
                    }
                    $model = $model->whereIn($column, $valArr);
                } elseif ($value[0] == 'not in' && !empty($value[1])) {
                    $valArr = $value[1];
                    if (is_string($value[1])) {
                        $valArr = explode(",", trim($value[1]));
                    }
                    $model = $model->whereNotIn($column, $valArr);
                } elseif ($value[0] == 'null') {
                    $model = $model->whereNull($column);
                } elseif ($value[0] == 'not null') {
                    $model = $model->whereNotNull($column);
                } elseif ($value[0] !== '' || $value[1] !== '') {
                    $model = $model->whereBetween($column, $value);
                }
            } else {
                $model = $model->where($column, $value);
            }
        }
        
        // 处理排序字段，明确指定表名
        if ($field) {
            if (strpos($field, '.') === false) {
                $field = 'archives.' . $field;
            }
            $model = $model->orderBy($field, $order);
        } else {
            // 默认按排序号和时间排序
            $model = $model->orderBy('archives.sort_order', 'desc')
                          ->orderBy('archives.add_time', 'desc');
        }
        
        // 联查栏目表，获取栏目名称
        $model = $model->leftJoin('arctype', 'archives.typeid', '=', 'arctype.id')
                      ->select('archives.*', 'arctype.typename');
        
        // 处理 exclude_channel：排除指定的模型（单页模型和留言模型）
        if ($excludeChannel !== null) {
            $excludeChannels = [];
            if (is_string($excludeChannel)) {
                $excludeChannels = array_map('trim', explode(',', $excludeChannel));
            } elseif (is_array($excludeChannel)) {
                $excludeChannels = $excludeChannel;
            } else {
                $excludeChannels = [(string)$excludeChannel];
            }
            if (!empty($excludeChannels)) {
                $model = $model->whereNotIn('arctype.current_channel', $excludeChannels);
            }
        }
        
        return (new \app\cms\service\ContentAccessService())->filterArchiveQuery($model,'archives');
    }

    /**
     * 查询后置处理：补充栏目名称等字段
     * @param mixed $items
     * @return mixed
     */
    protected function afterQuery($items)
    {
        $channelNids = (new \app\cms\repository\CmsRepository())->table('channeltype')->column('nid', 'id');
        foreach ($items as $item) {
            $item->channel_nid = $channelNids[$item->channel] ?? '';
            // 如果没有关联到栏目，显示空字符串
            if (empty($item->typename)) {
                $item->typename = '';
            }
        }
        return $items;
    }

    /**
     * 过滤附加表数据（类似 inputFilter，但基于附加表结构）
     * 
     * 为什么需要这个过滤：
     * 1. inputFilter 是基于主表（archives）结构，不能用于附加表
     * 2. 附加表（如 wa_article_content）是动态表，字段结构可能不同
     * 3. handleAddonField 处理后的数据可能包含附加表中不存在的字段
     * 4. 附加表可能没有某些字段（如 typeid、add_time、update_time 等）
     * 
     * @param string $tableName 附加表名
     * @param array $data 要过滤的数据
     * @return array 过滤后的数据
     * @throws BusinessException
     */
    protected function filterAddonTableData(string $tableName, array $data): array
    {
        $db = Util::db();
        $tableColumns = $db->select("SHOW COLUMNS FROM `{$tableName}`");
        if (!$tableColumns) {
            throw new BusinessException("附加表 {$tableName} 不存在", 2);
        }
        
        $columns = array_column($tableColumns, 'Type', 'Field');
        $filteredData = [];
        
        foreach ($data as $col => $item) {
            // 只保留表中存在的字段
            if (!isset($columns[$col])) {
                continue;
            }
            
            // 非字符串类型传空则为null（类似 inputFilter 的逻辑）
            if ($item === '' && strpos(strtolower($columns[$col]), 'varchar') === false && strpos(strtolower($columns[$col]), 'text') === false) {
                $filteredData[$col] = null;
            } else {
                $filteredData[$col] = $item;
            }
            
            // 处理数组（转换为逗号分隔字符串）
            if (is_array($filteredData[$col])) {
                $filteredData[$col] = implode(',', $filteredData[$col]);
            }
        }
        
        // 处理时间字段（如果存在）
        if (isset($columns['add_time']) && !isset($filteredData['add_time'])) {
            $filteredData['add_time'] = time();
        }
        if (isset($columns['update_time']) && !isset($filteredData['update_time'])) {
            $filteredData['update_time'] = time();
        }
        
        return $filteredData;
    }

    /**
     * 获取常用标签列表（用于标签输入框提示）
     */
    public function getCommonTags(Request $request)
    {
        $tags = $request->input('tags', '');
        // $type = $request->input('type', 0); // 0=常用标签，1=输入提示（当前未使用）
        $isClick = $request->input('is_click', 0); // 是否点击了"显示常用标签"按钮
        
        $lang = (string)$request->input('lang', \app\cms\CmsContext::current()->lang);
        
        $newTagList = [];
        $newtids = [];
        
        // 如果点击了"显示常用标签"按钮，忽略输入框内容，返回全部标签
        $searchTags = '';
        if ($isClick == 0) {
            // 如果输入框有值，取最后一个标签作为搜索关键词
            if (!empty($tags)) {
                $tagsArr = explode(',', $tags);
                $searchTags = trim(end($tagsArr));
            }
        }
        
        // 发布最新文档的tag里前3个
        if (empty($searchTags)) {
            $taglistRow = Taglist::where('lang', $lang)
                ->select('tid', 'tag')
                ->orderBy('aid', 'desc')
                ->limit(20)
                ->get()
                ->toArray();
            
            foreach ($taglistRow as $val) {
                if (count($newTagList) >= 3) {
                    break;
                }
                $tid = is_array($val) ? $val['tid'] : $val->tid;
                if (!in_array($tid, $newtids)) {
                    $newTagList[] = [
                        'tid' => $tid,
                        'tag' => is_array($val) ? $val['tag'] : $val->tag
                    ];
                    $newtids[] = $tid;
                }
            }
        }
        $list = $newTagList;
        
        // 常用标签
        $tagindexQuery = Tagindex::where('lang', $lang);
        if (!empty($newtids)) {
            $tagindexQuery->whereNotIn('id', $newtids);
        }
        // 只有在非点击按钮的情况下才进行搜索过滤
        if (!empty($searchTags)) {
            $tagindexQuery->where('tag', 'like', '%' . $searchTags . '%');
        }
        
        $num = 20 - count($list);
        $row = $tagindexQuery->where('is_common', 1)
            ->select('id as tid', 'tag')
            ->orderBy('total', 'desc')
            ->orderBy('id', 'desc')
            ->limit($num)
            ->get()
            ->toArray();
        
        // 转换为统一格式
        foreach ($row as $key => $item) {
            $row[$key] = [
                'tid' => is_array($item) ? $item['tid'] : $item->tid,
                'tag' => is_array($item) ? $item['tag'] : $item->tag
            ];
        }
        $list = array_merge($list, $row);
        
        // 不够数量进行补充
        $surplusNum = $num - count($list);
        if ($surplusNum > 0) {
            // 提取 tid
            $ids = [];
            foreach ($list as $item) {
                $ids[] = $item['tid'];
            }
            
            $tagindexQuery2 = Tagindex::where('lang', $lang);
            if (!empty($ids)) {
                $tagindexQuery2->whereNotIn('id', $ids);
            }
            // 只有在非点击按钮的情况下才进行搜索过滤
            if (!empty($searchTags)) {
                $tagindexQuery2->where('tag', 'like', '%' . $searchTags . '%');
            }
            
            $row2 = $tagindexQuery2->select('id as tid', 'tag')
                ->orderBy('total', 'desc')
                ->orderBy('id', 'desc')
                ->limit($surplusNum)
                ->get()
                ->toArray();
            
            // 转换为统一格式
            foreach ($row2 as $key => $item) {
                $row2[$key] = [
                    'tid' => is_array($item) ? $item['tid'] : $item->tid,
                    'tag' => is_array($item) ? $item['tag'] : $item->tag
                ];
            }
            $list = array_merge($list, $row2);
        }
        
        // 返回数组，不生成HTML
        $result = [];
        if (!empty($list)) {
            $tagsInput = $request->input('tags', '');
            $tagsInput = str_replace('，', ',', $tagsInput);
            $tagArr = explode(',', $tagsInput);
            foreach ($tagArr as $key => $val) {
                $tagArr[$key] = trim($val);
            }
            
            foreach ($list as $item) {
                $tagName = $item['tag'] ?? '';
                if (empty($tagName)) {
                    continue;
                }
                $result[] = [
                    'tid' => $item['tid'] ?? 0,
                    'tag' => $tagName,
                    'selected' => in_array($tagName, $tagArr)
                ];
            }
        }
        
        return $this->json(0, 'success', ['list' => $result]);
    }

}
