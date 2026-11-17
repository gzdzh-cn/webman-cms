# 项目协作规范

本文件只保存现行、长期有效的规则。环境状态以现场配置及实际进程为准，不依据历史库名、主题、端口或安装记录执行变更。任务涉及哪个模块，再读取对应文档。

## 项目与保护边界

- 使用 Webman 承载 EyouCMS 1.7.7 企业官网；原版源码仅供对照，不是运行依赖。仅允许 `app/cms/extension/Registry` 显式登记并随 CMS 发布的已适配插件；安装配置复用本站 StateStore，管理动作服务端强制超管。会员、收藏、支付、原版插件加载/安装、可视化和 Web 端 PHP 代码生成保持禁用。
- 保留原主题、原版源码、原始 SQL、上传、已发布 HTML、私有配置、业务数据和无关改动；不重建 `.git`，不 reset 或覆盖既有暂存。读取暂存使用 `git --no-optional-locks ls-files --stage`，避免刷新索引。
- 不依据历史状态重装、导入、切换主题/模式或重置账号。管理员只存密码哈希，密钥及凭据不得公开、提交或写入运行日志。
- 开发站通常为 8787，操作前核验目标。8793 及其项目外保留环境禁止自动同步、清理或启动；不触碰其他任务的数据、进程和备份。

## 目录架构约束

| 位置 | 唯一职责与放置要求 |
| --- | --- |
| `app/cms/` | CMS 实现根目录，按现有模块分层；不另建平行业务根目录。 |
| `app/cms/http`、`process`、`console` | HTTP、中间件、worker 和命令实现；外层 Webman 入口保持薄接入。 |
| `app/cms/admin/` | 后台控制器、模型、逻辑、权限和模块配置；页面只放 `admin/view`，经 AdminView 渲染。 |
| `app/cms/service`、`repository`、`persistence` | 业务流程、数据查询和持久状态，复用现有服务，不向入口堆业务代码。 |
| `app/cms/compat`、`template`、`support` | Eyou 适配、隔离编译/渲染及公共辅助逻辑。 |
| `app/cms/install`、`database`、`maintenance` | 安装逻辑、后续迁移和维护工具；维护入口统一为 `php webman cms:*`。 |
| `app/cms/resources/` | 私有源码包、资源清单、默认资料和构建输入；仅 admin-assets 中逐文件登记的资源允许经受控 URL 响应，其余内容禁止公开访问。 |
| `app/view/cms/system/` | 系统内置模板，属于私有源码；不放入 public。 |
| `config/` | 框架配置、安装记录和私有任务状态；不存业务代码或公开资源。 |
| `template/<theme>/pc\|mobile/` | 各套主题及其自带 CSS、JS、图片；template 下只存主题。 |
| `app/cms/resources/admin-assets/` | 全部系统静态资源实体；后台沿用 css/js/images/vendor，共用资源放 common。浏览器使用 /admin-assets/，由 AssetManifest 逐文件授权；不得整目录公开 resources。 |
| `uploads/` | 固定上传实体位置；旧 upload/uploads URL 通过兼容层解析。 |
| `public/html/` | 受管生成 HTML；不混入模板源、源码、配置或测试产物。 |
| `runtime/` | 可重建的缓存、会话、日志、运行控制及必要锁；具体写入遵守缓存模式。 |
| `install/` 或可信记录指定的 `install_<12位小写字母>/` | 安装前后两种状态，不能为运行重建第二份安装资料；初始 SQL 随安装资料封存。 |
| `docs/` | 开发说明、交接和验收记录，不参与运行。 |
| `support/`、`vendor/` | Webman 支持层与 Composer 锁定依赖；不直接修改 vendor。 |

