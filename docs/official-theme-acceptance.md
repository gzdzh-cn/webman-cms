# 官方主题与安装初始化验收

日期：2026-11-07。交付开发站8787；8793未操作。主agent负责集成和实际验证，数据agent负责演示初始化，主题agent负责原生主题，独立agent只读复核。

## 交付范围

- “文档管理”入口显示为“文章管理”，原ArchiveController权限key、URL和业务接口不变，数据库权限节点未迁改。
- 模板列表对Webman站点中的EyouCMS主题显示“当前为 Webman 模式，不支持 EyouCMS 模板”；启用接口保留相同拒绝语义，混用标签另行提示。
- 原default显示“EyouCMS 默认模板”；只调整目录元数据投影，原主题文件不变。
- webman-default显示“Webman 官方默认模板”：深蓝企业视觉、PC/mobile首页、产品、文章、单页、搜索、联系及留言；本地插画、响应式导航和960×600封面。共52份HTM、72个文件，303840字节。
- 安装新增默认不勾选的“导入初始化数据”。勾选时建立10栏目、18文档（3单页／6产品／9文章及方案）、6产品图片记录和3留言字段；所有内容可由后台维护。关闭时仍是完整空站结构。兼容模式空站同样可勾选；导入原站SQL时禁用且服务端拒绝混入演示内容。
- 初始化选项及版本进入安装预检、暂存、发布与恢复签名。安装页维护源在app/cms/install/view/index.html，新包复制覆盖在打包副本进行，不修改已封存源站资料。既有密码二次确认、8～72字节及独立明文按钮保持。

## 实际验证

在项目外独立副本8788和专属cmsit_theme_数据库串行执行，未向原库安装：

- Webman空站不带初始化：栏目、文档和正文均为0；Webman／EyouCMS带初始化均核对上述数量和关联。SQL与演示同时提交在DDL前拒绝；预检后或未完成任务中改选项拒绝接管。
- 52份模板编译和PHP语法检查；独立复核166条include、34条本地资源引用、16份SVG、封面及主题交互。安装界面21组、主题脚本10组和演示数据11组独立纯检查通过。这些不代替实际安装证据。
- 54个PC/mobile页面和27项资源HTTP检查通过，覆盖首页、所有栏目、非单页文档详情和搜索。单页使用栏目地址验收，不宣称独立View地址可用。
- 真实浏览器：1440×1000与390×844首页无横向溢出或破图；手机导航、产品列表及详情、联系和留言正常。隔离留言显示操作成功，并读库确认一条提交及联系人／电话／需求描述3条值持久化。
- 安装页真实浏览器核对默认不勾选、两个空站模式均可选、导入SQL取消并禁用初始化、退出导入后不自动重新勾选；浏览器未代替用户提交真实安装。
- 8787已reload。实际服务读取确认“文章管理”投影、两套主题名称和当前Webman兼容状态。此前后台浏览器已看到新提示及原生主题名称；最终刷新时浏览器连接超时，EyouCMS新名称的最终证据为服务读取，不冒充最终浏览器截图。

## 保护与边界

webman_admin仍为168表9826行；当前webman_cms_new仍为167表5937行，均与本轮基线逐表行数和内容SHA一致。default／1003／1004、public/uploads、public/html、源安装归档、.git/index、.env及安装配置SHA一致。当前开发站未重装或自动填充演示内容，账号密码未改。

源封存记录中的database/eyoucms-1.7.7.sql在本轮开始前已经缺失，实际其余5文件保持。未改记录掩盖缺失；隔离新包采用三份可信空站／后台／内部SQL独立构造，不宣称历史封存包完整。本轮不关闭原版严格标签兼容、全部角色或其他未完成验收。

## 证据

项目外证据目录：`/Volumes/disk/site/php/dzhcms/webman-cms-preserved-20261107-v1/official-theme-20261107-223751`。

- 安装：verification/install-cases.json、install-final-cases.json。
- 模板／HTTP：verification/native-theme-compile.json、theme-static-checks.json、theme-http.json。
- 留言／保护：verification/guestbook-persistence.json、protected-final.json、live-projection.json。
- 独立复核：independent/final-review.md及逐文件SHA绑定final-review.json。
- 浏览器截图：verification/official-home-desktop.jpg、official-home-mobile.jpg、installer-demo-option.jpg、guestbook-browser-success.jpg。

主题结构见[主题维护说明](webman-official-theme.md)，安装操作见[安装说明](installation.md)。隔离环境仅用于验收，不替代开发站或正式站。

## 安装模式选择交互补充

2026-11-07：兼容模式勾选框改为“网站模式”原生下拉框，Webman模式默认选中，另提供兼容EyouCMS模式。两种模式的初始化选项均可勾选；只切换模式保留初始化选择。开启原站SQL导入清空并禁用初始化，切回Webman清除SQL选择及上传令牌。对外仍提交compatibility=0|1，未修改安装服务及入口开关，未提交安装。

维护页DOM、JS语法及25组前端边界检查通过，包含模式切换使预检失效、请求参数、SQL互斥、忙碌／完成状态和原密码控件。另通过12组独立合成DOM复核及8787安装页实际HTTP输出检查。此补充为源码、HTTP和合成DOM事件验证，未重新安装数据库或重复声称真实浏览器验收。证据在项目外install-mode-dropdown-20261107目录。

## 初始化选项布局整理

2026-11-07：唯一初始化控件移入网站模式白卡，位于下拉框说明后且在兼容专属区域外；删除外侧独立区域。SQL选项改为“导入已有 EyouCMS 网站 SQL”，说明其用于迁移原站而非示例初始化。业务脚本仅调整SQL提示文案，原接口、预检与互斥逻辑保持。

8787独立浏览器页实际验证：默认未勾选、切换两模式保留勾选、SQL导入清空禁用初始化、取消后恢复可选且不自动勾选；390px下无横向溢出，DOM确认初始化控件只有1个且位于同一模式卡。8项独立结构检查、12组独立交互回归通过。未提交安装、上传SQL或操作数据库；未改现有用户表单。证据及截图位于项目外install-data-layout-20261107与installer-data-group-review。
