<?php

namespace plugin\admin\app\controller;

use support\Request;
use support\Response;
use plugin\admin\app\model\Channeltype;
use plugin\admin\app\model\Channelfield;
use plugin\admin\app\controller\Crud;
use plugin\admin\app\logic\FieldLogic;
use support\exception\BusinessException;


/**
 * 频道模型 
 */
class ChanneltypeController extends Crud
{
    
    /**
     * @var Channeltype
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Channeltype;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('channeltype/index');
    }

    /**
     * 指定查询where条件,并没有真正的查询数据库操作
     * 重写父类方法，添加 status=1 的过滤条件
     * @param array $where
     * @param string|null $field
     * @param string $order
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\support\Model
     */
    protected function doSelect(array $where, ?string $field = null, string $order = 'desc')
    {
        // 先调用父类方法
        $query = parent::doSelect($where, $field, $order);
        
        // 只返回 status=1 的模型
        $query = $query->where('status', 1);
        
        return $query;
    }

    /**
     * 查询后置方法，添加字段数量统计
     * @param mixed $items
     * @return mixed
     */
    protected function afterQuery($items)
    {
        // 获取所有 channel_id
        $channelIds = [];
        foreach ($items as $item) {
            $channelIds[] = $item->id;
        }
        
        if (empty($channelIds)) {
            return $items;
        }
        
        // 获取字段数量统计（只统计 ifcontrol=0 的字段）
        $fieldCounts = Channelfield::where('ifcontrol', 0)
            ->whereIn('channel_id', $channelIds)
            ->selectRaw('channel_id, count(id) as num')
            ->groupBy('channel_id')
            ->pluck('num', 'channel_id')
            ->toArray();
        
        // 为每个模型添加字段数量
        foreach ($items as $item) {
            $channelId = $item->id;
            $item->field_count = isset($fieldCounts[$channelId]) ? (int)$fieldCounts[$channelId] : 0;
        }
        
        return $items;
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $data = $this->insertInput($request);
            $id = $this->doInsert($data);
            
            // 插入成功后，同步主表 archives 的字段到 channelfield
            if ($id) {
                $fieldLogic = new FieldLogic();
                $fieldLogic->synArchivesTableColumns($id);
            }
            
            return $this->json(0, 'ok', ['id' => $id]);
        }
        return view('channeltype/insert');
    }

    /**
     * 插入前置方法，自动设置相关字段的值
     * @param Request $request
     * @return array
     * @throws BusinessException
     */
    protected function insertInput(Request $request): array
    {
        $data = parent::insertInput($request);
        
        // 验证 title 和 nid 不能为空
        if (empty($data['title'])) {
            throw new BusinessException('模型名称不能为空');
        }
        if (empty($data['nid'])) {
            throw new BusinessException('模型标识不能为空');
        }
        
        // 检查 nid 是否唯一
        $nid = $data['nid'];
        $exists = $this->model->where('nid', $nid)->exists();
        if ($exists) {
            throw new BusinessException('模型标识已存在，请使用其他值');
        }
        
        // table 字段的值和 nid 一样
        $data['table'] = $nid;
        
        // ctl_name 的值是 nid 的首字母大写
        // 将首字母转换为大写
        $ctl_name = ucfirst($nid);
        $data['ctl_name'] = $ctl_name;
        
        // ntitle 和 title 一样的值
        $data['ntitle'] = $data['title'];
        
        return $data;
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::update($request);
        }
        return view('channeltype/update');
    }

    /**
     * 删除
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function delete(Request $request): Response
    {
        $ids = $this->deleteInput($request);
        
        // 先删除模型
        $this->doDelete($ids);
        
        // 模型删除成功后，再删除该模型在 channelfield 表中的所有字段
        if (!empty($ids)) {
            Channelfield::whereIn('channel_id', $ids)->delete();
        }
        
        return $this->json(0);
    }

}
