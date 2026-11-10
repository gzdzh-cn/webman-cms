# 目录与运行架构

2026-11-10 统一系统资源已备份发布8787并完整停启：686个共享实体迁至public/admin-assets/common，template/_shared实体不存在；2132实体完整性、10656新旧URL隔离HTTP、三缓存模式/五主题及运行站只读复验通过。当前default/eyou/开发模式2、私有配置、业务、原会话代际及Git暂存保持。浏览器夹具同主机Cookie污染已恢复原有效会话，实际进入后台；准确范围和回退见[统一资源验收](unified-system-assets-acceptance.md)。

2026-11-10 系统资源统一：公开系统资源统一在 `public/admin-assets`，共享资源迁至 `public/admin-assets/common`；`template` 仅存主题，禁止创建 `_shared` 实体或软链接。全部登记旧地址保留逐文件别名，动态语言 JS 继续使用文件／Redis 派生缓存，不写公开目录。format=2 保持，当前 revision 为 `2026-11-10-unified-system-assets-v1`；配套包、隔离验收和发布状态见[统一资源验收](unified-system-assets-acceptance.md)。本段取代下方历史共享资源布局与旧配套包约定。

2026-11-10 本轮常用模板语法已备份发布8787并完整重启，运行站Eyou/3144/开发模式2及原管理员会话保持；只读HTTP和后台主题列表复验通过，隔离服务已停止。验收记录包含本轮隔离库资源遗漏导致的OOM中断及原库恢复，不记为无中断交付。

2026-11-10 公共模板能力新增TemplateCapabilities及TemplateRuntime：源/编译AST共用固定调用定义，支持Request.param固定路径、安全assign及受控图片/URL适配。已知会员、收藏、交易片段输出空但内部仍检查；固定旧目录诊断规则保留。独立enterprise-syntax-v1缓存版本不改变模板模式、会话代际或已发布HTML。范围、缺页、隔离验收、OOM恢复及发布记录见[本轮验收](eyoucms/template-syntax-acceptance-20261110.md)。本段更新下方“普通官网不支持内容”的已知禁用片段范围。

2026-11-10 安装器支持已有 .env：预检使用表单参数，数据库完成后同步已有数据库覆盖项并保存PHP受管块，其他配置保留；无 .env 不创建。进程环境冲突及私有文件检查保持。见[验收](install-existing-env-acceptance.md)。本段更新下方“只写固定受管块”的历史规则。

2026-11-09 Redis 缓存模式新增 `app/cms/cache` 统一存储、数据库缓存/会话代际和固定请求会话分派。web_cmsmode=3时模板、数据、语言派生缓存及会话进入Redis；故障时缓存使用请求内存，会话返回503。runtime仅允许日志、运行控制和必要锁；文件模式继续可用。`cms:cache-clear --all` 收窄为缓存和会话，不再整删runtime。当前运行站保留开发模式，配置及验收见[Redis维护](redis-cache.md)和[验收](redis-cache-acceptance.md)。本段取代下文缓存/派生JS仅放runtime及--all整删的旧说明。

2026-11-09 PHP 正式配置调整：数据库及布局统一从 config/database.php 保存值读取，支持本地 .env 存在键优先于显式进程环境；无 .env 可安装、运行与重启。新安装只写固定受管块，旧任务按版本兼容；签名密钥仍在项目外。具体隔离验收、发布状态与边界见 php-configuration-acceptance.md。

2026-11-09 企业官网安全加固已交付8787：插件、可视化和在线PHP生成关闭；模板AST/静态SQL策略、统一上传和外部密钥HMAC存储启用，后台旧缓存复用关闭，执行已认证内存快照。旧会话需重新登录。8796独立库验收、runtime重建及运行站只读复验范围、原3144搜索缺页和生产权限要求见[安全验收](security-hardening-acceptance.md)。原主题、附件、SQL及Git暂存保留，8793不动。

