# 首次安装识别与安装资料封存验收

日期：2026-11-07。范围限于首次安装引导、安装状态判断、安装目录封存和相关维护接入；不关闭 EyouCMS 严格兼容或其他后台功能未完成项。

## 交付状态

8787于2026-11-07 17:18停机部署本批19个文件并重启，原安装资料封存为 `install_pkfvcenmwift`，3个文件的封存前后SHA完全一致。`config/cms-installer-archive.json` 为complete；重复 --seal 和 --check 通过，未再次改名。8793保留站点未操作，8788／8789隔离服务已停止。

切换后只读核对168张表、9826行及表结构与原快照完全一致；账号、密码哈希、角色与权限未改。模板1408、上传153、后台资源1383个文件保持，public/html原无实体，本次未创建；Git索引原SHA保持，没有stage/reset/commit。隐藏Finder元数据沿用基线单列口径，安装资料封存本身无排除项。

开发站首页、PC/mobile及匿名登录HTML通过HTTP检查，安装入口403、随机目录及其SQL／私有记录404。浏览器原管理员会话仍有效，重载后工作台统计、站点信息和服务器信息正常显示；初次重载曾出现工作台iframe空白，再次正常刷新后恢复，未查明原因且未改动原后台JS，不能据此宣称消除所有初始化竞态。未执行真实业务写入或新一轮验证码登录。

发布及保护证据：`live-cutover/result.json`、`live-cutover/http-health.json`、`verification/live-database-after-cutover.json`、`verification/live-protected-after-cutover.json`、`verification/live-after-cutover-freeze.json`。浏览器截图为`verification/live-install-closed.png`与`verification/live-backend-after.png`。

## 实现

- `InstallationGate` 在 CMS 上下文及后台鉴权之前处理安装状态。新站首页、后台普通页面进入 `/install`；POST、JSON／XHR、已登记的查询 API 和前台 API 返回明确的未安装 JSON，不转发原提交内容。
- 安装状态读取 `.env`、配置目录中的安装锁／进度和数据库标记。未配置、目标库不存在、空库允许安装；属于当前进度的中断允许续装；未知业务库拒绝覆盖；连接错误不开放安装入口。常规已安装页面使用匹配配置的本地锁，安装入口和封存命令进一步连接数据库核验。
- 安装完成先保存环境配置、数据库完成标记及本地完成锁，再将 `install` 同级改名为 `install_` 加 12 位安全随机小写字母。SQL、隐藏文件和空目录均包含在封存核验范围。
- `config/cms-installer-archive.json` 记录确定目标、逐文件 SHA／大小及 prepared/complete 进度，权限为 0600；与安装共用互斥锁。恢复沿用同一目标，冲突或字节变化拒绝继续。
- 数据库已完成但写锁／改名失败时返回“已安装、封存待处理”，不重做数据库。`php webman cms:install --seal --check` 只读预演，`--seal` 验证实际库和前缀后收尾，必要时补写丢失的本地锁。
- `/install` 安装后关闭，随机目录不是 Web 入口。HTTP 不暴露随机名称、SQL、归档记录或私有源码。Web 向导不扫描归档；数据库初始化维护仅读取精确记录、SHA 匹配的资料。日常升级不依赖资料。

## 隔离证据

证据和回退资料位于项目外：

```text
/Volumes/disk/site/php/dzhcms/webman-cms-preserved-20261107-v1/install-seal-20261107-v1/
```

| 场景 | 证据及结论 |
| --- | --- |
| 文件系统故障与重试 | `verification/installer-archive-results.json`：权限不足、源／目标冲突、SHA 变化、软链接、各封存中断状态、重复执行均覆盖；实际发现并修复纯数字文件名清单恢复问题。 |
| 数据库与 CLI | `installer-service-results.json`：未配置、空库、未知表、可信／不可信进度、数据库完成但无本地锁、错误前缀及连接失败；CLI 空站安装自动封存，重复安装拒绝。 |
| 并发与最终写锁失败 | `installer-lock-results.json`、`installer-local-lock-results.json`：独立 PHP 进程锁争用；数据库完成后注入本地锁写入失败，正常 seal 恢复，数据摘要保持。 |
| 真实 HTTP 新站 | `installer-http-results.json`：真实 Cookie／CSRF 的 check→install，自动封存、重复拒绝、原入口关闭、随机目录拒绝；停全部 worker、整删 runtime 后仍正常；移除本地锁及归档后数据库仍阻止重装。PC/mobile 首页响应 SHA 与各自基线一致。 |
| 已安装站点克隆 | `existing-isolated-seal.json`：克隆库封存预演、实际封存、重复执行、PC/mobile 200、真实 `/app/admin` 登录 HTML 200、安装入口 403、归档／记录 404。 |
| CLI／部署接入 | `install-adapter-results.json`、`install-deployment-results.json`：安装接口不泄露归档目录、CLI 选项互斥、安装状态读取、部署精确目录／隐藏文件／空目录及资料删除适配。未运行硬编码 8793 的完整旧部署演练。 |

HTTP 新站记录中的 `/app/admin/account/login` 是登录 JSON 动作，其 SHA 比较不能作为登录 HTML 证据；登录 HTML 另由已装克隆 `/app/admin` 及开发站核验。首次向导与关闭页有实际浏览器截图；新管理员由隔离 HTTP 集成测试创建并核验密码哈希，没有冒充浏览器完成验证码登录。

首次 API 分类复核发现裸 GET 查询接口会返回 302，已补已登记读接口及前台 API 识别；修复后请求级和真实HTTP各25项通过，见`installer-gate-regression-fixture.json`与`installer-gate-regression-http.json`，保留首次反例及dict/get多余路径段反例。检查数量不表示模板兼容率。

## 保护范围和回退

发布前保存完整数据库快照、原 Git 索引、原安装资料和本批代码。发布只允许候选与基线之间的业务代码／说明差异，不部署隔离 `.env`、隔离数据库锁、runtime 或用户内容。

封存前后单独核验实际安装树；相对本轮源码基线，安装向导 `index.html` 新增封存待处理提示，属于明确代码变更；同级改名本身不改变任何文件，原始 SQL 保持原字节。

失败时先停 8787，在确认归档与封存清单一致且原 `install` 不存在后改回原名，再恢复本批代码及原状态文件，最后启动。不得覆盖新的上传、业务数据、主题或 Git 索引。冲突时保留双方并人工核对，不删除其中一方。

全新安装包使用标准 `install/`，只能在独立打包副本移除源站凭据和安装状态。运行站归档可删除；私有记录保留在 config，runtime 可按停机清理规则全部重建。
