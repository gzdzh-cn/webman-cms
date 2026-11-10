# 后台目录迁移验收

日期：2026-11-07。当前阶段：已于13:52完成开发站8787切换和登录后复验。8793保持原样。

## 结构与兼容契约

后台实现、页面、配置、辅助函数与90个旧身份类集中到app/cms/admin。legacy保留真实薄类而非class_alias，主路由明确登记原URL；实体plugin/admin不存在。静态资源继续使用admin-assets和_shared，不修改主题、uploads、已生成HTML。

模块路由分为内置routes.php与生成generated-routes.php。主HTTP接入识别旧namespace为主应用，AdminEntry核验控制器及动作；旧模型连接别名注册于主配置。AdminView负责普通、登录、403及生成器页面。Composer移除webman/admin并声明conflict，其余依赖版本不变，保留上游MIT许可证及私有来源信息。

## 证据位置

本轮独立目录：`/Volumes/disk/site/php/dzhcms/webman-cms-preserved-20261107-v1/admin-relocation-20261107-v1`。baseline保存源码，baseline-manifest.json记录文件SHA，git-index与两个diff保存原暂存/未暂存状态；database-before.json为受控私有备份，不放入公开目录。验证与截图位于verification。

## 当前检查

- 隔离目录移除plugin/admin后冷启动，前台首页、后台登录、验证码、匿名401、未知后台404和CMS实现入口拒绝已通过。
- 90类原有公开签名/祖先链、4helper、20模型/关联、65视图字节保持；Table/Plugin内部protected实现因生成与包保护调整单列。
- 六类授权身份使用独立fixture对照；路由分派检查覆盖明确控制器和HTTP方法。fixture结果不冒充真实角色登录。
- 安装、生成闭环和隔离浏览器验收已完成，开发站切换及最终保护范围已核对，详见下方交付记录。

## 运维

新增CMS CRUD默认写app/cms/admin/controller、model和view，同时生成legacy薄类和路由登记；不同源码拒绝覆盖。生成响应提示reload_required时执行 `php start.php reload` 后新路由生效。后台变更不修改原角色授权字符串或重建菜单。

回退应先停止本轮worker，按外部切换快照恢复本批代码、配置、Composer锁与依赖，再启动。不得把旧runtime整份覆盖到运行站，不覆盖新上传、业务库或生成HTML。原Git目录和暂存状态始终保留。

本轮不关闭EyouCMS严格兼容的T16/T17、未提供主题和既有富文本来源问题。

## 补充验证与已知差异

- 空站安装：独立CMS/后台前缀、空内容、管理员哈希和菜单初始化通过；重复安装、丢失本地安装锁、已有业务表均拒绝覆盖。外部副本删除install/docs/runtime后维护状态与资源核验通过。
- Composer干净安装83个保留包及hooks通过，webman/admin不在vendor、plugin/admin不存在；其余包版本和元数据保持。原SQL复用同字节资料，资源清单仅43个引用说明更新。
- 原生插件ZIP处理覆盖正常安装更新、保留名称及大小写、越界/跨根/重复/符号链接、发布失败回退；需要ZipArchive，第三方安装回调仍为受信任PHP代码，不能称为沙箱。
- 资源包恢复2129实体，重复恢复0写入、冲突拒绝及SHA检查通过。独立部署允许plugin根目录缺失，保留合法第三方插件。
- 真实隔离DB的生成器实现：新模型/控制器/视图/legacy/路由登记、菜单授权、页面渲染、CRUD保存与重开通过；菜单写入失败注入确认源码及登记回退、事务无残留。使用测试session的CLI请求；不能替代浏览器正常登录。
- 路由对照334项中332项一致，两项有意差异是 `/app/admin/admin/index`、`/select` 原被资源通配误吞成404，现正确进入管理员控制器的鉴权。匿名返回401，未增加权限。

## 隔离配置纠正记录

首次.env替换未匹配`DB_NAME = ...`带空格格式，8788一度沿用开发库。生成器实际写入前的SELECT DATABASE强校验已拒绝执行，随后停止8788、修正配置并重启，直接配置下核验到wmcms_adminmove_20261107_a1后再开始写入测试。修正前只有匿名/前台HTTP读验，前台请求可能更新原有点击和搜索统计；不称为无数据库副作用。修正后完整334项HTTP已重跑，首次证据保留为http-contract-before-db-isolation-fix.json。账号、权限和业务正文未由本次测试修改。

## 冷启动与切换预检