- 根目录仅保留上述职责目录、Webman 标准入口及必要项目文件；不得新建根 data/resources/scripts/sql、app/admin、plugin/admin，或通过软链接恢复旧布局。
- 不重建 `public/admin-assets` 实体或别名，不创建 `template/_shared`、根 pc/mobile、旧 public/admin/component/plugins/static/config/errpage 等资源实体或别名目录；历史 URL 只通过逐文件资源映射兼容。现有用户备份和未知目录不得据此自动删除。
- 新文件先匹配现有职责，禁止把系统资源塞进主题、把业务数据放进 runtime、把私有源码放进 public。目录调整必须同步路径适配、资源清单、构建/安装/恢复流程及文档，并验证旧 URL。
- 路径复用 `app\cms\Paths`、UploadPaths、ThemeCatalog 和 AssetManifest；不散落硬编码路径，不以目录别名或副本掩盖依赖。
- 开发验证和临时实验使用项目外副本；生产构建默认 bin 及 bin/build-runtime，纳入忽略和打包排除。数据库备份、截图及测试夹具不得写入 runtime。

## 模板、资源与后台边界

- 每请求新建 CMS 上下文，隔离站点、语言、设备、主题、路由和标签变量；不把请求数据留在进程静态变量。
- 保留隔离编译器，不启动原 ThinkPHP、不覆盖 Composer 的 think 命名空间。模板模式按可信设置读取，安装后不在线切换；缺页明确报错，不跨主题静默补齐。
- 源检查与编译检查共用能力规则；原生 PHP、任意调用、动态 SQL、插件及可视化标签继续拒绝。已知会员/收藏/交易片段输出空，内部危险语法仍检查；受限内容不得因此公开。
- 缓存和生成产物身份包含模板模式、编译版本及站点签名，并保留语言、主题、设备隔离；修改编译语义同步提升独立编译版本。
- 系统资源逐文件登记 canonical/旧 URL；组件 HTML 只按白名单公开，PHP、模板源、配置、安装 SQL 和 docs 不公开。清单、构建输入、产物 SHA 与配套包须一致，不混用新旧布局的包。
- 后台仅走显式路由登记和原权限身份；保留 `plugin\admin` 兼容类名及连接别名，不恢复插件实体、自动发现或 webman/admin 依赖。菜单可见性不授予接口权限，后台 UI 沿用统一交互规范。
- 插件市场及后续适配插件的管理页、配置编辑必须复用现有后台布局、字号、颜色、查询区、表格、表单和反馈规范（见 `docs/backend-ui-guide.md`），不得另设视觉体系。简单配置使用 720px 标准弹窗，复杂配置按规范使用整页；弹窗分组清晰、顶栏和操作栏固定、只滚动正文，高度不超过 85vh，手机单列且操作可达。编辑提供保存、重置和取消，重置恢复最近读取或保存的配置；保留未保存确认、防重复、失败保留输入、Esc 与焦点恢复，凭据保持脱敏及独立清除操作。
- 生成页面必须经 Webman 核验签名、归属和状态，不能用代理 try_files 绕过；数据库持久化与公开文件发布分别验证。

## 配置、缓存与持久化

