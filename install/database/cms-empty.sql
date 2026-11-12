-- CMS empty-site schema and essential seeds. Derived from the verified 1.7.7 compatibility baseline.
-- No website categories, documents, accounts or visitor submissions.
CREATE TABLE `cms_ad` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '广告id',
  `pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '广告位置ID',
  `media_type` tinyint(1) DEFAULT '0' COMMENT '广告类型',
  `title` varchar(500) DEFAULT '' COMMENT '广告名称',
  `links` varchar(500) DEFAULT '' COMMENT '广告链接',
  `litpic` varchar(500) DEFAULT '' COMMENT '图片地址',
  `start_time` int(11) DEFAULT '0' COMMENT '投放时间',
  `end_time` int(11) DEFAULT '0' COMMENT '结束时间',
  `intro` text COMMENT '描述',
  `link_man` varchar(60) DEFAULT '' COMMENT '添加人',
  `link_email` varchar(60) DEFAULT '' COMMENT '添加人邮箱',
  `link_phone` varchar(60) DEFAULT '' COMMENT '添加人联系电话',
  `click` int(11) DEFAULT '0' COMMENT '点击量',
  `bgcolor` varchar(30) DEFAULT '' COMMENT '背景颜色',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '1=显示，0=屏蔽',
  `sort_order` int(11) DEFAULT '0' COMMENT '排序',
  `target` varchar(50) DEFAULT '' COMMENT '是否开启浏览器新窗口',
  `admin_id` int(10) DEFAULT '0' COMMENT '管理员ID',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '伪删除，1=是，0=否',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '多语言',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `position_id` (`pid`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='广告表';
CREATE TABLE `cms_ad_position` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(60) NOT NULL DEFAULT '' COMMENT '广告位置名称',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '广告展示类型，1图片类型，2媒体类型，3HTML代码',
  `width` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '广告位宽度',
  `height` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '广告位高度',
  `intro` text NOT NULL COMMENT '广告描述',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0关闭1开启',
  `lang` varchar(50) NOT NULL DEFAULT 'cn' COMMENT '多语言',
  `admin_id` int(10) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '伪删除，1=是，0=否',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='广告位置表';
CREATE TABLE `cms_admin` (
  `admin_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `user_name` varchar(20) NOT NULL DEFAULT '' COMMENT '用户名',
  `pen_name` varchar(50) DEFAULT '' COMMENT '笔名（发布文章后显示责任编辑的名字）',
  `true_name` varchar(20) DEFAULT '' COMMENT '真实姓名',
  `mobile` varchar(11) DEFAULT '' COMMENT '手机号码',
  `email` varchar(60) DEFAULT '' COMMENT 'email',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  `head_pic` varchar(255) DEFAULT '' COMMENT '头像',
  `last_login` int(11) DEFAULT '0' COMMENT '最后登录时间',
  `last_ip` varchar(15) DEFAULT '' COMMENT '最后登录ip',
  `login_cnt` int(11) DEFAULT '0' COMMENT '登录次数',
  `session_id` varchar(50) DEFAULT '' COMMENT 'session_id',
  `parent_id` int(10) DEFAULT '0' COMMENT '父管理员ID',
  `role_id` int(10) NOT NULL DEFAULT '-1' COMMENT '角色组ID（-1表示超级管理员）',
  `mark_lang` varchar(50) DEFAULT 'cn' COMMENT '当前语言标识',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态(0=屏蔽，1=正常)',
  `syn_users_id` int(10) DEFAULT '0' COMMENT '同步注册到会员表',
  `desc` varchar(500) DEFAULT '' COMMENT '工作内容',
  `wechat_appid` varchar(50) DEFAULT '' COMMENT '公众号appid',
  `wechat_followed` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '记录是否关注了微信公众号，默认0；0=未关注、1=已关注',
  `wechat_open_id` varchar(50) NOT NULL DEFAULT '' COMMENT 'open_id，关注微信公众号后存入',
  `union_id` varchar(50) DEFAULT '' COMMENT '微信用户的unionId',
  `add_time` int(11) DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`admin_id`),
  KEY `user_name` (`user_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='管理员表';
CREATE TABLE `cms_admin_log` (
  `log_id` bigint(16) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `admin_id` int(10) NOT NULL DEFAULT '-1' COMMENT '管理员id',
  `log_info` text COMMENT '日志描述',
  `log_ip` varchar(30) DEFAULT '' COMMENT 'ip地址',
  `log_url` varchar(255) DEFAULT '' COMMENT 'url',
  `log_time` int(11) DEFAULT '0' COMMENT '日志时间',
  PRIMARY KEY (`log_id`),
  KEY `admin_id` (`admin_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=693 DEFAULT CHARSET=utf8 COMMENT='管理员操作日志表';
CREATE TABLE `cms_admin_menu` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` int(11) DEFAULT '0',
  `title` varchar(100) DEFAULT '' COMMENT '导航名称',
  `original_title` varchar(100) DEFAULT '' COMMENT '自定义标题',
  `controller_name` varchar(50) DEFAULT '' COMMENT '控制器',
  `action_name` varchar(50) DEFAULT '' COMMENT '方法名',
  `param` varchar(255) DEFAULT '' COMMENT '参数',
  `icon` varchar(50) DEFAULT 'iconfont e-lanmuguanli' COMMENT '图标',
  `is_menu` tinyint(1) DEFAULT '0' COMMENT '是否显示为左侧菜单',
  `is_switch` tinyint(1) DEFAULT '0' COMMENT '是否显示在switch_map页面中',
  `target` varchar(50) DEFAULT 'workspace' COMMENT '链接打开方式',
  `sort_order` int(10) DEFAULT '100' COMMENT '排序号',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，1=显示，0=隐藏',
  `lang` varchar(20) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_id` (`menu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='外挂功能地图菜单表';
CREATE TABLE `cms_admin_theme` (
  `theme_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `theme_type` tinyint(1) DEFAULT '0' COMMENT '主题类型：1=登录页，2=欢迎页',
  `theme_title` varchar(50) DEFAULT '' COMMENT '主题标题',
  `theme_pic` varchar(255) DEFAULT '' COMMENT '主题效果图',
  `theme_color_model` varchar(10) DEFAULT '' COMMENT '主题颜色模式',
  `theme_main_color` varchar(20) DEFAULT '' COMMENT '主题主色',
  `theme_assist_color` varchar(20) DEFAULT '' COMMENT '主题辅色',
  `login_logo` varchar(255) DEFAULT '' COMMENT '登录图标',
  `login_bgimg_model` varchar(10) DEFAULT '' COMMENT '登录背景图模式',
  `login_bgimg` varchar(255) DEFAULT '' COMMENT '登录背景图',
  `login_tplname` varchar(100) DEFAULT '' COMMENT '登录页自定义模板',
  `admin_logo` varchar(255) DEFAULT '' COMMENT '后台Logo',
  `welcome_tplname` varchar(100) DEFAULT '' COMMENT '欢迎页自定义模板',
  `is_system` tinyint(1) DEFAULT '0' COMMENT '内置主题',
  `sort_order` int(10) DEFAULT '100' COMMENT '排序号',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`theme_id`)
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8 COMMENT='后台主题风格表';
CREATE TABLE `cms_admin_wxlogin` (
  `wx_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1=官方公众号，2=微信应用',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `openid` varchar(50) NOT NULL DEFAULT '' COMMENT 'openid',
  `nickname` varchar(100) NOT NULL DEFAULT '' COMMENT '微信昵称',
  `unionid` varchar(200) NOT NULL DEFAULT '' COMMENT 'unionid',
  `headimgurl` varchar(200) NOT NULL DEFAULT '' COMMENT '头像',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`wx_id`) USING BTREE,
  KEY `openid` (`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='后台微信登录记录表';
CREATE TABLE `cms_archives` (
  `aid` int(10) NOT NULL AUTO_INCREMENT,
  `typeid` int(10) NOT NULL DEFAULT '0' COMMENT '当前栏目',
  `stypeid` varchar(90) DEFAULT '' COMMENT '副栏目ID集合',
  `channel` int(10) NOT NULL DEFAULT '0' COMMENT '模型ID',
  `is_b` tinyint(1) DEFAULT '0' COMMENT '加粗',
  `title` varchar(500) DEFAULT '' COMMENT '文档标题',
  `subtitle` varchar(500) DEFAULT '' COMMENT '副标题',
  `introduction` varchar(500) DEFAULT '' COMMENT '促销语',
  `litpic` varchar(250) DEFAULT '' COMMENT '封面图片',
  `is_head` tinyint(1) DEFAULT '0' COMMENT '头条（0=否，1=是）',
  `is_special` tinyint(1) DEFAULT '0' COMMENT '特荐（0=否，1=是）',
  `is_top` tinyint(1) DEFAULT '0' COMMENT '置顶（0=否，1=是）',
  `is_recom` tinyint(1) DEFAULT '0' COMMENT '推荐（0=否，1=是）',
  `is_jump` tinyint(1) DEFAULT '0' COMMENT '跳转链接（0=否，1=是）',
  `is_litpic` tinyint(1) DEFAULT '0' COMMENT '图片（0=否，1=是）',
  `is_roll` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '滚动（0=否，1=是）',
  `is_slide` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '幻灯（0=否，1=是）',
  `is_diyattr` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '自定义（0=否，1=是）',
  `origin` varchar(200) DEFAULT '' COMMENT '来源',
  `author` varchar(200) DEFAULT '' COMMENT '作者',
  `click` int(10) DEFAULT '0' COMMENT '点击数',
  `arcrank` int(10) DEFAULT '0' COMMENT '阅读权限：0=开放浏览，-1=待审核稿件',
  `jumplinks` varchar(255) DEFAULT '' COMMENT '跳转网址',
  `ismake` tinyint(1) DEFAULT '0' COMMENT '是否静态页面（0=动态，1=静态）',
  `seo_title` varchar(500) DEFAULT '' COMMENT 'SEO标题',
  `seo_keywords` varchar(500) DEFAULT '' COMMENT 'SEO关键词',
  `seo_description` text COMMENT 'SEO描述',
  `attrlist_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '参数列表ID',
  `merchant_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '多商家ID',
  `free_shipping` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '商品是否包邮(1包邮(免运费)  0跟随系统)',
  `users_price` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '会员价',
  `crossed_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '商品划线价',
  `users_discount_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '产品会员折扣类型(0:系统默认折扣; 1:指定会员级别; 2:不参与折扣;)',
  `users_free` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否会员免费，默认0不免费，1为免费',
  `old_price` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '产品旧价',
  `sales_num` int(10) NOT NULL DEFAULT '0' COMMENT '总销售量',
  `virtual_sales` int(10) DEFAULT '0' COMMENT '商品虚拟销售量',
  `sales_all` int(10) DEFAULT '0' COMMENT '虚拟总销量',
  `stock_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品库存量',
  `stock_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '商品库存在产品详情页是否显示，1为显示，0为不显示',
  `prom_type` tinyint(1) unsigned DEFAULT '0' COMMENT '产品类型：0=普通产品，1=虚拟(默认手动发货)，2=虚拟(网盘)，3=虚拟(自定义文本) 4-核销',
  `logistics_type` varchar(100) DEFAULT '1' COMMENT '商品物流支持类型(1: 物流配送; 2: 到店核销)',
  `tempview` varchar(200) DEFAULT '' COMMENT '文档模板',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态(0=屏蔽，1=正常)',
  `sort_order` int(10) DEFAULT '0' COMMENT '排序号',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `admin_id` int(10) DEFAULT '0' COMMENT '管理员ID',
  `users_id` int(10) DEFAULT '0' COMMENT '会员ID',
  `arc_level_id` int(10) DEFAULT '0' COMMENT '文档会员权限ID',
  `restric_type` tinyint(1) DEFAULT '0' COMMENT '限制模式，0=免费，1=付费，2=会员专享，3=会员付费，4=会员积分购买',
  `users_score` varchar(20) DEFAULT '' COMMENT 'restric_type=4时，会员可使用积分进行文章订单支付购买',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '伪删除，1=是，0=否',
  `del_method` tinyint(1) DEFAULT '0' COMMENT '伪删除状态，1为主动删除，2为跟随上级栏目被动删除',
  `joinaid` int(10) DEFAULT '0' COMMENT '关联文档ID',
  `downcount` int(10) DEFAULT '0' COMMENT '下载次数',
  `appraise` int(10) DEFAULT '0' COMMENT '评价数',
  `collection` int(10) DEFAULT '0' COMMENT '收藏数',
  `htmlfilename` varchar(500) DEFAULT '' COMMENT '自定义文件名',
  `province_id` int(10) DEFAULT '0' COMMENT '省份',
  `city_id` int(10) DEFAULT '0' COMMENT '所在城市',
  `area_id` int(10) DEFAULT '0' COMMENT '所在区域',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `removal_time` int(11) DEFAULT '0' COMMENT '下架时间（用于自动下架，配合定时发布插件的下架功能）',
  `no_vip_pay` tinyint(3) DEFAULT '0' COMMENT 'restric_type = 2 时,会员专享,非会员可付费使用,0-关闭,1-开启',
  `editor_remote_img_local` tinyint(1) DEFAULT '1' COMMENT '远程图片本地化',
  `editor_img_clear_link` tinyint(1) DEFAULT '1' COMMENT '清除非本站链接',
  `editor_ai_create` tinyint(1) DEFAULT '0' COMMENT 'AI创作声明',
  `reason` text COMMENT '退回原因',
  `stock_code` varchar(100) DEFAULT NULL COMMENT '商品编码',
  PRIMARY KEY (`aid`),
  KEY `add_time` (`add_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8 COMMENT='文档主表';
CREATE TABLE `cms_archives_flag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `flag_name` varchar(255) NOT NULL DEFAULT '' COMMENT '文档属性名称',
  `flag_attr` varchar(10) NOT NULL DEFAULT '' COMMENT '属性值',
  `flag_fieldname` varchar(255) NOT NULL DEFAULT '' COMMENT '字段名',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态， 1---显示， 0---隐藏',
  `ifsystem` tinyint(1) DEFAULT '0' COMMENT '字段分类，1=系统(不可修改)，0=自定义',
  `sort_order` int(11) unsigned NOT NULL DEFAULT '0',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `flag_attr` (`flag_attr`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='文档属性配置表';
INSERT INTO `cms_archives_flag` VALUES ('1', '头条', 'h', 'is_head', '1', '1', '1', 'cn', '1606272350', '1606272350');
INSERT INTO `cms_archives_flag` VALUES ('2', '推荐', 'c', 'is_recom', '1', '1', '2', 'cn', '1606272350', '1606272350');
INSERT INTO `cms_archives_flag` VALUES ('3', '加推', 'a', 'is_special', '0', '1', '3', 'cn', '1606272350', '1606272350');
INSERT INTO `cms_archives_flag` VALUES ('4', '标粗', 'b', 'is_b', '0', '1', '4', 'cn', '1606272350', '1606272350');
INSERT INTO `cms_archives_flag` VALUES ('5', '有图', 'p', 'is_litpic', '1', '1', '5', 'cn', '1606272350', '1606272350');
INSERT INTO `cms_archives_flag` VALUES ('6', '外链', 'j', 'is_jump', '1', '1', '6', 'cn', '1606272350', '1606272350');
INSERT INTO `cms_archives_flag` VALUES ('7', '轮播', 's', 'is_slide', '0', '1', '7', 'cn', '1606272350', '1606272350');
INSERT INTO `cms_archives_flag` VALUES ('8', '滚动', 'r', 'is_roll', '0', '1', '8', 'cn', '1606272350', '1606272350');
INSERT INTO `cms_archives_flag` VALUES ('9', '热文', 'd', 'is_diyattr', '0', '1', '9', 'cn', '1606272350', '1606272350');
CREATE TABLE `cms_arcmulti` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `tagid` varchar(60) NOT NULL DEFAULT '' COMMENT '标签ID',
  `tagname` varchar(60) NOT NULL DEFAULT '' COMMENT '标签名',
  `innertext` text NOT NULL COMMENT '标签模板代码',
  `pagesize` int(10) NOT NULL DEFAULT '0' COMMENT '分页列表',
  `querysql` text NOT NULL COMMENT '完整SQL',
  `ordersql` varchar(200) DEFAULT '' COMMENT '排序SQL',
  `addfieldsSql` varchar(255) DEFAULT '' COMMENT '附加字段SQL',
  `addtableName` varchar(50) DEFAULT '' COMMENT '附加字段的数据表，不包含表前缀',
  `attstr` text COMMENT '属性字符串',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8 COMMENT='多页标记存储数据表';
CREATE TABLE `cms_arcrank` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT '权限ID',
  `rank` smallint(6) DEFAULT '0' COMMENT '权限值',
  `name` char(20) DEFAULT '' COMMENT '会员名称',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='文档阅读权限表';
INSERT INTO `cms_arcrank` VALUES ('1', '0', '开放浏览', 'cn', '0', '1552376880');
INSERT INTO `cms_arcrank` VALUES ('2', '-1', '待审核稿件', 'cn', '0', '1552376880');
CREATE TABLE `cms_arctype` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '栏目ID',
  `channeltype` int(10) DEFAULT '0' COMMENT '栏目顶级模型ID',
  `current_channel` int(10) DEFAULT '0' COMMENT '栏目当前模型ID',
  `parent_id` int(10) DEFAULT '0' COMMENT '栏目上级ID',
  `topid` int(10) DEFAULT '0' COMMENT '顶级栏目ID',
  `typename` varchar(500) DEFAULT '' COMMENT '栏目名称',
  `dirname` varchar(250) DEFAULT '' COMMENT '目录英文名',
  `dirpath` varchar(500) DEFAULT '' COMMENT '目录存放HTML路径',
  `diy_dirpath` varchar(500) DEFAULT '' COMMENT '列表静态文件存放规则',
  `rulelist` varchar(200) DEFAULT '' COMMENT '列表静态文件存放规则',
  `ruleview` varchar(200) DEFAULT '' COMMENT '文档静态文件存放规则',
  `englist_name` varchar(500) DEFAULT '' COMMENT '栏目英文名',
  `grade` tinyint(1) DEFAULT '0' COMMENT '栏目等级',
  `typelink` varchar(500) DEFAULT '' COMMENT '栏目链接',
  `litpic` varchar(500) DEFAULT '' COMMENT '栏目图片',
  `templist` varchar(200) DEFAULT '' COMMENT '列表模板文件名',
  `tempview` varchar(200) DEFAULT '' COMMENT '文档模板文件名',
  `seo_title` varchar(500) DEFAULT '' COMMENT 'SEO标题',
  `seo_keywords` varchar(500) DEFAULT '' COMMENT 'seo关键字',
  `seo_description` text COMMENT 'seo描述',
  `sort_order` int(10) DEFAULT '0' COMMENT '排序号',
  `is_hidden` tinyint(1) DEFAULT '0' COMMENT '是否隐藏栏目：0=显示，1=隐藏',
  `is_part` tinyint(1) DEFAULT '0' COMMENT '栏目属性：0=内容栏目，1=外部链接',
  `admin_id` int(10) DEFAULT '0' COMMENT '管理员ID',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '伪删除，1=是，0=否',
  `del_method` tinyint(1) DEFAULT '0' COMMENT '伪删除状态，1为主动删除，2为跟随上级栏目被动删除',
  `status` tinyint(1) DEFAULT '1' COMMENT '启用 (1=正常，0=屏蔽)',
  `is_release` tinyint(1) DEFAULT '0' COMMENT '栏目是否应用于会员投稿发布，1是，0否',
  `weapp_code` varchar(50) DEFAULT '' COMMENT '插件栏目唯一标识',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `target` tinyint(1) DEFAULT '0' COMMENT '新窗口打开',
  `nofollow` tinyint(1) DEFAULT '0' COMMENT '防抓取',
  `typearcrank` int(10) DEFAULT '0' COMMENT '阅读权限：0=开放浏览，-1=待审核稿件',
  `empty_logic` tinyint(1) DEFAULT '0' COMMENT '空内容逻辑',
  `page_limit` varchar(10) DEFAULT '0' COMMENT '限制页面 1-栏目页面 0-文档页面',
  `total_arc` int(10) DEFAULT '0' COMMENT '栏目下文档数量',
  PRIMARY KEY (`id`),
  UNIQUE KEY `dirname` (`dirname`,`lang`) USING BTREE,
  KEY `parent_id` (`channeltype`,`parent_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8 COMMENT='文档栏目表';
CREATE TABLE `cms_article_content` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) DEFAULT '0' COMMENT '文档ID',
  `content` longtext COMMENT '内容详情',
  `content_ey_m` longtext COMMENT '手机端内容详情',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `news_id` (`aid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COMMENT='文章附加表';
CREATE TABLE `cms_article_order` (
  `order_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章订单ID',
  `order_code` varchar(50) NOT NULL DEFAULT '' COMMENT '媒体订单编号',
  `users_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `order_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态：0未付款，1已付款',
  `order_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单应付总金额',
  `order_score` varchar(20) NOT NULL DEFAULT '' COMMENT '订单应付总积分',
  `pay_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
  `pay_name` varchar(20) NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `wechat_pay_type` varchar(20) NOT NULL DEFAULT '' COMMENT '微信支付时，标记使用的支付类型（扫码支付，微信内部，微信H5页面）',
  `pay_details` text COMMENT '支付时返回的数据，以serialize序列化后存入，用于后续查询。',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '视频文档ID',
  `product_name` varchar(100) DEFAULT '' COMMENT '视频文档名称',
  `product_litpic` varchar(500) DEFAULT '' COMMENT '视频文档封面图片',
  `lang` varchar(30) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned DEFAULT '0' COMMENT '下单时间',
  `update_time` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_code` (`order_code`) USING BTREE,
  KEY `users_id` (`users_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章订单表';
CREATE TABLE `cms_article_pay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) DEFAULT '0',
  `part_free` tinyint(1) DEFAULT '0' COMMENT '是否试看 0-否 1-是',
  `size` varchar(50) DEFAULT '1' COMMENT 'KB',
  `free_content` longtext COMMENT '试看内容',
  `add_time` int(11) DEFAULT '0',
  `update_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章付费预览表';
CREATE TABLE `cms_ask` (
  `ask_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `type_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题栏目ID',
  `users_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `ask_title` varchar(200) NOT NULL DEFAULT '' COMMENT '问题标题',
  `is_recom` tinyint(1) NOT NULL DEFAULT '0' COMMENT '问题是否推荐',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '问题状态：0未解决，1已解决，2已关闭',
  `click` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '浏览点击量',
  `replies` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题回复量',
  `content` text NOT NULL COMMENT '问题内容',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '问题网址',
  `users_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '问题发布时IP地址',
  `is_review` tinyint(1) NOT NULL DEFAULT '1' COMMENT '问题是否审核，1是，0否',
  `follow` tinyint(1) NOT NULL DEFAULT '0' COMMENT '关注问题则表示有回复时发送邮件通知到问题发布人',
  `solve_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '解决时间(这个问题存在最佳答案则表示已解决)',
  `bestanswer_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最佳答案',
  `sort_order` int(10) NOT NULL DEFAULT '100' COMMENT '排序号',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `money` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '悬赏金额',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '1-删除',
  PRIMARY KEY (`ask_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='问题表';
CREATE TABLE `cms_ask_answer` (
  `answer_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ask_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题ID',
  `is_bestanswer` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否最佳答案，0否，1是',
  `is_review` tinyint(1) NOT NULL DEFAULT '1' COMMENT '问题是否审核，1是，0否',
  `type_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题栏目ID',
  `users_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户名',
  `click_like` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞量',
  `users_ip` varchar(30) NOT NULL DEFAULT '' COMMENT '用户IP地址',
  `content` text NOT NULL COMMENT '内容',
  `ifcheck` tinyint(1) NOT NULL DEFAULT '1',
  `answer_pid` int(10) NOT NULL DEFAULT '0' COMMENT '子答案的父答案',
  `at_users_id` int(10) NOT NULL DEFAULT '0' COMMENT '被@的用户ID',
  `at_answer_id` int(10) NOT NULL DEFAULT '0' COMMENT '@答案ID',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-删除',
  PRIMARY KEY (`answer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='答案表';
CREATE TABLE `cms_ask_answer_like` (
  `like_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `ask_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '问题ID',
  `answer_id` int(10) NOT NULL DEFAULT '0' COMMENT '答案ID',
  `users_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `click_like` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点赞',
  `like_source` tinyint(1) unsigned NOT NULL DEFAULT '2' COMMENT '点赞来源，1=点赞提问(ask_id)，2=点赞评论(answer_id)，3=点赞回复(answer_id)，默认值为2，兼容以前的那些评论数据',
  `users_ip` varchar(30) NOT NULL DEFAULT '' COMMENT '用户IP地址',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`like_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='答案点赞表';
CREATE TABLE `cms_ask_score_level` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(55) DEFAULT '',
  `min` mediumint(8) DEFAULT '0',
  `max` mediumint(8) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='积分等级表';
CREATE TABLE `cms_ask_type` (
  `type_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '栏目自增',
  `type_name` varchar(100) NOT NULL DEFAULT '' COMMENT '栏目名称',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级ID',
  `seo_title` varchar(200) DEFAULT '' COMMENT 'SEO标题',
  `seo_keywords` varchar(200) DEFAULT '' COMMENT 'seo关键字',
  `seo_description` text COMMENT 'seo描述',
  `sort_order` int(10) unsigned NOT NULL DEFAULT '100' COMMENT '排序号',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='问题栏目分类表';
CREATE TABLE `cms_auth_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '' COMMENT '角色名',
  `pid` int(10) DEFAULT '0' COMMENT '父角色ID',
  `remark` text COMMENT '备注信息',
  `grade` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '级别',
  `language` text COMMENT '多语言权限',
  `online_update` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '在线升级',
  `switch_map` tinyint(1) DEFAULT '0' COMMENT '功能地图',
  `only_oneself` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '只看自己发布',
  `check_oneself` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '发布文档自动通过审核，1--是，0--否',
  `cud` varchar(255) DEFAULT '' COMMENT '增改删',
  `permission` longtext COMMENT '已允许的权限',
  `built_in` tinyint(1) DEFAULT '0' COMMENT '内置用户组，1表示内置',
  `sort_order` int(10) DEFAULT '0' COMMENT '排序号',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态(1=正常，0=屏蔽)',
  `admin_id` int(10) DEFAULT '0' COMMENT '操作管理员ID',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='管理员角色表';
CREATE TABLE `cms_channelfield` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '字段名称',
  `channel_id` int(10) NOT NULL DEFAULT '0' COMMENT '所属文档模型id',
  `title` varchar(250) NOT NULL DEFAULT '' COMMENT '字段标题',
  `dtype` varchar(32) NOT NULL DEFAULT '' COMMENT '字段类型',
  `define` text NOT NULL COMMENT '字段定义',
  `maxlength` int(10) NOT NULL DEFAULT '0' COMMENT '最大长度，文本数据必须填写，大于255为text类型',
  `dfvalue` text NOT NULL COMMENT '默认值',
  `dfvalue_unit` varchar(50) NOT NULL DEFAULT '' COMMENT '数值单位',
  `remark` varchar(256) NOT NULL DEFAULT '' COMMENT '提示说明',
  `is_screening` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否应用于条件筛选',
  `is_release` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否应用于会员投稿发布',
  `ifeditable` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否在编辑页显示',
  `ifrequire` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否必填',
  `ifsystem` tinyint(1) NOT NULL DEFAULT '0' COMMENT '字段分类，1=系统(不可修改)，0=自定义',
  `ifmain` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否主表字段',
  `ifcontrol` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态，控制该条数据是否允许被控制，1为不允许控制，0为允许控制',
  `sort_order` int(5) NOT NULL DEFAULT '100' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `set_type` tinyint(3) DEFAULT '0' COMMENT '区域选择时使用是否为三级联动,1-是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=551 DEFAULT CHARSET=utf8 COMMENT='自定义字段表';
INSERT INTO `cms_channelfield` VALUES ('1', 'add_time', '0', '新增时间', 'datetime', 'int(11)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533091575', '1533091575', '0');
INSERT INTO `cms_channelfield` VALUES ('2', 'update_time', '0', '更新时间', 'datetime', 'int(11)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533091601', '1533091601', '0');
INSERT INTO `cms_channelfield` VALUES ('3', 'aid', '0', '文档ID', 'int', 'int(11)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533091624', '1533091624', '0');
INSERT INTO `cms_channelfield` VALUES ('4', 'typeid', '0', '当前栏目ID', 'int', 'int(11)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533091930', '1533091930', '0');
INSERT INTO `cms_channelfield` VALUES ('5', 'channel', '0', '模型ID', 'int', 'int(11)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092214', '1533092214', '0');
INSERT INTO `cms_channelfield` VALUES ('6', 'is_b', '0', '是否加粗', 'switch', 'tinyint(1)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092246', '1533092246', '0');
INSERT INTO `cms_channelfield` VALUES ('7', 'title', '0', '文档标题', 'text', 'varchar(250)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092381', '1533092381', '0');
INSERT INTO `cms_channelfield` VALUES ('8', 'litpic', '0', '缩略图', 'img', 'varchar(250)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092398', '1533092398', '0');
INSERT INTO `cms_channelfield` VALUES ('9', 'is_head', '0', '是否头条', 'switch', 'tinyint(1)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092420', '1533092420', '0');
INSERT INTO `cms_channelfield` VALUES ('10', 'is_special', '0', '是否特荐', 'switch', 'tinyint(1)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092439', '1533092439', '0');
INSERT INTO `cms_channelfield` VALUES ('11', 'is_top', '0', '是否置顶', 'switch', 'tinyint(1)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092454', '1533092454', '0');
INSERT INTO `cms_channelfield` VALUES ('12', 'is_recom', '0', '是否推荐', 'switch', 'tinyint(1)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092468', '1533092468', '0');
INSERT INTO `cms_channelfield` VALUES ('13', 'is_jump', '0', '是否跳转', 'switch', 'tinyint(1)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092484', '1533092484', '0');
INSERT INTO `cms_channelfield` VALUES ('14', 'author', '0', '编辑者', 'text', 'varchar(250)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092498', '1533092498', '0');
INSERT INTO `cms_channelfield` VALUES ('15', 'click', '0', '浏览量', 'int', 'int(11)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092512', '1533092512', '0');
INSERT INTO `cms_channelfield` VALUES ('16', 'arcrank', '0', '阅读权限', 'select', 'tinyint(1)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092534', '1533092534', '0');
INSERT INTO `cms_channelfield` VALUES ('17', 'jumplinks', '0', '跳转链接', 'text', 'varchar(250)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092553', '1533092553', '0');
INSERT INTO `cms_channelfield` VALUES ('18', 'ismake', '0', '是否静态页面', 'switch', 'tinyint(1)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092698', '1533092698', '0');
INSERT INTO `cms_channelfield` VALUES ('19', 'seo_title', '0', 'SEO标题', 'text', 'varchar(250)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092713', '1533092713', '0');
INSERT INTO `cms_channelfield` VALUES ('20', 'seo_keywords', '0', 'SEO关键词', 'text', 'varchar(250)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092725', '1533092725', '0');
INSERT INTO `cms_channelfield` VALUES ('21', 'seo_description', '0', 'SEO描述', 'text', 'varchar(250)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092739', '1533092739', '0');
INSERT INTO `cms_channelfield` VALUES ('22', 'status', '0', '状态', 'switch', 'tinyint(1)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092753', '1533092753', '0');
INSERT INTO `cms_channelfield` VALUES ('23', 'sort_order', '0', '排序号', 'int', 'int(11)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092766', '1533092766', '0');
INSERT INTO `cms_channelfield` VALUES ('24', 'content', '2', '内容详情', 'htmltext', 'longtext', '250', '', '', '', '0', '1', '1', '0', '1', '0', '0', '100', '1', '1533359739', '1533359739', '0');
INSERT INTO `cms_channelfield` VALUES ('25', 'content', '3', '内容详情', 'htmltext', 'longtext', '250', '', '', '', '0', '1', '1', '0', '1', '0', '0', '100', '1', '1533359588', '1533359588', '0');
INSERT INTO `cms_channelfield` VALUES ('26', 'content', '4', '内容详情', 'htmltext', 'longtext', '250', '', '', '', '0', '1', '1', '0', '1', '0', '0', '100', '1', '1533359752', '1533359752', '0');
INSERT INTO `cms_channelfield` VALUES ('27', 'content', '6', '内容详情', 'htmltext', 'longtext', '250', '', '', '', '0', '1', '1', '0', '1', '0', '0', '100', '1', '1533464715', '1533464715', '0');
INSERT INTO `cms_channelfield` VALUES ('29', 'content', '1', '内容详情', 'htmltext', 'longtext', '250', '', '', '', '0', '1', '1', '0', '1', '0', '0', '100', '1', '1533464713', '1533464713', '0');
INSERT INTO `cms_channelfield` VALUES ('30', 'update_time', '-99', '更新时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('31', 'add_time', '-99', '新增时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('32', 'status', '-99', '启用 (1=正常，0=屏蔽)', 'switch', 'tinyint(1)', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('33', 'is_part', '-99', '栏目属性：0=内容栏目，1=外部链接', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('34', 'is_hidden', '-99', '是否隐藏栏目：0=显示，1=隐藏', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('35', 'sort_order', '-99', '排序号', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('36', 'seo_description', '-99', 'seo描述', 'multitext', 'text', '0', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('37', 'seo_keywords', '-99', 'seo关键字', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('38', 'seo_title', '-99', 'SEO标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('39', 'tempview', '-99', '文档模板文件名', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('40', 'templist', '-99', '列表模板文件名', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('41', 'litpic', '-99', '栏目图片', 'img', 'varchar(250)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('42', 'typelink', '-99', '栏目链接', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('43', 'grade', '-99', '栏目等级', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('44', 'englist_name', '-99', '栏目英文名', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('45', 'dirpath', '-99', '目录存放HTML路径', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('46', 'dirname', '-99', '目录英文名', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('47', 'typename', '-99', '栏目名称', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('48', 'parent_id', '-99', '栏目上级ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('49', 'current_channel', '-99', '栏目当前模型ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('50', 'channeltype', '-99', '栏目顶级模型ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('51', 'id', '-99', '栏目ID', 'int', 'int(10)', '10', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('52', 'del_method', '-99', '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1547890773', '1547890773', '0');
INSERT INTO `cms_channelfield` VALUES ('53', 'is_del', '0', '是否伪删除', 'switch', 'tinyint(1)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1547890773', '1547890773', '0');
INSERT INTO `cms_channelfield` VALUES ('54', 'del_method', '0', '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1547890773', '1547890773', '0');
INSERT INTO `cms_channelfield` VALUES ('55', 'prom_type', '0', '产品类型：0普通产品，1虚拟产品', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('56', 'users_price', '0', '价格', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '0', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('57', 'prom_type', '2', '产品类型：0普通产品，1虚拟产品', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('58', 'users_price', '2', '价格', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '0', '100', '1', '1557042574', '1563498415', '0');
INSERT INTO `cms_channelfield` VALUES ('59', 'update_time', '2', '更新时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('60', 'add_time', '2', '新增时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('61', 'del_method', '2', '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('62', 'is_del', '2', '伪删除，1=是，0=否', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('63', 'admin_id', '2', '管理员ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('64', 'lang', '2', '语言标识', 'text', 'varchar(50)', '50', 'cn', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('65', 'sort_order', '2', '排序号', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('66', 'status', '2', '状态(0=屏蔽，1=正常)', 'switch', 'tinyint(1)', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('67', 'tempview', '2', '文档模板文件名', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('68', 'seo_description', '2', 'SEO描述', 'multitext', 'text', '0', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('69', 'seo_keywords', '2', 'SEO关键词', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('70', 'seo_title', '2', 'SEO标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('71', 'ismake', '2', '是否静态页面（0=动态，1=静态）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('72', 'jumplinks', '2', '外链跳转', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('73', 'arcrank', '2', '阅读权限：0=开放浏览，-1=待审核稿件', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('74', 'click', '2', '浏览量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('75', 'author', '2', '作者', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('76', 'is_litpic', '2', '图片（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('77', 'is_jump', '2', '跳转链接（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('78', 'is_recom', '2', '推荐（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('79', 'is_top', '2', '置顶（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('80', 'is_special', '2', '特荐（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('81', 'is_head', '2', '头条（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('82', 'litpic', '2', '缩略图', 'img', 'varchar(250)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('83', 'title', '2', '标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('84', 'is_b', '2', '加粗', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('85', 'channel', '2', '模型ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('86', 'typeid', '2', '当前栏目', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('87', 'aid', '2', 'aid', 'int', 'int(10)', '10', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563518642', '1563518642', '0');
INSERT INTO `cms_channelfield` VALUES ('88', 'fxrq', '2', '发行日期', 'radio', 'enum(\'2019年\',\'2018年\',\'2017年\')', '0', '2019年,2018年,2017年', '', '', '1', '0', '1', '0', '0', '0', '0', '100', '1', '1563518738', '1563518738', '0');
INSERT INTO `cms_channelfield` VALUES ('89', 'update_time', '9', '更新时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('90', 'add_time', '9', '新增时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('91', 'del_method', '9', '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('92', 'is_del', '9', '伪删除，1=是，0=否', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('93', 'admin_id', '9', '管理员ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('94', 'lang', '9', '语言标识', 'text', 'varchar(50)', '50', 'cn', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('95', 'sort_order', '9', '排序号', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('96', 'status', '9', '状态(0=屏蔽，1=正常)', 'switch', 'tinyint(1)', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('97', 'tempview', '9', '文档模板文件名', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('98', 'prom_type', '9', '产品类型：0普通产品，1虚拟产品', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('99', 'users_price', '9', '价格', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('100', 'seo_description', '9', 'SEO描述', 'multitext', 'text', '0', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('101', 'seo_keywords', '9', 'SEO关键词', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('102', 'seo_title', '9', 'SEO标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('103', 'ismake', '9', '是否静态页面（0=动态，1=静态）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('104', 'jumplinks', '9', '外链跳转', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('105', 'arcrank', '9', '阅读权限：0=开放浏览，-1=待审核稿件', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('106', 'click', '9', '浏览量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('107', 'author', '9', '作者', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '0', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('108', 'is_litpic', '9', '图片（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('109', 'is_jump', '9', '跳转链接（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('110', 'is_recom', '9', '推荐（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('111', 'is_top', '9', '置顶（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('112', 'is_special', '9', '特荐（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('113', 'is_head', '9', '头条（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('114', 'litpic', '9', '缩略图', 'img', 'varchar(250)', '250', '', '', '', '0', '0', '0', '0', '1', '1', '0', '100', '1', '1563526560', '1563526567', '0');
INSERT INTO `cms_channelfield` VALUES ('115', 'title', '9', '标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('116', 'is_b', '9', '加粗', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('117', 'channel', '9', '模型ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('118', 'typeid', '9', '当前栏目', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('119', 'aid', '9', 'aid', 'int', 'int(10)', '10', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1563526560', '1563526560', '0');
INSERT INTO `cms_channelfield` VALUES ('120', 'gzdd', '9', '工作地点', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '0', '0', '0', '1', '1', '1563526665', '1563528016', '0');
INSERT INTO `cms_channelfield` VALUES ('121', 'xlyq', '9', '学历要求', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '0', '0', '0', '2', '1', '1563526681', '1563528016', '0');
INSERT INTO `cms_channelfield` VALUES ('122', 'xzdy', '9', '薪资待遇', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '0', '0', '0', '3', '1', '1563526694', '1563528018', '0');
INSERT INTO `cms_channelfield` VALUES ('123', 'gzxz', '9', '工作性质', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '0', '0', '0', '4', '1', '1563526705', '1563528018', '0');
INSERT INTO `cms_channelfield` VALUES ('124', 'gznx', '9', '工作年限', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '0', '0', '0', '5', '1', '1563526716', '1563528019', '0');
INSERT INTO `cms_channelfield` VALUES ('125', 'zprs', '9', '招聘人数', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '0', '0', '0', '6', '1', '1563526727', '1563528021', '0');
INSERT INTO `cms_channelfield` VALUES ('126', 'nnxq', '9', '内容详情', 'htmltext', 'longtext', '0', '', '', '', '0', '0', '1', '0', '0', '0', '0', '7', '1', '1563526769', '1563528023', '0');
INSERT INTO `cms_channelfield` VALUES ('127', 'users_id', '0', '会员ID', 'int', 'int(11)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('128', 'arc_level_id', '0', '文档会员权限ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('129', 'arc_level_id', '4', '文档会员权限ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('130', 'arc_level_id', '2', '文档会员权限ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1565662106', '1565662106', '0');
INSERT INTO `cms_channelfield` VALUES ('131', 'users_id', '2', '会员ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1565662106', '1565662106', '0');
INSERT INTO `cms_channelfield` VALUES ('132', 'jiawei', '2', '价位区段', 'radio', 'enum(\'0-1000\',\'1000-1699\',\'1700-2799\',\'2800-3500\',\'3500-10000\')', '0', '0-1000,1000-1699,1700-2799,2800-3500,3500-10000', '', '', '1', '0', '1', '0', '0', '0', '0', '100', '1', '1565662216', '1565663341', '0');
INSERT INTO `cms_channelfield` VALUES ('133', 'yanse', '2', '机身颜色', 'radio', 'enum(\'银色\',\'绿色\',\'黑色\',\'灰色\')', '0', '银色,绿色,黑色,灰色', '', '', '1', '0', '1', '0', '0', '0', '0', '100', '1', '1565662279', '1565662740', '0');
INSERT INTO `cms_channelfield` VALUES ('136', 'weapp_code', '-99', '插件栏目唯一标识', 'text', 'varchar(200)', '200', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('137', 'is_release', '-99', '栏目是否应用于会员投稿发布，1是，0否', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('138', 'old_price', '0', '产品旧价', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('139', 'stock_count', '0', '商品库存量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('140', 'stock_show', '0', '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('141', 'joinaid', '0', '关联文档ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('142', 'downcount', '0', '下载次数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('143', 'downcount', '4', '下载次数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('144', 'update_time', '1', '更新时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('145', 'add_time', '1', '新增时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('146', 'downcount', '1', '下载次数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('147', 'joinaid', '1', '关联文档ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('148', 'del_method', '1', '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('149', 'is_del', '1', '伪删除，1=是，0=否', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('150', 'arc_level_id', '1', '文档会员权限ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('151', 'users_id', '1', '会员ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('152', 'admin_id', '1', '管理员ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('153', 'lang', '1', '语言标识', 'text', 'varchar(50)', '50', 'cn', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('154', 'sort_order', '1', '排序号', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('155', 'status', '1', '状态(0=屏蔽，1=正常)', 'switch', 'tinyint(1)', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('156', 'tempview', '1', '文档模板文件名', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('157', 'prom_type', '1', '产品类型：0普通产品，1虚拟产品', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('158', 'stock_show', '1', '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1) unsigned', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('159', 'stock_count', '1', '商品库存量', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('160', 'old_price', '1', '产品旧价', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('161', 'users_price', '1', '会员价', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('162', 'seo_description', '1', 'SEO描述', 'multitext', 'text', '0', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('163', 'seo_keywords', '1', 'SEO关键词', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('164', 'seo_title', '1', 'SEO标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('165', 'ismake', '1', '是否静态页面（0=动态，1=静态）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('166', 'jumplinks', '1', '外链跳转', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('167', 'arcrank', '1', '阅读权限：0=开放浏览，-1=待审核稿件', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('168', 'click', '1', '浏览量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('169', 'author', '1', '作者', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('170', 'is_litpic', '1', '图片（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('171', 'is_jump', '1', '跳转链接（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('172', 'is_recom', '1', '推荐（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('173', 'is_top', '1', '置顶（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('174', 'is_special', '1', '特荐（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('175', 'is_head', '1', '头条（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('176', 'litpic', '1', '缩略图', 'img', 'varchar(250)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('177', 'title', '1', '标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('178', 'is_b', '1', '加粗', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('179', 'channel', '1', '模型ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('180', 'typeid', '1', '当前栏目', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('181', 'aid', '1', 'aid', 'int', 'int(10)', '10', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233787', '1574233787', '0');
INSERT INTO `cms_channelfield` VALUES ('182', 'downcount', '2', '下载次数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233793', '1574233793', '0');
INSERT INTO `cms_channelfield` VALUES ('183', 'joinaid', '2', '关联文档ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233793', '1574233793', '0');
INSERT INTO `cms_channelfield` VALUES ('184', 'stock_show', '2', '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1) unsigned', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233793', '1574233793', '0');
INSERT INTO `cms_channelfield` VALUES ('185', 'stock_count', '2', '商品库存量', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233793', '1574233793', '0');
INSERT INTO `cms_channelfield` VALUES ('186', 'old_price', '2', '产品旧价', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233793', '1574233793', '0');
INSERT INTO `cms_channelfield` VALUES ('187', 'update_time', '3', '更新时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('188', 'add_time', '3', '新增时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('189', 'downcount', '3', '下载次数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('190', 'joinaid', '3', '关联文档ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('191', 'del_method', '3', '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('192', 'is_del', '3', '伪删除，1=是，0=否', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('193', 'arc_level_id', '3', '文档会员权限ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('194', 'users_id', '3', '会员ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('195', 'admin_id', '3', '管理员ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('196', 'lang', '3', '语言标识', 'text', 'varchar(50)', '50', 'cn', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('197', 'sort_order', '3', '排序号', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('198', 'status', '3', '状态(0=屏蔽，1=正常)', 'switch', 'tinyint(1)', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('199', 'tempview', '3', '文档模板文件名', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('200', 'prom_type', '3', '产品类型：0普通产品，1虚拟产品', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('201', 'stock_show', '3', '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1) unsigned', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('202', 'stock_count', '3', '商品库存量', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('203', 'old_price', '3', '产品旧价', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('204', 'users_price', '3', '会员价', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('205', 'seo_description', '3', 'SEO描述', 'multitext', 'text', '0', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('206', 'seo_keywords', '3', 'SEO关键词', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('207', 'seo_title', '3', 'SEO标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('208', 'ismake', '3', '是否静态页面（0=动态，1=静态）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('209', 'jumplinks', '3', '外链跳转', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('210', 'arcrank', '3', '阅读权限：0=开放浏览，-1=待审核稿件', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('211', 'click', '3', '浏览量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('212', 'author', '3', '作者', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('213', 'is_litpic', '3', '图片（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('214', 'is_jump', '3', '跳转链接（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('215', 'is_recom', '3', '推荐（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('216', 'is_top', '3', '置顶（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('217', 'is_special', '3', '特荐（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('218', 'is_head', '3', '头条（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('219', 'litpic', '3', '缩略图', 'img', 'varchar(250)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('220', 'title', '3', '标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('221', 'is_b', '3', '加粗', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('222', 'channel', '3', '模型ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('223', 'typeid', '3', '当前栏目', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('224', 'aid', '3', 'aid', 'int', 'int(10)', '10', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('225', 'update_time', '4', '更新时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('226', 'add_time', '4', '新增时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('227', 'joinaid', '4', '关联文档ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('228', 'del_method', '4', '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('229', 'is_del', '4', '伪删除，1=是，0=否', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('230', 'users_id', '4', '会员ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('231', 'admin_id', '4', '管理员ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('232', 'lang', '4', '语言标识', 'text', 'varchar(50)', '50', 'cn', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('233', 'sort_order', '4', '排序号', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('234', 'status', '4', '状态(0=屏蔽，1=正常)', 'switch', 'tinyint(1)', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('235', 'tempview', '4', '文档模板文件名', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('236', 'prom_type', '4', '产品类型：0普通产品，1虚拟产品', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('237', 'stock_show', '4', '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1) unsigned', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('238', 'stock_count', '4', '商品库存量', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('239', 'old_price', '4', '产品旧价', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('240', 'users_price', '4', '会员价', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('241', 'seo_description', '4', 'SEO描述', 'multitext', 'text', '0', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('242', 'seo_keywords', '4', 'SEO关键词', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('243', 'seo_title', '4', 'SEO标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('244', 'ismake', '4', '是否静态页面（0=动态，1=静态）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('245', 'jumplinks', '4', '外链跳转', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('246', 'arcrank', '4', '阅读权限：0=开放浏览，-1=待审核稿件', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('247', 'click', '4', '浏览量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('248', 'author', '4', '作者', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('249', 'is_litpic', '4', '图片（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('250', 'is_jump', '4', '跳转链接（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('251', 'is_recom', '4', '推荐（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('252', 'is_top', '4', '置顶（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('253', 'is_special', '4', '特荐（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('254', 'is_head', '4', '头条（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('255', 'litpic', '4', '缩略图', 'img', 'varchar(250)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('256', 'title', '4', '标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('257', 'is_b', '4', '加粗', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('258', 'channel', '4', '模型ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('259', 'typeid', '4', '当前栏目', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('260', 'aid', '4', 'aid', 'int', 'int(10)', '10', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233799', '1574233799', '0');
INSERT INTO `cms_channelfield` VALUES ('261', 'update_time', '6', '更新时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('262', 'add_time', '6', '新增时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('263', 'downcount', '6', '下载次数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('264', 'joinaid', '6', '关联文档ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('265', 'del_method', '6', '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('266', 'is_del', '6', '伪删除，1=是，0=否', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('267', 'arc_level_id', '6', '文档会员权限ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('268', 'users_id', '6', '会员ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('269', 'admin_id', '6', '管理员ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('270', 'lang', '6', '语言标识', 'text', 'varchar(50)', '50', 'cn', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('271', 'sort_order', '6', '排序号', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('272', 'status', '6', '状态(0=屏蔽，1=正常)', 'switch', 'tinyint(1)', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('273', 'tempview', '6', '文档模板文件名', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('274', 'prom_type', '6', '产品类型：0普通产品，1虚拟产品', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('275', 'stock_show', '6', '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1) unsigned', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('276', 'stock_count', '6', '商品库存量', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('277', 'old_price', '6', '产品旧价', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('278', 'users_price', '6', '会员价', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('279', 'seo_description', '6', 'SEO描述', 'multitext', 'text', '0', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('280', 'seo_keywords', '6', 'SEO关键词', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('281', 'seo_title', '6', 'SEO标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('282', 'ismake', '6', '是否静态页面（0=动态，1=静态）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('283', 'jumplinks', '6', '外链跳转', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('284', 'arcrank', '6', '阅读权限：0=开放浏览，-1=待审核稿件', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('285', 'click', '6', '浏览量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('286', 'author', '6', '作者', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('287', 'is_litpic', '6', '图片（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('288', 'is_jump', '6', '跳转链接（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('289', 'is_recom', '6', '推荐（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('290', 'is_top', '6', '置顶（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('291', 'is_special', '6', '特荐（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('292', 'is_head', '6', '头条（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('293', 'litpic', '6', '缩略图', 'img', 'varchar(250)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('294', 'title', '6', '标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('295', 'is_b', '6', '加粗', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('296', 'channel', '6', '模型ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('297', 'typeid', '6', '当前栏目', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('298', 'aid', '6', 'aid', 'int', 'int(10)', '10', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233802', '1574233802', '0');
INSERT INTO `cms_channelfield` VALUES ('299', 'downcount', '9', '下载次数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233808', '1574233808', '0');
INSERT INTO `cms_channelfield` VALUES ('300', 'joinaid', '9', '关联文档ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233808', '1574233808', '0');
INSERT INTO `cms_channelfield` VALUES ('301', 'arc_level_id', '9', '文档会员权限ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233808', '1574233808', '0');
INSERT INTO `cms_channelfield` VALUES ('302', 'users_id', '9', '会员ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233808', '1574233808', '0');
INSERT INTO `cms_channelfield` VALUES ('303', 'stock_show', '9', '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1) unsigned', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233808', '1574233808', '0');
INSERT INTO `cms_channelfield` VALUES ('304', 'stock_count', '9', '商品库存量', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233808', '1574233808', '0');
INSERT INTO `cms_channelfield` VALUES ('305', 'old_price', '9', '产品旧价', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233808', '1574233808', '0');
INSERT INTO `cms_channelfield` VALUES ('306', 'htmlfilename', '0', '自定义文件名', 'text', 'varchar(50)', '50', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('307', 'htmlfilename', '1', '自定义文件名', 'text', 'varchar(50)', '50', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('308', 'htmlfilename', '2', '自定义文件名', 'text', 'varchar(50)', '50', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('309', 'htmlfilename', '3', '自定义文件名', 'text', 'varchar(50)', '50', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('310', 'htmlfilename', '4', '自定义文件名', 'text', 'varchar(50)', '50', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('311', 'htmlfilename', '6', '自定义文件名', 'text', 'varchar(50)', '50', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('312', 'attrlist_id', '0', '参数列表ID', 'int', 'int(10) unsigned', '10', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533091930', '1533091930', '0');
INSERT INTO `cms_channelfield` VALUES ('313', 'sales_num', '0', '销售量', 'int', 'int(10) unsigned', '10', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533091930', '1533091930', '0');
INSERT INTO `cms_channelfield` VALUES ('314', 'update_time', '5', '更新时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('315', 'add_time', '5', '新增时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('316', 'htmlfilename', '5', '自定义文件名', 'text', 'varchar(50)', '50', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('317', 'downcount', '5', '下载次数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('318', 'joinaid', '5', '关联文档ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('319', 'del_method', '5', '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('320', 'is_del', '5', '伪删除，1=是，0=否', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('321', 'arc_level_id', '5', '文档会员权限ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('322', 'users_id', '5', '会员ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('323', 'admin_id', '5', '管理员ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('324', 'lang', '5', '语言标识', 'text', 'varchar(50)', '50', 'cn', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('325', 'sort_order', '5', '排序号', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('326', 'status', '5', '状态(0=屏蔽，1=正常)', 'switch', 'tinyint(1)', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('327', 'tempview', '5', '文档模板文件名', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('328', 'prom_type', '5', '产品类型：0=普通产品，1=虚拟(默认手动发货)，2=虚拟(网盘', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('329', 'stock_show', '5', '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1) unsigned', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('330', 'stock_count', '5', '商品库存量', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('331', 'sales_num', '5', '销售量', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('332', 'old_price', '5', '产品旧价', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('333', 'users_price', '5', '会员价', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('334', 'attrlist_id', '5', '参数列表ID', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('335', 'seo_description', '5', 'SEO描述', 'multitext', 'text', '0', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('336', 'seo_keywords', '5', 'SEO关键词', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('337', 'seo_title', '5', 'SEO标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('338', 'ismake', '5', '是否静态页面（0=动态，1=静态）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('339', 'jumplinks', '5', '外链跳转', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('340', 'arcrank', '5', '阅读权限：0=开放浏览，-1=待审核稿件', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('341', 'click', '5', '浏览量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('342', 'author', '5', '作者', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('343', 'is_litpic', '5', '图片（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('344', 'is_jump', '5', '跳转链接（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('345', 'is_recom', '5', '推荐（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('346', 'is_top', '5', '置顶（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('347', 'is_special', '5', '特荐（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('348', 'is_head', '5', '头条（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('349', 'litpic', '5', '缩略图', 'img', 'varchar(250)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('350', 'title', '5', '标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('351', 'is_b', '5', '加粗', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('352', 'channel', '5', '模型ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('353', 'typeid', '5', '当前栏目', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('354', 'aid', '5', 'aid', 'int', 'int(10)', '10', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('355', 'content', '5', '内容详情', 'htmltext', 'longtext', '0', '', '', '', '0', '1', '1', '0', '1', '0', '0', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('356', 'courseware', '5', '课件地址', 'text', 'varchar(200)', '200', '', '', '', '0', '1', '0', '0', '1', '0', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('357', 'courseware_free', '5', '课件收费', 'select', 'enum(\'免费\',\'收费\')', '0', '免费,收费', '', '', '0', '1', '0', '0', '1', '0', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('358', 'total_duration', '5', '视频总时长', 'int', 'int(10)', '10', '0', '', '', '0', '1', '0', '0', '1', '0', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('359', 'total_video', '5', '视频数', 'int', 'int(10)', '10', '0', '', '', '0', '1', '0', '0', '1', '0', '1', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('404', 'topid', '-99', '顶级栏目ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1557042574', '1557042574', '0');
INSERT INTO `cms_channelfield` VALUES ('361', 'update_time', '7', '更新时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('362', 'add_time', '7', '新增时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('363', 'htmlfilename', '7', '自定义文件名', 'text', 'varchar(50)', '50', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('364', 'downcount', '7', '下载次数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('365', 'joinaid', '7', '关联文档ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('366', 'del_method', '7', '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('367', 'is_del', '7', '伪删除，1=是，0=否', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('368', 'arc_level_id', '7', '文档会员权限ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('369', 'users_id', '7', '会员ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('370', 'admin_id', '7', '管理员ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('371', 'lang', '7', '语言标识', 'text', 'varchar(50)', '50', 'cn', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('372', 'sort_order', '7', '排序号', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('373', 'status', '7', '状态(0=屏蔽，1=正常)', 'switch', 'tinyint(1)', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('374', 'tempview', '7', '文档模板文件名', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('375', 'prom_type', '7', '产品类型：0=普通产品，1=虚拟(默认手动发货)，2=虚拟(网盘', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('376', 'stock_show', '7', '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1) unsigned', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('377', 'stock_count', '7', '商品库存量', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('378', 'sales_num', '7', '销售量', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('379', 'old_price', '7', '产品旧价', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('380', 'users_free', '7', '是否会员免费，默认0不免费，1为免费', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('381', 'users_price', '7', '会员价', 'decimal', 'decimal(10,2)', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('382', 'attrlist_id', '7', '参数列表ID', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('383', 'seo_description', '7', 'SEO描述', 'multitext', 'text', '0', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('384', 'seo_keywords', '7', 'SEO关键词', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('385', 'seo_title', '7', 'SEO标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('386', 'ismake', '7', '是否静态页面（0=动态，1=静态）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('387', 'jumplinks', '7', '外链跳转', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('388', 'arcrank', '7', '阅读权限：0=开放浏览，-1=待审核稿件', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('389', 'click', '7', '浏览量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('390', 'author', '7', '作者', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('391', 'is_litpic', '7', '图片（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('392', 'is_jump', '7', '跳转链接（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('393', 'is_recom', '7', '推荐（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('394', 'is_top', '7', '置顶（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('395', 'is_special', '7', '特荐（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('396', 'is_head', '7', '头条（0=否，1=是）', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('397', 'litpic', '7', '缩略图', 'img', 'varchar(250)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('398', 'title', '7', '标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('399', 'is_b', '7', '加粗', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('400', 'channel', '7', '模型ID', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('401', 'typeid', '7', '当前栏目', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('402', 'aid', '7', 'aid', 'int', 'int(10)', '10', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('403', 'content', '7', '内容详情', 'htmltext', 'longtext', '0', '', '', '', '0', '1', '1', '0', '1', '0', '0', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('405', 'is_slide', '0', '是否幻灯', 'switch', 'tinyint(1)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092420', '1533092420', '0');
INSERT INTO `cms_channelfield` VALUES ('406', 'is_roll', '0', '是否幻灯', 'switch', 'tinyint(1)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092420', '1533092420', '0');
INSERT INTO `cms_channelfield` VALUES ('407', 'is_diyattr', '0', '是否自定义', 'switch', 'tinyint(1)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533092420', '1533092420', '0');
INSERT INTO `cms_channelfield` VALUES ('408', 'restric_type', '0', '限制模式，0=免费，1=付费，2=会员专享，3=会员付费', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1616293251', '1616293251', '0');
INSERT INTO `cms_channelfield` VALUES ('409', 'diy_dirpath', '-99', '自定义HTML保存路径', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('410', 'rulelist', '-99', '列表静态文件存放规则', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('411', 'ruleview', '-99', '文档静态文件存放规则', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('412', 'subtitle', '0', '副标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1636338535', '1636338535', '0');
INSERT INTO `cms_channelfield` VALUES ('413', 'origin', '0', '来源', 'text', 'varchar(30)', '30', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1636338535', '1636338535', '0');
INSERT INTO `cms_channelfield` VALUES ('414', 'stypeid', '0', '副栏目', 'text', 'varchar(90)', '90', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1636338535', '1636338535', '0');
INSERT INTO `cms_channelfield` VALUES ('415', 'area_id', '1', '所在区域', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949813', '1641949813', '0');
INSERT INTO `cms_channelfield` VALUES ('416', 'city_id', '1', '所在城市', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949813', '1641949813', '0');
INSERT INTO `cms_channelfield` VALUES ('417', 'province_id', '1', '省份', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949813', '1641949813', '0');
INSERT INTO `cms_channelfield` VALUES ('418', 'collection', '1', '收藏数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949813', '1641949813', '0');
INSERT INTO `cms_channelfield` VALUES ('419', 'appraise', '1', '评价数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949813', '1641949813', '0');
INSERT INTO `cms_channelfield` VALUES ('420', 'restric_type', '1', '限制模式，0=免费，1=付费，2=会员专享，3=会员付费', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949813', '1641949813', '0');
INSERT INTO `cms_channelfield` VALUES ('421', 'sales_num', '1', '销售量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949813', '1641949813', '0');
INSERT INTO `cms_channelfield` VALUES ('422', 'users_free', '1', '是否会员免费，默认0不免费，1为免费', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949813', '1641949813', '0');
INSERT INTO `cms_channelfield` VALUES ('423', 'attrlist_id', '1', '参数列表ID', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949813', '1641949813', '0');
INSERT INTO `cms_channelfield` VALUES ('424', 'origin', '1', '来源', 'text', 'varchar(30)', '30', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949813', '1641949813', '0');
INSERT INTO `cms_channelfield` VALUES ('425', 'is_diyattr', '1', '自定义（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949813', '1641949813', '0');
INSERT INTO `cms_channelfield` VALUES ('426', 'is_slide', '1', '幻灯（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949813', '1641949813', '0');
INSERT INTO `cms_channelfield` VALUES ('427', 'is_roll', '1', '滚动（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949813', '1641949813', '0');
INSERT INTO `cms_channelfield` VALUES ('428', 'subtitle', '1', '副标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949813', '1641949813', '0');
INSERT INTO `cms_channelfield` VALUES ('429', 'stypeid', '1', '副栏目ID集合', 'text', 'varchar(90)', '90', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949813', '1641949813', '0');
INSERT INTO `cms_channelfield` VALUES ('430', 'area_id', '2', '所在区域', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949815', '1641949815', '0');
INSERT INTO `cms_channelfield` VALUES ('431', 'city_id', '2', '所在城市', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949815', '1641949815', '0');
INSERT INTO `cms_channelfield` VALUES ('432', 'province_id', '2', '省份', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949815', '1641949815', '0');
INSERT INTO `cms_channelfield` VALUES ('433', 'collection', '2', '收藏数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949815', '1641949815', '0');
INSERT INTO `cms_channelfield` VALUES ('434', 'appraise', '2', '评价数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949815', '1641949815', '0');
INSERT INTO `cms_channelfield` VALUES ('435', 'restric_type', '2', '限制模式，0=免费，1=付费，2=会员专享，3=会员付费', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949815', '1641949815', '0');
INSERT INTO `cms_channelfield` VALUES ('436', 'sales_num', '2', '销售量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949815', '1641949815', '0');
INSERT INTO `cms_channelfield` VALUES ('437', 'users_free', '2', '是否会员免费，默认0不免费，1为免费', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949815', '1641949815', '0');
INSERT INTO `cms_channelfield` VALUES ('438', 'attrlist_id', '2', '参数列表ID', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949815', '1641949815', '0');
INSERT INTO `cms_channelfield` VALUES ('439', 'origin', '2', '来源', 'text', 'varchar(30)', '30', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949815', '1641949815', '0');
INSERT INTO `cms_channelfield` VALUES ('440', 'is_diyattr', '2', '自定义（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949815', '1641949815', '0');
INSERT INTO `cms_channelfield` VALUES ('441', 'is_slide', '2', '幻灯（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949815', '1641949815', '0');
INSERT INTO `cms_channelfield` VALUES ('442', 'is_roll', '2', '滚动（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949815', '1641949815', '0');
INSERT INTO `cms_channelfield` VALUES ('443', 'subtitle', '2', '副标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949815', '1641949815', '0');
INSERT INTO `cms_channelfield` VALUES ('444', 'stypeid', '2', '副栏目ID集合', 'text', 'varchar(90)', '90', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949815', '1641949815', '0');
INSERT INTO `cms_channelfield` VALUES ('445', 'area_id', '3', '所在区域', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949817', '1641949817', '0');
INSERT INTO `cms_channelfield` VALUES ('446', 'city_id', '3', '所在城市', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949817', '1641949817', '0');
INSERT INTO `cms_channelfield` VALUES ('447', 'province_id', '3', '省份', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949817', '1641949817', '0');
INSERT INTO `cms_channelfield` VALUES ('448', 'collection', '3', '收藏数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949817', '1641949817', '0');
INSERT INTO `cms_channelfield` VALUES ('449', 'appraise', '3', '评价数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949817', '1641949817', '0');
INSERT INTO `cms_channelfield` VALUES ('450', 'restric_type', '3', '限制模式，0=免费，1=付费，2=会员专享，3=会员付费', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949817', '1641949817', '0');
INSERT INTO `cms_channelfield` VALUES ('451', 'sales_num', '3', '销售量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949817', '1641949817', '0');
INSERT INTO `cms_channelfield` VALUES ('452', 'users_free', '3', '是否会员免费，默认0不免费，1为免费', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949817', '1641949817', '0');
INSERT INTO `cms_channelfield` VALUES ('453', 'attrlist_id', '3', '参数列表ID', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949817', '1641949817', '0');
INSERT INTO `cms_channelfield` VALUES ('454', 'origin', '3', '来源', 'text', 'varchar(30)', '30', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949817', '1641949817', '0');
INSERT INTO `cms_channelfield` VALUES ('455', 'is_diyattr', '3', '自定义（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949817', '1641949817', '0');
INSERT INTO `cms_channelfield` VALUES ('456', 'is_slide', '3', '幻灯（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949817', '1641949817', '0');
INSERT INTO `cms_channelfield` VALUES ('457', 'is_roll', '3', '滚动（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949817', '1641949817', '0');
INSERT INTO `cms_channelfield` VALUES ('458', 'subtitle', '3', '副标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949817', '1641949817', '0');
INSERT INTO `cms_channelfield` VALUES ('459', 'stypeid', '3', '副栏目ID集合', 'text', 'varchar(90)', '90', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949817', '1641949817', '0');
INSERT INTO `cms_channelfield` VALUES ('460', 'area_id', '4', '所在区域', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949819', '1641949819', '0');
INSERT INTO `cms_channelfield` VALUES ('461', 'city_id', '4', '所在城市', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949819', '1641949819', '0');
INSERT INTO `cms_channelfield` VALUES ('462', 'province_id', '4', '省份', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949819', '1641949819', '0');
INSERT INTO `cms_channelfield` VALUES ('463', 'collection', '4', '收藏数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949819', '1641949819', '0');
INSERT INTO `cms_channelfield` VALUES ('464', 'appraise', '4', '评价数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949819', '1641949819', '0');
INSERT INTO `cms_channelfield` VALUES ('465', 'restric_type', '4', '限制模式，0=免费，1=付费，2=会员专享，3=会员付费', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949819', '1641949819', '0');
INSERT INTO `cms_channelfield` VALUES ('466', 'sales_num', '4', '销售量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949819', '1641949819', '0');
INSERT INTO `cms_channelfield` VALUES ('467', 'users_free', '4', '是否会员免费，默认0不免费，1为免费', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949819', '1641949819', '0');
INSERT INTO `cms_channelfield` VALUES ('468', 'attrlist_id', '4', '参数列表ID', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949819', '1641949819', '0');
INSERT INTO `cms_channelfield` VALUES ('469', 'origin', '4', '来源', 'text', 'varchar(30)', '30', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949819', '1641949819', '0');
INSERT INTO `cms_channelfield` VALUES ('470', 'is_diyattr', '4', '自定义（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949819', '1641949819', '0');
INSERT INTO `cms_channelfield` VALUES ('471', 'is_slide', '4', '幻灯（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949819', '1641949819', '0');
INSERT INTO `cms_channelfield` VALUES ('472', 'is_roll', '4', '滚动（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949819', '1641949819', '0');
INSERT INTO `cms_channelfield` VALUES ('473', 'subtitle', '4', '副标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949819', '1641949819', '0');
INSERT INTO `cms_channelfield` VALUES ('474', 'stypeid', '4', '副栏目ID集合', 'text', 'varchar(90)', '90', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949819', '1641949819', '0');
INSERT INTO `cms_channelfield` VALUES ('475', 'area_id', '6', '所在区域', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949820', '1641949820', '0');
INSERT INTO `cms_channelfield` VALUES ('476', 'city_id', '6', '所在城市', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949820', '1641949820', '0');
INSERT INTO `cms_channelfield` VALUES ('477', 'province_id', '6', '省份', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949820', '1641949820', '0');
INSERT INTO `cms_channelfield` VALUES ('478', 'collection', '6', '收藏数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949820', '1641949820', '0');
INSERT INTO `cms_channelfield` VALUES ('479', 'appraise', '6', '评价数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949820', '1641949820', '0');
INSERT INTO `cms_channelfield` VALUES ('480', 'restric_type', '6', '限制模式，0=免费，1=付费，2=会员专享，3=会员付费', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949820', '1641949820', '0');
INSERT INTO `cms_channelfield` VALUES ('481', 'sales_num', '6', '销售量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949820', '1641949820', '0');
INSERT INTO `cms_channelfield` VALUES ('482', 'users_free', '6', '是否会员免费，默认0不免费，1为免费', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949820', '1641949820', '0');
INSERT INTO `cms_channelfield` VALUES ('483', 'attrlist_id', '6', '参数列表ID', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949820', '1641949820', '0');
INSERT INTO `cms_channelfield` VALUES ('484', 'origin', '6', '来源', 'text', 'varchar(30)', '30', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949820', '1641949820', '0');
INSERT INTO `cms_channelfield` VALUES ('485', 'is_diyattr', '6', '自定义（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949820', '1641949820', '0');
INSERT INTO `cms_channelfield` VALUES ('486', 'is_slide', '6', '幻灯（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949820', '1641949820', '0');
INSERT INTO `cms_channelfield` VALUES ('487', 'is_roll', '6', '滚动（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949820', '1641949820', '0');
INSERT INTO `cms_channelfield` VALUES ('488', 'subtitle', '6', '副标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949820', '1641949820', '0');
INSERT INTO `cms_channelfield` VALUES ('489', 'stypeid', '6', '副栏目ID集合', 'text', 'varchar(90)', '90', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949820', '1641949820', '0');
INSERT INTO `cms_channelfield` VALUES ('490', 'area_id', '7', '所在区域', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949822', '1641949822', '0');
INSERT INTO `cms_channelfield` VALUES ('491', 'city_id', '7', '所在城市', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949822', '1641949822', '0');
INSERT INTO `cms_channelfield` VALUES ('492', 'province_id', '7', '省份', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949822', '1641949822', '0');
INSERT INTO `cms_channelfield` VALUES ('493', 'collection', '7', '收藏数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949822', '1641949822', '0');
INSERT INTO `cms_channelfield` VALUES ('494', 'appraise', '7', '评价数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949822', '1641949822', '0');
INSERT INTO `cms_channelfield` VALUES ('495', 'restric_type', '7', '限制模式，0=免费，1=付费，2=会员专享，3=会员付费', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949822', '1641949822', '0');
INSERT INTO `cms_channelfield` VALUES ('496', 'origin', '7', '来源', 'text', 'varchar(30)', '30', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949822', '1641949822', '0');
INSERT INTO `cms_channelfield` VALUES ('497', 'is_diyattr', '7', '自定义（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949822', '1641949822', '0');
INSERT INTO `cms_channelfield` VALUES ('498', 'is_slide', '7', '幻灯（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949822', '1641949822', '0');
INSERT INTO `cms_channelfield` VALUES ('499', 'is_roll', '7', '滚动（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949822', '1641949822', '0');
INSERT INTO `cms_channelfield` VALUES ('500', 'subtitle', '7', '副标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949822', '1641949822', '0');
INSERT INTO `cms_channelfield` VALUES ('501', 'stypeid', '7', '副栏目ID集合', 'text', 'varchar(90)', '90', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949822', '1641949822', '0');
INSERT INTO `cms_channelfield` VALUES ('502', 'area_id', '9', '所在区域', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949825', '1641949825', '0');
INSERT INTO `cms_channelfield` VALUES ('503', 'city_id', '9', '所在城市', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949825', '1641949825', '0');
INSERT INTO `cms_channelfield` VALUES ('504', 'province_id', '9', '省份', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949825', '1641949825', '0');
INSERT INTO `cms_channelfield` VALUES ('505', 'htmlfilename', '9', '自定义文件名', 'img', 'varchar(250)', '250', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949825', '1641949825', '0');
INSERT INTO `cms_channelfield` VALUES ('506', 'collection', '9', '收藏数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949825', '1641949825', '0');
INSERT INTO `cms_channelfield` VALUES ('507', 'appraise', '9', '评价数', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949825', '1641949825', '0');
INSERT INTO `cms_channelfield` VALUES ('508', 'restric_type', '9', '限制模式，0=免费，1=付费，2=会员专享，3=会员付费', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949825', '1641949825', '0');
INSERT INTO `cms_channelfield` VALUES ('509', 'sales_num', '9', '销售量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949825', '1641949825', '0');
INSERT INTO `cms_channelfield` VALUES ('510', 'users_free', '9', '是否会员免费，默认0不免费，1为免费', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949825', '1641949825', '0');
INSERT INTO `cms_channelfield` VALUES ('511', 'attrlist_id', '9', '参数列表ID', 'int', 'int(10) unsigned', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949825', '1641949825', '0');
INSERT INTO `cms_channelfield` VALUES ('512', 'origin', '9', '来源', 'text', 'varchar(30)', '30', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949825', '1641949825', '0');
INSERT INTO `cms_channelfield` VALUES ('513', 'is_diyattr', '9', '自定义（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949825', '1641949825', '0');
INSERT INTO `cms_channelfield` VALUES ('514', 'is_slide', '9', '幻灯（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949825', '1641949825', '0');
INSERT INTO `cms_channelfield` VALUES ('515', 'is_roll', '9', '滚动（0=否，1=是）', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949825', '1641949825', '0');
INSERT INTO `cms_channelfield` VALUES ('516', 'subtitle', '9', '副标题', 'text', 'varchar(200)', '200', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949825', '1641949825', '0');
INSERT INTO `cms_channelfield` VALUES ('517', 'stypeid', '9', '副栏目ID集合', 'text', 'varchar(90)', '90', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1641949825', '1641949825', '0');
INSERT INTO `cms_channelfield` VALUES ('518', 'target', '-99', '新窗口打开', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1547890773', '1547890773', '0');
INSERT INTO `cms_channelfield` VALUES ('519', 'nofollow', '-99', '防抓取', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1547890773', '1547890773', '0');
INSERT INTO `cms_channelfield` VALUES ('520', 'content_ey_m', '1', '手机端内容详情', 'htmltext', 'longtext', '250', '', '', '', '0', '1', '1', '0', '1', '0', '0', '100', '1', '1533464713', '1623047123', '0');
INSERT INTO `cms_channelfield` VALUES ('521', 'content_ey_m', '2', '手机端内容详情', 'htmltext', 'longtext', '250', '', '', '', '0', '1', '1', '0', '1', '0', '0', '100', '1', '1645086030', '1645086039', '0');
INSERT INTO `cms_channelfield` VALUES ('522', 'content_ey_m', '3', '手机端内容详情', 'htmltext', 'longtext', '250', '', '', '', '0', '1', '1', '0', '1', '0', '0', '100', '1', '1533359588', '1533359588', '0');
INSERT INTO `cms_channelfield` VALUES ('523', 'content_ey_m', '4', '手机端内容详情', 'htmltext', 'longtext', '250', '', '', '', '0', '1', '1', '0', '1', '0', '0', '100', '1', '1533359752', '1533359752', '0');
INSERT INTO `cms_channelfield` VALUES ('524', 'content_ey_m', '5', '手机端内容详情', 'htmltext', 'longtext', '250', '', '', '', '0', '1', '1', '0', '1', '0', '0', '100', '1', '1591957363', '1591957363', '0');
INSERT INTO `cms_channelfield` VALUES ('525', 'content_ey_m', '6', '手机端内容详情', 'htmltext', 'longtext', '250', '', '', '', '0', '1', '1', '0', '1', '0', '0', '100', '1', '1533464715', '1533464715', '0');
INSERT INTO `cms_channelfield` VALUES ('526', 'content_ey_m', '7', '手机端内容详情', 'htmltext', 'longtext', '250', '', '', '', '0', '1', '1', '0', '1', '0', '0', '100', '1', '1602320145', '1602320145', '0');
INSERT INTO `cms_channelfield` VALUES ('527', 'typearcrank', '-99', '阅读权限：0=开放浏览，-1=待审核稿件', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1547890773', '1547890773', '0');
INSERT INTO `cms_channelfield` VALUES ('528', 'empty_logic', '-99', '空内容逻辑', 'switch', 'tinyint(1)', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1533524780', '1533524780', '0');
INSERT INTO `cms_channelfield` VALUES ('529', 'users_discount_type', '0', '产品会员折扣类型(0:系统默认折扣; 1:指定会员级别; 2:不参与折扣;)', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1683873488', '1683873488', '0');
INSERT INTO `cms_channelfield` VALUES ('530', 'logistics_type', '0', '商品物流支持类型(1: 物流配送; 2: 到店核销)', 'text', 'varchar(100)', '100', '', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1690364521', '1690364521', '0');
INSERT INTO `cms_channelfield` VALUES ('531', 'editor_img_clear_link', '3', '清除非本站链接', 'switch', 'tinyint(1)', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1701047939', '1701047939', '0');
INSERT INTO `cms_channelfield` VALUES ('532', 'editor_remote_img_local', '3', '远程图片本地化', 'switch', 'tinyint(1)', '1', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1701047939', '1701047939', '0');
INSERT INTO `cms_channelfield` VALUES ('533', 'no_vip_pay', '3', 'restric_type = 2 时,会员专享,非会员可付费使用,0-关闭,1-开启', 'switch', 'tinyint(3)', '3', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1701047939', '1701047939', '0');
INSERT INTO `cms_channelfield` VALUES ('534', 'logistics_type', '3', '商品物流支持类型(1: 物流配送; 2: 到店核销)', 'text', 'varchar(100)', '100', '1', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1701047939', '1701047939', '0');
INSERT INTO `cms_channelfield` VALUES ('535', 'sales_all', '3', '虚拟总销量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1701047939', '1701047939', '0');
INSERT INTO `cms_channelfield` VALUES ('536', 'virtual_sales', '3', '商品虚拟销售量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1701047939', '1701047939', '0');
INSERT INTO `cms_channelfield` VALUES ('537', 'users_discount_type', '3', '产品会员折扣类型(0:系统默认折扣; 1:指定会员级别; 2:不参与折扣;)', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1701047939', '1701047939', '0');
INSERT INTO `cms_channelfield` VALUES ('538', 'crossed_price', '3', '商品划线价', 'decimal', 'decimal(10,2) unsigned', '10', '0.00', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1701047939', '1701047939', '0');
INSERT INTO `cms_channelfield` VALUES ('539', 'free_shipping', '3', '商品是否包邮(1包邮(免运费)  0跟随系统)', 'switch', 'tinyint(1) unsigned', '1', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1701047939', '1701047939', '0');
INSERT INTO `cms_channelfield` VALUES ('540', 'merchant_id', '3', '多商家ID', 'datetime', 'int(11) unsigned', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1701047939', '1701047939', '0');
INSERT INTO `cms_channelfield` VALUES ('541', 'total_arc', '-99', '栏目下文档数量', 'int', 'int(10)', '10', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1711942240', '1711942240', '0');
INSERT INTO `cms_channelfield` VALUES ('542', 'removal_time', '1', '下架时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('543', 'removal_time', '2', '下架时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('544', 'removal_time', '3', '下架时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('545', 'removal_time', '4', '下架时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('546', 'removal_time', '5', '下架时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('547', 'removal_time', '6', '下架时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('548', 'removal_time', '7', '下架时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('549', 'removal_time', '9', '下架时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
INSERT INTO `cms_channelfield` VALUES ('550', 'removal_time', '0', '下架时间', 'datetime', 'int(11)', '11', '0', '', '', '0', '0', '1', '0', '1', '1', '1', '100', '1', '1574233796', '1574233796', '0');
CREATE TABLE `cms_channelfield_bind` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `typeid` int(10) DEFAULT '0' COMMENT '栏目ID',
  `field_id` int(10) DEFAULT '0' COMMENT '自定义字段ID',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=280 DEFAULT CHARSET=utf8 COMMENT='栏目与自定义字段绑定表';
CREATE TABLE `cms_channelfield_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT '' COMMENT '字段名称',
  `channel_id` int(10) DEFAULT '0' COMMENT '模型ID',
  `dtype` varchar(32) DEFAULT '' COMMENT '字段类型',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='自定义字段日志表';
CREATE TABLE `cms_channeltype` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` varchar(50) NOT NULL DEFAULT '' COMMENT '识别id',
  `title` varchar(30) DEFAULT '' COMMENT '名称',
  `ntitle` varchar(30) DEFAULT '' COMMENT '左侧菜单名称',
  `table` varchar(50) DEFAULT '' COMMENT '表名',
  `ctl_name` varchar(50) DEFAULT '' COMMENT '控制器名称（区分大小写）',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态(1=启用，0=屏蔽)',
  `ifsystem` tinyint(1) DEFAULT '0' COMMENT '字段分类，1=系统(不可修改)，0=自定义',
  `is_repeat_title` tinyint(1) DEFAULT '1' COMMENT '文档标题重复，1=允许，0=不允许',
  `is_release` tinyint(1) DEFAULT '0' COMMENT '模型是否允许应用于会员投稿发布，1是，0否',
  `is_litpic_users_release` tinyint(1) DEFAULT '1' COMMENT '缩略图是否应用于会员投稿，1=允许，0=不允许',
  `data` text COMMENT '额外序列化存储数据',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '伪删除，1=是，0=否',
  `sort_order` smallint(6) DEFAULT '50' COMMENT '排序',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idention` (`nid`) USING BTREE,
  UNIQUE KEY `ctl_name` (`ctl_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=101 DEFAULT CHARSET=utf8 COMMENT='系统模型表';
INSERT INTO `cms_channeltype` VALUES ('1', 'article', '文章模型', '文章', 'article', 'Article', '1', '1', '1', '1', '1', '', '0', '1', '0', '1564532747');
INSERT INTO `cms_channeltype` VALUES ('4', 'download', '下载模型', '下载', 'download', 'Download', '1', '1', '1', '1', '1', '', '0', '4', '0', '1564532747');
INSERT INTO `cms_channeltype` VALUES ('2', 'product', '产品模型', '产品', 'product', 'Product', '1', '1', '1', '1', '1', '', '0', '2', '0', '1564532747');
INSERT INTO `cms_channeltype` VALUES ('8', 'guestbook', '留言模型', '留言', 'guestbook', 'Guestbook', '1', '1', '1', '1', '1', '', '0', '8', '1509197711', '1690181604');
INSERT INTO `cms_channeltype` VALUES ('6', 'single', '单页模型', '单页', 'single', 'Single', '1', '1', '1', '1', '1', '', '0', '6', '1523091961', '1564532747');
INSERT INTO `cms_channeltype` VALUES ('3', 'images', '图集模型', '图集', 'images', 'Images', '1', '1', '1', '1', '1', '', '0', '3', '1523929121', '1564532747');
INSERT INTO `cms_channeltype` VALUES ('9', 'recruit', '招聘模型', '人才招聘', 'recruit', 'Recruit', '1', '0', '1', '1', '1', '', '0', '50', '1563526560', '1564532747');
INSERT INTO `cms_channeltype` VALUES ('5', 'media', '视频模型', '视频', 'media', 'Media', '0', '1', '1', '1', '1', '', '0', '5', '1509197711', '1564532747');
INSERT INTO `cms_channeltype` VALUES ('7', 'special', '专题模型', '专题', 'special', 'Special', '0', '1', '1', '1', '1', '', '0', '7', '1509197711', '1564532747');
INSERT INTO `cms_channeltype` VALUES ('51', 'ask', '问答模型', '问答', 'ask', 'Ask', '0', '1', '1', '1', '1', '', '1', '9', '1509197711', '1692668301');
CREATE TABLE `cms_citysite` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `name` varchar(32) DEFAULT '' COMMENT '地区名称',
  `level` tinyint(4) DEFAULT '0' COMMENT '地区等级 分省市县区',
  `parent_id` int(10) DEFAULT '0' COMMENT '父id',
  `topid` int(10) DEFAULT '0' COMMENT '顶级ID',
  `initial` varchar(5) DEFAULT '' COMMENT '首字母',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态（1：开启，0：隐藏）',
  `is_hot` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否热门',
  `domain` varchar(50) NOT NULL DEFAULT '' COMMENT '二级域名',
  `is_open` tinyint(1) DEFAULT '0' COMMENT '二级域名开启状态，0=否，1=是',
  `seoset` tinyint(1) DEFAULT '0' COMMENT 'SEO设置，0=使用主站，1=自定义',
  `seo_title` varchar(200) DEFAULT '' COMMENT 'SEO标题',
  `seo_keywords` varchar(200) DEFAULT '' COMMENT 'SEO关键词',
  `seo_description` text COMMENT 'SEO描述',
  `sort_order` int(6) unsigned NOT NULL DEFAULT '100' COMMENT '排序',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `showall` tinyint(3) DEFAULT '1' COMMENT '是否显示主站信息',
  PRIMARY KEY (`id`),
  KEY `domain` (`domain`) USING BTREE,
  KEY `level` (`level`,`status`) USING BTREE,
  KEY `initial` (`initial`,`sort_order`,`id`) USING BTREE,
  KEY `parent_id` (`parent_id`,`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='城市分站表';
CREATE TABLE `cms_common_pic` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '常用图片ID',
  `pic_path` varchar(255) NOT NULL DEFAULT '' COMMENT '图片地址',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '多语言',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COMMENT='常用图片';
CREATE TABLE `cms_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT '' COMMENT '配置的key键名',
  `value` text,
  `inc_type` varchar(64) DEFAULT '' COMMENT '配置分组',
  `desc` varchar(50) DEFAULT '' COMMENT '描述',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '是否已删除，0=否，1=是',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=381 DEFAULT CHARSET=utf8 COMMENT='系统配置表';
INSERT INTO `cms_config` VALUES ('1', 'is_mark', '0', 'water', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('2', 'mark_txt', '易优Cms', 'water', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('3', 'mark_img', '/uploads/allimg/20190114/f0d5e5830502125f5077212a90ef3a33.png', 'water', '', 'cn', '0', '1547463466');
INSERT INTO `cms_config` VALUES ('4', 'mark_width', '200', 'water', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('5', 'mark_height', '50', 'water', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('6', 'mark_degree', '54', 'water', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('7', 'mark_quality', '56', 'water', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('8', 'mark_sel', '9', 'water', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('9', 'sms_time_out', '120', 'sms', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('10', 'theme_style', '1', 'basic', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('11', 'file_size', '2', 'basic', '', 'cn', '0', '1580983826');
INSERT INTO `cms_config` VALUES ('12', 'image_type', 'jpg|gif|png|bmp|jpeg|ico|webp', 'basic', '', 'cn', '0', '1675389691');
INSERT INTO `cms_config` VALUES ('13', 'file_type', 'zip|gz|rar|iso|doc|xls|ppt|wps|docx|xlsx|pptx', 'basic', '', 'cn', '0', '1675389691');
INSERT INTO `cms_config` VALUES ('14', 'media_type', 'swf|mpg|mp3|rm|rmvb|wmv|wma|wav|mid|mov|mp4', 'basic', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('15', 'web_keywords', '网站建设,网站设计,网站开发', 'web', '', 'cn', '0', '1699840830');
INSERT INTO `cms_config` VALUES ('17', 'sms_platform', '1', 'sms', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('18', 'seo_viewtitle_format', '2', 'seo', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('24', 'mark_type', 'img', 'water', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('25', 'mark_txt_size', '30', 'water', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('26', 'mark_txt_color', '#000000', 'water', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('27', 'oss_switch', '0', 'oss', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('28', 'web_name', '伟创网络科技', 'web', '', 'cn', '0', '1690162017');
INSERT INTO `cms_config` VALUES ('29', 'web_logo', '/uploads/allimg/20210114/1-2101140933194M.png', 'web', '', 'cn', '0', '1610696352');
INSERT INTO `cms_config` VALUES ('30', 'web_ico', '/favicon.ico', 'web', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('31', 'web_basehost', 'http://dev166.eyou.hk', 'web', '', 'cn', '0', '1701048286');
INSERT INTO `cms_config` VALUES ('32', 'web_description', '我们是一家专业的网站建设公司，致力于为客户提供高质量的网站设计和开发服务。我们拥有一支经验丰富、技术娴熟的团队，能够根据客户的需求和要求，设计出具有创意和吸引力的网站。', 'web', '', 'cn', '0', '1699840830');
INSERT INTO `cms_config` VALUES ('79', 'web_recordnum', '琼ICP备xxxxxxxx号', 'web', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('33', 'web_copyright', 'Copyright © 2012-2022 某某公司 版权所有', 'web', '', 'cn', '0', '1667183867');
INSERT INTO `cms_config` VALUES ('34', 'web_thirdcode_pc', '', 'web', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('35', 'web_thirdcode_wap', '', 'web', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('39', 'seo_arcdir', '/html', 'seo', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('40', 'seo_pseudo', '1', 'seo', '', 'cn', '0', '1623813164');
INSERT INTO `cms_config` VALUES ('41', 'list_symbol', '&gt;', 'basic', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('42', 'sitemap_auto', '1', 'sitemap', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('43', 'sitemap_not1', '0', 'sitemap', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('44', 'sitemap_not2', '1', 'sitemap', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('45', 'sitemap_xml', '1', 'sitemap', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('46', 'sitemap_txt', '0', 'sitemap', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('47', 'sitemap_zzbaidutoken', '', 'sitemap', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('48', 'seo_expires_in', '7200', 'seo', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('55', 'web_title', '伟创网络科技有限公司', 'web', '', 'cn', '0', '1690162017');
INSERT INTO `cms_config` VALUES ('57', 'web_authortoken', '', 'web', '', 'cn', '0', '1701048286');
INSERT INTO `cms_config` VALUES ('60', 'web_attr_1', '400-12345-67890', 'web', '', 'cn', '0', '1667183895');
INSERT INTO `cms_config` VALUES ('62', 'seo_inlet', '1', 'seo', '', 'cn', '0', '1553566003');
INSERT INTO `cms_config` VALUES ('63', 'web_cmspath', '', 'web', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('64', 'web_sqldatapath', '/data/sqldata_ttbBSmgQz4JeurPSnpoL', 'web', '', 'cn', '0', '1765331657');
INSERT INTO `cms_config` VALUES ('65', 'web_cmsurl', '', 'web', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('66', 'web_templets_dir', '/template', 'web', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('67', 'web_templeturl', '/template', 'web', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('68', 'web_templets_pc', '/template/pc', 'web', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('69', 'web_templets_m', '/template/mobile', 'web', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('70', 'web_eyoucms', 'http://www.eyoucms.com', 'web', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('78', '_cmscopyright', 'cbQ1AmriPQ7LHyth9xeHH6Lj', 'php', '', 'cn', '0', '1571040555');
INSERT INTO `cms_config` VALUES ('76', 'seo_liststitle_format', '2', 'seo', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('77', 'web_status', '0', 'web', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('80', 'web_is_authortoken', '0', 'web', '', 'cn', '0', '1703142156');
INSERT INTO `cms_config` VALUES ('81', 'web_adminbasefile', '/login.php', 'web', '', 'cn', '0', '1614152866');
INSERT INTO `cms_config` VALUES ('82', 'seo_rewrite_format', '1', 'seo', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('83', 'web_cmsmode', '2', 'web', '', 'cn', '0', '1701048841');
INSERT INTO `cms_config` VALUES ('84', 'web_htmlcache_expires_in', '0', 'web', '', 'cn', '0', '1546477337');
INSERT INTO `cms_config` VALUES ('85', 'web_show_popup_upgrade', '1', 'web', '', 'cn', '0', '1653359674');
INSERT INTO `cms_config` VALUES ('86', 'web_weapp_switch', '1', 'web', '', 'cn', '0', '1563498417');
INSERT INTO `cms_config` VALUES ('88', 'seo_dynamic_format', '1', 'seo', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('89', 'system_sql_mode', 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION', 'system', '', 'cn', '0', '1725268809');
INSERT INTO `cms_config` VALUES ('90', 'system_home_default_lang', 'cn', 'system', '', 'cn', '0', '0');
INSERT INTO `cms_config` VALUES ('91', 'system_langnum', '1', 'system', '', 'cn', '0', '1610357910');
INSERT INTO `cms_config` VALUES ('170', 'web_exception', '0', 'web', '', 'cn', '0', '1546477337');
INSERT INTO `cms_config` VALUES ('171', 'web_language_switch', '0', 'web', '', 'cn', '0', '1703142357');
INSERT INTO `cms_config` VALUES ('174', 'web_is_https', '0', 'web', '', 'cn', '0', '1552968816');
INSERT INTO `cms_config` VALUES ('176', 'smtp_syn_weapp', '1', 'smtp', '', 'cn', '0', '1553566547');
INSERT INTO `cms_config` VALUES ('178', 'php_eyou_blacklist', '', 'php', '', 'cn', '0', '1553654429');
INSERT INTO `cms_config` VALUES ('190', 'system_auth_code', '$2y$11$43dd7eab6b1e84d79e28f86', 'system', '', 'cn', '0', '1557733856');
INSERT INTO `cms_config` VALUES ('192', 'system_upgrade_filelist', 'ZGF0YS9tb2RlbC90ZW1wbGF0ZS9wYy92aWV3X2N1c3RvbW1vZGVsLmh0bTxicj5kYXRhL21vZGVsL3RlbXBsYXRlL3BjL2xpc3RzX2N1c3RvbW1vZGVsLmh0bTxicj5kYXRhL2NvbmYvdmVyc2lvbi50eHQ8YnI+ZGF0YS9kZG9zX3JlcGFpci8uaHRhY2Nlc3M8YnI+ZGF0YS93ZWFwcC9TYW1wbGUvd2VhcHAvU2FtcGxlL2ZpbGVsaXN0LnR4dDxicj5kYXRhL3dlYXBwL1NhbXBsZS93ZWFwcC9TYW1wbGUvYmVoYXZpb3IvcGx1Z2lucy9TYW1wbGVCZWhhdmlvci5waHA8YnI+YXBwbGljYXRpb24vdXNlci9tb2RlbC9QYXlBcGkucGhwPGJyPmFwcGxpY2F0aW9uL3VzZXIvbW9kZWwvTWVkaWFGaWxlLnBocDxicj5hcHBsaWNhdGlvbi91c2VyL21vZGVsL1Nob3AucGhwPGJyPmFwcGxpY2F0aW9uL3VzZXIvYmVoYXZpb3IvTW9kdWxlSW5pdEJlaGF2aW9yLnBocDxicj5hcHBsaWNhdGlvbi91c2VyL2JlaGF2aW9yL1ZpZXdGaWx0ZXJCZWhhdmlvci5waHA8YnI+YXBwbGljYXRpb24vdXNlci9sb2dpYy9QYXlBcGlMb2dpYy5waHA8YnI+YXBwbGljYXRpb24vdXNlci9odG1sLnBocDxicj5hcHBsaWNhdGlvbi91c2VyL2NvbnRyb2xsZXIvVXBsb2FkaWZ5LnBocDxicj5hcHBsaWNhdGlvbi91c2VyL2NvbnRyb2xsZXIvU2hvcENvbW1lbnQucGhwPGJyPmFwcGxpY2F0aW9uL3VzZXIvY29udHJvbGxlci9Vc2Vyc1JlbGVhc2UucGhwPGJyPmFwcGxpY2F0aW9uL3VzZXIvY29udHJvbGxlci9QYXlBcGkucGhwPGJyPmFwcGxpY2F0aW9uL3VzZXIvY29udHJvbGxlci9QYXkucGhwPGJyPmFwcGxpY2F0aW9uL3VzZXIvY29udHJvbGxlci9TaG9wLnBocDxicj5hcHBsaWNhdGlvbi91c2VyL2NvbnRyb2xsZXIvU210cG1haWwucGhwPGJyPmFwcGxpY2F0aW9uL3VzZXIvY29udHJvbGxlci9Vc2Vycy5waHA8YnI+YXBwbGljYXRpb24vY29tbW9uLnBocDxicj5hcHBsaWNhdGlvbi9wbHVnaW5zL2JlaGF2aW9yL1ZpZXdGaWx0ZXJCZWhhdmlvci5waHA8YnI+YXBwbGljYXRpb24vcGx1Z2lucy9odG1sLnBocDxicj5hcHBsaWNhdGlvbi9jb21tb24vbW9kZWwvQXJjdHlwZS5waHA8YnI+YXBwbGljYXRpb24vY29tbW9uL21vZGVsL0FsaVBheS5waHA8YnI+YXBwbGljYXRpb24vY29tbW9uL21vZGVsL1Nob3BTZXJ2aWNlSGFuZGxlLnBocDxicj5hcHBsaWNhdGlvbi9jb21tb24vbW9kZWwvVGFnbGlzdC5waHA8YnI+YXBwbGljYXRpb24vY29tbW9uL21vZGVsL0V5b3VVc2Vycy5waHA8YnI+YXBwbGljYXRpb24vY29tbW9uL21vZGVsL1Rpa1Rvay5waHA8YnI+YXBwbGljYXRpb24vY29tbW9uL21vZGVsL1Nob3BQdWJsaWNIYW5kbGUucGhwPGJyPmFwcGxpY2F0aW9uL2NvbW1vbi9tb2RlbC9CYWlkdVBheS5waHA8YnI+YXBwbGljYXRpb24vY29tbW9uL21vZGVsL1dlYXBwLnBocDxicj5hcHBsaWNhdGlvbi9jb21tb24vbW9kZWwvTGFuZ3VhZ2UucGhwPGJyPmFwcGxpY2F0aW9uL2NvbW1vbi9tb2RlbC9Vc2Vyc1JlY2hhcmdlUGFjay5waHA8YnI+YXBwbGljYXRpb24vY29tbW9uL21vZGVsL09yZGVyUHJlSGFuZGxlLnBocDxicj5hcHBsaWNhdGlvbi9jb21tb24vYmVoYXZpb3IvRmlsZVByb3RlY3QucGhwPGJyPmFwcGxpY2F0aW9uL2NvbW1vbi9sb2dpYy9TaG9wQ29tbW9uTG9naWMucGhwPGJyPmFwcGxpY2F0aW9uL2NvbW1vbi9sb2dpYy9BcmN0eXBlTG9naWMucGhwPGJyPmFwcGxpY2F0aW9uL2NvbW1vbi9jb250cm9sbGVyL0NvbW1vbi5waHA8YnI+YXBwbGljYXRpb24vaG9tZS9tb2RlbC9NZWRpYUZpbGUucGhwPGJyPmFwcGxpY2F0aW9uL2hvbWUvbW9kZWwvRG93bmxvYWRGaWxlLnBocDxicj5hcHBsaWNhdGlvbi9ob21lL2JlaGF2aW9yL01vZHVsZUluaXRCZWhhdmlvci5waHA8YnI+YXBwbGljYXRpb24vaG9tZS9iZWhhdmlvci9WaWV3RmlsdGVyQmVoYXZpb3IucGhwPGJyPmFwcGxpY2F0aW9uL2hvbWUvbG9naWMvRmllbGRMb2dpYy5waHA8YnI+YXBwbGljYXRpb24vaG9tZS9odG1sLnBocDxicj5hcHBsaWNhdGlvbi9ob21lL2NvbnRyb2xsZXIvVGFncy5waHA8YnI+YXBwbGljYXRpb24vaG9tZS9jb250cm9sbGVyL1ZpZXcucGhwPGJyPmFwcGxpY2F0aW9uL2hvbWUvY29udHJvbGxlci9TZWFyY2gucGhwPGJyPmFwcGxpY2F0aW9uL2hvbWUvY29udHJvbGxlci9DaXR5c2l0ZS5waHA8YnI+YXBwbGljYXRpb24vaG9tZS9jb250cm9sbGVyL0xpc3RzLnBocDxicj5hcHBsaWNhdGlvbi9ob21lL2NvbnRyb2xsZXIvSW5kZXgucGhwPGJyPmFwcGxpY2F0aW9uL2Z1bmN0aW9uLnBocDxicj5hcHBsaWNhdGlvbi9oZWxwZXIucGhwPGJyPmFwcGxpY2F0aW9uL2FwaS9tb2RlbC92MS9BcGkucGhw', 'system', '', 'cn', '0', '1765331659');
INSERT INTO `cms_config` VALUES ('380', 'editor_bd_style', '', 'editor', '', 'cn', '0', '1765331866');
INSERT INTO `cms_config` VALUES ('366', 'web_recycle_switch', '1', 'web', '', 'cn', '0', '1701045570');
INSERT INTO `cms_config` VALUES ('354', 'php_websensitive', '5aix5LmQfOWNmuW9qXzkuJbnlYzmna985aSc5bqXfOi1jOWNmnzmnqrmlK986aOO5pq0fGJvYnzmirzms6h8OyYjfGHniYd85aSn54mHfOasp+e+jnwo5Lit5Zu9KXzvvIjkuK3lm73vvIl85Y2K5bKbwrfkvZPogrI=', 'php', '', 'cn', '0', '1721637118');
INSERT INTO `cms_config` VALUES ('331', 'other_pcwapjs', '0', 'other', '', 'cn', '0', '1636442096');
INSERT INTO `cms_config` VALUES ('247', 'syn_admin_logic_video_addfields', '5', 'syn', '', 'cn', '0', '1614152870');
INSERT INTO `cms_config` VALUES ('248', 'syn_admin_logic_add_tag', '1', 'syn', '', 'cn', '0', '1591957363');
INSERT INTO `cms_config` VALUES ('249', 'syn_admin_logic_users_parameter', '1', 'syn', '', 'cn', '0', '1591957363');
INSERT INTO `cms_config` VALUES ('227', 'seo_html_arcdir', 'html', 'seo', '', 'cn', '0', '1658916485');
INSERT INTO `cms_config` VALUES ('228', 'seo_html_listname', '2', 'seo', '', 'cn', '0', '1567578996');
INSERT INTO `cms_config` VALUES ('229', 'seo_html_pagename', '2', 'seo', '', 'cn', '0', '1567578996');
INSERT INTO `cms_config` VALUES ('230', 'seo_force_inlet', '1', 'seo', '', 'cn', '0', '1567578996');
INSERT INTO `cms_config` VALUES ('193', 'system_version', 'v1.7.7', 'system', '', 'cn', '0', '1765331695');
INSERT INTO `cms_config` VALUES ('195', 'web_users_switch', '1', 'web', '', 'cn', '0', '1563498413');
INSERT INTO `cms_config` VALUES ('199', 'system_correctarctypedirpath', '1', 'system', '', 'cn', '0', '1563503940');
INSERT INTO `cms_config` VALUES ('203', 'web_attr_13', '/uploads/allimg/20210115/1-210115153Z9511.png', 'web', '', 'cn', '0', '1610696352');
INSERT INTO `cms_config` VALUES ('225', 'system_synleveldata', '1', 'system', '', 'cn', '0', '1564532901');
INSERT INTO `cms_config` VALUES ('235', 'system_robots_edit', '1', 'system', '', 'cn', '0', '1571038279');
INSERT INTO `cms_config` VALUES ('237', 'syn_gb_attribute_showlist', '1', 'syn', '', 'cn', '0', '1576764161');
INSERT INTO `cms_config` VALUES ('238', 'system_smtp_tpl_5', '1', 'system', '', 'cn', '0', '1587364685');
INSERT INTO `cms_config` VALUES ('240', 'syn_admin_logic_sms_template', '1', 'syn', '', 'cn', '0', '1591262356');
INSERT INTO `cms_config` VALUES ('241', 'php_weapp_plugin_open', '1', 'php', '', 'cn', '0', '1703142166');
INSERT INTO `cms_config` VALUES ('243', 'syn_admin_logic_unlink', '1', 'syn', '', 'cn', '0', '1591262356');
INSERT INTO `cms_config` VALUES ('244', 'syn_admin_logic_update_basic', '1', 'syn', '', 'cn', '0', '1591262356');
INSERT INTO `cms_config` VALUES ('245', 'syn_admin_logic_update_tag', '1', 'syn', '', 'cn', '0', '1591262356');
INSERT INTO `cms_config` VALUES ('246', 'syn_admin_logic_update_arctype', '1', 'syn', '', 'cn', '0', '1591262356');
INSERT INTO `cms_config` VALUES ('250', 'syn_admin_logic_users_download', '1', 'syn', '', 'cn', '0', '1602320126');
INSERT INTO `cms_config` VALUES ('251', 'syn_admin_logic_special_addfields', '5', 'syn', '', 'cn', '0', '1614152870');
INSERT INTO `cms_config` VALUES ('252', 'syn_admin_logic_session_conf', '1', 'syn', '', 'cn', '0', '1602320145');
INSERT INTO `cms_config` VALUES ('253', 'syn_admin_logic_arctype_topid', '2', 'syn', '', 'cn', '0', '1610334638');
INSERT INTO `cms_config` VALUES ('254', 'syn_admin_logic_arctype_topid2', '1', 'syn', '', 'cn', '0', '1609929250');
INSERT INTO `cms_config` VALUES ('255', 'web_attr_15', 'demo@admin.com', 'web', '', 'cn', '0', '1667183901');
INSERT INTO `cms_config` VALUES ('256', 'web_attr_16', '广东省广州市天河区某某科技园', 'web', '', 'cn', '0', '1609930161');
INSERT INTO `cms_config` VALUES ('257', 'web_attr_17', '/uploads/allimg/20210106/1-2101061T919343.jpg', 'web', '', 'cn', '0', '1610696352');
INSERT INTO `cms_config` VALUES ('261', 'web_recordnum_mode', '0', 'web', '', 'cn', '0', '1609930161');
INSERT INTO `cms_config` VALUES ('262', 'thumb_open', '0', 'thumb', '', 'cn', '0', '1609985492');
INSERT INTO `cms_config` VALUES ('263', 'thumb_mode', '2', 'thumb', '', 'cn', '0', '1609985492');
INSERT INTO `cms_config` VALUES ('264', 'thumb_color', '#FFFFFF', 'thumb', '', 'cn', '0', '1609985492');
INSERT INTO `cms_config` VALUES ('265', 'thumb_width', '300', 'thumb', '', 'cn', '0', '1609985492');
INSERT INTO `cms_config` VALUES ('266', 'thumb_height', '300', 'thumb', '', 'cn', '0', '1609985492');
INSERT INTO `cms_config` VALUES ('297', 'system_usecodelist', 'a944VVVTVAUHAAEFUQwMBwJSWFFWAAIEUFMFB1BqFnYOVFcCDA1GGRVnVFsJWkFBHBMnCw1DU0ZfUBBDHRN2XAxRVgwVEQsFBwAAF00XcApJXAgKC0dEWxMdRCNVdV1BDFFWDBURCxcbEnVcGFhdDVlBEwsvVlpYEx1EK1hfTlAIGhRHPBoPUFFFExlDYFMEEh1DMQlSUEETHUQpREFdUghZVxUEGkYZFXFdXBhAWixDQkNIQHpZQUNfDw9WExgaJl1CDAIERhkVclBcBUBHC19EDRFAGxRuUF9ETRNyW1QNXVsRDAwKFxsSd1oOQUQRWV8VRk4Vcl1CUhMSQhMYGjJQVxAUFgVbFRwTbQhAWQplVENIQHVXQFJZMQBFVEYaTRp8ChINCFpWVEIXTRdgDFlfBQEaFRoWZlAID1hEYUoNGhRHMRYUXFZeVlAIT10EVxNNRjBYVFtFQkRNE39bTAhbXUdJQTddWEd3QA1ZYAZIRUNIQHRZWUFDAxJCYV1bFU1KAEdPRmZeV19QBRcYQX1YDw0BWENaRRNKQ3JQR1AOTUxHSUE3UFlDWEEIQ1FBHBMiAAlSTxYdEyoIUlRaSwQaFEcxEQVbRFZUR0MZFjRRQwQXQBsUZ1hDAw8THRZqCF9QEQYPDVZcEh0XKVxQBhIdQycHWUVBQhNKQ3ZEUUsVW1QQB0FIF3FHcloFUBZPEmATVkAbFHtBVBQARVhCXUMUGjcAFAVHUxIdFyxUREEcEzEREV9XWF0TSkN8XkFLBEsaSUczBUxFVVBRQxkWNEh8BAoXFRoWZl4UCl5DUF0TGhRHKQwHWk4SHRc1VEcIdl0OE0AbFHBYSFZRABMYGihWTgwRBkYZFWVCUBNGFk8SfQgRG1ZYFh0TLQRIXV1fCUwaSUc3BVJHXERGQxkWKFVIFgsQU0UWHRMxA11eU1EPGhRHJBYQWlVZRBdNF1cBAAAjVTNzdHJgeSQnfHN1aSB3ehI3MSZzdn9zQidhdQRqZjcyFXtjcgBwIApBdXxuWGpvNyciJl5bdmVZDVBwFH5rJVUNcmBfe2kjIEJmdQkJWWknESUxcAdycmQ7YGZTSGgkMgF6YF9rfSNQe1JsXw11fCMkLTxzfVV3BA1jZiV2YDYyVnR3YmN5MiR8c2VqEnx+MwwgFQBZU20aEFNgDEVtTjIKQWobY0AsVlRWRGw1C2E1BhpReWQHemU3B31UVAMUDgRaWQJ0Qi8DaUt/UjAADFI2Dj5tUl5DeiJcATB+RRABLl9sTXlWPyp7A38IN3ppJDw7D3R/ZlZBIm0EIXFjCjEmcHNAdGAeLGtkZkgEanknMzIxUn9nd1I2cGIxdGA0VSNgXnZ0YApQZWBmXCl8fSgpIVVdZnJWTRR0cgdncBsXM3FjBHBiCidpd1MIM3dUMzw1Ml1TeFZYJUZtUXJCKE8uXUddekdRVFBiBWEtem1QBjtUZ1QSbA', 'system', '', 'cn', '0', '1610350612');
INSERT INTO `cms_config` VALUES ('298', 'web_theme_color_model', '1', 'web', '', 'cn', '0', '1610357887');
INSERT INTO `cms_config` VALUES ('299', 'web_adminlogo', '/public/static/admin/images/logo_ey.png', 'web', '', 'cn', '0', '1610357887');
INSERT INTO `cms_config` VALUES ('300', 'web_loginlogo', '', 'web', '', 'cn', '0', '1610357887');
INSERT INTO `cms_config` VALUES ('301', 'web_loginbgimg_model', '1', 'web', '', 'cn', '0', '1610352403');
INSERT INTO `cms_config` VALUES ('378', 'system_explanation_welcome_5', '1', 'system', '', 'cn', '0', '1751269976');
INSERT INTO `cms_config` VALUES ('303', 'web_loginbgimg', '/public/static/admin/images/login-bg.jpg', 'web', '', 'cn', '0', '1610352403');
INSERT INTO `cms_config` VALUES ('304', 'syn_admin_logic_1608884981', '1', 'syn', '', 'cn', '0', '1610352406');
INSERT INTO `cms_config` VALUES ('305', 'web_users_tpl_theme', '', 'web', '', 'cn', '0', '1610352449');
INSERT INTO `cms_config` VALUES ('272', 'php_serviceinfo', '2ff9AgYIVgIGVVEHAQpXDlEHAwEGBVsJDVQAAghOFkNXVhVXWEsPRl8TBBULGwZWDU5XL15nX3duf1lAZgxFbEhfE3hUVgprTAgPVggBBlY3VVcBQm5fd1AAXnpme1laSFkIQ0RVCnkPXVdWfnYGRxUGBCR5QGBWQ1xdR0tYRwJaDClLb1IAdX4LCG5VAQZWN1hXV15+X3dYQkQeVB5eWkVbU35fTFNoQ1wqb0tPRX4OXRcoAFpUDwpHQnxdDQFtAH9TUH1aEUBQDhdtYVZURS0LQE8RV0xMUV5GU1hZUVlBU0QIFGQTClQDVmtEDAEEVGoXVlEPAWRMBlIAAGlBAFcEBG5DAAAFUjkUA1QIAGsRAgdSUmpMDwsFDGlEAwcMCmgTBAEMVW5DU1QPBmVGAwFQVD9GUF8ICBMYF1BAQFxdRgtBUQlECBQ5FA9TXAZrEQNQWlVqTAAJV1BpRAFRVwdoEwsADVZuQ1NSDwllRgFTAlE/RgMOXF9tQQAFBVBoR1IAAgcaShBXEBVfXkteRAMEQFkRakwAW1QBaUQABg1UaBMKBl4CbkNRBFQEZUYOUgNSP0YCXAkKbUEBVARVaEcDVAYOZBMEBV1Za0QPBANXahdWBFJfZEwEAAVVaUFSVARXEBoaB0dCDQ5FXEpUBkoDQFkRakwAW1QBaUQABg1UaBMKBl4CbkNRBFQEZUYOUgNSP0YCXAkKbUEBVARVaEcDVAYOZBMEBV1Za0QPBANXahdWBFJfZEwEAAVVaUFSVARXEBoaB0dCDQ5FXEpUAkYMQD9GDltdDG1BAAMMUmhHDFZUUmQTBlMGVGtEAAUCVHcrP0YBCwwBbUEDAg0MaEcCUQYFZBMHAQEHa0QMBwcAahcFVQYIGhUTVUFAQFFGS0YDQ0NdFUYUX0NSSHBEfg5GVSpeYA1bfl1Nb2cMBFVlBQp7XFcffEw8UXh1eERRN0ELLmB/D11Ae1hQeXdEV19iAFZxVBJoZSxXektoAnkOdxUuewZKcVN0QXtifAJRS34KV356FlFbMwdTdlVHbTd/VC5ZUQh1bXhDeHkFR31YfQ99WEsPaG4NFlZpc19vV2QSAWRjUHdTfAB4dXAGeXZ2X351cVd7XBVWflxvB1QjWhs5ZQ8JWW4AWHxbWk55dn1Wf3J5EVRuVVwVHRtaU0YMVE8RVExBUFUWDxNRUlBUR0QeFFwJX1cMDxULG1ZOC0NMC1gUFRpaXlBQEw8WAFNWVwEGW1cCAwRQAVIJUAEGUAEAVQUMCAgHAlFVBwYWHhYDSkZRFFdFOghZEwMDG0ZCEBpcQ01nTVhZUBMPBBgQVwpdRV05RVMHQw0BFRFCFFINDlJfV2dXRFkXCwUYFltaEkBZGlwQFElDQlhdEQ1UGkAWQFNLUV0TDhdQUVldXBZKEFRZD1ZDOgJYRFdHFV4GTkFEU1hISW5EWURSXVptWxZXWBpcAxpHEV5VGwkCSBQLEGxZVVwbCwQZE0BEU0BVAldpSxJbWwBDDQEVEUIUURACV1NmXU1YWVATDwQYEEcSU0JNFRAMVE0VWEpsUwFaQFkDGhtRSm5HTF8XDgQeFgdWUmcSW1sAQw0ADAYGVg5VWwMOFRpMQVBURVBrQFtZAxAMCVEGA1ZXDgYNBRtGRgoTbFdVVFZGa0ZUR0JdUVE5XUUaXBBTHCtdUwthWy1cDRt/dXNMWgNXXH5cfldWYD8Gb0IkUVIxO1x/fnlUAGI3Gn5Me1tdZW0EaF92V1ZgBUt4fA5RUjE4BHxUfl4odSgXUARaQHBbW1xpfWEFfGA3AG5wMwF4MSgDaXFmBClcM1drfmwKd0t+X2l9YQZ8YCxfbnAzAHsfIE9pcWYGKkwrV2t+bAtjZn5YaX1hBnxZPwJucDMAeDIoQHhQRF4GbiwNfl9wDnBbTFhTBwEEe3MoRFR/X0tmNgVOa25iWTRaGlJ9YnQIdwBMBH8HYlx8BR4DeFIFRWwjGQZ/bXlfKWAaUn1id1RjXUwEfwdiXn1yHgN5fzBabwkZBn9+ZV89WhpSfWJ3U3cATAR8T3VDf14eUXoKPERUCzAceFcDCkZL', 'php', '', 'cn', '0', '1765331696');
INSERT INTO `cms_config` VALUES ('352', 'web_status_mode', '0', 'web', '', 'cn', '0', '1667183867');
INSERT INTO `cms_config` VALUES ('353', 'web_status_text', '网站暂时关闭，维护中……', 'web', '', 'cn', '0', '1667183867');
INSERT INTO `cms_config` VALUES ('273', 'php_servicemeal', '5', 'php', '', 'cn', '0', '1765331696');
INSERT INTO `cms_config` VALUES ('274', 'php_servicecode', '4ab130c105a16c0c6bfccf350166dd22', 'php', '', 'cn', '0', '1741228771');
INSERT INTO `cms_config` VALUES ('278', 'syn_admin_logic_check_oneself', '1', 'syn', '', 'cn', '0', '1610334638');
INSERT INTO `cms_config` VALUES ('279', 'syn_admin_logic_links_group', '1', 'syn', '', 'cn', '0', '1610334638');
INSERT INTO `cms_config` VALUES ('280', 'sms_type', '1', 'sms', '', 'cn', '0', '1610334638');
INSERT INTO `cms_config` VALUES ('282', 'syn_admin_logic_1608189503', '1', 'syn', '', 'cn', '0', '1610334638');
INSERT INTO `cms_config` VALUES ('306', 'download_select_servername', 'a:6:{i:0;s:12:\"立即下载\";i:1;s:15:\"本地服务器\";i:2;s:15:\"远程服务器\";i:3;s:12:\"百度网盘\";i:4;s:15:\"七牛云存储\";i:5;s:12:\"腾讯网盘\";}', 'download', '', 'cn', '0', '1610439420');
INSERT INTO `cms_config` VALUES ('285', 'syn_admin_logic_1608191377', '1', 'syn', '', 'cn', '0', '1610334638');
INSERT INTO `cms_config` VALUES ('286', 'system_paginate_pagesize', '100', 'system', '', 'cn', '0', '1616490137');
INSERT INTO `cms_config` VALUES ('287', 'web_theme_color', '#3398cc', 'web', '', 'cn', '0', '1610357887');
INSERT INTO `cms_config` VALUES ('288', 'web_assist_color', '#2189be', 'web', '', 'cn', '0', '1610357887');
INSERT INTO `cms_config` VALUES ('294', 'syn_admin_logic_1609039608', '1', 'syn', '', 'cn', '0', '1610334638');
INSERT INTO `cms_config` VALUES ('295', 'syn_admin_logic_1609291091', '1', 'syn', '', 'cn', '0', '1610334638');
INSERT INTO `cms_config` VALUES ('296', 'admin_logic_1610086647', '1', 'syn', '', 'cn', '0', '1610334638');
INSERT INTO `cms_config` VALUES ('307', 'web_mobile_domain_open', '0', 'web', '', 'cn', '0', '1610585089');
INSERT INTO `cms_config` VALUES ('308', 'web_login_expiretime', '3600', 'web', '', 'cn', '0', '1610585089');
INSERT INTO `cms_config` VALUES ('309', 'web_tpl_theme', '', 'web', '', 'cn', '0', '1610585089');
INSERT INTO `cms_config` VALUES ('310', 'syn_admin_logic_video_addfields_2', '1', 'syn', '', 'cn', '0', '1614152870');
INSERT INTO `cms_config` VALUES ('311', 'syn_admin_logic_1608884981_2', '1', 'syn', '', 'cn', '0', '1614152870');
INSERT INTO `cms_config` VALUES ('312', 'basic_indexname', '首页', 'basic', '', 'cn', '0', '1614152874');
INSERT INTO `cms_config` VALUES ('313', 'basic_img_style_wh', '0', 'basic', '', 'cn', '0', '1614152874');
INSERT INTO `cms_config` VALUES ('314', 'max_filesize', '100', 'basic', '', 'cn', '0', '1614152874');
INSERT INTO `cms_config` VALUES ('315', 'max_sizeunit', 'MB', 'basic', '', 'cn', '0', '1614152874');
INSERT INTO `cms_config` VALUES ('316', 'other_arcclick', '500|1000', 'other', '', 'cn', '0', '1614152874');
INSERT INTO `cms_config` VALUES ('317', 'other_arcdownload', '100|500', 'other', '', 'cn', '0', '1614152874');
INSERT INTO `cms_config` VALUES ('318', 'syn_admin_logic_balance_pay', '1', 'syn', '', 'cn', '0', '1616460912');
INSERT INTO `cms_config` VALUES ('319', 'syn_admin_logic_1610086648', '1', 'syn', '', 'cn', '0', '1616460912');
INSERT INTO `cms_config` VALUES ('320', 'syn_admin_logic_1614829120', '1', 'syn', '', 'cn', '0', '1616460912');
INSERT INTO `cms_config` VALUES ('321', 'syn_admin_logic_1616123192', '1', 'syn', '', 'cn', '0', '1616460912');
INSERT INTO `cms_config` VALUES ('322', 'syn_admin_logic_1614829121', '1', 'syn', '', 'cn', '0', '1623322108');
INSERT INTO `cms_config` VALUES ('323', 'syn_admin_logic_ask_answer_like', '1', 'syn', '', 'cn', '0', '1623322108');
INSERT INTO `cms_config` VALUES ('324', 'syn_admin_logic_archives_1618279798', '1', 'syn', '', 'cn', '0', '1623322108');
INSERT INTO `cms_config` VALUES ('325', 'syn_admin_logic_1623036205', '1', 'syn', '', 'cn', '0', '1623322108');
INSERT INTO `cms_config` VALUES ('326', 'admin_logic_1623055490', '1', 'syn', '', 'cn', '0', '1623322108');
INSERT INTO `cms_config` VALUES ('327', 'system_use_language', '0', 'system', '', 'cn', '0', '1623322108');
INSERT INTO `cms_config` VALUES ('328', 'admin_logic_1623133485', '1', 'syn', '', 'cn', '0', '1623322108');
INSERT INTO `cms_config` VALUES ('329', 'recycle_switch', '1', 'web', '', 'cn', '0', '1623809302');
INSERT INTO `cms_config` VALUES ('330', 'seo_rewrite_view_format', '1', 'seo', '', 'cn', '0', '1623809665');
INSERT INTO `cms_config` VALUES ('332', 'seo_uphtml_after_home', '0', 'seo', '', 'cn', '0', '1641949807');
INSERT INTO `cms_config` VALUES ('333', 'seo_uphtml_after_channel', '1', 'seo', '', 'cn', '0', '1641949807');
INSERT INTO `cms_config` VALUES ('334', 'seo_uphtml_after_pernext', '1', 'seo', '', 'cn', '0', '1641949807');
INSERT INTO `cms_config` VALUES ('335', 'seo_html_templet', 'index.htm', 'seo', '', 'cn', '0', '1641949807');
INSERT INTO `cms_config` VALUES ('336', 'seo_html_position', '../index.html', 'seo', '', 'cn', '0', '1641949807');
INSERT INTO `cms_config` VALUES ('337', 'seo_showmod', '1', 'seo', '', 'cn', '0', '1641949807');
INSERT INTO `cms_config` VALUES ('338', 'seo_maxpagesize', '50', 'seo', '', 'cn', '0', '1641949807');
INSERT INTO `cms_config` VALUES ('339', 'seo_upnext', '1', 'seo', '', 'cn', '0', '1641949807');
INSERT INTO `cms_config` VALUES ('340', 'seo_pagesize', '20', 'seo', '', 'cn', '0', '1641949807');
INSERT INTO `cms_config` VALUES ('341', 'basic_img_auto_wh', '0', 'basic', '', 'cn', '0', '1641949807');
INSERT INTO `cms_config` VALUES ('342', 'basic_img_alt', '0', 'basic', '', 'cn', '0', '1641949807');
INSERT INTO `cms_config` VALUES ('343', 'basic_img_title', '0', 'basic', '', 'cn', '0', '1641949807');
INSERT INTO `cms_config` VALUES ('344', 'system_crypt_auth_code', '$2y$11$43dd7eab6b1e84d79e28f86', 'system', '', 'cn', '0', '1650263716');
INSERT INTO `cms_config` VALUES ('345', 'web_citysite_open', '0', 'web', '', 'cn', '0', '1703142363');
INSERT INTO `cms_config` VALUES ('346', 'admin_logic_1_1648775669', '1', 'syn', '', 'cn', '0', '1650263716');
INSERT INTO `cms_config` VALUES ('347', 'web_stypeid_open', '0', 'web', '', 'cn', '0', '1653359674');
INSERT INTO `cms_config` VALUES ('348', 'web_status_url', '', 'web', '', 'cn', '0', '1653359786');
INSERT INTO `cms_config` VALUES ('349', 'web_status_tpl', 'http://e24.meiz20.hk/public/close.html', 'web', '', 'cn', '0', '1692667955');
INSERT INTO `cms_config` VALUES ('350', 'web_garecordnum', '', 'web', '', 'cn', '0', '1653359786');
INSERT INTO `cms_config` VALUES ('351', 'web_garecordnum_mode', '0', 'web', '', 'cn', '0', '1653359786');
INSERT INTO `cms_config` VALUES ('355', 'editor_select', '1', 'basic', '', 'cn', '0', '1675389691');
INSERT INTO `cms_config` VALUES ('356', 'editor_remote_img_local', '1', 'basic', '', 'cn', '0', '1675389691');
INSERT INTO `cms_config` VALUES ('357', 'editor_img_clear_link', '1', 'basic', '', 'cn', '0', '1675389691');
INSERT INTO `cms_config` VALUES ('358', 'php_allow_service_os', 'eyJjb2RlIjoxLCJtc2ciOiJcdTY4YzBcdTZkNGJcdTUyMzBcdTY1YjBcdTcyNDhcdTY3MmMiLCJtc2cxIjoiXHU1NTQ2XHU3NTI4XHU3MjQ4XHU2NzJjXHU2NTJmXHU2MzAxXHU1NzI4XHU3ZWJmXHU2NmY0XHU2NWIwIiwibXNnMiI6Ijxmb250IGNvbG9yPSdyZWQnPlx1NTM0N1x1N2VhN1x1NjcwZFx1NTJhMVx1NTNlZlx1N2VjOFx1OGVhYlx1NGVhYlx1NTNkN1x1MzAwMjxcL2ZvbnQ+In0=', 'php', '', 'cn', '0', '1741228771');
INSERT INTO `cms_config` VALUES ('367', 'absolute_path_open', '0', 'web', '', 'cn', '0', '1701048818');
INSERT INTO `cms_config` VALUES ('364', 'php_upgradelist', '', 'php', '', 'cn', '0', '1690161726');
INSERT INTO `cms_config` VALUES ('359', 'php_atqueryrequest', 'eyIwIjp7ImV4cGlyZV90aW1lIjoyNzY0ODAwfSwiMSI6eyJleHBpcmVfdGltZSI6MzQ1NjAwMH0sIjEuNSI6eyJleHBpcmVfdGltZSI6Mjg1MTIwMH0sIjIiOnsiZXhwaXJlX3RpbWUiOjM4MDE2MDB9LCI1Ijp7ImV4cGlyZV90aW1lIjozMDI0MDAwfX0=', 'php', '', 'cn', '0', '1765331696');
INSERT INTO `cms_config` VALUES ('360', 'php_atqueryrequest_time', '0', 'php', '', 'cn', '0', '1765331880');
INSERT INTO `cms_config` VALUES ('361', 'search_tabu_words', '<\r\n>\r\n\"\r\n;\r\n,\r\n@\r\n&\r\n#\r\n\\\r\n*\r\neval\r\ncall_user_func_array\r\nfile_put_contents\r\nphpinfo\r\nshell_exec', 'search', '', 'cn', '0', '1744682574');
INSERT INTO `cms_config` VALUES ('362', 'web_anti_brushing', '1', 'web', '', 'cn', '0', '1686877656');
INSERT INTO `cms_config` VALUES ('363', 'php_atqueryrequest_time2', '0', 'php', '', 'cn', '0', '1765331880');
INSERT INTO `cms_config` VALUES ('365', 'web_theme_styleid', '100', 'web', '', 'cn', '0', '1692667955');
INSERT INTO `cms_config` VALUES ('368', 'seo_uphtml_after_home13', '1', 'seo', '', 'cn', '0', '1703142160');
INSERT INTO `cms_config` VALUES ('369', 'seo_uphtml_after_channel13', '1', 'seo', '', 'cn', '0', '1703142160');
INSERT INTO `cms_config` VALUES ('370', 'seo_uphtml_after_pernext13', '1', 'seo', '', 'cn', '0', '1703142160');
INSERT INTO `cms_config` VALUES ('371', 'seo_uphtml_editafter_home', '0', 'seo', '', 'cn', '0', '1706510953');
INSERT INTO `cms_config` VALUES ('372', 'seo_uphtml_editafter_channel', '0', 'seo', '', 'cn', '0', '1706510953');
INSERT INTO `cms_config` VALUES ('373', 'seo_uphtml_editafter_pernext', '1', 'seo', '', 'cn', '0', '1706510953');
INSERT INTO `cms_config` VALUES ('374', 'wechat_push_notice_open', '0', 'wechat', '', 'cn', '0', '1721637131');
INSERT INTO `cms_config` VALUES ('375', 'web_xss_filter', '', 'web', '', 'cn', '0', '1732860821');
INSERT INTO `cms_config` VALUES ('376', 'web_xss_words', 'union\r\ndelete\r\noutfile\r\nchar\r\nconcat\r\ntruncate\r\ninsert\r\nrevoke\r\ngrant\r\nreplace\r\nrename\r\ndeclare\r\nexec\r\ndelimiter\r\nphar\r\neval\r\nonerror\r\nscript', 'web', '', 'cn', '0', '1732860821');
INSERT INTO `cms_config` VALUES ('377', 'web_anti_words', 'wd', 'web', '', 'cn', '0', '1732860821');
INSERT INTO `cms_config` VALUES ('379', 'intellect_arr', '[\"title\",\"seo_keywords\"]', 'search', '', 'cn', '0', '1756178725');
CREATE TABLE `cms_config_attribute` (
  `attr_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '表单id',
  `inc_type` varchar(20) DEFAULT '' COMMENT '变量分组',
  `type_id` int(11) unsigned DEFAULT '1',
  `attr_name` varchar(60) DEFAULT '' COMMENT '变量标题',
  `attr_var_name` varchar(50) DEFAULT '' COMMENT '变量名',
  `attr_input_type` tinyint(1) unsigned DEFAULT '0' COMMENT ' 0=文本框，1=下拉框，2=多行文本框，3=上传图片',
  `sort_order` int(10) DEFAULT '0' COMMENT '排序号',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`attr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='自定义变量表';
INSERT INTO `cms_config_attribute` VALUES ('1', 'web', '1', '客服电话', 'web_attr_1', '0', '100', 'cn', '1525962574', '1667183895');
INSERT INTO `cms_config_attribute` VALUES ('13', 'web', '1', '手机端LOGO', 'web_attr_13', '3', '100', 'cn', '1563793690', '1609930141');
INSERT INTO `cms_config_attribute` VALUES ('15', 'web', '1', '邮箱地址', 'web_attr_15', '0', '100', 'cn', '1609930141', '1667183901');
INSERT INTO `cms_config_attribute` VALUES ('16', 'web', '1', '公司地址', 'web_attr_16', '0', '100', 'cn', '1609930141', '1609930141');
INSERT INTO `cms_config_attribute` VALUES ('17', 'web', '1', '公众号二维码', 'web_attr_17', '3', '100', 'cn', '1609930141', '1609930141');
CREATE TABLE `cms_config_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) DEFAULT '' COMMENT '分组名称',
  `status` tinyint(1) DEFAULT '1',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `sort_order` int(11) DEFAULT '100' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='自定义变量分组表';
INSERT INTO `cms_config_type` VALUES ('1', '默认分组', '1', 'cn', '1650271499', '1650271499', '100');
CREATE TABLE `cms_country` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '编码',
  `continent` varchar(50) NOT NULL DEFAULT '' COMMENT '所属大洲',
  `sort_order` int(11) unsigned DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态(0:禁用; 1:启用;)',
  `add_time` int(11) unsigned DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=251 DEFAULT CHARSET=utf8 COMMENT='国家列表';
INSERT INTO `cms_country` VALUES ('1', '中国', 'CN', 'AS', '1', '1', '1704038400', '1732173305');
INSERT INTO `cms_country` VALUES ('2', 'Afghanistan', 'AF', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('3', 'Albania', 'AL', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('4', 'Algeria', 'DZ', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('5', 'American Samoa', 'AS', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('6', 'Andorra', 'AD', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('7', 'Angola', 'AO', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('8', 'Anguilla', 'AI', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('9', 'Antarctica', 'AQ', 'AN', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('10', 'Antigua and Barbuda', 'AG', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('11', 'Argentina', 'AR', 'SA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('12', 'Armenia', 'AM', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('13', 'Aruba', 'AW', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('14', 'Australia', 'AU', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('15', 'Austria', 'AT', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('16', 'Azerbaijan', 'AZ', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('17', 'Bahamas', 'BS', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('18', 'Bahrain', 'BH', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('19', 'Bangladesh', 'BD', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('20', 'Barbados', 'BB', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('21', 'Belarus', 'BY', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('22', 'Belgium', 'BE', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('23', 'Belize', 'BZ', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('24', 'Benin', 'BJ', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('25', 'Bermuda', 'BM', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('26', 'Bhutan', 'BT', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('27', 'Bolivia', 'BO', 'SA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('28', 'Bosnia and Herzegovina', 'BA', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('29', 'Botswana', 'BW', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('30', 'Bouvet Island', 'BV', 'AN', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('31', 'Brazil', 'BR', 'SA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('32', 'British Indian Ocean Territory', 'IO', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('33', 'Brunei Darussalam', 'BN', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('34', 'Bulgaria', 'BG', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('35', 'Burkina Faso', 'BF', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('36', 'Burundi', 'BI', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('37', 'Cambodia', 'KH', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('38', 'Cameroon', 'CM', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('39', 'Canada', 'CA', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('40', 'Cape Verde', 'CV', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('41', 'Cayman Islands', 'KY', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('42', 'Central African Republic', 'CF', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('43', 'Chad', 'TD', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('44', 'Chile', 'CL', 'SA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('45', 'Christmas Island', 'CX', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('46', 'Cocos (Keeling) Islands', 'CC', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('47', 'Colombia', 'CO', 'SA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('48', 'Comoros', 'KM', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('49', 'Congo', 'CG', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('50', 'Cook Islands', 'CK', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('51', 'Costa Rica', 'CR', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('52', 'Cote D\'Ivoire', 'CI', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('53', 'Croatia', 'HR', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('54', 'Cuba', 'CU', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('55', 'Cyprus', 'CY', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('56', 'Czech Republic', 'CZ', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('57', 'Denmark', 'DK', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('58', 'Djibouti', 'DJ', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('59', 'Dominica', 'DM', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('60', 'Dominican Republic', 'DO', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('61', 'East Timor', 'TL', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('62', 'Ecuador', 'EC', 'SA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('63', 'Egypt', 'EG', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('64', 'El Salvador', 'SV', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('65', 'Equatorial Guinea', 'GQ', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('66', 'Eritrea', 'ER', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('67', 'Estonia', 'EE', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('68', 'Ethiopia', 'ET', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('69', 'Falkland Islands (Malvinas)', 'FK', 'SA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('70', 'Faroe Islands', 'FO', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('71', 'Fiji', 'FJ', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('72', 'Finland', 'FI', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('73', 'France, Metropolitan', 'FR', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('74', 'French Guiana', 'GF', 'SA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('75', 'French Polynesia', 'PF', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('76', 'French Southern Territories', 'TF', 'AN', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('77', 'Gabon', 'GA', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('78', 'Gambia', 'GM', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('79', 'Georgia', 'GE', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('80', 'Germany', 'DE', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('81', 'Ghana', 'GH', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('82', 'Gibraltar', 'GI', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('83', 'Greece', 'GR', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('84', 'Greenland', 'GL', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('85', 'Grenada', 'GD', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('86', 'Guadeloupe', 'GP', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('87', 'Guam', 'GU', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('88', 'Guatemala', 'GT', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('89', 'Guinea', 'GN', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('90', 'Guinea-Bissau', 'GW', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('91', 'Guyana', 'GY', 'SA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('92', 'Haiti', 'HT', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('93', 'Heard and Mc Donald Islands', 'HM', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('94', 'Honduras', 'HN', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('95', 'Hungary', 'HU', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('96', 'Iceland', 'IS', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('97', 'India', 'IN', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('98', 'Indonesia', 'ID', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('99', 'Iran (Islamic Republic of)', 'IR', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('100', 'Iraq', 'IQ', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('101', 'Ireland', 'IE', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('102', 'Israel', 'IL', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('103', 'Italy', 'IT', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('104', 'Jamaica', 'JM', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('105', 'Japan', 'JP', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('106', 'Jordan', 'JO', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('107', 'Kazakhstan', 'KZ', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('108', 'Kenya', 'KE', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('109', 'Kiribati', 'KI', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('110', 'North Korea', 'KP', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('111', 'South Korea', 'KR', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('112', 'Kuwait', 'KW', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('113', 'Kyrgyzstan', 'KG', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('114', 'Lao People\'s Democratic Republic', 'LA', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('115', 'Latvia', 'LV', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('116', 'Lebanon', 'LB', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('117', 'Lesotho', 'LS', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('118', 'Liberia', 'LR', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('119', 'Libyan Arab Jamahiriya', 'LY', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('120', 'Liechtenstein', 'LI', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('121', 'Lithuania', 'LT', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('122', 'Luxembourg', 'LU', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('123', 'FYROM', 'MK', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('124', 'Madagascar', 'MG', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('125', 'Malawi', 'MW', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('126', 'Malaysia', 'MY', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('127', 'Maldives', 'MV', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('128', 'Mali', 'ML', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('129', 'Malta', 'MT', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('130', 'Marshall Islands', 'MH', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('131', 'Martinique', 'MQ', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('132', 'Mauritania', 'MR', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('133', 'Mauritius', 'MU', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('134', 'Mayotte', 'YT', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('135', 'Mexico', 'MX', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('136', 'Micronesia, Federated States of', 'FM', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('137', 'Moldova, Republic of', 'MD', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('138', 'Monaco', 'MC', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('139', 'Mongolia', 'MN', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('140', 'Montserrat', 'MS', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('141', 'Morocco', 'MA', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('142', 'Mozambique', 'MZ', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('143', 'Myanmar', 'MM', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('144', 'Namibia', 'NA', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('145', 'Nauru', 'NR', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('146', 'Nepal', 'NP', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('147', 'Netherlands', 'NL', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('148', 'Netherlands Antilles', 'AN', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('149', 'New Caledonia', 'NC', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('150', 'New Zealand', 'NZ', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('151', 'Nicaragua', 'NI', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('152', 'Niger', 'NE', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('153', 'Nigeria', 'NG', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('154', 'Niue', 'NU', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('155', 'Norfolk Island', 'NF', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('156', 'Northern Mariana Islands', 'MP', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('157', 'Norway', 'NO', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('158', 'Oman', 'OM', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('159', 'Pakistan', 'PK', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('160', 'Palau', 'PW', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('161', 'Panama', 'PA', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('162', 'Papua New Guinea', 'PG', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('163', 'Paraguay', 'PY', 'SA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('164', 'Peru', 'PE', 'SA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('165', 'Philippines', 'PH', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('166', 'Pitcairn', 'PN', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('167', 'Poland', 'PL', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('168', 'Portugal', 'PT', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('169', 'Puerto Rico', 'PR', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('170', 'Qatar', 'QA', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('171', 'Reunion', 'RE', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('172', 'Romania', 'RO', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('173', 'Russian Federation', 'RU', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('174', 'Rwanda', 'RW', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('175', 'Saint Kitts and Nevis', 'KN', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('176', 'Saint Lucia', 'LC', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('177', 'Saint Vincent and the Grenadines', 'VC', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('178', 'Samoa', 'WS', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('179', 'San Marino', 'SM', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('180', 'Sao Tome and Principe', 'ST', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('181', 'Saudi Arabia', 'SA', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('182', 'Senegal', 'SN', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('183', 'Seychelles', 'SC', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('184', 'Sierra Leone', 'SL', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('185', 'Singapore', 'SG', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('186', 'Slovak Republic', 'SK', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('187', 'Slovenia', 'SI', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('188', 'Solomon Islands', 'SB', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('189', 'Somalia', 'SO', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('190', 'South Africa', 'ZA', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('191', 'South Georgia &amp; South Sandwich Islands', 'GS', 'SA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('192', 'Spain', 'ES', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('193', 'Sri Lanka', 'LK', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('194', 'St. Helena', 'SH', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('195', 'St. Pierre and Miquelon', 'PM', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('196', 'Sudan', 'SD', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('197', 'Suriname', 'SR', 'SA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('198', 'Svalbard and Jan Mayen Islands', 'SJ', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('199', 'Swaziland', 'SZ', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('200', 'Sweden', 'SE', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('201', 'Switzerland', 'CH', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('202', 'Syrian Arab Republic', 'SY', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('203', 'Tajikistan', 'TJ', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('204', 'Tanzania, United Republic of', 'TZ', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('205', 'Thailand', 'TH', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('206', 'Togo', 'TG', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('207', 'Tokelau', 'TK', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('208', 'Tonga', 'TO', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('209', 'Trinidad and Tobago', 'TT', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('210', 'Tunisia', 'TN', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('211', 'Turkey', 'TR', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('212', 'Turkmenistan', 'TM', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('213', 'Turks and Caicos Islands', 'TC', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('214', 'Tuvalu', 'TV', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('215', 'Uganda', 'UG', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('216', 'Ukraine', 'UA', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('217', 'United Arab Emirates', 'AE', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('218', 'United Kingdom', 'GB', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('219', 'United States', 'US', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('220', 'United States Minor Outlying Islands', 'UM', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('221', 'Uruguay', 'UY', 'SA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('222', 'Uzbekistan', 'UZ', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('223', 'Vanuatu', 'VU', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('224', 'Vatican City State (Holy See)', 'VA', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('225', 'Venezuela', 'VE', 'SA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('226', 'Viet Nam', 'VN', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('227', 'Virgin Islands (British)', 'VG', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('228', 'Virgin Islands (U.S.)', 'VI', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('229', 'Wallis and Futuna Islands', 'WF', 'OA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('230', 'Western Sahara', 'EH', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('231', 'Yemen', 'YE', 'AS', '100', '1', '1704038400', '1732173279');
INSERT INTO `cms_country` VALUES ('232', 'Democratic Republic of Congo', 'CD', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('233', 'Zambia', 'ZM', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('234', 'Zimbabwe', 'ZW', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('235', 'Montenegro', 'ME', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('236', 'Serbia', 'RS', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('237', 'Aaland Islands', 'AX', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('238', 'Bonaire, Sint Eustatius and Saba', 'BQ', 'SA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('239', 'Curacao', 'CW', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('240', 'Palestinian Territory, Occupied', 'PS', 'AS', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('241', 'South Sudan', 'SS', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('242', 'St. Barthelemy', 'BL', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('243', 'St. Martin (French part)', 'MF', 'NA', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('244', 'Canary Islands', 'IC', 'AF', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('245', 'Ascension Island (British)', 'AC', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('246', 'Kosovo, Republic of', 'XK', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('247', 'Isle of Man', 'IM', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('248', 'Tristan da Cunha', 'TA', 'NULL', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('249', 'Guernsey', 'GG', 'EU', '100', '1', '1704038400', '1704038400');
INSERT INTO `cms_country` VALUES ('250', 'Jersey', 'JE', 'EU', '100', '1', '1704038400', '1704038400');
CREATE TABLE `cms_ddos_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `md5key` varchar(50) DEFAULT '' COMMENT 'md5值',
  `file_name` text COMMENT '文件名',
  `file_num` int(10) DEFAULT '0' COMMENT '已扫描数',
  `file_total` int(10) DEFAULT '0' COMMENT '总文件数',
  `file_doubt_total` int(10) DEFAULT '0' COMMENT '可疑恶意文件数',
  `file_excess` int(5) DEFAULT '0' COMMENT '大于0表示多余，2表示官方目录的多余自行修复',
  `file_grade` int(10) DEFAULT '0' COMMENT '文件级别，0=正常，100=异常文件，200=疑似木马，970=低危，980=中危，990=高危',
  `html` longtext,
  `admin_id` int(11) DEFAULT '0',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='ddos查杀进度记录表';
CREATE TABLE `cms_ddos_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT '' COMMENT '配置的key键名',
  `value` longtext,
  `inc_type` varchar(50) DEFAULT 'ddos',
  `admin_id` int(11) DEFAULT '0',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='ddos业务存储表';
CREATE TABLE `cms_ddos_whitelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `md5key` varchar(50) DEFAULT '' COMMENT 'md5值',
  `file_name` text COMMENT '文件名',
  `admin_id` int(11) DEFAULT '0',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ddos扫描白名单列表';
CREATE TABLE `cms_discount_active` (
  `active_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '活动会场ID',
  `active_name` varchar(255) DEFAULT '' COMMENT '活动名称',
  `start_date` int(11) unsigned DEFAULT '0' COMMENT '活动开始时间',
  `end_date` int(11) DEFAULT '0' COMMENT '活动结束时间',
  `limit_type` tinyint(3) unsigned DEFAULT '1' COMMENT '限购类型 1-不限购,2-活动期内每人最多购买n件,3-活动期内每人每天最多购买n件',
  `limit` int(11) unsigned DEFAULT '0' COMMENT '限购数量',
  `preheat` tinyint(3) DEFAULT '0' COMMENT '是否开启预热 0-关闭 1-开启',
  `preheat_time` int(11) DEFAULT '0' COMMENT '预热时间,不能大于开启时间',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '活动状态(0禁用 1启用)',
  `is_del` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`active_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='限时折扣-活动会场表';
CREATE TABLE `cms_discount_active_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `active_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动ID',
  `aid` int(11) NOT NULL DEFAULT '0' COMMENT '文档id',
  `discount_goods_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '秒杀商品ID',
  `sales_actual` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '实际销量',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='限时折扣-活动会场与商品关联表';
CREATE TABLE `cms_discount_goods` (
  `discount_gid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '限时折扣商品ID',
  `aid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID->aid',
  `discount_stock` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '限时折扣商品库存总量',
  `discount_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '限时折扣价格',
  `sales` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '累积销量',
  `virtual_sales` int(11) NOT NULL DEFAULT '0' COMMENT '虚拟销量',
  `sort_order` int(11) unsigned NOT NULL DEFAULT '100' COMMENT '商品排序(数字越小越靠前)',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '商品状态(0下架 1上架)',
  `is_del` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `is_sku` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-多规格商品',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`discount_gid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='限时折扣-商品表';
CREATE TABLE `cms_diyminipro` (
  `mini_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '模板ID',
  `categoryid` int(11) NOT NULL DEFAULT '0' COMMENT '分类ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '模板标题',
  `litpic` varchar(250) NOT NULL DEFAULT '' COMMENT '封面图',
  `component` text NOT NULL COMMENT '组件库',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：4=审核中，5=发布',
  `cloud_id` int(10) NOT NULL DEFAULT '0' COMMENT '云ID',
  `config` text NOT NULL COMMENT '相关序列化信息',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '伪删除，1=是，0=否',
  `lang` varchar(10) NOT NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`mini_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信小程序记录表';
CREATE TABLE `cms_diyminipro_page` (
  `page_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '页面id',
  `page_type` tinyint(3) NOT NULL DEFAULT '-1' COMMENT '页面类型(1首页 -1自定义页)',
  `page_name` varchar(255) NOT NULL DEFAULT '' COMMENT '页面名称',
  `page_data` longtext NOT NULL COMMENT '页面数据',
  `mini_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '微信小程序id',
  `is_home` tinyint(1) NOT NULL DEFAULT '0' COMMENT '设为首页：0=否，1=是',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '系统内置',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示：1=显示，0=隐藏',
  `is_del` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '伪删除，1=是，0=否',
  `lang` varchar(10) NOT NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`page_id`),
  KEY `mini_id` (`mini_id`,`lang`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信小程序diy页面表';
CREATE TABLE `cms_diyminipro_setting` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '页面组',
  `value` text NOT NULL COMMENT '组装之后的值',
  `mini_id` int(11) NOT NULL DEFAULT '0' COMMENT '小程序ID',
  `lang` varchar(10) NOT NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `type` (`name`) USING BTREE,
  KEY `mini_id` (`mini_id`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信小程序多功能配置表';
CREATE TABLE `cms_download_attr_field` (
  `field_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `field_name` varchar(32) DEFAULT '' COMMENT '字段名称',
  `field_title` varchar(32) DEFAULT '' COMMENT '字段标题',
  `field_use` tinyint(1) DEFAULT '0' COMMENT '字段是否使用，0未使用，1为使用',
  `sort_order` smallint(5) DEFAULT '0' COMMENT '排序',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned DEFAULT '0' COMMENT '上传时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`field_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='上传文件属性表';
INSERT INTO `cms_download_attr_field` VALUES ('1', 'extract_code', '提取码', '1', '1', 'cn', '1561001807', '1561024954');
INSERT INTO `cms_download_attr_field` VALUES ('2', 'server_name', '服务器名称', '1', '2', 'cn', '1561001807', '1561078673');
CREATE TABLE `cms_download_content` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) DEFAULT '0' COMMENT '文档ID',
  `content` longtext COMMENT '内容详情',
  `content_ey_m` longtext COMMENT '手机端内容详情',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `news_id` (`aid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='下载附加表';
CREATE TABLE `cms_download_file` (
  `file_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `aid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `title` varchar(200) DEFAULT '' COMMENT '产品标题',
  `file_url` varchar(255) DEFAULT '' COMMENT '文件存储路径',
  `extract_code` varchar(20) DEFAULT '' COMMENT '文件提取码',
  `file_size` varchar(255) DEFAULT '' COMMENT '文件大小',
  `file_ext` varchar(50) DEFAULT '' COMMENT '文件后缀名',
  `file_name` varchar(200) DEFAULT '' COMMENT '文件名',
  `server_name` varchar(200) DEFAULT '' COMMENT '服务器名称',
  `file_mime` varchar(200) DEFAULT '' COMMENT '文件类型',
  `uhash` varchar(200) DEFAULT '' COMMENT '自定义的一种加密方式，用于文件下载权限验证',
  `md5file` varchar(200) DEFAULT '' COMMENT 'md5_file加密，可以检测上传/下载的文件包是否损坏',
  `is_remote` tinyint(1) DEFAULT '0' COMMENT '是否远程',
  `downcount` int(10) DEFAULT '0' COMMENT '下载次数',
  `sort_order` smallint(5) DEFAULT '0' COMMENT '排序',
  `add_time` int(10) unsigned DEFAULT '0' COMMENT '上传时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`file_id`),
  KEY `arcid` (`aid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8 COMMENT='下载附件表';
CREATE TABLE `cms_download_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `users_id` int(10) DEFAULT '0' COMMENT '用户ID',
  `aid` int(10) DEFAULT '0' COMMENT '文档ID',
  `file_id` int(10) DEFAULT '0' COMMENT '附件ID',
  `ip` varchar(20) DEFAULT '' COMMENT 'ip',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '编辑时间',
  PRIMARY KEY (`log_id`),
  KEY `file_id` (`file_id`,`aid`,`users_id`) USING BTREE,
  KEY `aid` (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='下载记录表';
CREATE TABLE `cms_download_order` (
  `order_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章订单ID',
  `order_code` varchar(50) NOT NULL DEFAULT '' COMMENT '媒体订单编号',
  `users_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `order_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态：0未付款，1已付款',
  `order_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单应付总金额',
  `pay_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
  `pay_name` varchar(20) NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `wechat_pay_type` varchar(20) NOT NULL DEFAULT '' COMMENT '微信支付时，标记使用的支付类型（扫码支付，微信内部，微信H5页面）',
  `pay_details` text COMMENT '支付时返回的数据，以serialize序列化后存入，用于后续查询。',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '视频文档ID',
  `product_name` varchar(100) DEFAULT '' COMMENT '视频文档名称',
  `product_litpic` varchar(500) DEFAULT '' COMMENT '视频文档封面图片',
  `lang` varchar(30) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned DEFAULT '0' COMMENT '下单时间',
  `update_time` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_code` (`order_code`) USING BTREE,
  KEY `users_id` (`users_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
CREATE TABLE `cms_faq_asklist` (
  `asklist_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '问题ID',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '分组ID',
  `asklist_title` varchar(255) NOT NULL DEFAULT '' COMMENT '问题标题',
  `asklist_content` text NOT NULL COMMENT '问题回答',
  `sort_order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `lang` varchar(50) NOT NULL DEFAULT 'cn' COMMENT '多语言',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`asklist_id`),
  KEY `group_id` (`group_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='常见问题问答列表';
CREATE TABLE `cms_faq_group` (
  `group_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '分组ID',
  `group_title` varchar(60) NOT NULL DEFAULT '' COMMENT '分组标题',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '启用状态(0:否; 1:是;)',
  `lang` varchar(50) NOT NULL DEFAULT 'cn' COMMENT '多语言',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='常见问题分组列表';
CREATE TABLE `cms_field_custom_param` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `field_id` int(11) DEFAULT '0',
  `channel_id` int(11) DEFAULT '0',
  `dtype` varchar(40) DEFAULT '',
  `name` varchar(60) NOT NULL DEFAULT '' COMMENT '名称',
  `dfvalue` varchar(500) DEFAULT '',
  `sort_order` int(11) unsigned DEFAULT '0' COMMENT '排序',
  `add_time` int(11) unsigned DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `field_id` (`field_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='自定义字段-多选项[新]';
CREATE TABLE `cms_field_type` (
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '字段类型',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '中文类型名',
  `ifoption` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要设置选项',
  `sort_order` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='字段类型表';
INSERT INTO `cms_field_type` VALUES ('text', '单行文本', '0', '110', '1532485708', '1532485708');
INSERT INTO `cms_field_type` VALUES ('checkbox', '多选项[旧]', '1', '151', '1532485708', '1532485708');
INSERT INTO `cms_field_type` VALUES ('multitext', '多行文本', '0', '120', '1532485708', '1532485708');
INSERT INTO `cms_field_type` VALUES ('radio', '单选项[旧]', '1', '141', '1532485708', '1532485708');
INSERT INTO `cms_field_type` VALUES ('switch', '开关', '0', '230', '1532485708', '1532485708');
INSERT INTO `cms_field_type` VALUES ('select', '下拉框', '1', '161', '1532485708', '1532485708');
INSERT INTO `cms_field_type` VALUES ('img', '单张图', '0', '200', '1532485708', '1532485708');
INSERT INTO `cms_field_type` VALUES ('int', '整数类型', '0', '170', '1532485708', '1532485708');
INSERT INTO `cms_field_type` VALUES ('datetime', '日期和时间', '0', '220', '1532485708', '1532485708');
INSERT INTO `cms_field_type` VALUES ('htmltext', 'HTML文本', '0', '130', '1532485708', '1532485708');
INSERT INTO `cms_field_type` VALUES ('imgs', '多张图', '0', '210', '1532485708', '1532485708');
INSERT INTO `cms_field_type` VALUES ('decimal', '金额类型', '0', '190', '1532485708', '1532485708');
INSERT INTO `cms_field_type` VALUES ('float', '小数类型', '0', '180', '1532485708', '1532485708');
INSERT INTO `cms_field_type` VALUES ('region', '区域类型', '1', '169', '1532485708', '1532485708');
INSERT INTO `cms_field_type` VALUES ('file', '附件类型', '0', '210', '1532485708', '1532485708');
INSERT INTO `cms_field_type` VALUES ('media', '多媒体类型', '0', '210', '1532485708', '1532485708');
INSERT INTO `cms_field_type` VALUES ('checkboxs', '多选项', '0', '150', '1532485708', '1532485708');
INSERT INTO `cms_field_type` VALUES ('radios', '单选项', '0', '140', '1532485708', '1532485708');
CREATE TABLE `cms_foreign_pack` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `type` int(4) DEFAULT '0' COMMENT '分类：1=列表，2=留言',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '变量名',
  `value` text NOT NULL COMMENT '变量值',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `sort_order` int(10) DEFAULT '0' COMMENT '排序号',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=utf8 COMMENT='外贸助手语言包变量';
INSERT INTO `cms_foreign_pack` VALUES ('1', '1', 'page1', '首页', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('2', '1', 'page2', '上一页', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('3', '1', 'page3', '下一页', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('4', '1', 'page4', '末页', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('5', '1', 'page5', '共<strong>%s</strong>页 <strong>%s</strong>条', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('6', '1', 'page1', 'Home', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('7', '1', 'page2', 'Pre', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('8', '1', 'page3', 'Next', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('9', '1', 'page4', 'Last', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('10', '1', 'page5', 'Road <strong>%s</strong> page <strong>%s</strong> strip', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('11', '2', 'gbook1', '操作成功', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('12', '2', 'gbook1', 'success', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('14', '2', 'gbook2', 'The same IP cannot be submitted repeatedly within %s seconds!', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('13', '2', 'gbook2', '同一个IP在%s秒之内不能重复提交！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('15', '2', 'gbook3', '%s不能为空！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('16', '2', 'gbook3', '%s Cannot be empty!', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('17', '2', 'gbook4', '%s格式不正确！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('18', '2', 'gbook4', '%s Incorrect format!', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('19', '2', 'gbook5', '图片验证码不能为空！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('20', '2', 'gbook5', 'Picture verification code cannot be empty!', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('21', '2', 'gbook6', '图片验证码不正确！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('22', '2', 'gbook6', 'The picture verification code is incorrect!', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('23', '2', 'gbook7', '请输入手机号码！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('24', '2', 'gbook7', 'Please enter your mobile number!', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('25', '2', 'gbook8', '手机号码和手机验证码不一致，请重新输入！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('26', '2', 'gbook8', 'Mobile phone number and mobile phone verification code are inconsistent, please re-enter!', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('27', '2', 'gbook9', '手机验证码已被使用或超时，请重新发送！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('28', '2', 'gbook9', 'The mobile phone verification code has been used or timed out. Please resend it!', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('29', '2', 'gbook10', '请输入手机验证码！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('30', '2', 'gbook10', 'Please enter the mobile phone verification code!', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('31', '2', 'gbook11', '表单缺少标签属性{$field.hidden}', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('32', '2', 'gbook11', 'The form is missing label attribute {$field.hidden}', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('33', '2', 'gbook12', '页面自动 %s跳转%s 等待时间：', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('34', '2', 'gbook12', 'Page automatic %sjump%s waiting time：', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('35', '2', 'gbook13', '%s至少选择一项！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('36', '2', 'gbook13', 'Select at least one item to %s', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('37', '2', 'gbook14', '请选择%s', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('38', '2', 'gbook14', 'Please select %s', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('39', '2', 'gbook15', '请输入正确的手机号码！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('40', '2', 'gbook15', 'Please enter the correct mobile number！', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('41', '2', 'gbook16', '图片验证码', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('42', '2', 'gbook16', 'Picture verification code', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('43', '2', 'gbook17', '手机验证码', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('44', '2', 'gbook17', 'Mobile verification code', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('45', '2', 'gbook18', '获取验证码', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('46', '2', 'gbook18', 'Get verification code', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('47', '2', 'gbook19', '看不清？点击更换验证码', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('48', '2', 'gbook19', 'Can\'t see clearly? Click to change the verification code', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('49', '2', 'gbook20', '看不清？%s点击更换%s', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('50', '2', 'gbook20', 'Can\'t see clearly? %sClick to replace%s', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('51', '1', 'page6', '第%s页', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('52', '1', 'page6', '%s', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('53', '3', 'system1', '图', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('54', '3', 'system1', 'pic', 'en', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('55', '3', 'system2', '确定', 'cn', '100', '1543890216', '1704164971');
INSERT INTO `cms_foreign_pack` VALUES ('56', '3', 'system2', 'ok', 'en', '100', '1543890216', '1706859563');
INSERT INTO `cms_foreign_pack` VALUES ('57', '3', 'system3', '取消', 'cn', '100', '1543890216', '1704164971');
INSERT INTO `cms_foreign_pack` VALUES ('58', '3', 'system3', 'cancel', 'en', '100', '1543890216', '1704164971');
INSERT INTO `cms_foreign_pack` VALUES ('59', '4', 'users1', '您的购物车还没有商品！', 'cn', '100', '1543890216', '1704164971');
INSERT INTO `cms_foreign_pack` VALUES ('60', '4', 'users1', 'Your shopping cart doesn\'t have any products yet!', 'en', '100', '1543890216', '1704164971');
INSERT INTO `cms_foreign_pack` VALUES ('61', '3', 'system4', '提示', 'cn', '100', '1543890216', '1704164971');
INSERT INTO `cms_foreign_pack` VALUES ('62', '3', 'system4', 'prompt', 'en', '100', '1543890216', '1704164971');
INSERT INTO `cms_foreign_pack` VALUES ('63', '4', 'users2', '%s不能为空！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('64', '4', 'users2', '%s cannot be empty!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('65', '4', 'users3', '%s格式不正确！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('66', '4', 'users3', '%s Incorrect format!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('67', '4', 'users4', '邮箱验证码已被使用或超时，请重新发送！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('68', '4', 'users4', 'The email verification code has been used or timed out. Please resend it!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('69', '4', 'users5', '邮箱验证码不正确，请重新输入！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('70', '4', 'users5', 'The email verification code is incorrect, please re-enter!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('71', '4', 'users6', '短信验证码不正确，请重新输入！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('72', '4', 'users6', 'The SMS verification code is incorrect, please re-enter!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('73', '4', 'users7', '%s已存在！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('74', '4', 'users7', '%s already exists!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('75', '3', 'system5', '是', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('76', '3', 'system5', 'yes', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('77', '3', 'system6', '否', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('78', '3', 'system6', 'no', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('79', '4', 'users8', '签到成功', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('80', '4', 'users8', 'Successful check-in', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('81', '4', 'users9', '今日已签过到', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('82', '4', 'users9', 'Signed in today', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('83', '3', 'system7', '请至少选择一项！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('84', '3', 'system7', 'Please select at least one item!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('85', '4', 'users10', '是否删除该收藏？', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('86', '4', 'users10', 'Do you want to delete this collection?', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('87', '4', 'users11', '确认批量删除收藏？', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('88', '4', 'users11', 'Confirm bulk deletion of favorites?', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('89', '3', 'system8', '正在处理', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('90', '3', 'system8', 'Processing', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('91', '3', 'system9', '请勿刷新页面', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('92', '3', 'system9', 'Do not refresh', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('93', '4', 'users12', '每日签到', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('94', '4', 'users12', 'Daily Attendance', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('95', '3', 'system10', '上传成功', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('96', '3', 'system10', 'Upload successful', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('97', '4', 'users13', '充值金额不能为空！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('98', '4', 'users13', 'Recharge amount cannot be empty!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('99', '4', 'users14', '请输入正确的充值金额！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('100', '4', 'users14', 'Please enter the correct recharge amount!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('101', '4', 'users15', '请选择支付方式！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('102', '4', 'users15', 'Please choose a payment method!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('103', '4', 'users16', '用户名不能为空！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('104', '4', 'users16', 'The username cannot be empty!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('105', '4', 'users17', '用户名不正确！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('106', '4', 'users17', 'The username is incorrect!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('107', '4', 'users18', '密码不能为空！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('108', '4', 'users18', 'Password cannot be empty!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('109', '4', 'users19', '图片验证码不能为空！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('110', '4', 'users19', 'The image verification code cannot be empty!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('111', '4', 'users20', '图片验证码错误', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('112', '4', 'users20', 'Image verification code error', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('113', '4', 'users21', '前台禁止管理员登录！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('114', '4', 'users21', 'The front desk prohibits administrators from logging in!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('115', '4', 'users22', '该会员尚未激活，请联系管理员！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('116', '4', 'users22', 'This member has not been activated yet. Please contact the administrator!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('117', '4', 'users23', '管理员审核中，请稍等！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('118', '4', 'users23', 'Administrator review in progress, please wait!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('119', '4', 'users24', '登录成功', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('120', '4', 'users24', 'Login succeeded', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('121', '4', 'users25', '密码不正确！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('122', '4', 'users25', 'The password is incorrect!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('123', '4', 'users26', '该用户名不存在，请注册！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('124', '4', 'users26', 'The username does not exist, please register!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('125', '4', 'users27', '看不清？点击更换验证码', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('126', '4', 'users27', 'Can\'t see clearly? Click to change the verification code', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('127', '4', 'users28', '手机号码不能为空！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('128', '4', 'users28', 'Mobile phone number cannot be empty!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('129', '4', 'users29', '手机号码格式不正确！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('130', '4', 'users29', 'The phone number format is incorrect!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('131', '4', 'users30', '手机验证码不能为空！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('132', '4', 'users30', 'Mobile verification code cannot be empty!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('133', '4', 'users31', '手机验证码已失效！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('134', '4', 'users31', 'The mobile verification code has expired!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('135', '4', 'users32', '手机号码已经注册！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('136', '4', 'users32', 'The phone number has been registered!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('137', '4', 'users33', '用户名为系统禁止注册！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('138', '4', 'users33', 'The username is prohibited from registration by the system!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('139', '4', 'users34', '请输入2-30位的汉字、英文、数字、下划线等组合', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('140', '4', 'users34', 'Please enter a combination of Chinese characters, English characters, numbers, underscores, etc. that are 2-30 digits long', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('141', '4', 'users35', '登录密码不能为空！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('142', '4', 'users35', 'Login password cannot be empty!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('143', '4', 'users36', '重复密码不能为空！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('144', '4', 'users36', 'The duplicate password cannot be empty!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('145', '4', 'users37', '用户名已存在', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('146', '4', 'users37', 'The username already exists', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('147', '4', 'users38', '两次密码输入不一致！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('148', '4', 'users38', 'The two password inputs are inconsistent!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('149', '4', 'users39', '注册成功，正在跳转中……', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('150', '4', 'users39', 'Registration successful, jumping in progress……', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('151', '4', 'users40', '注册成功，等管理员激活才能登录！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('152', '4', 'users40', 'Registration successful, wait for administrator activation before logging in!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('153', '4', 'users41', '注册成功，请登录！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('154', '4', 'users41', 'Registration successful, please log in!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('155', '3', 'system11', '操作失败', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('156', '3', 'system11', 'Operation failed', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('157', '3', 'system12', '操作成功', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('158', '3', 'system12', 'Operation successful', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('159', '4', 'users42', '昵称不可为纯空格', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('160', '4', 'users42', 'Nicknames cannot be pure spaces', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('161', '4', 'users43', '原密码不能为空！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('162', '4', 'users43', 'The original password cannot be empty!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('163', '4', 'users44', '新密码不能为空！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('164', '4', 'users44', 'The new password cannot be empty!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('165', '4', 'users45', '手机号码不存在，不能找回密码！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('166', '4', 'users45', 'Mobile phone number does not exist, password cannot be retrieved!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('167', '4', 'users46', '手机号码未绑定，不能找回密码！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('168', '4', 'users46', 'Mobile phone number is not bound, password cannot be retrieved!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('169', '4', 'users47', '手机验证码已被使用或超时，请重新发送！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('170', '4', 'users47', 'The mobile verification code has been used or timed out. Please resend it!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('171', '4', 'users48', '晚上好～', 'cn', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('172', '4', 'users48', 'Good evening~', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('173', '4', 'users49', '早上好～', 'cn', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('174', '4', 'users49', 'Good morning~', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('175', '4', 'users50', '下午好～', 'cn', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('176', '4', 'users50', 'Good afternoon~', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('177', '3', 'system13', '含有敏感词（%s），禁止搜索！', 'cn', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('178', '3', 'system13', 'Contains sensitive words (%s), search prohibited!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('179', '3', 'system14', '过度频繁搜索，离解禁还有%s分钟！', 'cn', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('180', '3', 'system14', 'Excessive frequent searches, with %s minutes left before lifting the ban!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('181', '3', 'system15', '关键词不能为空！', 'cn', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('182', '3', 'system15', 'Keywords cannot be empty!', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('183', '4', 'users51', '商品库存仅%s件！', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('184', '4', 'users51', 'The inventory of the product is only %s pieces', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('185', '4', 'users52', '商品数量最少为%s', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_foreign_pack` VALUES ('186', '4', 'users52', 'The minimum quantity of goods is %s', 'en', '100', '1543890216', '1706580800');
INSERT INTO `cms_foreign_pack` VALUES ('187', '2', 'gbook21', '请输入邮箱地址！', 'cn', '100', '1756113758', '1756113758');
INSERT INTO `cms_foreign_pack` VALUES ('188', '2', 'gbook21', 'Please enter your email address!', 'en', '100', '1756113758', '1756113758');
INSERT INTO `cms_foreign_pack` VALUES ('189', '2', 'gbook22', '请输入邮箱验证码！', 'cn', '100', '1756113758', '1756113758');
INSERT INTO `cms_foreign_pack` VALUES ('190', '2', 'gbook22', 'Please enter the email verification code!', 'en', '100', '1756113758', '1756113758');
INSERT INTO `cms_foreign_pack` VALUES ('191', '2', 'gbook23', '请输入正确的邮箱地址！', 'cn', '100', '1756113758', '1756113758');
INSERT INTO `cms_foreign_pack` VALUES ('192', '2', 'gbook23', 'Please enter a valid email address!', 'en', '100', '1756113758', '1756113758');
INSERT INTO `cms_foreign_pack` VALUES ('193', '2', 'gbook24', '邮箱验证码不正确，请重新输入！', 'cn', '100', '1756113758', '1756113758');
INSERT INTO `cms_foreign_pack` VALUES ('194', '2', 'gbook24', 'The email verification code is incorrect, please re-enter!', 'en', '100', '1756113758', '1756113758');
CREATE TABLE `cms_form` (
  `form_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增表单ID',
  `form_name` varchar(255) NOT NULL DEFAULT '' COMMENT '表单名称',
  `intro` text NOT NULL COMMENT '表单描述，预留',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '表单状态，0关闭，1开启',
  `attr_auto` tinyint(1) DEFAULT '0' COMMENT '自动标签：0=否，1=是',
  `lang` varchar(10) NOT NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `open_reply` tinyint(1) DEFAULT '0' COMMENT '是否开启回复 0-未开启,1-开启',
  `open_examine` tinyint(1) DEFAULT '0' COMMENT '是否开启审核 0-不审核,1-审核',
  PRIMARY KEY (`form_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='表单管理表';
CREATE TABLE `cms_guestbook` (
  `aid` int(11) NOT NULL AUTO_INCREMENT,
  `form_type` tinyint(1) DEFAULT '0' COMMENT '数据分类：0=留言模型，1=自由表单',
  `typeid` int(11) DEFAULT '0' COMMENT '栏目ID/表单ID',
  `channel` smallint(5) DEFAULT '0' COMMENT '模型ID',
  `users_id` int(11) DEFAULT '0' COMMENT '用户id',
  `md5data` varchar(50) DEFAULT '' COMMENT '数据序列化之后的MD5加密，提交内容的唯一性',
  `ip` varchar(255) DEFAULT '' COMMENT 'ip地址',
  `is_read` tinyint(1) DEFAULT '0' COMMENT '0=未读，1=已读',
  `is_star` tinyint(1) DEFAULT '0' COMMENT '标记星号',
  `source` tinyint(1) DEFAULT '0' COMMENT '提交来源：1=电脑端，2=手机端',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `reply` varchar(1000) DEFAULT '' COMMENT '留言回复内容',
  `admin_id` int(11) DEFAULT '0' COMMENT '回复管理员ID',
  `reply_time` int(11) DEFAULT '0' COMMENT '回复时间',
  `examine` tinyint(1) DEFAULT '1' COMMENT '0-未审核 1-审核通过 2-审核不通过',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='留言主表';
CREATE TABLE `cms_guestbook_attr` (
  `guest_attr_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '留言表单id自增',
  `aid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '留言id',
  `form_type` tinyint(1) DEFAULT '0' COMMENT '数据分类：0=留言模型，1=自由表单',
  `attr_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '表单id',
  `attr_value` text COMMENT '表单值',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`guest_attr_id`),
  KEY `attr_id` (`attr_id`) USING BTREE,
  KEY `guest_id` (`aid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 COMMENT='留言表单属性值';
CREATE TABLE `cms_guestbook_attribute` (
  `attr_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '表单id',
  `attr_name` varchar(60) DEFAULT '' COMMENT '表单名称',
  `typeid` int(11) unsigned DEFAULT '0' COMMENT '栏目ID/表单ID',
  `form_type` tinyint(1) DEFAULT '0' COMMENT '数据分类：0=留言模型，1=自由表单',
  `attr_input_type` tinyint(1) unsigned DEFAULT '0' COMMENT ' 0=文本框，1=下拉框，2=多行文本框',
  `attr_values` text COMMENT '可选值列表',
  `is_showlist` tinyint(1) DEFAULT '0' COMMENT '在列表显示 0=隐藏，1=显示',
  `required` tinyint(1) DEFAULT '0' COMMENT '必填 0=否，1=是',
  `validate_type` smallint(5) DEFAULT '0' COMMENT '验证格式，0=不验证，1=手机，2=Email',
  `real_validate` tinyint(1) unsigned DEFAULT '0' COMMENT '是否进行真实验证，0=不验证，1=真实验证',
  `sort_order` int(11) unsigned DEFAULT '0' COMMENT '表单排序',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '是否已删除，0=否，1=是',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`attr_id`),
  KEY `guest_id` (`typeid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COMMENT='留言表单属性';
CREATE TABLE `cms_hooks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '钩子名称',
  `description` text COMMENT '描述',
  `module` varchar(50) DEFAULT '' COMMENT '钩子挂载的插件',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态：0=无效，1=有效',
  `add_time` int(10) DEFAULT NULL,
  `update_time` int(10) unsigned DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='插件钩子表';
CREATE TABLE `cms_images_content` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) DEFAULT '0' COMMENT '文档ID',
  `content` longtext COMMENT '内容详情',
  `content_ey_m` longtext COMMENT '手机端内容详情',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `news_id` (`aid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='图集附加表';
CREATE TABLE `cms_images_upload` (
  `img_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `aid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '图集ID',
  `title` varchar(200) DEFAULT '' COMMENT '产品标题',
  `image_url` varchar(255) DEFAULT '' COMMENT '文件存储路径',
  `intro` varchar(2000) DEFAULT '' COMMENT '图集描述',
  `width` int(11) DEFAULT '0' COMMENT '图片宽度',
  `height` int(11) DEFAULT '0' COMMENT '图片高度',
  `filesize` mediumint(8) unsigned DEFAULT '0' COMMENT '文件大小',
  `mime` varchar(50) DEFAULT '' COMMENT '图片类型',
  `sort_order` smallint(5) DEFAULT '0' COMMENT '排序',
  `add_time` int(10) unsigned DEFAULT '0' COMMENT '上传时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`img_id`),
  KEY `arcid` (`aid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8 COMMENT='图集图片表';
CREATE TABLE `cms_language` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '信息ID，自增',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '语言名称',
  `mark` varchar(50) NOT NULL DEFAULT '' COMMENT '语言标识（唯一）',
  `url` varchar(200) NOT NULL DEFAULT '' COMMENT '单独域名(外部链接)',
  `target` tinyint(1) NOT NULL DEFAULT '0' COMMENT '新窗口打开，0=否，1=是',
  `is_home_default` tinyint(1) DEFAULT '0' COMMENT '默认前台语言，1=是，0=否',
  `is_admin_default` tinyint(1) DEFAULT '0' COMMENT '默认后台语言，1=是，0=否',
  `syn_pack_id` int(10) DEFAULT '0' COMMENT '最后一次同步官方语言包ID',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '语言状态，0=关闭，1=开启',
  `sort_order` int(10) DEFAULT '0' COMMENT '排序号',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='多语言主表';
INSERT INTO `cms_language` VALUES ('1', '简体中文', 'cn', '', '0', '1', '1', '24', '1', '100', '1541583096', '1543890743');
CREATE TABLE `cms_language_archives_copy_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `channel` int(10) DEFAULT '0',
  `typeid` int(10) DEFAULT '0' COMMENT '分类ID',
  `new_typeid` int(10) DEFAULT '0',
  `oldid` int(10) DEFAULT '0',
  `newid` int(10) DEFAULT '0',
  `lang` varchar(20) DEFAULT '' COMMENT '生成语言',
  `add_time` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `index_oldid` (`oldid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `cms_language_attr` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '信息ID，自增',
  `attr_name` varchar(200) NOT NULL DEFAULT '' COMMENT '来自ey_weapp_language_attr表的attr_name',
  `attr_value` text NOT NULL COMMENT '变量值',
  `attr_group` varchar(50) DEFAULT '' COMMENT '分组，以表名划分（不含表前缀）',
  `lang` varchar(50) NOT NULL DEFAULT '' COMMENT '所属语言',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `attr_value` (`attr_name`,`lang`)
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8 COMMENT='多语言模板变量关联绑定表';
INSERT INTO `cms_language_attr` VALUES ('1', 'tid1', '1', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('2', 'tid2', '2', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('3', 'tid3', '3', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('4', 'tid4', '4', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('5', 'tid5', '5', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('6', 'tid6', '6', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('7', 'tid8', '8', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('8', 'tid9', '9', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('9', 'tid10', '10', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('10', 'tid11', '11', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('11', 'tid12', '12', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('12', 'tid13', '13', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('13', 'tid20', '20', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('14', 'tid21', '21', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('15', 'tid22', '22', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('16', 'tid23', '23', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('17', 'tid24', '24', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('18', 'tid25', '25', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('19', 'tid26', '26', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('20', 'tid27', '27', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('21', 'tid28', '28', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('22', 'tid29', '29', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('23', 'tid30', '30', 'arctype', 'cn', '1545267517', '1545267517');
INSERT INTO `cms_language_attr` VALUES ('24', 'attr_1', '1', 'guestbook_attribute', 'cn', '1545267518', '1545267518');
INSERT INTO `cms_language_attr` VALUES ('25', 'attr_2', '2', 'guestbook_attribute', 'cn', '1545267518', '1545267518');
INSERT INTO `cms_language_attr` VALUES ('26', 'attr_3', '3', 'guestbook_attribute', 'cn', '1545267518', '1545267518');
INSERT INTO `cms_language_attr` VALUES ('27', 'attr_4', '4', 'guestbook_attribute', 'cn', '1545267518', '1545267518');
INSERT INTO `cms_language_attr` VALUES ('28', 'attr_5', '5', 'guestbook_attribute', 'cn', '1545267518', '1545267518');
INSERT INTO `cms_language_attr` VALUES ('29', 'attr_6', '6', 'guestbook_attribute', 'cn', '1545267518', '1545267518');
INSERT INTO `cms_language_attr` VALUES ('30', 'attr_7', '7', 'guestbook_attribute', 'cn', '1545267518', '1545267518');
INSERT INTO `cms_language_attr` VALUES ('31', 'attr_1', '1', 'product_attribute', 'cn', '1545267518', '1545267518');
INSERT INTO `cms_language_attr` VALUES ('32', 'attr_2', '2', 'product_attribute', 'cn', '1545267518', '1545267518');
INSERT INTO `cms_language_attr` VALUES ('33', 'attr_3', '3', 'product_attribute', 'cn', '1545267518', '1545267518');
INSERT INTO `cms_language_attr` VALUES ('34', 'attr_4', '4', 'product_attribute', 'cn', '1545267518', '1545267518');
INSERT INTO `cms_language_attr` VALUES ('35', 'attr_5', '5', 'product_attribute', 'cn', '1545267518', '1545267518');
INSERT INTO `cms_language_attr` VALUES ('36', 'attr_6', '6', 'product_attribute', 'cn', '1545267518', '1545267518');
INSERT INTO `cms_language_attr` VALUES ('37', 'attr_7', '7', 'product_attribute', 'cn', '1545267518', '1545267518');
INSERT INTO `cms_language_attr` VALUES ('38', 'attr_8', '8', 'product_attribute', 'cn', '1545267518', '1545267518');
INSERT INTO `cms_language_attr` VALUES ('39', 'ad1', '1', 'ad', 'cn', '1545267518', '1545267518');
INSERT INTO `cms_language_attr` VALUES ('41', 'adp1', '1', 'ad_position', 'cn', '1545267518', '1545267518');
INSERT INTO `cms_language_attr` VALUES ('83', 'ad5', '5', 'ad', 'cn', '1553046945', '1553046945');
INSERT INTO `cms_language_attr` VALUES ('85', 'attr_16', '16', 'guestbook_attribute', 'cn', '1563533188', '1563533188');
INSERT INTO `cms_language_attr` VALUES ('87', 'attr_18', '18', 'guestbook_attribute', 'cn', '1563533246', '1563533246');
INSERT INTO `cms_language_attr` VALUES ('89', 'attr_20', '20', 'guestbook_attribute', 'cn', '1563533261', '1563533261');
INSERT INTO `cms_language_attr` VALUES ('91', 'attr_22', '22', 'guestbook_attribute', 'cn', '1563533269', '1563533269');
INSERT INTO `cms_language_attr` VALUES ('93', 'tid54', '54', 'arctype', 'cn', '1563761937', '1563761937');
INSERT INTO `cms_language_attr` VALUES ('95', 'adp3', '3', 'ad_position', 'cn', '1563764323', '1563764323');
INSERT INTO `cms_language_attr` VALUES ('97', 'ad7', '7', 'ad', 'cn', '1563764323', '1563764323');
INSERT INTO `cms_language_attr` VALUES ('99', 'ad9', '9', 'ad', 'cn', '1563764323', '1563764323');
INSERT INTO `cms_language_attr` VALUES ('103', 'attr_19', '19', 'product_attribute', 'cn', '1564539436', '1564539436');
INSERT INTO `cms_language_attr` VALUES ('105', 'attr_21', '21', 'product_attribute', 'cn', '1564539503', '1564539503');
INSERT INTO `cms_language_attr` VALUES ('107', 'attr_23', '23', 'product_attribute', 'cn', '1564539517', '1564539517');
INSERT INTO `cms_language_attr` VALUES ('109', 'attr_25', '25', 'product_attribute', 'cn', '1564539530', '1564539530');
INSERT INTO `cms_language_attr` VALUES ('111', 'attr_27', '27', 'product_attribute', 'cn', '1564539541', '1564539541');
INSERT INTO `cms_language_attr` VALUES ('113', 'tid56', '56', 'arctype', 'cn', '1564625026', '1564625026');
INSERT INTO `cms_language_attr` VALUES ('115', 'tid58', '58', 'arctype', 'cn', '1564632567', '1564632567');
INSERT INTO `cms_language_attr` VALUES ('117', 'tid60', '60', 'arctype', 'cn', '1564632676', '1564632676');
INSERT INTO `cms_language_attr` VALUES ('119', 'tid62', '62', 'arctype', 'cn', '1564632717', '1564632717');
INSERT INTO `cms_language_attr` VALUES ('121', 'tid64', '64', 'arctype', 'cn', '1565083870', '1565083870');
INSERT INTO `cms_language_attr` VALUES ('123', 'tid66', '66', 'arctype', 'cn', '1565083875', '1565083875');
INSERT INTO `cms_language_attr` VALUES ('127', 'ad13', '13', 'ad', 'cn', '1565225126', '1565225126');
INSERT INTO `cms_language_attr` VALUES ('129', 'attr_24', '24', 'guestbook_attribute', 'cn', '1609930411', '1609930411');
INSERT INTO `cms_language_attr` VALUES ('131', 'linksgroup1', '1', 'links_group', 'cn', '1701045570', '1701045570');
INSERT INTO `cms_language_attr` VALUES ('132', 'form1', '1', 'form', 'cn', '1703142214', '1703142214');
INSERT INTO `cms_language_attr` VALUES ('133', 'attr_26', '26', 'form_attribute', 'cn', '1703142214', '1703142214');
INSERT INTO `cms_language_attr` VALUES ('134', 'attr_27', '27', 'form_attribute', 'cn', '1703142214', '1703142214');
INSERT INTO `cms_language_attr` VALUES ('135', 'attr_28', '28', 'form_attribute', 'cn', '1703142214', '1703142214');
INSERT INTO `cms_language_attr` VALUES ('136', 'attr_29', '29', 'form_attribute', 'cn', '1703142214', '1703142214');
INSERT INTO `cms_language_attr` VALUES ('137', 'attrlist_1', '1', 'shop_product_attrlist', 'cn', '1713251003', '1713251003');
INSERT INTO `cms_language_attr` VALUES ('138', 'attrlist_2', '2', 'shop_product_attrlist', 'cn', '1713251003', '1713251003');
INSERT INTO `cms_language_attr` VALUES ('139', 'attrlist_3', '3', 'shop_product_attrlist', 'cn', '1713251003', '1713251003');
INSERT INTO `cms_language_attr` VALUES ('140', 'attribute_1', '1', 'shop_product_attribute', 'cn', '1713251003', '1713251003');
INSERT INTO `cms_language_attr` VALUES ('141', 'attribute_2', '2', 'shop_product_attribute', 'cn', '1713251003', '1713251003');
INSERT INTO `cms_language_attr` VALUES ('142', 'attribute_3', '3', 'shop_product_attribute', 'cn', '1713251003', '1713251003');
INSERT INTO `cms_language_attr` VALUES ('143', 'attribute_4', '4', 'shop_product_attribute', 'cn', '1713251003', '1713251003');
INSERT INTO `cms_language_attr` VALUES ('144', 'attribute_5', '5', 'shop_product_attribute', 'cn', '1713251003', '1713251003');
INSERT INTO `cms_language_attr` VALUES ('145', 'attribute_6', '6', 'shop_product_attribute', 'cn', '1713251003', '1713251003');
INSERT INTO `cms_language_attr` VALUES ('146', 'attribute_7', '7', 'shop_product_attribute', 'cn', '1713251003', '1713251003');
INSERT INTO `cms_language_attr` VALUES ('147', 'attribute_8', '8', 'shop_product_attribute', 'cn', '1713251003', '1713251003');
INSERT INTO `cms_language_attr` VALUES ('148', 'attribute_9', '9', 'shop_product_attribute', 'cn', '1713251003', '1713251003');
CREATE TABLE `cms_language_attribute` (
  `attr_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '信息ID，自增',
  `attr_title` varchar(200) NOT NULL DEFAULT '' COMMENT '变量标题',
  `attr_name` varchar(200) NOT NULL DEFAULT '' COMMENT '变量名称',
  `attr_group` varchar(50) DEFAULT '' COMMENT '分组，以表名划分（不含表前缀）',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '伪删除，0=否，1=是',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`attr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8 COMMENT='多语言模板变量表';
INSERT INTO `cms_language_attribute` VALUES ('1', '关于我们', 'tid1', 'arctype', '0', '1545267517', '1545267517');
INSERT INTO `cms_language_attribute` VALUES ('2', '新闻动态', 'tid2', 'arctype', '0', '1545267517', '1545267517');
INSERT INTO `cms_language_attribute` VALUES ('3', '产品展示', 'tid3', 'arctype', '0', '1545267517', '1545267517');
INSERT INTO `cms_language_attribute` VALUES ('4', '解决方案', 'tid4', 'arctype', '0', '1545267517', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('5', '资料下载', 'tid5', 'arctype', '0', '1545267517', '1545267517');
INSERT INTO `cms_language_attribute` VALUES ('6', '在线应聘', 'tid6', 'arctype', '1', '1545267517', '1690162042');
INSERT INTO `cms_language_attribute` VALUES ('7', '公司简介', 'tid8', 'arctype', '0', '1545267517', '1545267517');
INSERT INTO `cms_language_attribute` VALUES ('8', '公司荣誉', 'tid9', 'arctype', '0', '1545267517', '1545267517');
INSERT INTO `cms_language_attribute` VALUES ('9', '公司动态', 'tid10', 'arctype', '0', '1545267517', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('10', '行业资讯', 'tid11', 'arctype', '0', '1545267517', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('11', '媒体报道', 'tid12', 'arctype', '0', '1545267517', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('12', '单页面', 'tid13', 'arctype', '1', '1545267517', '1565084026');
INSERT INTO `cms_language_attribute` VALUES ('13', '手机数码', 'tid20', 'arctype', '0', '1545267517', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('14', '电脑产品', 'tid21', 'arctype', '0', '1545267517', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('15', '周边配件', 'tid22', 'arctype', '0', '1545267517', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('16', '人才招聘', 'tid23', 'arctype', '1', '1545267517', '1690162055');
INSERT INTO `cms_language_attribute` VALUES ('17', '智能手机', 'tid24', 'arctype', '0', '1545267517', '1545267517');
INSERT INTO `cms_language_attribute` VALUES ('18', '畅玩手机', 'tid25', 'arctype', '0', '1545267517', '1545267517');
INSERT INTO `cms_language_attribute` VALUES ('19', '笔记本电脑', 'tid26', 'arctype', '0', '1545267517', '1545267517');
INSERT INTO `cms_language_attribute` VALUES ('20', '耳机', 'tid27', 'arctype', '0', '1545267517', '1545267517');
INSERT INTO `cms_language_attribute` VALUES ('21', '音箱', 'tid28', 'arctype', '0', '1545267517', '1545267517');
INSERT INTO `cms_language_attribute` VALUES ('22', '充电宝', 'tid29', 'arctype', '0', '1545267517', '1545267517');
INSERT INTO `cms_language_attribute` VALUES ('23', '联系我们', 'tid30', 'arctype', '0', '1545267517', '1610357904');
INSERT INTO `cms_language_attribute` VALUES ('24', '姓名', 'attr_1', 'guestbook_attribute', '0', '1545267518', '1545267518');
INSERT INTO `cms_language_attribute` VALUES ('25', '手机号码', 'attr_2', 'guestbook_attribute', '0', '1545267518', '1545267518');
INSERT INTO `cms_language_attribute` VALUES ('26', '需求选择', 'attr_3', 'guestbook_attribute', '0', '1545267518', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('27', '申请职位', 'attr_4', 'guestbook_attribute', '0', '1545267518', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('28', '姓名', 'attr_5', 'guestbook_attribute', '0', '1545267518', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('29', '性别', 'attr_6', 'guestbook_attribute', '0', '1545267518', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('30', '模式', 'attr_7', 'guestbook_attribute', '0', '1545267518', '1545267518');
INSERT INTO `cms_language_attribute` VALUES ('31', '用户界面', 'attr_1', 'product_attribute', '0', '1545267518', '1545267518');
INSERT INTO `cms_language_attribute` VALUES ('32', '操作系统', 'attr_2', 'product_attribute', '0', '1545267518', '1545267518');
INSERT INTO `cms_language_attribute` VALUES ('33', '键盘类型', 'attr_3', 'product_attribute', '0', '1545267518', '1545267518');
INSERT INTO `cms_language_attribute` VALUES ('34', ' 产品型号', 'attr_4', 'product_attribute', '0', '1545267518', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('35', '屏幕大小', 'attr_5', 'product_attribute', '0', '1545267518', '1545267518');
INSERT INTO `cms_language_attribute` VALUES ('36', '整机净重', 'attr_6', 'product_attribute', '0', '1545267518', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('37', '产品型号', 'attr_7', 'product_attribute', '0', '1545267518', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('38', '支持蓝牙', 'attr_8', 'product_attribute', '0', '1545267518', '1545267518');
INSERT INTO `cms_language_attribute` VALUES ('39', '共展蓝图', 'ad1', 'ad', '0', '1545267518', '1545267518');
INSERT INTO `cms_language_attribute` VALUES ('41', '首页-大幻灯片', 'adp1', 'ad_position', '0', '1545267518', '1545267518');
INSERT INTO `cms_language_attribute` VALUES ('42', '第三组广告', 'ad5', 'ad', '0', '1553046945', '1553046945');
INSERT INTO `cms_language_attribute` VALUES ('43', '手机', 'attr_16', 'guestbook_attribute', '0', '1563533188', '1563533188');
INSERT INTO `cms_language_attribute` VALUES ('44', '学历', 'attr_18', 'guestbook_attribute', '0', '1563533246', '1563533246');
INSERT INTO `cms_language_attribute` VALUES ('45', '工作经验', 'attr_20', 'guestbook_attribute', '0', '1563533261', '1563533261');
INSERT INTO `cms_language_attribute` VALUES ('46', '自我评价', 'attr_22', 'guestbook_attribute', '0', '1563533269', '1563533269');
INSERT INTO `cms_language_attribute` VALUES ('47', '联系我们', 'tid54', 'arctype', '1', '1563761937', '1609929617');
INSERT INTO `cms_language_attribute` VALUES ('48', '手机端首页头部幻灯', 'adp3', 'ad_position', '0', '1563764323', '1563764323');
INSERT INTO `cms_language_attribute` VALUES ('49', '', 'ad7', 'ad', '0', '1563764323', '1563764323');
INSERT INTO `cms_language_attribute` VALUES ('50', '', 'ad9', 'ad', '0', '1563764323', '1563764323');
INSERT INTO `cms_language_attribute` VALUES ('52', '商品名称', 'attr_19', 'product_attribute', '0', '1564539436', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('53', '商品毛重', 'attr_21', 'product_attribute', '0', '1564539503', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('54', '商品产地', 'attr_23', 'product_attribute', '0', '1564539517', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('55', '多卡支持', 'attr_25', 'product_attribute', '0', '1564539530', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('56', '机身内存', 'attr_27', 'product_attribute', '0', '1564539541', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('57', '国内媒体', 'tid56', 'arctype', '1', '1564625026', '1565083902');
INSERT INTO `cms_language_attribute` VALUES ('58', '软文选写', 'tid58', 'arctype', '1', '1564632567', '1565083896');
INSERT INTO `cms_language_attribute` VALUES ('59', '国外媒体', 'tid60', 'arctype', '1', '1564632676', '1565083908');
INSERT INTO `cms_language_attribute` VALUES ('60', '岛内媒体', 'tid62', 'arctype', '1', '1564632717', '1565083886');
INSERT INTO `cms_language_attribute` VALUES ('61', '系统方案', 'tid64', 'arctype', '0', '1565083870', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('62', '应用方案', 'tid66', 'arctype', '0', '1565083875', '1574233888');
INSERT INTO `cms_language_attribute` VALUES ('64', '', 'ad13', 'ad', '0', '1565225126', '1565225126');
INSERT INTO `cms_language_attribute` VALUES ('65', '留言内容', 'attr_24', 'guestbook_attribute', '0', '1609930411', '1609930411');
INSERT INTO `cms_language_attribute` VALUES ('66', '默认分组', 'linksgroup1', 'links_group', '0', '1701045570', '1701045570');
INSERT INTO `cms_language_attribute` VALUES ('67', '联系我们', 'form1', 'form', '0', '1703142214', '1703142214');
INSERT INTO `cms_language_attribute` VALUES ('68', '称呼', 'attr_26', 'form_attribute', '0', '1703142214', '1703142214');
INSERT INTO `cms_language_attribute` VALUES ('69', '手机', 'attr_27', 'form_attribute', '0', '1703142214', '1703142214');
INSERT INTO `cms_language_attribute` VALUES ('70', '分类', 'attr_28', 'form_attribute', '0', '1703142214', '1703142214');
INSERT INTO `cms_language_attribute` VALUES ('71', '内容', 'attr_29', 'form_attribute', '0', '1703142214', '1703142214');
INSERT INTO `cms_language_attribute` VALUES ('72', '手机数码', 'attrlist_1', 'shop_product_attrlist', '0', '1713251003', '1713251003');
INSERT INTO `cms_language_attribute` VALUES ('73', '电脑产品', 'attrlist_2', 'shop_product_attrlist', '0', '1713251003', '1713251003');
INSERT INTO `cms_language_attribute` VALUES ('74', '耳机', 'attrlist_3', 'shop_product_attrlist', '0', '1713251003', '1713251003');
INSERT INTO `cms_language_attribute` VALUES ('75', '操作系统', 'attribute_1', 'shop_product_attribute', '0', '1713251003', '1713251003');
INSERT INTO `cms_language_attribute` VALUES ('76', '用户界面', 'attribute_2', 'shop_product_attribute', '0', '1713251003', '1713251003');
INSERT INTO `cms_language_attribute` VALUES ('77', '键盘类型', 'attribute_3', 'shop_product_attribute', '0', '1713251003', '1713251003');
INSERT INTO `cms_language_attribute` VALUES ('78', '产品型号', 'attribute_4', 'shop_product_attribute', '0', '1713251003', '1713251003');
INSERT INTO `cms_language_attribute` VALUES ('79', '屏幕大小', 'attribute_5', 'shop_product_attribute', '0', '1713251003', '1713251003');
INSERT INTO `cms_language_attribute` VALUES ('80', '整机净重', 'attribute_6', 'shop_product_attribute', '0', '1713251003', '1713251003');
INSERT INTO `cms_language_attribute` VALUES ('81', '产品型号', 'attribute_7', 'shop_product_attribute', '0', '1713251003', '1713251003');
INSERT INTO `cms_language_attribute` VALUES ('82', '支持蓝牙', 'attribute_8', 'shop_product_attribute', '0', '1713251003', '1713251003');
INSERT INTO `cms_language_attribute` VALUES ('83', '机身内存', 'attribute_9', 'shop_product_attribute', '0', '1713251003', '1713251003');
CREATE TABLE `cms_language_mark` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '国家语言名称',
  `cn_title` varchar(50) NOT NULL DEFAULT '' COMMENT '中文名称',
  `mark` varchar(50) DEFAULT '' COMMENT '多语言标识',
  `pinyin` varchar(100) DEFAULT '' COMMENT '拼音',
  `sort_order` int(10) NOT NULL DEFAULT '0' COMMENT '排序号',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8 COMMENT='国家语言表';
INSERT INTO `cms_language_mark` VALUES ('1', '简体中文', '简体中文', 'cn', 'zhongwenjianti', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('2', 'Vietnamese', '越南语', 'vi', 'yuenanyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('3', '繁体中文', '繁体中文', 'zh', 'zhongwenfanti', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('4', 'English', '英语', 'en', 'yingyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('5', 'Indonesian', '印尼语', 'id', 'yinniyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('6', 'Urdu', '乌尔都语', 'ur', 'wuerduyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('7', 'Yiddish', '意第绪语', 'yi', 'yidixuyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('8', 'Italian', '意大利语', 'it', 'yidaliyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('9', 'Greek', '希腊语', 'el', 'xilayu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('10', 'Spanish Basque', '西班牙的巴斯克语', 'eu', 'xibanyadebasikeyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('11', 'Spanish', '西班牙语', 'es', 'xibanyayu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('12', 'Hungarian', '匈牙利语', 'hu', 'xiongyaliyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('13', 'Hebrew', '希伯来语', 'iw', 'xibolaiyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('14', 'Ukrainian', '乌克兰语', 'uk', 'wukelanyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('15', 'Welsh', '威尔士语', 'cy', 'weiershiyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('16', 'Thai', '泰语', 'th', 'taiyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('17', 'Turkish', '土耳其语', 'tr', 'tuerqiyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('18', 'Swahili', '斯瓦希里语', 'sw', 'siwaxiliyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('19', 'Japanese', '日语', 'ja', 'riyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('20', 'Swedish', '瑞典语', 'sv', 'ruidianyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('21', 'Serbian', '塞尔维亚语', 'sr', 'saierweiyayu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('22', 'Slovak', '斯洛伐克语', 'sk', 'siluofakeyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('23', 'Slovenian', '斯洛文尼亚语', 'sl', 'siluowenniyayu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('24', 'Portuguese', '葡萄牙语', 'pt', 'putaoyayu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('25', 'Norwegian', '挪威语', 'no', 'nuoweiyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('26', 'Macedonian', '马其顿语', 'mk', 'maqidunyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('27', 'Malay', '马来语', 'ms', 'malaiyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('28', 'Maltese', '马耳他语', 'mt', 'maertayu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('29', 'Romanian', '罗马尼亚语', 'ro', 'luomaniyayu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('30', 'Lithuanian', '立陶宛语', 'lt', 'litaowanyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('31', 'Latvian', '拉脱维亚语', 'lv', 'latuoweiyayu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('32', 'Latin', '拉丁语', 'la', 'ladingyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('33', 'Croatian', '克罗地亚语', 'hr', 'keluodiyayu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('34', 'Czech', '捷克语', 'cs', 'jiekeyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('35', 'Catalan', '加泰罗尼亚语', 'ca', 'jiatailuoniyayu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('36', 'Galician', '加利西亚语', 'gl', 'jialixiyayu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('37', 'Dutch', '荷兰语', 'nl', 'helanyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('38', 'Korean', '韩语', 'ko', 'hanyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('39', 'Haitian Creole', '海地克里奥尔语', 'ht', 'haidikeliaoeryu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('40', 'Finnish', '芬兰语', 'fi', 'fenlanyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('41', 'Filipino', '菲律宾语', 'tl', 'feilvbinyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('42', 'Russian', '俄语', 'ru', 'eyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('43', 'Boolean (Afrikaans)', '布尔语(南非荷兰语)', 'af', 'bueryunanfeihelanyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('44', 'French', '法语', 'fr', 'fayu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('45', 'Danish', '丹麦语', 'da', 'danmaiyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('46', 'German', '德语', 'de', 'deyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('47', 'Azerbaijani', '阿塞拜疆语', 'az', 'asaibaijiangyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('48', 'Irish', '爱尔兰语', 'ga', 'aierlanyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('49', 'Estonian', '爱沙尼亚语', 'et', 'aishaniyayu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('50', 'Belarusian', '白俄罗斯语', 'be', 'baieluosiyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('51', 'Bulgarian', '保加利亚语', 'bg', 'baojialiyayu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('52', 'Icelandic', '冰岛语', 'is', 'bingdaoyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('53', 'Polish', '波兰语', 'pl', 'bolanyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('54', 'Persian', '波斯语', 'fa', 'bosiyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('55', 'Arabic', '阿拉伯语', 'ar', 'alaboyu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('56', 'Albanian', '阿尔巴尼亚语', 'sq', 'aerbaniyayu', '100', '0', '1541583096');
INSERT INTO `cms_language_mark` VALUES ('57', 'Uyghur', '维吾尔族语', 'ug', 'weiwuerzuyu', '100', '0', '1541583096');
CREATE TABLE `cms_language_pack` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '变量名',
  `value` text NOT NULL COMMENT '变量值',
  `is_syn` tinyint(1) DEFAULT '0' COMMENT '同步官方语言包：0=否，1=是',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `sort_order` int(10) DEFAULT '0' COMMENT '排序号',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COMMENT='模板语言包变量';
INSERT INTO `cms_language_pack` VALUES ('1', 'sys1', '首页', '1', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_language_pack` VALUES ('2', 'sys2', '上一页', '1', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_language_pack` VALUES ('3', 'sys3', '下一页', '1', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_language_pack` VALUES ('4', 'sys4', '末页', '1', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_language_pack` VALUES ('5', 'sys5', '共<strong>%s</strong>页 <strong>%s</strong>条', '1', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_language_pack` VALUES ('6', 'sys6', '全部', '1', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_language_pack` VALUES ('7', 'sys7', '搜索', '1', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_language_pack` VALUES ('8', 'sys8', '查看详情', '1', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_language_pack` VALUES ('9', 'sys9', '网站首页', '1', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_language_pack` VALUES ('10', 'sys10', '没有了', '1', 'cn', '100', '1543890216', '1703142234');
INSERT INTO `cms_language_pack` VALUES ('11', 'sys11', '上一篇', '1', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_language_pack` VALUES ('12', 'sys12', '下一篇', '1', 'cn', '100', '1543890216', '1543890216');
INSERT INTO `cms_language_pack` VALUES ('25', 'yybl1', '网站首页', '0', 'cn', '100', '1545272835', '1545272835');
INSERT INTO `cms_language_pack` VALUES ('27', 'yybl2', '全部', '0', 'cn', '100', '1545272897', '1545272897');
INSERT INTO `cms_language_pack` VALUES ('29', 'yybl3', '查看更多', '0', 'cn', '100', '1545272961', '1545272961');
INSERT INTO `cms_language_pack` VALUES ('31', 'yybl4', '联系热线', '0', 'cn', '100', '1545273023', '1545273023');
INSERT INTO `cms_language_pack` VALUES ('33', 'yybl5', '你的位置', '0', 'cn', '100', '1545273158', '1545273158');
INSERT INTO `cms_language_pack` VALUES ('35', 'yybl6', '请输入关键词', '0', 'cn', '100', '1545273239', '1545273239');
INSERT INTO `cms_language_pack` VALUES ('37', 'yybl7', '为您推荐', '0', 'cn', '100', '1545273292', '1545273292');
INSERT INTO `cms_language_pack` VALUES ('39', 'yybl8', '热门推荐', '0', 'cn', '100', '1545273376', '1545273376');
INSERT INTO `cms_language_pack` VALUES ('41', 'yybl9', '详细信息', '0', 'cn', '100', '1545273418', '1545273418');
INSERT INTO `cms_language_pack` VALUES ('43', 'yybl10', '下载包', '0', 'cn', '100', '1545273596', '1545273596');
INSERT INTO `cms_language_pack` VALUES ('45', 'yybl11', '文件附件列表', '0', 'cn', '100', '1545273655', '1545273655');
INSERT INTO `cms_language_pack` VALUES ('47', 'yybl12', '结果', '0', 'cn', '100', '1545274437', '1545274437');
INSERT INTO `cms_language_pack` VALUES ('49', 'yybl13', '没有数据了', '0', 'cn', '100', '1545274472', '1547516837');
CREATE TABLE `cms_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `typeid` tinyint(1) DEFAULT '1' COMMENT '类型：1=文字链接，2=图片链接',
  `groupid` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '分组id， 默认分组值为1',
  `title` varchar(50) DEFAULT '' COMMENT '网站标题',
  `url` varchar(500) DEFAULT '' COMMENT '网站地址',
  `logo` varchar(255) DEFAULT '' COMMENT '网站LOGO',
  `sort_order` int(11) DEFAULT '0' COMMENT '排序号',
  `target` tinyint(1) DEFAULT '0' COMMENT '是否开启浏览器新窗口',
  `nofollow` tinyint(1) DEFAULT '0',
  `email` varchar(50) DEFAULT NULL,
  `intro` text COMMENT '网站简况',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态(1=显示，0=屏蔽)',
  `province_id` int(10) DEFAULT '0' COMMENT '省份',
  `city_id` int(10) DEFAULT '0' COMMENT '所在城市',
  `area_id` int(10) DEFAULT '0' COMMENT '所在区域',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `delete_time` int(11) DEFAULT '0' COMMENT '软删除时间',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='友情链接表';
CREATE TABLE `cms_links_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL DEFAULT '' COMMENT '分组名称',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '状态(1=显示，0=屏蔽)',
  `sort_order` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序号',
  `lang` varchar(50) NOT NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='友情链接分组';
CREATE TABLE `cms_media_content` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '文档ID',
  `content` longtext COMMENT '内容详情',
  `content_ey_m` longtext COMMENT '手机端内容详情',
  `courseware` varchar(200) NOT NULL DEFAULT '' COMMENT '课件地址',
  `courseware_free` enum('免费','收费') NOT NULL DEFAULT '免费' COMMENT '课件收费',
  `total_duration` int(10) NOT NULL DEFAULT '0' COMMENT '视频总时长',
  `total_video` int(10) NOT NULL DEFAULT '0' COMMENT '视频数',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `aid` (`aid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='视频附加表';
CREATE TABLE `cms_media_file` (
  `file_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '视频模型文件表',
  `aid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文档ID',
  `title` varchar(200) NOT NULL DEFAULT '' COMMENT '文档标题',
  `file_name` varchar(200) NOT NULL DEFAULT '' COMMENT '文件名称',
  `file_title` varchar(200) NOT NULL DEFAULT '' COMMENT '选集标题',
  `file_url` text NOT NULL COMMENT '存储路径',
  `file_time` int(8) NOT NULL DEFAULT '0' COMMENT '文件时长',
  `file_ext` varchar(50) NOT NULL DEFAULT '' COMMENT '文件后缀名',
  `file_size` varchar(255) NOT NULL DEFAULT '' COMMENT '文件大小',
  `file_mime` varchar(200) NOT NULL DEFAULT '' COMMENT '文件类型',
  `uhash` varchar(200) NOT NULL DEFAULT '' COMMENT '自定义的一种加密方式，用于视频播放的权限验证',
  `md5file` varchar(200) NOT NULL DEFAULT '' COMMENT 'md5_file加密，可以检测上传/播放的视频文件是否损坏',
  `is_remote` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否远程 1-远程',
  `playcount` int(10) NOT NULL DEFAULT '0' COMMENT '播放次数',
  `gratis` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否试看，0不试看，1试看',
  `sort_order` smallint(5) NOT NULL DEFAULT '0' COMMENT '排序',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上传时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`file_id`) USING BTREE,
  KEY `aid` (`aid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='视频附件表';
CREATE TABLE `cms_media_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `users_id` int(10) DEFAULT '0' COMMENT '用户ID',
  `aid` int(10) DEFAULT '0' COMMENT '文档ID',
  `file_id` int(10) DEFAULT '0' COMMENT '视频ID',
  `ip` varchar(20) DEFAULT '' COMMENT 'ip',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '编辑时间',
  PRIMARY KEY (`log_id`),
  KEY `file_id` (`file_id`,`aid`,`users_id`) USING BTREE,
  KEY `aid` (`aid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='视频日志表';
CREATE TABLE `cms_media_order` (
  `order_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '媒体订单ID',
  `order_code` varchar(50) NOT NULL DEFAULT '' COMMENT '媒体订单编号',
  `users_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `mobile` varchar(20) DEFAULT '' COMMENT '手机',
  `order_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态：0未付款，1已付款',
  `order_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单应付总金额',
  `pay_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
  `pay_name` varchar(20) NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `wechat_pay_type` varchar(20) NOT NULL DEFAULT '' COMMENT '微信支付时，标记使用的支付类型（扫码支付，微信内部，微信H5页面）',
  `pay_details` text COMMENT '支付时返回的数据，以serialize序列化后存入，用于后续查询。',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '视频文档ID',
  `product_name` varchar(100) DEFAULT '' COMMENT '视频文档名称',
  `product_litpic` varchar(500) DEFAULT '' COMMENT '视频文档封面图片',
  `lang` varchar(30) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned DEFAULT '0' COMMENT '下单时间',
  `update_time` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_code` (`order_code`) USING BTREE,
  KEY `users_id` (`users_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='视频订单表';
CREATE TABLE `cms_media_play_record` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `users_id` int(10) DEFAULT '0' COMMENT '用户id',
  `aid` int(10) DEFAULT '0' COMMENT '课程id',
  `file_id` int(10) DEFAULT '0' COMMENT '文件id',
  `play_time` int(10) DEFAULT '0' COMMENT '播放时间',
  `add_time` int(10) DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='视频播放时长表';
CREATE TABLE `cms_memgift` (
  `gift_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '礼品列表',
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '-1-实物,2-会员产品',
  `type_id` int(10) DEFAULT '0' COMMENT '类型为会员产品时的会员产品类型(users_type_manage)type_id',
  `score` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所需积分',
  `litpic` varchar(250) NOT NULL DEFAULT '',
  `giftname` varchar(60) NOT NULL DEFAULT '',
  `num` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '兑换次数',
  `content` longtext COMMENT '礼品详情',
  `stock` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '库存总数',
  `is_del` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0-正常,1-删除',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '礼品状态：1=显示，0=隐藏',
  `add_time` int(10) DEFAULT '0',
  `update_time` int(10) DEFAULT '0',
  `sort_order` int(10) DEFAULT '100' COMMENT '排序',
  PRIMARY KEY (`gift_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='礼品兑换表';
CREATE TABLE `cms_memgiftget` (
  `gid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `giftname` char(60) NOT NULL DEFAULT '',
  `gift_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '礼品ID',
  `score` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '积分',
  `users_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态,0-待发货,1-已发货,2-退回,3-重发',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '姓名',
  `mobile` varchar(55) NOT NULL DEFAULT '' COMMENT '手机',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `add_time` int(10) DEFAULT '0',
  `update_time` int(10) DEFAULT '0',
  `type_id` int(11) DEFAULT '0' COMMENT '兑换会员产品时,会员产品套餐(表::users_type_manage)type_id',
  PRIMARY KEY (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='礼品兑换记录表';
CREATE TABLE `cms_nav_list` (
  `nav_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '导航ID',
  `nav_name` varchar(200) NOT NULL DEFAULT '' COMMENT '导航名称',
  `parent_id` int(10) NOT NULL DEFAULT '0' COMMENT '上级菜单id',
  `topid` int(10) NOT NULL DEFAULT '0' COMMENT '顶级菜单id',
  `en_name` varchar(200) NOT NULL DEFAULT '' COMMENT '英文名称',
  `nav_url` varchar(200) NOT NULL DEFAULT '' COMMENT '导航链接',
  `position_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '导航位置',
  `arctype_sync` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否与栏目同步',
  `type_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '同步栏目的ID',
  `nav_pic` varchar(255) NOT NULL DEFAULT '' COMMENT '导航图片',
  `is_remote` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否远程图片',
  `target` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否打开新窗口，1=是，0=否',
  `nofollow` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否使用nofollow，1=是，0=否',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '启用 (1=正常，0=停用)',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '伪删除，1=是，0=否',
  `sort_order` int(10) unsigned NOT NULL DEFAULT '100' COMMENT '排序号',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`nav_id`) USING BTREE,
  KEY `position_id` (`position_id`) USING BTREE,
  KEY `type_id` (`type_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='导航列表';
CREATE TABLE `cms_nav_position` (
  `position_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '导航列表ID',
  `position_name` varchar(200) DEFAULT '' COMMENT '导航列表名称',
  `sort_order` int(10) DEFAULT '0' COMMENT '排序号',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '伪删除，1=是，0=否',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`position_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='导航位置表';
INSERT INTO `cms_nav_position` VALUES ('1', 'PC端主导航', '100', '0', '0', '1624861478');
INSERT INTO `cms_nav_position` VALUES ('2', 'PC端顶部导航', '100', '0', '0', '1624861478');
INSERT INTO `cms_nav_position` VALUES ('3', 'PC端中部导航', '100', '0', '0', '1624861478');
INSERT INTO `cms_nav_position` VALUES ('4', 'PC端底部导航', '100', '0', '0', '1624861478');
INSERT INTO `cms_nav_position` VALUES ('5', '移动端中部导航', '100', '0', '0', '1624861478');
INSERT INTO `cms_nav_position` VALUES ('6', '移动端底部导航', '100', '0', '0', '1624861478');
CREATE TABLE `cms_pay_api_config` (
  `pay_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '支付接口配置ID，自增',
  `pay_name` varchar(64) NOT NULL DEFAULT '' COMMENT '支付接口配置名称，微信支付，支付宝支付...',
  `pay_mark` varchar(64) NOT NULL DEFAULT '' COMMENT '支付接口配置标识，wechat，alipay...',
  `pay_info` text NOT NULL COMMENT '支付接口配置信息，数组以序列化存储',
  `pay_terminal` varchar(100) NOT NULL DEFAULT '' COMMENT '支付时的终端，暂时预留',
  `system_built` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否属于系统内置，0否，1是',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0=关闭，1=开启)',
  `lang` varchar(30) NOT NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`pay_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='支付接口配置表';
CREATE TABLE `cms_product_attr` (
  `product_attr_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '产品属性id自增',
  `aid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '产品id',
  `attr_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '属性id',
  `attr_value` text COMMENT '属性值',
  `attr_price` varchar(255) DEFAULT '' COMMENT '属性价格',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`product_attr_id`),
  KEY `aid` (`aid`) USING BTREE,
  KEY `attr_id` (`attr_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8 COMMENT='产品表单属性值';
CREATE TABLE `cms_product_attribute` (
  `attr_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '属性id',
  `attr_name` varchar(60) DEFAULT '' COMMENT '属性名称',
  `typeid` int(11) unsigned DEFAULT '0' COMMENT '栏目id',
  `attr_index` tinyint(1) unsigned DEFAULT '0' COMMENT '0不需要检索 1关键字检索 2范围检索',
  `attr_input_type` tinyint(1) unsigned DEFAULT '0' COMMENT ' 0=文本框，1=下拉框，2=多行文本框',
  `attr_values` text COMMENT '可选值列表',
  `sort_order` int(11) unsigned DEFAULT '0' COMMENT '属性排序',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '是否已删除，0=否，1=是',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`attr_id`),
  KEY `cat_id` (`typeid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COMMENT='产品表单属性表';
CREATE TABLE `cms_product_content` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) DEFAULT '0' COMMENT '文档ID',
  `content` longtext COMMENT '内容详情',
  `content_ey_m` longtext COMMENT '手机端内容详情',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `fxrq` enum('2019年','2018年','2017年') DEFAULT '2019年' COMMENT '发行日期',
  `jiawei` enum('0-1000','1000-1699','1700-2799','2800-3500','3500-10000') DEFAULT '0-1000' COMMENT '价位区段',
  `yanse` enum('银色','绿色','黑色','灰色') DEFAULT '银色' COMMENT '机身颜色',
  PRIMARY KEY (`id`),
  KEY `news_id` (`aid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COMMENT='产品附加表';
CREATE TABLE `cms_product_custom_param` (
  `param_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '参数ID',
  `aid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '参数ID',
  `param_name` varchar(60) NOT NULL DEFAULT '' COMMENT '参数名称',
  `param_value` varchar(200) NOT NULL DEFAULT '' COMMENT '参数值',
  `sort_order` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '属性排序',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`param_id`),
  KEY `aid` (`aid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品自定义参数表';
CREATE TABLE `cms_product_img` (
  `img_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `aid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `title` varchar(200) DEFAULT '' COMMENT '产品标题',
  `image_url` varchar(255) DEFAULT '' COMMENT '文件存储路径',
  `intro` varchar(2000) DEFAULT '' COMMENT '图集描述',
  `width` int(11) DEFAULT '0' COMMENT '图片宽度',
  `height` int(11) DEFAULT '0' COMMENT '图片高度',
  `filesize` varchar(255) DEFAULT '' COMMENT '文件大小',
  `mime` varchar(50) DEFAULT '' COMMENT '图片类型',
  `sort_order` smallint(5) DEFAULT '0' COMMENT '排序',
  `add_time` int(10) unsigned DEFAULT '0' COMMENT '上传时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`img_id`),
  KEY `arcid` (`aid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=239 DEFAULT CHARSET=utf8 COMMENT='产品图片表';
CREATE TABLE `cms_product_netdisk` (
  `nd_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '网盘商品id',
  `aid` int(10) DEFAULT '0' COMMENT '产品ID',
  `netdisk_url` varchar(255) NOT NULL DEFAULT '' COMMENT '网盘地址',
  `netdisk_pwd` varchar(50) NOT NULL DEFAULT '' COMMENT '提取码',
  `unzip_pwd` varchar(50) NOT NULL DEFAULT '' COMMENT '解压密码',
  `text_content` text NOT NULL COMMENT '文本内容',
  `lang` varchar(10) NOT NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`nd_id`) USING BTREE,
  KEY `aid` (`aid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品虚拟表';
CREATE TABLE `cms_product_spec_data` (
  `spec_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `aid` int(10) DEFAULT '0' COMMENT '产品ID',
  `spec_mark_id` int(10) DEFAULT '0' COMMENT '规格标记ID',
  `spec_name` varchar(255) DEFAULT '' COMMENT '规格名称',
  `spec_value_id` int(10) DEFAULT '0' COMMENT '规格值ID',
  `spec_value` varchar(255) DEFAULT '' COMMENT '规格值',
  `spec_is_select` tinyint(1) DEFAULT '0' COMMENT '是否选中（0=否，1=是）',
  `open_image` tinyint(1) unsigned DEFAULT '0' COMMENT '规格是否开启图片',
  `spec_image` varchar(255) DEFAULT '' COMMENT '规格图片',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`spec_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COMMENT='产品规格数据表';
CREATE TABLE `cms_product_spec_data_handle` (
  `handle_id` int(10) NOT NULL AUTO_INCREMENT,
  `spec_id` int(10) DEFAULT '0' COMMENT '对应 product_spec_data 数据表',
  `aid` int(10) DEFAULT '0' COMMENT '对应 product_spec_data 数据表',
  `spec_mark_id` int(10) DEFAULT '0' COMMENT '对应 product_spec_data 数据表',
  `spec_name` varchar(255) DEFAULT '' COMMENT '对应 product_spec_data 数据表',
  `spec_value_id` int(10) DEFAULT '0' COMMENT '对应 product_spec_data 数据表',
  `spec_value` varchar(255) DEFAULT '' COMMENT '对应 product_spec_data 数据表',
  `spec_is_select` tinyint(1) DEFAULT '0' COMMENT '对应 product_spec_data 数据表',
  `open_image` tinyint(1) unsigned DEFAULT '0' COMMENT '规格是否开启图片',
  `spec_image` varchar(255) DEFAULT '' COMMENT '规格图片',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '对应 product_spec_data 数据表',
  `add_time` int(11) DEFAULT '0' COMMENT '对应 product_spec_data 数据表',
  `update_time` int(11) DEFAULT '0' COMMENT '对应 product_spec_data 数据表',
  PRIMARY KEY (`handle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品规格表(product_spec_data)预处理规格数据表';
CREATE TABLE `cms_product_spec_preset` (
  `preset_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `preset_mark_id` int(10) DEFAULT '0' COMMENT '预设参数标记ID',
  `preset_name` varchar(100) DEFAULT '' COMMENT '规格名称',
  `preset_value` varchar(100) DEFAULT '' COMMENT '规格值',
  `spec_sync` tinyint(1) unsigned DEFAULT '0' COMMENT '是否同步到已发布的商品规格：0否，1是。',
  `sort_order` int(10) DEFAULT '100' COMMENT '排序号',
  `merchant_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '多商家ID',
  `product_add` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否在商品添加或编辑页添加的规格信息，0否，1是，默认0',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`preset_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='产品规格预设表';
CREATE TABLE `cms_product_spec_value` (
  `value_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '产品ID',
  `spec_value_id` varchar(100) NOT NULL DEFAULT '' COMMENT '规格值ID',
  `spec_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '规格价格',
  `spec_crossed_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '商品规格划线价',
  `spec_stock` int(10) NOT NULL DEFAULT '0' COMMENT '规格库存',
  `spec_sales_num` int(10) NOT NULL DEFAULT '0' COMMENT '销售量',
  `seckill_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '秒杀价格',
  `seckill_stock` int(10) NOT NULL DEFAULT '0' COMMENT '秒杀库存(独立库存，与spec_stock/限时折扣库存不同步)',
  `seckill_sales_num` int(10) NOT NULL DEFAULT '0' COMMENT '秒杀销售量',
  `stock_code` varchar(100) DEFAULT NULL COMMENT '商品编码',
  `is_seckill` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-普通 1-秒杀',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `discount_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '限时折扣价格',
  `discount_stock` int(10) NOT NULL DEFAULT '0' COMMENT '限时折扣库存(独立库存，与spec_stock/秒杀库存不同步)',
  `discount_sales_num` int(10) NOT NULL DEFAULT '0' COMMENT '限时折扣销售量',
  `is_discount` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-普通 1-限时折扣',
  PRIMARY KEY (`value_id`)
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8 COMMENT='产品多规格组装表';
CREATE TABLE `cms_product_spec_value_handle` (
  `handle_id` int(11) NOT NULL AUTO_INCREMENT,
  `value_id` int(20) NOT NULL DEFAULT '0' COMMENT '对应 product_spec_value 数据表',
  `aid` int(10) NOT NULL DEFAULT '0' COMMENT '对应 product_spec_value 数据表',
  `spec_value_id` varchar(100) NOT NULL DEFAULT '' COMMENT '对应 product_spec_value 数据表',
  `spec_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '对应 product_spec_value 数据表',
  `spec_crossed_price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '对应 product_spec_value 数据表',
  `spec_stock` int(10) NOT NULL DEFAULT '0' COMMENT '对应 product_spec_value 数据表',
  `spec_sales_num` int(10) NOT NULL DEFAULT '0' COMMENT '对应 product_spec_value 数据表',
  `stock_code` varchar(100) DEFAULT NULL COMMENT '商品编码',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '对应 product_spec_value 数据表',
  `add_time` int(11) DEFAULT '0' COMMENT '对应 product_spec_value 数据表',
  `update_time` int(11) DEFAULT '0' COMMENT '对应 product_spec_value 数据表',
  `seckill_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '对应 product_spec_value 数据表',
  `seckill_stock` int(10) NOT NULL DEFAULT '0' COMMENT '对应 product_spec_value 数据表',
  `seckill_sales_num` int(10) NOT NULL DEFAULT '0' COMMENT '对应 product_spec_value 数据表',
  `is_seckill` tinyint(1) NOT NULL DEFAULT '0' COMMENT '对应 product_spec_value 数据表',
  `discount_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '对应 product_spec_value 数据表',
  `discount_stock` int(10) NOT NULL DEFAULT '0' COMMENT '对应 product_spec_value 数据表',
  `discount_sales_num` int(10) NOT NULL DEFAULT '0' COMMENT '对应 product_spec_value 数据表',
  `is_discount` tinyint(1) NOT NULL DEFAULT '0' COMMENT '对应 product_spec_value 数据表',
  PRIMARY KEY (`handle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品规格价格表(product_spec_value)预处理规格价格表';
CREATE TABLE `cms_product_users_discount` (
  `users_discount_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `aid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `level_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员等级ID',
  `users_discount_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '会员等级价格',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '下单时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`users_discount_id`),
  KEY `aid` (`aid`) USING BTREE,
  KEY `level_id` (`level_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='产品指定会员等级折扣列表';
CREATE TABLE `cms_quickentry` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(20) DEFAULT '' COMMENT '名称',
  `laytext` varchar(50) DEFAULT '' COMMENT '完整标题',
  `type` smallint(5) DEFAULT '0' COMMENT '归类，1=快捷入口，2=内容统计',
  `controller` varchar(50) DEFAULT '' COMMENT '控制器名',
  `action` varchar(20) DEFAULT '' COMMENT '操作名',
  `vars` varchar(100) DEFAULT '' COMMENT 'URL参数字符串',
  `groups` smallint(5) DEFAULT '0' COMMENT '分组，1=模型',
  `checked` tinyint(4) DEFAULT '0' COMMENT '选中，0=否，1=是',
  `litpic` varchar(100) DEFAULT '',
  `menu_id` int(10) DEFAULT '0' COMMENT '左侧菜单ID',
  `menu_group` smallint(5) DEFAULT '0' COMMENT '菜单分组，0=欢迎页不归属；1=文档相关；2=高级扩展；3=商城模块；4=SEO模块',
  `is_lang` tinyint(1) DEFAULT '0' COMMENT '是否支持多语言，1=支持，0=不支持',
  `auth_role` varchar(500) DEFAULT '' COMMENT '权限',
  `intro` varchar(500) DEFAULT '' COMMENT '描述',
  `statistics_type` int(5) DEFAULT '0' COMMENT 'statistics_data表对应的type值',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，1=有效，0=无效',
  `sort_order` int(10) DEFAULT '0' COMMENT '排序',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=utf8 COMMENT='快捷入口表';
CREATE TABLE `cms_recruit_content` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) DEFAULT '0' COMMENT '文档ID',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `gzdd` varchar(200) NOT NULL DEFAULT '' COMMENT '工作地点',
  `xlyq` varchar(200) NOT NULL DEFAULT '' COMMENT '学历要求',
  `xzdy` varchar(200) NOT NULL DEFAULT '' COMMENT '薪资待遇',
  `gzxz` varchar(200) NOT NULL DEFAULT '' COMMENT '工作性质',
  `gznx` varchar(200) NOT NULL DEFAULT '' COMMENT '工作年限',
  `zprs` varchar(200) NOT NULL DEFAULT '' COMMENT '招聘人数',
  `nnxq` longtext COMMENT '内容详情',
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='附加表';
CREATE TABLE `cms_region` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `name` varchar(32) DEFAULT '' COMMENT '地区名称',
  `level` tinyint(4) DEFAULT '0' COMMENT '地区等级 分省市县区',
  `parent_id` int(10) DEFAULT '0' COMMENT '父id',
  `initial` varchar(5) DEFAULT '' COMMENT '首字母',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`) USING BTREE,
  KEY `level` (`level`) USING BTREE,
  KEY `initial` (`initial`)
) ENGINE=InnoDB AUTO_INCREMENT=47989 DEFAULT CHARSET=utf8 COMMENT='区域表';
INSERT INTO `cms_region` VALUES ('1', '北京市', '1', '0', 'B');
INSERT INTO `cms_region` VALUES ('2', '北京市', '2', '1', 'B');
INSERT INTO `cms_region` VALUES ('3', '东城区', '3', '2', 'D');
INSERT INTO `cms_region` VALUES ('14', '西城区', '3', '2', 'X');
INSERT INTO `cms_region` VALUES ('22', '崇文区', '3', '2', 'C');
INSERT INTO `cms_region` VALUES ('30', '宣武区', '3', '2', 'X');
INSERT INTO `cms_region` VALUES ('39', '朝阳区', '3', '2', 'C');
INSERT INTO `cms_region` VALUES ('83', '丰台区', '3', '2', 'F');
INSERT INTO `cms_region` VALUES ('105', '石景山区', '3', '2', 'S');
INSERT INTO `cms_region` VALUES ('115', '海淀区', '3', '2', 'H');
INSERT INTO `cms_region` VALUES ('145', '门头沟区', '3', '2', 'M');
INSERT INTO `cms_region` VALUES ('159', '房山区', '3', '2', 'F');
INSERT INTO `cms_region` VALUES ('188', '通州区', '3', '2', 'T');
INSERT INTO `cms_region` VALUES ('204', '顺义区', '3', '2', 'S');
INSERT INTO `cms_region` VALUES ('227', '昌平区', '3', '2', 'C');
INSERT INTO `cms_region` VALUES ('245', '大兴区', '3', '2', 'D');
INSERT INTO `cms_region` VALUES ('264', '怀柔区', '3', '2', 'H');
INSERT INTO `cms_region` VALUES ('281', '平谷区', '3', '2', 'P');
INSERT INTO `cms_region` VALUES ('301', '密云区', '3', '2', 'M');
INSERT INTO `cms_region` VALUES ('322', '延庆区', '3', '2', 'Y');
INSERT INTO `cms_region` VALUES ('338', '天津市', '1', '0', 'T');
INSERT INTO `cms_region` VALUES ('339', '天津市', '2', '338', 'T');
INSERT INTO `cms_region` VALUES ('340', '和平区', '3', '339', 'H');
INSERT INTO `cms_region` VALUES ('347', '河东区', '3', '339', 'H');
INSERT INTO `cms_region` VALUES ('361', '河西区', '3', '339', 'H');
INSERT INTO `cms_region` VALUES ('375', '南开区', '3', '339', 'N');
INSERT INTO `cms_region` VALUES ('388', '河北区', '3', '339', 'H');
INSERT INTO `cms_region` VALUES ('399', '红桥区', '3', '339', 'H');
INSERT INTO `cms_region` VALUES ('410', '塘沽区', '3', '339', 'T');
INSERT INTO `cms_region` VALUES ('425', '汉沽区', '3', '339', 'H');
INSERT INTO `cms_region` VALUES ('435', '大港区', '3', '339', 'D');
INSERT INTO `cms_region` VALUES ('445', '东丽区', '3', '339', 'D');
INSERT INTO `cms_region` VALUES ('460', '西青区', '3', '339', 'X');
INSERT INTO `cms_region` VALUES ('473', '津南区', '3', '339', 'J');
INSERT INTO `cms_region` VALUES ('488', '北辰区', '3', '339', 'B');
INSERT INTO `cms_region` VALUES ('504', '武清区', '3', '339', 'W');
INSERT INTO `cms_region` VALUES ('538', '宝坻区', '3', '339', 'B');
INSERT INTO `cms_region` VALUES ('570', '宁河区', '3', '339', 'N');
INSERT INTO `cms_region` VALUES ('586', '静海区', '3', '339', 'J');
INSERT INTO `cms_region` VALUES ('608', '蓟州区', '3', '339', 'J');
INSERT INTO `cms_region` VALUES ('636', '河北省', '1', '0', 'H');
INSERT INTO `cms_region` VALUES ('637', '石家庄市', '2', '636', 'S');
INSERT INTO `cms_region` VALUES ('638', '市辖区', '3', '637', 'S');
INSERT INTO `cms_region` VALUES ('639', '长安区', '3', '637', 'C');
INSERT INTO `cms_region` VALUES ('651', '桥东区', '3', '637', 'Q');
INSERT INTO `cms_region` VALUES ('662', '桥西区', '3', '637', 'Q');
INSERT INTO `cms_region` VALUES ('675', '新华区', '3', '637', 'X');
INSERT INTO `cms_region` VALUES ('691', '井陉矿区', '3', '637', 'J');
INSERT INTO `cms_region` VALUES ('697', '裕华区', '3', '637', 'Y');
INSERT INTO `cms_region` VALUES ('708', '井陉县', '3', '637', 'J');
INSERT INTO `cms_region` VALUES ('726', '正定县', '3', '637', 'Z');
INSERT INTO `cms_region` VALUES ('736', '栾城县', '3', '637', 'L');
INSERT INTO `cms_region` VALUES ('745', '行唐县', '3', '637', 'X');
INSERT INTO `cms_region` VALUES ('761', '灵寿县', '3', '637', 'L');
INSERT INTO `cms_region` VALUES ('777', '高邑县', '3', '637', 'G');
INSERT INTO `cms_region` VALUES ('783', '深泽县', '3', '637', 'S');
INSERT INTO `cms_region` VALUES ('790', '赞皇县', '3', '637', 'Z');
INSERT INTO `cms_region` VALUES ('802', '无极县', '3', '637', 'W');
INSERT INTO `cms_region` VALUES ('814', '平山县', '3', '637', 'P');
INSERT INTO `cms_region` VALUES ('838', '元氏县', '3', '637', 'Y');
INSERT INTO `cms_region` VALUES ('854', '赵县', '3', '637', 'Z');
INSERT INTO `cms_region` VALUES ('866', '辛集市', '3', '637', 'X');
INSERT INTO `cms_region` VALUES ('882', '藁城市', '3', '637', 'G');
INSERT INTO `cms_region` VALUES ('898', '晋州市', '3', '637', 'J');
INSERT INTO `cms_region` VALUES ('909', '新乐市', '3', '637', 'X');
INSERT INTO `cms_region` VALUES ('922', '鹿泉市', '3', '637', 'L');
INSERT INTO `cms_region` VALUES ('936', '唐山市', '2', '636', 'T');
INSERT INTO `cms_region` VALUES ('937', '市辖区', '3', '936', 'S');
INSERT INTO `cms_region` VALUES ('938', '路南区', '3', '936', 'L');
INSERT INTO `cms_region` VALUES ('952', '路北区', '3', '936', 'L');
INSERT INTO `cms_region` VALUES ('965', '古冶区', '3', '936', 'G');
INSERT INTO `cms_region` VALUES ('977', '开平区', '3', '936', 'K');
INSERT INTO `cms_region` VALUES ('989', '丰南区', '3', '936', 'F');
INSERT INTO `cms_region` VALUES ('1007', '丰润区', '3', '936', 'F');
INSERT INTO `cms_region` VALUES ('1034', '滦县', '3', '936', 'L');
INSERT INTO `cms_region` VALUES ('1048', '滦南县', '3', '936', 'L');
INSERT INTO `cms_region` VALUES ('1067', '乐亭县', '3', '936', 'L');
INSERT INTO `cms_region` VALUES ('1085', '迁西县', '3', '936', 'Q');
INSERT INTO `cms_region` VALUES ('1104', '玉田县', '3', '936', 'Y');
INSERT INTO `cms_region` VALUES ('1125', '唐海县', '3', '936', 'T');
INSERT INTO `cms_region` VALUES ('1140', '遵化市', '3', '936', 'Z');
INSERT INTO `cms_region` VALUES ('1168', '迁安市', '3', '936', 'Q');
INSERT INTO `cms_region` VALUES ('1188', '秦皇岛市', '2', '636', 'Q');
INSERT INTO `cms_region` VALUES ('1189', '市辖区', '3', '1188', 'S');
INSERT INTO `cms_region` VALUES ('1190', '海港区', '3', '1188', 'H');
INSERT INTO `cms_region` VALUES ('1208', '山海关区', '3', '1188', 'S');
INSERT INTO `cms_region` VALUES ('1218', '北戴河区', '3', '1188', 'B');
INSERT INTO `cms_region` VALUES ('1223', '青龙县', '3', '1188', 'Q');
INSERT INTO `cms_region` VALUES ('1249', '昌黎县', '3', '1188', 'C');
INSERT INTO `cms_region` VALUES ('1266', '抚宁县', '3', '1188', 'F');
INSERT INTO `cms_region` VALUES ('1278', '卢龙县', '3', '1188', 'L');
INSERT INTO `cms_region` VALUES ('1291', '邯郸市', '2', '636', 'H');
INSERT INTO `cms_region` VALUES ('1292', '市辖区', '3', '1291', 'S');
INSERT INTO `cms_region` VALUES ('1293', '邯山区', '3', '1291', 'H');
INSERT INTO `cms_region` VALUES ('1307', '丛台区', '3', '1291', 'C');
INSERT INTO `cms_region` VALUES ('1319', '复兴区', '3', '1291', 'F');
INSERT INTO `cms_region` VALUES ('1329', '峰峰矿区', '3', '1291', 'F');
INSERT INTO `cms_region` VALUES ('1339', '邯郸县', '3', '1291', 'H');
INSERT INTO `cms_region` VALUES ('1350', '临漳县', '3', '1291', 'L');
INSERT INTO `cms_region` VALUES ('1365', '成安县', '3', '1291', 'C');
INSERT INTO `cms_region` VALUES ('1375', '大名县', '3', '1291', 'D');
INSERT INTO `cms_region` VALUES ('1396', '涉县', '3', '1291', 'S');
INSERT INTO `cms_region` VALUES ('1414', '磁县', '3', '1291', 'C');
INSERT INTO `cms_region` VALUES ('1434', '肥乡县', '3', '1291', 'F');
INSERT INTO `cms_region` VALUES ('1444', '永年县', '3', '1291', 'Y');
INSERT INTO `cms_region` VALUES ('1465', '邱县', '3', '1291', 'Q');
INSERT INTO `cms_region` VALUES ('1473', '鸡泽县', '3', '1291', 'J');
INSERT INTO `cms_region` VALUES ('1481', '广平县', '3', '1291', 'G');
INSERT INTO `cms_region` VALUES ('1489', '馆陶县', '3', '1291', 'G');
INSERT INTO `cms_region` VALUES ('1498', '魏县', '3', '1291', 'W');
INSERT INTO `cms_region` VALUES ('1520', '曲周县', '3', '1291', 'Q');
INSERT INTO `cms_region` VALUES ('1531', '武安市', '3', '1291', 'W');
INSERT INTO `cms_region` VALUES ('1554', '邢台市', '2', '636', 'X');
INSERT INTO `cms_region` VALUES ('1555', '市辖区', '3', '1554', 'S');
INSERT INTO `cms_region` VALUES ('1556', '桥东区', '3', '1554', 'Q');
INSERT INTO `cms_region` VALUES ('1564', '桥西区', '3', '1554', 'Q');
INSERT INTO `cms_region` VALUES ('1572', '邢台县', '3', '1554', 'X');
INSERT INTO `cms_region` VALUES ('1593', '临城县', '3', '1554', 'L');
INSERT INTO `cms_region` VALUES ('1602', '内邱县', '3', '1554', 'N');
INSERT INTO `cms_region` VALUES ('1612', '柏乡县', '3', '1554', 'B');
INSERT INTO `cms_region` VALUES ('1619', '隆尧县', '3', '1554', 'L');
INSERT INTO `cms_region` VALUES ('1633', '任县', '3', '1554', 'R');
INSERT INTO `cms_region` VALUES ('1642', '南和县', '3', '1554', 'N');
INSERT INTO `cms_region` VALUES ('1651', '宁晋县', '3', '1554', 'N');
INSERT INTO `cms_region` VALUES ('1669', '巨鹿县', '3', '1554', 'J');
INSERT INTO `cms_region` VALUES ('1680', '新河县', '3', '1554', 'X');
INSERT INTO `cms_region` VALUES ('1687', '广宗县', '3', '1554', 'G');
INSERT INTO `cms_region` VALUES ('1696', '平乡县', '3', '1554', 'P');
INSERT INTO `cms_region` VALUES ('1704', '威县', '3', '1554', 'W');
INSERT INTO `cms_region` VALUES ('1721', '清河县', '3', '1554', 'Q');
INSERT INTO `cms_region` VALUES ('1728', '临西县', '3', '1554', 'L');
INSERT INTO `cms_region` VALUES ('1738', '南宫市', '3', '1554', 'N');
INSERT INTO `cms_region` VALUES ('1754', '沙河市', '3', '1554', 'S');
INSERT INTO `cms_region` VALUES ('1772', '保定市', '2', '636', 'B');
INSERT INTO `cms_region` VALUES ('1773', '市辖区', '3', '1772', 'S');
INSERT INTO `cms_region` VALUES ('1774', '新市区', '3', '1772', 'X');
INSERT INTO `cms_region` VALUES ('1787', '北市区', '3', '1772', 'B');
INSERT INTO `cms_region` VALUES ('1796', '南市区', '3', '1772', 'N');
INSERT INTO `cms_region` VALUES ('1806', '满城区', '3', '1772', 'M');
INSERT INTO `cms_region` VALUES ('1820', '清苑区', '3', '1772', 'Q');
INSERT INTO `cms_region` VALUES ('1839', '涞水县', '3', '1772', 'L');
INSERT INTO `cms_region` VALUES ('1856', '阜平县', '3', '1772', 'F');
INSERT INTO `cms_region` VALUES ('1870', '徐水区', '3', '1772', 'X');
INSERT INTO `cms_region` VALUES ('1885', '定兴县', '3', '1772', 'D');
INSERT INTO `cms_region` VALUES ('1902', '唐县', '3', '1772', 'T');
INSERT INTO `cms_region` VALUES ('1923', '高阳县', '3', '1772', 'G');
INSERT INTO `cms_region` VALUES ('1933', '容城县', '3', '1772', 'R');
INSERT INTO `cms_region` VALUES ('1942', '涞源县', '3', '1772', 'L');
INSERT INTO `cms_region` VALUES ('1960', '望都县', '3', '1772', 'W');
INSERT INTO `cms_region` VALUES ('1969', '安新县', '3', '1772', 'A');
INSERT INTO `cms_region` VALUES ('1982', '易县', '3', '1772', 'Y');
INSERT INTO `cms_region` VALUES ('2010', '曲阳县', '3', '1772', 'Q');
INSERT INTO `cms_region` VALUES ('2029', '蠡县', '3', '1772', 'L');
INSERT INTO `cms_region` VALUES ('2043', '顺平县', '3', '1772', 'S');
INSERT INTO `cms_region` VALUES ('2054', '博野县', '3', '1772', 'B');
INSERT INTO `cms_region` VALUES ('2062', '雄县', '3', '1772', 'X');
INSERT INTO `cms_region` VALUES ('2072', '涿州市', '3', '1772', 'Z');
INSERT INTO `cms_region` VALUES ('2088', '定州市', '3', '1772', 'D');
INSERT INTO `cms_region` VALUES ('2114', '安国市', '3', '1772', 'A');
INSERT INTO `cms_region` VALUES ('2126', '高碑店市', '3', '1772', 'G');
INSERT INTO `cms_region` VALUES ('2142', '张家口市', '2', '636', 'Z');
INSERT INTO `cms_region` VALUES ('2143', '市辖区', '3', '2142', 'S');
INSERT INTO `cms_region` VALUES ('2144', '桥东区', '3', '2142', 'Q');
INSERT INTO `cms_region` VALUES ('2154', '桥西区', '3', '2142', 'Q');
INSERT INTO `cms_region` VALUES ('2164', '宣化区', '3', '2142', 'X');
INSERT INTO `cms_region` VALUES ('2176', '下花园区', '3', '2142', 'X');
INSERT INTO `cms_region` VALUES ('2183', '宣化县', '3', '2142', 'X');
INSERT INTO `cms_region` VALUES ('2198', '张北县', '3', '2142', 'Z');
INSERT INTO `cms_region` VALUES ('2220', '康保县', '3', '2142', 'K');
INSERT INTO `cms_region` VALUES ('2237', '沽源县', '3', '2142', 'G');
INSERT INTO `cms_region` VALUES ('2256', '尚义县', '3', '2142', 'S');
INSERT INTO `cms_region` VALUES ('2271', '蔚县', '3', '2142', 'W');
INSERT INTO `cms_region` VALUES ('2294', '阳原县', '3', '2142', 'Y');
INSERT INTO `cms_region` VALUES ('2309', '怀安县', '3', '2142', 'H');
INSERT INTO `cms_region` VALUES ('2321', '万全县', '3', '2142', 'W');
INSERT INTO `cms_region` VALUES ('2333', '怀来县', '3', '2142', 'H');
INSERT INTO `cms_region` VALUES ('2351', '涿鹿县', '3', '2142', 'Z');
INSERT INTO `cms_region` VALUES ('2369', '赤城县', '3', '2142', 'C');
INSERT INTO `cms_region` VALUES ('2388', '崇礼县', '3', '2142', 'C');
INSERT INTO `cms_region` VALUES ('2400', '承德市', '2', '636', 'C');
INSERT INTO `cms_region` VALUES ('2401', '市辖区', '3', '2400', 'S');
INSERT INTO `cms_region` VALUES ('2402', '双桥区', '3', '2400', 'S');
INSERT INTO `cms_region` VALUES ('2415', '双滦区', '3', '2400', 'S');
INSERT INTO `cms_region` VALUES ('2422', '鹰手营子矿区', '3', '2400', 'Y');
INSERT INTO `cms_region` VALUES ('2427', '承德县', '3', '2400', 'C');
INSERT INTO `cms_region` VALUES ('2453', '兴隆县', '3', '2400', 'X');
INSERT INTO `cms_region` VALUES ('2474', '平泉县', '3', '2400', 'P');
INSERT INTO `cms_region` VALUES ('2494', '滦平县', '3', '2400', 'L');
INSERT INTO `cms_region` VALUES ('2517', '隆化县', '3', '2400', 'L');
INSERT INTO `cms_region` VALUES ('2543', '丰宁县', '3', '2400', 'F');
INSERT INTO `cms_region` VALUES ('2570', '宽城县', '3', '2400', 'K');
INSERT INTO `cms_region` VALUES ('2589', '围场县', '3', '2400', 'W');
INSERT INTO `cms_region` VALUES ('2629', '沧州市', '2', '636', 'C');
INSERT INTO `cms_region` VALUES ('2630', '市辖区', '3', '2629', 'S');
INSERT INTO `cms_region` VALUES ('2631', '新华区', '3', '2629', 'X');
INSERT INTO `cms_region` VALUES ('2639', '运河区', '3', '2629', 'Y');
INSERT INTO `cms_region` VALUES ('2648', '沧县', '3', '2629', 'C');
INSERT INTO `cms_region` VALUES ('2668', '青县', '3', '2629', 'Q');
INSERT INTO `cms_region` VALUES ('2680', '东光县', '3', '2629', 'D');
INSERT INTO `cms_region` VALUES ('2690', '海兴县', '3', '2629', 'H');
INSERT INTO `cms_region` VALUES ('2701', '盐山县', '3', '2629', 'Y');
INSERT INTO `cms_region` VALUES ('2714', '肃宁县', '3', '2629', 'S');
INSERT INTO `cms_region` VALUES ('2724', '南皮县', '3', '2629', 'N');
INSERT INTO `cms_region` VALUES ('2734', '吴桥县', '3', '2629', 'W');
INSERT INTO `cms_region` VALUES ('2745', '献县', '3', '2629', 'X');
INSERT INTO `cms_region` VALUES ('2765', '孟村县', '3', '2629', 'M');
INSERT INTO `cms_region` VALUES ('2772', '泊头市', '3', '2629', 'B');
INSERT INTO `cms_region` VALUES ('2788', '任邱市', '3', '2629', 'R');
INSERT INTO `cms_region` VALUES ('2809', '黄骅市', '3', '2629', 'H');
INSERT INTO `cms_region` VALUES ('2828', '河间市', '3', '2629', 'H');
INSERT INTO `cms_region` VALUES ('2849', '廊坊市', '2', '636', 'L');
INSERT INTO `cms_region` VALUES ('2850', '市辖区', '3', '2849', 'S');
INSERT INTO `cms_region` VALUES ('2851', '安次区', '3', '2849', 'A');
INSERT INTO `cms_region` VALUES ('2862', '广阳区', '3', '2849', 'G');
INSERT INTO `cms_region` VALUES ('2873', '固安县', '3', '2849', 'G');
INSERT INTO `cms_region` VALUES ('2883', '永清县', '3', '2849', 'Y');
INSERT INTO `cms_region` VALUES ('2895', '香河县', '3', '2849', 'X');
INSERT INTO `cms_region` VALUES ('2906', '大城县', '3', '2849', 'D');
INSERT INTO `cms_region` VALUES ('2918', '文安县', '3', '2849', 'W');
INSERT INTO `cms_region` VALUES ('2932', '大厂县', '3', '2849', 'D');
INSERT INTO `cms_region` VALUES ('2939', '霸州市', '3', '2849', 'B');
INSERT INTO `cms_region` VALUES ('2953', '三河市', '3', '2849', 'S');
INSERT INTO `cms_region` VALUES ('2968', '衡水市', '2', '636', 'H');
INSERT INTO `cms_region` VALUES ('2969', '市辖区', '3', '2968', 'S');
INSERT INTO `cms_region` VALUES ('2970', '桃城区', '3', '2968', 'T');
INSERT INTO `cms_region` VALUES ('2983', '枣强县', '3', '2968', 'Z');
INSERT INTO `cms_region` VALUES ('2995', '武邑县', '3', '2968', 'W');
INSERT INTO `cms_region` VALUES ('3005', '武强县', '3', '2968', 'W');
INSERT INTO `cms_region` VALUES ('3012', '饶阳县', '3', '2968', 'R');
INSERT INTO `cms_region` VALUES ('3020', '安平县', '3', '2968', 'A');
INSERT INTO `cms_region` VALUES ('3029', '故城县', '3', '2968', 'G');
INSERT INTO `cms_region` VALUES ('3043', '景县', '3', '2968', 'J');
INSERT INTO `cms_region` VALUES ('3060', '阜城县', '3', '2968', 'F');
INSERT INTO `cms_region` VALUES ('3071', '冀州市', '3', '2968', 'J');
INSERT INTO `cms_region` VALUES ('3083', '深州市', '3', '2968', 'S');
INSERT INTO `cms_region` VALUES ('3102', '山西省', '1', '0', 'S');
INSERT INTO `cms_region` VALUES ('3103', '太原市', '2', '3102', 'T');
INSERT INTO `cms_region` VALUES ('3104', '市辖区', '3', '3103', 'S');
INSERT INTO `cms_region` VALUES ('3105', '小店区(人口含高新经济区)', '3', '3103', 'X');
INSERT INTO `cms_region` VALUES ('3117', '迎泽区', '3', '3103', 'Y');
INSERT INTO `cms_region` VALUES ('3126', '杏花岭区', '3', '3103', 'X');
INSERT INTO `cms_region` VALUES ('3140', '尖草坪区', '3', '3103', 'J');
INSERT INTO `cms_region` VALUES ('3155', '万柏林区', '3', '3103', 'W');
INSERT INTO `cms_region` VALUES ('3171', '晋源区', '3', '3103', 'J');
INSERT INTO `cms_region` VALUES ('3178', '清徐县', '3', '3103', 'Q');
INSERT INTO `cms_region` VALUES ('3188', '阳曲县', '3', '3103', 'Y');
INSERT INTO `cms_region` VALUES ('3200', '娄烦县', '3', '3103', 'L');
INSERT INTO `cms_region` VALUES ('3209', '古交市', '3', '3103', 'G');
INSERT INTO `cms_region` VALUES ('3224', '大同市', '2', '3102', 'D');
INSERT INTO `cms_region` VALUES ('3225', '市辖区', '3', '3224', 'S');
INSERT INTO `cms_region` VALUES ('3226', '大同市城区', '3', '3224', 'D');
INSERT INTO `cms_region` VALUES ('3241', '矿区', '3', '3224', 'K');
INSERT INTO `cms_region` VALUES ('3266', '南郊区', '3', '3224', 'N');
INSERT INTO `cms_region` VALUES ('3277', '新荣区', '3', '3224', 'X');
INSERT INTO `cms_region` VALUES ('3286', '阳高县', '3', '3224', 'Y');
INSERT INTO `cms_region` VALUES ('3300', '天镇县', '3', '3224', 'T');
INSERT INTO `cms_region` VALUES ('3312', '广灵县', '3', '3224', 'G');
INSERT INTO `cms_region` VALUES ('3322', '灵丘县', '3', '3224', 'L');
INSERT INTO `cms_region` VALUES ('3335', '浑源县', '3', '3224', 'H');
INSERT INTO `cms_region` VALUES ('3354', '左云县', '3', '3224', 'Z');
INSERT INTO `cms_region` VALUES ('3364', '大同县', '3', '3224', 'D');
INSERT INTO `cms_region` VALUES ('3379', '阳泉市', '2', '3102', 'Y');
INSERT INTO `cms_region` VALUES ('3380', '市辖区', '3', '3379', 'S');
INSERT INTO `cms_region` VALUES ('3381', '城区', '3', '3379', 'C');
INSERT INTO `cms_region` VALUES ('3388', '矿区', '3', '3379', 'K');
INSERT INTO `cms_region` VALUES ('3395', '郊区', '3', '3379', 'J');
INSERT INTO `cms_region` VALUES ('3405', '平定县', '3', '3379', 'P');
INSERT INTO `cms_region` VALUES ('3416', '盂县', '3', '3379', 'Y');
INSERT INTO `cms_region` VALUES ('3431', '长治市', '2', '3102', 'C');
INSERT INTO `cms_region` VALUES ('3432', '市辖区', '3', '3431', 'S');
INSERT INTO `cms_region` VALUES ('3433', '长治市城区', '3', '3431', 'C');
INSERT INTO `cms_region` VALUES ('3445', '长治市郊区', '3', '3431', 'C');
INSERT INTO `cms_region` VALUES ('3454', '长治县', '3', '3431', 'C');
INSERT INTO `cms_region` VALUES ('3466', '襄垣县', '3', '3431', 'X');
INSERT INTO `cms_region` VALUES ('3478', '屯留县', '3', '3431', 'T');
INSERT INTO `cms_region` VALUES ('3493', '平顺县', '3', '3431', 'P');
INSERT INTO `cms_region` VALUES ('3506', '黎城县', '3', '3431', 'L');
INSERT INTO `cms_region` VALUES ('3516', '壶关县', '3', '3431', 'H');
INSERT INTO `cms_region` VALUES ('3530', '长子县', '3', '3431', 'C');
INSERT INTO `cms_region` VALUES ('3543', '武乡县', '3', '3431', 'W');
INSERT INTO `cms_region` VALUES ('3558', '沁县', '3', '3431', 'Q');
INSERT INTO `cms_region` VALUES ('3572', '沁源县', '3', '3431', 'Q');
INSERT INTO `cms_region` VALUES ('3587', '潞城市', '3', '3431', 'L');
INSERT INTO `cms_region` VALUES ('3597', '晋城市', '2', '3102', 'J');
INSERT INTO `cms_region` VALUES ('3598', '市辖区', '3', '3597', 'S');
INSERT INTO `cms_region` VALUES ('3599', '晋城市城区', '3', '3597', 'J');
INSERT INTO `cms_region` VALUES ('3608', '沁水县', '3', '3597', 'Q');
INSERT INTO `cms_region` VALUES ('3623', '阳城县', '3', '3597', 'Y');
INSERT INTO `cms_region` VALUES ('3642', '陵川县', '3', '3597', 'L');
INSERT INTO `cms_region` VALUES ('3655', '泽州县', '3', '3597', 'Z');
INSERT INTO `cms_region` VALUES ('3673', '高平市', '3', '3597', 'G');
INSERT INTO `cms_region` VALUES ('3690', '朔州市', '2', '3102', 'S');
INSERT INTO `cms_region` VALUES ('3691', '市辖区', '3', '3690', 'S');
INSERT INTO `cms_region` VALUES ('3692', '朔城区', '3', '3690', 'S');
INSERT INTO `cms_region` VALUES ('3709', '平鲁区', '3', '3690', 'P');
INSERT INTO `cms_region` VALUES ('3723', '山阴县', '3', '3690', 'S');
INSERT INTO `cms_region` VALUES ('3739', '应县', '3', '3690', 'Y');
INSERT INTO `cms_region` VALUES ('3752', '右玉县', '3', '3690', 'Y');
INSERT INTO `cms_region` VALUES ('3763', '怀仁县', '3', '3690', 'H');
INSERT INTO `cms_region` VALUES ('3776', '晋中市', '2', '3102', 'J');
INSERT INTO `cms_region` VALUES ('3777', '市辖区', '3', '3776', 'S');
INSERT INTO `cms_region` VALUES ('3778', '榆次区', '3', '3776', 'Y');
INSERT INTO `cms_region` VALUES ('3799', '榆社县', '3', '3776', 'Y');
INSERT INTO `cms_region` VALUES ('3810', '左权县', '3', '3776', 'Z');
INSERT INTO `cms_region` VALUES ('3822', '和顺县', '3', '3776', 'H');
INSERT INTO `cms_region` VALUES ('3833', '昔阳县', '3', '3776', 'X');
INSERT INTO `cms_region` VALUES ('3846', '寿阳县', '3', '3776', 'S');
INSERT INTO `cms_region` VALUES ('3861', '太谷县', '3', '3776', 'T');
INSERT INTO `cms_region` VALUES ('3871', '祁县', '3', '3776', 'Q');
INSERT INTO `cms_region` VALUES ('3880', '平遥县', '3', '3776', 'P');
INSERT INTO `cms_region` VALUES ('3895', '灵石县', '3', '3776', 'L');
INSERT INTO `cms_region` VALUES ('3908', '介休市', '3', '3776', 'J');
INSERT INTO `cms_region` VALUES ('3925', '运城市', '2', '3102', 'Y');
INSERT INTO `cms_region` VALUES ('3926', '市辖区', '3', '3925', 'S');
INSERT INTO `cms_region` VALUES ('3927', '盐湖区', '3', '3925', 'Y');
INSERT INTO `cms_region` VALUES ('3950', '临猗县', '3', '3925', 'L');
INSERT INTO `cms_region` VALUES ('3967', '万荣县', '3', '3925', 'W');
INSERT INTO `cms_region` VALUES ('3982', '闻喜县', '3', '3925', 'W');
INSERT INTO `cms_region` VALUES ('3996', '稷山县', '3', '3925', 'J');
INSERT INTO `cms_region` VALUES ('4004', '新绛县', '3', '3925', 'X');
INSERT INTO `cms_region` VALUES ('4013', '绛县', '3', '3925', 'J');
INSERT INTO `cms_region` VALUES ('4024', '垣曲县', '3', '3925', 'Y');
INSERT INTO `cms_region` VALUES ('4036', '夏县', '3', '3925', 'X');
INSERT INTO `cms_region` VALUES ('4048', '平陆县', '3', '3925', 'P');
INSERT INTO `cms_region` VALUES ('4059', '芮城县', '3', '3925', 'R');
INSERT INTO `cms_region` VALUES ('4070', '永济市', '3', '3925', 'Y');
INSERT INTO `cms_region` VALUES ('4082', '河津市', '3', '3925', 'H');
INSERT INTO `cms_region` VALUES ('4093', '忻州市', '2', '3102', 'X');
INSERT INTO `cms_region` VALUES ('4094', '市辖区', '3', '4093', 'S');
INSERT INTO `cms_region` VALUES ('4095', '忻府区', '3', '4093', 'X');
INSERT INTO `cms_region` VALUES ('4116', '定襄县', '3', '4093', 'D');
INSERT INTO `cms_region` VALUES ('4126', '五台县', '3', '4093', 'W');
INSERT INTO `cms_region` VALUES ('4146', '代县', '3', '4093', 'D');
INSERT INTO `cms_region` VALUES ('4158', '繁峙县', '3', '4093', 'F');
INSERT INTO `cms_region` VALUES ('4172', '宁武县', '3', '4093', 'N');
INSERT INTO `cms_region` VALUES ('4189', '静乐县', '3', '4093', 'J');
INSERT INTO `cms_region` VALUES ('4204', '神池县', '3', '4093', 'S');
INSERT INTO `cms_region` VALUES ('4215', '五寨县', '3', '4093', 'W');
INSERT INTO `cms_region` VALUES ('4228', '岢岚县', '3', '4093', 'K');
INSERT INTO `cms_region` VALUES ('4241', '河曲县', '3', '4093', 'H');
INSERT INTO `cms_region` VALUES ('4255', '保德县', '3', '4093', 'B');
INSERT INTO `cms_region` VALUES ('4269', '偏关县', '3', '4093', 'P');
INSERT INTO `cms_region` VALUES ('4280', '原平市', '3', '4093', 'Y');
INSERT INTO `cms_region` VALUES ('4304', '临汾市', '2', '3102', 'L');
INSERT INTO `cms_region` VALUES ('4305', '市辖区', '3', '4304', 'S');
INSERT INTO `cms_region` VALUES ('4306', '尧都区', '3', '4304', 'Y');
INSERT INTO `cms_region` VALUES ('4333', '曲沃县', '3', '4304', 'Q');
INSERT INTO `cms_region` VALUES ('4341', '翼城县', '3', '4304', 'Y');
INSERT INTO `cms_region` VALUES ('4352', '襄汾县', '3', '4304', 'X');
INSERT INTO `cms_region` VALUES ('4366', '洪洞县', '3', '4304', 'H');
INSERT INTO `cms_region` VALUES ('4383', '古县', '3', '4304', 'G');
INSERT INTO `cms_region` VALUES ('4391', '安泽县', '3', '4304', 'A');
INSERT INTO `cms_region` VALUES ('4399', '浮山县', '3', '4304', 'F');
INSERT INTO `cms_region` VALUES ('4409', '吉县', '3', '4304', 'J');
INSERT INTO `cms_region` VALUES ('4418', '乡宁县', '3', '4304', 'X');
INSERT INTO `cms_region` VALUES ('4429', '大宁县', '3', '4304', 'D');
INSERT INTO `cms_region` VALUES ('4436', '隰县', '3', '4304', 'X');
INSERT INTO `cms_region` VALUES ('4445', '永和县', '3', '4304', 'Y');
INSERT INTO `cms_region` VALUES ('4453', '蒲县', '3', '4304', 'P');
INSERT INTO `cms_region` VALUES ('4463', '汾西县', '3', '4304', 'F');
INSERT INTO `cms_region` VALUES ('4472', '侯马市', '3', '4304', 'H');
INSERT INTO `cms_region` VALUES ('4481', '霍州市', '3', '4304', 'H');
INSERT INTO `cms_region` VALUES ('4494', '吕梁市', '2', '3102', 'L');
INSERT INTO `cms_region` VALUES ('4495', '市辖区', '3', '4494', 'S');
INSERT INTO `cms_region` VALUES ('4496', '离石区', '3', '4494', 'L');
INSERT INTO `cms_region` VALUES ('4509', '文水县', '3', '4494', 'W');
INSERT INTO `cms_region` VALUES ('4522', '交城县', '3', '4494', 'J');
INSERT INTO `cms_region` VALUES ('4533', '兴县', '3', '4494', 'X');
INSERT INTO `cms_region` VALUES ('4551', '临县', '3', '4494', 'L');
INSERT INTO `cms_region` VALUES ('4575', '柳林县', '3', '4494', 'L');
INSERT INTO `cms_region` VALUES ('4591', '石楼县', '3', '4494', 'S');
INSERT INTO `cms_region` VALUES ('4601', '岚县', '3', '4494', 'L');
INSERT INTO `cms_region` VALUES ('4614', '方山县', '3', '4494', 'F');
INSERT INTO `cms_region` VALUES ('4622', '中阳县', '3', '4494', 'Z');
INSERT INTO `cms_region` VALUES ('4630', '交口县', '3', '4494', 'J');
INSERT INTO `cms_region` VALUES ('4638', '孝义市', '3', '4494', 'X');
INSERT INTO `cms_region` VALUES ('4655', '汾阳市', '3', '4494', 'F');
INSERT INTO `cms_region` VALUES ('4670', '内蒙古自治区', '1', '0', 'N');
INSERT INTO `cms_region` VALUES ('4671', '呼和浩特市', '2', '4670', 'H');
INSERT INTO `cms_region` VALUES ('4672', '市辖区', '3', '4671', 'S');
INSERT INTO `cms_region` VALUES ('4673', '新城区', '3', '4671', 'X');
INSERT INTO `cms_region` VALUES ('4684', '回民区', '3', '4671', 'H');
INSERT INTO `cms_region` VALUES ('4693', '玉泉区', '3', '4671', 'Y');
INSERT INTO `cms_region` VALUES ('4702', '赛罕区', '3', '4671', 'S');
INSERT INTO `cms_region` VALUES ('4715', '土左旗', '3', '4671', 'T');
INSERT INTO `cms_region` VALUES ('4727', '托克托县', '3', '4671', 'T');
INSERT INTO `cms_region` VALUES ('4733', '和林格尔县', '3', '4671', 'H');
INSERT INTO `cms_region` VALUES ('4742', '清水河县', '3', '4671', 'Q');
INSERT INTO `cms_region` VALUES ('4749', '武川县', '3', '4671', 'W');
INSERT INTO `cms_region` VALUES ('4759', '包头市', '2', '4670', 'B');
INSERT INTO `cms_region` VALUES ('4760', '市辖区', '3', '4759', 'S');
INSERT INTO `cms_region` VALUES ('4761', '东河区', '3', '4759', 'D');
INSERT INTO `cms_region` VALUES ('4775', '昆都仑区', '3', '4759', 'K');
INSERT INTO `cms_region` VALUES ('4791', '青山区', '3', '4759', 'Q');
INSERT INTO `cms_region` VALUES ('4803', '石拐区', '3', '4759', 'S');
INSERT INTO `cms_region` VALUES ('4810', '白云鄂博矿区', '3', '4759', 'B');
INSERT INTO `cms_region` VALUES ('4813', '九原区', '3', '4759', 'J');
INSERT INTO `cms_region` VALUES ('4823', '土默特右旗', '3', '4759', 'T');
INSERT INTO `cms_region` VALUES ('4833', '固阳县', '3', '4759', 'G');
INSERT INTO `cms_region` VALUES ('4840', '达茂联合旗', '3', '4759', 'D');
INSERT INTO `cms_region` VALUES ('4849', '乌海市', '2', '4670', 'W');
INSERT INTO `cms_region` VALUES ('4850', '乌海市辖区', '3', '4849', 'W');
INSERT INTO `cms_region` VALUES ('4851', '海勃湾区', '3', '4849', 'H');
INSERT INTO `cms_region` VALUES ('4859', '海南区', '3', '4849', 'H');
INSERT INTO `cms_region` VALUES ('4865', '乌达区', '3', '4849', 'W');
INSERT INTO `cms_region` VALUES ('4874', '赤峰市', '2', '4670', 'C');
INSERT INTO `cms_region` VALUES ('4875', '市辖区', '3', '4874', 'S');
INSERT INTO `cms_region` VALUES ('4876', '红山区', '3', '4874', 'H');
INSERT INTO `cms_region` VALUES ('4888', '元宝山区', '3', '4874', 'Y');
INSERT INTO `cms_region` VALUES ('4896', '松山区', '3', '4874', 'S');
INSERT INTO `cms_region` VALUES ('4919', '阿鲁科尔沁旗', '3', '4874', 'A');
INSERT INTO `cms_region` VALUES ('4932', '巴林左旗', '3', '4874', 'B');
INSERT INTO `cms_region` VALUES ('4944', '巴林右旗', '3', '4874', 'B');
INSERT INTO `cms_region` VALUES ('4953', '林西县', '3', '4874', 'L');
INSERT INTO `cms_region` VALUES ('4963', '克什克腾旗', '3', '4874', 'K');
INSERT INTO `cms_region` VALUES ('4975', '翁牛特旗', '3', '4874', 'W');
INSERT INTO `cms_region` VALUES ('4988', '喀喇沁旗', '3', '4874', 'K');
INSERT INTO `cms_region` VALUES ('4999', '宁城县', '3', '4874', 'N');
INSERT INTO `cms_region` VALUES ('5013', '敖汉旗', '3', '4874', 'A');
INSERT INTO `cms_region` VALUES ('5029', '通辽市', '2', '4670', 'T');
INSERT INTO `cms_region` VALUES ('5030', '市辖区', '3', '5029', 'S');
INSERT INTO `cms_region` VALUES ('5031', '科尔沁区', '3', '5029', 'K');
INSERT INTO `cms_region` VALUES ('5062', '科尔沁左翼中旗', '3', '5029', 'K');
INSERT INTO `cms_region` VALUES ('5079', '科左后旗', '3', '5029', 'K');
INSERT INTO `cms_region` VALUES ('5104', '开鲁县', '3', '5029', 'K');
INSERT INTO `cms_region` VALUES ('5118', '库伦旗', '3', '5029', 'K');
INSERT INTO `cms_region` VALUES ('5125', '奈曼旗', '3', '5029', 'N');
INSERT INTO `cms_region` VALUES ('5139', '扎鲁特旗', '3', '5029', 'Z');
INSERT INTO `cms_region` VALUES ('5155', '霍林郭勒市', '3', '5029', 'H');
INSERT INTO `cms_region` VALUES ('5162', '鄂尔多斯市', '2', '4670', 'E');
INSERT INTO `cms_region` VALUES ('5163', '东胜区', '3', '5162', 'D');
INSERT INTO `cms_region` VALUES ('5176', '达拉特旗', '3', '5162', 'D');
INSERT INTO `cms_region` VALUES ('5185', '准格尔旗', '3', '5162', 'Z');
INSERT INTO `cms_region` VALUES ('5195', '鄂托克前旗', '3', '5162', 'E');
INSERT INTO `cms_region` VALUES ('5201', '鄂托克旗', '3', '5162', 'E');
INSERT INTO `cms_region` VALUES ('5210', '杭锦旗', '3', '5162', 'H');
INSERT INTO `cms_region` VALUES ('5219', '乌审旗', '3', '5162', 'W');
INSERT INTO `cms_region` VALUES ('5228', '伊金霍洛旗', '3', '5162', 'Y');
INSERT INTO `cms_region` VALUES ('5236', '呼伦贝尔市', '2', '4670', 'H');
INSERT INTO `cms_region` VALUES ('5237', '市辖区', '3', '5236', 'S');
INSERT INTO `cms_region` VALUES ('5238', '海拉尔区', '3', '5236', 'H');
INSERT INTO `cms_region` VALUES ('5249', '阿荣旗', '3', '5236', 'A');
INSERT INTO `cms_region` VALUES ('5262', '莫力达瓦旗', '3', '5236', 'M');
INSERT INTO `cms_region` VALUES ('5277', '鄂伦春旗', '3', '5236', 'E');
INSERT INTO `cms_region` VALUES ('5303', '鄂温旗', '3', '5236', 'E');
INSERT INTO `cms_region` VALUES ('5314', '陈巴尔虎旗镇', '3', '5236', 'C');
INSERT INTO `cms_region` VALUES ('5323', '新巴尔虎左旗', '3', '5236', 'X');
INSERT INTO `cms_region` VALUES ('5330', '新巴尔虎右旗', '3', '5236', 'X');
INSERT INTO `cms_region` VALUES ('5337', '满洲里市', '3', '5236', 'M');
INSERT INTO `cms_region` VALUES ('5354', '牙克石市', '3', '5236', 'Y');
INSERT INTO `cms_region` VALUES ('5371', '扎兰屯市', '3', '5236', 'Z');
INSERT INTO `cms_region` VALUES ('5397', '额尔古纳市', '3', '5236', 'E');
INSERT INTO `cms_region` VALUES ('5410', '根河市', '3', '5236', 'G');
INSERT INTO `cms_region` VALUES ('5418', '巴彦淖尔市', '2', '4670', 'B');
INSERT INTO `cms_region` VALUES ('5419', '市辖区', '3', '5418', 'S');
INSERT INTO `cms_region` VALUES ('5420', '临河区', '3', '5418', 'L');
INSERT INTO `cms_region` VALUES ('5440', '五原县', '3', '5418', 'W');
INSERT INTO `cms_region` VALUES ('5450', '磴口县', '3', '5418', 'D');
INSERT INTO `cms_region` VALUES ('5461', '乌拉特前旗', '3', '5418', 'W');
INSERT INTO `cms_region` VALUES ('5477', '乌拉特中旗', '3', '5418', 'W');
INSERT INTO `cms_region` VALUES ('5489', '乌拉特后旗', '3', '5418', 'W');
INSERT INTO `cms_region` VALUES ('5495', '杭锦后旗', '3', '5418', 'H');
INSERT INTO `cms_region` VALUES ('5505', '乌兰察布市', '2', '4670', 'W');
INSERT INTO `cms_region` VALUES ('5506', '市辖区', '3', '5505', 'S');
INSERT INTO `cms_region` VALUES ('5507', '集宁区', '3', '5505', 'J');
INSERT INTO `cms_region` VALUES ('5518', '卓资县', '3', '5505', 'Z');
INSERT INTO `cms_region` VALUES ('5526', '化德县', '3', '5505', 'H');
INSERT INTO `cms_region` VALUES ('5532', '商都县', '3', '5505', 'S');
INSERT INTO `cms_region` VALUES ('5542', '兴和县', '3', '5505', 'X');
INSERT INTO `cms_region` VALUES ('5551', '凉城县', '3', '5505', 'L');
INSERT INTO `cms_region` VALUES ('5562', '察哈尔右翼前旗', '3', '5505', 'C');
INSERT INTO `cms_region` VALUES ('5571', '察右中旗', '3', '5505', 'C');
INSERT INTO `cms_region` VALUES ('5582', '察哈尔右翼后旗', '3', '5505', 'C');
INSERT INTO `cms_region` VALUES ('5590', '四子王旗', '3', '5505', 'S');
INSERT INTO `cms_region` VALUES ('5603', '丰镇市', '3', '5505', 'F');
INSERT INTO `cms_region` VALUES ('5616', '兴安盟', '2', '4670', 'X');
INSERT INTO `cms_region` VALUES ('5617', '乌兰浩特市', '3', '5616', 'W');
INSERT INTO `cms_region` VALUES ('5629', '阿尔山市', '3', '5616', 'A');
INSERT INTO `cms_region` VALUES ('5636', '科右前旗', '3', '5616', 'K');
INSERT INTO `cms_region` VALUES ('5655', '科右中旗', '3', '5616', 'K');
INSERT INTO `cms_region` VALUES ('5677', '扎赉特旗', '3', '5616', 'Z');
INSERT INTO `cms_region` VALUES ('5692', '突泉县', '3', '5616', 'T');
INSERT INTO `cms_region` VALUES ('5702', '锡林郭勒盟', '2', '4670', 'X');
INSERT INTO `cms_region` VALUES ('5703', '二连浩特市', '3', '5702', 'E');
INSERT INTO `cms_region` VALUES ('5709', '锡林浩特市', '3', '5702', 'X');
INSERT INTO `cms_region` VALUES ('5723', '阿巴嘎旗', '3', '5702', 'A');
INSERT INTO `cms_region` VALUES ('5731', '苏尼特左旗', '3', '5702', 'S');
INSERT INTO `cms_region` VALUES ('5738', '苏尼特右旗', '3', '5702', 'S');
INSERT INTO `cms_region` VALUES ('5745', '东乌珠穆沁旗', '3', '5702', 'D');
INSERT INTO `cms_region` VALUES ('5758', '西乌珠穆沁旗', '3', '5702', 'X');
INSERT INTO `cms_region` VALUES ('5766', '太仆寺旗', '3', '5702', 'T');
INSERT INTO `cms_region` VALUES ('5774', '镶黄旗', '3', '5702', 'X');
INSERT INTO `cms_region` VALUES ('5778', '正镶白旗', '3', '5702', 'Z');
INSERT INTO `cms_region` VALUES ('5785', '正蓝旗', '3', '5702', 'Z');
INSERT INTO `cms_region` VALUES ('5794', '多伦县', '3', '5702', 'D');
INSERT INTO `cms_region` VALUES ('5799', '阿拉善盟', '2', '4670', 'A');
INSERT INTO `cms_region` VALUES ('5800', '阿拉善左旗', '3', '5799', 'A');
INSERT INTO `cms_region` VALUES ('5814', '阿拉善右旗', '3', '5799', 'A');
INSERT INTO `cms_region` VALUES ('5820', '额济纳旗', '3', '5799', 'E');
INSERT INTO `cms_region` VALUES ('5827', '辽宁省', '1', '0', 'L');
INSERT INTO `cms_region` VALUES ('5828', '沈阳市', '2', '5827', 'S');
INSERT INTO `cms_region` VALUES ('5829', '市辖区', '3', '5828', 'S');
INSERT INTO `cms_region` VALUES ('5830', '和平区', '3', '5828', 'H');
INSERT INTO `cms_region` VALUES ('5848', '沈河区', '3', '5828', 'S');
INSERT INTO `cms_region` VALUES ('5859', '大东区', '3', '5828', 'D');
INSERT INTO `cms_region` VALUES ('5873', '皇姑区', '3', '5828', 'H');
INSERT INTO `cms_region` VALUES ('5894', '铁西区', '3', '5828', 'T');
INSERT INTO `cms_region` VALUES ('5909', '苏家屯区', '3', '5828', 'S');
INSERT INTO `cms_region` VALUES ('5934', '东陵区', '3', '5828', 'D');
INSERT INTO `cms_region` VALUES ('5954', '新城子区', '3', '5828', 'X');
INSERT INTO `cms_region` VALUES ('5975', '于洪区', '3', '5828', 'Y');
INSERT INTO `cms_region` VALUES ('5998', '辽中县', '3', '5828', 'L');
INSERT INTO `cms_region` VALUES ('6020', '康平县', '3', '5828', 'K');
INSERT INTO `cms_region` VALUES ('6038', '法库县', '3', '5828', 'F');
INSERT INTO `cms_region` VALUES ('6058', '新民市', '3', '5828', 'X');
INSERT INTO `cms_region` VALUES ('6088', '大连市', '2', '5827', 'D');
INSERT INTO `cms_region` VALUES ('6089', '市辖区', '3', '6088', 'S');
INSERT INTO `cms_region` VALUES ('6090', '中山区', '3', '6088', 'Z');
INSERT INTO `cms_region` VALUES ('6099', '西岗区', '3', '6088', 'X');
INSERT INTO `cms_region` VALUES ('6107', '沙河口区', '3', '6088', 'S');
INSERT INTO `cms_region` VALUES ('6117', '甘井子区', '3', '6088', 'G');
INSERT INTO `cms_region` VALUES ('6137', '旅顺口区', '3', '6088', 'L');
INSERT INTO `cms_region` VALUES ('6153', '金州区', '3', '6088', 'J');
INSERT INTO `cms_region` VALUES ('6178', '长海县', '3', '6088', 'C');
INSERT INTO `cms_region` VALUES ('6184', '瓦房店市', '3', '6088', 'W');
INSERT INTO `cms_region` VALUES ('6217', '普兰店市', '3', '6088', 'P');
INSERT INTO `cms_region` VALUES ('6239', '庄河市', '3', '6088', 'Z');
INSERT INTO `cms_region` VALUES ('6266', '鞍山市', '2', '5827', 'A');
INSERT INTO `cms_region` VALUES ('6267', '市辖区', '3', '6266', 'S');
INSERT INTO `cms_region` VALUES ('6268', '铁东区', '3', '6266', 'T');
INSERT INTO `cms_region` VALUES ('6282', '铁西区', '3', '6266', 'T');
INSERT INTO `cms_region` VALUES ('6293', '立山区', '3', '6266', 'L');
INSERT INTO `cms_region` VALUES ('6303', '千山区', '3', '6266', 'Q');
INSERT INTO `cms_region` VALUES ('6316', '台安县', '3', '6266', 'T');
INSERT INTO `cms_region` VALUES ('6331', '岫岩县', '3', '6266', 'X');
INSERT INTO `cms_region` VALUES ('6354', '海城市', '3', '6266', 'H');
INSERT INTO `cms_region` VALUES ('6384', '抚顺市', '2', '5827', 'F');
INSERT INTO `cms_region` VALUES ('6385', '市辖区', '3', '6384', 'S');
INSERT INTO `cms_region` VALUES ('6386', '新抚区', '3', '6384', 'X');
INSERT INTO `cms_region` VALUES ('6395', '东洲区', '3', '6384', 'D');
INSERT INTO `cms_region` VALUES ('6409', '望花区', '3', '6384', 'W');
INSERT INTO `cms_region` VALUES ('6422', '顺城区', '3', '6384', 'S');
INSERT INTO `cms_region` VALUES ('6432', '抚顺县', '3', '6384', 'F');
INSERT INTO `cms_region` VALUES ('6445', '新宾县', '3', '6384', 'X');
INSERT INTO `cms_region` VALUES ('6461', '清原县', '3', '6384', 'Q');
INSERT INTO `cms_region` VALUES ('6476', '本溪市', '2', '5827', 'B');
INSERT INTO `cms_region` VALUES ('6477', '市辖区', '3', '6476', 'S');
INSERT INTO `cms_region` VALUES ('6478', '平山区', '3', '6476', 'P');
INSERT INTO `cms_region` VALUES ('6488', '溪湖区', '3', '6476', 'X');
INSERT INTO `cms_region` VALUES ('6499', '明山区', '3', '6476', 'M');
INSERT INTO `cms_region` VALUES ('6509', '南芬区', '3', '6476', 'N');
INSERT INTO `cms_region` VALUES ('6515', '本溪县', '3', '6476', 'B');
INSERT INTO `cms_region` VALUES ('6528', '桓仁县', '3', '6476', 'H');
INSERT INTO `cms_region` VALUES ('6542', '丹东市', '2', '5827', 'D');
INSERT INTO `cms_region` VALUES ('6543', '市辖区', '3', '6542', 'S');
INSERT INTO `cms_region` VALUES ('6544', '元宝区', '3', '6542', 'Y');
INSERT INTO `cms_region` VALUES ('6552', '振兴区', '3', '6542', 'Z');
INSERT INTO `cms_region` VALUES ('6563', '振安区', '3', '6542', 'Z');
INSERT INTO `cms_region` VALUES ('6573', '宽甸县', '3', '6542', 'K');
INSERT INTO `cms_region` VALUES ('6596', '东港市', '3', '6542', 'D');
INSERT INTO `cms_region` VALUES ('6621', '凤城市', '3', '6542', 'F');
INSERT INTO `cms_region` VALUES ('6643', '锦州市', '2', '5827', 'J');
INSERT INTO `cms_region` VALUES ('6644', '市辖区', '3', '6643', 'S');
INSERT INTO `cms_region` VALUES ('6645', '古塔区', '3', '6643', 'G');
INSERT INTO `cms_region` VALUES ('6655', '凌河区', '3', '6643', 'L');
INSERT INTO `cms_region` VALUES ('6668', '太和区', '3', '6643', 'T');
INSERT INTO `cms_region` VALUES ('6683', '黑山县', '3', '6643', 'H');
INSERT INTO `cms_region` VALUES ('6706', '义县', '3', '6643', 'Y');
INSERT INTO `cms_region` VALUES ('6725', '凌海市', '3', '6643', 'L');
INSERT INTO `cms_region` VALUES ('6750', '北镇市', '3', '6643', 'B');
INSERT INTO `cms_region` VALUES ('6771', '营口市', '2', '5827', 'Y');
INSERT INTO `cms_region` VALUES ('6772', '市辖区', '3', '6771', 'S');
INSERT INTO `cms_region` VALUES ('6773', '站前区', '3', '6771', 'Z');
INSERT INTO `cms_region` VALUES ('6781', '西市区', '3', '6771', 'X');
INSERT INTO `cms_region` VALUES ('6789', '鲅鱼圈区', '3', '6771', 'B');
INSERT INTO `cms_region` VALUES ('6797', '老边区', '3', '6771', 'L');
INSERT INTO `cms_region` VALUES ('6804', '盖州市', '3', '6771', 'G');
INSERT INTO `cms_region` VALUES ('6832', '大石桥市', '3', '6771', 'D');
INSERT INTO `cms_region` VALUES ('6851', '阜新市', '2', '5827', 'F');
INSERT INTO `cms_region` VALUES ('6852', '市辖区', '3', '6851', 'S');
INSERT INTO `cms_region` VALUES ('6853', '海州区', '3', '6851', 'H');
INSERT INTO `cms_region` VALUES ('6865', '新邱区', '3', '6851', 'X');
INSERT INTO `cms_region` VALUES ('6871', '太平区', '3', '6851', 'T');
INSERT INTO `cms_region` VALUES ('6878', '清河门区', '3', '6851', 'Q');
INSERT INTO `cms_region` VALUES ('6885', '细河区', '3', '6851', 'X');
INSERT INTO `cms_region` VALUES ('6893', '阜新县', '3', '6851', 'F');
INSERT INTO `cms_region` VALUES ('6930', '彰武县', '3', '6851', 'Z');
INSERT INTO `cms_region` VALUES ('6955', '辽阳市', '2', '5827', 'L');
INSERT INTO `cms_region` VALUES ('6956', '市辖区', '3', '6955', 'S');
INSERT INTO `cms_region` VALUES ('6957', '白塔区', '3', '6955', 'B');
INSERT INTO `cms_region` VALUES ('6964', '文圣区', '3', '6955', 'W');
INSERT INTO `cms_region` VALUES ('6971', '宏伟区', '3', '6955', 'H');
INSERT INTO `cms_region` VALUES ('6977', '弓长岭区', '3', '6955', 'G');
INSERT INTO `cms_region` VALUES ('6983', '太子河区', '3', '6955', 'T');
INSERT INTO `cms_region` VALUES ('6989', '辽阳县', '3', '6955', 'L');
INSERT INTO `cms_region` VALUES ('7007', '灯塔市', '3', '6955', 'D');
INSERT INTO `cms_region` VALUES ('7024', '盘锦市', '2', '5827', 'P');
INSERT INTO `cms_region` VALUES ('7025', '市辖区', '3', '7024', 'S');
INSERT INTO `cms_region` VALUES ('7026', '双台子区', '3', '7024', 'S');
INSERT INTO `cms_region` VALUES ('7036', '兴隆台区', '3', '7024', 'X');
INSERT INTO `cms_region` VALUES ('7055', '大洼县', '3', '7024', 'D');
INSERT INTO `cms_region` VALUES ('7072', '盘山县', '3', '7024', 'P');
INSERT INTO `cms_region` VALUES ('7088', '铁岭市', '2', '5827', 'T');
INSERT INTO `cms_region` VALUES ('7089', '市辖区', '3', '7088', 'S');
INSERT INTO `cms_region` VALUES ('7090', '银州区', '3', '7088', 'Y');
INSERT INTO `cms_region` VALUES ('7099', '清河区', '3', '7088', 'Q');
INSERT INTO `cms_region` VALUES ('7105', '铁岭县', '3', '7088', 'T');
INSERT INTO `cms_region` VALUES ('7121', '西丰县', '3', '7088', 'X');
INSERT INTO `cms_region` VALUES ('7140', '昌图县', '3', '7088', 'C');
INSERT INTO `cms_region` VALUES ('7180', '调兵山市', '3', '7088', 'D');
INSERT INTO `cms_region` VALUES ('7186', '开原市', '3', '7088', 'K');
INSERT INTO `cms_region` VALUES ('7208', '朝阳市', '2', '5827', 'C');
INSERT INTO `cms_region` VALUES ('7209', '市辖区', '3', '7208', 'S');
INSERT INTO `cms_region` VALUES ('7210', '双塔区', '3', '7208', 'S');
INSERT INTO `cms_region` VALUES ('7225', '龙城区', '3', '7208', 'L');
INSERT INTO `cms_region` VALUES ('7238', '朝阳县', '3', '7208', 'C');
INSERT INTO `cms_region` VALUES ('7267', '建平县', '3', '7208', 'J');
INSERT INTO `cms_region` VALUES ('7299', '喀喇沁左翼县', '3', '7208', 'K');
INSERT INTO `cms_region` VALUES ('7322', '北票市', '3', '7208', 'B');
INSERT INTO `cms_region` VALUES ('7360', '凌源市', '3', '7208', 'L');
INSERT INTO `cms_region` VALUES ('7391', '葫芦岛市', '2', '5827', 'H');
INSERT INTO `cms_region` VALUES ('7392', '市辖区', '3', '7391', 'S');
INSERT INTO `cms_region` VALUES ('7393', '连山区', '3', '7391', 'L');
INSERT INTO `cms_region` VALUES ('7419', '龙港区', '3', '7391', 'L');
INSERT INTO `cms_region` VALUES ('7433', '南票区', '3', '7391', 'N');
INSERT INTO `cms_region` VALUES ('7446', '绥中县', '3', '7391', 'S');
INSERT INTO `cms_region` VALUES ('7474', '建昌县', '3', '7391', 'J');
INSERT INTO `cms_region` VALUES ('7503', '兴城市', '3', '7391', 'X');
INSERT INTO `cms_region` VALUES ('7531', '吉林省', '1', '0', 'J');
INSERT INTO `cms_region` VALUES ('7532', '长春市', '2', '7531', 'C');
INSERT INTO `cms_region` VALUES ('7533', '长春市辖区', '3', '7532', 'C');
INSERT INTO `cms_region` VALUES ('7534', '南关区', '3', '7532', 'N');
INSERT INTO `cms_region` VALUES ('7552', '宽城区', '3', '7532', 'K');
INSERT INTO `cms_region` VALUES ('7569', '朝阳区', '3', '7532', 'C');
INSERT INTO `cms_region` VALUES ('7582', '二道区', '3', '7532', 'E');
INSERT INTO `cms_region` VALUES ('7597', '绿园区', '3', '7532', 'L');
INSERT INTO `cms_region` VALUES ('7610', '双阳区', '3', '7532', 'S');
INSERT INTO `cms_region` VALUES ('7619', '农安县', '3', '7532', 'N');
INSERT INTO `cms_region` VALUES ('7642', '九台市', '3', '7532', 'J');
INSERT INTO `cms_region` VALUES ('7658', '榆树市', '3', '7532', 'Y');
INSERT INTO `cms_region` VALUES ('7687', '德惠市', '3', '7532', 'D');
INSERT INTO `cms_region` VALUES ('7706', '吉林市', '2', '7531', 'J');
INSERT INTO `cms_region` VALUES ('7707', '吉林市辖区', '3', '7706', 'J');
INSERT INTO `cms_region` VALUES ('7708', '昌邑区', '3', '7706', 'C');
INSERT INTO `cms_region` VALUES ('7731', '龙潭区', '3', '7706', 'L');
INSERT INTO `cms_region` VALUES ('7752', '船营区', '3', '7706', 'C');
INSERT INTO `cms_region` VALUES ('7768', '丰满区', '3', '7706', 'F');
INSERT INTO `cms_region` VALUES ('7781', '永吉县', '3', '7706', 'Y');
INSERT INTO `cms_region` VALUES ('7792', '蛟河市', '3', '7706', 'J');
INSERT INTO `cms_region` VALUES ('7810', '桦甸市', '3', '7706', 'H');
INSERT INTO `cms_region` VALUES ('7828', '舒兰市', '3', '7706', 'S');
INSERT INTO `cms_region` VALUES ('7849', '磐石市', '3', '7706', 'P');
INSERT INTO `cms_region` VALUES ('7868', '四平市', '2', '7531', 'S');
INSERT INTO `cms_region` VALUES ('7869', '四平市辖区', '3', '7868', 'S');
INSERT INTO `cms_region` VALUES ('7870', '铁西区', '3', '7868', 'T');
INSERT INTO `cms_region` VALUES ('7878', '铁东区', '3', '7868', 'T');
INSERT INTO `cms_region` VALUES ('7892', '梨树县', '3', '7868', 'L');
INSERT INTO `cms_region` VALUES ('7916', '伊通县', '3', '7868', 'Y');
INSERT INTO `cms_region` VALUES ('7933', '公主岭市', '3', '7868', 'G');
INSERT INTO `cms_region` VALUES ('7964', '双辽市', '3', '7868', 'S');
INSERT INTO `cms_region` VALUES ('7986', '辽源市', '2', '7531', 'L');
INSERT INTO `cms_region` VALUES ('7987', '辽源市辖区', '3', '7986', 'L');
INSERT INTO `cms_region` VALUES ('7988', '龙山区', '3', '7986', 'L');
INSERT INTO `cms_region` VALUES ('8000', '西安区', '3', '7986', 'X');
INSERT INTO `cms_region` VALUES ('8008', '东丰县', '3', '7986', 'D');
INSERT INTO `cms_region` VALUES ('8023', '东辽县', '3', '7986', 'D');
INSERT INTO `cms_region` VALUES ('8037', '通化市', '2', '7531', 'T');
INSERT INTO `cms_region` VALUES ('8038', '通化市辖区', '3', '8037', 'T');
INSERT INTO `cms_region` VALUES ('8039', '东昌区', '3', '8037', 'D');
INSERT INTO `cms_region` VALUES ('8051', '二道江区', '3', '8037', 'E');
INSERT INTO `cms_region` VALUES ('8058', '通化县', '3', '8037', 'T');
INSERT INTO `cms_region` VALUES ('8076', '辉南县', '3', '8037', 'H');
INSERT INTO `cms_region` VALUES ('8088', '柳河县', '3', '8037', 'L');
INSERT INTO `cms_region` VALUES ('8104', '梅河口市', '3', '8037', 'M');
INSERT INTO `cms_region` VALUES ('8129', '集安市', '3', '8037', 'J');
INSERT INTO `cms_region` VALUES ('8144', '白山市', '2', '7531', 'B');
INSERT INTO `cms_region` VALUES ('8145', '白山市辖区', '3', '8144', 'B');
INSERT INTO `cms_region` VALUES ('8146', '八道江区', '3', '8144', 'B');
INSERT INTO `cms_region` VALUES ('8159', '江源区', '3', '8144', 'J');
INSERT INTO `cms_region` VALUES ('8168', '抚松县', '3', '8144', 'F');
INSERT INTO `cms_region` VALUES ('8183', '靖宇县', '3', '8144', 'J');
INSERT INTO `cms_region` VALUES ('8192', '长白县', '3', '8144', 'C');
INSERT INTO `cms_region` VALUES ('8202', '临江市', '3', '8144', 'L');
INSERT INTO `cms_region` VALUES ('8216', '松原市', '2', '7531', 'S');
INSERT INTO `cms_region` VALUES ('8217', '松原市辖区', '3', '8216', 'S');
INSERT INTO `cms_region` VALUES ('8218', '宁江区', '3', '8216', 'N');
INSERT INTO `cms_region` VALUES ('8239', '前郭县', '3', '8216', 'Q');
INSERT INTO `cms_region` VALUES ('8266', '长岭县', '3', '8216', 'C');
INSERT INTO `cms_region` VALUES ('8300', '乾安县', '3', '8216', 'Q');
INSERT INTO `cms_region` VALUES ('8311', '扶余县', '3', '8216', 'F');
INSERT INTO `cms_region` VALUES ('8333', '白城市', '2', '7531', 'B');
INSERT INTO `cms_region` VALUES ('8334', '白城市辖区', '3', '8333', 'B');
INSERT INTO `cms_region` VALUES ('8335', '洮北区', '3', '8333', 'T');
INSERT INTO `cms_region` VALUES ('8362', '镇赉县', '3', '8333', 'Z');
INSERT INTO `cms_region` VALUES ('8375', '通榆县', '3', '8333', 'T');
INSERT INTO `cms_region` VALUES ('8393', '洮南市', '3', '8333', 'T');
INSERT INTO `cms_region` VALUES ('8420', '大安市', '3', '8333', 'D');
INSERT INTO `cms_region` VALUES ('8445', '延边州', '2', '7531', 'Y');
INSERT INTO `cms_region` VALUES ('8446', '延吉市', '3', '8445', 'Y');
INSERT INTO `cms_region` VALUES ('8456', '图们市', '3', '8445', 'T');
INSERT INTO `cms_region` VALUES ('8464', '敦化市', '3', '8445', 'D');
INSERT INTO `cms_region` VALUES ('8489', '珲春市', '3', '8445', 'H');
INSERT INTO `cms_region` VALUES ('8504', '龙井市', '3', '8445', 'L');
INSERT INTO `cms_region` VALUES ('8515', '和龙市', '3', '8445', 'H');
INSERT INTO `cms_region` VALUES ('8530', '汪清县', '3', '8445', 'W');
INSERT INTO `cms_region` VALUES ('8545', '安图县', '3', '8445', 'A');
INSERT INTO `cms_region` VALUES ('8558', '黑龙江省', '1', '0', 'H');
INSERT INTO `cms_region` VALUES ('8559', '哈尔滨市', '2', '8558', 'H');
INSERT INTO `cms_region` VALUES ('8560', '市辖区', '3', '8559', 'S');
INSERT INTO `cms_region` VALUES ('8561', '道里区', '3', '8559', 'D');
INSERT INTO `cms_region` VALUES ('8585', '南岗区', '3', '8559', 'N');
INSERT INTO `cms_region` VALUES ('8606', '道外区', '3', '8559', 'D');
INSERT INTO `cms_region` VALUES ('8633', '平房区', '3', '8559', 'P');
INSERT INTO `cms_region` VALUES ('8642', '松北区', '3', '8559', 'S');
INSERT INTO `cms_region` VALUES ('8650', '香坊区', '3', '8559', 'X');
INSERT INTO `cms_region` VALUES ('8676', '呼兰区', '3', '8559', 'H');
INSERT INTO `cms_region` VALUES ('8694', '阿城区', '3', '8559', 'A');
INSERT INTO `cms_region` VALUES ('8714', '依兰县', '3', '8559', 'Y');
INSERT INTO `cms_region` VALUES ('8729', '方正县', '3', '8559', 'F');
INSERT INTO `cms_region` VALUES ('8740', '宾县', '3', '8559', 'B');
INSERT INTO `cms_region` VALUES ('8758', '巴彦县', '3', '8559', 'B');
INSERT INTO `cms_region` VALUES ('8778', '木兰县', '3', '8559', 'M');
INSERT INTO `cms_region` VALUES ('8788', '通河县', '3', '8559', 'T');
INSERT INTO `cms_region` VALUES ('8800', '延寿县', '3', '8559', 'Y');
INSERT INTO `cms_region` VALUES ('8812', '双城市', '3', '8559', 'S');
INSERT INTO `cms_region` VALUES ('8838', '尚志市', '3', '8559', 'S');
INSERT INTO `cms_region` VALUES ('8858', '五常市', '3', '8559', 'W');
INSERT INTO `cms_region` VALUES ('8884', '齐齐哈尔市', '2', '8558', 'Q');
INSERT INTO `cms_region` VALUES ('8885', '市辖区', '3', '8884', 'S');
INSERT INTO `cms_region` VALUES ('8886', '龙沙区', '3', '8884', 'L');
INSERT INTO `cms_region` VALUES ('8894', '建华区', '3', '8884', 'J');
INSERT INTO `cms_region` VALUES ('8901', '铁锋区', '3', '8884', 'T');
INSERT INTO `cms_region` VALUES ('8911', '昂昂溪区', '3', '8884', 'A');
INSERT INTO `cms_region` VALUES ('8918', '富拉尔基区', '3', '8884', 'F');
INSERT INTO `cms_region` VALUES ('8928', '碾子山区', '3', '8884', 'N');
INSERT INTO `cms_region` VALUES ('8934', '梅里斯达斡尔族区', '3', '8884', 'M');
INSERT INTO `cms_region` VALUES ('8943', '龙江县', '3', '8884', 'L');
INSERT INTO `cms_region` VALUES ('8958', '依安县', '3', '8884', 'Y');
INSERT INTO `cms_region` VALUES ('8976', '泰来县', '3', '8884', 'T');
INSERT INTO `cms_region` VALUES ('8998', '甘南县', '3', '8884', 'G');
INSERT INTO `cms_region` VALUES ('9011', '富裕县', '3', '8884', 'F');
INSERT INTO `cms_region` VALUES ('9024', '克山县', '3', '8884', 'K');
INSERT INTO `cms_region` VALUES ('9048', '克东县', '3', '8884', 'K');
INSERT INTO `cms_region` VALUES ('9067', '拜泉县', '3', '8884', 'B');
INSERT INTO `cms_region` VALUES ('9084', '讷河市', '3', '8884', 'N');
INSERT INTO `cms_region` VALUES ('9117', '鸡西市', '2', '8558', 'J');
INSERT INTO `cms_region` VALUES ('9118', '市辖区', '3', '9117', 'S');
INSERT INTO `cms_region` VALUES ('9119', '鸡冠区', '3', '9117', 'J');
INSERT INTO `cms_region` VALUES ('9129', '恒山区', '3', '9117', 'H');
INSERT INTO `cms_region` VALUES ('9139', '滴道区', '3', '9117', 'D');
INSERT INTO `cms_region` VALUES ('9146', '梨树区', '3', '9117', 'L');
INSERT INTO `cms_region` VALUES ('9153', '城子河区', '3', '9117', 'C');
INSERT INTO `cms_region` VALUES ('9161', '麻山区', '3', '9117', 'M');
INSERT INTO `cms_region` VALUES ('9164', '鸡东县', '3', '9117', 'J');
INSERT INTO `cms_region` VALUES ('9178', '虎林市', '3', '9117', 'H');
INSERT INTO `cms_region` VALUES ('9199', '密山市', '3', '9117', 'M');
INSERT INTO `cms_region` VALUES ('9222', '鹤岗市', '2', '8558', 'H');
INSERT INTO `cms_region` VALUES ('9223', '市辖区', '3', '9222', 'S');
INSERT INTO `cms_region` VALUES ('9224', '向阳区', '3', '9222', 'X');
INSERT INTO `cms_region` VALUES ('9230', '工农区', '3', '9222', 'G');
INSERT INTO `cms_region` VALUES ('9237', '南山区', '3', '9222', 'N');
INSERT INTO `cms_region` VALUES ('9244', '兴安区', '3', '9222', 'X');
INSERT INTO `cms_region` VALUES ('9250', '东山区', '3', '9222', 'D');
INSERT INTO `cms_region` VALUES ('9261', '兴山区', '3', '9222', 'X');
INSERT INTO `cms_region` VALUES ('9266', '萝北县', '3', '9222', 'L');
INSERT INTO `cms_region` VALUES ('9283', '绥滨县', '3', '9222', 'S');
INSERT INTO `cms_region` VALUES ('9296', '双鸭山市', '2', '8558', 'S');
INSERT INTO `cms_region` VALUES ('9297', '市辖区', '3', '9296', 'S');
INSERT INTO `cms_region` VALUES ('9298', '尖山区', '3', '9296', 'J');
INSERT INTO `cms_region` VALUES ('9307', '岭东区', '3', '9296', 'L');
INSERT INTO `cms_region` VALUES ('9317', '四方台区', '3', '9296', 'S');
INSERT INTO `cms_region` VALUES ('9323', '宝山区', '3', '9296', 'B');
INSERT INTO `cms_region` VALUES ('9335', '集贤县', '3', '9296', 'J');
INSERT INTO `cms_region` VALUES ('9356', '友谊县', '3', '9296', 'Y');
INSERT INTO `cms_region` VALUES ('9370', '宝清县', '3', '9296', 'B');
INSERT INTO `cms_region` VALUES ('9393', '饶河县', '3', '9296', 'R');
INSERT INTO `cms_region` VALUES ('9419', '大庆市', '2', '8558', 'D');
INSERT INTO `cms_region` VALUES ('9420', '市辖区', '3', '9419', 'S');
INSERT INTO `cms_region` VALUES ('9421', '萨尔图区', '3', '9419', 'S');
INSERT INTO `cms_region` VALUES ('9431', '龙凤区', '3', '9419', 'L');
INSERT INTO `cms_region` VALUES ('9440', '让胡路区', '3', '9419', 'R');
INSERT INTO `cms_region` VALUES ('9451', '红岗区', '3', '9419', 'H');
INSERT INTO `cms_region` VALUES ('9458', '大同区', '3', '9419', 'D');
INSERT INTO `cms_region` VALUES ('9474', '肇州县', '3', '9419', 'Z');
INSERT INTO `cms_region` VALUES ('9489', '肇源县', '3', '9419', 'Z');
INSERT INTO `cms_region` VALUES ('9514', '林甸县', '3', '9419', 'L');
INSERT INTO `cms_region` VALUES ('9527', '杜尔伯特县', '3', '9419', 'D');
INSERT INTO `cms_region` VALUES ('9553', '伊春市', '2', '8558', 'Y');
INSERT INTO `cms_region` VALUES ('9554', '市辖区', '3', '9553', 'S');
INSERT INTO `cms_region` VALUES ('9555', '伊春区', '3', '9553', 'Y');
INSERT INTO `cms_region` VALUES ('9561', '南岔区', '3', '9553', 'N');
INSERT INTO `cms_region` VALUES ('9581', '友好区', '3', '9553', 'Y');
INSERT INTO `cms_region` VALUES ('9599', '西林区', '3', '9553', 'X');
INSERT INTO `cms_region` VALUES ('9603', '翠峦区', '3', '9553', 'C');
INSERT INTO `cms_region` VALUES ('9614', '新青区', '3', '9553', 'X');
INSERT INTO `cms_region` VALUES ('9631', '美溪区', '3', '9553', 'M');
INSERT INTO `cms_region` VALUES ('9647', '金山屯区', '3', '9553', 'J');
INSERT INTO `cms_region` VALUES ('9660', '五营区', '3', '9553', 'W');
INSERT INTO `cms_region` VALUES ('9671', '乌马河区', '3', '9553', 'W');
INSERT INTO `cms_region` VALUES ('9685', '汤旺河区', '3', '9553', 'T');
INSERT INTO `cms_region` VALUES ('9702', '带岭区', '3', '9553', 'D');
INSERT INTO `cms_region` VALUES ('9715', '乌伊岭区', '3', '9553', 'W');
INSERT INTO `cms_region` VALUES ('9729', '红星区', '3', '9553', 'H');
INSERT INTO `cms_region` VALUES ('9742', '上甘岭区', '3', '9553', 'S');
INSERT INTO `cms_region` VALUES ('9756', '嘉荫县', '3', '9553', 'J');
INSERT INTO `cms_region` VALUES ('9772', '铁力市', '3', '9553', 'T');
INSERT INTO `cms_region` VALUES ('9785', '佳木斯市', '2', '8558', 'J');
INSERT INTO `cms_region` VALUES ('9786', '市辖区', '3', '9785', 'S');
INSERT INTO `cms_region` VALUES ('9787', '向阳区', '3', '9785', 'X');
INSERT INTO `cms_region` VALUES ('9795', '前进区', '3', '9785', 'Q');
INSERT INTO `cms_region` VALUES ('9802', '东风区', '3', '9785', 'D');
INSERT INTO `cms_region` VALUES ('9810', '郊区', '3', '9785', 'J');
INSERT INTO `cms_region` VALUES ('9825', '桦南县', '3', '9785', 'H');
INSERT INTO `cms_region` VALUES ('9839', '桦川县', '3', '9785', 'H');
INSERT INTO `cms_region` VALUES ('9851', '汤原县', '3', '9785', 'T');
INSERT INTO `cms_region` VALUES ('9866', '抚远县', '3', '9785', 'F');
INSERT INTO `cms_region` VALUES ('9879', '同江市', '3', '9785', 'T');
INSERT INTO `cms_region` VALUES ('9907', '富锦市', '3', '9785', 'F');
INSERT INTO `cms_region` VALUES ('9930', '七台河市', '2', '8558', 'Q');
INSERT INTO `cms_region` VALUES ('9931', '市辖区', '3', '9930', 'S');
INSERT INTO `cms_region` VALUES ('9932', '新兴区', '3', '9930', 'X');
INSERT INTO `cms_region` VALUES ('9944', '桃山区', '3', '9930', 'T');
INSERT INTO `cms_region` VALUES ('9952', '茄子河区', '3', '9930', 'Q');
INSERT INTO `cms_region` VALUES ('9962', '勃利县', '3', '9930', 'B');
INSERT INTO `cms_region` VALUES ('9981', '牡丹江市', '2', '8558', 'M');
INSERT INTO `cms_region` VALUES ('9982', '市辖区', '3', '9981', 'S');
INSERT INTO `cms_region` VALUES ('9983', '东安区', '3', '9981', 'D');
INSERT INTO `cms_region` VALUES ('9989', '阳明区', '3', '9981', 'Y');
INSERT INTO `cms_region` VALUES ('9996', '爱民区', '3', '9981', 'A');
INSERT INTO `cms_region` VALUES ('10005', '西安区', '3', '9981', 'X');
INSERT INTO `cms_region` VALUES ('10014', '东宁县', '3', '9981', 'D');
INSERT INTO `cms_region` VALUES ('10022', '林口县', '3', '9981', 'L');
INSERT INTO `cms_region` VALUES ('10036', '绥芬河市', '3', '9981', 'S');
INSERT INTO `cms_region` VALUES ('10039', '海林市', '3', '9981', 'H');
INSERT INTO `cms_region` VALUES ('10056', '宁安市', '3', '9981', 'N');
INSERT INTO `cms_region` VALUES ('10072', '穆棱市', '3', '9981', 'M');
INSERT INTO `cms_region` VALUES ('10084', '黑河市', '2', '8558', 'H');
INSERT INTO `cms_region` VALUES ('10085', '市辖区', '3', '10084', 'S');
INSERT INTO `cms_region` VALUES ('10086', '爱辉区', '3', '10084', 'A');
INSERT INTO `cms_region` VALUES ('10122', '嫩江县', '3', '10084', 'N');
INSERT INTO `cms_region` VALUES ('10150', '逊克县', '3', '10084', 'X');
INSERT INTO `cms_region` VALUES ('10168', '孙吴县', '3', '10084', 'S');
INSERT INTO `cms_region` VALUES ('10192', '北安市', '3', '10084', 'B');
INSERT INTO `cms_region` VALUES ('10214', '五大连池市', '3', '10084', 'W');
INSERT INTO `cms_region` VALUES ('10252', '绥化市', '2', '8558', 'S');
INSERT INTO `cms_region` VALUES ('10253', '市辖区', '3', '10252', 'S');
INSERT INTO `cms_region` VALUES ('10254', '北林区', '3', '10252', 'B');
INSERT INTO `cms_region` VALUES ('10281', '望奎县', '3', '10252', 'W');
INSERT INTO `cms_region` VALUES ('10301', '兰西县', '3', '10252', 'L');
INSERT INTO `cms_region` VALUES ('10320', '青冈县', '3', '10252', 'Q');
INSERT INTO `cms_region` VALUES ('10342', '庆安县', '3', '10252', 'Q');
INSERT INTO `cms_region` VALUES ('10360', '明水县', '3', '10252', 'M');
INSERT INTO `cms_region` VALUES ('10380', '绥棱县', '3', '10252', 'S');
INSERT INTO `cms_region` VALUES ('10401', '安达市', '3', '10252', 'A');
INSERT INTO `cms_region` VALUES ('10425', '肇东市', '3', '10252', 'Z');
INSERT INTO `cms_region` VALUES ('10452', '海伦市', '3', '10252', 'H');
INSERT INTO `cms_region` VALUES ('10483', '大兴安岭地区', '2', '8558', 'D');
INSERT INTO `cms_region` VALUES ('10484', '加格达奇区', '3', '10483', 'J');
INSERT INTO `cms_region` VALUES ('10495', '松岭区', '3', '10483', 'S');
INSERT INTO `cms_region` VALUES ('10500', '新林区', '3', '10483', 'X');
INSERT INTO `cms_region` VALUES ('10509', '呼中区', '3', '10483', 'H');
INSERT INTO `cms_region` VALUES ('10515', '呼玛县', '3', '10483', 'H');
INSERT INTO `cms_region` VALUES ('10525', '塔河县', '3', '10483', 'T');
INSERT INTO `cms_region` VALUES ('10534', '漠河县', '3', '10483', 'M');
INSERT INTO `cms_region` VALUES ('10543', '上海市', '1', '0', 'S');
INSERT INTO `cms_region` VALUES ('10544', '上海市', '2', '10543', 'S');
INSERT INTO `cms_region` VALUES ('10545', '黄浦区', '3', '10544', 'H');
INSERT INTO `cms_region` VALUES ('10555', '卢湾区', '3', '10544', 'L');
INSERT INTO `cms_region` VALUES ('10560', '徐汇区', '3', '10544', 'X');
INSERT INTO `cms_region` VALUES ('10575', '长宁区', '3', '10544', 'C');
INSERT INTO `cms_region` VALUES ('10586', '静安区', '3', '10544', 'J');
INSERT INTO `cms_region` VALUES ('10592', '普陀区', '3', '10544', 'P');
INSERT INTO `cms_region` VALUES ('10602', '闸北区', '3', '10544', 'Z');
INSERT INTO `cms_region` VALUES ('10612', '虹口区', '3', '10544', 'H');
INSERT INTO `cms_region` VALUES ('10623', '杨浦区', '3', '10544', 'Y');
INSERT INTO `cms_region` VALUES ('10636', '闵行区', '3', '10544', 'M');
INSERT INTO `cms_region` VALUES ('10650', '宝山区', '3', '10544', 'B');
INSERT INTO `cms_region` VALUES ('10664', '嘉定区', '3', '10544', 'J');
INSERT INTO `cms_region` VALUES ('10678', '浦东新区', '3', '10544', 'P');
INSERT INTO `cms_region` VALUES ('10704', '金山区', '3', '10544', 'J');
INSERT INTO `cms_region` VALUES ('10715', '松江区', '3', '10544', 'S');
INSERT INTO `cms_region` VALUES ('10735', '青浦区', '3', '10544', 'Q');
INSERT INTO `cms_region` VALUES ('10747', '南汇区', '3', '10544', 'N');
INSERT INTO `cms_region` VALUES ('10765', '奉贤区', '3', '10544', 'F');
INSERT INTO `cms_region` VALUES ('10780', '崇明区', '3', '10544', 'C');
INSERT INTO `cms_region` VALUES ('10808', '江苏省', '1', '0', 'J');
INSERT INTO `cms_region` VALUES ('10809', '南京市', '2', '10808', 'N');
INSERT INTO `cms_region` VALUES ('10810', '市辖区', '3', '10809', 'S');
INSERT INTO `cms_region` VALUES ('10811', '玄武区', '3', '10809', 'X');
INSERT INTO `cms_region` VALUES ('10820', '白下区', '3', '10809', 'B');
INSERT INTO `cms_region` VALUES ('10831', '秦淮区', '3', '10809', 'Q');
INSERT INTO `cms_region` VALUES ('10837', '建邺区', '3', '10809', 'J');
INSERT INTO `cms_region` VALUES ('10845', '鼓楼区', '3', '10809', 'G');
INSERT INTO `cms_region` VALUES ('10853', '下关区', '3', '10809', 'X');
INSERT INTO `cms_region` VALUES ('10860', '浦口区', '3', '10809', 'P');
INSERT INTO `cms_region` VALUES ('10876', '栖霞区', '3', '10809', 'Q');
INSERT INTO `cms_region` VALUES ('10894', '雨花台区', '3', '10809', 'Y');
INSERT INTO `cms_region` VALUES ('10903', '江宁区', '3', '10809', 'J');
INSERT INTO `cms_region` VALUES ('10916', '六合区', '3', '10809', 'L');
INSERT INTO `cms_region` VALUES ('10937', '溧水县', '3', '10809', 'L');
INSERT INTO `cms_region` VALUES ('10947', '高淳县', '3', '10809', 'G');
INSERT INTO `cms_region` VALUES ('10960', '无锡市', '2', '10808', 'W');
INSERT INTO `cms_region` VALUES ('10961', '新吴区', '3', '10960', 'X');
INSERT INTO `cms_region` VALUES ('10962', '梁溪区', '3', '10960', 'L');
INSERT INTO `cms_region` VALUES ('10981', '锡山区', '3', '10960', 'X');
INSERT INTO `cms_region` VALUES ('10990', '惠山区', '3', '10960', 'H');
INSERT INTO `cms_region` VALUES ('10999', '滨湖区', '3', '10960', 'B');
INSERT INTO `cms_region` VALUES ('11018', '江阴市', '3', '10960', 'J');
INSERT INTO `cms_region` VALUES ('11039', '宜兴市', '3', '10960', 'Y');
INSERT INTO `cms_region` VALUES ('11067', '徐州市', '2', '10808', 'X');
INSERT INTO `cms_region` VALUES ('11068', '市辖区', '3', '11067', 'S');
INSERT INTO `cms_region` VALUES ('11069', '鼓楼区', '3', '11067', 'G');
INSERT INTO `cms_region` VALUES ('11081', '云龙区', '3', '11067', 'Y');
INSERT INTO `cms_region` VALUES ('11089', '九里区', '3', '11067', 'J');
INSERT INTO `cms_region` VALUES ('11103', '贾汪区', '3', '11067', 'J');
INSERT INTO `cms_region` VALUES ('11115', '泉山区', '3', '11067', 'Q');
INSERT INTO `cms_region` VALUES ('11126', '丰县', '3', '11067', 'F');
INSERT INTO `cms_region` VALUES ('11142', '沛县', '3', '11067', 'P');
INSERT INTO `cms_region` VALUES ('11160', '铜山县', '3', '11067', 'T');
INSERT INTO `cms_region` VALUES ('11182', '睢宁县', '3', '11067', 'S');
INSERT INTO `cms_region` VALUES ('11200', '新沂市', '3', '11067', 'X');
INSERT INTO `cms_region` VALUES ('11218', '邳州市', '3', '11067', 'P');
INSERT INTO `cms_region` VALUES ('11245', '常州市', '2', '10808', 'C');
INSERT INTO `cms_region` VALUES ('11246', '常州市区', '3', '11245', 'C');
INSERT INTO `cms_region` VALUES ('11247', '天宁区', '3', '11245', 'T');
INSERT INTO `cms_region` VALUES ('11254', '钟楼区', '3', '11245', 'Z');
INSERT INTO `cms_region` VALUES ('11262', '戚墅堰区', '3', '11245', 'Q');
INSERT INTO `cms_region` VALUES ('11266', '新北区', '3', '11245', 'X');
INSERT INTO `cms_region` VALUES ('11276', '武进区', '3', '11245', 'W');
INSERT INTO `cms_region` VALUES ('11311', '溧阳市', '3', '11245', 'L');
INSERT INTO `cms_region` VALUES ('11331', '金坛市', '3', '11245', 'J');
INSERT INTO `cms_region` VALUES ('11348', '苏州市', '2', '10808', 'S');
INSERT INTO `cms_region` VALUES ('11349', '市辖区', '3', '11348', 'S');
INSERT INTO `cms_region` VALUES ('11350', '沧浪区', '3', '11348', 'C');
INSERT INTO `cms_region` VALUES ('11357', '平江区', '3', '11348', 'P');
INSERT INTO `cms_region` VALUES ('11368', '金阊区', '3', '11348', 'J');
INSERT INTO `cms_region` VALUES ('11374', '苏州高新区虎丘区', '3', '11348', 'S');
INSERT INTO `cms_region` VALUES ('11387', '吴中区', '3', '11348', 'W');
INSERT INTO `cms_region` VALUES ('11409', '相城区', '3', '11348', 'X');
INSERT INTO `cms_region` VALUES ('11419', '常熟市', '3', '11348', 'C');
INSERT INTO `cms_region` VALUES ('11433', '张家港市', '3', '11348', 'Z');
INSERT INTO `cms_region` VALUES ('11448', '昆山市', '3', '11348', 'K');
INSERT INTO `cms_region` VALUES ('11460', '吴江市', '3', '11348', 'W');
INSERT INTO `cms_region` VALUES ('11472', '太仓市', '3', '11348', 'T');
INSERT INTO `cms_region` VALUES ('11482', '南通市', '2', '10808', 'N');
INSERT INTO `cms_region` VALUES ('11483', '市辖区', '3', '11482', 'S');
INSERT INTO `cms_region` VALUES ('11484', '崇川区', '3', '11482', 'C');
INSERT INTO `cms_region` VALUES ('11502', '港闸区', '3', '11482', 'G');
INSERT INTO `cms_region` VALUES ('11510', '海安县', '3', '11482', 'H');
INSERT INTO `cms_region` VALUES ('11526', '如东', '3', '11482', 'R');
INSERT INTO `cms_region` VALUES ('11542', '启东市', '3', '11482', 'Q');
INSERT INTO `cms_region` VALUES ('11568', '如皋市', '3', '11482', 'R');
INSERT INTO `cms_region` VALUES ('11600', '通州市', '3', '11482', 'T');
INSERT INTO `cms_region` VALUES ('11627', '海门市', '3', '11482', 'H');
INSERT INTO `cms_region` VALUES ('11663', '连云港市', '2', '10808', 'L');
INSERT INTO `cms_region` VALUES ('11664', '市辖区', '3', '11663', 'S');
INSERT INTO `cms_region` VALUES ('11665', '连云区', '3', '11663', 'L');
INSERT INTO `cms_region` VALUES ('11678', '新浦区', '3', '11663', 'X');
INSERT INTO `cms_region` VALUES ('11692', '海州区', '3', '11663', 'H');
INSERT INTO `cms_region` VALUES ('11699', '赣榆县', '3', '11663', 'G');
INSERT INTO `cms_region` VALUES ('11722', '东海县', '3', '11663', 'D');
INSERT INTO `cms_region` VALUES ('11747', '灌云县', '3', '11663', 'G');
INSERT INTO `cms_region` VALUES ('11771', '灌南县', '3', '11663', 'G');
INSERT INTO `cms_region` VALUES ('11786', '淮安市', '2', '10808', 'H');
INSERT INTO `cms_region` VALUES ('11787', '市辖区', '3', '11786', 'S');
INSERT INTO `cms_region` VALUES ('11788', '清河区', '3', '11786', 'Q');
INSERT INTO `cms_region` VALUES ('11801', '楚州区', '3', '11786', 'C');
INSERT INTO `cms_region` VALUES ('11830', '淮阴区', '3', '11786', 'H');
INSERT INTO `cms_region` VALUES ('11853', '清浦区', '3', '11786', 'Q');
INSERT INTO `cms_region` VALUES ('11863', '涟水县', '3', '11786', 'L');
INSERT INTO `cms_region` VALUES ('11896', '洪泽县', '3', '11786', 'H');
INSERT INTO `cms_region` VALUES ('11909', '盱眙县', '3', '11786', 'X');
INSERT INTO `cms_region` VALUES ('11931', '金湖县', '3', '11786', 'J');
INSERT INTO `cms_region` VALUES ('11947', '盐城市', '2', '10808', 'Y');
INSERT INTO `cms_region` VALUES ('11948', '市辖区', '3', '11947', 'S');
INSERT INTO `cms_region` VALUES ('11949', '亭湖区', '3', '11947', 'T');
INSERT INTO `cms_region` VALUES ('11967', '盐都区', '3', '11947', 'Y');
INSERT INTO `cms_region` VALUES ('11982', '响水县', '3', '11947', 'X');
INSERT INTO `cms_region` VALUES ('11998', '滨海县', '3', '11947', 'B');
INSERT INTO `cms_region` VALUES ('12017', '阜宁县', '3', '11947', 'F');
INSERT INTO `cms_region` VALUES ('12040', '射阳县', '3', '11947', 'S');
INSERT INTO `cms_region` VALUES ('12066', '建湖县', '3', '11947', 'J');
INSERT INTO `cms_region` VALUES ('12083', '东台市', '3', '11947', 'D');
INSERT INTO `cms_region` VALUES ('12117', '大丰市', '3', '11947', 'D');
INSERT INTO `cms_region` VALUES ('12135', '扬州市', '2', '10808', 'Y');
INSERT INTO `cms_region` VALUES ('12136', '市辖区', '3', '12135', 'S');
INSERT INTO `cms_region` VALUES ('12137', '广陵区', '3', '12135', 'G');
INSERT INTO `cms_region` VALUES ('12144', '邗江区', '3', '12135', 'H');
INSERT INTO `cms_region` VALUES ('12160', '维扬区', '3', '12135', 'W');
INSERT INTO `cms_region` VALUES ('12175', '宝应县', '3', '12135', 'B');
INSERT INTO `cms_region` VALUES ('12191', '仪征市', '3', '12135', 'Y');
INSERT INTO `cms_region` VALUES ('12212', '高邮市', '3', '12135', 'G');
INSERT INTO `cms_region` VALUES ('12235', '江都市', '3', '12135', 'J');
INSERT INTO `cms_region` VALUES ('12249', '镇江市', '2', '10808', 'Z');
INSERT INTO `cms_region` VALUES ('12250', '市区', '3', '12249', 'S');
INSERT INTO `cms_region` VALUES ('12251', '京口区', '3', '12249', 'J');
INSERT INTO `cms_region` VALUES ('12265', '润州区', '3', '12249', 'R');
INSERT INTO `cms_region` VALUES ('12273', '丹徒区', '3', '12249', 'D');
INSERT INTO `cms_region` VALUES ('12282', '丹阳市', '3', '12249', 'D');
INSERT INTO `cms_region` VALUES ('12300', '扬中市', '3', '12249', 'Y');
INSERT INTO `cms_region` VALUES ('12312', '句容市', '3', '12249', 'J');
INSERT INTO `cms_region` VALUES ('12343', '泰州市', '2', '10808', 'T');
INSERT INTO `cms_region` VALUES ('12344', '市辖区', '3', '12343', 'S');
INSERT INTO `cms_region` VALUES ('12345', '海陵区', '3', '12343', 'H');
INSERT INTO `cms_region` VALUES ('12362', '高港区', '3', '12343', 'G');
INSERT INTO `cms_region` VALUES ('12370', '兴化市', '3', '12343', 'X');
INSERT INTO `cms_region` VALUES ('12407', '靖江市', '3', '12343', 'J');
INSERT INTO `cms_region` VALUES ('12423', '泰兴市', '3', '12343', 'T');
INSERT INTO `cms_region` VALUES ('12450', '姜堰市', '3', '12343', 'J');
INSERT INTO `cms_region` VALUES ('12475', '宿迁市', '2', '10808', 'S');
INSERT INTO `cms_region` VALUES ('12476', '市辖区', '3', '12475', 'S');
INSERT INTO `cms_region` VALUES ('12477', '宿城区', '3', '12475', 'S');
INSERT INTO `cms_region` VALUES ('12496', '宿豫区', '3', '12475', 'S');
INSERT INTO `cms_region` VALUES ('12515', '沭阳县', '3', '12475', 'S');
INSERT INTO `cms_region` VALUES ('12551', '泗阳县', '3', '12475', 'S');
INSERT INTO `cms_region` VALUES ('12570', '泗洪县', '3', '12475', 'S');
INSERT INTO `cms_region` VALUES ('12596', '浙江省', '1', '0', 'Z');
INSERT INTO `cms_region` VALUES ('12597', '杭州市', '2', '12596', 'H');
INSERT INTO `cms_region` VALUES ('12598', '市辖区', '3', '12597', 'S');
INSERT INTO `cms_region` VALUES ('12599', '上城区', '3', '12597', 'S');
INSERT INTO `cms_region` VALUES ('12606', '下城区', '3', '12597', 'X');
INSERT INTO `cms_region` VALUES ('12615', '江干区', '3', '12597', 'J');
INSERT INTO `cms_region` VALUES ('12626', '拱墅区', '3', '12597', 'G');
INSERT INTO `cms_region` VALUES ('12637', '西湖区', '3', '12597', 'X');
INSERT INTO `cms_region` VALUES ('12652', '滨江区', '3', '12597', 'B');
INSERT INTO `cms_region` VALUES ('12656', '萧山区', '3', '12597', 'X');
INSERT INTO `cms_region` VALUES ('12685', '余杭区', '3', '12597', 'Y');
INSERT INTO `cms_region` VALUES ('12705', '桐庐县', '3', '12597', 'T');
INSERT INTO `cms_region` VALUES ('12719', '淳安县', '3', '12597', 'C');
INSERT INTO `cms_region` VALUES ('12743', '建德市', '3', '12597', 'J');
INSERT INTO `cms_region` VALUES ('12760', '富阳市', '3', '12597', 'F');
INSERT INTO `cms_region` VALUES ('12786', '临安市', '3', '12597', 'L');
INSERT INTO `cms_region` VALUES ('12813', '宁波市', '2', '12596', 'N');
INSERT INTO `cms_region` VALUES ('12814', '市辖区', '3', '12813', 'S');
INSERT INTO `cms_region` VALUES ('12815', '海曙区', '3', '12813', 'H');
INSERT INTO `cms_region` VALUES ('12824', '江东区', '3', '12813', 'J');
INSERT INTO `cms_region` VALUES ('12832', '江北区', '3', '12813', 'J');
INSERT INTO `cms_region` VALUES ('12841', '北仑区', '3', '12813', 'B');
INSERT INTO `cms_region` VALUES ('12851', '镇海区', '3', '12813', 'Z');
INSERT INTO `cms_region` VALUES ('12858', '鄞州区', '3', '12813', 'Y');
INSERT INTO `cms_region` VALUES ('12881', '象山县', '3', '12813', 'X');
INSERT INTO `cms_region` VALUES ('12900', '宁海县', '3', '12813', 'N');
INSERT INTO `cms_region` VALUES ('12919', '余姚市', '3', '12813', 'Y');
INSERT INTO `cms_region` VALUES ('12941', '慈溪市', '3', '12813', 'C');
INSERT INTO `cms_region` VALUES ('12962', '奉化市', '3', '12813', 'F');
INSERT INTO `cms_region` VALUES ('12974', '温州市', '2', '12596', 'W');
INSERT INTO `cms_region` VALUES ('12975', '市辖区', '3', '12974', 'S');
INSERT INTO `cms_region` VALUES ('12976', '鹿城区', '3', '12974', 'L');
INSERT INTO `cms_region` VALUES ('12998', '龙湾区', '3', '12974', 'L');
INSERT INTO `cms_region` VALUES ('13009', '瓯海区', '3', '12974', 'O');
INSERT INTO `cms_region` VALUES ('13023', '洞头县', '3', '12974', 'D');
INSERT INTO `cms_region` VALUES ('13030', '永嘉县', '3', '12974', 'Y');
INSERT INTO `cms_region` VALUES ('13069', '平阳县', '3', '12974', 'P');
INSERT INTO `cms_region` VALUES ('13101', '苍南县', '3', '12974', 'C');
INSERT INTO `cms_region` VALUES ('13138', '文成县', '3', '12974', 'W');
INSERT INTO `cms_region` VALUES ('13172', '泰顺县', '3', '12974', 'T');
INSERT INTO `cms_region` VALUES ('13209', '瑞安市', '3', '12974', 'R');
INSERT INTO `cms_region` VALUES ('13248', '乐清市', '3', '12974', 'L');
INSERT INTO `cms_region` VALUES ('13280', '嘉兴市', '2', '12596', 'J');
INSERT INTO `cms_region` VALUES ('13281', '市辖区', '3', '13280', 'S');
INSERT INTO `cms_region` VALUES ('13282', '南湖区', '3', '13280', 'N');
INSERT INTO `cms_region` VALUES ('13295', '秀洲区', '3', '13280', 'X');
INSERT INTO `cms_region` VALUES ('13304', '嘉善县', '3', '13280', 'J');
INSERT INTO `cms_region` VALUES ('13316', '海盐县', '3', '13280', 'H');
INSERT INTO `cms_region` VALUES ('13325', '海宁市', '3', '13280', 'H');
INSERT INTO `cms_region` VALUES ('13339', '平湖市', '3', '13280', 'P');
INSERT INTO `cms_region` VALUES ('13350', '桐乡市', '3', '13280', 'T');
INSERT INTO `cms_region` VALUES ('13364', '湖州市', '2', '12596', 'H');
INSERT INTO `cms_region` VALUES ('13365', '市辖区', '3', '13364', 'S');
INSERT INTO `cms_region` VALUES ('13366', '吴兴区', '3', '13364', 'W');
INSERT INTO `cms_region` VALUES ('13382', '南浔区', '3', '13364', 'N');
INSERT INTO `cms_region` VALUES ('13392', '德清县', '3', '13364', 'D');
INSERT INTO `cms_region` VALUES ('13404', '长兴县', '3', '13364', 'C');
INSERT INTO `cms_region` VALUES ('13421', '安吉县', '3', '13364', 'A');
INSERT INTO `cms_region` VALUES ('13437', '绍兴市', '2', '12596', 'S');
INSERT INTO `cms_region` VALUES ('13438', '市辖区', '3', '13437', 'S');
INSERT INTO `cms_region` VALUES ('13439', '越城区', '3', '13437', 'Y');
INSERT INTO `cms_region` VALUES ('13453', '绍兴县', '3', '13437', 'S');
INSERT INTO `cms_region` VALUES ('13473', '新昌县', '3', '13437', 'X');
INSERT INTO `cms_region` VALUES ('13490', '诸暨市', '3', '13437', 'Z');
INSERT INTO `cms_region` VALUES ('13518', '上虞市', '3', '13437', 'S');
INSERT INTO `cms_region` VALUES ('13542', '嵊州市', '3', '13437', 'S');
INSERT INTO `cms_region` VALUES ('13564', '金华市', '2', '12596', 'J');
INSERT INTO `cms_region` VALUES ('13565', '市辖区', '3', '13564', 'S');
INSERT INTO `cms_region` VALUES ('13566', '婺城区', '3', '13564', 'W');
INSERT INTO `cms_region` VALUES ('13594', '金东区', '3', '13564', 'J');
INSERT INTO `cms_region` VALUES ('13606', '武义县', '3', '13564', 'W');
INSERT INTO `cms_region` VALUES ('13625', '浦江县', '3', '13564', 'P');
INSERT INTO `cms_region` VALUES ('13641', '磐安县', '3', '13564', 'P');
INSERT INTO `cms_region` VALUES ('13662', '兰溪市', '3', '13564', 'L');
INSERT INTO `cms_region` VALUES ('13678', '义乌市', '3', '13564', 'Y');
INSERT INTO `cms_region` VALUES ('13692', '东阳市', '3', '13564', 'D');
INSERT INTO `cms_region` VALUES ('13711', '永康市', '3', '13564', 'Y');
INSERT INTO `cms_region` VALUES ('13726', '衢州市', '2', '12596', 'Q');
INSERT INTO `cms_region` VALUES ('13727', '市辖区', '3', '13726', 'S');
INSERT INTO `cms_region` VALUES ('13728', '柯城区', '3', '13726', 'K');
INSERT INTO `cms_region` VALUES ('13746', '衢江区', '3', '13726', 'Q');
INSERT INTO `cms_region` VALUES ('13768', '常山县', '3', '13726', 'C');
INSERT INTO `cms_region` VALUES ('13783', '开化县', '3', '13726', 'K');
INSERT INTO `cms_region` VALUES ('13802', '龙游县', '3', '13726', 'L');
INSERT INTO `cms_region` VALUES ('13818', '江山市', '3', '13726', 'J');
INSERT INTO `cms_region` VALUES ('13840', '舟山市', '2', '12596', 'Z');
INSERT INTO `cms_region` VALUES ('13841', '市辖区', '3', '13840', 'S');
INSERT INTO `cms_region` VALUES ('13842', '定海区', '3', '13840', 'D');
INSERT INTO `cms_region` VALUES ('13859', '普陀区', '3', '13840', 'P');
INSERT INTO `cms_region` VALUES ('13874', '岱山县', '3', '13840', 'D');
INSERT INTO `cms_region` VALUES ('13882', '嵊泗县', '3', '13840', 'S');
INSERT INTO `cms_region` VALUES ('13890', '台州市', '2', '12596', 'T');
INSERT INTO `cms_region` VALUES ('13891', '市辖区', '3', '13890', 'S');
INSERT INTO `cms_region` VALUES ('13892', '椒江区', '3', '13890', 'J');
INSERT INTO `cms_region` VALUES ('13903', '黄岩区', '3', '13890', 'H');
INSERT INTO `cms_region` VALUES ('13923', '路桥区', '3', '13890', 'L');
INSERT INTO `cms_region` VALUES ('13934', '玉环县', '3', '13890', 'Y');
INSERT INTO `cms_region` VALUES ('13944', '三门县', '3', '13890', 'S');
INSERT INTO `cms_region` VALUES ('13959', '天台县', '3', '13890', 'T');
INSERT INTO `cms_region` VALUES ('13975', '仙居县', '3', '13890', 'X');
INSERT INTO `cms_region` VALUES ('13996', '温岭市', '3', '13890', 'W');
INSERT INTO `cms_region` VALUES ('14013', '临海市', '3', '13890', 'L');
INSERT INTO `cms_region` VALUES ('14033', '丽水市', '2', '12596', 'L');
INSERT INTO `cms_region` VALUES ('14034', '市辖区', '3', '14033', 'S');
INSERT INTO `cms_region` VALUES ('14035', '莲都区', '3', '14033', 'L');
INSERT INTO `cms_region` VALUES ('14054', '青田县', '3', '14033', 'Q');
INSERT INTO `cms_region` VALUES ('14086', '缙云县', '3', '14033', 'J');
INSERT INTO `cms_region` VALUES ('14111', '遂昌县', '3', '14033', 'S');
INSERT INTO `cms_region` VALUES ('14132', '松阳县', '3', '14033', 'S');
INSERT INTO `cms_region` VALUES ('14153', '云和县', '3', '14033', 'Y');
INSERT INTO `cms_region` VALUES ('14168', '庆元县', '3', '14033', 'Q');
INSERT INTO `cms_region` VALUES ('14189', '景宁县', '3', '14033', 'J');
INSERT INTO `cms_region` VALUES ('14214', '龙泉市', '3', '14033', 'L');
INSERT INTO `cms_region` VALUES ('14234', '安徽省', '1', '0', 'A');
INSERT INTO `cms_region` VALUES ('14235', '合肥市', '2', '14234', 'H');
INSERT INTO `cms_region` VALUES ('14236', '市辖区', '3', '14235', 'S');
INSERT INTO `cms_region` VALUES ('14237', '瑶海区', '3', '14235', 'Y');
INSERT INTO `cms_region` VALUES ('14254', '庐阳区', '3', '14235', 'L');
INSERT INTO `cms_region` VALUES ('14269', '蜀山区', '3', '14235', 'S');
INSERT INTO `cms_region` VALUES ('14286', '包河区', '3', '14235', 'B');
INSERT INTO `cms_region` VALUES ('14297', '长丰县', '3', '14235', 'C');
INSERT INTO `cms_region` VALUES ('14314', '肥东县', '3', '14235', 'F');
INSERT INTO `cms_region` VALUES ('14334', '肥西县', '3', '14235', 'F');
INSERT INTO `cms_region` VALUES ('14351', '芜湖市', '2', '14234', 'W');
INSERT INTO `cms_region` VALUES ('14352', '市辖区', '3', '14351', 'S');
INSERT INTO `cms_region` VALUES ('14353', '镜湖区', '3', '14351', 'J');
INSERT INTO `cms_region` VALUES ('14366', '弋江区', '3', '14351', 'Y');
INSERT INTO `cms_region` VALUES ('14374', '鸠江区', '3', '14351', 'J');
INSERT INTO `cms_region` VALUES ('14382', '三山区', '3', '14351', 'S');
INSERT INTO `cms_region` VALUES ('14387', '芜湖县', '3', '14351', 'W');
INSERT INTO `cms_region` VALUES ('14394', '繁昌县', '3', '14351', 'F');
INSERT INTO `cms_region` VALUES ('14401', '南陵县', '3', '14351', 'N');
INSERT INTO `cms_region` VALUES ('14410', '蚌埠市', '2', '14234', 'B');
INSERT INTO `cms_region` VALUES ('14411', '市辖区', '3', '14410', 'S');
INSERT INTO `cms_region` VALUES ('14412', '龙子湖区', '3', '14410', 'L');
INSERT INTO `cms_region` VALUES ('14422', '蚌山区', '3', '14410', 'B');
INSERT INTO `cms_region` VALUES ('14434', '禹会区', '3', '14410', 'Y');
INSERT INTO `cms_region` VALUES ('14443', '淮上区', '3', '14410', 'H');
INSERT INTO `cms_region` VALUES ('14449', '怀远县', '3', '14410', 'H');
INSERT INTO `cms_region` VALUES ('14471', '五河县', '3', '14410', 'W');
INSERT INTO `cms_region` VALUES ('14487', '固镇县', '3', '14410', 'G');
INSERT INTO `cms_region` VALUES ('14500', '淮南市', '2', '14234', 'H');
INSERT INTO `cms_region` VALUES ('14501', '市辖区', '3', '14500', 'S');
INSERT INTO `cms_region` VALUES ('14502', '大通区', '3', '14500', 'D');
INSERT INTO `cms_region` VALUES ('14508', '田家庵区', '3', '14500', 'T');
INSERT INTO `cms_region` VALUES ('14523', '谢家集区', '3', '14500', 'X');
INSERT INTO `cms_region` VALUES ('14535', '八公山区', '3', '14500', 'B');
INSERT INTO `cms_region` VALUES ('14542', '潘集区', '3', '14500', 'P');
INSERT INTO `cms_region` VALUES ('14554', '凤台县', '3', '14500', 'F');
INSERT INTO `cms_region` VALUES ('14575', '马鞍山市', '2', '14234', 'M');
INSERT INTO `cms_region` VALUES ('14576', '市辖区', '3', '14575', 'S');
INSERT INTO `cms_region` VALUES ('14577', '金家庄区', '3', '14575', 'J');
INSERT INTO `cms_region` VALUES ('14583', '花山区', '3', '14575', 'H');
INSERT INTO `cms_region` VALUES ('14589', '雨山区', '3', '14575', 'Y');
INSERT INTO `cms_region` VALUES ('14597', '当涂县', '3', '14575', 'D');
INSERT INTO `cms_region` VALUES ('14612', '淮北市', '2', '14234', 'H');
INSERT INTO `cms_region` VALUES ('14613', '市辖区', '3', '14612', 'S');
INSERT INTO `cms_region` VALUES ('14614', '杜集区', '3', '14612', 'D');
INSERT INTO `cms_region` VALUES ('14620', '相山区', '3', '14612', 'X');
INSERT INTO `cms_region` VALUES ('14632', '烈山区', '3', '14612', 'L');
INSERT INTO `cms_region` VALUES ('14641', '濉溪县', '3', '14612', 'S');
INSERT INTO `cms_region` VALUES ('14653', '铜陵市', '2', '14234', 'T');
INSERT INTO `cms_region` VALUES ('14654', '市辖区', '3', '14653', 'S');
INSERT INTO `cms_region` VALUES ('14655', '铜官山区', '3', '14653', 'T');
INSERT INTO `cms_region` VALUES ('14663', '狮子山区', '3', '14653', 'S');
INSERT INTO `cms_region` VALUES ('14671', '铜陵市郊区', '3', '14653', 'T');
INSERT INTO `cms_region` VALUES ('14678', '铜陵县', '3', '14653', 'T');
INSERT INTO `cms_region` VALUES ('14687', '安庆市', '2', '14234', 'A');
INSERT INTO `cms_region` VALUES ('14688', '市辖区', '3', '14687', 'S');
INSERT INTO `cms_region` VALUES ('14689', '迎江区', '3', '14687', 'Y');
INSERT INTO `cms_region` VALUES ('14700', '大观区', '3', '14687', 'D');
INSERT INTO `cms_region` VALUES ('14712', '宜秀区', '3', '14687', 'Y');
INSERT INTO `cms_region` VALUES ('14720', '怀宁县', '3', '14687', 'H');
INSERT INTO `cms_region` VALUES ('14741', '枞阳县', '3', '14687', 'C');
INSERT INTO `cms_region` VALUES ('14764', '潜山县', '3', '14687', 'Q');
INSERT INTO `cms_region` VALUES ('14782', '太湖县', '3', '14687', 'T');
INSERT INTO `cms_region` VALUES ('14798', '宿松县', '3', '14687', 'S');
INSERT INTO `cms_region` VALUES ('14823', '望江县', '3', '14687', 'W');
INSERT INTO `cms_region` VALUES ('14834', '岳西县', '3', '14687', 'Y');
INSERT INTO `cms_region` VALUES ('14859', '桐城市', '3', '14687', 'T');
INSERT INTO `cms_region` VALUES ('14887', '黄山市', '2', '14234', 'H');
INSERT INTO `cms_region` VALUES ('14888', '市辖区', '3', '14887', 'S');
INSERT INTO `cms_region` VALUES ('14889', '屯溪区', '3', '14887', 'T');
INSERT INTO `cms_region` VALUES ('14900', '黄山区', '3', '14887', 'H');
INSERT INTO `cms_region` VALUES ('14917', '徽州区', '3', '14887', 'H');
INSERT INTO `cms_region` VALUES ('14926', '歙县', '3', '14887', 'S');
INSERT INTO `cms_region` VALUES ('14955', '休宁县', '3', '14887', 'X');
INSERT INTO `cms_region` VALUES ('14977', '黟县', '3', '14887', 'Y');
INSERT INTO `cms_region` VALUES ('14986', '祁门县', '3', '14887', 'Q');
INSERT INTO `cms_region` VALUES ('15005', '滁州市', '2', '14234', 'C');
INSERT INTO `cms_region` VALUES ('15006', '市辖区', '3', '15005', 'S');
INSERT INTO `cms_region` VALUES ('15007', '琅琊区', '3', '15005', 'L');
INSERT INTO `cms_region` VALUES ('15016', '南谯区', '3', '15005', 'N');
INSERT INTO `cms_region` VALUES ('15034', '来安县', '3', '15005', 'L');
INSERT INTO `cms_region` VALUES ('15053', '全椒县', '3', '15005', 'Q');
INSERT INTO `cms_region` VALUES ('15071', '定远县', '3', '15005', 'D');
INSERT INTO `cms_region` VALUES ('15109', '凤阳县', '3', '15005', 'F');
INSERT INTO `cms_region` VALUES ('15136', '天长市', '3', '15005', 'T');
INSERT INTO `cms_region` VALUES ('15166', '明光市', '3', '15005', 'M');
INSERT INTO `cms_region` VALUES ('15194', '阜阳市', '2', '14234', 'F');
INSERT INTO `cms_region` VALUES ('15195', '市辖区', '3', '15194', 'S');
INSERT INTO `cms_region` VALUES ('15196', '颍州区', '3', '15194', 'Y');
INSERT INTO `cms_region` VALUES ('15211', '颍东区', '3', '15194', 'Y');
INSERT INTO `cms_region` VALUES ('15224', '颍泉区', '3', '15194', 'Y');
INSERT INTO `cms_region` VALUES ('15231', '临泉县', '3', '15194', 'L');
INSERT INTO `cms_region` VALUES ('15264', '太和县', '3', '15194', 'T');
INSERT INTO `cms_region` VALUES ('15296', '阜南县', '3', '15194', 'F');
INSERT INTO `cms_region` VALUES ('15328', '颍上县', '3', '15194', 'Y');
INSERT INTO `cms_region` VALUES ('15359', '界首市', '3', '15194', 'J');
INSERT INTO `cms_region` VALUES ('15378', '宿州市', '2', '14234', 'S');
INSERT INTO `cms_region` VALUES ('15379', '市辖区', '3', '15378', 'S');
INSERT INTO `cms_region` VALUES ('15380', '墉桥区', '3', '15378', 'Y');
INSERT INTO `cms_region` VALUES ('15417', '砀山县', '3', '15378', 'D');
INSERT INTO `cms_region` VALUES ('15437', '萧县', '3', '15378', 'X');
INSERT INTO `cms_region` VALUES ('15461', '灵璧县', '3', '15378', 'L');
INSERT INTO `cms_region` VALUES ('15482', '泗县', '3', '15378', 'S');
INSERT INTO `cms_region` VALUES ('15499', '巢湖市', '2', '14234', 'C');
INSERT INTO `cms_region` VALUES ('15500', '市辖区', '3', '15499', 'S');
INSERT INTO `cms_region` VALUES ('15501', '居巢区', '3', '15499', 'J');
INSERT INTO `cms_region` VALUES ('15520', '庐江县', '3', '15499', 'L');
INSERT INTO `cms_region` VALUES ('15542', '无为县', '3', '15499', 'W');
INSERT INTO `cms_region` VALUES ('15566', '含山县', '3', '15499', 'H');
INSERT INTO `cms_region` VALUES ('15575', '和县', '3', '15499', 'H');
INSERT INTO `cms_region` VALUES ('15586', '六安市', '2', '14234', 'L');
INSERT INTO `cms_region` VALUES ('15587', '市辖区', '3', '15586', 'S');
INSERT INTO `cms_region` VALUES ('15588', '金安区', '3', '15586', 'J');
INSERT INTO `cms_region` VALUES ('15612', '裕安区', '3', '15586', 'Y');
INSERT INTO `cms_region` VALUES ('15635', '寿县', '3', '15586', 'S');
INSERT INTO `cms_region` VALUES ('15662', '霍邱县', '3', '15586', 'H');
INSERT INTO `cms_region` VALUES ('15698', '舒城县', '3', '15586', 'S');
INSERT INTO `cms_region` VALUES ('15720', '金寨县', '3', '15586', 'J');
INSERT INTO `cms_region` VALUES ('15747', '霍山县', '3', '15586', 'H');
INSERT INTO `cms_region` VALUES ('15764', '亳州市', '2', '14234', 'H');
INSERT INTO `cms_region` VALUES ('15765', '市辖区', '3', '15764', 'S');
INSERT INTO `cms_region` VALUES ('15766', '谯城区', '3', '15764', 'Q');
INSERT INTO `cms_region` VALUES ('15795', '涡阳县', '3', '15764', 'W');
INSERT INTO `cms_region` VALUES ('15823', '蒙城县', '3', '15764', 'M');
INSERT INTO `cms_region` VALUES ('15843', '利辛县', '3', '15764', 'L');
INSERT INTO `cms_region` VALUES ('15871', '池州市', '2', '14234', 'C');
INSERT INTO `cms_region` VALUES ('15872', '市辖区', '3', '15871', 'S');
INSERT INTO `cms_region` VALUES ('15873', '贵池区', '3', '15871', 'G');
INSERT INTO `cms_region` VALUES ('15900', '东至县', '3', '15871', 'D');
INSERT INTO `cms_region` VALUES ('15930', '石台县', '3', '15871', 'S');
INSERT INTO `cms_region` VALUES ('15944', '青阳县', '3', '15871', 'Q');
INSERT INTO `cms_region` VALUES ('15958', '宣城市', '2', '14234', 'X');
INSERT INTO `cms_region` VALUES ('15959', '市辖区', '3', '15958', 'S');
INSERT INTO `cms_region` VALUES ('15960', '宣州区', '3', '15958', 'X');
INSERT INTO `cms_region` VALUES ('15987', '郎溪县', '3', '15958', 'L');
INSERT INTO `cms_region` VALUES ('16001', '广德县', '3', '15958', 'G');
INSERT INTO `cms_region` VALUES ('16013', '泾县', '3', '15958', 'J');
INSERT INTO `cms_region` VALUES ('16025', '绩溪县', '3', '15958', 'J');
INSERT INTO `cms_region` VALUES ('16037', '旌德县', '3', '15958', 'J');
INSERT INTO `cms_region` VALUES ('16048', '宁国市', '3', '15958', 'N');
INSERT INTO `cms_region` VALUES ('16068', '福建省', '1', '0', 'F');
INSERT INTO `cms_region` VALUES ('16069', '福州市', '2', '16068', 'F');
INSERT INTO `cms_region` VALUES ('16070', '市辖区', '3', '16069', 'S');
INSERT INTO `cms_region` VALUES ('16071', '鼓楼区', '3', '16069', 'G');
INSERT INTO `cms_region` VALUES ('16082', '台江区', '3', '16069', 'T');
INSERT INTO `cms_region` VALUES ('16093', '仓山区', '3', '16069', 'C');
INSERT INTO `cms_region` VALUES ('16108', '马尾区', '3', '16069', 'M');
INSERT INTO `cms_region` VALUES ('16113', '晋安区', '3', '16069', 'J');
INSERT INTO `cms_region` VALUES ('16123', '闽侯县', '3', '16069', 'M');
INSERT INTO `cms_region` VALUES ('16140', '连江县', '3', '16069', 'L');
INSERT INTO `cms_region` VALUES ('16164', '罗源县', '3', '16069', 'L');
INSERT INTO `cms_region` VALUES ('16177', '闽清县', '3', '16069', 'M');
INSERT INTO `cms_region` VALUES ('16194', '永泰县', '3', '16069', 'Y');
INSERT INTO `cms_region` VALUES ('16216', '平潭县', '3', '16069', 'P');
INSERT INTO `cms_region` VALUES ('16232', '福清市', '3', '16069', 'F');
INSERT INTO `cms_region` VALUES ('16259', '长乐市', '3', '16069', 'C');
INSERT INTO `cms_region` VALUES ('16278', '厦门市', '2', '16068', 'X');
INSERT INTO `cms_region` VALUES ('16279', '市辖区', '3', '16278', 'S');
INSERT INTO `cms_region` VALUES ('16280', '思明区', '3', '16278', 'S');
INSERT INTO `cms_region` VALUES ('16294', '海沧区', '3', '16278', 'H');
INSERT INTO `cms_region` VALUES ('16303', '湖里区', '3', '16278', 'H');
INSERT INTO `cms_region` VALUES ('16315', '集美区', '3', '16278', 'J');
INSERT INTO `cms_region` VALUES ('16326', '同安区', '3', '16278', 'T');
INSERT INTO `cms_region` VALUES ('16341', '翔安区', '3', '16278', 'X');
INSERT INTO `cms_region` VALUES ('16348', '莆田市', '2', '16068', 'P');
INSERT INTO `cms_region` VALUES ('16349', '市辖区', '3', '16348', 'S');
INSERT INTO `cms_region` VALUES ('16350', '城厢区', '3', '16348', 'C');
INSERT INTO `cms_region` VALUES ('16358', '涵江区', '3', '16348', 'H');
INSERT INTO `cms_region` VALUES ('16372', '荔城区', '3', '16348', 'L');
INSERT INTO `cms_region` VALUES ('16379', '秀屿区', '3', '16348', 'X');
INSERT INTO `cms_region` VALUES ('16393', '仙游县', '3', '16348', 'X');
INSERT INTO `cms_region` VALUES ('16412', '三明市', '2', '16068', 'S');
INSERT INTO `cms_region` VALUES ('16413', '市辖区', '3', '16412', 'S');
INSERT INTO `cms_region` VALUES ('16414', '梅列区', '3', '16412', 'M');
INSERT INTO `cms_region` VALUES ('16421', '三元区', '3', '16412', 'S');
INSERT INTO `cms_region` VALUES ('16430', '明溪县', '3', '16412', 'M');
INSERT INTO `cms_region` VALUES ('16440', '清流县', '3', '16412', 'Q');
INSERT INTO `cms_region` VALUES ('16455', '宁化县', '3', '16412', 'N');
INSERT INTO `cms_region` VALUES ('16472', '大田县', '3', '16412', 'D');
INSERT INTO `cms_region` VALUES ('16492', '尤溪县', '3', '16412', 'Y');
INSERT INTO `cms_region` VALUES ('16508', '沙县', '3', '16412', 'S');
INSERT INTO `cms_region` VALUES ('16521', '将乐县', '3', '16412', 'J');
INSERT INTO `cms_region` VALUES ('16535', '泰宁县', '3', '16412', 'T');
INSERT INTO `cms_region` VALUES ('16545', '建宁县', '3', '16412', 'J');
INSERT INTO `cms_region` VALUES ('16556', '永安市', '3', '16412', 'Y');
INSERT INTO `cms_region` VALUES ('16572', '泉州市', '2', '16068', 'Q');
INSERT INTO `cms_region` VALUES ('16573', '市辖区', '3', '16572', 'S');
INSERT INTO `cms_region` VALUES ('16574', '鲤城区', '3', '16572', 'L');
INSERT INTO `cms_region` VALUES ('16584', '丰泽区', '3', '16572', 'F');
INSERT INTO `cms_region` VALUES ('16593', '洛江区', '3', '16572', 'L');
INSERT INTO `cms_region` VALUES ('16600', '泉港区', '3', '16572', 'Q');
INSERT INTO `cms_region` VALUES ('16608', '惠安县', '3', '16572', 'H');
INSERT INTO `cms_region` VALUES ('16625', '安溪县', '3', '16572', 'A');
INSERT INTO `cms_region` VALUES ('16650', '永春县', '3', '16572', 'Y');
INSERT INTO `cms_region` VALUES ('16673', '德化县', '3', '16572', 'D');
INSERT INTO `cms_region` VALUES ('16692', '金门县', '3', '16572', 'J');
INSERT INTO `cms_region` VALUES ('16693', '石狮市', '3', '16572', 'S');
INSERT INTO `cms_region` VALUES ('16703', '晋江市', '3', '16572', 'J');
INSERT INTO `cms_region` VALUES ('16726', '南安市', '3', '16572', 'N');
INSERT INTO `cms_region` VALUES ('16754', '漳州市', '2', '16068', 'Z');
INSERT INTO `cms_region` VALUES ('16755', '市辖区', '3', '16754', 'S');
INSERT INTO `cms_region` VALUES ('16756', '芗城区', '3', '16754', 'X');
INSERT INTO `cms_region` VALUES ('16772', '龙文区', '3', '16754', 'L');
INSERT INTO `cms_region` VALUES ('16778', '云霄县', '3', '16754', 'Y');
INSERT INTO `cms_region` VALUES ('16790', '漳浦县', '3', '16754', 'Z');
INSERT INTO `cms_region` VALUES ('16821', '诏安县', '3', '16754', 'Z');
INSERT INTO `cms_region` VALUES ('16842', '长泰县', '3', '16754', 'C');
INSERT INTO `cms_region` VALUES ('16852', '东山县', '3', '16754', 'D');
INSERT INTO `cms_region` VALUES ('16860', '南靖县', '3', '16754', 'N');
INSERT INTO `cms_region` VALUES ('16872', '平和县', '3', '16754', 'P');
INSERT INTO `cms_region` VALUES ('16889', '华安县', '3', '16754', 'H');
INSERT INTO `cms_region` VALUES ('16899', '龙海市', '3', '16754', 'L');
INSERT INTO `cms_region` VALUES ('16924', '南平市', '2', '16068', 'N');
INSERT INTO `cms_region` VALUES ('16925', '市辖区', '3', '16924', 'S');
INSERT INTO `cms_region` VALUES ('16926', '延平区', '3', '16924', 'Y');
INSERT INTO `cms_region` VALUES ('16948', '顺昌县', '3', '16924', 'S');
INSERT INTO `cms_region` VALUES ('16961', '浦城县', '3', '16924', 'P');
INSERT INTO `cms_region` VALUES ('16982', '光泽县', '3', '16924', 'G');
INSERT INTO `cms_region` VALUES ('16991', '松溪县', '3', '16924', 'S');
INSERT INTO `cms_region` VALUES ('17001', '政和县', '3', '16924', 'Z');
INSERT INTO `cms_region` VALUES ('17012', '邵武市', '3', '16924', 'S');
INSERT INTO `cms_region` VALUES ('17033', '武夷山市', '3', '16924', 'W');
INSERT INTO `cms_region` VALUES ('17044', '建瓯市', '3', '16924', 'J');
INSERT INTO `cms_region` VALUES ('17063', '建阳市', '3', '16924', 'J');
INSERT INTO `cms_region` VALUES ('17077', '龙岩市', '2', '16068', 'L');
INSERT INTO `cms_region` VALUES ('17078', '市辖区', '3', '17077', 'S');
INSERT INTO `cms_region` VALUES ('17079', '新罗区', '3', '17077', 'X');
INSERT INTO `cms_region` VALUES ('17099', '长汀县', '3', '17077', 'C');
INSERT INTO `cms_region` VALUES ('17118', '永定县', '3', '17077', 'Y');
INSERT INTO `cms_region` VALUES ('17143', '上杭县', '3', '17077', 'S');
INSERT INTO `cms_region` VALUES ('17166', '武平县', '3', '17077', 'W');
INSERT INTO `cms_region` VALUES ('17184', '连城县', '3', '17077', 'L');
INSERT INTO `cms_region` VALUES ('17202', '漳平市', '3', '17077', 'Z');
INSERT INTO `cms_region` VALUES ('17219', '宁德市　', '2', '16068', 'N');
INSERT INTO `cms_region` VALUES ('17220', '市辖区', '3', '17219', 'S');
INSERT INTO `cms_region` VALUES ('17221', '蕉城区', '3', '17219', 'J');
INSERT INTO `cms_region` VALUES ('17239', '霞浦县', '3', '17219', 'X');
INSERT INTO `cms_region` VALUES ('17254', '古田县', '3', '17219', 'G');
INSERT INTO `cms_region` VALUES ('17269', '屏南县', '3', '17219', 'P');
INSERT INTO `cms_region` VALUES ('17281', '寿宁县', '3', '17219', 'S');
INSERT INTO `cms_region` VALUES ('17296', '周宁县', '3', '17219', 'Z');
INSERT INTO `cms_region` VALUES ('17306', '柘荣县', '3', '17219', 'Z');
INSERT INTO `cms_region` VALUES ('17316', '福安市', '3', '17219', 'F');
INSERT INTO `cms_region` VALUES ('17341', '福鼎市', '3', '17219', 'F');
INSERT INTO `cms_region` VALUES ('17359', '江西省', '1', '0', 'J');
INSERT INTO `cms_region` VALUES ('17360', '南昌市', '2', '17359', 'N');
INSERT INTO `cms_region` VALUES ('17361', '市辖区', '3', '17360', 'S');
INSERT INTO `cms_region` VALUES ('17362', '东湖区', '3', '17360', 'D');
INSERT INTO `cms_region` VALUES ('17374', '西湖区', '3', '17360', 'X');
INSERT INTO `cms_region` VALUES ('17387', '青云谱区', '3', '17360', 'Q');
INSERT INTO `cms_region` VALUES ('17395', '湾里区', '3', '17360', 'W');
INSERT INTO `cms_region` VALUES ('17402', '青山湖区', '3', '17360', 'Q');
INSERT INTO `cms_region` VALUES ('17420', '南昌县', '3', '17360', 'N');
INSERT INTO `cms_region` VALUES ('17443', '新建县', '3', '17360', 'X');
INSERT INTO `cms_region` VALUES ('17471', '安义县', '3', '17360', 'A');
INSERT INTO `cms_region` VALUES ('17485', '进贤县', '3', '17360', 'J');
INSERT INTO `cms_region` VALUES ('17508', '景德镇市', '2', '17359', 'J');
INSERT INTO `cms_region` VALUES ('17509', '市辖区', '3', '17508', 'S');
INSERT INTO `cms_region` VALUES ('17510', '昌江区', '3', '17508', 'C');
INSERT INTO `cms_region` VALUES ('17534', '珠山区', '3', '17508', 'Z');
INSERT INTO `cms_region` VALUES ('17545', '浮梁县', '3', '17508', 'F');
INSERT INTO `cms_region` VALUES ('17568', '乐平市', '3', '17508', 'L');
INSERT INTO `cms_region` VALUES ('17589', '萍乡市', '2', '17359', 'P');
INSERT INTO `cms_region` VALUES ('17590', '市辖区', '3', '17589', 'S');
INSERT INTO `cms_region` VALUES ('17591', '安源区', '3', '17589', 'A');
INSERT INTO `cms_region` VALUES ('17604', '湘东区', '3', '17589', 'X');
INSERT INTO `cms_region` VALUES ('17616', '莲花县', '3', '17589', 'L');
INSERT INTO `cms_region` VALUES ('17630', '上栗县', '3', '17589', 'S');
INSERT INTO `cms_region` VALUES ('17640', '芦溪县', '3', '17589', 'L');
INSERT INTO `cms_region` VALUES ('17651', '九江市', '2', '17359', 'J');
INSERT INTO `cms_region` VALUES ('17652', '市辖区', '3', '17651', 'S');
INSERT INTO `cms_region` VALUES ('17653', '庐山区', '3', '17651', 'L');
INSERT INTO `cms_region` VALUES ('17667', '浔阳区', '3', '17651', 'X');
INSERT INTO `cms_region` VALUES ('17676', '九江县', '3', '17651', 'J');
INSERT INTO `cms_region` VALUES ('17693', '武宁县', '3', '17651', 'W');
INSERT INTO `cms_region` VALUES ('17714', '修水县', '3', '17651', 'X');
INSERT INTO `cms_region` VALUES ('17751', '永修县', '3', '17651', 'Y');
INSERT INTO `cms_region` VALUES ('17773', '德安县', '3', '17651', 'D');
INSERT INTO `cms_region` VALUES ('17792', '星子县', '3', '17651', 'X');
INSERT INTO `cms_region` VALUES ('17807', '都昌县', '3', '17651', 'D');
INSERT INTO `cms_region` VALUES ('17834', '湖口县', '3', '17651', 'H');
INSERT INTO `cms_region` VALUES ('17849', '彭泽县', '3', '17651', 'P');
INSERT INTO `cms_region` VALUES ('17872', '瑞昌市', '3', '17651', 'R');
INSERT INTO `cms_region` VALUES ('17894', '新余市', '2', '17359', 'X');
INSERT INTO `cms_region` VALUES ('17895', '市辖区', '3', '17894', 'S');
INSERT INTO `cms_region` VALUES ('17896', '渝水区', '3', '17894', 'Y');
INSERT INTO `cms_region` VALUES ('17917', '分宜县', '3', '17894', 'F');
INSERT INTO `cms_region` VALUES ('17934', '鹰潭市', '2', '17359', 'Y');
INSERT INTO `cms_region` VALUES ('17935', '市辖区', '3', '17934', 'S');
INSERT INTO `cms_region` VALUES ('17936', '月湖区', '3', '17934', 'Y');
INSERT INTO `cms_region` VALUES ('17945', '余江县', '3', '17934', 'Y');
INSERT INTO `cms_region` VALUES ('17966', '贵溪市', '3', '17934', 'G');
INSERT INTO `cms_region` VALUES ('17999', '赣州市', '2', '17359', 'G');
INSERT INTO `cms_region` VALUES ('18000', '市辖区', '3', '17999', 'S');
INSERT INTO `cms_region` VALUES ('18001', '章贡区', '3', '17999', 'Z');
INSERT INTO `cms_region` VALUES ('18016', '赣县', '3', '17999', 'G');
INSERT INTO `cms_region` VALUES ('18037', '信丰县', '3', '17999', 'X');
INSERT INTO `cms_region` VALUES ('18055', '大余县', '3', '17999', 'D');
INSERT INTO `cms_region` VALUES ('18068', '上犹县', '3', '17999', 'S');
INSERT INTO `cms_region` VALUES ('18084', '崇义县', '3', '17999', 'C');
INSERT INTO `cms_region` VALUES ('18102', '安远县', '3', '17999', 'A');
INSERT INTO `cms_region` VALUES ('18122', '龙南县', '3', '17999', 'L');
INSERT INTO `cms_region` VALUES ('18139', '定南县', '3', '17999', 'D');
INSERT INTO `cms_region` VALUES ('18148', '全南县', '3', '17999', 'Q');
INSERT INTO `cms_region` VALUES ('18161', '宁都县', '3', '17999', 'N');
INSERT INTO `cms_region` VALUES ('18187', '于都县', '3', '17999', 'Y');
INSERT INTO `cms_region` VALUES ('18212', '兴国县', '3', '17999', 'X');
INSERT INTO `cms_region` VALUES ('18239', '会昌县', '3', '17999', 'H');
INSERT INTO `cms_region` VALUES ('18260', '寻乌县', '3', '17999', 'X');
INSERT INTO `cms_region` VALUES ('18276', '石城县', '3', '17999', 'S');
INSERT INTO `cms_region` VALUES ('18287', '瑞金市', '3', '17999', 'R');
INSERT INTO `cms_region` VALUES ('18306', '南康市', '3', '17999', 'N');
INSERT INTO `cms_region` VALUES ('18330', '吉安市', '2', '17359', 'J');
INSERT INTO `cms_region` VALUES ('18331', '市辖区', '3', '18330', 'S');
INSERT INTO `cms_region` VALUES ('18332', '吉州区', '3', '18330', 'J');
INSERT INTO `cms_region` VALUES ('18345', '青原区', '3', '18330', 'Q');
INSERT INTO `cms_region` VALUES ('18356', '吉安县', '3', '18330', 'J');
INSERT INTO `cms_region` VALUES ('18378', '吉水县', '3', '18330', 'J');
INSERT INTO `cms_region` VALUES ('18398', '峡江县', '3', '18330', 'X');
INSERT INTO `cms_region` VALUES ('18411', '新干县', '3', '18330', 'X');
INSERT INTO `cms_region` VALUES ('18429', '永丰县', '3', '18330', 'Y');
INSERT INTO `cms_region` VALUES ('18454', '泰和县', '3', '18330', 'T');
INSERT INTO `cms_region` VALUES ('18483', '遂川县', '3', '18330', 'S');
INSERT INTO `cms_region` VALUES ('18510', '万安县', '3', '18330', 'W');
INSERT INTO `cms_region` VALUES ('18529', '安福县', '3', '18330', 'A');
INSERT INTO `cms_region` VALUES ('18550', '永新县', '3', '18330', 'Y');
INSERT INTO `cms_region` VALUES ('18575', '井冈山市', '3', '18330', 'J');
INSERT INTO `cms_region` VALUES ('18598', '宜春市', '2', '17359', 'Y');
INSERT INTO `cms_region` VALUES ('18599', '市辖区', '3', '18598', 'S');
INSERT INTO `cms_region` VALUES ('18600', '袁州区', '3', '18598', 'Y');
INSERT INTO `cms_region` VALUES ('18639', '奉新县', '3', '18598', 'F');
INSERT INTO `cms_region` VALUES ('18659', '万载县', '3', '18598', 'W');
INSERT INTO `cms_region` VALUES ('18678', '上高县', '3', '18598', 'S');
INSERT INTO `cms_region` VALUES ('18696', '宜丰县', '3', '18598', 'Y');
INSERT INTO `cms_region` VALUES ('18714', '靖安县', '3', '18598', 'J');
INSERT INTO `cms_region` VALUES ('18727', '铜鼓县', '3', '18598', 'T');
INSERT INTO `cms_region` VALUES ('18741', '丰城市', '3', '18598', 'F');
INSERT INTO `cms_region` VALUES ('18777', '樟树市', '3', '18598', 'Z');
INSERT INTO `cms_region` VALUES ('18799', '高安市', '3', '18598', 'G');
INSERT INTO `cms_region` VALUES ('18829', '抚州市', '2', '17359', 'F');
INSERT INTO `cms_region` VALUES ('18830', '市辖区', '3', '18829', 'S');
INSERT INTO `cms_region` VALUES ('18831', '临川区', '3', '18829', 'L');
INSERT INTO `cms_region` VALUES ('18869', '南城县', '3', '18829', 'N');
INSERT INTO `cms_region` VALUES ('18882', '黎川县', '3', '18829', 'L');
INSERT INTO `cms_region` VALUES ('18900', '南丰县', '3', '18829', 'N');
INSERT INTO `cms_region` VALUES ('18915', '崇仁县', '3', '18829', 'C');
INSERT INTO `cms_region` VALUES ('18931', '乐安县', '3', '18829', 'L');
INSERT INTO `cms_region` VALUES ('18949', '宜黄县', '3', '18829', 'Y');
INSERT INTO `cms_region` VALUES ('18965', '金溪县', '3', '18829', 'J');
INSERT INTO `cms_region` VALUES ('18980', '资溪县', '3', '18829', 'Z');
INSERT INTO `cms_region` VALUES ('18988', '东乡县', '3', '18829', 'D');
INSERT INTO `cms_region` VALUES ('19010', '广昌县', '3', '18829', 'G');
INSERT INTO `cms_region` VALUES ('19024', '上饶市', '2', '17359', 'S');
INSERT INTO `cms_region` VALUES ('19025', '市辖区', '3', '19024', 'S');
INSERT INTO `cms_region` VALUES ('19026', '信州区', '3', '19024', 'X');
INSERT INTO `cms_region` VALUES ('19038', '上饶县', '3', '19024', 'S');
INSERT INTO `cms_region` VALUES ('19062', '广丰县', '3', '19024', 'G');
INSERT INTO `cms_region` VALUES ('19088', '玉山县', '3', '19024', 'Y');
INSERT INTO `cms_region` VALUES ('19108', '铅山县', '3', '19024', 'Q');
INSERT INTO `cms_region` VALUES ('19136', '横峰县', '3', '19024', 'H');
INSERT INTO `cms_region` VALUES ('19151', '弋阳县', '3', '19024', 'Y');
INSERT INTO `cms_region` VALUES ('19171', '余干县', '3', '19024', 'Y');
INSERT INTO `cms_region` VALUES ('19202', '鄱阳县', '3', '19024', 'P');
INSERT INTO `cms_region` VALUES ('19234', '万年县', '3', '19024', 'W');
INSERT INTO `cms_region` VALUES ('19248', '婺源县', '3', '19024', 'W');
INSERT INTO `cms_region` VALUES ('19265', '德兴市', '3', '19024', 'D');
INSERT INTO `cms_region` VALUES ('19280', '山东省', '1', '0', 'S');
INSERT INTO `cms_region` VALUES ('19281', '济南市', '2', '19280', 'J');
INSERT INTO `cms_region` VALUES ('19282', '市辖区', '3', '19281', 'S');
INSERT INTO `cms_region` VALUES ('19283', '历下区', '3', '19281', 'L');
INSERT INTO `cms_region` VALUES ('19295', '市中区', '3', '19281', 'S');
INSERT INTO `cms_region` VALUES ('19311', '槐荫区', '3', '19281', 'H');
INSERT INTO `cms_region` VALUES ('19326', '天桥区', '3', '19281', 'T');
INSERT INTO `cms_region` VALUES ('19342', '历城区', '3', '19281', 'L');
INSERT INTO `cms_region` VALUES ('19359', '长清区', '3', '19281', 'C');
INSERT INTO `cms_region` VALUES ('19370', '平阴县', '3', '19281', 'P');
INSERT INTO `cms_region` VALUES ('19378', '济阳县', '3', '19281', 'J');
INSERT INTO `cms_region` VALUES ('19387', '商河县', '3', '19281', 'S');
INSERT INTO `cms_region` VALUES ('19400', '章丘市', '3', '19281', 'Z');
INSERT INTO `cms_region` VALUES ('19421', '青岛市', '2', '19280', 'Q');
INSERT INTO `cms_region` VALUES ('19422', '市辖区', '3', '19421', 'S');
INSERT INTO `cms_region` VALUES ('19423', '市南区', '3', '19421', 'S');
INSERT INTO `cms_region` VALUES ('19438', '市北区', '3', '19421', 'S');
INSERT INTO `cms_region` VALUES ('19456', '四方区', '3', '19421', 'S');
INSERT INTO `cms_region` VALUES ('19464', '黄岛区', '3', '19421', 'H');
INSERT INTO `cms_region` VALUES ('19471', '崂山区', '3', '19421', 'L');
INSERT INTO `cms_region` VALUES ('19476', '李沧区', '3', '19421', 'L');
INSERT INTO `cms_region` VALUES ('19488', '城阳区', '3', '19421', 'C');
INSERT INTO `cms_region` VALUES ('19497', '胶州市', '3', '19421', 'J');
INSERT INTO `cms_region` VALUES ('19516', '即墨市', '3', '19421', 'J');
INSERT INTO `cms_region` VALUES ('19540', '平度市', '3', '19421', 'P');
INSERT INTO `cms_region` VALUES ('19572', '胶南市', '3', '19421', 'J');
INSERT INTO `cms_region` VALUES ('19590', '莱西市', '3', '19421', 'L');
INSERT INTO `cms_region` VALUES ('19608', '淄博市', '2', '19280', 'Z');
INSERT INTO `cms_region` VALUES ('19609', '市辖区', '3', '19608', 'S');
INSERT INTO `cms_region` VALUES ('19610', '淄川区', '3', '19608', 'Z');
INSERT INTO `cms_region` VALUES ('19632', '张店区', '3', '19608', 'Z');
INSERT INTO `cms_region` VALUES ('19649', '博山区', '3', '19608', 'B');
INSERT INTO `cms_region` VALUES ('19663', '临淄区', '3', '19608', 'L');
INSERT INTO `cms_region` VALUES ('19678', '周村区', '3', '19608', 'Z');
INSERT INTO `cms_region` VALUES ('19688', '桓台县', '3', '19608', 'H');
INSERT INTO `cms_region` VALUES ('19700', '高青县', '3', '19608', 'G');
INSERT INTO `cms_region` VALUES ('19710', '沂源县', '3', '19608', 'Y');
INSERT INTO `cms_region` VALUES ('19724', '枣庄市', '2', '19280', 'Z');
INSERT INTO `cms_region` VALUES ('19725', '市辖区', '3', '19724', 'S');
INSERT INTO `cms_region` VALUES ('19726', '市中区', '3', '19724', 'S');
INSERT INTO `cms_region` VALUES ('19738', '薛城区', '3', '19724', 'X');
INSERT INTO `cms_region` VALUES ('19748', '峄城区', '3', '19724', 'Y');
INSERT INTO `cms_region` VALUES ('19756', '台儿庄区', '3', '19724', 'T');
INSERT INTO `cms_region` VALUES ('19763', '山亭区', '3', '19724', 'S');
INSERT INTO `cms_region` VALUES ('19774', '滕州市', '3', '19724', 'T');
INSERT INTO `cms_region` VALUES ('19796', '东营市', '2', '19280', 'D');
INSERT INTO `cms_region` VALUES ('19797', '市辖区', '3', '19796', 'S');
INSERT INTO `cms_region` VALUES ('19798', '东营区', '3', '19796', 'D');
INSERT INTO `cms_region` VALUES ('19809', '河口区', '3', '19796', 'H');
INSERT INTO `cms_region` VALUES ('19817', '垦利县', '3', '19796', 'K');
INSERT INTO `cms_region` VALUES ('19825', '利津县', '3', '19796', 'L');
INSERT INTO `cms_region` VALUES ('19835', '广饶县', '3', '19796', 'G');
INSERT INTO `cms_region` VALUES ('19846', '烟台市', '2', '19280', 'Y');
INSERT INTO `cms_region` VALUES ('19847', '市辖区', '3', '19846', 'S');
INSERT INTO `cms_region` VALUES ('19848', '芝罘区', '3', '19846', 'Z');
INSERT INTO `cms_region` VALUES ('19861', '福山区', '3', '19846', 'F');
INSERT INTO `cms_region` VALUES ('19873', '牟平区', '3', '19846', 'M');
INSERT INTO `cms_region` VALUES ('19887', '莱山区', '3', '19846', 'L');
INSERT INTO `cms_region` VALUES ('19893', '长岛县', '3', '19846', 'C');
INSERT INTO `cms_region` VALUES ('19902', '龙口市', '3', '19846', 'L');
INSERT INTO `cms_region` VALUES ('19916', '莱阳市', '3', '19846', 'L');
INSERT INTO `cms_region` VALUES ('19935', '莱州市', '3', '19846', 'L');
INSERT INTO `cms_region` VALUES ('19952', '蓬莱市', '3', '19846', 'P');
INSERT INTO `cms_region` VALUES ('19965', '招远市', '3', '19846', 'Z');
INSERT INTO `cms_region` VALUES ('19980', '栖霞市', '3', '19846', 'Q');
INSERT INTO `cms_region` VALUES ('19996', '海阳市', '3', '19846', 'H');
INSERT INTO `cms_region` VALUES ('20012', '潍坊市', '2', '19280', 'W');
INSERT INTO `cms_region` VALUES ('20013', '市辖区', '3', '20012', 'S');
INSERT INTO `cms_region` VALUES ('20014', '潍城区', '3', '20012', 'W');
INSERT INTO `cms_region` VALUES ('20023', '寒亭区', '3', '20012', 'H');
INSERT INTO `cms_region` VALUES ('20034', '坊子区', '3', '20012', 'F');
INSERT INTO `cms_region` VALUES ('20043', '奎文区', '3', '20012', 'K');
INSERT INTO `cms_region` VALUES ('20055', '临朐县', '3', '20012', 'L');
INSERT INTO `cms_region` VALUES ('20074', '昌乐县', '3', '20012', 'C');
INSERT INTO `cms_region` VALUES ('20091', '青州市', '3', '20012', 'Q');
INSERT INTO `cms_region` VALUES ('20113', '诸城市', '3', '20012', 'Z');
INSERT INTO `cms_region` VALUES ('20137', '寿光市', '3', '20012', 'S');
INSERT INTO `cms_region` VALUES ('20155', '安丘市', '3', '20012', 'A');
INSERT INTO `cms_region` VALUES ('20179', '高密市', '3', '20012', 'G');
INSERT INTO `cms_region` VALUES ('20200', '昌邑市', '3', '20012', 'C');
INSERT INTO `cms_region` VALUES ('20216', '济宁市', '2', '19280', 'J');
INSERT INTO `cms_region` VALUES ('20217', '市辖区', '3', '20216', 'S');
INSERT INTO `cms_region` VALUES ('20218', '市中区', '3', '20216', 'S');
INSERT INTO `cms_region` VALUES ('20227', '任城区', '3', '20216', 'R');
INSERT INTO `cms_region` VALUES ('20257', '鱼台县', '3', '20216', 'Y');
INSERT INTO `cms_region` VALUES ('20268', '金乡县', '3', '20216', 'J');
INSERT INTO `cms_region` VALUES ('20282', '嘉祥县', '3', '20216', 'J');
INSERT INTO `cms_region` VALUES ('20298', '汶上县', '3', '20216', 'W');
INSERT INTO `cms_region` VALUES ('20313', '泗水县', '3', '20216', 'S');
INSERT INTO `cms_region` VALUES ('20327', '梁山县', '3', '20216', 'L');
INSERT INTO `cms_region` VALUES ('20342', '曲阜市', '3', '20216', 'Q');
INSERT INTO `cms_region` VALUES ('20355', '兖州市', '3', '20216', 'Y');
INSERT INTO `cms_region` VALUES ('20368', '邹城市', '3', '20216', 'Z');
INSERT INTO `cms_region` VALUES ('20386', '泰安市', '2', '19280', 'T');
INSERT INTO `cms_region` VALUES ('20387', '市辖区', '3', '20386', 'S');
INSERT INTO `cms_region` VALUES ('20388', '泰山区', '3', '20386', 'T');
INSERT INTO `cms_region` VALUES ('20397', '岱岳区', '3', '20386', 'D');
INSERT INTO `cms_region` VALUES ('20416', '宁阳县', '3', '20386', 'N');
INSERT INTO `cms_region` VALUES ('20429', '东平县', '3', '20386', 'D');
INSERT INTO `cms_region` VALUES ('20444', '新泰市', '3', '20386', 'X');
INSERT INTO `cms_region` VALUES ('20465', '肥城市', '3', '20386', 'F');
INSERT INTO `cms_region` VALUES ('20480', '威海市', '2', '19280', 'W');
INSERT INTO `cms_region` VALUES ('20481', '市辖区', '3', '20480', 'S');
INSERT INTO `cms_region` VALUES ('20482', '环翠区', '3', '20480', 'H');
INSERT INTO `cms_region` VALUES ('20500', '文登市', '3', '20480', 'W');
INSERT INTO `cms_region` VALUES ('20519', '荣成市', '3', '20480', 'R');
INSERT INTO `cms_region` VALUES ('20542', '乳山市', '3', '20480', 'R');
INSERT INTO `cms_region` VALUES ('20558', '日照市', '2', '19280', 'R');
INSERT INTO `cms_region` VALUES ('20559', '市辖区', '3', '20558', 'S');
INSERT INTO `cms_region` VALUES ('20560', '东港区', '3', '20558', 'D');
INSERT INTO `cms_region` VALUES ('20573', '岚山区', '3', '20558', 'L');
INSERT INTO `cms_region` VALUES ('20583', '五莲县', '3', '20558', 'W');
INSERT INTO `cms_region` VALUES ('20596', '莒县', '3', '20558', 'J');
INSERT INTO `cms_region` VALUES ('20618', '莱芜市', '2', '19280', 'L');
INSERT INTO `cms_region` VALUES ('20619', '市辖区', '3', '20618', 'S');
INSERT INTO `cms_region` VALUES ('20620', '莱城区', '3', '20618', 'L');
INSERT INTO `cms_region` VALUES ('20636', '钢城区', '3', '20618', 'G');
INSERT INTO `cms_region` VALUES ('20642', '临沂市', '2', '19280', 'L');
INSERT INTO `cms_region` VALUES ('20643', '临沂市辖区', '3', '20642', 'L');
INSERT INTO `cms_region` VALUES ('20644', '兰山区', '3', '20642', 'L');
INSERT INTO `cms_region` VALUES ('20656', '罗庄区', '3', '20642', 'L');
INSERT INTO `cms_region` VALUES ('20665', '河东区', '3', '20642', 'H');
INSERT INTO `cms_region` VALUES ('20678', '沂南县', '3', '20642', 'Y');
INSERT INTO `cms_region` VALUES ('20696', '郯城县', '3', '20642', 'T');
INSERT INTO `cms_region` VALUES ('20714', '沂水县', '3', '20642', 'Y');
INSERT INTO `cms_region` VALUES ('20734', '苍山县', '3', '20642', 'C');
INSERT INTO `cms_region` VALUES ('20756', '费县', '3', '20642', 'F');
INSERT INTO `cms_region` VALUES ('20775', '平邑县', '3', '20642', 'P');
INSERT INTO `cms_region` VALUES ('20792', '莒南县', '3', '20642', 'J');
INSERT INTO `cms_region` VALUES ('20811', '蒙阴县', '3', '20642', 'M');
INSERT INTO `cms_region` VALUES ('20823', '临沭县', '3', '20642', 'L');
INSERT INTO `cms_region` VALUES ('20836', '德州市', '2', '19280', 'D');
INSERT INTO `cms_region` VALUES ('20837', '市辖区', '3', '20836', 'S');
INSERT INTO `cms_region` VALUES ('20838', '德城区', '3', '20836', 'D');
INSERT INTO `cms_region` VALUES ('20850', '陵县', '3', '20836', 'L');
INSERT INTO `cms_region` VALUES ('20864', '宁津县', '3', '20836', 'N');
INSERT INTO `cms_region` VALUES ('20876', '庆云县', '3', '20836', 'Q');
INSERT INTO `cms_region` VALUES ('20886', '临邑县', '3', '20836', 'L');
INSERT INTO `cms_region` VALUES ('20899', '齐河县', '3', '20836', 'Q');
INSERT INTO `cms_region` VALUES ('20914', '平原县', '3', '20836', 'P');
INSERT INTO `cms_region` VALUES ('20927', '夏津县', '3', '20836', 'X');
INSERT INTO `cms_region` VALUES ('20942', '武城县', '3', '20836', 'W');
INSERT INTO `cms_region` VALUES ('20952', '乐陵市', '3', '20836', 'L');
INSERT INTO `cms_region` VALUES ('20969', '禹城市', '3', '20836', 'Y');
INSERT INTO `cms_region` VALUES ('20981', '聊城市', '2', '19280', 'L');
INSERT INTO `cms_region` VALUES ('20982', '市辖区', '3', '20981', 'S');
INSERT INTO `cms_region` VALUES ('20983', '东昌府区', '3', '20981', 'D');
INSERT INTO `cms_region` VALUES ('21004', '阳谷县', '3', '20981', 'Y');
INSERT INTO `cms_region` VALUES ('21023', '莘县', '3', '20981', 'S');
INSERT INTO `cms_region` VALUES ('21046', '茌平县', '3', '20981', 'C');
INSERT INTO `cms_region` VALUES ('21063', '东阿县', '3', '20981', 'D');
INSERT INTO `cms_region` VALUES ('21075', '冠县', '3', '20981', 'G');
INSERT INTO `cms_region` VALUES ('21093', '高唐县', '3', '20981', 'G');
INSERT INTO `cms_region` VALUES ('21106', '临清市', '3', '20981', 'L');
INSERT INTO `cms_region` VALUES ('21123', '滨州市', '2', '19280', 'B');
INSERT INTO `cms_region` VALUES ('21124', '市辖区', '3', '21123', 'S');
INSERT INTO `cms_region` VALUES ('21125', '滨城区', '3', '21123', 'B');
INSERT INTO `cms_region` VALUES ('21141', '惠民县', '3', '21123', 'H');
INSERT INTO `cms_region` VALUES ('21156', '阳信县', '3', '21123', 'Y');
INSERT INTO `cms_region` VALUES ('21166', '无棣县', '3', '21123', 'W');
INSERT INTO `cms_region` VALUES ('21178', '沾化县', '3', '21123', 'Z');
INSERT INTO `cms_region` VALUES ('21190', '博兴县', '3', '21123', 'B');
INSERT INTO `cms_region` VALUES ('21201', '邹平县', '3', '21123', 'Z');
INSERT INTO `cms_region` VALUES ('21218', '菏泽市', '2', '19280', 'H');
INSERT INTO `cms_region` VALUES ('21219', '市辖区', '3', '21218', 'S');
INSERT INTO `cms_region` VALUES ('21220', '牡丹区', '3', '21218', 'M');
INSERT INTO `cms_region` VALUES ('21245', '曹县', '3', '21218', 'C');
INSERT INTO `cms_region` VALUES ('21271', '单县', '3', '21218', 'D');
INSERT INTO `cms_region` VALUES ('21292', '成武县', '3', '21218', 'C');
INSERT INTO `cms_region` VALUES ('21305', '巨野县', '3', '21218', 'J');
INSERT INTO `cms_region` VALUES ('21322', '郓城县', '3', '21218', 'Y');
INSERT INTO `cms_region` VALUES ('21344', '鄄城县', '3', '21218', 'J');
INSERT INTO `cms_region` VALUES ('21361', '定陶县', '3', '21218', 'D');
INSERT INTO `cms_region` VALUES ('21373', '东明县', '3', '21218', 'D');
INSERT INTO `cms_region` VALUES ('21387', '河南省', '1', '0', 'H');
INSERT INTO `cms_region` VALUES ('21388', '郑州市', '2', '21387', 'Z');
INSERT INTO `cms_region` VALUES ('21389', '市辖区', '3', '21388', 'S');
INSERT INTO `cms_region` VALUES ('21390', '中原区', '3', '21388', 'Z');
INSERT INTO `cms_region` VALUES ('21404', '二七区', '3', '21388', 'E');
INSERT INTO `cms_region` VALUES ('21420', '管城回族区', '3', '21388', 'G');
INSERT INTO `cms_region` VALUES ('21435', '金水区', '3', '21388', 'J');
INSERT INTO `cms_region` VALUES ('21453', '上街区', '3', '21388', 'S');
INSERT INTO `cms_region` VALUES ('21460', '惠济区', '3', '21388', 'H');
INSERT INTO `cms_region` VALUES ('21469', '中牟县', '3', '21388', 'Z');
INSERT INTO `cms_region` VALUES ('21487', '巩义市', '3', '21388', 'G');
INSERT INTO `cms_region` VALUES ('21508', '荥阳市', '3', '21388', 'X');
INSERT INTO `cms_region` VALUES ('21523', '新密市', '3', '21388', 'X');
INSERT INTO `cms_region` VALUES ('21542', '新郑市', '3', '21388', 'X');
INSERT INTO `cms_region` VALUES ('21558', '登封市', '3', '21388', 'D');
INSERT INTO `cms_region` VALUES ('21575', '开封市', '2', '21387', 'K');
INSERT INTO `cms_region` VALUES ('21576', '市辖区', '3', '21575', 'S');
INSERT INTO `cms_region` VALUES ('21577', '龙亭区', '3', '21575', 'L');
INSERT INTO `cms_region` VALUES ('21584', '顺河区', '3', '21575', 'S');
INSERT INTO `cms_region` VALUES ('21593', '鼓楼区', '3', '21575', 'G');
INSERT INTO `cms_region` VALUES ('21602', '禹王台区', '3', '21575', 'Y');
INSERT INTO `cms_region` VALUES ('21610', '金明区', '3', '21575', 'J');
INSERT INTO `cms_region` VALUES ('21618', '杞县', '3', '21575', 'Q');
INSERT INTO `cms_region` VALUES ('21640', '通许县', '3', '21575', 'T');
INSERT INTO `cms_region` VALUES ('21653', '尉氏县', '3', '21575', 'W');
INSERT INTO `cms_region` VALUES ('21671', '开封县', '3', '21575', 'K');
INSERT INTO `cms_region` VALUES ('21687', '兰考县', '3', '21575', 'L');
INSERT INTO `cms_region` VALUES ('21711', '洛阳市', '2', '21387', 'L');
INSERT INTO `cms_region` VALUES ('21712', '市辖区', '3', '21711', 'S');
INSERT INTO `cms_region` VALUES ('21713', '老城区', '3', '21711', 'L');
INSERT INTO `cms_region` VALUES ('21722', '西工区', '3', '21711', 'X');
INSERT INTO `cms_region` VALUES ('21733', '廛河回族区', '3', '21711', 'C');
INSERT INTO `cms_region` VALUES ('21742', '涧西区', '3', '21711', 'J');
INSERT INTO `cms_region` VALUES ('21758', '吉利区', '3', '21711', 'J');
INSERT INTO `cms_region` VALUES ('21761', '洛龙区', '3', '21711', 'L');
INSERT INTO `cms_region` VALUES ('21770', '孟津县', '3', '21711', 'M');
INSERT INTO `cms_region` VALUES ('21781', '新安县', '3', '21711', 'X');
INSERT INTO `cms_region` VALUES ('21794', '栾川县', '3', '21711', 'L');
INSERT INTO `cms_region` VALUES ('21809', '嵩县', '3', '21711', 'S');
INSERT INTO `cms_region` VALUES ('21829', '汝阳县', '3', '21711', 'R');
INSERT INTO `cms_region` VALUES ('21844', '宜阳县', '3', '21711', 'Y');
INSERT INTO `cms_region` VALUES ('21862', '洛宁县', '3', '21711', 'L');
INSERT INTO `cms_region` VALUES ('21881', '伊川县', '3', '21711', 'Y');
INSERT INTO `cms_region` VALUES ('21896', '偃师市', '3', '21711', 'Y');
INSERT INTO `cms_region` VALUES ('21913', '平顶山市', '2', '21387', 'P');
INSERT INTO `cms_region` VALUES ('21914', '市辖区', '3', '21913', 'S');
INSERT INTO `cms_region` VALUES ('21915', '新华区', '3', '21913', 'X');
INSERT INTO `cms_region` VALUES ('21928', '卫东区', '3', '21913', 'W');
INSERT INTO `cms_region` VALUES ('21940', '石龙区', '3', '21913', 'S');
INSERT INTO `cms_region` VALUES ('21945', '湛河区', '3', '21913', 'Z');
INSERT INTO `cms_region` VALUES ('21954', '宝丰县', '3', '21913', 'B');
INSERT INTO `cms_region` VALUES ('21968', '叶  县', '3', '21913', 'Y');
INSERT INTO `cms_region` VALUES ('21987', '鲁山县', '3', '21913', 'L');
INSERT INTO `cms_region` VALUES ('22009', '郏  县', '3', '21913', 'J');
INSERT INTO `cms_region` VALUES ('22024', '舞钢市', '3', '21913', 'W');
INSERT INTO `cms_region` VALUES ('22037', '汝州市', '3', '21913', 'R');
INSERT INTO `cms_region` VALUES ('22058', '安阳市', '2', '21387', 'A');
INSERT INTO `cms_region` VALUES ('22059', '市辖区', '3', '22058', 'S');
INSERT INTO `cms_region` VALUES ('22060', '文峰区', '3', '22058', 'W');
INSERT INTO `cms_region` VALUES ('22080', '北关区', '3', '22058', 'B');
INSERT INTO `cms_region` VALUES ('22090', '殷都区', '3', '22058', 'Y');
INSERT INTO `cms_region` VALUES ('22101', '龙安区', '3', '22058', 'L');
INSERT INTO `cms_region` VALUES ('22111', '安阳县', '3', '22058', 'A');
INSERT INTO `cms_region` VALUES ('22133', '汤阴县', '3', '22058', 'T');
INSERT INTO `cms_region` VALUES ('22144', '滑县', '3', '22058', 'H');
INSERT INTO `cms_region` VALUES ('22167', '内黄县', '3', '22058', 'N');
INSERT INTO `cms_region` VALUES ('22185', '林州市', '3', '22058', 'L');
INSERT INTO `cms_region` VALUES ('22206', '鹤壁市', '2', '21387', 'H');
INSERT INTO `cms_region` VALUES ('22207', '市辖区', '3', '22206', 'S');
INSERT INTO `cms_region` VALUES ('22208', '鹤山区', '3', '22206', 'H');
INSERT INTO `cms_region` VALUES ('22216', '山城区', '3', '22206', 'S');
INSERT INTO `cms_region` VALUES ('22224', '淇滨区', '3', '22206', 'Q');
INSERT INTO `cms_region` VALUES ('22232', '浚县', '3', '22206', 'J');
INSERT INTO `cms_region` VALUES ('22243', '淇县', '3', '22206', 'Q');
INSERT INTO `cms_region` VALUES ('22251', '新乡市', '2', '21387', 'X');
INSERT INTO `cms_region` VALUES ('22252', '市辖区', '3', '22251', 'S');
INSERT INTO `cms_region` VALUES ('22253', '红旗区', '3', '22251', 'H');
INSERT INTO `cms_region` VALUES ('22264', '卫滨区', '3', '22251', 'W');
INSERT INTO `cms_region` VALUES ('22273', '凤泉区', '3', '22251', 'F');
INSERT INTO `cms_region` VALUES ('22279', '牧野区', '3', '22251', 'M');
INSERT INTO `cms_region` VALUES ('22290', '新乡县', '3', '22251', 'X');
INSERT INTO `cms_region` VALUES ('22299', '获嘉县', '3', '22251', 'H');
INSERT INTO `cms_region` VALUES ('22312', '原阳县', '3', '22251', 'Y');
INSERT INTO `cms_region` VALUES ('22330', '延津县', '3', '22251', 'Y');
INSERT INTO `cms_region` VALUES ('22347', '封丘县', '3', '22251', 'F');
INSERT INTO `cms_region` VALUES ('22367', '长垣县', '3', '22251', 'C');
INSERT INTO `cms_region` VALUES ('22386', '卫辉市', '3', '22251', 'W');
INSERT INTO `cms_region` VALUES ('22400', '辉县市', '3', '22251', 'H');
INSERT INTO `cms_region` VALUES ('22423', '焦作市', '2', '21387', 'J');
INSERT INTO `cms_region` VALUES ('22424', '市辖区', '3', '22423', 'S');
INSERT INTO `cms_region` VALUES ('22425', '解放区', '3', '22423', 'J');
INSERT INTO `cms_region` VALUES ('22435', '中站区', '3', '22423', 'Z');
INSERT INTO `cms_region` VALUES ('22446', '马村区', '3', '22423', 'M');
INSERT INTO `cms_region` VALUES ('22454', '山阳区', '3', '22423', 'S');
INSERT INTO `cms_region` VALUES ('22465', '修武县', '3', '22423', 'X');
INSERT INTO `cms_region` VALUES ('22475', '博爱县', '3', '22423', 'B');
INSERT INTO `cms_region` VALUES ('22487', '武陟县', '3', '22423', 'W');
INSERT INTO `cms_region` VALUES ('22503', '温县', '3', '22423', 'W');
INSERT INTO `cms_region` VALUES ('22515', '济源市', '3', '22423', 'J');
INSERT INTO `cms_region` VALUES ('22532', '沁阳市', '3', '22423', 'Q');
INSERT INTO `cms_region` VALUES ('22546', '孟州市', '3', '22423', 'M');
INSERT INTO `cms_region` VALUES ('22558', '濮阳市', '2', '21387', 'P');
INSERT INTO `cms_region` VALUES ('22559', '市辖区', '3', '22558', 'S');
INSERT INTO `cms_region` VALUES ('22560', '华龙区', '3', '22558', 'H');
INSERT INTO `cms_region` VALUES ('22578', '清丰县', '3', '22558', 'Q');
INSERT INTO `cms_region` VALUES ('22596', '南乐县', '3', '22558', 'N');
INSERT INTO `cms_region` VALUES ('22609', '范县', '3', '22558', 'F');
INSERT INTO `cms_region` VALUES ('22622', '台前县', '3', '22558', 'T');
INSERT INTO `cms_region` VALUES ('22632', '濮阳县', '3', '22558', 'P');
INSERT INTO `cms_region` VALUES ('22655', '许昌市', '2', '21387', 'X');
INSERT INTO `cms_region` VALUES ('22656', '市辖区', '3', '22655', 'S');
INSERT INTO `cms_region` VALUES ('22657', '魏都区', '3', '22655', 'W');
INSERT INTO `cms_region` VALUES ('22671', '许昌县', '3', '22655', 'X');
INSERT INTO `cms_region` VALUES ('22688', '鄢陵县', '3', '22655', 'Y');
INSERT INTO `cms_region` VALUES ('22701', '襄城县', '3', '22655', 'X');
INSERT INTO `cms_region` VALUES ('22718', '禹州市', '3', '22655', 'Y');
INSERT INTO `cms_region` VALUES ('22745', '长葛市', '3', '22655', 'C');
INSERT INTO `cms_region` VALUES ('22762', '漯河市', '2', '21387', 'L');
INSERT INTO `cms_region` VALUES ('22763', '市辖区', '3', '22762', 'S');
INSERT INTO `cms_region` VALUES ('22764', '源汇区', '3', '22762', 'Y');
INSERT INTO `cms_region` VALUES ('22773', '郾城区', '3', '22762', 'Y');
INSERT INTO `cms_region` VALUES ('22783', '召陵区', '3', '22762', 'Z');
INSERT INTO `cms_region` VALUES ('22793', '舞阳县', '3', '22762', 'W');
INSERT INTO `cms_region` VALUES ('22808', '临颖县', '3', '22762', 'L');
INSERT INTO `cms_region` VALUES ('22824', '三门峡市', '2', '21387', 'S');
INSERT INTO `cms_region` VALUES ('22825', '市辖区', '3', '22824', 'S');
INSERT INTO `cms_region` VALUES ('22826', '湖滨区', '3', '22824', 'H');
INSERT INTO `cms_region` VALUES ('22838', '渑池县', '3', '22824', 'M');
INSERT INTO `cms_region` VALUES ('22851', '陕县', '3', '22824', 'S');
INSERT INTO `cms_region` VALUES ('22865', '卢氏县', '3', '22824', 'L');
INSERT INTO `cms_region` VALUES ('22885', '义马市', '3', '22824', 'Y');
INSERT INTO `cms_region` VALUES ('22893', '灵宝市', '3', '22824', 'L');
INSERT INTO `cms_region` VALUES ('22910', '南阳市', '2', '21387', 'N');
INSERT INTO `cms_region` VALUES ('22911', '市辖区', '3', '22910', 'S');
INSERT INTO `cms_region` VALUES ('22912', '宛城区', '3', '22910', 'W');
INSERT INTO `cms_region` VALUES ('22930', '卧龙区', '3', '22910', 'W');
INSERT INTO `cms_region` VALUES ('22951', '南召县', '3', '22910', 'N');
INSERT INTO `cms_region` VALUES ('22973', '方城县', '3', '22910', 'F');
INSERT INTO `cms_region` VALUES ('22992', '西峡县', '3', '22910', 'X');
INSERT INTO `cms_region` VALUES ('23013', '镇平县', '3', '22910', 'Z');
INSERT INTO `cms_region` VALUES ('23036', '内乡县', '3', '22910', 'N');
INSERT INTO `cms_region` VALUES ('23053', '淅川县', '3', '22910', 'X');
INSERT INTO `cms_region` VALUES ('23071', '社旗县', '3', '22910', 'S');
INSERT INTO `cms_region` VALUES ('23087', '唐河县', '3', '22910', 'T');
INSERT INTO `cms_region` VALUES ('23108', '新野县', '3', '22910', 'X');
INSERT INTO `cms_region` VALUES ('23123', '桐柏县', '3', '22910', 'T');
INSERT INTO `cms_region` VALUES ('23140', '邓州市', '3', '22910', 'D');
INSERT INTO `cms_region` VALUES ('23170', '商丘市', '2', '21387', 'S');
INSERT INTO `cms_region` VALUES ('23171', '市辖区', '3', '23170', 'S');
INSERT INTO `cms_region` VALUES ('23172', '梁园区', '3', '23170', 'L');
INSERT INTO `cms_region` VALUES ('23192', '睢阳区', '3', '23170', 'S');
INSERT INTO `cms_region` VALUES ('23211', '民权县', '3', '23170', 'M');
INSERT INTO `cms_region` VALUES ('23232', '睢县', '3', '23170', 'S');
INSERT INTO `cms_region` VALUES ('23253', '宁陵县', '3', '23170', 'N');
INSERT INTO `cms_region` VALUES ('23268', '柘城县', '3', '23170', 'Z');
INSERT INTO `cms_region` VALUES ('23290', '虞城县', '3', '23170', 'Y');
INSERT INTO `cms_region` VALUES ('23317', '夏邑县', '3', '23170', 'X');
INSERT INTO `cms_region` VALUES ('23342', '永城市', '3', '23170', 'Y');
INSERT INTO `cms_region` VALUES ('23372', '信阳市', '2', '21387', 'X');
INSERT INTO `cms_region` VALUES ('23373', '市辖区', '3', '23372', 'S');
INSERT INTO `cms_region` VALUES ('23374', '浉河区', '3', '23372', 'S');
INSERT INTO `cms_region` VALUES ('23393', '平桥区', '3', '23372', 'P');
INSERT INTO `cms_region` VALUES ('23414', '罗山县', '3', '23372', 'L');
INSERT INTO `cms_region` VALUES ('23435', '光山县', '3', '23372', 'G');
INSERT INTO `cms_region` VALUES ('23455', '新县', '3', '23372', 'X');
INSERT INTO `cms_region` VALUES ('23471', '商城县', '3', '23372', 'S');
INSERT INTO `cms_region` VALUES ('23492', '固始县', '3', '23372', 'G');
INSERT INTO `cms_region` VALUES ('23525', '潢川县', '3', '23372', 'H');
INSERT INTO `cms_region` VALUES ('23549', '淮滨县', '3', '23372', 'H');
INSERT INTO `cms_region` VALUES ('23567', '息县', '3', '23372', 'X');
INSERT INTO `cms_region` VALUES ('23589', '周口市', '2', '21387', 'Z');
INSERT INTO `cms_region` VALUES ('23590', '市辖区', '3', '23589', 'S');
INSERT INTO `cms_region` VALUES ('23591', '川汇区', '3', '23589', 'C');
INSERT INTO `cms_region` VALUES ('23604', '扶沟县', '3', '23589', 'F');
INSERT INTO `cms_region` VALUES ('23621', '西华县', '3', '23589', 'X');
INSERT INTO `cms_region` VALUES ('23647', '商水县', '3', '23589', 'S');
INSERT INTO `cms_region` VALUES ('23672', '沈丘县', '3', '23589', 'S');
INSERT INTO `cms_region` VALUES ('23695', '郸城县', '3', '23589', 'D');
INSERT INTO `cms_region` VALUES ('23716', '淮阳县', '3', '23589', 'H');
INSERT INTO `cms_region` VALUES ('23736', '太康县', '3', '23589', 'T');
INSERT INTO `cms_region` VALUES ('23766', '鹿邑县', '3', '23589', 'L');
INSERT INTO `cms_region` VALUES ('23796', '项城市', '3', '23589', 'X');
INSERT INTO `cms_region` VALUES ('23818', '驻马店市', '2', '21387', 'Z');
INSERT INTO `cms_region` VALUES ('23819', '市辖区', '3', '23818', 'S');
INSERT INTO `cms_region` VALUES ('23820', '驿城区', '3', '23818', 'Y');
INSERT INTO `cms_region` VALUES ('23840', '西平县', '3', '23818', 'X');
INSERT INTO `cms_region` VALUES ('23861', '上蔡县', '3', '23818', 'S');
INSERT INTO `cms_region` VALUES ('23886', '平舆县', '3', '23818', 'P');
INSERT INTO `cms_region` VALUES ('23905', '正阳县', '3', '23818', 'Z');
INSERT INTO `cms_region` VALUES ('23926', '确山县', '3', '23818', 'Q');
INSERT INTO `cms_region` VALUES ('23940', '泌阳县', '3', '23818', 'M');
INSERT INTO `cms_region` VALUES ('23965', '汝南县', '3', '23818', 'R');
INSERT INTO `cms_region` VALUES ('23983', '遂平县', '3', '23818', 'S');
INSERT INTO `cms_region` VALUES ('23999', '新蔡县', '3', '23818', 'X');
INSERT INTO `cms_region` VALUES ('24022', '湖北省', '1', '0', 'H');
INSERT INTO `cms_region` VALUES ('24023', '武汉市', '2', '24022', 'W');
INSERT INTO `cms_region` VALUES ('24024', '市辖区', '3', '24023', 'S');
INSERT INTO `cms_region` VALUES ('24025', '江岸区', '3', '24023', 'J');
INSERT INTO `cms_region` VALUES ('24043', '江汉区', '3', '24023', 'J');
INSERT INTO `cms_region` VALUES ('24057', '硚口区', '3', '24023', 'Q');
INSERT INTO `cms_region` VALUES ('24069', '汉阳区', '3', '24023', 'H');
INSERT INTO `cms_region` VALUES ('24082', '武昌区', '3', '24023', 'W');
INSERT INTO `cms_region` VALUES ('24098', '青山区', '3', '24023', 'Q');
INSERT INTO `cms_region` VALUES ('24111', '洪山区', '3', '24023', 'H');
INSERT INTO `cms_region` VALUES ('24129', '东西湖区', '3', '24023', 'D');
INSERT INTO `cms_region` VALUES ('24142', '汉南区', '3', '24023', 'H');
INSERT INTO `cms_region` VALUES ('24150', '蔡甸区', '3', '24023', 'C');
INSERT INTO `cms_region` VALUES ('24165', '江夏区', '3', '24023', 'J');
INSERT INTO `cms_region` VALUES ('24185', '黄陂区', '3', '24023', 'H');
INSERT INTO `cms_region` VALUES ('24205', '武汉市新洲区', '3', '24023', 'W');
INSERT INTO `cms_region` VALUES ('24224', '黄石市', '2', '24022', 'H');
INSERT INTO `cms_region` VALUES ('24225', '市辖区', '3', '24224', 'S');
INSERT INTO `cms_region` VALUES ('24226', '黄石港区', '3', '24224', 'H');
INSERT INTO `cms_region` VALUES ('24233', '西塞山区', '3', '24224', 'X');
INSERT INTO `cms_region` VALUES ('24242', '下陆区', '3', '24224', 'X');
INSERT INTO `cms_region` VALUES ('24247', '铁山区', '3', '24224', 'T');
INSERT INTO `cms_region` VALUES ('24250', '阳新县', '3', '24224', 'Y');
INSERT INTO `cms_region` VALUES ('24273', '大冶市', '3', '24224', 'D');
INSERT INTO `cms_region` VALUES ('24291', '十堰市', '2', '24022', 'S');
INSERT INTO `cms_region` VALUES ('24292', '市辖区', '3', '24291', 'S');
INSERT INTO `cms_region` VALUES ('24293', '茅箭区', '3', '24291', 'M');
INSERT INTO `cms_region` VALUES ('24302', '张湾区', '3', '24291', 'Z');
INSERT INTO `cms_region` VALUES ('24314', '郧县', '3', '24291', 'Y');
INSERT INTO `cms_region` VALUES ('24335', '郧西县', '3', '24291', 'Y');
INSERT INTO `cms_region` VALUES ('24354', '竹山县', '3', '24291', 'Z');
INSERT INTO `cms_region` VALUES ('24374', '竹溪县', '3', '24291', 'Z');
INSERT INTO `cms_region` VALUES ('24405', '房县', '3', '24291', 'F');
INSERT INTO `cms_region` VALUES ('24435', '丹江口市', '3', '24291', 'D');
INSERT INTO `cms_region` VALUES ('24453', '宜昌市', '2', '24022', 'Y');
INSERT INTO `cms_region` VALUES ('24454', '市辖区', '3', '24453', 'S');
INSERT INTO `cms_region` VALUES ('24455', '西陵区', '3', '24453', 'X');
INSERT INTO `cms_region` VALUES ('24465', '伍家岗区', '3', '24453', 'W');
INSERT INTO `cms_region` VALUES ('24471', '点军区', '3', '24453', 'D');
INSERT INTO `cms_region` VALUES ('24477', '猇亭区', '3', '24453', 'X');
INSERT INTO `cms_region` VALUES ('24481', '夷陵区', '3', '24453', 'Y');
INSERT INTO `cms_region` VALUES ('24495', '远安县', '3', '24453', 'Y');
INSERT INTO `cms_region` VALUES ('24503', '兴山县', '3', '24453', 'X');
INSERT INTO `cms_region` VALUES ('24512', '秭归县', '3', '24453', 'Z');
INSERT INTO `cms_region` VALUES ('24525', '长阳县', '3', '24453', 'C');
INSERT INTO `cms_region` VALUES ('24537', '五峰县', '3', '24453', 'W');
INSERT INTO `cms_region` VALUES ('24546', '宜都市', '3', '24453', 'Y');
INSERT INTO `cms_region` VALUES ('24559', '当阳市', '3', '24453', 'D');
INSERT INTO `cms_region` VALUES ('24570', '枝江市', '3', '24453', 'Z');
INSERT INTO `cms_region` VALUES ('24580', '襄樊市', '2', '24022', 'X');
INSERT INTO `cms_region` VALUES ('24581', '市辖区', '3', '24580', 'S');
INSERT INTO `cms_region` VALUES ('24582', '襄城区', '3', '24580', 'X');
INSERT INTO `cms_region` VALUES ('24591', '樊城区', '3', '24580', 'F');
INSERT INTO `cms_region` VALUES ('24608', '襄阳区', '3', '24580', 'X');
INSERT INTO `cms_region` VALUES ('24623', '南漳县', '3', '24580', 'N');
INSERT INTO `cms_region` VALUES ('24635', '谷城县', '3', '24580', 'G');
INSERT INTO `cms_region` VALUES ('24647', '保康县', '3', '24580', 'B');
INSERT INTO `cms_region` VALUES ('24659', '老河口市', '3', '24580', 'L');
INSERT INTO `cms_region` VALUES ('24674', '枣阳市', '3', '24580', 'Z');
INSERT INTO `cms_region` VALUES ('24692', '宜城市', '3', '24580', 'Y');
INSERT INTO `cms_region` VALUES ('24706', '鄂州市', '2', '24022', 'E');
INSERT INTO `cms_region` VALUES ('24707', '市辖区', '3', '24706', 'S');
INSERT INTO `cms_region` VALUES ('24708', '粱子湖区', '3', '24706', 'L');
INSERT INTO `cms_region` VALUES ('24714', '华容区', '3', '24706', 'H');
INSERT INTO `cms_region` VALUES ('24722', '鄂城区', '3', '24706', 'E');
INSERT INTO `cms_region` VALUES ('24737', '荆门市', '2', '24022', 'J');
INSERT INTO `cms_region` VALUES ('24738', '市辖区', '3', '24737', 'S');
INSERT INTO `cms_region` VALUES ('24739', '东宝区', '3', '24737', 'D');
INSERT INTO `cms_region` VALUES ('24749', '掇刀区', '3', '24737', 'D');
INSERT INTO `cms_region` VALUES ('24755', '京山县', '3', '24737', 'J');
INSERT INTO `cms_region` VALUES ('24778', '沙洋县', '3', '24737', 'S');
INSERT INTO `cms_region` VALUES ('24794', '钟祥市', '3', '24737', 'Z');
INSERT INTO `cms_region` VALUES ('24816', '孝感市', '2', '24022', 'X');
INSERT INTO `cms_region` VALUES ('24817', '市辖区', '3', '24816', 'S');
INSERT INTO `cms_region` VALUES ('24818', '孝南区', '3', '24816', 'X');
INSERT INTO `cms_region` VALUES ('24838', '孝昌县', '3', '24816', 'X');
INSERT INTO `cms_region` VALUES ('24853', '大悟县', '3', '24816', 'D');
INSERT INTO `cms_region` VALUES ('24871', '云梦县', '3', '24816', 'Y');
INSERT INTO `cms_region` VALUES ('24885', '应城市', '3', '24816', 'Y');
INSERT INTO `cms_region` VALUES ('24903', '安陆市', '3', '24816', 'A');
INSERT INTO `cms_region` VALUES ('24920', '汉川市', '3', '24816', 'H');
INSERT INTO `cms_region` VALUES ('24949', '荆州市', '2', '24022', 'J');
INSERT INTO `cms_region` VALUES ('24950', '市辖区', '3', '24949', 'S');
INSERT INTO `cms_region` VALUES ('24951', '沙市区', '3', '24949', 'S');
INSERT INTO `cms_region` VALUES ('24965', '荆州区', '3', '24949', 'J');
INSERT INTO `cms_region` VALUES ('24978', '公安县', '3', '24949', 'G');
INSERT INTO `cms_region` VALUES ('24995', '监利县', '3', '24949', 'J');
INSERT INTO `cms_region` VALUES ('25019', '江陵县', '3', '24949', 'J');
INSERT INTO `cms_region` VALUES ('25032', '石首市', '3', '24949', 'S');
INSERT INTO `cms_region` VALUES ('25048', '洪湖市', '3', '24949', 'H');
INSERT INTO `cms_region` VALUES ('25069', '松滋市', '3', '24949', 'S');
INSERT INTO `cms_region` VALUES ('25086', '黄冈市', '2', '24022', 'H');
INSERT INTO `cms_region` VALUES ('25087', '市辖区', '3', '25086', 'S');
INSERT INTO `cms_region` VALUES ('25088', '黄州区', '3', '25086', 'H');
INSERT INTO `cms_region` VALUES ('25099', '团风县', '3', '25086', 'T');
INSERT INTO `cms_region` VALUES ('25112', '红安县', '3', '25086', 'H');
INSERT INTO `cms_region` VALUES ('25126', '罗田县', '3', '25086', 'L');
INSERT INTO `cms_region` VALUES ('25143', '英山县', '3', '25086', 'Y');
INSERT INTO `cms_region` VALUES ('25158', '浠水县', '3', '25086', 'X');
INSERT INTO `cms_region` VALUES ('25175', '蕲春县', '3', '25086', 'Q');
INSERT INTO `cms_region` VALUES ('25192', '黄梅县', '3', '25086', 'H');
INSERT INTO `cms_region` VALUES ('25211', '麻城市', '3', '25086', 'M');
INSERT INTO `cms_region` VALUES ('25235', '武穴市', '3', '25086', 'W');
INSERT INTO `cms_region` VALUES ('25249', '咸宁市', '2', '24022', 'X');
INSERT INTO `cms_region` VALUES ('25250', '市辖区', '3', '25249', 'S');
INSERT INTO `cms_region` VALUES ('25251', '咸安区', '3', '25249', 'X');
INSERT INTO `cms_region` VALUES ('25266', '嘉鱼县', '3', '25249', 'J');
INSERT INTO `cms_region` VALUES ('25276', '通城县', '3', '25249', 'T');
INSERT INTO `cms_region` VALUES ('25290', '崇阳县', '3', '25249', 'C');
INSERT INTO `cms_region` VALUES ('25303', '通山县', '3', '25249', 'T');
INSERT INTO `cms_region` VALUES ('25317', '赤壁市', '3', '25249', 'C');
INSERT INTO `cms_region` VALUES ('25335', '随州市', '2', '24022', 'S');
INSERT INTO `cms_region` VALUES ('25336', '市辖区', '3', '25335', 'S');
INSERT INTO `cms_region` VALUES ('25337', '曾都区', '3', '25335', 'Z');
INSERT INTO `cms_region` VALUES ('25367', '广水市', '3', '25335', 'G');
INSERT INTO `cms_region` VALUES ('25388', '恩施州', '2', '24022', 'E');
INSERT INTO `cms_region` VALUES ('25389', '恩施市', '3', '25388', 'E');
INSERT INTO `cms_region` VALUES ('25406', '利川市', '3', '25388', 'L');
INSERT INTO `cms_region` VALUES ('25422', '建始县', '3', '25388', 'J');
INSERT INTO `cms_region` VALUES ('25433', '巴东县', '3', '25388', 'B');
INSERT INTO `cms_region` VALUES ('25446', '宣恩县', '3', '25388', 'X');
INSERT INTO `cms_region` VALUES ('25456', '咸丰县', '3', '25388', 'X');
INSERT INTO `cms_region` VALUES ('25467', '来凤县', '3', '25388', 'L');
INSERT INTO `cms_region` VALUES ('25476', '鹤峰县', '3', '25388', 'H');
INSERT INTO `cms_region` VALUES ('25487', '省直辖行政单位', '2', '24022', 'S');
INSERT INTO `cms_region` VALUES ('25488', '仙桃市', '3', '25487', 'X');
INSERT INTO `cms_region` VALUES ('25516', '潜江市', '3', '25487', 'Q');
INSERT INTO `cms_region` VALUES ('25541', '天门市', '3', '25487', 'T');
INSERT INTO `cms_region` VALUES ('25570', '神农架林区', '3', '25487', 'S');
INSERT INTO `cms_region` VALUES ('25579', '湖南省', '1', '0', 'H');
INSERT INTO `cms_region` VALUES ('25580', '长沙市', '2', '25579', 'C');
INSERT INTO `cms_region` VALUES ('25581', '市辖区', '3', '25580', 'S');
INSERT INTO `cms_region` VALUES ('25582', '芙蓉区', '3', '25580', 'F');
INSERT INTO `cms_region` VALUES ('25596', '天心区', '3', '25580', 'T');
INSERT INTO `cms_region` VALUES ('25607', '岳麓区', '3', '25580', 'Y');
INSERT INTO `cms_region` VALUES ('25620', '开福区', '3', '25580', 'K');
INSERT INTO `cms_region` VALUES ('25634', '雨花区', '3', '25580', 'Y');
INSERT INTO `cms_region` VALUES ('25645', '长沙县', '3', '25580', 'C');
INSERT INTO `cms_region` VALUES ('25666', '望城县', '3', '25580', 'W');
INSERT INTO `cms_region` VALUES ('25686', '宁乡县', '3', '25580', 'N');
INSERT INTO `cms_region` VALUES ('25720', '浏阳市', '3', '25580', 'L');
INSERT INTO `cms_region` VALUES ('25758', '株洲市', '2', '25579', 'Z');
INSERT INTO `cms_region` VALUES ('25759', '市辖区', '3', '25758', 'S');
INSERT INTO `cms_region` VALUES ('25760', '荷塘区', '3', '25758', 'H');
INSERT INTO `cms_region` VALUES ('25768', '芦淞区', '3', '25758', 'L');
INSERT INTO `cms_region` VALUES ('25777', '石峰区', '3', '25758', 'S');
INSERT INTO `cms_region` VALUES ('25785', '天元区', '3', '25758', 'T');
INSERT INTO `cms_region` VALUES ('25791', '株洲县', '3', '25758', 'Z');
INSERT INTO `cms_region` VALUES ('25810', '攸县', '3', '25758', 'Y');
INSERT INTO `cms_region` VALUES ('25836', '茶陵县', '3', '25758', 'C');
INSERT INTO `cms_region` VALUES ('25863', '炎陵县', '3', '25758', 'Y');
INSERT INTO `cms_region` VALUES ('25881', '醴陵市', '3', '25758', 'L');
INSERT INTO `cms_region` VALUES ('25912', '湘潭市', '2', '25579', 'X');
INSERT INTO `cms_region` VALUES ('25913', '市辖区', '3', '25912', 'S');
INSERT INTO `cms_region` VALUES ('25914', '雨湖区', '3', '25912', 'Y');
INSERT INTO `cms_region` VALUES ('25929', '岳塘区', '3', '25912', 'Y');
INSERT INTO `cms_region` VALUES ('25947', '湘潭县', '3', '25912', 'X');
INSERT INTO `cms_region` VALUES ('25970', '湘乡市', '3', '25912', 'X');
INSERT INTO `cms_region` VALUES ('25993', '韶山市', '3', '25912', 'S');
INSERT INTO `cms_region` VALUES ('26001', '衡阳市', '2', '25579', 'H');
INSERT INTO `cms_region` VALUES ('26002', '市辖区', '3', '26001', 'S');
INSERT INTO `cms_region` VALUES ('26003', '珠晖区', '3', '26001', 'Z');
INSERT INTO `cms_region` VALUES ('26019', '雁峰区', '3', '26001', 'Y');
INSERT INTO `cms_region` VALUES ('26028', '石鼓区', '3', '26001', 'S');
INSERT INTO `cms_region` VALUES ('26037', '蒸湘区', '3', '26001', 'Z');
INSERT INTO `cms_region` VALUES ('26045', '南岳区', '3', '26001', 'N');
INSERT INTO `cms_region` VALUES ('26051', '衡阳县', '3', '26001', 'H');
INSERT INTO `cms_region` VALUES ('26080', '衡南县', '3', '26001', 'H');
INSERT INTO `cms_region` VALUES ('26112', '衡山县', '3', '26001', 'H');
INSERT INTO `cms_region` VALUES ('26130', '衡东县', '3', '26001', 'H');
INSERT INTO `cms_region` VALUES ('26155', '祁东县', '3', '26001', 'Q');
INSERT INTO `cms_region` VALUES ('26179', '耒阳市', '3', '26001', 'L');
INSERT INTO `cms_region` VALUES ('26215', '常宁市', '3', '26001', 'C');
INSERT INTO `cms_region` VALUES ('26242', '邵阳市', '2', '25579', 'S');
INSERT INTO `cms_region` VALUES ('26243', '市辖区', '3', '26242', 'S');
INSERT INTO `cms_region` VALUES ('26244', '双清区', '3', '26242', 'S');
INSERT INTO `cms_region` VALUES ('26257', '大祥区', '3', '26242', 'D');
INSERT INTO `cms_region` VALUES ('26272', '北塔区', '3', '26242', 'B');
INSERT INTO `cms_region` VALUES ('26279', '邵东县', '3', '26242', 'S');
INSERT INTO `cms_region` VALUES ('26306', '新邵县', '3', '26242', 'X');
INSERT INTO `cms_region` VALUES ('26322', '邵阳县', '3', '26242', 'S');
INSERT INTO `cms_region` VALUES ('26348', '隆回县', '3', '26242', 'L');
INSERT INTO `cms_region` VALUES ('26375', '洞口县', '3', '26242', 'D');
INSERT INTO `cms_region` VALUES ('26399', '绥宁县', '3', '26242', 'S');
INSERT INTO `cms_region` VALUES ('26425', '新宁县', '3', '26242', 'X');
INSERT INTO `cms_region` VALUES ('26444', '城步县', '3', '26242', 'C');
INSERT INTO `cms_region` VALUES ('26465', '武冈市', '3', '26242', 'W');
INSERT INTO `cms_region` VALUES ('26485', '岳阳市', '2', '25579', 'Y');
INSERT INTO `cms_region` VALUES ('26486', '市辖区', '3', '26485', 'S');
INSERT INTO `cms_region` VALUES ('26487', '岳阳楼区', '3', '26485', 'Y');
INSERT INTO `cms_region` VALUES ('26511', '云溪区', '3', '26485', 'Y');
INSERT INTO `cms_region` VALUES ('26521', '君山区', '3', '26485', 'J');
INSERT INTO `cms_region` VALUES ('26529', '岳阳县', '3', '26485', 'Y');
INSERT INTO `cms_region` VALUES ('26551', '华容县', '3', '26485', 'H');
INSERT INTO `cms_region` VALUES ('26572', '湘阴县', '3', '26485', 'X');
INSERT INTO `cms_region` VALUES ('26592', '平江县', '3', '26485', 'P');
INSERT INTO `cms_region` VALUES ('26620', '汩罗市', '3', '26485', 'G');
INSERT INTO `cms_region` VALUES ('26657', '临湘市', '3', '26485', 'L');
INSERT INTO `cms_region` VALUES ('26683', '常德市', '2', '25579', 'C');
INSERT INTO `cms_region` VALUES ('26684', '市辖区', '3', '26683', 'S');
INSERT INTO `cms_region` VALUES ('26685', '武陵区', '3', '26683', 'W');
INSERT INTO `cms_region` VALUES ('26702', '鼎城区', '3', '26683', 'D');
INSERT INTO `cms_region` VALUES ('26741', '安乡县', '3', '26683', 'A');
INSERT INTO `cms_region` VALUES ('26762', '汉寿县', '3', '26683', 'H');
INSERT INTO `cms_region` VALUES ('26793', '澧县', '3', '26683', 'L');
INSERT INTO `cms_region` VALUES ('26826', '临澧县', '3', '26683', 'L');
INSERT INTO `cms_region` VALUES ('26844', '桃源县', '3', '26683', 'T');
INSERT INTO `cms_region` VALUES ('26885', '石门县', '3', '26683', 'S');
INSERT INTO `cms_region` VALUES ('26912', '津市市', '3', '26683', 'J');
INSERT INTO `cms_region` VALUES ('26925', '张家界市', '2', '25579', 'Z');
INSERT INTO `cms_region` VALUES ('26926', '市辖区', '3', '26925', 'S');
INSERT INTO `cms_region` VALUES ('26927', '永定区', '3', '26925', 'Y');
INSERT INTO `cms_region` VALUES ('26959', '武陵源区', '3', '26925', 'W');
INSERT INTO `cms_region` VALUES ('26966', '慈利县', '3', '26925', 'C');
INSERT INTO `cms_region` VALUES ('26998', '桑植县', '3', '26925', 'S');
INSERT INTO `cms_region` VALUES ('27038', '益阳市', '2', '25579', 'Y');
INSERT INTO `cms_region` VALUES ('27039', '市辖区', '3', '27038', 'S');
INSERT INTO `cms_region` VALUES ('27040', '资阳区', '3', '27038', 'Z');
INSERT INTO `cms_region` VALUES ('27049', '赫山区', '3', '27038', 'H');
INSERT INTO `cms_region` VALUES ('27069', '南县', '3', '27038', 'N');
INSERT INTO `cms_region` VALUES ('27087', '桃江县', '3', '27038', 'T');
INSERT INTO `cms_region` VALUES ('27106', '安化县', '3', '27038', 'A');
INSERT INTO `cms_region` VALUES ('27130', '沅江市', '3', '27038', 'Y');
INSERT INTO `cms_region` VALUES ('27147', '郴州市', '2', '25579', 'C');
INSERT INTO `cms_region` VALUES ('27148', '市辖区', '3', '27147', 'S');
INSERT INTO `cms_region` VALUES ('27149', '北湖区', '3', '27147', 'B');
INSERT INTO `cms_region` VALUES ('27168', '苏仙区', '3', '27147', 'S');
INSERT INTO `cms_region` VALUES ('27188', '桂阳县', '3', '27147', 'G');
INSERT INTO `cms_region` VALUES ('27228', '宜章县', '3', '27147', 'Y');
INSERT INTO `cms_region` VALUES ('27256', '永兴县', '3', '27147', 'Y');
INSERT INTO `cms_region` VALUES ('27282', '嘉禾县', '3', '27147', 'J');
INSERT INTO `cms_region` VALUES ('27300', '临武县', '3', '27147', 'L');
INSERT INTO `cms_region` VALUES ('27323', '汝城县', '3', '27147', 'R');
INSERT INTO `cms_region` VALUES ('27347', '桂东县', '3', '27147', 'G');
INSERT INTO `cms_region` VALUES ('27367', '安仁县', '3', '27147', 'A');
INSERT INTO `cms_region` VALUES ('27389', '资兴市', '3', '27147', 'Z');
INSERT INTO `cms_region` VALUES ('27418', '永州市', '2', '25579', 'Y');
INSERT INTO `cms_region` VALUES ('27419', '市辖区', '3', '27418', 'S');
INSERT INTO `cms_region` VALUES ('27420', '零陵区', '3', '27418', 'L');
INSERT INTO `cms_region` VALUES ('27437', '冷水滩区', '3', '27418', 'L');
INSERT INTO `cms_region` VALUES ('27459', '祁阳县', '3', '27418', 'Q');
INSERT INTO `cms_region` VALUES ('27492', '东安县', '3', '27418', 'D');
INSERT INTO `cms_region` VALUES ('27511', '双牌县', '3', '27418', 'S');
INSERT INTO `cms_region` VALUES ('27527', '道县', '3', '27418', 'D');
INSERT INTO `cms_region` VALUES ('27554', '江永县', '3', '27418', 'J');
INSERT INTO `cms_region` VALUES ('27567', '宁远县', '3', '27418', 'N');
INSERT INTO `cms_region` VALUES ('27585', '蓝山县', '3', '27418', 'L');
INSERT INTO `cms_region` VALUES ('27606', '新田县', '3', '27418', 'X');
INSERT INTO `cms_region` VALUES ('27626', '江华县', '3', '27418', 'J');
INSERT INTO `cms_region` VALUES ('27650', '怀化市', '2', '25579', 'H');
INSERT INTO `cms_region` VALUES ('27651', '市辖区', '3', '27650', 'S');
INSERT INTO `cms_region` VALUES ('27652', '鹤城区', '3', '27650', 'H');
INSERT INTO `cms_region` VALUES ('27667', '中方县', '3', '27650', 'Z');
INSERT INTO `cms_region` VALUES ('27690', '沅陵县', '3', '27650', 'Y');
INSERT INTO `cms_region` VALUES ('27714', '辰溪县', '3', '27650', 'C');
INSERT INTO `cms_region` VALUES ('27745', '溆浦县', '3', '27650', 'X');
INSERT INTO `cms_region` VALUES ('27789', '会同县', '3', '27650', 'H');
INSERT INTO `cms_region` VALUES ('27815', '麻阳县', '3', '27650', 'M');
INSERT INTO `cms_region` VALUES ('27839', '新晃县', '3', '27650', 'X');
INSERT INTO `cms_region` VALUES ('27863', '芷江县', '3', '27650', 'Z');
INSERT INTO `cms_region` VALUES ('27892', '靖州苗族侗族县', '3', '27650', 'J');
INSERT INTO `cms_region` VALUES ('27906', '通道县', '3', '27650', 'T');
INSERT INTO `cms_region` VALUES ('27930', '洪江市', '3', '27650', 'H');
INSERT INTO `cms_region` VALUES ('27963', '娄底市', '2', '25579', 'L');
INSERT INTO `cms_region` VALUES ('27964', '市辖区', '3', '27963', 'S');
INSERT INTO `cms_region` VALUES ('27965', '娄星区', '3', '27963', 'L');
INSERT INTO `cms_region` VALUES ('27980', '双峰县', '3', '27963', 'S');
INSERT INTO `cms_region` VALUES ('27997', '新化县', '3', '27963', 'X');
INSERT INTO `cms_region` VALUES ('28027', '冷水江市', '3', '27963', 'L');
INSERT INTO `cms_region` VALUES ('28044', '涟源市', '3', '27963', 'L');
INSERT INTO `cms_region` VALUES ('28065', '湘西州', '2', '25579', 'X');
INSERT INTO `cms_region` VALUES ('28066', '吉首市', '3', '28065', 'J');
INSERT INTO `cms_region` VALUES ('28082', '泸溪县', '3', '28065', 'L');
INSERT INTO `cms_region` VALUES ('28099', '凤凰县', '3', '28065', 'F');
INSERT INTO `cms_region` VALUES ('28124', '花垣县', '3', '28065', 'H');
INSERT INTO `cms_region` VALUES ('28143', '保靖县', '3', '28065', 'B');
INSERT INTO `cms_region` VALUES ('28161', '古丈县', '3', '28065', 'G');
INSERT INTO `cms_region` VALUES ('28174', '永顺县', '3', '28065', 'Y');
INSERT INTO `cms_region` VALUES ('28205', '龙山县', '3', '28065', 'L');
INSERT INTO `cms_region` VALUES ('28240', '广东省', '1', '0', 'G');
INSERT INTO `cms_region` VALUES ('28241', '广州市', '2', '28240', 'G');
INSERT INTO `cms_region` VALUES ('28242', '市辖区', '3', '28241', 'S');
INSERT INTO `cms_region` VALUES ('28243', '荔湾区', '3', '28241', 'L');
INSERT INTO `cms_region` VALUES ('28266', '越秀区', '3', '28241', 'Y');
INSERT INTO `cms_region` VALUES ('28289', '海珠区', '3', '28241', 'H');
INSERT INTO `cms_region` VALUES ('28308', '天河区', '3', '28241', 'T');
INSERT INTO `cms_region` VALUES ('28330', '白云区', '3', '28241', 'B');
INSERT INTO `cms_region` VALUES ('28349', '黄埔区', '3', '28241', 'H');
INSERT INTO `cms_region` VALUES ('28359', '番禺区', '3', '28241', 'F');
INSERT INTO `cms_region` VALUES ('28377', '花都区', '3', '28241', 'H');
INSERT INTO `cms_region` VALUES ('28386', '南沙区', '3', '28241', 'N');
INSERT INTO `cms_region` VALUES ('28392', '萝岗区', '3', '28241', 'L');
INSERT INTO `cms_region` VALUES ('28399', '增城市', '3', '28241', 'Z');
INSERT INTO `cms_region` VALUES ('28409', '从化市', '3', '28241', 'C');
INSERT INTO `cms_region` VALUES ('28421', '韶关市', '2', '28240', 'S');
INSERT INTO `cms_region` VALUES ('28422', '市辖区', '3', '28421', 'S');
INSERT INTO `cms_region` VALUES ('28423', '武江区', '3', '28421', 'W');
INSERT INTO `cms_region` VALUES ('28431', '浈江区', '3', '28421', 'Z');
INSERT INTO `cms_region` VALUES ('28448', '曲江区', '3', '28421', 'Q');
INSERT INTO `cms_region` VALUES ('28463', '始兴县', '3', '28421', 'S');
INSERT INTO `cms_region` VALUES ('28475', '仁化县', '3', '28421', 'R');
INSERT INTO `cms_region` VALUES ('28488', '翁源县', '3', '28421', 'W');
INSERT INTO `cms_region` VALUES ('28497', '乳源县', '3', '28421', 'R');
INSERT INTO `cms_region` VALUES ('28509', '新丰县', '3', '28421', 'X');
INSERT INTO `cms_region` VALUES ('28517', '乐昌市', '3', '28421', 'L');
INSERT INTO `cms_region` VALUES ('28539', '南雄市', '3', '28421', 'N');
INSERT INTO `cms_region` VALUES ('28558', '深圳市', '2', '28240', 'S');
INSERT INTO `cms_region` VALUES ('28559', '市辖区', '3', '28558', 'S');
INSERT INTO `cms_region` VALUES ('28560', '罗湖区', '3', '28558', 'L');
INSERT INTO `cms_region` VALUES ('28571', '福田区', '3', '28558', 'F');
INSERT INTO `cms_region` VALUES ('28581', '南山区', '3', '28558', 'N');
INSERT INTO `cms_region` VALUES ('28590', '宝安区', '3', '28558', 'B');
INSERT INTO `cms_region` VALUES ('28604', '龙岗区', '3', '28558', 'L');
INSERT INTO `cms_region` VALUES ('28619', '盐田区', '3', '28558', 'Y');
INSERT INTO `cms_region` VALUES ('28626', '珠海市', '2', '28240', 'Z');
INSERT INTO `cms_region` VALUES ('28627', '市辖区', '3', '28626', 'S');
INSERT INTO `cms_region` VALUES ('28628', '香洲区', '3', '28626', 'X');
INSERT INTO `cms_region` VALUES ('28646', '斗门区', '3', '28626', 'D');
INSERT INTO `cms_region` VALUES ('28654', '金湾区', '3', '28626', 'J');
INSERT INTO `cms_region` VALUES ('28659', '汕头市', '2', '28240', 'S');
INSERT INTO `cms_region` VALUES ('28660', '市辖区', '3', '28659', 'S');
INSERT INTO `cms_region` VALUES ('28661', '龙湖区', '3', '28659', 'L');
INSERT INTO `cms_region` VALUES ('28669', '金平区', '3', '28659', 'J');
INSERT INTO `cms_region` VALUES ('28687', '濠江区', '3', '28659', 'H');
INSERT INTO `cms_region` VALUES ('28695', '潮阳区', '3', '28659', 'C');
INSERT INTO `cms_region` VALUES ('28709', '潮南区', '3', '28659', 'C');
INSERT INTO `cms_region` VALUES ('28721', '澄海区', '3', '28659', 'C');
INSERT INTO `cms_region` VALUES ('28733', '南澳县', '3', '28659', 'N');
INSERT INTO `cms_region` VALUES ('28737', '佛山市', '2', '28240', 'F');
INSERT INTO `cms_region` VALUES ('28738', '市辖区', '3', '28737', 'S');
INSERT INTO `cms_region` VALUES ('28739', '禅城区', '3', '28737', 'C');
INSERT INTO `cms_region` VALUES ('28744', '南海区', '3', '28737', 'N');
INSERT INTO `cms_region` VALUES ('28753', '顺德区', '3', '28737', 'S');
INSERT INTO `cms_region` VALUES ('28764', '三水区', '3', '28737', 'S');
INSERT INTO `cms_region` VALUES ('28776', '高明区', '3', '28737', 'G');
INSERT INTO `cms_region` VALUES ('28785', '江门市', '2', '28240', 'J');
INSERT INTO `cms_region` VALUES ('28786', '市辖区', '3', '28785', 'S');
INSERT INTO `cms_region` VALUES ('28787', '蓬江区', '3', '28785', 'P');
INSERT INTO `cms_region` VALUES ('28797', '江海区', '3', '28785', 'J');
INSERT INTO `cms_region` VALUES ('28803', '新会区', '3', '28785', 'X');
INSERT INTO `cms_region` VALUES ('28818', '台山市', '3', '28785', 'T');
INSERT INTO `cms_region` VALUES ('28837', '开平市', '3', '28785', 'K');
INSERT INTO `cms_region` VALUES ('28853', '鹤山市', '3', '28785', 'H');
INSERT INTO `cms_region` VALUES ('28867', '恩平市', '3', '28785', 'E');
INSERT INTO `cms_region` VALUES ('28880', '湛江市', '2', '28240', 'Z');
INSERT INTO `cms_region` VALUES ('28881', '市辖区', '3', '28880', 'S');
INSERT INTO `cms_region` VALUES ('28882', '湛江市赤坎区', '3', '28880', 'Z');
INSERT INTO `cms_region` VALUES ('28891', '湛江市霞山区', '3', '28880', 'Z');
INSERT INTO `cms_region` VALUES ('28904', '湛江市坡头区', '3', '28880', 'Z');
INSERT INTO `cms_region` VALUES ('28914', '湛江市麻章区', '3', '28880', 'Z');
INSERT INTO `cms_region` VALUES ('28923', '遂溪县', '3', '28880', 'S');
INSERT INTO `cms_region` VALUES ('28941', '徐闻县', '3', '28880', 'X');
INSERT INTO `cms_region` VALUES ('28962', '廉江市', '3', '28880', 'L');
INSERT INTO `cms_region` VALUES ('28984', '雷州市', '3', '28880', 'L');
INSERT INTO `cms_region` VALUES ('29010', '吴川市', '3', '28880', 'W');
INSERT INTO `cms_region` VALUES ('29026', '茂名市', '2', '28240', 'M');
INSERT INTO `cms_region` VALUES ('29027', '市辖区', '3', '29026', 'S');
INSERT INTO `cms_region` VALUES ('29028', '茂南区', '3', '29026', 'M');
INSERT INTO `cms_region` VALUES ('29045', '茂港区', '3', '29026', 'M');
INSERT INTO `cms_region` VALUES ('29053', '电白县', '3', '29026', 'D');
INSERT INTO `cms_region` VALUES ('29075', '高州市', '3', '29026', 'G');
INSERT INTO `cms_region` VALUES ('29107', '化州市', '3', '29026', 'H');
INSERT INTO `cms_region` VALUES ('29138', '信宜市', '3', '29026', 'X');
INSERT INTO `cms_region` VALUES ('29159', '肇庆市', '2', '28240', 'Z');
INSERT INTO `cms_region` VALUES ('29160', '市辖区', '3', '29159', 'S');
INSERT INTO `cms_region` VALUES ('29161', '端州区', '3', '29159', 'D');
INSERT INTO `cms_region` VALUES ('29169', '鼎湖区', '3', '29159', 'D');
INSERT INTO `cms_region` VALUES ('29178', '广宁县', '3', '29159', 'G');
INSERT INTO `cms_region` VALUES ('29196', '怀集县', '3', '29159', 'H');
INSERT INTO `cms_region` VALUES ('29217', '封开县', '3', '29159', 'F');
INSERT INTO `cms_region` VALUES ('29234', '德庆县', '3', '29159', 'D');
INSERT INTO `cms_region` VALUES ('29248', '高要市', '3', '29159', 'G');
INSERT INTO `cms_region` VALUES ('29266', '四会市', '3', '29159', 'S');
INSERT INTO `cms_region` VALUES ('29282', '惠州市', '2', '28240', 'H');
INSERT INTO `cms_region` VALUES ('29283', '市辖区', '3', '29282', 'S');
INSERT INTO `cms_region` VALUES ('29284', '惠城区', '3', '29282', 'H');
INSERT INTO `cms_region` VALUES ('29304', '惠阳区', '3', '29282', 'H');
INSERT INTO `cms_region` VALUES ('29317', '博罗县', '3', '29282', 'B');
INSERT INTO `cms_region` VALUES ('29335', '惠东县', '3', '29282', 'H');
INSERT INTO `cms_region` VALUES ('29355', '龙门县', '3', '29282', 'L');
INSERT INTO `cms_region` VALUES ('29371', '梅州市', '2', '28240', 'M');
INSERT INTO `cms_region` VALUES ('29372', '市辖区', '3', '29371', 'S');
INSERT INTO `cms_region` VALUES ('29373', '梅江区', '3', '29371', 'M');
INSERT INTO `cms_region` VALUES ('29380', '梅县', '3', '29371', 'M');
INSERT INTO `cms_region` VALUES ('29400', '大埔县', '3', '29371', 'D');
INSERT INTO `cms_region` VALUES ('29418', '丰顺县', '3', '29371', 'F');
INSERT INTO `cms_region` VALUES ('29436', '五华县', '3', '29371', 'W');
INSERT INTO `cms_region` VALUES ('29453', '平远县', '3', '29371', 'P');
INSERT INTO `cms_region` VALUES ('29466', '蕉岭县', '3', '29371', 'J');
INSERT INTO `cms_region` VALUES ('29477', '兴宁市', '3', '29371', 'X');
INSERT INTO `cms_region` VALUES ('29498', '汕尾市', '2', '28240', 'S');
INSERT INTO `cms_region` VALUES ('29499', '市辖区', '3', '29498', 'S');
INSERT INTO `cms_region` VALUES ('29500', '城区', '3', '29498', 'C');
INSERT INTO `cms_region` VALUES ('29511', '海丰县', '3', '29498', 'H');
INSERT INTO `cms_region` VALUES ('29529', '陆河县', '3', '29498', 'L');
INSERT INTO `cms_region` VALUES ('29538', '陆丰市', '3', '29498', 'L');
INSERT INTO `cms_region` VALUES ('29568', '河源市', '2', '28240', 'H');
INSERT INTO `cms_region` VALUES ('29569', '市辖区', '3', '29568', 'S');
INSERT INTO `cms_region` VALUES ('29570', '源城区', '3', '29568', 'Y');
INSERT INTO `cms_region` VALUES ('29578', '紫金县', '3', '29568', 'Z');
INSERT INTO `cms_region` VALUES ('29599', '龙川县', '3', '29568', 'L');
INSERT INTO `cms_region` VALUES ('29625', '连平县', '3', '29568', 'L');
INSERT INTO `cms_region` VALUES ('29639', '和平县', '3', '29568', 'H');
INSERT INTO `cms_region` VALUES ('29657', '东源县', '3', '29568', 'D');
INSERT INTO `cms_region` VALUES ('29679', '阳江市', '2', '28240', 'Y');
INSERT INTO `cms_region` VALUES ('29680', '市辖区', '3', '29679', 'S');
INSERT INTO `cms_region` VALUES ('29681', '江城区', '3', '29679', 'J');
INSERT INTO `cms_region` VALUES ('29698', '阳西县', '3', '29679', 'Y');
INSERT INTO `cms_region` VALUES ('29709', '阳东县', '3', '29679', 'Y');
INSERT INTO `cms_region` VALUES ('29729', '阳春市', '3', '29679', 'Y');
INSERT INTO `cms_region` VALUES ('29755', '清远市', '2', '28240', 'Q');
INSERT INTO `cms_region` VALUES ('29756', '市辖区', '3', '29755', 'S');
INSERT INTO `cms_region` VALUES ('29757', '清城区', '3', '29755', 'Q');
INSERT INTO `cms_region` VALUES ('29766', '佛冈县', '3', '29755', 'F');
INSERT INTO `cms_region` VALUES ('29773', '阳山县', '3', '29755', 'Y');
INSERT INTO `cms_region` VALUES ('29787', '连山县', '3', '29755', 'L');
INSERT INTO `cms_region` VALUES ('29797', '连南县', '3', '29755', 'L');
INSERT INTO `cms_region` VALUES ('29805', '清新县', '3', '29755', 'Q');
INSERT INTO `cms_region` VALUES ('29816', '英德市', '3', '29755', 'Y');
INSERT INTO `cms_region` VALUES ('29842', '连州市', '3', '29755', 'L');
INSERT INTO `cms_region` VALUES ('29855', '东莞市', '2', '28240', 'D');
INSERT INTO `cms_region` VALUES ('29890', '中山市', '2', '28240', 'Z');
INSERT INTO `cms_region` VALUES ('29915', '潮州市', '2', '28240', 'C');
INSERT INTO `cms_region` VALUES ('29916', '市辖区', '3', '29915', 'S');
INSERT INTO `cms_region` VALUES ('29917', '潮州市湘桥区', '3', '29915', 'C');
INSERT INTO `cms_region` VALUES ('29930', '潮州市潮安县', '3', '29915', 'C');
INSERT INTO `cms_region` VALUES ('29954', '潮州市饶平县', '3', '29915', 'C');
INSERT INTO `cms_region` VALUES ('29977', '揭阳市', '2', '28240', 'J');
INSERT INTO `cms_region` VALUES ('29978', '市辖区', '3', '29977', 'S');
INSERT INTO `cms_region` VALUES ('29979', '榕城区', '3', '29977', 'R');
INSERT INTO `cms_region` VALUES ('29990', '揭东县', '3', '29977', 'J');
INSERT INTO `cms_region` VALUES ('30008', '揭西县', '3', '29977', 'J');
INSERT INTO `cms_region` VALUES ('30032', '惠来县', '3', '29977', 'H');
INSERT INTO `cms_region` VALUES ('30054', '普宁市', '3', '29977', 'P');
INSERT INTO `cms_region` VALUES ('30086', '云浮市', '2', '28240', 'Y');
INSERT INTO `cms_region` VALUES ('30087', '市辖区', '3', '30086', 'S');
INSERT INTO `cms_region` VALUES ('30088', '云城区', '3', '30086', 'Y');
INSERT INTO `cms_region` VALUES ('30096', '新兴县', '3', '30086', 'X');
INSERT INTO `cms_region` VALUES ('30112', '郁南县', '3', '30086', 'Y');
INSERT INTO `cms_region` VALUES ('30132', '云安县', '3', '30086', 'Y');
INSERT INTO `cms_region` VALUES ('30141', '罗定市', '3', '30086', 'L');
INSERT INTO `cms_region` VALUES ('30164', '广西壮族自治区', '1', '0', 'G');
INSERT INTO `cms_region` VALUES ('30165', '南宁市', '2', '30164', 'N');
INSERT INTO `cms_region` VALUES ('30166', '市辖区', '3', '30165', 'S');
INSERT INTO `cms_region` VALUES ('30167', '兴宁区', '3', '30165', 'X');
INSERT INTO `cms_region` VALUES ('30174', '青秀区', '3', '30165', 'Q');
INSERT INTO `cms_region` VALUES ('30186', '江南区', '3', '30165', 'J');
INSERT INTO `cms_region` VALUES ('30196', '西乡塘区', '3', '30165', 'X');
INSERT INTO `cms_region` VALUES ('30214', '良庆区', '3', '30165', 'L');
INSERT INTO `cms_region` VALUES ('30222', '邕宁区', '3', '30165', 'Y');
INSERT INTO `cms_region` VALUES ('30228', '武鸣县', '3', '30165', 'W');
INSERT INTO `cms_region` VALUES ('30245', '隆安县', '3', '30165', 'L');
INSERT INTO `cms_region` VALUES ('30257', '马山县', '3', '30165', 'M');
INSERT INTO `cms_region` VALUES ('30270', '上林县', '3', '30165', 'S');
INSERT INTO `cms_region` VALUES ('30282', '宾阳县', '3', '30165', 'B');
INSERT INTO `cms_region` VALUES ('30300', '横县', '3', '30165', 'H');
INSERT INTO `cms_region` VALUES ('30319', '柳州市', '2', '30164', 'L');
INSERT INTO `cms_region` VALUES ('30320', '市辖区', '3', '30319', 'S');
INSERT INTO `cms_region` VALUES ('30321', '城中区', '3', '30319', 'C');
INSERT INTO `cms_region` VALUES ('30329', '鱼峰区', '3', '30319', 'Y');
INSERT INTO `cms_region` VALUES ('30338', '柳南区', '3', '30319', 'L');
INSERT INTO `cms_region` VALUES ('30348', '柳北区', '3', '30319', 'L');
INSERT INTO `cms_region` VALUES ('30361', '柳江县', '3', '30319', 'L');
INSERT INTO `cms_region` VALUES ('30374', '柳城县', '3', '30319', 'L');
INSERT INTO `cms_region` VALUES ('30387', '鹿寨县', '3', '30319', 'L');
INSERT INTO `cms_region` VALUES ('30398', '融安县', '3', '30319', 'R');
INSERT INTO `cms_region` VALUES ('30411', '融水县', '3', '30319', 'R');
INSERT INTO `cms_region` VALUES ('30432', '三江县', '3', '30319', 'S');
INSERT INTO `cms_region` VALUES ('30448', '桂林市', '2', '30164', 'G');
INSERT INTO `cms_region` VALUES ('30449', '市辖区', '3', '30448', 'S');
INSERT INTO `cms_region` VALUES ('30450', '秀峰区', '3', '30448', 'X');
INSERT INTO `cms_region` VALUES ('30454', '叠彩区', '3', '30448', 'D');
INSERT INTO `cms_region` VALUES ('30458', '象山区', '3', '30448', 'X');
INSERT INTO `cms_region` VALUES ('30463', '七星区', '3', '30448', 'Q');
INSERT INTO `cms_region` VALUES ('30469', '雁山区', '3', '30448', 'Y');
INSERT INTO `cms_region` VALUES ('30475', '阳朔县', '3', '30448', 'Y');
INSERT INTO `cms_region` VALUES ('30485', '临桂县', '3', '30448', 'L');
INSERT INTO `cms_region` VALUES ('30497', '灵川县', '3', '30448', 'L');
INSERT INTO `cms_region` VALUES ('30509', '全州县', '3', '30448', 'Q');
INSERT INTO `cms_region` VALUES ('30528', '兴安县', '3', '30448', 'X');
INSERT INTO `cms_region` VALUES ('30539', '永福县', '3', '30448', 'Y');
INSERT INTO `cms_region` VALUES ('30549', '灌阳县', '3', '30448', 'G');
INSERT INTO `cms_region` VALUES ('30559', '龙胜县', '3', '30448', 'L');
INSERT INTO `cms_region` VALUES ('30570', '资源县', '3', '30448', 'Z');
INSERT INTO `cms_region` VALUES ('30578', '平乐县', '3', '30448', 'P');
INSERT INTO `cms_region` VALUES ('30589', '荔浦县', '3', '30448', 'L');
INSERT INTO `cms_region` VALUES ('30603', '恭城县', '3', '30448', 'G');
INSERT INTO `cms_region` VALUES ('30613', '梧州市', '2', '30164', 'W');
INSERT INTO `cms_region` VALUES ('30614', '市辖区', '3', '30613', 'S');
INSERT INTO `cms_region` VALUES ('30615', '万秀区', '3', '30613', 'W');
INSERT INTO `cms_region` VALUES ('30622', '蝶山区', '3', '30613', 'D');
INSERT INTO `cms_region` VALUES ('30628', '长洲区', '3', '30613', 'C');
INSERT INTO `cms_region` VALUES ('30633', '苍梧县', '3', '30613', 'C');
INSERT INTO `cms_region` VALUES ('30646', '藤县', '3', '30613', 'T');
INSERT INTO `cms_region` VALUES ('30663', '蒙山县', '3', '30613', 'M');
INSERT INTO `cms_region` VALUES ('30673', '岑溪市', '3', '30613', 'C');
INSERT INTO `cms_region` VALUES ('30688', '北海市', '2', '30164', 'B');
INSERT INTO `cms_region` VALUES ('30689', '市辖区', '3', '30688', 'S');
INSERT INTO `cms_region` VALUES ('30690', '海城区', '3', '30688', 'H');
INSERT INTO `cms_region` VALUES ('30699', '银海区', '3', '30688', 'Y');
INSERT INTO `cms_region` VALUES ('30704', '铁山港区', '3', '30688', 'T');
INSERT INTO `cms_region` VALUES ('30708', '合浦县', '3', '30688', 'H');
INSERT INTO `cms_region` VALUES ('30724', '防城港市', '2', '30164', 'F');
INSERT INTO `cms_region` VALUES ('30725', '市辖区', '3', '30724', 'S');
INSERT INTO `cms_region` VALUES ('30726', '港口区', '3', '30724', 'G');
INSERT INTO `cms_region` VALUES ('30732', '防城区', '3', '30724', 'F');
INSERT INTO `cms_region` VALUES ('30748', '上思县', '3', '30724', 'S');
INSERT INTO `cms_region` VALUES ('30758', '东兴市', '3', '30724', 'D');
INSERT INTO `cms_region` VALUES ('30762', '钦州市', '2', '30164', 'Q');
INSERT INTO `cms_region` VALUES ('30763', '市辖区', '3', '30762', 'S');
INSERT INTO `cms_region` VALUES ('30764', '钦南区', '3', '30762', 'Q');
INSERT INTO `cms_region` VALUES ('30783', '钦北区', '3', '30762', 'Q');
INSERT INTO `cms_region` VALUES ('30796', '灵山县', '3', '30762', 'L');
INSERT INTO `cms_region` VALUES ('30817', '浦北县', '3', '30762', 'P');
INSERT INTO `cms_region` VALUES ('30834', '贵港市', '2', '30164', 'G');
INSERT INTO `cms_region` VALUES ('30835', '市辖区', '3', '30834', 'S');
INSERT INTO `cms_region` VALUES ('30836', '港北区', '3', '30834', 'G');
INSERT INTO `cms_region` VALUES ('30845', '港南区', '3', '30834', 'G');
INSERT INTO `cms_region` VALUES ('30855', '覃塘区', '3', '30834', 'Q');
INSERT INTO `cms_region` VALUES ('30866', '平南县', '3', '30834', 'P');
INSERT INTO `cms_region` VALUES ('30888', '桂平市', '3', '30834', 'G');
INSERT INTO `cms_region` VALUES ('30915', '玉林市', '2', '30164', 'Y');
INSERT INTO `cms_region` VALUES ('30916', '市辖区', '3', '30915', 'S');
INSERT INTO `cms_region` VALUES ('30917', '玉州区', '3', '30915', 'Y');
INSERT INTO `cms_region` VALUES ('30933', '容县', '3', '30915', 'R');
INSERT INTO `cms_region` VALUES ('30949', '陆川县', '3', '30915', 'L');
INSERT INTO `cms_region` VALUES ('30964', '博白县', '3', '30915', 'B');
INSERT INTO `cms_region` VALUES ('30993', '兴业县', '3', '30915', 'X');
INSERT INTO `cms_region` VALUES ('31007', '北流市', '3', '30915', 'B');
INSERT INTO `cms_region` VALUES ('31033', '百色市', '2', '30164', 'B');
INSERT INTO `cms_region` VALUES ('31034', '市辖区', '3', '31033', 'S');
INSERT INTO `cms_region` VALUES ('31035', '右江区', '3', '31033', 'Y');
INSERT INTO `cms_region` VALUES ('31045', '田阳县', '3', '31033', 'T');
INSERT INTO `cms_region` VALUES ('31056', '田东县', '3', '31033', 'T');
INSERT INTO `cms_region` VALUES ('31067', '平果县', '3', '31033', 'P');
INSERT INTO `cms_region` VALUES ('31081', '德保县', '3', '31033', 'D');
INSERT INTO `cms_region` VALUES ('31095', '靖西县', '3', '31033', 'J');
INSERT INTO `cms_region` VALUES ('31115', '那坡县', '3', '31033', 'N');
INSERT INTO `cms_region` VALUES ('31125', '凌云县', '3', '31033', 'L');
INSERT INTO `cms_region` VALUES ('31134', '乐业县', '3', '31033', 'L');
INSERT INTO `cms_region` VALUES ('31143', '田林县', '3', '31033', 'T');
INSERT INTO `cms_region` VALUES ('31158', '西林县', '3', '31033', 'X');
INSERT INTO `cms_region` VALUES ('31167', '隆林县', '3', '31033', 'L');
INSERT INTO `cms_region` VALUES ('31184', '贺州市', '2', '30164', 'H');
INSERT INTO `cms_region` VALUES ('31185', '市辖区', '3', '31184', 'S');
INSERT INTO `cms_region` VALUES ('31186', '八步区', '3', '31184', 'B');
INSERT INTO `cms_region` VALUES ('31208', '昭平县', '3', '31184', 'Z');
INSERT INTO `cms_region` VALUES ('31221', '钟山县', '3', '31184', 'Z');
INSERT INTO `cms_region` VALUES ('31236', '富川县', '3', '31184', 'F');
INSERT INTO `cms_region` VALUES ('31249', '河池市', '2', '30164', 'H');
INSERT INTO `cms_region` VALUES ('31250', '市辖区', '3', '31249', 'S');
INSERT INTO `cms_region` VALUES ('31251', '金城江区', '3', '31249', 'J');
INSERT INTO `cms_region` VALUES ('31264', '南丹县', '3', '31249', 'N');
INSERT INTO `cms_region` VALUES ('31276', '天峨县', '3', '31249', 'T');
INSERT INTO `cms_region` VALUES ('31286', '凤山县', '3', '31249', 'F');
INSERT INTO `cms_region` VALUES ('31296', '东兰县', '3', '31249', 'D');
INSERT INTO `cms_region` VALUES ('31311', '罗城县', '3', '31249', 'L');
INSERT INTO `cms_region` VALUES ('31323', '环江县', '3', '31249', 'H');
INSERT INTO `cms_region` VALUES ('31336', '巴马县', '3', '31249', 'B');
INSERT INTO `cms_region` VALUES ('31347', '都安县', '3', '31249', 'D');
INSERT INTO `cms_region` VALUES ('31367', '大化县', '3', '31249', 'D');
INSERT INTO `cms_region` VALUES ('31384', '宜州市', '3', '31249', 'Y');
INSERT INTO `cms_region` VALUES ('31401', '来宾市', '2', '30164', 'L');
INSERT INTO `cms_region` VALUES ('31402', '市辖区', '3', '31401', 'S');
INSERT INTO `cms_region` VALUES ('31403', '兴宾区', '3', '31401', 'X');
INSERT INTO `cms_region` VALUES ('31427', '忻城县', '3', '31401', 'X');
INSERT INTO `cms_region` VALUES ('31440', '象州县', '3', '31401', 'X');
INSERT INTO `cms_region` VALUES ('31452', '武宣县', '3', '31401', 'W');
INSERT INTO `cms_region` VALUES ('31463', '金秀县', '3', '31401', 'J');
INSERT INTO `cms_region` VALUES ('31474', '合山市', '3', '31401', 'H');
INSERT INTO `cms_region` VALUES ('31478', '崇左市', '2', '30164', 'C');
INSERT INTO `cms_region` VALUES ('31479', '市辖区', '3', '31478', 'S');
INSERT INTO `cms_region` VALUES ('31480', '江州区', '3', '31478', 'J');
INSERT INTO `cms_region` VALUES ('31490', '扶绥县', '3', '31478', 'F');
INSERT INTO `cms_region` VALUES ('31502', '宁明县', '3', '31478', 'N');
INSERT INTO `cms_region` VALUES ('31516', '龙州县', '3', '31478', 'L');
INSERT INTO `cms_region` VALUES ('31529', '大新县', '3', '31478', 'D');
INSERT INTO `cms_region` VALUES ('31544', '天等县', '3', '31478', 'T');
INSERT INTO `cms_region` VALUES ('31558', '凭祥市', '3', '31478', 'P');
INSERT INTO `cms_region` VALUES ('31563', '海南省', '1', '0', 'H');
INSERT INTO `cms_region` VALUES ('31564', '海口市', '2', '31563', 'H');
INSERT INTO `cms_region` VALUES ('31565', '市辖区', '3', '31564', 'S');
INSERT INTO `cms_region` VALUES ('31566', '秀英区', '3', '31564', 'X');
INSERT INTO `cms_region` VALUES ('31575', '龙华区', '3', '31564', 'L');
INSERT INTO `cms_region` VALUES ('31587', '琼山区', '3', '31564', 'Q');
INSERT INTO `cms_region` VALUES ('31601', '美兰区', '3', '31564', 'M');
INSERT INTO `cms_region` VALUES ('31618', '三亚市', '2', '31563', 'S');
INSERT INTO `cms_region` VALUES ('47967', '海棠区', '3', '31618', 'H');
INSERT INTO `cms_region` VALUES ('31634', '五指山市', '2', '31563', 'W');
INSERT INTO `cms_region` VALUES ('31635', '冲山镇', '3', '31634', 'C');
INSERT INTO `cms_region` VALUES ('31636', '南圣镇', '3', '31634', 'N');
INSERT INTO `cms_region` VALUES ('31637', '毛阳镇', '3', '31634', 'M');
INSERT INTO `cms_region` VALUES ('31638', '番阳镇', '3', '31634', 'F');
INSERT INTO `cms_region` VALUES ('31639', '畅好乡', '3', '31634', 'C');
INSERT INTO `cms_region` VALUES ('31640', '毛道乡', '3', '31634', 'M');
INSERT INTO `cms_region` VALUES ('31641', '水满乡', '3', '31634', 'S');
INSERT INTO `cms_region` VALUES ('31642', '国营畅好农场', '3', '31634', 'G');
INSERT INTO `cms_region` VALUES ('31643', '琼海市', '2', '31563', 'Q');
INSERT INTO `cms_region` VALUES ('31644', '嘉积镇', '3', '31643', 'J');
INSERT INTO `cms_region` VALUES ('31645', '万泉镇', '3', '31643', 'W');
INSERT INTO `cms_region` VALUES ('31646', '石壁镇', '3', '31643', 'S');
INSERT INTO `cms_region` VALUES ('31647', '中原镇', '3', '31643', 'Z');
INSERT INTO `cms_region` VALUES ('31648', '博敖镇', '3', '31643', 'B');
INSERT INTO `cms_region` VALUES ('31649', '阳江镇', '3', '31643', 'Y');
INSERT INTO `cms_region` VALUES ('31650', '龙江镇', '3', '31643', 'L');
INSERT INTO `cms_region` VALUES ('31651', '潭门镇', '3', '31643', 'T');
INSERT INTO `cms_region` VALUES ('31652', '塔洋镇', '3', '31643', 'T');
INSERT INTO `cms_region` VALUES ('31653', '长坡镇', '3', '31643', 'C');
INSERT INTO `cms_region` VALUES ('31654', '大路镇', '3', '31643', 'D');
INSERT INTO `cms_region` VALUES ('31655', '会山镇', '3', '31643', 'H');
INSERT INTO `cms_region` VALUES ('31656', '国营东太农场', '3', '31643', 'G');
INSERT INTO `cms_region` VALUES ('31657', '国营东平农场', '3', '31643', 'G');
INSERT INTO `cms_region` VALUES ('31658', '国营东红农场', '3', '31643', 'G');
INSERT INTO `cms_region` VALUES ('31659', '国营东升农场', '3', '31643', 'G');
INSERT INTO `cms_region` VALUES ('31660', '国营南俸农场', '3', '31643', 'G');
INSERT INTO `cms_region` VALUES ('31661', '彬村山华侨农场', '3', '31643', 'B');
INSERT INTO `cms_region` VALUES ('31662', '儋州市', '2', '31563', 'D');
INSERT INTO `cms_region` VALUES ('31663', '那大镇', '3', '31662', 'N');
INSERT INTO `cms_region` VALUES ('31664', '和庆镇', '3', '31662', 'H');
INSERT INTO `cms_region` VALUES ('31665', '南丰镇', '3', '31662', 'N');
INSERT INTO `cms_region` VALUES ('31666', '大成镇', '3', '31662', 'D');
INSERT INTO `cms_region` VALUES ('31667', '雅星镇', '3', '31662', 'Y');
INSERT INTO `cms_region` VALUES ('31668', '兰洋镇', '3', '31662', 'L');
INSERT INTO `cms_region` VALUES ('31669', '光村镇', '3', '31662', 'G');
INSERT INTO `cms_region` VALUES ('31670', '木棠镇', '3', '31662', 'M');
INSERT INTO `cms_region` VALUES ('31671', '海头镇', '3', '31662', 'H');
INSERT INTO `cms_region` VALUES ('31672', '峨蔓镇', '3', '31662', 'E');
INSERT INTO `cms_region` VALUES ('31673', '三都镇', '3', '31662', 'S');
INSERT INTO `cms_region` VALUES ('31674', '王五镇', '3', '31662', 'W');
INSERT INTO `cms_region` VALUES ('31675', '白马井镇', '3', '31662', 'B');
INSERT INTO `cms_region` VALUES ('31676', '中和镇', '3', '31662', 'Z');
INSERT INTO `cms_region` VALUES ('31677', '排浦镇', '3', '31662', 'P');
INSERT INTO `cms_region` VALUES ('31678', '东成镇', '3', '31662', 'D');
INSERT INTO `cms_region` VALUES ('31679', '新州镇', '3', '31662', 'X');
INSERT INTO `cms_region` VALUES ('31680', '国营西培农场', '3', '31662', 'G');
INSERT INTO `cms_region` VALUES ('31681', '国营西华农场', '3', '31662', 'G');
INSERT INTO `cms_region` VALUES ('31682', '国营西庆农场', '3', '31662', 'G');
INSERT INTO `cms_region` VALUES ('31683', '国营西流农场', '3', '31662', 'G');
INSERT INTO `cms_region` VALUES ('31684', '国营西联农场', '3', '31662', 'G');
INSERT INTO `cms_region` VALUES ('31685', '国营蓝洋农场', '3', '31662', 'G');
INSERT INTO `cms_region` VALUES ('31686', '国营新盈农场', '3', '31662', 'G');
INSERT INTO `cms_region` VALUES ('31687', '国营八一农场东山分场', '3', '31662', 'G');
INSERT INTO `cms_region` VALUES ('31688', '国营八一农场金川分场', '3', '31662', 'G');
INSERT INTO `cms_region` VALUES ('31689', '国营八一农场长岭分场', '3', '31662', 'G');
INSERT INTO `cms_region` VALUES ('31690', '国营八一农场英岛分场', '3', '31662', 'G');
INSERT INTO `cms_region` VALUES ('31691', '国营八一农场春江分场', '3', '31662', 'G');
INSERT INTO `cms_region` VALUES ('31692', '国营八一农场强打管区', '3', '31662', 'G');
INSERT INTO `cms_region` VALUES ('31693', '国营龙山农场', '3', '31662', 'G');
INSERT INTO `cms_region` VALUES ('31694', '国营红岭农场', '3', '31662', 'G');
INSERT INTO `cms_region` VALUES ('31695', '洋浦经济开发区', '3', '31662', 'Y');
INSERT INTO `cms_region` VALUES ('31696', '华南热作学院', '3', '31662', 'H');
INSERT INTO `cms_region` VALUES ('31697', '文昌市', '2', '31563', 'W');
INSERT INTO `cms_region` VALUES ('31698', '文城镇', '3', '31697', 'W');
INSERT INTO `cms_region` VALUES ('31699', '重兴镇', '3', '31697', 'Z');
INSERT INTO `cms_region` VALUES ('31700', '蓬莱镇', '3', '31697', 'P');
INSERT INTO `cms_region` VALUES ('31701', '会文镇', '3', '31697', 'H');
INSERT INTO `cms_region` VALUES ('31702', '东路镇', '3', '31697', 'D');
INSERT INTO `cms_region` VALUES ('31703', '潭牛镇', '3', '31697', 'T');
INSERT INTO `cms_region` VALUES ('31704', '东阁镇', '3', '31697', 'D');
INSERT INTO `cms_region` VALUES ('31705', '文教镇', '3', '31697', 'W');
INSERT INTO `cms_region` VALUES ('31706', '东郊镇', '3', '31697', 'D');
INSERT INTO `cms_region` VALUES ('31707', '龙楼镇', '3', '31697', 'L');
INSERT INTO `cms_region` VALUES ('31708', '昌洒镇', '3', '31697', 'C');
INSERT INTO `cms_region` VALUES ('31709', '翁田镇', '3', '31697', 'W');
INSERT INTO `cms_region` VALUES ('31710', '抱罗镇', '3', '31697', 'B');
INSERT INTO `cms_region` VALUES ('31711', '冯坡镇', '3', '31697', 'F');
INSERT INTO `cms_region` VALUES ('31712', '锦山镇', '3', '31697', 'J');
INSERT INTO `cms_region` VALUES ('31713', '铺前镇', '3', '31697', 'P');
INSERT INTO `cms_region` VALUES ('31714', '国营东路农场', '3', '31697', 'G');
INSERT INTO `cms_region` VALUES ('31715', '国营南阳农场', '3', '31697', 'G');
INSERT INTO `cms_region` VALUES ('31716', '国营罗豆农场', '3', '31697', 'G');
INSERT INTO `cms_region` VALUES ('31717', '国营文昌橡胶研究所', '3', '31697', 'G');
INSERT INTO `cms_region` VALUES ('31718', '万宁市', '2', '31563', 'W');
INSERT INTO `cms_region` VALUES ('31719', '万城镇', '3', '31718', 'W');
INSERT INTO `cms_region` VALUES ('31720', '龙滚镇', '3', '31718', 'L');
INSERT INTO `cms_region` VALUES ('31721', '和乐镇', '3', '31718', 'H');
INSERT INTO `cms_region` VALUES ('31722', '后安镇', '3', '31718', 'H');
INSERT INTO `cms_region` VALUES ('31723', '大茂镇', '3', '31718', 'D');
INSERT INTO `cms_region` VALUES ('31724', '东澳镇', '3', '31718', 'D');
INSERT INTO `cms_region` VALUES ('31725', '礼纪镇', '3', '31718', 'L');
INSERT INTO `cms_region` VALUES ('31726', '长丰镇', '3', '31718', 'C');
INSERT INTO `cms_region` VALUES ('31727', '山根镇', '3', '31718', 'S');
INSERT INTO `cms_region` VALUES ('31728', '北大镇', '3', '31718', 'B');
INSERT INTO `cms_region` VALUES ('31729', '南桥镇', '3', '31718', 'N');
INSERT INTO `cms_region` VALUES ('31730', '三更罗镇', '3', '31718', 'S');
INSERT INTO `cms_region` VALUES ('31731', '国营东兴农场', '3', '31718', 'G');
INSERT INTO `cms_region` VALUES ('31732', '国营东和农场', '3', '31718', 'G');
INSERT INTO `cms_region` VALUES ('31733', '国营东岭农场', '3', '31718', 'G');
INSERT INTO `cms_region` VALUES ('31734', '国营南林农场', '3', '31718', 'G');
INSERT INTO `cms_region` VALUES ('31735', '国营新中农场', '3', '31718', 'G');
INSERT INTO `cms_region` VALUES ('31736', '兴隆华侨农场', '3', '31718', 'X');
INSERT INTO `cms_region` VALUES ('31737', '地方国营六连林场', '3', '31718', 'D');
INSERT INTO `cms_region` VALUES ('31738', '东方市', '2', '31563', 'D');
INSERT INTO `cms_region` VALUES ('31739', '八所镇', '3', '31738', 'B');
INSERT INTO `cms_region` VALUES ('31740', '东河镇', '3', '31738', 'D');
INSERT INTO `cms_region` VALUES ('31741', '大田镇', '3', '31738', 'D');
INSERT INTO `cms_region` VALUES ('31742', '感城镇', '3', '31738', 'G');
INSERT INTO `cms_region` VALUES ('31743', '板桥镇', '3', '31738', 'B');
INSERT INTO `cms_region` VALUES ('31744', '三家镇', '3', '31738', 'S');
INSERT INTO `cms_region` VALUES ('31745', '四更镇', '3', '31738', 'S');
INSERT INTO `cms_region` VALUES ('31746', '新龙镇', '3', '31738', 'X');
INSERT INTO `cms_region` VALUES ('31747', '天安乡', '3', '31738', 'T');
INSERT INTO `cms_region` VALUES ('31748', '江边乡', '3', '31738', 'J');
INSERT INTO `cms_region` VALUES ('31749', '国营广坝农场', '3', '31738', 'G');
INSERT INTO `cms_region` VALUES ('31750', '国营公爱农场', '3', '31738', 'G');
INSERT INTO `cms_region` VALUES ('31751', '国营红泉农场', '3', '31738', 'G');
INSERT INTO `cms_region` VALUES ('31752', '省国营东方华侨农场', '3', '31738', 'S');
INSERT INTO `cms_region` VALUES ('31753', '定安县', '2', '31563', 'D');
INSERT INTO `cms_region` VALUES ('31754', '定城镇', '3', '31753', 'D');
INSERT INTO `cms_region` VALUES ('31755', '新竹镇', '3', '31753', 'X');
INSERT INTO `cms_region` VALUES ('31756', '龙湖镇', '3', '31753', 'L');
INSERT INTO `cms_region` VALUES ('31757', '黄竹镇', '3', '31753', 'H');
INSERT INTO `cms_region` VALUES ('31758', '雷鸣镇', '3', '31753', 'L');
INSERT INTO `cms_region` VALUES ('31759', '龙门镇', '3', '31753', 'L');
INSERT INTO `cms_region` VALUES ('31760', '龙河镇', '3', '31753', 'L');
INSERT INTO `cms_region` VALUES ('31761', '岭口镇', '3', '31753', 'L');
INSERT INTO `cms_region` VALUES ('31762', '翰林镇', '3', '31753', 'H');
INSERT INTO `cms_region` VALUES ('31763', '富文镇', '3', '31753', 'F');
INSERT INTO `cms_region` VALUES ('31764', '国营中瑞农场', '3', '31753', 'G');
INSERT INTO `cms_region` VALUES ('31765', '国营南海农场', '3', '31753', 'G');
INSERT INTO `cms_region` VALUES ('31766', '国营金鸡岭农场', '3', '31753', 'G');
INSERT INTO `cms_region` VALUES ('31767', '定安热作研究所', '3', '31753', 'D');
INSERT INTO `cms_region` VALUES ('31768', '屯昌县', '2', '31563', 'T');
INSERT INTO `cms_region` VALUES ('31769', '屯城镇', '3', '31768', 'T');
INSERT INTO `cms_region` VALUES ('31770', '新兴镇', '3', '31768', 'X');
INSERT INTO `cms_region` VALUES ('31771', '枫木镇', '3', '31768', 'F');
INSERT INTO `cms_region` VALUES ('31772', '乌坡镇', '3', '31768', 'W');
INSERT INTO `cms_region` VALUES ('31773', '南吕镇', '3', '31768', 'N');
INSERT INTO `cms_region` VALUES ('31774', '南坤镇', '3', '31768', 'N');
INSERT INTO `cms_region` VALUES ('31775', '坡心镇', '3', '31768', 'P');
INSERT INTO `cms_region` VALUES ('31776', '西昌镇', '3', '31768', 'X');
INSERT INTO `cms_region` VALUES ('31777', '国营中建农场', '3', '31768', 'G');
INSERT INTO `cms_region` VALUES ('31778', '国营中坤农场', '3', '31768', 'G');
INSERT INTO `cms_region` VALUES ('31779', '国营黄岭农场', '3', '31768', 'G');
INSERT INTO `cms_region` VALUES ('31780', '国营南吕农场', '3', '31768', 'G');
INSERT INTO `cms_region` VALUES ('31781', '国营广青农场', '3', '31768', 'G');
INSERT INTO `cms_region` VALUES ('31782', '国营晨星农场', '3', '31768', 'G');
INSERT INTO `cms_region` VALUES ('31783', '澄迈县', '2', '31563', 'C');
INSERT INTO `cms_region` VALUES ('31784', '金江镇', '3', '31783', 'J');
INSERT INTO `cms_region` VALUES ('31785', '老城镇', '3', '31783', 'L');
INSERT INTO `cms_region` VALUES ('31786', '瑞溪镇', '3', '31783', 'R');
INSERT INTO `cms_region` VALUES ('31787', '永发镇', '3', '31783', 'Y');
INSERT INTO `cms_region` VALUES ('31788', '加乐镇', '3', '31783', 'J');
INSERT INTO `cms_region` VALUES ('31789', '文儒镇', '3', '31783', 'W');
INSERT INTO `cms_region` VALUES ('31790', '中兴镇', '3', '31783', 'Z');
INSERT INTO `cms_region` VALUES ('31791', '仁兴镇', '3', '31783', 'R');
INSERT INTO `cms_region` VALUES ('31792', '福山镇', '3', '31783', 'F');
INSERT INTO `cms_region` VALUES ('31793', '桥头镇', '3', '31783', 'Q');
INSERT INTO `cms_region` VALUES ('31794', '国营红光农场', '3', '31783', 'G');
INSERT INTO `cms_region` VALUES ('31795', '国营红岗农场', '3', '31783', 'G');
INSERT INTO `cms_region` VALUES ('31796', '国营西达农场', '3', '31783', 'G');
INSERT INTO `cms_region` VALUES ('31797', '国营昆仑农场', '3', '31783', 'G');
INSERT INTO `cms_region` VALUES ('31798', '国营和岭农场', '3', '31783', 'G');
INSERT INTO `cms_region` VALUES ('31799', '国营金安农场', '3', '31783', 'G');
INSERT INTO `cms_region` VALUES ('31800', '澄迈县华侨农场', '3', '31783', 'C');
INSERT INTO `cms_region` VALUES ('31801', '临高县', '2', '31563', 'L');
INSERT INTO `cms_region` VALUES ('31802', '临城镇', '3', '31801', 'L');
INSERT INTO `cms_region` VALUES ('31803', '波莲镇', '3', '31801', 'B');
INSERT INTO `cms_region` VALUES ('31804', '东英镇', '3', '31801', 'D');
INSERT INTO `cms_region` VALUES ('31805', '博厚镇', '3', '31801', 'B');
INSERT INTO `cms_region` VALUES ('31806', '皇桐镇', '3', '31801', 'H');
INSERT INTO `cms_region` VALUES ('31807', '多文镇', '3', '31801', 'D');
INSERT INTO `cms_region` VALUES ('31808', '和舍镇', '3', '31801', 'H');
INSERT INTO `cms_region` VALUES ('31809', '南宝镇', '3', '31801', 'N');
INSERT INTO `cms_region` VALUES ('31810', '新盈镇', '3', '31801', 'X');
INSERT INTO `cms_region` VALUES ('31811', '调楼镇', '3', '31801', 'D');
INSERT INTO `cms_region` VALUES ('31812', '国营红华农场', '3', '31801', 'G');
INSERT INTO `cms_region` VALUES ('31813', '国营加来农场', '3', '31801', 'G');
INSERT INTO `cms_region` VALUES ('31814', '白沙县', '2', '31563', 'B');
INSERT INTO `cms_region` VALUES ('31815', '牙叉镇', '3', '31814', 'Y');
INSERT INTO `cms_region` VALUES ('31816', '七坊镇', '3', '31814', 'Q');
INSERT INTO `cms_region` VALUES ('31817', '邦溪镇', '3', '31814', 'B');
INSERT INTO `cms_region` VALUES ('31818', '打安镇', '3', '31814', 'D');
INSERT INTO `cms_region` VALUES ('31819', '细水乡', '3', '31814', 'X');
INSERT INTO `cms_region` VALUES ('31820', '元门乡', '3', '31814', 'Y');
INSERT INTO `cms_region` VALUES ('31821', '南开乡', '3', '31814', 'N');
INSERT INTO `cms_region` VALUES ('31822', '阜龙乡', '3', '31814', 'F');
INSERT INTO `cms_region` VALUES ('31823', '青松乡', '3', '31814', 'Q');
INSERT INTO `cms_region` VALUES ('31824', '金波乡', '3', '31814', 'J');
INSERT INTO `cms_region` VALUES ('31825', '荣邦乡', '3', '31814', 'R');
INSERT INTO `cms_region` VALUES ('31826', '国营金波农场', '3', '31814', 'G');
INSERT INTO `cms_region` VALUES ('31827', '国营白沙农场', '3', '31814', 'G');
INSERT INTO `cms_region` VALUES ('31828', '国营牙叉农场', '3', '31814', 'G');
INSERT INTO `cms_region` VALUES ('31829', '国营卫星农场', '3', '31814', 'G');
INSERT INTO `cms_region` VALUES ('31830', '国营龙江农场', '3', '31814', 'G');
INSERT INTO `cms_region` VALUES ('31831', '国营珠碧江农场', '3', '31814', 'G');
INSERT INTO `cms_region` VALUES ('31832', '国营芙蓉田农场', '3', '31814', 'G');
INSERT INTO `cms_region` VALUES ('31833', '国营大岭农场', '3', '31814', 'G');
INSERT INTO `cms_region` VALUES ('31834', '国营邦溪农场', '3', '31814', 'G');
INSERT INTO `cms_region` VALUES ('31835', '昌江县', '2', '31563', 'C');
INSERT INTO `cms_region` VALUES ('31836', '石碌镇', '3', '31835', 'S');
INSERT INTO `cms_region` VALUES ('31837', '叉河镇', '3', '31835', 'C');
INSERT INTO `cms_region` VALUES ('31838', '十月田镇', '3', '31835', 'S');
INSERT INTO `cms_region` VALUES ('31839', '乌烈镇', '3', '31835', 'W');
INSERT INTO `cms_region` VALUES ('31840', '昌化镇', '3', '31835', 'C');
INSERT INTO `cms_region` VALUES ('31841', '海尾镇', '3', '31835', 'H');
INSERT INTO `cms_region` VALUES ('31842', '七叉镇', '3', '31835', 'Q');
INSERT INTO `cms_region` VALUES ('31843', '王下乡', '3', '31835', 'W');
INSERT INTO `cms_region` VALUES ('31844', '国营红田农场', '3', '31835', 'G');
INSERT INTO `cms_region` VALUES ('31845', '国营红林农场', '3', '31835', 'G');
INSERT INTO `cms_region` VALUES ('31846', '国营坝王岭林场', '3', '31835', 'G');
INSERT INTO `cms_region` VALUES ('31847', '海南钢铁公司', '3', '31835', 'H');
INSERT INTO `cms_region` VALUES ('31848', '乐东县', '2', '31563', 'L');
INSERT INTO `cms_region` VALUES ('31849', '抱由镇', '3', '31848', 'B');
INSERT INTO `cms_region` VALUES ('31850', '万冲镇', '3', '31848', 'W');
INSERT INTO `cms_region` VALUES ('31851', '大安镇', '3', '31848', 'D');
INSERT INTO `cms_region` VALUES ('31852', '志仲镇', '3', '31848', 'Z');
INSERT INTO `cms_region` VALUES ('31853', '千家镇', '3', '31848', 'Q');
INSERT INTO `cms_region` VALUES ('31854', '九所镇', '3', '31848', 'J');
INSERT INTO `cms_region` VALUES ('31855', '利国镇', '3', '31848', 'L');
INSERT INTO `cms_region` VALUES ('31856', '黄流镇', '3', '31848', 'H');
INSERT INTO `cms_region` VALUES ('31857', '佛罗镇', '3', '31848', 'F');
INSERT INTO `cms_region` VALUES ('31858', '尖峰镇', '3', '31848', 'J');
INSERT INTO `cms_region` VALUES ('31859', '莺歌海镇', '3', '31848', 'Y');
INSERT INTO `cms_region` VALUES ('31860', '国营乐中农场', '3', '31848', 'G');
INSERT INTO `cms_region` VALUES ('31861', '国营山荣农场', '3', '31848', 'G');
INSERT INTO `cms_region` VALUES ('31862', '国营乐光农场', '3', '31848', 'G');
INSERT INTO `cms_region` VALUES ('31863', '国营报伦农场', '3', '31848', 'G');
INSERT INTO `cms_region` VALUES ('31864', '国营福报农场', '3', '31848', 'G');
INSERT INTO `cms_region` VALUES ('31865', '国营保国农场', '3', '31848', 'G');
INSERT INTO `cms_region` VALUES ('31866', '国营保显农场', '3', '31848', 'G');
INSERT INTO `cms_region` VALUES ('31867', '国营尖峰岭林业公司', '3', '31848', 'G');
INSERT INTO `cms_region` VALUES ('31868', '国营莺歌海盐场', '3', '31848', 'G');
INSERT INTO `cms_region` VALUES ('31869', '陵水县', '2', '31563', 'L');
INSERT INTO `cms_region` VALUES ('31870', '椰林镇', '3', '31869', 'Y');
INSERT INTO `cms_region` VALUES ('31871', '光坡镇', '3', '31869', 'G');
INSERT INTO `cms_region` VALUES ('31872', '三才镇', '3', '31869', 'S');
INSERT INTO `cms_region` VALUES ('31873', '英州镇', '3', '31869', 'Y');
INSERT INTO `cms_region` VALUES ('31874', '隆广镇', '3', '31869', 'L');
INSERT INTO `cms_region` VALUES ('31875', '文罗镇', '3', '31869', 'W');
INSERT INTO `cms_region` VALUES ('31876', '本号镇', '3', '31869', 'B');
INSERT INTO `cms_region` VALUES ('31877', '新村镇', '3', '31869', 'X');
INSERT INTO `cms_region` VALUES ('31878', '黎安镇', '3', '31869', 'L');
INSERT INTO `cms_region` VALUES ('31879', '提蒙乡', '3', '31869', 'T');
INSERT INTO `cms_region` VALUES ('31880', '群英乡', '3', '31869', 'Q');
INSERT INTO `cms_region` VALUES ('31881', '国营岭门农场', '3', '31869', 'G');
INSERT INTO `cms_region` VALUES ('31882', '国营南平农场', '3', '31869', 'G');
INSERT INTO `cms_region` VALUES ('31883', '国营吊罗山林业公司', '3', '31869', 'G');
INSERT INTO `cms_region` VALUES ('31884', '保亭县', '2', '31563', 'B');
INSERT INTO `cms_region` VALUES ('31885', '保城镇', '3', '31884', 'B');
INSERT INTO `cms_region` VALUES ('31886', '什玲镇', '3', '31884', 'S');
INSERT INTO `cms_region` VALUES ('31887', '加茂镇', '3', '31884', 'J');
INSERT INTO `cms_region` VALUES ('31888', '响水镇', '3', '31884', 'X');
INSERT INTO `cms_region` VALUES ('31889', '新政镇', '3', '31884', 'X');
INSERT INTO `cms_region` VALUES ('31890', '三道镇', '3', '31884', 'S');
INSERT INTO `cms_region` VALUES ('31891', '六弓乡', '3', '31884', 'L');
INSERT INTO `cms_region` VALUES ('31892', '南林乡', '3', '31884', 'N');
INSERT INTO `cms_region` VALUES ('31893', '毛感乡', '3', '31884', 'M');
INSERT INTO `cms_region` VALUES ('31894', '国营五指山茶场', '3', '31884', 'G');
INSERT INTO `cms_region` VALUES ('31895', '国营新星农场', '3', '31884', 'G');
INSERT INTO `cms_region` VALUES ('31896', '国营保亭热作所', '3', '31884', 'G');
INSERT INTO `cms_region` VALUES ('31897', '国营金江农场', '3', '31884', 'G');
INSERT INTO `cms_region` VALUES ('31898', '国营南茂农场', '3', '31884', 'G');
INSERT INTO `cms_region` VALUES ('31899', '国营三道农场', '3', '31884', 'G');
INSERT INTO `cms_region` VALUES ('31900', '琼中县', '2', '31563', 'Q');
INSERT INTO `cms_region` VALUES ('31901', '营根镇', '3', '31900', 'Y');
INSERT INTO `cms_region` VALUES ('31902', '湾岭镇', '3', '31900', 'W');
INSERT INTO `cms_region` VALUES ('31903', '黎母山镇', '3', '31900', 'L');
INSERT INTO `cms_region` VALUES ('31904', '和平镇', '3', '31900', 'H');
INSERT INTO `cms_region` VALUES ('31905', '长征镇', '3', '31900', 'C');
INSERT INTO `cms_region` VALUES ('31906', '红毛镇', '3', '31900', 'H');
INSERT INTO `cms_region` VALUES ('31907', '中平镇', '3', '31900', 'Z');
INSERT INTO `cms_region` VALUES ('31908', '吊罗山乡', '3', '31900', 'D');
INSERT INTO `cms_region` VALUES ('31909', '上安乡', '3', '31900', 'S');
INSERT INTO `cms_region` VALUES ('31910', '什运乡', '3', '31900', 'S');
INSERT INTO `cms_region` VALUES ('31911', '国营新进农场', '3', '31900', 'G');
INSERT INTO `cms_region` VALUES ('31912', '国营大丰农场', '3', '31900', 'G');
INSERT INTO `cms_region` VALUES ('31913', '国营阳江农场', '3', '31900', 'G');
INSERT INTO `cms_region` VALUES ('31914', '国营乌石农场', '3', '31900', 'G');
INSERT INTO `cms_region` VALUES ('31915', '国营南方农场', '3', '31900', 'G');
INSERT INTO `cms_region` VALUES ('31916', '国营岭头农场', '3', '31900', 'G');
INSERT INTO `cms_region` VALUES ('31917', '国营加钗农场', '3', '31900', 'G');
INSERT INTO `cms_region` VALUES ('31918', '国营长征农场', '3', '31900', 'G');
INSERT INTO `cms_region` VALUES ('31919', '国营乘坡农场', '3', '31900', 'G');
INSERT INTO `cms_region` VALUES ('31920', '国营太平农场', '3', '31900', 'G');
INSERT INTO `cms_region` VALUES ('31921', '国营新伟农场', '3', '31900', 'G');
INSERT INTO `cms_region` VALUES ('31922', '国营黎母山林业公司', '3', '31900', 'G');
INSERT INTO `cms_region` VALUES ('31923', '西沙群岛', '3', '31924', 'X');
INSERT INTO `cms_region` VALUES ('31924', '三沙市', '2', '31563', 'S');
INSERT INTO `cms_region` VALUES ('31925', '南沙群岛', '3', '31924', 'N');
INSERT INTO `cms_region` VALUES ('47499', '香港岛', '2', '47494', 'X');
INSERT INTO `cms_region` VALUES ('31927', '中沙群岛的岛礁及其海域', '3', '31924', 'Z');
INSERT INTO `cms_region` VALUES ('47498', '九龙', '2', '47494', 'J');
INSERT INTO `cms_region` VALUES ('31929', '重庆市', '1', '0', 'Z');
INSERT INTO `cms_region` VALUES ('31930', '重庆市', '2', '31929', 'C');
INSERT INTO `cms_region` VALUES ('31931', '万州区', '3', '31930', 'W');
INSERT INTO `cms_region` VALUES ('31984', '涪陵区', '3', '31930', 'F');
INSERT INTO `cms_region` VALUES ('32031', '渝中区', '3', '31930', 'Y');
INSERT INTO `cms_region` VALUES ('32044', '大渡口区', '3', '31930', 'D');
INSERT INTO `cms_region` VALUES ('32053', '江北区', '3', '31930', 'J');
INSERT INTO `cms_region` VALUES ('32066', '沙坪坝区', '3', '31930', 'S');
INSERT INTO `cms_region` VALUES ('32093', '九龙坡区', '3', '31930', 'J');
INSERT INTO `cms_region` VALUES ('32112', '南岸区', '3', '31930', 'N');
INSERT INTO `cms_region` VALUES ('32127', '北碚区', '3', '31930', 'B');
INSERT INTO `cms_region` VALUES ('32145', '万盛区', '3', '31930', 'W');
INSERT INTO `cms_region` VALUES ('32156', '双桥区', '3', '31930', 'S');
INSERT INTO `cms_region` VALUES ('32160', '渝北区', '3', '31930', 'Y');
INSERT INTO `cms_region` VALUES ('32191', '巴南区', '3', '31930', 'B');
INSERT INTO `cms_region` VALUES ('32213', '黔江区', '3', '31930', 'Q');
INSERT INTO `cms_region` VALUES ('32244', '长寿区', '3', '31930', 'C');
INSERT INTO `cms_region` VALUES ('32263', '江津区', '3', '31930', 'J');
INSERT INTO `cms_region` VALUES ('32291', '合川区', '3', '31930', 'H');
INSERT INTO `cms_region` VALUES ('32322', '永川区', '3', '31930', 'Y');
INSERT INTO `cms_region` VALUES ('32345', '南川区', '3', '31930', 'N');
INSERT INTO `cms_region` VALUES ('32380', '县', '2', '31929', 'X');
INSERT INTO `cms_region` VALUES ('32381', '綦江县', '3', '32380', 'Q');
INSERT INTO `cms_region` VALUES ('32401', '潼南县', '3', '32380', 'T');
INSERT INTO `cms_region` VALUES ('32424', '铜梁县', '3', '32380', 'T');
INSERT INTO `cms_region` VALUES ('32453', '大足县', '3', '32380', 'D');
INSERT INTO `cms_region` VALUES ('32478', '荣昌县', '3', '32380', 'R');
INSERT INTO `cms_region` VALUES ('32499', '璧山县', '3', '32380', 'B');
INSERT INTO `cms_region` VALUES ('32513', '梁平县', '3', '32380', 'L');
INSERT INTO `cms_region` VALUES ('32549', '城口县', '3', '32380', 'C');
INSERT INTO `cms_region` VALUES ('32574', '丰都县', '3', '32380', 'F');
INSERT INTO `cms_region` VALUES ('32606', '垫江县', '3', '32380', 'D');
INSERT INTO `cms_region` VALUES ('32632', '武隆县', '3', '32380', 'W');
INSERT INTO `cms_region` VALUES ('32659', '忠县', '3', '32380', 'Z');
INSERT INTO `cms_region` VALUES ('32688', '开县', '3', '32380', 'K');
INSERT INTO `cms_region` VALUES ('32727', '云阳县', '3', '32380', 'Y');
INSERT INTO `cms_region` VALUES ('32771', '奉节县', '3', '32380', 'F');
INSERT INTO `cms_region` VALUES ('32802', '巫山县', '3', '32380', 'W');
INSERT INTO `cms_region` VALUES ('32829', '巫溪县', '3', '32380', 'W');
INSERT INTO `cms_region` VALUES ('32861', '石柱县', '3', '32380', 'S');
INSERT INTO `cms_region` VALUES ('32894', '秀山县', '3', '32380', 'X');
INSERT INTO `cms_region` VALUES ('32927', '酉阳县', '3', '32380', 'Y');
INSERT INTO `cms_region` VALUES ('32967', '彭水县', '3', '32380', 'P');
INSERT INTO `cms_region` VALUES ('33007', '四川省', '1', '0', 'S');
INSERT INTO `cms_region` VALUES ('33008', '成都市', '2', '33007', 'C');
INSERT INTO `cms_region` VALUES ('33009', '市辖区', '3', '33008', 'S');
INSERT INTO `cms_region` VALUES ('33010', '锦江区', '3', '33008', 'J');
INSERT INTO `cms_region` VALUES ('33027', '青羊区', '3', '33008', 'Q');
INSERT INTO `cms_region` VALUES ('33042', '金牛区', '3', '33008', 'J');
INSERT INTO `cms_region` VALUES ('33058', '武侯区', '3', '33008', 'W');
INSERT INTO `cms_region` VALUES ('33076', '成华区', '3', '33008', 'C');
INSERT INTO `cms_region` VALUES ('33091', '龙泉驿区', '3', '33008', 'L');
INSERT INTO `cms_region` VALUES ('33104', '青白江区', '3', '33008', 'Q');
INSERT INTO `cms_region` VALUES ('33116', '新都区', '3', '33008', 'X');
INSERT INTO `cms_region` VALUES ('33130', '温江区', '3', '33008', 'W');
INSERT INTO `cms_region` VALUES ('33141', '金堂县', '3', '33008', 'J');
INSERT INTO `cms_region` VALUES ('33163', '双流县', '3', '33008', 'S');
INSERT INTO `cms_region` VALUES ('33189', '郫县', '3', '33008', 'P');
INSERT INTO `cms_region` VALUES ('33205', '大邑县', '3', '33008', 'D');
INSERT INTO `cms_region` VALUES ('33226', '蒲江县', '3', '33008', 'P');
INSERT INTO `cms_region` VALUES ('33239', '新津县', '3', '33008', 'X');
INSERT INTO `cms_region` VALUES ('33252', '都江堰市', '3', '33008', 'D');
INSERT INTO `cms_region` VALUES ('33272', '彭州市', '3', '33008', 'P');
INSERT INTO `cms_region` VALUES ('33293', '邛崃市', '3', '33008', 'Q');
INSERT INTO `cms_region` VALUES ('33318', '崇州市', '3', '33008', 'C');
INSERT INTO `cms_region` VALUES ('33344', '自贡市', '2', '33007', 'Z');
INSERT INTO `cms_region` VALUES ('33345', '市辖区', '3', '33344', 'S');
INSERT INTO `cms_region` VALUES ('33346', '自流井区', '3', '33344', 'Z');
INSERT INTO `cms_region` VALUES ('33360', '贡井区', '3', '33344', 'G');
INSERT INTO `cms_region` VALUES ('33374', '大安区', '3', '33344', 'D');
INSERT INTO `cms_region` VALUES ('33391', '沿滩区', '3', '33344', 'Y');
INSERT INTO `cms_region` VALUES ('33405', '荣县', '3', '33344', 'R');
INSERT INTO `cms_region` VALUES ('33433', '富顺县', '3', '33344', 'F');
INSERT INTO `cms_region` VALUES ('33460', '攀枝花市', '2', '33007', 'P');
INSERT INTO `cms_region` VALUES ('33461', '市辖区', '3', '33460', 'S');
INSERT INTO `cms_region` VALUES ('33462', '攀枝花东区', '3', '33460', 'P');
INSERT INTO `cms_region` VALUES ('33473', '西区', '3', '33460', 'X');
INSERT INTO `cms_region` VALUES ('33481', '仁和区', '3', '33460', 'R');
INSERT INTO `cms_region` VALUES ('33497', '米易县', '3', '33460', 'M');
INSERT INTO `cms_region` VALUES ('33511', '盐边县', '3', '33460', 'Y');
INSERT INTO `cms_region` VALUES ('33528', '泸州市', '2', '33007', 'L');
INSERT INTO `cms_region` VALUES ('33529', '市辖区', '3', '33528', 'S');
INSERT INTO `cms_region` VALUES ('33530', '江阳区', '3', '33528', 'J');
INSERT INTO `cms_region` VALUES ('33548', '纳溪区', '3', '33528', 'N');
INSERT INTO `cms_region` VALUES ('33563', '龙马潭区', '3', '33528', 'L');
INSERT INTO `cms_region` VALUES ('33577', '泸县', '3', '33528', 'L');
INSERT INTO `cms_region` VALUES ('33597', '合江县', '3', '33528', 'H');
INSERT INTO `cms_region` VALUES ('33625', '叙永县', '3', '33528', 'X');
INSERT INTO `cms_region` VALUES ('33654', '古蔺县', '3', '33528', 'G');
INSERT INTO `cms_region` VALUES ('33681', '德阳市', '2', '33007', 'D');
INSERT INTO `cms_region` VALUES ('33682', '市辖区', '3', '33681', 'S');
INSERT INTO `cms_region` VALUES ('33683', '旌阳区', '3', '33681', 'J');
INSERT INTO `cms_region` VALUES ('33701', '中江县', '3', '33681', 'Z');
INSERT INTO `cms_region` VALUES ('33747', '罗江县', '3', '33681', 'L');
INSERT INTO `cms_region` VALUES ('33758', '广汉市', '3', '33681', 'G');
INSERT INTO `cms_region` VALUES ('33778', '什邡市', '3', '33681', 'S');
INSERT INTO `cms_region` VALUES ('33795', '绵竹市', '3', '33681', 'M');
INSERT INTO `cms_region` VALUES ('33817', '绵阳市', '2', '33007', 'M');
INSERT INTO `cms_region` VALUES ('33818', '市辖区', '3', '33817', 'S');
INSERT INTO `cms_region` VALUES ('33819', '涪城区', '3', '33817', 'F');
INSERT INTO `cms_region` VALUES ('33844', '游仙区', '3', '33817', 'Y');
INSERT INTO `cms_region` VALUES ('33873', '三台县', '3', '33817', 'S');
INSERT INTO `cms_region` VALUES ('33937', '盐亭县', '3', '33817', 'Y');
INSERT INTO `cms_region` VALUES ('33974', '安县', '3', '33817', 'A');
INSERT INTO `cms_region` VALUES ('33995', '梓潼县', '3', '33817', 'Z');
INSERT INTO `cms_region` VALUES ('34028', '北川县', '3', '33817', 'B');
INSERT INTO `cms_region` VALUES ('34049', '平武县', '3', '33817', 'P');
INSERT INTO `cms_region` VALUES ('34075', '江油市', '3', '33817', 'J');
INSERT INTO `cms_region` VALUES ('34120', '广元市', '2', '33007', 'G');
INSERT INTO `cms_region` VALUES ('34121', '市辖区', '3', '34120', 'S');
INSERT INTO `cms_region` VALUES ('34122', '市中区', '3', '34120', 'S');
INSERT INTO `cms_region` VALUES ('34143', '元坝区', '3', '34120', 'Y');
INSERT INTO `cms_region` VALUES ('34173', '朝天区', '3', '34120', 'C');
INSERT INTO `cms_region` VALUES ('34199', '旺苍县', '3', '34120', 'W');
INSERT INTO `cms_region` VALUES ('34238', '青川县', '3', '34120', 'Q');
INSERT INTO `cms_region` VALUES ('34276', '剑阁县', '3', '34120', 'J');
INSERT INTO `cms_region` VALUES ('34334', '苍溪县', '3', '34120', 'C');
INSERT INTO `cms_region` VALUES ('34376', '遂宁市', '2', '33007', 'S');
INSERT INTO `cms_region` VALUES ('34377', '市辖区', '3', '34376', 'S');
INSERT INTO `cms_region` VALUES ('34378', '船山区', '3', '34376', 'C');
INSERT INTO `cms_region` VALUES ('34404', '安居区', '3', '34376', 'A');
INSERT INTO `cms_region` VALUES ('34426', '蓬溪县', '3', '34376', 'P');
INSERT INTO `cms_region` VALUES ('34458', '射洪县', '3', '34376', 'S');
INSERT INTO `cms_region` VALUES ('34489', '大英县', '3', '34376', 'D');
INSERT INTO `cms_region` VALUES ('34501', '内江市', '2', '33007', 'N');
INSERT INTO `cms_region` VALUES ('34502', '市辖区', '3', '34501', 'S');
INSERT INTO `cms_region` VALUES ('34503', '市中区', '3', '34501', 'S');
INSERT INTO `cms_region` VALUES ('34524', '东兴区', '3', '34501', 'D');
INSERT INTO `cms_region` VALUES ('34554', '威远县', '3', '34501', 'W');
INSERT INTO `cms_region` VALUES ('34575', '资中县', '3', '34501', 'Z');
INSERT INTO `cms_region` VALUES ('34609', '隆昌县', '3', '34501', 'L');
INSERT INTO `cms_region` VALUES ('34628', '乐山市', '2', '33007', 'L');
INSERT INTO `cms_region` VALUES ('34629', '市辖区', '3', '34628', 'S');
INSERT INTO `cms_region` VALUES ('34630', '市中区', '3', '34628', 'S');
INSERT INTO `cms_region` VALUES ('34661', '沙湾区', '3', '34628', 'S');
INSERT INTO `cms_region` VALUES ('34676', '五通桥区', '3', '34628', 'W');
INSERT INTO `cms_region` VALUES ('34689', '金口河区', '3', '34628', 'J');
INSERT INTO `cms_region` VALUES ('34696', '犍为县', '3', '34628', 'Q');
INSERT INTO `cms_region` VALUES ('34727', '井研县', '3', '34628', 'J');
INSERT INTO `cms_region` VALUES ('34755', '夹江县', '3', '34628', 'J');
INSERT INTO `cms_region` VALUES ('34778', '沐川县', '3', '34628', 'M');
INSERT INTO `cms_region` VALUES ('34799', '峨边县', '3', '34628', 'E');
INSERT INTO `cms_region` VALUES ('34819', '马边县', '3', '34628', 'M');
INSERT INTO `cms_region` VALUES ('34840', '峨眉山市', '3', '34628', 'E');
INSERT INTO `cms_region` VALUES ('34859', '南充市', '2', '33007', 'N');
INSERT INTO `cms_region` VALUES ('34860', '市辖区', '3', '34859', 'S');
INSERT INTO `cms_region` VALUES ('34861', '顺庆区', '3', '34859', 'S');
INSERT INTO `cms_region` VALUES ('34890', '高坪区', '3', '34859', 'G');
INSERT INTO `cms_region` VALUES ('34923', '嘉陵区', '3', '34859', 'J');
INSERT INTO `cms_region` VALUES ('34967', '南部县', '3', '34859', 'N');
INSERT INTO `cms_region` VALUES ('35040', '营山县', '3', '34859', 'Y');
INSERT INTO `cms_region` VALUES ('35094', '蓬安县', '3', '34859', 'P');
INSERT INTO `cms_region` VALUES ('35134', '仪陇县', '3', '34859', 'Y');
INSERT INTO `cms_region` VALUES ('35193', '西充县', '3', '34859', 'X');
INSERT INTO `cms_region` VALUES ('35238', '阆中市', '3', '34859', 'L');
INSERT INTO `cms_region` VALUES ('35288', '眉山市', '2', '33007', 'M');
INSERT INTO `cms_region` VALUES ('35289', '市辖区', '3', '35288', 'S');
INSERT INTO `cms_region` VALUES ('35290', '东坡区', '3', '35288', 'D');
INSERT INTO `cms_region` VALUES ('35317', '仁寿县', '3', '35288', 'R');
INSERT INTO `cms_region` VALUES ('35378', '彭山县', '3', '35288', 'P');
INSERT INTO `cms_region` VALUES ('35392', '洪雅县', '3', '35288', 'H');
INSERT INTO `cms_region` VALUES ('35408', '丹棱县', '3', '35288', 'D');
INSERT INTO `cms_region` VALUES ('35416', '青神县', '3', '35288', 'Q');
INSERT INTO `cms_region` VALUES ('35427', '宜宾市', '2', '33007', 'Y');
INSERT INTO `cms_region` VALUES ('35428', '市辖区', '3', '35427', 'S');
INSERT INTO `cms_region` VALUES ('35429', '翠屏区', '3', '35427', 'C');
INSERT INTO `cms_region` VALUES ('35454', '宜宾县', '3', '35427', 'Y');
INSERT INTO `cms_region` VALUES ('35481', '南溪县', '3', '35427', 'N');
INSERT INTO `cms_region` VALUES ('35497', '江安县', '3', '35427', 'J');
INSERT INTO `cms_region` VALUES ('35516', '长宁县', '3', '35427', 'C');
INSERT INTO `cms_region` VALUES ('35535', '高县', '3', '35427', 'G');
INSERT INTO `cms_region` VALUES ('35555', '珙县', '3', '35427', 'G');
INSERT INTO `cms_region` VALUES ('35573', '筠连县', '3', '35427', 'J');
INSERT INTO `cms_region` VALUES ('35592', '兴文县', '3', '35427', 'X');
INSERT INTO `cms_region` VALUES ('35608', '屏山县', '3', '35427', 'P');
INSERT INTO `cms_region` VALUES ('35625', '广安市', '2', '33007', 'G');
INSERT INTO `cms_region` VALUES ('35626', '市辖区', '3', '35625', 'S');
INSERT INTO `cms_region` VALUES ('35627', '广安区', '3', '35625', 'G');
INSERT INTO `cms_region` VALUES ('35677', '岳池县', '3', '35625', 'Y');
INSERT INTO `cms_region` VALUES ('35721', '武胜县', '3', '35625', 'W');
INSERT INTO `cms_region` VALUES ('35753', '邻水县', '3', '35625', 'L');
INSERT INTO `cms_region` VALUES ('35799', '华蓥市', '3', '35625', 'H');
INSERT INTO `cms_region` VALUES ('35813', '达州市', '2', '33007', 'D');
INSERT INTO `cms_region` VALUES ('35814', '市辖区', '3', '35813', 'S');
INSERT INTO `cms_region` VALUES ('35815', '通川区', '3', '35813', 'T');
INSERT INTO `cms_region` VALUES ('35829', '达县', '3', '35813', 'D');
INSERT INTO `cms_region` VALUES ('35894', '宣汉县', '3', '35813', 'X');
INSERT INTO `cms_region` VALUES ('35949', '开江县', '3', '35813', 'K');
INSERT INTO `cms_region` VALUES ('35970', '大竹县', '3', '35813', 'D');
INSERT INTO `cms_region` VALUES ('36021', '渠县', '3', '35813', 'Q');
INSERT INTO `cms_region` VALUES ('36082', '万源市', '3', '35813', 'W');
INSERT INTO `cms_region` VALUES ('36136', '雅安市', '2', '33007', 'Y');
INSERT INTO `cms_region` VALUES ('36137', '市辖区', '3', '36136', 'S');
INSERT INTO `cms_region` VALUES ('36138', '雨城区', '3', '36136', 'Y');
INSERT INTO `cms_region` VALUES ('36161', '名山县', '3', '36136', 'M');
INSERT INTO `cms_region` VALUES ('36182', '荥经县', '3', '36136', 'Y');
INSERT INTO `cms_region` VALUES ('36204', '汉源县', '3', '36136', 'H');
INSERT INTO `cms_region` VALUES ('36245', '石棉县', '3', '36136', 'S');
INSERT INTO `cms_region` VALUES ('36263', '天全县', '3', '36136', 'T');
INSERT INTO `cms_region` VALUES ('36279', '芦山县', '3', '36136', 'L');
INSERT INTO `cms_region` VALUES ('36289', '宝兴县', '3', '36136', 'B');
INSERT INTO `cms_region` VALUES ('36299', '巴中市', '2', '33007', 'B');
INSERT INTO `cms_region` VALUES ('36300', '市辖区', '3', '36299', 'S');
INSERT INTO `cms_region` VALUES ('36301', '巴州区', '3', '36299', 'B');
INSERT INTO `cms_region` VALUES ('36354', '通江县', '3', '36299', 'T');
INSERT INTO `cms_region` VALUES ('36404', '南江县', '3', '36299', 'N');
INSERT INTO `cms_region` VALUES ('36453', '平昌县', '3', '36299', 'P');
INSERT INTO `cms_region` VALUES ('36497', '资阳市', '2', '33007', 'Z');
INSERT INTO `cms_region` VALUES ('36498', '市辖区', '3', '36497', 'S');
INSERT INTO `cms_region` VALUES ('36499', '雁江区', '3', '36497', 'Y');
INSERT INTO `cms_region` VALUES ('36527', '安岳县', '3', '36497', 'A');
INSERT INTO `cms_region` VALUES ('36597', '乐至县', '3', '36497', 'L');
INSERT INTO `cms_region` VALUES ('36623', '简阳市', '3', '36497', 'J');
INSERT INTO `cms_region` VALUES ('36679', '阿坝州', '2', '33007', 'A');
INSERT INTO `cms_region` VALUES ('36680', '汶川县', '3', '36679', 'W');
INSERT INTO `cms_region` VALUES ('36694', '理县', '3', '36679', 'L');
INSERT INTO `cms_region` VALUES ('36708', '茂县', '3', '36679', 'M');
INSERT INTO `cms_region` VALUES ('36731', '松潘县', '3', '36679', 'S');
INSERT INTO `cms_region` VALUES ('36759', '九寨沟县', '3', '36679', 'J');
INSERT INTO `cms_region` VALUES ('36778', '金川县', '3', '36679', 'J');
INSERT INTO `cms_region` VALUES ('36802', '小金县', '3', '36679', 'X');
INSERT INTO `cms_region` VALUES ('36824', '黑水县', '3', '36679', 'H');
INSERT INTO `cms_region` VALUES ('36842', '马尔康县', '3', '36679', 'M');
INSERT INTO `cms_region` VALUES ('36858', '壤塘县', '3', '36679', 'R');
INSERT INTO `cms_region` VALUES ('36871', '阿坝县', '3', '36679', 'A');
INSERT INTO `cms_region` VALUES ('36893', '若尔盖县', '3', '36679', 'R');
INSERT INTO `cms_region` VALUES ('36914', '红原县', '3', '36679', 'H');
INSERT INTO `cms_region` VALUES ('36926', '甘孜州', '2', '33007', 'G');
INSERT INTO `cms_region` VALUES ('36927', '康定县', '3', '36926', 'K');
INSERT INTO `cms_region` VALUES ('36949', '泸定县', '3', '36926', 'L');
INSERT INTO `cms_region` VALUES ('36962', '丹巴县', '3', '36926', 'D');
INSERT INTO `cms_region` VALUES ('36978', '九龙县', '3', '36926', 'J');
INSERT INTO `cms_region` VALUES ('36997', '雅江县', '3', '36926', 'Y');
INSERT INTO `cms_region` VALUES ('37015', '道孚县', '3', '36926', 'D');
INSERT INTO `cms_region` VALUES ('37038', '炉霍县', '3', '36926', 'L');
INSERT INTO `cms_region` VALUES ('37055', '甘孜县', '3', '36926', 'G');
INSERT INTO `cms_region` VALUES ('37078', '新龙县', '3', '36926', 'X');
INSERT INTO `cms_region` VALUES ('37098', '德格县', '3', '36926', 'D');
INSERT INTO `cms_region` VALUES ('37125', '白玉县', '3', '36926', 'B');
INSERT INTO `cms_region` VALUES ('37143', '石渠县', '3', '36926', 'S');
INSERT INTO `cms_region` VALUES ('37166', '色达县', '3', '36926', 'S');
INSERT INTO `cms_region` VALUES ('37184', '理塘县', '3', '36926', 'L');
INSERT INTO `cms_region` VALUES ('37209', '巴塘县', '3', '36926', 'B');
INSERT INTO `cms_region` VALUES ('37229', '乡城县', '3', '36926', 'X');
INSERT INTO `cms_region` VALUES ('37242', '稻城县', '3', '36926', 'D');
INSERT INTO `cms_region` VALUES ('37257', '得荣县', '3', '36926', 'D');
INSERT INTO `cms_region` VALUES ('37270', '凉山州', '2', '33007', 'L');
INSERT INTO `cms_region` VALUES ('37271', '西昌市', '3', '37270', 'X');
INSERT INTO `cms_region` VALUES ('37315', '木里县', '3', '37270', 'M');
INSERT INTO `cms_region` VALUES ('37345', '盐源县', '3', '37270', 'Y');
INSERT INTO `cms_region` VALUES ('37380', '德昌', '3', '37270', 'D');
INSERT INTO `cms_region` VALUES ('37404', '会理县', '3', '37270', 'H');
INSERT INTO `cms_region` VALUES ('37455', '会东县', '3', '37270', 'H');
INSERT INTO `cms_region` VALUES ('37509', '宁南县', '3', '37270', 'N');
INSERT INTO `cms_region` VALUES ('37535', '普格县', '3', '37270', 'P');
INSERT INTO `cms_region` VALUES ('37570', '布拖县', '3', '37270', 'B');
INSERT INTO `cms_region` VALUES ('37601', '金阳县', '3', '37270', 'J');
INSERT INTO `cms_region` VALUES ('37636', '昭觉县', '3', '37270', 'Z');
INSERT INTO `cms_region` VALUES ('37684', '喜德县', '3', '37270', 'X');
INSERT INTO `cms_region` VALUES ('37709', '冕宁县', '3', '37270', 'M');
INSERT INTO `cms_region` VALUES ('37748', '越西县', '3', '37270', 'Y');
INSERT INTO `cms_region` VALUES ('37790', '甘洛县', '3', '37270', 'G');
INSERT INTO `cms_region` VALUES ('37819', '美姑县', '3', '37270', 'M');
INSERT INTO `cms_region` VALUES ('37856', '雷波县', '3', '37270', 'L');
INSERT INTO `cms_region` VALUES ('37906', '贵州省', '1', '0', 'G');
INSERT INTO `cms_region` VALUES ('37907', '贵阳市', '2', '37906', 'G');
INSERT INTO `cms_region` VALUES ('37908', '市辖区', '3', '37907', 'S');
INSERT INTO `cms_region` VALUES ('37909', '南明区', '3', '37907', 'N');
INSERT INTO `cms_region` VALUES ('37927', '云岩区', '3', '37907', 'Y');
INSERT INTO `cms_region` VALUES ('37944', '花溪区', '3', '37907', 'H');
INSERT INTO `cms_region` VALUES ('37961', '乌当区', '3', '37907', 'W');
INSERT INTO `cms_region` VALUES ('37977', '白云区', '3', '37907', 'B');
INSERT INTO `cms_region` VALUES ('37987', '小河区', '3', '37907', 'X');
INSERT INTO `cms_region` VALUES ('37991', '开阳县', '3', '37907', 'K');
INSERT INTO `cms_region` VALUES ('38008', '息烽县', '3', '37907', 'X');
INSERT INTO `cms_region` VALUES ('38019', '修文县', '3', '37907', 'X');
INSERT INTO `cms_region` VALUES ('38030', '清镇市', '3', '37907', 'Q');
INSERT INTO `cms_region` VALUES ('38042', '六盘水市', '2', '37906', 'L');
INSERT INTO `cms_region` VALUES ('38043', '钟山区', '3', '38042', 'Z');
INSERT INTO `cms_region` VALUES ('38053', '六枝特区', '3', '38042', 'L');
INSERT INTO `cms_region` VALUES ('38073', '水城县', '3', '38042', 'S');
INSERT INTO `cms_region` VALUES ('38107', '盘县', '3', '38042', 'P');
INSERT INTO `cms_region` VALUES ('38145', '遵义市', '2', '37906', 'Z');
INSERT INTO `cms_region` VALUES ('38146', '市辖区', '3', '38145', 'S');
INSERT INTO `cms_region` VALUES ('38147', '红花岗区', '3', '38145', 'H');
INSERT INTO `cms_region` VALUES ('38164', '汇川区', '3', '38145', 'H');
INSERT INTO `cms_region` VALUES ('38174', '遵义县', '3', '38145', 'Z');
INSERT INTO `cms_region` VALUES ('38206', '桐梓县', '3', '38145', 'T');
INSERT INTO `cms_region` VALUES ('38231', '绥阳县', '3', '38145', 'S');
INSERT INTO `cms_region` VALUES ('38247', '正安县', '3', '38145', 'Z');
INSERT INTO `cms_region` VALUES ('38267', '道真县', '3', '38145', 'D');
INSERT INTO `cms_region` VALUES ('38282', '务川县', '3', '38145', 'W');
INSERT INTO `cms_region` VALUES ('38298', '凤冈县', '3', '38145', 'F');
INSERT INTO `cms_region` VALUES ('38313', '湄潭县', '3', '38145', 'M');
INSERT INTO `cms_region` VALUES ('38329', '余庆县', '3', '38145', 'Y');
INSERT INTO `cms_region` VALUES ('38340', '习水县', '3', '38145', 'X');
INSERT INTO `cms_region` VALUES ('38364', '赤水市', '3', '38145', 'C');
INSERT INTO `cms_region` VALUES ('38382', '仁怀市', '3', '38145', 'R');
INSERT INTO `cms_region` VALUES ('38402', '安顺市', '2', '37906', 'A');
INSERT INTO `cms_region` VALUES ('38403', '市辖区', '3', '38402', 'S');
INSERT INTO `cms_region` VALUES ('38404', '西秀区', '3', '38402', 'X');
INSERT INTO `cms_region` VALUES ('38429', '平坝县', '3', '38402', 'P');
INSERT INTO `cms_region` VALUES ('38440', '普定县', '3', '38402', 'P');
INSERT INTO `cms_region` VALUES ('38452', '镇宁县', '3', '38402', 'Z');
INSERT INTO `cms_region` VALUES ('38469', '关岭县', '3', '38402', 'G');
INSERT INTO `cms_region` VALUES ('38484', '紫云县', '3', '38402', 'Z');
INSERT INTO `cms_region` VALUES ('38497', '铜仁地区', '2', '37906', 'T');
INSERT INTO `cms_region` VALUES ('38498', '铜仁市', '3', '38497', 'T');
INSERT INTO `cms_region` VALUES ('38516', '江口县', '3', '38497', 'J');
INSERT INTO `cms_region` VALUES ('38526', '玉屏县', '3', '38497', 'Y');
INSERT INTO `cms_region` VALUES ('38533', '石阡县', '3', '38497', 'S');
INSERT INTO `cms_region` VALUES ('38552', '思南县　', '3', '38497', 'S');
INSERT INTO `cms_region` VALUES ('38580', '印江县', '3', '38497', 'Y');
INSERT INTO `cms_region` VALUES ('38598', '德江县', '3', '38497', 'D');
INSERT INTO `cms_region` VALUES ('38619', '沿河县', '3', '38497', 'Y');
INSERT INTO `cms_region` VALUES ('38642', '松桃县', '3', '38497', 'S');
INSERT INTO `cms_region` VALUES ('38671', '万山特区', '3', '38497', 'W');
INSERT INTO `cms_region` VALUES ('38677', '黔西南州', '2', '37906', 'Q');
INSERT INTO `cms_region` VALUES ('38678', '兴义市', '3', '38677', 'X');
INSERT INTO `cms_region` VALUES ('38705', '兴仁县', '3', '38677', 'X');
INSERT INTO `cms_region` VALUES ('38722', '普安县', '3', '38677', 'P');
INSERT INTO `cms_region` VALUES ('38737', '晴隆县', '3', '38677', 'Q');
INSERT INTO `cms_region` VALUES ('38752', '贞丰县', '3', '38677', 'Z');
INSERT INTO `cms_region` VALUES ('38766', '望谟县', '3', '38677', 'W');
INSERT INTO `cms_region` VALUES ('38784', '册亨县', '3', '38677', 'C');
INSERT INTO `cms_region` VALUES ('38799', '安龙县', '3', '38677', 'A');
INSERT INTO `cms_region` VALUES ('38816', '毕节地区', '2', '37906', 'B');
INSERT INTO `cms_region` VALUES ('38817', '毕节市', '3', '38816', 'B');
INSERT INTO `cms_region` VALUES ('38859', '大方县', '3', '38816', 'D');
INSERT INTO `cms_region` VALUES ('38896', '黔西县', '3', '38816', 'Q');
INSERT INTO `cms_region` VALUES ('38925', '金沙县', '3', '38816', 'J');
INSERT INTO `cms_region` VALUES ('38952', '织金县', '3', '38816', 'Z');
INSERT INTO `cms_region` VALUES ('38985', '纳雍县', '3', '38816', 'N');
INSERT INTO `cms_region` VALUES ('39011', '威宁县', '3', '38816', 'W');
INSERT INTO `cms_region` VALUES ('39047', '赫章县', '3', '38816', 'H');
INSERT INTO `cms_region` VALUES ('39075', '黔东南州', '2', '37906', 'Q');
INSERT INTO `cms_region` VALUES ('39076', '凯里市', '3', '39075', 'K');
INSERT INTO `cms_region` VALUES ('39092', '黄平县', '3', '39075', 'H');
INSERT INTO `cms_region` VALUES ('39107', '施秉县', '3', '39075', 'S');
INSERT INTO `cms_region` VALUES ('39116', '三穗县', '3', '39075', 'S');
INSERT INTO `cms_region` VALUES ('39126', '镇远县', '3', '39075', 'Z');
INSERT INTO `cms_region` VALUES ('39139', '岑巩县', '3', '39075', 'C');
INSERT INTO `cms_region` VALUES ('39151', '天柱县', '3', '39075', 'T');
INSERT INTO `cms_region` VALUES ('39168', '锦屏县', '3', '39075', 'J');
INSERT INTO `cms_region` VALUES ('39184', '剑河县', '3', '39075', 'J');
INSERT INTO `cms_region` VALUES ('39197', '台江县', '3', '39075', 'T');
INSERT INTO `cms_region` VALUES ('39206', '黎平县', '3', '39075', 'L');
INSERT INTO `cms_region` VALUES ('39232', '榕江县', '3', '39075', 'R');
INSERT INTO `cms_region` VALUES ('39252', '从江县', '3', '39075', 'C');
INSERT INTO `cms_region` VALUES ('39274', '雷山县', '3', '39075', 'L');
INSERT INTO `cms_region` VALUES ('39284', '麻江县', '3', '39075', 'M');
INSERT INTO `cms_region` VALUES ('39294', '丹寨县', '3', '39075', 'D');
INSERT INTO `cms_region` VALUES ('39302', '黔南州', '2', '37906', 'Q');
INSERT INTO `cms_region` VALUES ('39303', '都匀市', '3', '39302', 'D');
INSERT INTO `cms_region` VALUES ('39327', '福泉市', '3', '39302', 'F');
INSERT INTO `cms_region` VALUES ('39345', '荔波县', '3', '39302', 'L');
INSERT INTO `cms_region` VALUES ('39363', '贵定县', '3', '39302', 'G');
INSERT INTO `cms_region` VALUES ('39384', '瓮安县', '3', '39302', 'W');
INSERT INTO `cms_region` VALUES ('39408', '独山县', '3', '39302', 'D');
INSERT INTO `cms_region` VALUES ('39427', '平塘县', '3', '39302', 'P');
INSERT INTO `cms_region` VALUES ('39447', '罗甸县', '3', '39302', 'L');
INSERT INTO `cms_region` VALUES ('39474', '长顺县', '3', '39302', 'C');
INSERT INTO `cms_region` VALUES ('39493', '龙里县', '3', '39302', 'L');
INSERT INTO `cms_region` VALUES ('39508', '惠水县', '3', '39302', 'H');
INSERT INTO `cms_region` VALUES ('39534', '三都县', '3', '39302', 'S');
INSERT INTO `cms_region` VALUES ('39556', '云南省', '1', '0', 'Y');
INSERT INTO `cms_region` VALUES ('39557', '昆明市', '2', '39556', 'K');
INSERT INTO `cms_region` VALUES ('39558', '市辖区', '3', '39557', 'S');
INSERT INTO `cms_region` VALUES ('39559', '五华区', '3', '39557', 'W');
INSERT INTO `cms_region` VALUES ('39571', '盘龙区', '3', '39557', 'P');
INSERT INTO `cms_region` VALUES ('39582', '官渡区', '3', '39557', 'G');
INSERT INTO `cms_region` VALUES ('39594', '西山区', '3', '39557', 'X');
INSERT INTO `cms_region` VALUES ('39605', '东川区', '3', '39557', 'D');
INSERT INTO `cms_region` VALUES ('39614', '呈贡县', '3', '39557', 'C');
INSERT INTO `cms_region` VALUES ('39622', '晋宁县', '3', '39557', 'J');
INSERT INTO `cms_region` VALUES ('39632', '富民县', '3', '39557', 'F');
INSERT INTO `cms_region` VALUES ('39640', '宜良县', '3', '39557', 'Y');
INSERT INTO `cms_region` VALUES ('39650', '石林县', '3', '39557', 'S');
INSERT INTO `cms_region` VALUES ('39659', '嵩明县', '3', '39557', 'S');
INSERT INTO `cms_region` VALUES ('39667', '禄劝县', '3', '39557', 'L');
INSERT INTO `cms_region` VALUES ('39684', '寻甸县', '3', '39557', 'X');
INSERT INTO `cms_region` VALUES ('39701', '安宁市', '3', '39557', 'A');
INSERT INTO `cms_region` VALUES ('39710', '曲靖市', '2', '39556', 'Q');
INSERT INTO `cms_region` VALUES ('39711', '市辖区', '3', '39710', 'S');
INSERT INTO `cms_region` VALUES ('39712', '麒麟区', '3', '39710', 'Q');
INSERT INTO `cms_region` VALUES ('39724', '马龙县', '3', '39710', 'M');
INSERT INTO `cms_region` VALUES ('39733', '陆良县', '3', '39710', 'L');
INSERT INTO `cms_region` VALUES ('39744', '师宗县', '3', '39710', 'S');
INSERT INTO `cms_region` VALUES ('39753', '罗平县', '3', '39710', 'L');
INSERT INTO `cms_region` VALUES ('39766', '富源县', '3', '39710', 'F');
INSERT INTO `cms_region` VALUES ('39778', '会泽县', '3', '39710', 'H');
INSERT INTO `cms_region` VALUES ('39800', '沾益县', '3', '39710', 'Z');
INSERT INTO `cms_region` VALUES ('39809', '宣威市', '3', '39710', 'X');
INSERT INTO `cms_region` VALUES ('39836', '玉溪市', '2', '39556', 'Y');
INSERT INTO `cms_region` VALUES ('39837', '市辖区', '3', '39836', 'S');
INSERT INTO `cms_region` VALUES ('39838', '红塔区', '3', '39836', 'H');
INSERT INTO `cms_region` VALUES ('39850', '江川县', '3', '39836', 'J');
INSERT INTO `cms_region` VALUES ('39858', '澄江县', '3', '39836', 'C');
INSERT INTO `cms_region` VALUES ('39865', '通海县', '3', '39836', 'T');
INSERT INTO `cms_region` VALUES ('39875', '华宁县', '3', '39836', 'H');
INSERT INTO `cms_region` VALUES ('39881', '易门县', '3', '39836', 'Y');
INSERT INTO `cms_region` VALUES ('39889', '峨山县', '3', '39836', 'E');
INSERT INTO `cms_region` VALUES ('39899', '新平县', '3', '39836', 'X');
INSERT INTO `cms_region` VALUES ('39912', '元江县', '3', '39836', 'Y');
INSERT INTO `cms_region` VALUES ('39923', '保山市', '2', '39556', 'B');
INSERT INTO `cms_region` VALUES ('39924', '市辖区', '3', '39923', 'S');
INSERT INTO `cms_region` VALUES ('39925', '隆阳区', '3', '39923', 'L');
INSERT INTO `cms_region` VALUES ('39946', '施甸县', '3', '39923', 'S');
INSERT INTO `cms_region` VALUES ('39960', '腾冲县', '3', '39923', 'T');
INSERT INTO `cms_region` VALUES ('39979', '龙陵县', '3', '39923', 'L');
INSERT INTO `cms_region` VALUES ('39990', '昌宁县', '3', '39923', 'C');
INSERT INTO `cms_region` VALUES ('40004', '昭通市', '2', '39556', 'Z');
INSERT INTO `cms_region` VALUES ('40005', '市辖区', '3', '40004', 'S');
INSERT INTO `cms_region` VALUES ('40006', '昭阳区', '3', '40004', 'Z');
INSERT INTO `cms_region` VALUES ('40027', '鲁甸县', '3', '40004', 'L');
INSERT INTO `cms_region` VALUES ('40040', '巧家县', '3', '40004', 'Q');
INSERT INTO `cms_region` VALUES ('40057', '盐津县', '3', '40004', 'Y');
INSERT INTO `cms_region` VALUES ('40068', '大关县', '3', '40004', 'D');
INSERT INTO `cms_region` VALUES ('40078', '永善县', '3', '40004', 'Y');
INSERT INTO `cms_region` VALUES ('40094', '绥江县', '3', '40004', 'S');
INSERT INTO `cms_region` VALUES ('40100', '镇雄县', '3', '40004', 'Z');
INSERT INTO `cms_region` VALUES ('40129', '彝良县', '3', '40004', 'Y');
INSERT INTO `cms_region` VALUES ('40145', '威信县', '3', '40004', 'W');
INSERT INTO `cms_region` VALUES ('40156', '水富县', '3', '40004', 'S');
INSERT INTO `cms_region` VALUES ('40160', '丽江市', '2', '39556', 'L');
INSERT INTO `cms_region` VALUES ('40161', '市辖区', '3', '40160', 'S');
INSERT INTO `cms_region` VALUES ('40162', '古城区', '3', '40160', 'G');
INSERT INTO `cms_region` VALUES ('40172', '玉龙县', '3', '40160', 'Y');
INSERT INTO `cms_region` VALUES ('40189', '永胜县', '3', '40160', 'Y');
INSERT INTO `cms_region` VALUES ('40205', '华坪县', '3', '40160', 'H');
INSERT INTO `cms_region` VALUES ('40214', '宁蒗县', '3', '40160', 'N');
INSERT INTO `cms_region` VALUES ('40230', '思茅市', '2', '39556', 'S');
INSERT INTO `cms_region` VALUES ('40231', '市辖区', '3', '40230', 'S');
INSERT INTO `cms_region` VALUES ('40232', '翠云区', '3', '40230', 'C');
INSERT INTO `cms_region` VALUES ('40240', '普洱县', '3', '40230', 'P');
INSERT INTO `cms_region` VALUES ('40250', '墨江县', '3', '40230', 'M');
INSERT INTO `cms_region` VALUES ('40266', '景东县', '3', '40230', 'J');
INSERT INTO `cms_region` VALUES ('40280', '景谷县', '3', '40230', 'J');
INSERT INTO `cms_region` VALUES ('40291', '镇沅县', '3', '40230', 'Z');
INSERT INTO `cms_region` VALUES ('40301', '江城县', '3', '40230', 'J');
INSERT INTO `cms_region` VALUES ('40310', '孟连县', '3', '40230', 'M');
INSERT INTO `cms_region` VALUES ('40318', '澜沧县', '3', '40230', 'L');
INSERT INTO `cms_region` VALUES ('40340', '西盟县', '3', '40230', 'X');
INSERT INTO `cms_region` VALUES ('40348', '临沧市', '2', '39556', 'L');
INSERT INTO `cms_region` VALUES ('40349', '市辖区', '3', '40348', 'S');
INSERT INTO `cms_region` VALUES ('40350', '临翔区', '3', '40348', 'L');
INSERT INTO `cms_region` VALUES ('40361', '凤庆县', '3', '40348', 'F');
INSERT INTO `cms_region` VALUES ('40375', '云县', '3', '40348', 'Y');
INSERT INTO `cms_region` VALUES ('40388', '永德县', '3', '40348', 'Y');
INSERT INTO `cms_region` VALUES ('40400', '镇康县', '3', '40348', 'Z');
INSERT INTO `cms_region` VALUES ('40408', '双江县', '3', '40348', 'S');
INSERT INTO `cms_region` VALUES ('40417', '耿马县', '3', '40348', 'G');
INSERT INTO `cms_region` VALUES ('40429', '沧源县', '3', '40348', 'C');
INSERT INTO `cms_region` VALUES ('40441', '楚雄州', '2', '39556', 'C');
INSERT INTO `cms_region` VALUES ('40442', '楚雄市', '3', '40441', 'C');
INSERT INTO `cms_region` VALUES ('40458', '双柏县', '3', '40441', 'S');
INSERT INTO `cms_region` VALUES ('40467', '牟定县', '3', '40441', 'M');
INSERT INTO `cms_region` VALUES ('40475', '南华县', '3', '40441', 'N');
INSERT INTO `cms_region` VALUES ('40486', '姚安县', '3', '40441', 'Y');
INSERT INTO `cms_region` VALUES ('40496', '大姚县', '3', '40441', 'D');
INSERT INTO `cms_region` VALUES ('40509', '永仁县', '3', '40441', 'Y');
INSERT INTO `cms_region` VALUES ('40517', '元谋县', '3', '40441', 'Y');
INSERT INTO `cms_region` VALUES ('40528', '武定县', '3', '40441', 'W');
INSERT INTO `cms_region` VALUES ('40540', '禄丰县', '3', '40441', 'L');
INSERT INTO `cms_region` VALUES ('40555', '红河州', '2', '39556', 'H');
INSERT INTO `cms_region` VALUES ('40556', '个旧市', '3', '40555', 'G');
INSERT INTO `cms_region` VALUES ('40567', '开远市', '3', '40555', 'K');
INSERT INTO `cms_region` VALUES ('40576', '蒙自县', '3', '40555', 'M');
INSERT INTO `cms_region` VALUES ('40588', '屏边县', '3', '40555', 'P');
INSERT INTO `cms_region` VALUES ('40596', '建水县', '3', '40555', 'J');
INSERT INTO `cms_region` VALUES ('40611', '石屏县', '3', '40555', 'S');
INSERT INTO `cms_region` VALUES ('40621', '弥勒县', '3', '40555', 'M');
INSERT INTO `cms_region` VALUES ('40635', '泸西县', '3', '40555', 'L');
INSERT INTO `cms_region` VALUES ('40644', '元阳县', '3', '40555', 'Y');
INSERT INTO `cms_region` VALUES ('40659', '红河县', '3', '40555', 'H');
INSERT INTO `cms_region` VALUES ('40673', '金平县', '3', '40555', 'J');
INSERT INTO `cms_region` VALUES ('40688', '绿春县', '3', '40555', 'L');
INSERT INTO `cms_region` VALUES ('40698', '河口县', '3', '40555', 'H');
INSERT INTO `cms_region` VALUES ('40705', '文山州', '2', '39556', 'W');
INSERT INTO `cms_region` VALUES ('40706', '文山县', '3', '40705', 'W');
INSERT INTO `cms_region` VALUES ('40722', '砚山县', '3', '40705', 'Y');
INSERT INTO `cms_region` VALUES ('40734', '西畴县', '3', '40705', 'X');
INSERT INTO `cms_region` VALUES ('40744', '麻栗坡县', '3', '40705', 'M');
INSERT INTO `cms_region` VALUES ('40756', '马关县', '3', '40705', 'M');
INSERT INTO `cms_region` VALUES ('40770', '丘北县', '3', '40705', 'Q');
INSERT INTO `cms_region` VALUES ('40783', '广南县', '3', '40705', 'G');
INSERT INTO `cms_region` VALUES ('40802', '富宁县', '3', '40705', 'F');
INSERT INTO `cms_region` VALUES ('40816', '西双版纳州', '2', '39556', 'X');
INSERT INTO `cms_region` VALUES ('40817', '景洪市', '3', '40816', 'J');
INSERT INTO `cms_region` VALUES ('40829', '勐海县', '3', '40816', 'M');
INSERT INTO `cms_region` VALUES ('40841', '勐腊县', '3', '40816', 'M');
INSERT INTO `cms_region` VALUES ('40852', '大理州', '2', '39556', 'D');
INSERT INTO `cms_region` VALUES ('40853', '大理市', '3', '40852', 'D');
INSERT INTO `cms_region` VALUES ('40866', '漾濞县', '3', '40852', 'Y');
INSERT INTO `cms_region` VALUES ('40876', '祥云县', '3', '40852', 'X');
INSERT INTO `cms_region` VALUES ('40887', '宾川县', '3', '40852', 'B');
INSERT INTO `cms_region` VALUES ('40901', '弥渡县', '3', '40852', 'M');
INSERT INTO `cms_region` VALUES ('40910', '南涧县', '3', '40852', 'N');
INSERT INTO `cms_region` VALUES ('40919', '巍山县', '3', '40852', 'W');
INSERT INTO `cms_region` VALUES ('40930', '永平县', '3', '40852', 'Y');
INSERT INTO `cms_region` VALUES ('40938', '云龙县', '3', '40852', 'Y');
INSERT INTO `cms_region` VALUES ('40950', '洱源县', '3', '40852', 'E');
INSERT INTO `cms_region` VALUES ('40960', '剑川县', '3', '40852', 'J');
INSERT INTO `cms_region` VALUES ('40969', '鹤庆县', '3', '40852', 'H');
INSERT INTO `cms_region` VALUES ('40979', '德宏州', '2', '39556', 'D');
INSERT INTO `cms_region` VALUES ('40980', '瑞丽市', '3', '40979', 'R');
INSERT INTO `cms_region` VALUES ('40988', '潞西市', '3', '40979', 'L');
INSERT INTO `cms_region` VALUES ('41000', '梁河县', '3', '40979', 'L');
INSERT INTO `cms_region` VALUES ('41010', '盈江县', '3', '40979', 'Y');
INSERT INTO `cms_region` VALUES ('41026', '陇川县', '3', '40979', 'L');
INSERT INTO `cms_region` VALUES ('41036', '怒江州', '2', '39556', 'N');
INSERT INTO `cms_region` VALUES ('41037', '泸水县', '3', '41036', 'L');
INSERT INTO `cms_region` VALUES ('41047', '福贡县', '3', '41036', 'F');
INSERT INTO `cms_region` VALUES ('41055', '贡山县', '3', '41036', 'G');
INSERT INTO `cms_region` VALUES ('41061', '兰坪县', '3', '41036', 'L');
INSERT INTO `cms_region` VALUES ('41070', '迪庆州', '2', '39556', 'D');
INSERT INTO `cms_region` VALUES ('41071', '香格里拉县', '3', '41070', 'X');
INSERT INTO `cms_region` VALUES ('41083', '德钦县', '3', '41070', 'D');
INSERT INTO `cms_region` VALUES ('41092', '维西县', '3', '41070', 'W');
INSERT INTO `cms_region` VALUES ('41103', '西藏自治区', '1', '0', 'X');
INSERT INTO `cms_region` VALUES ('41104', '拉萨市', '2', '41103', 'L');
INSERT INTO `cms_region` VALUES ('41105', '市辖区', '3', '41104', 'S');
INSERT INTO `cms_region` VALUES ('41106', '城关区', '3', '41104', 'C');
INSERT INTO `cms_region` VALUES ('41118', '林周县', '3', '41104', 'L');
INSERT INTO `cms_region` VALUES ('41129', '当雄县', '3', '41104', 'D');
INSERT INTO `cms_region` VALUES ('41138', '尼木县', '3', '41104', 'N');
INSERT INTO `cms_region` VALUES ('41147', '曲水县', '3', '41104', 'Q');
INSERT INTO `cms_region` VALUES ('41154', '堆龙德庆', '3', '41104', 'D');
INSERT INTO `cms_region` VALUES ('41162', '达孜县', '3', '41104', 'D');
INSERT INTO `cms_region` VALUES ('41169', '墨竹工卡县', '3', '41104', 'M');
INSERT INTO `cms_region` VALUES ('41178', '昌都地区', '2', '41103', 'C');
INSERT INTO `cms_region` VALUES ('41179', '昌都县', '3', '41178', 'C');
INSERT INTO `cms_region` VALUES ('41195', '江达县', '3', '41178', 'J');
INSERT INTO `cms_region` VALUES ('41209', '贡觉县', '3', '41178', 'G');
INSERT INTO `cms_region` VALUES ('41222', '类乌齐县', '3', '41178', 'L');
INSERT INTO `cms_region` VALUES ('41233', '丁青县', '3', '41178', 'D');
INSERT INTO `cms_region` VALUES ('41247', '察亚县', '3', '41178', 'C');
INSERT INTO `cms_region` VALUES ('41261', '八宿县', '3', '41178', 'B');
INSERT INTO `cms_region` VALUES ('41276', '左贡县', '3', '41178', 'Z');
INSERT INTO `cms_region` VALUES ('41287', '芒康县', '3', '41178', 'M');
INSERT INTO `cms_region` VALUES ('41304', '洛隆县', '3', '41178', 'L');
INSERT INTO `cms_region` VALUES ('41316', '边坝县', '3', '41178', 'B');
INSERT INTO `cms_region` VALUES ('41328', '山南地区', '2', '41103', 'S');
INSERT INTO `cms_region` VALUES ('41329', '乃东县', '3', '41328', 'N');
INSERT INTO `cms_region` VALUES ('41337', '扎囊县', '3', '41328', 'Z');
INSERT INTO `cms_region` VALUES ('41343', '贡嘎县', '3', '41328', 'G');
INSERT INTO `cms_region` VALUES ('41352', '桑日县', '3', '41328', 'S');
INSERT INTO `cms_region` VALUES ('41357', '琼结县', '3', '41328', 'Q');
INSERT INTO `cms_region` VALUES ('41362', '曲松县', '3', '41328', 'Q');
INSERT INTO `cms_region` VALUES ('41368', '措美县', '3', '41328', 'C');
INSERT INTO `cms_region` VALUES ('41373', '洛扎县', '3', '41328', 'L');
INSERT INTO `cms_region` VALUES ('41381', '加查县', '3', '41328', 'J');
INSERT INTO `cms_region` VALUES ('41389', '隆子县', '3', '41328', 'L');
INSERT INTO `cms_region` VALUES ('41401', '错那县', '3', '41328', 'C');
INSERT INTO `cms_region` VALUES ('41412', '浪卡子县', '3', '41328', 'L');
INSERT INTO `cms_region` VALUES ('41423', '日喀则地区', '2', '41103', 'R');
INSERT INTO `cms_region` VALUES ('41424', '日喀则市', '3', '41423', 'R');
INSERT INTO `cms_region` VALUES ('41437', '南木林县', '3', '41423', 'N');
INSERT INTO `cms_region` VALUES ('41455', '江孜县', '3', '41423', 'J');
INSERT INTO `cms_region` VALUES ('41475', '定日县', '3', '41423', 'D');
INSERT INTO `cms_region` VALUES ('41489', '萨迦县', '3', '41423', 'S');
INSERT INTO `cms_region` VALUES ('41501', '拉孜县', '3', '41423', 'L');
INSERT INTO `cms_region` VALUES ('41513', '昂仁县', '3', '41423', 'A');
INSERT INTO `cms_region` VALUES ('41531', '谢通门县', '3', '41423', 'X');
INSERT INTO `cms_region` VALUES ('41551', '白朗县', '3', '41423', 'B');
INSERT INTO `cms_region` VALUES ('41563', '仁布县', '3', '41423', 'R');
INSERT INTO `cms_region` VALUES ('41573', '康马县', '3', '41423', 'K');
INSERT INTO `cms_region` VALUES ('41583', '定结县', '3', '41423', 'D');
INSERT INTO `cms_region` VALUES ('41594', '仲巴县', '3', '41423', 'Z');
INSERT INTO `cms_region` VALUES ('41608', '亚东县', '3', '41423', 'Y');
INSERT INTO `cms_region` VALUES ('41616', '吉隆县', '3', '41423', 'J');
INSERT INTO `cms_region` VALUES ('41622', '聂拉木县', '3', '41423', 'N');
INSERT INTO `cms_region` VALUES ('41630', '萨嘎县', '3', '41423', 'S');
INSERT INTO `cms_region` VALUES ('41639', '岗巴县', '3', '41423', 'G');
INSERT INTO `cms_region` VALUES ('41645', '那曲地区', '2', '41103', 'N');
INSERT INTO `cms_region` VALUES ('41646', '那曲县', '3', '41645', 'N');
INSERT INTO `cms_region` VALUES ('41659', '嘉黎县', '3', '41645', 'J');
INSERT INTO `cms_region` VALUES ('41670', '比如县', '3', '41645', 'B');
INSERT INTO `cms_region` VALUES ('41681', '聂荣县', '3', '41645', 'N');
INSERT INTO `cms_region` VALUES ('41692', '安多县', '3', '41645', 'A');
INSERT INTO `cms_region` VALUES ('41706', '申扎县', '3', '41645', 'S');
INSERT INTO `cms_region` VALUES ('41715', '索县', '3', '41645', 'S');
INSERT INTO `cms_region` VALUES ('41726', '班戈县', '3', '41645', 'B');
INSERT INTO `cms_region` VALUES ('41737', '巴青县', '3', '41645', 'B');
INSERT INTO `cms_region` VALUES ('41748', '尼玛县', '3', '41645', 'N');
INSERT INTO `cms_region` VALUES ('41770', '阿里地区', '2', '41103', 'A');
INSERT INTO `cms_region` VALUES ('41771', '普兰县', '3', '41770', 'P');
INSERT INTO `cms_region` VALUES ('41775', '札达县', '3', '41770', 'Z');
INSERT INTO `cms_region` VALUES ('41782', '噶尔县', '3', '41770', 'G');
INSERT INTO `cms_region` VALUES ('41788', '日土县', '3', '41770', 'R');
INSERT INTO `cms_region` VALUES ('41794', '革吉县', '3', '41770', 'G');
INSERT INTO `cms_region` VALUES ('41800', '改则县', '3', '41770', 'G');
INSERT INTO `cms_region` VALUES ('41808', '措勤县', '3', '41770', 'C');
INSERT INTO `cms_region` VALUES ('41814', '林芝地区', '2', '41103', 'L');
INSERT INTO `cms_region` VALUES ('41815', '林芝县', '3', '41814', 'L');
INSERT INTO `cms_region` VALUES ('41823', '工布江达县', '3', '41814', 'G');
INSERT INTO `cms_region` VALUES ('41833', '米林县', '3', '41814', 'M');
INSERT INTO `cms_region` VALUES ('41842', '墨脱县', '3', '41814', 'M');
INSERT INTO `cms_region` VALUES ('41851', '波密县', '3', '41814', 'B');
INSERT INTO `cms_region` VALUES ('41863', '察隅县', '3', '41814', 'C');
INSERT INTO `cms_region` VALUES ('41870', '朗县', '3', '41814', 'L');
INSERT INTO `cms_region` VALUES ('41877', '陕西省', '1', '0', 'S');
INSERT INTO `cms_region` VALUES ('41878', '西安市', '2', '41877', 'X');
INSERT INTO `cms_region` VALUES ('41879', '市辖区', '3', '41878', 'S');
INSERT INTO `cms_region` VALUES ('41880', '新城区', '3', '41878', 'X');
INSERT INTO `cms_region` VALUES ('41890', '碑林区', '3', '41878', 'B');
INSERT INTO `cms_region` VALUES ('41899', '莲湖区', '3', '41878', 'L');
INSERT INTO `cms_region` VALUES ('41909', '灞桥区', '3', '41878', 'B');
INSERT INTO `cms_region` VALUES ('41919', '未央区', '3', '41878', 'W');
INSERT INTO `cms_region` VALUES ('41930', '雁塔区', '3', '41878', 'Y');
INSERT INTO `cms_region` VALUES ('41939', '阎良区', '3', '41878', 'Y');
INSERT INTO `cms_region` VALUES ('41947', '临潼区', '3', '41878', 'L');
INSERT INTO `cms_region` VALUES ('41971', '长安区', '3', '41878', 'C');
INSERT INTO `cms_region` VALUES ('41997', '蓝田县', '3', '41878', 'L');
INSERT INTO `cms_region` VALUES ('42020', '周至县', '3', '41878', 'Z');
INSERT INTO `cms_region` VALUES ('42043', '户县', '3', '41878', 'H');
INSERT INTO `cms_region` VALUES ('42060', '高陵县', '3', '41878', 'G');
INSERT INTO `cms_region` VALUES ('42069', '铜川市', '2', '41877', 'T');
INSERT INTO `cms_region` VALUES ('42070', '市辖区', '3', '42069', 'S');
INSERT INTO `cms_region` VALUES ('42071', '王益区', '3', '42069', 'W');
INSERT INTO `cms_region` VALUES ('42079', '印台区', '3', '42069', 'Y');
INSERT INTO `cms_region` VALUES ('42091', '耀州区', '3', '42069', 'Y');
INSERT INTO `cms_region` VALUES ('42108', '宜君县', '3', '42069', 'Y');
INSERT INTO `cms_region` VALUES ('42119', '宝鸡市', '2', '41877', 'B');
INSERT INTO `cms_region` VALUES ('42120', '市辖区', '3', '42119', 'S');
INSERT INTO `cms_region` VALUES ('42121', '渭滨区', '3', '42119', 'W');
INSERT INTO `cms_region` VALUES ('42133', '金台区', '3', '42119', 'J');
INSERT INTO `cms_region` VALUES ('42146', '陈仓区', '3', '42119', 'C');
INSERT INTO `cms_region` VALUES ('42165', '凤翔县', '3', '42119', 'F');
INSERT INTO `cms_region` VALUES ('42183', '岐山县', '3', '42119', 'Q');
INSERT INTO `cms_region` VALUES ('42198', '扶风县', '3', '42119', 'F');
INSERT INTO `cms_region` VALUES ('42211', '眉县', '3', '42119', 'M');
INSERT INTO `cms_region` VALUES ('42224', '陇县', '3', '42119', 'L');
INSERT INTO `cms_region` VALUES ('42240', '千阳县', '3', '42119', 'Q');
INSERT INTO `cms_region` VALUES ('42252', '麟游县', '3', '42119', 'L');
INSERT INTO `cms_region` VALUES ('42263', '凤县', '3', '42119', 'F');
INSERT INTO `cms_region` VALUES ('42278', '太白县', '3', '42119', 'T');
INSERT INTO `cms_region` VALUES ('42287', '咸阳市', '2', '41877', 'X');
INSERT INTO `cms_region` VALUES ('42288', '市辖区', '3', '42287', 'S');
INSERT INTO `cms_region` VALUES ('42289', '秦都区', '3', '42287', 'Q');
INSERT INTO `cms_region` VALUES ('42302', '杨凌区', '3', '42287', 'Y');
INSERT INTO `cms_region` VALUES ('42308', '渭城区', '3', '42287', 'W');
INSERT INTO `cms_region` VALUES ('42319', '三原县', '3', '42287', 'S');
INSERT INTO `cms_region` VALUES ('42334', '泾阳县', '3', '42287', 'J');
INSERT INTO `cms_region` VALUES ('42351', '乾县', '3', '42287', 'Q');
INSERT INTO `cms_region` VALUES ('42372', '礼泉县', '3', '42287', 'L');
INSERT INTO `cms_region` VALUES ('42388', '永寿县', '3', '42287', 'Y');
INSERT INTO `cms_region` VALUES ('42402', '彬县', '3', '42287', 'B');
INSERT INTO `cms_region` VALUES ('42419', '长武县', '3', '42287', 'C');
INSERT INTO `cms_region` VALUES ('42431', '旬邑县', '3', '42287', 'X');
INSERT INTO `cms_region` VALUES ('42446', '淳化县', '3', '42287', 'C');
INSERT INTO `cms_region` VALUES ('42462', '武功县', '3', '42287', 'W');
INSERT INTO `cms_region` VALUES ('42475', '兴平市', '3', '42287', 'X');
INSERT INTO `cms_region` VALUES ('42490', '渭南市', '2', '41877', 'W');
INSERT INTO `cms_region` VALUES ('42491', '市辖区', '3', '42490', 'S');
INSERT INTO `cms_region` VALUES ('42492', '临渭区', '3', '42490', 'L');
INSERT INTO `cms_region` VALUES ('42523', '华县', '3', '42490', 'H');
INSERT INTO `cms_region` VALUES ('42538', '潼关县', '3', '42490', 'T');
INSERT INTO `cms_region` VALUES ('42547', '大荔县', '3', '42490', 'D');
INSERT INTO `cms_region` VALUES ('42577', '合阳县', '3', '42490', 'H');
INSERT INTO `cms_region` VALUES ('42594', '澄城县', '3', '42490', 'C');
INSERT INTO `cms_region` VALUES ('42609', '蒲城县', '3', '42490', 'P');
INSERT INTO `cms_region` VALUES ('42634', '白水县', '3', '42490', 'B');
INSERT INTO `cms_region` VALUES ('42649', '富平县', '3', '42490', 'F');
INSERT INTO `cms_region` VALUES ('42674', '韩城市', '3', '42490', 'H');
INSERT INTO `cms_region` VALUES ('42691', '华阴市', '3', '42490', 'H');
INSERT INTO `cms_region` VALUES ('42703', '延安市', '2', '41877', 'Y');
INSERT INTO `cms_region` VALUES ('42704', '市辖区', '3', '42703', 'S');
INSERT INTO `cms_region` VALUES ('42705', '宝塔区', '3', '42703', 'B');
INSERT INTO `cms_region` VALUES ('42729', '延长县', '3', '42703', 'Y');
INSERT INTO `cms_region` VALUES ('42742', '延川县', '3', '42703', 'Y');
INSERT INTO `cms_region` VALUES ('42757', '子长县', '3', '42703', 'Z');
INSERT INTO `cms_region` VALUES ('42771', '安塞县', '3', '42703', 'A');
INSERT INTO `cms_region` VALUES ('42784', '志丹县', '3', '42703', 'Z');
INSERT INTO `cms_region` VALUES ('42796', '吴起县', '3', '42703', 'W');
INSERT INTO `cms_region` VALUES ('42809', '甘泉县', '3', '42703', 'G');
INSERT INTO `cms_region` VALUES ('42818', '富县', '3', '42703', 'F');
INSERT INTO `cms_region` VALUES ('42833', '洛川县', '3', '42703', 'L');
INSERT INTO `cms_region` VALUES ('42850', '宜川县', '3', '42703', 'Y');
INSERT INTO `cms_region` VALUES ('42863', '黄龙县', '3', '42703', 'H');
INSERT INTO `cms_region` VALUES ('42874', '黄陵县', '3', '42703', 'H');
INSERT INTO `cms_region` VALUES ('42888', '汉中市', '2', '41877', 'H');
INSERT INTO `cms_region` VALUES ('42889', '市辖区', '3', '42888', 'S');
INSERT INTO `cms_region` VALUES ('42890', '汉台区', '3', '42888', 'H');
INSERT INTO `cms_region` VALUES ('42908', '南郑县', '3', '42888', 'N');
INSERT INTO `cms_region` VALUES ('42939', '城固县', '3', '42888', 'C');
INSERT INTO `cms_region` VALUES ('42965', '洋县', '3', '42888', 'Y');
INSERT INTO `cms_region` VALUES ('42992', '西乡县', '3', '42888', 'X');
INSERT INTO `cms_region` VALUES ('43016', '勉县', '3', '42888', 'M');
INSERT INTO `cms_region` VALUES ('43042', '宁强县', '3', '42888', 'N');
INSERT INTO `cms_region` VALUES ('43069', '略阳县', '3', '42888', 'L');
INSERT INTO `cms_region` VALUES ('43091', '镇巴县', '3', '42888', 'Z');
INSERT INTO `cms_region` VALUES ('43116', '留坝县', '3', '42888', 'L');
INSERT INTO `cms_region` VALUES ('43126', '佛坪县', '3', '42888', 'F');
INSERT INTO `cms_region` VALUES ('43136', '榆林市', '2', '41877', 'Y');
INSERT INTO `cms_region` VALUES ('43137', '市辖区', '3', '43136', 'S');
INSERT INTO `cms_region` VALUES ('43138', '榆阳区', '3', '43136', 'Y');
INSERT INTO `cms_region` VALUES ('43170', '神木县', '3', '43136', 'S');
INSERT INTO `cms_region` VALUES ('43190', '府谷县', '3', '43136', 'F');
INSERT INTO `cms_region` VALUES ('43211', '横山县', '3', '43136', 'H');
INSERT INTO `cms_region` VALUES ('43230', '靖边县', '3', '43136', 'J');
INSERT INTO `cms_region` VALUES ('43253', '定边县', '3', '43136', 'D');
INSERT INTO `cms_region` VALUES ('43279', '绥德县', '3', '43136', 'S');
INSERT INTO `cms_region` VALUES ('43300', '米脂县', '3', '43136', 'M');
INSERT INTO `cms_region` VALUES ('43314', '佳县', '3', '43136', 'J');
INSERT INTO `cms_region` VALUES ('43335', '吴堡县', '3', '43136', 'W');
INSERT INTO `cms_region` VALUES ('43344', '清涧县', '3', '43136', 'Q');
INSERT INTO `cms_region` VALUES ('43360', '子洲县', '3', '43136', 'Z');
INSERT INTO `cms_region` VALUES ('43379', '安康市', '2', '41877', 'A');
INSERT INTO `cms_region` VALUES ('43380', '市辖区', '3', '43379', 'S');
INSERT INTO `cms_region` VALUES ('43381', '汉滨区', '3', '43379', 'H');
INSERT INTO `cms_region` VALUES ('43428', '汉阴县', '3', '43379', 'H');
INSERT INTO `cms_region` VALUES ('43447', '石泉县', '3', '43379', 'S');
INSERT INTO `cms_region` VALUES ('43463', '宁陕县', '3', '43379', 'N');
INSERT INTO `cms_region` VALUES ('43478', '紫阳县', '3', '43379', 'Z');
INSERT INTO `cms_region` VALUES ('43504', '岚皋县', '3', '43379', 'L');
INSERT INTO `cms_region` VALUES ('43522', '平利县', '3', '43379', 'P');
INSERT INTO `cms_region` VALUES ('43535', '镇坪县', '3', '43379', 'Z');
INSERT INTO `cms_region` VALUES ('43546', '旬阳县', '3', '43379', 'X');
INSERT INTO `cms_region` VALUES ('43575', '白河县', '3', '43379', 'B');
INSERT INTO `cms_region` VALUES ('43592', '商洛市', '2', '41877', 'S');
INSERT INTO `cms_region` VALUES ('43593', '市辖区', '3', '43592', 'S');
INSERT INTO `cms_region` VALUES ('43594', '商州区', '3', '43592', 'S');
INSERT INTO `cms_region` VALUES ('43628', '洛南县', '3', '43592', 'L');
INSERT INTO `cms_region` VALUES ('43654', '丹凤县', '3', '43592', 'D');
INSERT INTO `cms_region` VALUES ('43676', '商南县', '3', '43592', 'S');
INSERT INTO `cms_region` VALUES ('43699', '山阳县', '3', '43592', 'S');
INSERT INTO `cms_region` VALUES ('43730', '镇安县', '3', '43592', 'Z');
INSERT INTO `cms_region` VALUES ('43759', '柞水县', '3', '43592', 'Z');
INSERT INTO `cms_region` VALUES ('43776', '甘肃省', '1', '0', 'G');
INSERT INTO `cms_region` VALUES ('43777', '兰州市', '2', '43776', 'L');
INSERT INTO `cms_region` VALUES ('43778', '市辖区', '3', '43777', 'S');
INSERT INTO `cms_region` VALUES ('43779', '城关区', '3', '43777', 'C');
INSERT INTO `cms_region` VALUES ('43804', '七里河区', '3', '43777', 'Q');
INSERT INTO `cms_region` VALUES ('43820', '兰州市西固区', '3', '43777', 'L');
INSERT INTO `cms_region` VALUES ('43836', '安宁区', '3', '43777', 'A');
INSERT INTO `cms_region` VALUES ('43845', '红古区', '3', '43777', 'H');
INSERT INTO `cms_region` VALUES ('43853', '永登县', '3', '43777', 'Y');
INSERT INTO `cms_region` VALUES ('43872', '皋兰县', '3', '43777', 'G');
INSERT INTO `cms_region` VALUES ('43880', '榆中县', '3', '43777', 'Y');
INSERT INTO `cms_region` VALUES ('43904', '嘉峪关市', '2', '43776', 'J');
INSERT INTO `cms_region` VALUES ('43905', '市辖', '3', '43904', 'S');
INSERT INTO `cms_region` VALUES ('43914', '金昌市', '2', '43776', 'J');
INSERT INTO `cms_region` VALUES ('43915', '市辖区', '3', '43914', 'S');
INSERT INTO `cms_region` VALUES ('43916', '金川区', '3', '43914', 'J');
INSERT INTO `cms_region` VALUES ('43925', '永昌县', '3', '43914', 'Y');
INSERT INTO `cms_region` VALUES ('43936', '白银市', '2', '43776', 'B');
INSERT INTO `cms_region` VALUES ('43937', '市辖区', '3', '43936', 'S');
INSERT INTO `cms_region` VALUES ('43938', '白银区', '3', '43936', 'B');
INSERT INTO `cms_region` VALUES ('43949', '平川区', '3', '43936', 'P');
INSERT INTO `cms_region` VALUES ('43961', '靖远县', '3', '43936', 'J');
INSERT INTO `cms_region` VALUES ('43980', '会宁县', '3', '43936', 'H');
INSERT INTO `cms_region` VALUES ('44009', '景泰县', '3', '43936', 'J');
INSERT INTO `cms_region` VALUES ('44022', '天水市', '2', '43776', 'T');
INSERT INTO `cms_region` VALUES ('44023', '市辖区', '3', '44022', 'S');
INSERT INTO `cms_region` VALUES ('44024', '秦州区', '3', '44022', 'Q');
INSERT INTO `cms_region` VALUES ('44048', '麦积区', '3', '44022', 'M');
INSERT INTO `cms_region` VALUES ('44069', '清水县', '3', '44022', 'Q');
INSERT INTO `cms_region` VALUES ('44088', '秦安县', '3', '44022', 'Q');
INSERT INTO `cms_region` VALUES ('44106', '甘谷县', '3', '44022', 'G');
INSERT INTO `cms_region` VALUES ('44122', '武山县', '3', '44022', 'W');
INSERT INTO `cms_region` VALUES ('44138', '张家川县', '3', '44022', 'Z');
INSERT INTO `cms_region` VALUES ('44154', '武威市', '2', '43776', 'W');
INSERT INTO `cms_region` VALUES ('44155', '市辖区', '3', '44154', 'S');
INSERT INTO `cms_region` VALUES ('44156', '凉州区', '3', '44154', 'L');
INSERT INTO `cms_region` VALUES ('44202', '民勤县', '3', '44154', 'M');
INSERT INTO `cms_region` VALUES ('44221', '古浪县', '3', '44154', 'G');
INSERT INTO `cms_region` VALUES ('44242', '天祝县', '3', '44154', 'T');
INSERT INTO `cms_region` VALUES ('44265', '张掖市', '2', '43776', 'Z');
INSERT INTO `cms_region` VALUES ('44266', '市辖区', '3', '44265', 'S');
INSERT INTO `cms_region` VALUES ('44267', '甘州区', '3', '44265', 'G');
INSERT INTO `cms_region` VALUES ('44294', '肃南县', '3', '44265', 'S');
INSERT INTO `cms_region` VALUES ('44305', '民乐县', '3', '44265', 'M');
INSERT INTO `cms_region` VALUES ('44317', '临泽县', '3', '44265', 'L');
INSERT INTO `cms_region` VALUES ('44331', '高台县', '3', '44265', 'G');
INSERT INTO `cms_region` VALUES ('44341', '山丹县', '3', '44265', 'S');
INSERT INTO `cms_region` VALUES ('44352', '平凉市', '2', '43776', 'P');
INSERT INTO `cms_region` VALUES ('44353', '市辖区', '3', '44352', 'S');
INSERT INTO `cms_region` VALUES ('44354', '崆峒区', '3', '44352', 'K');
INSERT INTO `cms_region` VALUES ('44375', '泾川县', '3', '44352', 'J');
INSERT INTO `cms_region` VALUES ('44392', '灵台县', '3', '44352', 'L');
INSERT INTO `cms_region` VALUES ('44408', '崇信县', '3', '44352', 'C');
INSERT INTO `cms_region` VALUES ('44418', '华亭县', '3', '44352', 'H');
INSERT INTO `cms_region` VALUES ('44431', '庄浪县', '3', '44352', 'Z');
INSERT INTO `cms_region` VALUES ('44451', '静宁县', '3', '44352', 'J');
INSERT INTO `cms_region` VALUES ('44477', '酒泉市', '2', '43776', 'J');
INSERT INTO `cms_region` VALUES ('44478', '市辖区', '3', '44477', 'S');
INSERT INTO `cms_region` VALUES ('44479', '肃州区', '3', '44477', 'S');
INSERT INTO `cms_region` VALUES ('44504', '金塔县', '3', '44477', 'J');
INSERT INTO `cms_region` VALUES ('44516', '瓜州县', '3', '44477', 'G');
INSERT INTO `cms_region` VALUES ('44531', '肃北县', '3', '44477', 'S');
INSERT INTO `cms_region` VALUES ('44535', '阿克塞县', '3', '44477', 'A');
INSERT INTO `cms_region` VALUES ('44539', '玉门市', '3', '44477', 'Y');
INSERT INTO `cms_region` VALUES ('44557', '敦煌市', '3', '44477', 'D');
INSERT INTO `cms_region` VALUES ('44569', '庆阳市', '2', '43776', 'Q');
INSERT INTO `cms_region` VALUES ('44570', '市辖区', '3', '44569', 'S');
INSERT INTO `cms_region` VALUES ('44571', '西峰区', '3', '44569', 'X');
INSERT INTO `cms_region` VALUES ('44582', '庆城县', '3', '44569', 'Q');
INSERT INTO `cms_region` VALUES ('44598', '环县', '3', '44569', 'H');
INSERT INTO `cms_region` VALUES ('44620', '华池县', '3', '44569', 'H');
INSERT INTO `cms_region` VALUES ('44636', '合水县', '3', '44569', 'H');
INSERT INTO `cms_region` VALUES ('44649', '正宁县', '3', '44569', 'Z');
INSERT INTO `cms_region` VALUES ('44660', '宁县', '3', '44569', 'N');
INSERT INTO `cms_region` VALUES ('44679', '镇原县', '3', '44569', 'Z');
INSERT INTO `cms_region` VALUES ('44699', '定西市', '2', '43776', 'D');
INSERT INTO `cms_region` VALUES ('44700', '市辖区', '3', '44699', 'S');
INSERT INTO `cms_region` VALUES ('44701', '安定区', '3', '44699', 'A');
INSERT INTO `cms_region` VALUES ('44723', '通渭县', '3', '44699', 'T');
INSERT INTO `cms_region` VALUES ('44742', '陇西县', '3', '44699', 'L');
INSERT INTO `cms_region` VALUES ('44760', '渭源县', '3', '44699', 'W');
INSERT INTO `cms_region` VALUES ('44777', '临洮县', '3', '44699', 'L');
INSERT INTO `cms_region` VALUES ('44796', '漳县', '3', '44699', 'Z');
INSERT INTO `cms_region` VALUES ('44810', '岷县', '3', '44699', 'M');
INSERT INTO `cms_region` VALUES ('44829', '陇南市', '2', '43776', 'L');
INSERT INTO `cms_region` VALUES ('44830', '市辖区', '3', '44829', 'S');
INSERT INTO `cms_region` VALUES ('44831', '武都区', '3', '44829', 'W');
INSERT INTO `cms_region` VALUES ('44868', '成县', '3', '44829', 'C');
INSERT INTO `cms_region` VALUES ('44886', '文县', '3', '44829', 'W');
INSERT INTO `cms_region` VALUES ('44907', '宕昌县', '3', '44829', 'D');
INSERT INTO `cms_region` VALUES ('44933', '康县', '3', '44829', 'K');
INSERT INTO `cms_region` VALUES ('44955', '西和县', '3', '44829', 'X');
INSERT INTO `cms_region` VALUES ('44976', '礼县', '3', '44829', 'L');
INSERT INTO `cms_region` VALUES ('45006', '徽县', '3', '44829', 'H');
INSERT INTO `cms_region` VALUES ('45022', '两当县', '3', '44829', 'L');
INSERT INTO `cms_region` VALUES ('45035', '临夏州', '2', '43776', 'L');
INSERT INTO `cms_region` VALUES ('45036', '临夏市', '3', '45035', 'L');
INSERT INTO `cms_region` VALUES ('45047', '临夏县', '3', '45035', 'L');
INSERT INTO `cms_region` VALUES ('45073', '康乐县', '3', '45035', 'K');
INSERT INTO `cms_region` VALUES ('45089', '永靖县', '3', '45035', 'Y');
INSERT INTO `cms_region` VALUES ('45107', '广河县', '3', '45035', 'G');
INSERT INTO `cms_region` VALUES ('45117', '和政县', '3', '45035', 'H');
INSERT INTO `cms_region` VALUES ('45131', '东乡县', '3', '45035', 'D');
INSERT INTO `cms_region` VALUES ('45156', '积石山县', '3', '45035', 'J');
INSERT INTO `cms_region` VALUES ('45174', '甘南州', '2', '43776', 'G');
INSERT INTO `cms_region` VALUES ('45175', '合作市', '3', '45174', 'H');
INSERT INTO `cms_region` VALUES ('45186', '临潭县', '3', '45174', 'L');
INSERT INTO `cms_region` VALUES ('45203', '卓尼县', '3', '45174', 'Z');
INSERT INTO `cms_region` VALUES ('45219', '舟曲县', '3', '45174', 'Z');
INSERT INTO `cms_region` VALUES ('45239', '迭部县', '3', '45174', 'D');
INSERT INTO `cms_region` VALUES ('45251', '玛曲县', '3', '45174', 'M');
INSERT INTO `cms_region` VALUES ('45263', '碌曲县', '3', '45174', 'L');
INSERT INTO `cms_region` VALUES ('45272', '夏河县', '3', '45174', 'X');
INSERT INTO `cms_region` VALUES ('45286', '青海省', '1', '0', 'Q');
INSERT INTO `cms_region` VALUES ('45287', '西宁市', '2', '45286', 'X');
INSERT INTO `cms_region` VALUES ('45288', '市辖区', '3', '45287', 'S');
INSERT INTO `cms_region` VALUES ('45289', '城东区', '3', '45287', 'C');
INSERT INTO `cms_region` VALUES ('45299', '城中区', '3', '45287', 'C');
INSERT INTO `cms_region` VALUES ('45306', '城西区', '3', '45287', 'C');
INSERT INTO `cms_region` VALUES ('45314', '城北区', '3', '45287', 'C');
INSERT INTO `cms_region` VALUES ('45320', '大通县', '3', '45287', 'D');
INSERT INTO `cms_region` VALUES ('45341', '湟中县', '3', '45287', 'H');
INSERT INTO `cms_region` VALUES ('45358', '湟源县', '3', '45287', 'H');
INSERT INTO `cms_region` VALUES ('45368', '海东地区', '2', '45286', 'H');
INSERT INTO `cms_region` VALUES ('45369', '平安县', '3', '45368', 'P');
INSERT INTO `cms_region` VALUES ('45378', '民和县', '3', '45368', 'M');
INSERT INTO `cms_region` VALUES ('45401', '乐都县', '3', '45368', 'L');
INSERT INTO `cms_region` VALUES ('45421', '互助县', '3', '45368', 'H');
INSERT INTO `cms_region` VALUES ('45441', '化隆县', '3', '45368', 'H');
INSERT INTO `cms_region` VALUES ('45461', '循化县', '3', '45368', 'X');
INSERT INTO `cms_region` VALUES ('45471', '海北州', '2', '45286', 'H');
INSERT INTO `cms_region` VALUES ('45472', '门源县', '3', '45471', 'M');
INSERT INTO `cms_region` VALUES ('45487', '祁连县', '3', '45471', 'Q');
INSERT INTO `cms_region` VALUES ('45495', '海晏县', '3', '45471', 'H');
INSERT INTO `cms_region` VALUES ('45502', '刚察县', '3', '45471', 'G');
INSERT INTO `cms_region` VALUES ('45510', '黄南州', '2', '45286', 'H');
INSERT INTO `cms_region` VALUES ('45511', '同仁县', '3', '45510', 'T');
INSERT INTO `cms_region` VALUES ('45523', '尖扎县', '3', '45510', 'J');
INSERT INTO `cms_region` VALUES ('45533', '泽库县', '3', '45510', 'Z');
INSERT INTO `cms_region` VALUES ('45542', '河南县', '3', '45510', 'H');
INSERT INTO `cms_region` VALUES ('45548', '海南州', '2', '45286', 'H');
INSERT INTO `cms_region` VALUES ('45549', '共和县', '3', '45548', 'G');
INSERT INTO `cms_region` VALUES ('45566', '同德县', '3', '45548', 'T');
INSERT INTO `cms_region` VALUES ('45573', '贵德县', '3', '45548', 'G');
INSERT INTO `cms_region` VALUES ('45581', '兴海县', '3', '45548', 'X');
INSERT INTO `cms_region` VALUES ('45589', '贵南县', '3', '45548', 'G');
INSERT INTO `cms_region` VALUES ('45597', '果洛州', '2', '45286', 'G');
INSERT INTO `cms_region` VALUES ('45598', '玛沁县', '3', '45597', 'M');
INSERT INTO `cms_region` VALUES ('45607', '班玛县', '3', '45597', 'B');
INSERT INTO `cms_region` VALUES ('45617', '甘德县', '3', '45597', 'G');
INSERT INTO `cms_region` VALUES ('45625', '达日县', '3', '45597', 'D');
INSERT INTO `cms_region` VALUES ('45636', '久治县', '3', '45597', 'J');
INSERT INTO `cms_region` VALUES ('45643', '玛多县', '3', '45597', 'M');
INSERT INTO `cms_region` VALUES ('45648', '玉树州', '2', '45286', 'Y');
INSERT INTO `cms_region` VALUES ('45649', '玉树县', '3', '45648', 'Y');
INSERT INTO `cms_region` VALUES ('45659', '杂多县', '3', '45648', 'Z');
INSERT INTO `cms_region` VALUES ('45668', '称多县', '3', '45648', 'C');
INSERT INTO `cms_region` VALUES ('45676', '治多县', '3', '45648', 'Z');
INSERT INTO `cms_region` VALUES ('45683', '囊谦县', '3', '45648', 'N');
INSERT INTO `cms_region` VALUES ('45694', '曲麻莱县', '3', '45648', 'Q');
INSERT INTO `cms_region` VALUES ('45701', '海西州', '2', '45286', 'H');
INSERT INTO `cms_region` VALUES ('45702', '格尔木市', '3', '45701', 'G');
INSERT INTO `cms_region` VALUES ('45714', '德令哈市', '3', '45701', 'D');
INSERT INTO `cms_region` VALUES ('45727', '乌兰县', '3', '45701', 'W');
INSERT INTO `cms_region` VALUES ('45733', '都兰县', '3', '45701', 'D');
INSERT INTO `cms_region` VALUES ('45742', '天峻县', '3', '45701', 'T');
INSERT INTO `cms_region` VALUES ('45753', '宁夏回族自治区', '1', '0', 'N');
INSERT INTO `cms_region` VALUES ('45754', '银川市', '2', '45753', 'Y');
INSERT INTO `cms_region` VALUES ('45755', '市辖区', '3', '45754', 'S');
INSERT INTO `cms_region` VALUES ('45756', '兴庆区', '3', '45754', 'X');
INSERT INTO `cms_region` VALUES ('45772', '西夏区', '3', '45754', 'X');
INSERT INTO `cms_region` VALUES ('45784', '金凤区', '3', '45754', 'J');
INSERT INTO `cms_region` VALUES ('45794', '永宁县', '3', '45754', 'Y');
INSERT INTO `cms_region` VALUES ('45803', '贺兰县', '3', '45754', 'H');
INSERT INTO `cms_region` VALUES ('45813', '灵武市', '3', '45754', 'L');
INSERT INTO `cms_region` VALUES ('45825', '石嘴山市', '2', '45753', 'S');
INSERT INTO `cms_region` VALUES ('45826', '市辖区', '3', '45825', 'S');
INSERT INTO `cms_region` VALUES ('45827', '大武口区', '3', '45825', 'D');
INSERT INTO `cms_region` VALUES ('45839', '惠农区', '3', '45825', 'H');
INSERT INTO `cms_region` VALUES ('45856', '平罗县', '3', '45825', 'P');
INSERT INTO `cms_region` VALUES ('45871', '吴忠市', '2', '45753', 'W');
INSERT INTO `cms_region` VALUES ('45872', '市辖区', '3', '45871', 'S');
INSERT INTO `cms_region` VALUES ('45877', '利通区', '3', '45871', 'L');
INSERT INTO `cms_region` VALUES ('45892', '盐池县', '3', '45871', 'Y');
INSERT INTO `cms_region` VALUES ('45903', '同心县', '3', '45871', 'T');
INSERT INTO `cms_region` VALUES ('45914', '青铜峡市', '3', '45871', 'Q');
INSERT INTO `cms_region` VALUES ('45926', '固原市', '2', '45753', 'G');
INSERT INTO `cms_region` VALUES ('45927', '市辖区', '3', '45926', 'S');
INSERT INTO `cms_region` VALUES ('45928', '原州区', '3', '45926', 'Y');
INSERT INTO `cms_region` VALUES ('45944', '西吉县', '3', '45926', 'X');
INSERT INTO `cms_region` VALUES ('45964', '隆德县', '3', '45926', 'L');
INSERT INTO `cms_region` VALUES ('45978', '泾源县', '3', '45926', 'J');
INSERT INTO `cms_region` VALUES ('45986', '彭阳县', '3', '45926', 'P');
INSERT INTO `cms_region` VALUES ('45999', '中卫市', '2', '45753', 'Z');
INSERT INTO `cms_region` VALUES ('46000', '市辖区', '3', '45999', 'S');
INSERT INTO `cms_region` VALUES ('46012', '沙坡头区', '3', '45999', 'S');
INSERT INTO `cms_region` VALUES ('46013', '中宁县', '3', '45999', 'Z');
INSERT INTO `cms_region` VALUES ('46026', '海原县', '3', '45999', 'H');
INSERT INTO `cms_region` VALUES ('46047', '新疆维吾尔自治区', '1', '0', 'X');
INSERT INTO `cms_region` VALUES ('46048', '乌鲁木齐市', '2', '46047', 'W');
INSERT INTO `cms_region` VALUES ('46049', '市辖区', '3', '46048', 'S');
INSERT INTO `cms_region` VALUES ('46050', '天山区', '3', '46048', 'T');
INSERT INTO `cms_region` VALUES ('46065', '沙依巴克区', '3', '46048', 'S');
INSERT INTO `cms_region` VALUES ('46079', '新市区', '3', '46048', 'X');
INSERT INTO `cms_region` VALUES ('46095', '水磨沟区', '3', '46048', 'S');
INSERT INTO `cms_region` VALUES ('46104', '头屯河区', '3', '46048', 'T');
INSERT INTO `cms_region` VALUES ('46114', '达坂城区', '3', '46048', 'D');
INSERT INTO `cms_region` VALUES ('46123', '东山区', '3', '46048', 'D');
INSERT INTO `cms_region` VALUES ('46128', '乌鲁木齐县', '3', '46048', 'W');
INSERT INTO `cms_region` VALUES ('46138', '克拉玛依市', '2', '46047', 'K');
INSERT INTO `cms_region` VALUES ('46139', '市辖区', '3', '46138', 'S');
INSERT INTO `cms_region` VALUES ('46140', '独山子区', '3', '46138', 'D');
INSERT INTO `cms_region` VALUES ('46144', '克拉玛依区', '3', '46138', 'K');
INSERT INTO `cms_region` VALUES ('46155', '白碱滩区', '3', '46138', 'B');
INSERT INTO `cms_region` VALUES ('46158', '乌尔禾区', '3', '46138', 'W');
INSERT INTO `cms_region` VALUES ('46162', '吐鲁番地区', '2', '46047', 'T');
INSERT INTO `cms_region` VALUES ('46163', '吐鲁番市', '3', '46162', 'T');
INSERT INTO `cms_region` VALUES ('46178', '鄯善县', '3', '46162', 'S');
INSERT INTO `cms_region` VALUES ('46189', '托克逊县', '3', '46162', 'T');
INSERT INTO `cms_region` VALUES ('46197', '哈密地区', '2', '46047', 'H');
INSERT INTO `cms_region` VALUES ('46198', '哈密市', '3', '46197', 'H');
INSERT INTO `cms_region` VALUES ('46230', '巴里坤县', '3', '46197', 'B');
INSERT INTO `cms_region` VALUES ('46246', '伊吾县', '3', '46197', 'Y');
INSERT INTO `cms_region` VALUES ('46255', '昌吉州', '2', '46047', 'C');
INSERT INTO `cms_region` VALUES ('46256', '昌吉市', '3', '46255', 'C');
INSERT INTO `cms_region` VALUES ('46275', '阜康市', '3', '46255', 'F');
INSERT INTO `cms_region` VALUES ('46289', '米泉市', '3', '46255', 'M');
INSERT INTO `cms_region` VALUES ('46299', '呼图壁县', '3', '46255', 'H');
INSERT INTO `cms_region` VALUES ('46316', '玛纳斯', '3', '46255', 'M');
INSERT INTO `cms_region` VALUES ('46337', '奇台县', '3', '46255', 'Q');
INSERT INTO `cms_region` VALUES ('46355', '吉木萨尔县', '3', '46255', 'J');
INSERT INTO `cms_region` VALUES ('46366', '木垒县', '3', '46255', 'M');
INSERT INTO `cms_region` VALUES ('46380', '博州', '2', '46047', 'B');
INSERT INTO `cms_region` VALUES ('46381', '博乐市', '3', '46380', 'B');
INSERT INTO `cms_region` VALUES ('46399', '精河县', '3', '46380', 'J');
INSERT INTO `cms_region` VALUES ('46410', '温泉县', '3', '46380', 'W');
INSERT INTO `cms_region` VALUES ('46422', '巴州', '2', '46047', 'B');
INSERT INTO `cms_region` VALUES ('46423', '库尔勒市', '3', '46422', 'K');
INSERT INTO `cms_region` VALUES ('46451', '轮台县', '3', '46422', 'L');
INSERT INTO `cms_region` VALUES ('46463', '尉犁县', '3', '46422', 'W');
INSERT INTO `cms_region` VALUES ('46476', '若羌县', '3', '46422', 'R');
INSERT INTO `cms_region` VALUES ('46486', '且末县', '3', '46422', 'Q');
INSERT INTO `cms_region` VALUES ('46500', '焉耆县', '3', '46422', 'Y');
INSERT INTO `cms_region` VALUES ('46512', '和静县', '3', '46422', 'H');
INSERT INTO `cms_region` VALUES ('46531', '和硕县', '3', '46422', 'H');
INSERT INTO `cms_region` VALUES ('46542', '博湖县', '3', '46422', 'B');
INSERT INTO `cms_region` VALUES ('46551', '阿克苏地区', '2', '46047', 'A');
INSERT INTO `cms_region` VALUES ('46552', '阿克苏市', '3', '46551', 'A');
INSERT INTO `cms_region` VALUES ('46571', '温宿县', '3', '46551', 'W');
INSERT INTO `cms_region` VALUES ('46592', '库车县', '3', '46551', 'K');
INSERT INTO `cms_region` VALUES ('46617', '沙雅县', '3', '46551', 'S');
INSERT INTO `cms_region` VALUES ('46630', '新和县', '3', '46551', 'X');
INSERT INTO `cms_region` VALUES ('46640', '拜城县', '3', '46551', 'B');
INSERT INTO `cms_region` VALUES ('46657', '乌什县', '3', '46551', 'W');
INSERT INTO `cms_region` VALUES ('46668', '阿瓦提县', '3', '46551', 'A');
INSERT INTO `cms_region` VALUES ('46682', '柯坪县', '3', '46551', 'K');
INSERT INTO `cms_region` VALUES ('46688', '克州', '2', '46047', 'K');
INSERT INTO `cms_region` VALUES ('46689', '阿图什市', '3', '46688', 'A');
INSERT INTO `cms_region` VALUES ('46704', '阿克陶县', '3', '46688', 'A');
INSERT INTO `cms_region` VALUES ('46723', '阿合奇县', '3', '46688', 'A');
INSERT INTO `cms_region` VALUES ('46733', '乌恰县', '3', '46688', 'W');
INSERT INTO `cms_region` VALUES ('46747', '喀什地区', '2', '46047', 'K');
INSERT INTO `cms_region` VALUES ('46748', '喀什市', '3', '46747', 'K');
INSERT INTO `cms_region` VALUES ('46761', '疏附县', '3', '46747', 'S');
INSERT INTO `cms_region` VALUES ('46780', '疏勒县', '3', '46747', 'S');
INSERT INTO `cms_region` VALUES ('46797', '英吉沙县', '3', '46747', 'Y');
INSERT INTO `cms_region` VALUES ('46813', '泽普县', '3', '46747', 'Z');
INSERT INTO `cms_region` VALUES ('46830', '莎车县', '3', '46747', 'S');
INSERT INTO `cms_region` VALUES ('46863', '叶城县', '3', '46747', 'Y');
INSERT INTO `cms_region` VALUES ('46885', '麦盖提县', '3', '46747', 'M');
INSERT INTO `cms_region` VALUES ('46902', '岳普湖县', '3', '46747', 'Y');
INSERT INTO `cms_region` VALUES ('46913', '伽师县', '3', '46747', 'Q');
INSERT INTO `cms_region` VALUES ('46928', '巴楚县', '3', '46747', 'B');
INSERT INTO `cms_region` VALUES ('46942', '塔什库尔干县', '3', '46747', 'T');
INSERT INTO `cms_region` VALUES ('46957', '和田地区', '2', '46047', 'H');
INSERT INTO `cms_region` VALUES ('46958', '和田市', '3', '46957', 'H');
INSERT INTO `cms_region` VALUES ('46971', '和田县', '3', '46957', 'H');
INSERT INTO `cms_region` VALUES ('46983', '墨玉县', '3', '46957', 'M');
INSERT INTO `cms_region` VALUES ('47002', '皮山县', '3', '46957', 'P');
INSERT INTO `cms_region` VALUES ('47020', '洛浦县', '3', '46957', 'L');
INSERT INTO `cms_region` VALUES ('47032', '策勒县', '3', '46957', 'C');
INSERT INTO `cms_region` VALUES ('47042', '于田县', '3', '46957', 'Y');
INSERT INTO `cms_region` VALUES ('47061', '民丰县', '3', '46957', 'M');
INSERT INTO `cms_region` VALUES ('47069', '伊犁州', '2', '46047', 'Y');
INSERT INTO `cms_region` VALUES ('47070', '伊宁市', '3', '47069', 'Y');
INSERT INTO `cms_region` VALUES ('47091', '奎屯市', '3', '47069', 'K');
INSERT INTO `cms_region` VALUES ('47099', '伊宁县', '3', '47069', 'Y');
INSERT INTO `cms_region` VALUES ('47121', '察布查尔县', '3', '47069', 'C');
INSERT INTO `cms_region` VALUES ('47143', '霍城县', '3', '47069', 'H');
INSERT INTO `cms_region` VALUES ('47164', '巩留县', '3', '47069', 'G');
INSERT INTO `cms_region` VALUES ('47180', '新源县', '3', '47069', 'X');
INSERT INTO `cms_region` VALUES ('47196', '昭苏县', '3', '47069', 'Z');
INSERT INTO `cms_region` VALUES ('47214', '特克斯县', '3', '47069', 'T');
INSERT INTO `cms_region` VALUES ('47226', '尼勒克县', '3', '47069', 'N');
INSERT INTO `cms_region` VALUES ('47241', '塔城地区', '2', '46047', 'T');
INSERT INTO `cms_region` VALUES ('47242', '塔城市', '3', '47241', 'T');
INSERT INTO `cms_region` VALUES ('47258', '乌苏市', '3', '47241', 'W');
INSERT INTO `cms_region` VALUES ('47291', '额敏县', '3', '47241', 'E');
INSERT INTO `cms_region` VALUES ('47315', '沙湾县', '3', '47241', 'S');
INSERT INTO `cms_region` VALUES ('47338', '托里县', '3', '47241', 'T');
INSERT INTO `cms_region` VALUES ('47351', '裕民县', '3', '47241', 'Y');
INSERT INTO `cms_region` VALUES ('47360', '和布县', '3', '47241', 'H');
INSERT INTO `cms_region` VALUES ('47374', '阿勒泰地区', '2', '46047', 'A');
INSERT INTO `cms_region` VALUES ('47375', '阿勒泰市', '3', '47374', 'A');
INSERT INTO `cms_region` VALUES ('47393', '布尔津县', '3', '47374', 'B');
INSERT INTO `cms_region` VALUES ('47401', '富蕴县', '3', '47374', 'F');
INSERT INTO `cms_region` VALUES ('47411', '福海县', '3', '47374', 'F');
INSERT INTO `cms_region` VALUES ('47424', '哈巴河县', '3', '47374', 'H');
INSERT INTO `cms_region` VALUES ('47433', '青河县', '3', '47374', 'Q');
INSERT INTO `cms_region` VALUES ('47441', '吉木乃县', '3', '47374', 'J');
INSERT INTO `cms_region` VALUES ('47450', '省直辖行政单位', '2', '46047', 'S');
INSERT INTO `cms_region` VALUES ('47451', '石河子市', '3', '47450', 'S');
INSERT INTO `cms_region` VALUES ('47460', '阿拉尔市', '3', '47450', 'A');
INSERT INTO `cms_region` VALUES ('47477', '图木舒克市', '3', '47450', 'T');
INSERT INTO `cms_region` VALUES ('47486', '五家渠市', '3', '47450', 'W');
INSERT INTO `cms_region` VALUES ('47493', '台湾省', '1', '0', 'T');
INSERT INTO `cms_region` VALUES ('47494', '香港特别行政区', '1', '0', 'X');
INSERT INTO `cms_region` VALUES ('47495', '澳门特别行政区', '1', '0', 'A');
INSERT INTO `cms_region` VALUES ('47496', '龙华新区', '3', '28558', 'L');
INSERT INTO `cms_region` VALUES ('47497', '光明新区', '3', '28558', 'G');
INSERT INTO `cms_region` VALUES ('47500', '新界', '2', '47494', 'X');
INSERT INTO `cms_region` VALUES ('47501', '观塘区', '3', '47498', 'G');
INSERT INTO `cms_region` VALUES ('47502', '黄大仙区', '3', '47498', 'H');
INSERT INTO `cms_region` VALUES ('47503', '九龙城区', '3', '47498', 'J');
INSERT INTO `cms_region` VALUES ('47504', '深水埗区', '3', '47498', 'S');
INSERT INTO `cms_region` VALUES ('47505', '油尖旺区', '3', '47498', 'Y');
INSERT INTO `cms_region` VALUES ('47506', '东区', '3', '47499', 'D');
INSERT INTO `cms_region` VALUES ('47507', '南区', '3', '47499', 'N');
INSERT INTO `cms_region` VALUES ('47508', '湾仔', '3', '47499', 'W');
INSERT INTO `cms_region` VALUES ('47509', '中西区', '3', '47499', 'Z');
INSERT INTO `cms_region` VALUES ('47510', '北区', '3', '47500', 'B');
INSERT INTO `cms_region` VALUES ('47511', '大埔区', '3', '47500', 'D');
INSERT INTO `cms_region` VALUES ('47512', '葵青区', '3', '47500', 'K');
INSERT INTO `cms_region` VALUES ('47513', '离岛区', '3', '47500', 'L');
INSERT INTO `cms_region` VALUES ('47514', '荃湾区', '3', '47500', 'Q');
INSERT INTO `cms_region` VALUES ('47515', '沙田区', '3', '47500', 'S');
INSERT INTO `cms_region` VALUES ('47516', '屯门区', '3', '47500', 'T');
INSERT INTO `cms_region` VALUES ('47517', '西贡区', '3', '47500', 'X');
INSERT INTO `cms_region` VALUES ('47518', '元朗区', '3', '47500', 'Y');
INSERT INTO `cms_region` VALUES ('47519', '澳门半岛', '2', '47495', 'A');
INSERT INTO `cms_region` VALUES ('47520', '离岛', '2', '47495', 'L');
INSERT INTO `cms_region` VALUES ('47521', '大堂区', '3', '47519', 'D');
INSERT INTO `cms_region` VALUES ('47522', '风顺堂区', '3', '47519', 'F');
INSERT INTO `cms_region` VALUES ('47523', '花地玛堂区', '3', '47519', 'H');
INSERT INTO `cms_region` VALUES ('47524', '花王堂区', '3', '47519', 'H');
INSERT INTO `cms_region` VALUES ('47525', '望德堂区', '3', '47519', 'W');
INSERT INTO `cms_region` VALUES ('47526', '嘉模堂区', '3', '47520', 'J');
INSERT INTO `cms_region` VALUES ('47527', '路氹填海区', '3', '47520', 'L');
INSERT INTO `cms_region` VALUES ('47528', '圣方济各堂区', '3', '47520', 'S');
INSERT INTO `cms_region` VALUES ('47529', '高雄市', '2', '47493', 'G');
INSERT INTO `cms_region` VALUES ('47530', '花莲县', '2', '47493', 'H');
INSERT INTO `cms_region` VALUES ('47531', '基隆市', '2', '47493', 'J');
INSERT INTO `cms_region` VALUES ('47532', '嘉义市', '2', '47493', 'J');
INSERT INTO `cms_region` VALUES ('47533', '嘉义县', '2', '47493', 'J');
INSERT INTO `cms_region` VALUES ('47534', '金门县', '2', '47493', 'J');
INSERT INTO `cms_region` VALUES ('47535', '连江县', '2', '47493', 'L');
INSERT INTO `cms_region` VALUES ('47536', '苗栗县', '2', '47493', 'M');
INSERT INTO `cms_region` VALUES ('47537', '南投县', '2', '47493', 'N');
INSERT INTO `cms_region` VALUES ('47538', '澎湖县', '2', '47493', 'P');
INSERT INTO `cms_region` VALUES ('47539', '屏东县', '2', '47493', 'P');
INSERT INTO `cms_region` VALUES ('47540', '台北市', '2', '47493', 'T');
INSERT INTO `cms_region` VALUES ('47541', '台东县', '2', '47493', 'T');
INSERT INTO `cms_region` VALUES ('47542', '台南市', '2', '47493', 'T');
INSERT INTO `cms_region` VALUES ('47543', '台中市', '2', '47493', 'T');
INSERT INTO `cms_region` VALUES ('47544', '桃园市', '2', '47493', 'T');
INSERT INTO `cms_region` VALUES ('47545', '新北市', '2', '47493', 'X');
INSERT INTO `cms_region` VALUES ('47546', '新竹市', '2', '47493', 'X');
INSERT INTO `cms_region` VALUES ('47547', '新竹县', '2', '47493', 'X');
INSERT INTO `cms_region` VALUES ('47548', '宜兰县', '2', '47493', 'Y');
INSERT INTO `cms_region` VALUES ('47549', '云林县', '2', '47493', 'Y');
INSERT INTO `cms_region` VALUES ('47550', '彰化县', '2', '47493', 'Z');
INSERT INTO `cms_region` VALUES ('47551', '阿莲区', '3', '47529', 'A');
INSERT INTO `cms_region` VALUES ('47552', '大寮区', '3', '47529', 'D');
INSERT INTO `cms_region` VALUES ('47553', '大社区', '3', '47529', 'D');
INSERT INTO `cms_region` VALUES ('47554', '大树区', '3', '47529', 'D');
INSERT INTO `cms_region` VALUES ('47555', '凤山区', '3', '47529', 'F');
INSERT INTO `cms_region` VALUES ('47556', '冈山区', '3', '47529', 'G');
INSERT INTO `cms_region` VALUES ('47557', '鼓山区', '3', '47529', 'G');
INSERT INTO `cms_region` VALUES ('47558', '湖内区', '3', '47529', 'H');
INSERT INTO `cms_region` VALUES ('47559', '甲仙区', '3', '47529', 'J');
INSERT INTO `cms_region` VALUES ('47560', '林园区', '3', '47529', 'L');
INSERT INTO `cms_region` VALUES ('47561', '苓雅区', '3', '47529', 'L');
INSERT INTO `cms_region` VALUES ('47562', '六龟区', '3', '47529', 'L');
INSERT INTO `cms_region` VALUES ('47563', '路竹区', '3', '47529', 'L');
INSERT INTO `cms_region` VALUES ('47564', '茂林区', '3', '47529', 'M');
INSERT INTO `cms_region` VALUES ('47565', '美浓区', '3', '47529', 'M');
INSERT INTO `cms_region` VALUES ('47566', '弥陀区', '3', '47529', 'M');
INSERT INTO `cms_region` VALUES ('47567', '楠梓区', '3', '47529', 'N');
INSERT INTO `cms_region` VALUES ('47568', '那玛夏区', '3', '47529', 'N');
INSERT INTO `cms_region` VALUES ('47569', '内门区', '3', '47529', 'N');
INSERT INTO `cms_region` VALUES ('47570', '鸟松区', '3', '47529', 'N');
INSERT INTO `cms_region` VALUES ('47571', '旗津区', '3', '47529', 'Q');
INSERT INTO `cms_region` VALUES ('47572', '旗门区', '3', '47529', 'Q');
INSERT INTO `cms_region` VALUES ('47573', '其它区', '3', '47529', 'Q');
INSERT INTO `cms_region` VALUES ('47574', '前金区', '3', '47529', 'Q');
INSERT INTO `cms_region` VALUES ('47575', '前镇区', '3', '47529', 'Q');
INSERT INTO `cms_region` VALUES ('47576', '桥头区', '3', '47529', 'Q');
INSERT INTO `cms_region` VALUES ('47577', '茄萣区', '3', '47529', 'Q');
INSERT INTO `cms_region` VALUES ('47578', '芩雅区', '3', '47529', 'Q');
INSERT INTO `cms_region` VALUES ('47579', '仁武区', '3', '47529', 'R');
INSERT INTO `cms_region` VALUES ('47580', '三民区', '3', '47529', 'S');
INSERT INTO `cms_region` VALUES ('47581', '杉林区', '3', '47529', 'S');
INSERT INTO `cms_region` VALUES ('47582', '桃源区', '3', '47529', 'T');
INSERT INTO `cms_region` VALUES ('47583', '田寮区', '3', '47529', 'T');
INSERT INTO `cms_region` VALUES ('47584', '小港区', '3', '47529', 'X');
INSERT INTO `cms_region` VALUES ('47585', '新兴区', '3', '47529', 'X');
INSERT INTO `cms_region` VALUES ('47586', '燕巢区', '3', '47529', 'Y');
INSERT INTO `cms_region` VALUES ('47587', '盐埕区', '3', '47529', 'Y');
INSERT INTO `cms_region` VALUES ('47588', '永安区', '3', '47529', 'Y');
INSERT INTO `cms_region` VALUES ('47589', '梓官区', '3', '47529', 'Z');
INSERT INTO `cms_region` VALUES ('47590', '左营区', '3', '47529', 'Z');
INSERT INTO `cms_region` VALUES ('47591', '丰滨乡', '3', '47530', 'F');
INSERT INTO `cms_region` VALUES ('47592', '凤林镇', '3', '47530', 'F');
INSERT INTO `cms_region` VALUES ('47593', '富里乡', '3', '47530', 'F');
INSERT INTO `cms_region` VALUES ('47594', '光复乡', '3', '47530', 'G');
INSERT INTO `cms_region` VALUES ('47595', '花莲市', '3', '47530', 'H');
INSERT INTO `cms_region` VALUES ('47596', '吉安乡', '3', '47530', 'J');
INSERT INTO `cms_region` VALUES ('47597', '瑞穗乡', '3', '47530', 'R');
INSERT INTO `cms_region` VALUES ('47598', '寿丰乡', '3', '47530', 'S');
INSERT INTO `cms_region` VALUES ('47599', '太鲁阁', '3', '47530', 'T');
INSERT INTO `cms_region` VALUES ('47600', '万荣乡', '3', '47530', 'W');
INSERT INTO `cms_region` VALUES ('47601', '新城乡', '3', '47530', 'X');
INSERT INTO `cms_region` VALUES ('47602', '秀林乡', '3', '47530', 'X');
INSERT INTO `cms_region` VALUES ('47603', '玉里镇', '3', '47530', 'Y');
INSERT INTO `cms_region` VALUES ('47604', '卓溪乡', '3', '47530', 'Z');
INSERT INTO `cms_region` VALUES ('47605', '安乐区', '3', '47531', 'A');
INSERT INTO `cms_region` VALUES ('47606', '暖暖区', '3', '47531', 'N');
INSERT INTO `cms_region` VALUES ('47607', '七堵区', '3', '47531', 'Q');
INSERT INTO `cms_region` VALUES ('47608', '其它区', '3', '47531', 'Q');
INSERT INTO `cms_region` VALUES ('47609', '仁爱区', '3', '47531', 'R');
INSERT INTO `cms_region` VALUES ('47610', '信义区', '3', '47531', 'X');
INSERT INTO `cms_region` VALUES ('47611', '中山区', '3', '47531', 'Z');
INSERT INTO `cms_region` VALUES ('47612', '中正区', '3', '47531', 'Z');
INSERT INTO `cms_region` VALUES ('47613', '东区', '3', '47532', 'D');
INSERT INTO `cms_region` VALUES ('47614', '西区', '3', '47532', 'X');
INSERT INTO `cms_region` VALUES ('47615', '其它区', '3', '47532', 'Q');
INSERT INTO `cms_region` VALUES ('47616', '阿里山乡', '3', '47533', 'A');
INSERT INTO `cms_region` VALUES ('47617', '布袋镇', '3', '47533', 'B');
INSERT INTO `cms_region` VALUES ('47618', '大林镇', '3', '47533', 'D');
INSERT INTO `cms_region` VALUES ('47619', '大埔乡', '3', '47533', 'D');
INSERT INTO `cms_region` VALUES ('47620', '东石乡', '3', '47533', 'D');
INSERT INTO `cms_region` VALUES ('47621', '番路乡', '3', '47533', 'F');
INSERT INTO `cms_region` VALUES ('47622', '六脚乡', '3', '47533', 'L');
INSERT INTO `cms_region` VALUES ('47623', '鹿草乡', '3', '47533', 'L');
INSERT INTO `cms_region` VALUES ('47624', '梅山乡', '3', '47533', 'M');
INSERT INTO `cms_region` VALUES ('47625', '民雄乡', '3', '47533', 'M');
INSERT INTO `cms_region` VALUES ('47626', '朴子市', '3', '47533', 'P');
INSERT INTO `cms_region` VALUES ('47627', '水上乡', '3', '47533', 'S');
INSERT INTO `cms_region` VALUES ('47628', '太保市', '3', '47533', 'T');
INSERT INTO `cms_region` VALUES ('47629', '溪口乡', '3', '47533', 'X');
INSERT INTO `cms_region` VALUES ('47630', '新港乡', '3', '47533', 'X');
INSERT INTO `cms_region` VALUES ('47631', '义竹乡', '3', '47533', 'Y');
INSERT INTO `cms_region` VALUES ('47632', '中埔乡', '3', '47533', 'Z');
INSERT INTO `cms_region` VALUES ('47633', '竹崎乡', '3', '47533', 'Z');
INSERT INTO `cms_region` VALUES ('47634', '金城镇', '3', '47534', 'J');
INSERT INTO `cms_region` VALUES ('47635', '金湖镇', '3', '47534', 'J');
INSERT INTO `cms_region` VALUES ('47636', '金宁乡', '3', '47534', 'J');
INSERT INTO `cms_region` VALUES ('47637', '金沙镇', '3', '47534', 'J');
INSERT INTO `cms_region` VALUES ('47638', '烈屿乡', '3', '47534', 'L');
INSERT INTO `cms_region` VALUES ('47639', '乌邱乡', '3', '47534', 'W');
INSERT INTO `cms_region` VALUES ('47640', '北竿乡', '3', '47535', 'B');
INSERT INTO `cms_region` VALUES ('47641', '东引乡', '3', '47535', 'D');
INSERT INTO `cms_region` VALUES ('47642', '莒光乡', '3', '47535', 'J');
INSERT INTO `cms_region` VALUES ('47643', '南竿乡', '3', '47535', 'N');
INSERT INTO `cms_region` VALUES ('47644', '大湖乡', '3', '47536', 'D');
INSERT INTO `cms_region` VALUES ('47645', '公馆乡', '3', '47536', 'G');
INSERT INTO `cms_region` VALUES ('47646', '后龙镇', '3', '47536', 'H');
INSERT INTO `cms_region` VALUES ('47647', '苗栗市', '3', '47536', 'M');
INSERT INTO `cms_region` VALUES ('47648', '南庄乡', '3', '47536', 'N');
INSERT INTO `cms_region` VALUES ('47649', '三湾乡', '3', '47536', 'S');
INSERT INTO `cms_region` VALUES ('47650', '三义乡', '3', '47536', 'S');
INSERT INTO `cms_region` VALUES ('47651', '狮潭乡', '3', '47536', 'S');
INSERT INTO `cms_region` VALUES ('47652', '泰安乡', '3', '47536', 'T');
INSERT INTO `cms_region` VALUES ('47653', '铜锣乡', '3', '47536', 'T');
INSERT INTO `cms_region` VALUES ('47654', '通宵镇', '3', '47536', 'T');
INSERT INTO `cms_region` VALUES ('47655', '头份镇', '3', '47536', 'T');
INSERT INTO `cms_region` VALUES ('47656', '头屋乡', '3', '47536', 'T');
INSERT INTO `cms_region` VALUES ('47657', '西湖乡', '3', '47536', 'X');
INSERT INTO `cms_region` VALUES ('47658', '苑里镇', '3', '47536', 'Y');
INSERT INTO `cms_region` VALUES ('47659', '造桥乡', '3', '47536', 'Z');
INSERT INTO `cms_region` VALUES ('47660', '竹南镇', '3', '47536', 'Z');
INSERT INTO `cms_region` VALUES ('47661', '卓兰镇', '3', '47536', 'Z');
INSERT INTO `cms_region` VALUES ('47662', '草屯镇', '3', '47537', 'C');
INSERT INTO `cms_region` VALUES ('47663', '国姓乡', '3', '47537', 'G');
INSERT INTO `cms_region` VALUES ('47664', '集集镇', '3', '47537', 'J');
INSERT INTO `cms_region` VALUES ('47665', '鹿谷乡', '3', '47537', 'L');
INSERT INTO `cms_region` VALUES ('47666', '名间乡', '3', '47537', 'M');
INSERT INTO `cms_region` VALUES ('47667', '南投市', '3', '47537', 'N');
INSERT INTO `cms_region` VALUES ('47668', '埔里镇', '3', '47537', 'P');
INSERT INTO `cms_region` VALUES ('47669', '仁爱乡', '3', '47537', 'R');
INSERT INTO `cms_region` VALUES ('47670', '水里乡', '3', '47537', 'S');
INSERT INTO `cms_region` VALUES ('47671', '信义乡', '3', '47537', 'X');
INSERT INTO `cms_region` VALUES ('47672', '鱼池乡', '3', '47537', 'Y');
INSERT INTO `cms_region` VALUES ('47673', '中寮乡', '3', '47537', 'Z');
INSERT INTO `cms_region` VALUES ('47674', '竹山镇', '3', '47537', 'Z');
INSERT INTO `cms_region` VALUES ('47675', '白沙乡', '3', '47538', 'B');
INSERT INTO `cms_region` VALUES ('47676', '湖西乡', '3', '47538', 'H');
INSERT INTO `cms_region` VALUES ('47677', '马公市', '3', '47538', 'M');
INSERT INTO `cms_region` VALUES ('47678', '七美乡', '3', '47538', 'Q');
INSERT INTO `cms_region` VALUES ('47679', '望安乡', '3', '47538', 'W');
INSERT INTO `cms_region` VALUES ('47680', '西屿乡', '3', '47538', 'X');
INSERT INTO `cms_region` VALUES ('47681', '长治乡', '3', '47539', 'C');
INSERT INTO `cms_region` VALUES ('47682', '潮州镇', '3', '47539', 'C');
INSERT INTO `cms_region` VALUES ('47683', '车城乡', '3', '47539', 'C');
INSERT INTO `cms_region` VALUES ('47684', '春日乡', '3', '47539', 'C');
INSERT INTO `cms_region` VALUES ('47685', '东港镇', '3', '47539', 'D');
INSERT INTO `cms_region` VALUES ('47686', '枋寮乡', '3', '47539', 'F');
INSERT INTO `cms_region` VALUES ('47687', '枋山乡', '3', '47539', 'F');
INSERT INTO `cms_region` VALUES ('47688', '高树乡', '3', '47539', 'G');
INSERT INTO `cms_region` VALUES ('47689', '恒春镇', '3', '47539', 'H');
INSERT INTO `cms_region` VALUES ('47690', '佳冬乡', '3', '47539', 'J');
INSERT INTO `cms_region` VALUES ('47691', '九如乡', '3', '47539', 'J');
INSERT INTO `cms_region` VALUES ('47692', '崁顶乡', '3', '47539', 'K');
INSERT INTO `cms_region` VALUES ('47693', '来义乡', '3', '47539', 'L');
INSERT INTO `cms_region` VALUES ('47694', '里港乡', '3', '47539', 'L');
INSERT INTO `cms_region` VALUES ('47695', '林边乡', '3', '47539', 'L');
INSERT INTO `cms_region` VALUES ('47696', '麟洛乡', '3', '47539', 'L');
INSERT INTO `cms_region` VALUES ('47697', '琉球乡', '3', '47539', 'L');
INSERT INTO `cms_region` VALUES ('47698', '玛家乡', '3', '47539', 'M');
INSERT INTO `cms_region` VALUES ('47699', '满州乡', '3', '47539', 'M');
INSERT INTO `cms_region` VALUES ('47700', '牡丹乡', '3', '47539', 'M');
INSERT INTO `cms_region` VALUES ('47701', '南州乡', '3', '47539', 'N');
INSERT INTO `cms_region` VALUES ('47702', '内埔乡', '3', '47539', 'N');
INSERT INTO `cms_region` VALUES ('47703', '屏东市', '3', '47539', 'P');
INSERT INTO `cms_region` VALUES ('47704', '三地门乡', '3', '47539', 'S');
INSERT INTO `cms_region` VALUES ('47705', '狮子乡', '3', '47539', 'S');
INSERT INTO `cms_region` VALUES ('47706', '泰武乡', '3', '47539', 'T');
INSERT INTO `cms_region` VALUES ('47707', '万丹乡', '3', '47539', 'W');
INSERT INTO `cms_region` VALUES ('47708', '万峦乡', '3', '47539', 'W');
INSERT INTO `cms_region` VALUES ('47709', '雾台乡', '3', '47539', 'W');
INSERT INTO `cms_region` VALUES ('47710', '新埤乡', '3', '47539', 'X');
INSERT INTO `cms_region` VALUES ('47711', '新园乡', '3', '47539', 'X');
INSERT INTO `cms_region` VALUES ('47712', '盐埔乡', '3', '47539', 'Y');
INSERT INTO `cms_region` VALUES ('47713', '竹田乡', '3', '47539', 'Z');
INSERT INTO `cms_region` VALUES ('47714', '北投区', '3', '47540', 'B');
INSERT INTO `cms_region` VALUES ('47715', '大安区', '3', '47540', 'D');
INSERT INTO `cms_region` VALUES ('47716', '大同区', '3', '47540', 'D');
INSERT INTO `cms_region` VALUES ('47717', '南港区', '3', '47540', 'N');
INSERT INTO `cms_region` VALUES ('47718', '内湖区', '3', '47540', 'N');
INSERT INTO `cms_region` VALUES ('47719', '士林区', '3', '47540', 'S');
INSERT INTO `cms_region` VALUES ('47720', '松山区', '3', '47540', 'S');
INSERT INTO `cms_region` VALUES ('47721', '万华区', '3', '47540', 'W');
INSERT INTO `cms_region` VALUES ('47722', '文山区', '3', '47540', 'W');
INSERT INTO `cms_region` VALUES ('47723', '信义区', '3', '47540', 'X');
INSERT INTO `cms_region` VALUES ('47724', '中山区', '3', '47540', 'Z');
INSERT INTO `cms_region` VALUES ('47725', '中正区', '3', '47540', 'Z');
INSERT INTO `cms_region` VALUES ('47726', '其它区', '3', '47540', 'Q');
INSERT INTO `cms_region` VALUES ('47727', '卑南乡', '3', '47541', 'B');
INSERT INTO `cms_region` VALUES ('47728', '长滨乡', '3', '47541', 'C');
INSERT INTO `cms_region` VALUES ('47729', '成功镇', '3', '47541', 'C');
INSERT INTO `cms_region` VALUES ('47730', '池上乡', '3', '47541', 'C');
INSERT INTO `cms_region` VALUES ('47731', '达仁乡', '3', '47541', 'D');
INSERT INTO `cms_region` VALUES ('47732', '大武乡', '3', '47541', 'D');
INSERT INTO `cms_region` VALUES ('47733', '东河乡', '3', '47541', 'D');
INSERT INTO `cms_region` VALUES ('47734', '关山镇', '3', '47541', 'G');
INSERT INTO `cms_region` VALUES ('47735', '海端乡', '3', '47541', 'H');
INSERT INTO `cms_region` VALUES ('47736', '金峰乡', '3', '47541', 'J');
INSERT INTO `cms_region` VALUES ('47737', '兰屿乡', '3', '47541', 'L');
INSERT INTO `cms_region` VALUES ('47738', '鹿野乡', '3', '47541', 'L');
INSERT INTO `cms_region` VALUES ('47739', '绿岛乡', '3', '47541', 'L');
INSERT INTO `cms_region` VALUES ('47740', '台东市', '3', '47541', 'T');
INSERT INTO `cms_region` VALUES ('47741', '太麻里乡', '3', '47541', 'T');
INSERT INTO `cms_region` VALUES ('47742', '延平乡', '3', '47541', 'Y');
INSERT INTO `cms_region` VALUES ('47743', '中西区', '3', '47542', 'Z');
INSERT INTO `cms_region` VALUES ('47744', '东区', '3', '47542', 'D');
INSERT INTO `cms_region` VALUES ('47745', '南区', '3', '47542', 'N');
INSERT INTO `cms_region` VALUES ('47746', '北区', '3', '47542', 'B');
INSERT INTO `cms_region` VALUES ('47747', '安平区', '3', '47542', 'A');
INSERT INTO `cms_region` VALUES ('47748', '安南区', '3', '47542', 'A');
INSERT INTO `cms_region` VALUES ('47749', '其它区', '3', '47542', 'Q');
INSERT INTO `cms_region` VALUES ('47750', '永康区', '3', '47542', 'Y');
INSERT INTO `cms_region` VALUES ('47751', '归仁区', '3', '47542', 'G');
INSERT INTO `cms_region` VALUES ('47752', '新化区', '3', '47542', 'X');
INSERT INTO `cms_region` VALUES ('47753', '左镇区', '3', '47542', 'Z');
INSERT INTO `cms_region` VALUES ('47754', '玉井区', '3', '47542', 'Y');
INSERT INTO `cms_region` VALUES ('47755', '楠西区', '3', '47542', 'N');
INSERT INTO `cms_region` VALUES ('47756', '南化区', '3', '47542', 'N');
INSERT INTO `cms_region` VALUES ('47757', '仁德区', '3', '47542', 'R');
INSERT INTO `cms_region` VALUES ('47758', '关庙区', '3', '47542', 'G');
INSERT INTO `cms_region` VALUES ('47759', '龙崎区', '3', '47542', 'L');
INSERT INTO `cms_region` VALUES ('47760', '官田区', '3', '47542', 'G');
INSERT INTO `cms_region` VALUES ('47761', '麻豆区', '3', '47542', 'M');
INSERT INTO `cms_region` VALUES ('47762', '佳里区', '3', '47542', 'J');
INSERT INTO `cms_region` VALUES ('47763', '西港区', '3', '47542', 'X');
INSERT INTO `cms_region` VALUES ('47764', '七股区', '3', '47542', 'Q');
INSERT INTO `cms_region` VALUES ('47765', '将军区', '3', '47542', 'J');
INSERT INTO `cms_region` VALUES ('47766', '学甲区', '3', '47542', 'X');
INSERT INTO `cms_region` VALUES ('47767', '北门区', '3', '47542', 'B');
INSERT INTO `cms_region` VALUES ('47768', '新营区', '3', '47542', 'X');
INSERT INTO `cms_region` VALUES ('47769', '后壁区', '3', '47542', 'H');
INSERT INTO `cms_region` VALUES ('47770', '白河区', '3', '47542', 'B');
INSERT INTO `cms_region` VALUES ('47771', '东山区', '3', '47542', 'D');
INSERT INTO `cms_region` VALUES ('47772', '六甲区', '3', '47542', 'L');
INSERT INTO `cms_region` VALUES ('47773', '下营区', '3', '47542', 'X');
INSERT INTO `cms_region` VALUES ('47774', '柳营区', '3', '47542', 'L');
INSERT INTO `cms_region` VALUES ('47775', '盐水区', '3', '47542', 'Y');
INSERT INTO `cms_region` VALUES ('47776', '善化区', '3', '47542', 'S');
INSERT INTO `cms_region` VALUES ('47777', '大内区', '3', '47542', 'D');
INSERT INTO `cms_region` VALUES ('47778', '山上区', '3', '47542', 'S');
INSERT INTO `cms_region` VALUES ('47779', '新市区', '3', '47542', 'X');
INSERT INTO `cms_region` VALUES ('47780', '安定区', '3', '47542', 'A');
INSERT INTO `cms_region` VALUES ('47781', '中区', '3', '47543', 'Z');
INSERT INTO `cms_region` VALUES ('47782', '东区', '3', '47543', 'D');
INSERT INTO `cms_region` VALUES ('47783', '南区', '3', '47543', 'N');
INSERT INTO `cms_region` VALUES ('47784', '西区', '3', '47543', 'X');
INSERT INTO `cms_region` VALUES ('47785', '北区', '3', '47543', 'B');
INSERT INTO `cms_region` VALUES ('47786', '北屯区', '3', '47543', 'B');
INSERT INTO `cms_region` VALUES ('47787', '西屯区', '3', '47543', 'X');
INSERT INTO `cms_region` VALUES ('47788', '南屯区', '3', '47543', 'N');
INSERT INTO `cms_region` VALUES ('47789', '其它区', '3', '47543', 'Q');
INSERT INTO `cms_region` VALUES ('47790', '太平区', '3', '47543', 'T');
INSERT INTO `cms_region` VALUES ('47791', '大里区', '3', '47543', 'D');
INSERT INTO `cms_region` VALUES ('47792', '雾峰区', '3', '47543', 'W');
INSERT INTO `cms_region` VALUES ('47793', '乌日区', '3', '47543', 'W');
INSERT INTO `cms_region` VALUES ('47794', '丰原区', '3', '47543', 'F');
INSERT INTO `cms_region` VALUES ('47795', '后里区', '3', '47543', 'H');
INSERT INTO `cms_region` VALUES ('47796', '石冈区', '3', '47543', 'S');
INSERT INTO `cms_region` VALUES ('47797', '东势区', '3', '47543', 'D');
INSERT INTO `cms_region` VALUES ('47798', '和平区', '3', '47543', 'H');
INSERT INTO `cms_region` VALUES ('47799', '新社区', '3', '47543', 'X');
INSERT INTO `cms_region` VALUES ('47800', '潭子区', '3', '47543', 'T');
INSERT INTO `cms_region` VALUES ('47801', '大雅区', '3', '47543', 'D');
INSERT INTO `cms_region` VALUES ('47802', '神冈区', '3', '47543', 'S');
INSERT INTO `cms_region` VALUES ('47803', '大肚区', '3', '47543', 'D');
INSERT INTO `cms_region` VALUES ('47804', '沙鹿区', '3', '47543', 'S');
INSERT INTO `cms_region` VALUES ('47805', '龙井区', '3', '47543', 'L');
INSERT INTO `cms_region` VALUES ('47806', '梧栖区', '3', '47543', 'W');
INSERT INTO `cms_region` VALUES ('47807', '清水区', '3', '47543', 'Q');
INSERT INTO `cms_region` VALUES ('47808', '大甲区', '3', '47543', 'D');
INSERT INTO `cms_region` VALUES ('47809', '外埔区', '3', '47543', 'W');
INSERT INTO `cms_region` VALUES ('47810', '大安区', '3', '47543', 'D');
INSERT INTO `cms_region` VALUES ('47811', '中坜区', '3', '47544', 'Z');
INSERT INTO `cms_region` VALUES ('47812', '平镇区', '3', '47544', 'P');
INSERT INTO `cms_region` VALUES ('47813', '龙潭区', '3', '47544', 'L');
INSERT INTO `cms_region` VALUES ('47814', '杨梅区', '3', '47544', 'Y');
INSERT INTO `cms_region` VALUES ('47815', '新屋区', '3', '47544', 'X');
INSERT INTO `cms_region` VALUES ('47816', '观音区', '3', '47544', 'G');
INSERT INTO `cms_region` VALUES ('47817', '桃园区', '3', '47544', 'T');
INSERT INTO `cms_region` VALUES ('47818', '龟山区', '3', '47544', 'G');
INSERT INTO `cms_region` VALUES ('47819', '八德区', '3', '47544', 'B');
INSERT INTO `cms_region` VALUES ('47820', '大溪区', '3', '47544', 'D');
INSERT INTO `cms_region` VALUES ('47821', '复兴区', '3', '47544', 'F');
INSERT INTO `cms_region` VALUES ('47822', '大园区', '3', '47544', 'D');
INSERT INTO `cms_region` VALUES ('47823', '芦竹区', '3', '47544', 'L');
INSERT INTO `cms_region` VALUES ('47824', '万里区', '3', '47545', 'W');
INSERT INTO `cms_region` VALUES ('47825', '金山区', '3', '47545', 'J');
INSERT INTO `cms_region` VALUES ('47826', '板桥区', '3', '47545', 'B');
INSERT INTO `cms_region` VALUES ('47827', '汐止区', '3', '47545', 'X');
INSERT INTO `cms_region` VALUES ('47828', '深坑区', '3', '47545', 'S');
INSERT INTO `cms_region` VALUES ('47829', '石碇区', '3', '47545', 'S');
INSERT INTO `cms_region` VALUES ('47830', '瑞芳区', '3', '47545', 'R');
INSERT INTO `cms_region` VALUES ('47831', '平溪区', '3', '47545', 'P');
INSERT INTO `cms_region` VALUES ('47832', '双溪区', '3', '47545', 'S');
INSERT INTO `cms_region` VALUES ('47833', '贡寮区', '3', '47545', 'G');
INSERT INTO `cms_region` VALUES ('47834', '新店区', '3', '47545', 'X');
INSERT INTO `cms_region` VALUES ('47835', '坪林区', '3', '47545', 'P');
INSERT INTO `cms_region` VALUES ('47836', '乌来区', '3', '47545', 'W');
INSERT INTO `cms_region` VALUES ('47837', '永和区', '3', '47545', 'Y');
INSERT INTO `cms_region` VALUES ('47838', '中和区', '3', '47545', 'Z');
INSERT INTO `cms_region` VALUES ('47839', '土城区', '3', '47545', 'T');
INSERT INTO `cms_region` VALUES ('47840', '三峡区', '3', '47545', 'S');
INSERT INTO `cms_region` VALUES ('47841', '树林区', '3', '47545', 'S');
INSERT INTO `cms_region` VALUES ('47842', '莺歌区', '3', '47545', 'Y');
INSERT INTO `cms_region` VALUES ('47843', '三重区', '3', '47545', 'S');
INSERT INTO `cms_region` VALUES ('47844', '新庄区', '3', '47545', 'X');
INSERT INTO `cms_region` VALUES ('47845', '泰山区', '3', '47545', 'T');
INSERT INTO `cms_region` VALUES ('47846', '林口区', '3', '47545', 'L');
INSERT INTO `cms_region` VALUES ('47847', '芦洲区', '3', '47545', 'L');
INSERT INTO `cms_region` VALUES ('47848', '五股区', '3', '47545', 'W');
INSERT INTO `cms_region` VALUES ('47849', '八里区', '3', '47545', 'B');
INSERT INTO `cms_region` VALUES ('47850', '淡水区', '3', '47545', 'D');
INSERT INTO `cms_region` VALUES ('47851', '三芝区', '3', '47545', 'S');
INSERT INTO `cms_region` VALUES ('47852', '石门区', '3', '47545', 'S');
INSERT INTO `cms_region` VALUES ('47853', '东区', '3', '47546', 'D');
INSERT INTO `cms_region` VALUES ('47854', '北区', '3', '47546', 'B');
INSERT INTO `cms_region` VALUES ('47855', '香山区', '3', '47546', 'X');
INSERT INTO `cms_region` VALUES ('47856', '其它区', '3', '47546', 'Q');
INSERT INTO `cms_region` VALUES ('47857', '竹北市', '3', '47547', 'Z');
INSERT INTO `cms_region` VALUES ('47858', '湖口乡', '3', '47547', 'H');
INSERT INTO `cms_region` VALUES ('47859', '新丰乡', '3', '47547', 'X');
INSERT INTO `cms_region` VALUES ('47860', '新埔镇', '3', '47547', 'X');
INSERT INTO `cms_region` VALUES ('47861', '关西镇', '3', '47547', 'G');
INSERT INTO `cms_region` VALUES ('47862', '芎林乡', '3', '47547', 'X');
INSERT INTO `cms_region` VALUES ('47863', '宝山乡', '3', '47547', 'B');
INSERT INTO `cms_region` VALUES ('47864', '竹东镇', '3', '47547', 'Z');
INSERT INTO `cms_region` VALUES ('47865', '五峰乡', '3', '47547', 'W');
INSERT INTO `cms_region` VALUES ('47866', '横山乡', '3', '47547', 'H');
INSERT INTO `cms_region` VALUES ('47867', '尖石乡', '3', '47547', 'J');
INSERT INTO `cms_region` VALUES ('47868', '北埔乡', '3', '47547', 'B');
INSERT INTO `cms_region` VALUES ('47869', '峨眉乡', '3', '47547', 'E');
INSERT INTO `cms_region` VALUES ('47870', '宜兰市', '3', '47548', 'Y');
INSERT INTO `cms_region` VALUES ('47871', '头城镇', '3', '47548', 'T');
INSERT INTO `cms_region` VALUES ('47872', '礁溪乡', '3', '47548', 'J');
INSERT INTO `cms_region` VALUES ('47873', '壮围乡', '3', '47548', 'Z');
INSERT INTO `cms_region` VALUES ('47874', '员山乡', '3', '47548', 'Y');
INSERT INTO `cms_region` VALUES ('47875', '罗东镇', '3', '47548', 'L');
INSERT INTO `cms_region` VALUES ('47876', '三星乡', '3', '47548', 'S');
INSERT INTO `cms_region` VALUES ('47877', '大同乡', '3', '47548', 'D');
INSERT INTO `cms_region` VALUES ('47878', '五结乡', '3', '47548', 'W');
INSERT INTO `cms_region` VALUES ('47879', '冬山乡', '3', '47548', 'D');
INSERT INTO `cms_region` VALUES ('47880', '苏澳镇', '3', '47548', 'S');
INSERT INTO `cms_region` VALUES ('47881', '南澳乡', '3', '47548', 'N');
INSERT INTO `cms_region` VALUES ('47882', '钓鱼台', '3', '47548', 'D');
INSERT INTO `cms_region` VALUES ('47883', '斗南镇', '3', '47549', 'D');
INSERT INTO `cms_region` VALUES ('47884', '大埤乡', '3', '47549', 'D');
INSERT INTO `cms_region` VALUES ('47885', '虎尾镇', '3', '47549', 'H');
INSERT INTO `cms_region` VALUES ('47886', '土库镇', '3', '47549', 'T');
INSERT INTO `cms_region` VALUES ('47887', '褒忠乡', '3', '47549', 'B');
INSERT INTO `cms_region` VALUES ('47888', '东势乡', '3', '47549', 'D');
INSERT INTO `cms_region` VALUES ('47889', '台西乡', '3', '47549', 'T');
INSERT INTO `cms_region` VALUES ('47890', '仑背乡', '3', '47549', 'L');
INSERT INTO `cms_region` VALUES ('47891', '麦寮乡', '3', '47549', 'M');
INSERT INTO `cms_region` VALUES ('47892', '斗六市', '3', '47549', 'D');
INSERT INTO `cms_region` VALUES ('47893', '林内乡', '3', '47549', 'L');
INSERT INTO `cms_region` VALUES ('47894', '古坑乡', '3', '47549', 'G');
INSERT INTO `cms_region` VALUES ('47895', '莿桐乡', '3', '47549', 'C');
INSERT INTO `cms_region` VALUES ('47896', '西螺镇', '3', '47549', 'X');
INSERT INTO `cms_region` VALUES ('47897', '二仑乡', '3', '47549', 'E');
INSERT INTO `cms_region` VALUES ('47898', '北港镇', '3', '47549', 'B');
INSERT INTO `cms_region` VALUES ('47899', '水林乡', '3', '47549', 'S');
INSERT INTO `cms_region` VALUES ('47900', '口湖乡', '3', '47549', 'K');
INSERT INTO `cms_region` VALUES ('47901', '四湖乡', '3', '47549', 'S');
INSERT INTO `cms_region` VALUES ('47902', '元长乡', '3', '47549', 'Y');
INSERT INTO `cms_region` VALUES ('47903', '彰化市', '3', '47550', 'Z');
INSERT INTO `cms_region` VALUES ('47904', '芬园乡', '3', '47550', 'F');
INSERT INTO `cms_region` VALUES ('47905', '花坛乡', '3', '47550', 'H');
INSERT INTO `cms_region` VALUES ('47906', '秀水乡', '3', '47550', 'X');
INSERT INTO `cms_region` VALUES ('47907', '鹿港镇', '3', '47550', 'L');
INSERT INTO `cms_region` VALUES ('47908', '福兴乡', '3', '47550', 'F');
INSERT INTO `cms_region` VALUES ('47909', '线西乡', '3', '47550', 'X');
INSERT INTO `cms_region` VALUES ('47910', '和美镇', '3', '47550', 'H');
INSERT INTO `cms_region` VALUES ('47911', '伸港乡', '3', '47550', 'S');
INSERT INTO `cms_region` VALUES ('47912', '员林镇', '3', '47550', 'Y');
INSERT INTO `cms_region` VALUES ('47913', '社头乡', '3', '47550', 'S');
INSERT INTO `cms_region` VALUES ('47914', '永靖乡', '3', '47550', 'Y');
INSERT INTO `cms_region` VALUES ('47915', '埔心乡', '3', '47550', 'P');
INSERT INTO `cms_region` VALUES ('47916', '溪湖镇', '3', '47550', 'X');
INSERT INTO `cms_region` VALUES ('47917', '大村乡', '3', '47550', 'D');
INSERT INTO `cms_region` VALUES ('47918', '埔盐乡', '3', '47550', 'P');
INSERT INTO `cms_region` VALUES ('47919', '田中镇', '3', '47550', 'T');
INSERT INTO `cms_region` VALUES ('47920', '北斗镇', '3', '47550', 'B');
INSERT INTO `cms_region` VALUES ('47921', '田尾乡', '3', '47550', 'T');
INSERT INTO `cms_region` VALUES ('47922', '埤头乡', '3', '47550', 'P');
INSERT INTO `cms_region` VALUES ('47923', '溪州乡', '3', '47550', 'X');
INSERT INTO `cms_region` VALUES ('47924', '竹塘乡', '3', '47550', 'Z');
INSERT INTO `cms_region` VALUES ('47925', '二林镇', '3', '47550', 'E');
INSERT INTO `cms_region` VALUES ('47926', '大城乡', '3', '47550', 'D');
INSERT INTO `cms_region` VALUES ('47927', '芳苑乡', '3', '47550', 'F');
INSERT INTO `cms_region` VALUES ('47928', '二水乡', '3', '47550', 'E');
INSERT INTO `cms_region` VALUES ('47929', '莲池区', '3', '1772', 'L');
INSERT INTO `cms_region` VALUES ('47930', '竞秀区', '3', '1772', 'J');
INSERT INTO `cms_region` VALUES ('47931', '常平镇', '3', '29855', 'C');
INSERT INTO `cms_region` VALUES ('47932', '茶山镇', '3', '29855', 'C');
INSERT INTO `cms_region` VALUES ('47933', '大朗镇', '3', '29855', 'D');
INSERT INTO `cms_region` VALUES ('47934', '大岭山镇', '3', '29855', 'D');
INSERT INTO `cms_region` VALUES ('47935', '道滘镇', '3', '29855', 'D');
INSERT INTO `cms_region` VALUES ('47936', '东城街道', '3', '29855', 'D');
INSERT INTO `cms_region` VALUES ('47937', '东坑镇', '3', '29855', 'D');
INSERT INTO `cms_region` VALUES ('47938', '凤岗镇', '3', '29855', 'F');
INSERT INTO `cms_region` VALUES ('47939', '高埗镇', '3', '29855', 'G');
INSERT INTO `cms_region` VALUES ('47940', '莞城街道', '3', '29855', 'G');
INSERT INTO `cms_region` VALUES ('47941', '横沥镇', '3', '29855', 'H');
INSERT INTO `cms_region` VALUES ('47942', '洪梅镇', '3', '29855', 'H');
INSERT INTO `cms_region` VALUES ('47943', '厚街镇', '3', '29855', 'H');
INSERT INTO `cms_region` VALUES ('47944', '黄江镇', '3', '29855', 'H');
INSERT INTO `cms_region` VALUES ('47945', '虎门镇', '3', '29855', 'H');
INSERT INTO `cms_region` VALUES ('47946', '寮步镇', '3', '29855', 'L');
INSERT INTO `cms_region` VALUES ('47947', '麻涌镇', '3', '29855', 'M');
INSERT INTO `cms_region` VALUES ('47948', '南城街道', '3', '29855', 'N');
INSERT INTO `cms_region` VALUES ('47949', '桥头镇', '3', '29855', 'Q');
INSERT INTO `cms_region` VALUES ('47950', '清溪镇', '3', '29855', 'Q');
INSERT INTO `cms_region` VALUES ('47951', '企石镇', '3', '29855', 'Q');
INSERT INTO `cms_region` VALUES ('47952', '沙田镇', '3', '29855', 'S');
INSERT INTO `cms_region` VALUES ('47953', '石碣镇', '3', '29855', 'S');
INSERT INTO `cms_region` VALUES ('47954', '石龙镇', '3', '29855', 'S');
INSERT INTO `cms_region` VALUES ('47955', '石排镇', '3', '29855', 'S');
INSERT INTO `cms_region` VALUES ('47956', '松山湖管委会', '3', '29855', 'S');
INSERT INTO `cms_region` VALUES ('47957', '塘厦镇', '3', '29855', 'T');
INSERT INTO `cms_region` VALUES ('47958', '望牛墩镇', '3', '29855', 'W');
INSERT INTO `cms_region` VALUES ('47959', '万江街道', '3', '29855', 'W');
INSERT INTO `cms_region` VALUES ('47960', '谢岗镇', '3', '29855', 'X');
INSERT INTO `cms_region` VALUES ('47961', '长安镇', '3', '29855', 'Z');
INSERT INTO `cms_region` VALUES ('47962', '樟木头镇', '3', '29855', 'Z');
INSERT INTO `cms_region` VALUES ('47963', '中堂镇', '3', '29855', 'Z');
INSERT INTO `cms_region` VALUES ('47964', '海外', '1', '0', 'H');
INSERT INTO `cms_region` VALUES ('47965', '海外', '2', '47964', 'H');
INSERT INTO `cms_region` VALUES ('47966', '海外', '3', '47965', 'H');
INSERT INTO `cms_region` VALUES ('47968', '吉阳区', '3', '31618', 'J');
INSERT INTO `cms_region` VALUES ('47969', '天涯区', '3', '31618', 'T');
INSERT INTO `cms_region` VALUES ('47970', '崖州区', '3', '31618', 'Y');
INSERT INTO `cms_region` VALUES ('47971', '济源市', '2', '21387', 'J');
INSERT INTO `cms_region` VALUES ('47972', '沁园街道', '3', '47971', 'Q');
INSERT INTO `cms_region` VALUES ('47973', '济水街道', '3', '47971', 'J');
INSERT INTO `cms_region` VALUES ('47974', '北海街道', '3', '47971', 'B');
INSERT INTO `cms_region` VALUES ('47975', '天坛街道', '3', '47971', 'T');
INSERT INTO `cms_region` VALUES ('47976', '玉泉街道', '3', '47971', 'Y');
INSERT INTO `cms_region` VALUES ('47977', '克井镇', '3', '47971', 'K');
INSERT INTO `cms_region` VALUES ('47978', '五龙口镇', '3', '47971', 'W');
INSERT INTO `cms_region` VALUES ('47979', '轵城镇', '3', '47971', 'Z');
INSERT INTO `cms_region` VALUES ('47980', '承留镇', '3', '47971', 'C');
INSERT INTO `cms_region` VALUES ('47981', '邵原镇', '3', '47971', 'S');
INSERT INTO `cms_region` VALUES ('47982', '坡头镇', '3', '47971', 'P');
INSERT INTO `cms_region` VALUES ('47983', '梨林镇', '3', '47971', 'L');
INSERT INTO `cms_region` VALUES ('47984', '大峪镇', '3', '47971', 'D');
INSERT INTO `cms_region` VALUES ('47985', '思礼镇', '3', '47971', 'S');
INSERT INTO `cms_region` VALUES ('47986', '王屋镇', '3', '47971', 'W');
INSERT INTO `cms_region` VALUES ('47987', '下冶镇', '3', '47971', 'X');
INSERT INTO `cms_region` VALUES ('47988', '龙港市', '3', '12974', 'L');
INSERT INTO `cms_region` VALUES ('29891', '黄圃镇', '3', '29890', 'H');
INSERT INTO `cms_region` VALUES ('29892', '南头镇', '3', '29890', 'N');
INSERT INTO `cms_region` VALUES ('29893', '东凤镇', '3', '29890', 'D');
INSERT INTO `cms_region` VALUES ('29894', '阜沙镇', '3', '29890', 'F');
INSERT INTO `cms_region` VALUES ('29895', '小榄镇', '3', '29890', 'X');
INSERT INTO `cms_region` VALUES ('29896', '古镇镇', '3', '29890', 'G');
INSERT INTO `cms_region` VALUES ('29897', '横栏镇', '3', '29890', 'H');
INSERT INTO `cms_region` VALUES ('29898', '三角镇', '3', '29890', 'S');
INSERT INTO `cms_region` VALUES ('29899', '港口镇', '3', '29890', 'G');
INSERT INTO `cms_region` VALUES ('29900', '大涌镇', '3', '29890', 'D');
INSERT INTO `cms_region` VALUES ('29901', '沙溪镇', '3', '29890', 'S');
INSERT INTO `cms_region` VALUES ('29902', '三乡镇', '3', '29890', 'S');
INSERT INTO `cms_region` VALUES ('29903', '板芙镇', '3', '29890', 'B');
INSERT INTO `cms_region` VALUES ('29904', '神湾镇', '3', '29890', 'S');
INSERT INTO `cms_region` VALUES ('29905', '坦洲镇', '3', '29890', 'T');
INSERT INTO `cms_region` VALUES ('29906', '中山火炬高技术产业开发区', '3', '29890', 'Z');
INSERT INTO `cms_region` VALUES ('29907', '翠亨新区', '3', '29890', 'C');
INSERT INTO `cms_region` VALUES ('29908', '石岐街道', '3', '29890', 'S');
INSERT INTO `cms_region` VALUES ('29909', '东区街道', '3', '29890', 'D');
INSERT INTO `cms_region` VALUES ('29910', '西区街道', '3', '29890', 'X');
INSERT INTO `cms_region` VALUES ('29911', '南区街道', '3', '29890', 'N');
INSERT INTO `cms_region` VALUES ('29912', '五桂山街道', '3', '29890', 'W');
INSERT INTO `cms_region` VALUES ('29913', '火炬开发区街道', '3', '29890', 'H');
INSERT INTO `cms_region` VALUES ('29914', '民众街道', '3', '29890', 'M');
INSERT INTO `cms_region` VALUES ('29918', '南朗街道', '3', '29890', 'N');
CREATE TABLE `cms_search_locking` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `users_id` int(10) DEFAULT '0' COMMENT '用户ID',
  `ip` varchar(20) DEFAULT '' COMMENT 'ip',
  `locking_time` int(11) DEFAULT '0' COMMENT '锁定时间',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='搜索记录锁定表';
CREATE TABLE `cms_search_word` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `word` varchar(255) DEFAULT '' COMMENT '关键词',
  `searchNum` int(10) DEFAULT '1' COMMENT '搜索次数',
  `resultNum` int(10) DEFAULT '0' COMMENT '搜索结果数量',
  `sort_order` int(10) DEFAULT '0' COMMENT '排序号',
  `users_id` int(11) DEFAULT '0' COMMENT '用户id',
  `ip` varchar(20) DEFAULT '' COMMENT 'ip',
  `is_hot` tinyint(1) DEFAULT '0' COMMENT '是否热搜',
  `lang` varchar(30) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `word` (`word`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='搜索词统计表';
CREATE TABLE `cms_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT '' COMMENT '配置的key键名',
  `value` text,
  `inc_type` varchar(64) DEFAULT '' COMMENT '配置分组',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `inc_type` (`inc_type`,`lang`)
) ENGINE=InnoDB AUTO_INCREMENT=157 DEFAULT CHARSET=utf8 COMMENT='系统非全局配置表';
CREATE TABLE `cms_sharp_active` (
  `active_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '活动会场ID',
  `active_date` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动日期',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '活动状态(0禁用 1启用)',
  `is_del` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`active_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='整点秒杀-活动会场表';
CREATE TABLE `cms_sharp_active_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `active_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动会场ID',
  `active_time_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动场次ID',
  `aid` int(11) NOT NULL DEFAULT '0' COMMENT '文档id',
  `sharp_goods_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '秒杀商品ID',
  `sales_actual` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '实际销量',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='整点秒杀-活动会场与商品关联表';
CREATE TABLE `cms_sharp_active_time` (
  `active_time_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '场次ID',
  `active_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '活动会场ID',
  `active_time` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '场次时间(0点-23点)',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '活动状态(0禁用 1启用)',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`active_time_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='整点秒杀-活动会场场次表';
CREATE TABLE `cms_sharp_goods` (
  `sharp_goods_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '秒杀商品ID',
  `aid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID->aid',
  `limit` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '限购数量',
  `seckill_stock` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '秒杀商品库存总量',
  `seckill_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '秒杀价格',
  `sales` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '累积销量',
  `virtual_sales` int(11) NOT NULL DEFAULT '0' COMMENT '虚拟销量',
  `sort_order` int(11) unsigned NOT NULL DEFAULT '100' COMMENT '商品排序(数字越小越靠前)',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '商品状态(0下架 1上架)',
  `is_del` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  `is_sku` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-多规格商品',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`sharp_goods_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='整点秒杀-商品表';
CREATE TABLE `cms_sharp_setting` (
  `key` varchar(30) NOT NULL DEFAULT '' COMMENT '设置项标示',
  `describe` varchar(255) NOT NULL DEFAULT '' COMMENT '设置项描述',
  `values` mediumtext NOT NULL COMMENT '设置内容(json格式)',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  UNIQUE KEY `unique_key` (`key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='整点秒杀设置表';
CREATE TABLE `cms_shop_address` (
  `addr_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '地址id',
  `users_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员id',
  `consignee` varchar(60) NOT NULL DEFAULT '' COMMENT '收货人',
  `country` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '国家',
  `province` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '省份',
  `city` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '城市',
  `district` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '县区',
  `address` varchar(500) NOT NULL DEFAULT '' COMMENT '详细地址',
  `zipcode` varchar(10) NOT NULL DEFAULT '' COMMENT '邮政编码',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机',
  `is_default` tinyint(1) DEFAULT '0' COMMENT '是否默认，0否，1是。',
  `lang` varchar(30) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`addr_id`),
  KEY `users_id` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='收货地址表';
CREATE TABLE `cms_shop_cart` (
  `cart_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '购物车表',
  `users_id` int(10) unsigned DEFAULT '0' COMMENT '会员id',
  `product_id` int(10) unsigned DEFAULT '0' COMMENT '产品id',
  `product_num` int(10) unsigned DEFAULT '0' COMMENT '购买数量',
  `spec_value_id` varchar(100) DEFAULT '' COMMENT '规格值ID',
  `selected` tinyint(1) DEFAULT '1' COMMENT '购物车选中状态：0未选中，1选中',
  `lang` varchar(30) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '加入购物车的时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `discount_active_id` int(11) DEFAULT '0' COMMENT '限时折扣ID,用来区分购物车的商品哪些是限时折扣活动的',
  PRIMARY KEY (`cart_id`),
  KEY `users_id` (`users_id`,`product_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='购物车表';
CREATE TABLE `cms_shop_coupon` (
  `coupon_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `coupon_code` varchar(100) NOT NULL DEFAULT '' COMMENT '优惠券编号',
  `coupon_name` varchar(100) NOT NULL DEFAULT '' COMMENT '优惠券名称',
  `coupon_color` varchar(25) NOT NULL DEFAULT '' COMMENT '优惠券颜色',
  `coupon_form` tinyint(1) NOT NULL DEFAULT '1' COMMENT '优惠券类型 1-满减券',
  `coupon_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '可使用商品(1全站通用，2指定商品，3指定商品分类)',
  `product_id` varchar(255) NOT NULL DEFAULT '' COMMENT '指定商品ID，在coupon_type=2时使用',
  `arctype_id` varchar(255) NOT NULL DEFAULT '' COMMENT '指定商品分类ID，在coupon_type=3时使用',
  `coupon_price` decimal(10,0) NOT NULL DEFAULT '0' COMMENT '优惠券金额，例如10',
  `conditions_use` decimal(10,0) NOT NULL DEFAULT '0' COMMENT '优惠券使用条件，例如300',
  `coupon_stock` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '优惠券库存，例如100',
  `redeem_points` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '兑换优惠券所需积分，为0则表示免费兑换',
  `redeem_authority` varchar(10) NOT NULL DEFAULT '' COMMENT '兑换权限，存入多个会员等级组ID',
  `valid_days` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '有效天数，例如30',
  `start_date` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '优惠券开放领取时间',
  `end_date` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '优惠券结束领取时间',
  `sort_order` int(11) unsigned NOT NULL DEFAULT '100' COMMENT '规格排序号',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '优惠券状态(0=关闭，1=开启)',
  `use_type` int(1) NOT NULL DEFAULT '1' COMMENT '使用期限 \r\n1-固定日期\r\n 2-领取后当天开始N(valid_days)天内有效\r\n 2-领取后次日开始N(valid_days)天内有效',
  `use_start_time` int(11) NOT NULL COMMENT '使用期限开始时间',
  `use_end_time` int(11) NOT NULL COMMENT '使用期限结束时间',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '0-未删除 1-已删除',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`coupon_id`),
  KEY `product_id` (`product_id`) USING BTREE,
  KEY `arctype_id` (`arctype_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠券主表';
CREATE TABLE `cms_shop_coupon_use` (
  `use_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `users_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `coupon_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '优惠券ID',
  `coupon_code` varchar(100) NOT NULL DEFAULT '' COMMENT '优惠券编号',
  `get_ip` varchar(15) NOT NULL DEFAULT '' COMMENT '领取时的IP地址',
  `get_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '优惠券领取时的时间',
  `use_status` tinyint(4) unsigned NOT NULL DEFAULT '1' COMMENT '优惠券使用状态(0未使用，1已使用，2已过期，3已冻结)',
  `use_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '优惠券使用时的时间',
  `start_time` int(10) NOT NULL DEFAULT '0' COMMENT '优惠券有效开始时间',
  `end_time` int(10) NOT NULL DEFAULT '0' COMMENT '优惠券有效结束时间',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`use_id`),
  KEY `coupon_id` (`coupon_id`) USING BTREE,
  KEY `coupon_code` (`coupon_code`) USING BTREE,
  KEY `users_id` (`users_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='优惠券-领取记录表';
CREATE TABLE `cms_shop_express` (
  `express_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `express_code` varchar(32) NOT NULL DEFAULT '' COMMENT '物流code（快递100）',
  `express_name` varchar(32) NOT NULL DEFAULT '' COMMENT '物流名称',
  `express_lnitials` varchar(5) NOT NULL DEFAULT '' COMMENT '首字母',
  `is_choose` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '快递公司是否选中(0=否，1=是)',
  `sort_order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序号',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `wx_delivery_id` varchar(20) DEFAULT '' COMMENT '微信快递编码',
  PRIMARY KEY (`express_id`)
) ENGINE=InnoDB AUTO_INCREMENT=598 DEFAULT CHARSET=utf8 COMMENT='快递公司表';
CREATE TABLE `cms_shop_goods_label` (
  `label_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `label_title` varchar(60) NOT NULL DEFAULT '' COMMENT '标签标题',
  `label_pic` varchar(250) NOT NULL DEFAULT '' COMMENT '标签路径',
  `label_intro` varchar(500) NOT NULL DEFAULT '' COMMENT '标签描述',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态(1:启用; 2:禁用)',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`label_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='商城商品服务标签';
CREATE TABLE `cms_shop_goods_label_bind` (
  `bind_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `aid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID(archives 表 aid 字段)',
  `label_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商品服务标签ID(shop_goods_label 表 label_id 字段)',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`bind_id`),
  KEY `aid` (`aid`) USING BTREE,
  KEY `label_id` (`label_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商城商品服务标签与商品ID关联绑定表';
CREATE TABLE `cms_shop_order` (
  `order_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单id',
  `order_code` varchar(50) NOT NULL DEFAULT '' COMMENT '订单编号',
  `users_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员id',
  `merchant_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '多商家ID',
  `order_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态：0未付款(已下单)，1已付款(待发货)，2已发货(待收货)，3已完成(确认收货)，-1订单取消(已关闭)，4订单过期',
  `payment_method` tinyint(1) DEFAULT '0' COMMENT '订单支付方式，0为在线支付，1为货到付款，默认0',
  `pay_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
  `pay_name` varchar(20) NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `wechat_pay_type` varchar(20) NOT NULL DEFAULT '' COMMENT '微信支付时，标记使用的支付类型（扫码支付，微信内部，微信H5页面）',
  `order_terminal` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '订单终端(1=电脑端、2=手机端、3微信小程序)',
  `contains_virtual` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '订单是否包含虚拟商品(1=不包含、2=包含)',
  `manual_refund` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '订单是否被手动关闭并退款',
  `refund_note` varchar(500) NOT NULL DEFAULT '' COMMENT '订单手动退款原因',
  `pay_details` text COMMENT '支付时返回的数据，以serialize序列化后存入，用于后续查询。',
  `express_order` varchar(50) DEFAULT '' COMMENT '发货物流单号',
  `express_name` varchar(32) DEFAULT '' COMMENT '发货物流名称',
  `express_code` varchar(32) DEFAULT '' COMMENT '发货物流code',
  `express_time` int(11) DEFAULT '0' COMMENT '发货时间',
  `consignee` varchar(30) NOT NULL DEFAULT '' COMMENT '收货人',
  `confirm_time` int(11) DEFAULT '0' COMMENT '收货确认时间',
  `allow_service` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '订单是否允许申请售后维权，0=允许申请维权，1=不允许申请维权',
  `obtain_scores` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '消费获得积分数',
  `is_obtain_scores` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '该订单是否已赠送积分，0=未赠送，1=已赠送',
  `shipping_fee` decimal(20,2) DEFAULT '0.00' COMMENT '订单运费',
  `order_total_amount` decimal(20,2) DEFAULT '0.00' COMMENT '订单总价',
  `order_amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '应付款金额',
  `order_total_num` int(10) DEFAULT '0' COMMENT '订单总数',
  `is_total_amount` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '订单是否已将实际支付金额(order表order_amount字段)累加到会员累计消费金额(users表order_total_amount字段) (0:未累计; 1:已累计;)',
  `country` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '国家',
  `province` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '省份',
  `city` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '城市',
  `district` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '县区',
  `address` varchar(500) NOT NULL DEFAULT '' COMMENT '收货地址',
  `mobile` varchar(20) DEFAULT '' COMMENT '手机',
  `prom_type` tinyint(1) unsigned DEFAULT '0' COMMENT '订单类型：0普通订单，1虚拟订单,2-核销订单(单次)',
  `waybill_type` tinyint(1) DEFAULT '0' COMMENT '发货方式选择   --- 是否采用电子面单，0=手动填写，1=电子面单',
  `virtual_delivery` text COMMENT '虚拟订单时，卖家发货给买家的回复',
  `admin_note` text COMMENT '管理员操作备注',
  `is_comment` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已评论，0为否，1为是，默认0',
  `user_note` text COMMENT '会员备注',
  `group` varchar(50) DEFAULT '' COMMENT '订单分组',
  `order_md5` varchar(50) DEFAULT '' COMMENT '订单标识串，删除未付款的重复订单',
  `order_source` tinyint(3) DEFAULT '10' COMMENT '10-普通订单 20-秒杀订单',
  `order_source_id` int(10) DEFAULT '0' COMMENT '来源id(秒杀订单:active_time_id)',
  `lang` varchar(30) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned DEFAULT '0' COMMENT '下单时间',
  `update_time` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
  `coupon_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '优惠券数据表ID',
  `use_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员的优惠券数据表ID',
  `coupon_price` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '使用的优惠券金额',
  `verify_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '核销记录表ID(weapp_verify表)',
  `logistics_type` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '订单物流类型(1:快递发货; 2:到店核销;)',
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_code` (`order_code`),
  KEY `users_id` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单主表';
CREATE TABLE `cms_shop_order_comment` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `users_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `order_code` varchar(50) NOT NULL DEFAULT '' COMMENT '订单编号',
  `details_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单明细表ID',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '商品ID',
  `total_score` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '总评分，1：好评，2中评，3差评',
  `content` text NOT NULL COMMENT '评论内容',
  `upload_img` varchar(3000) NOT NULL DEFAULT '' COMMENT '晒单图片',
  `admin_reply` varchar(1000) NOT NULL DEFAULT '' COMMENT '管理员回复',
  `ip_address` varchar(15) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `is_show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示：0否，1是',
  `is_anonymous` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否匿名评价：0否，1是',
  `is_new_comment` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否新版评价：0否，1是',
  `lang` varchar(30) NOT NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`comment_id`),
  KEY `users_id` (`users_id`),
  KEY `order_id` (`order_id`),
  KEY `details_id` (`details_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品评价表';
CREATE TABLE `cms_shop_order_details` (
  `details_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `order_id` int(10) NOT NULL DEFAULT '0' COMMENT '订单ID',
  `users_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员id',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品id',
  `product_name` varchar(100) NOT NULL DEFAULT '' COMMENT '产品名称',
  `num` int(10) NOT NULL DEFAULT '0' COMMENT '单个产品数量',
  `data` text COMMENT '序列化额外数据',
  `product_price` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '产品单价',
  `prom_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '产品类型：0普通产品，1虚拟产品',
  `litpic` varchar(500) NOT NULL DEFAULT '' COMMENT '封面图片',
  `apply_service` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否申请退换货服务：0 未申请、1已申请',
  `is_comment` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已评论，0为否，1为是，默认0',
  `lang` varchar(30) NOT NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '下单时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`details_id`),
  KEY `users_id` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单详情表';
CREATE TABLE `cms_shop_order_log` (
  `action_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `users_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `action_user` int(10) DEFAULT '0' COMMENT '操作人；0:用户操作；1以上:管理员id',
  `order_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '订单状态，单条记录状态',
  `express_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '物流状态，0:未发货，1:已发货',
  `pay_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '支付状态，0:未支付，1:已支付',
  `action_desc` varchar(255) DEFAULT '' COMMENT '状态描述',
  `action_note` varchar(255) NOT NULL DEFAULT '' COMMENT '操作备注',
  `lang` varchar(30) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
  PRIMARY KEY (`action_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单操作记录表';
CREATE TABLE `cms_shop_order_service` (
  `service_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `service_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '类型：1换货，2退货，3维修',
  `users_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员id',
  `merchant_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '多商家ID',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `order_code` varchar(50) NOT NULL DEFAULT '' COMMENT '订单编号',
  `details_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单明细表ID',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品ID',
  `product_name` varchar(200) NOT NULL DEFAULT '' COMMENT '产品名称',
  `product_spec` varchar(200) NOT NULL DEFAULT '' COMMENT '产品规格',
  `product_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '产品数量',
  `product_img` varchar(500) NOT NULL DEFAULT '' COMMENT '产品图片',
  `content` varchar(500) NOT NULL DEFAULT '' COMMENT '退换货描述',
  `upload_img` varchar(3000) NOT NULL DEFAULT '' COMMENT '上传的图片',
  `address` varchar(500) NOT NULL DEFAULT '' COMMENT '退货的收货地址',
  `consignee` varchar(30) NOT NULL DEFAULT '' COMMENT '收货人',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机',
  `manual_refund` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '服务单是否被手动关闭并退款',
  `manual_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '服务单手动退款时间',
  `refund_note` varchar(500) NOT NULL DEFAULT '' COMMENT '服务单手动退款原因',
  `refund_way` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '退款方式(1:退款到余额; 2:线下退款; 3:原路退回(微信))',
  `refund_price` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '退还金额',
  `refund_balance` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '退还余额',
  `refund_code` varchar(40) NOT NULL DEFAULT '' COMMENT '退款单号',
  `refund_reason` varchar(255) NOT NULL DEFAULT '' COMMENT '提交维权时的(退货退款 or 换货)原因',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态：1审核中 2审核通过 3审核不通过 4已发货 5已收货 6换货完成 7退款完成 8服务取消',
  `audit_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '审核时间',
  `refund_remark` varchar(500) NOT NULL DEFAULT '' COMMENT '退款备注',
  `users_delivery` varchar(500) NOT NULL DEFAULT '' COMMENT '会员发货信息',
  `admin_delivery` varchar(500) NOT NULL DEFAULT '' COMMENT '管理员发货信息',
  `admin_note` varchar(1000) NOT NULL DEFAULT '' COMMENT '管理员操作备注',
  `lang` varchar(30) NOT NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '申请时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`service_id`),
  KEY `users_id` (`users_id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `order_code` (`order_code`) USING BTREE,
  KEY `product_id` (`product_id`) USING BTREE,
  KEY `details_id` (`details_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单退换货服务表';
CREATE TABLE `cms_shop_order_service_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `service_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '服务表ID',
  `order_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '订单ID',
  `users_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `log_note` varchar(500) NOT NULL DEFAULT '' COMMENT '记录备注',
  `lang` varchar(30) NOT NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '操作时间',
  PRIMARY KEY (`log_id`),
  KEY `service_id` (`service_id`) USING BTREE,
  KEY `order_id` (`order_id`) USING BTREE,
  KEY `users_id` (`users_id`) USING BTREE,
  KEY `admin_id` (`admin_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单退换货服务记录表';
CREATE TABLE `cms_shop_order_unified_pay` (
  `unified_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '统一支付订单ID',
  `unified_number` varchar(30) NOT NULL DEFAULT '' COMMENT '统一支付订单编号',
  `unified_amount` decimal(20,2) NOT NULL DEFAULT '0.00' COMMENT '统一支付订单应付款金额',
  `users_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `order_ids` text NOT NULL COMMENT '合并支付的订单ID，serialize序列化存储',
  `pay_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '统一支付订单状态：0未付款，1已付款',
  `pay_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '统一支付订单时间',
  `pay_name` varchar(20) NOT NULL DEFAULT '' COMMENT '统一支付订单方式名称',
  `wechat_pay_type` varchar(20) NOT NULL DEFAULT '' COMMENT '微信支付时，标记使用的支付类型（扫码支付，微信内部，微信H5页面）',
  `add_time` int(11) unsigned DEFAULT '0' COMMENT '下单时间',
  `update_time` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`unified_id`),
  KEY `users_id` (`users_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单统一支付表';
CREATE TABLE `cms_shop_product_attr` (
  `product_attr_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '产品属性id自增',
  `aid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '产品id',
  `attr_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '属性id',
  `attr_value` text NOT NULL COMMENT '属性值',
  `attr_price` varchar(255) DEFAULT '' COMMENT '属性价格',
  `is_custom` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否自定义参数(0否 1是)',
  `sort_order` int(11) unsigned NOT NULL DEFAULT '100' COMMENT '属性排序',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`product_attr_id`),
  KEY `aid` (`aid`) USING BTREE,
  KEY `attr_id` (`attr_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8;
CREATE TABLE `cms_shop_product_attribute` (
  `attr_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '属性id',
  `attr_name` varchar(60) NOT NULL DEFAULT '' COMMENT '属性名称',
  `list_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '栏目id',
  `attr_index` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0不需要检索 1关键字检索 2范围检索',
  `attr_input_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT ' 0=文本框，1=下拉框，2=多行文本框',
  `attr_values` text NOT NULL COMMENT '可选值列表',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0=禁用，1=启用)',
  `sort_order` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '属性排序',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已删除，0=否，1=是',
  `is_custom` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否自定义参数(0否 1是)',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`attr_id`),
  KEY `cat_id` (`list_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
CREATE TABLE `cms_shop_product_attrlist` (
  `list_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '列表id',
  `list_name` varchar(60) NOT NULL DEFAULT '' COMMENT '列表名称',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0=禁用，1=启用)',
  `attr_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '参数数量',
  `desc` text NOT NULL COMMENT '描述备注',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已删除，0=否，1=是',
  `sort_order` int(11) unsigned NOT NULL DEFAULT '100' COMMENT '列表排序',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`list_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
CREATE TABLE `cms_shop_shipping_template` (
  `template_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '运费模板ID',
  `template_region` varchar(255) NOT NULL DEFAULT '' COMMENT '模板运送区域',
  `template_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '模板运费',
  `province_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'region表id',
  `lang` varchar(30) DEFAULT 'cn' COMMENT '语言标识',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COMMENT='运费模板表';
CREATE TABLE `cms_single_content` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文档ID',
  `typeid` int(10) DEFAULT '0' COMMENT '栏目ID',
  `content` longtext COMMENT '内容详情',
  `content_ey_m` longtext COMMENT '手机端内容详情',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='单页附加表';
CREATE TABLE `cms_sms_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '表id',
  `source` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '发送来源，与场景ID对应：0=注册，1=绑定，2=登录密码，3=支付密码，4=找回密码',
  `sms_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '短信服务商类型，1---阿里云短信， 2---腾讯云短信',
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '发送时间',
  `code` varchar(10) NOT NULL DEFAULT '' COMMENT '验证码',
  `status` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '发送状态,1:成功,0:失败',
  `is_use` int(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否使用，1:是，0:否',
  `msg` varchar(255) NOT NULL DEFAULT '' COMMENT '短信内容',
  `ip` varchar(20) DEFAULT 'IP地址',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `error_msg` text NOT NULL COMMENT '发送短信异常内容',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手机短信发送记录';
CREATE TABLE `cms_sms_template` (
  `tpl_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `sms_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '短信服务商类型，1---阿里云短信， 2---腾讯云短信',
  `tpl_title` varchar(128) NOT NULL DEFAULT '' COMMENT '短信标题',
  `sms_sign` varchar(50) NOT NULL DEFAULT '' COMMENT '短信签名',
  `sms_tpl_code` varchar(100) NOT NULL DEFAULT '' COMMENT '短信模板ID',
  `tpl_content` varchar(1000) NOT NULL DEFAULT '' COMMENT '发送短信内容',
  `send_scene` varchar(100) NOT NULL DEFAULT '' COMMENT '短信发送场景',
  `is_open` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开启使用这个模板，1为是，0为否。',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`tpl_id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8 COMMENT='手机短信发送模板';
INSERT INTO `cms_sms_template` VALUES ('1', '1', '账号注册', '', '', '验证码为 ${content} ，请在30分钟内输入验证。', '0', '1', 'cn', '1591262356', '1591262356');
INSERT INTO `cms_sms_template` VALUES ('2', '1', '手机绑定', '', '', '验证码为 ${content} ，请在30分钟内输入验证。', '1', '1', 'cn', '1591262356', '1591262356');
INSERT INTO `cms_sms_template` VALUES ('3', '1', '找回密码', '', '', '验证码为 ${content} ，请在30分钟内输入验证。', '4', '1', 'cn', '1591262356', '1591262356');
INSERT INTO `cms_sms_template` VALUES ('4', '1', '订单付款', '', '', '您有新的消息：您有新的${content}订单，请注意查收！', '5', '1', 'cn', '1591262356', '1616460912');
INSERT INTO `cms_sms_template` VALUES ('9', '2', '账号注册', '', '', '验证码为 {1} ，请在30分钟内输入验证。', '0', '1', 'cn', '1610334638', '1610334638');
INSERT INTO `cms_sms_template` VALUES ('10', '2', '手机绑定', '', '', '验证码为 {1} ，请在30分钟内输入验证。', '1', '1', 'cn', '1610334638', '1610334638');
INSERT INTO `cms_sms_template` VALUES ('11', '2', '找回密码', '', '', '验证码为 {1} ，请在30分钟内输入验证。', '4', '1', 'cn', '1610334638', '1610334638');
INSERT INTO `cms_sms_template` VALUES ('12', '2', '订单付款', '', '', '您有新的消息：您有新的{1}订单，请注意查收！', '5', '1', 'cn', '1610334638', '1616460912');
INSERT INTO `cms_sms_template` VALUES ('17', '1', '订单发货', '', '', '您有新的消息：您有新的${content}订单，请注意查收！', '6', '1', 'cn', '1591262356', '1616460912');
INSERT INTO `cms_sms_template` VALUES ('19', '2', '订单发货', '', '', '您有新的消息：您有新的{1}订单，请注意查收！', '6', '1', 'cn', '1610334638', '1616460912');
INSERT INTO `cms_sms_template` VALUES ('26', '1', '留言验证', '', '', '验证码为 ${content} ，请在30分钟内输入验证。', '7', '1', 'cn', '1591262356', '1591262356');
INSERT INTO `cms_sms_template` VALUES ('25', '1', '账号登录', '', '', '验证码为 ${content} ，请在30分钟内输入验证。', '2', '1', 'cn', '1591262356', '1591262356');
INSERT INTO `cms_sms_template` VALUES ('29', '2', '账号登录', '', '', '验证码为 {1} ，请在30分钟内输入验证。', '2', '1', 'cn', '1610334638', '1610334638');
INSERT INTO `cms_sms_template` VALUES ('30', '2', '留言验证', '', '', '验证码为 {1} ，请在30分钟内输入验证。', '7', '1', 'cn', '1610334638', '1610334638');
INSERT INTO `cms_sms_template` VALUES ('33', '1', '留言表单', '', '', '您有新的留言消息，请查收！', '11', '1', 'cn', '1591262356', '1591262356');
INSERT INTO `cms_sms_template` VALUES ('35', '2', '留言表单', '', '', '您有新的留言消息，请查收！', '11', '1', 'cn', '1610334638', '1610334638');
INSERT INTO `cms_sms_template` VALUES ('37', '1', '投稿提醒', '', '', '您有新的会员投稿，请查看！', '20', '0', 'cn', '1591262356', '1591262356');
INSERT INTO `cms_sms_template` VALUES ('39', '2', '投稿提醒', '', '', '您有新的会员投稿，请查看！', '20', '0', 'cn', '1610334638', '1610334638');
INSERT INTO `cms_sms_template` VALUES ('42', '1', '后台登录', '', '', '验证码为 ${content} ，请在30分钟内输入验证。', '30', '1', 'cn', '1591262356', '1591262356');
INSERT INTO `cms_sms_template` VALUES ('43', '2', '后台登录', '', '', '验证码为 {1} ，请在30分钟内输入验证。', '30', '1', 'cn', '1610334638', '1610334638');
CREATE TABLE `cms_smtp_record` (
  `record_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `source` tinyint(1) DEFAULT '0' COMMENT '来源，与场景ID对应：0=默认，2=注册，3=绑定邮箱，4=找回密码',
  `email` varchar(50) DEFAULT '' COMMENT '邮件地址',
  `users_id` int(10) DEFAULT '0' COMMENT '用户ID',
  `code` varchar(20) DEFAULT '' COMMENT '发送邮件内容',
  `status` tinyint(1) DEFAULT '0' COMMENT '是否使用，默认0，0为未使用，1为使用',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='邮件发送记录表';
CREATE TABLE `cms_smtp_tpl` (
  `tpl_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `tpl_name` varchar(200) DEFAULT '' COMMENT '模板名称',
  `tpl_title` varchar(200) DEFAULT '' COMMENT '邮件标题',
  `tpl_content` text COMMENT '发送邮件内容',
  `send_scene` int(5) DEFAULT '0' COMMENT '邮件发送场景(1=留言表单）',
  `is_open` tinyint(1) DEFAULT '0' COMMENT '是否开启使用这个模板，1为是，0为否。',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`tpl_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='邮件模板表';
INSERT INTO `cms_smtp_tpl` VALUES ('1', '留言表单', '您有新的留言消息，请查收！', '${content}', '1', '1', 'cn', '1544763495', '1552638302');
INSERT INTO `cms_smtp_tpl` VALUES ('2', '会员注册', '验证码已发送至您的邮箱，请登录邮箱查看验证码！', '${content}', '2', '1', 'cn', '1544763495', '1552667056');
INSERT INTO `cms_smtp_tpl` VALUES ('3', '绑定邮箱', '验证码已发送至您的邮箱，请登录邮箱查看验证码！', '${content}', '3', '1', 'cn', '1544763495', '1552667400');
INSERT INTO `cms_smtp_tpl` VALUES ('4', '找回密码', '验证码已发送至您的邮箱，请登录邮箱查看验证码！', '${content}', '4', '1', 'cn', '1544763495', '1552663577');
INSERT INTO `cms_smtp_tpl` VALUES ('9', '订单付款', '您有新的待发货订单消息，请到商城订单查看！', '${content}', '5', '1', 'cn', '1587364685', '1616460912');
INSERT INTO `cms_smtp_tpl` VALUES ('11', '订单发货', '您有新的待收货订单消息，请到会员订单查看！', '${content}', '6', '1', 'cn', '1616460912', '1616460912');
INSERT INTO `cms_smtp_tpl` VALUES ('12', '投稿提醒', '您有新的投稿文档，请及时查看！', '${content}', '20', '0', 'cn', '1544763495', '1552667056');
INSERT INTO `cms_smtp_tpl` VALUES ('13', '后台登录', '后台登录验证码，请查收！', '${content}', '30', '1', 'cn', '1544763495', '1552667056');
INSERT INTO `cms_smtp_tpl` VALUES ('14', '邮箱验证', '您的邮箱验证码，请查收！', '${content}', '40', '1', 'cn', '1756178725', '1756178725');
CREATE TABLE `cms_special_content` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) DEFAULT '0' COMMENT '文档ID',
  `content` longtext COMMENT '内容详情',
  `content_ey_m` longtext COMMENT '手机端内容详情',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `aid` (`aid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='专题附加表';
CREATE TABLE `cms_special_node` (
  `node_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `aid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文档ID',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '节点名称',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '节点标识',
  `isauto` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否自动获取文档',
  `keywords` varchar(200) NOT NULL DEFAULT '' COMMENT '关键字（多个中间用'',''分开）',
  `typeid` int(10) NOT NULL DEFAULT '0' COMMENT '栏目ID',
  `aidlist` text NOT NULL COMMENT '关联文章列表（多个中间用'',''分开）',
  `allsorts` text NOT NULL,
  `row` int(5) NOT NULL DEFAULT '10' COMMENT '文档数',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `sort_order` int(11) DEFAULT '0' COMMENT '排序',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='专题节点表';
CREATE TABLE `cms_sql_cache_table` (
  `cache_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `sql_name` varchar(60) NOT NULL DEFAULT '' COMMENT 'mysql语句的标识名称，目前由模型名称+模型ID组成',
  `sql_result` text NOT NULL COMMENT 'mysql执行结果',
  `sql_md5` varchar(60) NOT NULL DEFAULT '' COMMENT 'mysql语句MD5的值',
  `sql_query` text NOT NULL COMMENT '完整mysql语句',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`cache_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='mysql缓存表';
CREATE TABLE `cms_statistics_data` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `date` int(10) DEFAULT '0',
  `num` int(10) DEFAULT '0',
  `type` mediumint(8) DEFAULT '1' COMMENT '1-浏览量 2-订单 3-销售额 4-新增会员 5-充值金额 6-商品数 7-文章数 8-tag数 9-待审数',
  `total` decimal(8,2) DEFAULT '0.00' COMMENT 'type=3/5用',
  `lang` varchar(10) DEFAULT 'cn',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='商城欢迎页主题的统计表';
CREATE TABLE `cms_tagindex` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'tagid',
  `tag` varchar(50) NOT NULL DEFAULT '' COMMENT 'tag内容',
  `typeid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '栏目ID',
  `litpic` varchar(250) DEFAULT '' COMMENT '封面图',
  `seo_title` varchar(200) DEFAULT '' COMMENT 'SEO标题',
  `seo_keywords` varchar(200) DEFAULT '' COMMENT 'SEO关键词',
  `seo_description` text COMMENT 'SEO描述',
  `count` int(10) unsigned DEFAULT '0' COMMENT '点击',
  `total` int(10) unsigned DEFAULT '0' COMMENT '文档数',
  `weekcc` int(10) unsigned DEFAULT '0' COMMENT '周统计',
  `monthcc` int(10) unsigned DEFAULT '0' COMMENT '月统计',
  `weekup` int(10) unsigned DEFAULT '0' COMMENT '每周更新',
  `monthup` int(10) unsigned DEFAULT '0' COMMENT '每月更新',
  `is_common` tinyint(1) DEFAULT '0' COMMENT '是否常用标签，0=否，1=是',
  `sort_order` int(10) DEFAULT '100' COMMENT '排序号',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(10) unsigned DEFAULT '0' COMMENT '添加时间',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `typeid` (`typeid`) USING BTREE,
  KEY `count` (`count`,`total`,`weekcc`,`monthcc`,`weekup`,`monthup`,`add_time`) USING BTREE,
  KEY `tag` (`tag`) USING BTREE,
  KEY `lang` (`lang`,`add_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COMMENT='标签索引表';
CREATE TABLE `cms_taglist` (
  `tid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'tagid',
  `aid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文档ID',
  `typeid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '栏目ID',
  `tag` varchar(50) DEFAULT '' COMMENT 'tag内容',
  `arcrank` tinyint(1) DEFAULT '0' COMMENT '阅读权限',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`tid`,`aid`),
  KEY `aid` (`aid`,`typeid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章标签表';
CREATE TABLE `cms_ui_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `md5key` varchar(100) NOT NULL DEFAULT '' COMMENT '唯一键值（由 theme_style、page、name）组成',
  `theme_style` varchar(200) DEFAULT 'pc' COMMENT '模板风格',
  `page` varchar(64) DEFAULT '' COMMENT '页面分组',
  `type` varchar(50) DEFAULT '' COMMENT '编辑类型',
  `name` varchar(50) DEFAULT '' COMMENT '与页面的e-id对应',
  `value` text COMMENT '页面美化的val值',
  `idcode` varchar(50) DEFAULT '' COMMENT '页面唯一id标识（由 标识符+栏目id或文档id等）组成',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `md5key` (`md5key`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='可视化参数设置';
CREATE TABLE `cms_uploads` (
  `img_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `group_code` varchar(10) DEFAULT 'image' COMMENT '文件类型分组',
  `type_id` int(10) NOT NULL DEFAULT '0' COMMENT '分组ID',
  `aid` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '文档ID',
  `title` varchar(500) DEFAULT '' COMMENT '文档标题',
  `image_url` varchar(255) DEFAULT '' COMMENT '文件存储路径',
  `intro` varchar(500) DEFAULT '' COMMENT '图集描述',
  `width` int(11) DEFAULT '0' COMMENT '图片宽度',
  `height` int(11) DEFAULT '0' COMMENT '图片高度',
  `filesize` int(11) unsigned DEFAULT '0' COMMENT '文件大小',
  `mime` varchar(50) DEFAULT '' COMMENT '图片类型',
  `users_id` int(11) DEFAULT '0' COMMENT '用户ID',
  `sort_order` smallint(5) DEFAULT '100' COMMENT '排序',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '1已删除 0未删除',
  `add_time` int(10) unsigned DEFAULT '0' COMMENT '上传时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`img_id`),
  KEY `aid` (`aid`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='上传记录表';
CREATE TABLE `cms_uploads_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_code` varchar(10) DEFAULT 'image' COMMENT '文件类型分组',
  `upload_type` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `add_time` int(10) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='上传分组表';
CREATE TABLE `cms_users` (
  `users_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '表id',
  `username` varchar(30) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '登录密码',
  `nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
  `is_mobile` tinyint(1) DEFAULT '0' COMMENT '绑定手机号，0为不绑定，1为绑定',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号码（仅用于登录）',
  `is_email` tinyint(1) DEFAULT '0' COMMENT '绑定邮箱，0为不绑定，1为绑定',
  `email` varchar(60) NOT NULL DEFAULT '' COMMENT '电子邮件（仅用于登录）',
  `paypwd` varchar(255) DEFAULT '' COMMENT '支付密码，暂时未用到，可保留。',
  `users_money` decimal(20,2) DEFAULT '0.00' COMMENT '用户金额',
  `frozen_money` decimal(20,2) DEFAULT '0.00' COMMENT '冻结金额',
  `scores` int(10) DEFAULT '0' COMMENT '积分',
  `devote` int(10) DEFAULT '0' COMMENT '贡献值',
  `reg_time` int(11) unsigned DEFAULT '0' COMMENT '注册时间',
  `last_login` int(11) unsigned DEFAULT '0' COMMENT '最后登录时间',
  `last_ip` varchar(15) DEFAULT '' COMMENT '最后登录ip',
  `login_count` int(11) DEFAULT '0' COMMENT '登陆次数',
  `head_pic` varchar(255) DEFAULT '' COMMENT '头像',
  `province` int(6) DEFAULT '0' COMMENT '省份',
  `city` int(6) DEFAULT '0' COMMENT '市区',
  `district` int(6) DEFAULT '0' COMMENT '县',
  `level` smallint(5) DEFAULT '0' COMMENT '会员等级',
  `open_level_time` int(11) unsigned DEFAULT '0' COMMENT '开通会员级别时间',
  `level_maturity_days` varchar(20) DEFAULT '' COMMENT '会员级别到期天数',
  `discount` decimal(20,2) DEFAULT '1.00' COMMENT '会员折扣，默认1不享受',
  `total_amount` decimal(20,2) DEFAULT '0.00' COMMENT '消费累计额度',
  `order_total_amount` decimal(20,2) unsigned DEFAULT '0.00' COMMENT '订单累计总额，用于会员自动升级。',
  `is_activation` tinyint(1) DEFAULT '1' COMMENT '是否激活，0否，1是。\r\n后台注册默认为1激活。\r\n前台注册时，当会员功能设置选择后台审核，需后台激活才可以登陆。',
  `register_place` tinyint(1) DEFAULT '2' COMMENT '注册位置。后台注册不受注册验证影响，1为后台注册，2为前台注册。默认为2。',
  `open_id` varchar(50) NOT NULL DEFAULT '' COMMENT '第三方唯一标识openid',
  `thirdparty` tinyint(1) DEFAULT '0' COMMENT '第三方注册类型 0=普通, 1=微信, 2=QQ, 3=手机号, 4=微信小程序， 5=微站点, 6-微信公众号, 7-百度小程序, 8-抖音小程序',
  `is_lock` tinyint(1) DEFAULT '0' COMMENT '是否被锁定冻结',
  `admin_id` int(10) DEFAULT '0' COMMENT '关联管理员ID',
  `lang` varchar(20) DEFAULT 'cn' COMMENT '语言标识',
  `is_del` tinyint(1) DEFAULT '0' COMMENT '伪删除，1=是，0=否',
  `unread_notice_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '未读消息数量',
  `update_time` int(11) unsigned DEFAULT '0' COMMENT '更新时间',
  `users_notes` varchar(500) NOT NULL DEFAULT '' COMMENT '会员备注',
  `sex` varchar(10) DEFAULT '保密' COMMENT '性别- 男,女,保密',
  `coin` int(11) unsigned DEFAULT '0' COMMENT '金币',
  `union_id` varchar(50) NOT NULL DEFAULT '' COMMENT '微信用户的unionId',
  `source` tinyint(3) DEFAULT '1' COMMENT '来源\r\n1-PC端\r\n2-H5\r\n3-微信公众号\r\n4-微信小程序\r\n5-百度小程序\r\n6-抖音小程序\r\n',
  PRIMARY KEY (`users_id`),
  KEY `union_id` (`union_id`) USING BTREE,
  KEY `username` (`username`) USING BTREE,
  KEY `mobile` (`mobile`) USING BTREE,
  KEY `open_id` (`open_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='会员信息表';
CREATE TABLE `cms_users_bottom_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `title` varchar(30) DEFAULT '' COMMENT '导航名称',
  `mca` varchar(50) DEFAULT '' COMMENT '分组/控制器/操作名',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT '图标',
  `sort_order` int(10) DEFAULT '100' COMMENT '排序号',
  `status` tinyint(1) DEFAULT '1' COMMENT '功能开关状态，1=开启，0=关闭',
  `display` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示, 1--是, 0--否',
  `lang` varchar(20) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='会员中心移动端底部菜单表';
CREATE TABLE `cms_users_collection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(10) DEFAULT '0',
  `aid` int(10) DEFAULT '0' COMMENT '文档id',
  `channel` int(10) DEFAULT '0' COMMENT '模型',
  `typeid` int(10) DEFAULT '0' COMMENT '栏目',
  `title` varchar(200) DEFAULT '' COMMENT '网站标题',
  `litpic` varchar(255) DEFAULT '' COMMENT '缩略图',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='我的收藏';
CREATE TABLE `cms_users_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '会员功能配置表ID',
  `name` varchar(50) DEFAULT '' COMMENT '配置的key键名',
  `value` text COMMENT '配置的value值',
  `desc` varchar(100) DEFAULT '' COMMENT '键名说明',
  `inc_type` varchar(64) DEFAULT '' COMMENT '配置分组',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COMMENT='会员功能配置表';
CREATE TABLE `cms_users_footprint` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel` int(10) DEFAULT '0' COMMENT '频道模型',
  `typeid` int(10) DEFAULT '0' COMMENT '栏目id',
  `aid` int(10) DEFAULT '0' COMMENT '文档id',
  `title` varchar(100) DEFAULT '' COMMENT '网站标题',
  `litpic` varchar(255) DEFAULT '' COMMENT '缩略图',
  `users_id` int(10) DEFAULT '0',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `users_id` (`users_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COMMENT='我的足迹';
CREATE TABLE `cms_users_forward` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(10) DEFAULT '0',
  `aid` int(10) DEFAULT '0' COMMENT '文档id',
  `channel` int(10) DEFAULT '0' COMMENT '模型',
  `typeid` int(10) DEFAULT '0' COMMENT '栏目',
  `title` varchar(200) DEFAULT '' COMMENT '网站标题',
  `litpic` varchar(255) DEFAULT '' COMMENT '缩略图',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='转发记录';
CREATE TABLE `cms_users_level` (
  `level_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `level_name` varchar(30) DEFAULT '' COMMENT '级别名称',
  `level_value` int(10) DEFAULT '0' COMMENT '会员等级值',
  `is_system` tinyint(1) DEFAULT '0' COMMENT '类型，1=系统，0=用户',
  `amount` decimal(10,2) DEFAULT '0.00' COMMENT '消费额度',
  `down_count` int(10) DEFAULT '0' COMMENT '每天下载次数限制',
  `discount_type` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '升级条件类型(0:不设置折扣; 1:自定义折扣)',
  `discount` float(10,2) DEFAULT '100.00' COMMENT '折扣率，初始值为100即100%，无折扣',
  `posts_count` int(10) DEFAULT '5' COMMENT '会员投稿次数限制',
  `ask_is_release` tinyint(1) DEFAULT '1' COMMENT '允许在问答中发布问题，1=是，0=否',
  `ask_is_review` tinyint(1) DEFAULT '0' COMMENT '在问答中发布问题或回答是否需要审核，1=是，0=否',
  `upgrade_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '升级条件类型(0:不自动升级; 1:订单金额)',
  `upgrade_order_money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '累计完成订单金额满多少自动升级成当前会员等级',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '会员等级状态(0:禁用; 1:启用)',
  `lang` varchar(20) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`level_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='会员级别表';
CREATE TABLE `cms_users_like` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(10) DEFAULT '0',
  `aid` int(10) DEFAULT '0' COMMENT '文档id',
  `channel` int(10) DEFAULT '0' COMMENT '模型',
  `typeid` int(10) DEFAULT '0' COMMENT '栏目',
  `title` varchar(200) DEFAULT '' COMMENT '网站标题',
  `litpic` varchar(255) DEFAULT '' COMMENT '缩略图',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='我喜欢的';
CREATE TABLE `cms_users_list` (
  `list_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `users_id` int(10) NOT NULL DEFAULT '0' COMMENT '会员ID',
  `para_id` int(10) NOT NULL DEFAULT '0' COMMENT '属性ID',
  `info` text COMMENT '属性值',
  `lang` varchar(50) NOT NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`list_id`),
  KEY `users_id` (`users_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='会员属性表(信息）';
CREATE TABLE `cms_users_log_off` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(11) DEFAULT '0',
  `username` varchar(25) DEFAULT NULL,
  `nickname` varchar(30) DEFAULT NULL,
  `mobile` varchar(25) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0' COMMENT '0-审核中 1-通过 2-拒绝',
  `handle_time` int(11) DEFAULT '0' COMMENT '操作时间',
  `admin_id` int(11) DEFAULT '0' COMMENT '操作人',
  `refuse_reason` varchar(255) DEFAULT NULL COMMENT '拒绝原因',
  `add_time` int(11) DEFAULT '0' COMMENT '申请时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员注销日志表';
CREATE TABLE `cms_users_login_log` (
  `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '会员日志自增ID',
  `users_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `log_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '日志时间，年月日(例:20230406)',
  `log_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '日志次数',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`log_id`),
  UNIQUE KEY `users_id` (`users_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员登录日志 - 用于登录赠送积分';
CREATE TABLE `cms_users_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `title` varchar(30) DEFAULT '' COMMENT '导航名称',
  `version` varchar(10) DEFAULT 'weapp' COMMENT '分组',
  `mca` varchar(200) DEFAULT '' COMMENT '分组/控制器/操作名',
  `active_url` varchar(500) DEFAULT '' COMMENT '标记为选中的url',
  `is_userpage` tinyint(1) DEFAULT '0' COMMENT '默认会员首页',
  `sort_order` int(10) DEFAULT '0' COMMENT '排序号',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，1=显示，0=隐藏',
  `lang` varchar(20) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `type` tinyint(3) DEFAULT '0' COMMENT '左侧菜单类型',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='会员菜单表';
CREATE TABLE `cms_users_money` (
  `moneyid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '金额明细表ID',
  `users_id` int(10) DEFAULT '0' COMMENT '会员表ID',
  `money` decimal(10,2) DEFAULT '0.00' COMMENT '金额',
  `users_money` decimal(20,2) DEFAULT '0.00' COMMENT '此条记录的账户金额',
  `cause` text COMMENT '事由，暂时在升级消费中使用到，以serialize序列化后存入，用于后续查询。',
  `cause_type` tinyint(1) DEFAULT '0' COMMENT '数据类型,0-消费,1-充值,2-退款,3-订单支付,4-管理员添加,5-管理员减少',
  `status` tinyint(1) DEFAULT '1' COMMENT '是否成功，默认1，0失败，1未付款，2已付款，3已完成，4订单取消。',
  `pay_method` varchar(50) DEFAULT '' COMMENT '支付方式，wechat为微信支付，alipay为支付宝支付，balance为余额支付',
  `wechat_pay_type` varchar(20) NOT NULL DEFAULT '' COMMENT '微信支付时，标记使用的支付类型（扫码支付，微信内部，微信H5页面）',
  `pay_details` text COMMENT '支付时返回的数据，以serialize序列化后存入，用于后续查询。',
  `order_number` varchar(30) DEFAULT '' COMMENT '订单号',
  `level_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员升级时所升级的会员级别ID，默认0',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `admin_id` int(10) DEFAULT '0' COMMENT '管理员表ID',
  PRIMARY KEY (`moneyid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='金额明细表';
CREATE TABLE `cms_users_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT '' COMMENT '通知标题',
  `users_id` text NOT NULL COMMENT '用户id',
  `usernames` text NOT NULL COMMENT '用户名字符串',
  `remark` text COMMENT '通知信息',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='站内通知';
CREATE TABLE `cms_users_notice_read` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `notice_id` int(10) DEFAULT NULL COMMENT '站内信id',
  `is_read` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已读, 1---是, 0---否',
  `is_del` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除, 1---是, 0---否',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户已读站内通知';
CREATE TABLE `cms_users_notice_tpl` (
  `tpl_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `tpl_name` varchar(200) DEFAULT '' COMMENT '模板名称',
  `tpl_title` varchar(200) DEFAULT '' COMMENT '站内信标题',
  `tpl_content` text COMMENT '发送内容',
  `send_scene` tinyint(1) DEFAULT '0' COMMENT '站内信发送场景(1=留言表单）',
  `is_open` tinyint(1) DEFAULT '0' COMMENT '是否开启使用这个模板，1为是，0为否。',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`tpl_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='站内信模板表';
CREATE TABLE `cms_users_notice_tpl_content` (
  `content_id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `source` tinyint(1) DEFAULT '0' COMMENT '来源，对应 users_notice_tpl 表 send_scene 字段',
  `admin_id` int(10) DEFAULT '0' COMMENT '管理员ID，不为空则表示管理员接收信息',
  `users_id` int(10) DEFAULT '0' COMMENT '用户ID，不为空则表示会员接收信息，暂未使用',
  `content_title` varchar(200) DEFAULT '' COMMENT '通知标题',
  `content` text COMMENT '接收的通知内容',
  `is_read` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已读，默认0，1是，0否',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `aid` int(11) DEFAULT '0' COMMENT '留言id',
  PRIMARY KEY (`content_id`),
  KEY `admin_id` (`admin_id`) USING BTREE,
  KEY `users_id` (`users_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='站内信发送接收记录表';
CREATE TABLE `cms_users_parameter` (
  `para_id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `dtype` varchar(32) NOT NULL DEFAULT '' COMMENT '字段类型',
  `dfvalue` varchar(1000) NOT NULL DEFAULT '' COMMENT '默认值',
  `is_system` tinyint(1) DEFAULT '0' COMMENT '是否为系统属性，系统属性不可删除，1为是，0为否，默认0。',
  `is_hidden` tinyint(1) DEFAULT '0' COMMENT '是否禁用属性，1为是，0为否',
  `is_required` tinyint(1) DEFAULT '0' COMMENT '是否为必填属性，1为是，0为否，默认0。',
  `is_reg` tinyint(1) DEFAULT '1' COMMENT '是否为注册表单，1为是，0为否',
  `placeholder` varchar(255) DEFAULT '' COMMENT '提示文字',
  `sort_order` smallint(5) NOT NULL DEFAULT '0' COMMENT '排序',
  `lang` varchar(50) NOT NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`para_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='会员属性表(字段)';
CREATE TABLE `cms_users_recharge_pack` (
  `pack_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '会员充值套餐ID',
  `pack_names` varchar(100) NOT NULL DEFAULT '' COMMENT '会员充值套餐名称',
  `pack_face_value` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '会员充值套餐充值面值',
  `pack_pay_prices` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '会员充值套餐购买价格',
  `pack_sales_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员充值套餐销售数量',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '会员充值套餐状态(1:开启; 2:关闭;)',
  `sort_order` int(11) unsigned NOT NULL DEFAULT '100' COMMENT '排序号',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`pack_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员充值套餐列表';
CREATE TABLE `cms_users_recharge_pack_order` (
  `order_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '会员充值套餐订单ID',
  `pack_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员充值套餐ID',
  `users_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `order_code` varchar(50) NOT NULL DEFAULT '' COMMENT '订单编号',
  `order_status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '订单状态(1:未支付; 2:已支付(未执行充值); 3:已充值(已执行充值); 4:已过期;)',
  `order_pack_names` varchar(100) NOT NULL DEFAULT '' COMMENT '会员充值套餐名称',
  `order_face_value` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单充值面值',
  `order_pay_prices` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单支付价格',
  `order_pay_code` varchar(50) NOT NULL DEFAULT '' COMMENT '订单支付编号(待用)',
  `order_pay_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '支付时间',
  `order_pay_name` varchar(20) NOT NULL DEFAULT '' COMMENT '支付方式名称',
  `order_pay_terminal` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '订单终端(1:电脑端; 2:手机端; 3:微信小程序; 4:抖音小程序)',
  `order_pay_details` text COMMENT '支付成功返回的数据，serialize序列化存入',
  `lang` varchar(30) NOT NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`order_id`),
  KEY `pack_id` (`pack_id`),
  KEY `users_id` (`users_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员充值套餐订单';
CREATE TABLE `cms_users_score` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '积分明细表',
  `type` tinyint(2) DEFAULT '1' COMMENT '类型:1-提问,2-回答,3-最佳答案4-悬赏退回,5-每日签到,6-管理员编辑,7-问题悬赏/获得悬赏,8-消费赠送积分,9-积分兑换/退回,10-登录赠送积分，11-积分商城订单支付，12-积分商城订单退回，13-文章订单支付',
  `users_id` int(10) DEFAULT '0' COMMENT '用户id',
  `ask_id` int(10) DEFAULT '0' COMMENT '问题id',
  `reply_id` int(10) DEFAULT '0' COMMENT '回答id',
  `score` varchar(20) NOT NULL DEFAULT '' COMMENT '积分',
  `devote` int(10) DEFAULT '0' COMMENT '贡献值,同score',
  `money` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `info` varchar(255) DEFAULT '' COMMENT '说明',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(10) DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  `current_score` int(10) DEFAULT '0' COMMENT '当前积分',
  `current_devote` int(10) DEFAULT '0' COMMENT '当前贡献值',
  `admin_id` int(10) DEFAULT '0' COMMENT '管理员表ID',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='积分详情表';
CREATE TABLE `cms_users_signin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `users_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `lang` varchar(50) NOT NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '签到时间',
  PRIMARY KEY (`id`),
  KEY `users_id` (`users_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='用户签到表';
CREATE TABLE `cms_users_type_manage` (
  `type_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `type_name` varchar(30) DEFAULT '' COMMENT '类型名称',
  `level_id` int(10) DEFAULT '0' COMMENT '会员等级ID',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `limit_id` int(10) DEFAULT '0' COMMENT '会员期限限制，存储ID，值对应常量表的admin_member_limit_arr数组',
  `activity` varchar(30) DEFAULT '' COMMENT '活动文案',
  `sort_order` smallint(5) NOT NULL DEFAULT '0' COMMENT '排序',
  `lang` varchar(20) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='会员产品类型表';
CREATE TABLE `cms_weapp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) DEFAULT '' COMMENT '插件标识',
  `name` varchar(55) DEFAULT '' COMMENT '中文名字',
  `config` text COMMENT '配置信息',
  `data` longtext COMMENT '额外序列化存储数据，简单插件可以不创建表，存储这里即可',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态：0=未安装，1=启用，-1=禁用',
  `tag_weapp` tinyint(1) DEFAULT '1' COMMENT '1=自动绑定，2=手工调用。关联模板标签weapp，自动调用内置的show钩子方法',
  `thorough` tinyint(1) DEFAULT '0' COMMENT '彻底卸载：0=是，1=否',
  `position` varchar(30) DEFAULT 'default' COMMENT '插件位置',
  `is_buy` tinyint(1) DEFAULT '0' COMMENT '0-本地,1-线上购买 2-线上购买,但已删除,不显示在我的插件列表',
  `is_upgrade` tinyint(1) DEFAULT '1' COMMENT '是否提示升级',
  `sort_order` int(10) DEFAULT '100' COMMENT '排序号',
  `add_time` int(11) DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) DEFAULT '0' COMMENT '更新时间',
  `quick_sort` int(10) DEFAULT '100' COMMENT '首页快捷菜单专用排序号',
  `checked` tinyint(4) DEFAULT '0' COMMENT '选中，0=否，1=是(首页快捷菜单用)',
  PRIMARY KEY (`id`),
  KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='插件应用表';
CREATE TABLE `cms_wechat_template` (
  `tpl_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `tpl_title` varchar(128) NOT NULL DEFAULT '' COMMENT '模板标题',
  `template_title` varchar(100) DEFAULT '' COMMENT '官方模板标题',
  `template_code` varchar(30) DEFAULT '' COMMENT '模板编号',
  `template_id` varchar(100) NOT NULL DEFAULT '' COMMENT '模板ID',
  `tpl_data` text NOT NULL COMMENT '模板内容序列化',
  `send_scene` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '发送场景',
  `is_open` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否开启使用这个模板，1为是，0为否。',
  `info` varchar(200) DEFAULT '' COMMENT '发送说明',
  `lang` varchar(50) DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`tpl_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='微信公众号消息发送模板';
CREATE TABLE `cms_wx_shipping_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `users_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '会员ID',
  `order_code` varchar(255) NOT NULL DEFAULT '' COMMENT '订单编号',
  `order_source` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单来源(1:会员普通充值订单; 2:会员商城商品订单; 3:会员升级订单; 8:会员视频订单; 20:会员套餐充值订单;)',
  `pay_success` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否支付成功(1:是; 0:否)',
  `pay_config` varchar(500) NOT NULL DEFAULT '' COMMENT '订单支付时所使用的支付配置信息(serialize存入，需unserialize解析)',
  `errcode` varchar(255) NOT NULL DEFAULT '' COMMENT '错误代码',
  `errmsg` varchar(255) NOT NULL DEFAULT '' COMMENT '错误提示',
  `add_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信发货推送表';
CREATE TABLE `cms_wx_users` (
  `wxuser_id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `openid` varchar(100) NOT NULL DEFAULT '' COMMENT 'openid',
  `unionid` varchar(100) DEFAULT '' COMMENT 'unionid',
  `nickname` varchar(100) NOT NULL DEFAULT '' COMMENT '微信昵称',
  `headimgurl` varchar(200) NOT NULL DEFAULT '' COMMENT '头像',
  `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '新增时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `provider` varchar(25) DEFAULT '' COMMENT '来源',
  PRIMARY KEY (`wxuser_id`),
  KEY `openid` (`openid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信小程序用户表';
