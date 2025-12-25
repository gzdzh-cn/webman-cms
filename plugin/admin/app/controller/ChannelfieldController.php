<?php

namespace plugin\admin\app\controller;

use support\Request;
use support\Response;
use plugin\admin\app\model\Channelfield;
use plugin\admin\app\model\Channeltype;
use plugin\admin\app\model\Arctype;
use plugin\admin\app\controller\Crud;
use support\exception\BusinessException;

/**
 * 频道字段管理
 */
class ChannelfieldController extends Crud
{
    /**
     * @var Channelfield
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Channelfield;
    }
    /**
     * 字段列表
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        // 如果是数据请求（有 page 或 limit 参数），返回 JSON 数据
        if ($request->get('page') || $request->get('limit')) {
            $channel_id = (int)$request->get('channel_id', 0);
            $page = (int)$request->get('page', 1);
            $limit = (int)$request->get('limit', 10);
            
            $query = Channelfield::where('ifcontrol', 0);
            
            // 通过 channel_id 筛选
            if ($channel_id > 0) {
                $query = $query->where('channel_id', $channel_id);
            }
            
            // 搜索条件 - 支持 keywords 和 keyword 两种参数名（兼容原项目）
            $keywords = trim($request->get('keywords', '') ?: $request->get('keyword', ''));
            if ($keywords) {
                $keywords = addslashes($keywords); // 防止SQL注入
                $query = $query->where(function($q) use ($keywords) {
                    $q->where('channelfield.title', 'like', "%{$keywords}%")
                      ->orWhere('channelfield.name', 'like', "%{$keywords}%");
                });
            }
            
            // 关联查询模型名称
            $query = $query->leftJoin('channeltype', 'channelfield.channel_id', '=', 'channeltype.id')
                          ->select('channelfield.*', 'channeltype.title as channel_title');
            
            // 排序：系统字段优先，然后按排序字段和ID排序（参考原项目：ifsystem desc, id asc）
            $query = $query->orderBy('channelfield.ifsystem', 'desc')
                          ->orderBy('channelfield.sort_order', 'asc')
                          ->orderBy('channelfield.id', 'asc');
            
            // 使用 paginate 进行分页
            $paginator = $query->paginate($limit, ['*'], 'page', $page);
            
            $items = $paginator->items();
            $data = [];
            foreach ($items as $item) {
                $row = is_array($item) ? $item : $item->toArray();
                // 如果没有关联到模型，显示"通用"或空
                if (empty($row['channel_title'])) {
                    $row['channel_title'] = $row['channel_id'] == 0 ? '通用' : '';
                }
                $data[] = $row;
            }
            
            return json(['code' => 0, 'msg' => 'ok', 'count' => $paginator->total(), 'data' => $data]);
        }
        
        // 否则返回视图
        return view('channelfield/index', ['channel_id' => $request->get('channel_id', 0)]);
    }

    /**
     * 字段新增
     * @param Request $request
     * @return Response
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return view('channelfield/insert', ['channel_id' => $request->get('channel_id', 0)]);
        }
        
        $data = $request->post();
        $data['channel_id'] = (int)$request->get('channel_id', 0);
        $channelfield = new Channelfield();
        $channelfield->fill($data);
        $channelfield->save();
        return $this->json(0, 'ok', ['id' => $channelfield->id]);
    }

    /**
     * 字段更新
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response
    {
        if ($request->method() === 'GET') {
            $id = (int)$request->get('id', 0);
            if (!$id) {
                return $this->json(1, 'ID不能为空');
            }
            return view('channelfield/update', [
                'channel_id' => $request->get('channel_id', 0),
                'id' => $id
            ]);
        }
        
        // POST 请求：更新数据
        $id = (int)($request->post('id', 0) ?: $request->get('id', 0));
        if (!$id) {
            return $this->json(1, 'ID不能为空');
        }
        
        $data = $request->post();
        unset($data['id']);
        
        if (empty($data)) {
            return $this->json(1, '没有要更新的数据');
        }
        
        $channelfield = Channelfield::find($id);
        if (!$channelfield) {
            return $this->json(1, '记录不存在');
        }
        
        $channelfield->fill($data);
        $channelfield->save();
        return $this->json(0);
    }

    /**
     * 字段删除
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request): Response
    {
        $ids = $request->post('id', '');
        if (empty($ids)) {
            return $this->json(1, 'ID不能为空');
        }
        
        $ids = is_array($ids) ? $ids : explode(',', $ids);
        Channelfield::whereIn('id', $ids)->delete();
        return $this->json(0);
    }

    /**
     * 标签调用
     * @param Request $request
     * @return Response
     */
    public function tagCall(Request $request): Response
    {
        return view('channelfield/tagCall');
    }

    /**
     * 获取指定模型的栏目列表（树形结构）
     * @param Request $request
     * @return Response
     */
    public function getArctypeList(Request $request): Response
    {
        // 支持 channel_id 和 channeltype 两种参数名（前端使用 channeltype）
        $channel_id = (int)($request->get('channeltype', 0) ?: $request->get('channelId', 0));
        $format = $request->get('format', 'tree');
        $limit = (int)$request->get('limit', 2000);
        
        $query = Arctype::where('is_del', 0);

        // 通过 channeltype 筛选（对应模型的栏目）
        if ($channel_id > 0) {
            $query = $query->where('channeltype', $channel_id);
        }
        
        $items = $query->orderBy('sort_order', 'asc')
                      ->orderBy('id', 'asc')
                      ->limit($limit)
                      ->get()
                      ->toArray();
        
        // 处理数据，兼容前端树组件的字段名
        foreach ($items as &$item) {
            $item['pid'] = $item['parent_id'];
            $item['name'] = $item['typename'];
        }
        
        // 如果是树形格式，转换为树形结构
        if ($format === 'tree') {
            $items = $this->buildTree($items);
        }
        
        return json(['code' => 0, 'msg' => 'ok', 'data' => $items]);
    }

    /**
     * 将扁平数据转换为树形结构
     * @param array $items
     * @param int $pid
     * @return array
     */
    private function buildTree(array $items, $pid = 0): array
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item['parent_id'] == $pid) {
                $children = $this->buildTree($items, $item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }
}