- 资源校验、构建、打包、恢复及部署预检统一使用 PHP CLI；不引入 Node、Python、npm 或外部压缩器。资源按受管源码原字节发布，保留版权声明并锁定摘要。
- 部署参数统一登记在 `app/cms/config/ConfigSchema.php`，外置配置按 `config.yaml`、`config/config.yaml`、`config/manifest/config.yaml` 选第一份，不合并；PHP 仅作适配。外置字段优先级为本地 `.env` 存在键 > 显式进程环境 > 所选 YAML > 默认；内置 YAML 固定且忽略全部外置部署参数，写入明确只读。Redis 参数按请求重读并验证，其余启动参数完整重启；不输出连接密码。
- 二进制关闭全部安装/交接入口且不携带安装资料；未内置 YAML、主题或系统资源必须在规定外置位置存在，启动前拒绝缺失，不创建空目录。内置系统资源、public/template 各根优先，禁止从外置补齐；构建由 build.embed_admin_assets 布尔开关独立控制系统资源，默认 true，false 关闭；build.embedded_resources 默认 [] 只选择额外内置资源，空列表不关闭系统资源，列表环境变量使用 JSON 数组；public 仅选择允许的根公开文件，旧系统资源列表条目不能覆盖开关，旧版 public 聚合元数据继续兼容读取；uploads/runtime 永远外置。构建平台与资源列表由统一定义验证。
- 包内代码使用 `Paths::code()`，外置目录使用 `Paths` 对应固定目录；不能用包内 base_path 推导可写数据目录。实体目录固定相对源码入口或二进制所在目录，禁止恢复 paths 或 CMS_*_DIR / CMS_SITE_ROOT 的自定义实体目录能力；build 输出/运行时路径和 YAML/.env 选择仍按其专用规则。新部署参数必须登记类型、默认值、环境名称、生效时机与敏感性。源码及二进制共用入口；二进制运行和重启不得依赖系统 PHP 或外部 PHP 脚本。
- 不引入本地签名密钥或替代密钥依赖；缓存/会话使用版本化存储，前台缓存复用须验证依赖与编译 AST，后台继续强制编译。私有文件及 Redis 写权限是存储信任边界；摘要不提供抗伪造认证。
- 网站模式只保留 runtime1、Redis3；旧开发2只读兼容为 runtime1，新保存拒绝2。开发行为统一由 app.debug / APP_DEBUG 控制，完整重启生效：保留原调试行为、前台模板每次编译及原开发模式对应的业务缓存规则；后台服务器详情也仅在开启时可查看。Redis 模式的模板、数据、语言派生缓存与会话进入 Redis，不创建 runtime/cms、runtime/views 或文件会话；runtime 仅保留日志、控制文件和必要锁。
- Redis 缓存故障降级为数据库查询及内存编译，不回退文件缓存；会话故障明确503，不当作普通未登录。缓存/会话代际存数据库，防止旧数据复活；普通清缓存保留会话，跨存储切换要求重新登录。
- 业务配置、生成任务、发布记录、维护回执和登录限流存数据库；生成 HTML、上传保持各自实体位置。清理只处理本站且核验归属，不恢复整份旧 runtime。
- 新装使用单前缀，保留既有布局兼容，不自动迁改旧表。安装状态以标准目录/锁及可信 journal 判断；重装只替换明确预检确认的同名表，未完成任务沿原绑定续装，不清整库或猜测归档目录。
- 安装、续装、封存和源码打包必须先读对应文档；私有 journal/归档记录在 config，打包在独立副本进行，不修改源站锁、凭据或封存资料。

## 验证与发布

- PHP ≥8.2；本机显式使用 `/Users/lizheng/Library/FlyEnv/env/php/bin/php`。按改动运行必要语法、行为和集成检查，不能用 HTTP200 代替页面或业务验收。
- 业务测试使用独立数据库、Redis和部署，设置资源上限；数据库操作串行，不写运行库夹具，不复用用户会话。并行工作修改共享文件前须协调。
- 发布前备份本次文件并核验前镜像，按明确清单部署；涉及预加载 PHP 类或路径迁移须完整重启准确目标，不能以 reload 代替。无运行代码变更的文档修改不重启服务。
- 保留失败、未验证范围及证据来源；兼容任务ID、既有证据索引和SHA不重写，不以检查数量推算兼容率。目录整理不关闭历史兼容缺口。

## 按需文档与维护方式

- 架构：[目录与运行架构](docs/architecture.md)；资源：[维护规则](docs/resource-maintenance.md)、[统一目录验收](docs/unified-system-assets-acceptance.md)。
- 安装：[安装说明](docs/installation.md)、[FORMAT3策略](docs/install-policy-v3-acceptance.md)、[续装恢复](docs/install-resume-acceptance.md)、[源码包](docs/source-package.md)。
- 缓存/配置：[Redis](docs/redis-cache.md)、[配置规则](docs/php-configuration-acceptance.md)；安全：[安全边界](docs/security-hardening.md)。
- 模板：[常用语法验收](docs/eyoucms/template-syntax-acceptance-20261110.md)；后台：[UI规范](docs/backend-ui-guide.md)；多语言：[维护说明](docs/multilingual.md)。
- 交接与待办：[当前交接](docs/eyoucms/current-handoff.md)、[路线图](docs/eyoucms/roadmap.md)、[验收缺口](docs/eyoucms/acceptance-gaps.md)。历史描述不构成当前环境状态或操作授权。
- 本文件只在长期规则变化时更新，直接修改对应条目；修复经过、测试数量、部署时间和临时分工写入交接或专项验收，不再逐次追加到文件顶部。
- 精简前全文见[历史快照](docs/agents-history-20261110.md)，仅供追溯，不作为现行指令，不要求每次任务读取。
