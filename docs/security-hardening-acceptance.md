# 企业官网安全加固验收

日期：2026-11-09。候选目录与证据根为 `/Volumes/disk/site/php/dzhcms/security-hardening-20261109-121438`，隔离服务 8796，独立 MySQL 5.7 容器 `cms-security-mysql57-20261109`、端口 13324、库 `security_site`。源数据使用运行库的一致性快照。测试账户只修改隔离库，未重置运行站账户。开发 8787 发布状态在本文末节更新；8793 不操作。

## 已验证

- 原先提前修改的六文件以实际源流复核：UploadPaths 目录边界、UploadController 所有新上传、FormService 公共表单进入统一上传策略；UiConfigService 所属可视化入口关闭；BasicSettingsService 不能放开危险格式；TagList 的搜索、字段、排序与 ID 条件重新审查及绑定。不能以原语法检查作为这些结论的依据。
- `sql-tests/table-controller-results.txt`：数据表后台的搜索词/库名绑定，字段与方向白名单、分页上限和元数据标识符转义经 SQL 观察器验证；实际 HTTP 注入输入另纳入最终接口复验。
- `http-tests/installer-session-results.txt`、`upload-short-echo-results.txt`：14 项真实 Webman 请求/会话存储测试（仅替换 Cookie 发送边界，未执行安装）及 6 项上传服务测试通过，覆盖旧用途失效、安装状态跨库、其他身份状态拒绝，以及未闭合短 echo 代码和读取分块边界。
- `sql-tests/security-test-results.txt`：只读真实 MySQL 参数绑定查询；静态 SELECT、批量 ID、排序、分页 SQL 签名及恶意模板结构；12 个禁止 AST 样本。3144 的 38 个、webman-default 的 52 个顶层 PC/mobile 模板通过编译检查。该编译检查本身不展开 include，实际页面渲染另有 HTTP 验证。
- `feature-tests/`：启用 PHP assertions 的直接控制器/旧路由、标签、钩子和恶意插件配置加载检查；3144 旧会员页面有 91 条不支持提示、0 条主题阻断。普通共享模板含 PHP、可视化内容标签及旧会员页 PHP 之后的内容标签会阻断，并报告行号。
- `upload-tests/`：真实格式、随机名称、大小、双扩展名、PHP 图像、Office 宏、路径与链接；图片中的随机短标签样似字节不误判。这里包含模拟数据库服务调用，不能替代下面的真实 HTTP。
- `http-tests/audit-results.json`、`public-form-results.json`：真实管理员登录、图片/头像/DOCX、匿名拒绝、伪造图像/PHP/双扩展名、请求目录不改变目标；公开表单真实数据库与文件发布，异常输入不写入；本任务表单记录精确清理。模板图片上传入口 HTTP 403，普通 TXT 创建/读取/编辑/PHP 内容拒绝，任务文件已清理。
- `runtime-tests/boundary-results.txt`、`key-results.json`：原始字节认证、头/正文/签名和有效期、路径/用途/模式/站点替换、未签名内容、恶意序列化头不解析、会话 ID/正文、模板包含越界/软硬链接；150 次并发替换期间只能执行安全快照或拒绝。五种无效/缺失密钥情形均拒绝。
- `http-tests/runtime-tamper.json`：篡改 72 个候选模板/缓存/语言派生文件后，首页、栏目、联系页、详情和后台匿名入口仍能读取，未返回篡改内容，未创建测试执行标记。管理员退出后回放旧完整签名会话及直接修改会话都被拒绝；真实 HTTP 与数据库撤销记录测试分别记录。
- `http-tests/front-regression.json`、`observed-links.json`、`details.json`：3144 PC/mobile 首页、栏目、产品详情、实际联系页可读，`uiset=on` 无编辑注入。首页与服务栏目输出与发布前 8787 字节 SHA 相同；动态 token/计数内容可能不同。
- `http-tests/static-results.json`：PC/mobile 首页和详情四种真实静态任务完成并可通过应用读取；两个终端首页静态文件被改后未返回篡改字节。后台内容、语言、角色和静态任务列表有只读 HTTP 回归。
- `http-tests/permission-results.json`、`content-results.json`：独立受限角色的上传授权/撤权、模板管理与新建权限成对检查通过；测试栏目/文章真实创建、读取、更新及回收通过，测试业务行及正文来源记录精确清理。该内容夹具的自动静态队列未完整追踪完成，静态生成以专门的四项测试为准。
- 会话最终复核：仅精确安装交接字段允许跨安装数据库变更；旧身份字段及未知状态绑定数据库。运行时测试覆盖字段分类、数据库替换及 selector 降级拒绝，管理员登录/退出/旧签名回放另经真实 HTTP 验证。
- 最终 runtime 整体移出后重启：`http-tests/runtime-rebuild-results.json` 的 20 次 PC/mobile 冷、热动态页面读取通过，新增模板/数据/后台模板文件全部带签名（`runtime-tests/rebuilt-signatures.json`）；重建后的登录、上传、模板编辑、SQL 注入输入、会话退出回放及四种静态生成重新通过。
- 新增语言关联 ID、模板辅助函数白名单和普通数组反序列化检查；旧顶层对象数组缓存按未命中重查。兼容辅助函数中不在模板白名单且没有当前调用的 `getAllArctype`/`getAllArctypeCount` 仍属可信 PHP 的历史内部接口，不能作为将来开放任意参数调用的安全接口。
- `nginx-validation.txt`：独立完整配置包装后的 Nginx 语法检查通过；未加载或修改原 EyouCMS 虚拟主机。