2026-11-09 当前FORMAT3安装器由InstallerTableIdentity定位真实表选项；完整schema SHA继续保留，自增恢复意图先持久化、后ALTER并精确复核，沿用双安装锁。仅安装任务自有表可进入该流程，原冲突表仍严格检查；冻结V2未变。验证和实际发布状态见[安装恢复验收](install-resume-acceptance.md)。

2026-11-09 有限整改：兼容Session只使用Webman传输，Request在请求上下文中保留旧惰性快照；不使用原生PHP会话。正文解析按模型字段类型，不凭字符串外观反序列化。DatabaseBackup的cleanup为任务专属预检/显式确认/归属与SHA复核，意图持久化后禁止原任务回退；仅清已完成前镜像或已回退暂存，不自动清理。MySQL8摘要只规范化COLLATE隐含的冗余列字符集。实际验收和仍开放的恢复、容量与浏览器分支见[本轮记录](enterprise-final-acceptance.md)。

2026-11-09 兼容Request的环境变量存在性检查按原版区分惰性首次读取与原始存储读取；状态仍位于请求memo，不修改进程环境或后台登录。范围见[有限验收](eyoucms/request-env-finite-20261109.md)。

2026-11-09 域名和静态目录：首页index.html只作为已生成规范目录首页的受管别名，共用原manifest/所有权/SHA/模式及站点签名；精确记录无效不回退，无生成结果不造动态入口。目录URL发布检查对应index.html；不新增m目录路由。兼容Request保留原域名语义，手机域名运行策略显式规范化Host。见[本批有限验收](eyoucms/route-static-finite-20261109.md)。

2026-11-09 企业官网补齐增加 ContentAccessService、HtmltextProvenanceService、ArchiveWorkflowService、CategoryBatchService 和 EnterpriseToolsService，后台仍由app/cms/admin以原URL/权限身份接入；角色范围、来源及幂等任务复用CMS持久状态表，不新增业务表。DatabaseBackup/DatabaseTraffic提供项目私有备份及参与锁的HTTP/生成维护门禁；robots只写固定文件，图片策略只作用新操作。正常runtime不保存有效来源或备份。隔离验收、共享数据库OOM事件、发布状态与剩余严格兼容边界见[本批验收](enterprise-completion-acceptance.md)；下方历史未安装/旧库名不授权重装。

2026-11-08 编辑语言菜单、同步任务窗口和城市分站UI已细化，继续由现有LanguageAdminContext与CitysiteService提供业务。城市整页编辑入口显式投影原select/save权限；语言切换检测嵌套页面在途写入。验收与既有城市服务边界见[本批记录](language-city-ui-acceptance.md)。

2026-11-08 后台UI统一已完成隔离8789验收并交付8787，用户正常登录后的42个导航只读复验通过。`admin/config/ui.php`与`admin/support/UiDefinition`维护业务列、表单及窗口配置，`CmsDataController`原index以展示参数承接复杂整页编辑，保存仍走原接口和权限身份。`AdminView`接入共用CmsUI；自研源码、压缩产物及恢复清单成组维护。未新增业务表，未改变安装进度、账号、权限身份或原主题。规范与准确发布状态见[后台交互规范](backend-ui-guide.md)及[验收](backend-ui-acceptance.md)。

2026-11-08 多语言改造已交付8787并完成正常登录后的页面复验。`LanguagePolicy`统一总开关、主语言和公开语言；`LanguageAdminContext`统一后台编辑语言及旧提交拒绝；`LanguageAccessService`补充角色语言范围。数据库译文及私有JSON为有效来源，缓存和派生JS只放runtime；语言包操作目标独立于后台编辑语言。复用CMS持久状态记录预检、映射、翻译状态及恢复资料，不新增业务表。运行库webman_cms只补缺失总开关并保持关闭，未执行内容覆盖。开关位置、同步边界与发布证据见[多语言维护](multilingual.md)及[验收](multilingual-acceptance.md)。下方其它库名及交付记录属于历史，不授权重装；8793与原EyouCMS未修改。