独立8789副本只从本轮数据库快照恢复168表9825行，核验实际专用库后启动。停止全部worker，删除整个runtime/install/docs，再次启动：PC首页、手机首页、登录页响应SHA前后一致，验证码JPEG正常；15条生成状态SHA保持，12个已完成任务未重复执行。2945份主题/上传/公开HTML/后台资源实体字节保持，安装状态与资源verify通过，旧目录和旧vendor包均不存在。演练已停服、专用DB已清理，证据cold-start-results.json。

8787切换前源码只读预检：8889文件与本轮基线一致，.git/index原SHA保持。原主题及上传1562文件不在部署delta中。切换脚本按逐文件SHA拒绝并发改动，仅发布本轮增删改；失败先停worker再恢复本批文件及依赖，不涉及业务数据或新上传。正常登录后的隔离浏览器验收与开发站切换均已完成。


最终切换前数据库只读审核：168表结构相同，167表全行相同；wa_admins/wa_roles/wa_rules/wa_admin_roles全行一致，密码哈希、ID及授权字符串保持。唯一实际新增为1条非现有管理员标识的登录限流记录（count=1），不能仅凭该记录归因调用方，未清理或回写。ey_users_level.discount最初显示的差异源于检查器PDO字符串格式，与快照相同PDO设置复核后整表相同。证据live-database-before-cutover.json、live-difference-details.json及冻结清单live-audit-freeze.json。

## 正常浏览器验收（隔离8788）

用户完成验证码并正常登录专用隔离账号后，实际验证：工作台和系统版本、文档列表MIIX筛选两条、打开文档#99、TinyMCE源码弹窗与公式延迟插件、取消返回后筛选保持；功能地图、附件上传、管理员列表和编辑窗口打开关闭、角色权限列表均正常。菜单深浅色切换后恢复浅色、侧栏折叠展开、刷新后外层页签保留通过。编辑窗口未提交原账号或原正文。

通过原生文件选择器上传96×48 PNG，附件列表显示209Bytes和正确尺寸；数据库记录与实际文件SHA均核对一致。正常注销返回登录页后，按账号ID、上传记录、文件SHA清理本次隔离账号、关联和上传文件，外部临时明文凭据已删除。浏览器截图为isolated-editor-formula.png、isolated-upload-success.png、isolated-appearance-dark.png、isolated-role-list.png；清理证据browser-fixture-cleanup.json。

六类角色是独立授权fixture，不能称为六类真实浏览器登录。已记录的原正文HTML编码问题保持原状。本轮浏览器未新增权限或修改业务正文。8793始终未操作。

## 开发站交付结果

2026-11-07 13:52:17，8787发布1352项文件增删改并正常启动。`plugin/admin`和`vendor/webman/admin`实体均不存在；旧namespace由Composer显式指向CMS内部legacy。原管理员会话仍有效，实际复验工作台新版本、文档MIIX筛选两条、#99编辑器加载与取消返回、SEO设置和管理员列表。开发站38项HTTP/新旧资源及HEAD/私有路径拒绝检查通过。截图live-after-dashboard.png、live-after-admin-list.png，HTTP记录live-http-after-cutover.json。

切换后独立只读核对：168张表与切换前逐行一致；账号、密码哈希、角色、权限节点和授权字符串与原始基线一致。1352项批准源码变更SHA全部匹配。模板1409、上传153、公开HTML0、后台资源1383份文件保持原字节；当前没有公开HTML实体，不把空集合称为已有HTML页面实际浏览验证。Git目录原位保留，原暂存索引SHA一致。额外发现6份Finder的.DS_Store元数据变化，严格整目录报告保留passed=false，未当成业务代码变化或覆盖用户元数据；没有其他计划外源码差异。证据live-after-cutover-review.json及live-after-cutover-freeze.json。后续仅同步本文等4份交付文档，另列final-documentation-sync.json。

首次发布因健康检查误把登录页中的正常account/login表单地址判为故障而自动回退；逐文件验证恢复成功后修正检查，再次发布通过。首次记录保存在live-cutover-attempt1-rolled-back，成功快照在live-cutover；没有因此回写业务数据库。该回退测试不是业务实现故障。

隔离临时账号、上传样本和明文凭据已清理，8788在最终复验后停止。8793未启动、未同步。安装、恢复、生成闭环及冷启动采用本轮隔离证据；六类角色权限采用独立fixture，未夸大为六类浏览器登录。本次交付仅完成后台目录迁移，既有EyouCMS严格兼容未完成项继续开放。