## 只读扫描与边界

`readonly-scan.json` 保存原主题、uploads、runtime 的文件 SHA 和特征；`readonly-source-scan.json` 保存首轮 619 个源码/配置文件中的复核位置、行号、SHA 与依据；`readonly-source-scan-final.json` 保存最终 622 个源码/配置/入口文件的完整 SHA 与 151 个复核位置。源码内的编译器字符串替换、固定配置 include、受限维护调用不直接判为木马。旧会员模板含 PHP，渲染明确不支持；原上传 `.htaccess` 是 Apache 执行限制配置。21 个 JPEG 及主题 PNG 内出现短标签相似字节，不据此认定木马。未发现简单 eval+base64/gzip 的混淆链；这不是无木马证明，未自动删除/隔离任何原文件。

3144 原主题缺少 PC/mobile `lists_search.htm`。`http-tests/baseline-compare.json` 证明发布前 8787 与候选在搜索 URL 都返回相同的 500/错误页摘要。本轮保留原主题，不跨主题补页；搜索 SQL 安全检查已单独进行，不能把缺页称为搜索页面回归通过。实际数据中单页内容通过栏目 URL 访问，不把猜测的 View 单页 URL 404 当新回归。

测试覆盖企业官网主要请求与安全边界，未逐条穷尽所有历史兼容模型、标签属性、全部角色/UI 组合或 MySQL 8 分支。通用 raw Query 仍须遵守绑定和服务端标识符规则。生产独立 Unix 服务账号、源码只读和密钥只读权限需要实际主机部署；当前开发机 worker 由同一用户运行，不能宣称完成生产操作系统隔离。

## 发布记录

已于 2026-11-09 13:18:33 UTC+08:00 将 118 个差异文件发布到 8787，停止目标站 worker 后将原 runtime 整体移至项目外私有备份，再逐文件原子发布、生成 Composer autoload 并启动。没有复制候选 `.env`、测试库、主题、附件或静态产物，没有执行安装/业务数据导入；8793 未操作。原 `.env` 只追加 `CMS_SECURITY_KEY_FILE=/Volumes/disk/site/php/dzhcms/cms-private/dzhcms002.key`。旧未签名缓存及全部旧会话失效，管理员需要正常重新登录；未重置任何运行站密码。

运行站 `release/http-smoke.json` 的 21 次只读请求通过：PC/mobile 首页、服务栏目、联系页、产品详情和后台登录页可读；已关闭的插件/可视化/生成器入口、源码模板及私有文件拒绝访问。未使用隔离凭据登录运行站，运行站后台写操作的验证来自隔离环境。新 runtime 中前台模板与数据缓存均为签名文件；后台签名渲染在隔离环境真实登录验证。

逐文件备份、原环境、旧 runtime、SHA 清单及发布日志位于 `/Volumes/disk/site/php/dzhcms/security-hardening-20261109-121438/release`。回退命令为 `python3 /Volumes/disk/site/php/dzhcms/security-hardening-20261109-121438/release/release.py --rollback --apply`；脚本先核对当前文件仍等于本次发布 SHA，避免覆盖发布后的人工修改，再停止 8787、逐文件恢复并重建空 runtime。它不会恢复旧会话，也不会操作 8793。部署权限隔离和原主题搜索缺页仍按上文边界保留。

发布后 `release/protection-verification.json` 已核验：2618 个原主题/附件/静态文件与原 SQL 摘要保持，相关目录无新增文件；118 个发布文件与最终清单一致，Composer 锁未变，`.env` 为精确追加密钥路径，Git index SHA 与发布前相同。隔离 8796 和本任务 MySQL 容器已停止，候选、测试库及证据保留以供复核。