2026-11-08：安装交接新增真实阶段动画、独立完整重启及本次安装浏览器的一次性管理员登录。持久状态在私有 config，普通登录共用 AdminLoginSession；资格不随 runtime 重建。当前8787已安装，数据库为 webman_new3，历史“待安装”段落不可用于触发重装。本轮不代为重装、不改原库或8793。见[安装交接验收](install-handoff-acceptance.md)。

2026-11-08：安装数据规则已更新为四种组合，详见[安装说明](installation.md)与[本轮验收](install-policy-v3-acceptance.md)。EyouCMS 必须上传 SQL，初始化控制源数据／仅结构，主题由无数据库扫描发现并选择；下方旧交付段落为历史。

2026-11-07：双安装模式、单前缀及精确冲突重装已完成隔离验收并交付8787；开发站仍待用户安装。原库、环境、主题／上传／静态文件及Git索引保持，8793未操作。当前规则取代下方历史安装描述；实际范围见[本轮验收](install-dual-mode-acceptance.md)。

2026-11-07 EyouCMS 安装入口及文件锁规则已交付8787，原版库识别、HTTP新装和runtime重建隔离验证通过；现有数据、资源及归档保持。本文安装章节取代上一轮“HTTP/CLI 状态始终核验数据库”的描述；历史交付记录仍按原证据保留。见[本轮安装验收](install-eyou-acceptance.md)。

2026-11-07 安装引导与封存已交付8787：根安装目录为 `install_pkfvcenmwift`，`/install` 保持关闭，config保存私有封存记录。新站自动引导及封存在隔离环境验证，开发站原数据与资源保持。见[安装封存验收](install-seal-acceptance.md)。8793未操作。

2026-11-07 后台目录迁移已交付8787：`plugin/admin`实体已删除，后台页面、配置、业务与兼容身份均归`app/cms/admin`。原URL、权限和资源保留，验收范围见[后台迁移](admin-relocation-acceptance.md)。

2026-11-07 资源整理：后台发布资源集中于 `public/admin-assets`，前台共用资源在 `template/_shared/assets`；私有配置、字体和源码不公开。旧资源地址按 format=2 清单兼容。当前构建／安装／恢复流程见[资源维护](resource-maintenance.md)，历史 format=1 包只用于外部回退。

2026-11-07：开发站 8787 已完成本轮停机迁移并重启，新架构已运行。旧状态已逐项核验入库，整个 runtime 已清除并自动重建；8793 保留副本未改动、未同步。迁移后 HTTP 复查与当前管理员登录后的主要页面读取、筛选和打开／返回验收已通过。本轮架构迁移验收完成；未扩大为全部角色、业务写入或 EyouCMS 严格兼容验收。

Webman 承载 EyouCMS 1.7.7 企业官网模板、标签与后台，支持栏目、文档、单页、展示产品、图集、下载、表单、多语言和站点管理，不含会员、商城交易。相邻原版源码仅供开发对照，不是运行依赖。兼容边界见[当前交接](eyoucms/current-handoff.md)和[剩余任务](eyoucms/acceptance-gaps.md)。

## 根目录和职责

```text
webman-cms/
├── app/
│   ├── cms/          CMS 业务、兼容、安装、维护和私有资源
│   └── …             Webman 控制器、中间件、命令、进程与视图接入
├── config/           框架配置、安装锁、私有封存记录和环境适配
├── public/
│   ├── html/         生成的公开静态页面
│   ├── uploads/      上传实体
│   ├── admin-assets/ 系统公开资源（后台资源及common公共资源）
│   └── …             必要公共入口
├── runtime/          停机后可整删、按需重建的运行文件
├── support/          Webman 支持层
├── vendor/           Composer 锁定依赖
├── template/         原版主题及独立webman-default原生主题
├── install/          首次安装／明确去锁重装的页面、可信空站基线和原始资料
│                     完成后改名为 install_<12位小写字母>/，归档可删除
├── docs/             开发文档，不参与运行
└── .git、.env、Composer 文件、启动入口等必要文件
```

