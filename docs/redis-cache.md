# Redis 缓存模式

后台「基本设置 → 核心设置 → 网站模式」提供 `1` 运营、`2` 开发、`3` Redis 缓存模式。Redis 模式沿用运营模式的业务缓存规则；模板、数据、语言字典、派生语言 JS 和登录会话存入 Redis。网站模式由管理员配置并主动启用，维护时保留当前已选模式。

## 配置与启用

连接配置统一在私有 `config/manifest/config.yaml` 的 redis 区段；`config/cms_cache.php` 仅为框架适配。源码 PHP 必须安装 Redis 扩展才能启用该模式，二进制内置扩展；安装向导与文件模式不依赖 Redis 服务。优先级为本地 `.env` 存在键、显式进程环境、YAML、内置默认值。空密码及数据库编号 0 均是显式覆盖，不返回密码。没有 `.env` 时直接配置 YAML 即可。

| 环境变量 | PHP 键 | 默认值 |
| --- | --- | --- |
| `CMS_REDIS_HOST` | `host` | `127.0.0.1` |
| `CMS_REDIS_PORT` | `port` | `6379` |
| `CMS_REDIS_USERNAME` | `username` | 空，支持 Redis ACL |
| `CMS_REDIS_PASSWORD` | `password` | 空 |
| `CMS_REDIS_DATABASE` | `database` | `0` |
| `CMS_REDIS_TIMEOUT` | `timeout` | `0.5` 秒 |
| `CMS_REDIS_READ_TIMEOUT` | `read_timeout` | `0.5` 秒 |
| `CMS_REDIS_PREFIX` | `prefix` | `cms:` |

Redis 数据库编号由 `.env` 的 `CMS_REDIS_DATABASE` 控制；未配置环境覆盖时使用 YAML 的 `redis.database`，默认 `0`。编号从零开始：`0` 为第一个库、`1` 为第二个库、`2` 为第三个库。例如选择第三个库：

```dotenv
CMS_REDIS_DATABASE=2
```

YAML 的库编号使用整数；同名环境项 `CMS_REDIS_DATABASE` 仍优先：

```yaml
redis:
  database: 2
```

编号必须为非负整数，且所选库必须由实际 Redis 服务支持。保存前在指定库中执行连接和临时键验证，失败不会切换模式；模板、数据缓存和会话均使用此库。参数修改同样在线生效；已经启用 Redis 时更换数据库，应先切回文件模式再切入 Redis，使原登录统一失效。

超时必须大于 0 且不超过 30 秒。前缀允许字母、数字、冒号、下划线和短横线，长度 1–100。Redis 每个请求读取并固定一份最新私有配置，连接参数变化后各 worker 在下次使用时重建连接；同请求的缓存与会话始终使用同一配置。后台接口不返回连接密码。

启动时没有 Redis 配置也可正常使用运营或开发模式。后续在 `.env` 补填 `CMS_REDIS_*` 或修改 YAML 的 redis 区段，后台直接选择 Redis 模式保存即可。保存前读取最新文件并验证连接、临时键读写、过期和删除能力，失败保持原模式；修正后可直接重试。YAML 不经 PHP OPcache，连接参数变化后按请求快照重连。修改进程环境或源码 PHP 扩展仍需完整重启。迁移 Redis 实例或库编号时，先切回文件模式，再修改配置并切入 Redis，以统一使原登录失效。

管理员在主语言下保存共享模式；仍要求拥有设置及所有目标语言的原有权限。切入 Redis 前用随机短期键验证连接、读写、TTL 和删除，失败保留原模式。切入或切出 Redis 会使本站旧登录失效，保存接口返回 `session_reset_required=true`，页面提示后跳转登录。

```sh
php webman cms:cache-mode --check
php webman cms:cache-mode --set=3
# Redis 故障时，可直接恢复运营或开发模式：
php webman cms:cache-mode --set=1
php webman cms:cache-mode --set=2
```

`--check` 检查失败返回非零退出码；不输出密码。`--check` 与 `--set` 互斥。模式变更要求语言、配置、CMS 状态表使用 InnoDB，模式与代际同事务提交。

## 存储、清理与恢复

缓存接口在 `app/cms/cache`，提供文件、Redis 和请求内存实现。键隔离部署目录、数据库、表布局、站点签名、模板模式、编译版本、类别及既有语言、主题、设备等维度。数据反序列化白名单、格式及过期检查继续生效；无需本地 key。运营与 Redis 模式均在依赖未变时复用前台编译结果，复用前验证依赖路径、SHA 和编译 AST；后台继续强制编译。动态语言 JS 使用请求内生成字节响应，原 URL、ETag、304 和 HEAD 行为保留。

缓存与会话代际存于现有 `cms_state_records` 的 `cache-policy/epochs`，不新增业务表。请求固定模式与会话代际，即使同请求切换 CMS 渲染上下文也不会重新选存储。清理通过数据库代际失效，不执行 `FLUSHDB`、`FLUSHALL` 或跨站扫描。没有显式 TTL 的 Redis 派生缓存保留一天，旧代际按 TTL 自动退出；显式 TTL 沿用调用方值。文件实现同样对未指定 TTL 的缓存设一天上限，按读取和写入抽样回收过期条目。

后台清缓存和内容更新调用同一代际机制，保留登录。CLI 在停止本站 worker 后执行：

```sh
php start.php stop
php webman cms:cache-clear        # 本站缓存失效，保留会话
php webman cms:cache-clear --all  # 同时使本站会话失效
php start.php start -d
```

CLI 只移除已知缓存目录；`--all` 另清本站文件会话。日志、PID/status、必要锁和其他持久状态不在删除目标中；旧文档中「`--all` 整删 runtime」的描述由此规则替代。已存在的旧文件缓存不因选择 Redis 自动删除，可在停机时运行清理命令。

Redis 缓存读写失败，本请求直接查数据库并使用内存编译，不落文件缓存；下个请求重新尝试连接。失败的缓存变更轮换数据库缓存代际，恢复时不能读回旧值。会话读写失败返回明确 503，不回退文件，也不作为普通未登录处理；失败的会话写入或注销轮换会话代际，防止恢复后旧登录复活。故障日志每 worker 限频一分钟，不含键、会话或凭据。已验证编译字节在请求内固定，校验后断线仍可完成执行。

Redis 模式正常访问不新增或更新 runtime 缓存和会话文件。runtime 仍允许日志、PID/status、监控与必要文件锁。数据库业务配置、生成任务和发布记录继续入库，生成 HTML 和上传文件保持原位置。

应用启动入口不再创建 `runtime/views`，Windows 入口同步去除该初始化；bootstrap 也不再执行事后删除。模板编辑所需文件锁按需放在 `runtime/locks/template-files`，因此 Redis 模式启动、状态查询、重载、重启、正常页面访问及模板编辑均不需要创建 `runtime/cms` 或 `runtime/views`。其他模式仍按原规则使用 CMS 文件缓存；历史目录和文件不会被此初始化自动删除。

启动适配位于 `app/cms/secure/App.php`，保留锁定的 Webman Framework v2.1.4 `support\App::run()` 流程，仅去掉默认视图目录初始化，继承关系和安全配置加载保留。升级框架时应核对并同步启动流程。必要锁路径变更交付时停止全部旧 worker 后再启动，避免两版本并用不同锁路径。

本轮隔离验证、实际覆盖范围与交付保护记录见 [Redis 验收](redis-cache-acceptance.md)。
