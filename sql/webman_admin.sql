/*
 Navicat Premium Data Transfer

 Source Server         : docker-mysql57
 Source Server Type    : MySQL
 Source Server Version : 50744 (5.7.44)
 Source Host           : localhost:13307
 Source Schema         : webman_admin

 Target Server Type    : MySQL
 Target Server Version : 50744 (5.7.44)
 File Encoding         : 65001

 Date: 06/01/2026 16:53:31
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for wa_admin_roles
-- ----------------------------
DROP TABLE IF EXISTS `wa_admin_roles`;
CREATE TABLE `wa_admin_roles`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `role_id` int(11) NOT NULL COMMENT '角色id',
  `admin_id` int(11) NOT NULL COMMENT '管理员id',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `role_admin_id`(`role_id`, `admin_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '管理员角色表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_admin_roles
-- ----------------------------
INSERT INTO `wa_admin_roles` VALUES (1, 1, 1);

-- ----------------------------
-- Table structure for wa_admins
-- ----------------------------
DROP TABLE IF EXISTS `wa_admins`;
CREATE TABLE `wa_admins`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `username` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '用户名',
  `nickname` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '昵称',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '密码',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '/app/admin/avatar.png' COMMENT '头像',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '邮箱',
  `mobile` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '手机',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `login_at` datetime NULL DEFAULT NULL COMMENT '登录时间',
  `status` tinyint(4) NULL DEFAULT NULL COMMENT '禁用',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '管理员表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_admins
-- ----------------------------
INSERT INTO `wa_admins` VALUES (1, 'admin', '超级管理员', '$2y$10$q.aigT8mRjRRK8xVewpGtOUwgo9xtcG4u/Ar4gq28qEHwJKYy.uKq', '/app/admin/avatar.png', NULL, NULL, '2025-12-16 21:57:06', '2026-01-05 11:31:05', '2026-01-05 11:31:05', NULL);

-- ----------------------------
-- Table structure for wa_archives
-- ----------------------------
DROP TABLE IF EXISTS `wa_archives`;
CREATE TABLE `wa_archives`  (
  `aid` int(10) NOT NULL AUTO_INCREMENT,
  `typeid` int(10) NOT NULL DEFAULT 0 COMMENT '当前栏目',
  `stypeid` varchar(90) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '副栏目ID集合',
  `channel` int(10) NOT NULL DEFAULT 0 COMMENT '模型ID',
  `is_b` tinyint(1) NULL DEFAULT 0 COMMENT '加粗',
  `title` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '文档标题',
  `subtitle` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '副标题',
  `introduction` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '促销语',
  `litpic` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '封面图片',
  `is_head` tinyint(1) NULL DEFAULT 0 COMMENT '头条（0=否，1=是）',
  `is_special` tinyint(1) NULL DEFAULT 0 COMMENT '特荐（0=否，1=是）',
  `is_top` tinyint(1) NULL DEFAULT 0 COMMENT '置顶（0=否，1=是）',
  `is_recom` tinyint(1) NULL DEFAULT 0 COMMENT '推荐（0=否，1=是）',
  `is_jump` tinyint(1) NULL DEFAULT 0 COMMENT '跳转链接（0=否，1=是）',
  `is_litpic` tinyint(1) NULL DEFAULT 0 COMMENT '图片（0=否，1=是）',
  `is_roll` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '滚动（0=否，1=是）',
  `is_slide` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '幻灯（0=否，1=是）',
  `is_diyattr` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '自定义（0=否，1=是）',
  `origin` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '来源',
  `author` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '作者',
  `click` int(10) NULL DEFAULT 0 COMMENT '点击数',
  `arcrank` int(10) NULL DEFAULT 0 COMMENT '阅读权限：0=开放浏览，-1=待审核稿件',
  `jumplinks` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '跳转网址',
  `ismake` tinyint(1) NULL DEFAULT 0 COMMENT '是否静态页面（0=动态，1=静态）',
  `seo_title` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'SEO标题',
  `seo_keywords` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'SEO关键词',
  `seo_description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'SEO描述',
  `attrlist_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '参数列表ID',
  `merchant_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '多商家ID',
  `free_shipping` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品是否包邮(1包邮(免运费)  0跟随系统)',
  `users_price` decimal(20, 2) NOT NULL DEFAULT 0.00 COMMENT '会员价',
  `crossed_price` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品划线价',
  `users_discount_type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '产品会员折扣类型(0:系统默认折扣; 1:指定会员级别; 2:不参与折扣;)',
  `users_free` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否会员免费，默认0不免费，1为免费',
  `old_price` decimal(20, 2) NOT NULL DEFAULT 0.00 COMMENT '产品旧价',
  `sales_num` int(10) NOT NULL DEFAULT 0 COMMENT '总销售量',
  `virtual_sales` int(10) NULL DEFAULT 0 COMMENT '商品虚拟销售量',
  `sales_all` int(10) NULL DEFAULT 0 COMMENT '虚拟总销量',
  `stock_count` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品库存量',
  `stock_show` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '商品库存在产品详情页是否显示，1为显示，0为不显示',
  `prom_type` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '产品类型：0=普通产品，1=虚拟(默认手动发货)，2=虚拟(网盘)，3=虚拟(自定义文本) 4-核销',
  `logistics_type` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '1' COMMENT '商品物流支持类型(1: 物流配送; 2: 到店核销)',
  `tempview` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '文档模板',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态(0=屏蔽，1=正常)',
  `sort_order` int(10) NULL DEFAULT 0 COMMENT '排序号',
  `lang` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'cn' COMMENT '语言标识',
  `admin_id` int(10) NULL DEFAULT 0 COMMENT '管理员ID',
  `users_id` int(10) NULL DEFAULT 0 COMMENT '会员ID',
  `arc_level_id` int(10) NULL DEFAULT 0 COMMENT '文档会员权限ID',
  `restric_type` tinyint(1) NULL DEFAULT 0 COMMENT '限制模式，0=免费，1=付费，2=会员专享，3=会员付费，4=会员积分购买',
  `users_score` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'restric_type=4时，会员可使用积分进行文章订单支付购买',
  `is_del` tinyint(1) NULL DEFAULT 0 COMMENT '伪删除，1=是，0=否',
  `del_method` tinyint(1) NULL DEFAULT 0 COMMENT '伪删除状态，1为主动删除，2为跟随上级栏目被动删除',
  `joinaid` int(10) NULL DEFAULT 0 COMMENT '关联文档ID',
  `downcount` int(10) NULL DEFAULT 0 COMMENT '下载次数',
  `appraise` int(10) NULL DEFAULT 0 COMMENT '评价数',
  `collection` int(10) NULL DEFAULT 0 COMMENT '收藏数',
  `htmlfilename` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '自定义文件名',
  `province_id` int(10) NULL DEFAULT 0 COMMENT '省份',
  `city_id` int(10) NULL DEFAULT 0 COMMENT '所在城市',
  `area_id` int(10) NULL DEFAULT 0 COMMENT '所在区域',
  `add_time` int(11) NULL DEFAULT 0 COMMENT '新增时间',
  `update_time` int(11) NULL DEFAULT 0 COMMENT '更新时间',
  `removal_time` int(11) NULL DEFAULT 0 COMMENT '下架时间（用于自动下架，配合定时发布插件的下架功能）',
  `no_vip_pay` tinyint(3) NULL DEFAULT 0 COMMENT 'restric_type = 2 时,会员专享,非会员可付费使用,0-关闭,1-开启',
  `editor_remote_img_local` tinyint(1) NULL DEFAULT 1 COMMENT '远程图片本地化',
  `editor_img_clear_link` tinyint(1) NULL DEFAULT 1 COMMENT '清除非本站链接',
  `editor_ai_create` tinyint(1) NULL DEFAULT 0 COMMENT 'AI创作声明',
  `reason` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '退回原因',
  `stock_code` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '商品编码',
  PRIMARY KEY (`aid`) USING BTREE,
  INDEX `add_time`(`add_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 108 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '文档主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_archives
-- ----------------------------
INSERT INTO `wa_archives` VALUES (1, 1, '', 6, 0, '关于我们', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, '', 0, '', '', '', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1526539465, 1690181692, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (2, 8, '', 6, 0, '公司简介', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, '', 0, '', '', '', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1526540452, 1609929701, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (4, 11, '', 1, 1, 'seo是什么？', '', '', '/uploads/allimg/20210107/1-21010G00910428.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 132, 0, '', 0, '', '', '在了解seo是什么意思之后，才能学习seo。什么是seo，从官方解释来看，seo=Search（搜索）Engine（引擎）Optimization（优化），即搜索引擎优化。使用过百度或其他搜索引擎，在', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1526545072, 1610615837, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (9, 10, '', 1, 0, '用户界面设计和体验设计的差别', '', '', '/uploads/allimg/20210107/1-21010G0093N30.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 169, 0, '', 0, '', '', '有时候我们需要获取图集中的第一张图片，下面给出解决办法： 第一步：修改include/extend.func.php 添加  // 提取图集第一张大图', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1526552582, 1610615832, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (10, 10, '', 1, 0, '新手科普文！什么是用户界面和体验设计？', '', '', '/uploads/allimg/20210107/1-21010G0095IU.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 130, 0, '', 0, '', '', '在仿站时，我们常常会自定义很多字段，那么如何在首页调用呢，下面给出方法：一、指定channelid属性（channelid=‘17‘ 17是指内容模型里面指定的模型ID) 二、指定要调用出来的字段ad', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1526552685, 1610615827, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (12, 10, '', 1, 1, '一文读懂互联网女皇和她的报告：互联网领域的投资圣经、选股指南', '', '', '/uploads/allimg/20210107/1-21010G0101L43.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 267, 0, '', 0, '', '', '北京时间 5 月 31 日凌晨，有“互联网女皇”之称的玛丽·米克尔发布了 2018 年的互联网趋势报告，这也是她第 23 年公布互联网报告。每年的互联网女皇报告几乎都会成为每个互联网创业者的必读报告。那么，互联网女皇是谁?为什么她的报告会如此受关注呢', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1526552714, 1610615823, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (13, 12, '', 1, 0, '网站建设的五大核心要素', '', '', '/uploads/allimg/20210107/1-21010G01035129.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 160, 0, '', 0, 'SEO标', 'O关键', 'SEO描述', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1526608216, 1610615818, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (14, 10, '', 1, 0, '网站建设，静态页面和动态页面如何选择', '', '', '/uploads/allimg/20190114/1621fb9e84a97e78b1c1cac6ec6b37bd.png', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 151, 0, '', 0, '', '', '网站建设，静态页面和动态页面如何选择　　电商网站建设为什么要使用静态页面制作。我们都知道，网站制作有分为静态页面制作和动态网页制作，那么建设电商网站采用哪种网站设计技术更好呢?　　我们建设网站最终目的', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1526609496, 1610615811, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (19, 12, '', 1, 0, '从三方面完美的体验企业网站的核心价值', '', '', '/uploads/allimg/20210107/1-21010G011414W.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 146, 0, '', 0, '', '', '从三方面完美的体验企业网站的核心价值　　随着互联网的迅猛发展，一个企业的发展离不开互联网的发展，企业注重企业网站建设，那么必然会给其带来不错的效果。企业网站建设其核心价值直接体现在网站对于用户和商家而', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1526610848, 1610615806, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (20, 11, '', 1, 0, 'CMS是如何应运而生的？', '', '', '/uploads/allimg/20210107/1-21010G0120L29.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 180, 0, '', 0, '', '', '随着网络应用的丰富和发展，很多网站往往不能迅速跟进大量信息衍生及业务模式变革的脚步，常常需要花费许多时间、人力和物力来处理信息更新和维护工作；遇到网站扩充的时候，整合内外网及分支网站的工作就变得更加复', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1526611606, 1610615801, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (21, 11, '', 1, 0, '网站设计与SEO的关系，高手是从这4个维度分析的！', '', '', '/uploads/allimg/20210107/1-21010G012205c.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 296, 0, '', 0, '', '', 'SEO（搜索引擎优化）和有效的网站设计是齐头并进的。好的网站设计是关于创建一个吸引目标受众的网站，并让他们采取某种行动。但是，如果该网站不遵循目前的SEO最佳做法，它的排名将会受到影响，从而会导致真正', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1526611744, 1610615796, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (27, 24, '', 2, 0, '华为HUAWEI NOTE 8', '', '', '/uploads/allimg/20190319/ef3caff1fe91f367fe4939d664a8a5da.jpg', 0, 0, 0, 1, 0, 1, 0, 0, 0, '', 'admin', 289, 0, '', 0, '', '', '全向录音/指向回放、定向免提、指关节手势、分屏多窗口、语音控制、情景智能、单手操作、杂志锁屏、手机找回、无线WIFI打印、学生模式、多屏互动、运动健康全向录音/指向回放、定向免提、指关节手势、分屏多窗', 1, 0, 0, 1909.00, 0.00, 0, 0, 0.00, 0, 0, 0, 2991, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1526613043, 1610615791, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (28, 26, '', 2, 0, '小米笔记本Air 13.3', '', '', '/uploads/allimg/20190114/66109e989148356eadb4ff1eee285826.jpg', 0, 0, 0, 1, 0, 1, 0, 0, 0, '', 'admin', 149, 0, '', 0, '', '', '轻薄全金属机身/256GBSSD/第八代Intel酷睿i5处理器/FHD全贴合屏幕/指纹解锁/office激活不支持7天无理由退货...', 2, 0, 0, 4530.00, 0.00, 0, 0, 0.00, 0, 0, 0, 2997, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1526613271, 1610615782, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (29, 27, '', 2, 0, ' 小米蓝牙项圈耳机', '', '', '/uploads/allimg/20190114/252a53e6fbc8f441b2570f755d2bbeb8.jpg', 0, 0, 0, 1, 0, 1, 0, 0, 0, '', 'admin', 211, 0, '', 0, '', '', '特性M3平板定制AKG品牌高保真耳机，配合M3平板享受HiFi音质...', 3, 0, 0, 198.00, 0.00, 0, 0, 0.00, 0, 0, 0, 2997, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1526613739, 1610615773, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (30, 5, '', 4, 0, '工程机械推土挖掘机类网站模板', '', '', '/uploads/allimg/20190114/4873105f54a14f3785047bd8ecc8b5ac.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 262, 0, '', 0, '', '', '易优新内核开发的模板，该模板属于企业通用、HTML5响应式、工程机械、挖掘机、推土机类企业使用，一款适用性很强的模板，基本可以适合各行业的企业网站！', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1526614069, 1610615767, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (31, 5, '', 4, 0, ' dom编程-window对象', '', '', '/uploads/allimg/20190731/81e62e05fe7cdfb5e9abc50852047dcf.gif', 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 'admin', 249, 0, '', 0, '', '', 'DOM编程－window对象 回顾 请简述一下脚本执行的原理。 JavaScript中有哪些控制语句及其含义？ 如何创建一个有参函数以及如何调用它？ 预习检查 解释名词“根节点”、“子节点”和“相邻节点“。 window对象常用的属性有哪些？ 请解', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1526614168, 1610615761, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (37, 24, '', 2, 0, '华为无线快充手机', '', '', '/uploads/allimg/20190319/8a405e72e2acf9c5a29da7341a0eff89.jpg', 0, 0, 0, 1, 0, 1, 0, 0, 0, '', 'admin', 300, 0, '', 0, '', '', '全身都是科技亮点！7nm麒麟芯片，问鼎性能巅峰，4000万超广角徕卡三摄，随手捕捉大场面', 1, 0, 0, 3109.00, 0.00, 0, 0, 0.00, 0, 0, 0, 2997, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1527507844, 1610615751, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (38, 11, '', 1, 0, '商梦网校：单页SEO站群技术，用10个网站优化排名！', '', '', '/uploads/allimg/20210107/1-21010G012425K.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 121, 0, '', 0, '', '', 'SEO很多伙伴都了解，就是搜索引擎排名优化，通过对网站内部和外部进行优化当用户搜索相应关键词时网站能够排名在搜索引擎前面，具体可以百度搜索“网络营销课程”查看商梦网校操作的案例！但单页SEO很多伙伴可能会有点陌生，单页SEO是将单页网站与内容内容结合', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1527555069, 1610615746, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (39, 12, '', 1, 0, '回顾中国饮料40年发展史，总有一款是你儿时记忆的味道', '', '', '/uploads/allimg/20210107/1-21010G0125Ia.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 186, 0, '', 0, '', '', '对于记忆来说，味道往往是最美的，儿时喝过的饮料，至今回想起来依然觉得津津有味。今天是六一儿童节，青山资本梳理了中国40年来饮料发展的简史，权当节日的小消遣，顺便看看能否找到你记忆深处的那个味道？第一阶段：国人味蕾的开启时代百事可乐在华第一家工厂开业1', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1527824652, 1767581394, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (40, 12, '', 1, 0, '社交媒体时代，如何对粉丝估值？', '空气源热泵两联供系统', '', '/uploads/allimg/20210107/1-21010G01311136.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '来源', 'admin1', 2421, 0, '', 0, 'SEO标题', 'SEO关键词', '11约翰·奎尔奇说，社交媒体有很多营销挑战，如何为粉丝来估值是一个大问题。从营销角度来思考，要关注强纽带和弱纽带。你可能以为，强纽带的密友产生最大的营销影响，研究发现不是这样的，产生更大的影响反而是跟你更疏远的人。演讲者｜约翰·奎尔奇（哈佛商学院教授，曾', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', 'view_article.htm', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '自定义文件', 0, 0, 0, 1527824837, 1767578704, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (41, 12, '', 1, 0, '《颠覆营销:大数据时代的商业革命》：大数据“多即少，少即多”各种行销手段早已令人眼花缭乱', '', '', '/uploads/allimg/20210107/1-21010G0132R20.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '来源', 'admin', 2271, 0, '', 0, 'SEO标题', '网站', '11各种行销手段早已令人眼花缭乱，但究其本质都是在研究客户（消费者），研究客户的所想、所需，使产品或服务有的放矢。大数据时代又给它赋予了新名词：精准营销。大数据最先应用的领域多为面对客户的行业，最先应用的情景也多为精准营销。“酒好也怕巷子深”，产品或服务', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 'show', 0, 0, 0, NULL, 1767537986, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (42, 64, '', 3, 0, 'VIVO X27 手机摄影', '', '', '/uploads/allimg/20190808/17268e40477444ecbf11bcb643f321c2.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 261, 0, '', 0, '', '', 'SEO（搜索引擎优化）和有效的网站设计是齐头并进的。好的网站设计是关于创建一个吸引目标受众的网站，并让他们采取某种行动。但是，如果该网站不遵循目前的 SEO 最佳做法，它的排名将会受到影响，从而会导致真正参与该网站的访问者的数量的较少。相反地，如果将', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1531731387, 1565234124, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (43, 64, '', 3, 0, '3C数码蓝牙耳机产品渲染', '', '', '/uploads/allimg/20190808/1c3dabff0cbf24fb6667899396a866aa.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 279, 0, '', 0, '', '', 'SEO（搜索引擎优化）和有效的网站设计是齐头并进的。好的网站设计是关于创建一个吸引目标受众的网站，并让他们采取某种行动。但是，如果该网站不遵循目前的 SEO 最佳做法，它的排名将会受到影响，从而会导致真正参与该网站的访问者的数量的较少。相反地，如果将', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1531732591, 1610615719, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (44, 64, '', 3, 0, '喷油耳机 建模渲染', '', '', '/uploads/allimg/20190808/45b6f3f95d30a97cfa4a83d315b5c4f1.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 315, 0, '', 0, '', '', 'SEO（搜索引擎优化）和有效的网站设计是齐头并进的。好的网站设计是关于创建一个吸引目标受众的网站，并让他们采取某种行动。但是，如果该网站不遵循目前的 SEO 最佳做法，它的排名将会受到影响，从而会导致真正参与该网站的访问者的数量的较少。相反地，如果将', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1531732811, 1610615714, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (84, 9, '', 1, 0, '荣誉证书一', '', '', '/uploads/allimg/20190722/7a6063154f58f9b76042c01674cfeb34.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 191, 0, '', 0, '', '', '', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1563761544, 1610615703, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (85, 9, '', 1, 0, '荣誉证书二', '', '', '/uploads/allimg/20190722/9bcc063da2a8b6cfa394f8ce55264c86.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 215, 0, '', 0, '', '', '', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1563761561, 1610615688, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (86, 9, '', 1, 0, '荣誉证书三', '', '', '/uploads/allimg/20190722/72c23e63ccabc9e3f42b16dfab17cf4e.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 245, 0, '', 0, '', '', '', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1563761575, 1610615683, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (87, 9, '', 1, 0, '荣誉证书四', '', '', '/uploads/allimg/20190722/a519a45ce1f695da6bb656fb6f4ddcb5.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 182, 0, '', 0, '', '', '', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1563761589, 1610615678, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (89, 20, '', 2, 0, 'Apple iPhone 8 Plus (A1899) 64GB 深空灰色 移动联通4G手机', '', '', '/uploads/allimg/20190731/582042862ba0d06c9408a9a1e669a067.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 233, 0, '', 0, '', '', '主体品牌Apple型号iPhone 8 Plus入网型号A1899上市年份2017年上市月份以官网信息为准基本信息机身颜色深空灰色机身长度（mm）158.4机身宽度（mm）78.1机身厚度（mm）7.5机身重量（g）202输入方式触控运营商标志或内容', 1, 0, 0, 1599.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1564539099, 1610615667, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (90, 24, '', 2, 0, '小米8屏幕指纹版 6GB+128GB 黑色 全网通4G 双卡双待 全面屏拍照智能游戏手机', '', '', '/uploads/allimg/20190731/c4539460b957fea39a9db19e61eb0afe.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 132, 0, '', 0, '', '', '主体品牌小米（MI）型号小米8屏幕指纹版入网型号以官网信息为准上市年份2018年上市月份9月基本信息机身颜色黑色机身长度（mm）154.9机身宽度（mm）74.8机身厚度（mm）7.6机身重量（g）177运营商标志或内容无机身材质分类玻璃后盖操作系统', 1, 0, 0, 1709.00, 0.00, 0, 0, 0.00, 0, 0, 0, 2997, 1, 0, '1', '', 1, 100, 'cn', 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1564539940, 1610615660, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (91, 5, '', 4, 0, '计算机软件系统故障及维护', '', '', '/uploads/allimg/20190731/0c8845e11a94b0f765ab24259c5b06b9.gif', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 276, 0, '', 0, '', '', 'WindowsXP操作系统原理使用系统维护工具系统启动故障的修复病毒防治的一般方法循辱魂币禾赫促陛醛放忆蛔睡钱佯回改波坏敏寄锈掳长提每臣传遥抄个似计算机软件系统故障及维护计算机软件系统故障及维护13.1.1WindowsXP的架构特点WindowsX', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1564565735, 1610615652, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (92, 5, '', 4, 0, '计算机软件系统故障及维护2', '', '', '/uploads/allimg/20190808/682be7153d02d14890144bef217149d1.jpg', 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 'admin', 238, 0, '', 0, '', '', 'Windows操作系统只能使用2 GB,64位可使用4 GB)。接下来,NTLDR启动内建的微型文件系统驱动通过这个步骤,使NTLDR可以识别每一个用NTFS或FAT文件系统格式操作系统只能使用2 GB,64位可使用4 GB)', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 1, '', 0, 0, 0, 1564566264, 1610615642, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (93, 64, '', 3, 0, '鼠标封面设计', '', '', '/uploads/allimg/20190808/b1f94bd8a0feba4062fa19d795099af4.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 264, 0, '', 0, '', '', '软件开发是根据用户要求建造出软件系统或者系统中的软件部分的过程。软件开发是一项包括需求捕捉，需求分析，设计，实现和测试的系统工程。软件一般是用某种程序设计语言来实现的。通常采用软件开发工具可以进行开发。软件分为系统软件和应用软件。 软件并不', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1565161115, 1565232857, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (98, 26, '', 2, 0, 'MIIX520 二合一笔记本12.2英寸 i7', '', '', '/uploads/allimg/20190808/7dd05a89099c482a51be7faf1bb38ad4.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 133, 0, '', 0, '', '', '', 2, 0, 0, 6939.00, 0.00, 0, 0, 0.00, 0, 0, 0, 2997, 1, 0, '1', '', 1, 100, 'cn', 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1565228564, 1610615629, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (99, 26, '', 2, 0, 'MIIX 520 酷睿i5笔记本', '', '', '/uploads/allimg/20190808/821fcaa266d291b4f504fb9a1d412c1c.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 169, 0, '', 0, '', '', '', 2, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 2997, 1, 0, '1', '', 1, 100, 'cn', 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1565228905, 1767538730, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (100, 26, '', 2, 0, '小新 Air 超轻薄笔记本', '', '', '/uploads/allimg/20190808/a4b1ab346ae389e638f4a424b7396ee2.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 281, 0, '', 0, '', '', '', 2, 0, 0, 5499.00, 0.00, 0, 0, 0.00, 0, 0, 0, 2997, 1, 0, '1', '', 1, 100, 'cn', 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1565229115, 1767547473, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (101, 27, '', 2, 0, '联想 X1无线运动蓝牙耳机', '', '', '/uploads/allimg/20190808/3ade68e134d3f8fbbd3401c545541106.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 110, 0, '', 0, '', '', '', 3, 0, 0, 99.00, 0.00, 0, 0, 0.00, 0, 0, 0, 2997, 1, 0, '1', '', 1, 100, 'cn', 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1565229341, 1610615602, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (102, 28, '', 2, 0, '联想智能音箱MINI', '', '', '/uploads/allimg/20190808/989d19deb2377e199ec63d5ef9244be8.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 223, 0, '', 0, '', '', 'CMS人性化推荐 它更懂你；可轻松实现联想SIOT设备控制', 3, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 2997, 1, 0, '1', '', 1, 100, 'cn', 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1565229484, 1767576224, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (103, 28, '', 2, 0, '联想智能音箱G1', '副标题', '', '/uploads/allimg/20190808/13fba5d0f2454c4b8fee4ada1d3fb39b.jpg', 1, 0, 0, 1, 1, 1, 0, 0, 0, '', 'admin', 172, 0, '', 0, '', '', '怦然心动的多彩生活 | 贴心的智能体验', 3, 0, 0, 120.00, 0.00, 0, 0, 0.00, 0, 0, 0, 2997, 1, 0, '1', '', 1, 100, 'cn', 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1565229770, 1767546562, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (104, 66, '', 3, 0, '手机摄影', '', '', '/uploads/allimg/20190808/17268e40477444ecbf11bcb643f321c2.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 261, 0, '', 0, '', '', 'SEO（搜索引擎优化）和有效的网站设计是齐头并进的。好的网站设计是关于创建一个吸引目标受众的网站，并让他们采取某种行动。但是，如果该网站不遵循目前的 SEO 最佳做法，它的排名将会受到影响，从而会导致真正参与该网站的访问者的数量的较少。相反地，如果将', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1609986111, 1767668178, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (105, 66, '', 3, 0, '数码蓝牙耳机产品渲染', '', '', '/uploads/allimg/20190808/1c3dabff0cbf24fb6667899396a866aa.jpg', 0, 0, 0, 0, 0, 1, 0, 0, 0, '', 'admin', 282, 0, '', 0, '', '', 'SEO（搜索引擎优化）和有效的网站设计是齐头并进的。好的网站设计是关于创建一个吸引目标受众的网站，并让他们采取某种行动。但是，如果该网站不遵循目前的 SEO 最佳做法，它的排名将会受到影响，从而会导致真正参与该网站的访问者的数量的较少。相反地，如果将', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 99999, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1609986111, 1767434395, 0, 0, 1, 1, 0, '', '');
INSERT INTO `wa_archives` VALUES (106, 30, '', 6, 0, '联系我们', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, '', '', 0, 0, '', 0, '', '', '', 0, 0, 0, 0.00, 0.00, 0, 0, 0.00, 0, 0, 0, 0, 1, 0, '1', '', 1, 100, 'cn', 0, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 1690166673, 1690166673, 0, 0, 1, 1, 0, '', '');

-- ----------------------------
-- Table structure for wa_archives_flag
-- ----------------------------
DROP TABLE IF EXISTS `wa_archives_flag`;
CREATE TABLE `wa_archives_flag`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `flag_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文档属性名称',
  `flag_attr` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '属性值',
  `flag_fieldname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '字段名',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '状态， 1---显示， 0---隐藏',
  `ifsystem` tinyint(1) NULL DEFAULT 0 COMMENT '字段分类，1=系统(不可修改)，0=自定义',
  `sort_order` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `lang` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) NULL DEFAULT 0 COMMENT '新增时间',
  `update_time` int(11) NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `flag_attr`(`flag_attr`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '文档属性配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_archives_flag
-- ----------------------------
INSERT INTO `wa_archives_flag` VALUES (1, '头条', 'h', 'is_head', 1, 1, 1, 'cn', 1606272350, 1606272350);
INSERT INTO `wa_archives_flag` VALUES (2, '推荐', 'c', 'is_recom', 1, 1, 2, 'cn', 1606272350, 1606272350);
INSERT INTO `wa_archives_flag` VALUES (3, '加推', 'a', 'is_special', 0, 1, 3, 'cn', 1606272350, 1606272350);
INSERT INTO `wa_archives_flag` VALUES (4, '标粗', 'b', 'is_b', 0, 1, 4, 'cn', 1606272350, 1606272350);
INSERT INTO `wa_archives_flag` VALUES (5, '有图', 'p', 'is_litpic', 1, 1, 5, 'cn', 1606272350, 1606272350);
INSERT INTO `wa_archives_flag` VALUES (6, '外链', 'j', 'is_jump', 1, 1, 6, 'cn', 1606272350, 1606272350);
INSERT INTO `wa_archives_flag` VALUES (7, '轮播', 's', 'is_slide', 0, 1, 7, 'cn', 1606272350, 1606272350);
INSERT INTO `wa_archives_flag` VALUES (8, '滚动', 'r', 'is_roll', 0, 1, 8, 'cn', 1606272350, 1606272350);
INSERT INTO `wa_archives_flag` VALUES (9, '热文', 'd', 'is_diyattr', 0, 1, 9, 'cn', 1606272350, 1606272350);

-- ----------------------------
-- Table structure for wa_arctype
-- ----------------------------
DROP TABLE IF EXISTS `wa_arctype`;
CREATE TABLE `wa_arctype`  (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '栏目ID',
  `channeltype` int(10) NULL DEFAULT 0 COMMENT '栏目顶级模型ID',
  `current_channel` int(10) NULL DEFAULT 0 COMMENT '栏目当前模型ID',
  `parent_id` int(10) NULL DEFAULT 0 COMMENT '栏目上级ID',
  `topid` int(10) NULL DEFAULT 0 COMMENT '顶级栏目ID',
  `typename` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '栏目名称',
  `dirname` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '目录英文名',
  `dirpath` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '目录存放HTML路径',
  `diy_dirpath` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '列表静态文件存放规则',
  `rulelist` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '列表静态文件存放规则',
  `ruleview` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '文档静态文件存放规则',
  `englist_name` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '栏目英文名',
  `grade` tinyint(1) NULL DEFAULT 0 COMMENT '栏目等级',
  `typelink` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '栏目链接',
  `litpic` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '栏目图片',
  `templist` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '列表模板文件名',
  `tempview` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '文档模板文件名',
  `seo_title` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'SEO标题',
  `seo_keywords` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'seo关键字',
  `seo_description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'seo描述',
  `sort_order` int(10) NULL DEFAULT 0 COMMENT '排序号',
  `is_hidden` tinyint(1) NULL DEFAULT 0 COMMENT '是否隐藏栏目：0=显示，1=隐藏',
  `is_part` tinyint(1) NULL DEFAULT 0 COMMENT '栏目属性：0=内容栏目，1=外部链接',
  `admin_id` int(10) NULL DEFAULT 0 COMMENT '管理员ID',
  `is_del` tinyint(1) NULL DEFAULT 0 COMMENT '伪删除，1=是，0=否',
  `del_method` tinyint(1) NULL DEFAULT 0 COMMENT '伪删除状态，1为主动删除，2为跟随上级栏目被动删除',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '启用 (1=正常，0=屏蔽)',
  `is_release` tinyint(1) NULL DEFAULT 0 COMMENT '栏目是否应用于会员投稿发布，1是，0否',
  `weapp_code` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '插件栏目唯一标识',
  `lang` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) NULL DEFAULT 0 COMMENT '新增时间',
  `update_time` int(11) NULL DEFAULT 0 COMMENT '更新时间',
  `target` tinyint(1) NULL DEFAULT 0 COMMENT '新窗口打开',
  `nofollow` tinyint(1) NULL DEFAULT 0 COMMENT '防抓取',
  `typearcrank` int(10) NULL DEFAULT 0 COMMENT '阅读权限：0=开放浏览，-1=待审核稿件',
  `empty_logic` tinyint(1) NULL DEFAULT 0 COMMENT '空内容逻辑',
  `page_limit` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '限制页面 1-栏目页面 0-文档页面',
  `total_arc` int(10) NULL DEFAULT 0 COMMENT '栏目下文档数量',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `dirname`(`dirname`, `lang`) USING BTREE,
  INDEX `parent_id`(`channeltype`, `parent_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 79 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '文档栏目表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_arctype
-- ----------------------------
INSERT INTO `wa_arctype` VALUES (1, 6, 6, 0, 0, '关于我们', 'guanyuwomen', '/guanyuwomen', '/guanyuwomen', '{栏目目录}/index.html', '{栏目目录}/{aid}.html', '', 0, '/index.php?m=home&c=Lists&a=index&tid=8', '', 'lists_single.htm', '', '', '', '', 1, 0, 1, 0, 0, 0, 1, 0, '', 'cn', 1526539465, 1767670583, 1, 1, 0, 0, '0', 0, NULL, '2026-01-06 11:36:23');
INSERT INTO `wa_arctype` VALUES (2, 1, 1, 0, 0, '新闻动态', 'xinwendongtai', '/xinwendongtai', '/xinwendongtai', '', '', 'News &amp; Trends', 0, '', '', 'lists_article.htm', 'view_article.htm', '', '', '', 2, 0, 0, 0, 0, 0, 1, 1, '', 'cn', 1526539487, 1767169561, 0, 0, 0, 0, '0', 13, NULL, '2025-12-31 16:26:01');
INSERT INTO `wa_arctype` VALUES (3, 2, 2, 0, 0, '产品展示', 'chanpinzhanshi', '/chanpinzhanshi', '/chanpinzhanshi', '', '', 'Product show', 0, '', '/uploads/allimg/20210106/1-2101061SR5120.jpg', 'lists_product.htm', 'view_product.htm', '', '', '未来，期待与用户携手缔造一个更好的易而优CMS', 3, 0, 0, 0, 0, 0, 1, 0, '', 'cn', 1526539505, 1609929507, 0, 0, 0, 0, '0', 12, NULL, NULL);
INSERT INTO `wa_arctype` VALUES (4, 3, 3, 0, 0, '解决方案', 'kehuanli', '/kehuanli', '/kehuanli', '', '', 'Case', 0, '', '', 'lists_images.htm', 'view_images.htm', 'SEO', '', '', 4, 0, 0, 0, 0, 0, 1, 0, '', 'cn', 1526539517, 1766759791, 0, 0, 0, 0, '0', 6, NULL, '2025-12-26 22:36:31');
INSERT INTO `wa_arctype` VALUES (5, 4, 4, 0, 0, '资料下载', 'ziliaoxiazai', '/ziliaoxiazai', '/ziliaoxiazai', '', '', 'Download', 0, '', '/uploads/allimg/20210106/1-2101061S911K5.jpg', 'lists_download.htm', 'view_download.htm', '', '', '', 5, 0, 0, 0, 0, 0, 1, 0, '', 'cn', 1526539530, 1609929553, 0, 0, 0, 0, '0', 4, NULL, NULL);
INSERT INTO `wa_arctype` VALUES (8, 6, 6, 1, 1, '公司简介', 'guanyuwomenq', '/guanyuwomen/guanyuwomenq', '/guanyuwomen/guanyuwomenq', '', '', 'About Us', 1, '', '', 'lists_single.htm', '', '', '', '', 1, 0, 0, 0, 0, 0, 1, 0, '', 'cn', 1526540452, 1767345410, 0, 0, 0, 0, '0', 0, NULL, '2026-01-02 17:16:50');
INSERT INTO `wa_arctype` VALUES (9, 1, 1, 1, 1, '公司荣誉', 'gsry', '/guanyuwomen/gsry', '/guanyuwomen/gsry', '', '', 'GLORIES Glories', 1, '', '', 'lists_article.htm', 'view_article.htm', '', '', '', 2, 0, 0, 0, 0, 0, 1, 1, '', 'cn', 1526540478, 1767345411, 0, 0, 0, 0, '0', 4, NULL, '2026-01-02 17:16:51');
INSERT INTO `wa_arctype` VALUES (10, 1, 1, 2, 2, '公司动态', 'gongsidongtai', '/xinwendongtai/gongsidongtai', '/xinwendongtai/gongsidongtai', '', '', '', 1, '', '', 'lists_article.htm', 'view_article.htm', '', '', '', 100, 0, 0, 0, 0, 0, 1, 1, '', 'cn', 1526540530, 1767345388, 0, 0, 0, 0, '0', 4, NULL, '2026-01-02 17:16:28');
INSERT INTO `wa_arctype` VALUES (11, 1, 1, 2, 2, '行业资讯', 'xingyezixun', '/xinwendongtai/xingyezixun', '/xinwendongtai/xingyezixun', '', '', '', 1, '', '', 'lists_article.htm', 'view_article.htm', '', '', '', 100, 0, 0, 0, 0, 0, 1, 1, '', 'cn', 1526540543, 1767345392, 0, 0, 0, 0, '0', 4, NULL, '2026-01-02 17:16:32');
INSERT INTO `wa_arctype` VALUES (12, 1, 1, 2, 2, '媒体报道', 'meitibaodao', '/xinwendongtai/meitibaodao', '/xinwendongtai/meitibaodao', '', '', '', 1, '', '', 'lists_article.htm', 'view_article.htm', '', '', '', 100, 0, 0, 0, 0, 0, 1, 1, '', 'cn', 1526540554, 1609929495, 0, 0, 0, 0, '0', 5, NULL, NULL);
INSERT INTO `wa_arctype` VALUES (20, 2, 2, 3, 3, '手机数码', 'shouji', '/chanpinzhanshi/shouji', '/chanpinzhanshi/shouji', '', '', '', 1, '', '', 'lists_product.htm', 'view_product.htm', '', '', '', 100, 0, 0, 0, 0, 0, 1, 0, '', 'cn', 1526612114, 1609929507, 0, 0, 0, 0, '0', 4, NULL, NULL);
INSERT INTO `wa_arctype` VALUES (21, 2, 2, 3, 3, '电脑产品', 'diannao', '/chanpinzhanshi/diannao', '/chanpinzhanshi/diannao', '', '', '', 1, '', '', 'lists_product.htm', 'view_product.htm', '', '', '', 100, 0, 0, 0, 0, 0, 1, 0, '', 'cn', 1526612188, 1609929507, 0, 0, 0, 0, '0', 4, NULL, NULL);
INSERT INTO `wa_arctype` VALUES (22, 2, 2, 3, 3, '周边配件', 'peijian', '/chanpinzhanshi/peijian', '/chanpinzhanshi/peijian', '', '', '', 1, '', '', 'lists_product.htm', 'view_product.htm', '', '', '', 100, 0, 0, 0, 0, 0, 1, 0, '', 'cn', 1526612218, 1609929507, 0, 0, 0, 0, '0', 4, NULL, NULL);
INSERT INTO `wa_arctype` VALUES (24, 2, 2, 20, 3, '智能手机', 'zhinenshouji', '/chanpinzhanshi/shouji/zhinenshouji', '/chanpinzhanshi/shouji/zhinenshouji', '', '', '', 2, '', '', 'lists_product.htm', 'view_product.htm', '', '', '', 100, 0, 0, 0, 0, 0, 1, 0, '', 'cn', 1526612571, 1610334638, 0, 0, 0, 0, '0', 3, NULL, '2025-12-26 02:12:26');
INSERT INTO `wa_arctype` VALUES (25, 2, 2, 20, 3, '畅玩手机', 'changwanshouji', '/chanpinzhanshi/shouji/changwanshouji', '/chanpinzhanshi/shouji/changwanshouji', '', '', '', 2, '', '', 'lists_product.htm', 'view_product.htm', '', '', '', 100, 0, 0, 0, 0, 0, 1, 0, '', 'cn', 1526612606, 1610334638, 0, 0, 0, 0, '0', 0, NULL, '2025-12-26 02:12:42');
INSERT INTO `wa_arctype` VALUES (26, 2, 2, 21, 3, '笔记本电脑', 'bijibendiannao', '/chanpinzhanshi/diannao/bijibendiannao', '/chanpinzhanshi/diannao/bijibendiannao', '', '', '', 2, '', '', 'lists_product.htm', 'view_product.htm', '', '', '', 100, 0, 0, 0, 0, 0, 1, 0, '', 'cn', 1526612635, 1610334638, 0, 0, 0, 0, '0', 4, NULL, '2025-12-26 02:13:03');
INSERT INTO `wa_arctype` VALUES (27, 2, 2, 22, 3, '耳机', 'erji', '/chanpinzhanshi/peijian/erji', '/chanpinzhanshi/peijian/erji', '', '', '', 2, '', '', 'lists_product.htm', 'view_product.htm', '', '', '', 100, 0, 0, 0, 0, 0, 1, 0, '', 'cn', 1526612661, 1610334638, 0, 0, 0, 0, '0', 2, NULL, NULL);
INSERT INTO `wa_arctype` VALUES (28, 2, 2, 22, 3, '音箱', 'yinxiang', '/chanpinzhanshi/peijian/yinxiang', '/chanpinzhanshi/peijian/yinxiang', '', '', '', 2, '', '', 'lists_product.htm', 'view_product.htm', '', '', '', 100, 0, 0, 0, 0, 0, 1, 0, '', 'cn', 1526612678, 1610334638, 0, 0, 0, 0, '0', 2, NULL, '2025-12-26 02:13:17');
INSERT INTO `wa_arctype` VALUES (29, 2, 2, 22, 3, '充电宝', 'chongdianbao', '/chanpinzhanshi/peijian/chongdianbao', '/chanpinzhanshi/peijian/chongdianbao', '', '', '', 2, '', '', 'lists_product.htm', 'view_product.htm', '', '', '', 100, 0, 0, 0, 0, 0, 1, 0, '', 'cn', 1526612691, 1610334638, 0, 0, 0, 0, '0', 0, NULL, NULL);
INSERT INTO `wa_arctype` VALUES (30, 6, 6, 0, 0, '联系我们', 'lianxiwomen986', '/lianxiwomen986', '/lianxiwomen986', '{栏目目录}/index.html', '{栏目目录}/{aid}.html', 'Online Message', 0, '', '/uploads/allimg/20210106/1-2101061T032D7.jpg', 'lists_single_contact.htm', '', '', '', '', 7, 0, 0, 0, 0, 0, 1, 0, '', 'cn', 1526634493, 1690166673, 0, 0, 0, 0, '0', 0, NULL, '2025-12-26 00:40:15');
INSERT INTO `wa_arctype` VALUES (64, 3, 3, 4, 4, '系统方案', 'xitong', '/kehuanli/xitong', '/kehuanli/xitong', '', '', '', 1, '', '', 'lists_images.htm', 'view_images.htm', '', '', '', 100, 0, 0, 1, 0, 0, 1, 0, '', 'cn', 1565083870, 1766759791, 0, 0, 0, 0, '0', 4, NULL, '2025-12-26 22:36:31');
INSERT INTO `wa_arctype` VALUES (66, 3, 3, 4, 4, '应用方案', 'yingyong', '/kehuanli/yingyong', '/kehuanli/yingyong', '', '', '', 1, '', '', 'lists_images.htm', 'view_images.htm', '', '', '', 100, 0, 0, 1, 0, 0, 1, 0, '', 'cn', 1565083875, 1766759791, 0, 0, 0, 0, '0', 2, NULL, '2025-12-26 22:36:31');
INSERT INTO `wa_arctype` VALUES (69, 2, 2, 24, 3, 'HTC', 'htc', '/htc', '/htc', '', '', '', 3, '', '', 'lists_product.htm', 'view_product.htm', '', '', '', 100, 0, 0, 1, 0, 0, 1, 0, '', 'cn', 1766678741, 1766678741, 0, 0, 0, 1, '0', 0, '2025-12-26 00:05:41', '2025-12-26 02:13:45');
INSERT INTO `wa_arctype` VALUES (78, 2, 2, 69, 3, '多普达', 'duopuda', '/htc/duopuda', '/htc/duopuda', '', '', '', 4, '', '', 'lists_product.htm', 'view_product.htm', '', '', '', 100, 0, 0, 1, 0, 0, 1, 0, '', 'cn', 1766688650, 1766688650, 0, 0, 0, 1, '0', 0, '2025-12-26 02:50:50', '2025-12-26 02:50:50');

-- ----------------------------
-- Table structure for wa_article_content
-- ----------------------------
DROP TABLE IF EXISTS `wa_article_content`;
CREATE TABLE `wa_article_content`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) NULL DEFAULT 0 COMMENT '文档ID',
  `content` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '内容详情',
  `content_ey_m` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '手机端内容详情',
  `add_time` int(11) NULL DEFAULT 0 COMMENT '新增时间',
  `update_time` int(11) NULL DEFAULT 0 COMMENT '更新时间',
  `test` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '测试字段',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `news_id`(`aid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 45 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文章附加表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_article_content
-- ----------------------------
INSERT INTO `wa_article_content` VALUES (1, 4, '&lt;p&gt;在了解seo是什么意思之后，才能学习seo。&lt;br/&gt;&lt;/p&gt;&lt;p&gt;什么是seo，从官方解释来看，seo=Search（搜索） Engine（引擎） Optimization（优化），即搜索引擎优化。&lt;/p&gt;&lt;p&gt;使用过百度或其他搜索引擎，在搜索框中输入某一个关键词，如铁艺大门，排名靠前带有广告字样，背景略不同的是竞价位置，为俗称的&lt;a href=&quot;http://www.xminseo.com/2376.html&quot; title=&quot;&quot;&gt;sem&lt;/a&gt;位置。&lt;/p&gt;&lt;p&gt;seo是基于搜索引擎营销的一种网络营销方式，通过seo技术，提升网站关键词排名，获得展现，继而获得曝光，继而获得用户点击，继而获得转化。&lt;/p&gt;&lt;p&gt;一：seo分类。&lt;/p&gt;&lt;p&gt;细化来看，所有有利于网站关键词排名提升的点，都可以归纳于seo，为便于理解，我们将seo分为站内seo和站外seo。&lt;/p&gt;&lt;p&gt;1：站内seo。&lt;/p&gt;&lt;p&gt;什么是站内seo？通俗来讲，就是指网站内部优化，即网站本身内部的优化，包括代码标签优化、内容优化、安全建设、用户体验等。&lt;/p&gt;&lt;p&gt;2：站外seo。&lt;/p&gt;&lt;p&gt;什么是站外seo？通俗来讲，就是网站的外部优化，包括外链建设，品牌建设，速度优化，引流等。&lt;/p&gt;&lt;p&gt;二：seo相关建议。&lt;/p&gt;&lt;p&gt;1：建议把seo定位于一种网络营销方式，在学习，使用seo的过程中，将他作为一种获取流量的渠道。&lt;/p&gt;&lt;p&gt;2：新手学习seo的理想平台是百度搜索资源平台而非其他；理论联系实际操作是更为有效的学习方式；有经验的seo高手教会更快的掌握好seo；多思考，多总结，才能领悟seo的精髓。&lt;/p&gt;&lt;p&gt;3：学习seo之前，熟悉掌握相关seo术语很有必要。&lt;/p&gt;&lt;p&gt;4：很多时候，seo的理论与现实是相违背的，也就是说seo的理论点不复杂，操作点却很难达到。&lt;/p&gt;&lt;p&gt;新手接触seo，感觉无所适从，请熟读seo术语，后面会越来越轻松。&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '', 1610615837, 1610615837, '');
INSERT INTO `wa_article_content` VALUES (5, 9, '&lt;p&gt;注：用户界面（UI，User Interface）设计是设计软件产品所涉及到的几个交叉学科之一。不论是用户体验（UX，User Experience）、交互设计（ID，Interaction Design），还是视觉/图形设计（Visual / Graphic Design），都能牵扯到用户界面设计。&lt;/p&gt;&lt;p&gt;一、什么是用户界面设计？&lt;/p&gt;&lt;p&gt;广泛来讲，用户界面是人与机器交流的媒介。用户向机器发出指令，机器随即开始一段进程，回复信息，并给出反馈。用户可以根据用户反馈进行下一步操作的决策。&lt;/p&gt;&lt;p&gt;人机交互（HCI，Human Computer Interaciton）所关注的主要是数字界面，即过去的打孔机、命令行，直至今天的图形界面（GUI，Graphic Design）。&lt;/p&gt;&lt;p&gt;用户界面设计对于数码产品来说主要关注的是布局、信息结构，以及界面元素在显示屏和各种终端平台上的展示。电子游戏和电视界面也包括其中。&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '', 1610615832, 1610615832, '');
INSERT INTO `wa_article_content` VALUES (15, 39, '&lt;p&gt;对于记忆来说，味道往往是最美的，儿时喝过的饮料，至今回想起来依然觉得津津有味。&lt;br/&gt;&lt;/p&gt;&lt;p&gt;今天是六一儿童节，青山资本梳理了中国40年来饮料发展的简史，权当节日的小消遣，顺便看看能否找到你记忆深处的那个味道？&lt;/p&gt;&lt;h2&gt;第一阶段：国人味蕾的开启时代&lt;/h2&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;&lt;p&gt;百事可乐在华第一家工厂开业&lt;/p&gt;&lt;p&gt;1981年，可口可乐在中国第一条生产线正式投产，主要供应旅游饭店，卖给外国人收取外汇，百事可乐也在深圳建立了第一家罐装厂。&lt;/p&gt;&lt;p&gt;1982年，国家把饮料纳入“国家计划管理产品”，可口可乐开始在北京市场进行内销。&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '', 1610615741, 1610615741, '');
INSERT INTO `wa_article_content` VALUES (16, 40, '<p>1111约翰&middot;奎尔奇说， 社交媒体有很多营销挑战，如何为粉丝来估值是一个大问题。从营销角度来思考，要关注强纽带和弱纽带。你可能以为，强纽带的密友产生最大的营销影响，研究发现不是这样的，产生更大的影响反而是跟你更疏远的人。</p>\n<p>演讲者｜ 约翰&middot;奎尔奇</p>\n<p>（ 哈佛商学院教授， 曾任伦敦商学院院长、中欧国际工商学院副院长）</p>\n<p>非常感谢大家在周日早上回来听我讲课。对于你们这些创业者，或者希望成为创业者的人，我今天准备了一个特别的讲座。</p>\n<p>很多创业者没有把最终愿景很好界定，所以每天都忙于灭火，忙于生存。</p>\n<p>创业营销，你必须做好规划</p>\n<p>今天将从创业营销这个话题开始，包括你如何生存和成功。创业营销包括四个关键领域，你必须很好地去规划：</p>\n<ul class=\" list-paddingleft-2\" style=\"list-style-type: inherit;\">\n<li>\n<p>要有正确的目标客户和最终用户；</p>\n</li>\n<li>\n<p>要有正确的产品和服务</p>\n</li>\n<li>\n<p>要有一个非常好的人才团队，使得商业创意能够实现；</p>\n</li>\n<li>\n<p>要有好的合作伙伴，不是分销商，而是会计、律师等服务伙伴。</p>\n</li>\n</ul>\n<p>那么，何为创业营销？ ？</p>\n<p>第一，这是从愿景到行动的逆向工程设计</p>\n<p>当星巴克只有 5 家店时，创始人就有一个愿景，让星巴克成为你生活中的第三空间。</p>\n<p>对创业者要从愿景开始，向后进行逆向工程的设计：看一下需要有什么样的行动，才能实现愿景。 很多创业者没有把最终愿景很好界定，所以每天都忙于灭火，忙于生存。</p>\n<p>第二，快速的周期，低成本进行试验，以提供证据</p>\n<p>有了愿景要去思考，怎样做一些快速的低成本实验测试创意，向合作伙伴、客户等证明，这是一个非常好的愿景。换句话说， 你需要短期的成就作为证据。</p>\n<p>第三，与高瞻远瞩的客户共同开发</p>\n<p>大多数的客户是保守的，不想浪费时间在新公司上。 你必须要找到有远见的客户，他们愿意在你身上冒风险。 他们可能是小的新兴客户，不是你想要进入的那个市场的好根基客户。</p>\n<p>第四：创建小步快跑的综合路线图</p>\n<p>包括创建产品路线图、客户图、合作伙伴路线图、人才路线图。 创业者应该有一个长达一年甚至三年的路线图，看下你希望这个公司在这四个维度上应该怎么样取得进步。</p>\n<p>举个例子</p>\n<p>上世纪 90 年代末， John Osher 发明了 SpinBrush ，这是一个低成本的电动牙刷。因为 他洞察到市场上存在着一个很大的空白：普通手动牙刷每支两美元，电动牙刷要 50 美元。 但是这两者之间，没有任何中间产品。</p>\n<p>他想开发一个牙刷，价格介于两者之间。他思考了下新牙刷成功的性能标准：</p>\n<ul class=\" list-paddingleft-2\" style=\"list-style-type: inherit;\">\n<li>\n<p>清洁上要优于手动牙刷，不然消费者不会付出更高的价格；</p>\n</li>\n<li>\n<p>自带电池能用三个月，如果每周都要换电池太崩溃；</p>\n</li>\n<li>\n<p>包装中有试用的特点，大家愿意看看牙刷启动后是怎么旋转的；</p>\n</li>\n<li>\n<p>零售价不到 6 美元。</p>\n</li>\n</ul>\n<p>他对新牙刷的定位是：是更好的手动牙刷，而不是一个更便宜的电动牙刷。</p>\n<p>对于消费者，是从 2 美元增加到 6 美元，而不是从 50 美元降到 6 美元。因为如果是后者，零售商会觉得赔了：消费者只花了6美元，而以前是50美元。但是现在，消费者从花2块提高到了花6块。</p>\n<p>所以创业者不仅要考虑最终用户，还要思考如何让分销商多赚钱，因为你必须通过他们，产品才能到最终客户那里。 界定竞争的时候，好的定位声明非常重要。 最后，他把公司卖给了 宝洁，一共赚了4.8亿美元。</p>\n<p>大家看，其实非常简单，就是因为他有大量的消费者洞察，填补了没有任何人看见的市场空白。</p>\n<p>再举个例子</p>\n<p>这家公司叫 Intuit ，创始人在20年前就发现，好多人在应对自己税务处理的时候，每年要填一个纳税申报单再交给政府，很麻烦。</p>\n<p>Intuit 是第一个开发个人理财软件的公司，尤其是做纳税管理方面的软件，不管是个人还是小企业都可以用。 但是这个好用的软件包，不知道卖向哪里，没人相信它能用。</p>\n<p>有时候你最大的问题就是，你的新产品如何把分销商搞定。他们分销很多东西，根本没时间花五小时检查你这个不知名的产品能不能用。</p>\n<p>最后他直接向消费者保证： 如果买了这个产品，六分钟内没学会怎么用，钱退给你，产品也送给你。</p>\n<p>除了退钱，他们还做了什么与众不同的事情呢？</p>\n<ul class=\" list-paddingleft-2\" style=\"list-style-type: inherit;\">\n<li>\n<p>在买家允许下，跟着买家观察他的首次使用过程。</p>\n</li>\n<li>\n<p>公司所有高管每个月必须花两小时做客户的技术支持，听客户遇到的问题；</p>\n</li>\n<li>\n<p>做客户服务的技术支持，是公司里晋升的必经路径；</p>\n</li>\n<li>\n<p>把客户的信当着所有高管的面大声朗读，不管是感谢还是指责。</p>\n</li>\n</ul>\n<p>这使得他们 50% 的销售是来自于口碑， 20% 的销售是来自于技术支持的推荐。</p>\n<p>&ldquo; 客户真正想要的和技术真正能做好的交叉点 &mdash;&mdash; 在此处才能找到真正的伟大。 &rdquo;</p>\n<p>&ldquo; 我们不管做什么，都是有客户存在的。 &rdquo;</p>\n<p>&mdash;&mdash;ScottCook（ Intuit创始人）</p>\n<p>&nbsp;</p>', '', 1767578704, 1767578704, 'addnew');
INSERT INTO `wa_article_content` VALUES (17, 41, '<p>各种行销手段早已令人眼花缭乱，但究其本质都是在研究客户（消费者），研究客户的所想、所需，使产品或服务有的放矢。大数据时代又给它赋予了新名词：精准营销。大数据最先应用的领域多为面对客户的行业，最先应用的情景也多为精准营销。</p>\n<p>&ldquo;酒好也怕巷子深&rdquo;，产品或服务的信息要送达客户才可能促成交易。一般认为，向客户传达产品或服务信息要靠广告。广告古已有之，&ldquo;三碗不过岗&rdquo;的酒幌子就是广告。没有互联网的时代，我们熟悉的是电视广告、广播广告、印刷品平面广告、户外广告牌等，当然，也包括吆喝叫卖。但过去的广告是千人一面、不区分受众的。后来商家对客户的信息有所采集就有了CRM，经过客户分类，可以更好地服务于不同的客户群体。互联网+大数据时代让CRM有了新的发展机遇，管理客户不再是简单的数字统计和没有个性的（或简单聚类的）直邮、定投。随着商家对客户知道更多、了解更深，便有机会为客户提供个性化的营销方案，进一步改善客户体验，成为了个性化营销或叫精准营销。大数据时代，让很多过去的不可能变为可能，营销活动也赢来了新的发展机遇。</p>\n<p>时代不同，商业经营的形式会变化，但本质就是两件事：开源，节流。开源是开拓新客户，发现新商机；节流是减少内部运营成本，提高资源利用效率。要实现这一切都需要以数据为依据的决策。过去，人们也在长期的经营活动中，采集和运用了与经营活动相关的很多强相关数据，也形成了选择客户的标准。鉴于当时的技术瓶颈，做大样本的数据采集及数据分析成本都过高，无法在更大范围推广运用。大数据时代，人们有了廉价采集数据和存储数据的可能，廉价的计算资源让数据分析成为了可能。</p>\n<p>大数据精准营销的背后，是用多维度的数据来观察客户，描述客户，就是说为客户画像。说&ldquo;依托大数据，可以让营销人员比过去更了解客户，比客户自己更了解客户的需求&rdquo;并不为过。营销人员无不想知道客户是谁、在哪里、消费习惯是什么、需要什么、什么时候需要、用什么方式向他们传递信息更为有效等等，通过数据采集和数据分析分析可以找到答案。精准营销不仅可以帮助商家开源---发现潜在客户，还可以帮助商家节流---发现潜在风险。当我们对客户了解更多，就会知道哪位客户可能在经营中存在风险。</p>\n<p>若问每个经营者是否会运用从业经验来进行营销，多数答案是肯定的。但若问经营者是否会利用数据进行营销，恐怕答案就是五花八门。一般认为，应用数据进行营销是大公司的事情，与小公司无缘。其实，大到跨国公司，小到街边小贩，运用数据进行营销，都会收到意想不到的结果。不相信吗？街边小贩留意一下天气预报（刮风，下雨，还是暴晒）就知道明天有哪些生意的机会，进而知道该如何备货。建议中小公司的人不要拒绝精准营销的理念，不妨学学精准营销的思想方法。即便是经营者有丰富的经验，把经验数据化对经营也会很有帮助。</p>\n<p>《颠覆营销》一书就是在教读者如何运用大数据来做营销。书中案例丰富、语言可读性强。值得关心大数据营销的各界朋友读一读。</p>\n<p>我认同书中的不少观点：&ldquo;大数据重新定义产业竞争规则，比的不是数据规模大小，不是统计技术，也不是强大的计算能力，而是核心数据的解读能力&rdquo;。在很多人纠结于大数据定义的今天，我们确实更应该关注数据的核心价值理解与应用。书中提出的&ldquo;问对问题&rdquo;也很重要。经营者平时的问题一定不少，但追问究竟时，就可能出现偏差，导致&ldquo;失之毫厘谬以千里&rdquo;。问对问题能力的提高涉及思想方法，需要在锻炼中提高。验证问题是否问对了，恰恰就是数据分析师可以做贡献的地方。</p>\n<p>本书还引起了二个值得更深入思考的问题：</p>\n<p>仅仅发现不同客户群体的消费习惯，适时提醒客户去消费，还远远不够。比如：某消费者一个月的正常理性消费在两千元的水平，一般在A，B两家商店消费。A商店运用了精准营销的理念会让消费者把这两千元都花在A商店，随着B商店的后来居上，消费者又可能重新回到B商店消费这两千元。在供给过剩需求不足的今天，既有的消费额在不同商家中进行分配或迁移都不能带来社会消费总量的增加。大数据营销的更高水平应用是提前知晓客户尚未被满足、甚至尚未被发现的需求。大数据的价值挖掘有机会把商家（含厂家）和客户连在一起，让商家提供更多的满足客户个性化需求的产品或服务，让客户的消费意愿提高。这是数据价值挖掘工作者面临的新挑战。</p>\n<p>数据真的越多越好吗？不少大数据公司热衷于用爬虫软件在网上&ldquo;爬&rdquo;各种数据。然而同一数据集在不同的应用场景价值密度是不一样的，针对特定应用场景也并非是数据维度越多就越好，一定要围绕应用目标来采集数据和使用数据。提升维度来采集更多数据一定是有助于更详尽地描述事物，但无疑也增加了处理数据的复杂性。每一次技术的进步，都给人类带来新的想象空间，难免欲望膨胀自信满满，对世界的认知也随之升维，甚至是无节制地升维。之后发现升维带来资源的占用，智慧跟不上，无节制地升维反而是解决方案复杂化，冷静下来会重新启动降维思考。也许人类的认知与智慧就是在升维、降维、再升维、再降维中交替前行的。本书的降维思考，必要时回归本元的思考给人们启示。</p>\n<p>大数据时代工具手段固然重要，思想方法更为重要。</p>\n<p>&nbsp;</p>', '', 1767537986, 1767537986, 'addnew2');
INSERT INTO `wa_article_content` VALUES (6, 10, '&lt;p&gt;Z Yuhan：用户界面（UI，User Interface）设计是设计软件产品所涉及到的几个交叉学科之一。不论是用户体验（UX，User Experience）、交互设计（ID，Interaction Design），还是视觉/图形设计（Visual / Graphic Design），都能牵扯到用户界面设计。&lt;/p&gt;&lt;h4&gt;一、什么是用户界面设计？&lt;/h4&gt;&lt;p&gt;广泛来讲，用户界面是人与机器交流的媒介。用户向机器发出指令，机器随即开始一段进程，回复信息，并给出反馈。用户可以根据用户反馈进行下一步操作的决策。&lt;/p&gt;&lt;p&gt;人机交互（HCI，Human Computer Interaciton）所关注的主要是数字界面，即过去的打孔机、命令行，直至今天的图形界面（GUI，Graphic Design）。&lt;/p&gt;&lt;p&gt;用户界面设计对于数码产品来说主要关注的是布局、信息结构，以及界面元素在显示屏和各种终端平台上的展示。电子游戏和电视界面也包括其中。&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '', 1610615827, 1610615827, '');
INSERT INTO `wa_article_content` VALUES (7, 12, '&lt;p&gt;北京时间 5 月 31 日凌晨，有“互联网女皇”之称的玛丽·米克尔发布了 2018 年的互联网趋势报告，这也是她第 23 年公布互联网报告。&lt;br/&gt;&lt;/p&gt;&lt;p&gt;每年的互联网女皇报告几乎都会成为每个互联网创业者的必读报告。那么，互联网女皇是谁?为什么她的报告会如此受关注呢?&lt;/p&gt;&lt;p&gt;互联网女皇： 90 年代华尔街的象征&lt;/p&gt;&lt;p&gt;1958 年 9 月，玛丽·米克尔(Mary Meeker)出生于美国印第安纳州。&lt;/p&gt;&lt;p&gt;1982 年，米克尔加入了当时最负盛名的券商美林公司，担任股票经纪人。&lt;/p&gt;&lt;p&gt;米克尔的明星分析师之路开始于 1991 年，这年她加入了知名投行摩根士丹利，开始了自己辉煌的科技分析师生涯。&lt;/p&gt;&lt;p&gt;自 1995 年以来，米克尔的工作随着网络潮流变化而变化，她逐重于研究雅虎、美国在线及亚马孙等知名公司将如何调整结构并相互竞争。&lt;/p&gt;&lt;p&gt;1996 年，玛丽·米克尔如愿地成为摩根·斯坦利技术股票分析部的负责人，还创造出了华尔街闪耀的新职业——互联网分析师。就像垃圾债券代表了 80 年代华尔街一样，玛丽·米克尔成了 90 年代华尔街的象征。&lt;/p&gt;&lt;p&gt;2010 年底，米克尔辞去摩根士丹利董事总经理的职位，离开华尔街，去到加州成为知名风投KPCB的合伙人。KPCB公司(Kleiner\r\n Perkins Caufield &amp;amp; Byers)成立于 1972 年，是美国最大的风险基金，其最得意的杰作是网景公司的创立。&lt;/p&gt;&lt;p&gt;互联网女皇报告：互联网领域的投资圣经、选股指南&lt;/p&gt;&lt;p&gt;1994 年，米克尔在《纽约时报》上偶然看到一篇讲述创业公司Mosaic研发网络浏览器的报道。米克尔立即意识到，这种网络浏览器可能会改变人们获取信息的方式。她随后就联系了Mosaic的两位创始人，并向华尔街投资者大力介绍这家公司。&lt;/p&gt;&lt;p&gt;Mosaic后来改名为网景，并在 1995 年在纽约上市。得益于米克尔与网景两位创始人的良好关系，摩根士丹利成为网景首次公开募股(IPO)的主承销商。&lt;/p&gt;&lt;p&gt;当年 8 月 9 日，网景上市首日收盘，股价从 14 美元的发行价暴增至 75 美元，创下了当时的上市公司首日涨幅记录。当年网景IPO也成为互联网时代到来的一大标志。&lt;/p&gt;&lt;p&gt;1995 年，除了负责网景的上市交易外，米克尔还与同事克里斯o德普开始发布《互联网报告》，并最早提出了“页面浏览量”等网络类股分析指标。这份报告被投资者视为互联网领域的投资圣经，并且成书公开发行，在整个科技行业引发了巨大反响。&lt;/p&gt;&lt;p&gt;1996- 1997 年，米克尔和摩根士丹利发布了《互联网广告报告》与《互联网零售业报告》，一举奠定了米克尔互联网领域第一分析师的地位。互联网女皇报告几乎成为当时每个互联网创业者的必读报告。&lt;/p&gt;&lt;p&gt;互联网女皇报告，无异于选股指南。她向投资者推荐的美国在线、戴尔、亚马逊、eBay等公司股票，都很快带来了超过十倍的投资回报。&lt;/p&gt;&lt;p&gt;互联网女皇报告中的“神预测”&lt;/p&gt;&lt;p&gt;业界如此看重互联网女皇报告的最主要原因，在于米克尔的那些神预测。以下，我们简单罗列了几点互联网女皇报告中的神预测例子。&lt;/p&gt;', '', 1610615823, 1610615823, '');
INSERT INTO `wa_article_content` VALUES (8, 13, '&lt;p&gt;网站建设的五大核心要素&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/uploads/ueditor/20190114/75c3c73acccc98cc5553d39eabf5fb38.jpg&quot; title=&quot;网站建设的五大核心要素(图1)&quot; alt=&quot;网站建设的五大核心要素(图1)&quot;/&gt;&lt;/p&gt;&lt;p&gt;　　企业要实行网络营销，首先需要进行网站制作。网站是由众多的Web页面组成的，而这些页面设计的好坏，直接影响到这个网站能否得到用户的欢迎。判断一个主页设计的好坏，要从多方面综合考虑，不能仅仅看它设计得是否生动漂亮，而应该看这个网站能否最大限度地替用户考虑。&lt;/p&gt;&lt;p&gt;&lt;img title=&quot;网站建设的五大核心要素(图2)&quot; alt=&quot;网站建设的五大核心要素(图2)&quot; class=&quot;limg&quot; src=&quot;http://www.eyoucms.com/uploads/allimg/180426/1510032P3-1.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;　　3、以产品为核心原则&lt;/p&gt;&lt;p&gt;　　网站制作最重要的目的及功能就是为产品展示。顾客访问网站的主要目的是为了对产品和服务进行深入的了解，网站的价值也就在于灵活地向用户展示产品说明及图片甚至多媒体信息，即使一个功能简单的网站至少也相当于一本可以随时更新的产品宣传资料。过时的产品信息或者产品信息不完善不仅无法促进销售，同时也影响顾客的信心。顾客在访问网站时，关心的不是个人的信息，而是能够提供什么样的产品、产品的优势是什么。所以，以产品为核心是网站成功的一首要前提。&lt;/p&gt;&lt;p&gt;　　产品信息一般应该包括以下几方面内容：产品名称产品规格、产品用途、产品特性、产品认证情况及产品图片等。其次，产品规格、产品用途和产品特性等信息应尽可能详细地描述。&lt;/p&gt;&lt;p&gt;　　4、以网站的信息交互能力强为原则&lt;/p&gt;&lt;p&gt;　　如果一个网站只能提供浏览者浏览，而不能引导浏览者参与到网站内容的一部分建设中，那么它的吸引力是有限的。只有当浏览者能够很方便地和信息发布者交流信息时，该网站的魅力才能充分体现出来。虚拟论坛的设计在产品使用者之间、产品使用者与产品开发经理之间展开对产品的各种讨论。在线营销人员还可以借此收集市场信息，制定有效的营销计划。而网站消费者的反馈信息直接在网上公布，能够吸引消费者回访该网站，并由此可形成与顾客的固定关系。&lt;/p&gt;&lt;p&gt;　　当顾客在网上找到感兴趣的产品时，如何针对该产品及时进行询价和反馈?这不仅仅是通过电子函件方式就能够实现的。网站上应该提供相应的信息反馈模块，使顾客能够针对某个或多个产品方便快捷地进行询价或反馈。同时，企业的业务员应该能够及时查到顾客的反馈信息并及时回复：每个业务部门或业务员应该能够针对其发布的产品，方便地管理顾客的信息和反馈信息。通过网站可以为顾客提供各种在线服务和帮助信息，比如常见问题解答(FAQ)、详尽的联系信息、在线填写寻求帮助的表单、通过聊天实时回答顾客的咨询等。同时，利用网站还可以实现增进顾客关系的目的，比如通过发行各种免费邮件列表、提供有奖竞猜等方式吸引用户的参与。通过网站上的在线调查表，可以获得用户的反馈信息，用于产品调查、消费者行为调查、品牌形象调查等，是获得第一手市场资料有效的调查工具。&lt;/p&gt;&lt;p&gt;　　5、以完善的检索能力为原则&lt;/p&gt;&lt;p&gt;　　对于一个网站来说，如何合理地组织自己要发布的信息内容，以便让浏览者能够快速、准确地找到要找的信息，这是一个网站内容组织是否成功的关键。如果网站的结构设计不能使顾客方便、快捷地找到所需的信息，再好的设计也不能吸引长久的客户。即使将他吸引到了网站主页，将来也会中断访问。为了达到上述设计目标，一些网站在网页上设计了信息索引和目录索引。使用者能很快地找到感兴趣的那部分信息。&lt;/p&gt;&lt;p&gt;　　因此，为了网站内容的实用，有一定规模的网站一定要提供检索功能，以便于用户查找本网站的信息。为了给浏览者创造方便条件，网页设计者经常将网页内容设计成树形结构，方便纵向查询。访问者从主页开始就可以层层深入到所有“树权”和“树梢”的信息内容。另外，还可以设计一个搜索系统，让访问者很容易地就找到相关的内容。网址的搜索系统，设计应相当周全，允许访问者从任一页面进入。同时，在网站的任何一个页面都要设计有“返回主页”的链接，以方便访问者回到“树干”。&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '', 1610615818, 1610615818, '');
INSERT INTO `wa_article_content` VALUES (9, 14, '&lt;p&gt;网站建设，静态页面和动态页面如何选择&lt;/p&gt;&lt;p&gt;　　电商网站建设为什么要使用静态页面制作。我们都知道，网站制作有分为静态页面制作和动态网页制作，那么建设电商网站采用哪种网站设计技术更好呢?&lt;/p&gt;&lt;p&gt;　　我们建设网站最终目的是为了给用户浏览，所以从用户的角度出发进行思考才是最实际的，使用动态网页制作技术虽然网页美观度大大提升了，但是却不利于网站优化，今天小编重点和大家谈谈，网站建设为什么要使用静态页面制作。&lt;/p&gt;&lt;p&gt;&lt;img src=&quot;/uploads/ueditor/20190114/47caf8cc457ff50c8a66f4c4a23cfeb1.png&quot; title=&quot;网站建设，静态页面和动态页面如何选择(图1)&quot; alt=&quot;网站建设，静态页面和动态页面如何选择(图1)&quot;/&gt;&lt;/p&gt;&lt;p&gt;　　做静态网站建设所采用的技术原理是一对一的形式，也就是说这样的网站上面，一个内容对应的就是一个页面，无论网站访问者如何操作都只是让服务器把固有的数据传送给请求者，没有脚本计算和后台数据库读取过程，大大降低了部分安全隐患。静态网站设计除了拥有上述的速度快，安全性高这两个特点之外还具有跨平台，跨服务器功能。&lt;/p&gt;&lt;p&gt;　　现在熟悉搜索引擎原理工作原理的朋友应该都知道，它所提供给广大用户的信息是本身就存在于数据库当中的信息而不是实时的信息，固定的信息内容更容易接受和保存。我们可能常常会遇到这样的问题，当我们搜索自己所需要的信息时得出来的结果可能已经失效，这就是静态页面网站设计的不足之处，但又因为它的稳定，所以久久不会被删除。&lt;/p&gt;&lt;p&gt;　　与静态页面网站设计不同，生成的动态页面信息不但不易被搜索引擎所检索，而且打开速度慢，再者也不稳定，这就是为什么这么多专业网站建设公司都一再建议客户使用静态形式的网站设计的原因，有些网站建设公司会考虑把页面进行伪静态处理，但不知道大家有没有注意过，伪静态处理的URL通常是不规则的。&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '', 1610615811, 1610615811, '');
INSERT INTO `wa_article_content` VALUES (10, 19, '&lt;p&gt;从三方面完美的体验企业网站的核心价值&lt;/p&gt;&lt;p&gt;　　随着互联网的迅猛发展，一个企业的发展离不开互联网的发展，企业注重企业网站建设，那么必然会给其带来不错的效果。企业网站建设其核心价值直接体现在网站对于用户和商家而言，是否能够满足他们利益需求，能否提高企业发展，提高企业的发展渠道。&lt;/p&gt;&lt;p&gt;&lt;img title=&quot;从三方面完美的体验企业网站的核心价值(图1)&quot; alt=&quot;从三方面完美的体验企业网站的核心价值(图1)&quot; class=&quot;rimg&quot; src=&quot;http://www.eyoucms.com/uploads/allimg/180426/150RQ155-0.jpg&quot;/&gt;&lt;/p&gt;&lt;p&gt;　　一个好的导航系统就是一个好的导游，认为每一个网站设计方案都有权利与义务帮助客户及时准确的找到自己感兴趣的内容主体和需要的东西。&lt;/p&gt;&lt;p&gt;　　另一方面体现在网站对商家现金利益需求的满足，而此却建立在网站对用户需求满足的基础之上。因为，如果网站不能够满足用户利益的需求，用户就不会为网站创造价值，不能吸引更多的用户参与到网站中来，不能实现网站价值循环式的增长，用户规模将会无法得到较大发展，很难实现对商家现金利益需求的满足，商家在网站投放广告是基于网站促进发生交易可能性的大小，交易可能性越大，商家才可以获得更大的现金利益，否则，将会白白浪费广告费。&lt;/p&gt;&lt;p&gt;　　其次体现在对用户利益需求的满足，网站在发展初期更多的是要为用户提供他们需求的内容，积极的创造内容价值，满足用户各种基础性利益的需求，尤其是各类疑问的解答，相关兴趣或者专业资料的提供，各种资讯信息的发布。让用户能够基于某一种原因留下来，在基础性的工作做好的前提下，您可以着力于用户交易利益需求的满足，或者开始就将交易与用户的相关需求结合起来，打造一个个活跃度高的交易类版块，为用户提供此类交易最全面、最方便的资料和场所，积极促进用户活跃度的提高和迅速实现网站盈利。&lt;/p&gt;&lt;p&gt;　　我们认为让客户在首页即可看到与自己寻找的讯息高度相关的行业信息是非常明智的抉择，一个没有大量行业专业信息体现的网站设计称不上合格的网站设计，也无法真正的为客户从根本上解决问题。&lt;/p&gt;&lt;p&gt;　　我们只有尽量的在网站设计当中体现出如何才能在众多的行业竞争对手中脱颖而出，让客户可以信任我们呢?网站建设公司认为唯有尽量表现出自己的专业实力方可,当然除了这三点之外，网站设计仍旧有很多需要注意的地方，但不管怎么样，核心价值还是应该要重点体现，将重点放在核心内容上才是网站设计的真谛， 我们知道网站运营的核心理念是价值，站长们务必牢牢树立，一切从用户出发，积极满足用户需求，让用户发挥创造力，为网站创造价值，实现网站价值循环式增长，让站长运营变成用户运营是我们的终极目标，一劳永逸，盈利不断。&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '', 1610615806, 1610615806, '');
INSERT INTO `wa_article_content` VALUES (11, 20, '&lt;p&gt;随着网络应用的丰富和发展，很多网站往往不能迅速跟进大量信息衍生及业务模式变革的脚步，常常需要花费许多时间、人力和物力来处理信息更新和维护工作；遇到网站扩充的时候，整合内外网及分支网站的工作就变得更加复杂，甚至还需重新建设网站；如此下去，用户始终在一个高成本、低效率的循环中升级、整合…&lt;/p&gt;&lt;p&gt;于是，我们听到许多用户这样的反馈：&lt;/p&gt;&lt;p&gt;页面制作无序，网站风格不统一，大量信息堆积，发布显得异常沉重；&lt;/p&gt;&lt;p&gt;内容繁杂，手工管理效率低下，手工链接视音频信息经常无法实现；&lt;/p&gt;&lt;p&gt;应用难度较高，许多工作需要技术人员配合才能完成，角色分工不明确；&lt;/p&gt;&lt;p&gt;改版工作量大，系统扩展能力差，集成其它应用时更是降低了灵活性；&lt;/p&gt;&lt;p&gt;对于网站建设和信息发布人员来说，他们最关注的系统的易用性和的功能的完善性，因此，这对网站建设和信息发布工具提出了一个很高的要求。&lt;/p&gt;&lt;p&gt;首先，角色定位明确，以充分保证工作人员的工作效率；其次，功能完整，满足各门道&amp;quot;把关人&amp;quot;应用所需，使信息发布准确无误。比如，为编辑、美工、主编及运维人员设置权限和实时管理功能。&lt;/p&gt;&lt;p&gt;此外，保障网站架构的安全性也是用户关注的焦点。能有效管理网站访问者的登陆权限，使内网数据库不受攻击，从而时刻保证网站的安全稳定，免于用户的后顾之忧。&lt;/p&gt;&lt;p&gt;根据以上需求，一套专业的内容管理系统CMS应运而生，来有效解决用户网站建设与信息发布中常见的问题和需求。对网站内容管理是该软件的最大优势，它流程完善、功能丰富，可把稿件分门别类并授权给合法用户编辑管理，而不需要用户去理会那些难懂的SQL语法。&lt;/p&gt;', '', 1610615802, 1610615802, '');
INSERT INTO `wa_article_content` VALUES (12, 21, '&lt;p&gt;SEO（搜索引擎优化）和有效的网站设计是齐头并进的。好的网站设计是关于创建一个吸引目标受众的网站，并让他们采取某种行动。但是，如果该网站不遵循目前的 SEO 最佳做法，它的排名将会受到影响，从而会导致真正参与该网站的访问者的数量的较少。&lt;/p&gt;&lt;p&gt;相反地，如果将关注的焦点放在搜索引擎优化以及如何取悦搜索引擎蜘蛛上，那么网站可能会排名很高，并且会获得大量的搜索引擎流量，但是如果设计很不尽人意，那就不一样了。为了在当今的数字环境中取得成功，必须将重点放在网站设计和搜索引擎优化上。&lt;/p&gt;&lt;p&gt;一、但是，SEO 不会扼杀掉网页设计师的创造力吗？&lt;/p&gt;&lt;p&gt;在过去的五年中，对“优化设计”的巨大需求已经被网页设计师所接受。在此之前，设计师们主要关注的是用户的体验，而不是“机器人”的体验。&lt;/p&gt;&lt;p&gt;如今，设计师不仅要让网站看起来有吸引力，而且要确保行为召唤必须符合网站页面“折叠”的要求，网站的加载速度必须很快，必须使用面包屑路径，清晰明了的导航选择，必须使用&amp;nbsp;CSS，JavaScript 文件必须保持在最低限度…这是一项艰巨的任务。&lt;/p&gt;&lt;p&gt;一些设计师可能想知道，所有这些新的 SEO 规则是否会损害创建网站的自由？&lt;/p&gt;&lt;p&gt;对于“干净”的网站设计而言，它可以帮助一个网站快速加载，容易被搜索引擎蜘蛛抓取。因此，在现实中，创造力和最优化需要能够同时在一起“蓬勃发展”。&lt;/p&gt;&lt;p&gt;二、把它们结合在一起&lt;/p&gt;&lt;p&gt;有一些核心元素支持每一个 SEO 策略和网站设计项目：&lt;/p&gt;&lt;p&gt;1.　关键词分析&lt;/p&gt;&lt;p&gt;在启动一个商业网站项目时，必须进行彻底的关键词分析。为了做到这一点，网页设计师必须紧密深入地了解客户的目标受众，并定义受众中的人口结构是如何融入到企业正试图达到的更大的目标市场。然后，应该对网站进行适当的关键词/长尾关键词优化。&lt;/p&gt;&lt;p&gt;2.　内容层次结构&lt;/p&gt;&lt;p&gt;对于一个企业来说，创建好的内容是不够的，他们还必须在战略上规划内容的位置。&lt;/p&gt;&lt;p&gt;有效的计划意味着将相关的内容放到虚拟的容器中，通过创造性的设计和内部链接让内容层级结构一目了然。并且，一个经过优化的网站是对用户和搜索引擎蜘蛛都很友好的网站。&lt;/p&gt;&lt;p&gt;3.　从用户的角度思考&lt;/p&gt;&lt;p&gt;通常情况下，你的网站有越多的页面或文章，目标用户找到你的机会就越多。当他们着陆这些特定的页面的时候，你需要确保你能帮助他们轻松的找到你。&lt;/p&gt;&lt;p&gt;所以你必须从用户的角度进行思考，要让用户立即清楚地知道他们在进行访问的页面的当前位置，并帮助用户在尽可能少的点击下从页面转换到另一页面。&lt;/p&gt;&lt;p&gt;三、为什么&amp;nbsp;SEO 策略如此重要？&lt;/p&gt;&lt;p&gt;拥有合适的网站结构和信息架构，最终将会帮助企业提供一种引人入胜的用户体验，同时减少对每一次新增长的需求。但是，除非你的品牌是众所周知的，否则通常是搜索引擎对网站所收到的大部分流量负责。SEO 策略有能力利用重要的客户数据，挖掘新的潜在收入流。&lt;/p&gt;&lt;p&gt;对于那些试图进行搜索引擎优化的网站所有者来说，有一些地方经常是麻烦的。现在，我将为网站所有者提供搜索引擎优化建议，以获得更高排名的页面。&lt;/p&gt;&lt;p&gt;1. &amp;nbsp;URL 结构&lt;/p&gt;&lt;p&gt;大多数网站创建的 URL 都包含很多随机字符，比如问号，没有关键词或任何有价值的内容。当搜索引擎的 URL 包含 SEO 的关键词或短语时，页面将会在搜索引擎中排名更高。因此，在 URL 中设置关键词非常重要。&lt;/p&gt;&lt;p&gt;2.　页面的标题&lt;/p&gt;&lt;p&gt;搜索引擎排名中最重要的因素之一是页面标题。不过，许多网站并没有改变他们的网页标题。在青柠建站平台中，你可以通过使用 SEO 标题标签插件，它很容易让你为你的文章和页面创建标题。&lt;/p&gt;&lt;p&gt;3.　重复的内容&lt;/p&gt;&lt;p&gt;没有一个搜索引擎喜欢看到重复的内容。重复内容是一些网站的主要问题，因为类别页面和日历/日期页面经常会导致搜索引擎在多个页面上找到相同的内容。&lt;/p&gt;&lt;p&gt;对于网站所有者来说，有几种方法可以克服重复的内容问题。其中一种方法是使用 robot.txt 文件，用来指导搜索引擎哪些页面应该被忽略，只留下要索引的主要页面。&lt;/p&gt;&lt;p&gt;4. &amp;nbsp;Meta 标签&lt;/p&gt;&lt;p&gt;在设计一个传统的静态网站时，你可以为每个页面输入元标签（描述）。尽管这些标签在搜索引擎排名上的影响力没有以前那么大，但在你的页面上有这些标签并不会带来什么坏处。&lt;/p&gt;&lt;p&gt;然而，大多数建站平台并没有给用户在写文章时添加元标签的选项。对于 青柠建站平台 用户来说，添加元标签插件将允许你为任何页面输入元标签。&lt;/p&gt;&lt;p&gt;四、网页设计师在 SEO 方面的职责是什么？&lt;/p&gt;&lt;p&gt;搜索引擎优化是一个持续的过程，它不能通过以特定的方式设计一个网站来实现。当然，网页设计师应该付出相当大的努力来帮助客户构建一个优化的站点，但是网页设计师在 SEO 方面的职责是什么，以及客户的职责是什么？&lt;/p&gt;&lt;p&gt;作为一个企业主，你的网站的优化对你来说比设计师更重要（这并不是说设计师不关心，但是设计师的注意力通常集中在网站的视觉和功能上）。你比设计师更了解你的客户 / 潜在客户，所以你应该对你的目标有更多的建设性意见。&lt;/p&gt;&lt;p&gt;也许有些客户对 SEO 和目标关键词可能不太了解，那么理想的情况是让客户和你在这个问题上协同工作。&lt;/p&gt;&lt;p&gt;根据我的经验，让客户参与其中的最简单方法之一就是简单地解释网站上使用的词语和短语（标题、文案等）会对网站排名有直接的影响。&lt;/p&gt;&lt;p&gt;我通常会要求客户给我一份他们认为潜在访问者可能会在搜索中使用的词语和短语列表。在我不太熟悉的行业中设计网站时，这一点尤其重要。&lt;/p&gt;&lt;p&gt;当然，可能需要做一些研究。客户应该承担起关键词研究的责任，还是应该由设计师来负责？&lt;/p&gt;&lt;p&gt;我的经验是，如果客户参与进来，这项研究通常会更有效，但这并不总是可能的。设计师应该有足够的知识来为客户提供建议，并且应该愿意提供帮助，但是最终最好还是让客户尽可能地参与进来。事实上，如果客户关心 SEO，参与过程会达到一个更加合理的期望。&lt;/p&gt;&lt;p&gt;设定现实的期望也可能是设计师的责任。&lt;/p&gt;&lt;p&gt;我有一些潜在的客户来找我说：“我被 SEO 专家告知，只要在网站页面上插入竞争热门的关键词就可以让我的网站排名第一或第二”。&lt;/p&gt;&lt;p&gt;在这种情况下，我会很明显地会指出，“搜索引擎优化需要持续的工作，而这种工作通常不能通过以某种方式创建网站来完成的。”&lt;/p&gt;&lt;p&gt;我经常建议客户在他们的网站上添加一个博客，以获得更多的内容，并提高排名的机会。&lt;/p&gt;&lt;p&gt;结语&lt;/p&gt;&lt;p&gt;虽然这只是一个简短的总结，但这些是网站所有者和设计师将面对的最重要的 SEO 话题。通过了解这些知识，你可以更好地创建出对用户和搜索引擎都友好的网站。&lt;/p&gt;&lt;p&gt;本文由易优小编设计 原创授权发布易优网站，未经授权，转载必究。&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '', 1610615796, 1610615796, '');
INSERT INTO `wa_article_content` VALUES (14, 38, '&lt;p&gt;SEO很多伙伴都了解，就是搜索引擎排名优化，通过对网站内部和外部进行优化当用户搜索相应关键词时网站能够排名在搜索引擎前面，具体可以百度搜索“网络营销课程”查看商梦网校操作的案例！&lt;/p&gt;&lt;p&gt;但单页SEO很多伙伴可能会有点陌生，单页SEO是将单页网站与内容内容结合为一体的SEO优化方案，主要是提升网站流量利用率让用户打开网站就能看到目标页面，转换更多订单，创造更多收益。单页SEO的操作理念也是由商梦网校提出，并一起推荐操作大家的模式。&lt;/p&gt;&lt;p&gt;那什么又是单页SEO站群呢，因为操作SEO成功率并不是100%，也就是意味着你做了并不会绝对有排名。因为在任何时候搜索引擎，特别是百度的索引数据库里，只有60%的网页数量。也就是说，大量的网页它是没有收录进来，它本身的能力所限无法做到中文的所有几百亿个网页都收录进来。所以，对于大部分网站，都有被删除网页，没有排名，或被K的经历，或没有排名。处理办法：坦然面对这一切。一个网站的成本才多少钱？如果因此对SEO失去信心，那就是最大的失去了。&lt;/p&gt;&lt;p&gt;不过我们也想到了一个更好的解决方案，这个方案在最早期我们开始操作，并且取得了非常不错的成绩就是“站群”，我们可以假设一个网站排名的机会为1，如果我们用 10 个网站来进行优化排名机会可以提升 10 倍， 10 个网站我们也不要求都获得排名只需要有1- 3 个网站获得排名这个操作就是成功的，因为对于我们做站群来说投入 10 个网站的成本也就 1000 块左右；这个投资也是非常划算的，这个思路其实有点像竞价，不像传统的SEO，因为传统的SEO我们投资一个网站成本一两百，就想获得排名，然后给我们几百上千倍的回报。结果就相当于我们把希望寄托在一颗树上，结果这颗树没有开花结果，我们就饿死了。&lt;/p&gt;&lt;p&gt;想给一个项目建立数十个网站也是需要掌握很多技术的，特别是批量建站方面，以及后期的维护。这次商梦网校升级加入的单页SEO站群操作方法，没有长篇大论直接给你演示怎么干，你只需要复制我们提供的方法就可以了。当然这里面也有很多核心的技术，比如域名注册和空间购买技巧虽然非常简单，但是直接会影响我们后期操作结果，我们给提供的技巧也会将你的成本降到低，如果投资建立 10 个网站域名与空间的成本不到 1000 元。相当于 1000 你就可以启动一个站群项目。核心的还是文章的采集，我们的原理是利用火车头采集原创文章然后实现挂机自动发布，只需要设置好每天几点运行软件就会自动更新网站文章，还会自动网站自动瞄文本，自动加入关键词。这些很多同学可能会问会不会太复杂，可以这样告诉你复杂的工作我们已经帮你搞定，到你使用的时候已经是打包好的解决方案。&lt;/p&gt;&lt;p&gt;网站前期整体搭建只要花时间就能搞定，但真正考验人的基实还是在于后期优化，对于网站后期优化特别是外链增加收录和权重这一块，我们还是没有长篇大论会直接给你演示实用、高效的方法让你的站群快速的获得收录，增加权重，获得排名，你需要做的就拷贝我们商梦网校的方法和模式；这些经验都是我们长期操作整理下来的，并非几天修炼的结果。&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '', 1610615746, 1610615746, '');
INSERT INTO `wa_article_content` VALUES (31, 72, '', '', 1563502397, 1563502397, '');
INSERT INTO `wa_article_content` VALUES (32, 73, '', '', 1563502433, 1563502433, '');
INSERT INTO `wa_article_content` VALUES (33, 74, '', '', 1563502458, 1563502458, '');
INSERT INTO `wa_article_content` VALUES (34, 75, '', '', 1563502473, 1563502473, '');
INSERT INTO `wa_article_content` VALUES (35, 76, '', '', 1563502499, 1563502499, '');
INSERT INTO `wa_article_content` VALUES (36, 77, '', '', 1563502542, 1563502542, '');
INSERT INTO `wa_article_content` VALUES (37, 78, '', '', 1563502559, 1563502559, '');
INSERT INTO `wa_article_content` VALUES (38, 79, '', '', 1563502578, 1563502578, '');
INSERT INTO `wa_article_content` VALUES (39, 80, '', '', 1563502596, 1563502596, '');
INSERT INTO `wa_article_content` VALUES (40, 81, '', '', 1563502609, 1563502609, '');
INSERT INTO `wa_article_content` VALUES (41, 84, '', '', 1610615703, 1610615703, '');
INSERT INTO `wa_article_content` VALUES (42, 85, '', '', 1610615688, 1610615688, '');
INSERT INTO `wa_article_content` VALUES (43, 86, '', '', 1610615683, 1610615683, '');
INSERT INTO `wa_article_content` VALUES (44, 87, '', '', 1610615678, 1610615678, '');

-- ----------------------------
-- Table structure for wa_channelfield
-- ----------------------------
DROP TABLE IF EXISTS `wa_channelfield`;
CREATE TABLE `wa_channelfield`  (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `name` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '字段名称',
  `channel_id` int(10) NOT NULL DEFAULT 0 COMMENT '所属文档模型id',
  `title` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '字段标题',
  `dtype` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '字段类型',
  `define` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '字段定义',
  `maxlength` int(10) NOT NULL DEFAULT 0 COMMENT '最大长度，文本数据必须填写，大于255为text类型',
  `dfvalue` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '默认值',
  `dfvalue_unit` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '数值单位',
  `remark` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '提示说明',
  `is_screening` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否应用于条件筛选',
  `is_release` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否应用于会员投稿发布',
  `ifeditable` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否在编辑页显示',
  `ifrequire` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否必填',
  `ifsystem` tinyint(1) NOT NULL DEFAULT 0 COMMENT '字段分类，1=系统(不可修改)，0=自定义',
  `ifmain` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否主表字段',
  `ifcontrol` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态，控制该条数据是否允许被控制，1为不允许控制，0为允许控制',
  `sort_order` int(5) NOT NULL DEFAULT 100 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `add_time` int(11) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT 0 COMMENT '更新时间',
  `set_type` tinyint(3) NULL DEFAULT 0 COMMENT '区域选择时使用是否为三级联动,1-是',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 642 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '自定义字段表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_channelfield
-- ----------------------------
INSERT INTO `wa_channelfield` VALUES (1, 'add_time', 0, '新增时间', 'datetime', 'int(11)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533091575, 1533091575, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (2, 'update_time', 0, '更新时间', 'datetime', 'int(11)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533091601, 1533091601, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (3, 'aid', 0, '文档ID', 'int', 'int(11)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533091624, 1533091624, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (4, 'typeid', 0, '当前栏目ID', 'int', 'int(11)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533091930, 1533091930, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (5, 'channel', 0, '模型ID', 'int', 'int(11)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092214, 1533092214, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (6, 'is_b', 0, '是否加粗', 'switch', 'tinyint(1)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092246, 1533092246, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (7, 'title', 0, '文档标题', 'text', 'varchar(250)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092381, 1533092381, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (8, 'litpic', 0, '缩略图', 'img', 'varchar(250)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092398, 1533092398, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (9, 'is_head', 0, '是否头条', 'switch', 'tinyint(1)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092420, 1533092420, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (10, 'is_special', 0, '是否特荐', 'switch', 'tinyint(1)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092439, 1533092439, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (11, 'is_top', 0, '是否置顶', 'switch', 'tinyint(1)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092454, 1533092454, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (12, 'is_recom', 0, '是否推荐', 'switch', 'tinyint(1)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092468, 1533092468, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (13, 'is_jump', 0, '是否跳转', 'switch', 'tinyint(1)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092484, 1533092484, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (14, 'author', 0, '编辑者', 'text', 'varchar(250)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092498, 1533092498, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (15, 'click', 0, '浏览量', 'int', 'int(11)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092512, 1533092512, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (16, 'arcrank', 0, '阅读权限', 'select', 'tinyint(1)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092534, 1533092534, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (17, 'jumplinks', 0, '跳转链接', 'text', 'varchar(250)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092553, 1533092553, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (18, 'ismake', 0, '是否静态页面', 'switch', 'tinyint(1)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092698, 1533092698, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (19, 'seo_title', 0, 'SEO标题', 'text', 'varchar(250)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092713, 1533092713, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (20, 'seo_keywords', 0, 'SEO关键词', 'text', 'varchar(250)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092725, 1533092725, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (21, 'seo_description', 0, 'SEO描述', 'text', 'varchar(250)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092739, 1533092739, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (22, 'status', 0, '状态', 'switch', 'tinyint(1)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092753, 1533092753, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (23, 'sort_order', 0, '排序号', 'int', 'int(11)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092766, 1533092766, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (24, 'content', 2, '内容详情', 'htmltext', 'longtext', 250, '', '', '', 0, 1, 1, 0, 1, 0, 0, 100, 1, 1533359739, 1767431759, 0, NULL, '2026-01-03 17:15:59');
INSERT INTO `wa_channelfield` VALUES (25, 'content', 3, '内容详情', 'htmltext', 'longtext', 250, '', '', '', 0, 1, 1, 0, 1, 0, 0, 100, 1, 1533359588, 1533359588, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (26, 'content', 4, '内容详情', 'htmltext', 'longtext', 250, '', '', '', 0, 1, 1, 0, 1, 0, 0, 100, 1, 1533359752, 1533359752, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (27, 'content', 6, '内容详情', 'htmltext', 'longtext', 250, '', '', '', 0, 1, 1, 0, 1, 0, 0, 100, 1, 1533464715, 1767256216, 0, NULL, '2026-01-01 16:30:16');
INSERT INTO `wa_channelfield` VALUES (29, 'content', 1, '内容详情', 'htmltext', 'longtext', 250, '', '', '', 0, 1, 1, 0, 1, 0, 0, 100, 1, 1533464713, 1767256165, 0, NULL, '2026-01-01 16:29:25');
INSERT INTO `wa_channelfield` VALUES (30, 'update_time', -99, '更新时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (31, 'add_time', -99, '新增时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (32, 'status', -99, '启用 (1=正常，0=屏蔽)', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (33, 'is_part', -99, '栏目属性：0=内容栏目，1=外部链接', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (34, 'is_hidden', -99, '是否隐藏栏目：0=显示，1=隐藏', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (35, 'sort_order', -99, '排序号', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (36, 'seo_description', -99, 'seo描述', 'multitext', 'text', 0, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (37, 'seo_keywords', -99, 'seo关键字', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (38, 'seo_title', -99, 'SEO标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (39, 'tempview', -99, '文档模板文件名', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (40, 'templist', -99, '列表模板文件名', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (41, 'litpic', -99, '栏目图片', 'img', 'varchar(250)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (42, 'typelink', -99, '栏目链接', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (43, 'grade', -99, '栏目等级', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (44, 'englist_name', -99, '栏目英文名', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (45, 'dirpath', -99, '目录存放HTML路径', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (46, 'dirname', -99, '目录英文名', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (47, 'typename', -99, '栏目名称', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (48, 'parent_id', -99, '栏目上级ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (49, 'current_channel', -99, '栏目当前模型ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (50, 'channeltype', -99, '栏目顶级模型ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (51, 'id', -99, '栏目ID', 'int', 'int(10)', 10, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (52, 'del_method', -99, '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1547890773, 1547890773, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (53, 'is_del', 0, '是否伪删除', 'switch', 'tinyint(1)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1547890773, 1547890773, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (54, 'del_method', 0, '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1547890773, 1547890773, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (55, 'prom_type', 0, '产品类型：0普通产品，1虚拟产品', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (56, 'users_price', 0, '价格', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 0, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (57, 'prom_type', 2, '产品类型：0普通产品，1虚拟产品', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (58, 'users_price', 2, '价格', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 0, 100, 1, 1557042574, 1767431760, 0, NULL, '2026-01-03 17:16:00');
INSERT INTO `wa_channelfield` VALUES (59, 'update_time', 2, '更新时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (60, 'add_time', 2, '新增时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (61, 'del_method', 2, '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (62, 'is_del', 2, '伪删除，1=是，0=否', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (63, 'admin_id', 2, '管理员ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (64, 'lang', 2, '语言标识', 'text', 'varchar(50)', 50, 'cn', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (65, 'sort_order', 2, '排序号', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (66, 'status', 2, '状态(0=屏蔽，1=正常)', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (67, 'tempview', 2, '文档模板文件名', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (68, 'seo_description', 2, 'SEO描述', 'multitext', 'text', 0, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (69, 'seo_keywords', 2, 'SEO关键词', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (70, 'seo_title', 2, 'SEO标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (71, 'ismake', 2, '是否静态页面（0=动态，1=静态）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (72, 'jumplinks', 2, '外链跳转', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (73, 'arcrank', 2, '阅读权限：0=开放浏览，-1=待审核稿件', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (74, 'click', 2, '浏览量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (75, 'author', 2, '作者', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (76, 'is_litpic', 2, '图片（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (77, 'is_jump', 2, '跳转链接（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (78, 'is_recom', 2, '推荐（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (79, 'is_top', 2, '置顶（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (80, 'is_special', 2, '特荐（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (81, 'is_head', 2, '头条（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (82, 'litpic', 2, '缩略图', 'img', 'varchar(250)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (83, 'title', 2, '标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (84, 'is_b', 2, '加粗', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (85, 'channel', 2, '模型ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (86, 'typeid', 2, '当前栏目', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (87, 'aid', 2, 'aid', 'int', 'int(10)', 10, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563518642, 1563518642, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (88, 'fxrq', 2, '发行日期', 'radio', 'enum(\'2019年\',\'2018年\',\'2017年\')', 0, '2019年,2018年,2017年', '', '', 1, 0, 1, 0, 0, 0, 0, 100, 1, 1563518738, 1767431681, 0, NULL, '2026-01-03 17:14:41');
INSERT INTO `wa_channelfield` VALUES (89, 'update_time', 9, '更新时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (90, 'add_time', 9, '新增时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (91, 'del_method', 9, '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (92, 'is_del', 9, '伪删除，1=是，0=否', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (93, 'admin_id', 9, '管理员ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (94, 'lang', 9, '语言标识', 'text', 'varchar(50)', 50, 'cn', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (95, 'sort_order', 9, '排序号', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (96, 'status', 9, '状态(0=屏蔽，1=正常)', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (97, 'tempview', 9, '文档模板文件名', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (98, 'prom_type', 9, '产品类型：0普通产品，1虚拟产品', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (99, 'users_price', 9, '价格', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (100, 'seo_description', 9, 'SEO描述', 'multitext', 'text', 0, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (101, 'seo_keywords', 9, 'SEO关键词', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (102, 'seo_title', 9, 'SEO标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (103, 'ismake', 9, '是否静态页面（0=动态，1=静态）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (104, 'jumplinks', 9, '外链跳转', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (105, 'arcrank', 9, '阅读权限：0=开放浏览，-1=待审核稿件', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (106, 'click', 9, '浏览量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (107, 'author', 9, '作者', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 0, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (108, 'is_litpic', 9, '图片（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (109, 'is_jump', 9, '跳转链接（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (110, 'is_recom', 9, '推荐（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (111, 'is_top', 9, '置顶（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (112, 'is_special', 9, '特荐（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (113, 'is_head', 9, '头条（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (114, 'litpic', 9, '缩略图', 'img', 'varchar(250)', 250, '', '', '', 0, 0, 0, 0, 1, 1, 0, 100, 1, 1563526560, 1563526567, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (115, 'title', 9, '标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (116, 'is_b', 9, '加粗', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (117, 'channel', 9, '模型ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (118, 'typeid', 9, '当前栏目', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (119, 'aid', 9, 'aid', 'int', 'int(10)', 10, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1563526560, 1563526560, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (120, 'gzdd', 9, '工作地点', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 0, 0, 0, 1, 1, 1563526665, 1563528016, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (121, 'xlyq', 9, '学历要求', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 0, 0, 0, 2, 1, 1563526681, 1563528016, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (122, 'xzdy', 9, '薪资待遇', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 0, 0, 0, 3, 1, 1563526694, 1563528018, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (123, 'gzxz', 9, '工作性质', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 0, 0, 0, 4, 1, 1563526705, 1563528018, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (124, 'gznx', 9, '工作年限', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 0, 0, 0, 5, 1, 1563526716, 1563528019, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (125, 'zprs', 9, '招聘人数', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 0, 0, 0, 6, 1, 1563526727, 1563528021, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (126, 'nnxq', 9, '内容详情', 'htmltext', 'longtext', 0, '', '', '', 0, 0, 1, 0, 0, 0, 0, 7, 1, 1563526769, 1563528023, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (127, 'users_id', 0, '会员ID', 'int', 'int(11)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (128, 'arc_level_id', 0, '文档会员权限ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (129, 'arc_level_id', 4, '文档会员权限ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (130, 'arc_level_id', 2, '文档会员权限ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1565662106, 1565662106, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (131, 'users_id', 2, '会员ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1565662106, 1565662106, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (132, 'jiawei', 2, '价位区段', 'radio', 'enum(\'0-1000\',\'1000-1699\',\'1700-2799\',\'2800-3500\',\'3500-10000\')', 0, '0-1000,1000-1699,1700-2799,2800-3500,3500-10000', '', '', 1, 0, 1, 0, 0, 0, 0, 100, 1, 1565662216, 1767431700, 0, NULL, '2026-01-03 17:15:00');
INSERT INTO `wa_channelfield` VALUES (133, 'yanse', 2, '机身颜色', 'radio', 'enum(\'银色\',\'绿色\',\'黑色\',\'灰色\')', 0, '银色,绿色,黑色,灰色', '', '', 1, 0, 1, 0, 0, 0, 0, 100, 1, 1565662279, 1767431700, 0, NULL, '2026-01-03 17:15:00');
INSERT INTO `wa_channelfield` VALUES (136, 'weapp_code', -99, '插件栏目唯一标识', 'text', 'varchar(200)', 200, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (137, 'is_release', -99, '栏目是否应用于会员投稿发布，1是，0否', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (138, 'old_price', 0, '产品旧价', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (139, 'stock_count', 0, '商品库存量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (140, 'stock_show', 0, '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (141, 'joinaid', 0, '关联文档ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (142, 'downcount', 0, '下载次数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (143, 'downcount', 4, '下载次数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (144, 'update_time', 1, '更新时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (145, 'add_time', 1, '新增时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (146, 'downcount', 1, '下载次数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (147, 'joinaid', 1, '关联文档ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (148, 'del_method', 1, '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (149, 'is_del', 1, '伪删除，1=是，0=否', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (150, 'arc_level_id', 1, '文档会员权限ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (151, 'users_id', 1, '会员ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (152, 'admin_id', 1, '管理员ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (153, 'lang', 1, '语言标识', 'text', 'varchar(50)', 50, 'cn', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (154, 'sort_order', 1, '排序号', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (155, 'status', 1, '状态(0=屏蔽，1=正常)', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (156, 'tempview', 1, '文档模板文件名', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (157, 'prom_type', 1, '产品类型：0普通产品，1虚拟产品', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (158, 'stock_show', 1, '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1) unsigned', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (159, 'stock_count', 1, '商品库存量', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (160, 'old_price', 1, '产品旧价', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (161, 'users_price', 1, '会员价', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (162, 'seo_description', 1, 'SEO描述', 'multitext', 'text', 0, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (163, 'seo_keywords', 1, 'SEO关键词', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (164, 'seo_title', 1, 'SEO标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (165, 'ismake', 1, '是否静态页面（0=动态，1=静态）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (166, 'jumplinks', 1, '外链跳转', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (167, 'arcrank', 1, '阅读权限：0=开放浏览，-1=待审核稿件', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (168, 'click', 1, '浏览量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (169, 'author', 1, '作者', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (170, 'is_litpic', 1, '图片（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (171, 'is_jump', 1, '跳转链接（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (172, 'is_recom', 1, '推荐（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (173, 'is_top', 1, '置顶（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (174, 'is_special', 1, '特荐（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (175, 'is_head', 1, '头条（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (176, 'litpic', 1, '缩略图', 'img', 'varchar(250)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (177, 'title', 1, '标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (178, 'is_b', 1, '加粗', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (179, 'channel', 1, '模型ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (180, 'typeid', 1, '当前栏目', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (181, 'aid', 1, 'aid', 'int', 'int(10)', 10, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233787, 1574233787, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (182, 'downcount', 2, '下载次数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233793, 1574233793, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (183, 'joinaid', 2, '关联文档ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233793, 1574233793, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (184, 'stock_show', 2, '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1) unsigned', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233793, 1574233793, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (185, 'stock_count', 2, '商品库存量', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233793, 1574233793, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (186, 'old_price', 2, '产品旧价', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233793, 1574233793, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (187, 'update_time', 3, '更新时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (188, 'add_time', 3, '新增时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (189, 'downcount', 3, '下载次数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (190, 'joinaid', 3, '关联文档ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (191, 'del_method', 3, '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (192, 'is_del', 3, '伪删除，1=是，0=否', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (193, 'arc_level_id', 3, '文档会员权限ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (194, 'users_id', 3, '会员ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (195, 'admin_id', 3, '管理员ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (196, 'lang', 3, '语言标识', 'text', 'varchar(50)', 50, 'cn', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (197, 'sort_order', 3, '排序号', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (198, 'status', 3, '状态(0=屏蔽，1=正常)', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (199, 'tempview', 3, '文档模板文件名', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (200, 'prom_type', 3, '产品类型：0普通产品，1虚拟产品', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (201, 'stock_show', 3, '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1) unsigned', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (202, 'stock_count', 3, '商品库存量', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (203, 'old_price', 3, '产品旧价', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (204, 'users_price', 3, '会员价', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (205, 'seo_description', 3, 'SEO描述', 'multitext', 'text', 0, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (206, 'seo_keywords', 3, 'SEO关键词', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (207, 'seo_title', 3, 'SEO标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (208, 'ismake', 3, '是否静态页面（0=动态，1=静态）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (209, 'jumplinks', 3, '外链跳转', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (210, 'arcrank', 3, '阅读权限：0=开放浏览，-1=待审核稿件', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (211, 'click', 3, '浏览量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (212, 'author', 3, '作者', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (213, 'is_litpic', 3, '图片（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (214, 'is_jump', 3, '跳转链接（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (215, 'is_recom', 3, '推荐（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (216, 'is_top', 3, '置顶（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (217, 'is_special', 3, '特荐（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (218, 'is_head', 3, '头条（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (219, 'litpic', 3, '缩略图', 'img', 'varchar(250)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (220, 'title', 3, '标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (221, 'is_b', 3, '加粗', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (222, 'channel', 3, '模型ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (223, 'typeid', 3, '当前栏目', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (224, 'aid', 3, 'aid', 'int', 'int(10)', 10, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (225, 'update_time', 4, '更新时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (226, 'add_time', 4, '新增时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (227, 'joinaid', 4, '关联文档ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (228, 'del_method', 4, '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (229, 'is_del', 4, '伪删除，1=是，0=否', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (230, 'users_id', 4, '会员ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (231, 'admin_id', 4, '管理员ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (232, 'lang', 4, '语言标识', 'text', 'varchar(50)', 50, 'cn', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (233, 'sort_order', 4, '排序号', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (234, 'status', 4, '状态(0=屏蔽，1=正常)', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (235, 'tempview', 4, '文档模板文件名', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (236, 'prom_type', 4, '产品类型：0普通产品，1虚拟产品', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (237, 'stock_show', 4, '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1) unsigned', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (238, 'stock_count', 4, '商品库存量', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (239, 'old_price', 4, '产品旧价', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (240, 'users_price', 4, '会员价', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (241, 'seo_description', 4, 'SEO描述', 'multitext', 'text', 0, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (242, 'seo_keywords', 4, 'SEO关键词', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (243, 'seo_title', 4, 'SEO标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (244, 'ismake', 4, '是否静态页面（0=动态，1=静态）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (245, 'jumplinks', 4, '外链跳转', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (246, 'arcrank', 4, '阅读权限：0=开放浏览，-1=待审核稿件', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (247, 'click', 4, '浏览量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (248, 'author', 4, '作者', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (249, 'is_litpic', 4, '图片（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (250, 'is_jump', 4, '跳转链接（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (251, 'is_recom', 4, '推荐（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (252, 'is_top', 4, '置顶（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (253, 'is_special', 4, '特荐（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (254, 'is_head', 4, '头条（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (255, 'litpic', 4, '缩略图', 'img', 'varchar(250)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (256, 'title', 4, '标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (257, 'is_b', 4, '加粗', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (258, 'channel', 4, '模型ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (259, 'typeid', 4, '当前栏目', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (260, 'aid', 4, 'aid', 'int', 'int(10)', 10, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233799, 1574233799, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (261, 'update_time', 6, '更新时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (262, 'add_time', 6, '新增时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (263, 'downcount', 6, '下载次数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (264, 'joinaid', 6, '关联文档ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (265, 'del_method', 6, '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (266, 'is_del', 6, '伪删除，1=是，0=否', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (267, 'arc_level_id', 6, '文档会员权限ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (268, 'users_id', 6, '会员ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (269, 'admin_id', 6, '管理员ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (270, 'lang', 6, '语言标识', 'text', 'varchar(50)', 50, 'cn', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (271, 'sort_order', 6, '排序号', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (272, 'status', 6, '状态(0=屏蔽，1=正常)', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (273, 'tempview', 6, '文档模板文件名', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (274, 'prom_type', 6, '产品类型：0普通产品，1虚拟产品', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (275, 'stock_show', 6, '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1) unsigned', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (276, 'stock_count', 6, '商品库存量', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (277, 'old_price', 6, '产品旧价', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (278, 'users_price', 6, '会员价', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (279, 'seo_description', 6, 'SEO描述', 'multitext', 'text', 0, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (280, 'seo_keywords', 6, 'SEO关键词', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (281, 'seo_title', 6, 'SEO标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (282, 'ismake', 6, '是否静态页面（0=动态，1=静态）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (283, 'jumplinks', 6, '外链跳转', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (284, 'arcrank', 6, '阅读权限：0=开放浏览，-1=待审核稿件', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (285, 'click', 6, '浏览量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (286, 'author', 6, '作者', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (287, 'is_litpic', 6, '图片（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (288, 'is_jump', 6, '跳转链接（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (289, 'is_recom', 6, '推荐（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (290, 'is_top', 6, '置顶（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (291, 'is_special', 6, '特荐（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (292, 'is_head', 6, '头条（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (293, 'litpic', 6, '缩略图', 'img', 'varchar(250)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (294, 'title', 6, '标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (295, 'is_b', 6, '加粗', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (296, 'channel', 6, '模型ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (297, 'typeid', 6, '当前栏目', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (298, 'aid', 6, 'aid', 'int', 'int(10)', 10, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233802, 1574233802, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (299, 'downcount', 9, '下载次数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233808, 1574233808, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (300, 'joinaid', 9, '关联文档ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233808, 1574233808, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (301, 'arc_level_id', 9, '文档会员权限ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233808, 1574233808, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (302, 'users_id', 9, '会员ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233808, 1574233808, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (303, 'stock_show', 9, '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1) unsigned', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233808, 1574233808, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (304, 'stock_count', 9, '商品库存量', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233808, 1574233808, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (305, 'old_price', 9, '产品旧价', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233808, 1574233808, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (306, 'htmlfilename', 0, '自定义文件名', 'text', 'varchar(50)', 50, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (307, 'htmlfilename', 1, '自定义文件名', 'text', 'varchar(50)', 50, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (308, 'htmlfilename', 2, '自定义文件名', 'text', 'varchar(50)', 50, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (309, 'htmlfilename', 3, '自定义文件名', 'text', 'varchar(50)', 50, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (310, 'htmlfilename', 4, '自定义文件名', 'text', 'varchar(50)', 50, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (311, 'htmlfilename', 6, '自定义文件名', 'text', 'varchar(50)', 50, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (312, 'attrlist_id', 0, '参数列表ID', 'int', 'int(10) unsigned', 10, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533091930, 1533091930, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (313, 'sales_num', 0, '销售量', 'int', 'int(10) unsigned', 10, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533091930, 1533091930, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (314, 'update_time', 5, '更新时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (315, 'add_time', 5, '新增时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (316, 'htmlfilename', 5, '自定义文件名', 'text', 'varchar(50)', 50, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (317, 'downcount', 5, '下载次数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (318, 'joinaid', 5, '关联文档ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (319, 'del_method', 5, '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (320, 'is_del', 5, '伪删除，1=是，0=否', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (321, 'arc_level_id', 5, '文档会员权限ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (322, 'users_id', 5, '会员ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (323, 'admin_id', 5, '管理员ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (324, 'lang', 5, '语言标识', 'text', 'varchar(50)', 50, 'cn', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (325, 'sort_order', 5, '排序号', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (326, 'status', 5, '状态(0=屏蔽，1=正常)', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (327, 'tempview', 5, '文档模板文件名', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (328, 'prom_type', 5, '产品类型：0=普通产品，1=虚拟(默认手动发货)，2=虚拟(网盘', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (329, 'stock_show', 5, '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1) unsigned', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (330, 'stock_count', 5, '商品库存量', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (331, 'sales_num', 5, '销售量', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (332, 'old_price', 5, '产品旧价', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (333, 'users_price', 5, '会员价', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (334, 'attrlist_id', 5, '参数列表ID', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (335, 'seo_description', 5, 'SEO描述', 'multitext', 'text', 0, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (336, 'seo_keywords', 5, 'SEO关键词', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (337, 'seo_title', 5, 'SEO标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (338, 'ismake', 5, '是否静态页面（0=动态，1=静态）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (339, 'jumplinks', 5, '外链跳转', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (340, 'arcrank', 5, '阅读权限：0=开放浏览，-1=待审核稿件', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (341, 'click', 5, '浏览量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (342, 'author', 5, '作者', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (343, 'is_litpic', 5, '图片（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (344, 'is_jump', 5, '跳转链接（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (345, 'is_recom', 5, '推荐（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (346, 'is_top', 5, '置顶（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (347, 'is_special', 5, '特荐（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (348, 'is_head', 5, '头条（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (349, 'litpic', 5, '缩略图', 'img', 'varchar(250)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (350, 'title', 5, '标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (351, 'is_b', 5, '加粗', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (352, 'channel', 5, '模型ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (353, 'typeid', 5, '当前栏目', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (354, 'aid', 5, 'aid', 'int', 'int(10)', 10, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (355, 'content', 5, '内容详情', 'htmltext', 'longtext', 0, '', '', '', 0, 1, 1, 0, 1, 0, 0, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (356, 'courseware', 5, '课件地址', 'text', 'varchar(200)', 200, '', '', '', 0, 1, 0, 0, 1, 0, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (357, 'courseware_free', 5, '课件收费', 'select', 'enum(\'免费\',\'收费\')', 0, '免费,收费', '', '', 0, 1, 0, 0, 1, 0, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (358, 'total_duration', 5, '视频总时长', 'int', 'int(10)', 10, '0', '', '', 0, 1, 0, 0, 1, 0, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (359, 'total_video', 5, '视频数', 'int', 'int(10)', 10, '0', '', '', 0, 1, 0, 0, 1, 0, 1, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (361, 'update_time', 7, '更新时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (362, 'add_time', 7, '新增时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (363, 'htmlfilename', 7, '自定义文件名', 'text', 'varchar(50)', 50, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (364, 'downcount', 7, '下载次数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (365, 'joinaid', 7, '关联文档ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (366, 'del_method', 7, '伪删除状态，1为主动删除，2为跟随上级栏目被动删除', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (367, 'is_del', 7, '伪删除，1=是，0=否', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (368, 'arc_level_id', 7, '文档会员权限ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (369, 'users_id', 7, '会员ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (370, 'admin_id', 7, '管理员ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (371, 'lang', 7, '语言标识', 'text', 'varchar(50)', 50, 'cn', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (372, 'sort_order', 7, '排序号', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (373, 'status', 7, '状态(0=屏蔽，1=正常)', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (374, 'tempview', 7, '文档模板文件名', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (375, 'prom_type', 7, '产品类型：0=普通产品，1=虚拟(默认手动发货)，2=虚拟(网盘', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (376, 'stock_show', 7, '商品库存在产品详情页是否显示，1为显示，0为不显示', 'switch', 'tinyint(1) unsigned', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (377, 'stock_count', 7, '商品库存量', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (378, 'sales_num', 7, '销售量', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (379, 'old_price', 7, '产品旧价', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (380, 'users_free', 7, '是否会员免费，默认0不免费，1为免费', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (381, 'users_price', 7, '会员价', 'decimal', 'decimal(10,2)', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (382, 'attrlist_id', 7, '参数列表ID', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (383, 'seo_description', 7, 'SEO描述', 'multitext', 'text', 0, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (384, 'seo_keywords', 7, 'SEO关键词', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (385, 'seo_title', 7, 'SEO标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (386, 'ismake', 7, '是否静态页面（0=动态，1=静态）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (387, 'jumplinks', 7, '外链跳转', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (388, 'arcrank', 7, '阅读权限：0=开放浏览，-1=待审核稿件', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (389, 'click', 7, '浏览量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (390, 'author', 7, '作者', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (391, 'is_litpic', 7, '图片（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (392, 'is_jump', 7, '跳转链接（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (393, 'is_recom', 7, '推荐（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (394, 'is_top', 7, '置顶（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (395, 'is_special', 7, '特荐（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (396, 'is_head', 7, '头条（0=否，1=是）', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (397, 'litpic', 7, '缩略图', 'img', 'varchar(250)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (398, 'title', 7, '标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (399, 'is_b', 7, '加粗', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (400, 'channel', 7, '模型ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (401, 'typeid', 7, '当前栏目', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (402, 'aid', 7, 'aid', 'int', 'int(10)', 10, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (403, 'content', 7, '内容详情', 'htmltext', 'longtext', 0, '', '', '', 0, 1, 1, 0, 1, 0, 0, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (404, 'topid', -99, '顶级栏目ID', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1557042574, 1557042574, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (405, 'is_slide', 0, '是否幻灯', 'switch', 'tinyint(1)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092420, 1533092420, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (406, 'is_roll', 0, '是否幻灯', 'switch', 'tinyint(1)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092420, 1533092420, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (407, 'is_diyattr', 0, '是否自定义', 'switch', 'tinyint(1)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533092420, 1533092420, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (408, 'restric_type', 0, '限制模式，0=免费，1=付费，2=会员专享，3=会员付费', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1616293251, 1616293251, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (409, 'diy_dirpath', -99, '自定义HTML保存路径', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (410, 'rulelist', -99, '列表静态文件存放规则', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (411, 'ruleview', -99, '文档静态文件存放规则', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (412, 'subtitle', 0, '副标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1636338535, 1636338535, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (413, 'origin', 0, '来源', 'text', 'varchar(30)', 30, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1636338535, 1636338535, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (414, 'stypeid', 0, '副栏目', 'text', 'varchar(90)', 90, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1636338535, 1636338535, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (415, 'area_id', 1, '所在区域', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949813, 1641949813, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (416, 'city_id', 1, '所在城市', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949813, 1641949813, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (417, 'province_id', 1, '省份', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949813, 1641949813, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (418, 'collection', 1, '收藏数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949813, 1641949813, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (419, 'appraise', 1, '评价数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949813, 1641949813, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (420, 'restric_type', 1, '限制模式，0=免费，1=付费，2=会员专享，3=会员付费', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949813, 1641949813, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (421, 'sales_num', 1, '销售量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949813, 1641949813, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (422, 'users_free', 1, '是否会员免费，默认0不免费，1为免费', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949813, 1641949813, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (423, 'attrlist_id', 1, '参数列表ID', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949813, 1641949813, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (424, 'origin', 1, '来源', 'text', 'varchar(30)', 30, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949813, 1641949813, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (425, 'is_diyattr', 1, '自定义（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949813, 1641949813, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (426, 'is_slide', 1, '幻灯（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949813, 1641949813, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (427, 'is_roll', 1, '滚动（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949813, 1641949813, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (428, 'subtitle', 1, '副标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949813, 1641949813, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (429, 'stypeid', 1, '副栏目ID集合', 'text', 'varchar(90)', 90, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949813, 1641949813, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (430, 'area_id', 2, '所在区域', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949815, 1641949815, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (431, 'city_id', 2, '所在城市', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949815, 1641949815, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (432, 'province_id', 2, '省份', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949815, 1641949815, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (433, 'collection', 2, '收藏数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949815, 1641949815, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (434, 'appraise', 2, '评价数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949815, 1641949815, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (435, 'restric_type', 2, '限制模式，0=免费，1=付费，2=会员专享，3=会员付费', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949815, 1641949815, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (436, 'sales_num', 2, '销售量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949815, 1641949815, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (437, 'users_free', 2, '是否会员免费，默认0不免费，1为免费', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949815, 1641949815, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (438, 'attrlist_id', 2, '参数列表ID', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949815, 1641949815, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (439, 'origin', 2, '来源', 'text', 'varchar(30)', 30, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949815, 1641949815, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (440, 'is_diyattr', 2, '自定义（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949815, 1641949815, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (441, 'is_slide', 2, '幻灯（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949815, 1641949815, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (442, 'is_roll', 2, '滚动（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949815, 1641949815, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (443, 'subtitle', 2, '副标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949815, 1641949815, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (444, 'stypeid', 2, '副栏目ID集合', 'text', 'varchar(90)', 90, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949815, 1641949815, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (445, 'area_id', 3, '所在区域', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949817, 1641949817, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (446, 'city_id', 3, '所在城市', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949817, 1641949817, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (447, 'province_id', 3, '省份', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949817, 1641949817, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (448, 'collection', 3, '收藏数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949817, 1641949817, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (449, 'appraise', 3, '评价数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949817, 1641949817, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (450, 'restric_type', 3, '限制模式，0=免费，1=付费，2=会员专享，3=会员付费', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949817, 1641949817, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (451, 'sales_num', 3, '销售量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949817, 1641949817, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (452, 'users_free', 3, '是否会员免费，默认0不免费，1为免费', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949817, 1641949817, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (453, 'attrlist_id', 3, '参数列表ID', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949817, 1641949817, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (454, 'origin', 3, '来源', 'text', 'varchar(30)', 30, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949817, 1641949817, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (455, 'is_diyattr', 3, '自定义（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949817, 1641949817, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (456, 'is_slide', 3, '幻灯（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949817, 1641949817, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (457, 'is_roll', 3, '滚动（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949817, 1641949817, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (458, 'subtitle', 3, '副标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949817, 1641949817, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (459, 'stypeid', 3, '副栏目ID集合', 'text', 'varchar(90)', 90, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949817, 1641949817, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (460, 'area_id', 4, '所在区域', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949819, 1641949819, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (461, 'city_id', 4, '所在城市', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949819, 1641949819, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (462, 'province_id', 4, '省份', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949819, 1641949819, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (463, 'collection', 4, '收藏数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949819, 1641949819, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (464, 'appraise', 4, '评价数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949819, 1641949819, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (465, 'restric_type', 4, '限制模式，0=免费，1=付费，2=会员专享，3=会员付费', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949819, 1641949819, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (466, 'sales_num', 4, '销售量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949819, 1641949819, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (467, 'users_free', 4, '是否会员免费，默认0不免费，1为免费', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949819, 1641949819, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (468, 'attrlist_id', 4, '参数列表ID', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949819, 1641949819, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (469, 'origin', 4, '来源', 'text', 'varchar(30)', 30, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949819, 1641949819, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (470, 'is_diyattr', 4, '自定义（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949819, 1641949819, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (471, 'is_slide', 4, '幻灯（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949819, 1641949819, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (472, 'is_roll', 4, '滚动（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949819, 1641949819, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (473, 'subtitle', 4, '副标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949819, 1641949819, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (474, 'stypeid', 4, '副栏目ID集合', 'text', 'varchar(90)', 90, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949819, 1641949819, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (475, 'area_id', 6, '所在区域', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949820, 1641949820, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (476, 'city_id', 6, '所在城市', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949820, 1641949820, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (477, 'province_id', 6, '省份', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949820, 1641949820, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (478, 'collection', 6, '收藏数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949820, 1641949820, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (479, 'appraise', 6, '评价数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949820, 1641949820, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (480, 'restric_type', 6, '限制模式，0=免费，1=付费，2=会员专享，3=会员付费', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949820, 1641949820, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (481, 'sales_num', 6, '销售量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949820, 1641949820, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (482, 'users_free', 6, '是否会员免费，默认0不免费，1为免费', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949820, 1641949820, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (483, 'attrlist_id', 6, '参数列表ID', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949820, 1641949820, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (484, 'origin', 6, '来源', 'text', 'varchar(30)', 30, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949820, 1641949820, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (485, 'is_diyattr', 6, '自定义（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949820, 1641949820, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (486, 'is_slide', 6, '幻灯（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949820, 1641949820, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (487, 'is_roll', 6, '滚动（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949820, 1641949820, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (488, 'subtitle', 6, '副标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949820, 1641949820, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (489, 'stypeid', 6, '副栏目ID集合', 'text', 'varchar(90)', 90, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949820, 1641949820, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (490, 'area_id', 7, '所在区域', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949822, 1641949822, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (491, 'city_id', 7, '所在城市', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949822, 1641949822, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (492, 'province_id', 7, '省份', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949822, 1641949822, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (493, 'collection', 7, '收藏数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949822, 1641949822, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (494, 'appraise', 7, '评价数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949822, 1641949822, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (495, 'restric_type', 7, '限制模式，0=免费，1=付费，2=会员专享，3=会员付费', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949822, 1641949822, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (496, 'origin', 7, '来源', 'text', 'varchar(30)', 30, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949822, 1641949822, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (497, 'is_diyattr', 7, '自定义（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949822, 1641949822, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (498, 'is_slide', 7, '幻灯（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949822, 1641949822, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (499, 'is_roll', 7, '滚动（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949822, 1641949822, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (500, 'subtitle', 7, '副标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949822, 1641949822, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (501, 'stypeid', 7, '副栏目ID集合', 'text', 'varchar(90)', 90, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949822, 1641949822, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (502, 'area_id', 9, '所在区域', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949825, 1641949825, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (503, 'city_id', 9, '所在城市', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949825, 1641949825, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (504, 'province_id', 9, '省份', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949825, 1641949825, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (505, 'htmlfilename', 9, '自定义文件名', 'img', 'varchar(250)', 250, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949825, 1641949825, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (506, 'collection', 9, '收藏数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949825, 1641949825, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (507, 'appraise', 9, '评价数', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949825, 1641949825, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (508, 'restric_type', 9, '限制模式，0=免费，1=付费，2=会员专享，3=会员付费', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949825, 1641949825, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (509, 'sales_num', 9, '销售量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949825, 1641949825, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (510, 'users_free', 9, '是否会员免费，默认0不免费，1为免费', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949825, 1641949825, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (511, 'attrlist_id', 9, '参数列表ID', 'int', 'int(10) unsigned', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949825, 1641949825, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (512, 'origin', 9, '来源', 'text', 'varchar(30)', 30, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949825, 1641949825, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (513, 'is_diyattr', 9, '自定义（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949825, 1641949825, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (514, 'is_slide', 9, '幻灯（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949825, 1641949825, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (515, 'is_roll', 9, '滚动（0=否，1=是）', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949825, 1641949825, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (516, 'subtitle', 9, '副标题', 'text', 'varchar(200)', 200, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949825, 1641949825, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (517, 'stypeid', 9, '副栏目ID集合', 'text', 'varchar(90)', 90, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1641949825, 1641949825, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (518, 'target', -99, '新窗口打开', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1547890773, 1547890773, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (519, 'nofollow', -99, '防抓取', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1547890773, 1547890773, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (520, 'content_ey_m', 1, '手机端内容详情', 'htmltext', 'longtext', 250, '', '', '', 0, 1, 1, 0, 1, 0, 0, 100, 1, 1533464713, 1767256170, 0, NULL, '2026-01-01 16:29:30');
INSERT INTO `wa_channelfield` VALUES (521, 'content_ey_m', 2, '手机端内容详情', 'htmltext', 'longtext', 250, '', '', '', 0, 1, 1, 0, 1, 0, 0, 100, 1, 1645086030, 1645086039, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (522, 'content_ey_m', 3, '手机端内容详情', 'htmltext', 'longtext', 250, '', '', '', 0, 1, 1, 0, 1, 0, 0, 100, 1, 1533359588, 1533359588, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (523, 'content_ey_m', 4, '手机端内容详情', 'htmltext', 'longtext', 250, '', '', '', 0, 1, 1, 0, 1, 0, 0, 100, 1, 1533359752, 1533359752, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (524, 'content_ey_m', 5, '手机端内容详情', 'htmltext', 'longtext', 250, '', '', '', 0, 1, 1, 0, 1, 0, 0, 100, 1, 1591957363, 1591957363, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (525, 'content_ey_m', 6, '手机端内容详情', 'htmltext', 'longtext', 250, '', '', '', 0, 1, 1, 0, 1, 0, 0, 100, 1, 1533464715, 1767256221, 0, NULL, '2026-01-01 16:30:21');
INSERT INTO `wa_channelfield` VALUES (526, 'content_ey_m', 7, '手机端内容详情', 'htmltext', 'longtext', 250, '', '', '', 0, 1, 1, 0, 1, 0, 0, 100, 1, 1602320145, 1602320145, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (527, 'typearcrank', -99, '阅读权限：0=开放浏览，-1=待审核稿件', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1547890773, 1547890773, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (528, 'empty_logic', -99, '空内容逻辑', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1533524780, 1533524780, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (529, 'users_discount_type', 0, '产品会员折扣类型(0:系统默认折扣; 1:指定会员级别; 2:不参与折扣;)', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1683873488, 1683873488, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (530, 'logistics_type', 0, '商品物流支持类型(1: 物流配送; 2: 到店核销)', 'text', 'varchar(100)', 100, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1690364521, 1690364521, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (531, 'editor_img_clear_link', 3, '清除非本站链接', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1701047939, 1701047939, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (532, 'editor_remote_img_local', 3, '远程图片本地化', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1701047939, 1701047939, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (533, 'no_vip_pay', 3, 'restric_type = 2 时,会员专享,非会员可付费使用,0-关闭,1-开启', 'switch', 'tinyint(3)', 3, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1701047939, 1701047939, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (534, 'logistics_type', 3, '商品物流支持类型(1: 物流配送; 2: 到店核销)', 'text', 'varchar(100)', 100, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1701047939, 1701047939, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (535, 'sales_all', 3, '虚拟总销量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1701047939, 1701047939, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (536, 'virtual_sales', 3, '商品虚拟销售量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1701047939, 1701047939, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (537, 'users_discount_type', 3, '产品会员折扣类型(0:系统默认折扣; 1:指定会员级别; 2:不参与折扣;)', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1701047939, 1701047939, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (538, 'crossed_price', 3, '商品划线价', 'decimal', 'decimal(10,2) unsigned', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1701047939, 1701047939, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (539, 'free_shipping', 3, '商品是否包邮(1包邮(免运费)  0跟随系统)', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1701047939, 1701047939, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (540, 'merchant_id', 3, '多商家ID', 'datetime', 'int(11) unsigned', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1701047939, 1701047939, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (541, 'total_arc', -99, '栏目下文档数量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1711942240, 1711942240, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (542, 'removal_time', 1, '下架时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (543, 'removal_time', 2, '下架时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (544, 'removal_time', 3, '下架时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (545, 'removal_time', 4, '下架时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (546, 'removal_time', 5, '下架时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (547, 'removal_time', 6, '下架时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (548, 'removal_time', 7, '下架时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (549, 'removal_time', 9, '下架时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (550, 'removal_time', 0, '下架时间', 'datetime', 'int(11)', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1574233796, 1574233796, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (551, 'stock_code', 1, '商品编码', 'text', 'varchar(100)', 100, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766102909, 1766102909, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (552, 'reason', 1, '退回原因', 'multitext', 'text', 0, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766102909, 1766102909, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (553, 'editor_ai_create', 1, 'AI创作声明', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766102909, 1766102909, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (554, 'editor_img_clear_link', 1, '清除非本站链接', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766102909, 1766102909, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (555, 'editor_remote_img_local', 1, '远程图片本地化', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766102909, 1766102909, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (556, 'no_vip_pay', 1, 'restric_type = 2 时,会员专享,非会员可付费使用,0-关闭,1-开启', 'switch', 'tinyint(3)', 3, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766102909, 1766102909, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (557, 'users_score', 1, 'restric_type=4时，会员可使用积分进行文章订单支付购买', 'text', 'varchar(20)', 20, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766102909, 1766102909, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (558, 'logistics_type', 1, '商品物流支持类型(1: 物流配送; 2: 到店核销)', 'text', 'varchar(100)', 100, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766102909, 1766102909, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (559, 'sales_all', 1, '虚拟总销量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766102909, 1766102909, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (560, 'virtual_sales', 1, '商品虚拟销售量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766102909, 1766102909, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (561, 'users_discount_type', 1, '产品会员折扣类型(0:系统默认折扣; 1:指定会员级别; 2:不参与折扣;)', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766102909, 1766102909, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (562, 'crossed_price', 1, '商品划线价', 'decimal', 'decimal(10,2) unsigned', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766102909, 1766102909, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (563, 'free_shipping', 1, '商品是否包邮(1包邮(免运费)  0跟随系统)', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766102909, 1766102909, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (564, 'merchant_id', 1, '多商家ID', 'datetime', 'int(11) unsigned', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766102909, 1766102909, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (565, 'introduction', 1, '促销语', 'text', 'varchar(500)', 500, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766102909, 1766102909, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (566, 'stock_code', 2, '商品编码', 'text', 'varchar(100)', 100, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766532851, 1766532851, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (567, 'reason', 2, '退回原因', 'multitext', 'text', 0, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766532851, 1766532851, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (568, 'editor_ai_create', 2, 'AI创作声明', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766532851, 1766532851, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (569, 'editor_img_clear_link', 2, '清除非本站链接', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766532851, 1766532851, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (570, 'editor_remote_img_local', 2, '远程图片本地化', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766532851, 1766532851, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (571, 'no_vip_pay', 2, 'restric_type = 2 时,会员专享,非会员可付费使用,0-关闭,1-开启', 'switch', 'tinyint(3)', 3, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766532851, 1766532851, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (572, 'users_score', 2, 'restric_type=4时，会员可使用积分进行文章订单支付购买', 'text', 'varchar(20)', 20, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766532851, 1766532851, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (573, 'logistics_type', 2, '商品物流支持类型(1: 物流配送; 2: 到店核销)', 'text', 'varchar(100)', 100, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766532851, 1766532851, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (574, 'sales_all', 2, '虚拟总销量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766532851, 1766532851, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (575, 'virtual_sales', 2, '商品虚拟销售量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766532851, 1766532851, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (576, 'users_discount_type', 2, '产品会员折扣类型(0:系统默认折扣; 1:指定会员级别; 2:不参与折扣;)', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766532851, 1766532851, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (577, 'crossed_price', 2, '商品划线价', 'decimal', 'decimal(10,2) unsigned', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766532851, 1766532851, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (578, 'free_shipping', 2, '商品是否包邮(1包邮(免运费)  0跟随系统)', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766532851, 1766532851, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (579, 'merchant_id', 2, '多商家ID', 'datetime', 'int(11) unsigned', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766532851, 1766532851, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (580, 'introduction', 2, '促销语', 'text', 'varchar(500)', 500, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766532851, 1766532851, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (581, 'stock_code', 3, '商品编码', 'text', 'varchar(100)', 100, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533071, 1766533071, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (582, 'reason', 3, '退回原因', 'multitext', 'text', 0, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533071, 1766533071, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (583, 'editor_ai_create', 3, 'AI创作声明', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533071, 1766533071, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (584, 'users_score', 3, 'restric_type=4时，会员可使用积分进行文章订单支付购买', 'text', 'varchar(20)', 20, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533071, 1766533071, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (585, 'introduction', 3, '促销语', 'text', 'varchar(500)', 500, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533071, 1766533071, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (586, 'stock_code', 6, '商品编码', 'text', 'varchar(100)', 100, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533076, 1766533076, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (587, 'reason', 6, '退回原因', 'multitext', 'text', 0, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533076, 1766533076, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (588, 'editor_ai_create', 6, 'AI创作声明', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533076, 1766533076, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (589, 'editor_img_clear_link', 6, '清除非本站链接', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533076, 1766533076, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (590, 'editor_remote_img_local', 6, '远程图片本地化', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533076, 1766533076, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (591, 'no_vip_pay', 6, 'restric_type = 2 时,会员专享,非会员可付费使用,0-关闭,1-开启', 'switch', 'tinyint(3)', 3, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533076, 1766533076, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (592, 'users_score', 6, 'restric_type=4时，会员可使用积分进行文章订单支付购买', 'text', 'varchar(20)', 20, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533076, 1766533076, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (593, 'logistics_type', 6, '商品物流支持类型(1: 物流配送; 2: 到店核销)', 'text', 'varchar(100)', 100, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533076, 1766533076, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (594, 'sales_all', 6, '虚拟总销量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533076, 1766533076, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (595, 'virtual_sales', 6, '商品虚拟销售量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533076, 1766533076, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (596, 'users_discount_type', 6, '产品会员折扣类型(0:系统默认折扣; 1:指定会员级别; 2:不参与折扣;)', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533076, 1766533076, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (597, 'crossed_price', 6, '商品划线价', 'decimal', 'decimal(10,2) unsigned', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533076, 1766533076, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (598, 'free_shipping', 6, '商品是否包邮(1包邮(免运费)  0跟随系统)', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533076, 1766533076, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (599, 'merchant_id', 6, '多商家ID', 'datetime', 'int(11) unsigned', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533076, 1766533076, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (600, 'introduction', 6, '促销语', 'text', 'varchar(500)', 500, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533076, 1766533076, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (601, 'stock_code', 7, '商品编码', 'text', 'varchar(100)', 100, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533083, 1766533083, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (602, 'reason', 7, '退回原因', 'multitext', 'text', 0, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533083, 1766533083, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (603, 'editor_ai_create', 7, 'AI创作声明', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533083, 1766533083, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (604, 'editor_img_clear_link', 7, '清除非本站链接', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533083, 1766533083, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (605, 'editor_remote_img_local', 7, '远程图片本地化', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533083, 1766533083, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (606, 'no_vip_pay', 7, 'restric_type = 2 时,会员专享,非会员可付费使用,0-关闭,1-开启', 'switch', 'tinyint(3)', 3, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533083, 1766533083, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (607, 'users_score', 7, 'restric_type=4时，会员可使用积分进行文章订单支付购买', 'text', 'varchar(20)', 20, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533083, 1766533083, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (608, 'logistics_type', 7, '商品物流支持类型(1: 物流配送; 2: 到店核销)', 'text', 'varchar(100)', 100, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533083, 1766533083, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (609, 'sales_all', 7, '虚拟总销量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533083, 1766533083, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (610, 'virtual_sales', 7, '商品虚拟销售量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533083, 1766533083, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (611, 'users_discount_type', 7, '产品会员折扣类型(0:系统默认折扣; 1:指定会员级别; 2:不参与折扣;)', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533083, 1766533083, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (612, 'crossed_price', 7, '商品划线价', 'decimal', 'decimal(10,2) unsigned', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533083, 1766533083, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (613, 'free_shipping', 7, '商品是否包邮(1包邮(免运费)  0跟随系统)', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533083, 1766533083, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (614, 'merchant_id', 7, '多商家ID', 'datetime', 'int(11) unsigned', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533083, 1766533083, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (615, 'introduction', 7, '促销语', 'text', 'varchar(500)', 500, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533083, 1766533083, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (616, 'stock_code', 9, '商品编码', 'text', 'varchar(100)', 100, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533089, 1766533089, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (617, 'reason', 9, '退回原因', 'multitext', 'text', 0, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533089, 1766533089, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (618, 'editor_ai_create', 9, 'AI创作声明', 'switch', 'tinyint(1)', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533089, 1766533089, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (619, 'editor_img_clear_link', 9, '清除非本站链接', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533089, 1766533089, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (620, 'editor_remote_img_local', 9, '远程图片本地化', 'switch', 'tinyint(1)', 1, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533089, 1766533089, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (621, 'no_vip_pay', 9, 'restric_type = 2 时,会员专享,非会员可付费使用,0-关闭,1-开启', 'switch', 'tinyint(3)', 3, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533089, 1766533089, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (622, 'users_score', 9, 'restric_type=4时，会员可使用积分进行文章订单支付购买', 'text', 'varchar(20)', 20, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533089, 1766533089, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (623, 'logistics_type', 9, '商品物流支持类型(1: 物流配送; 2: 到店核销)', 'text', 'varchar(100)', 100, '1', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533089, 1766533089, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (624, 'sales_all', 9, '虚拟总销量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533089, 1766533089, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (625, 'virtual_sales', 9, '商品虚拟销售量', 'int', 'int(10)', 10, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533089, 1766533089, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (626, 'users_discount_type', 9, '产品会员折扣类型(0:系统默认折扣; 1:指定会员级别; 2:不参与折扣;)', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533089, 1766533089, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (627, 'crossed_price', 9, '商品划线价', 'decimal', 'decimal(10,2) unsigned', 10, '0.00', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533089, 1766533089, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (628, 'free_shipping', 9, '商品是否包邮(1包邮(免运费)  0跟随系统)', 'switch', 'tinyint(1) unsigned', 1, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533089, 1766533089, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (629, 'merchant_id', 9, '多商家ID', 'datetime', 'int(11) unsigned', 11, '0', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533089, 1766533089, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (630, 'introduction', 9, '促销语', 'text', 'varchar(500)', 500, '', '', '', 0, 0, 1, 0, 1, 1, 1, 100, 1, 1766533089, 1766533089, 0, NULL, NULL);
INSERT INTO `wa_channelfield` VALUES (637, 'test', 1, '测试字段', 'text', 'varchar(255)', 255, 'addnew', '个', '', 0, 1, 1, 0, 0, 0, 0, 100, 1, 1767067690, 1767580027, 0, '2025-12-30 12:08:10', '2026-01-05 10:27:07');
INSERT INTO `wa_channelfield` VALUES (640, 'num', 6, '点击数', 'text', 'varchar(255)', 255, '', '', '', 0, 0, 1, 0, 0, 0, 0, 100, 1, 1767256274, 1767349745, 0, '2026-01-01 16:31:14', '2026-01-02 18:29:05');
INSERT INTO `wa_channelfield` VALUES (641, 'test', 2, '测试', 'text', 'varchar(255)', 255, '', '', '', 0, 0, 1, 0, 0, 0, 0, 100, 1, 1767518002, 1767518002, 0, '2026-01-04 17:13:22', '2026-01-04 17:13:22');

-- ----------------------------
-- Table structure for wa_channelfield_bind
-- ----------------------------
DROP TABLE IF EXISTS `wa_channelfield_bind`;
CREATE TABLE `wa_channelfield_bind`  (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `typeid` int(10) NULL DEFAULT 0 COMMENT '栏目ID',
  `field_id` int(10) NULL DEFAULT 0 COMMENT '自定义字段ID',
  `add_time` int(11) NULL DEFAULT 0 COMMENT '新增时间',
  `update_time` int(11) NULL DEFAULT 0 COMMENT '更新时间',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 366 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '栏目与自定义字段绑定表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_channelfield_bind
-- ----------------------------
INSERT INTO `wa_channelfield_bind` VALUES (1, 0, 24, 1563518642, 1563518642, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (2, 0, 25, 1563518642, 1563518642, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (3, 0, 26, 1563518642, 1563518642, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (4, 0, 27, 1563518642, 1563518642, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (5, 0, 29, 1563518642, 1563518642, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (6, 3, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (7, 20, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (8, 21, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (9, 22, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (10, 24, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (11, 25, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (12, 26, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (13, 27, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (14, 28, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (15, 29, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (16, 41, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (17, 42, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (18, 43, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (19, 44, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (20, 45, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (21, 46, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (22, 47, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (23, 48, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (24, 49, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (25, 50, 88, 1563518738, 1563518738, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (26, 0, 120, 1563526665, 1563526665, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (27, 0, 121, 1563526681, 1563526681, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (28, 0, 122, 1563526694, 1563526694, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (29, 0, 123, 1563526705, 1563526705, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (30, 0, 124, 1563526716, 1563526716, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (31, 0, 125, 1563526727, 1563526727, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (32, 0, 126, 1563526769, 1563526769, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (193, 3, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (194, 20, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (195, 21, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (196, 22, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (197, 24, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (198, 25, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (199, 26, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (200, 27, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (201, 28, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (202, 29, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (203, 41, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (204, 42, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (205, 43, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (206, 44, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (207, 45, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (208, 46, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (209, 47, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (210, 48, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (211, 49, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (212, 50, 133, 1565662740, 1565662740, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (213, 3, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (214, 20, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (215, 21, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (216, 22, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (217, 24, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (218, 25, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (219, 26, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (220, 27, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (221, 28, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (222, 29, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (223, 41, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (224, 42, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (225, 43, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (226, 44, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (227, 45, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (228, 46, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (229, 47, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (230, 48, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (231, 49, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (232, 50, 132, 1565663341, 1565663341, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (274, 0, 355, 1591957363, 1591957363, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (275, 0, 356, 1591957363, 1591957363, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (276, 0, 357, 1591957363, 1591957363, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (277, 0, 358, 1591957363, 1591957363, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (278, 0, 359, 1591957363, 1591957363, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (279, 0, 403, 1602320145, 1602320145, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (340, 1, 640, 1767349745, 1767349745, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (341, 8, 640, 1767349745, 1767349745, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (342, 30, 640, 1767349745, 1767349745, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (353, 3, 641, 1767518002, 1767518002, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (354, 20, 641, 1767518002, 1767518002, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (355, 24, 641, 1767518002, 1767518002, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (356, 69, 641, 1767518002, 1767518002, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (357, 78, 641, 1767518002, 1767518002, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (358, 25, 641, 1767518002, 1767518002, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (359, 21, 641, 1767518002, 1767518002, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (360, 26, 641, 1767518002, 1767518002, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (361, 22, 641, 1767518002, 1767518002, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (362, 27, 641, 1767518002, 1767518002, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (363, 28, 641, 1767518002, 1767518002, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (364, 29, 641, 1767518002, 1767518002, NULL, NULL);
INSERT INTO `wa_channelfield_bind` VALUES (365, 9, 637, 1767580027, 1767580027, NULL, NULL);

-- ----------------------------
-- Table structure for wa_channelfield_log
-- ----------------------------
DROP TABLE IF EXISTS `wa_channelfield_log`;
CREATE TABLE `wa_channelfield_log`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '字段名称',
  `channel_id` int(10) NULL DEFAULT 0 COMMENT '模型ID',
  `dtype` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '字段类型',
  `add_time` int(11) NULL DEFAULT 0 COMMENT '新增时间',
  `update_time` int(11) NULL DEFAULT 0 COMMENT '更新时间',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '自定义字段日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_channelfield_log
-- ----------------------------

-- ----------------------------
-- Table structure for wa_channeltype
-- ----------------------------
DROP TABLE IF EXISTS `wa_channeltype`;
CREATE TABLE `wa_channeltype`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '识别id',
  `title` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '名称',
  `ntitle` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '左侧菜单名称',
  `table` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '表名',
  `ctl_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '控制器名称（区分大小写）',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态(1=启用，0=屏蔽)',
  `ifsystem` tinyint(1) NULL DEFAULT 0 COMMENT '字段分类，1=系统(不可修改)，0=自定义',
  `is_repeat_title` tinyint(1) NULL DEFAULT 1 COMMENT '文档标题重复，1=允许，0=不允许',
  `is_release` tinyint(1) NULL DEFAULT 0 COMMENT '模型是否允许应用于会员投稿发布，1是，0否',
  `is_litpic_users_release` tinyint(1) NULL DEFAULT 1 COMMENT '缩略图是否应用于会员投稿，1=允许，0=不允许',
  `data` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '额外序列化存储数据',
  `is_del` tinyint(1) NULL DEFAULT 0 COMMENT '伪删除，1=是，0=否',
  `sort_order` smallint(6) NULL DEFAULT 50 COMMENT '排序',
  `add_time` int(11) NULL DEFAULT 0 COMMENT '新增时间',
  `update_time` int(11) NULL DEFAULT 0 COMMENT '更新时间',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `idention`(`nid`) USING BTREE,
  UNIQUE INDEX `ctl_name`(`ctl_name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 52 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统模型表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_channeltype
-- ----------------------------
INSERT INTO `wa_channeltype` VALUES (1, 'article', '文章模型', '文章', 'article', 'Article', 1, 1, 1, 0, 1, '', 0, 1, 0, 1564532747, NULL, '2025-12-24 20:03:54');
INSERT INTO `wa_channeltype` VALUES (2, 'product', '产品模型', '产品', 'product', 'Product', 1, 1, 1, 0, 1, '', 0, 2, 0, 1564532747, NULL, NULL);
INSERT INTO `wa_channeltype` VALUES (3, 'images', '图集模型', '图集', 'images', 'Images', 1, 1, 1, 0, 1, '', 0, 3, 1523929121, 1564532747, NULL, NULL);
INSERT INTO `wa_channeltype` VALUES (4, 'download', '下载模型', '下载', 'download', 'Download', 1, 1, 1, 0, 1, '', 0, 4, 0, 1564532747, NULL, NULL);
INSERT INTO `wa_channeltype` VALUES (5, 'media', '视频模型', '视频', 'media', 'Media', 0, 1, 1, 0, 1, '', 0, 5, 1509197711, 1564532747, NULL, NULL);
INSERT INTO `wa_channeltype` VALUES (6, 'single', '单页模型', '单页', 'single', 'Single', 1, 1, 1, 0, 1, '', 0, 6, 1523091961, 1564532747, NULL, NULL);
INSERT INTO `wa_channeltype` VALUES (7, 'special', '专题模型', '专题', 'special', 'Special', 0, 1, 1, 0, 1, '', 0, 7, 1509197711, 1564532747, NULL, NULL);
INSERT INTO `wa_channeltype` VALUES (8, 'guestbook', '留言模型', '留言', 'guestbook', 'Guestbook', 1, 1, 1, 0, 1, '', 0, 8, 1509197711, 1690181604, NULL, NULL);
INSERT INTO `wa_channeltype` VALUES (9, 'recruit', '招聘模型', '人才招聘', 'recruit', 'Recruit', 1, 0, 1, 0, 1, '', 0, 50, 1563526560, 1564532747, NULL, NULL);
INSERT INTO `wa_channeltype` VALUES (51, 'ask', '问答模型', '问答', 'ask', 'Ask', 0, 1, 1, 0, 1, '', 1, 9, 1509197711, 1692668301, NULL, NULL);

-- ----------------------------
-- Table structure for wa_field_custom_param
-- ----------------------------
DROP TABLE IF EXISTS `wa_field_custom_param`;
CREATE TABLE `wa_field_custom_param`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `field_id` int(11) NULL DEFAULT 0,
  `channel_id` int(11) NULL DEFAULT 0,
  `dtype` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `name` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '名称',
  `dfvalue` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
  `sort_order` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '排序',
  `add_time` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '添加时间',
  `update_time` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `field_id`(`field_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '自定义字段-多选项[新]' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_field_custom_param
-- ----------------------------

-- ----------------------------
-- Table structure for wa_field_type
-- ----------------------------
DROP TABLE IF EXISTS `wa_field_type`;
CREATE TABLE `wa_field_type`  (
  `name` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '字段类型',
  `title` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '中文类型名',
  `ifoption` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否需要设置选项',
  `sort_order` int(10) NOT NULL DEFAULT 0 COMMENT '排序',
  `add_time` int(11) NOT NULL DEFAULT 0 COMMENT '新增时间',
  `update_time` int(11) NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '字段类型表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_field_type
-- ----------------------------
INSERT INTO `wa_field_type` VALUES ('checkbox', '多选项[旧]', 1, 151, 1532485708, 1532485708);
INSERT INTO `wa_field_type` VALUES ('checkboxs', '多选项', 0, 150, 1532485708, 1532485708);
INSERT INTO `wa_field_type` VALUES ('datetime', '日期和时间', 0, 220, 1532485708, 1532485708);
INSERT INTO `wa_field_type` VALUES ('decimal', '金额类型', 0, 190, 1532485708, 1532485708);
INSERT INTO `wa_field_type` VALUES ('file', '附件类型', 0, 210, 1532485708, 1532485708);
INSERT INTO `wa_field_type` VALUES ('float', '小数类型', 0, 180, 1532485708, 1532485708);
INSERT INTO `wa_field_type` VALUES ('htmltext', 'HTML文本', 0, 130, 1532485708, 1532485708);
INSERT INTO `wa_field_type` VALUES ('img', '单张图', 0, 200, 1532485708, 1532485708);
INSERT INTO `wa_field_type` VALUES ('imgs', '多张图', 0, 210, 1532485708, 1532485708);
INSERT INTO `wa_field_type` VALUES ('int', '整数类型', 0, 170, 1532485708, 1532485708);
INSERT INTO `wa_field_type` VALUES ('media', '多媒体类型', 0, 210, 1532485708, 1532485708);
INSERT INTO `wa_field_type` VALUES ('multitext', '多行文本', 0, 120, 1532485708, 1532485708);
INSERT INTO `wa_field_type` VALUES ('radio', '单选项[旧]', 1, 141, 1532485708, 1532485708);
INSERT INTO `wa_field_type` VALUES ('radios', '单选项', 0, 140, 1532485708, 1532485708);
INSERT INTO `wa_field_type` VALUES ('region', '区域类型', 1, 169, 1532485708, 1532485708);
INSERT INTO `wa_field_type` VALUES ('select', '下拉框', 1, 161, 1532485708, 1532485708);
INSERT INTO `wa_field_type` VALUES ('switch', '开关', 0, 230, 1532485708, 1532485708);
INSERT INTO `wa_field_type` VALUES ('text', '单行文本', 0, 110, 1532485708, 1532485708);

-- ----------------------------
-- Table structure for wa_options
-- ----------------------------
DROP TABLE IF EXISTS `wa_options`;
CREATE TABLE `wa_options`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '键',
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '值',
  `created_at` datetime NOT NULL DEFAULT '2022-08-15 00:00:00' COMMENT '创建时间',
  `updated_at` datetime NOT NULL DEFAULT '2022-08-15 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '选项表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_options
-- ----------------------------
INSERT INTO `wa_options` VALUES (1, 'system_config', '{\"logo\":{\"title\":\"Webman Cms\",\"image\":\"\\/app\\/admin\\/admin\\/images\\/logo.png\",\"icp\":\"\",\"beian\":\"\",\"footer_txt\":\"\"},\"menu\":{\"data\":\"\\/app\\/admin\\/rule\\/get\",\"method\":\"GET\",\"accordion\":true,\"collapse\":false,\"control\":false,\"controlWidth\":500,\"select\":\"0\",\"async\":true},\"tab\":{\"enable\":true,\"keepState\":true,\"preload\":false,\"session\":true,\"max\":\"30\",\"index\":{\"id\":\"0\",\"href\":\"\\/app\\/admin\\/index\\/dashboard\",\"title\":\"\\u4eea\\u8868\\u76d8\"}},\"theme\":{\"defaultColor\":\"2\",\"defaultMenu\":\"light-theme\",\"defaultHeader\":\"light-theme\",\"allowCustom\":true,\"banner\":false},\"colors\":[{\"id\":\"1\",\"color\":\"#36b368\",\"second\":\"#f0f9eb\"},{\"id\":\"2\",\"color\":\"#2d8cf0\",\"second\":\"#ecf5ff\"},{\"id\":\"3\",\"color\":\"#f6ad55\",\"second\":\"#fdf6ec\"},{\"id\":\"4\",\"color\":\"#f56c6c\",\"second\":\"#fef0f0\"},{\"id\":\"5\",\"color\":\"#3963bc\",\"second\":\"#ecf5ff\"}],\"other\":{\"keepLoad\":\"500\",\"autoHead\":false,\"footer\":false},\"header\":{\"message\":false}}', '2022-12-05 14:49:01', '2025-12-31 14:20:52');
INSERT INTO `wa_options` VALUES (2, 'table_form_schema_wa_users', '{\"id\":{\"field\":\"id\",\"_field_id\":\"0\",\"comment\":\"主键\",\"control\":\"inputNumber\",\"control_args\":\"\",\"list_show\":true,\"enable_sort\":true,\"searchable\":true,\"search_type\":\"normal\",\"form_show\":false},\"username\":{\"field\":\"username\",\"_field_id\":\"1\",\"comment\":\"用户名\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"enable_sort\":false},\"nickname\":{\"field\":\"nickname\",\"_field_id\":\"2\",\"comment\":\"昵称\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"enable_sort\":false},\"password\":{\"field\":\"password\",\"_field_id\":\"3\",\"comment\":\"密码\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"search_type\":\"normal\",\"list_show\":false,\"enable_sort\":false,\"searchable\":false},\"sex\":{\"field\":\"sex\",\"_field_id\":\"4\",\"comment\":\"性别\",\"control\":\"select\",\"control_args\":\"url:\\/app\\/admin\\/dict\\/get\\/sex\",\"form_show\":true,\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"enable_sort\":false},\"avatar\":{\"field\":\"avatar\",\"_field_id\":\"5\",\"comment\":\"头像\",\"control\":\"uploadImage\",\"control_args\":\"url:\\/app\\/admin\\/upload\\/avatar\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"email\":{\"field\":\"email\",\"_field_id\":\"6\",\"comment\":\"邮箱\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"enable_sort\":false},\"mobile\":{\"field\":\"mobile\",\"_field_id\":\"7\",\"comment\":\"手机\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"enable_sort\":false},\"level\":{\"field\":\"level\",\"_field_id\":\"8\",\"comment\":\"等级\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"list_show\":false,\"enable_sort\":false},\"birthday\":{\"field\":\"birthday\",\"_field_id\":\"9\",\"comment\":\"生日\",\"control\":\"datePicker\",\"control_args\":\"\",\"form_show\":true,\"searchable\":true,\"search_type\":\"between\",\"list_show\":false,\"enable_sort\":false},\"money\":{\"field\":\"money\",\"_field_id\":\"10\",\"comment\":\"余额(元)\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"list_show\":false,\"enable_sort\":false},\"score\":{\"field\":\"score\",\"_field_id\":\"11\",\"comment\":\"积分\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"list_show\":false,\"enable_sort\":false},\"last_time\":{\"field\":\"last_time\",\"_field_id\":\"12\",\"comment\":\"登录时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"form_show\":true,\"searchable\":true,\"search_type\":\"between\",\"list_show\":false,\"enable_sort\":false},\"last_ip\":{\"field\":\"last_ip\",\"_field_id\":\"13\",\"comment\":\"登录ip\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"list_show\":false,\"enable_sort\":false},\"join_time\":{\"field\":\"join_time\",\"_field_id\":\"14\",\"comment\":\"注册时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"form_show\":true,\"searchable\":true,\"search_type\":\"between\",\"list_show\":false,\"enable_sort\":false},\"join_ip\":{\"field\":\"join_ip\",\"_field_id\":\"15\",\"comment\":\"注册ip\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"list_show\":false,\"enable_sort\":false},\"token\":{\"field\":\"token\",\"_field_id\":\"16\",\"comment\":\"token\",\"control\":\"input\",\"control_args\":\"\",\"search_type\":\"normal\",\"form_show\":false,\"list_show\":false,\"enable_sort\":false,\"searchable\":false},\"created_at\":{\"field\":\"created_at\",\"_field_id\":\"17\",\"comment\":\"创建时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"form_show\":true,\"search_type\":\"between\",\"list_show\":false,\"enable_sort\":false,\"searchable\":false},\"updated_at\":{\"field\":\"updated_at\",\"_field_id\":\"18\",\"comment\":\"更新时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"search_type\":\"between\",\"form_show\":false,\"list_show\":false,\"enable_sort\":false,\"searchable\":false},\"role\":{\"field\":\"role\",\"_field_id\":\"19\",\"comment\":\"角色\",\"control\":\"inputNumber\",\"control_args\":\"\",\"search_type\":\"normal\",\"form_show\":false,\"list_show\":false,\"enable_sort\":false,\"searchable\":false},\"status\":{\"field\":\"status\",\"_field_id\":\"20\",\"comment\":\"禁用\",\"control\":\"switch\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false}}', '2022-08-15 00:00:00', '2022-12-23 15:28:13');
INSERT INTO `wa_options` VALUES (3, 'table_form_schema_wa_roles', '{\"id\":{\"field\":\"id\",\"_field_id\":\"0\",\"comment\":\"主键\",\"control\":\"inputNumber\",\"control_args\":\"\",\"list_show\":true,\"search_type\":\"normal\",\"form_show\":false,\"enable_sort\":false,\"searchable\":false},\"name\":{\"field\":\"name\",\"_field_id\":\"1\",\"comment\":\"角色组\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"rules\":{\"field\":\"rules\",\"_field_id\":\"2\",\"comment\":\"权限\",\"control\":\"treeSelectMulti\",\"control_args\":\"url:\\/app\\/admin\\/rule\\/get?type=0,1,2\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"created_at\":{\"field\":\"created_at\",\"_field_id\":\"3\",\"comment\":\"创建时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"search_type\":\"normal\",\"form_show\":false,\"list_show\":false,\"enable_sort\":false,\"searchable\":false},\"updated_at\":{\"field\":\"updated_at\",\"_field_id\":\"4\",\"comment\":\"更新时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"search_type\":\"normal\",\"form_show\":false,\"list_show\":false,\"enable_sort\":false,\"searchable\":false},\"pid\":{\"field\":\"pid\",\"_field_id\":\"5\",\"comment\":\"父级\",\"control\":\"select\",\"control_args\":\"url:\\/app\\/admin\\/role\\/select?format=tree\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false}}', '2022-08-15 00:00:00', '2022-12-19 14:24:25');
INSERT INTO `wa_options` VALUES (4, 'table_form_schema_wa_rules', '{\"id\":{\"field\":\"id\",\"_field_id\":\"0\",\"comment\":\"主键\",\"control\":\"inputNumber\",\"control_args\":\"\",\"search_type\":\"normal\",\"form_show\":false,\"list_show\":false,\"enable_sort\":false,\"searchable\":false},\"title\":{\"field\":\"title\",\"_field_id\":\"1\",\"comment\":\"标题\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"enable_sort\":false},\"icon\":{\"field\":\"icon\",\"_field_id\":\"2\",\"comment\":\"图标\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"key\":{\"field\":\"key\",\"_field_id\":\"3\",\"comment\":\"标识\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"enable_sort\":false},\"pid\":{\"field\":\"pid\",\"_field_id\":\"4\",\"comment\":\"上级菜单\",\"control\":\"treeSelect\",\"control_args\":\"\\/app\\/admin\\/rule\\/select?format=tree&type=0,1\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"created_at\":{\"field\":\"created_at\",\"_field_id\":\"5\",\"comment\":\"创建时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"search_type\":\"normal\",\"form_show\":false,\"list_show\":false,\"enable_sort\":false,\"searchable\":false},\"updated_at\":{\"field\":\"updated_at\",\"_field_id\":\"6\",\"comment\":\"更新时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"search_type\":\"normal\",\"form_show\":false,\"list_show\":false,\"enable_sort\":false,\"searchable\":false},\"href\":{\"field\":\"href\",\"_field_id\":\"7\",\"comment\":\"url\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"type\":{\"field\":\"type\",\"_field_id\":\"8\",\"comment\":\"类型\",\"control\":\"input\",\"control_args\":\"data:0:目录,1:菜单,2:权限\",\"form_show\":true,\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"enable_sort\":false},\"weight\":{\"field\":\"weight\",\"_field_id\":\"9\",\"comment\":\"排序\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"status\":{\"field\":\"status\",\"_field_id\":\"10\",\"comment\":\"状态\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false}}', '2022-08-15 00:00:00', '2026-01-06 10:37:57');
INSERT INTO `wa_options` VALUES (5, 'table_form_schema_wa_admins', '{\"id\":{\"field\":\"id\",\"_field_id\":\"0\",\"comment\":\"ID\",\"control\":\"inputNumber\",\"control_args\":\"\",\"list_show\":true,\"enable_sort\":true,\"search_type\":\"between\",\"form_show\":false,\"searchable\":false},\"username\":{\"field\":\"username\",\"_field_id\":\"1\",\"comment\":\"用户名\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"enable_sort\":false},\"nickname\":{\"field\":\"nickname\",\"_field_id\":\"2\",\"comment\":\"昵称\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"enable_sort\":false},\"password\":{\"field\":\"password\",\"_field_id\":\"3\",\"comment\":\"密码\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"search_type\":\"normal\",\"list_show\":false,\"enable_sort\":false,\"searchable\":false},\"avatar\":{\"field\":\"avatar\",\"_field_id\":\"4\",\"comment\":\"头像\",\"control\":\"uploadImage\",\"control_args\":\"url:\\/app\\/admin\\/upload\\/avatar\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"email\":{\"field\":\"email\",\"_field_id\":\"5\",\"comment\":\"邮箱\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"enable_sort\":false},\"mobile\":{\"field\":\"mobile\",\"_field_id\":\"6\",\"comment\":\"手机\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"enable_sort\":false},\"created_at\":{\"field\":\"created_at\",\"_field_id\":\"7\",\"comment\":\"创建时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"form_show\":true,\"searchable\":true,\"search_type\":\"between\",\"list_show\":false,\"enable_sort\":false},\"updated_at\":{\"field\":\"updated_at\",\"_field_id\":\"8\",\"comment\":\"更新时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"form_show\":true,\"search_type\":\"normal\",\"list_show\":false,\"enable_sort\":false,\"searchable\":false},\"login_at\":{\"field\":\"login_at\",\"_field_id\":\"9\",\"comment\":\"登录时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"between\",\"enable_sort\":false,\"searchable\":false},\"status\":{\"field\":\"status\",\"_field_id\":\"10\",\"comment\":\"禁用\",\"control\":\"switch\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false}}', '2022-08-15 00:00:00', '2022-12-23 15:36:48');
INSERT INTO `wa_options` VALUES (6, 'table_form_schema_wa_options', '{\"id\":{\"field\":\"id\",\"_field_id\":\"0\",\"comment\":\"\",\"control\":\"inputNumber\",\"control_args\":\"\",\"list_show\":true,\"search_type\":\"normal\",\"form_show\":false,\"enable_sort\":false,\"searchable\":false},\"name\":{\"field\":\"name\",\"_field_id\":\"1\",\"comment\":\"键\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"value\":{\"field\":\"value\",\"_field_id\":\"2\",\"comment\":\"值\",\"control\":\"textArea\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"created_at\":{\"field\":\"created_at\",\"_field_id\":\"3\",\"comment\":\"创建时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"search_type\":\"normal\",\"form_show\":false,\"list_show\":false,\"enable_sort\":false,\"searchable\":false},\"updated_at\":{\"field\":\"updated_at\",\"_field_id\":\"4\",\"comment\":\"更新时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"search_type\":\"normal\",\"form_show\":false,\"list_show\":false,\"enable_sort\":false,\"searchable\":false}}', '2022-08-15 00:00:00', '2022-12-08 11:36:57');
INSERT INTO `wa_options` VALUES (7, 'table_form_schema_wa_uploads', '{\"id\":{\"field\":\"id\",\"_field_id\":\"0\",\"comment\":\"主键\",\"control\":\"inputNumber\",\"control_args\":\"\",\"list_show\":true,\"enable_sort\":true,\"search_type\":\"normal\",\"form_show\":false,\"searchable\":false},\"name\":{\"field\":\"name\",\"_field_id\":\"1\",\"comment\":\"名称\",\"control\":\"input\",\"control_args\":\"\",\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"form_show\":false,\"enable_sort\":false},\"url\":{\"field\":\"url\",\"_field_id\":\"2\",\"comment\":\"文件\",\"control\":\"upload\",\"control_args\":\"url:\\/app\\/admin\\/upload\\/file\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"admin_id\":{\"field\":\"admin_id\",\"_field_id\":\"3\",\"comment\":\"管理员\",\"control\":\"select\",\"control_args\":\"url:\\/app\\/admin\\/admin\\/select?format=select\",\"search_type\":\"normal\",\"form_show\":false,\"list_show\":false,\"enable_sort\":false,\"searchable\":false},\"file_size\":{\"field\":\"file_size\",\"_field_id\":\"4\",\"comment\":\"文件大小\",\"control\":\"inputNumber\",\"control_args\":\"\",\"list_show\":true,\"search_type\":\"between\",\"form_show\":false,\"enable_sort\":false,\"searchable\":false},\"mime_type\":{\"field\":\"mime_type\",\"_field_id\":\"5\",\"comment\":\"mime类型\",\"control\":\"input\",\"control_args\":\"\",\"list_show\":true,\"search_type\":\"normal\",\"form_show\":false,\"enable_sort\":false,\"searchable\":false},\"image_width\":{\"field\":\"image_width\",\"_field_id\":\"6\",\"comment\":\"图片宽度\",\"control\":\"inputNumber\",\"control_args\":\"\",\"list_show\":true,\"search_type\":\"normal\",\"form_show\":false,\"enable_sort\":false,\"searchable\":false},\"image_height\":{\"field\":\"image_height\",\"_field_id\":\"7\",\"comment\":\"图片高度\",\"control\":\"inputNumber\",\"control_args\":\"\",\"list_show\":true,\"search_type\":\"normal\",\"form_show\":false,\"enable_sort\":false,\"searchable\":false},\"ext\":{\"field\":\"ext\",\"_field_id\":\"8\",\"comment\":\"扩展名\",\"control\":\"input\",\"control_args\":\"\",\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"form_show\":false,\"enable_sort\":false},\"storage\":{\"field\":\"storage\",\"_field_id\":\"9\",\"comment\":\"存储位置\",\"control\":\"input\",\"control_args\":\"\",\"search_type\":\"normal\",\"form_show\":false,\"list_show\":false,\"enable_sort\":false,\"searchable\":false},\"created_at\":{\"field\":\"created_at\",\"_field_id\":\"10\",\"comment\":\"上传时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"searchable\":true,\"search_type\":\"between\",\"form_show\":false,\"list_show\":false,\"enable_sort\":false},\"category\":{\"field\":\"category\",\"_field_id\":\"11\",\"comment\":\"类别\",\"control\":\"select\",\"control_args\":\"url:\\/app\\/admin\\/dict\\/get\\/upload\",\"form_show\":true,\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"enable_sort\":false},\"updated_at\":{\"field\":\"updated_at\",\"_field_id\":\"12\",\"comment\":\"更新时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false}}', '2022-08-15 00:00:00', '2022-12-08 11:47:45');
INSERT INTO `wa_options` VALUES (8, 'dict_upload', '[{\"value\":\"1\",\"name\":\"分类1\"},{\"value\":\"2\",\"name\":\"分类2\"},{\"value\":\"3\",\"name\":\"分类3\"}]', '2022-12-04 16:24:13', '2022-12-04 16:24:13');
INSERT INTO `wa_options` VALUES (9, 'dict_sex', '[{\"value\":\"0\",\"name\":\"女\"},{\"value\":\"1\",\"name\":\"男\"}]', '2022-12-04 15:04:40', '2022-12-04 15:04:40');
INSERT INTO `wa_options` VALUES (10, 'dict_status', '[{\"value\":\"0\",\"name\":\"正常\"},{\"value\":\"1\",\"name\":\"禁用\"}]', '2022-12-04 15:05:09', '2022-12-04 15:05:09');
INSERT INTO `wa_options` VALUES (11, 'table_form_schema_wa_admin_roles', '{\"id\":{\"field\":\"id\",\"_field_id\":\"0\",\"comment\":\"主键\",\"control\":\"inputNumber\",\"control_args\":\"\",\"list_show\":true,\"enable_sort\":true,\"searchable\":true,\"search_type\":\"normal\",\"form_show\":false},\"role_id\":{\"field\":\"role_id\",\"_field_id\":\"1\",\"comment\":\"角色id\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"admin_id\":{\"field\":\"admin_id\",\"_field_id\":\"2\",\"comment\":\"管理员id\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false}}', '2022-08-15 00:00:00', '2022-12-20 19:42:51');
INSERT INTO `wa_options` VALUES (12, 'dict_dict_name', '[{\"value\":\"dict_name\",\"name\":\"字典名称\"},{\"value\":\"status\",\"name\":\"启禁用状态\"},{\"value\":\"sex\",\"name\":\"性别\"},{\"value\":\"upload\",\"name\":\"附件分类\"}]', '2022-08-15 00:00:00', '2022-12-20 19:42:51');
INSERT INTO `wa_options` VALUES (13, 'table_form_schema_wa_arctype', '{\"id\":{\"field\":\"id\",\"_field_id\":\"0\",\"comment\":\"主键\",\"control\":\"inputNumber\",\"control_args\":\"\",\"list_show\":true,\"enable_sort\":true,\"searchable\":true,\"search_type\":\"normal\",\"form_show\":false},\"created_at\":{\"field\":\"created_at\",\"_field_id\":\"1\",\"comment\":\"创建时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"list_show\":true,\"enable_sort\":true,\"searchable\":true,\"search_type\":\"between\",\"form_show\":false},\"updated_at\":{\"field\":\"updated_at\",\"_field_id\":\"2\",\"comment\":\"更新时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"list_show\":true,\"search_type\":\"between\",\"form_show\":false,\"enable_sort\":false,\"searchable\":false},\"channeltype\":{\"field\":\"channeltype\",\"_field_id\":\"3\",\"comment\":\"栏目顶级模型ID\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"current_channel\":{\"field\":\"current_channel\",\"_field_id\":\"4\",\"comment\":\"栏目当前模型ID\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"parent_id\":{\"field\":\"parent_id\",\"_field_id\":\"5\",\"comment\":\"栏目上级ID\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"topid\":{\"field\":\"topid\",\"_field_id\":\"6\",\"comment\":\"顶级栏目ID\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"typename\":{\"field\":\"typename\",\"_field_id\":\"7\",\"comment\":\"栏目名称\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"dirname\":{\"field\":\"dirname\",\"_field_id\":\"8\",\"comment\":\"目录英文名\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"dirpath\":{\"field\":\"dirpath\",\"_field_id\":\"9\",\"comment\":\"目录存放HTML路径\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"diy_dirpath\":{\"field\":\"diy_dirpath\",\"_field_id\":\"10\",\"comment\":\"列表静态文件存放规则\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"rulelist\":{\"field\":\"rulelist\",\"_field_id\":\"11\",\"comment\":\"列表静态文件存放规则\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"ruleview\":{\"field\":\"ruleview\",\"_field_id\":\"12\",\"comment\":\"文档静态文件存放规则\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"englist_name\":{\"field\":\"englist_name\",\"_field_id\":\"13\",\"comment\":\"栏目英文名\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"grade\":{\"field\":\"grade\",\"_field_id\":\"14\",\"comment\":\"栏目等级\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"typelink\":{\"field\":\"typelink\",\"_field_id\":\"15\",\"comment\":\"栏目链接\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"litpic\":{\"field\":\"litpic\",\"_field_id\":\"16\",\"comment\":\"栏目图片\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"templist\":{\"field\":\"templist\",\"_field_id\":\"17\",\"comment\":\"列表模板文件名\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"tempview\":{\"field\":\"tempview\",\"_field_id\":\"18\",\"comment\":\"文档模板文件名\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"seo_title\":{\"field\":\"seo_title\",\"_field_id\":\"19\",\"comment\":\"SEO标题\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"seo_keywords\":{\"field\":\"seo_keywords\",\"_field_id\":\"20\",\"comment\":\"seo关键字\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"seo_description\":{\"field\":\"seo_description\",\"_field_id\":\"21\",\"comment\":\"seo描述\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"sort_order\":{\"field\":\"sort_order\",\"_field_id\":\"22\",\"comment\":\"排序号\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"is_hidden\":{\"field\":\"is_hidden\",\"_field_id\":\"23\",\"comment\":\"是否隐藏栏目：0=显示，1=隐藏\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"is_part\":{\"field\":\"is_part\",\"_field_id\":\"24\",\"comment\":\"栏目属性：0=内容栏目，1=外部链接\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"admin_id\":{\"field\":\"admin_id\",\"_field_id\":\"25\",\"comment\":\"管理员ID\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"is_del\":{\"field\":\"is_del\",\"_field_id\":\"26\",\"comment\":\"伪删除，1=是，0=否\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"del_method\":{\"field\":\"del_method\",\"_field_id\":\"27\",\"comment\":\"伪删除状态，1为主动删除，2为跟随上级栏目被动删除\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"status\":{\"field\":\"status\",\"_field_id\":\"28\",\"comment\":\"启用 (1=正常，0=屏蔽)\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"is_release\":{\"field\":\"is_release\",\"_field_id\":\"29\",\"comment\":\"栏目是否应用于会员投稿发布，1是，0否\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"weapp_code\":{\"field\":\"weapp_code\",\"_field_id\":\"30\",\"comment\":\"插件栏目唯一标识\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"lang\":{\"field\":\"lang\",\"_field_id\":\"31\",\"comment\":\"语言标识\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"add_time\":{\"field\":\"add_time\",\"_field_id\":\"32\",\"comment\":\"新增时间\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"update_time\":{\"field\":\"update_time\",\"_field_id\":\"33\",\"comment\":\"更新时间\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"target\":{\"field\":\"target\",\"_field_id\":\"34\",\"comment\":\"新窗口打开\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"nofollow\":{\"field\":\"nofollow\",\"_field_id\":\"35\",\"comment\":\"防抓取\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"typearcrank\":{\"field\":\"typearcrank\",\"_field_id\":\"36\",\"comment\":\"阅读权限：0=开放浏览，-1=待审核稿件\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"empty_logic\":{\"field\":\"empty_logic\",\"_field_id\":\"37\",\"comment\":\"空内容逻辑\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"page_limit\":{\"field\":\"page_limit\",\"_field_id\":\"38\",\"comment\":\"限制页面 1-栏目页面 0-文档页面\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"total_arc\":{\"field\":\"total_arc\",\"_field_id\":\"39\",\"comment\":\"栏目下文档数量\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false}}', '2022-08-15 00:00:00', '2025-12-19 10:09:48');
INSERT INTO `wa_options` VALUES (14, 'table_form_schema_wa_channeltype', '{\"id\":{\"field\":\"id\",\"_field_id\":\"0\",\"comment\":\"主键\",\"control\":\"inputNumber\",\"control_args\":\"\",\"list_show\":true,\"enable_sort\":true,\"searchable\":true,\"search_type\":\"normal\",\"form_show\":false},\"created_at\":{\"field\":\"created_at\",\"_field_id\":\"1\",\"comment\":\"创建时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"list_show\":true,\"enable_sort\":true,\"searchable\":true,\"search_type\":\"between\",\"form_show\":false},\"updated_at\":{\"field\":\"updated_at\",\"_field_id\":\"2\",\"comment\":\"更新时间\",\"control\":\"dateTimePicker\",\"control_args\":\"\",\"list_show\":true,\"search_type\":\"between\",\"form_show\":false,\"enable_sort\":false,\"searchable\":false},\"nid\":{\"field\":\"nid\",\"_field_id\":\"3\",\"comment\":\"识别id\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"title\":{\"field\":\"title\",\"_field_id\":\"4\",\"comment\":\"名称\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"ntitle\":{\"field\":\"ntitle\",\"_field_id\":\"5\",\"comment\":\"左侧菜单名称\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"table\":{\"field\":\"table\",\"_field_id\":\"6\",\"comment\":\"表名\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"status\":{\"field\":\"status\",\"_field_id\":\"7\",\"comment\":\"状态(1=启用，0=屏蔽)\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"ifsystem\":{\"field\":\"ifsystem\",\"_field_id\":\"8\",\"comment\":\"字段分类，1=系统(不可修改)，0=自定义\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"is_repeat_title\":{\"field\":\"is_repeat_title\",\"_field_id\":\"9\",\"comment\":\"文档标题重复，1=允许，0=不允许\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"is_release\":{\"field\":\"is_release\",\"_field_id\":\"10\",\"comment\":\"模型是否允许应用于会员投稿发布，1是，0否\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"is_litpic_users_release\":{\"field\":\"is_litpic_users_release\",\"_field_id\":\"11\",\"comment\":\"缩略图是否应用于会员投稿，1=允许，0=不允许\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"data\":{\"field\":\"data\",\"_field_id\":\"12\",\"comment\":\"额外序列化存储数据\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"is_del\":{\"field\":\"is_del\",\"_field_id\":\"13\",\"comment\":\"伪删除，1=是，0=否\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"sort_order\":{\"field\":\"sort_order\",\"_field_id\":\"14\",\"comment\":\"排序\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"add_time\":{\"field\":\"add_time\",\"_field_id\":\"15\",\"comment\":\"新增时间\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"update_time\":{\"field\":\"update_time\",\"_field_id\":\"16\",\"comment\":\"更新时间\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false}}', '2022-08-15 00:00:00', '2025-12-23 10:44:10');
INSERT INTO `wa_options` VALUES (16, 'table_form_schema_wa_channelfield', '{\"id\":{\"field\":\"id\",\"_field_id\":\"0\",\"comment\":\"自增ID\",\"control\":\"inputNumber\",\"control_args\":\"\",\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"form_show\":false,\"enable_sort\":false},\"name\":{\"field\":\"name\",\"_field_id\":\"1\",\"comment\":\"字段名称\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"enable_sort\":false},\"channel_id\":{\"field\":\"channel_id\",\"_field_id\":\"2\",\"comment\":\"所属文档模型id\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"title\":{\"field\":\"title\",\"_field_id\":\"3\",\"comment\":\"字段标题\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"searchable\":true,\"search_type\":\"normal\",\"enable_sort\":false},\"dtype\":{\"field\":\"dtype\",\"_field_id\":\"4\",\"comment\":\"字段类型\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"define\":{\"field\":\"define\",\"_field_id\":\"5\",\"comment\":\"字段定义\",\"control\":\"textArea\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"maxlength\":{\"field\":\"maxlength\",\"_field_id\":\"6\",\"comment\":\"最大长度，文本数据必须填写，大于255为text类型\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"dfvalue\":{\"field\":\"dfvalue\",\"_field_id\":\"7\",\"comment\":\"默认值\",\"control\":\"textArea\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"dfvalue_unit\":{\"field\":\"dfvalue_unit\",\"_field_id\":\"8\",\"comment\":\"数值单位\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"remark\":{\"field\":\"remark\",\"_field_id\":\"9\",\"comment\":\"提示说明\",\"control\":\"input\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"is_screening\":{\"field\":\"is_screening\",\"_field_id\":\"10\",\"comment\":\"是否应用于条件筛选\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"is_release\":{\"field\":\"is_release\",\"_field_id\":\"11\",\"comment\":\"是否应用于会员投稿发布\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"ifeditable\":{\"field\":\"ifeditable\",\"_field_id\":\"12\",\"comment\":\"是否在编辑页显示\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"ifrequire\":{\"field\":\"ifrequire\",\"_field_id\":\"13\",\"comment\":\"是否必填\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"ifsystem\":{\"field\":\"ifsystem\",\"_field_id\":\"14\",\"comment\":\"字段分类，1=系统(不可修改)，0=自定义\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"ifmain\":{\"field\":\"ifmain\",\"_field_id\":\"15\",\"comment\":\"是否主表字段\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"ifcontrol\":{\"field\":\"ifcontrol\",\"_field_id\":\"16\",\"comment\":\"状态，控制该条数据是否允许被控制，1为不允许控制，0为允许控制\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"sort_order\":{\"field\":\"sort_order\",\"_field_id\":\"17\",\"comment\":\"排序\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"status\":{\"field\":\"status\",\"_field_id\":\"18\",\"comment\":\"状态\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"add_time\":{\"field\":\"add_time\",\"_field_id\":\"19\",\"comment\":\"创建时间\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"update_time\":{\"field\":\"update_time\",\"_field_id\":\"20\",\"comment\":\"更新时间\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false},\"set_type\":{\"field\":\"set_type\",\"_field_id\":\"21\",\"comment\":\"区域选择时使用是否为三级联动,1-是\",\"control\":\"inputNumber\",\"control_args\":\"\",\"form_show\":true,\"list_show\":true,\"search_type\":\"normal\",\"enable_sort\":false,\"searchable\":false}}', '2022-08-15 00:00:00', '2025-12-24 09:42:42');

-- ----------------------------
-- Table structure for wa_product_content
-- ----------------------------
DROP TABLE IF EXISTS `wa_product_content`;
CREATE TABLE `wa_product_content`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) NULL DEFAULT 0 COMMENT '文档ID',
  `content` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '内容详情',
  `content_ey_m` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '手机端内容详情',
  `add_time` int(11) NULL DEFAULT 0 COMMENT '新增时间',
  `update_time` int(11) NULL DEFAULT 0 COMMENT '更新时间',
  `fxrq` enum('2019年','2018年','2017年') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '2019年' COMMENT '发行日期',
  `jiawei` enum('0-1000','1000-1699','1700-2799','2800-3500','3500-10000') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0-1000' COMMENT '价位区段',
  `yanse` enum('银色','绿色','黑色','灰色') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '银色' COMMENT '机身颜色',
  `test` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '测试',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `news_id`(`aid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 19 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '产品附加表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_product_content
-- ----------------------------
INSERT INTO `wa_product_content` VALUES (2, 27, '&lt;p style=&quot;text-align:center&quot;&gt;&lt;img src=&quot;/uploads/ueditor/20210111/1-210111144620C3.jpg&quot; title=&quot;华为HUAWEI NOTE 8(图1)&quot; alt=&quot;华为HUAWEI NOTE 8(图1)&quot;/&gt;&lt;/p&gt;', '', 1610615791, 1610615791, '2018年', '1700-2799', '银色', '');
INSERT INTO `wa_product_content` VALUES (3, 28, '&lt;p&gt;轻薄全金属机身 / 256GB SSD / 第八代 Intel 酷睿i5 处理器 / FHD 全贴合屏幕 / 指纹解锁 / office激活不支持7天无理由退货&lt;/p&gt;&lt;p style=&quot;text-align: center;&quot;&gt;&lt;img style=&quot;max-width:100%!important;height:auto;&quot; src=&quot;/uploads/ueditor/20190114/aa0555d4f00163878c1d39ab046bb742.jpg&quot; title=&quot;小米笔记本Air 13.3(图1)&quot; alt=&quot;小米笔记本Air 13.3(图1)&quot;/&gt;&lt;/p&gt;', '', 1610615782, 1610615782, '2019年', '3500-10000', '黑色', '');
INSERT INTO `wa_product_content` VALUES (4, 29, '&lt;p style=&quot;text-align: center;&quot;&gt;特性	M3平板定制AKG品牌高保真耳机，配合M3平板享受HiFi音质&lt;/p&gt;&lt;p style=&quot;text-align: center;&quot;&gt;&lt;img src=&quot;/uploads/ueditor/20210111/1-210111144012118.jpg&quot; title=&quot; 小米蓝牙项圈耳机(图1)&quot; alt=&quot; 小米蓝牙项圈耳机(图1)&quot;/&gt;&lt;img src=&quot;/uploads/ueditor/20210111/1-21011114414R57.jpg&quot; title=&quot; 小米蓝牙项圈耳机(图2)&quot; alt=&quot; 小米蓝牙项圈耳机(图2)&quot;/&gt;&lt;/p&gt;', '', 1610615773, 1610615773, '2017年', '0-1000', '银色', '');
INSERT INTO `wa_product_content` VALUES (5, 37, '&lt;p&gt;全身都是科技亮点！7nm麒麟芯片，问鼎性能巅峰，4000万超广角徕卡三摄，随手捕捉大场面，支持25mm微距拍摄，解锁大波新题材，充电也有无线、反向玩法，快充之快刷新世界观。&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;&lt;p&gt;&lt;img style=&quot;max-width:100%!important;height:auto;&quot; src=&quot;/uploads/ueditor/20190319/e44a5110abdd18ef314e34769272bb44.jpg&quot; title=&quot;华为无线快充手机(图1)&quot; alt=&quot;华为无线快充手机(图1)&quot;/&gt;&lt;br/&gt;&lt;/p&gt;', '', 1610615751, 1610615751, '2019年', '2800-3500', '绿色', '');
INSERT INTO `wa_product_content` VALUES (10, 89, '&lt;p style=&quot;text-align: center;&quot;&gt;&lt;img src=&quot;/uploads/ueditor/20210111/1-210111143T0R0.png&quot; title=&quot;Apple iPhone 8 Plus (A1899) 64GB 深空灰色 移动联通4G手机(图1)&quot; alt=&quot;Apple iPhone 8 Plus (A1899) 64GB 深空灰色 移动联通4G手机(图1)&quot;/&gt;&lt;/p&gt;', '', 1610615667, 1610615667, '2017年', '1000-1699', '黑色', '');
INSERT INTO `wa_product_content` VALUES (11, 90, '&lt;p style=&quot;text-align: center;&quot;&gt;&lt;img src=&quot;/uploads/ueditor/20210111/1-21011114234VL.jpg&quot; title=&quot;小米8屏幕指纹版 6GB+128GB 黑色 全网通4G 双卡双待 全面屏拍照智能游戏手机(图1)&quot; alt=&quot;小米8屏幕指纹版 6GB+128GB 黑色 全网通4G 双卡双待 全面屏拍照智能游戏手机(图1)&quot;/&gt; &amp;nbsp; &amp;nbsp;&lt;img src=&quot;/uploads/ueditor/20210111/1-2101111424243S.jpg&quot; title=&quot;小米8屏幕指纹版 6GB+128GB 黑色 全网通4G 双卡双待 全面屏拍照智能游戏手机(图2)&quot; alt=&quot;小米8屏幕指纹版 6GB+128GB 黑色 全网通4G 双卡双待 全面屏拍照智能游戏手机(图2)&quot;/&gt;&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '', 1610615660, 1610615660, '2018年', '1700-2799', '黑色', '');
INSERT INTO `wa_product_content` VALUES (12, 98, '&lt;p&gt;&lt;img style=&quot;max-width:100%!important;height:auto;&quot; src=&quot;/uploads/ueditor/20190808/dea649468185da6603ff8ceb4dc0bd02.jpg&quot; title=&quot;MIIX520 二合一笔记本12.2英寸 i7(图1)&quot; alt=&quot;MIIX520 二合一笔记本12.2英寸 i7(图1)&quot;/&gt;&lt;/p&gt;', '', 1610615629, 1610615629, '2019年', '3500-10000', '黑色', '');
INSERT INTO `wa_product_content` VALUES (13, 99, '<p><img style=\"max-width: 100%!important; height: auto;\" title=\"MIIX 520 酷睿i5笔记本(图1)\" src=\"/uploads/ueditor/20190808/a008be68bc7b5743b93199d321bac0d6.jpg\" alt=\"MIIX 520 酷睿i5笔记本(图1)\" /></p>', '', 1767538730, 1767538730, '2018年', '3500-10000', '黑色', 'addnew');
INSERT INTO `wa_product_content` VALUES (14, 100, '&lt;p&gt;&lt;img style=&quot;max-width:100%!important;height:auto;&quot; src=&quot;/uploads/ueditor/20190808/847b3f2651ecfcf10a8efb17dbb8b27f.jpg&quot; title=&quot;小新 Air 超轻薄笔记本(图1)&quot; alt=&quot;小新 Air 超轻薄笔记本(图1)&quot;/&gt;&lt;/p&gt;', '', 1610615609, 1610615609, '2017年', '3500-10000', '银色', '');
INSERT INTO `wa_product_content` VALUES (15, 101, '&lt;p style=&quot;text-align: center;&quot;&gt;&lt;img style=&quot;max-width:100%!important;height:auto;&quot; src=&quot;/uploads/ueditor/20190808/cd0d121db4625c6b9ace794c921ac645.jpg&quot; title=&quot;联想 X1无线运动蓝牙耳机(图1)&quot; alt=&quot;联想 X1无线运动蓝牙耳机(图1)&quot;/&gt;&lt;/p&gt;', '', 1610615602, 1610615602, '2019年', '0-1000', '黑色', '');
INSERT INTO `wa_product_content` VALUES (16, 102, '<p style=\"text-align: center;\"><img style=\"max-width: 100%!important; height: auto;\" title=\"联想智能音箱MINI(图1)\" src=\"/uploads/ueditor/20190808/a7bc6e179f0ae1ff8499c3bb34a1a233.jpg\" alt=\"联想智能音箱MINI(图1)\" /></p>', '', 1767538750, 1767538750, '2018年', '0-1000', '绿色', 'addnew');
INSERT INTO `wa_product_content` VALUES (18, 107, '<p>反反复复</p>', '', 1767545990, 1767545990, '2019年', '0-1000', '银色', 'addnew');
INSERT INTO `wa_product_content` VALUES (17, 103, '<p style=\"text-align: center;\"><img style=\"max-width: 100%!important; height: auto;\" title=\"联想智能音箱G1(图1)\" src=\"/uploads/ueditor/20190808/aedbc2e4cd0257c38a1677f5825a3662.jpg\" alt=\"联想智能音箱G1(图1)\" /></p>', '', 1767546562, 1767546562, '2018年', '1000-1699', '绿色', 'addnew');

-- ----------------------------
-- Table structure for wa_recruit_content
-- ----------------------------
DROP TABLE IF EXISTS `wa_recruit_content`;
CREATE TABLE `wa_recruit_content`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) NULL DEFAULT 0 COMMENT '文档ID',
  `add_time` int(11) NULL DEFAULT 0 COMMENT '新增时间',
  `update_time` int(11) NULL DEFAULT 0 COMMENT '更新时间',
  `gzdd` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '工作地点',
  `xlyq` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '学历要求',
  `xzdy` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '薪资待遇',
  `gzxz` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '工作性质',
  `gznx` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '工作年限',
  `zprs` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '招聘人数',
  `nnxq` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '内容详情',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `aid`(`aid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 7 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '附加表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_recruit_content
-- ----------------------------

-- ----------------------------
-- Table structure for wa_region
-- ----------------------------
DROP TABLE IF EXISTS `wa_region`;
CREATE TABLE `wa_region`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '表id',
  `name` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '地区名称',
  `level` tinyint(4) NULL DEFAULT 0 COMMENT '地区等级 分省市县区',
  `parent_id` int(10) NULL DEFAULT 0 COMMENT '父id',
  `initial` varchar(5) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '首字母',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `parent_id`(`parent_id`) USING BTREE,
  INDEX `level`(`level`) USING BTREE,
  INDEX `initial`(`initial`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 47989 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '区域表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_region
-- ----------------------------
INSERT INTO `wa_region` VALUES (1, '北京市', 1, 0, 'B');
INSERT INTO `wa_region` VALUES (2, '北京市', 2, 1, 'B');
INSERT INTO `wa_region` VALUES (3, '东城区', 3, 2, 'D');
INSERT INTO `wa_region` VALUES (14, '西城区', 3, 2, 'X');
INSERT INTO `wa_region` VALUES (22, '崇文区', 3, 2, 'C');
INSERT INTO `wa_region` VALUES (30, '宣武区', 3, 2, 'X');
INSERT INTO `wa_region` VALUES (39, '朝阳区', 3, 2, 'C');
INSERT INTO `wa_region` VALUES (83, '丰台区', 3, 2, 'F');
INSERT INTO `wa_region` VALUES (105, '石景山区', 3, 2, 'S');
INSERT INTO `wa_region` VALUES (115, '海淀区', 3, 2, 'H');
INSERT INTO `wa_region` VALUES (145, '门头沟区', 3, 2, 'M');
INSERT INTO `wa_region` VALUES (159, '房山区', 3, 2, 'F');
INSERT INTO `wa_region` VALUES (188, '通州区', 3, 2, 'T');
INSERT INTO `wa_region` VALUES (204, '顺义区', 3, 2, 'S');
INSERT INTO `wa_region` VALUES (227, '昌平区', 3, 2, 'C');
INSERT INTO `wa_region` VALUES (245, '大兴区', 3, 2, 'D');
INSERT INTO `wa_region` VALUES (264, '怀柔区', 3, 2, 'H');
INSERT INTO `wa_region` VALUES (281, '平谷区', 3, 2, 'P');
INSERT INTO `wa_region` VALUES (301, '密云区', 3, 2, 'M');
INSERT INTO `wa_region` VALUES (322, '延庆区', 3, 2, 'Y');
INSERT INTO `wa_region` VALUES (338, '天津市', 1, 0, 'T');
INSERT INTO `wa_region` VALUES (339, '天津市', 2, 338, 'T');
INSERT INTO `wa_region` VALUES (340, '和平区', 3, 339, 'H');
INSERT INTO `wa_region` VALUES (347, '河东区', 3, 339, 'H');
INSERT INTO `wa_region` VALUES (361, '河西区', 3, 339, 'H');
INSERT INTO `wa_region` VALUES (375, '南开区', 3, 339, 'N');
INSERT INTO `wa_region` VALUES (388, '河北区', 3, 339, 'H');
INSERT INTO `wa_region` VALUES (399, '红桥区', 3, 339, 'H');
INSERT INTO `wa_region` VALUES (410, '塘沽区', 3, 339, 'T');
INSERT INTO `wa_region` VALUES (425, '汉沽区', 3, 339, 'H');
INSERT INTO `wa_region` VALUES (435, '大港区', 3, 339, 'D');
INSERT INTO `wa_region` VALUES (445, '东丽区', 3, 339, 'D');
INSERT INTO `wa_region` VALUES (460, '西青区', 3, 339, 'X');
INSERT INTO `wa_region` VALUES (473, '津南区', 3, 339, 'J');
INSERT INTO `wa_region` VALUES (488, '北辰区', 3, 339, 'B');
INSERT INTO `wa_region` VALUES (504, '武清区', 3, 339, 'W');
INSERT INTO `wa_region` VALUES (538, '宝坻区', 3, 339, 'B');
INSERT INTO `wa_region` VALUES (570, '宁河区', 3, 339, 'N');
INSERT INTO `wa_region` VALUES (586, '静海区', 3, 339, 'J');
INSERT INTO `wa_region` VALUES (608, '蓟州区', 3, 339, 'J');
INSERT INTO `wa_region` VALUES (636, '河北省', 1, 0, 'H');
INSERT INTO `wa_region` VALUES (637, '石家庄市', 2, 636, 'S');
INSERT INTO `wa_region` VALUES (638, '市辖区', 3, 637, 'S');
INSERT INTO `wa_region` VALUES (639, '长安区', 3, 637, 'C');
INSERT INTO `wa_region` VALUES (651, '桥东区', 3, 637, 'Q');
INSERT INTO `wa_region` VALUES (662, '桥西区', 3, 637, 'Q');
INSERT INTO `wa_region` VALUES (675, '新华区', 3, 637, 'X');
INSERT INTO `wa_region` VALUES (691, '井陉矿区', 3, 637, 'J');
INSERT INTO `wa_region` VALUES (697, '裕华区', 3, 637, 'Y');
INSERT INTO `wa_region` VALUES (708, '井陉县', 3, 637, 'J');
INSERT INTO `wa_region` VALUES (726, '正定县', 3, 637, 'Z');
INSERT INTO `wa_region` VALUES (736, '栾城县', 3, 637, 'L');
INSERT INTO `wa_region` VALUES (745, '行唐县', 3, 637, 'X');
INSERT INTO `wa_region` VALUES (761, '灵寿县', 3, 637, 'L');
INSERT INTO `wa_region` VALUES (777, '高邑县', 3, 637, 'G');
INSERT INTO `wa_region` VALUES (783, '深泽县', 3, 637, 'S');
INSERT INTO `wa_region` VALUES (790, '赞皇县', 3, 637, 'Z');
INSERT INTO `wa_region` VALUES (802, '无极县', 3, 637, 'W');
INSERT INTO `wa_region` VALUES (814, '平山县', 3, 637, 'P');
INSERT INTO `wa_region` VALUES (838, '元氏县', 3, 637, 'Y');
INSERT INTO `wa_region` VALUES (854, '赵县', 3, 637, 'Z');
INSERT INTO `wa_region` VALUES (866, '辛集市', 3, 637, 'X');
INSERT INTO `wa_region` VALUES (882, '藁城市', 3, 637, 'G');
INSERT INTO `wa_region` VALUES (898, '晋州市', 3, 637, 'J');
INSERT INTO `wa_region` VALUES (909, '新乐市', 3, 637, 'X');
INSERT INTO `wa_region` VALUES (922, '鹿泉市', 3, 637, 'L');
INSERT INTO `wa_region` VALUES (936, '唐山市', 2, 636, 'T');
INSERT INTO `wa_region` VALUES (937, '市辖区', 3, 936, 'S');
INSERT INTO `wa_region` VALUES (938, '路南区', 3, 936, 'L');
INSERT INTO `wa_region` VALUES (952, '路北区', 3, 936, 'L');
INSERT INTO `wa_region` VALUES (965, '古冶区', 3, 936, 'G');
INSERT INTO `wa_region` VALUES (977, '开平区', 3, 936, 'K');
INSERT INTO `wa_region` VALUES (989, '丰南区', 3, 936, 'F');
INSERT INTO `wa_region` VALUES (1007, '丰润区', 3, 936, 'F');
INSERT INTO `wa_region` VALUES (1034, '滦县', 3, 936, 'L');
INSERT INTO `wa_region` VALUES (1048, '滦南县', 3, 936, 'L');
INSERT INTO `wa_region` VALUES (1067, '乐亭县', 3, 936, 'L');
INSERT INTO `wa_region` VALUES (1085, '迁西县', 3, 936, 'Q');
INSERT INTO `wa_region` VALUES (1104, '玉田县', 3, 936, 'Y');
INSERT INTO `wa_region` VALUES (1125, '唐海县', 3, 936, 'T');
INSERT INTO `wa_region` VALUES (1140, '遵化市', 3, 936, 'Z');
INSERT INTO `wa_region` VALUES (1168, '迁安市', 3, 936, 'Q');
INSERT INTO `wa_region` VALUES (1188, '秦皇岛市', 2, 636, 'Q');
INSERT INTO `wa_region` VALUES (1189, '市辖区', 3, 1188, 'S');
INSERT INTO `wa_region` VALUES (1190, '海港区', 3, 1188, 'H');
INSERT INTO `wa_region` VALUES (1208, '山海关区', 3, 1188, 'S');
INSERT INTO `wa_region` VALUES (1218, '北戴河区', 3, 1188, 'B');
INSERT INTO `wa_region` VALUES (1223, '青龙县', 3, 1188, 'Q');
INSERT INTO `wa_region` VALUES (1249, '昌黎县', 3, 1188, 'C');
INSERT INTO `wa_region` VALUES (1266, '抚宁县', 3, 1188, 'F');
INSERT INTO `wa_region` VALUES (1278, '卢龙县', 3, 1188, 'L');
INSERT INTO `wa_region` VALUES (1291, '邯郸市', 2, 636, 'H');
INSERT INTO `wa_region` VALUES (1292, '市辖区', 3, 1291, 'S');
INSERT INTO `wa_region` VALUES (1293, '邯山区', 3, 1291, 'H');
INSERT INTO `wa_region` VALUES (1307, '丛台区', 3, 1291, 'C');
INSERT INTO `wa_region` VALUES (1319, '复兴区', 3, 1291, 'F');
INSERT INTO `wa_region` VALUES (1329, '峰峰矿区', 3, 1291, 'F');
INSERT INTO `wa_region` VALUES (1339, '邯郸县', 3, 1291, 'H');
INSERT INTO `wa_region` VALUES (1350, '临漳县', 3, 1291, 'L');
INSERT INTO `wa_region` VALUES (1365, '成安县', 3, 1291, 'C');
INSERT INTO `wa_region` VALUES (1375, '大名县', 3, 1291, 'D');
INSERT INTO `wa_region` VALUES (1396, '涉县', 3, 1291, 'S');
INSERT INTO `wa_region` VALUES (1414, '磁县', 3, 1291, 'C');
INSERT INTO `wa_region` VALUES (1434, '肥乡县', 3, 1291, 'F');
INSERT INTO `wa_region` VALUES (1444, '永年县', 3, 1291, 'Y');
INSERT INTO `wa_region` VALUES (1465, '邱县', 3, 1291, 'Q');
INSERT INTO `wa_region` VALUES (1473, '鸡泽县', 3, 1291, 'J');
INSERT INTO `wa_region` VALUES (1481, '广平县', 3, 1291, 'G');
INSERT INTO `wa_region` VALUES (1489, '馆陶县', 3, 1291, 'G');
INSERT INTO `wa_region` VALUES (1498, '魏县', 3, 1291, 'W');
INSERT INTO `wa_region` VALUES (1520, '曲周县', 3, 1291, 'Q');
INSERT INTO `wa_region` VALUES (1531, '武安市', 3, 1291, 'W');
INSERT INTO `wa_region` VALUES (1554, '邢台市', 2, 636, 'X');
INSERT INTO `wa_region` VALUES (1555, '市辖区', 3, 1554, 'S');
INSERT INTO `wa_region` VALUES (1556, '桥东区', 3, 1554, 'Q');
INSERT INTO `wa_region` VALUES (1564, '桥西区', 3, 1554, 'Q');
INSERT INTO `wa_region` VALUES (1572, '邢台县', 3, 1554, 'X');
INSERT INTO `wa_region` VALUES (1593, '临城县', 3, 1554, 'L');
INSERT INTO `wa_region` VALUES (1602, '内邱县', 3, 1554, 'N');
INSERT INTO `wa_region` VALUES (1612, '柏乡县', 3, 1554, 'B');
INSERT INTO `wa_region` VALUES (1619, '隆尧县', 3, 1554, 'L');
INSERT INTO `wa_region` VALUES (1633, '任县', 3, 1554, 'R');
INSERT INTO `wa_region` VALUES (1642, '南和县', 3, 1554, 'N');
INSERT INTO `wa_region` VALUES (1651, '宁晋县', 3, 1554, 'N');
INSERT INTO `wa_region` VALUES (1669, '巨鹿县', 3, 1554, 'J');
INSERT INTO `wa_region` VALUES (1680, '新河县', 3, 1554, 'X');
INSERT INTO `wa_region` VALUES (1687, '广宗县', 3, 1554, 'G');
INSERT INTO `wa_region` VALUES (1696, '平乡县', 3, 1554, 'P');
INSERT INTO `wa_region` VALUES (1704, '威县', 3, 1554, 'W');
INSERT INTO `wa_region` VALUES (1721, '清河县', 3, 1554, 'Q');
INSERT INTO `wa_region` VALUES (1728, '临西县', 3, 1554, 'L');
INSERT INTO `wa_region` VALUES (1738, '南宫市', 3, 1554, 'N');
INSERT INTO `wa_region` VALUES (1754, '沙河市', 3, 1554, 'S');
INSERT INTO `wa_region` VALUES (1772, '保定市', 2, 636, 'B');
INSERT INTO `wa_region` VALUES (1773, '市辖区', 3, 1772, 'S');
INSERT INTO `wa_region` VALUES (1774, '新市区', 3, 1772, 'X');
INSERT INTO `wa_region` VALUES (1787, '北市区', 3, 1772, 'B');
INSERT INTO `wa_region` VALUES (1796, '南市区', 3, 1772, 'N');
INSERT INTO `wa_region` VALUES (1806, '满城区', 3, 1772, 'M');
INSERT INTO `wa_region` VALUES (1820, '清苑区', 3, 1772, 'Q');
INSERT INTO `wa_region` VALUES (1839, '涞水县', 3, 1772, 'L');
INSERT INTO `wa_region` VALUES (1856, '阜平县', 3, 1772, 'F');
INSERT INTO `wa_region` VALUES (1870, '徐水区', 3, 1772, 'X');
INSERT INTO `wa_region` VALUES (1885, '定兴县', 3, 1772, 'D');
INSERT INTO `wa_region` VALUES (1902, '唐县', 3, 1772, 'T');
INSERT INTO `wa_region` VALUES (1923, '高阳县', 3, 1772, 'G');
INSERT INTO `wa_region` VALUES (1933, '容城县', 3, 1772, 'R');
INSERT INTO `wa_region` VALUES (1942, '涞源县', 3, 1772, 'L');
INSERT INTO `wa_region` VALUES (1960, '望都县', 3, 1772, 'W');
INSERT INTO `wa_region` VALUES (1969, '安新县', 3, 1772, 'A');
INSERT INTO `wa_region` VALUES (1982, '易县', 3, 1772, 'Y');
INSERT INTO `wa_region` VALUES (2010, '曲阳县', 3, 1772, 'Q');
INSERT INTO `wa_region` VALUES (2029, '蠡县', 3, 1772, 'L');
INSERT INTO `wa_region` VALUES (2043, '顺平县', 3, 1772, 'S');
INSERT INTO `wa_region` VALUES (2054, '博野县', 3, 1772, 'B');
INSERT INTO `wa_region` VALUES (2062, '雄县', 3, 1772, 'X');
INSERT INTO `wa_region` VALUES (2072, '涿州市', 3, 1772, 'Z');
INSERT INTO `wa_region` VALUES (2088, '定州市', 3, 1772, 'D');
INSERT INTO `wa_region` VALUES (2114, '安国市', 3, 1772, 'A');
INSERT INTO `wa_region` VALUES (2126, '高碑店市', 3, 1772, 'G');
INSERT INTO `wa_region` VALUES (2142, '张家口市', 2, 636, 'Z');
INSERT INTO `wa_region` VALUES (2143, '市辖区', 3, 2142, 'S');
INSERT INTO `wa_region` VALUES (2144, '桥东区', 3, 2142, 'Q');
INSERT INTO `wa_region` VALUES (2154, '桥西区', 3, 2142, 'Q');
INSERT INTO `wa_region` VALUES (2164, '宣化区', 3, 2142, 'X');
INSERT INTO `wa_region` VALUES (2176, '下花园区', 3, 2142, 'X');
INSERT INTO `wa_region` VALUES (2183, '宣化县', 3, 2142, 'X');
INSERT INTO `wa_region` VALUES (2198, '张北县', 3, 2142, 'Z');
INSERT INTO `wa_region` VALUES (2220, '康保县', 3, 2142, 'K');
INSERT INTO `wa_region` VALUES (2237, '沽源县', 3, 2142, 'G');
INSERT INTO `wa_region` VALUES (2256, '尚义县', 3, 2142, 'S');
INSERT INTO `wa_region` VALUES (2271, '蔚县', 3, 2142, 'W');
INSERT INTO `wa_region` VALUES (2294, '阳原县', 3, 2142, 'Y');
INSERT INTO `wa_region` VALUES (2309, '怀安县', 3, 2142, 'H');
INSERT INTO `wa_region` VALUES (2321, '万全县', 3, 2142, 'W');
INSERT INTO `wa_region` VALUES (2333, '怀来县', 3, 2142, 'H');
INSERT INTO `wa_region` VALUES (2351, '涿鹿县', 3, 2142, 'Z');
INSERT INTO `wa_region` VALUES (2369, '赤城县', 3, 2142, 'C');
INSERT INTO `wa_region` VALUES (2388, '崇礼县', 3, 2142, 'C');
INSERT INTO `wa_region` VALUES (2400, '承德市', 2, 636, 'C');
INSERT INTO `wa_region` VALUES (2401, '市辖区', 3, 2400, 'S');
INSERT INTO `wa_region` VALUES (2402, '双桥区', 3, 2400, 'S');
INSERT INTO `wa_region` VALUES (2415, '双滦区', 3, 2400, 'S');
INSERT INTO `wa_region` VALUES (2422, '鹰手营子矿区', 3, 2400, 'Y');
INSERT INTO `wa_region` VALUES (2427, '承德县', 3, 2400, 'C');
INSERT INTO `wa_region` VALUES (2453, '兴隆县', 3, 2400, 'X');
INSERT INTO `wa_region` VALUES (2474, '平泉县', 3, 2400, 'P');
INSERT INTO `wa_region` VALUES (2494, '滦平县', 3, 2400, 'L');
INSERT INTO `wa_region` VALUES (2517, '隆化县', 3, 2400, 'L');
INSERT INTO `wa_region` VALUES (2543, '丰宁县', 3, 2400, 'F');
INSERT INTO `wa_region` VALUES (2570, '宽城县', 3, 2400, 'K');
INSERT INTO `wa_region` VALUES (2589, '围场县', 3, 2400, 'W');
INSERT INTO `wa_region` VALUES (2629, '沧州市', 2, 636, 'C');
INSERT INTO `wa_region` VALUES (2630, '市辖区', 3, 2629, 'S');
INSERT INTO `wa_region` VALUES (2631, '新华区', 3, 2629, 'X');
INSERT INTO `wa_region` VALUES (2639, '运河区', 3, 2629, 'Y');
INSERT INTO `wa_region` VALUES (2648, '沧县', 3, 2629, 'C');
INSERT INTO `wa_region` VALUES (2668, '青县', 3, 2629, 'Q');
INSERT INTO `wa_region` VALUES (2680, '东光县', 3, 2629, 'D');
INSERT INTO `wa_region` VALUES (2690, '海兴县', 3, 2629, 'H');
INSERT INTO `wa_region` VALUES (2701, '盐山县', 3, 2629, 'Y');
INSERT INTO `wa_region` VALUES (2714, '肃宁县', 3, 2629, 'S');
INSERT INTO `wa_region` VALUES (2724, '南皮县', 3, 2629, 'N');
INSERT INTO `wa_region` VALUES (2734, '吴桥县', 3, 2629, 'W');
INSERT INTO `wa_region` VALUES (2745, '献县', 3, 2629, 'X');
INSERT INTO `wa_region` VALUES (2765, '孟村县', 3, 2629, 'M');
INSERT INTO `wa_region` VALUES (2772, '泊头市', 3, 2629, 'B');
INSERT INTO `wa_region` VALUES (2788, '任邱市', 3, 2629, 'R');
INSERT INTO `wa_region` VALUES (2809, '黄骅市', 3, 2629, 'H');
INSERT INTO `wa_region` VALUES (2828, '河间市', 3, 2629, 'H');
INSERT INTO `wa_region` VALUES (2849, '廊坊市', 2, 636, 'L');
INSERT INTO `wa_region` VALUES (2850, '市辖区', 3, 2849, 'S');
INSERT INTO `wa_region` VALUES (2851, '安次区', 3, 2849, 'A');
INSERT INTO `wa_region` VALUES (2862, '广阳区', 3, 2849, 'G');
INSERT INTO `wa_region` VALUES (2873, '固安县', 3, 2849, 'G');
INSERT INTO `wa_region` VALUES (2883, '永清县', 3, 2849, 'Y');
INSERT INTO `wa_region` VALUES (2895, '香河县', 3, 2849, 'X');
INSERT INTO `wa_region` VALUES (2906, '大城县', 3, 2849, 'D');
INSERT INTO `wa_region` VALUES (2918, '文安县', 3, 2849, 'W');
INSERT INTO `wa_region` VALUES (2932, '大厂县', 3, 2849, 'D');
INSERT INTO `wa_region` VALUES (2939, '霸州市', 3, 2849, 'B');
INSERT INTO `wa_region` VALUES (2953, '三河市', 3, 2849, 'S');
INSERT INTO `wa_region` VALUES (2968, '衡水市', 2, 636, 'H');
INSERT INTO `wa_region` VALUES (2969, '市辖区', 3, 2968, 'S');
INSERT INTO `wa_region` VALUES (2970, '桃城区', 3, 2968, 'T');
INSERT INTO `wa_region` VALUES (2983, '枣强县', 3, 2968, 'Z');
INSERT INTO `wa_region` VALUES (2995, '武邑县', 3, 2968, 'W');
INSERT INTO `wa_region` VALUES (3005, '武强县', 3, 2968, 'W');
INSERT INTO `wa_region` VALUES (3012, '饶阳县', 3, 2968, 'R');
INSERT INTO `wa_region` VALUES (3020, '安平县', 3, 2968, 'A');
INSERT INTO `wa_region` VALUES (3029, '故城县', 3, 2968, 'G');
INSERT INTO `wa_region` VALUES (3043, '景县', 3, 2968, 'J');
INSERT INTO `wa_region` VALUES (3060, '阜城县', 3, 2968, 'F');
INSERT INTO `wa_region` VALUES (3071, '冀州市', 3, 2968, 'J');
INSERT INTO `wa_region` VALUES (3083, '深州市', 3, 2968, 'S');
INSERT INTO `wa_region` VALUES (3102, '山西省', 1, 0, 'S');
INSERT INTO `wa_region` VALUES (3103, '太原市', 2, 3102, 'T');
INSERT INTO `wa_region` VALUES (3104, '市辖区', 3, 3103, 'S');
INSERT INTO `wa_region` VALUES (3105, '小店区(人口含高新经济区)', 3, 3103, 'X');
INSERT INTO `wa_region` VALUES (3117, '迎泽区', 3, 3103, 'Y');
INSERT INTO `wa_region` VALUES (3126, '杏花岭区', 3, 3103, 'X');
INSERT INTO `wa_region` VALUES (3140, '尖草坪区', 3, 3103, 'J');
INSERT INTO `wa_region` VALUES (3155, '万柏林区', 3, 3103, 'W');
INSERT INTO `wa_region` VALUES (3171, '晋源区', 3, 3103, 'J');
INSERT INTO `wa_region` VALUES (3178, '清徐县', 3, 3103, 'Q');
INSERT INTO `wa_region` VALUES (3188, '阳曲县', 3, 3103, 'Y');
INSERT INTO `wa_region` VALUES (3200, '娄烦县', 3, 3103, 'L');
INSERT INTO `wa_region` VALUES (3209, '古交市', 3, 3103, 'G');
INSERT INTO `wa_region` VALUES (3224, '大同市', 2, 3102, 'D');
INSERT INTO `wa_region` VALUES (3225, '市辖区', 3, 3224, 'S');
INSERT INTO `wa_region` VALUES (3226, '大同市城区', 3, 3224, 'D');
INSERT INTO `wa_region` VALUES (3241, '矿区', 3, 3224, 'K');
INSERT INTO `wa_region` VALUES (3266, '南郊区', 3, 3224, 'N');
INSERT INTO `wa_region` VALUES (3277, '新荣区', 3, 3224, 'X');
INSERT INTO `wa_region` VALUES (3286, '阳高县', 3, 3224, 'Y');
INSERT INTO `wa_region` VALUES (3300, '天镇县', 3, 3224, 'T');
INSERT INTO `wa_region` VALUES (3312, '广灵县', 3, 3224, 'G');
INSERT INTO `wa_region` VALUES (3322, '灵丘县', 3, 3224, 'L');
INSERT INTO `wa_region` VALUES (3335, '浑源县', 3, 3224, 'H');
INSERT INTO `wa_region` VALUES (3354, '左云县', 3, 3224, 'Z');
INSERT INTO `wa_region` VALUES (3364, '大同县', 3, 3224, 'D');
INSERT INTO `wa_region` VALUES (3379, '阳泉市', 2, 3102, 'Y');
INSERT INTO `wa_region` VALUES (3380, '市辖区', 3, 3379, 'S');
INSERT INTO `wa_region` VALUES (3381, '城区', 3, 3379, 'C');
INSERT INTO `wa_region` VALUES (3388, '矿区', 3, 3379, 'K');
INSERT INTO `wa_region` VALUES (3395, '郊区', 3, 3379, 'J');
INSERT INTO `wa_region` VALUES (3405, '平定县', 3, 3379, 'P');
INSERT INTO `wa_region` VALUES (3416, '盂县', 3, 3379, 'Y');
INSERT INTO `wa_region` VALUES (3431, '长治市', 2, 3102, 'C');
INSERT INTO `wa_region` VALUES (3432, '市辖区', 3, 3431, 'S');
INSERT INTO `wa_region` VALUES (3433, '长治市城区', 3, 3431, 'C');
INSERT INTO `wa_region` VALUES (3445, '长治市郊区', 3, 3431, 'C');
INSERT INTO `wa_region` VALUES (3454, '长治县', 3, 3431, 'C');
INSERT INTO `wa_region` VALUES (3466, '襄垣县', 3, 3431, 'X');
INSERT INTO `wa_region` VALUES (3478, '屯留县', 3, 3431, 'T');
INSERT INTO `wa_region` VALUES (3493, '平顺县', 3, 3431, 'P');
INSERT INTO `wa_region` VALUES (3506, '黎城县', 3, 3431, 'L');
INSERT INTO `wa_region` VALUES (3516, '壶关县', 3, 3431, 'H');
INSERT INTO `wa_region` VALUES (3530, '长子县', 3, 3431, 'C');
INSERT INTO `wa_region` VALUES (3543, '武乡县', 3, 3431, 'W');
INSERT INTO `wa_region` VALUES (3558, '沁县', 3, 3431, 'Q');
INSERT INTO `wa_region` VALUES (3572, '沁源县', 3, 3431, 'Q');
INSERT INTO `wa_region` VALUES (3587, '潞城市', 3, 3431, 'L');
INSERT INTO `wa_region` VALUES (3597, '晋城市', 2, 3102, 'J');
INSERT INTO `wa_region` VALUES (3598, '市辖区', 3, 3597, 'S');
INSERT INTO `wa_region` VALUES (3599, '晋城市城区', 3, 3597, 'J');
INSERT INTO `wa_region` VALUES (3608, '沁水县', 3, 3597, 'Q');
INSERT INTO `wa_region` VALUES (3623, '阳城县', 3, 3597, 'Y');
INSERT INTO `wa_region` VALUES (3642, '陵川县', 3, 3597, 'L');
INSERT INTO `wa_region` VALUES (3655, '泽州县', 3, 3597, 'Z');
INSERT INTO `wa_region` VALUES (3673, '高平市', 3, 3597, 'G');
INSERT INTO `wa_region` VALUES (3690, '朔州市', 2, 3102, 'S');
INSERT INTO `wa_region` VALUES (3691, '市辖区', 3, 3690, 'S');
INSERT INTO `wa_region` VALUES (3692, '朔城区', 3, 3690, 'S');
INSERT INTO `wa_region` VALUES (3709, '平鲁区', 3, 3690, 'P');
INSERT INTO `wa_region` VALUES (3723, '山阴县', 3, 3690, 'S');
INSERT INTO `wa_region` VALUES (3739, '应县', 3, 3690, 'Y');
INSERT INTO `wa_region` VALUES (3752, '右玉县', 3, 3690, 'Y');
INSERT INTO `wa_region` VALUES (3763, '怀仁县', 3, 3690, 'H');
INSERT INTO `wa_region` VALUES (3776, '晋中市', 2, 3102, 'J');
INSERT INTO `wa_region` VALUES (3777, '市辖区', 3, 3776, 'S');
INSERT INTO `wa_region` VALUES (3778, '榆次区', 3, 3776, 'Y');
INSERT INTO `wa_region` VALUES (3799, '榆社县', 3, 3776, 'Y');
INSERT INTO `wa_region` VALUES (3810, '左权县', 3, 3776, 'Z');
INSERT INTO `wa_region` VALUES (3822, '和顺县', 3, 3776, 'H');
INSERT INTO `wa_region` VALUES (3833, '昔阳县', 3, 3776, 'X');
INSERT INTO `wa_region` VALUES (3846, '寿阳县', 3, 3776, 'S');
INSERT INTO `wa_region` VALUES (3861, '太谷县', 3, 3776, 'T');
INSERT INTO `wa_region` VALUES (3871, '祁县', 3, 3776, 'Q');
INSERT INTO `wa_region` VALUES (3880, '平遥县', 3, 3776, 'P');
INSERT INTO `wa_region` VALUES (3895, '灵石县', 3, 3776, 'L');
INSERT INTO `wa_region` VALUES (3908, '介休市', 3, 3776, 'J');
INSERT INTO `wa_region` VALUES (3925, '运城市', 2, 3102, 'Y');
INSERT INTO `wa_region` VALUES (3926, '市辖区', 3, 3925, 'S');
INSERT INTO `wa_region` VALUES (3927, '盐湖区', 3, 3925, 'Y');
INSERT INTO `wa_region` VALUES (3950, '临猗县', 3, 3925, 'L');
INSERT INTO `wa_region` VALUES (3967, '万荣县', 3, 3925, 'W');
INSERT INTO `wa_region` VALUES (3982, '闻喜县', 3, 3925, 'W');
INSERT INTO `wa_region` VALUES (3996, '稷山县', 3, 3925, 'J');
INSERT INTO `wa_region` VALUES (4004, '新绛县', 3, 3925, 'X');
INSERT INTO `wa_region` VALUES (4013, '绛县', 3, 3925, 'J');
INSERT INTO `wa_region` VALUES (4024, '垣曲县', 3, 3925, 'Y');
INSERT INTO `wa_region` VALUES (4036, '夏县', 3, 3925, 'X');
INSERT INTO `wa_region` VALUES (4048, '平陆县', 3, 3925, 'P');
INSERT INTO `wa_region` VALUES (4059, '芮城县', 3, 3925, 'R');
INSERT INTO `wa_region` VALUES (4070, '永济市', 3, 3925, 'Y');
INSERT INTO `wa_region` VALUES (4082, '河津市', 3, 3925, 'H');
INSERT INTO `wa_region` VALUES (4093, '忻州市', 2, 3102, 'X');
INSERT INTO `wa_region` VALUES (4094, '市辖区', 3, 4093, 'S');
INSERT INTO `wa_region` VALUES (4095, '忻府区', 3, 4093, 'X');
INSERT INTO `wa_region` VALUES (4116, '定襄县', 3, 4093, 'D');
INSERT INTO `wa_region` VALUES (4126, '五台县', 3, 4093, 'W');
INSERT INTO `wa_region` VALUES (4146, '代县', 3, 4093, 'D');
INSERT INTO `wa_region` VALUES (4158, '繁峙县', 3, 4093, 'F');
INSERT INTO `wa_region` VALUES (4172, '宁武县', 3, 4093, 'N');
INSERT INTO `wa_region` VALUES (4189, '静乐县', 3, 4093, 'J');
INSERT INTO `wa_region` VALUES (4204, '神池县', 3, 4093, 'S');
INSERT INTO `wa_region` VALUES (4215, '五寨县', 3, 4093, 'W');
INSERT INTO `wa_region` VALUES (4228, '岢岚县', 3, 4093, 'K');
INSERT INTO `wa_region` VALUES (4241, '河曲县', 3, 4093, 'H');
INSERT INTO `wa_region` VALUES (4255, '保德县', 3, 4093, 'B');
INSERT INTO `wa_region` VALUES (4269, '偏关县', 3, 4093, 'P');
INSERT INTO `wa_region` VALUES (4280, '原平市', 3, 4093, 'Y');
INSERT INTO `wa_region` VALUES (4304, '临汾市', 2, 3102, 'L');
INSERT INTO `wa_region` VALUES (4305, '市辖区', 3, 4304, 'S');
INSERT INTO `wa_region` VALUES (4306, '尧都区', 3, 4304, 'Y');
INSERT INTO `wa_region` VALUES (4333, '曲沃县', 3, 4304, 'Q');
INSERT INTO `wa_region` VALUES (4341, '翼城县', 3, 4304, 'Y');
INSERT INTO `wa_region` VALUES (4352, '襄汾县', 3, 4304, 'X');
INSERT INTO `wa_region` VALUES (4366, '洪洞县', 3, 4304, 'H');
INSERT INTO `wa_region` VALUES (4383, '古县', 3, 4304, 'G');
INSERT INTO `wa_region` VALUES (4391, '安泽县', 3, 4304, 'A');
INSERT INTO `wa_region` VALUES (4399, '浮山县', 3, 4304, 'F');
INSERT INTO `wa_region` VALUES (4409, '吉县', 3, 4304, 'J');
INSERT INTO `wa_region` VALUES (4418, '乡宁县', 3, 4304, 'X');
INSERT INTO `wa_region` VALUES (4429, '大宁县', 3, 4304, 'D');
INSERT INTO `wa_region` VALUES (4436, '隰县', 3, 4304, 'X');
INSERT INTO `wa_region` VALUES (4445, '永和县', 3, 4304, 'Y');
INSERT INTO `wa_region` VALUES (4453, '蒲县', 3, 4304, 'P');
INSERT INTO `wa_region` VALUES (4463, '汾西县', 3, 4304, 'F');
INSERT INTO `wa_region` VALUES (4472, '侯马市', 3, 4304, 'H');
INSERT INTO `wa_region` VALUES (4481, '霍州市', 3, 4304, 'H');
INSERT INTO `wa_region` VALUES (4494, '吕梁市', 2, 3102, 'L');
INSERT INTO `wa_region` VALUES (4495, '市辖区', 3, 4494, 'S');
INSERT INTO `wa_region` VALUES (4496, '离石区', 3, 4494, 'L');
INSERT INTO `wa_region` VALUES (4509, '文水县', 3, 4494, 'W');
INSERT INTO `wa_region` VALUES (4522, '交城县', 3, 4494, 'J');
INSERT INTO `wa_region` VALUES (4533, '兴县', 3, 4494, 'X');
INSERT INTO `wa_region` VALUES (4551, '临县', 3, 4494, 'L');
INSERT INTO `wa_region` VALUES (4575, '柳林县', 3, 4494, 'L');
INSERT INTO `wa_region` VALUES (4591, '石楼县', 3, 4494, 'S');
INSERT INTO `wa_region` VALUES (4601, '岚县', 3, 4494, 'L');
INSERT INTO `wa_region` VALUES (4614, '方山县', 3, 4494, 'F');
INSERT INTO `wa_region` VALUES (4622, '中阳县', 3, 4494, 'Z');
INSERT INTO `wa_region` VALUES (4630, '交口县', 3, 4494, 'J');
INSERT INTO `wa_region` VALUES (4638, '孝义市', 3, 4494, 'X');
INSERT INTO `wa_region` VALUES (4655, '汾阳市', 3, 4494, 'F');
INSERT INTO `wa_region` VALUES (4670, '内蒙古自治区', 1, 0, 'N');
INSERT INTO `wa_region` VALUES (4671, '呼和浩特市', 2, 4670, 'H');
INSERT INTO `wa_region` VALUES (4672, '市辖区', 3, 4671, 'S');
INSERT INTO `wa_region` VALUES (4673, '新城区', 3, 4671, 'X');
INSERT INTO `wa_region` VALUES (4684, '回民区', 3, 4671, 'H');
INSERT INTO `wa_region` VALUES (4693, '玉泉区', 3, 4671, 'Y');
INSERT INTO `wa_region` VALUES (4702, '赛罕区', 3, 4671, 'S');
INSERT INTO `wa_region` VALUES (4715, '土左旗', 3, 4671, 'T');
INSERT INTO `wa_region` VALUES (4727, '托克托县', 3, 4671, 'T');
INSERT INTO `wa_region` VALUES (4733, '和林格尔县', 3, 4671, 'H');
INSERT INTO `wa_region` VALUES (4742, '清水河县', 3, 4671, 'Q');
INSERT INTO `wa_region` VALUES (4749, '武川县', 3, 4671, 'W');
INSERT INTO `wa_region` VALUES (4759, '包头市', 2, 4670, 'B');
INSERT INTO `wa_region` VALUES (4760, '市辖区', 3, 4759, 'S');
INSERT INTO `wa_region` VALUES (4761, '东河区', 3, 4759, 'D');
INSERT INTO `wa_region` VALUES (4775, '昆都仑区', 3, 4759, 'K');
INSERT INTO `wa_region` VALUES (4791, '青山区', 3, 4759, 'Q');
INSERT INTO `wa_region` VALUES (4803, '石拐区', 3, 4759, 'S');
INSERT INTO `wa_region` VALUES (4810, '白云鄂博矿区', 3, 4759, 'B');
INSERT INTO `wa_region` VALUES (4813, '九原区', 3, 4759, 'J');
INSERT INTO `wa_region` VALUES (4823, '土默特右旗', 3, 4759, 'T');
INSERT INTO `wa_region` VALUES (4833, '固阳县', 3, 4759, 'G');
INSERT INTO `wa_region` VALUES (4840, '达茂联合旗', 3, 4759, 'D');
INSERT INTO `wa_region` VALUES (4849, '乌海市', 2, 4670, 'W');
INSERT INTO `wa_region` VALUES (4850, '乌海市辖区', 3, 4849, 'W');
INSERT INTO `wa_region` VALUES (4851, '海勃湾区', 3, 4849, 'H');
INSERT INTO `wa_region` VALUES (4859, '海南区', 3, 4849, 'H');
INSERT INTO `wa_region` VALUES (4865, '乌达区', 3, 4849, 'W');
INSERT INTO `wa_region` VALUES (4874, '赤峰市', 2, 4670, 'C');
INSERT INTO `wa_region` VALUES (4875, '市辖区', 3, 4874, 'S');
INSERT INTO `wa_region` VALUES (4876, '红山区', 3, 4874, 'H');
INSERT INTO `wa_region` VALUES (4888, '元宝山区', 3, 4874, 'Y');
INSERT INTO `wa_region` VALUES (4896, '松山区', 3, 4874, 'S');
INSERT INTO `wa_region` VALUES (4919, '阿鲁科尔沁旗', 3, 4874, 'A');
INSERT INTO `wa_region` VALUES (4932, '巴林左旗', 3, 4874, 'B');
INSERT INTO `wa_region` VALUES (4944, '巴林右旗', 3, 4874, 'B');
INSERT INTO `wa_region` VALUES (4953, '林西县', 3, 4874, 'L');
INSERT INTO `wa_region` VALUES (4963, '克什克腾旗', 3, 4874, 'K');
INSERT INTO `wa_region` VALUES (4975, '翁牛特旗', 3, 4874, 'W');
INSERT INTO `wa_region` VALUES (4988, '喀喇沁旗', 3, 4874, 'K');
INSERT INTO `wa_region` VALUES (4999, '宁城县', 3, 4874, 'N');
INSERT INTO `wa_region` VALUES (5013, '敖汉旗', 3, 4874, 'A');
INSERT INTO `wa_region` VALUES (5029, '通辽市', 2, 4670, 'T');
INSERT INTO `wa_region` VALUES (5030, '市辖区', 3, 5029, 'S');
INSERT INTO `wa_region` VALUES (5031, '科尔沁区', 3, 5029, 'K');
INSERT INTO `wa_region` VALUES (5062, '科尔沁左翼中旗', 3, 5029, 'K');
INSERT INTO `wa_region` VALUES (5079, '科左后旗', 3, 5029, 'K');
INSERT INTO `wa_region` VALUES (5104, '开鲁县', 3, 5029, 'K');
INSERT INTO `wa_region` VALUES (5118, '库伦旗', 3, 5029, 'K');
INSERT INTO `wa_region` VALUES (5125, '奈曼旗', 3, 5029, 'N');
INSERT INTO `wa_region` VALUES (5139, '扎鲁特旗', 3, 5029, 'Z');
INSERT INTO `wa_region` VALUES (5155, '霍林郭勒市', 3, 5029, 'H');
INSERT INTO `wa_region` VALUES (5162, '鄂尔多斯市', 2, 4670, 'E');
INSERT INTO `wa_region` VALUES (5163, '东胜区', 3, 5162, 'D');
INSERT INTO `wa_region` VALUES (5176, '达拉特旗', 3, 5162, 'D');
INSERT INTO `wa_region` VALUES (5185, '准格尔旗', 3, 5162, 'Z');
INSERT INTO `wa_region` VALUES (5195, '鄂托克前旗', 3, 5162, 'E');
INSERT INTO `wa_region` VALUES (5201, '鄂托克旗', 3, 5162, 'E');
INSERT INTO `wa_region` VALUES (5210, '杭锦旗', 3, 5162, 'H');
INSERT INTO `wa_region` VALUES (5219, '乌审旗', 3, 5162, 'W');
INSERT INTO `wa_region` VALUES (5228, '伊金霍洛旗', 3, 5162, 'Y');
INSERT INTO `wa_region` VALUES (5236, '呼伦贝尔市', 2, 4670, 'H');
INSERT INTO `wa_region` VALUES (5237, '市辖区', 3, 5236, 'S');
INSERT INTO `wa_region` VALUES (5238, '海拉尔区', 3, 5236, 'H');
INSERT INTO `wa_region` VALUES (5249, '阿荣旗', 3, 5236, 'A');
INSERT INTO `wa_region` VALUES (5262, '莫力达瓦旗', 3, 5236, 'M');
INSERT INTO `wa_region` VALUES (5277, '鄂伦春旗', 3, 5236, 'E');
INSERT INTO `wa_region` VALUES (5303, '鄂温旗', 3, 5236, 'E');
INSERT INTO `wa_region` VALUES (5314, '陈巴尔虎旗镇', 3, 5236, 'C');
INSERT INTO `wa_region` VALUES (5323, '新巴尔虎左旗', 3, 5236, 'X');
INSERT INTO `wa_region` VALUES (5330, '新巴尔虎右旗', 3, 5236, 'X');
INSERT INTO `wa_region` VALUES (5337, '满洲里市', 3, 5236, 'M');
INSERT INTO `wa_region` VALUES (5354, '牙克石市', 3, 5236, 'Y');
INSERT INTO `wa_region` VALUES (5371, '扎兰屯市', 3, 5236, 'Z');
INSERT INTO `wa_region` VALUES (5397, '额尔古纳市', 3, 5236, 'E');
INSERT INTO `wa_region` VALUES (5410, '根河市', 3, 5236, 'G');
INSERT INTO `wa_region` VALUES (5418, '巴彦淖尔市', 2, 4670, 'B');
INSERT INTO `wa_region` VALUES (5419, '市辖区', 3, 5418, 'S');
INSERT INTO `wa_region` VALUES (5420, '临河区', 3, 5418, 'L');
INSERT INTO `wa_region` VALUES (5440, '五原县', 3, 5418, 'W');
INSERT INTO `wa_region` VALUES (5450, '磴口县', 3, 5418, 'D');
INSERT INTO `wa_region` VALUES (5461, '乌拉特前旗', 3, 5418, 'W');
INSERT INTO `wa_region` VALUES (5477, '乌拉特中旗', 3, 5418, 'W');
INSERT INTO `wa_region` VALUES (5489, '乌拉特后旗', 3, 5418, 'W');
INSERT INTO `wa_region` VALUES (5495, '杭锦后旗', 3, 5418, 'H');
INSERT INTO `wa_region` VALUES (5505, '乌兰察布市', 2, 4670, 'W');
INSERT INTO `wa_region` VALUES (5506, '市辖区', 3, 5505, 'S');
INSERT INTO `wa_region` VALUES (5507, '集宁区', 3, 5505, 'J');
INSERT INTO `wa_region` VALUES (5518, '卓资县', 3, 5505, 'Z');
INSERT INTO `wa_region` VALUES (5526, '化德县', 3, 5505, 'H');
INSERT INTO `wa_region` VALUES (5532, '商都县', 3, 5505, 'S');
INSERT INTO `wa_region` VALUES (5542, '兴和县', 3, 5505, 'X');
INSERT INTO `wa_region` VALUES (5551, '凉城县', 3, 5505, 'L');
INSERT INTO `wa_region` VALUES (5562, '察哈尔右翼前旗', 3, 5505, 'C');
INSERT INTO `wa_region` VALUES (5571, '察右中旗', 3, 5505, 'C');
INSERT INTO `wa_region` VALUES (5582, '察哈尔右翼后旗', 3, 5505, 'C');
INSERT INTO `wa_region` VALUES (5590, '四子王旗', 3, 5505, 'S');
INSERT INTO `wa_region` VALUES (5603, '丰镇市', 3, 5505, 'F');
INSERT INTO `wa_region` VALUES (5616, '兴安盟', 2, 4670, 'X');
INSERT INTO `wa_region` VALUES (5617, '乌兰浩特市', 3, 5616, 'W');
INSERT INTO `wa_region` VALUES (5629, '阿尔山市', 3, 5616, 'A');
INSERT INTO `wa_region` VALUES (5636, '科右前旗', 3, 5616, 'K');
INSERT INTO `wa_region` VALUES (5655, '科右中旗', 3, 5616, 'K');
INSERT INTO `wa_region` VALUES (5677, '扎赉特旗', 3, 5616, 'Z');
INSERT INTO `wa_region` VALUES (5692, '突泉县', 3, 5616, 'T');
INSERT INTO `wa_region` VALUES (5702, '锡林郭勒盟', 2, 4670, 'X');
INSERT INTO `wa_region` VALUES (5703, '二连浩特市', 3, 5702, 'E');
INSERT INTO `wa_region` VALUES (5709, '锡林浩特市', 3, 5702, 'X');
INSERT INTO `wa_region` VALUES (5723, '阿巴嘎旗', 3, 5702, 'A');
INSERT INTO `wa_region` VALUES (5731, '苏尼特左旗', 3, 5702, 'S');
INSERT INTO `wa_region` VALUES (5738, '苏尼特右旗', 3, 5702, 'S');
INSERT INTO `wa_region` VALUES (5745, '东乌珠穆沁旗', 3, 5702, 'D');
INSERT INTO `wa_region` VALUES (5758, '西乌珠穆沁旗', 3, 5702, 'X');
INSERT INTO `wa_region` VALUES (5766, '太仆寺旗', 3, 5702, 'T');
INSERT INTO `wa_region` VALUES (5774, '镶黄旗', 3, 5702, 'X');
INSERT INTO `wa_region` VALUES (5778, '正镶白旗', 3, 5702, 'Z');
INSERT INTO `wa_region` VALUES (5785, '正蓝旗', 3, 5702, 'Z');
INSERT INTO `wa_region` VALUES (5794, '多伦县', 3, 5702, 'D');
INSERT INTO `wa_region` VALUES (5799, '阿拉善盟', 2, 4670, 'A');
INSERT INTO `wa_region` VALUES (5800, '阿拉善左旗', 3, 5799, 'A');
INSERT INTO `wa_region` VALUES (5814, '阿拉善右旗', 3, 5799, 'A');
INSERT INTO `wa_region` VALUES (5820, '额济纳旗', 3, 5799, 'E');
INSERT INTO `wa_region` VALUES (5827, '辽宁省', 1, 0, 'L');
INSERT INTO `wa_region` VALUES (5828, '沈阳市', 2, 5827, 'S');
INSERT INTO `wa_region` VALUES (5829, '市辖区', 3, 5828, 'S');
INSERT INTO `wa_region` VALUES (5830, '和平区', 3, 5828, 'H');
INSERT INTO `wa_region` VALUES (5848, '沈河区', 3, 5828, 'S');
INSERT INTO `wa_region` VALUES (5859, '大东区', 3, 5828, 'D');
INSERT INTO `wa_region` VALUES (5873, '皇姑区', 3, 5828, 'H');
INSERT INTO `wa_region` VALUES (5894, '铁西区', 3, 5828, 'T');
INSERT INTO `wa_region` VALUES (5909, '苏家屯区', 3, 5828, 'S');
INSERT INTO `wa_region` VALUES (5934, '东陵区', 3, 5828, 'D');
INSERT INTO `wa_region` VALUES (5954, '新城子区', 3, 5828, 'X');
INSERT INTO `wa_region` VALUES (5975, '于洪区', 3, 5828, 'Y');
INSERT INTO `wa_region` VALUES (5998, '辽中县', 3, 5828, 'L');
INSERT INTO `wa_region` VALUES (6020, '康平县', 3, 5828, 'K');
INSERT INTO `wa_region` VALUES (6038, '法库县', 3, 5828, 'F');
INSERT INTO `wa_region` VALUES (6058, '新民市', 3, 5828, 'X');
INSERT INTO `wa_region` VALUES (6088, '大连市', 2, 5827, 'D');
INSERT INTO `wa_region` VALUES (6089, '市辖区', 3, 6088, 'S');
INSERT INTO `wa_region` VALUES (6090, '中山区', 3, 6088, 'Z');
INSERT INTO `wa_region` VALUES (6099, '西岗区', 3, 6088, 'X');
INSERT INTO `wa_region` VALUES (6107, '沙河口区', 3, 6088, 'S');
INSERT INTO `wa_region` VALUES (6117, '甘井子区', 3, 6088, 'G');
INSERT INTO `wa_region` VALUES (6137, '旅顺口区', 3, 6088, 'L');
INSERT INTO `wa_region` VALUES (6153, '金州区', 3, 6088, 'J');
INSERT INTO `wa_region` VALUES (6178, '长海县', 3, 6088, 'C');
INSERT INTO `wa_region` VALUES (6184, '瓦房店市', 3, 6088, 'W');
INSERT INTO `wa_region` VALUES (6217, '普兰店市', 3, 6088, 'P');
INSERT INTO `wa_region` VALUES (6239, '庄河市', 3, 6088, 'Z');
INSERT INTO `wa_region` VALUES (6266, '鞍山市', 2, 5827, 'A');
INSERT INTO `wa_region` VALUES (6267, '市辖区', 3, 6266, 'S');
INSERT INTO `wa_region` VALUES (6268, '铁东区', 3, 6266, 'T');
INSERT INTO `wa_region` VALUES (6282, '铁西区', 3, 6266, 'T');
INSERT INTO `wa_region` VALUES (6293, '立山区', 3, 6266, 'L');
INSERT INTO `wa_region` VALUES (6303, '千山区', 3, 6266, 'Q');
INSERT INTO `wa_region` VALUES (6316, '台安县', 3, 6266, 'T');
INSERT INTO `wa_region` VALUES (6331, '岫岩县', 3, 6266, 'X');
INSERT INTO `wa_region` VALUES (6354, '海城市', 3, 6266, 'H');
INSERT INTO `wa_region` VALUES (6384, '抚顺市', 2, 5827, 'F');
INSERT INTO `wa_region` VALUES (6385, '市辖区', 3, 6384, 'S');
INSERT INTO `wa_region` VALUES (6386, '新抚区', 3, 6384, 'X');
INSERT INTO `wa_region` VALUES (6395, '东洲区', 3, 6384, 'D');
INSERT INTO `wa_region` VALUES (6409, '望花区', 3, 6384, 'W');
INSERT INTO `wa_region` VALUES (6422, '顺城区', 3, 6384, 'S');
INSERT INTO `wa_region` VALUES (6432, '抚顺县', 3, 6384, 'F');
INSERT INTO `wa_region` VALUES (6445, '新宾县', 3, 6384, 'X');
INSERT INTO `wa_region` VALUES (6461, '清原县', 3, 6384, 'Q');
INSERT INTO `wa_region` VALUES (6476, '本溪市', 2, 5827, 'B');
INSERT INTO `wa_region` VALUES (6477, '市辖区', 3, 6476, 'S');
INSERT INTO `wa_region` VALUES (6478, '平山区', 3, 6476, 'P');
INSERT INTO `wa_region` VALUES (6488, '溪湖区', 3, 6476, 'X');
INSERT INTO `wa_region` VALUES (6499, '明山区', 3, 6476, 'M');
INSERT INTO `wa_region` VALUES (6509, '南芬区', 3, 6476, 'N');
INSERT INTO `wa_region` VALUES (6515, '本溪县', 3, 6476, 'B');
INSERT INTO `wa_region` VALUES (6528, '桓仁县', 3, 6476, 'H');
INSERT INTO `wa_region` VALUES (6542, '丹东市', 2, 5827, 'D');
INSERT INTO `wa_region` VALUES (6543, '市辖区', 3, 6542, 'S');
INSERT INTO `wa_region` VALUES (6544, '元宝区', 3, 6542, 'Y');
INSERT INTO `wa_region` VALUES (6552, '振兴区', 3, 6542, 'Z');
INSERT INTO `wa_region` VALUES (6563, '振安区', 3, 6542, 'Z');
INSERT INTO `wa_region` VALUES (6573, '宽甸县', 3, 6542, 'K');
INSERT INTO `wa_region` VALUES (6596, '东港市', 3, 6542, 'D');
INSERT INTO `wa_region` VALUES (6621, '凤城市', 3, 6542, 'F');
INSERT INTO `wa_region` VALUES (6643, '锦州市', 2, 5827, 'J');
INSERT INTO `wa_region` VALUES (6644, '市辖区', 3, 6643, 'S');
INSERT INTO `wa_region` VALUES (6645, '古塔区', 3, 6643, 'G');
INSERT INTO `wa_region` VALUES (6655, '凌河区', 3, 6643, 'L');
INSERT INTO `wa_region` VALUES (6668, '太和区', 3, 6643, 'T');
INSERT INTO `wa_region` VALUES (6683, '黑山县', 3, 6643, 'H');
INSERT INTO `wa_region` VALUES (6706, '义县', 3, 6643, 'Y');
INSERT INTO `wa_region` VALUES (6725, '凌海市', 3, 6643, 'L');
INSERT INTO `wa_region` VALUES (6750, '北镇市', 3, 6643, 'B');
INSERT INTO `wa_region` VALUES (6771, '营口市', 2, 5827, 'Y');
INSERT INTO `wa_region` VALUES (6772, '市辖区', 3, 6771, 'S');
INSERT INTO `wa_region` VALUES (6773, '站前区', 3, 6771, 'Z');
INSERT INTO `wa_region` VALUES (6781, '西市区', 3, 6771, 'X');
INSERT INTO `wa_region` VALUES (6789, '鲅鱼圈区', 3, 6771, 'B');
INSERT INTO `wa_region` VALUES (6797, '老边区', 3, 6771, 'L');
INSERT INTO `wa_region` VALUES (6804, '盖州市', 3, 6771, 'G');
INSERT INTO `wa_region` VALUES (6832, '大石桥市', 3, 6771, 'D');
INSERT INTO `wa_region` VALUES (6851, '阜新市', 2, 5827, 'F');
INSERT INTO `wa_region` VALUES (6852, '市辖区', 3, 6851, 'S');
INSERT INTO `wa_region` VALUES (6853, '海州区', 3, 6851, 'H');
INSERT INTO `wa_region` VALUES (6865, '新邱区', 3, 6851, 'X');
INSERT INTO `wa_region` VALUES (6871, '太平区', 3, 6851, 'T');
INSERT INTO `wa_region` VALUES (6878, '清河门区', 3, 6851, 'Q');
INSERT INTO `wa_region` VALUES (6885, '细河区', 3, 6851, 'X');
INSERT INTO `wa_region` VALUES (6893, '阜新县', 3, 6851, 'F');
INSERT INTO `wa_region` VALUES (6930, '彰武县', 3, 6851, 'Z');
INSERT INTO `wa_region` VALUES (6955, '辽阳市', 2, 5827, 'L');
INSERT INTO `wa_region` VALUES (6956, '市辖区', 3, 6955, 'S');
INSERT INTO `wa_region` VALUES (6957, '白塔区', 3, 6955, 'B');
INSERT INTO `wa_region` VALUES (6964, '文圣区', 3, 6955, 'W');
INSERT INTO `wa_region` VALUES (6971, '宏伟区', 3, 6955, 'H');
INSERT INTO `wa_region` VALUES (6977, '弓长岭区', 3, 6955, 'G');
INSERT INTO `wa_region` VALUES (6983, '太子河区', 3, 6955, 'T');
INSERT INTO `wa_region` VALUES (6989, '辽阳县', 3, 6955, 'L');
INSERT INTO `wa_region` VALUES (7007, '灯塔市', 3, 6955, 'D');
INSERT INTO `wa_region` VALUES (7024, '盘锦市', 2, 5827, 'P');
INSERT INTO `wa_region` VALUES (7025, '市辖区', 3, 7024, 'S');
INSERT INTO `wa_region` VALUES (7026, '双台子区', 3, 7024, 'S');
INSERT INTO `wa_region` VALUES (7036, '兴隆台区', 3, 7024, 'X');
INSERT INTO `wa_region` VALUES (7055, '大洼县', 3, 7024, 'D');
INSERT INTO `wa_region` VALUES (7072, '盘山县', 3, 7024, 'P');
INSERT INTO `wa_region` VALUES (7088, '铁岭市', 2, 5827, 'T');
INSERT INTO `wa_region` VALUES (7089, '市辖区', 3, 7088, 'S');
INSERT INTO `wa_region` VALUES (7090, '银州区', 3, 7088, 'Y');
INSERT INTO `wa_region` VALUES (7099, '清河区', 3, 7088, 'Q');
INSERT INTO `wa_region` VALUES (7105, '铁岭县', 3, 7088, 'T');
INSERT INTO `wa_region` VALUES (7121, '西丰县', 3, 7088, 'X');
INSERT INTO `wa_region` VALUES (7140, '昌图县', 3, 7088, 'C');
INSERT INTO `wa_region` VALUES (7180, '调兵山市', 3, 7088, 'D');
INSERT INTO `wa_region` VALUES (7186, '开原市', 3, 7088, 'K');
INSERT INTO `wa_region` VALUES (7208, '朝阳市', 2, 5827, 'C');
INSERT INTO `wa_region` VALUES (7209, '市辖区', 3, 7208, 'S');
INSERT INTO `wa_region` VALUES (7210, '双塔区', 3, 7208, 'S');
INSERT INTO `wa_region` VALUES (7225, '龙城区', 3, 7208, 'L');
INSERT INTO `wa_region` VALUES (7238, '朝阳县', 3, 7208, 'C');
INSERT INTO `wa_region` VALUES (7267, '建平县', 3, 7208, 'J');
INSERT INTO `wa_region` VALUES (7299, '喀喇沁左翼县', 3, 7208, 'K');
INSERT INTO `wa_region` VALUES (7322, '北票市', 3, 7208, 'B');
INSERT INTO `wa_region` VALUES (7360, '凌源市', 3, 7208, 'L');
INSERT INTO `wa_region` VALUES (7391, '葫芦岛市', 2, 5827, 'H');
INSERT INTO `wa_region` VALUES (7392, '市辖区', 3, 7391, 'S');
INSERT INTO `wa_region` VALUES (7393, '连山区', 3, 7391, 'L');
INSERT INTO `wa_region` VALUES (7419, '龙港区', 3, 7391, 'L');
INSERT INTO `wa_region` VALUES (7433, '南票区', 3, 7391, 'N');
INSERT INTO `wa_region` VALUES (7446, '绥中县', 3, 7391, 'S');
INSERT INTO `wa_region` VALUES (7474, '建昌县', 3, 7391, 'J');
INSERT INTO `wa_region` VALUES (7503, '兴城市', 3, 7391, 'X');
INSERT INTO `wa_region` VALUES (7531, '吉林省', 1, 0, 'J');
INSERT INTO `wa_region` VALUES (7532, '长春市', 2, 7531, 'C');
INSERT INTO `wa_region` VALUES (7533, '长春市辖区', 3, 7532, 'C');
INSERT INTO `wa_region` VALUES (7534, '南关区', 3, 7532, 'N');
INSERT INTO `wa_region` VALUES (7552, '宽城区', 3, 7532, 'K');
INSERT INTO `wa_region` VALUES (7569, '朝阳区', 3, 7532, 'C');
INSERT INTO `wa_region` VALUES (7582, '二道区', 3, 7532, 'E');
INSERT INTO `wa_region` VALUES (7597, '绿园区', 3, 7532, 'L');
INSERT INTO `wa_region` VALUES (7610, '双阳区', 3, 7532, 'S');
INSERT INTO `wa_region` VALUES (7619, '农安县', 3, 7532, 'N');
INSERT INTO `wa_region` VALUES (7642, '九台市', 3, 7532, 'J');
INSERT INTO `wa_region` VALUES (7658, '榆树市', 3, 7532, 'Y');
INSERT INTO `wa_region` VALUES (7687, '德惠市', 3, 7532, 'D');
INSERT INTO `wa_region` VALUES (7706, '吉林市', 2, 7531, 'J');
INSERT INTO `wa_region` VALUES (7707, '吉林市辖区', 3, 7706, 'J');
INSERT INTO `wa_region` VALUES (7708, '昌邑区', 3, 7706, 'C');
INSERT INTO `wa_region` VALUES (7731, '龙潭区', 3, 7706, 'L');
INSERT INTO `wa_region` VALUES (7752, '船营区', 3, 7706, 'C');
INSERT INTO `wa_region` VALUES (7768, '丰满区', 3, 7706, 'F');
INSERT INTO `wa_region` VALUES (7781, '永吉县', 3, 7706, 'Y');
INSERT INTO `wa_region` VALUES (7792, '蛟河市', 3, 7706, 'J');
INSERT INTO `wa_region` VALUES (7810, '桦甸市', 3, 7706, 'H');
INSERT INTO `wa_region` VALUES (7828, '舒兰市', 3, 7706, 'S');
INSERT INTO `wa_region` VALUES (7849, '磐石市', 3, 7706, 'P');
INSERT INTO `wa_region` VALUES (7868, '四平市', 2, 7531, 'S');
INSERT INTO `wa_region` VALUES (7869, '四平市辖区', 3, 7868, 'S');
INSERT INTO `wa_region` VALUES (7870, '铁西区', 3, 7868, 'T');
INSERT INTO `wa_region` VALUES (7878, '铁东区', 3, 7868, 'T');
INSERT INTO `wa_region` VALUES (7892, '梨树县', 3, 7868, 'L');
INSERT INTO `wa_region` VALUES (7916, '伊通县', 3, 7868, 'Y');
INSERT INTO `wa_region` VALUES (7933, '公主岭市', 3, 7868, 'G');
INSERT INTO `wa_region` VALUES (7964, '双辽市', 3, 7868, 'S');
INSERT INTO `wa_region` VALUES (7986, '辽源市', 2, 7531, 'L');
INSERT INTO `wa_region` VALUES (7987, '辽源市辖区', 3, 7986, 'L');
INSERT INTO `wa_region` VALUES (7988, '龙山区', 3, 7986, 'L');
INSERT INTO `wa_region` VALUES (8000, '西安区', 3, 7986, 'X');
INSERT INTO `wa_region` VALUES (8008, '东丰县', 3, 7986, 'D');
INSERT INTO `wa_region` VALUES (8023, '东辽县', 3, 7986, 'D');
INSERT INTO `wa_region` VALUES (8037, '通化市', 2, 7531, 'T');
INSERT INTO `wa_region` VALUES (8038, '通化市辖区', 3, 8037, 'T');
INSERT INTO `wa_region` VALUES (8039, '东昌区', 3, 8037, 'D');
INSERT INTO `wa_region` VALUES (8051, '二道江区', 3, 8037, 'E');
INSERT INTO `wa_region` VALUES (8058, '通化县', 3, 8037, 'T');
INSERT INTO `wa_region` VALUES (8076, '辉南县', 3, 8037, 'H');
INSERT INTO `wa_region` VALUES (8088, '柳河县', 3, 8037, 'L');
INSERT INTO `wa_region` VALUES (8104, '梅河口市', 3, 8037, 'M');
INSERT INTO `wa_region` VALUES (8129, '集安市', 3, 8037, 'J');
INSERT INTO `wa_region` VALUES (8144, '白山市', 2, 7531, 'B');
INSERT INTO `wa_region` VALUES (8145, '白山市辖区', 3, 8144, 'B');
INSERT INTO `wa_region` VALUES (8146, '八道江区', 3, 8144, 'B');
INSERT INTO `wa_region` VALUES (8159, '江源区', 3, 8144, 'J');
INSERT INTO `wa_region` VALUES (8168, '抚松县', 3, 8144, 'F');
INSERT INTO `wa_region` VALUES (8183, '靖宇县', 3, 8144, 'J');
INSERT INTO `wa_region` VALUES (8192, '长白县', 3, 8144, 'C');
INSERT INTO `wa_region` VALUES (8202, '临江市', 3, 8144, 'L');
INSERT INTO `wa_region` VALUES (8216, '松原市', 2, 7531, 'S');
INSERT INTO `wa_region` VALUES (8217, '松原市辖区', 3, 8216, 'S');
INSERT INTO `wa_region` VALUES (8218, '宁江区', 3, 8216, 'N');
INSERT INTO `wa_region` VALUES (8239, '前郭县', 3, 8216, 'Q');
INSERT INTO `wa_region` VALUES (8266, '长岭县', 3, 8216, 'C');
INSERT INTO `wa_region` VALUES (8300, '乾安县', 3, 8216, 'Q');
INSERT INTO `wa_region` VALUES (8311, '扶余县', 3, 8216, 'F');
INSERT INTO `wa_region` VALUES (8333, '白城市', 2, 7531, 'B');
INSERT INTO `wa_region` VALUES (8334, '白城市辖区', 3, 8333, 'B');
INSERT INTO `wa_region` VALUES (8335, '洮北区', 3, 8333, 'T');
INSERT INTO `wa_region` VALUES (8362, '镇赉县', 3, 8333, 'Z');
INSERT INTO `wa_region` VALUES (8375, '通榆县', 3, 8333, 'T');
INSERT INTO `wa_region` VALUES (8393, '洮南市', 3, 8333, 'T');
INSERT INTO `wa_region` VALUES (8420, '大安市', 3, 8333, 'D');
INSERT INTO `wa_region` VALUES (8445, '延边州', 2, 7531, 'Y');
INSERT INTO `wa_region` VALUES (8446, '延吉市', 3, 8445, 'Y');
INSERT INTO `wa_region` VALUES (8456, '图们市', 3, 8445, 'T');
INSERT INTO `wa_region` VALUES (8464, '敦化市', 3, 8445, 'D');
INSERT INTO `wa_region` VALUES (8489, '珲春市', 3, 8445, 'H');
INSERT INTO `wa_region` VALUES (8504, '龙井市', 3, 8445, 'L');
INSERT INTO `wa_region` VALUES (8515, '和龙市', 3, 8445, 'H');
INSERT INTO `wa_region` VALUES (8530, '汪清县', 3, 8445, 'W');
INSERT INTO `wa_region` VALUES (8545, '安图县', 3, 8445, 'A');
INSERT INTO `wa_region` VALUES (8558, '黑龙江省', 1, 0, 'H');
INSERT INTO `wa_region` VALUES (8559, '哈尔滨市', 2, 8558, 'H');
INSERT INTO `wa_region` VALUES (8560, '市辖区', 3, 8559, 'S');
INSERT INTO `wa_region` VALUES (8561, '道里区', 3, 8559, 'D');
INSERT INTO `wa_region` VALUES (8585, '南岗区', 3, 8559, 'N');
INSERT INTO `wa_region` VALUES (8606, '道外区', 3, 8559, 'D');
INSERT INTO `wa_region` VALUES (8633, '平房区', 3, 8559, 'P');
INSERT INTO `wa_region` VALUES (8642, '松北区', 3, 8559, 'S');
INSERT INTO `wa_region` VALUES (8650, '香坊区', 3, 8559, 'X');
INSERT INTO `wa_region` VALUES (8676, '呼兰区', 3, 8559, 'H');
INSERT INTO `wa_region` VALUES (8694, '阿城区', 3, 8559, 'A');
INSERT INTO `wa_region` VALUES (8714, '依兰县', 3, 8559, 'Y');
INSERT INTO `wa_region` VALUES (8729, '方正县', 3, 8559, 'F');
INSERT INTO `wa_region` VALUES (8740, '宾县', 3, 8559, 'B');
INSERT INTO `wa_region` VALUES (8758, '巴彦县', 3, 8559, 'B');
INSERT INTO `wa_region` VALUES (8778, '木兰县', 3, 8559, 'M');
INSERT INTO `wa_region` VALUES (8788, '通河县', 3, 8559, 'T');
INSERT INTO `wa_region` VALUES (8800, '延寿县', 3, 8559, 'Y');
INSERT INTO `wa_region` VALUES (8812, '双城市', 3, 8559, 'S');
INSERT INTO `wa_region` VALUES (8838, '尚志市', 3, 8559, 'S');
INSERT INTO `wa_region` VALUES (8858, '五常市', 3, 8559, 'W');
INSERT INTO `wa_region` VALUES (8884, '齐齐哈尔市', 2, 8558, 'Q');
INSERT INTO `wa_region` VALUES (8885, '市辖区', 3, 8884, 'S');
INSERT INTO `wa_region` VALUES (8886, '龙沙区', 3, 8884, 'L');
INSERT INTO `wa_region` VALUES (8894, '建华区', 3, 8884, 'J');
INSERT INTO `wa_region` VALUES (8901, '铁锋区', 3, 8884, 'T');
INSERT INTO `wa_region` VALUES (8911, '昂昂溪区', 3, 8884, 'A');
INSERT INTO `wa_region` VALUES (8918, '富拉尔基区', 3, 8884, 'F');
INSERT INTO `wa_region` VALUES (8928, '碾子山区', 3, 8884, 'N');
INSERT INTO `wa_region` VALUES (8934, '梅里斯达斡尔族区', 3, 8884, 'M');
INSERT INTO `wa_region` VALUES (8943, '龙江县', 3, 8884, 'L');
INSERT INTO `wa_region` VALUES (8958, '依安县', 3, 8884, 'Y');
INSERT INTO `wa_region` VALUES (8976, '泰来县', 3, 8884, 'T');
INSERT INTO `wa_region` VALUES (8998, '甘南县', 3, 8884, 'G');
INSERT INTO `wa_region` VALUES (9011, '富裕县', 3, 8884, 'F');
INSERT INTO `wa_region` VALUES (9024, '克山县', 3, 8884, 'K');
INSERT INTO `wa_region` VALUES (9048, '克东县', 3, 8884, 'K');
INSERT INTO `wa_region` VALUES (9067, '拜泉县', 3, 8884, 'B');
INSERT INTO `wa_region` VALUES (9084, '讷河市', 3, 8884, 'N');
INSERT INTO `wa_region` VALUES (9117, '鸡西市', 2, 8558, 'J');
INSERT INTO `wa_region` VALUES (9118, '市辖区', 3, 9117, 'S');
INSERT INTO `wa_region` VALUES (9119, '鸡冠区', 3, 9117, 'J');
INSERT INTO `wa_region` VALUES (9129, '恒山区', 3, 9117, 'H');
INSERT INTO `wa_region` VALUES (9139, '滴道区', 3, 9117, 'D');
INSERT INTO `wa_region` VALUES (9146, '梨树区', 3, 9117, 'L');
INSERT INTO `wa_region` VALUES (9153, '城子河区', 3, 9117, 'C');
INSERT INTO `wa_region` VALUES (9161, '麻山区', 3, 9117, 'M');
INSERT INTO `wa_region` VALUES (9164, '鸡东县', 3, 9117, 'J');
INSERT INTO `wa_region` VALUES (9178, '虎林市', 3, 9117, 'H');
INSERT INTO `wa_region` VALUES (9199, '密山市', 3, 9117, 'M');
INSERT INTO `wa_region` VALUES (9222, '鹤岗市', 2, 8558, 'H');
INSERT INTO `wa_region` VALUES (9223, '市辖区', 3, 9222, 'S');
INSERT INTO `wa_region` VALUES (9224, '向阳区', 3, 9222, 'X');
INSERT INTO `wa_region` VALUES (9230, '工农区', 3, 9222, 'G');
INSERT INTO `wa_region` VALUES (9237, '南山区', 3, 9222, 'N');
INSERT INTO `wa_region` VALUES (9244, '兴安区', 3, 9222, 'X');
INSERT INTO `wa_region` VALUES (9250, '东山区', 3, 9222, 'D');
INSERT INTO `wa_region` VALUES (9261, '兴山区', 3, 9222, 'X');
INSERT INTO `wa_region` VALUES (9266, '萝北县', 3, 9222, 'L');
INSERT INTO `wa_region` VALUES (9283, '绥滨县', 3, 9222, 'S');
INSERT INTO `wa_region` VALUES (9296, '双鸭山市', 2, 8558, 'S');
INSERT INTO `wa_region` VALUES (9297, '市辖区', 3, 9296, 'S');
INSERT INTO `wa_region` VALUES (9298, '尖山区', 3, 9296, 'J');
INSERT INTO `wa_region` VALUES (9307, '岭东区', 3, 9296, 'L');
INSERT INTO `wa_region` VALUES (9317, '四方台区', 3, 9296, 'S');
INSERT INTO `wa_region` VALUES (9323, '宝山区', 3, 9296, 'B');
INSERT INTO `wa_region` VALUES (9335, '集贤县', 3, 9296, 'J');
INSERT INTO `wa_region` VALUES (9356, '友谊县', 3, 9296, 'Y');
INSERT INTO `wa_region` VALUES (9370, '宝清县', 3, 9296, 'B');
INSERT INTO `wa_region` VALUES (9393, '饶河县', 3, 9296, 'R');
INSERT INTO `wa_region` VALUES (9419, '大庆市', 2, 8558, 'D');
INSERT INTO `wa_region` VALUES (9420, '市辖区', 3, 9419, 'S');
INSERT INTO `wa_region` VALUES (9421, '萨尔图区', 3, 9419, 'S');
INSERT INTO `wa_region` VALUES (9431, '龙凤区', 3, 9419, 'L');
INSERT INTO `wa_region` VALUES (9440, '让胡路区', 3, 9419, 'R');
INSERT INTO `wa_region` VALUES (9451, '红岗区', 3, 9419, 'H');
INSERT INTO `wa_region` VALUES (9458, '大同区', 3, 9419, 'D');
INSERT INTO `wa_region` VALUES (9474, '肇州县', 3, 9419, 'Z');
INSERT INTO `wa_region` VALUES (9489, '肇源县', 3, 9419, 'Z');
INSERT INTO `wa_region` VALUES (9514, '林甸县', 3, 9419, 'L');
INSERT INTO `wa_region` VALUES (9527, '杜尔伯特县', 3, 9419, 'D');
INSERT INTO `wa_region` VALUES (9553, '伊春市', 2, 8558, 'Y');
INSERT INTO `wa_region` VALUES (9554, '市辖区', 3, 9553, 'S');
INSERT INTO `wa_region` VALUES (9555, '伊春区', 3, 9553, 'Y');
INSERT INTO `wa_region` VALUES (9561, '南岔区', 3, 9553, 'N');
INSERT INTO `wa_region` VALUES (9581, '友好区', 3, 9553, 'Y');
INSERT INTO `wa_region` VALUES (9599, '西林区', 3, 9553, 'X');
INSERT INTO `wa_region` VALUES (9603, '翠峦区', 3, 9553, 'C');
INSERT INTO `wa_region` VALUES (9614, '新青区', 3, 9553, 'X');
INSERT INTO `wa_region` VALUES (9631, '美溪区', 3, 9553, 'M');
INSERT INTO `wa_region` VALUES (9647, '金山屯区', 3, 9553, 'J');
INSERT INTO `wa_region` VALUES (9660, '五营区', 3, 9553, 'W');
INSERT INTO `wa_region` VALUES (9671, '乌马河区', 3, 9553, 'W');
INSERT INTO `wa_region` VALUES (9685, '汤旺河区', 3, 9553, 'T');
INSERT INTO `wa_region` VALUES (9702, '带岭区', 3, 9553, 'D');
INSERT INTO `wa_region` VALUES (9715, '乌伊岭区', 3, 9553, 'W');
INSERT INTO `wa_region` VALUES (9729, '红星区', 3, 9553, 'H');
INSERT INTO `wa_region` VALUES (9742, '上甘岭区', 3, 9553, 'S');
INSERT INTO `wa_region` VALUES (9756, '嘉荫县', 3, 9553, 'J');
INSERT INTO `wa_region` VALUES (9772, '铁力市', 3, 9553, 'T');
INSERT INTO `wa_region` VALUES (9785, '佳木斯市', 2, 8558, 'J');
INSERT INTO `wa_region` VALUES (9786, '市辖区', 3, 9785, 'S');
INSERT INTO `wa_region` VALUES (9787, '向阳区', 3, 9785, 'X');
INSERT INTO `wa_region` VALUES (9795, '前进区', 3, 9785, 'Q');
INSERT INTO `wa_region` VALUES (9802, '东风区', 3, 9785, 'D');
INSERT INTO `wa_region` VALUES (9810, '郊区', 3, 9785, 'J');
INSERT INTO `wa_region` VALUES (9825, '桦南县', 3, 9785, 'H');
INSERT INTO `wa_region` VALUES (9839, '桦川县', 3, 9785, 'H');
INSERT INTO `wa_region` VALUES (9851, '汤原县', 3, 9785, 'T');
INSERT INTO `wa_region` VALUES (9866, '抚远县', 3, 9785, 'F');
INSERT INTO `wa_region` VALUES (9879, '同江市', 3, 9785, 'T');
INSERT INTO `wa_region` VALUES (9907, '富锦市', 3, 9785, 'F');
INSERT INTO `wa_region` VALUES (9930, '七台河市', 2, 8558, 'Q');
INSERT INTO `wa_region` VALUES (9931, '市辖区', 3, 9930, 'S');
INSERT INTO `wa_region` VALUES (9932, '新兴区', 3, 9930, 'X');
INSERT INTO `wa_region` VALUES (9944, '桃山区', 3, 9930, 'T');
INSERT INTO `wa_region` VALUES (9952, '茄子河区', 3, 9930, 'Q');
INSERT INTO `wa_region` VALUES (9962, '勃利县', 3, 9930, 'B');
INSERT INTO `wa_region` VALUES (9981, '牡丹江市', 2, 8558, 'M');
INSERT INTO `wa_region` VALUES (9982, '市辖区', 3, 9981, 'S');
INSERT INTO `wa_region` VALUES (9983, '东安区', 3, 9981, 'D');
INSERT INTO `wa_region` VALUES (9989, '阳明区', 3, 9981, 'Y');
INSERT INTO `wa_region` VALUES (9996, '爱民区', 3, 9981, 'A');
INSERT INTO `wa_region` VALUES (10005, '西安区', 3, 9981, 'X');
INSERT INTO `wa_region` VALUES (10014, '东宁县', 3, 9981, 'D');
INSERT INTO `wa_region` VALUES (10022, '林口县', 3, 9981, 'L');
INSERT INTO `wa_region` VALUES (10036, '绥芬河市', 3, 9981, 'S');
INSERT INTO `wa_region` VALUES (10039, '海林市', 3, 9981, 'H');
INSERT INTO `wa_region` VALUES (10056, '宁安市', 3, 9981, 'N');
INSERT INTO `wa_region` VALUES (10072, '穆棱市', 3, 9981, 'M');
INSERT INTO `wa_region` VALUES (10084, '黑河市', 2, 8558, 'H');
INSERT INTO `wa_region` VALUES (10085, '市辖区', 3, 10084, 'S');
INSERT INTO `wa_region` VALUES (10086, '爱辉区', 3, 10084, 'A');
INSERT INTO `wa_region` VALUES (10122, '嫩江县', 3, 10084, 'N');
INSERT INTO `wa_region` VALUES (10150, '逊克县', 3, 10084, 'X');
INSERT INTO `wa_region` VALUES (10168, '孙吴县', 3, 10084, 'S');
INSERT INTO `wa_region` VALUES (10192, '北安市', 3, 10084, 'B');
INSERT INTO `wa_region` VALUES (10214, '五大连池市', 3, 10084, 'W');
INSERT INTO `wa_region` VALUES (10252, '绥化市', 2, 8558, 'S');
INSERT INTO `wa_region` VALUES (10253, '市辖区', 3, 10252, 'S');
INSERT INTO `wa_region` VALUES (10254, '北林区', 3, 10252, 'B');
INSERT INTO `wa_region` VALUES (10281, '望奎县', 3, 10252, 'W');
INSERT INTO `wa_region` VALUES (10301, '兰西县', 3, 10252, 'L');
INSERT INTO `wa_region` VALUES (10320, '青冈县', 3, 10252, 'Q');
INSERT INTO `wa_region` VALUES (10342, '庆安县', 3, 10252, 'Q');
INSERT INTO `wa_region` VALUES (10360, '明水县', 3, 10252, 'M');
INSERT INTO `wa_region` VALUES (10380, '绥棱县', 3, 10252, 'S');
INSERT INTO `wa_region` VALUES (10401, '安达市', 3, 10252, 'A');
INSERT INTO `wa_region` VALUES (10425, '肇东市', 3, 10252, 'Z');
INSERT INTO `wa_region` VALUES (10452, '海伦市', 3, 10252, 'H');
INSERT INTO `wa_region` VALUES (10483, '大兴安岭地区', 2, 8558, 'D');
INSERT INTO `wa_region` VALUES (10484, '加格达奇区', 3, 10483, 'J');
INSERT INTO `wa_region` VALUES (10495, '松岭区', 3, 10483, 'S');
INSERT INTO `wa_region` VALUES (10500, '新林区', 3, 10483, 'X');
INSERT INTO `wa_region` VALUES (10509, '呼中区', 3, 10483, 'H');
INSERT INTO `wa_region` VALUES (10515, '呼玛县', 3, 10483, 'H');
INSERT INTO `wa_region` VALUES (10525, '塔河县', 3, 10483, 'T');
INSERT INTO `wa_region` VALUES (10534, '漠河县', 3, 10483, 'M');
INSERT INTO `wa_region` VALUES (10543, '上海市', 1, 0, 'S');
INSERT INTO `wa_region` VALUES (10544, '上海市', 2, 10543, 'S');
INSERT INTO `wa_region` VALUES (10545, '黄浦区', 3, 10544, 'H');
INSERT INTO `wa_region` VALUES (10555, '卢湾区', 3, 10544, 'L');
INSERT INTO `wa_region` VALUES (10560, '徐汇区', 3, 10544, 'X');
INSERT INTO `wa_region` VALUES (10575, '长宁区', 3, 10544, 'C');
INSERT INTO `wa_region` VALUES (10586, '静安区', 3, 10544, 'J');
INSERT INTO `wa_region` VALUES (10592, '普陀区', 3, 10544, 'P');
INSERT INTO `wa_region` VALUES (10602, '闸北区', 3, 10544, 'Z');
INSERT INTO `wa_region` VALUES (10612, '虹口区', 3, 10544, 'H');
INSERT INTO `wa_region` VALUES (10623, '杨浦区', 3, 10544, 'Y');
INSERT INTO `wa_region` VALUES (10636, '闵行区', 3, 10544, 'M');
INSERT INTO `wa_region` VALUES (10650, '宝山区', 3, 10544, 'B');
INSERT INTO `wa_region` VALUES (10664, '嘉定区', 3, 10544, 'J');
INSERT INTO `wa_region` VALUES (10678, '浦东新区', 3, 10544, 'P');
INSERT INTO `wa_region` VALUES (10704, '金山区', 3, 10544, 'J');
INSERT INTO `wa_region` VALUES (10715, '松江区', 3, 10544, 'S');
INSERT INTO `wa_region` VALUES (10735, '青浦区', 3, 10544, 'Q');
INSERT INTO `wa_region` VALUES (10747, '南汇区', 3, 10544, 'N');
INSERT INTO `wa_region` VALUES (10765, '奉贤区', 3, 10544, 'F');
INSERT INTO `wa_region` VALUES (10780, '崇明区', 3, 10544, 'C');
INSERT INTO `wa_region` VALUES (10808, '江苏省', 1, 0, 'J');
INSERT INTO `wa_region` VALUES (10809, '南京市', 2, 10808, 'N');
INSERT INTO `wa_region` VALUES (10810, '市辖区', 3, 10809, 'S');
INSERT INTO `wa_region` VALUES (10811, '玄武区', 3, 10809, 'X');
INSERT INTO `wa_region` VALUES (10820, '白下区', 3, 10809, 'B');
INSERT INTO `wa_region` VALUES (10831, '秦淮区', 3, 10809, 'Q');
INSERT INTO `wa_region` VALUES (10837, '建邺区', 3, 10809, 'J');
INSERT INTO `wa_region` VALUES (10845, '鼓楼区', 3, 10809, 'G');
INSERT INTO `wa_region` VALUES (10853, '下关区', 3, 10809, 'X');
INSERT INTO `wa_region` VALUES (10860, '浦口区', 3, 10809, 'P');
INSERT INTO `wa_region` VALUES (10876, '栖霞区', 3, 10809, 'Q');
INSERT INTO `wa_region` VALUES (10894, '雨花台区', 3, 10809, 'Y');
INSERT INTO `wa_region` VALUES (10903, '江宁区', 3, 10809, 'J');
INSERT INTO `wa_region` VALUES (10916, '六合区', 3, 10809, 'L');
INSERT INTO `wa_region` VALUES (10937, '溧水县', 3, 10809, 'L');
INSERT INTO `wa_region` VALUES (10947, '高淳县', 3, 10809, 'G');
INSERT INTO `wa_region` VALUES (10960, '无锡市', 2, 10808, 'W');
INSERT INTO `wa_region` VALUES (10961, '新吴区', 3, 10960, 'X');
INSERT INTO `wa_region` VALUES (10962, '梁溪区', 3, 10960, 'L');
INSERT INTO `wa_region` VALUES (10981, '锡山区', 3, 10960, 'X');
INSERT INTO `wa_region` VALUES (10990, '惠山区', 3, 10960, 'H');
INSERT INTO `wa_region` VALUES (10999, '滨湖区', 3, 10960, 'B');
INSERT INTO `wa_region` VALUES (11018, '江阴市', 3, 10960, 'J');
INSERT INTO `wa_region` VALUES (11039, '宜兴市', 3, 10960, 'Y');
INSERT INTO `wa_region` VALUES (11067, '徐州市', 2, 10808, 'X');
INSERT INTO `wa_region` VALUES (11068, '市辖区', 3, 11067, 'S');
INSERT INTO `wa_region` VALUES (11069, '鼓楼区', 3, 11067, 'G');
INSERT INTO `wa_region` VALUES (11081, '云龙区', 3, 11067, 'Y');
INSERT INTO `wa_region` VALUES (11089, '九里区', 3, 11067, 'J');
INSERT INTO `wa_region` VALUES (11103, '贾汪区', 3, 11067, 'J');
INSERT INTO `wa_region` VALUES (11115, '泉山区', 3, 11067, 'Q');
INSERT INTO `wa_region` VALUES (11126, '丰县', 3, 11067, 'F');
INSERT INTO `wa_region` VALUES (11142, '沛县', 3, 11067, 'P');
INSERT INTO `wa_region` VALUES (11160, '铜山县', 3, 11067, 'T');
INSERT INTO `wa_region` VALUES (11182, '睢宁县', 3, 11067, 'S');
INSERT INTO `wa_region` VALUES (11200, '新沂市', 3, 11067, 'X');
INSERT INTO `wa_region` VALUES (11218, '邳州市', 3, 11067, 'P');
INSERT INTO `wa_region` VALUES (11245, '常州市', 2, 10808, 'C');
INSERT INTO `wa_region` VALUES (11246, '常州市区', 3, 11245, 'C');
INSERT INTO `wa_region` VALUES (11247, '天宁区', 3, 11245, 'T');
INSERT INTO `wa_region` VALUES (11254, '钟楼区', 3, 11245, 'Z');
INSERT INTO `wa_region` VALUES (11262, '戚墅堰区', 3, 11245, 'Q');
INSERT INTO `wa_region` VALUES (11266, '新北区', 3, 11245, 'X');
INSERT INTO `wa_region` VALUES (11276, '武进区', 3, 11245, 'W');
INSERT INTO `wa_region` VALUES (11311, '溧阳市', 3, 11245, 'L');
INSERT INTO `wa_region` VALUES (11331, '金坛市', 3, 11245, 'J');
INSERT INTO `wa_region` VALUES (11348, '苏州市', 2, 10808, 'S');
INSERT INTO `wa_region` VALUES (11349, '市辖区', 3, 11348, 'S');
INSERT INTO `wa_region` VALUES (11350, '沧浪区', 3, 11348, 'C');
INSERT INTO `wa_region` VALUES (11357, '平江区', 3, 11348, 'P');
INSERT INTO `wa_region` VALUES (11368, '金阊区', 3, 11348, 'J');
INSERT INTO `wa_region` VALUES (11374, '苏州高新区虎丘区', 3, 11348, 'S');
INSERT INTO `wa_region` VALUES (11387, '吴中区', 3, 11348, 'W');
INSERT INTO `wa_region` VALUES (11409, '相城区', 3, 11348, 'X');
INSERT INTO `wa_region` VALUES (11419, '常熟市', 3, 11348, 'C');
INSERT INTO `wa_region` VALUES (11433, '张家港市', 3, 11348, 'Z');
INSERT INTO `wa_region` VALUES (11448, '昆山市', 3, 11348, 'K');
INSERT INTO `wa_region` VALUES (11460, '吴江市', 3, 11348, 'W');
INSERT INTO `wa_region` VALUES (11472, '太仓市', 3, 11348, 'T');
INSERT INTO `wa_region` VALUES (11482, '南通市', 2, 10808, 'N');
INSERT INTO `wa_region` VALUES (11483, '市辖区', 3, 11482, 'S');
INSERT INTO `wa_region` VALUES (11484, '崇川区', 3, 11482, 'C');
INSERT INTO `wa_region` VALUES (11502, '港闸区', 3, 11482, 'G');
INSERT INTO `wa_region` VALUES (11510, '海安县', 3, 11482, 'H');
INSERT INTO `wa_region` VALUES (11526, '如东', 3, 11482, 'R');
INSERT INTO `wa_region` VALUES (11542, '启东市', 3, 11482, 'Q');
INSERT INTO `wa_region` VALUES (11568, '如皋市', 3, 11482, 'R');
INSERT INTO `wa_region` VALUES (11600, '通州市', 3, 11482, 'T');
INSERT INTO `wa_region` VALUES (11627, '海门市', 3, 11482, 'H');
INSERT INTO `wa_region` VALUES (11663, '连云港市', 2, 10808, 'L');
INSERT INTO `wa_region` VALUES (11664, '市辖区', 3, 11663, 'S');
INSERT INTO `wa_region` VALUES (11665, '连云区', 3, 11663, 'L');
INSERT INTO `wa_region` VALUES (11678, '新浦区', 3, 11663, 'X');
INSERT INTO `wa_region` VALUES (11692, '海州区', 3, 11663, 'H');
INSERT INTO `wa_region` VALUES (11699, '赣榆县', 3, 11663, 'G');
INSERT INTO `wa_region` VALUES (11722, '东海县', 3, 11663, 'D');
INSERT INTO `wa_region` VALUES (11747, '灌云县', 3, 11663, 'G');
INSERT INTO `wa_region` VALUES (11771, '灌南县', 3, 11663, 'G');
INSERT INTO `wa_region` VALUES (11786, '淮安市', 2, 10808, 'H');
INSERT INTO `wa_region` VALUES (11787, '市辖区', 3, 11786, 'S');
INSERT INTO `wa_region` VALUES (11788, '清河区', 3, 11786, 'Q');
INSERT INTO `wa_region` VALUES (11801, '楚州区', 3, 11786, 'C');
INSERT INTO `wa_region` VALUES (11830, '淮阴区', 3, 11786, 'H');
INSERT INTO `wa_region` VALUES (11853, '清浦区', 3, 11786, 'Q');
INSERT INTO `wa_region` VALUES (11863, '涟水县', 3, 11786, 'L');
INSERT INTO `wa_region` VALUES (11896, '洪泽县', 3, 11786, 'H');
INSERT INTO `wa_region` VALUES (11909, '盱眙县', 3, 11786, 'X');
INSERT INTO `wa_region` VALUES (11931, '金湖县', 3, 11786, 'J');
INSERT INTO `wa_region` VALUES (11947, '盐城市', 2, 10808, 'Y');
INSERT INTO `wa_region` VALUES (11948, '市辖区', 3, 11947, 'S');
INSERT INTO `wa_region` VALUES (11949, '亭湖区', 3, 11947, 'T');
INSERT INTO `wa_region` VALUES (11967, '盐都区', 3, 11947, 'Y');
INSERT INTO `wa_region` VALUES (11982, '响水县', 3, 11947, 'X');
INSERT INTO `wa_region` VALUES (11998, '滨海县', 3, 11947, 'B');
INSERT INTO `wa_region` VALUES (12017, '阜宁县', 3, 11947, 'F');
INSERT INTO `wa_region` VALUES (12040, '射阳县', 3, 11947, 'S');
INSERT INTO `wa_region` VALUES (12066, '建湖县', 3, 11947, 'J');
INSERT INTO `wa_region` VALUES (12083, '东台市', 3, 11947, 'D');
INSERT INTO `wa_region` VALUES (12117, '大丰市', 3, 11947, 'D');
INSERT INTO `wa_region` VALUES (12135, '扬州市', 2, 10808, 'Y');
INSERT INTO `wa_region` VALUES (12136, '市辖区', 3, 12135, 'S');
INSERT INTO `wa_region` VALUES (12137, '广陵区', 3, 12135, 'G');
INSERT INTO `wa_region` VALUES (12144, '邗江区', 3, 12135, 'H');
INSERT INTO `wa_region` VALUES (12160, '维扬区', 3, 12135, 'W');
INSERT INTO `wa_region` VALUES (12175, '宝应县', 3, 12135, 'B');
INSERT INTO `wa_region` VALUES (12191, '仪征市', 3, 12135, 'Y');
INSERT INTO `wa_region` VALUES (12212, '高邮市', 3, 12135, 'G');
INSERT INTO `wa_region` VALUES (12235, '江都市', 3, 12135, 'J');
INSERT INTO `wa_region` VALUES (12249, '镇江市', 2, 10808, 'Z');
INSERT INTO `wa_region` VALUES (12250, '市区', 3, 12249, 'S');
INSERT INTO `wa_region` VALUES (12251, '京口区', 3, 12249, 'J');
INSERT INTO `wa_region` VALUES (12265, '润州区', 3, 12249, 'R');
INSERT INTO `wa_region` VALUES (12273, '丹徒区', 3, 12249, 'D');
INSERT INTO `wa_region` VALUES (12282, '丹阳市', 3, 12249, 'D');
INSERT INTO `wa_region` VALUES (12300, '扬中市', 3, 12249, 'Y');
INSERT INTO `wa_region` VALUES (12312, '句容市', 3, 12249, 'J');
INSERT INTO `wa_region` VALUES (12343, '泰州市', 2, 10808, 'T');
INSERT INTO `wa_region` VALUES (12344, '市辖区', 3, 12343, 'S');
INSERT INTO `wa_region` VALUES (12345, '海陵区', 3, 12343, 'H');
INSERT INTO `wa_region` VALUES (12362, '高港区', 3, 12343, 'G');
INSERT INTO `wa_region` VALUES (12370, '兴化市', 3, 12343, 'X');
INSERT INTO `wa_region` VALUES (12407, '靖江市', 3, 12343, 'J');
INSERT INTO `wa_region` VALUES (12423, '泰兴市', 3, 12343, 'T');
INSERT INTO `wa_region` VALUES (12450, '姜堰市', 3, 12343, 'J');
INSERT INTO `wa_region` VALUES (12475, '宿迁市', 2, 10808, 'S');
INSERT INTO `wa_region` VALUES (12476, '市辖区', 3, 12475, 'S');
INSERT INTO `wa_region` VALUES (12477, '宿城区', 3, 12475, 'S');
INSERT INTO `wa_region` VALUES (12496, '宿豫区', 3, 12475, 'S');
INSERT INTO `wa_region` VALUES (12515, '沭阳县', 3, 12475, 'S');
INSERT INTO `wa_region` VALUES (12551, '泗阳县', 3, 12475, 'S');
INSERT INTO `wa_region` VALUES (12570, '泗洪县', 3, 12475, 'S');
INSERT INTO `wa_region` VALUES (12596, '浙江省', 1, 0, 'Z');
INSERT INTO `wa_region` VALUES (12597, '杭州市', 2, 12596, 'H');
INSERT INTO `wa_region` VALUES (12598, '市辖区', 3, 12597, 'S');
INSERT INTO `wa_region` VALUES (12599, '上城区', 3, 12597, 'S');
INSERT INTO `wa_region` VALUES (12606, '下城区', 3, 12597, 'X');
INSERT INTO `wa_region` VALUES (12615, '江干区', 3, 12597, 'J');
INSERT INTO `wa_region` VALUES (12626, '拱墅区', 3, 12597, 'G');
INSERT INTO `wa_region` VALUES (12637, '西湖区', 3, 12597, 'X');
INSERT INTO `wa_region` VALUES (12652, '滨江区', 3, 12597, 'B');
INSERT INTO `wa_region` VALUES (12656, '萧山区', 3, 12597, 'X');
INSERT INTO `wa_region` VALUES (12685, '余杭区', 3, 12597, 'Y');
INSERT INTO `wa_region` VALUES (12705, '桐庐县', 3, 12597, 'T');
INSERT INTO `wa_region` VALUES (12719, '淳安县', 3, 12597, 'C');
INSERT INTO `wa_region` VALUES (12743, '建德市', 3, 12597, 'J');
INSERT INTO `wa_region` VALUES (12760, '富阳市', 3, 12597, 'F');
INSERT INTO `wa_region` VALUES (12786, '临安市', 3, 12597, 'L');
INSERT INTO `wa_region` VALUES (12813, '宁波市', 2, 12596, 'N');
INSERT INTO `wa_region` VALUES (12814, '市辖区', 3, 12813, 'S');
INSERT INTO `wa_region` VALUES (12815, '海曙区', 3, 12813, 'H');
INSERT INTO `wa_region` VALUES (12824, '江东区', 3, 12813, 'J');
INSERT INTO `wa_region` VALUES (12832, '江北区', 3, 12813, 'J');
INSERT INTO `wa_region` VALUES (12841, '北仑区', 3, 12813, 'B');
INSERT INTO `wa_region` VALUES (12851, '镇海区', 3, 12813, 'Z');
INSERT INTO `wa_region` VALUES (12858, '鄞州区', 3, 12813, 'Y');
INSERT INTO `wa_region` VALUES (12881, '象山县', 3, 12813, 'X');
INSERT INTO `wa_region` VALUES (12900, '宁海县', 3, 12813, 'N');
INSERT INTO `wa_region` VALUES (12919, '余姚市', 3, 12813, 'Y');
INSERT INTO `wa_region` VALUES (12941, '慈溪市', 3, 12813, 'C');
INSERT INTO `wa_region` VALUES (12962, '奉化市', 3, 12813, 'F');
INSERT INTO `wa_region` VALUES (12974, '温州市', 2, 12596, 'W');
INSERT INTO `wa_region` VALUES (12975, '市辖区', 3, 12974, 'S');
INSERT INTO `wa_region` VALUES (12976, '鹿城区', 3, 12974, 'L');
INSERT INTO `wa_region` VALUES (12998, '龙湾区', 3, 12974, 'L');
INSERT INTO `wa_region` VALUES (13009, '瓯海区', 3, 12974, 'O');
INSERT INTO `wa_region` VALUES (13023, '洞头县', 3, 12974, 'D');
INSERT INTO `wa_region` VALUES (13030, '永嘉县', 3, 12974, 'Y');
INSERT INTO `wa_region` VALUES (13069, '平阳县', 3, 12974, 'P');
INSERT INTO `wa_region` VALUES (13101, '苍南县', 3, 12974, 'C');
INSERT INTO `wa_region` VALUES (13138, '文成县', 3, 12974, 'W');
INSERT INTO `wa_region` VALUES (13172, '泰顺县', 3, 12974, 'T');
INSERT INTO `wa_region` VALUES (13209, '瑞安市', 3, 12974, 'R');
INSERT INTO `wa_region` VALUES (13248, '乐清市', 3, 12974, 'L');
INSERT INTO `wa_region` VALUES (13280, '嘉兴市', 2, 12596, 'J');
INSERT INTO `wa_region` VALUES (13281, '市辖区', 3, 13280, 'S');
INSERT INTO `wa_region` VALUES (13282, '南湖区', 3, 13280, 'N');
INSERT INTO `wa_region` VALUES (13295, '秀洲区', 3, 13280, 'X');
INSERT INTO `wa_region` VALUES (13304, '嘉善县', 3, 13280, 'J');
INSERT INTO `wa_region` VALUES (13316, '海盐县', 3, 13280, 'H');
INSERT INTO `wa_region` VALUES (13325, '海宁市', 3, 13280, 'H');
INSERT INTO `wa_region` VALUES (13339, '平湖市', 3, 13280, 'P');
INSERT INTO `wa_region` VALUES (13350, '桐乡市', 3, 13280, 'T');
INSERT INTO `wa_region` VALUES (13364, '湖州市', 2, 12596, 'H');
INSERT INTO `wa_region` VALUES (13365, '市辖区', 3, 13364, 'S');
INSERT INTO `wa_region` VALUES (13366, '吴兴区', 3, 13364, 'W');
INSERT INTO `wa_region` VALUES (13382, '南浔区', 3, 13364, 'N');
INSERT INTO `wa_region` VALUES (13392, '德清县', 3, 13364, 'D');
INSERT INTO `wa_region` VALUES (13404, '长兴县', 3, 13364, 'C');
INSERT INTO `wa_region` VALUES (13421, '安吉县', 3, 13364, 'A');
INSERT INTO `wa_region` VALUES (13437, '绍兴市', 2, 12596, 'S');
INSERT INTO `wa_region` VALUES (13438, '市辖区', 3, 13437, 'S');
INSERT INTO `wa_region` VALUES (13439, '越城区', 3, 13437, 'Y');
INSERT INTO `wa_region` VALUES (13453, '绍兴县', 3, 13437, 'S');
INSERT INTO `wa_region` VALUES (13473, '新昌县', 3, 13437, 'X');
INSERT INTO `wa_region` VALUES (13490, '诸暨市', 3, 13437, 'Z');
INSERT INTO `wa_region` VALUES (13518, '上虞市', 3, 13437, 'S');
INSERT INTO `wa_region` VALUES (13542, '嵊州市', 3, 13437, 'S');
INSERT INTO `wa_region` VALUES (13564, '金华市', 2, 12596, 'J');
INSERT INTO `wa_region` VALUES (13565, '市辖区', 3, 13564, 'S');
INSERT INTO `wa_region` VALUES (13566, '婺城区', 3, 13564, 'W');
INSERT INTO `wa_region` VALUES (13594, '金东区', 3, 13564, 'J');
INSERT INTO `wa_region` VALUES (13606, '武义县', 3, 13564, 'W');
INSERT INTO `wa_region` VALUES (13625, '浦江县', 3, 13564, 'P');
INSERT INTO `wa_region` VALUES (13641, '磐安县', 3, 13564, 'P');
INSERT INTO `wa_region` VALUES (13662, '兰溪市', 3, 13564, 'L');
INSERT INTO `wa_region` VALUES (13678, '义乌市', 3, 13564, 'Y');
INSERT INTO `wa_region` VALUES (13692, '东阳市', 3, 13564, 'D');
INSERT INTO `wa_region` VALUES (13711, '永康市', 3, 13564, 'Y');
INSERT INTO `wa_region` VALUES (13726, '衢州市', 2, 12596, 'Q');
INSERT INTO `wa_region` VALUES (13727, '市辖区', 3, 13726, 'S');
INSERT INTO `wa_region` VALUES (13728, '柯城区', 3, 13726, 'K');
INSERT INTO `wa_region` VALUES (13746, '衢江区', 3, 13726, 'Q');
INSERT INTO `wa_region` VALUES (13768, '常山县', 3, 13726, 'C');
INSERT INTO `wa_region` VALUES (13783, '开化县', 3, 13726, 'K');
INSERT INTO `wa_region` VALUES (13802, '龙游县', 3, 13726, 'L');
INSERT INTO `wa_region` VALUES (13818, '江山市', 3, 13726, 'J');
INSERT INTO `wa_region` VALUES (13840, '舟山市', 2, 12596, 'Z');
INSERT INTO `wa_region` VALUES (13841, '市辖区', 3, 13840, 'S');
INSERT INTO `wa_region` VALUES (13842, '定海区', 3, 13840, 'D');
INSERT INTO `wa_region` VALUES (13859, '普陀区', 3, 13840, 'P');
INSERT INTO `wa_region` VALUES (13874, '岱山县', 3, 13840, 'D');
INSERT INTO `wa_region` VALUES (13882, '嵊泗县', 3, 13840, 'S');
INSERT INTO `wa_region` VALUES (13890, '台州市', 2, 12596, 'T');
INSERT INTO `wa_region` VALUES (13891, '市辖区', 3, 13890, 'S');
INSERT INTO `wa_region` VALUES (13892, '椒江区', 3, 13890, 'J');
INSERT INTO `wa_region` VALUES (13903, '黄岩区', 3, 13890, 'H');
INSERT INTO `wa_region` VALUES (13923, '路桥区', 3, 13890, 'L');
INSERT INTO `wa_region` VALUES (13934, '玉环县', 3, 13890, 'Y');
INSERT INTO `wa_region` VALUES (13944, '三门县', 3, 13890, 'S');
INSERT INTO `wa_region` VALUES (13959, '天台县', 3, 13890, 'T');
INSERT INTO `wa_region` VALUES (13975, '仙居县', 3, 13890, 'X');
INSERT INTO `wa_region` VALUES (13996, '温岭市', 3, 13890, 'W');
INSERT INTO `wa_region` VALUES (14013, '临海市', 3, 13890, 'L');
INSERT INTO `wa_region` VALUES (14033, '丽水市', 2, 12596, 'L');
INSERT INTO `wa_region` VALUES (14034, '市辖区', 3, 14033, 'S');
INSERT INTO `wa_region` VALUES (14035, '莲都区', 3, 14033, 'L');
INSERT INTO `wa_region` VALUES (14054, '青田县', 3, 14033, 'Q');
INSERT INTO `wa_region` VALUES (14086, '缙云县', 3, 14033, 'J');
INSERT INTO `wa_region` VALUES (14111, '遂昌县', 3, 14033, 'S');
INSERT INTO `wa_region` VALUES (14132, '松阳县', 3, 14033, 'S');
INSERT INTO `wa_region` VALUES (14153, '云和县', 3, 14033, 'Y');
INSERT INTO `wa_region` VALUES (14168, '庆元县', 3, 14033, 'Q');
INSERT INTO `wa_region` VALUES (14189, '景宁县', 3, 14033, 'J');
INSERT INTO `wa_region` VALUES (14214, '龙泉市', 3, 14033, 'L');
INSERT INTO `wa_region` VALUES (14234, '安徽省', 1, 0, 'A');
INSERT INTO `wa_region` VALUES (14235, '合肥市', 2, 14234, 'H');
INSERT INTO `wa_region` VALUES (14236, '市辖区', 3, 14235, 'S');
INSERT INTO `wa_region` VALUES (14237, '瑶海区', 3, 14235, 'Y');
INSERT INTO `wa_region` VALUES (14254, '庐阳区', 3, 14235, 'L');
INSERT INTO `wa_region` VALUES (14269, '蜀山区', 3, 14235, 'S');
INSERT INTO `wa_region` VALUES (14286, '包河区', 3, 14235, 'B');
INSERT INTO `wa_region` VALUES (14297, '长丰县', 3, 14235, 'C');
INSERT INTO `wa_region` VALUES (14314, '肥东县', 3, 14235, 'F');
INSERT INTO `wa_region` VALUES (14334, '肥西县', 3, 14235, 'F');
INSERT INTO `wa_region` VALUES (14351, '芜湖市', 2, 14234, 'W');
INSERT INTO `wa_region` VALUES (14352, '市辖区', 3, 14351, 'S');
INSERT INTO `wa_region` VALUES (14353, '镜湖区', 3, 14351, 'J');
INSERT INTO `wa_region` VALUES (14366, '弋江区', 3, 14351, 'Y');
INSERT INTO `wa_region` VALUES (14374, '鸠江区', 3, 14351, 'J');
INSERT INTO `wa_region` VALUES (14382, '三山区', 3, 14351, 'S');
INSERT INTO `wa_region` VALUES (14387, '芜湖县', 3, 14351, 'W');
INSERT INTO `wa_region` VALUES (14394, '繁昌县', 3, 14351, 'F');
INSERT INTO `wa_region` VALUES (14401, '南陵县', 3, 14351, 'N');
INSERT INTO `wa_region` VALUES (14410, '蚌埠市', 2, 14234, 'B');
INSERT INTO `wa_region` VALUES (14411, '市辖区', 3, 14410, 'S');
INSERT INTO `wa_region` VALUES (14412, '龙子湖区', 3, 14410, 'L');
INSERT INTO `wa_region` VALUES (14422, '蚌山区', 3, 14410, 'B');
INSERT INTO `wa_region` VALUES (14434, '禹会区', 3, 14410, 'Y');
INSERT INTO `wa_region` VALUES (14443, '淮上区', 3, 14410, 'H');
INSERT INTO `wa_region` VALUES (14449, '怀远县', 3, 14410, 'H');
INSERT INTO `wa_region` VALUES (14471, '五河县', 3, 14410, 'W');
INSERT INTO `wa_region` VALUES (14487, '固镇县', 3, 14410, 'G');
INSERT INTO `wa_region` VALUES (14500, '淮南市', 2, 14234, 'H');
INSERT INTO `wa_region` VALUES (14501, '市辖区', 3, 14500, 'S');
INSERT INTO `wa_region` VALUES (14502, '大通区', 3, 14500, 'D');
INSERT INTO `wa_region` VALUES (14508, '田家庵区', 3, 14500, 'T');
INSERT INTO `wa_region` VALUES (14523, '谢家集区', 3, 14500, 'X');
INSERT INTO `wa_region` VALUES (14535, '八公山区', 3, 14500, 'B');
INSERT INTO `wa_region` VALUES (14542, '潘集区', 3, 14500, 'P');
INSERT INTO `wa_region` VALUES (14554, '凤台县', 3, 14500, 'F');
INSERT INTO `wa_region` VALUES (14575, '马鞍山市', 2, 14234, 'M');
INSERT INTO `wa_region` VALUES (14576, '市辖区', 3, 14575, 'S');
INSERT INTO `wa_region` VALUES (14577, '金家庄区', 3, 14575, 'J');
INSERT INTO `wa_region` VALUES (14583, '花山区', 3, 14575, 'H');
INSERT INTO `wa_region` VALUES (14589, '雨山区', 3, 14575, 'Y');
INSERT INTO `wa_region` VALUES (14597, '当涂县', 3, 14575, 'D');
INSERT INTO `wa_region` VALUES (14612, '淮北市', 2, 14234, 'H');
INSERT INTO `wa_region` VALUES (14613, '市辖区', 3, 14612, 'S');
INSERT INTO `wa_region` VALUES (14614, '杜集区', 3, 14612, 'D');
INSERT INTO `wa_region` VALUES (14620, '相山区', 3, 14612, 'X');
INSERT INTO `wa_region` VALUES (14632, '烈山区', 3, 14612, 'L');
INSERT INTO `wa_region` VALUES (14641, '濉溪县', 3, 14612, 'S');
INSERT INTO `wa_region` VALUES (14653, '铜陵市', 2, 14234, 'T');
INSERT INTO `wa_region` VALUES (14654, '市辖区', 3, 14653, 'S');
INSERT INTO `wa_region` VALUES (14655, '铜官山区', 3, 14653, 'T');
INSERT INTO `wa_region` VALUES (14663, '狮子山区', 3, 14653, 'S');
INSERT INTO `wa_region` VALUES (14671, '铜陵市郊区', 3, 14653, 'T');
INSERT INTO `wa_region` VALUES (14678, '铜陵县', 3, 14653, 'T');
INSERT INTO `wa_region` VALUES (14687, '安庆市', 2, 14234, 'A');
INSERT INTO `wa_region` VALUES (14688, '市辖区', 3, 14687, 'S');
INSERT INTO `wa_region` VALUES (14689, '迎江区', 3, 14687, 'Y');
INSERT INTO `wa_region` VALUES (14700, '大观区', 3, 14687, 'D');
INSERT INTO `wa_region` VALUES (14712, '宜秀区', 3, 14687, 'Y');
INSERT INTO `wa_region` VALUES (14720, '怀宁县', 3, 14687, 'H');
INSERT INTO `wa_region` VALUES (14741, '枞阳县', 3, 14687, 'C');
INSERT INTO `wa_region` VALUES (14764, '潜山县', 3, 14687, 'Q');
INSERT INTO `wa_region` VALUES (14782, '太湖县', 3, 14687, 'T');
INSERT INTO `wa_region` VALUES (14798, '宿松县', 3, 14687, 'S');
INSERT INTO `wa_region` VALUES (14823, '望江县', 3, 14687, 'W');
INSERT INTO `wa_region` VALUES (14834, '岳西县', 3, 14687, 'Y');
INSERT INTO `wa_region` VALUES (14859, '桐城市', 3, 14687, 'T');
INSERT INTO `wa_region` VALUES (14887, '黄山市', 2, 14234, 'H');
INSERT INTO `wa_region` VALUES (14888, '市辖区', 3, 14887, 'S');
INSERT INTO `wa_region` VALUES (14889, '屯溪区', 3, 14887, 'T');
INSERT INTO `wa_region` VALUES (14900, '黄山区', 3, 14887, 'H');
INSERT INTO `wa_region` VALUES (14917, '徽州区', 3, 14887, 'H');
INSERT INTO `wa_region` VALUES (14926, '歙县', 3, 14887, 'S');
INSERT INTO `wa_region` VALUES (14955, '休宁县', 3, 14887, 'X');
INSERT INTO `wa_region` VALUES (14977, '黟县', 3, 14887, 'Y');
INSERT INTO `wa_region` VALUES (14986, '祁门县', 3, 14887, 'Q');
INSERT INTO `wa_region` VALUES (15005, '滁州市', 2, 14234, 'C');
INSERT INTO `wa_region` VALUES (15006, '市辖区', 3, 15005, 'S');
INSERT INTO `wa_region` VALUES (15007, '琅琊区', 3, 15005, 'L');
INSERT INTO `wa_region` VALUES (15016, '南谯区', 3, 15005, 'N');
INSERT INTO `wa_region` VALUES (15034, '来安县', 3, 15005, 'L');
INSERT INTO `wa_region` VALUES (15053, '全椒县', 3, 15005, 'Q');
INSERT INTO `wa_region` VALUES (15071, '定远县', 3, 15005, 'D');
INSERT INTO `wa_region` VALUES (15109, '凤阳县', 3, 15005, 'F');
INSERT INTO `wa_region` VALUES (15136, '天长市', 3, 15005, 'T');
INSERT INTO `wa_region` VALUES (15166, '明光市', 3, 15005, 'M');
INSERT INTO `wa_region` VALUES (15194, '阜阳市', 2, 14234, 'F');
INSERT INTO `wa_region` VALUES (15195, '市辖区', 3, 15194, 'S');
INSERT INTO `wa_region` VALUES (15196, '颍州区', 3, 15194, 'Y');
INSERT INTO `wa_region` VALUES (15211, '颍东区', 3, 15194, 'Y');
INSERT INTO `wa_region` VALUES (15224, '颍泉区', 3, 15194, 'Y');
INSERT INTO `wa_region` VALUES (15231, '临泉县', 3, 15194, 'L');
INSERT INTO `wa_region` VALUES (15264, '太和县', 3, 15194, 'T');
INSERT INTO `wa_region` VALUES (15296, '阜南县', 3, 15194, 'F');
INSERT INTO `wa_region` VALUES (15328, '颍上县', 3, 15194, 'Y');
INSERT INTO `wa_region` VALUES (15359, '界首市', 3, 15194, 'J');
INSERT INTO `wa_region` VALUES (15378, '宿州市', 2, 14234, 'S');
INSERT INTO `wa_region` VALUES (15379, '市辖区', 3, 15378, 'S');
INSERT INTO `wa_region` VALUES (15380, '墉桥区', 3, 15378, 'Y');
INSERT INTO `wa_region` VALUES (15417, '砀山县', 3, 15378, 'D');
INSERT INTO `wa_region` VALUES (15437, '萧县', 3, 15378, 'X');
INSERT INTO `wa_region` VALUES (15461, '灵璧县', 3, 15378, 'L');
INSERT INTO `wa_region` VALUES (15482, '泗县', 3, 15378, 'S');
INSERT INTO `wa_region` VALUES (15499, '巢湖市', 2, 14234, 'C');
INSERT INTO `wa_region` VALUES (15500, '市辖区', 3, 15499, 'S');
INSERT INTO `wa_region` VALUES (15501, '居巢区', 3, 15499, 'J');
INSERT INTO `wa_region` VALUES (15520, '庐江县', 3, 15499, 'L');
INSERT INTO `wa_region` VALUES (15542, '无为县', 3, 15499, 'W');
INSERT INTO `wa_region` VALUES (15566, '含山县', 3, 15499, 'H');
INSERT INTO `wa_region` VALUES (15575, '和县', 3, 15499, 'H');
INSERT INTO `wa_region` VALUES (15586, '六安市', 2, 14234, 'L');
INSERT INTO `wa_region` VALUES (15587, '市辖区', 3, 15586, 'S');
INSERT INTO `wa_region` VALUES (15588, '金安区', 3, 15586, 'J');
INSERT INTO `wa_region` VALUES (15612, '裕安区', 3, 15586, 'Y');
INSERT INTO `wa_region` VALUES (15635, '寿县', 3, 15586, 'S');
INSERT INTO `wa_region` VALUES (15662, '霍邱县', 3, 15586, 'H');
INSERT INTO `wa_region` VALUES (15698, '舒城县', 3, 15586, 'S');
INSERT INTO `wa_region` VALUES (15720, '金寨县', 3, 15586, 'J');
INSERT INTO `wa_region` VALUES (15747, '霍山县', 3, 15586, 'H');
INSERT INTO `wa_region` VALUES (15764, '亳州市', 2, 14234, 'H');
INSERT INTO `wa_region` VALUES (15765, '市辖区', 3, 15764, 'S');
INSERT INTO `wa_region` VALUES (15766, '谯城区', 3, 15764, 'Q');
INSERT INTO `wa_region` VALUES (15795, '涡阳县', 3, 15764, 'W');
INSERT INTO `wa_region` VALUES (15823, '蒙城县', 3, 15764, 'M');
INSERT INTO `wa_region` VALUES (15843, '利辛县', 3, 15764, 'L');
INSERT INTO `wa_region` VALUES (15871, '池州市', 2, 14234, 'C');
INSERT INTO `wa_region` VALUES (15872, '市辖区', 3, 15871, 'S');
INSERT INTO `wa_region` VALUES (15873, '贵池区', 3, 15871, 'G');
INSERT INTO `wa_region` VALUES (15900, '东至县', 3, 15871, 'D');
INSERT INTO `wa_region` VALUES (15930, '石台县', 3, 15871, 'S');
INSERT INTO `wa_region` VALUES (15944, '青阳县', 3, 15871, 'Q');
INSERT INTO `wa_region` VALUES (15958, '宣城市', 2, 14234, 'X');
INSERT INTO `wa_region` VALUES (15959, '市辖区', 3, 15958, 'S');
INSERT INTO `wa_region` VALUES (15960, '宣州区', 3, 15958, 'X');
INSERT INTO `wa_region` VALUES (15987, '郎溪县', 3, 15958, 'L');
INSERT INTO `wa_region` VALUES (16001, '广德县', 3, 15958, 'G');
INSERT INTO `wa_region` VALUES (16013, '泾县', 3, 15958, 'J');
INSERT INTO `wa_region` VALUES (16025, '绩溪县', 3, 15958, 'J');
INSERT INTO `wa_region` VALUES (16037, '旌德县', 3, 15958, 'J');
INSERT INTO `wa_region` VALUES (16048, '宁国市', 3, 15958, 'N');
INSERT INTO `wa_region` VALUES (16068, '福建省', 1, 0, 'F');
INSERT INTO `wa_region` VALUES (16069, '福州市', 2, 16068, 'F');
INSERT INTO `wa_region` VALUES (16070, '市辖区', 3, 16069, 'S');
INSERT INTO `wa_region` VALUES (16071, '鼓楼区', 3, 16069, 'G');
INSERT INTO `wa_region` VALUES (16082, '台江区', 3, 16069, 'T');
INSERT INTO `wa_region` VALUES (16093, '仓山区', 3, 16069, 'C');
INSERT INTO `wa_region` VALUES (16108, '马尾区', 3, 16069, 'M');
INSERT INTO `wa_region` VALUES (16113, '晋安区', 3, 16069, 'J');
INSERT INTO `wa_region` VALUES (16123, '闽侯县', 3, 16069, 'M');
INSERT INTO `wa_region` VALUES (16140, '连江县', 3, 16069, 'L');
INSERT INTO `wa_region` VALUES (16164, '罗源县', 3, 16069, 'L');
INSERT INTO `wa_region` VALUES (16177, '闽清县', 3, 16069, 'M');
INSERT INTO `wa_region` VALUES (16194, '永泰县', 3, 16069, 'Y');
INSERT INTO `wa_region` VALUES (16216, '平潭县', 3, 16069, 'P');
INSERT INTO `wa_region` VALUES (16232, '福清市', 3, 16069, 'F');
INSERT INTO `wa_region` VALUES (16259, '长乐市', 3, 16069, 'C');
INSERT INTO `wa_region` VALUES (16278, '厦门市', 2, 16068, 'X');
INSERT INTO `wa_region` VALUES (16279, '市辖区', 3, 16278, 'S');
INSERT INTO `wa_region` VALUES (16280, '思明区', 3, 16278, 'S');
INSERT INTO `wa_region` VALUES (16294, '海沧区', 3, 16278, 'H');
INSERT INTO `wa_region` VALUES (16303, '湖里区', 3, 16278, 'H');
INSERT INTO `wa_region` VALUES (16315, '集美区', 3, 16278, 'J');
INSERT INTO `wa_region` VALUES (16326, '同安区', 3, 16278, 'T');
INSERT INTO `wa_region` VALUES (16341, '翔安区', 3, 16278, 'X');
INSERT INTO `wa_region` VALUES (16348, '莆田市', 2, 16068, 'P');
INSERT INTO `wa_region` VALUES (16349, '市辖区', 3, 16348, 'S');
INSERT INTO `wa_region` VALUES (16350, '城厢区', 3, 16348, 'C');
INSERT INTO `wa_region` VALUES (16358, '涵江区', 3, 16348, 'H');
INSERT INTO `wa_region` VALUES (16372, '荔城区', 3, 16348, 'L');
INSERT INTO `wa_region` VALUES (16379, '秀屿区', 3, 16348, 'X');
INSERT INTO `wa_region` VALUES (16393, '仙游县', 3, 16348, 'X');
INSERT INTO `wa_region` VALUES (16412, '三明市', 2, 16068, 'S');
INSERT INTO `wa_region` VALUES (16413, '市辖区', 3, 16412, 'S');
INSERT INTO `wa_region` VALUES (16414, '梅列区', 3, 16412, 'M');
INSERT INTO `wa_region` VALUES (16421, '三元区', 3, 16412, 'S');
INSERT INTO `wa_region` VALUES (16430, '明溪县', 3, 16412, 'M');
INSERT INTO `wa_region` VALUES (16440, '清流县', 3, 16412, 'Q');
INSERT INTO `wa_region` VALUES (16455, '宁化县', 3, 16412, 'N');
INSERT INTO `wa_region` VALUES (16472, '大田县', 3, 16412, 'D');
INSERT INTO `wa_region` VALUES (16492, '尤溪县', 3, 16412, 'Y');
INSERT INTO `wa_region` VALUES (16508, '沙县', 3, 16412, 'S');
INSERT INTO `wa_region` VALUES (16521, '将乐县', 3, 16412, 'J');
INSERT INTO `wa_region` VALUES (16535, '泰宁县', 3, 16412, 'T');
INSERT INTO `wa_region` VALUES (16545, '建宁县', 3, 16412, 'J');
INSERT INTO `wa_region` VALUES (16556, '永安市', 3, 16412, 'Y');
INSERT INTO `wa_region` VALUES (16572, '泉州市', 2, 16068, 'Q');
INSERT INTO `wa_region` VALUES (16573, '市辖区', 3, 16572, 'S');
INSERT INTO `wa_region` VALUES (16574, '鲤城区', 3, 16572, 'L');
INSERT INTO `wa_region` VALUES (16584, '丰泽区', 3, 16572, 'F');
INSERT INTO `wa_region` VALUES (16593, '洛江区', 3, 16572, 'L');
INSERT INTO `wa_region` VALUES (16600, '泉港区', 3, 16572, 'Q');
INSERT INTO `wa_region` VALUES (16608, '惠安县', 3, 16572, 'H');
INSERT INTO `wa_region` VALUES (16625, '安溪县', 3, 16572, 'A');
INSERT INTO `wa_region` VALUES (16650, '永春县', 3, 16572, 'Y');
INSERT INTO `wa_region` VALUES (16673, '德化县', 3, 16572, 'D');
INSERT INTO `wa_region` VALUES (16692, '金门县', 3, 16572, 'J');
INSERT INTO `wa_region` VALUES (16693, '石狮市', 3, 16572, 'S');
INSERT INTO `wa_region` VALUES (16703, '晋江市', 3, 16572, 'J');
INSERT INTO `wa_region` VALUES (16726, '南安市', 3, 16572, 'N');
INSERT INTO `wa_region` VALUES (16754, '漳州市', 2, 16068, 'Z');
INSERT INTO `wa_region` VALUES (16755, '市辖区', 3, 16754, 'S');
INSERT INTO `wa_region` VALUES (16756, '芗城区', 3, 16754, 'X');
INSERT INTO `wa_region` VALUES (16772, '龙文区', 3, 16754, 'L');
INSERT INTO `wa_region` VALUES (16778, '云霄县', 3, 16754, 'Y');
INSERT INTO `wa_region` VALUES (16790, '漳浦县', 3, 16754, 'Z');
INSERT INTO `wa_region` VALUES (16821, '诏安县', 3, 16754, 'Z');
INSERT INTO `wa_region` VALUES (16842, '长泰县', 3, 16754, 'C');
INSERT INTO `wa_region` VALUES (16852, '东山县', 3, 16754, 'D');
INSERT INTO `wa_region` VALUES (16860, '南靖县', 3, 16754, 'N');
INSERT INTO `wa_region` VALUES (16872, '平和县', 3, 16754, 'P');
INSERT INTO `wa_region` VALUES (16889, '华安县', 3, 16754, 'H');
INSERT INTO `wa_region` VALUES (16899, '龙海市', 3, 16754, 'L');
INSERT INTO `wa_region` VALUES (16924, '南平市', 2, 16068, 'N');
INSERT INTO `wa_region` VALUES (16925, '市辖区', 3, 16924, 'S');
INSERT INTO `wa_region` VALUES (16926, '延平区', 3, 16924, 'Y');
INSERT INTO `wa_region` VALUES (16948, '顺昌县', 3, 16924, 'S');
INSERT INTO `wa_region` VALUES (16961, '浦城县', 3, 16924, 'P');
INSERT INTO `wa_region` VALUES (16982, '光泽县', 3, 16924, 'G');
INSERT INTO `wa_region` VALUES (16991, '松溪县', 3, 16924, 'S');
INSERT INTO `wa_region` VALUES (17001, '政和县', 3, 16924, 'Z');
INSERT INTO `wa_region` VALUES (17012, '邵武市', 3, 16924, 'S');
INSERT INTO `wa_region` VALUES (17033, '武夷山市', 3, 16924, 'W');
INSERT INTO `wa_region` VALUES (17044, '建瓯市', 3, 16924, 'J');
INSERT INTO `wa_region` VALUES (17063, '建阳市', 3, 16924, 'J');
INSERT INTO `wa_region` VALUES (17077, '龙岩市', 2, 16068, 'L');
INSERT INTO `wa_region` VALUES (17078, '市辖区', 3, 17077, 'S');
INSERT INTO `wa_region` VALUES (17079, '新罗区', 3, 17077, 'X');
INSERT INTO `wa_region` VALUES (17099, '长汀县', 3, 17077, 'C');
INSERT INTO `wa_region` VALUES (17118, '永定县', 3, 17077, 'Y');
INSERT INTO `wa_region` VALUES (17143, '上杭县', 3, 17077, 'S');
INSERT INTO `wa_region` VALUES (17166, '武平县', 3, 17077, 'W');
INSERT INTO `wa_region` VALUES (17184, '连城县', 3, 17077, 'L');
INSERT INTO `wa_region` VALUES (17202, '漳平市', 3, 17077, 'Z');
INSERT INTO `wa_region` VALUES (17219, '宁德市　', 2, 16068, 'N');
INSERT INTO `wa_region` VALUES (17220, '市辖区', 3, 17219, 'S');
INSERT INTO `wa_region` VALUES (17221, '蕉城区', 3, 17219, 'J');
INSERT INTO `wa_region` VALUES (17239, '霞浦县', 3, 17219, 'X');
INSERT INTO `wa_region` VALUES (17254, '古田县', 3, 17219, 'G');
INSERT INTO `wa_region` VALUES (17269, '屏南县', 3, 17219, 'P');
INSERT INTO `wa_region` VALUES (17281, '寿宁县', 3, 17219, 'S');
INSERT INTO `wa_region` VALUES (17296, '周宁县', 3, 17219, 'Z');
INSERT INTO `wa_region` VALUES (17306, '柘荣县', 3, 17219, 'Z');
INSERT INTO `wa_region` VALUES (17316, '福安市', 3, 17219, 'F');
INSERT INTO `wa_region` VALUES (17341, '福鼎市', 3, 17219, 'F');
INSERT INTO `wa_region` VALUES (17359, '江西省', 1, 0, 'J');
INSERT INTO `wa_region` VALUES (17360, '南昌市', 2, 17359, 'N');
INSERT INTO `wa_region` VALUES (17361, '市辖区', 3, 17360, 'S');
INSERT INTO `wa_region` VALUES (17362, '东湖区', 3, 17360, 'D');
INSERT INTO `wa_region` VALUES (17374, '西湖区', 3, 17360, 'X');
INSERT INTO `wa_region` VALUES (17387, '青云谱区', 3, 17360, 'Q');
INSERT INTO `wa_region` VALUES (17395, '湾里区', 3, 17360, 'W');
INSERT INTO `wa_region` VALUES (17402, '青山湖区', 3, 17360, 'Q');
INSERT INTO `wa_region` VALUES (17420, '南昌县', 3, 17360, 'N');
INSERT INTO `wa_region` VALUES (17443, '新建县', 3, 17360, 'X');
INSERT INTO `wa_region` VALUES (17471, '安义县', 3, 17360, 'A');
INSERT INTO `wa_region` VALUES (17485, '进贤县', 3, 17360, 'J');
INSERT INTO `wa_region` VALUES (17508, '景德镇市', 2, 17359, 'J');
INSERT INTO `wa_region` VALUES (17509, '市辖区', 3, 17508, 'S');
INSERT INTO `wa_region` VALUES (17510, '昌江区', 3, 17508, 'C');
INSERT INTO `wa_region` VALUES (17534, '珠山区', 3, 17508, 'Z');
INSERT INTO `wa_region` VALUES (17545, '浮梁县', 3, 17508, 'F');
INSERT INTO `wa_region` VALUES (17568, '乐平市', 3, 17508, 'L');
INSERT INTO `wa_region` VALUES (17589, '萍乡市', 2, 17359, 'P');
INSERT INTO `wa_region` VALUES (17590, '市辖区', 3, 17589, 'S');
INSERT INTO `wa_region` VALUES (17591, '安源区', 3, 17589, 'A');
INSERT INTO `wa_region` VALUES (17604, '湘东区', 3, 17589, 'X');
INSERT INTO `wa_region` VALUES (17616, '莲花县', 3, 17589, 'L');
INSERT INTO `wa_region` VALUES (17630, '上栗县', 3, 17589, 'S');
INSERT INTO `wa_region` VALUES (17640, '芦溪县', 3, 17589, 'L');
INSERT INTO `wa_region` VALUES (17651, '九江市', 2, 17359, 'J');
INSERT INTO `wa_region` VALUES (17652, '市辖区', 3, 17651, 'S');
INSERT INTO `wa_region` VALUES (17653, '庐山区', 3, 17651, 'L');
INSERT INTO `wa_region` VALUES (17667, '浔阳区', 3, 17651, 'X');
INSERT INTO `wa_region` VALUES (17676, '九江县', 3, 17651, 'J');
INSERT INTO `wa_region` VALUES (17693, '武宁县', 3, 17651, 'W');
INSERT INTO `wa_region` VALUES (17714, '修水县', 3, 17651, 'X');
INSERT INTO `wa_region` VALUES (17751, '永修县', 3, 17651, 'Y');
INSERT INTO `wa_region` VALUES (17773, '德安县', 3, 17651, 'D');
INSERT INTO `wa_region` VALUES (17792, '星子县', 3, 17651, 'X');
INSERT INTO `wa_region` VALUES (17807, '都昌县', 3, 17651, 'D');
INSERT INTO `wa_region` VALUES (17834, '湖口县', 3, 17651, 'H');
INSERT INTO `wa_region` VALUES (17849, '彭泽县', 3, 17651, 'P');
INSERT INTO `wa_region` VALUES (17872, '瑞昌市', 3, 17651, 'R');
INSERT INTO `wa_region` VALUES (17894, '新余市', 2, 17359, 'X');
INSERT INTO `wa_region` VALUES (17895, '市辖区', 3, 17894, 'S');
INSERT INTO `wa_region` VALUES (17896, '渝水区', 3, 17894, 'Y');
INSERT INTO `wa_region` VALUES (17917, '分宜县', 3, 17894, 'F');
INSERT INTO `wa_region` VALUES (17934, '鹰潭市', 2, 17359, 'Y');
INSERT INTO `wa_region` VALUES (17935, '市辖区', 3, 17934, 'S');
INSERT INTO `wa_region` VALUES (17936, '月湖区', 3, 17934, 'Y');
INSERT INTO `wa_region` VALUES (17945, '余江县', 3, 17934, 'Y');
INSERT INTO `wa_region` VALUES (17966, '贵溪市', 3, 17934, 'G');
INSERT INTO `wa_region` VALUES (17999, '赣州市', 2, 17359, 'G');
INSERT INTO `wa_region` VALUES (18000, '市辖区', 3, 17999, 'S');
INSERT INTO `wa_region` VALUES (18001, '章贡区', 3, 17999, 'Z');
INSERT INTO `wa_region` VALUES (18016, '赣县', 3, 17999, 'G');
INSERT INTO `wa_region` VALUES (18037, '信丰县', 3, 17999, 'X');
INSERT INTO `wa_region` VALUES (18055, '大余县', 3, 17999, 'D');
INSERT INTO `wa_region` VALUES (18068, '上犹县', 3, 17999, 'S');
INSERT INTO `wa_region` VALUES (18084, '崇义县', 3, 17999, 'C');
INSERT INTO `wa_region` VALUES (18102, '安远县', 3, 17999, 'A');
INSERT INTO `wa_region` VALUES (18122, '龙南县', 3, 17999, 'L');
INSERT INTO `wa_region` VALUES (18139, '定南县', 3, 17999, 'D');
INSERT INTO `wa_region` VALUES (18148, '全南县', 3, 17999, 'Q');
INSERT INTO `wa_region` VALUES (18161, '宁都县', 3, 17999, 'N');
INSERT INTO `wa_region` VALUES (18187, '于都县', 3, 17999, 'Y');
INSERT INTO `wa_region` VALUES (18212, '兴国县', 3, 17999, 'X');
INSERT INTO `wa_region` VALUES (18239, '会昌县', 3, 17999, 'H');
INSERT INTO `wa_region` VALUES (18260, '寻乌县', 3, 17999, 'X');
INSERT INTO `wa_region` VALUES (18276, '石城县', 3, 17999, 'S');
INSERT INTO `wa_region` VALUES (18287, '瑞金市', 3, 17999, 'R');
INSERT INTO `wa_region` VALUES (18306, '南康市', 3, 17999, 'N');
INSERT INTO `wa_region` VALUES (18330, '吉安市', 2, 17359, 'J');
INSERT INTO `wa_region` VALUES (18331, '市辖区', 3, 18330, 'S');
INSERT INTO `wa_region` VALUES (18332, '吉州区', 3, 18330, 'J');
INSERT INTO `wa_region` VALUES (18345, '青原区', 3, 18330, 'Q');
INSERT INTO `wa_region` VALUES (18356, '吉安县', 3, 18330, 'J');
INSERT INTO `wa_region` VALUES (18378, '吉水县', 3, 18330, 'J');
INSERT INTO `wa_region` VALUES (18398, '峡江县', 3, 18330, 'X');
INSERT INTO `wa_region` VALUES (18411, '新干县', 3, 18330, 'X');
INSERT INTO `wa_region` VALUES (18429, '永丰县', 3, 18330, 'Y');
INSERT INTO `wa_region` VALUES (18454, '泰和县', 3, 18330, 'T');
INSERT INTO `wa_region` VALUES (18483, '遂川县', 3, 18330, 'S');
INSERT INTO `wa_region` VALUES (18510, '万安县', 3, 18330, 'W');
INSERT INTO `wa_region` VALUES (18529, '安福县', 3, 18330, 'A');
INSERT INTO `wa_region` VALUES (18550, '永新县', 3, 18330, 'Y');
INSERT INTO `wa_region` VALUES (18575, '井冈山市', 3, 18330, 'J');
INSERT INTO `wa_region` VALUES (18598, '宜春市', 2, 17359, 'Y');
INSERT INTO `wa_region` VALUES (18599, '市辖区', 3, 18598, 'S');
INSERT INTO `wa_region` VALUES (18600, '袁州区', 3, 18598, 'Y');
INSERT INTO `wa_region` VALUES (18639, '奉新县', 3, 18598, 'F');
INSERT INTO `wa_region` VALUES (18659, '万载县', 3, 18598, 'W');
INSERT INTO `wa_region` VALUES (18678, '上高县', 3, 18598, 'S');
INSERT INTO `wa_region` VALUES (18696, '宜丰县', 3, 18598, 'Y');
INSERT INTO `wa_region` VALUES (18714, '靖安县', 3, 18598, 'J');
INSERT INTO `wa_region` VALUES (18727, '铜鼓县', 3, 18598, 'T');
INSERT INTO `wa_region` VALUES (18741, '丰城市', 3, 18598, 'F');
INSERT INTO `wa_region` VALUES (18777, '樟树市', 3, 18598, 'Z');
INSERT INTO `wa_region` VALUES (18799, '高安市', 3, 18598, 'G');
INSERT INTO `wa_region` VALUES (18829, '抚州市', 2, 17359, 'F');
INSERT INTO `wa_region` VALUES (18830, '市辖区', 3, 18829, 'S');
INSERT INTO `wa_region` VALUES (18831, '临川区', 3, 18829, 'L');
INSERT INTO `wa_region` VALUES (18869, '南城县', 3, 18829, 'N');
INSERT INTO `wa_region` VALUES (18882, '黎川县', 3, 18829, 'L');
INSERT INTO `wa_region` VALUES (18900, '南丰县', 3, 18829, 'N');
INSERT INTO `wa_region` VALUES (18915, '崇仁县', 3, 18829, 'C');
INSERT INTO `wa_region` VALUES (18931, '乐安县', 3, 18829, 'L');
INSERT INTO `wa_region` VALUES (18949, '宜黄县', 3, 18829, 'Y');
INSERT INTO `wa_region` VALUES (18965, '金溪县', 3, 18829, 'J');
INSERT INTO `wa_region` VALUES (18980, '资溪县', 3, 18829, 'Z');
INSERT INTO `wa_region` VALUES (18988, '东乡县', 3, 18829, 'D');
INSERT INTO `wa_region` VALUES (19010, '广昌县', 3, 18829, 'G');
INSERT INTO `wa_region` VALUES (19024, '上饶市', 2, 17359, 'S');
INSERT INTO `wa_region` VALUES (19025, '市辖区', 3, 19024, 'S');
INSERT INTO `wa_region` VALUES (19026, '信州区', 3, 19024, 'X');
INSERT INTO `wa_region` VALUES (19038, '上饶县', 3, 19024, 'S');
INSERT INTO `wa_region` VALUES (19062, '广丰县', 3, 19024, 'G');
INSERT INTO `wa_region` VALUES (19088, '玉山县', 3, 19024, 'Y');
INSERT INTO `wa_region` VALUES (19108, '铅山县', 3, 19024, 'Q');
INSERT INTO `wa_region` VALUES (19136, '横峰县', 3, 19024, 'H');
INSERT INTO `wa_region` VALUES (19151, '弋阳县', 3, 19024, 'Y');
INSERT INTO `wa_region` VALUES (19171, '余干县', 3, 19024, 'Y');
INSERT INTO `wa_region` VALUES (19202, '鄱阳县', 3, 19024, 'P');
INSERT INTO `wa_region` VALUES (19234, '万年县', 3, 19024, 'W');
INSERT INTO `wa_region` VALUES (19248, '婺源县', 3, 19024, 'W');
INSERT INTO `wa_region` VALUES (19265, '德兴市', 3, 19024, 'D');
INSERT INTO `wa_region` VALUES (19280, '山东省', 1, 0, 'S');
INSERT INTO `wa_region` VALUES (19281, '济南市', 2, 19280, 'J');
INSERT INTO `wa_region` VALUES (19282, '市辖区', 3, 19281, 'S');
INSERT INTO `wa_region` VALUES (19283, '历下区', 3, 19281, 'L');
INSERT INTO `wa_region` VALUES (19295, '市中区', 3, 19281, 'S');
INSERT INTO `wa_region` VALUES (19311, '槐荫区', 3, 19281, 'H');
INSERT INTO `wa_region` VALUES (19326, '天桥区', 3, 19281, 'T');
INSERT INTO `wa_region` VALUES (19342, '历城区', 3, 19281, 'L');
INSERT INTO `wa_region` VALUES (19359, '长清区', 3, 19281, 'C');
INSERT INTO `wa_region` VALUES (19370, '平阴县', 3, 19281, 'P');
INSERT INTO `wa_region` VALUES (19378, '济阳县', 3, 19281, 'J');
INSERT INTO `wa_region` VALUES (19387, '商河县', 3, 19281, 'S');
INSERT INTO `wa_region` VALUES (19400, '章丘市', 3, 19281, 'Z');
INSERT INTO `wa_region` VALUES (19421, '青岛市', 2, 19280, 'Q');
INSERT INTO `wa_region` VALUES (19422, '市辖区', 3, 19421, 'S');
INSERT INTO `wa_region` VALUES (19423, '市南区', 3, 19421, 'S');
INSERT INTO `wa_region` VALUES (19438, '市北区', 3, 19421, 'S');
INSERT INTO `wa_region` VALUES (19456, '四方区', 3, 19421, 'S');
INSERT INTO `wa_region` VALUES (19464, '黄岛区', 3, 19421, 'H');
INSERT INTO `wa_region` VALUES (19471, '崂山区', 3, 19421, 'L');
INSERT INTO `wa_region` VALUES (19476, '李沧区', 3, 19421, 'L');
INSERT INTO `wa_region` VALUES (19488, '城阳区', 3, 19421, 'C');
INSERT INTO `wa_region` VALUES (19497, '胶州市', 3, 19421, 'J');
INSERT INTO `wa_region` VALUES (19516, '即墨市', 3, 19421, 'J');
INSERT INTO `wa_region` VALUES (19540, '平度市', 3, 19421, 'P');
INSERT INTO `wa_region` VALUES (19572, '胶南市', 3, 19421, 'J');
INSERT INTO `wa_region` VALUES (19590, '莱西市', 3, 19421, 'L');
INSERT INTO `wa_region` VALUES (19608, '淄博市', 2, 19280, 'Z');
INSERT INTO `wa_region` VALUES (19609, '市辖区', 3, 19608, 'S');
INSERT INTO `wa_region` VALUES (19610, '淄川区', 3, 19608, 'Z');
INSERT INTO `wa_region` VALUES (19632, '张店区', 3, 19608, 'Z');
INSERT INTO `wa_region` VALUES (19649, '博山区', 3, 19608, 'B');
INSERT INTO `wa_region` VALUES (19663, '临淄区', 3, 19608, 'L');
INSERT INTO `wa_region` VALUES (19678, '周村区', 3, 19608, 'Z');
INSERT INTO `wa_region` VALUES (19688, '桓台县', 3, 19608, 'H');
INSERT INTO `wa_region` VALUES (19700, '高青县', 3, 19608, 'G');
INSERT INTO `wa_region` VALUES (19710, '沂源县', 3, 19608, 'Y');
INSERT INTO `wa_region` VALUES (19724, '枣庄市', 2, 19280, 'Z');
INSERT INTO `wa_region` VALUES (19725, '市辖区', 3, 19724, 'S');
INSERT INTO `wa_region` VALUES (19726, '市中区', 3, 19724, 'S');
INSERT INTO `wa_region` VALUES (19738, '薛城区', 3, 19724, 'X');
INSERT INTO `wa_region` VALUES (19748, '峄城区', 3, 19724, 'Y');
INSERT INTO `wa_region` VALUES (19756, '台儿庄区', 3, 19724, 'T');
INSERT INTO `wa_region` VALUES (19763, '山亭区', 3, 19724, 'S');
INSERT INTO `wa_region` VALUES (19774, '滕州市', 3, 19724, 'T');
INSERT INTO `wa_region` VALUES (19796, '东营市', 2, 19280, 'D');
INSERT INTO `wa_region` VALUES (19797, '市辖区', 3, 19796, 'S');
INSERT INTO `wa_region` VALUES (19798, '东营区', 3, 19796, 'D');
INSERT INTO `wa_region` VALUES (19809, '河口区', 3, 19796, 'H');
INSERT INTO `wa_region` VALUES (19817, '垦利县', 3, 19796, 'K');
INSERT INTO `wa_region` VALUES (19825, '利津县', 3, 19796, 'L');
INSERT INTO `wa_region` VALUES (19835, '广饶县', 3, 19796, 'G');
INSERT INTO `wa_region` VALUES (19846, '烟台市', 2, 19280, 'Y');
INSERT INTO `wa_region` VALUES (19847, '市辖区', 3, 19846, 'S');
INSERT INTO `wa_region` VALUES (19848, '芝罘区', 3, 19846, 'Z');
INSERT INTO `wa_region` VALUES (19861, '福山区', 3, 19846, 'F');
INSERT INTO `wa_region` VALUES (19873, '牟平区', 3, 19846, 'M');
INSERT INTO `wa_region` VALUES (19887, '莱山区', 3, 19846, 'L');
INSERT INTO `wa_region` VALUES (19893, '长岛县', 3, 19846, 'C');
INSERT INTO `wa_region` VALUES (19902, '龙口市', 3, 19846, 'L');
INSERT INTO `wa_region` VALUES (19916, '莱阳市', 3, 19846, 'L');
INSERT INTO `wa_region` VALUES (19935, '莱州市', 3, 19846, 'L');
INSERT INTO `wa_region` VALUES (19952, '蓬莱市', 3, 19846, 'P');
INSERT INTO `wa_region` VALUES (19965, '招远市', 3, 19846, 'Z');
INSERT INTO `wa_region` VALUES (19980, '栖霞市', 3, 19846, 'Q');
INSERT INTO `wa_region` VALUES (19996, '海阳市', 3, 19846, 'H');
INSERT INTO `wa_region` VALUES (20012, '潍坊市', 2, 19280, 'W');
INSERT INTO `wa_region` VALUES (20013, '市辖区', 3, 20012, 'S');
INSERT INTO `wa_region` VALUES (20014, '潍城区', 3, 20012, 'W');
INSERT INTO `wa_region` VALUES (20023, '寒亭区', 3, 20012, 'H');
INSERT INTO `wa_region` VALUES (20034, '坊子区', 3, 20012, 'F');
INSERT INTO `wa_region` VALUES (20043, '奎文区', 3, 20012, 'K');
INSERT INTO `wa_region` VALUES (20055, '临朐县', 3, 20012, 'L');
INSERT INTO `wa_region` VALUES (20074, '昌乐县', 3, 20012, 'C');
INSERT INTO `wa_region` VALUES (20091, '青州市', 3, 20012, 'Q');
INSERT INTO `wa_region` VALUES (20113, '诸城市', 3, 20012, 'Z');
INSERT INTO `wa_region` VALUES (20137, '寿光市', 3, 20012, 'S');
INSERT INTO `wa_region` VALUES (20155, '安丘市', 3, 20012, 'A');
INSERT INTO `wa_region` VALUES (20179, '高密市', 3, 20012, 'G');
INSERT INTO `wa_region` VALUES (20200, '昌邑市', 3, 20012, 'C');
INSERT INTO `wa_region` VALUES (20216, '济宁市', 2, 19280, 'J');
INSERT INTO `wa_region` VALUES (20217, '市辖区', 3, 20216, 'S');
INSERT INTO `wa_region` VALUES (20218, '市中区', 3, 20216, 'S');
INSERT INTO `wa_region` VALUES (20227, '任城区', 3, 20216, 'R');
INSERT INTO `wa_region` VALUES (20257, '鱼台县', 3, 20216, 'Y');
INSERT INTO `wa_region` VALUES (20268, '金乡县', 3, 20216, 'J');
INSERT INTO `wa_region` VALUES (20282, '嘉祥县', 3, 20216, 'J');
INSERT INTO `wa_region` VALUES (20298, '汶上县', 3, 20216, 'W');
INSERT INTO `wa_region` VALUES (20313, '泗水县', 3, 20216, 'S');
INSERT INTO `wa_region` VALUES (20327, '梁山县', 3, 20216, 'L');
INSERT INTO `wa_region` VALUES (20342, '曲阜市', 3, 20216, 'Q');
INSERT INTO `wa_region` VALUES (20355, '兖州市', 3, 20216, 'Y');
INSERT INTO `wa_region` VALUES (20368, '邹城市', 3, 20216, 'Z');
INSERT INTO `wa_region` VALUES (20386, '泰安市', 2, 19280, 'T');
INSERT INTO `wa_region` VALUES (20387, '市辖区', 3, 20386, 'S');
INSERT INTO `wa_region` VALUES (20388, '泰山区', 3, 20386, 'T');
INSERT INTO `wa_region` VALUES (20397, '岱岳区', 3, 20386, 'D');
INSERT INTO `wa_region` VALUES (20416, '宁阳县', 3, 20386, 'N');
INSERT INTO `wa_region` VALUES (20429, '东平县', 3, 20386, 'D');
INSERT INTO `wa_region` VALUES (20444, '新泰市', 3, 20386, 'X');
INSERT INTO `wa_region` VALUES (20465, '肥城市', 3, 20386, 'F');
INSERT INTO `wa_region` VALUES (20480, '威海市', 2, 19280, 'W');
INSERT INTO `wa_region` VALUES (20481, '市辖区', 3, 20480, 'S');
INSERT INTO `wa_region` VALUES (20482, '环翠区', 3, 20480, 'H');
INSERT INTO `wa_region` VALUES (20500, '文登市', 3, 20480, 'W');
INSERT INTO `wa_region` VALUES (20519, '荣成市', 3, 20480, 'R');
INSERT INTO `wa_region` VALUES (20542, '乳山市', 3, 20480, 'R');
INSERT INTO `wa_region` VALUES (20558, '日照市', 2, 19280, 'R');
INSERT INTO `wa_region` VALUES (20559, '市辖区', 3, 20558, 'S');
INSERT INTO `wa_region` VALUES (20560, '东港区', 3, 20558, 'D');
INSERT INTO `wa_region` VALUES (20573, '岚山区', 3, 20558, 'L');
INSERT INTO `wa_region` VALUES (20583, '五莲县', 3, 20558, 'W');
INSERT INTO `wa_region` VALUES (20596, '莒县', 3, 20558, 'J');
INSERT INTO `wa_region` VALUES (20618, '莱芜市', 2, 19280, 'L');
INSERT INTO `wa_region` VALUES (20619, '市辖区', 3, 20618, 'S');
INSERT INTO `wa_region` VALUES (20620, '莱城区', 3, 20618, 'L');
INSERT INTO `wa_region` VALUES (20636, '钢城区', 3, 20618, 'G');
INSERT INTO `wa_region` VALUES (20642, '临沂市', 2, 19280, 'L');
INSERT INTO `wa_region` VALUES (20643, '临沂市辖区', 3, 20642, 'L');
INSERT INTO `wa_region` VALUES (20644, '兰山区', 3, 20642, 'L');
INSERT INTO `wa_region` VALUES (20656, '罗庄区', 3, 20642, 'L');
INSERT INTO `wa_region` VALUES (20665, '河东区', 3, 20642, 'H');
INSERT INTO `wa_region` VALUES (20678, '沂南县', 3, 20642, 'Y');
INSERT INTO `wa_region` VALUES (20696, '郯城县', 3, 20642, 'T');
INSERT INTO `wa_region` VALUES (20714, '沂水县', 3, 20642, 'Y');
INSERT INTO `wa_region` VALUES (20734, '苍山县', 3, 20642, 'C');
INSERT INTO `wa_region` VALUES (20756, '费县', 3, 20642, 'F');
INSERT INTO `wa_region` VALUES (20775, '平邑县', 3, 20642, 'P');
INSERT INTO `wa_region` VALUES (20792, '莒南县', 3, 20642, 'J');
INSERT INTO `wa_region` VALUES (20811, '蒙阴县', 3, 20642, 'M');
INSERT INTO `wa_region` VALUES (20823, '临沭县', 3, 20642, 'L');
INSERT INTO `wa_region` VALUES (20836, '德州市', 2, 19280, 'D');
INSERT INTO `wa_region` VALUES (20837, '市辖区', 3, 20836, 'S');
INSERT INTO `wa_region` VALUES (20838, '德城区', 3, 20836, 'D');
INSERT INTO `wa_region` VALUES (20850, '陵县', 3, 20836, 'L');
INSERT INTO `wa_region` VALUES (20864, '宁津县', 3, 20836, 'N');
INSERT INTO `wa_region` VALUES (20876, '庆云县', 3, 20836, 'Q');
INSERT INTO `wa_region` VALUES (20886, '临邑县', 3, 20836, 'L');
INSERT INTO `wa_region` VALUES (20899, '齐河县', 3, 20836, 'Q');
INSERT INTO `wa_region` VALUES (20914, '平原县', 3, 20836, 'P');
INSERT INTO `wa_region` VALUES (20927, '夏津县', 3, 20836, 'X');
INSERT INTO `wa_region` VALUES (20942, '武城县', 3, 20836, 'W');
INSERT INTO `wa_region` VALUES (20952, '乐陵市', 3, 20836, 'L');
INSERT INTO `wa_region` VALUES (20969, '禹城市', 3, 20836, 'Y');
INSERT INTO `wa_region` VALUES (20981, '聊城市', 2, 19280, 'L');
INSERT INTO `wa_region` VALUES (20982, '市辖区', 3, 20981, 'S');
INSERT INTO `wa_region` VALUES (20983, '东昌府区', 3, 20981, 'D');
INSERT INTO `wa_region` VALUES (21004, '阳谷县', 3, 20981, 'Y');
INSERT INTO `wa_region` VALUES (21023, '莘县', 3, 20981, 'S');
INSERT INTO `wa_region` VALUES (21046, '茌平县', 3, 20981, 'C');
INSERT INTO `wa_region` VALUES (21063, '东阿县', 3, 20981, 'D');
INSERT INTO `wa_region` VALUES (21075, '冠县', 3, 20981, 'G');
INSERT INTO `wa_region` VALUES (21093, '高唐县', 3, 20981, 'G');
INSERT INTO `wa_region` VALUES (21106, '临清市', 3, 20981, 'L');
INSERT INTO `wa_region` VALUES (21123, '滨州市', 2, 19280, 'B');
INSERT INTO `wa_region` VALUES (21124, '市辖区', 3, 21123, 'S');
INSERT INTO `wa_region` VALUES (21125, '滨城区', 3, 21123, 'B');
INSERT INTO `wa_region` VALUES (21141, '惠民县', 3, 21123, 'H');
INSERT INTO `wa_region` VALUES (21156, '阳信县', 3, 21123, 'Y');
INSERT INTO `wa_region` VALUES (21166, '无棣县', 3, 21123, 'W');
INSERT INTO `wa_region` VALUES (21178, '沾化县', 3, 21123, 'Z');
INSERT INTO `wa_region` VALUES (21190, '博兴县', 3, 21123, 'B');
INSERT INTO `wa_region` VALUES (21201, '邹平县', 3, 21123, 'Z');
INSERT INTO `wa_region` VALUES (21218, '菏泽市', 2, 19280, 'H');
INSERT INTO `wa_region` VALUES (21219, '市辖区', 3, 21218, 'S');
INSERT INTO `wa_region` VALUES (21220, '牡丹区', 3, 21218, 'M');
INSERT INTO `wa_region` VALUES (21245, '曹县', 3, 21218, 'C');
INSERT INTO `wa_region` VALUES (21271, '单县', 3, 21218, 'D');
INSERT INTO `wa_region` VALUES (21292, '成武县', 3, 21218, 'C');
INSERT INTO `wa_region` VALUES (21305, '巨野县', 3, 21218, 'J');
INSERT INTO `wa_region` VALUES (21322, '郓城县', 3, 21218, 'Y');
INSERT INTO `wa_region` VALUES (21344, '鄄城县', 3, 21218, 'J');
INSERT INTO `wa_region` VALUES (21361, '定陶县', 3, 21218, 'D');
INSERT INTO `wa_region` VALUES (21373, '东明县', 3, 21218, 'D');
INSERT INTO `wa_region` VALUES (21387, '河南省', 1, 0, 'H');
INSERT INTO `wa_region` VALUES (21388, '郑州市', 2, 21387, 'Z');
INSERT INTO `wa_region` VALUES (21389, '市辖区', 3, 21388, 'S');
INSERT INTO `wa_region` VALUES (21390, '中原区', 3, 21388, 'Z');
INSERT INTO `wa_region` VALUES (21404, '二七区', 3, 21388, 'E');
INSERT INTO `wa_region` VALUES (21420, '管城回族区', 3, 21388, 'G');
INSERT INTO `wa_region` VALUES (21435, '金水区', 3, 21388, 'J');
INSERT INTO `wa_region` VALUES (21453, '上街区', 3, 21388, 'S');
INSERT INTO `wa_region` VALUES (21460, '惠济区', 3, 21388, 'H');
INSERT INTO `wa_region` VALUES (21469, '中牟县', 3, 21388, 'Z');
INSERT INTO `wa_region` VALUES (21487, '巩义市', 3, 21388, 'G');
INSERT INTO `wa_region` VALUES (21508, '荥阳市', 3, 21388, 'X');
INSERT INTO `wa_region` VALUES (21523, '新密市', 3, 21388, 'X');
INSERT INTO `wa_region` VALUES (21542, '新郑市', 3, 21388, 'X');
INSERT INTO `wa_region` VALUES (21558, '登封市', 3, 21388, 'D');
INSERT INTO `wa_region` VALUES (21575, '开封市', 2, 21387, 'K');
INSERT INTO `wa_region` VALUES (21576, '市辖区', 3, 21575, 'S');
INSERT INTO `wa_region` VALUES (21577, '龙亭区', 3, 21575, 'L');
INSERT INTO `wa_region` VALUES (21584, '顺河区', 3, 21575, 'S');
INSERT INTO `wa_region` VALUES (21593, '鼓楼区', 3, 21575, 'G');
INSERT INTO `wa_region` VALUES (21602, '禹王台区', 3, 21575, 'Y');
INSERT INTO `wa_region` VALUES (21610, '金明区', 3, 21575, 'J');
INSERT INTO `wa_region` VALUES (21618, '杞县', 3, 21575, 'Q');
INSERT INTO `wa_region` VALUES (21640, '通许县', 3, 21575, 'T');
INSERT INTO `wa_region` VALUES (21653, '尉氏县', 3, 21575, 'W');
INSERT INTO `wa_region` VALUES (21671, '开封县', 3, 21575, 'K');
INSERT INTO `wa_region` VALUES (21687, '兰考县', 3, 21575, 'L');
INSERT INTO `wa_region` VALUES (21711, '洛阳市', 2, 21387, 'L');
INSERT INTO `wa_region` VALUES (21712, '市辖区', 3, 21711, 'S');
INSERT INTO `wa_region` VALUES (21713, '老城区', 3, 21711, 'L');
INSERT INTO `wa_region` VALUES (21722, '西工区', 3, 21711, 'X');
INSERT INTO `wa_region` VALUES (21733, '廛河回族区', 3, 21711, 'C');
INSERT INTO `wa_region` VALUES (21742, '涧西区', 3, 21711, 'J');
INSERT INTO `wa_region` VALUES (21758, '吉利区', 3, 21711, 'J');
INSERT INTO `wa_region` VALUES (21761, '洛龙区', 3, 21711, 'L');
INSERT INTO `wa_region` VALUES (21770, '孟津县', 3, 21711, 'M');
INSERT INTO `wa_region` VALUES (21781, '新安县', 3, 21711, 'X');
INSERT INTO `wa_region` VALUES (21794, '栾川县', 3, 21711, 'L');
INSERT INTO `wa_region` VALUES (21809, '嵩县', 3, 21711, 'S');
INSERT INTO `wa_region` VALUES (21829, '汝阳县', 3, 21711, 'R');
INSERT INTO `wa_region` VALUES (21844, '宜阳县', 3, 21711, 'Y');
INSERT INTO `wa_region` VALUES (21862, '洛宁县', 3, 21711, 'L');
INSERT INTO `wa_region` VALUES (21881, '伊川县', 3, 21711, 'Y');
INSERT INTO `wa_region` VALUES (21896, '偃师市', 3, 21711, 'Y');
INSERT INTO `wa_region` VALUES (21913, '平顶山市', 2, 21387, 'P');
INSERT INTO `wa_region` VALUES (21914, '市辖区', 3, 21913, 'S');
INSERT INTO `wa_region` VALUES (21915, '新华区', 3, 21913, 'X');
INSERT INTO `wa_region` VALUES (21928, '卫东区', 3, 21913, 'W');
INSERT INTO `wa_region` VALUES (21940, '石龙区', 3, 21913, 'S');
INSERT INTO `wa_region` VALUES (21945, '湛河区', 3, 21913, 'Z');
INSERT INTO `wa_region` VALUES (21954, '宝丰县', 3, 21913, 'B');
INSERT INTO `wa_region` VALUES (21968, '叶  县', 3, 21913, 'Y');
INSERT INTO `wa_region` VALUES (21987, '鲁山县', 3, 21913, 'L');
INSERT INTO `wa_region` VALUES (22009, '郏  县', 3, 21913, 'J');
INSERT INTO `wa_region` VALUES (22024, '舞钢市', 3, 21913, 'W');
INSERT INTO `wa_region` VALUES (22037, '汝州市', 3, 21913, 'R');
INSERT INTO `wa_region` VALUES (22058, '安阳市', 2, 21387, 'A');
INSERT INTO `wa_region` VALUES (22059, '市辖区', 3, 22058, 'S');
INSERT INTO `wa_region` VALUES (22060, '文峰区', 3, 22058, 'W');
INSERT INTO `wa_region` VALUES (22080, '北关区', 3, 22058, 'B');
INSERT INTO `wa_region` VALUES (22090, '殷都区', 3, 22058, 'Y');
INSERT INTO `wa_region` VALUES (22101, '龙安区', 3, 22058, 'L');
INSERT INTO `wa_region` VALUES (22111, '安阳县', 3, 22058, 'A');
INSERT INTO `wa_region` VALUES (22133, '汤阴县', 3, 22058, 'T');
INSERT INTO `wa_region` VALUES (22144, '滑县', 3, 22058, 'H');
INSERT INTO `wa_region` VALUES (22167, '内黄县', 3, 22058, 'N');
INSERT INTO `wa_region` VALUES (22185, '林州市', 3, 22058, 'L');
INSERT INTO `wa_region` VALUES (22206, '鹤壁市', 2, 21387, 'H');
INSERT INTO `wa_region` VALUES (22207, '市辖区', 3, 22206, 'S');
INSERT INTO `wa_region` VALUES (22208, '鹤山区', 3, 22206, 'H');
INSERT INTO `wa_region` VALUES (22216, '山城区', 3, 22206, 'S');
INSERT INTO `wa_region` VALUES (22224, '淇滨区', 3, 22206, 'Q');
INSERT INTO `wa_region` VALUES (22232, '浚县', 3, 22206, 'J');
INSERT INTO `wa_region` VALUES (22243, '淇县', 3, 22206, 'Q');
INSERT INTO `wa_region` VALUES (22251, '新乡市', 2, 21387, 'X');
INSERT INTO `wa_region` VALUES (22252, '市辖区', 3, 22251, 'S');
INSERT INTO `wa_region` VALUES (22253, '红旗区', 3, 22251, 'H');
INSERT INTO `wa_region` VALUES (22264, '卫滨区', 3, 22251, 'W');
INSERT INTO `wa_region` VALUES (22273, '凤泉区', 3, 22251, 'F');
INSERT INTO `wa_region` VALUES (22279, '牧野区', 3, 22251, 'M');
INSERT INTO `wa_region` VALUES (22290, '新乡县', 3, 22251, 'X');
INSERT INTO `wa_region` VALUES (22299, '获嘉县', 3, 22251, 'H');
INSERT INTO `wa_region` VALUES (22312, '原阳县', 3, 22251, 'Y');
INSERT INTO `wa_region` VALUES (22330, '延津县', 3, 22251, 'Y');
INSERT INTO `wa_region` VALUES (22347, '封丘县', 3, 22251, 'F');
INSERT INTO `wa_region` VALUES (22367, '长垣县', 3, 22251, 'C');
INSERT INTO `wa_region` VALUES (22386, '卫辉市', 3, 22251, 'W');
INSERT INTO `wa_region` VALUES (22400, '辉县市', 3, 22251, 'H');
INSERT INTO `wa_region` VALUES (22423, '焦作市', 2, 21387, 'J');
INSERT INTO `wa_region` VALUES (22424, '市辖区', 3, 22423, 'S');
INSERT INTO `wa_region` VALUES (22425, '解放区', 3, 22423, 'J');
INSERT INTO `wa_region` VALUES (22435, '中站区', 3, 22423, 'Z');
INSERT INTO `wa_region` VALUES (22446, '马村区', 3, 22423, 'M');
INSERT INTO `wa_region` VALUES (22454, '山阳区', 3, 22423, 'S');
INSERT INTO `wa_region` VALUES (22465, '修武县', 3, 22423, 'X');
INSERT INTO `wa_region` VALUES (22475, '博爱县', 3, 22423, 'B');
INSERT INTO `wa_region` VALUES (22487, '武陟县', 3, 22423, 'W');
INSERT INTO `wa_region` VALUES (22503, '温县', 3, 22423, 'W');
INSERT INTO `wa_region` VALUES (22515, '济源市', 3, 22423, 'J');
INSERT INTO `wa_region` VALUES (22532, '沁阳市', 3, 22423, 'Q');
INSERT INTO `wa_region` VALUES (22546, '孟州市', 3, 22423, 'M');
INSERT INTO `wa_region` VALUES (22558, '濮阳市', 2, 21387, 'P');
INSERT INTO `wa_region` VALUES (22559, '市辖区', 3, 22558, 'S');
INSERT INTO `wa_region` VALUES (22560, '华龙区', 3, 22558, 'H');
INSERT INTO `wa_region` VALUES (22578, '清丰县', 3, 22558, 'Q');
INSERT INTO `wa_region` VALUES (22596, '南乐县', 3, 22558, 'N');
INSERT INTO `wa_region` VALUES (22609, '范县', 3, 22558, 'F');
INSERT INTO `wa_region` VALUES (22622, '台前县', 3, 22558, 'T');
INSERT INTO `wa_region` VALUES (22632, '濮阳县', 3, 22558, 'P');
INSERT INTO `wa_region` VALUES (22655, '许昌市', 2, 21387, 'X');
INSERT INTO `wa_region` VALUES (22656, '市辖区', 3, 22655, 'S');
INSERT INTO `wa_region` VALUES (22657, '魏都区', 3, 22655, 'W');
INSERT INTO `wa_region` VALUES (22671, '许昌县', 3, 22655, 'X');
INSERT INTO `wa_region` VALUES (22688, '鄢陵县', 3, 22655, 'Y');
INSERT INTO `wa_region` VALUES (22701, '襄城县', 3, 22655, 'X');
INSERT INTO `wa_region` VALUES (22718, '禹州市', 3, 22655, 'Y');
INSERT INTO `wa_region` VALUES (22745, '长葛市', 3, 22655, 'C');
INSERT INTO `wa_region` VALUES (22762, '漯河市', 2, 21387, 'L');
INSERT INTO `wa_region` VALUES (22763, '市辖区', 3, 22762, 'S');
INSERT INTO `wa_region` VALUES (22764, '源汇区', 3, 22762, 'Y');
INSERT INTO `wa_region` VALUES (22773, '郾城区', 3, 22762, 'Y');
INSERT INTO `wa_region` VALUES (22783, '召陵区', 3, 22762, 'Z');
INSERT INTO `wa_region` VALUES (22793, '舞阳县', 3, 22762, 'W');
INSERT INTO `wa_region` VALUES (22808, '临颖县', 3, 22762, 'L');
INSERT INTO `wa_region` VALUES (22824, '三门峡市', 2, 21387, 'S');
INSERT INTO `wa_region` VALUES (22825, '市辖区', 3, 22824, 'S');
INSERT INTO `wa_region` VALUES (22826, '湖滨区', 3, 22824, 'H');
INSERT INTO `wa_region` VALUES (22838, '渑池县', 3, 22824, 'M');
INSERT INTO `wa_region` VALUES (22851, '陕县', 3, 22824, 'S');
INSERT INTO `wa_region` VALUES (22865, '卢氏县', 3, 22824, 'L');
INSERT INTO `wa_region` VALUES (22885, '义马市', 3, 22824, 'Y');
INSERT INTO `wa_region` VALUES (22893, '灵宝市', 3, 22824, 'L');
INSERT INTO `wa_region` VALUES (22910, '南阳市', 2, 21387, 'N');
INSERT INTO `wa_region` VALUES (22911, '市辖区', 3, 22910, 'S');
INSERT INTO `wa_region` VALUES (22912, '宛城区', 3, 22910, 'W');
INSERT INTO `wa_region` VALUES (22930, '卧龙区', 3, 22910, 'W');
INSERT INTO `wa_region` VALUES (22951, '南召县', 3, 22910, 'N');
INSERT INTO `wa_region` VALUES (22973, '方城县', 3, 22910, 'F');
INSERT INTO `wa_region` VALUES (22992, '西峡县', 3, 22910, 'X');
INSERT INTO `wa_region` VALUES (23013, '镇平县', 3, 22910, 'Z');
INSERT INTO `wa_region` VALUES (23036, '内乡县', 3, 22910, 'N');
INSERT INTO `wa_region` VALUES (23053, '淅川县', 3, 22910, 'X');
INSERT INTO `wa_region` VALUES (23071, '社旗县', 3, 22910, 'S');
INSERT INTO `wa_region` VALUES (23087, '唐河县', 3, 22910, 'T');
INSERT INTO `wa_region` VALUES (23108, '新野县', 3, 22910, 'X');
INSERT INTO `wa_region` VALUES (23123, '桐柏县', 3, 22910, 'T');
INSERT INTO `wa_region` VALUES (23140, '邓州市', 3, 22910, 'D');
INSERT INTO `wa_region` VALUES (23170, '商丘市', 2, 21387, 'S');
INSERT INTO `wa_region` VALUES (23171, '市辖区', 3, 23170, 'S');
INSERT INTO `wa_region` VALUES (23172, '梁园区', 3, 23170, 'L');
INSERT INTO `wa_region` VALUES (23192, '睢阳区', 3, 23170, 'S');
INSERT INTO `wa_region` VALUES (23211, '民权县', 3, 23170, 'M');
INSERT INTO `wa_region` VALUES (23232, '睢县', 3, 23170, 'S');
INSERT INTO `wa_region` VALUES (23253, '宁陵县', 3, 23170, 'N');
INSERT INTO `wa_region` VALUES (23268, '柘城县', 3, 23170, 'Z');
INSERT INTO `wa_region` VALUES (23290, '虞城县', 3, 23170, 'Y');
INSERT INTO `wa_region` VALUES (23317, '夏邑县', 3, 23170, 'X');
INSERT INTO `wa_region` VALUES (23342, '永城市', 3, 23170, 'Y');
INSERT INTO `wa_region` VALUES (23372, '信阳市', 2, 21387, 'X');
INSERT INTO `wa_region` VALUES (23373, '市辖区', 3, 23372, 'S');
INSERT INTO `wa_region` VALUES (23374, '浉河区', 3, 23372, 'S');
INSERT INTO `wa_region` VALUES (23393, '平桥区', 3, 23372, 'P');
INSERT INTO `wa_region` VALUES (23414, '罗山县', 3, 23372, 'L');
INSERT INTO `wa_region` VALUES (23435, '光山县', 3, 23372, 'G');
INSERT INTO `wa_region` VALUES (23455, '新县', 3, 23372, 'X');
INSERT INTO `wa_region` VALUES (23471, '商城县', 3, 23372, 'S');
INSERT INTO `wa_region` VALUES (23492, '固始县', 3, 23372, 'G');
INSERT INTO `wa_region` VALUES (23525, '潢川县', 3, 23372, 'H');
INSERT INTO `wa_region` VALUES (23549, '淮滨县', 3, 23372, 'H');
INSERT INTO `wa_region` VALUES (23567, '息县', 3, 23372, 'X');
INSERT INTO `wa_region` VALUES (23589, '周口市', 2, 21387, 'Z');
INSERT INTO `wa_region` VALUES (23590, '市辖区', 3, 23589, 'S');
INSERT INTO `wa_region` VALUES (23591, '川汇区', 3, 23589, 'C');
INSERT INTO `wa_region` VALUES (23604, '扶沟县', 3, 23589, 'F');
INSERT INTO `wa_region` VALUES (23621, '西华县', 3, 23589, 'X');
INSERT INTO `wa_region` VALUES (23647, '商水县', 3, 23589, 'S');
INSERT INTO `wa_region` VALUES (23672, '沈丘县', 3, 23589, 'S');
INSERT INTO `wa_region` VALUES (23695, '郸城县', 3, 23589, 'D');
INSERT INTO `wa_region` VALUES (23716, '淮阳县', 3, 23589, 'H');
INSERT INTO `wa_region` VALUES (23736, '太康县', 3, 23589, 'T');
INSERT INTO `wa_region` VALUES (23766, '鹿邑县', 3, 23589, 'L');
INSERT INTO `wa_region` VALUES (23796, '项城市', 3, 23589, 'X');
INSERT INTO `wa_region` VALUES (23818, '驻马店市', 2, 21387, 'Z');
INSERT INTO `wa_region` VALUES (23819, '市辖区', 3, 23818, 'S');
INSERT INTO `wa_region` VALUES (23820, '驿城区', 3, 23818, 'Y');
INSERT INTO `wa_region` VALUES (23840, '西平县', 3, 23818, 'X');
INSERT INTO `wa_region` VALUES (23861, '上蔡县', 3, 23818, 'S');
INSERT INTO `wa_region` VALUES (23886, '平舆县', 3, 23818, 'P');
INSERT INTO `wa_region` VALUES (23905, '正阳县', 3, 23818, 'Z');
INSERT INTO `wa_region` VALUES (23926, '确山县', 3, 23818, 'Q');
INSERT INTO `wa_region` VALUES (23940, '泌阳县', 3, 23818, 'M');
INSERT INTO `wa_region` VALUES (23965, '汝南县', 3, 23818, 'R');
INSERT INTO `wa_region` VALUES (23983, '遂平县', 3, 23818, 'S');
INSERT INTO `wa_region` VALUES (23999, '新蔡县', 3, 23818, 'X');
INSERT INTO `wa_region` VALUES (24022, '湖北省', 1, 0, 'H');
INSERT INTO `wa_region` VALUES (24023, '武汉市', 2, 24022, 'W');
INSERT INTO `wa_region` VALUES (24024, '市辖区', 3, 24023, 'S');
INSERT INTO `wa_region` VALUES (24025, '江岸区', 3, 24023, 'J');
INSERT INTO `wa_region` VALUES (24043, '江汉区', 3, 24023, 'J');
INSERT INTO `wa_region` VALUES (24057, '硚口区', 3, 24023, 'Q');
INSERT INTO `wa_region` VALUES (24069, '汉阳区', 3, 24023, 'H');
INSERT INTO `wa_region` VALUES (24082, '武昌区', 3, 24023, 'W');
INSERT INTO `wa_region` VALUES (24098, '青山区', 3, 24023, 'Q');
INSERT INTO `wa_region` VALUES (24111, '洪山区', 3, 24023, 'H');
INSERT INTO `wa_region` VALUES (24129, '东西湖区', 3, 24023, 'D');
INSERT INTO `wa_region` VALUES (24142, '汉南区', 3, 24023, 'H');
INSERT INTO `wa_region` VALUES (24150, '蔡甸区', 3, 24023, 'C');
INSERT INTO `wa_region` VALUES (24165, '江夏区', 3, 24023, 'J');
INSERT INTO `wa_region` VALUES (24185, '黄陂区', 3, 24023, 'H');
INSERT INTO `wa_region` VALUES (24205, '武汉市新洲区', 3, 24023, 'W');
INSERT INTO `wa_region` VALUES (24224, '黄石市', 2, 24022, 'H');
INSERT INTO `wa_region` VALUES (24225, '市辖区', 3, 24224, 'S');
INSERT INTO `wa_region` VALUES (24226, '黄石港区', 3, 24224, 'H');
INSERT INTO `wa_region` VALUES (24233, '西塞山区', 3, 24224, 'X');
INSERT INTO `wa_region` VALUES (24242, '下陆区', 3, 24224, 'X');
INSERT INTO `wa_region` VALUES (24247, '铁山区', 3, 24224, 'T');
INSERT INTO `wa_region` VALUES (24250, '阳新县', 3, 24224, 'Y');
INSERT INTO `wa_region` VALUES (24273, '大冶市', 3, 24224, 'D');
INSERT INTO `wa_region` VALUES (24291, '十堰市', 2, 24022, 'S');
INSERT INTO `wa_region` VALUES (24292, '市辖区', 3, 24291, 'S');
INSERT INTO `wa_region` VALUES (24293, '茅箭区', 3, 24291, 'M');
INSERT INTO `wa_region` VALUES (24302, '张湾区', 3, 24291, 'Z');
INSERT INTO `wa_region` VALUES (24314, '郧县', 3, 24291, 'Y');
INSERT INTO `wa_region` VALUES (24335, '郧西县', 3, 24291, 'Y');
INSERT INTO `wa_region` VALUES (24354, '竹山县', 3, 24291, 'Z');
INSERT INTO `wa_region` VALUES (24374, '竹溪县', 3, 24291, 'Z');
INSERT INTO `wa_region` VALUES (24405, '房县', 3, 24291, 'F');
INSERT INTO `wa_region` VALUES (24435, '丹江口市', 3, 24291, 'D');
INSERT INTO `wa_region` VALUES (24453, '宜昌市', 2, 24022, 'Y');
INSERT INTO `wa_region` VALUES (24454, '市辖区', 3, 24453, 'S');
INSERT INTO `wa_region` VALUES (24455, '西陵区', 3, 24453, 'X');
INSERT INTO `wa_region` VALUES (24465, '伍家岗区', 3, 24453, 'W');
INSERT INTO `wa_region` VALUES (24471, '点军区', 3, 24453, 'D');
INSERT INTO `wa_region` VALUES (24477, '猇亭区', 3, 24453, 'X');
INSERT INTO `wa_region` VALUES (24481, '夷陵区', 3, 24453, 'Y');
INSERT INTO `wa_region` VALUES (24495, '远安县', 3, 24453, 'Y');
INSERT INTO `wa_region` VALUES (24503, '兴山县', 3, 24453, 'X');
INSERT INTO `wa_region` VALUES (24512, '秭归县', 3, 24453, 'Z');
INSERT INTO `wa_region` VALUES (24525, '长阳县', 3, 24453, 'C');
INSERT INTO `wa_region` VALUES (24537, '五峰县', 3, 24453, 'W');
INSERT INTO `wa_region` VALUES (24546, '宜都市', 3, 24453, 'Y');
INSERT INTO `wa_region` VALUES (24559, '当阳市', 3, 24453, 'D');
INSERT INTO `wa_region` VALUES (24570, '枝江市', 3, 24453, 'Z');
INSERT INTO `wa_region` VALUES (24580, '襄樊市', 2, 24022, 'X');
INSERT INTO `wa_region` VALUES (24581, '市辖区', 3, 24580, 'S');
INSERT INTO `wa_region` VALUES (24582, '襄城区', 3, 24580, 'X');
INSERT INTO `wa_region` VALUES (24591, '樊城区', 3, 24580, 'F');
INSERT INTO `wa_region` VALUES (24608, '襄阳区', 3, 24580, 'X');
INSERT INTO `wa_region` VALUES (24623, '南漳县', 3, 24580, 'N');
INSERT INTO `wa_region` VALUES (24635, '谷城县', 3, 24580, 'G');
INSERT INTO `wa_region` VALUES (24647, '保康县', 3, 24580, 'B');
INSERT INTO `wa_region` VALUES (24659, '老河口市', 3, 24580, 'L');
INSERT INTO `wa_region` VALUES (24674, '枣阳市', 3, 24580, 'Z');
INSERT INTO `wa_region` VALUES (24692, '宜城市', 3, 24580, 'Y');
INSERT INTO `wa_region` VALUES (24706, '鄂州市', 2, 24022, 'E');
INSERT INTO `wa_region` VALUES (24707, '市辖区', 3, 24706, 'S');
INSERT INTO `wa_region` VALUES (24708, '粱子湖区', 3, 24706, 'L');
INSERT INTO `wa_region` VALUES (24714, '华容区', 3, 24706, 'H');
INSERT INTO `wa_region` VALUES (24722, '鄂城区', 3, 24706, 'E');
INSERT INTO `wa_region` VALUES (24737, '荆门市', 2, 24022, 'J');
INSERT INTO `wa_region` VALUES (24738, '市辖区', 3, 24737, 'S');
INSERT INTO `wa_region` VALUES (24739, '东宝区', 3, 24737, 'D');
INSERT INTO `wa_region` VALUES (24749, '掇刀区', 3, 24737, 'D');
INSERT INTO `wa_region` VALUES (24755, '京山县', 3, 24737, 'J');
INSERT INTO `wa_region` VALUES (24778, '沙洋县', 3, 24737, 'S');
INSERT INTO `wa_region` VALUES (24794, '钟祥市', 3, 24737, 'Z');
INSERT INTO `wa_region` VALUES (24816, '孝感市', 2, 24022, 'X');
INSERT INTO `wa_region` VALUES (24817, '市辖区', 3, 24816, 'S');
INSERT INTO `wa_region` VALUES (24818, '孝南区', 3, 24816, 'X');
INSERT INTO `wa_region` VALUES (24838, '孝昌县', 3, 24816, 'X');
INSERT INTO `wa_region` VALUES (24853, '大悟县', 3, 24816, 'D');
INSERT INTO `wa_region` VALUES (24871, '云梦县', 3, 24816, 'Y');
INSERT INTO `wa_region` VALUES (24885, '应城市', 3, 24816, 'Y');
INSERT INTO `wa_region` VALUES (24903, '安陆市', 3, 24816, 'A');
INSERT INTO `wa_region` VALUES (24920, '汉川市', 3, 24816, 'H');
INSERT INTO `wa_region` VALUES (24949, '荆州市', 2, 24022, 'J');
INSERT INTO `wa_region` VALUES (24950, '市辖区', 3, 24949, 'S');
INSERT INTO `wa_region` VALUES (24951, '沙市区', 3, 24949, 'S');
INSERT INTO `wa_region` VALUES (24965, '荆州区', 3, 24949, 'J');
INSERT INTO `wa_region` VALUES (24978, '公安县', 3, 24949, 'G');
INSERT INTO `wa_region` VALUES (24995, '监利县', 3, 24949, 'J');
INSERT INTO `wa_region` VALUES (25019, '江陵县', 3, 24949, 'J');
INSERT INTO `wa_region` VALUES (25032, '石首市', 3, 24949, 'S');
INSERT INTO `wa_region` VALUES (25048, '洪湖市', 3, 24949, 'H');
INSERT INTO `wa_region` VALUES (25069, '松滋市', 3, 24949, 'S');
INSERT INTO `wa_region` VALUES (25086, '黄冈市', 2, 24022, 'H');
INSERT INTO `wa_region` VALUES (25087, '市辖区', 3, 25086, 'S');
INSERT INTO `wa_region` VALUES (25088, '黄州区', 3, 25086, 'H');
INSERT INTO `wa_region` VALUES (25099, '团风县', 3, 25086, 'T');
INSERT INTO `wa_region` VALUES (25112, '红安县', 3, 25086, 'H');
INSERT INTO `wa_region` VALUES (25126, '罗田县', 3, 25086, 'L');
INSERT INTO `wa_region` VALUES (25143, '英山县', 3, 25086, 'Y');
INSERT INTO `wa_region` VALUES (25158, '浠水县', 3, 25086, 'X');
INSERT INTO `wa_region` VALUES (25175, '蕲春县', 3, 25086, 'Q');
INSERT INTO `wa_region` VALUES (25192, '黄梅县', 3, 25086, 'H');
INSERT INTO `wa_region` VALUES (25211, '麻城市', 3, 25086, 'M');
INSERT INTO `wa_region` VALUES (25235, '武穴市', 3, 25086, 'W');
INSERT INTO `wa_region` VALUES (25249, '咸宁市', 2, 24022, 'X');
INSERT INTO `wa_region` VALUES (25250, '市辖区', 3, 25249, 'S');
INSERT INTO `wa_region` VALUES (25251, '咸安区', 3, 25249, 'X');
INSERT INTO `wa_region` VALUES (25266, '嘉鱼县', 3, 25249, 'J');
INSERT INTO `wa_region` VALUES (25276, '通城县', 3, 25249, 'T');
INSERT INTO `wa_region` VALUES (25290, '崇阳县', 3, 25249, 'C');
INSERT INTO `wa_region` VALUES (25303, '通山县', 3, 25249, 'T');
INSERT INTO `wa_region` VALUES (25317, '赤壁市', 3, 25249, 'C');
INSERT INTO `wa_region` VALUES (25335, '随州市', 2, 24022, 'S');
INSERT INTO `wa_region` VALUES (25336, '市辖区', 3, 25335, 'S');
INSERT INTO `wa_region` VALUES (25337, '曾都区', 3, 25335, 'Z');
INSERT INTO `wa_region` VALUES (25367, '广水市', 3, 25335, 'G');
INSERT INTO `wa_region` VALUES (25388, '恩施州', 2, 24022, 'E');
INSERT INTO `wa_region` VALUES (25389, '恩施市', 3, 25388, 'E');
INSERT INTO `wa_region` VALUES (25406, '利川市', 3, 25388, 'L');
INSERT INTO `wa_region` VALUES (25422, '建始县', 3, 25388, 'J');
INSERT INTO `wa_region` VALUES (25433, '巴东县', 3, 25388, 'B');
INSERT INTO `wa_region` VALUES (25446, '宣恩县', 3, 25388, 'X');
INSERT INTO `wa_region` VALUES (25456, '咸丰县', 3, 25388, 'X');
INSERT INTO `wa_region` VALUES (25467, '来凤县', 3, 25388, 'L');
INSERT INTO `wa_region` VALUES (25476, '鹤峰县', 3, 25388, 'H');
INSERT INTO `wa_region` VALUES (25487, '省直辖行政单位', 2, 24022, 'S');
INSERT INTO `wa_region` VALUES (25488, '仙桃市', 3, 25487, 'X');
INSERT INTO `wa_region` VALUES (25516, '潜江市', 3, 25487, 'Q');
INSERT INTO `wa_region` VALUES (25541, '天门市', 3, 25487, 'T');
INSERT INTO `wa_region` VALUES (25570, '神农架林区', 3, 25487, 'S');
INSERT INTO `wa_region` VALUES (25579, '湖南省', 1, 0, 'H');
INSERT INTO `wa_region` VALUES (25580, '长沙市', 2, 25579, 'C');
INSERT INTO `wa_region` VALUES (25581, '市辖区', 3, 25580, 'S');
INSERT INTO `wa_region` VALUES (25582, '芙蓉区', 3, 25580, 'F');
INSERT INTO `wa_region` VALUES (25596, '天心区', 3, 25580, 'T');
INSERT INTO `wa_region` VALUES (25607, '岳麓区', 3, 25580, 'Y');
INSERT INTO `wa_region` VALUES (25620, '开福区', 3, 25580, 'K');
INSERT INTO `wa_region` VALUES (25634, '雨花区', 3, 25580, 'Y');
INSERT INTO `wa_region` VALUES (25645, '长沙县', 3, 25580, 'C');
INSERT INTO `wa_region` VALUES (25666, '望城县', 3, 25580, 'W');
INSERT INTO `wa_region` VALUES (25686, '宁乡县', 3, 25580, 'N');
INSERT INTO `wa_region` VALUES (25720, '浏阳市', 3, 25580, 'L');
INSERT INTO `wa_region` VALUES (25758, '株洲市', 2, 25579, 'Z');
INSERT INTO `wa_region` VALUES (25759, '市辖区', 3, 25758, 'S');
INSERT INTO `wa_region` VALUES (25760, '荷塘区', 3, 25758, 'H');
INSERT INTO `wa_region` VALUES (25768, '芦淞区', 3, 25758, 'L');
INSERT INTO `wa_region` VALUES (25777, '石峰区', 3, 25758, 'S');
INSERT INTO `wa_region` VALUES (25785, '天元区', 3, 25758, 'T');
INSERT INTO `wa_region` VALUES (25791, '株洲县', 3, 25758, 'Z');
INSERT INTO `wa_region` VALUES (25810, '攸县', 3, 25758, 'Y');
INSERT INTO `wa_region` VALUES (25836, '茶陵县', 3, 25758, 'C');
INSERT INTO `wa_region` VALUES (25863, '炎陵县', 3, 25758, 'Y');
INSERT INTO `wa_region` VALUES (25881, '醴陵市', 3, 25758, 'L');
INSERT INTO `wa_region` VALUES (25912, '湘潭市', 2, 25579, 'X');
INSERT INTO `wa_region` VALUES (25913, '市辖区', 3, 25912, 'S');
INSERT INTO `wa_region` VALUES (25914, '雨湖区', 3, 25912, 'Y');
INSERT INTO `wa_region` VALUES (25929, '岳塘区', 3, 25912, 'Y');
INSERT INTO `wa_region` VALUES (25947, '湘潭县', 3, 25912, 'X');
INSERT INTO `wa_region` VALUES (25970, '湘乡市', 3, 25912, 'X');
INSERT INTO `wa_region` VALUES (25993, '韶山市', 3, 25912, 'S');
INSERT INTO `wa_region` VALUES (26001, '衡阳市', 2, 25579, 'H');
INSERT INTO `wa_region` VALUES (26002, '市辖区', 3, 26001, 'S');
INSERT INTO `wa_region` VALUES (26003, '珠晖区', 3, 26001, 'Z');
INSERT INTO `wa_region` VALUES (26019, '雁峰区', 3, 26001, 'Y');
INSERT INTO `wa_region` VALUES (26028, '石鼓区', 3, 26001, 'S');
INSERT INTO `wa_region` VALUES (26037, '蒸湘区', 3, 26001, 'Z');
INSERT INTO `wa_region` VALUES (26045, '南岳区', 3, 26001, 'N');
INSERT INTO `wa_region` VALUES (26051, '衡阳县', 3, 26001, 'H');
INSERT INTO `wa_region` VALUES (26080, '衡南县', 3, 26001, 'H');
INSERT INTO `wa_region` VALUES (26112, '衡山县', 3, 26001, 'H');
INSERT INTO `wa_region` VALUES (26130, '衡东县', 3, 26001, 'H');
INSERT INTO `wa_region` VALUES (26155, '祁东县', 3, 26001, 'Q');
INSERT INTO `wa_region` VALUES (26179, '耒阳市', 3, 26001, 'L');
INSERT INTO `wa_region` VALUES (26215, '常宁市', 3, 26001, 'C');
INSERT INTO `wa_region` VALUES (26242, '邵阳市', 2, 25579, 'S');
INSERT INTO `wa_region` VALUES (26243, '市辖区', 3, 26242, 'S');
INSERT INTO `wa_region` VALUES (26244, '双清区', 3, 26242, 'S');
INSERT INTO `wa_region` VALUES (26257, '大祥区', 3, 26242, 'D');
INSERT INTO `wa_region` VALUES (26272, '北塔区', 3, 26242, 'B');
INSERT INTO `wa_region` VALUES (26279, '邵东县', 3, 26242, 'S');
INSERT INTO `wa_region` VALUES (26306, '新邵县', 3, 26242, 'X');
INSERT INTO `wa_region` VALUES (26322, '邵阳县', 3, 26242, 'S');
INSERT INTO `wa_region` VALUES (26348, '隆回县', 3, 26242, 'L');
INSERT INTO `wa_region` VALUES (26375, '洞口县', 3, 26242, 'D');
INSERT INTO `wa_region` VALUES (26399, '绥宁县', 3, 26242, 'S');
INSERT INTO `wa_region` VALUES (26425, '新宁县', 3, 26242, 'X');
INSERT INTO `wa_region` VALUES (26444, '城步县', 3, 26242, 'C');
INSERT INTO `wa_region` VALUES (26465, '武冈市', 3, 26242, 'W');
INSERT INTO `wa_region` VALUES (26485, '岳阳市', 2, 25579, 'Y');
INSERT INTO `wa_region` VALUES (26486, '市辖区', 3, 26485, 'S');
INSERT INTO `wa_region` VALUES (26487, '岳阳楼区', 3, 26485, 'Y');
INSERT INTO `wa_region` VALUES (26511, '云溪区', 3, 26485, 'Y');
INSERT INTO `wa_region` VALUES (26521, '君山区', 3, 26485, 'J');
INSERT INTO `wa_region` VALUES (26529, '岳阳县', 3, 26485, 'Y');
INSERT INTO `wa_region` VALUES (26551, '华容县', 3, 26485, 'H');
INSERT INTO `wa_region` VALUES (26572, '湘阴县', 3, 26485, 'X');
INSERT INTO `wa_region` VALUES (26592, '平江县', 3, 26485, 'P');
INSERT INTO `wa_region` VALUES (26620, '汩罗市', 3, 26485, 'G');
INSERT INTO `wa_region` VALUES (26657, '临湘市', 3, 26485, 'L');
INSERT INTO `wa_region` VALUES (26683, '常德市', 2, 25579, 'C');
INSERT INTO `wa_region` VALUES (26684, '市辖区', 3, 26683, 'S');
INSERT INTO `wa_region` VALUES (26685, '武陵区', 3, 26683, 'W');
INSERT INTO `wa_region` VALUES (26702, '鼎城区', 3, 26683, 'D');
INSERT INTO `wa_region` VALUES (26741, '安乡县', 3, 26683, 'A');
INSERT INTO `wa_region` VALUES (26762, '汉寿县', 3, 26683, 'H');
INSERT INTO `wa_region` VALUES (26793, '澧县', 3, 26683, 'L');
INSERT INTO `wa_region` VALUES (26826, '临澧县', 3, 26683, 'L');
INSERT INTO `wa_region` VALUES (26844, '桃源县', 3, 26683, 'T');
INSERT INTO `wa_region` VALUES (26885, '石门县', 3, 26683, 'S');
INSERT INTO `wa_region` VALUES (26912, '津市市', 3, 26683, 'J');
INSERT INTO `wa_region` VALUES (26925, '张家界市', 2, 25579, 'Z');
INSERT INTO `wa_region` VALUES (26926, '市辖区', 3, 26925, 'S');
INSERT INTO `wa_region` VALUES (26927, '永定区', 3, 26925, 'Y');
INSERT INTO `wa_region` VALUES (26959, '武陵源区', 3, 26925, 'W');
INSERT INTO `wa_region` VALUES (26966, '慈利县', 3, 26925, 'C');
INSERT INTO `wa_region` VALUES (26998, '桑植县', 3, 26925, 'S');
INSERT INTO `wa_region` VALUES (27038, '益阳市', 2, 25579, 'Y');
INSERT INTO `wa_region` VALUES (27039, '市辖区', 3, 27038, 'S');
INSERT INTO `wa_region` VALUES (27040, '资阳区', 3, 27038, 'Z');
INSERT INTO `wa_region` VALUES (27049, '赫山区', 3, 27038, 'H');
INSERT INTO `wa_region` VALUES (27069, '南县', 3, 27038, 'N');
INSERT INTO `wa_region` VALUES (27087, '桃江县', 3, 27038, 'T');
INSERT INTO `wa_region` VALUES (27106, '安化县', 3, 27038, 'A');
INSERT INTO `wa_region` VALUES (27130, '沅江市', 3, 27038, 'Y');
INSERT INTO `wa_region` VALUES (27147, '郴州市', 2, 25579, 'C');
INSERT INTO `wa_region` VALUES (27148, '市辖区', 3, 27147, 'S');
INSERT INTO `wa_region` VALUES (27149, '北湖区', 3, 27147, 'B');
INSERT INTO `wa_region` VALUES (27168, '苏仙区', 3, 27147, 'S');
INSERT INTO `wa_region` VALUES (27188, '桂阳县', 3, 27147, 'G');
INSERT INTO `wa_region` VALUES (27228, '宜章县', 3, 27147, 'Y');
INSERT INTO `wa_region` VALUES (27256, '永兴县', 3, 27147, 'Y');
INSERT INTO `wa_region` VALUES (27282, '嘉禾县', 3, 27147, 'J');
INSERT INTO `wa_region` VALUES (27300, '临武县', 3, 27147, 'L');
INSERT INTO `wa_region` VALUES (27323, '汝城县', 3, 27147, 'R');
INSERT INTO `wa_region` VALUES (27347, '桂东县', 3, 27147, 'G');
INSERT INTO `wa_region` VALUES (27367, '安仁县', 3, 27147, 'A');
INSERT INTO `wa_region` VALUES (27389, '资兴市', 3, 27147, 'Z');
INSERT INTO `wa_region` VALUES (27418, '永州市', 2, 25579, 'Y');
INSERT INTO `wa_region` VALUES (27419, '市辖区', 3, 27418, 'S');
INSERT INTO `wa_region` VALUES (27420, '零陵区', 3, 27418, 'L');
INSERT INTO `wa_region` VALUES (27437, '冷水滩区', 3, 27418, 'L');
INSERT INTO `wa_region` VALUES (27459, '祁阳县', 3, 27418, 'Q');
INSERT INTO `wa_region` VALUES (27492, '东安县', 3, 27418, 'D');
INSERT INTO `wa_region` VALUES (27511, '双牌县', 3, 27418, 'S');
INSERT INTO `wa_region` VALUES (27527, '道县', 3, 27418, 'D');
INSERT INTO `wa_region` VALUES (27554, '江永县', 3, 27418, 'J');
INSERT INTO `wa_region` VALUES (27567, '宁远县', 3, 27418, 'N');
INSERT INTO `wa_region` VALUES (27585, '蓝山县', 3, 27418, 'L');
INSERT INTO `wa_region` VALUES (27606, '新田县', 3, 27418, 'X');
INSERT INTO `wa_region` VALUES (27626, '江华县', 3, 27418, 'J');
INSERT INTO `wa_region` VALUES (27650, '怀化市', 2, 25579, 'H');
INSERT INTO `wa_region` VALUES (27651, '市辖区', 3, 27650, 'S');
INSERT INTO `wa_region` VALUES (27652, '鹤城区', 3, 27650, 'H');
INSERT INTO `wa_region` VALUES (27667, '中方县', 3, 27650, 'Z');
INSERT INTO `wa_region` VALUES (27690, '沅陵县', 3, 27650, 'Y');
INSERT INTO `wa_region` VALUES (27714, '辰溪县', 3, 27650, 'C');
INSERT INTO `wa_region` VALUES (27745, '溆浦县', 3, 27650, 'X');
INSERT INTO `wa_region` VALUES (27789, '会同县', 3, 27650, 'H');
INSERT INTO `wa_region` VALUES (27815, '麻阳县', 3, 27650, 'M');
INSERT INTO `wa_region` VALUES (27839, '新晃县', 3, 27650, 'X');
INSERT INTO `wa_region` VALUES (27863, '芷江县', 3, 27650, 'Z');
INSERT INTO `wa_region` VALUES (27892, '靖州苗族侗族县', 3, 27650, 'J');
INSERT INTO `wa_region` VALUES (27906, '通道县', 3, 27650, 'T');
INSERT INTO `wa_region` VALUES (27930, '洪江市', 3, 27650, 'H');
INSERT INTO `wa_region` VALUES (27963, '娄底市', 2, 25579, 'L');
INSERT INTO `wa_region` VALUES (27964, '市辖区', 3, 27963, 'S');
INSERT INTO `wa_region` VALUES (27965, '娄星区', 3, 27963, 'L');
INSERT INTO `wa_region` VALUES (27980, '双峰县', 3, 27963, 'S');
INSERT INTO `wa_region` VALUES (27997, '新化县', 3, 27963, 'X');
INSERT INTO `wa_region` VALUES (28027, '冷水江市', 3, 27963, 'L');
INSERT INTO `wa_region` VALUES (28044, '涟源市', 3, 27963, 'L');
INSERT INTO `wa_region` VALUES (28065, '湘西州', 2, 25579, 'X');
INSERT INTO `wa_region` VALUES (28066, '吉首市', 3, 28065, 'J');
INSERT INTO `wa_region` VALUES (28082, '泸溪县', 3, 28065, 'L');
INSERT INTO `wa_region` VALUES (28099, '凤凰县', 3, 28065, 'F');
INSERT INTO `wa_region` VALUES (28124, '花垣县', 3, 28065, 'H');
INSERT INTO `wa_region` VALUES (28143, '保靖县', 3, 28065, 'B');
INSERT INTO `wa_region` VALUES (28161, '古丈县', 3, 28065, 'G');
INSERT INTO `wa_region` VALUES (28174, '永顺县', 3, 28065, 'Y');
INSERT INTO `wa_region` VALUES (28205, '龙山县', 3, 28065, 'L');
INSERT INTO `wa_region` VALUES (28240, '广东省', 1, 0, 'G');
INSERT INTO `wa_region` VALUES (28241, '广州市', 2, 28240, 'G');
INSERT INTO `wa_region` VALUES (28242, '市辖区', 3, 28241, 'S');
INSERT INTO `wa_region` VALUES (28243, '荔湾区', 3, 28241, 'L');
INSERT INTO `wa_region` VALUES (28266, '越秀区', 3, 28241, 'Y');
INSERT INTO `wa_region` VALUES (28289, '海珠区', 3, 28241, 'H');
INSERT INTO `wa_region` VALUES (28308, '天河区', 3, 28241, 'T');
INSERT INTO `wa_region` VALUES (28330, '白云区', 3, 28241, 'B');
INSERT INTO `wa_region` VALUES (28349, '黄埔区', 3, 28241, 'H');
INSERT INTO `wa_region` VALUES (28359, '番禺区', 3, 28241, 'F');
INSERT INTO `wa_region` VALUES (28377, '花都区', 3, 28241, 'H');
INSERT INTO `wa_region` VALUES (28386, '南沙区', 3, 28241, 'N');
INSERT INTO `wa_region` VALUES (28392, '萝岗区', 3, 28241, 'L');
INSERT INTO `wa_region` VALUES (28399, '增城市', 3, 28241, 'Z');
INSERT INTO `wa_region` VALUES (28409, '从化市', 3, 28241, 'C');
INSERT INTO `wa_region` VALUES (28421, '韶关市', 2, 28240, 'S');
INSERT INTO `wa_region` VALUES (28422, '市辖区', 3, 28421, 'S');
INSERT INTO `wa_region` VALUES (28423, '武江区', 3, 28421, 'W');
INSERT INTO `wa_region` VALUES (28431, '浈江区', 3, 28421, 'Z');
INSERT INTO `wa_region` VALUES (28448, '曲江区', 3, 28421, 'Q');
INSERT INTO `wa_region` VALUES (28463, '始兴县', 3, 28421, 'S');
INSERT INTO `wa_region` VALUES (28475, '仁化县', 3, 28421, 'R');
INSERT INTO `wa_region` VALUES (28488, '翁源县', 3, 28421, 'W');
INSERT INTO `wa_region` VALUES (28497, '乳源县', 3, 28421, 'R');
INSERT INTO `wa_region` VALUES (28509, '新丰县', 3, 28421, 'X');
INSERT INTO `wa_region` VALUES (28517, '乐昌市', 3, 28421, 'L');
INSERT INTO `wa_region` VALUES (28539, '南雄市', 3, 28421, 'N');
INSERT INTO `wa_region` VALUES (28558, '深圳市', 2, 28240, 'S');
INSERT INTO `wa_region` VALUES (28559, '市辖区', 3, 28558, 'S');
INSERT INTO `wa_region` VALUES (28560, '罗湖区', 3, 28558, 'L');
INSERT INTO `wa_region` VALUES (28571, '福田区', 3, 28558, 'F');
INSERT INTO `wa_region` VALUES (28581, '南山区', 3, 28558, 'N');
INSERT INTO `wa_region` VALUES (28590, '宝安区', 3, 28558, 'B');
INSERT INTO `wa_region` VALUES (28604, '龙岗区', 3, 28558, 'L');
INSERT INTO `wa_region` VALUES (28619, '盐田区', 3, 28558, 'Y');
INSERT INTO `wa_region` VALUES (28626, '珠海市', 2, 28240, 'Z');
INSERT INTO `wa_region` VALUES (28627, '市辖区', 3, 28626, 'S');
INSERT INTO `wa_region` VALUES (28628, '香洲区', 3, 28626, 'X');
INSERT INTO `wa_region` VALUES (28646, '斗门区', 3, 28626, 'D');
INSERT INTO `wa_region` VALUES (28654, '金湾区', 3, 28626, 'J');
INSERT INTO `wa_region` VALUES (28659, '汕头市', 2, 28240, 'S');
INSERT INTO `wa_region` VALUES (28660, '市辖区', 3, 28659, 'S');
INSERT INTO `wa_region` VALUES (28661, '龙湖区', 3, 28659, 'L');
INSERT INTO `wa_region` VALUES (28669, '金平区', 3, 28659, 'J');
INSERT INTO `wa_region` VALUES (28687, '濠江区', 3, 28659, 'H');
INSERT INTO `wa_region` VALUES (28695, '潮阳区', 3, 28659, 'C');
INSERT INTO `wa_region` VALUES (28709, '潮南区', 3, 28659, 'C');
INSERT INTO `wa_region` VALUES (28721, '澄海区', 3, 28659, 'C');
INSERT INTO `wa_region` VALUES (28733, '南澳县', 3, 28659, 'N');
INSERT INTO `wa_region` VALUES (28737, '佛山市', 2, 28240, 'F');
INSERT INTO `wa_region` VALUES (28738, '市辖区', 3, 28737, 'S');
INSERT INTO `wa_region` VALUES (28739, '禅城区', 3, 28737, 'C');
INSERT INTO `wa_region` VALUES (28744, '南海区', 3, 28737, 'N');
INSERT INTO `wa_region` VALUES (28753, '顺德区', 3, 28737, 'S');
INSERT INTO `wa_region` VALUES (28764, '三水区', 3, 28737, 'S');
INSERT INTO `wa_region` VALUES (28776, '高明区', 3, 28737, 'G');
INSERT INTO `wa_region` VALUES (28785, '江门市', 2, 28240, 'J');
INSERT INTO `wa_region` VALUES (28786, '市辖区', 3, 28785, 'S');
INSERT INTO `wa_region` VALUES (28787, '蓬江区', 3, 28785, 'P');
INSERT INTO `wa_region` VALUES (28797, '江海区', 3, 28785, 'J');
INSERT INTO `wa_region` VALUES (28803, '新会区', 3, 28785, 'X');
INSERT INTO `wa_region` VALUES (28818, '台山市', 3, 28785, 'T');
INSERT INTO `wa_region` VALUES (28837, '开平市', 3, 28785, 'K');
INSERT INTO `wa_region` VALUES (28853, '鹤山市', 3, 28785, 'H');
INSERT INTO `wa_region` VALUES (28867, '恩平市', 3, 28785, 'E');
INSERT INTO `wa_region` VALUES (28880, '湛江市', 2, 28240, 'Z');
INSERT INTO `wa_region` VALUES (28881, '市辖区', 3, 28880, 'S');
INSERT INTO `wa_region` VALUES (28882, '湛江市赤坎区', 3, 28880, 'Z');
INSERT INTO `wa_region` VALUES (28891, '湛江市霞山区', 3, 28880, 'Z');
INSERT INTO `wa_region` VALUES (28904, '湛江市坡头区', 3, 28880, 'Z');
INSERT INTO `wa_region` VALUES (28914, '湛江市麻章区', 3, 28880, 'Z');
INSERT INTO `wa_region` VALUES (28923, '遂溪县', 3, 28880, 'S');
INSERT INTO `wa_region` VALUES (28941, '徐闻县', 3, 28880, 'X');
INSERT INTO `wa_region` VALUES (28962, '廉江市', 3, 28880, 'L');
INSERT INTO `wa_region` VALUES (28984, '雷州市', 3, 28880, 'L');
INSERT INTO `wa_region` VALUES (29010, '吴川市', 3, 28880, 'W');
INSERT INTO `wa_region` VALUES (29026, '茂名市', 2, 28240, 'M');
INSERT INTO `wa_region` VALUES (29027, '市辖区', 3, 29026, 'S');
INSERT INTO `wa_region` VALUES (29028, '茂南区', 3, 29026, 'M');
INSERT INTO `wa_region` VALUES (29045, '茂港区', 3, 29026, 'M');
INSERT INTO `wa_region` VALUES (29053, '电白县', 3, 29026, 'D');
INSERT INTO `wa_region` VALUES (29075, '高州市', 3, 29026, 'G');
INSERT INTO `wa_region` VALUES (29107, '化州市', 3, 29026, 'H');
INSERT INTO `wa_region` VALUES (29138, '信宜市', 3, 29026, 'X');
INSERT INTO `wa_region` VALUES (29159, '肇庆市', 2, 28240, 'Z');
INSERT INTO `wa_region` VALUES (29160, '市辖区', 3, 29159, 'S');
INSERT INTO `wa_region` VALUES (29161, '端州区', 3, 29159, 'D');
INSERT INTO `wa_region` VALUES (29169, '鼎湖区', 3, 29159, 'D');
INSERT INTO `wa_region` VALUES (29178, '广宁县', 3, 29159, 'G');
INSERT INTO `wa_region` VALUES (29196, '怀集县', 3, 29159, 'H');
INSERT INTO `wa_region` VALUES (29217, '封开县', 3, 29159, 'F');
INSERT INTO `wa_region` VALUES (29234, '德庆县', 3, 29159, 'D');
INSERT INTO `wa_region` VALUES (29248, '高要市', 3, 29159, 'G');
INSERT INTO `wa_region` VALUES (29266, '四会市', 3, 29159, 'S');
INSERT INTO `wa_region` VALUES (29282, '惠州市', 2, 28240, 'H');
INSERT INTO `wa_region` VALUES (29283, '市辖区', 3, 29282, 'S');
INSERT INTO `wa_region` VALUES (29284, '惠城区', 3, 29282, 'H');
INSERT INTO `wa_region` VALUES (29304, '惠阳区', 3, 29282, 'H');
INSERT INTO `wa_region` VALUES (29317, '博罗县', 3, 29282, 'B');
INSERT INTO `wa_region` VALUES (29335, '惠东县', 3, 29282, 'H');
INSERT INTO `wa_region` VALUES (29355, '龙门县', 3, 29282, 'L');
INSERT INTO `wa_region` VALUES (29371, '梅州市', 2, 28240, 'M');
INSERT INTO `wa_region` VALUES (29372, '市辖区', 3, 29371, 'S');
INSERT INTO `wa_region` VALUES (29373, '梅江区', 3, 29371, 'M');
INSERT INTO `wa_region` VALUES (29380, '梅县', 3, 29371, 'M');
INSERT INTO `wa_region` VALUES (29400, '大埔县', 3, 29371, 'D');
INSERT INTO `wa_region` VALUES (29418, '丰顺县', 3, 29371, 'F');
INSERT INTO `wa_region` VALUES (29436, '五华县', 3, 29371, 'W');
INSERT INTO `wa_region` VALUES (29453, '平远县', 3, 29371, 'P');
INSERT INTO `wa_region` VALUES (29466, '蕉岭县', 3, 29371, 'J');
INSERT INTO `wa_region` VALUES (29477, '兴宁市', 3, 29371, 'X');
INSERT INTO `wa_region` VALUES (29498, '汕尾市', 2, 28240, 'S');
INSERT INTO `wa_region` VALUES (29499, '市辖区', 3, 29498, 'S');
INSERT INTO `wa_region` VALUES (29500, '城区', 3, 29498, 'C');
INSERT INTO `wa_region` VALUES (29511, '海丰县', 3, 29498, 'H');
INSERT INTO `wa_region` VALUES (29529, '陆河县', 3, 29498, 'L');
INSERT INTO `wa_region` VALUES (29538, '陆丰市', 3, 29498, 'L');
INSERT INTO `wa_region` VALUES (29568, '河源市', 2, 28240, 'H');
INSERT INTO `wa_region` VALUES (29569, '市辖区', 3, 29568, 'S');
INSERT INTO `wa_region` VALUES (29570, '源城区', 3, 29568, 'Y');
INSERT INTO `wa_region` VALUES (29578, '紫金县', 3, 29568, 'Z');
INSERT INTO `wa_region` VALUES (29599, '龙川县', 3, 29568, 'L');
INSERT INTO `wa_region` VALUES (29625, '连平县', 3, 29568, 'L');
INSERT INTO `wa_region` VALUES (29639, '和平县', 3, 29568, 'H');
INSERT INTO `wa_region` VALUES (29657, '东源县', 3, 29568, 'D');
INSERT INTO `wa_region` VALUES (29679, '阳江市', 2, 28240, 'Y');
INSERT INTO `wa_region` VALUES (29680, '市辖区', 3, 29679, 'S');
INSERT INTO `wa_region` VALUES (29681, '江城区', 3, 29679, 'J');
INSERT INTO `wa_region` VALUES (29698, '阳西县', 3, 29679, 'Y');
INSERT INTO `wa_region` VALUES (29709, '阳东县', 3, 29679, 'Y');
INSERT INTO `wa_region` VALUES (29729, '阳春市', 3, 29679, 'Y');
INSERT INTO `wa_region` VALUES (29755, '清远市', 2, 28240, 'Q');
INSERT INTO `wa_region` VALUES (29756, '市辖区', 3, 29755, 'S');
INSERT INTO `wa_region` VALUES (29757, '清城区', 3, 29755, 'Q');
INSERT INTO `wa_region` VALUES (29766, '佛冈县', 3, 29755, 'F');
INSERT INTO `wa_region` VALUES (29773, '阳山县', 3, 29755, 'Y');
INSERT INTO `wa_region` VALUES (29787, '连山县', 3, 29755, 'L');
INSERT INTO `wa_region` VALUES (29797, '连南县', 3, 29755, 'L');
INSERT INTO `wa_region` VALUES (29805, '清新县', 3, 29755, 'Q');
INSERT INTO `wa_region` VALUES (29816, '英德市', 3, 29755, 'Y');
INSERT INTO `wa_region` VALUES (29842, '连州市', 3, 29755, 'L');
INSERT INTO `wa_region` VALUES (29855, '东莞市', 2, 28240, 'D');
INSERT INTO `wa_region` VALUES (29890, '中山市', 2, 28240, 'Z');
INSERT INTO `wa_region` VALUES (29915, '潮州市', 2, 28240, 'C');
INSERT INTO `wa_region` VALUES (29916, '市辖区', 3, 29915, 'S');
INSERT INTO `wa_region` VALUES (29917, '潮州市湘桥区', 3, 29915, 'C');
INSERT INTO `wa_region` VALUES (29930, '潮州市潮安县', 3, 29915, 'C');
INSERT INTO `wa_region` VALUES (29954, '潮州市饶平县', 3, 29915, 'C');
INSERT INTO `wa_region` VALUES (29977, '揭阳市', 2, 28240, 'J');
INSERT INTO `wa_region` VALUES (29978, '市辖区', 3, 29977, 'S');
INSERT INTO `wa_region` VALUES (29979, '榕城区', 3, 29977, 'R');
INSERT INTO `wa_region` VALUES (29990, '揭东县', 3, 29977, 'J');
INSERT INTO `wa_region` VALUES (30008, '揭西县', 3, 29977, 'J');
INSERT INTO `wa_region` VALUES (30032, '惠来县', 3, 29977, 'H');
INSERT INTO `wa_region` VALUES (30054, '普宁市', 3, 29977, 'P');
INSERT INTO `wa_region` VALUES (30086, '云浮市', 2, 28240, 'Y');
INSERT INTO `wa_region` VALUES (30087, '市辖区', 3, 30086, 'S');
INSERT INTO `wa_region` VALUES (30088, '云城区', 3, 30086, 'Y');
INSERT INTO `wa_region` VALUES (30096, '新兴县', 3, 30086, 'X');
INSERT INTO `wa_region` VALUES (30112, '郁南县', 3, 30086, 'Y');
INSERT INTO `wa_region` VALUES (30132, '云安县', 3, 30086, 'Y');
INSERT INTO `wa_region` VALUES (30141, '罗定市', 3, 30086, 'L');
INSERT INTO `wa_region` VALUES (30164, '广西壮族自治区', 1, 0, 'G');
INSERT INTO `wa_region` VALUES (30165, '南宁市', 2, 30164, 'N');
INSERT INTO `wa_region` VALUES (30166, '市辖区', 3, 30165, 'S');
INSERT INTO `wa_region` VALUES (30167, '兴宁区', 3, 30165, 'X');
INSERT INTO `wa_region` VALUES (30174, '青秀区', 3, 30165, 'Q');
INSERT INTO `wa_region` VALUES (30186, '江南区', 3, 30165, 'J');
INSERT INTO `wa_region` VALUES (30196, '西乡塘区', 3, 30165, 'X');
INSERT INTO `wa_region` VALUES (30214, '良庆区', 3, 30165, 'L');
INSERT INTO `wa_region` VALUES (30222, '邕宁区', 3, 30165, 'Y');
INSERT INTO `wa_region` VALUES (30228, '武鸣县', 3, 30165, 'W');
INSERT INTO `wa_region` VALUES (30245, '隆安县', 3, 30165, 'L');
INSERT INTO `wa_region` VALUES (30257, '马山县', 3, 30165, 'M');
INSERT INTO `wa_region` VALUES (30270, '上林县', 3, 30165, 'S');
INSERT INTO `wa_region` VALUES (30282, '宾阳县', 3, 30165, 'B');
INSERT INTO `wa_region` VALUES (30300, '横县', 3, 30165, 'H');
INSERT INTO `wa_region` VALUES (30319, '柳州市', 2, 30164, 'L');
INSERT INTO `wa_region` VALUES (30320, '市辖区', 3, 30319, 'S');
INSERT INTO `wa_region` VALUES (30321, '城中区', 3, 30319, 'C');
INSERT INTO `wa_region` VALUES (30329, '鱼峰区', 3, 30319, 'Y');
INSERT INTO `wa_region` VALUES (30338, '柳南区', 3, 30319, 'L');
INSERT INTO `wa_region` VALUES (30348, '柳北区', 3, 30319, 'L');
INSERT INTO `wa_region` VALUES (30361, '柳江县', 3, 30319, 'L');
INSERT INTO `wa_region` VALUES (30374, '柳城县', 3, 30319, 'L');
INSERT INTO `wa_region` VALUES (30387, '鹿寨县', 3, 30319, 'L');
INSERT INTO `wa_region` VALUES (30398, '融安县', 3, 30319, 'R');
INSERT INTO `wa_region` VALUES (30411, '融水县', 3, 30319, 'R');
INSERT INTO `wa_region` VALUES (30432, '三江县', 3, 30319, 'S');
INSERT INTO `wa_region` VALUES (30448, '桂林市', 2, 30164, 'G');
INSERT INTO `wa_region` VALUES (30449, '市辖区', 3, 30448, 'S');
INSERT INTO `wa_region` VALUES (30450, '秀峰区', 3, 30448, 'X');
INSERT INTO `wa_region` VALUES (30454, '叠彩区', 3, 30448, 'D');
INSERT INTO `wa_region` VALUES (30458, '象山区', 3, 30448, 'X');
INSERT INTO `wa_region` VALUES (30463, '七星区', 3, 30448, 'Q');
INSERT INTO `wa_region` VALUES (30469, '雁山区', 3, 30448, 'Y');
INSERT INTO `wa_region` VALUES (30475, '阳朔县', 3, 30448, 'Y');
INSERT INTO `wa_region` VALUES (30485, '临桂县', 3, 30448, 'L');
INSERT INTO `wa_region` VALUES (30497, '灵川县', 3, 30448, 'L');
INSERT INTO `wa_region` VALUES (30509, '全州县', 3, 30448, 'Q');
INSERT INTO `wa_region` VALUES (30528, '兴安县', 3, 30448, 'X');
INSERT INTO `wa_region` VALUES (30539, '永福县', 3, 30448, 'Y');
INSERT INTO `wa_region` VALUES (30549, '灌阳县', 3, 30448, 'G');
INSERT INTO `wa_region` VALUES (30559, '龙胜县', 3, 30448, 'L');
INSERT INTO `wa_region` VALUES (30570, '资源县', 3, 30448, 'Z');
INSERT INTO `wa_region` VALUES (30578, '平乐县', 3, 30448, 'P');
INSERT INTO `wa_region` VALUES (30589, '荔浦县', 3, 30448, 'L');
INSERT INTO `wa_region` VALUES (30603, '恭城县', 3, 30448, 'G');
INSERT INTO `wa_region` VALUES (30613, '梧州市', 2, 30164, 'W');
INSERT INTO `wa_region` VALUES (30614, '市辖区', 3, 30613, 'S');
INSERT INTO `wa_region` VALUES (30615, '万秀区', 3, 30613, 'W');
INSERT INTO `wa_region` VALUES (30622, '蝶山区', 3, 30613, 'D');
INSERT INTO `wa_region` VALUES (30628, '长洲区', 3, 30613, 'C');
INSERT INTO `wa_region` VALUES (30633, '苍梧县', 3, 30613, 'C');
INSERT INTO `wa_region` VALUES (30646, '藤县', 3, 30613, 'T');
INSERT INTO `wa_region` VALUES (30663, '蒙山县', 3, 30613, 'M');
INSERT INTO `wa_region` VALUES (30673, '岑溪市', 3, 30613, 'C');
INSERT INTO `wa_region` VALUES (30688, '北海市', 2, 30164, 'B');
INSERT INTO `wa_region` VALUES (30689, '市辖区', 3, 30688, 'S');
INSERT INTO `wa_region` VALUES (30690, '海城区', 3, 30688, 'H');
INSERT INTO `wa_region` VALUES (30699, '银海区', 3, 30688, 'Y');
INSERT INTO `wa_region` VALUES (30704, '铁山港区', 3, 30688, 'T');
INSERT INTO `wa_region` VALUES (30708, '合浦县', 3, 30688, 'H');
INSERT INTO `wa_region` VALUES (30724, '防城港市', 2, 30164, 'F');
INSERT INTO `wa_region` VALUES (30725, '市辖区', 3, 30724, 'S');
INSERT INTO `wa_region` VALUES (30726, '港口区', 3, 30724, 'G');
INSERT INTO `wa_region` VALUES (30732, '防城区', 3, 30724, 'F');
INSERT INTO `wa_region` VALUES (30748, '上思县', 3, 30724, 'S');
INSERT INTO `wa_region` VALUES (30758, '东兴市', 3, 30724, 'D');
INSERT INTO `wa_region` VALUES (30762, '钦州市', 2, 30164, 'Q');
INSERT INTO `wa_region` VALUES (30763, '市辖区', 3, 30762, 'S');
INSERT INTO `wa_region` VALUES (30764, '钦南区', 3, 30762, 'Q');
INSERT INTO `wa_region` VALUES (30783, '钦北区', 3, 30762, 'Q');
INSERT INTO `wa_region` VALUES (30796, '灵山县', 3, 30762, 'L');
INSERT INTO `wa_region` VALUES (30817, '浦北县', 3, 30762, 'P');
INSERT INTO `wa_region` VALUES (30834, '贵港市', 2, 30164, 'G');
INSERT INTO `wa_region` VALUES (30835, '市辖区', 3, 30834, 'S');
INSERT INTO `wa_region` VALUES (30836, '港北区', 3, 30834, 'G');
INSERT INTO `wa_region` VALUES (30845, '港南区', 3, 30834, 'G');
INSERT INTO `wa_region` VALUES (30855, '覃塘区', 3, 30834, 'Q');
INSERT INTO `wa_region` VALUES (30866, '平南县', 3, 30834, 'P');
INSERT INTO `wa_region` VALUES (30888, '桂平市', 3, 30834, 'G');
INSERT INTO `wa_region` VALUES (30915, '玉林市', 2, 30164, 'Y');
INSERT INTO `wa_region` VALUES (30916, '市辖区', 3, 30915, 'S');
INSERT INTO `wa_region` VALUES (30917, '玉州区', 3, 30915, 'Y');
INSERT INTO `wa_region` VALUES (30933, '容县', 3, 30915, 'R');
INSERT INTO `wa_region` VALUES (30949, '陆川县', 3, 30915, 'L');
INSERT INTO `wa_region` VALUES (30964, '博白县', 3, 30915, 'B');
INSERT INTO `wa_region` VALUES (30993, '兴业县', 3, 30915, 'X');
INSERT INTO `wa_region` VALUES (31007, '北流市', 3, 30915, 'B');
INSERT INTO `wa_region` VALUES (31033, '百色市', 2, 30164, 'B');
INSERT INTO `wa_region` VALUES (31034, '市辖区', 3, 31033, 'S');
INSERT INTO `wa_region` VALUES (31035, '右江区', 3, 31033, 'Y');
INSERT INTO `wa_region` VALUES (31045, '田阳县', 3, 31033, 'T');
INSERT INTO `wa_region` VALUES (31056, '田东县', 3, 31033, 'T');
INSERT INTO `wa_region` VALUES (31067, '平果县', 3, 31033, 'P');
INSERT INTO `wa_region` VALUES (31081, '德保县', 3, 31033, 'D');
INSERT INTO `wa_region` VALUES (31095, '靖西县', 3, 31033, 'J');
INSERT INTO `wa_region` VALUES (31115, '那坡县', 3, 31033, 'N');
INSERT INTO `wa_region` VALUES (31125, '凌云县', 3, 31033, 'L');
INSERT INTO `wa_region` VALUES (31134, '乐业县', 3, 31033, 'L');
INSERT INTO `wa_region` VALUES (31143, '田林县', 3, 31033, 'T');
INSERT INTO `wa_region` VALUES (31158, '西林县', 3, 31033, 'X');
INSERT INTO `wa_region` VALUES (31167, '隆林县', 3, 31033, 'L');
INSERT INTO `wa_region` VALUES (31184, '贺州市', 2, 30164, 'H');
INSERT INTO `wa_region` VALUES (31185, '市辖区', 3, 31184, 'S');
INSERT INTO `wa_region` VALUES (31186, '八步区', 3, 31184, 'B');
INSERT INTO `wa_region` VALUES (31208, '昭平县', 3, 31184, 'Z');
INSERT INTO `wa_region` VALUES (31221, '钟山县', 3, 31184, 'Z');
INSERT INTO `wa_region` VALUES (31236, '富川县', 3, 31184, 'F');
INSERT INTO `wa_region` VALUES (31249, '河池市', 2, 30164, 'H');
INSERT INTO `wa_region` VALUES (31250, '市辖区', 3, 31249, 'S');
INSERT INTO `wa_region` VALUES (31251, '金城江区', 3, 31249, 'J');
INSERT INTO `wa_region` VALUES (31264, '南丹县', 3, 31249, 'N');
INSERT INTO `wa_region` VALUES (31276, '天峨县', 3, 31249, 'T');
INSERT INTO `wa_region` VALUES (31286, '凤山县', 3, 31249, 'F');
INSERT INTO `wa_region` VALUES (31296, '东兰县', 3, 31249, 'D');
INSERT INTO `wa_region` VALUES (31311, '罗城县', 3, 31249, 'L');
INSERT INTO `wa_region` VALUES (31323, '环江县', 3, 31249, 'H');
INSERT INTO `wa_region` VALUES (31336, '巴马县', 3, 31249, 'B');
INSERT INTO `wa_region` VALUES (31347, '都安县', 3, 31249, 'D');
INSERT INTO `wa_region` VALUES (31367, '大化县', 3, 31249, 'D');
INSERT INTO `wa_region` VALUES (31384, '宜州市', 3, 31249, 'Y');
INSERT INTO `wa_region` VALUES (31401, '来宾市', 2, 30164, 'L');
INSERT INTO `wa_region` VALUES (31402, '市辖区', 3, 31401, 'S');
INSERT INTO `wa_region` VALUES (31403, '兴宾区', 3, 31401, 'X');
INSERT INTO `wa_region` VALUES (31427, '忻城县', 3, 31401, 'X');
INSERT INTO `wa_region` VALUES (31440, '象州县', 3, 31401, 'X');
INSERT INTO `wa_region` VALUES (31452, '武宣县', 3, 31401, 'W');
INSERT INTO `wa_region` VALUES (31463, '金秀县', 3, 31401, 'J');
INSERT INTO `wa_region` VALUES (31474, '合山市', 3, 31401, 'H');
INSERT INTO `wa_region` VALUES (31478, '崇左市', 2, 30164, 'C');
INSERT INTO `wa_region` VALUES (31479, '市辖区', 3, 31478, 'S');
INSERT INTO `wa_region` VALUES (31480, '江州区', 3, 31478, 'J');
INSERT INTO `wa_region` VALUES (31490, '扶绥县', 3, 31478, 'F');
INSERT INTO `wa_region` VALUES (31502, '宁明县', 3, 31478, 'N');
INSERT INTO `wa_region` VALUES (31516, '龙州县', 3, 31478, 'L');
INSERT INTO `wa_region` VALUES (31529, '大新县', 3, 31478, 'D');
INSERT INTO `wa_region` VALUES (31544, '天等县', 3, 31478, 'T');
INSERT INTO `wa_region` VALUES (31558, '凭祥市', 3, 31478, 'P');
INSERT INTO `wa_region` VALUES (31563, '海南省', 1, 0, 'H');
INSERT INTO `wa_region` VALUES (31564, '海口市', 2, 31563, 'H');
INSERT INTO `wa_region` VALUES (31565, '市辖区', 3, 31564, 'S');
INSERT INTO `wa_region` VALUES (31566, '秀英区', 3, 31564, 'X');
INSERT INTO `wa_region` VALUES (31575, '龙华区', 3, 31564, 'L');
INSERT INTO `wa_region` VALUES (31587, '琼山区', 3, 31564, 'Q');
INSERT INTO `wa_region` VALUES (31601, '美兰区', 3, 31564, 'M');
INSERT INTO `wa_region` VALUES (31618, '三亚市', 2, 31563, 'S');
INSERT INTO `wa_region` VALUES (47967, '海棠区', 3, 31618, 'H');
INSERT INTO `wa_region` VALUES (31634, '五指山市', 2, 31563, 'W');
INSERT INTO `wa_region` VALUES (31635, '冲山镇', 3, 31634, 'C');
INSERT INTO `wa_region` VALUES (31636, '南圣镇', 3, 31634, 'N');
INSERT INTO `wa_region` VALUES (31637, '毛阳镇', 3, 31634, 'M');
INSERT INTO `wa_region` VALUES (31638, '番阳镇', 3, 31634, 'F');
INSERT INTO `wa_region` VALUES (31639, '畅好乡', 3, 31634, 'C');
INSERT INTO `wa_region` VALUES (31640, '毛道乡', 3, 31634, 'M');
INSERT INTO `wa_region` VALUES (31641, '水满乡', 3, 31634, 'S');
INSERT INTO `wa_region` VALUES (31642, '国营畅好农场', 3, 31634, 'G');
INSERT INTO `wa_region` VALUES (31643, '琼海市', 2, 31563, 'Q');
INSERT INTO `wa_region` VALUES (31644, '嘉积镇', 3, 31643, 'J');
INSERT INTO `wa_region` VALUES (31645, '万泉镇', 3, 31643, 'W');
INSERT INTO `wa_region` VALUES (31646, '石壁镇', 3, 31643, 'S');
INSERT INTO `wa_region` VALUES (31647, '中原镇', 3, 31643, 'Z');
INSERT INTO `wa_region` VALUES (31648, '博敖镇', 3, 31643, 'B');
INSERT INTO `wa_region` VALUES (31649, '阳江镇', 3, 31643, 'Y');
INSERT INTO `wa_region` VALUES (31650, '龙江镇', 3, 31643, 'L');
INSERT INTO `wa_region` VALUES (31651, '潭门镇', 3, 31643, 'T');
INSERT INTO `wa_region` VALUES (31652, '塔洋镇', 3, 31643, 'T');
INSERT INTO `wa_region` VALUES (31653, '长坡镇', 3, 31643, 'C');
INSERT INTO `wa_region` VALUES (31654, '大路镇', 3, 31643, 'D');
INSERT INTO `wa_region` VALUES (31655, '会山镇', 3, 31643, 'H');
INSERT INTO `wa_region` VALUES (31656, '国营东太农场', 3, 31643, 'G');
INSERT INTO `wa_region` VALUES (31657, '国营东平农场', 3, 31643, 'G');
INSERT INTO `wa_region` VALUES (31658, '国营东红农场', 3, 31643, 'G');
INSERT INTO `wa_region` VALUES (31659, '国营东升农场', 3, 31643, 'G');
INSERT INTO `wa_region` VALUES (31660, '国营南俸农场', 3, 31643, 'G');
INSERT INTO `wa_region` VALUES (31661, '彬村山华侨农场', 3, 31643, 'B');
INSERT INTO `wa_region` VALUES (31662, '儋州市', 2, 31563, 'D');
INSERT INTO `wa_region` VALUES (31663, '那大镇', 3, 31662, 'N');
INSERT INTO `wa_region` VALUES (31664, '和庆镇', 3, 31662, 'H');
INSERT INTO `wa_region` VALUES (31665, '南丰镇', 3, 31662, 'N');
INSERT INTO `wa_region` VALUES (31666, '大成镇', 3, 31662, 'D');
INSERT INTO `wa_region` VALUES (31667, '雅星镇', 3, 31662, 'Y');
INSERT INTO `wa_region` VALUES (31668, '兰洋镇', 3, 31662, 'L');
INSERT INTO `wa_region` VALUES (31669, '光村镇', 3, 31662, 'G');
INSERT INTO `wa_region` VALUES (31670, '木棠镇', 3, 31662, 'M');
INSERT INTO `wa_region` VALUES (31671, '海头镇', 3, 31662, 'H');
INSERT INTO `wa_region` VALUES (31672, '峨蔓镇', 3, 31662, 'E');
INSERT INTO `wa_region` VALUES (31673, '三都镇', 3, 31662, 'S');
INSERT INTO `wa_region` VALUES (31674, '王五镇', 3, 31662, 'W');
INSERT INTO `wa_region` VALUES (31675, '白马井镇', 3, 31662, 'B');
INSERT INTO `wa_region` VALUES (31676, '中和镇', 3, 31662, 'Z');
INSERT INTO `wa_region` VALUES (31677, '排浦镇', 3, 31662, 'P');
INSERT INTO `wa_region` VALUES (31678, '东成镇', 3, 31662, 'D');
INSERT INTO `wa_region` VALUES (31679, '新州镇', 3, 31662, 'X');
INSERT INTO `wa_region` VALUES (31680, '国营西培农场', 3, 31662, 'G');
INSERT INTO `wa_region` VALUES (31681, '国营西华农场', 3, 31662, 'G');
INSERT INTO `wa_region` VALUES (31682, '国营西庆农场', 3, 31662, 'G');
INSERT INTO `wa_region` VALUES (31683, '国营西流农场', 3, 31662, 'G');
INSERT INTO `wa_region` VALUES (31684, '国营西联农场', 3, 31662, 'G');
INSERT INTO `wa_region` VALUES (31685, '国营蓝洋农场', 3, 31662, 'G');
INSERT INTO `wa_region` VALUES (31686, '国营新盈农场', 3, 31662, 'G');
INSERT INTO `wa_region` VALUES (31687, '国营八一农场东山分场', 3, 31662, 'G');
INSERT INTO `wa_region` VALUES (31688, '国营八一农场金川分场', 3, 31662, 'G');
INSERT INTO `wa_region` VALUES (31689, '国营八一农场长岭分场', 3, 31662, 'G');
INSERT INTO `wa_region` VALUES (31690, '国营八一农场英岛分场', 3, 31662, 'G');
INSERT INTO `wa_region` VALUES (31691, '国营八一农场春江分场', 3, 31662, 'G');
INSERT INTO `wa_region` VALUES (31692, '国营八一农场强打管区', 3, 31662, 'G');
INSERT INTO `wa_region` VALUES (31693, '国营龙山农场', 3, 31662, 'G');
INSERT INTO `wa_region` VALUES (31694, '国营红岭农场', 3, 31662, 'G');
INSERT INTO `wa_region` VALUES (31695, '洋浦经济开发区', 3, 31662, 'Y');
INSERT INTO `wa_region` VALUES (31696, '华南热作学院', 3, 31662, 'H');
INSERT INTO `wa_region` VALUES (31697, '文昌市', 2, 31563, 'W');
INSERT INTO `wa_region` VALUES (31698, '文城镇', 3, 31697, 'W');
INSERT INTO `wa_region` VALUES (31699, '重兴镇', 3, 31697, 'Z');
INSERT INTO `wa_region` VALUES (31700, '蓬莱镇', 3, 31697, 'P');
INSERT INTO `wa_region` VALUES (31701, '会文镇', 3, 31697, 'H');
INSERT INTO `wa_region` VALUES (31702, '东路镇', 3, 31697, 'D');
INSERT INTO `wa_region` VALUES (31703, '潭牛镇', 3, 31697, 'T');
INSERT INTO `wa_region` VALUES (31704, '东阁镇', 3, 31697, 'D');
INSERT INTO `wa_region` VALUES (31705, '文教镇', 3, 31697, 'W');
INSERT INTO `wa_region` VALUES (31706, '东郊镇', 3, 31697, 'D');
INSERT INTO `wa_region` VALUES (31707, '龙楼镇', 3, 31697, 'L');
INSERT INTO `wa_region` VALUES (31708, '昌洒镇', 3, 31697, 'C');
INSERT INTO `wa_region` VALUES (31709, '翁田镇', 3, 31697, 'W');
INSERT INTO `wa_region` VALUES (31710, '抱罗镇', 3, 31697, 'B');
INSERT INTO `wa_region` VALUES (31711, '冯坡镇', 3, 31697, 'F');
INSERT INTO `wa_region` VALUES (31712, '锦山镇', 3, 31697, 'J');
INSERT INTO `wa_region` VALUES (31713, '铺前镇', 3, 31697, 'P');
INSERT INTO `wa_region` VALUES (31714, '国营东路农场', 3, 31697, 'G');
INSERT INTO `wa_region` VALUES (31715, '国营南阳农场', 3, 31697, 'G');
INSERT INTO `wa_region` VALUES (31716, '国营罗豆农场', 3, 31697, 'G');
INSERT INTO `wa_region` VALUES (31717, '国营文昌橡胶研究所', 3, 31697, 'G');
INSERT INTO `wa_region` VALUES (31718, '万宁市', 2, 31563, 'W');
INSERT INTO `wa_region` VALUES (31719, '万城镇', 3, 31718, 'W');
INSERT INTO `wa_region` VALUES (31720, '龙滚镇', 3, 31718, 'L');
INSERT INTO `wa_region` VALUES (31721, '和乐镇', 3, 31718, 'H');
INSERT INTO `wa_region` VALUES (31722, '后安镇', 3, 31718, 'H');
INSERT INTO `wa_region` VALUES (31723, '大茂镇', 3, 31718, 'D');
INSERT INTO `wa_region` VALUES (31724, '东澳镇', 3, 31718, 'D');
INSERT INTO `wa_region` VALUES (31725, '礼纪镇', 3, 31718, 'L');
INSERT INTO `wa_region` VALUES (31726, '长丰镇', 3, 31718, 'C');
INSERT INTO `wa_region` VALUES (31727, '山根镇', 3, 31718, 'S');
INSERT INTO `wa_region` VALUES (31728, '北大镇', 3, 31718, 'B');
INSERT INTO `wa_region` VALUES (31729, '南桥镇', 3, 31718, 'N');
INSERT INTO `wa_region` VALUES (31730, '三更罗镇', 3, 31718, 'S');
INSERT INTO `wa_region` VALUES (31731, '国营东兴农场', 3, 31718, 'G');
INSERT INTO `wa_region` VALUES (31732, '国营东和农场', 3, 31718, 'G');
INSERT INTO `wa_region` VALUES (31733, '国营东岭农场', 3, 31718, 'G');
INSERT INTO `wa_region` VALUES (31734, '国营南林农场', 3, 31718, 'G');
INSERT INTO `wa_region` VALUES (31735, '国营新中农场', 3, 31718, 'G');
INSERT INTO `wa_region` VALUES (31736, '兴隆华侨农场', 3, 31718, 'X');
INSERT INTO `wa_region` VALUES (31737, '地方国营六连林场', 3, 31718, 'D');
INSERT INTO `wa_region` VALUES (31738, '东方市', 2, 31563, 'D');
INSERT INTO `wa_region` VALUES (31739, '八所镇', 3, 31738, 'B');
INSERT INTO `wa_region` VALUES (31740, '东河镇', 3, 31738, 'D');
INSERT INTO `wa_region` VALUES (31741, '大田镇', 3, 31738, 'D');
INSERT INTO `wa_region` VALUES (31742, '感城镇', 3, 31738, 'G');
INSERT INTO `wa_region` VALUES (31743, '板桥镇', 3, 31738, 'B');
INSERT INTO `wa_region` VALUES (31744, '三家镇', 3, 31738, 'S');
INSERT INTO `wa_region` VALUES (31745, '四更镇', 3, 31738, 'S');
INSERT INTO `wa_region` VALUES (31746, '新龙镇', 3, 31738, 'X');
INSERT INTO `wa_region` VALUES (31747, '天安乡', 3, 31738, 'T');
INSERT INTO `wa_region` VALUES (31748, '江边乡', 3, 31738, 'J');
INSERT INTO `wa_region` VALUES (31749, '国营广坝农场', 3, 31738, 'G');
INSERT INTO `wa_region` VALUES (31750, '国营公爱农场', 3, 31738, 'G');
INSERT INTO `wa_region` VALUES (31751, '国营红泉农场', 3, 31738, 'G');
INSERT INTO `wa_region` VALUES (31752, '省国营东方华侨农场', 3, 31738, 'S');
INSERT INTO `wa_region` VALUES (31753, '定安县', 2, 31563, 'D');
INSERT INTO `wa_region` VALUES (31754, '定城镇', 3, 31753, 'D');
INSERT INTO `wa_region` VALUES (31755, '新竹镇', 3, 31753, 'X');
INSERT INTO `wa_region` VALUES (31756, '龙湖镇', 3, 31753, 'L');
INSERT INTO `wa_region` VALUES (31757, '黄竹镇', 3, 31753, 'H');
INSERT INTO `wa_region` VALUES (31758, '雷鸣镇', 3, 31753, 'L');
INSERT INTO `wa_region` VALUES (31759, '龙门镇', 3, 31753, 'L');
INSERT INTO `wa_region` VALUES (31760, '龙河镇', 3, 31753, 'L');
INSERT INTO `wa_region` VALUES (31761, '岭口镇', 3, 31753, 'L');
INSERT INTO `wa_region` VALUES (31762, '翰林镇', 3, 31753, 'H');
INSERT INTO `wa_region` VALUES (31763, '富文镇', 3, 31753, 'F');
INSERT INTO `wa_region` VALUES (31764, '国营中瑞农场', 3, 31753, 'G');
INSERT INTO `wa_region` VALUES (31765, '国营南海农场', 3, 31753, 'G');
INSERT INTO `wa_region` VALUES (31766, '国营金鸡岭农场', 3, 31753, 'G');
INSERT INTO `wa_region` VALUES (31767, '定安热作研究所', 3, 31753, 'D');
INSERT INTO `wa_region` VALUES (31768, '屯昌县', 2, 31563, 'T');
INSERT INTO `wa_region` VALUES (31769, '屯城镇', 3, 31768, 'T');
INSERT INTO `wa_region` VALUES (31770, '新兴镇', 3, 31768, 'X');
INSERT INTO `wa_region` VALUES (31771, '枫木镇', 3, 31768, 'F');
INSERT INTO `wa_region` VALUES (31772, '乌坡镇', 3, 31768, 'W');
INSERT INTO `wa_region` VALUES (31773, '南吕镇', 3, 31768, 'N');
INSERT INTO `wa_region` VALUES (31774, '南坤镇', 3, 31768, 'N');
INSERT INTO `wa_region` VALUES (31775, '坡心镇', 3, 31768, 'P');
INSERT INTO `wa_region` VALUES (31776, '西昌镇', 3, 31768, 'X');
INSERT INTO `wa_region` VALUES (31777, '国营中建农场', 3, 31768, 'G');
INSERT INTO `wa_region` VALUES (31778, '国营中坤农场', 3, 31768, 'G');
INSERT INTO `wa_region` VALUES (31779, '国营黄岭农场', 3, 31768, 'G');
INSERT INTO `wa_region` VALUES (31780, '国营南吕农场', 3, 31768, 'G');
INSERT INTO `wa_region` VALUES (31781, '国营广青农场', 3, 31768, 'G');
INSERT INTO `wa_region` VALUES (31782, '国营晨星农场', 3, 31768, 'G');
INSERT INTO `wa_region` VALUES (31783, '澄迈县', 2, 31563, 'C');
INSERT INTO `wa_region` VALUES (31784, '金江镇', 3, 31783, 'J');
INSERT INTO `wa_region` VALUES (31785, '老城镇', 3, 31783, 'L');
INSERT INTO `wa_region` VALUES (31786, '瑞溪镇', 3, 31783, 'R');
INSERT INTO `wa_region` VALUES (31787, '永发镇', 3, 31783, 'Y');
INSERT INTO `wa_region` VALUES (31788, '加乐镇', 3, 31783, 'J');
INSERT INTO `wa_region` VALUES (31789, '文儒镇', 3, 31783, 'W');
INSERT INTO `wa_region` VALUES (31790, '中兴镇', 3, 31783, 'Z');
INSERT INTO `wa_region` VALUES (31791, '仁兴镇', 3, 31783, 'R');
INSERT INTO `wa_region` VALUES (31792, '福山镇', 3, 31783, 'F');
INSERT INTO `wa_region` VALUES (31793, '桥头镇', 3, 31783, 'Q');
INSERT INTO `wa_region` VALUES (31794, '国营红光农场', 3, 31783, 'G');
INSERT INTO `wa_region` VALUES (31795, '国营红岗农场', 3, 31783, 'G');
INSERT INTO `wa_region` VALUES (31796, '国营西达农场', 3, 31783, 'G');
INSERT INTO `wa_region` VALUES (31797, '国营昆仑农场', 3, 31783, 'G');
INSERT INTO `wa_region` VALUES (31798, '国营和岭农场', 3, 31783, 'G');
INSERT INTO `wa_region` VALUES (31799, '国营金安农场', 3, 31783, 'G');
INSERT INTO `wa_region` VALUES (31800, '澄迈县华侨农场', 3, 31783, 'C');
INSERT INTO `wa_region` VALUES (31801, '临高县', 2, 31563, 'L');
INSERT INTO `wa_region` VALUES (31802, '临城镇', 3, 31801, 'L');
INSERT INTO `wa_region` VALUES (31803, '波莲镇', 3, 31801, 'B');
INSERT INTO `wa_region` VALUES (31804, '东英镇', 3, 31801, 'D');
INSERT INTO `wa_region` VALUES (31805, '博厚镇', 3, 31801, 'B');
INSERT INTO `wa_region` VALUES (31806, '皇桐镇', 3, 31801, 'H');
INSERT INTO `wa_region` VALUES (31807, '多文镇', 3, 31801, 'D');
INSERT INTO `wa_region` VALUES (31808, '和舍镇', 3, 31801, 'H');
INSERT INTO `wa_region` VALUES (31809, '南宝镇', 3, 31801, 'N');
INSERT INTO `wa_region` VALUES (31810, '新盈镇', 3, 31801, 'X');
INSERT INTO `wa_region` VALUES (31811, '调楼镇', 3, 31801, 'D');
INSERT INTO `wa_region` VALUES (31812, '国营红华农场', 3, 31801, 'G');
INSERT INTO `wa_region` VALUES (31813, '国营加来农场', 3, 31801, 'G');
INSERT INTO `wa_region` VALUES (31814, '白沙县', 2, 31563, 'B');
INSERT INTO `wa_region` VALUES (31815, '牙叉镇', 3, 31814, 'Y');
INSERT INTO `wa_region` VALUES (31816, '七坊镇', 3, 31814, 'Q');
INSERT INTO `wa_region` VALUES (31817, '邦溪镇', 3, 31814, 'B');
INSERT INTO `wa_region` VALUES (31818, '打安镇', 3, 31814, 'D');
INSERT INTO `wa_region` VALUES (31819, '细水乡', 3, 31814, 'X');
INSERT INTO `wa_region` VALUES (31820, '元门乡', 3, 31814, 'Y');
INSERT INTO `wa_region` VALUES (31821, '南开乡', 3, 31814, 'N');
INSERT INTO `wa_region` VALUES (31822, '阜龙乡', 3, 31814, 'F');
INSERT INTO `wa_region` VALUES (31823, '青松乡', 3, 31814, 'Q');
INSERT INTO `wa_region` VALUES (31824, '金波乡', 3, 31814, 'J');
INSERT INTO `wa_region` VALUES (31825, '荣邦乡', 3, 31814, 'R');
INSERT INTO `wa_region` VALUES (31826, '国营金波农场', 3, 31814, 'G');
INSERT INTO `wa_region` VALUES (31827, '国营白沙农场', 3, 31814, 'G');
INSERT INTO `wa_region` VALUES (31828, '国营牙叉农场', 3, 31814, 'G');
INSERT INTO `wa_region` VALUES (31829, '国营卫星农场', 3, 31814, 'G');
INSERT INTO `wa_region` VALUES (31830, '国营龙江农场', 3, 31814, 'G');
INSERT INTO `wa_region` VALUES (31831, '国营珠碧江农场', 3, 31814, 'G');
INSERT INTO `wa_region` VALUES (31832, '国营芙蓉田农场', 3, 31814, 'G');
INSERT INTO `wa_region` VALUES (31833, '国营大岭农场', 3, 31814, 'G');
INSERT INTO `wa_region` VALUES (31834, '国营邦溪农场', 3, 31814, 'G');
INSERT INTO `wa_region` VALUES (31835, '昌江县', 2, 31563, 'C');
INSERT INTO `wa_region` VALUES (31836, '石碌镇', 3, 31835, 'S');
INSERT INTO `wa_region` VALUES (31837, '叉河镇', 3, 31835, 'C');
INSERT INTO `wa_region` VALUES (31838, '十月田镇', 3, 31835, 'S');
INSERT INTO `wa_region` VALUES (31839, '乌烈镇', 3, 31835, 'W');
INSERT INTO `wa_region` VALUES (31840, '昌化镇', 3, 31835, 'C');
INSERT INTO `wa_region` VALUES (31841, '海尾镇', 3, 31835, 'H');
INSERT INTO `wa_region` VALUES (31842, '七叉镇', 3, 31835, 'Q');
INSERT INTO `wa_region` VALUES (31843, '王下乡', 3, 31835, 'W');
INSERT INTO `wa_region` VALUES (31844, '国营红田农场', 3, 31835, 'G');
INSERT INTO `wa_region` VALUES (31845, '国营红林农场', 3, 31835, 'G');
INSERT INTO `wa_region` VALUES (31846, '国营坝王岭林场', 3, 31835, 'G');
INSERT INTO `wa_region` VALUES (31847, '海南钢铁公司', 3, 31835, 'H');
INSERT INTO `wa_region` VALUES (31848, '乐东县', 2, 31563, 'L');
INSERT INTO `wa_region` VALUES (31849, '抱由镇', 3, 31848, 'B');
INSERT INTO `wa_region` VALUES (31850, '万冲镇', 3, 31848, 'W');
INSERT INTO `wa_region` VALUES (31851, '大安镇', 3, 31848, 'D');
INSERT INTO `wa_region` VALUES (31852, '志仲镇', 3, 31848, 'Z');
INSERT INTO `wa_region` VALUES (31853, '千家镇', 3, 31848, 'Q');
INSERT INTO `wa_region` VALUES (31854, '九所镇', 3, 31848, 'J');
INSERT INTO `wa_region` VALUES (31855, '利国镇', 3, 31848, 'L');
INSERT INTO `wa_region` VALUES (31856, '黄流镇', 3, 31848, 'H');
INSERT INTO `wa_region` VALUES (31857, '佛罗镇', 3, 31848, 'F');
INSERT INTO `wa_region` VALUES (31858, '尖峰镇', 3, 31848, 'J');
INSERT INTO `wa_region` VALUES (31859, '莺歌海镇', 3, 31848, 'Y');
INSERT INTO `wa_region` VALUES (31860, '国营乐中农场', 3, 31848, 'G');
INSERT INTO `wa_region` VALUES (31861, '国营山荣农场', 3, 31848, 'G');
INSERT INTO `wa_region` VALUES (31862, '国营乐光农场', 3, 31848, 'G');
INSERT INTO `wa_region` VALUES (31863, '国营报伦农场', 3, 31848, 'G');
INSERT INTO `wa_region` VALUES (31864, '国营福报农场', 3, 31848, 'G');
INSERT INTO `wa_region` VALUES (31865, '国营保国农场', 3, 31848, 'G');
INSERT INTO `wa_region` VALUES (31866, '国营保显农场', 3, 31848, 'G');
INSERT INTO `wa_region` VALUES (31867, '国营尖峰岭林业公司', 3, 31848, 'G');
INSERT INTO `wa_region` VALUES (31868, '国营莺歌海盐场', 3, 31848, 'G');
INSERT INTO `wa_region` VALUES (31869, '陵水县', 2, 31563, 'L');
INSERT INTO `wa_region` VALUES (31870, '椰林镇', 3, 31869, 'Y');
INSERT INTO `wa_region` VALUES (31871, '光坡镇', 3, 31869, 'G');
INSERT INTO `wa_region` VALUES (31872, '三才镇', 3, 31869, 'S');
INSERT INTO `wa_region` VALUES (31873, '英州镇', 3, 31869, 'Y');
INSERT INTO `wa_region` VALUES (31874, '隆广镇', 3, 31869, 'L');
INSERT INTO `wa_region` VALUES (31875, '文罗镇', 3, 31869, 'W');
INSERT INTO `wa_region` VALUES (31876, '本号镇', 3, 31869, 'B');
INSERT INTO `wa_region` VALUES (31877, '新村镇', 3, 31869, 'X');
INSERT INTO `wa_region` VALUES (31878, '黎安镇', 3, 31869, 'L');
INSERT INTO `wa_region` VALUES (31879, '提蒙乡', 3, 31869, 'T');
INSERT INTO `wa_region` VALUES (31880, '群英乡', 3, 31869, 'Q');
INSERT INTO `wa_region` VALUES (31881, '国营岭门农场', 3, 31869, 'G');
INSERT INTO `wa_region` VALUES (31882, '国营南平农场', 3, 31869, 'G');
INSERT INTO `wa_region` VALUES (31883, '国营吊罗山林业公司', 3, 31869, 'G');
INSERT INTO `wa_region` VALUES (31884, '保亭县', 2, 31563, 'B');
INSERT INTO `wa_region` VALUES (31885, '保城镇', 3, 31884, 'B');
INSERT INTO `wa_region` VALUES (31886, '什玲镇', 3, 31884, 'S');
INSERT INTO `wa_region` VALUES (31887, '加茂镇', 3, 31884, 'J');
INSERT INTO `wa_region` VALUES (31888, '响水镇', 3, 31884, 'X');
INSERT INTO `wa_region` VALUES (31889, '新政镇', 3, 31884, 'X');
INSERT INTO `wa_region` VALUES (31890, '三道镇', 3, 31884, 'S');
INSERT INTO `wa_region` VALUES (31891, '六弓乡', 3, 31884, 'L');
INSERT INTO `wa_region` VALUES (31892, '南林乡', 3, 31884, 'N');
INSERT INTO `wa_region` VALUES (31893, '毛感乡', 3, 31884, 'M');
INSERT INTO `wa_region` VALUES (31894, '国营五指山茶场', 3, 31884, 'G');
INSERT INTO `wa_region` VALUES (31895, '国营新星农场', 3, 31884, 'G');
INSERT INTO `wa_region` VALUES (31896, '国营保亭热作所', 3, 31884, 'G');
INSERT INTO `wa_region` VALUES (31897, '国营金江农场', 3, 31884, 'G');
INSERT INTO `wa_region` VALUES (31898, '国营南茂农场', 3, 31884, 'G');
INSERT INTO `wa_region` VALUES (31899, '国营三道农场', 3, 31884, 'G');
INSERT INTO `wa_region` VALUES (31900, '琼中县', 2, 31563, 'Q');
INSERT INTO `wa_region` VALUES (31901, '营根镇', 3, 31900, 'Y');
INSERT INTO `wa_region` VALUES (31902, '湾岭镇', 3, 31900, 'W');
INSERT INTO `wa_region` VALUES (31903, '黎母山镇', 3, 31900, 'L');
INSERT INTO `wa_region` VALUES (31904, '和平镇', 3, 31900, 'H');
INSERT INTO `wa_region` VALUES (31905, '长征镇', 3, 31900, 'C');
INSERT INTO `wa_region` VALUES (31906, '红毛镇', 3, 31900, 'H');
INSERT INTO `wa_region` VALUES (31907, '中平镇', 3, 31900, 'Z');
INSERT INTO `wa_region` VALUES (31908, '吊罗山乡', 3, 31900, 'D');
INSERT INTO `wa_region` VALUES (31909, '上安乡', 3, 31900, 'S');
INSERT INTO `wa_region` VALUES (31910, '什运乡', 3, 31900, 'S');
INSERT INTO `wa_region` VALUES (31911, '国营新进农场', 3, 31900, 'G');
INSERT INTO `wa_region` VALUES (31912, '国营大丰农场', 3, 31900, 'G');
INSERT INTO `wa_region` VALUES (31913, '国营阳江农场', 3, 31900, 'G');
INSERT INTO `wa_region` VALUES (31914, '国营乌石农场', 3, 31900, 'G');
INSERT INTO `wa_region` VALUES (31915, '国营南方农场', 3, 31900, 'G');
INSERT INTO `wa_region` VALUES (31916, '国营岭头农场', 3, 31900, 'G');
INSERT INTO `wa_region` VALUES (31917, '国营加钗农场', 3, 31900, 'G');
INSERT INTO `wa_region` VALUES (31918, '国营长征农场', 3, 31900, 'G');
INSERT INTO `wa_region` VALUES (31919, '国营乘坡农场', 3, 31900, 'G');
INSERT INTO `wa_region` VALUES (31920, '国营太平农场', 3, 31900, 'G');
INSERT INTO `wa_region` VALUES (31921, '国营新伟农场', 3, 31900, 'G');
INSERT INTO `wa_region` VALUES (31922, '国营黎母山林业公司', 3, 31900, 'G');
INSERT INTO `wa_region` VALUES (31923, '西沙群岛', 3, 31924, 'X');
INSERT INTO `wa_region` VALUES (31924, '三沙市', 2, 31563, 'S');
INSERT INTO `wa_region` VALUES (31925, '南沙群岛', 3, 31924, 'N');
INSERT INTO `wa_region` VALUES (47499, '香港岛', 2, 47494, 'X');
INSERT INTO `wa_region` VALUES (31927, '中沙群岛的岛礁及其海域', 3, 31924, 'Z');
INSERT INTO `wa_region` VALUES (47498, '九龙', 2, 47494, 'J');
INSERT INTO `wa_region` VALUES (31929, '重庆市', 1, 0, 'Z');
INSERT INTO `wa_region` VALUES (31930, '重庆市', 2, 31929, 'C');
INSERT INTO `wa_region` VALUES (31931, '万州区', 3, 31930, 'W');
INSERT INTO `wa_region` VALUES (31984, '涪陵区', 3, 31930, 'F');
INSERT INTO `wa_region` VALUES (32031, '渝中区', 3, 31930, 'Y');
INSERT INTO `wa_region` VALUES (32044, '大渡口区', 3, 31930, 'D');
INSERT INTO `wa_region` VALUES (32053, '江北区', 3, 31930, 'J');
INSERT INTO `wa_region` VALUES (32066, '沙坪坝区', 3, 31930, 'S');
INSERT INTO `wa_region` VALUES (32093, '九龙坡区', 3, 31930, 'J');
INSERT INTO `wa_region` VALUES (32112, '南岸区', 3, 31930, 'N');
INSERT INTO `wa_region` VALUES (32127, '北碚区', 3, 31930, 'B');
INSERT INTO `wa_region` VALUES (32145, '万盛区', 3, 31930, 'W');
INSERT INTO `wa_region` VALUES (32156, '双桥区', 3, 31930, 'S');
INSERT INTO `wa_region` VALUES (32160, '渝北区', 3, 31930, 'Y');
INSERT INTO `wa_region` VALUES (32191, '巴南区', 3, 31930, 'B');
INSERT INTO `wa_region` VALUES (32213, '黔江区', 3, 31930, 'Q');
INSERT INTO `wa_region` VALUES (32244, '长寿区', 3, 31930, 'C');
INSERT INTO `wa_region` VALUES (32263, '江津区', 3, 31930, 'J');
INSERT INTO `wa_region` VALUES (32291, '合川区', 3, 31930, 'H');
INSERT INTO `wa_region` VALUES (32322, '永川区', 3, 31930, 'Y');
INSERT INTO `wa_region` VALUES (32345, '南川区', 3, 31930, 'N');
INSERT INTO `wa_region` VALUES (32380, '县', 2, 31929, 'X');
INSERT INTO `wa_region` VALUES (32381, '綦江县', 3, 32380, 'Q');
INSERT INTO `wa_region` VALUES (32401, '潼南县', 3, 32380, 'T');
INSERT INTO `wa_region` VALUES (32424, '铜梁县', 3, 32380, 'T');
INSERT INTO `wa_region` VALUES (32453, '大足县', 3, 32380, 'D');
INSERT INTO `wa_region` VALUES (32478, '荣昌县', 3, 32380, 'R');
INSERT INTO `wa_region` VALUES (32499, '璧山县', 3, 32380, 'B');
INSERT INTO `wa_region` VALUES (32513, '梁平县', 3, 32380, 'L');
INSERT INTO `wa_region` VALUES (32549, '城口县', 3, 32380, 'C');
INSERT INTO `wa_region` VALUES (32574, '丰都县', 3, 32380, 'F');
INSERT INTO `wa_region` VALUES (32606, '垫江县', 3, 32380, 'D');
INSERT INTO `wa_region` VALUES (32632, '武隆县', 3, 32380, 'W');
INSERT INTO `wa_region` VALUES (32659, '忠县', 3, 32380, 'Z');
INSERT INTO `wa_region` VALUES (32688, '开县', 3, 32380, 'K');
INSERT INTO `wa_region` VALUES (32727, '云阳县', 3, 32380, 'Y');
INSERT INTO `wa_region` VALUES (32771, '奉节县', 3, 32380, 'F');
INSERT INTO `wa_region` VALUES (32802, '巫山县', 3, 32380, 'W');
INSERT INTO `wa_region` VALUES (32829, '巫溪县', 3, 32380, 'W');
INSERT INTO `wa_region` VALUES (32861, '石柱县', 3, 32380, 'S');
INSERT INTO `wa_region` VALUES (32894, '秀山县', 3, 32380, 'X');
INSERT INTO `wa_region` VALUES (32927, '酉阳县', 3, 32380, 'Y');
INSERT INTO `wa_region` VALUES (32967, '彭水县', 3, 32380, 'P');
INSERT INTO `wa_region` VALUES (33007, '四川省', 1, 0, 'S');
INSERT INTO `wa_region` VALUES (33008, '成都市', 2, 33007, 'C');
INSERT INTO `wa_region` VALUES (33009, '市辖区', 3, 33008, 'S');
INSERT INTO `wa_region` VALUES (33010, '锦江区', 3, 33008, 'J');
INSERT INTO `wa_region` VALUES (33027, '青羊区', 3, 33008, 'Q');
INSERT INTO `wa_region` VALUES (33042, '金牛区', 3, 33008, 'J');
INSERT INTO `wa_region` VALUES (33058, '武侯区', 3, 33008, 'W');
INSERT INTO `wa_region` VALUES (33076, '成华区', 3, 33008, 'C');
INSERT INTO `wa_region` VALUES (33091, '龙泉驿区', 3, 33008, 'L');
INSERT INTO `wa_region` VALUES (33104, '青白江区', 3, 33008, 'Q');
INSERT INTO `wa_region` VALUES (33116, '新都区', 3, 33008, 'X');
INSERT INTO `wa_region` VALUES (33130, '温江区', 3, 33008, 'W');
INSERT INTO `wa_region` VALUES (33141, '金堂县', 3, 33008, 'J');
INSERT INTO `wa_region` VALUES (33163, '双流县', 3, 33008, 'S');
INSERT INTO `wa_region` VALUES (33189, '郫县', 3, 33008, 'P');
INSERT INTO `wa_region` VALUES (33205, '大邑县', 3, 33008, 'D');
INSERT INTO `wa_region` VALUES (33226, '蒲江县', 3, 33008, 'P');
INSERT INTO `wa_region` VALUES (33239, '新津县', 3, 33008, 'X');
INSERT INTO `wa_region` VALUES (33252, '都江堰市', 3, 33008, 'D');
INSERT INTO `wa_region` VALUES (33272, '彭州市', 3, 33008, 'P');
INSERT INTO `wa_region` VALUES (33293, '邛崃市', 3, 33008, 'Q');
INSERT INTO `wa_region` VALUES (33318, '崇州市', 3, 33008, 'C');
INSERT INTO `wa_region` VALUES (33344, '自贡市', 2, 33007, 'Z');
INSERT INTO `wa_region` VALUES (33345, '市辖区', 3, 33344, 'S');
INSERT INTO `wa_region` VALUES (33346, '自流井区', 3, 33344, 'Z');
INSERT INTO `wa_region` VALUES (33360, '贡井区', 3, 33344, 'G');
INSERT INTO `wa_region` VALUES (33374, '大安区', 3, 33344, 'D');
INSERT INTO `wa_region` VALUES (33391, '沿滩区', 3, 33344, 'Y');
INSERT INTO `wa_region` VALUES (33405, '荣县', 3, 33344, 'R');
INSERT INTO `wa_region` VALUES (33433, '富顺县', 3, 33344, 'F');
INSERT INTO `wa_region` VALUES (33460, '攀枝花市', 2, 33007, 'P');
INSERT INTO `wa_region` VALUES (33461, '市辖区', 3, 33460, 'S');
INSERT INTO `wa_region` VALUES (33462, '攀枝花东区', 3, 33460, 'P');
INSERT INTO `wa_region` VALUES (33473, '西区', 3, 33460, 'X');
INSERT INTO `wa_region` VALUES (33481, '仁和区', 3, 33460, 'R');
INSERT INTO `wa_region` VALUES (33497, '米易县', 3, 33460, 'M');
INSERT INTO `wa_region` VALUES (33511, '盐边县', 3, 33460, 'Y');
INSERT INTO `wa_region` VALUES (33528, '泸州市', 2, 33007, 'L');
INSERT INTO `wa_region` VALUES (33529, '市辖区', 3, 33528, 'S');
INSERT INTO `wa_region` VALUES (33530, '江阳区', 3, 33528, 'J');
INSERT INTO `wa_region` VALUES (33548, '纳溪区', 3, 33528, 'N');
INSERT INTO `wa_region` VALUES (33563, '龙马潭区', 3, 33528, 'L');
INSERT INTO `wa_region` VALUES (33577, '泸县', 3, 33528, 'L');
INSERT INTO `wa_region` VALUES (33597, '合江县', 3, 33528, 'H');
INSERT INTO `wa_region` VALUES (33625, '叙永县', 3, 33528, 'X');
INSERT INTO `wa_region` VALUES (33654, '古蔺县', 3, 33528, 'G');
INSERT INTO `wa_region` VALUES (33681, '德阳市', 2, 33007, 'D');
INSERT INTO `wa_region` VALUES (33682, '市辖区', 3, 33681, 'S');
INSERT INTO `wa_region` VALUES (33683, '旌阳区', 3, 33681, 'J');
INSERT INTO `wa_region` VALUES (33701, '中江县', 3, 33681, 'Z');
INSERT INTO `wa_region` VALUES (33747, '罗江县', 3, 33681, 'L');
INSERT INTO `wa_region` VALUES (33758, '广汉市', 3, 33681, 'G');
INSERT INTO `wa_region` VALUES (33778, '什邡市', 3, 33681, 'S');
INSERT INTO `wa_region` VALUES (33795, '绵竹市', 3, 33681, 'M');
INSERT INTO `wa_region` VALUES (33817, '绵阳市', 2, 33007, 'M');
INSERT INTO `wa_region` VALUES (33818, '市辖区', 3, 33817, 'S');
INSERT INTO `wa_region` VALUES (33819, '涪城区', 3, 33817, 'F');
INSERT INTO `wa_region` VALUES (33844, '游仙区', 3, 33817, 'Y');
INSERT INTO `wa_region` VALUES (33873, '三台县', 3, 33817, 'S');
INSERT INTO `wa_region` VALUES (33937, '盐亭县', 3, 33817, 'Y');
INSERT INTO `wa_region` VALUES (33974, '安县', 3, 33817, 'A');
INSERT INTO `wa_region` VALUES (33995, '梓潼县', 3, 33817, 'Z');
INSERT INTO `wa_region` VALUES (34028, '北川县', 3, 33817, 'B');
INSERT INTO `wa_region` VALUES (34049, '平武县', 3, 33817, 'P');
INSERT INTO `wa_region` VALUES (34075, '江油市', 3, 33817, 'J');
INSERT INTO `wa_region` VALUES (34120, '广元市', 2, 33007, 'G');
INSERT INTO `wa_region` VALUES (34121, '市辖区', 3, 34120, 'S');
INSERT INTO `wa_region` VALUES (34122, '市中区', 3, 34120, 'S');
INSERT INTO `wa_region` VALUES (34143, '元坝区', 3, 34120, 'Y');
INSERT INTO `wa_region` VALUES (34173, '朝天区', 3, 34120, 'C');
INSERT INTO `wa_region` VALUES (34199, '旺苍县', 3, 34120, 'W');
INSERT INTO `wa_region` VALUES (34238, '青川县', 3, 34120, 'Q');
INSERT INTO `wa_region` VALUES (34276, '剑阁县', 3, 34120, 'J');
INSERT INTO `wa_region` VALUES (34334, '苍溪县', 3, 34120, 'C');
INSERT INTO `wa_region` VALUES (34376, '遂宁市', 2, 33007, 'S');
INSERT INTO `wa_region` VALUES (34377, '市辖区', 3, 34376, 'S');
INSERT INTO `wa_region` VALUES (34378, '船山区', 3, 34376, 'C');
INSERT INTO `wa_region` VALUES (34404, '安居区', 3, 34376, 'A');
INSERT INTO `wa_region` VALUES (34426, '蓬溪县', 3, 34376, 'P');
INSERT INTO `wa_region` VALUES (34458, '射洪县', 3, 34376, 'S');
INSERT INTO `wa_region` VALUES (34489, '大英县', 3, 34376, 'D');
INSERT INTO `wa_region` VALUES (34501, '内江市', 2, 33007, 'N');
INSERT INTO `wa_region` VALUES (34502, '市辖区', 3, 34501, 'S');
INSERT INTO `wa_region` VALUES (34503, '市中区', 3, 34501, 'S');
INSERT INTO `wa_region` VALUES (34524, '东兴区', 3, 34501, 'D');
INSERT INTO `wa_region` VALUES (34554, '威远县', 3, 34501, 'W');
INSERT INTO `wa_region` VALUES (34575, '资中县', 3, 34501, 'Z');
INSERT INTO `wa_region` VALUES (34609, '隆昌县', 3, 34501, 'L');
INSERT INTO `wa_region` VALUES (34628, '乐山市', 2, 33007, 'L');
INSERT INTO `wa_region` VALUES (34629, '市辖区', 3, 34628, 'S');
INSERT INTO `wa_region` VALUES (34630, '市中区', 3, 34628, 'S');
INSERT INTO `wa_region` VALUES (34661, '沙湾区', 3, 34628, 'S');
INSERT INTO `wa_region` VALUES (34676, '五通桥区', 3, 34628, 'W');
INSERT INTO `wa_region` VALUES (34689, '金口河区', 3, 34628, 'J');
INSERT INTO `wa_region` VALUES (34696, '犍为县', 3, 34628, 'Q');
INSERT INTO `wa_region` VALUES (34727, '井研县', 3, 34628, 'J');
INSERT INTO `wa_region` VALUES (34755, '夹江县', 3, 34628, 'J');
INSERT INTO `wa_region` VALUES (34778, '沐川县', 3, 34628, 'M');
INSERT INTO `wa_region` VALUES (34799, '峨边县', 3, 34628, 'E');
INSERT INTO `wa_region` VALUES (34819, '马边县', 3, 34628, 'M');
INSERT INTO `wa_region` VALUES (34840, '峨眉山市', 3, 34628, 'E');
INSERT INTO `wa_region` VALUES (34859, '南充市', 2, 33007, 'N');
INSERT INTO `wa_region` VALUES (34860, '市辖区', 3, 34859, 'S');
INSERT INTO `wa_region` VALUES (34861, '顺庆区', 3, 34859, 'S');
INSERT INTO `wa_region` VALUES (34890, '高坪区', 3, 34859, 'G');
INSERT INTO `wa_region` VALUES (34923, '嘉陵区', 3, 34859, 'J');
INSERT INTO `wa_region` VALUES (34967, '南部县', 3, 34859, 'N');
INSERT INTO `wa_region` VALUES (35040, '营山县', 3, 34859, 'Y');
INSERT INTO `wa_region` VALUES (35094, '蓬安县', 3, 34859, 'P');
INSERT INTO `wa_region` VALUES (35134, '仪陇县', 3, 34859, 'Y');
INSERT INTO `wa_region` VALUES (35193, '西充县', 3, 34859, 'X');
INSERT INTO `wa_region` VALUES (35238, '阆中市', 3, 34859, 'L');
INSERT INTO `wa_region` VALUES (35288, '眉山市', 2, 33007, 'M');
INSERT INTO `wa_region` VALUES (35289, '市辖区', 3, 35288, 'S');
INSERT INTO `wa_region` VALUES (35290, '东坡区', 3, 35288, 'D');
INSERT INTO `wa_region` VALUES (35317, '仁寿县', 3, 35288, 'R');
INSERT INTO `wa_region` VALUES (35378, '彭山县', 3, 35288, 'P');
INSERT INTO `wa_region` VALUES (35392, '洪雅县', 3, 35288, 'H');
INSERT INTO `wa_region` VALUES (35408, '丹棱县', 3, 35288, 'D');
INSERT INTO `wa_region` VALUES (35416, '青神县', 3, 35288, 'Q');
INSERT INTO `wa_region` VALUES (35427, '宜宾市', 2, 33007, 'Y');
INSERT INTO `wa_region` VALUES (35428, '市辖区', 3, 35427, 'S');
INSERT INTO `wa_region` VALUES (35429, '翠屏区', 3, 35427, 'C');
INSERT INTO `wa_region` VALUES (35454, '宜宾县', 3, 35427, 'Y');
INSERT INTO `wa_region` VALUES (35481, '南溪县', 3, 35427, 'N');
INSERT INTO `wa_region` VALUES (35497, '江安县', 3, 35427, 'J');
INSERT INTO `wa_region` VALUES (35516, '长宁县', 3, 35427, 'C');
INSERT INTO `wa_region` VALUES (35535, '高县', 3, 35427, 'G');
INSERT INTO `wa_region` VALUES (35555, '珙县', 3, 35427, 'G');
INSERT INTO `wa_region` VALUES (35573, '筠连县', 3, 35427, 'J');
INSERT INTO `wa_region` VALUES (35592, '兴文县', 3, 35427, 'X');
INSERT INTO `wa_region` VALUES (35608, '屏山县', 3, 35427, 'P');
INSERT INTO `wa_region` VALUES (35625, '广安市', 2, 33007, 'G');
INSERT INTO `wa_region` VALUES (35626, '市辖区', 3, 35625, 'S');
INSERT INTO `wa_region` VALUES (35627, '广安区', 3, 35625, 'G');
INSERT INTO `wa_region` VALUES (35677, '岳池县', 3, 35625, 'Y');
INSERT INTO `wa_region` VALUES (35721, '武胜县', 3, 35625, 'W');
INSERT INTO `wa_region` VALUES (35753, '邻水县', 3, 35625, 'L');
INSERT INTO `wa_region` VALUES (35799, '华蓥市', 3, 35625, 'H');
INSERT INTO `wa_region` VALUES (35813, '达州市', 2, 33007, 'D');
INSERT INTO `wa_region` VALUES (35814, '市辖区', 3, 35813, 'S');
INSERT INTO `wa_region` VALUES (35815, '通川区', 3, 35813, 'T');
INSERT INTO `wa_region` VALUES (35829, '达县', 3, 35813, 'D');
INSERT INTO `wa_region` VALUES (35894, '宣汉县', 3, 35813, 'X');
INSERT INTO `wa_region` VALUES (35949, '开江县', 3, 35813, 'K');
INSERT INTO `wa_region` VALUES (35970, '大竹县', 3, 35813, 'D');
INSERT INTO `wa_region` VALUES (36021, '渠县', 3, 35813, 'Q');
INSERT INTO `wa_region` VALUES (36082, '万源市', 3, 35813, 'W');
INSERT INTO `wa_region` VALUES (36136, '雅安市', 2, 33007, 'Y');
INSERT INTO `wa_region` VALUES (36137, '市辖区', 3, 36136, 'S');
INSERT INTO `wa_region` VALUES (36138, '雨城区', 3, 36136, 'Y');
INSERT INTO `wa_region` VALUES (36161, '名山县', 3, 36136, 'M');
INSERT INTO `wa_region` VALUES (36182, '荥经县', 3, 36136, 'Y');
INSERT INTO `wa_region` VALUES (36204, '汉源县', 3, 36136, 'H');
INSERT INTO `wa_region` VALUES (36245, '石棉县', 3, 36136, 'S');
INSERT INTO `wa_region` VALUES (36263, '天全县', 3, 36136, 'T');
INSERT INTO `wa_region` VALUES (36279, '芦山县', 3, 36136, 'L');
INSERT INTO `wa_region` VALUES (36289, '宝兴县', 3, 36136, 'B');
INSERT INTO `wa_region` VALUES (36299, '巴中市', 2, 33007, 'B');
INSERT INTO `wa_region` VALUES (36300, '市辖区', 3, 36299, 'S');
INSERT INTO `wa_region` VALUES (36301, '巴州区', 3, 36299, 'B');
INSERT INTO `wa_region` VALUES (36354, '通江县', 3, 36299, 'T');
INSERT INTO `wa_region` VALUES (36404, '南江县', 3, 36299, 'N');
INSERT INTO `wa_region` VALUES (36453, '平昌县', 3, 36299, 'P');
INSERT INTO `wa_region` VALUES (36497, '资阳市', 2, 33007, 'Z');
INSERT INTO `wa_region` VALUES (36498, '市辖区', 3, 36497, 'S');
INSERT INTO `wa_region` VALUES (36499, '雁江区', 3, 36497, 'Y');
INSERT INTO `wa_region` VALUES (36527, '安岳县', 3, 36497, 'A');
INSERT INTO `wa_region` VALUES (36597, '乐至县', 3, 36497, 'L');
INSERT INTO `wa_region` VALUES (36623, '简阳市', 3, 36497, 'J');
INSERT INTO `wa_region` VALUES (36679, '阿坝州', 2, 33007, 'A');
INSERT INTO `wa_region` VALUES (36680, '汶川县', 3, 36679, 'W');
INSERT INTO `wa_region` VALUES (36694, '理县', 3, 36679, 'L');
INSERT INTO `wa_region` VALUES (36708, '茂县', 3, 36679, 'M');
INSERT INTO `wa_region` VALUES (36731, '松潘县', 3, 36679, 'S');
INSERT INTO `wa_region` VALUES (36759, '九寨沟县', 3, 36679, 'J');
INSERT INTO `wa_region` VALUES (36778, '金川县', 3, 36679, 'J');
INSERT INTO `wa_region` VALUES (36802, '小金县', 3, 36679, 'X');
INSERT INTO `wa_region` VALUES (36824, '黑水县', 3, 36679, 'H');
INSERT INTO `wa_region` VALUES (36842, '马尔康县', 3, 36679, 'M');
INSERT INTO `wa_region` VALUES (36858, '壤塘县', 3, 36679, 'R');
INSERT INTO `wa_region` VALUES (36871, '阿坝县', 3, 36679, 'A');
INSERT INTO `wa_region` VALUES (36893, '若尔盖县', 3, 36679, 'R');
INSERT INTO `wa_region` VALUES (36914, '红原县', 3, 36679, 'H');
INSERT INTO `wa_region` VALUES (36926, '甘孜州', 2, 33007, 'G');
INSERT INTO `wa_region` VALUES (36927, '康定县', 3, 36926, 'K');
INSERT INTO `wa_region` VALUES (36949, '泸定县', 3, 36926, 'L');
INSERT INTO `wa_region` VALUES (36962, '丹巴县', 3, 36926, 'D');
INSERT INTO `wa_region` VALUES (36978, '九龙县', 3, 36926, 'J');
INSERT INTO `wa_region` VALUES (36997, '雅江县', 3, 36926, 'Y');
INSERT INTO `wa_region` VALUES (37015, '道孚县', 3, 36926, 'D');
INSERT INTO `wa_region` VALUES (37038, '炉霍县', 3, 36926, 'L');
INSERT INTO `wa_region` VALUES (37055, '甘孜县', 3, 36926, 'G');
INSERT INTO `wa_region` VALUES (37078, '新龙县', 3, 36926, 'X');
INSERT INTO `wa_region` VALUES (37098, '德格县', 3, 36926, 'D');
INSERT INTO `wa_region` VALUES (37125, '白玉县', 3, 36926, 'B');
INSERT INTO `wa_region` VALUES (37143, '石渠县', 3, 36926, 'S');
INSERT INTO `wa_region` VALUES (37166, '色达县', 3, 36926, 'S');
INSERT INTO `wa_region` VALUES (37184, '理塘县', 3, 36926, 'L');
INSERT INTO `wa_region` VALUES (37209, '巴塘县', 3, 36926, 'B');
INSERT INTO `wa_region` VALUES (37229, '乡城县', 3, 36926, 'X');
INSERT INTO `wa_region` VALUES (37242, '稻城县', 3, 36926, 'D');
INSERT INTO `wa_region` VALUES (37257, '得荣县', 3, 36926, 'D');
INSERT INTO `wa_region` VALUES (37270, '凉山州', 2, 33007, 'L');
INSERT INTO `wa_region` VALUES (37271, '西昌市', 3, 37270, 'X');
INSERT INTO `wa_region` VALUES (37315, '木里县', 3, 37270, 'M');
INSERT INTO `wa_region` VALUES (37345, '盐源县', 3, 37270, 'Y');
INSERT INTO `wa_region` VALUES (37380, '德昌', 3, 37270, 'D');
INSERT INTO `wa_region` VALUES (37404, '会理县', 3, 37270, 'H');
INSERT INTO `wa_region` VALUES (37455, '会东县', 3, 37270, 'H');
INSERT INTO `wa_region` VALUES (37509, '宁南县', 3, 37270, 'N');
INSERT INTO `wa_region` VALUES (37535, '普格县', 3, 37270, 'P');
INSERT INTO `wa_region` VALUES (37570, '布拖县', 3, 37270, 'B');
INSERT INTO `wa_region` VALUES (37601, '金阳县', 3, 37270, 'J');
INSERT INTO `wa_region` VALUES (37636, '昭觉县', 3, 37270, 'Z');
INSERT INTO `wa_region` VALUES (37684, '喜德县', 3, 37270, 'X');
INSERT INTO `wa_region` VALUES (37709, '冕宁县', 3, 37270, 'M');
INSERT INTO `wa_region` VALUES (37748, '越西县', 3, 37270, 'Y');
INSERT INTO `wa_region` VALUES (37790, '甘洛县', 3, 37270, 'G');
INSERT INTO `wa_region` VALUES (37819, '美姑县', 3, 37270, 'M');
INSERT INTO `wa_region` VALUES (37856, '雷波县', 3, 37270, 'L');
INSERT INTO `wa_region` VALUES (37906, '贵州省', 1, 0, 'G');
INSERT INTO `wa_region` VALUES (37907, '贵阳市', 2, 37906, 'G');
INSERT INTO `wa_region` VALUES (37908, '市辖区', 3, 37907, 'S');
INSERT INTO `wa_region` VALUES (37909, '南明区', 3, 37907, 'N');
INSERT INTO `wa_region` VALUES (37927, '云岩区', 3, 37907, 'Y');
INSERT INTO `wa_region` VALUES (37944, '花溪区', 3, 37907, 'H');
INSERT INTO `wa_region` VALUES (37961, '乌当区', 3, 37907, 'W');
INSERT INTO `wa_region` VALUES (37977, '白云区', 3, 37907, 'B');
INSERT INTO `wa_region` VALUES (37987, '小河区', 3, 37907, 'X');
INSERT INTO `wa_region` VALUES (37991, '开阳县', 3, 37907, 'K');
INSERT INTO `wa_region` VALUES (38008, '息烽县', 3, 37907, 'X');
INSERT INTO `wa_region` VALUES (38019, '修文县', 3, 37907, 'X');
INSERT INTO `wa_region` VALUES (38030, '清镇市', 3, 37907, 'Q');
INSERT INTO `wa_region` VALUES (38042, '六盘水市', 2, 37906, 'L');
INSERT INTO `wa_region` VALUES (38043, '钟山区', 3, 38042, 'Z');
INSERT INTO `wa_region` VALUES (38053, '六枝特区', 3, 38042, 'L');
INSERT INTO `wa_region` VALUES (38073, '水城县', 3, 38042, 'S');
INSERT INTO `wa_region` VALUES (38107, '盘县', 3, 38042, 'P');
INSERT INTO `wa_region` VALUES (38145, '遵义市', 2, 37906, 'Z');
INSERT INTO `wa_region` VALUES (38146, '市辖区', 3, 38145, 'S');
INSERT INTO `wa_region` VALUES (38147, '红花岗区', 3, 38145, 'H');
INSERT INTO `wa_region` VALUES (38164, '汇川区', 3, 38145, 'H');
INSERT INTO `wa_region` VALUES (38174, '遵义县', 3, 38145, 'Z');
INSERT INTO `wa_region` VALUES (38206, '桐梓县', 3, 38145, 'T');
INSERT INTO `wa_region` VALUES (38231, '绥阳县', 3, 38145, 'S');
INSERT INTO `wa_region` VALUES (38247, '正安县', 3, 38145, 'Z');
INSERT INTO `wa_region` VALUES (38267, '道真县', 3, 38145, 'D');
INSERT INTO `wa_region` VALUES (38282, '务川县', 3, 38145, 'W');
INSERT INTO `wa_region` VALUES (38298, '凤冈县', 3, 38145, 'F');
INSERT INTO `wa_region` VALUES (38313, '湄潭县', 3, 38145, 'M');
INSERT INTO `wa_region` VALUES (38329, '余庆县', 3, 38145, 'Y');
INSERT INTO `wa_region` VALUES (38340, '习水县', 3, 38145, 'X');
INSERT INTO `wa_region` VALUES (38364, '赤水市', 3, 38145, 'C');
INSERT INTO `wa_region` VALUES (38382, '仁怀市', 3, 38145, 'R');
INSERT INTO `wa_region` VALUES (38402, '安顺市', 2, 37906, 'A');
INSERT INTO `wa_region` VALUES (38403, '市辖区', 3, 38402, 'S');
INSERT INTO `wa_region` VALUES (38404, '西秀区', 3, 38402, 'X');
INSERT INTO `wa_region` VALUES (38429, '平坝县', 3, 38402, 'P');
INSERT INTO `wa_region` VALUES (38440, '普定县', 3, 38402, 'P');
INSERT INTO `wa_region` VALUES (38452, '镇宁县', 3, 38402, 'Z');
INSERT INTO `wa_region` VALUES (38469, '关岭县', 3, 38402, 'G');
INSERT INTO `wa_region` VALUES (38484, '紫云县', 3, 38402, 'Z');
INSERT INTO `wa_region` VALUES (38497, '铜仁地区', 2, 37906, 'T');
INSERT INTO `wa_region` VALUES (38498, '铜仁市', 3, 38497, 'T');
INSERT INTO `wa_region` VALUES (38516, '江口县', 3, 38497, 'J');
INSERT INTO `wa_region` VALUES (38526, '玉屏县', 3, 38497, 'Y');
INSERT INTO `wa_region` VALUES (38533, '石阡县', 3, 38497, 'S');
INSERT INTO `wa_region` VALUES (38552, '思南县　', 3, 38497, 'S');
INSERT INTO `wa_region` VALUES (38580, '印江县', 3, 38497, 'Y');
INSERT INTO `wa_region` VALUES (38598, '德江县', 3, 38497, 'D');
INSERT INTO `wa_region` VALUES (38619, '沿河县', 3, 38497, 'Y');
INSERT INTO `wa_region` VALUES (38642, '松桃县', 3, 38497, 'S');
INSERT INTO `wa_region` VALUES (38671, '万山特区', 3, 38497, 'W');
INSERT INTO `wa_region` VALUES (38677, '黔西南州', 2, 37906, 'Q');
INSERT INTO `wa_region` VALUES (38678, '兴义市', 3, 38677, 'X');
INSERT INTO `wa_region` VALUES (38705, '兴仁县', 3, 38677, 'X');
INSERT INTO `wa_region` VALUES (38722, '普安县', 3, 38677, 'P');
INSERT INTO `wa_region` VALUES (38737, '晴隆县', 3, 38677, 'Q');
INSERT INTO `wa_region` VALUES (38752, '贞丰县', 3, 38677, 'Z');
INSERT INTO `wa_region` VALUES (38766, '望谟县', 3, 38677, 'W');
INSERT INTO `wa_region` VALUES (38784, '册亨县', 3, 38677, 'C');
INSERT INTO `wa_region` VALUES (38799, '安龙县', 3, 38677, 'A');
INSERT INTO `wa_region` VALUES (38816, '毕节地区', 2, 37906, 'B');
INSERT INTO `wa_region` VALUES (38817, '毕节市', 3, 38816, 'B');
INSERT INTO `wa_region` VALUES (38859, '大方县', 3, 38816, 'D');
INSERT INTO `wa_region` VALUES (38896, '黔西县', 3, 38816, 'Q');
INSERT INTO `wa_region` VALUES (38925, '金沙县', 3, 38816, 'J');
INSERT INTO `wa_region` VALUES (38952, '织金县', 3, 38816, 'Z');
INSERT INTO `wa_region` VALUES (38985, '纳雍县', 3, 38816, 'N');
INSERT INTO `wa_region` VALUES (39011, '威宁县', 3, 38816, 'W');
INSERT INTO `wa_region` VALUES (39047, '赫章县', 3, 38816, 'H');
INSERT INTO `wa_region` VALUES (39075, '黔东南州', 2, 37906, 'Q');
INSERT INTO `wa_region` VALUES (39076, '凯里市', 3, 39075, 'K');
INSERT INTO `wa_region` VALUES (39092, '黄平县', 3, 39075, 'H');
INSERT INTO `wa_region` VALUES (39107, '施秉县', 3, 39075, 'S');
INSERT INTO `wa_region` VALUES (39116, '三穗县', 3, 39075, 'S');
INSERT INTO `wa_region` VALUES (39126, '镇远县', 3, 39075, 'Z');
INSERT INTO `wa_region` VALUES (39139, '岑巩县', 3, 39075, 'C');
INSERT INTO `wa_region` VALUES (39151, '天柱县', 3, 39075, 'T');
INSERT INTO `wa_region` VALUES (39168, '锦屏县', 3, 39075, 'J');
INSERT INTO `wa_region` VALUES (39184, '剑河县', 3, 39075, 'J');
INSERT INTO `wa_region` VALUES (39197, '台江县', 3, 39075, 'T');
INSERT INTO `wa_region` VALUES (39206, '黎平县', 3, 39075, 'L');
INSERT INTO `wa_region` VALUES (39232, '榕江县', 3, 39075, 'R');
INSERT INTO `wa_region` VALUES (39252, '从江县', 3, 39075, 'C');
INSERT INTO `wa_region` VALUES (39274, '雷山县', 3, 39075, 'L');
INSERT INTO `wa_region` VALUES (39284, '麻江县', 3, 39075, 'M');
INSERT INTO `wa_region` VALUES (39294, '丹寨县', 3, 39075, 'D');
INSERT INTO `wa_region` VALUES (39302, '黔南州', 2, 37906, 'Q');
INSERT INTO `wa_region` VALUES (39303, '都匀市', 3, 39302, 'D');
INSERT INTO `wa_region` VALUES (39327, '福泉市', 3, 39302, 'F');
INSERT INTO `wa_region` VALUES (39345, '荔波县', 3, 39302, 'L');
INSERT INTO `wa_region` VALUES (39363, '贵定县', 3, 39302, 'G');
INSERT INTO `wa_region` VALUES (39384, '瓮安县', 3, 39302, 'W');
INSERT INTO `wa_region` VALUES (39408, '独山县', 3, 39302, 'D');
INSERT INTO `wa_region` VALUES (39427, '平塘县', 3, 39302, 'P');
INSERT INTO `wa_region` VALUES (39447, '罗甸县', 3, 39302, 'L');
INSERT INTO `wa_region` VALUES (39474, '长顺县', 3, 39302, 'C');
INSERT INTO `wa_region` VALUES (39493, '龙里县', 3, 39302, 'L');
INSERT INTO `wa_region` VALUES (39508, '惠水县', 3, 39302, 'H');
INSERT INTO `wa_region` VALUES (39534, '三都县', 3, 39302, 'S');
INSERT INTO `wa_region` VALUES (39556, '云南省', 1, 0, 'Y');
INSERT INTO `wa_region` VALUES (39557, '昆明市', 2, 39556, 'K');
INSERT INTO `wa_region` VALUES (39558, '市辖区', 3, 39557, 'S');
INSERT INTO `wa_region` VALUES (39559, '五华区', 3, 39557, 'W');
INSERT INTO `wa_region` VALUES (39571, '盘龙区', 3, 39557, 'P');
INSERT INTO `wa_region` VALUES (39582, '官渡区', 3, 39557, 'G');
INSERT INTO `wa_region` VALUES (39594, '西山区', 3, 39557, 'X');
INSERT INTO `wa_region` VALUES (39605, '东川区', 3, 39557, 'D');
INSERT INTO `wa_region` VALUES (39614, '呈贡县', 3, 39557, 'C');
INSERT INTO `wa_region` VALUES (39622, '晋宁县', 3, 39557, 'J');
INSERT INTO `wa_region` VALUES (39632, '富民县', 3, 39557, 'F');
INSERT INTO `wa_region` VALUES (39640, '宜良县', 3, 39557, 'Y');
INSERT INTO `wa_region` VALUES (39650, '石林县', 3, 39557, 'S');
INSERT INTO `wa_region` VALUES (39659, '嵩明县', 3, 39557, 'S');
INSERT INTO `wa_region` VALUES (39667, '禄劝县', 3, 39557, 'L');
INSERT INTO `wa_region` VALUES (39684, '寻甸县', 3, 39557, 'X');
INSERT INTO `wa_region` VALUES (39701, '安宁市', 3, 39557, 'A');
INSERT INTO `wa_region` VALUES (39710, '曲靖市', 2, 39556, 'Q');
INSERT INTO `wa_region` VALUES (39711, '市辖区', 3, 39710, 'S');
INSERT INTO `wa_region` VALUES (39712, '麒麟区', 3, 39710, 'Q');
INSERT INTO `wa_region` VALUES (39724, '马龙县', 3, 39710, 'M');
INSERT INTO `wa_region` VALUES (39733, '陆良县', 3, 39710, 'L');
INSERT INTO `wa_region` VALUES (39744, '师宗县', 3, 39710, 'S');
INSERT INTO `wa_region` VALUES (39753, '罗平县', 3, 39710, 'L');
INSERT INTO `wa_region` VALUES (39766, '富源县', 3, 39710, 'F');
INSERT INTO `wa_region` VALUES (39778, '会泽县', 3, 39710, 'H');
INSERT INTO `wa_region` VALUES (39800, '沾益县', 3, 39710, 'Z');
INSERT INTO `wa_region` VALUES (39809, '宣威市', 3, 39710, 'X');
INSERT INTO `wa_region` VALUES (39836, '玉溪市', 2, 39556, 'Y');
INSERT INTO `wa_region` VALUES (39837, '市辖区', 3, 39836, 'S');
INSERT INTO `wa_region` VALUES (39838, '红塔区', 3, 39836, 'H');
INSERT INTO `wa_region` VALUES (39850, '江川县', 3, 39836, 'J');
INSERT INTO `wa_region` VALUES (39858, '澄江县', 3, 39836, 'C');
INSERT INTO `wa_region` VALUES (39865, '通海县', 3, 39836, 'T');
INSERT INTO `wa_region` VALUES (39875, '华宁县', 3, 39836, 'H');
INSERT INTO `wa_region` VALUES (39881, '易门县', 3, 39836, 'Y');
INSERT INTO `wa_region` VALUES (39889, '峨山县', 3, 39836, 'E');
INSERT INTO `wa_region` VALUES (39899, '新平县', 3, 39836, 'X');
INSERT INTO `wa_region` VALUES (39912, '元江县', 3, 39836, 'Y');
INSERT INTO `wa_region` VALUES (39923, '保山市', 2, 39556, 'B');
INSERT INTO `wa_region` VALUES (39924, '市辖区', 3, 39923, 'S');
INSERT INTO `wa_region` VALUES (39925, '隆阳区', 3, 39923, 'L');
INSERT INTO `wa_region` VALUES (39946, '施甸县', 3, 39923, 'S');
INSERT INTO `wa_region` VALUES (39960, '腾冲县', 3, 39923, 'T');
INSERT INTO `wa_region` VALUES (39979, '龙陵县', 3, 39923, 'L');
INSERT INTO `wa_region` VALUES (39990, '昌宁县', 3, 39923, 'C');
INSERT INTO `wa_region` VALUES (40004, '昭通市', 2, 39556, 'Z');
INSERT INTO `wa_region` VALUES (40005, '市辖区', 3, 40004, 'S');
INSERT INTO `wa_region` VALUES (40006, '昭阳区', 3, 40004, 'Z');
INSERT INTO `wa_region` VALUES (40027, '鲁甸县', 3, 40004, 'L');
INSERT INTO `wa_region` VALUES (40040, '巧家县', 3, 40004, 'Q');
INSERT INTO `wa_region` VALUES (40057, '盐津县', 3, 40004, 'Y');
INSERT INTO `wa_region` VALUES (40068, '大关县', 3, 40004, 'D');
INSERT INTO `wa_region` VALUES (40078, '永善县', 3, 40004, 'Y');
INSERT INTO `wa_region` VALUES (40094, '绥江县', 3, 40004, 'S');
INSERT INTO `wa_region` VALUES (40100, '镇雄县', 3, 40004, 'Z');
INSERT INTO `wa_region` VALUES (40129, '彝良县', 3, 40004, 'Y');
INSERT INTO `wa_region` VALUES (40145, '威信县', 3, 40004, 'W');
INSERT INTO `wa_region` VALUES (40156, '水富县', 3, 40004, 'S');
INSERT INTO `wa_region` VALUES (40160, '丽江市', 2, 39556, 'L');
INSERT INTO `wa_region` VALUES (40161, '市辖区', 3, 40160, 'S');
INSERT INTO `wa_region` VALUES (40162, '古城区', 3, 40160, 'G');
INSERT INTO `wa_region` VALUES (40172, '玉龙县', 3, 40160, 'Y');
INSERT INTO `wa_region` VALUES (40189, '永胜县', 3, 40160, 'Y');
INSERT INTO `wa_region` VALUES (40205, '华坪县', 3, 40160, 'H');
INSERT INTO `wa_region` VALUES (40214, '宁蒗县', 3, 40160, 'N');
INSERT INTO `wa_region` VALUES (40230, '思茅市', 2, 39556, 'S');
INSERT INTO `wa_region` VALUES (40231, '市辖区', 3, 40230, 'S');
INSERT INTO `wa_region` VALUES (40232, '翠云区', 3, 40230, 'C');
INSERT INTO `wa_region` VALUES (40240, '普洱县', 3, 40230, 'P');
INSERT INTO `wa_region` VALUES (40250, '墨江县', 3, 40230, 'M');
INSERT INTO `wa_region` VALUES (40266, '景东县', 3, 40230, 'J');
INSERT INTO `wa_region` VALUES (40280, '景谷县', 3, 40230, 'J');
INSERT INTO `wa_region` VALUES (40291, '镇沅县', 3, 40230, 'Z');
INSERT INTO `wa_region` VALUES (40301, '江城县', 3, 40230, 'J');
INSERT INTO `wa_region` VALUES (40310, '孟连县', 3, 40230, 'M');
INSERT INTO `wa_region` VALUES (40318, '澜沧县', 3, 40230, 'L');
INSERT INTO `wa_region` VALUES (40340, '西盟县', 3, 40230, 'X');
INSERT INTO `wa_region` VALUES (40348, '临沧市', 2, 39556, 'L');
INSERT INTO `wa_region` VALUES (40349, '市辖区', 3, 40348, 'S');
INSERT INTO `wa_region` VALUES (40350, '临翔区', 3, 40348, 'L');
INSERT INTO `wa_region` VALUES (40361, '凤庆县', 3, 40348, 'F');
INSERT INTO `wa_region` VALUES (40375, '云县', 3, 40348, 'Y');
INSERT INTO `wa_region` VALUES (40388, '永德县', 3, 40348, 'Y');
INSERT INTO `wa_region` VALUES (40400, '镇康县', 3, 40348, 'Z');
INSERT INTO `wa_region` VALUES (40408, '双江县', 3, 40348, 'S');
INSERT INTO `wa_region` VALUES (40417, '耿马县', 3, 40348, 'G');
INSERT INTO `wa_region` VALUES (40429, '沧源县', 3, 40348, 'C');
INSERT INTO `wa_region` VALUES (40441, '楚雄州', 2, 39556, 'C');
INSERT INTO `wa_region` VALUES (40442, '楚雄市', 3, 40441, 'C');
INSERT INTO `wa_region` VALUES (40458, '双柏县', 3, 40441, 'S');
INSERT INTO `wa_region` VALUES (40467, '牟定县', 3, 40441, 'M');
INSERT INTO `wa_region` VALUES (40475, '南华县', 3, 40441, 'N');
INSERT INTO `wa_region` VALUES (40486, '姚安县', 3, 40441, 'Y');
INSERT INTO `wa_region` VALUES (40496, '大姚县', 3, 40441, 'D');
INSERT INTO `wa_region` VALUES (40509, '永仁县', 3, 40441, 'Y');
INSERT INTO `wa_region` VALUES (40517, '元谋县', 3, 40441, 'Y');
INSERT INTO `wa_region` VALUES (40528, '武定县', 3, 40441, 'W');
INSERT INTO `wa_region` VALUES (40540, '禄丰县', 3, 40441, 'L');
INSERT INTO `wa_region` VALUES (40555, '红河州', 2, 39556, 'H');
INSERT INTO `wa_region` VALUES (40556, '个旧市', 3, 40555, 'G');
INSERT INTO `wa_region` VALUES (40567, '开远市', 3, 40555, 'K');
INSERT INTO `wa_region` VALUES (40576, '蒙自县', 3, 40555, 'M');
INSERT INTO `wa_region` VALUES (40588, '屏边县', 3, 40555, 'P');
INSERT INTO `wa_region` VALUES (40596, '建水县', 3, 40555, 'J');
INSERT INTO `wa_region` VALUES (40611, '石屏县', 3, 40555, 'S');
INSERT INTO `wa_region` VALUES (40621, '弥勒县', 3, 40555, 'M');
INSERT INTO `wa_region` VALUES (40635, '泸西县', 3, 40555, 'L');
INSERT INTO `wa_region` VALUES (40644, '元阳县', 3, 40555, 'Y');
INSERT INTO `wa_region` VALUES (40659, '红河县', 3, 40555, 'H');
INSERT INTO `wa_region` VALUES (40673, '金平县', 3, 40555, 'J');
INSERT INTO `wa_region` VALUES (40688, '绿春县', 3, 40555, 'L');
INSERT INTO `wa_region` VALUES (40698, '河口县', 3, 40555, 'H');
INSERT INTO `wa_region` VALUES (40705, '文山州', 2, 39556, 'W');
INSERT INTO `wa_region` VALUES (40706, '文山县', 3, 40705, 'W');
INSERT INTO `wa_region` VALUES (40722, '砚山县', 3, 40705, 'Y');
INSERT INTO `wa_region` VALUES (40734, '西畴县', 3, 40705, 'X');
INSERT INTO `wa_region` VALUES (40744, '麻栗坡县', 3, 40705, 'M');
INSERT INTO `wa_region` VALUES (40756, '马关县', 3, 40705, 'M');
INSERT INTO `wa_region` VALUES (40770, '丘北县', 3, 40705, 'Q');
INSERT INTO `wa_region` VALUES (40783, '广南县', 3, 40705, 'G');
INSERT INTO `wa_region` VALUES (40802, '富宁县', 3, 40705, 'F');
INSERT INTO `wa_region` VALUES (40816, '西双版纳州', 2, 39556, 'X');
INSERT INTO `wa_region` VALUES (40817, '景洪市', 3, 40816, 'J');
INSERT INTO `wa_region` VALUES (40829, '勐海县', 3, 40816, 'M');
INSERT INTO `wa_region` VALUES (40841, '勐腊县', 3, 40816, 'M');
INSERT INTO `wa_region` VALUES (40852, '大理州', 2, 39556, 'D');
INSERT INTO `wa_region` VALUES (40853, '大理市', 3, 40852, 'D');
INSERT INTO `wa_region` VALUES (40866, '漾濞县', 3, 40852, 'Y');
INSERT INTO `wa_region` VALUES (40876, '祥云县', 3, 40852, 'X');
INSERT INTO `wa_region` VALUES (40887, '宾川县', 3, 40852, 'B');
INSERT INTO `wa_region` VALUES (40901, '弥渡县', 3, 40852, 'M');
INSERT INTO `wa_region` VALUES (40910, '南涧县', 3, 40852, 'N');
INSERT INTO `wa_region` VALUES (40919, '巍山县', 3, 40852, 'W');
INSERT INTO `wa_region` VALUES (40930, '永平县', 3, 40852, 'Y');
INSERT INTO `wa_region` VALUES (40938, '云龙县', 3, 40852, 'Y');
INSERT INTO `wa_region` VALUES (40950, '洱源县', 3, 40852, 'E');
INSERT INTO `wa_region` VALUES (40960, '剑川县', 3, 40852, 'J');
INSERT INTO `wa_region` VALUES (40969, '鹤庆县', 3, 40852, 'H');
INSERT INTO `wa_region` VALUES (40979, '德宏州', 2, 39556, 'D');
INSERT INTO `wa_region` VALUES (40980, '瑞丽市', 3, 40979, 'R');
INSERT INTO `wa_region` VALUES (40988, '潞西市', 3, 40979, 'L');
INSERT INTO `wa_region` VALUES (41000, '梁河县', 3, 40979, 'L');
INSERT INTO `wa_region` VALUES (41010, '盈江县', 3, 40979, 'Y');
INSERT INTO `wa_region` VALUES (41026, '陇川县', 3, 40979, 'L');
INSERT INTO `wa_region` VALUES (41036, '怒江州', 2, 39556, 'N');
INSERT INTO `wa_region` VALUES (41037, '泸水县', 3, 41036, 'L');
INSERT INTO `wa_region` VALUES (41047, '福贡县', 3, 41036, 'F');
INSERT INTO `wa_region` VALUES (41055, '贡山县', 3, 41036, 'G');
INSERT INTO `wa_region` VALUES (41061, '兰坪县', 3, 41036, 'L');
INSERT INTO `wa_region` VALUES (41070, '迪庆州', 2, 39556, 'D');
INSERT INTO `wa_region` VALUES (41071, '香格里拉县', 3, 41070, 'X');
INSERT INTO `wa_region` VALUES (41083, '德钦县', 3, 41070, 'D');
INSERT INTO `wa_region` VALUES (41092, '维西县', 3, 41070, 'W');
INSERT INTO `wa_region` VALUES (41103, '西藏自治区', 1, 0, 'X');
INSERT INTO `wa_region` VALUES (41104, '拉萨市', 2, 41103, 'L');
INSERT INTO `wa_region` VALUES (41105, '市辖区', 3, 41104, 'S');
INSERT INTO `wa_region` VALUES (41106, '城关区', 3, 41104, 'C');
INSERT INTO `wa_region` VALUES (41118, '林周县', 3, 41104, 'L');
INSERT INTO `wa_region` VALUES (41129, '当雄县', 3, 41104, 'D');
INSERT INTO `wa_region` VALUES (41138, '尼木县', 3, 41104, 'N');
INSERT INTO `wa_region` VALUES (41147, '曲水县', 3, 41104, 'Q');
INSERT INTO `wa_region` VALUES (41154, '堆龙德庆', 3, 41104, 'D');
INSERT INTO `wa_region` VALUES (41162, '达孜县', 3, 41104, 'D');
INSERT INTO `wa_region` VALUES (41169, '墨竹工卡县', 3, 41104, 'M');
INSERT INTO `wa_region` VALUES (41178, '昌都地区', 2, 41103, 'C');
INSERT INTO `wa_region` VALUES (41179, '昌都县', 3, 41178, 'C');
INSERT INTO `wa_region` VALUES (41195, '江达县', 3, 41178, 'J');
INSERT INTO `wa_region` VALUES (41209, '贡觉县', 3, 41178, 'G');
INSERT INTO `wa_region` VALUES (41222, '类乌齐县', 3, 41178, 'L');
INSERT INTO `wa_region` VALUES (41233, '丁青县', 3, 41178, 'D');
INSERT INTO `wa_region` VALUES (41247, '察亚县', 3, 41178, 'C');
INSERT INTO `wa_region` VALUES (41261, '八宿县', 3, 41178, 'B');
INSERT INTO `wa_region` VALUES (41276, '左贡县', 3, 41178, 'Z');
INSERT INTO `wa_region` VALUES (41287, '芒康县', 3, 41178, 'M');
INSERT INTO `wa_region` VALUES (41304, '洛隆县', 3, 41178, 'L');
INSERT INTO `wa_region` VALUES (41316, '边坝县', 3, 41178, 'B');
INSERT INTO `wa_region` VALUES (41328, '山南地区', 2, 41103, 'S');
INSERT INTO `wa_region` VALUES (41329, '乃东县', 3, 41328, 'N');
INSERT INTO `wa_region` VALUES (41337, '扎囊县', 3, 41328, 'Z');
INSERT INTO `wa_region` VALUES (41343, '贡嘎县', 3, 41328, 'G');
INSERT INTO `wa_region` VALUES (41352, '桑日县', 3, 41328, 'S');
INSERT INTO `wa_region` VALUES (41357, '琼结县', 3, 41328, 'Q');
INSERT INTO `wa_region` VALUES (41362, '曲松县', 3, 41328, 'Q');
INSERT INTO `wa_region` VALUES (41368, '措美县', 3, 41328, 'C');
INSERT INTO `wa_region` VALUES (41373, '洛扎县', 3, 41328, 'L');
INSERT INTO `wa_region` VALUES (41381, '加查县', 3, 41328, 'J');
INSERT INTO `wa_region` VALUES (41389, '隆子县', 3, 41328, 'L');
INSERT INTO `wa_region` VALUES (41401, '错那县', 3, 41328, 'C');
INSERT INTO `wa_region` VALUES (41412, '浪卡子县', 3, 41328, 'L');
INSERT INTO `wa_region` VALUES (41423, '日喀则地区', 2, 41103, 'R');
INSERT INTO `wa_region` VALUES (41424, '日喀则市', 3, 41423, 'R');
INSERT INTO `wa_region` VALUES (41437, '南木林县', 3, 41423, 'N');
INSERT INTO `wa_region` VALUES (41455, '江孜县', 3, 41423, 'J');
INSERT INTO `wa_region` VALUES (41475, '定日县', 3, 41423, 'D');
INSERT INTO `wa_region` VALUES (41489, '萨迦县', 3, 41423, 'S');
INSERT INTO `wa_region` VALUES (41501, '拉孜县', 3, 41423, 'L');
INSERT INTO `wa_region` VALUES (41513, '昂仁县', 3, 41423, 'A');
INSERT INTO `wa_region` VALUES (41531, '谢通门县', 3, 41423, 'X');
INSERT INTO `wa_region` VALUES (41551, '白朗县', 3, 41423, 'B');
INSERT INTO `wa_region` VALUES (41563, '仁布县', 3, 41423, 'R');
INSERT INTO `wa_region` VALUES (41573, '康马县', 3, 41423, 'K');
INSERT INTO `wa_region` VALUES (41583, '定结县', 3, 41423, 'D');
INSERT INTO `wa_region` VALUES (41594, '仲巴县', 3, 41423, 'Z');
INSERT INTO `wa_region` VALUES (41608, '亚东县', 3, 41423, 'Y');
INSERT INTO `wa_region` VALUES (41616, '吉隆县', 3, 41423, 'J');
INSERT INTO `wa_region` VALUES (41622, '聂拉木县', 3, 41423, 'N');
INSERT INTO `wa_region` VALUES (41630, '萨嘎县', 3, 41423, 'S');
INSERT INTO `wa_region` VALUES (41639, '岗巴县', 3, 41423, 'G');
INSERT INTO `wa_region` VALUES (41645, '那曲地区', 2, 41103, 'N');
INSERT INTO `wa_region` VALUES (41646, '那曲县', 3, 41645, 'N');
INSERT INTO `wa_region` VALUES (41659, '嘉黎县', 3, 41645, 'J');
INSERT INTO `wa_region` VALUES (41670, '比如县', 3, 41645, 'B');
INSERT INTO `wa_region` VALUES (41681, '聂荣县', 3, 41645, 'N');
INSERT INTO `wa_region` VALUES (41692, '安多县', 3, 41645, 'A');
INSERT INTO `wa_region` VALUES (41706, '申扎县', 3, 41645, 'S');
INSERT INTO `wa_region` VALUES (41715, '索县', 3, 41645, 'S');
INSERT INTO `wa_region` VALUES (41726, '班戈县', 3, 41645, 'B');
INSERT INTO `wa_region` VALUES (41737, '巴青县', 3, 41645, 'B');
INSERT INTO `wa_region` VALUES (41748, '尼玛县', 3, 41645, 'N');
INSERT INTO `wa_region` VALUES (41770, '阿里地区', 2, 41103, 'A');
INSERT INTO `wa_region` VALUES (41771, '普兰县', 3, 41770, 'P');
INSERT INTO `wa_region` VALUES (41775, '札达县', 3, 41770, 'Z');
INSERT INTO `wa_region` VALUES (41782, '噶尔县', 3, 41770, 'G');
INSERT INTO `wa_region` VALUES (41788, '日土县', 3, 41770, 'R');
INSERT INTO `wa_region` VALUES (41794, '革吉县', 3, 41770, 'G');
INSERT INTO `wa_region` VALUES (41800, '改则县', 3, 41770, 'G');
INSERT INTO `wa_region` VALUES (41808, '措勤县', 3, 41770, 'C');
INSERT INTO `wa_region` VALUES (41814, '林芝地区', 2, 41103, 'L');
INSERT INTO `wa_region` VALUES (41815, '林芝县', 3, 41814, 'L');
INSERT INTO `wa_region` VALUES (41823, '工布江达县', 3, 41814, 'G');
INSERT INTO `wa_region` VALUES (41833, '米林县', 3, 41814, 'M');
INSERT INTO `wa_region` VALUES (41842, '墨脱县', 3, 41814, 'M');
INSERT INTO `wa_region` VALUES (41851, '波密县', 3, 41814, 'B');
INSERT INTO `wa_region` VALUES (41863, '察隅县', 3, 41814, 'C');
INSERT INTO `wa_region` VALUES (41870, '朗县', 3, 41814, 'L');
INSERT INTO `wa_region` VALUES (41877, '陕西省', 1, 0, 'S');
INSERT INTO `wa_region` VALUES (41878, '西安市', 2, 41877, 'X');
INSERT INTO `wa_region` VALUES (41879, '市辖区', 3, 41878, 'S');
INSERT INTO `wa_region` VALUES (41880, '新城区', 3, 41878, 'X');
INSERT INTO `wa_region` VALUES (41890, '碑林区', 3, 41878, 'B');
INSERT INTO `wa_region` VALUES (41899, '莲湖区', 3, 41878, 'L');
INSERT INTO `wa_region` VALUES (41909, '灞桥区', 3, 41878, 'B');
INSERT INTO `wa_region` VALUES (41919, '未央区', 3, 41878, 'W');
INSERT INTO `wa_region` VALUES (41930, '雁塔区', 3, 41878, 'Y');
INSERT INTO `wa_region` VALUES (41939, '阎良区', 3, 41878, 'Y');
INSERT INTO `wa_region` VALUES (41947, '临潼区', 3, 41878, 'L');
INSERT INTO `wa_region` VALUES (41971, '长安区', 3, 41878, 'C');
INSERT INTO `wa_region` VALUES (41997, '蓝田县', 3, 41878, 'L');
INSERT INTO `wa_region` VALUES (42020, '周至县', 3, 41878, 'Z');
INSERT INTO `wa_region` VALUES (42043, '户县', 3, 41878, 'H');
INSERT INTO `wa_region` VALUES (42060, '高陵县', 3, 41878, 'G');
INSERT INTO `wa_region` VALUES (42069, '铜川市', 2, 41877, 'T');
INSERT INTO `wa_region` VALUES (42070, '市辖区', 3, 42069, 'S');
INSERT INTO `wa_region` VALUES (42071, '王益区', 3, 42069, 'W');
INSERT INTO `wa_region` VALUES (42079, '印台区', 3, 42069, 'Y');
INSERT INTO `wa_region` VALUES (42091, '耀州区', 3, 42069, 'Y');
INSERT INTO `wa_region` VALUES (42108, '宜君县', 3, 42069, 'Y');
INSERT INTO `wa_region` VALUES (42119, '宝鸡市', 2, 41877, 'B');
INSERT INTO `wa_region` VALUES (42120, '市辖区', 3, 42119, 'S');
INSERT INTO `wa_region` VALUES (42121, '渭滨区', 3, 42119, 'W');
INSERT INTO `wa_region` VALUES (42133, '金台区', 3, 42119, 'J');
INSERT INTO `wa_region` VALUES (42146, '陈仓区', 3, 42119, 'C');
INSERT INTO `wa_region` VALUES (42165, '凤翔县', 3, 42119, 'F');
INSERT INTO `wa_region` VALUES (42183, '岐山县', 3, 42119, 'Q');
INSERT INTO `wa_region` VALUES (42198, '扶风县', 3, 42119, 'F');
INSERT INTO `wa_region` VALUES (42211, '眉县', 3, 42119, 'M');
INSERT INTO `wa_region` VALUES (42224, '陇县', 3, 42119, 'L');
INSERT INTO `wa_region` VALUES (42240, '千阳县', 3, 42119, 'Q');
INSERT INTO `wa_region` VALUES (42252, '麟游县', 3, 42119, 'L');
INSERT INTO `wa_region` VALUES (42263, '凤县', 3, 42119, 'F');
INSERT INTO `wa_region` VALUES (42278, '太白县', 3, 42119, 'T');
INSERT INTO `wa_region` VALUES (42287, '咸阳市', 2, 41877, 'X');
INSERT INTO `wa_region` VALUES (42288, '市辖区', 3, 42287, 'S');
INSERT INTO `wa_region` VALUES (42289, '秦都区', 3, 42287, 'Q');
INSERT INTO `wa_region` VALUES (42302, '杨凌区', 3, 42287, 'Y');
INSERT INTO `wa_region` VALUES (42308, '渭城区', 3, 42287, 'W');
INSERT INTO `wa_region` VALUES (42319, '三原县', 3, 42287, 'S');
INSERT INTO `wa_region` VALUES (42334, '泾阳县', 3, 42287, 'J');
INSERT INTO `wa_region` VALUES (42351, '乾县', 3, 42287, 'Q');
INSERT INTO `wa_region` VALUES (42372, '礼泉县', 3, 42287, 'L');
INSERT INTO `wa_region` VALUES (42388, '永寿县', 3, 42287, 'Y');
INSERT INTO `wa_region` VALUES (42402, '彬县', 3, 42287, 'B');
INSERT INTO `wa_region` VALUES (42419, '长武县', 3, 42287, 'C');
INSERT INTO `wa_region` VALUES (42431, '旬邑县', 3, 42287, 'X');
INSERT INTO `wa_region` VALUES (42446, '淳化县', 3, 42287, 'C');
INSERT INTO `wa_region` VALUES (42462, '武功县', 3, 42287, 'W');
INSERT INTO `wa_region` VALUES (42475, '兴平市', 3, 42287, 'X');
INSERT INTO `wa_region` VALUES (42490, '渭南市', 2, 41877, 'W');
INSERT INTO `wa_region` VALUES (42491, '市辖区', 3, 42490, 'S');
INSERT INTO `wa_region` VALUES (42492, '临渭区', 3, 42490, 'L');
INSERT INTO `wa_region` VALUES (42523, '华县', 3, 42490, 'H');
INSERT INTO `wa_region` VALUES (42538, '潼关县', 3, 42490, 'T');
INSERT INTO `wa_region` VALUES (42547, '大荔县', 3, 42490, 'D');
INSERT INTO `wa_region` VALUES (42577, '合阳县', 3, 42490, 'H');
INSERT INTO `wa_region` VALUES (42594, '澄城县', 3, 42490, 'C');
INSERT INTO `wa_region` VALUES (42609, '蒲城县', 3, 42490, 'P');
INSERT INTO `wa_region` VALUES (42634, '白水县', 3, 42490, 'B');
INSERT INTO `wa_region` VALUES (42649, '富平县', 3, 42490, 'F');
INSERT INTO `wa_region` VALUES (42674, '韩城市', 3, 42490, 'H');
INSERT INTO `wa_region` VALUES (42691, '华阴市', 3, 42490, 'H');
INSERT INTO `wa_region` VALUES (42703, '延安市', 2, 41877, 'Y');
INSERT INTO `wa_region` VALUES (42704, '市辖区', 3, 42703, 'S');
INSERT INTO `wa_region` VALUES (42705, '宝塔区', 3, 42703, 'B');
INSERT INTO `wa_region` VALUES (42729, '延长县', 3, 42703, 'Y');
INSERT INTO `wa_region` VALUES (42742, '延川县', 3, 42703, 'Y');
INSERT INTO `wa_region` VALUES (42757, '子长县', 3, 42703, 'Z');
INSERT INTO `wa_region` VALUES (42771, '安塞县', 3, 42703, 'A');
INSERT INTO `wa_region` VALUES (42784, '志丹县', 3, 42703, 'Z');
INSERT INTO `wa_region` VALUES (42796, '吴起县', 3, 42703, 'W');
INSERT INTO `wa_region` VALUES (42809, '甘泉县', 3, 42703, 'G');
INSERT INTO `wa_region` VALUES (42818, '富县', 3, 42703, 'F');
INSERT INTO `wa_region` VALUES (42833, '洛川县', 3, 42703, 'L');
INSERT INTO `wa_region` VALUES (42850, '宜川县', 3, 42703, 'Y');
INSERT INTO `wa_region` VALUES (42863, '黄龙县', 3, 42703, 'H');
INSERT INTO `wa_region` VALUES (42874, '黄陵县', 3, 42703, 'H');
INSERT INTO `wa_region` VALUES (42888, '汉中市', 2, 41877, 'H');
INSERT INTO `wa_region` VALUES (42889, '市辖区', 3, 42888, 'S');
INSERT INTO `wa_region` VALUES (42890, '汉台区', 3, 42888, 'H');
INSERT INTO `wa_region` VALUES (42908, '南郑县', 3, 42888, 'N');
INSERT INTO `wa_region` VALUES (42939, '城固县', 3, 42888, 'C');
INSERT INTO `wa_region` VALUES (42965, '洋县', 3, 42888, 'Y');
INSERT INTO `wa_region` VALUES (42992, '西乡县', 3, 42888, 'X');
INSERT INTO `wa_region` VALUES (43016, '勉县', 3, 42888, 'M');
INSERT INTO `wa_region` VALUES (43042, '宁强县', 3, 42888, 'N');
INSERT INTO `wa_region` VALUES (43069, '略阳县', 3, 42888, 'L');
INSERT INTO `wa_region` VALUES (43091, '镇巴县', 3, 42888, 'Z');
INSERT INTO `wa_region` VALUES (43116, '留坝县', 3, 42888, 'L');
INSERT INTO `wa_region` VALUES (43126, '佛坪县', 3, 42888, 'F');
INSERT INTO `wa_region` VALUES (43136, '榆林市', 2, 41877, 'Y');
INSERT INTO `wa_region` VALUES (43137, '市辖区', 3, 43136, 'S');
INSERT INTO `wa_region` VALUES (43138, '榆阳区', 3, 43136, 'Y');
INSERT INTO `wa_region` VALUES (43170, '神木县', 3, 43136, 'S');
INSERT INTO `wa_region` VALUES (43190, '府谷县', 3, 43136, 'F');
INSERT INTO `wa_region` VALUES (43211, '横山县', 3, 43136, 'H');
INSERT INTO `wa_region` VALUES (43230, '靖边县', 3, 43136, 'J');
INSERT INTO `wa_region` VALUES (43253, '定边县', 3, 43136, 'D');
INSERT INTO `wa_region` VALUES (43279, '绥德县', 3, 43136, 'S');
INSERT INTO `wa_region` VALUES (43300, '米脂县', 3, 43136, 'M');
INSERT INTO `wa_region` VALUES (43314, '佳县', 3, 43136, 'J');
INSERT INTO `wa_region` VALUES (43335, '吴堡县', 3, 43136, 'W');
INSERT INTO `wa_region` VALUES (43344, '清涧县', 3, 43136, 'Q');
INSERT INTO `wa_region` VALUES (43360, '子洲县', 3, 43136, 'Z');
INSERT INTO `wa_region` VALUES (43379, '安康市', 2, 41877, 'A');
INSERT INTO `wa_region` VALUES (43380, '市辖区', 3, 43379, 'S');
INSERT INTO `wa_region` VALUES (43381, '汉滨区', 3, 43379, 'H');
INSERT INTO `wa_region` VALUES (43428, '汉阴县', 3, 43379, 'H');
INSERT INTO `wa_region` VALUES (43447, '石泉县', 3, 43379, 'S');
INSERT INTO `wa_region` VALUES (43463, '宁陕县', 3, 43379, 'N');
INSERT INTO `wa_region` VALUES (43478, '紫阳县', 3, 43379, 'Z');
INSERT INTO `wa_region` VALUES (43504, '岚皋县', 3, 43379, 'L');
INSERT INTO `wa_region` VALUES (43522, '平利县', 3, 43379, 'P');
INSERT INTO `wa_region` VALUES (43535, '镇坪县', 3, 43379, 'Z');
INSERT INTO `wa_region` VALUES (43546, '旬阳县', 3, 43379, 'X');
INSERT INTO `wa_region` VALUES (43575, '白河县', 3, 43379, 'B');
INSERT INTO `wa_region` VALUES (43592, '商洛市', 2, 41877, 'S');
INSERT INTO `wa_region` VALUES (43593, '市辖区', 3, 43592, 'S');
INSERT INTO `wa_region` VALUES (43594, '商州区', 3, 43592, 'S');
INSERT INTO `wa_region` VALUES (43628, '洛南县', 3, 43592, 'L');
INSERT INTO `wa_region` VALUES (43654, '丹凤县', 3, 43592, 'D');
INSERT INTO `wa_region` VALUES (43676, '商南县', 3, 43592, 'S');
INSERT INTO `wa_region` VALUES (43699, '山阳县', 3, 43592, 'S');
INSERT INTO `wa_region` VALUES (43730, '镇安县', 3, 43592, 'Z');
INSERT INTO `wa_region` VALUES (43759, '柞水县', 3, 43592, 'Z');
INSERT INTO `wa_region` VALUES (43776, '甘肃省', 1, 0, 'G');
INSERT INTO `wa_region` VALUES (43777, '兰州市', 2, 43776, 'L');
INSERT INTO `wa_region` VALUES (43778, '市辖区', 3, 43777, 'S');
INSERT INTO `wa_region` VALUES (43779, '城关区', 3, 43777, 'C');
INSERT INTO `wa_region` VALUES (43804, '七里河区', 3, 43777, 'Q');
INSERT INTO `wa_region` VALUES (43820, '兰州市西固区', 3, 43777, 'L');
INSERT INTO `wa_region` VALUES (43836, '安宁区', 3, 43777, 'A');
INSERT INTO `wa_region` VALUES (43845, '红古区', 3, 43777, 'H');
INSERT INTO `wa_region` VALUES (43853, '永登县', 3, 43777, 'Y');
INSERT INTO `wa_region` VALUES (43872, '皋兰县', 3, 43777, 'G');
INSERT INTO `wa_region` VALUES (43880, '榆中县', 3, 43777, 'Y');
INSERT INTO `wa_region` VALUES (43904, '嘉峪关市', 2, 43776, 'J');
INSERT INTO `wa_region` VALUES (43905, '市辖', 3, 43904, 'S');
INSERT INTO `wa_region` VALUES (43914, '金昌市', 2, 43776, 'J');
INSERT INTO `wa_region` VALUES (43915, '市辖区', 3, 43914, 'S');
INSERT INTO `wa_region` VALUES (43916, '金川区', 3, 43914, 'J');
INSERT INTO `wa_region` VALUES (43925, '永昌县', 3, 43914, 'Y');
INSERT INTO `wa_region` VALUES (43936, '白银市', 2, 43776, 'B');
INSERT INTO `wa_region` VALUES (43937, '市辖区', 3, 43936, 'S');
INSERT INTO `wa_region` VALUES (43938, '白银区', 3, 43936, 'B');
INSERT INTO `wa_region` VALUES (43949, '平川区', 3, 43936, 'P');
INSERT INTO `wa_region` VALUES (43961, '靖远县', 3, 43936, 'J');
INSERT INTO `wa_region` VALUES (43980, '会宁县', 3, 43936, 'H');
INSERT INTO `wa_region` VALUES (44009, '景泰县', 3, 43936, 'J');
INSERT INTO `wa_region` VALUES (44022, '天水市', 2, 43776, 'T');
INSERT INTO `wa_region` VALUES (44023, '市辖区', 3, 44022, 'S');
INSERT INTO `wa_region` VALUES (44024, '秦州区', 3, 44022, 'Q');
INSERT INTO `wa_region` VALUES (44048, '麦积区', 3, 44022, 'M');
INSERT INTO `wa_region` VALUES (44069, '清水县', 3, 44022, 'Q');
INSERT INTO `wa_region` VALUES (44088, '秦安县', 3, 44022, 'Q');
INSERT INTO `wa_region` VALUES (44106, '甘谷县', 3, 44022, 'G');
INSERT INTO `wa_region` VALUES (44122, '武山县', 3, 44022, 'W');
INSERT INTO `wa_region` VALUES (44138, '张家川县', 3, 44022, 'Z');
INSERT INTO `wa_region` VALUES (44154, '武威市', 2, 43776, 'W');
INSERT INTO `wa_region` VALUES (44155, '市辖区', 3, 44154, 'S');
INSERT INTO `wa_region` VALUES (44156, '凉州区', 3, 44154, 'L');
INSERT INTO `wa_region` VALUES (44202, '民勤县', 3, 44154, 'M');
INSERT INTO `wa_region` VALUES (44221, '古浪县', 3, 44154, 'G');
INSERT INTO `wa_region` VALUES (44242, '天祝县', 3, 44154, 'T');
INSERT INTO `wa_region` VALUES (44265, '张掖市', 2, 43776, 'Z');
INSERT INTO `wa_region` VALUES (44266, '市辖区', 3, 44265, 'S');
INSERT INTO `wa_region` VALUES (44267, '甘州区', 3, 44265, 'G');
INSERT INTO `wa_region` VALUES (44294, '肃南县', 3, 44265, 'S');
INSERT INTO `wa_region` VALUES (44305, '民乐县', 3, 44265, 'M');
INSERT INTO `wa_region` VALUES (44317, '临泽县', 3, 44265, 'L');
INSERT INTO `wa_region` VALUES (44331, '高台县', 3, 44265, 'G');
INSERT INTO `wa_region` VALUES (44341, '山丹县', 3, 44265, 'S');
INSERT INTO `wa_region` VALUES (44352, '平凉市', 2, 43776, 'P');
INSERT INTO `wa_region` VALUES (44353, '市辖区', 3, 44352, 'S');
INSERT INTO `wa_region` VALUES (44354, '崆峒区', 3, 44352, 'K');
INSERT INTO `wa_region` VALUES (44375, '泾川县', 3, 44352, 'J');
INSERT INTO `wa_region` VALUES (44392, '灵台县', 3, 44352, 'L');
INSERT INTO `wa_region` VALUES (44408, '崇信县', 3, 44352, 'C');
INSERT INTO `wa_region` VALUES (44418, '华亭县', 3, 44352, 'H');
INSERT INTO `wa_region` VALUES (44431, '庄浪县', 3, 44352, 'Z');
INSERT INTO `wa_region` VALUES (44451, '静宁县', 3, 44352, 'J');
INSERT INTO `wa_region` VALUES (44477, '酒泉市', 2, 43776, 'J');
INSERT INTO `wa_region` VALUES (44478, '市辖区', 3, 44477, 'S');
INSERT INTO `wa_region` VALUES (44479, '肃州区', 3, 44477, 'S');
INSERT INTO `wa_region` VALUES (44504, '金塔县', 3, 44477, 'J');
INSERT INTO `wa_region` VALUES (44516, '瓜州县', 3, 44477, 'G');
INSERT INTO `wa_region` VALUES (44531, '肃北县', 3, 44477, 'S');
INSERT INTO `wa_region` VALUES (44535, '阿克塞县', 3, 44477, 'A');
INSERT INTO `wa_region` VALUES (44539, '玉门市', 3, 44477, 'Y');
INSERT INTO `wa_region` VALUES (44557, '敦煌市', 3, 44477, 'D');
INSERT INTO `wa_region` VALUES (44569, '庆阳市', 2, 43776, 'Q');
INSERT INTO `wa_region` VALUES (44570, '市辖区', 3, 44569, 'S');
INSERT INTO `wa_region` VALUES (44571, '西峰区', 3, 44569, 'X');
INSERT INTO `wa_region` VALUES (44582, '庆城县', 3, 44569, 'Q');
INSERT INTO `wa_region` VALUES (44598, '环县', 3, 44569, 'H');
INSERT INTO `wa_region` VALUES (44620, '华池县', 3, 44569, 'H');
INSERT INTO `wa_region` VALUES (44636, '合水县', 3, 44569, 'H');
INSERT INTO `wa_region` VALUES (44649, '正宁县', 3, 44569, 'Z');
INSERT INTO `wa_region` VALUES (44660, '宁县', 3, 44569, 'N');
INSERT INTO `wa_region` VALUES (44679, '镇原县', 3, 44569, 'Z');
INSERT INTO `wa_region` VALUES (44699, '定西市', 2, 43776, 'D');
INSERT INTO `wa_region` VALUES (44700, '市辖区', 3, 44699, 'S');
INSERT INTO `wa_region` VALUES (44701, '安定区', 3, 44699, 'A');
INSERT INTO `wa_region` VALUES (44723, '通渭县', 3, 44699, 'T');
INSERT INTO `wa_region` VALUES (44742, '陇西县', 3, 44699, 'L');
INSERT INTO `wa_region` VALUES (44760, '渭源县', 3, 44699, 'W');
INSERT INTO `wa_region` VALUES (44777, '临洮县', 3, 44699, 'L');
INSERT INTO `wa_region` VALUES (44796, '漳县', 3, 44699, 'Z');
INSERT INTO `wa_region` VALUES (44810, '岷县', 3, 44699, 'M');
INSERT INTO `wa_region` VALUES (44829, '陇南市', 2, 43776, 'L');
INSERT INTO `wa_region` VALUES (44830, '市辖区', 3, 44829, 'S');
INSERT INTO `wa_region` VALUES (44831, '武都区', 3, 44829, 'W');
INSERT INTO `wa_region` VALUES (44868, '成县', 3, 44829, 'C');
INSERT INTO `wa_region` VALUES (44886, '文县', 3, 44829, 'W');
INSERT INTO `wa_region` VALUES (44907, '宕昌县', 3, 44829, 'D');
INSERT INTO `wa_region` VALUES (44933, '康县', 3, 44829, 'K');
INSERT INTO `wa_region` VALUES (44955, '西和县', 3, 44829, 'X');
INSERT INTO `wa_region` VALUES (44976, '礼县', 3, 44829, 'L');
INSERT INTO `wa_region` VALUES (45006, '徽县', 3, 44829, 'H');
INSERT INTO `wa_region` VALUES (45022, '两当县', 3, 44829, 'L');
INSERT INTO `wa_region` VALUES (45035, '临夏州', 2, 43776, 'L');
INSERT INTO `wa_region` VALUES (45036, '临夏市', 3, 45035, 'L');
INSERT INTO `wa_region` VALUES (45047, '临夏县', 3, 45035, 'L');
INSERT INTO `wa_region` VALUES (45073, '康乐县', 3, 45035, 'K');
INSERT INTO `wa_region` VALUES (45089, '永靖县', 3, 45035, 'Y');
INSERT INTO `wa_region` VALUES (45107, '广河县', 3, 45035, 'G');
INSERT INTO `wa_region` VALUES (45117, '和政县', 3, 45035, 'H');
INSERT INTO `wa_region` VALUES (45131, '东乡县', 3, 45035, 'D');
INSERT INTO `wa_region` VALUES (45156, '积石山县', 3, 45035, 'J');
INSERT INTO `wa_region` VALUES (45174, '甘南州', 2, 43776, 'G');
INSERT INTO `wa_region` VALUES (45175, '合作市', 3, 45174, 'H');
INSERT INTO `wa_region` VALUES (45186, '临潭县', 3, 45174, 'L');
INSERT INTO `wa_region` VALUES (45203, '卓尼县', 3, 45174, 'Z');
INSERT INTO `wa_region` VALUES (45219, '舟曲县', 3, 45174, 'Z');
INSERT INTO `wa_region` VALUES (45239, '迭部县', 3, 45174, 'D');
INSERT INTO `wa_region` VALUES (45251, '玛曲县', 3, 45174, 'M');
INSERT INTO `wa_region` VALUES (45263, '碌曲县', 3, 45174, 'L');
INSERT INTO `wa_region` VALUES (45272, '夏河县', 3, 45174, 'X');
INSERT INTO `wa_region` VALUES (45286, '青海省', 1, 0, 'Q');
INSERT INTO `wa_region` VALUES (45287, '西宁市', 2, 45286, 'X');
INSERT INTO `wa_region` VALUES (45288, '市辖区', 3, 45287, 'S');
INSERT INTO `wa_region` VALUES (45289, '城东区', 3, 45287, 'C');
INSERT INTO `wa_region` VALUES (45299, '城中区', 3, 45287, 'C');
INSERT INTO `wa_region` VALUES (45306, '城西区', 3, 45287, 'C');
INSERT INTO `wa_region` VALUES (45314, '城北区', 3, 45287, 'C');
INSERT INTO `wa_region` VALUES (45320, '大通县', 3, 45287, 'D');
INSERT INTO `wa_region` VALUES (45341, '湟中县', 3, 45287, 'H');
INSERT INTO `wa_region` VALUES (45358, '湟源县', 3, 45287, 'H');
INSERT INTO `wa_region` VALUES (45368, '海东地区', 2, 45286, 'H');
INSERT INTO `wa_region` VALUES (45369, '平安县', 3, 45368, 'P');
INSERT INTO `wa_region` VALUES (45378, '民和县', 3, 45368, 'M');
INSERT INTO `wa_region` VALUES (45401, '乐都县', 3, 45368, 'L');
INSERT INTO `wa_region` VALUES (45421, '互助县', 3, 45368, 'H');
INSERT INTO `wa_region` VALUES (45441, '化隆县', 3, 45368, 'H');
INSERT INTO `wa_region` VALUES (45461, '循化县', 3, 45368, 'X');
INSERT INTO `wa_region` VALUES (45471, '海北州', 2, 45286, 'H');
INSERT INTO `wa_region` VALUES (45472, '门源县', 3, 45471, 'M');
INSERT INTO `wa_region` VALUES (45487, '祁连县', 3, 45471, 'Q');
INSERT INTO `wa_region` VALUES (45495, '海晏县', 3, 45471, 'H');
INSERT INTO `wa_region` VALUES (45502, '刚察县', 3, 45471, 'G');
INSERT INTO `wa_region` VALUES (45510, '黄南州', 2, 45286, 'H');
INSERT INTO `wa_region` VALUES (45511, '同仁县', 3, 45510, 'T');
INSERT INTO `wa_region` VALUES (45523, '尖扎县', 3, 45510, 'J');
INSERT INTO `wa_region` VALUES (45533, '泽库县', 3, 45510, 'Z');
INSERT INTO `wa_region` VALUES (45542, '河南县', 3, 45510, 'H');
INSERT INTO `wa_region` VALUES (45548, '海南州', 2, 45286, 'H');
INSERT INTO `wa_region` VALUES (45549, '共和县', 3, 45548, 'G');
INSERT INTO `wa_region` VALUES (45566, '同德县', 3, 45548, 'T');
INSERT INTO `wa_region` VALUES (45573, '贵德县', 3, 45548, 'G');
INSERT INTO `wa_region` VALUES (45581, '兴海县', 3, 45548, 'X');
INSERT INTO `wa_region` VALUES (45589, '贵南县', 3, 45548, 'G');
INSERT INTO `wa_region` VALUES (45597, '果洛州', 2, 45286, 'G');
INSERT INTO `wa_region` VALUES (45598, '玛沁县', 3, 45597, 'M');
INSERT INTO `wa_region` VALUES (45607, '班玛县', 3, 45597, 'B');
INSERT INTO `wa_region` VALUES (45617, '甘德县', 3, 45597, 'G');
INSERT INTO `wa_region` VALUES (45625, '达日县', 3, 45597, 'D');
INSERT INTO `wa_region` VALUES (45636, '久治县', 3, 45597, 'J');
INSERT INTO `wa_region` VALUES (45643, '玛多县', 3, 45597, 'M');
INSERT INTO `wa_region` VALUES (45648, '玉树州', 2, 45286, 'Y');
INSERT INTO `wa_region` VALUES (45649, '玉树县', 3, 45648, 'Y');
INSERT INTO `wa_region` VALUES (45659, '杂多县', 3, 45648, 'Z');
INSERT INTO `wa_region` VALUES (45668, '称多县', 3, 45648, 'C');
INSERT INTO `wa_region` VALUES (45676, '治多县', 3, 45648, 'Z');
INSERT INTO `wa_region` VALUES (45683, '囊谦县', 3, 45648, 'N');
INSERT INTO `wa_region` VALUES (45694, '曲麻莱县', 3, 45648, 'Q');
INSERT INTO `wa_region` VALUES (45701, '海西州', 2, 45286, 'H');
INSERT INTO `wa_region` VALUES (45702, '格尔木市', 3, 45701, 'G');
INSERT INTO `wa_region` VALUES (45714, '德令哈市', 3, 45701, 'D');
INSERT INTO `wa_region` VALUES (45727, '乌兰县', 3, 45701, 'W');
INSERT INTO `wa_region` VALUES (45733, '都兰县', 3, 45701, 'D');
INSERT INTO `wa_region` VALUES (45742, '天峻县', 3, 45701, 'T');
INSERT INTO `wa_region` VALUES (45753, '宁夏回族自治区', 1, 0, 'N');
INSERT INTO `wa_region` VALUES (45754, '银川市', 2, 45753, 'Y');
INSERT INTO `wa_region` VALUES (45755, '市辖区', 3, 45754, 'S');
INSERT INTO `wa_region` VALUES (45756, '兴庆区', 3, 45754, 'X');
INSERT INTO `wa_region` VALUES (45772, '西夏区', 3, 45754, 'X');
INSERT INTO `wa_region` VALUES (45784, '金凤区', 3, 45754, 'J');
INSERT INTO `wa_region` VALUES (45794, '永宁县', 3, 45754, 'Y');
INSERT INTO `wa_region` VALUES (45803, '贺兰县', 3, 45754, 'H');
INSERT INTO `wa_region` VALUES (45813, '灵武市', 3, 45754, 'L');
INSERT INTO `wa_region` VALUES (45825, '石嘴山市', 2, 45753, 'S');
INSERT INTO `wa_region` VALUES (45826, '市辖区', 3, 45825, 'S');
INSERT INTO `wa_region` VALUES (45827, '大武口区', 3, 45825, 'D');
INSERT INTO `wa_region` VALUES (45839, '惠农区', 3, 45825, 'H');
INSERT INTO `wa_region` VALUES (45856, '平罗县', 3, 45825, 'P');
INSERT INTO `wa_region` VALUES (45871, '吴忠市', 2, 45753, 'W');
INSERT INTO `wa_region` VALUES (45872, '市辖区', 3, 45871, 'S');
INSERT INTO `wa_region` VALUES (45877, '利通区', 3, 45871, 'L');
INSERT INTO `wa_region` VALUES (45892, '盐池县', 3, 45871, 'Y');
INSERT INTO `wa_region` VALUES (45903, '同心县', 3, 45871, 'T');
INSERT INTO `wa_region` VALUES (45914, '青铜峡市', 3, 45871, 'Q');
INSERT INTO `wa_region` VALUES (45926, '固原市', 2, 45753, 'G');
INSERT INTO `wa_region` VALUES (45927, '市辖区', 3, 45926, 'S');
INSERT INTO `wa_region` VALUES (45928, '原州区', 3, 45926, 'Y');
INSERT INTO `wa_region` VALUES (45944, '西吉县', 3, 45926, 'X');
INSERT INTO `wa_region` VALUES (45964, '隆德县', 3, 45926, 'L');
INSERT INTO `wa_region` VALUES (45978, '泾源县', 3, 45926, 'J');
INSERT INTO `wa_region` VALUES (45986, '彭阳县', 3, 45926, 'P');
INSERT INTO `wa_region` VALUES (45999, '中卫市', 2, 45753, 'Z');
INSERT INTO `wa_region` VALUES (46000, '市辖区', 3, 45999, 'S');
INSERT INTO `wa_region` VALUES (46012, '沙坡头区', 3, 45999, 'S');
INSERT INTO `wa_region` VALUES (46013, '中宁县', 3, 45999, 'Z');
INSERT INTO `wa_region` VALUES (46026, '海原县', 3, 45999, 'H');
INSERT INTO `wa_region` VALUES (46047, '新疆维吾尔自治区', 1, 0, 'X');
INSERT INTO `wa_region` VALUES (46048, '乌鲁木齐市', 2, 46047, 'W');
INSERT INTO `wa_region` VALUES (46049, '市辖区', 3, 46048, 'S');
INSERT INTO `wa_region` VALUES (46050, '天山区', 3, 46048, 'T');
INSERT INTO `wa_region` VALUES (46065, '沙依巴克区', 3, 46048, 'S');
INSERT INTO `wa_region` VALUES (46079, '新市区', 3, 46048, 'X');
INSERT INTO `wa_region` VALUES (46095, '水磨沟区', 3, 46048, 'S');
INSERT INTO `wa_region` VALUES (46104, '头屯河区', 3, 46048, 'T');
INSERT INTO `wa_region` VALUES (46114, '达坂城区', 3, 46048, 'D');
INSERT INTO `wa_region` VALUES (46123, '东山区', 3, 46048, 'D');
INSERT INTO `wa_region` VALUES (46128, '乌鲁木齐县', 3, 46048, 'W');
INSERT INTO `wa_region` VALUES (46138, '克拉玛依市', 2, 46047, 'K');
INSERT INTO `wa_region` VALUES (46139, '市辖区', 3, 46138, 'S');
INSERT INTO `wa_region` VALUES (46140, '独山子区', 3, 46138, 'D');
INSERT INTO `wa_region` VALUES (46144, '克拉玛依区', 3, 46138, 'K');
INSERT INTO `wa_region` VALUES (46155, '白碱滩区', 3, 46138, 'B');
INSERT INTO `wa_region` VALUES (46158, '乌尔禾区', 3, 46138, 'W');
INSERT INTO `wa_region` VALUES (46162, '吐鲁番地区', 2, 46047, 'T');
INSERT INTO `wa_region` VALUES (46163, '吐鲁番市', 3, 46162, 'T');
INSERT INTO `wa_region` VALUES (46178, '鄯善县', 3, 46162, 'S');
INSERT INTO `wa_region` VALUES (46189, '托克逊县', 3, 46162, 'T');
INSERT INTO `wa_region` VALUES (46197, '哈密地区', 2, 46047, 'H');
INSERT INTO `wa_region` VALUES (46198, '哈密市', 3, 46197, 'H');
INSERT INTO `wa_region` VALUES (46230, '巴里坤县', 3, 46197, 'B');
INSERT INTO `wa_region` VALUES (46246, '伊吾县', 3, 46197, 'Y');
INSERT INTO `wa_region` VALUES (46255, '昌吉州', 2, 46047, 'C');
INSERT INTO `wa_region` VALUES (46256, '昌吉市', 3, 46255, 'C');
INSERT INTO `wa_region` VALUES (46275, '阜康市', 3, 46255, 'F');
INSERT INTO `wa_region` VALUES (46289, '米泉市', 3, 46255, 'M');
INSERT INTO `wa_region` VALUES (46299, '呼图壁县', 3, 46255, 'H');
INSERT INTO `wa_region` VALUES (46316, '玛纳斯', 3, 46255, 'M');
INSERT INTO `wa_region` VALUES (46337, '奇台县', 3, 46255, 'Q');
INSERT INTO `wa_region` VALUES (46355, '吉木萨尔县', 3, 46255, 'J');
INSERT INTO `wa_region` VALUES (46366, '木垒县', 3, 46255, 'M');
INSERT INTO `wa_region` VALUES (46380, '博州', 2, 46047, 'B');
INSERT INTO `wa_region` VALUES (46381, '博乐市', 3, 46380, 'B');
INSERT INTO `wa_region` VALUES (46399, '精河县', 3, 46380, 'J');
INSERT INTO `wa_region` VALUES (46410, '温泉县', 3, 46380, 'W');
INSERT INTO `wa_region` VALUES (46422, '巴州', 2, 46047, 'B');
INSERT INTO `wa_region` VALUES (46423, '库尔勒市', 3, 46422, 'K');
INSERT INTO `wa_region` VALUES (46451, '轮台县', 3, 46422, 'L');
INSERT INTO `wa_region` VALUES (46463, '尉犁县', 3, 46422, 'W');
INSERT INTO `wa_region` VALUES (46476, '若羌县', 3, 46422, 'R');
INSERT INTO `wa_region` VALUES (46486, '且末县', 3, 46422, 'Q');
INSERT INTO `wa_region` VALUES (46500, '焉耆县', 3, 46422, 'Y');
INSERT INTO `wa_region` VALUES (46512, '和静县', 3, 46422, 'H');
INSERT INTO `wa_region` VALUES (46531, '和硕县', 3, 46422, 'H');
INSERT INTO `wa_region` VALUES (46542, '博湖县', 3, 46422, 'B');
INSERT INTO `wa_region` VALUES (46551, '阿克苏地区', 2, 46047, 'A');
INSERT INTO `wa_region` VALUES (46552, '阿克苏市', 3, 46551, 'A');
INSERT INTO `wa_region` VALUES (46571, '温宿县', 3, 46551, 'W');
INSERT INTO `wa_region` VALUES (46592, '库车县', 3, 46551, 'K');
INSERT INTO `wa_region` VALUES (46617, '沙雅县', 3, 46551, 'S');
INSERT INTO `wa_region` VALUES (46630, '新和县', 3, 46551, 'X');
INSERT INTO `wa_region` VALUES (46640, '拜城县', 3, 46551, 'B');
INSERT INTO `wa_region` VALUES (46657, '乌什县', 3, 46551, 'W');
INSERT INTO `wa_region` VALUES (46668, '阿瓦提县', 3, 46551, 'A');
INSERT INTO `wa_region` VALUES (46682, '柯坪县', 3, 46551, 'K');
INSERT INTO `wa_region` VALUES (46688, '克州', 2, 46047, 'K');
INSERT INTO `wa_region` VALUES (46689, '阿图什市', 3, 46688, 'A');
INSERT INTO `wa_region` VALUES (46704, '阿克陶县', 3, 46688, 'A');
INSERT INTO `wa_region` VALUES (46723, '阿合奇县', 3, 46688, 'A');
INSERT INTO `wa_region` VALUES (46733, '乌恰县', 3, 46688, 'W');
INSERT INTO `wa_region` VALUES (46747, '喀什地区', 2, 46047, 'K');
INSERT INTO `wa_region` VALUES (46748, '喀什市', 3, 46747, 'K');
INSERT INTO `wa_region` VALUES (46761, '疏附县', 3, 46747, 'S');
INSERT INTO `wa_region` VALUES (46780, '疏勒县', 3, 46747, 'S');
INSERT INTO `wa_region` VALUES (46797, '英吉沙县', 3, 46747, 'Y');
INSERT INTO `wa_region` VALUES (46813, '泽普县', 3, 46747, 'Z');
INSERT INTO `wa_region` VALUES (46830, '莎车县', 3, 46747, 'S');
INSERT INTO `wa_region` VALUES (46863, '叶城县', 3, 46747, 'Y');
INSERT INTO `wa_region` VALUES (46885, '麦盖提县', 3, 46747, 'M');
INSERT INTO `wa_region` VALUES (46902, '岳普湖县', 3, 46747, 'Y');
INSERT INTO `wa_region` VALUES (46913, '伽师县', 3, 46747, 'Q');
INSERT INTO `wa_region` VALUES (46928, '巴楚县', 3, 46747, 'B');
INSERT INTO `wa_region` VALUES (46942, '塔什库尔干县', 3, 46747, 'T');
INSERT INTO `wa_region` VALUES (46957, '和田地区', 2, 46047, 'H');
INSERT INTO `wa_region` VALUES (46958, '和田市', 3, 46957, 'H');
INSERT INTO `wa_region` VALUES (46971, '和田县', 3, 46957, 'H');
INSERT INTO `wa_region` VALUES (46983, '墨玉县', 3, 46957, 'M');
INSERT INTO `wa_region` VALUES (47002, '皮山县', 3, 46957, 'P');
INSERT INTO `wa_region` VALUES (47020, '洛浦县', 3, 46957, 'L');
INSERT INTO `wa_region` VALUES (47032, '策勒县', 3, 46957, 'C');
INSERT INTO `wa_region` VALUES (47042, '于田县', 3, 46957, 'Y');
INSERT INTO `wa_region` VALUES (47061, '民丰县', 3, 46957, 'M');
INSERT INTO `wa_region` VALUES (47069, '伊犁州', 2, 46047, 'Y');
INSERT INTO `wa_region` VALUES (47070, '伊宁市', 3, 47069, 'Y');
INSERT INTO `wa_region` VALUES (47091, '奎屯市', 3, 47069, 'K');
INSERT INTO `wa_region` VALUES (47099, '伊宁县', 3, 47069, 'Y');
INSERT INTO `wa_region` VALUES (47121, '察布查尔县', 3, 47069, 'C');
INSERT INTO `wa_region` VALUES (47143, '霍城县', 3, 47069, 'H');
INSERT INTO `wa_region` VALUES (47164, '巩留县', 3, 47069, 'G');
INSERT INTO `wa_region` VALUES (47180, '新源县', 3, 47069, 'X');
INSERT INTO `wa_region` VALUES (47196, '昭苏县', 3, 47069, 'Z');
INSERT INTO `wa_region` VALUES (47214, '特克斯县', 3, 47069, 'T');
INSERT INTO `wa_region` VALUES (47226, '尼勒克县', 3, 47069, 'N');
INSERT INTO `wa_region` VALUES (47241, '塔城地区', 2, 46047, 'T');
INSERT INTO `wa_region` VALUES (47242, '塔城市', 3, 47241, 'T');
INSERT INTO `wa_region` VALUES (47258, '乌苏市', 3, 47241, 'W');
INSERT INTO `wa_region` VALUES (47291, '额敏县', 3, 47241, 'E');
INSERT INTO `wa_region` VALUES (47315, '沙湾县', 3, 47241, 'S');
INSERT INTO `wa_region` VALUES (47338, '托里县', 3, 47241, 'T');
INSERT INTO `wa_region` VALUES (47351, '裕民县', 3, 47241, 'Y');
INSERT INTO `wa_region` VALUES (47360, '和布县', 3, 47241, 'H');
INSERT INTO `wa_region` VALUES (47374, '阿勒泰地区', 2, 46047, 'A');
INSERT INTO `wa_region` VALUES (47375, '阿勒泰市', 3, 47374, 'A');
INSERT INTO `wa_region` VALUES (47393, '布尔津县', 3, 47374, 'B');
INSERT INTO `wa_region` VALUES (47401, '富蕴县', 3, 47374, 'F');
INSERT INTO `wa_region` VALUES (47411, '福海县', 3, 47374, 'F');
INSERT INTO `wa_region` VALUES (47424, '哈巴河县', 3, 47374, 'H');
INSERT INTO `wa_region` VALUES (47433, '青河县', 3, 47374, 'Q');
INSERT INTO `wa_region` VALUES (47441, '吉木乃县', 3, 47374, 'J');
INSERT INTO `wa_region` VALUES (47450, '省直辖行政单位', 2, 46047, 'S');
INSERT INTO `wa_region` VALUES (47451, '石河子市', 3, 47450, 'S');
INSERT INTO `wa_region` VALUES (47460, '阿拉尔市', 3, 47450, 'A');
INSERT INTO `wa_region` VALUES (47477, '图木舒克市', 3, 47450, 'T');
INSERT INTO `wa_region` VALUES (47486, '五家渠市', 3, 47450, 'W');
INSERT INTO `wa_region` VALUES (47493, '台湾省', 1, 0, 'T');
INSERT INTO `wa_region` VALUES (47494, '香港特别行政区', 1, 0, 'X');
INSERT INTO `wa_region` VALUES (47495, '澳门特别行政区', 1, 0, 'A');
INSERT INTO `wa_region` VALUES (47496, '龙华新区', 3, 28558, 'L');
INSERT INTO `wa_region` VALUES (47497, '光明新区', 3, 28558, 'G');
INSERT INTO `wa_region` VALUES (47500, '新界', 2, 47494, 'X');
INSERT INTO `wa_region` VALUES (47501, '观塘区', 3, 47498, 'G');
INSERT INTO `wa_region` VALUES (47502, '黄大仙区', 3, 47498, 'H');
INSERT INTO `wa_region` VALUES (47503, '九龙城区', 3, 47498, 'J');
INSERT INTO `wa_region` VALUES (47504, '深水埗区', 3, 47498, 'S');
INSERT INTO `wa_region` VALUES (47505, '油尖旺区', 3, 47498, 'Y');
INSERT INTO `wa_region` VALUES (47506, '东区', 3, 47499, 'D');
INSERT INTO `wa_region` VALUES (47507, '南区', 3, 47499, 'N');
INSERT INTO `wa_region` VALUES (47508, '湾仔', 3, 47499, 'W');
INSERT INTO `wa_region` VALUES (47509, '中西区', 3, 47499, 'Z');
INSERT INTO `wa_region` VALUES (47510, '北区', 3, 47500, 'B');
INSERT INTO `wa_region` VALUES (47511, '大埔区', 3, 47500, 'D');
INSERT INTO `wa_region` VALUES (47512, '葵青区', 3, 47500, 'K');
INSERT INTO `wa_region` VALUES (47513, '离岛区', 3, 47500, 'L');
INSERT INTO `wa_region` VALUES (47514, '荃湾区', 3, 47500, 'Q');
INSERT INTO `wa_region` VALUES (47515, '沙田区', 3, 47500, 'S');
INSERT INTO `wa_region` VALUES (47516, '屯门区', 3, 47500, 'T');
INSERT INTO `wa_region` VALUES (47517, '西贡区', 3, 47500, 'X');
INSERT INTO `wa_region` VALUES (47518, '元朗区', 3, 47500, 'Y');
INSERT INTO `wa_region` VALUES (47519, '澳门半岛', 2, 47495, 'A');
INSERT INTO `wa_region` VALUES (47520, '离岛', 2, 47495, 'L');
INSERT INTO `wa_region` VALUES (47521, '大堂区', 3, 47519, 'D');
INSERT INTO `wa_region` VALUES (47522, '风顺堂区', 3, 47519, 'F');
INSERT INTO `wa_region` VALUES (47523, '花地玛堂区', 3, 47519, 'H');
INSERT INTO `wa_region` VALUES (47524, '花王堂区', 3, 47519, 'H');
INSERT INTO `wa_region` VALUES (47525, '望德堂区', 3, 47519, 'W');
INSERT INTO `wa_region` VALUES (47526, '嘉模堂区', 3, 47520, 'J');
INSERT INTO `wa_region` VALUES (47527, '路氹填海区', 3, 47520, 'L');
INSERT INTO `wa_region` VALUES (47528, '圣方济各堂区', 3, 47520, 'S');
INSERT INTO `wa_region` VALUES (47529, '高雄市', 2, 47493, 'G');
INSERT INTO `wa_region` VALUES (47530, '花莲县', 2, 47493, 'H');
INSERT INTO `wa_region` VALUES (47531, '基隆市', 2, 47493, 'J');
INSERT INTO `wa_region` VALUES (47532, '嘉义市', 2, 47493, 'J');
INSERT INTO `wa_region` VALUES (47533, '嘉义县', 2, 47493, 'J');
INSERT INTO `wa_region` VALUES (47534, '金门县', 2, 47493, 'J');
INSERT INTO `wa_region` VALUES (47535, '连江县', 2, 47493, 'L');
INSERT INTO `wa_region` VALUES (47536, '苗栗县', 2, 47493, 'M');
INSERT INTO `wa_region` VALUES (47537, '南投县', 2, 47493, 'N');
INSERT INTO `wa_region` VALUES (47538, '澎湖县', 2, 47493, 'P');
INSERT INTO `wa_region` VALUES (47539, '屏东县', 2, 47493, 'P');
INSERT INTO `wa_region` VALUES (47540, '台北市', 2, 47493, 'T');
INSERT INTO `wa_region` VALUES (47541, '台东县', 2, 47493, 'T');
INSERT INTO `wa_region` VALUES (47542, '台南市', 2, 47493, 'T');
INSERT INTO `wa_region` VALUES (47543, '台中市', 2, 47493, 'T');
INSERT INTO `wa_region` VALUES (47544, '桃园市', 2, 47493, 'T');
INSERT INTO `wa_region` VALUES (47545, '新北市', 2, 47493, 'X');
INSERT INTO `wa_region` VALUES (47546, '新竹市', 2, 47493, 'X');
INSERT INTO `wa_region` VALUES (47547, '新竹县', 2, 47493, 'X');
INSERT INTO `wa_region` VALUES (47548, '宜兰县', 2, 47493, 'Y');
INSERT INTO `wa_region` VALUES (47549, '云林县', 2, 47493, 'Y');
INSERT INTO `wa_region` VALUES (47550, '彰化县', 2, 47493, 'Z');
INSERT INTO `wa_region` VALUES (47551, '阿莲区', 3, 47529, 'A');
INSERT INTO `wa_region` VALUES (47552, '大寮区', 3, 47529, 'D');
INSERT INTO `wa_region` VALUES (47553, '大社区', 3, 47529, 'D');
INSERT INTO `wa_region` VALUES (47554, '大树区', 3, 47529, 'D');
INSERT INTO `wa_region` VALUES (47555, '凤山区', 3, 47529, 'F');
INSERT INTO `wa_region` VALUES (47556, '冈山区', 3, 47529, 'G');
INSERT INTO `wa_region` VALUES (47557, '鼓山区', 3, 47529, 'G');
INSERT INTO `wa_region` VALUES (47558, '湖内区', 3, 47529, 'H');
INSERT INTO `wa_region` VALUES (47559, '甲仙区', 3, 47529, 'J');
INSERT INTO `wa_region` VALUES (47560, '林园区', 3, 47529, 'L');
INSERT INTO `wa_region` VALUES (47561, '苓雅区', 3, 47529, 'L');
INSERT INTO `wa_region` VALUES (47562, '六龟区', 3, 47529, 'L');
INSERT INTO `wa_region` VALUES (47563, '路竹区', 3, 47529, 'L');
INSERT INTO `wa_region` VALUES (47564, '茂林区', 3, 47529, 'M');
INSERT INTO `wa_region` VALUES (47565, '美浓区', 3, 47529, 'M');
INSERT INTO `wa_region` VALUES (47566, '弥陀区', 3, 47529, 'M');
INSERT INTO `wa_region` VALUES (47567, '楠梓区', 3, 47529, 'N');
INSERT INTO `wa_region` VALUES (47568, '那玛夏区', 3, 47529, 'N');
INSERT INTO `wa_region` VALUES (47569, '内门区', 3, 47529, 'N');
INSERT INTO `wa_region` VALUES (47570, '鸟松区', 3, 47529, 'N');
INSERT INTO `wa_region` VALUES (47571, '旗津区', 3, 47529, 'Q');
INSERT INTO `wa_region` VALUES (47572, '旗门区', 3, 47529, 'Q');
INSERT INTO `wa_region` VALUES (47573, '其它区', 3, 47529, 'Q');
INSERT INTO `wa_region` VALUES (47574, '前金区', 3, 47529, 'Q');
INSERT INTO `wa_region` VALUES (47575, '前镇区', 3, 47529, 'Q');
INSERT INTO `wa_region` VALUES (47576, '桥头区', 3, 47529, 'Q');
INSERT INTO `wa_region` VALUES (47577, '茄萣区', 3, 47529, 'Q');
INSERT INTO `wa_region` VALUES (47578, '芩雅区', 3, 47529, 'Q');
INSERT INTO `wa_region` VALUES (47579, '仁武区', 3, 47529, 'R');
INSERT INTO `wa_region` VALUES (47580, '三民区', 3, 47529, 'S');
INSERT INTO `wa_region` VALUES (47581, '杉林区', 3, 47529, 'S');
INSERT INTO `wa_region` VALUES (47582, '桃源区', 3, 47529, 'T');
INSERT INTO `wa_region` VALUES (47583, '田寮区', 3, 47529, 'T');
INSERT INTO `wa_region` VALUES (47584, '小港区', 3, 47529, 'X');
INSERT INTO `wa_region` VALUES (47585, '新兴区', 3, 47529, 'X');
INSERT INTO `wa_region` VALUES (47586, '燕巢区', 3, 47529, 'Y');
INSERT INTO `wa_region` VALUES (47587, '盐埕区', 3, 47529, 'Y');
INSERT INTO `wa_region` VALUES (47588, '永安区', 3, 47529, 'Y');
INSERT INTO `wa_region` VALUES (47589, '梓官区', 3, 47529, 'Z');
INSERT INTO `wa_region` VALUES (47590, '左营区', 3, 47529, 'Z');
INSERT INTO `wa_region` VALUES (47591, '丰滨乡', 3, 47530, 'F');
INSERT INTO `wa_region` VALUES (47592, '凤林镇', 3, 47530, 'F');
INSERT INTO `wa_region` VALUES (47593, '富里乡', 3, 47530, 'F');
INSERT INTO `wa_region` VALUES (47594, '光复乡', 3, 47530, 'G');
INSERT INTO `wa_region` VALUES (47595, '花莲市', 3, 47530, 'H');
INSERT INTO `wa_region` VALUES (47596, '吉安乡', 3, 47530, 'J');
INSERT INTO `wa_region` VALUES (47597, '瑞穗乡', 3, 47530, 'R');
INSERT INTO `wa_region` VALUES (47598, '寿丰乡', 3, 47530, 'S');
INSERT INTO `wa_region` VALUES (47599, '太鲁阁', 3, 47530, 'T');
INSERT INTO `wa_region` VALUES (47600, '万荣乡', 3, 47530, 'W');
INSERT INTO `wa_region` VALUES (47601, '新城乡', 3, 47530, 'X');
INSERT INTO `wa_region` VALUES (47602, '秀林乡', 3, 47530, 'X');
INSERT INTO `wa_region` VALUES (47603, '玉里镇', 3, 47530, 'Y');
INSERT INTO `wa_region` VALUES (47604, '卓溪乡', 3, 47530, 'Z');
INSERT INTO `wa_region` VALUES (47605, '安乐区', 3, 47531, 'A');
INSERT INTO `wa_region` VALUES (47606, '暖暖区', 3, 47531, 'N');
INSERT INTO `wa_region` VALUES (47607, '七堵区', 3, 47531, 'Q');
INSERT INTO `wa_region` VALUES (47608, '其它区', 3, 47531, 'Q');
INSERT INTO `wa_region` VALUES (47609, '仁爱区', 3, 47531, 'R');
INSERT INTO `wa_region` VALUES (47610, '信义区', 3, 47531, 'X');
INSERT INTO `wa_region` VALUES (47611, '中山区', 3, 47531, 'Z');
INSERT INTO `wa_region` VALUES (47612, '中正区', 3, 47531, 'Z');
INSERT INTO `wa_region` VALUES (47613, '东区', 3, 47532, 'D');
INSERT INTO `wa_region` VALUES (47614, '西区', 3, 47532, 'X');
INSERT INTO `wa_region` VALUES (47615, '其它区', 3, 47532, 'Q');
INSERT INTO `wa_region` VALUES (47616, '阿里山乡', 3, 47533, 'A');
INSERT INTO `wa_region` VALUES (47617, '布袋镇', 3, 47533, 'B');
INSERT INTO `wa_region` VALUES (47618, '大林镇', 3, 47533, 'D');
INSERT INTO `wa_region` VALUES (47619, '大埔乡', 3, 47533, 'D');
INSERT INTO `wa_region` VALUES (47620, '东石乡', 3, 47533, 'D');
INSERT INTO `wa_region` VALUES (47621, '番路乡', 3, 47533, 'F');
INSERT INTO `wa_region` VALUES (47622, '六脚乡', 3, 47533, 'L');
INSERT INTO `wa_region` VALUES (47623, '鹿草乡', 3, 47533, 'L');
INSERT INTO `wa_region` VALUES (47624, '梅山乡', 3, 47533, 'M');
INSERT INTO `wa_region` VALUES (47625, '民雄乡', 3, 47533, 'M');
INSERT INTO `wa_region` VALUES (47626, '朴子市', 3, 47533, 'P');
INSERT INTO `wa_region` VALUES (47627, '水上乡', 3, 47533, 'S');
INSERT INTO `wa_region` VALUES (47628, '太保市', 3, 47533, 'T');
INSERT INTO `wa_region` VALUES (47629, '溪口乡', 3, 47533, 'X');
INSERT INTO `wa_region` VALUES (47630, '新港乡', 3, 47533, 'X');
INSERT INTO `wa_region` VALUES (47631, '义竹乡', 3, 47533, 'Y');
INSERT INTO `wa_region` VALUES (47632, '中埔乡', 3, 47533, 'Z');
INSERT INTO `wa_region` VALUES (47633, '竹崎乡', 3, 47533, 'Z');
INSERT INTO `wa_region` VALUES (47634, '金城镇', 3, 47534, 'J');
INSERT INTO `wa_region` VALUES (47635, '金湖镇', 3, 47534, 'J');
INSERT INTO `wa_region` VALUES (47636, '金宁乡', 3, 47534, 'J');
INSERT INTO `wa_region` VALUES (47637, '金沙镇', 3, 47534, 'J');
INSERT INTO `wa_region` VALUES (47638, '烈屿乡', 3, 47534, 'L');
INSERT INTO `wa_region` VALUES (47639, '乌邱乡', 3, 47534, 'W');
INSERT INTO `wa_region` VALUES (47640, '北竿乡', 3, 47535, 'B');
INSERT INTO `wa_region` VALUES (47641, '东引乡', 3, 47535, 'D');
INSERT INTO `wa_region` VALUES (47642, '莒光乡', 3, 47535, 'J');
INSERT INTO `wa_region` VALUES (47643, '南竿乡', 3, 47535, 'N');
INSERT INTO `wa_region` VALUES (47644, '大湖乡', 3, 47536, 'D');
INSERT INTO `wa_region` VALUES (47645, '公馆乡', 3, 47536, 'G');
INSERT INTO `wa_region` VALUES (47646, '后龙镇', 3, 47536, 'H');
INSERT INTO `wa_region` VALUES (47647, '苗栗市', 3, 47536, 'M');
INSERT INTO `wa_region` VALUES (47648, '南庄乡', 3, 47536, 'N');
INSERT INTO `wa_region` VALUES (47649, '三湾乡', 3, 47536, 'S');
INSERT INTO `wa_region` VALUES (47650, '三义乡', 3, 47536, 'S');
INSERT INTO `wa_region` VALUES (47651, '狮潭乡', 3, 47536, 'S');
INSERT INTO `wa_region` VALUES (47652, '泰安乡', 3, 47536, 'T');
INSERT INTO `wa_region` VALUES (47653, '铜锣乡', 3, 47536, 'T');
INSERT INTO `wa_region` VALUES (47654, '通宵镇', 3, 47536, 'T');
INSERT INTO `wa_region` VALUES (47655, '头份镇', 3, 47536, 'T');
INSERT INTO `wa_region` VALUES (47656, '头屋乡', 3, 47536, 'T');
INSERT INTO `wa_region` VALUES (47657, '西湖乡', 3, 47536, 'X');
INSERT INTO `wa_region` VALUES (47658, '苑里镇', 3, 47536, 'Y');
INSERT INTO `wa_region` VALUES (47659, '造桥乡', 3, 47536, 'Z');
INSERT INTO `wa_region` VALUES (47660, '竹南镇', 3, 47536, 'Z');
INSERT INTO `wa_region` VALUES (47661, '卓兰镇', 3, 47536, 'Z');
INSERT INTO `wa_region` VALUES (47662, '草屯镇', 3, 47537, 'C');
INSERT INTO `wa_region` VALUES (47663, '国姓乡', 3, 47537, 'G');
INSERT INTO `wa_region` VALUES (47664, '集集镇', 3, 47537, 'J');
INSERT INTO `wa_region` VALUES (47665, '鹿谷乡', 3, 47537, 'L');
INSERT INTO `wa_region` VALUES (47666, '名间乡', 3, 47537, 'M');
INSERT INTO `wa_region` VALUES (47667, '南投市', 3, 47537, 'N');
INSERT INTO `wa_region` VALUES (47668, '埔里镇', 3, 47537, 'P');
INSERT INTO `wa_region` VALUES (47669, '仁爱乡', 3, 47537, 'R');
INSERT INTO `wa_region` VALUES (47670, '水里乡', 3, 47537, 'S');
INSERT INTO `wa_region` VALUES (47671, '信义乡', 3, 47537, 'X');
INSERT INTO `wa_region` VALUES (47672, '鱼池乡', 3, 47537, 'Y');
INSERT INTO `wa_region` VALUES (47673, '中寮乡', 3, 47537, 'Z');
INSERT INTO `wa_region` VALUES (47674, '竹山镇', 3, 47537, 'Z');
INSERT INTO `wa_region` VALUES (47675, '白沙乡', 3, 47538, 'B');
INSERT INTO `wa_region` VALUES (47676, '湖西乡', 3, 47538, 'H');
INSERT INTO `wa_region` VALUES (47677, '马公市', 3, 47538, 'M');
INSERT INTO `wa_region` VALUES (47678, '七美乡', 3, 47538, 'Q');
INSERT INTO `wa_region` VALUES (47679, '望安乡', 3, 47538, 'W');
INSERT INTO `wa_region` VALUES (47680, '西屿乡', 3, 47538, 'X');
INSERT INTO `wa_region` VALUES (47681, '长治乡', 3, 47539, 'C');
INSERT INTO `wa_region` VALUES (47682, '潮州镇', 3, 47539, 'C');
INSERT INTO `wa_region` VALUES (47683, '车城乡', 3, 47539, 'C');
INSERT INTO `wa_region` VALUES (47684, '春日乡', 3, 47539, 'C');
INSERT INTO `wa_region` VALUES (47685, '东港镇', 3, 47539, 'D');
INSERT INTO `wa_region` VALUES (47686, '枋寮乡', 3, 47539, 'F');
INSERT INTO `wa_region` VALUES (47687, '枋山乡', 3, 47539, 'F');
INSERT INTO `wa_region` VALUES (47688, '高树乡', 3, 47539, 'G');
INSERT INTO `wa_region` VALUES (47689, '恒春镇', 3, 47539, 'H');
INSERT INTO `wa_region` VALUES (47690, '佳冬乡', 3, 47539, 'J');
INSERT INTO `wa_region` VALUES (47691, '九如乡', 3, 47539, 'J');
INSERT INTO `wa_region` VALUES (47692, '崁顶乡', 3, 47539, 'K');
INSERT INTO `wa_region` VALUES (47693, '来义乡', 3, 47539, 'L');
INSERT INTO `wa_region` VALUES (47694, '里港乡', 3, 47539, 'L');
INSERT INTO `wa_region` VALUES (47695, '林边乡', 3, 47539, 'L');
INSERT INTO `wa_region` VALUES (47696, '麟洛乡', 3, 47539, 'L');
INSERT INTO `wa_region` VALUES (47697, '琉球乡', 3, 47539, 'L');
INSERT INTO `wa_region` VALUES (47698, '玛家乡', 3, 47539, 'M');
INSERT INTO `wa_region` VALUES (47699, '满州乡', 3, 47539, 'M');
INSERT INTO `wa_region` VALUES (47700, '牡丹乡', 3, 47539, 'M');
INSERT INTO `wa_region` VALUES (47701, '南州乡', 3, 47539, 'N');
INSERT INTO `wa_region` VALUES (47702, '内埔乡', 3, 47539, 'N');
INSERT INTO `wa_region` VALUES (47703, '屏东市', 3, 47539, 'P');
INSERT INTO `wa_region` VALUES (47704, '三地门乡', 3, 47539, 'S');
INSERT INTO `wa_region` VALUES (47705, '狮子乡', 3, 47539, 'S');
INSERT INTO `wa_region` VALUES (47706, '泰武乡', 3, 47539, 'T');
INSERT INTO `wa_region` VALUES (47707, '万丹乡', 3, 47539, 'W');
INSERT INTO `wa_region` VALUES (47708, '万峦乡', 3, 47539, 'W');
INSERT INTO `wa_region` VALUES (47709, '雾台乡', 3, 47539, 'W');
INSERT INTO `wa_region` VALUES (47710, '新埤乡', 3, 47539, 'X');
INSERT INTO `wa_region` VALUES (47711, '新园乡', 3, 47539, 'X');
INSERT INTO `wa_region` VALUES (47712, '盐埔乡', 3, 47539, 'Y');
INSERT INTO `wa_region` VALUES (47713, '竹田乡', 3, 47539, 'Z');
INSERT INTO `wa_region` VALUES (47714, '北投区', 3, 47540, 'B');
INSERT INTO `wa_region` VALUES (47715, '大安区', 3, 47540, 'D');
INSERT INTO `wa_region` VALUES (47716, '大同区', 3, 47540, 'D');
INSERT INTO `wa_region` VALUES (47717, '南港区', 3, 47540, 'N');
INSERT INTO `wa_region` VALUES (47718, '内湖区', 3, 47540, 'N');
INSERT INTO `wa_region` VALUES (47719, '士林区', 3, 47540, 'S');
INSERT INTO `wa_region` VALUES (47720, '松山区', 3, 47540, 'S');
INSERT INTO `wa_region` VALUES (47721, '万华区', 3, 47540, 'W');
INSERT INTO `wa_region` VALUES (47722, '文山区', 3, 47540, 'W');
INSERT INTO `wa_region` VALUES (47723, '信义区', 3, 47540, 'X');
INSERT INTO `wa_region` VALUES (47724, '中山区', 3, 47540, 'Z');
INSERT INTO `wa_region` VALUES (47725, '中正区', 3, 47540, 'Z');
INSERT INTO `wa_region` VALUES (47726, '其它区', 3, 47540, 'Q');
INSERT INTO `wa_region` VALUES (47727, '卑南乡', 3, 47541, 'B');
INSERT INTO `wa_region` VALUES (47728, '长滨乡', 3, 47541, 'C');
INSERT INTO `wa_region` VALUES (47729, '成功镇', 3, 47541, 'C');
INSERT INTO `wa_region` VALUES (47730, '池上乡', 3, 47541, 'C');
INSERT INTO `wa_region` VALUES (47731, '达仁乡', 3, 47541, 'D');
INSERT INTO `wa_region` VALUES (47732, '大武乡', 3, 47541, 'D');
INSERT INTO `wa_region` VALUES (47733, '东河乡', 3, 47541, 'D');
INSERT INTO `wa_region` VALUES (47734, '关山镇', 3, 47541, 'G');
INSERT INTO `wa_region` VALUES (47735, '海端乡', 3, 47541, 'H');
INSERT INTO `wa_region` VALUES (47736, '金峰乡', 3, 47541, 'J');
INSERT INTO `wa_region` VALUES (47737, '兰屿乡', 3, 47541, 'L');
INSERT INTO `wa_region` VALUES (47738, '鹿野乡', 3, 47541, 'L');
INSERT INTO `wa_region` VALUES (47739, '绿岛乡', 3, 47541, 'L');
INSERT INTO `wa_region` VALUES (47740, '台东市', 3, 47541, 'T');
INSERT INTO `wa_region` VALUES (47741, '太麻里乡', 3, 47541, 'T');
INSERT INTO `wa_region` VALUES (47742, '延平乡', 3, 47541, 'Y');
INSERT INTO `wa_region` VALUES (47743, '中西区', 3, 47542, 'Z');
INSERT INTO `wa_region` VALUES (47744, '东区', 3, 47542, 'D');
INSERT INTO `wa_region` VALUES (47745, '南区', 3, 47542, 'N');
INSERT INTO `wa_region` VALUES (47746, '北区', 3, 47542, 'B');
INSERT INTO `wa_region` VALUES (47747, '安平区', 3, 47542, 'A');
INSERT INTO `wa_region` VALUES (47748, '安南区', 3, 47542, 'A');
INSERT INTO `wa_region` VALUES (47749, '其它区', 3, 47542, 'Q');
INSERT INTO `wa_region` VALUES (47750, '永康区', 3, 47542, 'Y');
INSERT INTO `wa_region` VALUES (47751, '归仁区', 3, 47542, 'G');
INSERT INTO `wa_region` VALUES (47752, '新化区', 3, 47542, 'X');
INSERT INTO `wa_region` VALUES (47753, '左镇区', 3, 47542, 'Z');
INSERT INTO `wa_region` VALUES (47754, '玉井区', 3, 47542, 'Y');
INSERT INTO `wa_region` VALUES (47755, '楠西区', 3, 47542, 'N');
INSERT INTO `wa_region` VALUES (47756, '南化区', 3, 47542, 'N');
INSERT INTO `wa_region` VALUES (47757, '仁德区', 3, 47542, 'R');
INSERT INTO `wa_region` VALUES (47758, '关庙区', 3, 47542, 'G');
INSERT INTO `wa_region` VALUES (47759, '龙崎区', 3, 47542, 'L');
INSERT INTO `wa_region` VALUES (47760, '官田区', 3, 47542, 'G');
INSERT INTO `wa_region` VALUES (47761, '麻豆区', 3, 47542, 'M');
INSERT INTO `wa_region` VALUES (47762, '佳里区', 3, 47542, 'J');
INSERT INTO `wa_region` VALUES (47763, '西港区', 3, 47542, 'X');
INSERT INTO `wa_region` VALUES (47764, '七股区', 3, 47542, 'Q');
INSERT INTO `wa_region` VALUES (47765, '将军区', 3, 47542, 'J');
INSERT INTO `wa_region` VALUES (47766, '学甲区', 3, 47542, 'X');
INSERT INTO `wa_region` VALUES (47767, '北门区', 3, 47542, 'B');
INSERT INTO `wa_region` VALUES (47768, '新营区', 3, 47542, 'X');
INSERT INTO `wa_region` VALUES (47769, '后壁区', 3, 47542, 'H');
INSERT INTO `wa_region` VALUES (47770, '白河区', 3, 47542, 'B');
INSERT INTO `wa_region` VALUES (47771, '东山区', 3, 47542, 'D');
INSERT INTO `wa_region` VALUES (47772, '六甲区', 3, 47542, 'L');
INSERT INTO `wa_region` VALUES (47773, '下营区', 3, 47542, 'X');
INSERT INTO `wa_region` VALUES (47774, '柳营区', 3, 47542, 'L');
INSERT INTO `wa_region` VALUES (47775, '盐水区', 3, 47542, 'Y');
INSERT INTO `wa_region` VALUES (47776, '善化区', 3, 47542, 'S');
INSERT INTO `wa_region` VALUES (47777, '大内区', 3, 47542, 'D');
INSERT INTO `wa_region` VALUES (47778, '山上区', 3, 47542, 'S');
INSERT INTO `wa_region` VALUES (47779, '新市区', 3, 47542, 'X');
INSERT INTO `wa_region` VALUES (47780, '安定区', 3, 47542, 'A');
INSERT INTO `wa_region` VALUES (47781, '中区', 3, 47543, 'Z');
INSERT INTO `wa_region` VALUES (47782, '东区', 3, 47543, 'D');
INSERT INTO `wa_region` VALUES (47783, '南区', 3, 47543, 'N');
INSERT INTO `wa_region` VALUES (47784, '西区', 3, 47543, 'X');
INSERT INTO `wa_region` VALUES (47785, '北区', 3, 47543, 'B');
INSERT INTO `wa_region` VALUES (47786, '北屯区', 3, 47543, 'B');
INSERT INTO `wa_region` VALUES (47787, '西屯区', 3, 47543, 'X');
INSERT INTO `wa_region` VALUES (47788, '南屯区', 3, 47543, 'N');
INSERT INTO `wa_region` VALUES (47789, '其它区', 3, 47543, 'Q');
INSERT INTO `wa_region` VALUES (47790, '太平区', 3, 47543, 'T');
INSERT INTO `wa_region` VALUES (47791, '大里区', 3, 47543, 'D');
INSERT INTO `wa_region` VALUES (47792, '雾峰区', 3, 47543, 'W');
INSERT INTO `wa_region` VALUES (47793, '乌日区', 3, 47543, 'W');
INSERT INTO `wa_region` VALUES (47794, '丰原区', 3, 47543, 'F');
INSERT INTO `wa_region` VALUES (47795, '后里区', 3, 47543, 'H');
INSERT INTO `wa_region` VALUES (47796, '石冈区', 3, 47543, 'S');
INSERT INTO `wa_region` VALUES (47797, '东势区', 3, 47543, 'D');
INSERT INTO `wa_region` VALUES (47798, '和平区', 3, 47543, 'H');
INSERT INTO `wa_region` VALUES (47799, '新社区', 3, 47543, 'X');
INSERT INTO `wa_region` VALUES (47800, '潭子区', 3, 47543, 'T');
INSERT INTO `wa_region` VALUES (47801, '大雅区', 3, 47543, 'D');
INSERT INTO `wa_region` VALUES (47802, '神冈区', 3, 47543, 'S');
INSERT INTO `wa_region` VALUES (47803, '大肚区', 3, 47543, 'D');
INSERT INTO `wa_region` VALUES (47804, '沙鹿区', 3, 47543, 'S');
INSERT INTO `wa_region` VALUES (47805, '龙井区', 3, 47543, 'L');
INSERT INTO `wa_region` VALUES (47806, '梧栖区', 3, 47543, 'W');
INSERT INTO `wa_region` VALUES (47807, '清水区', 3, 47543, 'Q');
INSERT INTO `wa_region` VALUES (47808, '大甲区', 3, 47543, 'D');
INSERT INTO `wa_region` VALUES (47809, '外埔区', 3, 47543, 'W');
INSERT INTO `wa_region` VALUES (47810, '大安区', 3, 47543, 'D');
INSERT INTO `wa_region` VALUES (47811, '中坜区', 3, 47544, 'Z');
INSERT INTO `wa_region` VALUES (47812, '平镇区', 3, 47544, 'P');
INSERT INTO `wa_region` VALUES (47813, '龙潭区', 3, 47544, 'L');
INSERT INTO `wa_region` VALUES (47814, '杨梅区', 3, 47544, 'Y');
INSERT INTO `wa_region` VALUES (47815, '新屋区', 3, 47544, 'X');
INSERT INTO `wa_region` VALUES (47816, '观音区', 3, 47544, 'G');
INSERT INTO `wa_region` VALUES (47817, '桃园区', 3, 47544, 'T');
INSERT INTO `wa_region` VALUES (47818, '龟山区', 3, 47544, 'G');
INSERT INTO `wa_region` VALUES (47819, '八德区', 3, 47544, 'B');
INSERT INTO `wa_region` VALUES (47820, '大溪区', 3, 47544, 'D');
INSERT INTO `wa_region` VALUES (47821, '复兴区', 3, 47544, 'F');
INSERT INTO `wa_region` VALUES (47822, '大园区', 3, 47544, 'D');
INSERT INTO `wa_region` VALUES (47823, '芦竹区', 3, 47544, 'L');
INSERT INTO `wa_region` VALUES (47824, '万里区', 3, 47545, 'W');
INSERT INTO `wa_region` VALUES (47825, '金山区', 3, 47545, 'J');
INSERT INTO `wa_region` VALUES (47826, '板桥区', 3, 47545, 'B');
INSERT INTO `wa_region` VALUES (47827, '汐止区', 3, 47545, 'X');
INSERT INTO `wa_region` VALUES (47828, '深坑区', 3, 47545, 'S');
INSERT INTO `wa_region` VALUES (47829, '石碇区', 3, 47545, 'S');
INSERT INTO `wa_region` VALUES (47830, '瑞芳区', 3, 47545, 'R');
INSERT INTO `wa_region` VALUES (47831, '平溪区', 3, 47545, 'P');
INSERT INTO `wa_region` VALUES (47832, '双溪区', 3, 47545, 'S');
INSERT INTO `wa_region` VALUES (47833, '贡寮区', 3, 47545, 'G');
INSERT INTO `wa_region` VALUES (47834, '新店区', 3, 47545, 'X');
INSERT INTO `wa_region` VALUES (47835, '坪林区', 3, 47545, 'P');
INSERT INTO `wa_region` VALUES (47836, '乌来区', 3, 47545, 'W');
INSERT INTO `wa_region` VALUES (47837, '永和区', 3, 47545, 'Y');
INSERT INTO `wa_region` VALUES (47838, '中和区', 3, 47545, 'Z');
INSERT INTO `wa_region` VALUES (47839, '土城区', 3, 47545, 'T');
INSERT INTO `wa_region` VALUES (47840, '三峡区', 3, 47545, 'S');
INSERT INTO `wa_region` VALUES (47841, '树林区', 3, 47545, 'S');
INSERT INTO `wa_region` VALUES (47842, '莺歌区', 3, 47545, 'Y');
INSERT INTO `wa_region` VALUES (47843, '三重区', 3, 47545, 'S');
INSERT INTO `wa_region` VALUES (47844, '新庄区', 3, 47545, 'X');
INSERT INTO `wa_region` VALUES (47845, '泰山区', 3, 47545, 'T');
INSERT INTO `wa_region` VALUES (47846, '林口区', 3, 47545, 'L');
INSERT INTO `wa_region` VALUES (47847, '芦洲区', 3, 47545, 'L');
INSERT INTO `wa_region` VALUES (47848, '五股区', 3, 47545, 'W');
INSERT INTO `wa_region` VALUES (47849, '八里区', 3, 47545, 'B');
INSERT INTO `wa_region` VALUES (47850, '淡水区', 3, 47545, 'D');
INSERT INTO `wa_region` VALUES (47851, '三芝区', 3, 47545, 'S');
INSERT INTO `wa_region` VALUES (47852, '石门区', 3, 47545, 'S');
INSERT INTO `wa_region` VALUES (47853, '东区', 3, 47546, 'D');
INSERT INTO `wa_region` VALUES (47854, '北区', 3, 47546, 'B');
INSERT INTO `wa_region` VALUES (47855, '香山区', 3, 47546, 'X');
INSERT INTO `wa_region` VALUES (47856, '其它区', 3, 47546, 'Q');
INSERT INTO `wa_region` VALUES (47857, '竹北市', 3, 47547, 'Z');
INSERT INTO `wa_region` VALUES (47858, '湖口乡', 3, 47547, 'H');
INSERT INTO `wa_region` VALUES (47859, '新丰乡', 3, 47547, 'X');
INSERT INTO `wa_region` VALUES (47860, '新埔镇', 3, 47547, 'X');
INSERT INTO `wa_region` VALUES (47861, '关西镇', 3, 47547, 'G');
INSERT INTO `wa_region` VALUES (47862, '芎林乡', 3, 47547, 'X');
INSERT INTO `wa_region` VALUES (47863, '宝山乡', 3, 47547, 'B');
INSERT INTO `wa_region` VALUES (47864, '竹东镇', 3, 47547, 'Z');
INSERT INTO `wa_region` VALUES (47865, '五峰乡', 3, 47547, 'W');
INSERT INTO `wa_region` VALUES (47866, '横山乡', 3, 47547, 'H');
INSERT INTO `wa_region` VALUES (47867, '尖石乡', 3, 47547, 'J');
INSERT INTO `wa_region` VALUES (47868, '北埔乡', 3, 47547, 'B');
INSERT INTO `wa_region` VALUES (47869, '峨眉乡', 3, 47547, 'E');
INSERT INTO `wa_region` VALUES (47870, '宜兰市', 3, 47548, 'Y');
INSERT INTO `wa_region` VALUES (47871, '头城镇', 3, 47548, 'T');
INSERT INTO `wa_region` VALUES (47872, '礁溪乡', 3, 47548, 'J');
INSERT INTO `wa_region` VALUES (47873, '壮围乡', 3, 47548, 'Z');
INSERT INTO `wa_region` VALUES (47874, '员山乡', 3, 47548, 'Y');
INSERT INTO `wa_region` VALUES (47875, '罗东镇', 3, 47548, 'L');
INSERT INTO `wa_region` VALUES (47876, '三星乡', 3, 47548, 'S');
INSERT INTO `wa_region` VALUES (47877, '大同乡', 3, 47548, 'D');
INSERT INTO `wa_region` VALUES (47878, '五结乡', 3, 47548, 'W');
INSERT INTO `wa_region` VALUES (47879, '冬山乡', 3, 47548, 'D');
INSERT INTO `wa_region` VALUES (47880, '苏澳镇', 3, 47548, 'S');
INSERT INTO `wa_region` VALUES (47881, '南澳乡', 3, 47548, 'N');
INSERT INTO `wa_region` VALUES (47882, '钓鱼台', 3, 47548, 'D');
INSERT INTO `wa_region` VALUES (47883, '斗南镇', 3, 47549, 'D');
INSERT INTO `wa_region` VALUES (47884, '大埤乡', 3, 47549, 'D');
INSERT INTO `wa_region` VALUES (47885, '虎尾镇', 3, 47549, 'H');
INSERT INTO `wa_region` VALUES (47886, '土库镇', 3, 47549, 'T');
INSERT INTO `wa_region` VALUES (47887, '褒忠乡', 3, 47549, 'B');
INSERT INTO `wa_region` VALUES (47888, '东势乡', 3, 47549, 'D');
INSERT INTO `wa_region` VALUES (47889, '台西乡', 3, 47549, 'T');
INSERT INTO `wa_region` VALUES (47890, '仑背乡', 3, 47549, 'L');
INSERT INTO `wa_region` VALUES (47891, '麦寮乡', 3, 47549, 'M');
INSERT INTO `wa_region` VALUES (47892, '斗六市', 3, 47549, 'D');
INSERT INTO `wa_region` VALUES (47893, '林内乡', 3, 47549, 'L');
INSERT INTO `wa_region` VALUES (47894, '古坑乡', 3, 47549, 'G');
INSERT INTO `wa_region` VALUES (47895, '莿桐乡', 3, 47549, 'C');
INSERT INTO `wa_region` VALUES (47896, '西螺镇', 3, 47549, 'X');
INSERT INTO `wa_region` VALUES (47897, '二仑乡', 3, 47549, 'E');
INSERT INTO `wa_region` VALUES (47898, '北港镇', 3, 47549, 'B');
INSERT INTO `wa_region` VALUES (47899, '水林乡', 3, 47549, 'S');
INSERT INTO `wa_region` VALUES (47900, '口湖乡', 3, 47549, 'K');
INSERT INTO `wa_region` VALUES (47901, '四湖乡', 3, 47549, 'S');
INSERT INTO `wa_region` VALUES (47902, '元长乡', 3, 47549, 'Y');
INSERT INTO `wa_region` VALUES (47903, '彰化市', 3, 47550, 'Z');
INSERT INTO `wa_region` VALUES (47904, '芬园乡', 3, 47550, 'F');
INSERT INTO `wa_region` VALUES (47905, '花坛乡', 3, 47550, 'H');
INSERT INTO `wa_region` VALUES (47906, '秀水乡', 3, 47550, 'X');
INSERT INTO `wa_region` VALUES (47907, '鹿港镇', 3, 47550, 'L');
INSERT INTO `wa_region` VALUES (47908, '福兴乡', 3, 47550, 'F');
INSERT INTO `wa_region` VALUES (47909, '线西乡', 3, 47550, 'X');
INSERT INTO `wa_region` VALUES (47910, '和美镇', 3, 47550, 'H');
INSERT INTO `wa_region` VALUES (47911, '伸港乡', 3, 47550, 'S');
INSERT INTO `wa_region` VALUES (47912, '员林镇', 3, 47550, 'Y');
INSERT INTO `wa_region` VALUES (47913, '社头乡', 3, 47550, 'S');
INSERT INTO `wa_region` VALUES (47914, '永靖乡', 3, 47550, 'Y');
INSERT INTO `wa_region` VALUES (47915, '埔心乡', 3, 47550, 'P');
INSERT INTO `wa_region` VALUES (47916, '溪湖镇', 3, 47550, 'X');
INSERT INTO `wa_region` VALUES (47917, '大村乡', 3, 47550, 'D');
INSERT INTO `wa_region` VALUES (47918, '埔盐乡', 3, 47550, 'P');
INSERT INTO `wa_region` VALUES (47919, '田中镇', 3, 47550, 'T');
INSERT INTO `wa_region` VALUES (47920, '北斗镇', 3, 47550, 'B');
INSERT INTO `wa_region` VALUES (47921, '田尾乡', 3, 47550, 'T');
INSERT INTO `wa_region` VALUES (47922, '埤头乡', 3, 47550, 'P');
INSERT INTO `wa_region` VALUES (47923, '溪州乡', 3, 47550, 'X');
INSERT INTO `wa_region` VALUES (47924, '竹塘乡', 3, 47550, 'Z');
INSERT INTO `wa_region` VALUES (47925, '二林镇', 3, 47550, 'E');
INSERT INTO `wa_region` VALUES (47926, '大城乡', 3, 47550, 'D');
INSERT INTO `wa_region` VALUES (47927, '芳苑乡', 3, 47550, 'F');
INSERT INTO `wa_region` VALUES (47928, '二水乡', 3, 47550, 'E');
INSERT INTO `wa_region` VALUES (47929, '莲池区', 3, 1772, 'L');
INSERT INTO `wa_region` VALUES (47930, '竞秀区', 3, 1772, 'J');
INSERT INTO `wa_region` VALUES (47931, '常平镇', 3, 29855, 'C');
INSERT INTO `wa_region` VALUES (47932, '茶山镇', 3, 29855, 'C');
INSERT INTO `wa_region` VALUES (47933, '大朗镇', 3, 29855, 'D');
INSERT INTO `wa_region` VALUES (47934, '大岭山镇', 3, 29855, 'D');
INSERT INTO `wa_region` VALUES (47935, '道滘镇', 3, 29855, 'D');
INSERT INTO `wa_region` VALUES (47936, '东城街道', 3, 29855, 'D');
INSERT INTO `wa_region` VALUES (47937, '东坑镇', 3, 29855, 'D');
INSERT INTO `wa_region` VALUES (47938, '凤岗镇', 3, 29855, 'F');
INSERT INTO `wa_region` VALUES (47939, '高埗镇', 3, 29855, 'G');
INSERT INTO `wa_region` VALUES (47940, '莞城街道', 3, 29855, 'G');
INSERT INTO `wa_region` VALUES (47941, '横沥镇', 3, 29855, 'H');
INSERT INTO `wa_region` VALUES (47942, '洪梅镇', 3, 29855, 'H');
INSERT INTO `wa_region` VALUES (47943, '厚街镇', 3, 29855, 'H');
INSERT INTO `wa_region` VALUES (47944, '黄江镇', 3, 29855, 'H');
INSERT INTO `wa_region` VALUES (47945, '虎门镇', 3, 29855, 'H');
INSERT INTO `wa_region` VALUES (47946, '寮步镇', 3, 29855, 'L');
INSERT INTO `wa_region` VALUES (47947, '麻涌镇', 3, 29855, 'M');
INSERT INTO `wa_region` VALUES (47948, '南城街道', 3, 29855, 'N');
INSERT INTO `wa_region` VALUES (47949, '桥头镇', 3, 29855, 'Q');
INSERT INTO `wa_region` VALUES (47950, '清溪镇', 3, 29855, 'Q');
INSERT INTO `wa_region` VALUES (47951, '企石镇', 3, 29855, 'Q');
INSERT INTO `wa_region` VALUES (47952, '沙田镇', 3, 29855, 'S');
INSERT INTO `wa_region` VALUES (47953, '石碣镇', 3, 29855, 'S');
INSERT INTO `wa_region` VALUES (47954, '石龙镇', 3, 29855, 'S');
INSERT INTO `wa_region` VALUES (47955, '石排镇', 3, 29855, 'S');
INSERT INTO `wa_region` VALUES (47956, '松山湖管委会', 3, 29855, 'S');
INSERT INTO `wa_region` VALUES (47957, '塘厦镇', 3, 29855, 'T');
INSERT INTO `wa_region` VALUES (47958, '望牛墩镇', 3, 29855, 'W');
INSERT INTO `wa_region` VALUES (47959, '万江街道', 3, 29855, 'W');
INSERT INTO `wa_region` VALUES (47960, '谢岗镇', 3, 29855, 'X');
INSERT INTO `wa_region` VALUES (47961, '长安镇', 3, 29855, 'Z');
INSERT INTO `wa_region` VALUES (47962, '樟木头镇', 3, 29855, 'Z');
INSERT INTO `wa_region` VALUES (47963, '中堂镇', 3, 29855, 'Z');
INSERT INTO `wa_region` VALUES (47964, '海外', 1, 0, 'H');
INSERT INTO `wa_region` VALUES (47965, '海外', 2, 47964, 'H');
INSERT INTO `wa_region` VALUES (47966, '海外', 3, 47965, 'H');
INSERT INTO `wa_region` VALUES (47968, '吉阳区', 3, 31618, 'J');
INSERT INTO `wa_region` VALUES (47969, '天涯区', 3, 31618, 'T');
INSERT INTO `wa_region` VALUES (47970, '崖州区', 3, 31618, 'Y');
INSERT INTO `wa_region` VALUES (47971, '济源市', 2, 21387, 'J');
INSERT INTO `wa_region` VALUES (47972, '沁园街道', 3, 47971, 'Q');
INSERT INTO `wa_region` VALUES (47973, '济水街道', 3, 47971, 'J');
INSERT INTO `wa_region` VALUES (47974, '北海街道', 3, 47971, 'B');
INSERT INTO `wa_region` VALUES (47975, '天坛街道', 3, 47971, 'T');
INSERT INTO `wa_region` VALUES (47976, '玉泉街道', 3, 47971, 'Y');
INSERT INTO `wa_region` VALUES (47977, '克井镇', 3, 47971, 'K');
INSERT INTO `wa_region` VALUES (47978, '五龙口镇', 3, 47971, 'W');
INSERT INTO `wa_region` VALUES (47979, '轵城镇', 3, 47971, 'Z');
INSERT INTO `wa_region` VALUES (47980, '承留镇', 3, 47971, 'C');
INSERT INTO `wa_region` VALUES (47981, '邵原镇', 3, 47971, 'S');
INSERT INTO `wa_region` VALUES (47982, '坡头镇', 3, 47971, 'P');
INSERT INTO `wa_region` VALUES (47983, '梨林镇', 3, 47971, 'L');
INSERT INTO `wa_region` VALUES (47984, '大峪镇', 3, 47971, 'D');
INSERT INTO `wa_region` VALUES (47985, '思礼镇', 3, 47971, 'S');
INSERT INTO `wa_region` VALUES (47986, '王屋镇', 3, 47971, 'W');
INSERT INTO `wa_region` VALUES (47987, '下冶镇', 3, 47971, 'X');
INSERT INTO `wa_region` VALUES (47988, '龙港市', 3, 12974, 'L');
INSERT INTO `wa_region` VALUES (29891, '黄圃镇', 3, 29890, 'H');
INSERT INTO `wa_region` VALUES (29892, '南头镇', 3, 29890, 'N');
INSERT INTO `wa_region` VALUES (29893, '东凤镇', 3, 29890, 'D');
INSERT INTO `wa_region` VALUES (29894, '阜沙镇', 3, 29890, 'F');
INSERT INTO `wa_region` VALUES (29895, '小榄镇', 3, 29890, 'X');
INSERT INTO `wa_region` VALUES (29896, '古镇镇', 3, 29890, 'G');
INSERT INTO `wa_region` VALUES (29897, '横栏镇', 3, 29890, 'H');
INSERT INTO `wa_region` VALUES (29898, '三角镇', 3, 29890, 'S');
INSERT INTO `wa_region` VALUES (29899, '港口镇', 3, 29890, 'G');
INSERT INTO `wa_region` VALUES (29900, '大涌镇', 3, 29890, 'D');
INSERT INTO `wa_region` VALUES (29901, '沙溪镇', 3, 29890, 'S');
INSERT INTO `wa_region` VALUES (29902, '三乡镇', 3, 29890, 'S');
INSERT INTO `wa_region` VALUES (29903, '板芙镇', 3, 29890, 'B');
INSERT INTO `wa_region` VALUES (29904, '神湾镇', 3, 29890, 'S');
INSERT INTO `wa_region` VALUES (29905, '坦洲镇', 3, 29890, 'T');
INSERT INTO `wa_region` VALUES (29906, '中山火炬高技术产业开发区', 3, 29890, 'Z');
INSERT INTO `wa_region` VALUES (29907, '翠亨新区', 3, 29890, 'C');
INSERT INTO `wa_region` VALUES (29908, '石岐街道', 3, 29890, 'S');
INSERT INTO `wa_region` VALUES (29909, '东区街道', 3, 29890, 'D');
INSERT INTO `wa_region` VALUES (29910, '西区街道', 3, 29890, 'X');
INSERT INTO `wa_region` VALUES (29911, '南区街道', 3, 29890, 'N');
INSERT INTO `wa_region` VALUES (29912, '五桂山街道', 3, 29890, 'W');
INSERT INTO `wa_region` VALUES (29913, '火炬开发区街道', 3, 29890, 'H');
INSERT INTO `wa_region` VALUES (29914, '民众街道', 3, 29890, 'M');
INSERT INTO `wa_region` VALUES (29918, '南朗街道', 3, 29890, 'N');

-- ----------------------------
-- Table structure for wa_roles
-- ----------------------------
DROP TABLE IF EXISTS `wa_roles`;
CREATE TABLE `wa_roles`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色组',
  `rules` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '权限',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  `pid` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '父级',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '管理员角色' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_roles
-- ----------------------------
INSERT INTO `wa_roles` VALUES (1, '超级管理员', '*', '2022-08-13 16:15:01', '2022-12-23 12:05:07', 0);
INSERT INTO `wa_roles` VALUES (3, '管理员', '115,116,117,118,119,120,132,134,135,136,137,138,139,140,141', '2026-01-06 15:17:28', '2026-01-06 15:17:28', 0);
INSERT INTO `wa_roles` VALUES (4, '内容管理', '134,135,136,137,138,139,140,141', '2026-01-06 15:17:53', '2026-01-06 15:17:53', 0);
INSERT INTO `wa_roles` VALUES (5, '付费管理员', '115,116,117,118,119,120,132,89,90,91,92,93,94,95,96,97,98,99,100,101,102,103,104,105,106,63,64,65,66,67,68,69,70,71,72,73,113,20,21,22,23,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,48,49,51,52,53,55,56,57,58,59,60,62,128,129,130,131', '2026-01-06 15:19:09', '2026-01-06 15:19:09', 1);
INSERT INTO `wa_roles` VALUES (6, '二级管理员', '134,135,136,137,138,139,140,141', '2026-01-06 15:47:35', '2026-01-06 15:47:35', 3);

-- ----------------------------
-- Table structure for wa_rules
-- ----------------------------
DROP TABLE IF EXISTS `wa_rules`;
CREATE TABLE `wa_rules`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '标题',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '图标',
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '标识',
  `pid` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '上级菜单',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  `href` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'url',
  `type` int(11) NOT NULL DEFAULT 1 COMMENT '类型',
  `weight` int(11) NULL DEFAULT 0 COMMENT '排序',
  `status` int(11) NOT NULL DEFAULT 1 COMMENT '状态',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 142 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '权限规则' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_rules
-- ----------------------------
INSERT INTO `wa_rules` VALUES (1, '数据库管理', 'layui-icon-template-1', 'database', 16, '2025-12-16 21:56:54', '2025-12-31 14:12:54', '', 0, 2, 1);
INSERT INTO `wa_rules` VALUES (2, '所有表', '', 'plugin\\admin\\app\\controller\\TableController', 1, '2025-12-16 21:56:54', '2025-12-31 14:06:46', '/app/admin/table/index', 1, 1, 1);
INSERT INTO `wa_rules` VALUES (3, '权限管理', 'layui-icon-vercode', 'auth', NULL, '2025-12-16 21:56:54', '2025-12-31 14:25:53', '', 0, 1003, 1);
INSERT INTO `wa_rules` VALUES (4, '账户管理', 'layui-icon-survey', 'plugin\\admin\\app\\controller\\AdminController', 3, '2025-12-16 21:56:54', '2025-12-31 14:28:24', '/app/admin/admin/index', 1, 1, 1);
INSERT INTO `wa_rules` VALUES (5, '角色管理', 'layui-icon-user', 'plugin\\admin\\app\\controller\\RoleController', 3, '2025-12-16 21:56:54', '2025-12-31 14:28:42', '/app/admin/role/index', 1, 2, 1);
INSERT INTO `wa_rules` VALUES (6, '菜单管理', 'layui-icon-align-left', 'plugin\\admin\\app\\controller\\RuleController', 3, '2025-12-16 21:56:54', '2025-12-31 14:28:55', '/app/admin/rule/index', 1, 3, 1);
INSERT INTO `wa_rules` VALUES (7, '会员管理', 'layui-icon-username', 'user', NULL, '2025-12-16 21:56:54', '2026-01-06 15:56:21', '', 0, 1002, 1);
INSERT INTO `wa_rules` VALUES (8, '用户', '', 'plugin\\admin\\app\\controller\\UserController', 7, '2025-12-16 21:56:54', '2025-12-31 14:08:41', '/app/admin/user/index', 1, 1, 1);
INSERT INTO `wa_rules` VALUES (9, '通用设置', 'layui-icon-snowflake', 'common', 121, '2025-12-16 21:56:54', '2025-12-31 14:27:25', '', 0, 2, 1);
INSERT INTO `wa_rules` VALUES (10, '个人资料', '', 'plugin\\admin\\app\\controller\\AccountController', 9, '2025-12-16 21:56:54', '2025-12-31 14:09:42', '/app/admin/account/index', 1, 1, 1);
INSERT INTO `wa_rules` VALUES (11, '附件管理', '', 'plugin\\admin\\app\\controller\\UploadController', 9, '2025-12-16 21:56:54', '2025-12-31 14:09:52', '/app/admin/upload/index', 1, 2, 1);
INSERT INTO `wa_rules` VALUES (12, '字典设置', '', 'plugin\\admin\\app\\controller\\DictController', 9, '2025-12-16 21:56:54', '2025-12-31 14:09:59', '/app/admin/dict/index', 1, 3, 1);
INSERT INTO `wa_rules` VALUES (13, '参数配置', '', 'plugin\\admin\\app\\controller\\ConfigController', 9, '2025-12-16 21:56:54', '2025-12-31 14:22:12', '/app/admin/config/index', 1, 4, 1);
INSERT INTO `wa_rules` VALUES (14, '插件管理', 'layui-icon-app', 'plugin', NULL, '2025-12-16 21:56:54', '2026-01-06 11:25:07', '', 0, 1004, 0);
INSERT INTO `wa_rules` VALUES (15, '应用插件', '', 'plugin\\admin\\app\\controller\\PluginController', 14, '2025-12-16 21:56:54', '2025-12-31 14:10:25', '/app/admin/plugin/index', 1, 1, 1);
INSERT INTO `wa_rules` VALUES (16, '开发辅助', 'layui-icon-fonts-code', 'dev', 121, '2025-12-16 21:56:54', '2025-12-31 14:24:59', '', 0, 3, 1);
INSERT INTO `wa_rules` VALUES (17, '表单构建', '', 'plugin\\admin\\app\\controller\\DevController', 16, '2025-12-16 21:56:55', '2025-12-31 14:10:59', '/app/admin/dev/form-build', 1, 1, 1);
INSERT INTO `wa_rules` VALUES (18, '示例页面', 'layui-icon-templeate-1', 'demos', 16, '2025-12-16 21:56:55', '2026-01-05 10:58:26', '', 0, 4, 1);
INSERT INTO `wa_rules` VALUES (19, '工作空间', 'layui-icon-console', 'demo1', 18, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '', 0, 0, 1);
INSERT INTO `wa_rules` VALUES (20, '控制后台', 'layui-icon-console', 'demo10', 19, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/console/console1.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (21, '数据分析', 'layui-icon-console', 'demo13', 19, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/console/console2.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (22, '百度一下', 'layui-icon-console', 'demo14', 19, '2025-12-16 21:56:55', '2025-12-16 21:56:55', 'http://www.baidu.com', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (23, '主题预览', 'layui-icon-console', 'demo15', 19, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/system/theme.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (24, '常用组件', 'layui-icon-component', 'demo20', 18, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '', 0, 0, 1);
INSERT INTO `wa_rules` VALUES (25, '功能按钮', 'layui-icon-face-smile', 'demo2011', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/button.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (26, '表单集合', 'layui-icon-face-cry', 'demo2014', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/form.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (27, '字体图标', 'layui-icon-face-cry', 'demo2010', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/icon.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (28, '多选下拉', 'layui-icon-face-cry', 'demo2012', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/select.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (29, '动态标签', 'layui-icon-face-cry', 'demo2013', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/tag.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (30, '数据表格', 'layui-icon-face-cry', 'demo2031', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/table.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (31, '分布表单', 'layui-icon-face-cry', 'demo2032', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/step.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (32, '树形表格', 'layui-icon-face-cry', 'demo2033', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/treetable.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (33, '树状结构', 'layui-icon-face-cry', 'demo2034', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/dtree.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (34, '文本编辑', 'layui-icon-face-cry', 'demo2035', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/tinymce.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (35, '卡片组件', 'layui-icon-face-cry', 'demo2036', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/card.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (36, '抽屉组件', 'layui-icon-face-cry', 'demo2021', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/drawer.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (37, '消息通知', 'layui-icon-face-cry', 'demo2022', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/notice.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (38, '加载组件', 'layui-icon-face-cry', 'demo2024', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/loading.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (39, '弹层组件', 'layui-icon-face-cry', 'demo2023', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/popup.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (40, '多选项卡', 'layui-icon-face-cry', 'demo60131', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/tab.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (41, '数据菜单', 'layui-icon-face-cry', 'demo60132', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/menu.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (42, '哈希加密', 'layui-icon-face-cry', 'demo2041', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/encrypt.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (43, '图标选择', 'layui-icon-face-cry', 'demo2042', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/iconPicker.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (44, '省市级联', 'layui-icon-face-cry', 'demo2043', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/area.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (45, '数字滚动', 'layui-icon-face-cry', 'demo2044', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/count.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (46, '顶部返回', 'layui-icon-face-cry', 'demo2045', 24, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/document/topBar.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (47, '结果页面', 'layui-icon-auz', 'demo666', 18, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '', 0, 0, 1);
INSERT INTO `wa_rules` VALUES (48, '成功', 'layui-icon-face-smile', 'demo667', 47, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/result/success.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (49, '失败', 'layui-icon-face-cry', 'demo668', 47, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/result/error.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (50, '错误页面', 'layui-icon-face-cry', 'demo-error', 18, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '', 0, 0, 1);
INSERT INTO `wa_rules` VALUES (51, '403', 'layui-icon-face-smile', 'demo403', 50, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/error/403.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (52, '404', 'layui-icon-face-cry', 'demo404', 50, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/error/404.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (53, '500', 'layui-icon-face-cry', 'demo500', 50, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/error/500.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (54, '系统管理', 'layui-icon-set-fill', 'demo-system', 18, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '', 0, 0, 1);
INSERT INTO `wa_rules` VALUES (55, '用户管理', 'layui-icon-face-smile', 'demo601', 54, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/system/user.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (56, '角色管理', 'layui-icon-face-cry', 'demo602', 54, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/system/role.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (57, '权限管理', 'layui-icon-face-cry', 'demo603', 54, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/system/power.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (58, '部门管理', 'layui-icon-face-cry', 'demo604', 54, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/system/deptment.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (59, '行为日志', 'layui-icon-face-cry', 'demo605', 54, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/system/log.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (60, '数据字典', 'layui-icon-face-cry', 'demo606', 54, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/system/dict.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (61, '常用页面', 'layui-icon-template-1', 'demo-common', 18, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '', 0, 0, 1);
INSERT INTO `wa_rules` VALUES (62, '空白页面', 'layui-icon-face-smile', 'demo702', 61, '2025-12-16 21:56:55', '2025-12-16 21:56:55', '/app/admin/demos/system/space.html', 1, 0, 1);
INSERT INTO `wa_rules` VALUES (63, '查看表', '', 'plugin\\admin\\app\\controller\\TableController@view', 2, '2025-12-16 22:07:39', '2025-12-23 11:56:05', '', 2, 0, 1);
INSERT INTO `wa_rules` VALUES (64, '查询表', NULL, 'plugin\\admin\\app\\controller\\TableController@show', 2, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (65, '创建表', NULL, 'plugin\\admin\\app\\controller\\TableController@create', 2, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (66, '修改表', NULL, 'plugin\\admin\\app\\controller\\TableController@modify', 2, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (67, '一键菜单', NULL, 'plugin\\admin\\app\\controller\\TableController@crud', 2, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (68, '查询记录', NULL, 'plugin\\admin\\app\\controller\\TableController@select', 2, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (69, '插入记录', NULL, 'plugin\\admin\\app\\controller\\TableController@insert', 2, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (70, '更新记录', NULL, 'plugin\\admin\\app\\controller\\TableController@update', 2, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (71, '删除记录', NULL, 'plugin\\admin\\app\\controller\\TableController@delete', 2, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (72, '删除表', NULL, 'plugin\\admin\\app\\controller\\TableController@drop', 2, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (73, '表摘要', NULL, 'plugin\\admin\\app\\controller\\TableController@schema', 2, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (74, '插入', NULL, 'plugin\\admin\\app\\controller\\AdminController@insert', 4, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (75, '更新', NULL, 'plugin\\admin\\app\\controller\\AdminController@update', 4, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (76, '删除', NULL, 'plugin\\admin\\app\\controller\\AdminController@delete', 4, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (77, '插入', NULL, 'plugin\\admin\\app\\controller\\RoleController@insert', 5, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (78, '更新', NULL, 'plugin\\admin\\app\\controller\\RoleController@update', 5, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (79, '删除', NULL, 'plugin\\admin\\app\\controller\\RoleController@delete', 5, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (80, '获取角色权限', NULL, 'plugin\\admin\\app\\controller\\RoleController@rules', 5, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (81, '查询', NULL, 'plugin\\admin\\app\\controller\\RuleController@select', 6, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (82, '添加', NULL, 'plugin\\admin\\app\\controller\\RuleController@insert', 6, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (83, '更新', NULL, 'plugin\\admin\\app\\controller\\RuleController@update', 6, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (84, '删除', NULL, 'plugin\\admin\\app\\controller\\RuleController@delete', 6, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (85, '插入', NULL, 'plugin\\admin\\app\\controller\\UserController@insert', 8, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (86, '更新', NULL, 'plugin\\admin\\app\\controller\\UserController@update', 8, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (87, '查询', NULL, 'plugin\\admin\\app\\controller\\UserController@select', 8, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (88, '删除', NULL, 'plugin\\admin\\app\\controller\\UserController@delete', 8, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (89, '更新', NULL, 'plugin\\admin\\app\\controller\\AccountController@update', 10, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (90, '修改密码', NULL, 'plugin\\admin\\app\\controller\\AccountController@password', 10, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (91, '查询', NULL, 'plugin\\admin\\app\\controller\\AccountController@select', 10, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (92, '添加', NULL, 'plugin\\admin\\app\\controller\\AccountController@insert', 10, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (93, '删除', NULL, 'plugin\\admin\\app\\controller\\AccountController@delete', 10, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (94, '浏览附件', NULL, 'plugin\\admin\\app\\controller\\UploadController@attachment', 11, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (95, '查询附件', NULL, 'plugin\\admin\\app\\controller\\UploadController@select', 11, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (96, '更新附件', NULL, 'plugin\\admin\\app\\controller\\UploadController@update', 11, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (97, '添加附件', NULL, 'plugin\\admin\\app\\controller\\UploadController@insert', 11, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (98, '上传文件', NULL, 'plugin\\admin\\app\\controller\\UploadController@file', 11, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (99, '上传图片', NULL, 'plugin\\admin\\app\\controller\\UploadController@image', 11, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (100, '上传头像', NULL, 'plugin\\admin\\app\\controller\\UploadController@avatar', 11, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (101, '删除附件', NULL, 'plugin\\admin\\app\\controller\\UploadController@delete', 11, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (102, '查询', NULL, 'plugin\\admin\\app\\controller\\DictController@select', 12, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (103, '插入', NULL, 'plugin\\admin\\app\\controller\\DictController@insert', 12, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (104, '更新', NULL, 'plugin\\admin\\app\\controller\\DictController@update', 12, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (105, '删除', NULL, 'plugin\\admin\\app\\controller\\DictController@delete', 12, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (106, '更改', NULL, 'plugin\\admin\\app\\controller\\ConfigController@update', 13, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (107, '列表', NULL, 'plugin\\admin\\app\\controller\\PluginController@list', 15, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (108, '安装', NULL, 'plugin\\admin\\app\\controller\\PluginController@install', 15, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (109, '卸载', NULL, 'plugin\\admin\\app\\controller\\PluginController@uninstall', 15, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (110, '支付', NULL, 'plugin\\admin\\app\\controller\\PluginController@pay', 15, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (111, '登录官网', NULL, 'plugin\\admin\\app\\controller\\PluginController@login', 15, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (112, '获取已安装的插件列表', NULL, 'plugin\\admin\\app\\controller\\PluginController@getInstalledPlugins', 15, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (113, '表单构建', NULL, 'plugin\\admin\\app\\controller\\DevController@formBuild', 17, '2025-12-16 22:07:39', '2025-12-16 22:07:39', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (114, '栏目管理', 'layui-icon-template-1', 'plugin\\admin\\app\\controller\\ArctypeController', NULL, '2025-12-16 22:43:38', '2026-01-06 11:14:58', '/app/admin/arctype/index', 1, 100, 1);
INSERT INTO `wa_rules` VALUES (115, '插入', NULL, 'plugin\\admin\\app\\controller\\ArctypeController@insert', 114, '2025-12-16 22:44:09', '2025-12-16 22:44:09', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (116, '更新', NULL, 'plugin\\admin\\app\\controller\\ArctypeController@update', 114, '2025-12-16 22:44:09', '2025-12-16 22:44:09', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (117, '查询', NULL, 'plugin\\admin\\app\\controller\\ArctypeController@select', 114, '2025-12-16 22:44:09', '2025-12-16 22:44:09', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (118, '删除', NULL, 'plugin\\admin\\app\\controller\\ArctypeController@delete', 114, '2025-12-16 22:44:09', '2025-12-16 22:44:09', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (119, '新增页面 - 基础设置 Tab 内容', NULL, 'plugin\\admin\\app\\controller\\ArctypeController@base', 114, '2025-12-17 09:11:58', '2025-12-17 09:11:58', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (120, '新增页面 - 高级设置 Tab 内容', NULL, 'plugin\\admin\\app\\controller\\ArctypeController@advanced', 114, '2025-12-17 09:11:58', '2025-12-17 09:11:58', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (121, '功能视图', 'layui-icon-password', 'functionalView', NULL, '2025-12-23 11:36:05', '2025-12-31 14:26:17', '', 0, 1005, 1);
INSERT INTO `wa_rules` VALUES (127, '频道模型', 'layui-icon-theme', 'plugin\\admin\\app\\controller\\ChanneltypeController', 121, '2025-12-23 12:55:15', '2025-12-31 14:26:50', '/app/admin/channeltype/index', 1, 1, 1);
INSERT INTO `wa_rules` VALUES (128, '插入', NULL, 'plugin\\admin\\app\\controller\\ChanneltypeController@insert', 127, '2025-12-23 20:30:12', '2025-12-23 20:30:12', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (129, '更新', NULL, 'plugin\\admin\\app\\controller\\ChanneltypeController@update', 127, '2025-12-23 20:30:12', '2025-12-23 20:30:12', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (130, '查询', NULL, 'plugin\\admin\\app\\controller\\ChanneltypeController@select', 127, '2025-12-23 20:30:12', '2025-12-23 20:30:12', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (131, '删除', NULL, 'plugin\\admin\\app\\controller\\ChanneltypeController@delete', 127, '2025-12-23 20:30:12', '2025-12-23 20:30:12', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (132, '获取模板文件列表', NULL, 'plugin\\admin\\app\\controller\\ArctypeController@getTemplates', 114, '2025-12-30 22:22:59', '2025-12-30 22:22:59', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (133, '内容管理', 'layui-icon-layouts', 'plugin\\admin\\app\\controller\\ArchiveController', NULL, '2025-12-30 22:24:23', '2025-12-31 12:12:22', '/app/admin/archive/index', 1, 101, 1);
INSERT INTO `wa_rules` VALUES (134, '插入', NULL, 'plugin\\admin\\app\\controller\\ArchiveController@insert', 133, '2025-12-30 22:26:47', '2025-12-30 22:26:47', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (135, '更新', NULL, 'plugin\\admin\\app\\controller\\ArchiveController@update', 133, '2025-12-30 22:26:47', '2025-12-30 22:26:47', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (136, '查询', NULL, 'plugin\\admin\\app\\controller\\ArchiveController@select', 133, '2025-12-30 22:26:47', '2025-12-30 22:26:47', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (137, '删除', NULL, 'plugin\\admin\\app\\controller\\ArchiveController@delete', 133, '2025-12-30 22:26:47', '2025-12-30 22:26:47', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (138, '单页模型编辑页面', NULL, 'plugin\\admin\\app\\controller\\ArchiveController@singlePage', 133, '2026-01-02 23:37:03', '2026-01-02 23:37:03', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (139, '获取附加表内容（用于单页模型）', NULL, 'plugin\\admin\\app\\controller\\ArchiveController@getContent', 133, '2026-01-02 23:37:03', '2026-01-02 23:37:03', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (140, '单独更新状态字段（不触发标签删除逻辑）', NULL, 'plugin\\admin\\app\\controller\\ArchiveController@updateStatus', 133, '2026-01-05 10:06:12', '2026-01-05 10:06:12', NULL, 2, 0, 1);
INSERT INTO `wa_rules` VALUES (141, '获取常用标签列表（用于标签输入框提示）', NULL, 'plugin\\admin\\app\\controller\\ArchiveController@getCommonTags', 133, '2026-01-05 10:06:12', '2026-01-05 10:06:12', NULL, 2, 0, 1);

-- ----------------------------
-- Table structure for wa_single_content
-- ----------------------------
DROP TABLE IF EXISTS `wa_single_content`;
CREATE TABLE `wa_single_content`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文档ID',
  `typeid` int(10) NULL DEFAULT 0 COMMENT '栏目ID',
  `content` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '内容详情',
  `content_ey_m` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '手机端内容详情',
  `add_time` int(11) NULL DEFAULT 0 COMMENT '新增时间',
  `update_time` int(11) NULL DEFAULT 0 COMMENT '更新时间',
  `num` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '点击数',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `aid`(`aid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 9 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '单页附加表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_single_content
-- ----------------------------
INSERT INTO `wa_single_content` VALUES (1, 1, 1, '<p>你好</p>', '', 1690181692, 1767349480, '111');
INSERT INTO `wa_single_content` VALUES (2, 2, 8, '&lt;h2 style=&quot;white-space: normal;&quot;&gt;伟创网络科技公司&lt;/h2&gt;&lt;p&gt;我们是一家领先的网络科技公司，致力于提供创新的解决方案，帮助客户在数字时代取得成功。通过结合最新的技术和行业趋势，我们致力于开发高效、可靠且安全的解决方案，以满足客户的需求。&lt;/p&gt;&lt;p&gt;我们的使命是帮助客户在不断变化的数字环境中保持竞争优势。我们相信技术的力量可以改变商业模式、提高效率并创造新的机会。因此，我们与客户合作，共同探索创新的可能性，并为他们提供量身定制的解决方案，使他们能够在数字化转型中取得成功。&lt;/p&gt;&lt;p&gt;为了实现我们的使命，我们拥有一支优秀的团队，他们是我们最宝贵的资产。我们的团队由热情、有才华且具有丰富经验的专业人士组成，他们在广泛的领域和技术方向上拥有深厚的知识和专业技能。我们鼓励创新、追求卓越，并提供一个积极、有创造力且鼓励团队合作的工作环境，以激发团队成员的潜力和创造力。&lt;/p&gt;&lt;p&gt;我们的服务范围涵盖了以下关键领域：&lt;/p&gt;&lt;p&gt;1. 网站开发和设计：我们提供全方位的网站开发和设计服务，帮助客户建立专业、易于使用且具有吸引力的在线平台。&lt;/p&gt;&lt;p&gt;2. 应用程序开发：我们专注于开发创新的应用程序，适应不同的平台和设备，为客户提供个性化的解决方案。&lt;/p&gt;&lt;p&gt;3. 数据分析和人工智能：我们利用先进的数据分析技术和机器学习算法，帮助客户从海量数据中提取洞察力，并做出明智的决策。&lt;/p&gt;&lt;p&gt;4. 云计算和网络安全：我们提供可靠的云计算解决方案和强大的网络安全措施，确保客户的数据得到充分的保护和安全。&lt;/p&gt;&lt;p&gt;我们以客户满意度和业务成功为导向，与客户建立长期合作伙伴关系。通过我们的行业专业知识和卓越的技术能力，我们助力客户在竞争激烈的市场中脱颖而出，并实现可持续增长。&lt;/p&gt;&lt;p&gt;如果您对我们的公司和服务有任何疑问或想要了解更多信息，请随时与我们联系。我们期待与您合作，共同开创美好的数字未来！&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '', 1690161800, 1690161800, '');
INSERT INTO `wa_single_content` VALUES (3, 3, 13, '&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;针对不同服务器、虚拟空间，运行PHP的环境也有所不同，目前主要分为：Nginx、apache、IIS以及其他服务器。下面分享如何去掉URL上的index.php字符，&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;记得最后要重启服务器，在管理后台清除缓存哦！&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;nbsp;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;【IIS服务器】&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;在网站根目录下有个 web.config 文件，这个文件的作用是重写URL，让URL变得简短，易于SEO优化，以及用户的记忆。打开 web.config 文件，在原有的基础上加以下代码片段即可。&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;rewrite&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;rules&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;rule name=&amp;quot;Imported Rule 1&amp;quot; enabled=&amp;quot;true&amp;quot; stopProcessing=&amp;quot;true&amp;quot;&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;match url=&amp;quot;^(.*)$&amp;quot; /&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;conditions logicalGrouping=&amp;quot;MatchAll&amp;quot;&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;add input=&amp;quot;{HTTP_HOST}&amp;quot; pattern=&amp;quot;^(.*)$&amp;quot; /&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;add input=&amp;quot;{REQUEST_FILENAME}&amp;quot; matchType=&amp;quot;IsFile&amp;quot; negate=&amp;quot;true&amp;quot; /&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;add input=&amp;quot;{REQUEST_FILENAME}&amp;quot; matchType=&amp;quot;IsDirectory&amp;quot; negate=&amp;quot;true&amp;quot; /&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;/conditions&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;action type=&amp;quot;Rewrite&amp;quot; url=&amp;quot;index.php/{R:1}&amp;quot; /&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;/rule&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;/rules&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;/rewrite&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;nbsp;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;以下是某个香港虚拟空间的效果：&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;lt;?xml version=&amp;quot;1.0&amp;quot; encoding=&amp;quot;UTF-8&amp;quot;?&amp;gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;lt;configuration&amp;gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;lt;system.webServer&amp;gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;lt;handlers&amp;gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;lt;remove name=&amp;quot;PHP-7.0-7i24.com&amp;quot; /&amp;gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;lt;remove name=&amp;quot;PHP-5.6-7i24.com&amp;quot; /&amp;gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;lt;remove name=&amp;quot;PHP-5.5-7i24.com&amp;quot; /&amp;gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;lt;remove name=&amp;quot;PHP-5.4-7i24.com&amp;quot; /&amp;gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;lt;remove name=&amp;quot;PHP-5.3-7i24.com&amp;quot; /&amp;gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;lt;remove name=&amp;quot;PHP-5.2-7i24.com&amp;quot; /&amp;gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;lt;add name=&amp;quot;PHP-5.4-7i24.com&amp;quot; path=&amp;quot;*.php&amp;quot; verb=&amp;quot;*&amp;quot; modules=&amp;quot;FastCgiModule&amp;quot; scriptProcessor=&amp;quot;c:php.4php-cgi.exe&amp;quot; resourceType=&amp;quot;Either&amp;quot; /&amp;gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;lt;/handlers&amp;gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;rewrite&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;rules&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;rule name=&amp;quot;Imported Rule 1&amp;quot; enabled=&amp;quot;true&amp;quot; stopProcessing=&amp;quot;true&amp;quot;&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;match url=&amp;quot;^(.*)$&amp;quot; /&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;conditions logicalGrouping=&amp;quot;MatchAll&amp;quot;&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;add input=&amp;quot;{HTTP_HOST}&amp;quot; pattern=&amp;quot;^(.*)$&amp;quot; /&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;add input=&amp;quot;{REQUEST_FILENAME}&amp;quot; matchType=&amp;quot;IsFile&amp;quot; negate=&amp;quot;true&amp;quot; /&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;add input=&amp;quot;{REQUEST_FILENAME}&amp;quot; matchType=&amp;quot;IsDirectory&amp;quot; negate=&amp;quot;true&amp;quot; /&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;/conditions&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;action type=&amp;quot;Rewrite&amp;quot; url=&amp;quot;index.php/{R:1}&amp;quot; /&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;/rule&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;/rules&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;&amp;lt;/rewrite&amp;gt;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;lt;/system.webServer&amp;gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;lt;/configuration&amp;gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;nbsp;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;【Nginx服务器】&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;在原有的nginx重写文件里新增以下代码片段：&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;location / {&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;if (!-e $request_filename) {&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;rewrite ^(.*)$ /index.php?s=/$1 last;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;break;&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;}&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;}&lt;/span&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;nbsp;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;【apache服务器】&lt;br style=&quot;box-sizing: border-box;&quot;/&gt;易优CMS在apache服务器环境默认自动隐藏index.php入口。&lt;br style=&quot;box-sizing: border-box;&quot;/&gt;如果发现没隐藏，可以检查根目录.htaccess是否含有以下代码段：&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;lt;IfModule mod_rewrite.c&amp;gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&lt;div style=&quot;box-sizing: border-box;&quot;&gt;Options +FollowSymlinks -Multiviews&lt;/div&gt;&lt;div style=&quot;box-sizing: border-box;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;RewriteEngine on&lt;/span&gt;&lt;/div&gt;&lt;div style=&quot;box-sizing: border-box;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;RewriteCond %{REQUEST_FILENAME} !-d&lt;/span&gt;&lt;/div&gt;&lt;div style=&quot;box-sizing: border-box;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;RewriteCond %{REQUEST_FILENAME} !-f&lt;/span&gt;&lt;/div&gt;&lt;div style=&quot;box-sizing: border-box;&quot;&gt;&lt;span style=&quot;box-sizing: border-box; color: rgb(255, 0, 0);&quot;&gt;RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]&lt;/span&gt;&lt;/div&gt;&lt;/div&gt;&lt;div yne-bulb-block=&quot;paragraph&quot; style=&quot;box-sizing: border-box; color: rgb(34, 34, 34); font-family: &amp;quot;Segoe UI&amp;quot;, &amp;quot;Lucida Grande&amp;quot;, Helvetica, Arial, &amp;quot;Microsoft YaHei&amp;quot;, FreeSans, Arimo, &amp;quot;Droid Sans&amp;quot;, &amp;quot;wenquanyi micro hei&amp;quot;, &amp;quot;Hiragino Sans GB&amp;quot;, &amp;quot;Hiragino Sans GB W3&amp;quot;, Roboto, Arial, sans-serif; font-size: 18px; white-space: normal;&quot;&gt;&amp;lt;/IfModule&amp;gt;&lt;br style=&quot;box-sizing: border-box;&quot;/&gt;&lt;br style=&quot;box-sizing: border-box;&quot;/&gt;如果存在，继续查看apache是否开启了URL重写模块 rewrite_module ， 然后重启服务就行了。&lt;/div&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '', 0, 1531710225, '');
INSERT INTO `wa_single_content` VALUES (6, 47, 39, '&lt;p&gt;For different servers and virtual spaces, the environment for running PHP is also different, currently mainly divided into: Nginx, apache, IIS and other servers. Here&amp;#39;s how to remove the index. PHP character from the URL. Remember to restart the server and clear the cache in the management background.&lt;/p&gt;&lt;p&gt;[IIS Server]&lt;/p&gt;&lt;p&gt;There is a web. config file in the root directory of the website. The function of this file is to rewrite the URL, make the URL short, easy to optimize by SEO, and the user&amp;#39;s memory. Open the web. config file and add the following code fragments on the original basis.&lt;/p&gt;&lt;p&gt;&amp;lt;rewrite&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;rules&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;rule name= &amp;quot;Imported Rule 1&amp;quot; enabled= &amp;quot;true&amp;quot; stopProcessing= &amp;quot;true&amp;quot;&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;match url=&amp;quot;^(. *)$&amp;quot;/&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;conditions logicalGrouping=&amp;quot;MatchAll&amp;quot;&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;add input=&amp;quot;{HTTP_HOST}&amp;quot; pattern=&amp;quot;^(. *)$&amp;quot;/&amp;gt;&amp;quot;&lt;/p&gt;&lt;p&gt;&amp;lt;add input=&amp;quot;{REQUEST_FILENAME}&amp;quot; matchType= &amp;quot;IsFile&amp;quot; negate= &amp;quot;true&amp;quot;/&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;add input=&amp;quot;{REQUEST_FILENAME}&amp;quot; matchType= &amp;quot;IsDirectory&amp;quot; negate= &amp;quot;true&amp;quot;/&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;/conditions&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;action type=&amp;quot;Rewrite&amp;quot; url=&amp;quot;index.php/{R:1}&amp;quot;/&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;/rule&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;/rules&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;/rewrite&amp;gt;&lt;/p&gt;&lt;p&gt;The following is the effect of a Hong Kong virtual space:&lt;/p&gt;&lt;p&gt;&amp;lt;? XML version = &amp;quot;1.0&amp;quot; encoding = &amp;quot;UTF-8&amp;quot;?&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;configuration&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;system.webServer&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;handlers&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;remove name=&amp;quot;PHP-7.0-7i24.com&amp;quot;/&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;remove name=&amp;quot;PHP-5.6-7i24.com&amp;quot;/&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;remove name=&amp;quot;PHP-5.5-7i24.com&amp;quot;/&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;remove name=&amp;quot;PHP-5.4-7i24.com&amp;quot;/&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;remove name=&amp;quot;PHP-5.3-7i24.com&amp;quot;/&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;remove name=&amp;quot;PHP-5.2-7i24.com&amp;quot;/&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;add name=&amp;quot;PHP-5.4-7i24.com&amp;quot; path=&amp;quot;*.php&amp;quot; verb=&amp;quot;*&amp;quot; modules=&amp;quot;FastCgiModule&amp;quot; scriptProcessor=&amp;quot;c:php.4php-cgi.exe&amp;quot; resourceType=&amp;quot;Either&amp;quot;/&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;/handlers&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;rewrite&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;rules&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;rule name= &amp;quot;Imported Rule 1&amp;quot; enabled= &amp;quot;true&amp;quot; stopProcessing= &amp;quot;true&amp;quot;&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;match url=&amp;quot;^(. *)$&amp;quot;/&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;conditions logicalGrouping=&amp;quot;MatchAll&amp;quot;&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;add input=&amp;quot;{HTTP_HOST}&amp;quot; pattern=&amp;quot;^(. *)$&amp;quot;/&amp;gt;&amp;quot;&lt;/p&gt;&lt;p&gt;&amp;lt;add input=&amp;quot;{REQUEST_FILENAME}&amp;quot; matchType= &amp;quot;IsFile&amp;quot; negate= &amp;quot;true&amp;quot;/&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;add input=&amp;quot;{REQUEST_FILENAME}&amp;quot; matchType= &amp;quot;IsDirectory&amp;quot; negate= &amp;quot;true&amp;quot;/&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;/conditions&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;action type=&amp;quot;Rewrite&amp;quot; url=&amp;quot;index.php/{R:1}&amp;quot;/&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;/rule&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;/rules&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;/rewrite&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;/system.webServer&amp;gt;&lt;/p&gt;&lt;p&gt;&amp;lt;/configuration&amp;gt;&lt;/p&gt;&lt;p&gt;[Nginx Server]&lt;/p&gt;&lt;p&gt;Add the following code fragments to the original nginx rewrite file:&lt;/p&gt;&lt;p&gt;Location / {&lt;/p&gt;&lt;p&gt;If (!-e $request_filename) {&lt;/p&gt;&lt;p&gt;Rewrite ^(. *)$/ index. php? S=/$1 last;&lt;/p&gt;&lt;p&gt;Break;&lt;/p&gt;&lt;p&gt;}&lt;/p&gt;&lt;p&gt;}&lt;/p&gt;&lt;p&gt;[apache server]&lt;/p&gt;&lt;p&gt;Yiyou CMS automatically hides index. PHP entries by default in Apache server environment.&lt;/p&gt;&lt;p&gt;If no hiding is found, you can check whether the root directory. htaccess contains the following code snippets:&lt;/p&gt;&lt;p&gt;&amp;lt;IfModule mod_rewrite.c&amp;gt;&lt;/p&gt;&lt;p&gt;Options + FollowSymlinks - Multiviews&lt;/p&gt;&lt;p&gt;RewriteEngine on&lt;/p&gt;&lt;p&gt;RewriteCond%{REQUEST_FILENAME}!-d&lt;/p&gt;&lt;p&gt;RewriteCond%{REQUEST_FILENAME}!-f&lt;/p&gt;&lt;p&gt;RewriteRule ^(*)$index.php/$1 [QSA, PT, L]&lt;/p&gt;&lt;p&gt;&amp;lt;/IfModule&amp;gt;&lt;/p&gt;&lt;p&gt;If it exists, go ahead and see if Apache has opened the URL rewrite_module and restart the service.&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;', '', 1545270877, 1545270877, '');
INSERT INTO `wa_single_content` VALUES (7, 88, 54, '&lt;p style=&quot;text-align: center;&quot;&gt;电话: 400-123-4567&lt;br style=&quot;outline: none; box-sizing: border-box; color: rgb(47, 47, 47); font-family: Arial, &amp;quot;Helvetica Neue&amp;quot;, Helvetica, sans-serif; font-size: 14px; text-align: center; white-space: normal;&quot;/&gt;传真: + 86-123-4567&lt;br style=&quot;outline: none; box-sizing: border-box; color: rgb(47, 47, 47); font-family: Arial, &amp;quot;Helvetica Neue&amp;quot;, Helvetica, sans-serif; font-size: 14px; text-align: center; white-space: normal;&quot;/&gt;QQ:1234567890&lt;br style=&quot;outline: none; box-sizing: border-box; color: rgb(47, 47, 47); font-family: Arial, &amp;quot;Helvetica Neue&amp;quot;, Helvetica, sans-serif; font-size: 14px; text-align: center; white-space: normal;&quot;/&gt;邮箱: admin@youweb.com&lt;br style=&quot;outline: none; box-sizing: border-box; color: rgb(47, 47, 47); font-family: Arial, &amp;quot;Helvetica Neue&amp;quot;, Helvetica, sans-serif; font-size: 14px; text-align: center; white-space: normal;&quot;/&gt;地址: 广东省广州市天河区某某工业区88号&lt;/p&gt;&lt;p&gt;&lt;br/&gt;&lt;/p&gt;&lt;p style=&quot;text-align: center;&quot;&gt;&lt;iframe class=&quot;ueditor_baidumap&quot; src=&quot;/public/plugins/Ueditor/dialogs/map/show.html#center=116.404,39.915&amp;zoom=10&amp;width=530&amp;height=340&amp;markers=116.404,39.915&amp;markerStyles=l,A&quot; frameborder=&quot;0&quot; width=&quot;534&quot; height=&quot;344&quot;&gt;&lt;/iframe&gt;&lt;/p&gt;', '', 1564645627, 1564645627, '');
INSERT INTO `wa_single_content` VALUES (8, 106, 30, '', '', 1690166673, 1690166673, '');

-- ----------------------------
-- Table structure for wa_tagindex
-- ----------------------------
DROP TABLE IF EXISTS `wa_tagindex`;
CREATE TABLE `wa_tagindex`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'tagid',
  `tag` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'tag内容',
  `typeid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '栏目ID',
  `litpic` varchar(250) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '封面图',
  `seo_title` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'SEO标题',
  `seo_keywords` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'SEO关键词',
  `seo_description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'SEO描述',
  `count` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '点击',
  `total` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '文档数',
  `weekcc` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '周统计',
  `monthcc` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '月统计',
  `weekup` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '每周更新',
  `monthup` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '每月更新',
  `is_common` tinyint(1) NULL DEFAULT 0 COMMENT '是否常用标签，0=否，1=是',
  `sort_order` int(10) NULL DEFAULT 100 COMMENT '排序号',
  `lang` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '添加时间',
  `update_time` int(10) NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `typeid`(`typeid`) USING BTREE,
  INDEX `count`(`count`, `total`, `weekcc`, `monthcc`, `weekup`, `monthup`, `add_time`) USING BTREE,
  INDEX `tag`(`tag`) USING BTREE,
  INDEX `lang`(`lang`, `add_time`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 52 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '标签索引表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_tagindex
-- ----------------------------
INSERT INTO `wa_tagindex` VALUES (28, '网站', 12, '', '', '', '', 0, 1, 0, 0, 1616490155, 0, 0, 100, 'cn', 1547462640, 0);
INSERT INTO `wa_tagindex` VALUES (29, '建设', 12, '', '', '', '', 0, 1, 0, 0, 1616490155, 0, 0, 100, 'cn', 1547462640, 0);
INSERT INTO `wa_tagindex` VALUES (30, '五大核心', 12, '', '', '', '', 0, 1, 0, 0, 1616490155, 0, 0, 100, 'cn', 1547462640, 0);
INSERT INTO `wa_tagindex` VALUES (31, '要素', 12, '', '', '', '', 0, 1, 0, 0, 1616490155, 0, 0, 100, 'cn', 1547462640, 0);
INSERT INTO `wa_tagindex` VALUES (32, '华为', 24, '', '', '', '', 0, 1, 0, 0, 1616490155, 0, 0, 100, 'cn', 1571038749, 0);
INSERT INTO `wa_tagindex` VALUES (33, 'HUAWEI', 24, '', '', '', '', 0, 2, 0, 0, 1616490155, 0, 0, 100, 'cn', 1571038749, 1767544648);
INSERT INTO `wa_tagindex` VALUES (34, 'NOTE 8', 24, '', '', '', '', 0, 1, 0, 0, 1616490155, 0, 0, 100, 'cn', 1571038749, 0);
INSERT INTO `wa_tagindex` VALUES (37, '一号', 5, '', '', '', '', 3, 1, 2, 2, 1563785452, 1563785452, 0, 100, 'cn', 1526614158, 0);
INSERT INTO `wa_tagindex` VALUES (38, '社交', 12, '', '', '', '', 10, 0, 2, 2, 1616490155, 1610348155, 0, 100, 'cn', 1563520600, 1767576417);
INSERT INTO `wa_tagindex` VALUES (39, '媒体', 12, '', '', '', '', 9, 0, 7, 7, 1616490155, 1610348163, 0, 100, 'cn', 1563520600, 1767576417);
INSERT INTO `wa_tagindex` VALUES (40, '营销', 12, '', '', '', '', 1, 1, 0, 0, 1616490155, 1609990101, 0, 100, 'cn', 1564545045, 0);
INSERT INTO `wa_tagindex` VALUES (41, '商业', 12, '', '', '', '', 4, 1, 0, 0, 1686876674, 1686876674, 0, 100, 'cn', 1564545045, 0);
INSERT INTO `wa_tagindex` VALUES (42, '工程', 5, '', '', '', '', 0, 1, 0, 0, 1616490155, 0, 0, 100, 'cn', 1564565463, 0);
INSERT INTO `wa_tagindex` VALUES (43, '机械', 5, '', '', '', '', 0, 1, 0, 0, 1616490155, 0, 0, 100, 'cn', 1564565463, 0);
INSERT INTO `wa_tagindex` VALUES (44, '推土', 5, '', '', '', '', 0, 1, 0, 0, 1616490155, 0, 0, 100, 'cn', 1564565463, 0);
INSERT INTO `wa_tagindex` VALUES (45, '挖掘', 5, '', '', '', '', 0, 1, 0, 0, 1616490155, 0, 0, 100, 'cn', 1564565463, 0);
INSERT INTO `wa_tagindex` VALUES (46, '网站模板', 5, '', '', '', '', 0, 3, 0, 0, 1616490155, 0, 0, 100, 'cn', 1564565463, 1767576627);
INSERT INTO `wa_tagindex` VALUES (47, 'WindowsXP', 5, '', '', '', '', 0, 2, 0, 0, 1616490155, 0, 0, 100, 'cn', 1564623458, 1767544110);
INSERT INTO `wa_tagindex` VALUES (48, '操作系统', 5, '', '', '', '', 0, 4, 0, 0, 1616490155, 0, 0, 100, 'cn', 1564623458, 1767578204);
INSERT INTO `wa_tagindex` VALUES (49, '网络优化', 64, '', '', '', '', 0, 4, 0, 0, 1616490155, 0, 0, 100, 'cn', 1565234125, 1767578204);
INSERT INTO `wa_tagindex` VALUES (50, '推广服务', 64, '', '', '', '', 0, 2, 0, 0, 1616490155, 0, 0, 100, 'cn', 1565234125, 1767544648);
INSERT INTO `wa_tagindex` VALUES (51, '很好', 28, '', '', '', '', 0, 1, 0, 0, 1767544425, 1767544425, 0, 100, 'cn', 1767544425, 1767577222);

-- ----------------------------
-- Table structure for wa_taglist
-- ----------------------------
DROP TABLE IF EXISTS `wa_taglist`;
CREATE TABLE `wa_taglist`  (
  `tid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'tagid',
  `aid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文档ID',
  `typeid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '栏目ID',
  `tag` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT 'tag内容',
  `arcrank` tinyint(1) NULL DEFAULT 0 COMMENT '阅读权限',
  `lang` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'cn' COMMENT '语言标识',
  `add_time` int(11) NULL DEFAULT 0 COMMENT '新增时间',
  `update_time` int(11) NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`tid`, `aid`) USING BTREE,
  INDEX `aid`(`aid`, `typeid`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文章标签表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_taglist
-- ----------------------------
INSERT INTO `wa_taglist` VALUES (30, 13, 12, '五大核心', 0, 'cn', 1547462639, 1610615818);
INSERT INTO `wa_taglist` VALUES (29, 13, 12, '建设', 0, 'cn', 1547462639, 1610615818);
INSERT INTO `wa_taglist` VALUES (34, 27, 24, 'NOTE 8', 0, 'cn', 1571038748, 1610615791);
INSERT INTO `wa_taglist` VALUES (33, 27, 24, 'HUAWEI', 0, 'cn', 1571038748, 1610615791);
INSERT INTO `wa_taglist` VALUES (32, 27, 24, '华为', 0, 'cn', 1571038748, 1610615791);
INSERT INTO `wa_taglist` VALUES (44, 30, 5, '推土', 0, 'cn', 1564565462, 1610615767);
INSERT INTO `wa_taglist` VALUES (43, 30, 5, '机械', 0, 'cn', 1564565462, 1610615767);
INSERT INTO `wa_taglist` VALUES (42, 30, 5, '工程', 0, 'cn', 1564565462, 1610615767);
INSERT INTO `wa_taglist` VALUES (28, 13, 12, '网站', 0, 'cn', 1547462639, 1610615818);
INSERT INTO `wa_taglist` VALUES (31, 13, 12, '要素', 0, 'cn', 1547462639, 1610615818);
INSERT INTO `wa_taglist` VALUES (41, 41, 12, '商业', 0, 'cn', 1564545044, 1610615730);
INSERT INTO `wa_taglist` VALUES (40, 41, 12, '营销', 0, 'cn', 1564545044, 1610615730);
INSERT INTO `wa_taglist` VALUES (45, 30, 5, '挖掘', 0, 'cn', 1564565462, 1610615767);
INSERT INTO `wa_taglist` VALUES (46, 30, 5, '网站模板', 0, 'cn', 1564565462, 1610615767);
INSERT INTO `wa_taglist` VALUES (48, 91, 5, '操作系统', 0, 'cn', 1564623457, 1610615652);
INSERT INTO `wa_taglist` VALUES (47, 91, 5, 'WindowsXP', 0, 'cn', 1564623457, 1610615652);
INSERT INTO `wa_taglist` VALUES (50, 42, 64, '推广服务', 0, 'cn', 1565234124, 0);
INSERT INTO `wa_taglist` VALUES (49, 42, 64, '网络优化', 0, 'cn', 1565234124, 0);
INSERT INTO `wa_taglist` VALUES (47, 103, 28, 'WindowsXP', 0, 'cn', 1767544110, 1767546562);
INSERT INTO `wa_taglist` VALUES (48, 103, 28, '操作系统', 0, 'cn', 1767544110, 1767546562);
INSERT INTO `wa_taglist` VALUES (51, 103, 28, '很好', 0, 'cn', 1767544425, 1767546562);
INSERT INTO `wa_taglist` VALUES (33, 103, 28, 'HUAWEI', 0, 'cn', 1767544648, 1767546562);
INSERT INTO `wa_taglist` VALUES (50, 103, 28, '推广服务', 0, 'cn', 1767544648, 1767546562);
INSERT INTO `wa_taglist` VALUES (49, 103, 28, '网络优化', 0, 'cn', 1767544648, 1767546562);
INSERT INTO `wa_taglist` VALUES (46, 103, 28, '网站模板', 0, 'cn', 1767544648, 1767546562);
INSERT INTO `wa_taglist` VALUES (46, 107, 24, '网站模板', 0, 'cn', 1767545990, 1767545990);
INSERT INTO `wa_taglist` VALUES (49, 107, 24, '网络优化', 0, 'cn', 1767545990, 1767545990);
INSERT INTO `wa_taglist` VALUES (48, 107, 24, '操作系统', 0, 'cn', 1767545990, 1767545990);
INSERT INTO `wa_taglist` VALUES (49, 40, 12, '网络优化', 0, 'cn', 1767578204, 1767578704);
INSERT INTO `wa_taglist` VALUES (48, 40, 12, '操作系统', 0, 'cn', 1767578204, 1767578704);

-- ----------------------------
-- Table structure for wa_uploads
-- ----------------------------
DROP TABLE IF EXISTS `wa_uploads`;
CREATE TABLE `wa_uploads`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '文件',
  `admin_id` int(11) NULL DEFAULT NULL COMMENT '管理员',
  `file_size` int(11) NOT NULL COMMENT '文件大小',
  `mime_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'mime类型',
  `image_width` int(11) NULL DEFAULT NULL COMMENT '图片宽度',
  `image_height` int(11) NULL DEFAULT NULL COMMENT '图片高度',
  `ext` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '扩展名',
  `storage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'local' COMMENT '存储位置',
  `created_at` date NULL DEFAULT NULL COMMENT '上传时间',
  `category` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '类别',
  `updated_at` date NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `category`(`category`) USING BTREE,
  INDEX `admin_id`(`admin_id`) USING BTREE,
  INDEX `name`(`name`) USING BTREE,
  INDEX `ext`(`ext`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '附件' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_uploads
-- ----------------------------

-- ----------------------------
-- Table structure for wa_users
-- ----------------------------
DROP TABLE IF EXISTS `wa_users`;
CREATE TABLE `wa_users`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `username` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '用户名',
  `nickname` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '昵称',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '密码',
  `sex` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '1' COMMENT '性别',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '头像',
  `email` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '邮箱',
  `mobile` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '手机',
  `level` tinyint(4) NOT NULL DEFAULT 0 COMMENT '等级',
  `birthday` date NULL DEFAULT NULL COMMENT '生日',
  `money` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '余额(元)',
  `score` int(11) NOT NULL DEFAULT 0 COMMENT '积分',
  `last_time` datetime NULL DEFAULT NULL COMMENT '登录时间',
  `last_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '登录ip',
  `join_time` datetime NULL DEFAULT NULL COMMENT '注册时间',
  `join_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '注册ip',
  `token` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'token',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `role` int(11) NOT NULL DEFAULT 1 COMMENT '角色',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '禁用',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE,
  INDEX `join_time`(`join_time`) USING BTREE,
  INDEX `mobile`(`mobile`) USING BTREE,
  INDEX `email`(`email`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wa_users
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