`install` 与 `install_<12位小写字母>` 表示安装前后两种状态，不是同时保留的两份资料。归档目标仅由 `config/cms-installer-archive.json` 精确记录；根目录无须为了运行或升级保留归档。

根目录不再设 `data`、`resources`、`scripts`、`sql`。`.git` 原位保留，不以容量限制迁出或删除；完整运行版本不继续套用精简源码包的 10MB 上限。

| CMS 内位置 | 职责 |
| --- | --- |
| `http`、`process` | 前台、异常、中间件和静态生成 worker 的实现。 |
| `admin/controller`、`admin/model`、`admin/logic`、`admin/support` | 企业后台及基础框架实现：内容、登录、账号、角色、菜单、上传、后台配置、表单工具和全局 helper；原插件类保留类型身份。 |
| `admin/api`、`admin/middleware`、`admin/exception` | 原鉴权／菜单扩展 API、请求权限处理和后台异常协议。 |
| `repository`、`service` | 查询、事务、语言、配置、模板及发布业务。 |
| `compat`、`support`、`template` | 隔离标签编译器、模型、helper 和模板渲染。 |
| `persistence` | 数据库持久状态、SHA 对象及来源引用。 |
| `install`、`database` | 安装向导逻辑、SQL 导入和后续升级迁移。 |
| `console`、`maintenance` | `php webman cms:*` 实现、状态迁移、缓存清理和资源恢复工具。 |
| `resources` | 兼容资产、拼音／版本资料、原 data 资料及资源恢复清单。 |
| `Paths.php`、`bootstrap` | 统一私有路径和运行目录重建。 |

后台由 `app/cms/admin` 自维护：`controller/model/logic/api` 为实现，`legacy/app` 和 `legacy/api` 保留原90个命名类，`view` 为65个既有页面，`config` 为模块配置。原 `plugin/admin` 实体删除，不使用软链接或目录副本，也不另建 `app/admin`。

PHP旧namespace、权限key与连接别名属于兼容身份。主路由显式登记有名旧控制器，`app/process/Http`将其识别为主应用，`AdminEntry`核验登记身份后执行原AccessControl。因此反射、static::class、关联类型与授权均继续使用旧身份。CMS实现目录没有新增自动公开URL，未知后台地址返回后台404。

`AdminView`独立定位模块页面，ThinkPHP编译缓存只存runtime/cms/admin/views；登录和403页面也使用此入口；生成器入口已关闭。模块配置通过config/cms_admin.php接入，主database/think-orm配置注册原连接别名，后台错误协议由CMS异常入口委派至后台Handler。数据库保存的品牌与外观配置仍优先。

Composer不再安装webman/admin，保留原依赖版本及上游许可证。业务插件市场、加载器、可视化编辑和 Web 端 PHP 代码生成均已关闭；`plugin\admin` 仅为后台历史身份，不代表支持业务插件。维护说明及验收边界见 [后台迁移](admin-relocation-acceptance.md)。

## 当前主题安全边界

不扫描业务插件目录的配置、路由和命令，不自动加载根目录 `plugin`／`weapp` 类；Composer 框架插件配置继续生效。在线插件、可视化及 Web 写 PHP 代码生成均关闭，旧数据不删除。主题检查显示不支持内容的位置，普通企业官网模板中的问题阻止安装和启用；仅固定 `pc|mobile/users/` 及 `system/article_pay.htm`、`system/download_pay.htm` 的旧会员支付页面作为非阻断诊断，实际渲染仍按安全策略拒绝。插件和可视化内容标签独立检查并阻断，普通目录及其子目录不豁免。

## 请求和资源边界

`config/route.php` 接入首页、原 index.php 查询、列表、详情、资源别名和 fallback。每次请求创建 CmsContext，隔离语言、城市、主题、设备、栏目、文档及分页；不把请求数据留在 worker 静态变量。CMS_BASE_PATH 支持前台部署前缀，不能据此声称前缀后台已验。

