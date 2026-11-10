# 项目协作规范

本文件只保存现行、长期有效的规则。环境状态以现场配置及实际进程为准，不依据历史库名、主题、端口或安装记录执行变更。任务涉及哪个模块，再读取对应文档。

## 项目与保护边界

- 使用 Webman 承载 EyouCMS 1.7.7 企业官网；原版源码仅供对照，不是运行依赖。会员、收藏、支付、业务插件、可视化和 Web 端 PHP 代码生成保持禁用。
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
| `app/cms/resources/` | 私有源码包、资源清单、默认资料和构建输入；禁止公开访问。 |
| `app/view/cms/system/` | 系统内置模板，属于私有源码；不放入 public。 |
| `config/` | 框架配置、安装记录和私有任务状态；不存业务代码或公开资源。 |
| `template/<theme>/pc\|mobile/` | 各套主题及其自带 CSS、JS、图片；template 下只存主题。 |
| `public/admin-assets/` | 全部系统公开资源；后台沿用 css/js/images/vendor，系统共用资源放 common。 |
| `public/uploads/` | 上传文件唯一受管实体位置；旧 upload/uploads URL 通过兼容层解析。 |
| `public/html/` | 受管生成 HTML；不混入模板源、源码、配置或测试产物。 |
| `runtime/` | 可重建的缓存、会话、日志、运行控制及必要锁；具体写入遵守缓存模式。 |
| `install/` 或可信记录指定的 `install_<12位小写字母>/` | 安装前后两种状态，不能为运行重建第二份安装资料；初始 SQL 随安装资料封存。 |
| `docs/` | 开发说明、交接和验收记录，不参与运行。 |
| `support/`、`vendor/` | Webman 支持层与 Composer 锁定依赖；不直接修改 vendor。 |

- 根目录仅保留上述职责目录、Webman 标准入口及必要项目文件；不得新建根 data/resources/scripts/sql、app/admin、plugin/admin，或通过软链接恢复旧布局。
- 不创建 `template/_shared`、根 pc/mobile、旧 public/admin/component/plugins/static/config/errpage 等资源实体或别名目录；历史 URL 只通过逐文件资源映射兼容。现有用户备份和未知目录不得据此自动删除。
- 新文件先匹配现有职责，禁止把系统资源塞进主题、把业务数据放进 runtime、把私有源码放进 public。目录调整必须同步路径适配、资源清单、构建/安装/恢复流程及文档，并验证旧 URL。
- 路径复用 `app\cms\Paths`、UploadPaths、ThemeCatalog 和 AssetManifest；不散落硬编码路径，不以目录别名或副本掩盖依赖。
- Node 依赖、构建缓存、测试副本、数据库备份、截图和临时实验工具放项目外独立工作目录；不写入应用根或 runtime。

## 模板、资源与后台边界

- 每请求新建 CMS 上下文，隔离站点、语言、设备、主题、路由和标签变量；不把请求数据留在进程静态变量。
- 保留隔离编译器，不启动原 ThinkPHP、不覆盖 Composer 的 think 命名空间。模板模式按可信设置读取，安装后不在线切换；缺页明确报错，不跨主题静默补齐。
- 源检查与编译检查共用能力规则；原生 PHP、任意调用、动态 SQL、插件及可视化标签继续拒绝。已知会员/收藏/交易片段输出空，内部危险语法仍检查；受限内容不得因此公开。
- 缓存和生成产物身份包含模板模式、编译版本及站点签名，并保留语言、主题、设备隔离；修改编译语义同步提升独立编译版本。
- 系统资源逐文件登记 canonical/旧 URL；组件 HTML 只按白名单公开，PHP、模板源、配置、安装 SQL 和 docs 不公开。清单、构建输入、产物 SHA 与配套包须一致，不混用新旧布局的包。
- 后台仅走显式路由登记和原权限身份；保留 `plugin\admin` 兼容类名及连接别名，不恢复插件实体、自动发现或 webman/admin 依赖。菜单可见性不授予接口权限，后台 UI 沿用统一交互规范。
- 生成页面必须经 Webman 核验签名、归属和状态，不能用代理 try_files 绕过；数据库持久化与公开文件发布分别验证。

## 配置、缓存与持久化

- 配置优先级为本地 `.env` 存在键 > 显式进程环境 > PHP 配置。Redis 参数按请求重读并验证，进程环境或扩展变更需重启；不输出连接密码。
- 三种缓存模式保留：运营1、开发2、Redis3。Redis 模式的模板、数据、语言派生缓存与会话进入 Redis，不创建 runtime/cms、runtime/views 或文件会话；runtime 仅保留日志、控制文件和必要锁。
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
