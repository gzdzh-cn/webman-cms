<?php

namespace plugin\admin\app\model;

use plugin\admin\app\model\Base;

/**
 * @property integer $aid (主键)
 * @property integer $typeid 当前栏目
 * @property string $stypeid 副栏目ID集合
 * @property integer $channel 模型ID
 * @property integer $is_b 加粗
 * @property string $title 文档标题
 * @property string $subtitle 副标题
 * @property string $introduction 促销语
 * @property string $litpic 封面图片
 * @property integer $is_head 头条（0=否，1=是）
 * @property integer $is_special 特荐（0=否，1=是）
 * @property integer $is_top 置顶（0=否，1=是）
 * @property integer $is_recom 推荐（0=否，1=是）
 * @property integer $is_jump 跳转链接（0=否，1=是）
 * @property integer $is_litpic 图片（0=否，1=是）
 * @property integer $is_roll 滚动（0=否，1=是）
 * @property integer $is_slide 幻灯（0=否，1=是）
 * @property integer $is_diyattr 自定义（0=否，1=是）
 * @property string $origin 来源
 * @property string $author 作者
 * @property integer $click 点击数
 * @property integer $arcrank 阅读权限：0=开放浏览，-1=待审核稿件
 * @property string $jumplinks 跳转网址
 * @property integer $ismake 是否静态页面（0=动态，1=静态）
 * @property string $seo_title SEO标题
 * @property string $seo_keywords SEO关键词
 * @property string $seo_description SEO描述
 * @property integer $attrlist_id 参数列表ID
 * @property integer $merchant_id 多商家ID
 * @property integer $free_shipping 商品是否包邮(1包邮(免运费)  0跟随系统)
 * @property string $users_price 会员价
 * @property string $crossed_price 商品划线价
 * @property integer $users_discount_type 产品会员折扣类型(0:系统默认折扣; 1:指定会员级别; 2:不参与折扣;)
 * @property integer $users_free 是否会员免费，默认0不免费，1为免费
 * @property string $old_price 产品旧价
 * @property integer $sales_num 总销售量
 * @property integer $virtual_sales 商品虚拟销售量
 * @property integer $sales_all 虚拟总销量
 * @property integer $stock_count 商品库存量
 * @property integer $stock_show 商品库存在产品详情页是否显示，1为显示，0为不显示
 * @property integer $prom_type 产品类型：0=普通产品，1=虚拟(默认手动发货)，2=虚拟(网盘)，3=虚拟(自定义文本) 4-核销
 * @property string $logistics_type 商品物流支持类型(1: 物流配送; 2: 到店核销)
 * @property string $tempview 文档模板
 * @property integer $status 状态(0=屏蔽，1=正常)
 * @property integer $sort_order 排序号
 * @property string $lang 语言标识
 * @property integer $admin_id 管理员ID
 * @property integer $users_id 会员ID
 * @property integer $arc_level_id 文档会员权限ID
 * @property integer $restric_type 限制模式，0=免费，1=付费，2=会员专享，3=会员付费，4=会员积分购买
 * @property string $users_score restric_type=4时，会员可使用积分进行文章订单支付购买
 * @property integer $is_del 伪删除，1=是，0=否
 * @property integer $del_method 伪删除状态，1为主动删除，2为跟随上级栏目被动删除
 * @property integer $joinaid 关联文档ID
 * @property integer $downcount 下载次数
 * @property integer $appraise 评价数
 * @property integer $collection 收藏数
 * @property string $htmlfilename 自定义文件名
 * @property integer $province_id 省份
 * @property integer $city_id 所在城市
 * @property integer $area_id 所在区域
 * @property integer $add_time 新增时间
 * @property integer $update_time 更新时间
 * @property integer $removal_time 下架时间（用于自动下架，配合定时发布插件的下架功能）
 * @property integer $no_vip_pay restric_type = 2 时,会员专享,非会员可付费使用,0-关闭,1-开启
 * @property integer $editor_remote_img_local 远程图片本地化
 * @property integer $editor_img_clear_link 清除非本站链接
 * @property integer $editor_ai_create AI创作声明
 * @property string $reason 退回原因
 * @property string $stock_code 商品编码
 */
class Archive extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'archives';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'aid';
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    
    
}