前台经站点策略、URL 与模型处理后，由 TemplateRenderer 使用隔离的 Eyou/Cx 编译器渲染；不启动原 ThinkPHP，不覆盖 Composer 的 think 命名空间。主应用禁用默认控制器路由，避免新实现目录成为额外公开入口；后台模块通过显式路由和原身份授权。

主题为 `template/<theme>/pc|mobile`；Webman模式默认webman-default，新装兼容模式按候选自动／明确选择主题，原版主题字节保留。cms_template_mode以可信后台options保存，缺项旧站按eyou；两种模式的标签及根变量严格分离，后台管理视图自身语法不切换。缺页允许启用后明确报错，不跨主题静默补页；原始错误详情受已有 `seo_native_error` 配置控制。上传唯一实体为 `public/uploads`，旧 `/upload`、`/uploads` 及带 public 前缀 URL 由兼容层解析，数据库旧 URL 无须改写，也不重建根目录别名。

Paths 统一项目路径；AssetResolver、UploadPaths 等继续校验词法路径和 realpath。应用根不是公开目录：PHP、模板源、配置、私有资源、docs 和安装 SQL 不作为公共静态文件读取。`/install/index.php` 及 `/install`、`/install/` 别名只通过专用路由提供向导；随机封存目录不会产生新的安装 URL。安装前页面请求由统一状态门控进入向导，API 请求返回明确的未安装状态；配置异常或已有站点不会因目录缺失而开放重装。

## 数据库和可删除 runtime

新安装使用一个前缀，默认wa_：CMS直接使用该前缀，后台使用固定backend_后缀（wa_backend_admins），内部状态为wa_cms_state_records等。157张CMS、7张后台、3张业务状态共167张基线；不创建或依赖cms_install_state。旧双前缀站保留布局兼容，见[数据库说明](database.md)。

生成任务、manifest、文件归属/hash、撤回记录、自动更新状态和维护回执保存在数据库；内容快照按 SHA 去重，引用按范围分开。旧 `ui_config` 数据保留但可视化编辑已关闭；登录限流也入库。管理员只存密码哈希，不依赖 `runtime/local-admin.json`。

多 worker 使用数据库锁与事务协调，公开文件原子写入。发布记录和文件不是单一数据库事务，失败须留下可恢复依据；不能把文件写成功当作整项发布成功。撤回和更新仍校验自有归属及 hash，保护外来或人工修改文件。

公开生成 HTML 固定在 `public/html`，URL 是否显示 `/html` 沿用现有设置，手机文件隔离在 `__mobile`。HTML 请求仍经 Webman 检查状态、模式、设备、签名和 hash，代理不得用 `try_files` 绕过。项目不承诺脱离 Webman 的整站静态导出。

**完成旧状态迁移后，支持停止全部 worker → 删除整个 runtime → 启动。** runtime 仅放编译／查询缓存、临时文件、会话、日志、PID 和运行锁；入口及启动器幂等创建必需目录，首次使用重建其余缓存。删除后需重新登录，旧日志不恢复；业务配置、任务、历史可视化数据和静态发布状态不丢失，`public/html` 不随清理删除或重新生成。旧实现的 runtime 在迁移前仍承载真实状态，不能提前套用此规则。

## 安装、维护和交付

`/install/index.php`提供环境、数据库、模式、上传、预检、管理员及完成步骤，`/install`和`/install/`保留别名。默认Webman空站，可勾选内置示例；EyouCMS模式必须上传SQL，初始化未选仅使用源结构及可信基础种子，选中则使用源数据。源前缀统一映射，InnoDB转换可选。InstallerThemes不依赖数据库，排除共享／软链接／非Eyou／混合主题；唯一候选自动使用，多套明确选择，无候选红字阻止，配置覆盖全部语言。可信后台options保存cms_template_mode，不允许上传站点配置覆盖；管理员密码两次输入且仅哈希保存。

