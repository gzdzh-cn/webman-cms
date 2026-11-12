# 移除本地 key 与模板缓存一致性验收

日期：2026-11-11。范围为移除 CMS 本地签名 key 机制，并统一运营与 Redis 模式的前台模板编译缓存规则。不新增替代密钥，不修改模板语法版本、主题、缓存模式或已发布 HTML。

## 实现与存储边界

- 删除 SignedEnvelope、SignedSessionHandler、security-key.php 及 cms_security.php，替换为无签名的 Webman 会话存储。配置定义、安装、运行检查和二进制打包不再要求 key。旧 YAML key_file 与旧环境项仅兼容忽略及提示，可删除。
- 缓存使用 storage-v2，文件及 Redis 会话使用 session-v2；旧登录一次性失效。保留数据库管理员登录注册、随机 nonce、撤销、TTL、存储切换和故障失效机制。安装匿名交接仅允许三个固定格式字段，不能携带管理员身份。
- 前台运营1、Redis3复用依赖有效的编译结果；开发2每次编译；后台每次从可信源编译。保留严格依赖注释、路径/别名/SHA检查、编译 AST 和执行内存快照。
- 数据缓存仍使用固定反序列化白名单；语言字典与数据库结果核对，语言 JS 只响应本次生成的字节。分页保存版本化的受控参数描述，通过固定标签查询重算，不执行存储 SQL。
- 备份 format=2 使用 SHA256 检查意外损坏，保留表归属、分卷、固定 DDL、内容指纹及任务检查。旧 HMAC 包明确拒绝，须使用配套旧版本处理。恢复/回退后的缓存和会话代际失效作为可续跑步骤记录，重复执行不重复轮换。

SHA256 不提供抗伪造认证。文件私有权限及本站 Redis 写权限是存储信任边界，模板 AST 也不能证明存储内容的真实性。本轮不更改第三方 API、TLS 等独立业务凭据。

## 隔离验收

工作目录 `/private/tmp/cms-no-key-20261111-VRlkoN`。独立 MySQL 8.4.8（19306）、Redis 7（19379/DB6）及两 HTTP worker（18878）；未以用户 Redis、运行库或8793作为测试环境。全部测试脚本使用 PHP，无 Node/Python。

| 项目 | 实测与证据 |
| --- | --- |
| PHP语法 | `evidence/lint-final.json`，应用及配置 PHP 无语法错误 |
| 缓存、安全与会话 | `evidence/behavior.json`，42项：文件/Redis命中、依赖重编译、开发强制编译、恶意AST/依赖注释拒绝、后台不复用持久字节、数据白名单、会话数据库隔离、匿名安装交接、身份伪造及撤销、分页描述验证 |
| 现有主题 | `evidence/themes.json`：default、webman-default × PC/mobile × 三缓存模式共12组首页实际渲染；本机未提供1003/1004/3144主题，不宣称其本轮通过 |
| HTTP | `evidence/http-final.json`及`evidence/supplemental.json`：隔离管理员登录、后台文章页面和列表接口、首页/手机页、真实产品/资讯详情；语言JS新旧URL的GET/HEAD/ETag304；配置、runtime、模板源和PHP私有路径拒绝 |
| 分页 | `evidence/pagination.json`：Eyou/Webman两种语法的arclist/tagarclist片段及受控count；arclist分页控件。tagarclist后直接arcpagelist的原编译变量不一致属于既有语义，本次未扩大语法或更改编译版本 |
| Redis故障 | `evidence/redis-outage.json`及`redis-timeout-final.log`：断线及真实暂停超时，公开页200、需要会话接口503、不回退文件；恢复后旧Redis会话字节虽仍存在，旧登录401，见`redis-preserved-recovery.json` |
| Redis文件写入 | 故障测试及HTTP检查时，不存在runtime/cms、views、sessions、cache；其后文件模式测试允许产生文件缓存，不将两类快照混用 |
| 数据库备份 | `evidence/backup-lifecycle.json`：167表6006行，创建、预览、恢复、回退、清理；损坏摘要、旧HMAC、危险DDL拒绝。`supplemental.json`额外核验清理实际phase和重复恢复/回退/清理幂等性 |
| 部署预检 | `evidence/deployment-preflight.json`：2132项系统资源及PHP运行时检查通过 |

浏览器已检查隔离首页的导航、产品和资讯。新安装在独立数据库完成，未复制安装记录或临时主题到运行站。

## 发布、回退与未验收范围

2026-11-11 已发布46项代码/文档变更，8787旧master74397完整停止，新master70951及两个HTTP worker70952/70953启动。确切旧key已在私有备份后移除，新进程在无key状态启动成功。YAML只删除退休字段；本地.env原本不存在，未创建。发布状态和最终文件清单见本任务 `evidence/release-plan.json`、`evidence/publish.json`。

发布后只读比对通过：default主题、Eyou模板模式、开发模式2、数据库连接身份、站点身份、缓存/会话代际及Git暂存保持。暂存索引SHA256仍为 `d2e92d1dad32e8943cc95ed8cb1ec621d96a430f97f8a36a035ca70156b52e5e`。`evidence/live-verification.json`记录首页/手机页/登录页及新旧语言JS正常、私有路径404；未登录接口沿用HTTP200及JSON业务码401。浏览器已确认后台登录页正常显示。现有登录须重新登录；原6个会话文件字节保持，新处理器不读取旧命名空间。

回退先停止准确目标，核验清单后使用项目外本任务 `rollback.php --apply` 成组恢复before内原文件、删除本次新增代码并恢复私有配置及旧key，完整启动。只回退本次文件，不覆盖业务数据、模板、上传或整份runtime；发生后续业务变更时先保留新状态并评估。私有旧key仅为配套旧版本回退保留，不被当前程序读取。

`evidence/build-inputs.json`核验5060项打包输入包含新处理器和依赖解析器，排除旧key实现及私有配置。本轮未重新构建Linux ARM64二进制：清理后现场无锁定micro.sfx及配套构建输入。已更新打包代码和配置，但不能将源码验收视作新二进制、原生Linux或systemd验收。历史二进制验收记录不代表本次新代码成品。