入口首先检查标准目录：install不存在，或install/install.lock为普通文件时，立即关闭且不连接数据库；异常类型、链接拒绝。标准目录存在且无锁即为主动开放安装，包括已有数据库；历史本地记录、原数据库和旧归档不再额外阻止明确去锁重装。可信未完成任务按相同绑定续跑，随机归档不参加入口扫描。

预检计算完整目标表名，展示精确冲突与plan_token。上传SQL不整份执行，先校验语句、核心字段、必要种子及数据行，再完整导入任务专属暂存表；全部成功并重核原表内容摘要后，才删除精确冲突、原子发布。改前缀后的原表及同前缀无关表保留，不按前缀清理。外键等未支持依赖拒绝自动替换。

进度在config/cms-installing.json，预检在config/cms-install-plan.json，均私有且不依赖runtime。FORMAT3将数据策略、是否带数据、SQL SHA及主题文件签名绑定计划和任务；待续装FORMAT2委托冻结旧实现及原CLI参数，不能以新选项接管。阶段为loading、dropping、publishing、finalizing、database_complete和complete；DDL不承诺整体回滚。MyISAM批次中断仅重建本任务暂存表；已发布项按指纹检查不重复导入。旧记录转入私有config/cms-install-history。

管理员、角色绑定、模式和随机cms_site_signature同事务完成；站点签名续装固定，参与静态／Sitemap等状态隔离。数据库完成后写.env、本地完成记录及install.lock，再清理上传、封存，journal最后完成。配置失败仍未锁定时以原参数补做收尾；锁已写入时通过cms:install --seal核验真实核心数据、管理员、前缀、模式及任务签名后继续，不重导入。

封存为install_加12位小写字母。InstallerArchive用私有config/cms-installer-archive.json记录唯一目标、完整SHA/大小、目录与prepared/complete阶段；中断沿用原清单和目标，不扫描、不覆盖。原EyouCMS的20位混合后缀与本项目是明确差异。随机目录及SQL禁止公开，安装归档和docs均可删除，运行及增量升级不依赖它们。

cms:install --input=… --preflight先输出计划，正式安装须带plan_token；--seal --check只读预演。cms:database-init仅保留空站基线只读预演，apply写入停用，避免绕过模式和冲突流程。创建新安装包仅在独立副本恢复标准install并清理副本凭据、锁和进度，不能修改源站归档，见[安装说明](installation.md)与[源码包](source-package.md)。

新工具统一使用 `php webman cms:*`；恢复资产及独立部署的 Python 工具在 `app/cms/maintenance`。外部快照和验收产物不回填 runtime。详细流程、停机和回退见[部署](deployment.md)、[维护归档](maintenance-archive.md)。

当前隔离检查覆盖 58 个原入口契约、PC/mobile 86 次响应、108 个静态资源 SHA 和 14 个私有／后台保护入口；随机验证码另核对有效图片响应。8787已完成停机切换、状态入库及runtime整删重建，最终HTTP和当前管理员登录后的主要页面验收已通过；上述证据不替代完整业务和EyouCMS严格兼容验收。

## 后台进一步归类的验证与边界

本轮87个原插件类、4个helper公开／受保护契约及65个视图SHA保持；原模型表名、连接、关联类型和权限key保持。五组权限身份及导航投影使用确定性内存fixture对照，13个中间件分支验证授权拒绝、成功提交与失败警告语义；这些结果不冒充真实角色登录或业务保存。当前管理员原会话在8787重载后仍有效，浏览器已读取管理员、角色权限树、菜单、外观、附件、个人资料，新增角色表单只打开并关闭，没有提交。

历史记录：当时Composer应用托管钩子保护后台接入文件，后台插件市场直接解压ZIP不经过该钩子；现行版本已关闭插件市场，以下旧结论不适用于现行入口。本轮未改变该安装渠道或执行插件安装，不能把Composer对照推广为全部更新渠道都不会覆盖自管后台。详细记录见[当前交接](eyoucms/current-handoff.md)。

历史段落的plugin目录与87类统计描述此前阶段；当前目录与90类身份以本文新结构及后台迁移验收为准。
