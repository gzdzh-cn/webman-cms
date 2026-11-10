# AGENTS.md 精简前历史快照（2026-11-10）

本页完整保留精简前原文，仅供追溯，不是现行项目指令。旧环境状态、临时分工、验收结论与授权不得直接用于当前操作；现行规则见项目根 AGENTS.md。原文中的相对路径以项目根为起点。

原文件 SHA-256：`bffbd879446cbdbe77ee042e1854c0167c2fd3f67fe68bd94a0b1c2a5b4ff222`。以下代码块内为原始文本，包含互相覆盖的历史描述，不再追加更新。

````markdown
# EyouCMS 企业官网兼容项目协作规范

2026-11-10 统一系统资源已备份发布8787并完整停启：686个共享实体迁至public/admin-assets/common，template/_shared实体不存在；2132实体完整性、10656新旧URL隔离HTTP、三缓存模式/五主题及运行站只读复验通过。当前default/eyou/开发模式2、私有配置、业务、原会话代际及Git暂存保持。浏览器夹具同主机Cookie污染已恢复原有效会话，实际进入后台；准确范围和回退见[统一资源验收](docs/unified-system-assets-acceptance.md)。

2026-11-10 系统资源统一：公开系统资源统一在 `public/admin-assets`，共享资源迁至 `public/admin-assets/common`；`template` 仅存主题，禁止创建 `_shared` 实体或软链接。全部登记旧地址保留逐文件别名，动态语言 JS 继续使用文件／Redis 派生缓存，不写公开目录。format=2 保持，当前 revision 为 `2026-11-10-unified-system-assets-v1`；配套包、隔离验收和发布状态见[统一资源验收](docs/unified-system-assets-acceptance.md)。本段取代下方历史共享资源布局与旧配套包约定。

2026-11-10 恢复uploads后的广告占位：TagAdv改缓存原始行，逐次检查图片并转换展示属性，raw-v2键隔离旧加工结果。备份单文件发布并完整重启8787，11项隔离回归、双端重复首页及图片GET、浏览器轮播复验通过；配置/原主题/业务/暂存保持。见docs/eyoucms/template-syntax-acceptance-20261110.md末节。

2026-11-10 官方default首页空图500：TemplateRuntime仅对AssetManifest生成的精确默认无图版本URL原址输出，保留其它路径/参数边界。备份部署单PHP并完整重启8787，51项定向检查、7项实际HTTP和浏览器首页复验通过；现场主题/模式/私有配置/Git暂存保留。证据及缺页独立边界见docs/eyoucms/template-syntax-acceptance-20261110.md末节。

2026-11-10 本轮常用模板语法已备份发布8787并完整重启，运行站Eyou/3144/开发模式2及原管理员会话保持；只读HTTP和后台主题列表复验通过，隔离服务已停止。验收记录包含本轮隔离库资源遗漏导致的OOM中断及原库恢复，不记为无中断交付。

2026-11-10 常用模板语法兼容：公共TemplateCapabilities/TemplateRuntime统一固定Request.param、安全assign、受控thumb_img/url及展示文本；已知会员/收藏/交易标签禁用且内部仍扫描，不阻断普通官网。此项更新下方旧“不支持内容一律阻断”的范围，原生PHP/插件/可视化仍拒绝。独立编译版本enterprise-syntax-v1，不改模板模式、会话代际或已发布HTML。四套原主题官网155文件加Webman52文件编译通过、102项完整HTTP及缓存/故障/浏览器验收完成；缺页和占位保留。发布及本轮隔离库引发OOM后的恢复事实，以docs/eyoucms/template-syntax-acceptance-20261110.md为准。

2026-11-10 文章列表栏目筛选：ArchiveListInput仅对Archive GET select的typeid in表达式做权限检查ID投影，语言及内容范围中间件共用，写操作和查询请求不变；修复左侧栏目点击导致403/右侧请求异常。33项边界及8787实际新闻6/父栏目6/子栏目2/切回新闻6验证通过，未写业务数据或改权限，见docs/article-list-filter-acceptance.md。

2026-11-10 安装交接修复：InstallerHandoff兼容本项目webman/start.php两种master入口；缺失记录仅原浏览器在30分钟内通过CSRF POST恢复，沿用原完成期限，不重发已消费资格。独立15项边界及14项真实HTTP检查通过；8787已从原完成页重试重启并实际进入后台，未重装，配置/安装记录/Git索引保持。见docs/install-handoff-acceptance.md首节。

2026-11-10 安装.env误报修复：主进程预加载旧Environment时reload不能获得新增方法，安装器改用独立EnvironmentParser及已有processEnvironment快照接口；取消将全部Throwable误报为格式错误。当前.env有效且未改；旧类fork回归、95项配置及12次8787只读连接失败检查通过，未提交真实安装，见docs/install-existing-env-acceptance.md。

2026-11-10 已有 .env 安装：安装使用本次表单参数，不再因本地数据库旧值冲突拒绝。新FORMAT3先完成目标库导入，再写PHP受管块并原子同步 .env 中已有的九项数据库/前缀配置；不创建 .env，不增加原本缺失的键，Redis及其他非数据库配置保留。预检只读，权限/链接/格式/进程环境冲突仍在数据库操作前校验；收尾失败保留database_complete供重试。验收见 docs/install-existing-env-acceptance.md；运行站配置、库和安装状态不代为提交或修改。

2026-11-09 后台顶栏与反馈：语言选择下拉末项为编辑语言（沿用权限），官网预览为图标，个人资料/刷新/全屏/外观/重启/退出集中管理员菜单。模式保存先动画确认；Redis 验证失败使用2秒提示及保留红字定位。重启仅超级管理员，同源CSRF、固定项目命令、独立进程、互斥及新master响应校验，不触发内容发布/缓存代际变化。8787已reload加载，未通过按钮实际重启运行站；独立8797实际HTTP/浏览器验收及准确边界见 docs/admin-topbar-acceptance.md。运行站私有配置、Redis模式和Git暂存保留。

2026-11-09 Redis 严格不预建目录：项目 secure/App::run 保留 Webman v2.1.4 启动流程，仅去掉默认 runtime/views 初始化，Windows 入口同步去掉；RuntimeBootstrap 不再事后删空 views。模板编辑锁迁到 runtime/locks/template-files，按需创建。Redis 模式启动/CLI 控制/访问/模板编辑均不创建 runtime/cms、runtime/views；文件模式 CMS 缓存规则保持。必要锁路径变更需停止全部旧 worker 后再启动，保留当前已由用户启用的 Redis 模式、会话代际和私有参数；升级框架核对启动适配。不要按下文历史开发模式切回。

2026-11-09 Redis 在线补填：连接配置按 Webman 请求固定并重读本地 `.env` 和 `config/cms_cache.php`，单独刷新 PHP 配置的 OPcache 条目，连接参数变化后各 worker 自动重连；启动后补填 `CMS_REDIS_*` 或修改 PHP 参数可直接在后台保存，无需重启。保存验证和模式/会话代际规则不变；进程环境或 PHP 扩展变更仍需重启。独立双 worker 验收见 docs/redis-cache-acceptance.md 末节；运行站配置与模式保留。

2026-11-09 Redis 缓存模式：后台核心设置新增 web_cmsmode=3，缓存与会话经固定请求策略进入 Redis；故障缓存走数据库/内存，会话明确503。缓存/会话代际在现有数据库状态表，--all只清本站缓存与会话、保留日志和运行控制文件。连接配置在config/cms_cache.php，运行站保留开发模式，由管理员主动启用。配置、真实隔离验收及边界见docs/redis-cache.md与docs/redis-cache-acceptance.md；不改8793或原主题/附件。

2026-11-09 PHP 正式配置调整：数据库及布局统一从 config/database.php 保存值读取，支持本地 .env 存在键优先于显式进程环境；无 .env 可安装、运行与重启。新安装只写固定受管块，旧任务按版本兼容；签名密钥仍在项目外。具体隔离验收、发布状态与边界见 docs/php-configuration-acceptance.md。

## 当前安全约束（覆盖下方历史描述）

2026-11-09 本轮安全加固已交付8787，旧会话统一重新登录；8796独立库/runtime重建及发布只读复验、原3144搜索缺页、生产权限边界和逐文件回退见 docs/security-hardening-acceptance.md。运行站原主题/附件/SQL/业务数据不覆盖，Git暂存保留，8793未操作。

- 关闭在线业务插件市场、业务插件配置／路由／命令扫描、`weapp` 与 `plugin` 根目录自动加载、可视化编辑和 Web 端 PHP 代码生成。保留 Composer 框架依赖及其配置、`plugin\admin` 后台历史身份，旧 `ui_config` 等数据不删除。
- 主题安装和启用扫描全部问题；企业官网模板中的不支持内容及所有不支持的插件／可视化内容标签阻断。固定 `pc|mobile/users/` 与 `system/article_pay.htm`、`system/download_pay.htm` 的其他问题仅作诊断，实际渲染仍由模板安全策略拒绝；普通目录和子目录不得豁免。
- 下方旧的插件市场、可视化迁移或生成器实施记录只作历史，不再授权恢复对应入口。禁止用现行规则推断已部署或数据库已变更。

2026-11-09 FORMAT3安装恢复：只对任务自有InnoDB表，行摘要不变且仅恢复计数能精确还原旧schema SHA时提高AUTO_INCREMENT；新检查点保存十进制实际值，旧检查点只用可信CREATE声明值证明，不改原SHA或冲突原表严格校验。修复意图先落私有journal，ALTER后精确核验；V2冻结。原OOM现场只读，新13322副本CLI恢复167表/71计数，不能冒称原现场续装。验证及8787发布状态见docs/install-resume-acceptance.md，外部writer须停写，T16/T17/大库仍开放。

2026-11-09 剩余整改有限批次：正文解序列化限定声明的imgs字段；兼容Session沿用Webman传输，补旧点号/前缀及请求快照；备份新增精确归属的手动清理与中断恢复，MySQL8摘要有限规范化。隔离8794/独立13319完成实际正文浏览器编辑、受限角色HTTP、同步多worker及四组合安装回归；正常受限角色浏览器编辑/四格式上传/审核/复制/移动补验已完成，修复编辑器三个元数据GET权限投影并显式校验当前管理员范围；image接口不创建附件目录行。全角色UI组合、浏览器失败恢复、HTTP大库、T16总体/T17及MySQL5.7崩溃后自增变化导致续装拒绝仍留项。准确发布、证据和回退见docs/enterprise-final-acceptance.md；不重装webman_cms，不改8793或原主题。

2026-11-09 Request 环境变量存在性有限修复：has(env)区分首次惰性过滤与已初始化原始来源，保持原名逐段检查，不改全局环境。102组原PHP7差分、实际双模式模板及请求上下文检查见docs/eyoucms/request-env-finite-20261109.md；T16总体/T17仍开放。

2026-11-09 域名/静态首页有限修复：兼容Request保留原域名拼写，SitePolicy独立规范化手机域名；已生成目录首页的index.html别名复用同一受管发布，不补m路由或未生成页回退。目录URL发布前检查与index.html实体一致。隔离8792/独立13319库验证，337表冷启动摘要保持；范围及真实失败见docs/eyoucms/route-static-finite-20261109.md。T16总体/T17及原40任务22条历史保持开放，8793不动。

2026-11-09 企业官网补齐已在8791/独立MySQL5.7端点13319完成有限验收并交付8787，登录后复验及剩余状态以 docs/enterprise-completion-acceptance.md 末节为准。栏目/本人范围必须按每个角色的成对授权计算；正文raw/encoded/unknown来源随正文事务保存，未知内容不批量猜测。新批量工具复用旧权限，预检和编辑语言切换不触发通用发布后处理。数据库备份私有且限定本项目格式，外部writer须停写；MySQL8未实测，不再泛扫KEY_COLUMN_USAGE。共享数据库OOM事件及复核须保留。运行库仍为现场确认的webman_cms，本轮不重装、不导入、不迁改角色或内容。T16总体/T17/旧静态别名404/未供主题仍开放；原40任务22条历史不重写。

2026-11-08 编辑语言与城市UI细化：顶栏采用显式可见菜单，复用统一语言协议；保存/上传/任务在途禁止切换。语言同步为1040px picker任务窗口，无错误整页编辑标记；城市列表/设置分开，新增编辑整页，原select+save服务端校验，未知历史值保留。隔离8790真实浏览器/HTTP与资源复现恢复验收见docs/language-city-ui-acceptance.md。本批不执行运行库内容同步或城市保存，不改账号/权限身份/原主题/8793。城市服务cn配置与按公开语言数量互斥属于既有边界，未在UI任务暗改。

## 当前状态与优先级

2026-11-08 后台UI统一已在8789独立库验收并交付8787；用户正常登录后的42导航只读复验通过。复杂表单整页，简单弹窗720px、选择1040px、确认520px，手机单列与局部表格滚动；展示配置在admin/config/ui.php与UiDefinition，CmsUI统一忙碌、未保存及返回，编辑URL不得覆盖列表恢复状态。66视图登记，三宽度页面与窗口、隔离真实业务、双模板模式及资源重建恢复已分别记录；未执行运行库业务写入，不将未覆盖的legacy编辑和浏览器上传冒充通过。见docs/backend-ui-acceptance.md。原始快照与资源包在项目外backend-ui-20261108-172923；不复制隔离配置／会话／测试账号，不修改安装进度、8793和已有权限身份。

2026-11-08 多语言改造已完成隔离验收并交付8787，用户正常登录后的功能地图、功能配置、本地语言包、栏目及文章筛选复验通过。总开关位于功能地图搜索下方、当前导航上方，保留当前关闭状态；顶栏统一编辑语言。语言列表提供指定语言的本地语言包入口，关闭总开关也可维护翻译，不改变编辑语言。系统主语言按最早语言ID、前台默认独立；角色scope及同步／语言包任务使用CMS持久状态。JSON仅离线补缺失，数据库人工值及空值优先，不执行上传PHP、不请求官方语言包。运行库仅新增可信总开关配置，未新增业务表或执行真实内容覆盖。部署前快照、回退及证据在项目外multilingual-20261108-124545；详情见docs/multilingual.md与docs/multilingual-acceptance.md。运行库为现场核验的webman_cms，历史库名不授权重装或回退。8793及原EyouCMS保持，严格兼容其它遗留项不关闭。

2026-11-08 安装蒙版：正式安装、收尾重试和重启等待使用唯一全屏 modal dialog，居中转圈与真实阶段数量；ready、失败和超时关闭，检查/预检/上传仍在页内。Esc/背景点击不可关闭，焦点及滚动受约束。三种宽度浏览器与37项合成事件检查通过；本轮仅UI，无真实重装或数据库操作，见docs/install-handoff-acceptance.md末节。

2026-11-08 安装交接：真实阶段动画、独立完整重启及首次点击免密登录已实现。协调器在app/cms/install/InstallerHandoff，CLI执行器在maintenance/install-restart.php；config/cms-install-handoff*是私有持久任务，不放runtime、不进安装包/版本库。资格绑定原浏览器任务/站点/新管理员，ready后10分钟且完成后最多30分钟；消耗后不重发，退出或清runtime后正常登录。配置/锁失败仅补收尾不重导入，封存单独失败不阻止重启。当前8787已由用户安装到webman_new3，严禁依据历史待安装状态重装。验收见docs/install-handoff-acceptance.md；原库与8793保护继续有效。

2026-11-08 安装数据策略 FORMAT3：模式卡内唯一初始化选项，默认不选、切换模式保留。Webman 使用内置结构＋可选示例；EyouCMS 必须上传 SQL，未勾选只取结构并种入可信基础配置，勾选导入源数据且不混内置示例。直接显示上传和引擎开关，无第二个导入勾选框。未安装即可扫描 Eyou 标签主题，唯一自动、多套明确选择、无候选红字阻止；所选主题覆盖全部导入语言旧路径。数据策略、SQL SHA 和主题文件签名绑定预检及续装；未完成 FORMAT2 任务使用冻结旧实现及原 CLI 参数，不接管新语义。当前规则覆盖下方历史互斥／兼容无SQL规则。本轮不提交8787真实安装，不操作原webman_admin和8793，见[验收](docs/install-policy-v3-acceptance.md)。

2026-11-07 安装数据选项整理：初始化仅一个控件，收进网站模式白卡、下拉说明后，两种模式共用；SQL项改名“导入已有 EyouCMS 网站 SQL”并明确迁移含义。8787实际浏览器验证两模式选择保留、SQL互斥及390px手机无横向溢出；8项结构、12组独立事件复核通过。未提交安装或修改数据库，证据在项目外install-data-layout-20261107与installer-data-group-review。

2026-11-07 安装模式入口已改为原生下拉选择：Webman 模式（默认）／兼容 EyouCMS 模式；两模式均可勾选初始化数据，原站SQL导入仍与初始化互斥。仅调整私有维护页面和文档，保留compatibility=0|1接口，未修改安装入口开关、封存目录或数据库。本轮HTTP观察8787当前显示安装向导，未代为提交；下方已安装状态为前轮记录。DOM及25组交互检查、12组独立复核和8787实际输出通过；证据位于项目外install-mode-dropdown-20261107。

2026-11-07 官方主题与安装初始化已交付8787：菜单使用“文章管理”；default展示为“EyouCMS 默认模板”，原字节保持；webman-default为完整PC/mobile官方企业主题。新装初始化默认不勾选，原站SQL导入与演示数据互斥，选项纳入预检和中断续装签名。当前开发库webman_cms_new已由用户完成安装，现为167表5937行，安装资料封存于install_zvtqrbezzcvk；下方“待安装”描述仅为历史，不得据此再次安装。本轮未重装／填充开发库；webman_admin与现开发库逐表SHA、原主题／上传／静态文件、环境、安装记录和Git索引保持。四agent分工及真实安装／HTTP／浏览器验收见[官方主题验收](docs/official-theme-acceptance.md)，8793未操作。

2026-11-07 安装密码输入调整：管理员密码支持8～72字节，必须二次确认；三个密码框使用独立眼睛按钮切换明文，默认隐藏，切换不提交或改值。此规则覆盖历史12字节下限，不记录或预填用户密码。

2026-11-07 安装双模式、单前缀、上传原站SQL及精确冲突重装已完成隔离验收并交付8787。开发站仍为webman_cms_new新空库待安装，未替用户提交安装；原webman_admin的168表9826行与切换前逐表内容SHA一致。Git索引、原主题／上传／静态文件及.env保持，8793未操作。本段与下方现行规则覆盖历史双前缀、安装标记和未知库一律拒绝等行为。主／模板／页面／数据／独立验收agent协同完成，数据库测试串行。范围、恢复及剩余验收见[本轮验收](docs/install-dual-mode-acceptance.md)。

2026-11-07 用户要求重新安装并选择“新空库，保留原库”：8787已切换到 `webman_cms_new`，标准install已恢复，旧配置锁与runtime已移入项目外私有备份，当前处于待安装状态。原 `webman_admin` 数据库保留，不可清理。安装页已打开且环境检查通过，等待用户填写数据库密码及新管理员密码并提交；尚未完成新站安装。回退资料在项目外 `install-restart-20261107-v1`，不可把下方历史“已安装”记录当作当前新库状态。

2026-11-07 EyouCMS 安装入口规则对齐已交付8787：文件锁、旧安装地址、原版157表识别、HTTP新装和runtime重建验证通过；现有168表9826行、资产、归档及Git索引保持。下列规则取代上一轮“安装入口和 CLI 状态总会连接数据库”的说明；历史证据保留。范围见[本轮安装验收](docs/install-eyou-acceptance.md)，8793未操作。

2026-11-07 安装自动引导与资料封存已交付8787：现有 install 封存为 `install_pkfvcenmwift`，私有记录在 config，原入口关闭。新站 HTTP／CLI 安装、失败恢复和 runtime 整删已在隔离环境验证；开发站168表9826行、账号权限、受保护资源及Git索引保持。细节与间歇页面加载观察见 `docs/install-seal-acceptance.md`，8793未操作。

2026-11-07 后台目录迁移：后台页面、模块配置及兼容入口统一到 `app/cms/admin`，`plugin/admin` 不再是允许的运行目录。已完成隔离验证并交付8787；原账号权限和资源保持。验收、6项Finder元数据差异及回退以 `docs/admin-relocation-acceptance.md` 为准。

2026-11-07 资源整理已交付8787：后台资源集中admin-assets、共享资源集中_shared，旧地址按逐文件清单兼容。隔离上传／编辑器／外观及开发站浏览器和9958资源URL复验通过；8793未同步。证据与边界见docs/resource-migration-acceptance.md。

2026-11-07：开发站 8787 已完成本轮停机迁移并重启，新架构已运行。旧状态已逐项核验入库，整个 runtime 已清除并自动重建；8793 保留副本未改动、未同步。迁移后 HTTP 复查与当前管理员登录后的主要页面读取、筛选和打开／返回验收已通过。本轮架构迁移验收完成；未扩大为全部角色、业务写入或 EyouCMS 严格兼容验收。

- 目标为 Webman 兼容 EyouCMS 1.7.7 企业官网的原 `{eyou:*}` 用法、模板／资源 URL、业务 ID 及关联关系；不实现会员、商城交易，保留必要 schema 与禁用行为。
- 原版对照为开发工作区中的 `EyouCMS-V1.7.7-UTF8-SP1`，禁止修改原版源码、原始 SQL 或无关用户改动。不能 reset、重建 Git 或覆盖既有暂存状态。
- `.git` 必须是原项目内实体目录；运行版本不再受历史精简源码包 10MB 限制。
- 先读 `docs/architecture.md`、`docs/eyoucms/current-handoff.md`，依 `docs/eyoucms/roadmap.md` 推进；历史执行日志不能覆盖当前合法配置。

## 多 agent 协作

用户要求主 agent 加最多三个并行子 agent。当前架构任务：主 agent 负责路径契约、集成和运行切换；代码 agent 负责业务归类与资源；数据 agent 负责数据库持久化及旧状态迁移；安装 agent 负责完整向导与 CLI。阶段任务可另分模板、后台、文档验收，但共享文件须提前约定负责人。

数据库操作串行，由主 agent 分配窗口。不能清理其他 agent 的数据、进程、快照或未提交文件。完成时报告修改范围、验证、失败与依赖；只读 HTTP、接口、浏览器、真实数据库及独立部署证据不能相互冒充。

## 目录准入和代码边界

- 根目录保留 Webman 标准目录与必要入口、`.git`、`.env`、Composer 文件，以及 `template`、`docs`。全新站带 `install`；安装成功后仅允许私有记录指定的同级 `install_<12位小写字母>` 归档，不能保留或自动创建第二份原 install。归档可在核验后删除；不重建根 `data/resources/scripts/sql`。
- 项目 PHP 尽量在 `app/cms` 分层：后台处理、服务／仓储、兼容／渲染、持久化、安装迁移、维护及私有资源。保留已有合理模块，不做无意义全量改名。
- 原前台／后台控制器、中间件、worker 和命令保留薄接入层；原 HTTP 路由、controller key、权限节点和业务协议不变。后台页面放在 `app/cms/admin/view`，统一经 AdminView 渲染。
- 后台登录、账号、权限、基础CRUD、工具、模型、API及中间件实现在`app/cms/admin`；原 `plugin\admin\` 命名类放在 `app/cms/admin/legacy`，Composer显式映射；不保留 `plugin/admin` 实体、软链接或副本。维护实现文件，不向薄入口回填业务；原类身份与必要的plugin祖先链不得移除。缓存失效、Sitemap调度、操作日志的公共实现使用`app/cms/service/AdminOperationService`。
- 项目路径使用 `app\cms\Paths`，原路径常量经兼容层映射；禁止根目录别名软链接。
- 私有资源在 `app/cms/resources`，新站初始 SQL 在 `install/database`，完成封存后随整个安装目录原字节移动；后续迁移在 `app/cms/database`，维护命令统一 `php webman cms:*`。归档定位只能由 `InstallerArchive` 读取精确私有记录并核对完整 SHA，禁止扫描 `install_*` 猜测目标。
- `docs` 不参与运行；不在应用根／runtime 存放验收截图、报告、备份、独立副本或实验工具。新验证和快照放项目外独立工作目录。

## 运行、状态及迁移

- PHP ≥ 8.2，本机显式使用 `/Users/lizheng/Library/FlyEnv/env/php/bin/php`。开发8787，当前隔离验证8788，保留交付8793不得自动同步。
- 新安装只设一个db_prefix，默认wa_；CMS表直接加前缀，后台加固定backend_，内部表为wa_cms_state_records等。157张CMS+7张后台+3张业务状态=167张基线，不创建或依赖cms_install_state；旧双前缀布局兼容读取，不擅自迁改原库表名。DDL不宣称整体事务回滚。
- 生成任务／manifest／归属hash／撤回／更新状态、SHA去重内容对象、维护回执和登录限流入数据库；旧ui_config历史数据保留，可视化入口已关闭。管理员只存密码哈希，不生成runtime明文密码文件、不重置已有密码。
- 旧runtime迁移默认只读预演，实际执行要求停全部worker、外部备份并核验源SHA；历史可视化迁移工具仅为旧记录，不得恢复已关闭的在线可视化；保留差异和原值。重复迁移跳过相同来源，来源变化拒绝覆盖。
- 完成迁移后支持停止全部worker、整删runtime、启动自动重建。会话失效、旧日志不恢复；业务配置、任务、发布状态和public/html保持。迁移完成前旧runtime不能整体删除。
- 数据库写入与公开文件发布分别核验，失败保留可恢复记录；不把文件写成功误报整项发布成功。所有清理继续保护外来和人工修改文件。
- 安装状态先检查标准install：目录不存在或其中有普通install.lock即关闭，不连数据库、不要求标记；符号链接和异常类型拒绝。标准install存在且无锁即允许主动重装，包括已有数据库；历史本地完成记录、数据库内容和旧归档不作为第二把锁。未完成可信任务须按原绑定续装，不能改参数接管。
- 未安装页面进入/install/index.php，保留/install与/install/，API明确返回未安装，不转发业务提交。删除runtime不解除文件锁。默认Webman空站；EyouCMS模式必须上传SQL（单文件≤50MiB、2MiB分片），import_demo控制源数据或仅结构；Webman的import_demo控制内置示例，InnoDB转换可选；非兼容伪造导入在任何数据库变更前拒绝。空站使用独立cms-empty.sql+可信后台/内部基线，不读本地原版SQL导入。
- 新安装完成先创建 `install/install.lock`，再立即将标准目录封存为 `install_<12位小写字母>`。原 EyouCMS 的20位混合随机后缀与本项目12位小写后缀属于保留差异，不宣称完全一致。已有 prepared/complete 归档 journal 的清单不能补写 lock 或重算来掩盖差异；恢复须沿用原目标和原清单。`config/cms-installer-archive.json` 保存目标、文件 SHA、目录与 prepared/complete 阶段，不能放入 runtime。失败返回安装成功但 archive_pending，使用 `php webman cms:install --seal --check` 预演、`--seal` 核验真实核心结构、基础数据及后台管理员；存在本轮journal还核对前缀、模式和随机站点签名，再补锁/继续原目标。禁止与--input或--preflight混用，不覆盖冲突目标，不重新导入。封存与安装共用互斥锁。
- 重装必须先完整SQL预检并审阅完整目标表冲突清单，以plan_token提交。只删除此次将创建的精确同名表，禁止按前缀或整库清理；换前缀的原表及同前缀无关表保留。先在任务专属暂存表完整导入并校验，再复核原表结构、行数和数据SHA，才进入精确删除和原子发布；外键依赖等未支持情况拒绝自动替换。
- 安装进度在私有config/cms-installing.json，FORMAT3绑定数据库、模式、数据策略、主题及文件签名、前缀、SQL SHA及引擎，阶段loading→dropping→publishing→finalizing→database_complete→complete。不存明文密码，不放runtime；DDL删除开始后保留journal/暂存表按原任务恢复，不擅清理。管理员、角色、cms_template_mode和cms_site_signature同事务写入，站点签名续装稳定。
- 收尾顺序为环境→本地记录→install.lock→上传清理→封存→journal complete。环境或锁失败且入口仍开时用相同参数补收尾；显式seal须核对实际库的任务签名，不能用旧环境对旧库封存。本轮CLI先--preflight取得令牌，cms:database-init --apply停用，仅保留空站基线只读预演。
- 安装SQL、原安装目录和随机归档始终禁止公开读取；随机目录不是Web安装地址，不通过公开接口返回。普通 `cms:migrate` 不依赖归档，归档删除后不重建install。
- 全新安装包必须在独立打包目录恢复标准 `install`，按私有记录验证源归档和复制结果的完整文件 SHA/大小及目录清单，原始 SQL 保持字节。验证完成后，仅删除副本中的 `install/install.lock`，并排除源站 `.env`、`config/cms-installed.json`、`config/cms-installing*`、`config/cms-installer-archive.json`、临时安装凭据及 runtime。绝不可为打包修改、去锁或改名源站归档；旧归档没有 lock 时不得注入。私有凭据禁止输出或提交。

## 模板、资源和后台

- 每请求新建CMS上下文，隔离ID、语言、城市、设备、主题、分页和标签变量；不把请求数据留在进程静态变量。
- 保留隔离编译器，不启动原ThinkPHP、不修改vendor、不覆盖Composer的think命名空间；不以空占位或固定演示值冒充标签。
- 主题在template/<theme>/pc|mobile；原版default及现有主题原字节保留，新增独立webman-default原生主题。可信backend_options.cms_template_mode保存webman/eyou，旧缺项为eyou，安装后仅只读不在线切换。当前模式只接受对应标签及根变量；局部变量不改，后台视图不纳入。模式/编译版本/随机站点签名必须进入缓存、任务及产物签名，不复用另一模式或旧站结果。不恢复根pc/mobile、official或旧3177样本。缺页不跨主题静默补齐；不完整主题可启用并按既有设置友好报错。
- 上传唯一实体`public/uploads`，旧URL由兼容层解析，不改数据库路径；根upload、uploads及public/upload保持不存在。
- `public/html`专放生成实体，内置HTM在私有`app/view/cms/system`；PHP、模板源、安装SQL、配置、docs和私有资源禁止作为公开静态资源读取。
- 受管HTML经Webman校验状态、模式、设备、签名与hash，代理不能用try_files绕过；不宣称脱离Webman的整站静态导出。
- 后台侧栏、功能地图和模块页签是权限投影，不额外授权。保留紧凑布局、外观偏好、筛选语言恢复、未保存保护和原业务事件；一级子菜单高度动画仅由Layui控制。
- SEO以URL设置、Sitemap等现有页签展示；Staticgenerate为“更多静态生成配置与任务”辅助页。基本设置保留原键、语言和独立保存权限。

## 验证、资料和保留环境

- 日常运行变更相关语法／行为检查，阶段门槛再做综合回归。原版在独立兼容PHP环境对照，Webman用PHP8；不以检查数量计算兼容率。
- 保留40任务及22项L/U/R的ID、要求、顺序和失败。T16总体、T17引用／比较、goto别名、富文本来源、未供主题等仍开放；架构整理不关闭S3/S4/S6。
- 保留79篇离线标签文档及来源／属性／任务矩阵。826份历史证据的逻辑索引和SHA不重写；旧归档缺失不能伪造恢复。历史工具不保证透明重放。
- 8793副本及manager位于`/Volumes/disk/site/php/dzhcms/webman-cms-preserved-20261107-v1/state/runtime`，属于项目外独立环境，不是本项目可清runtime。保留停止和未同步状态，不改库、cleanup或修补manager的旧SHA保护。
- 详细历史位置、资源包与回退方法见`docs/maintenance-archive.md`、`docs/source-package.md`；不得把整份旧runtime覆盖到新运行站。

## 资源布局与构建边界

- 系统公开资源统一在public/admin-assets；后台css/js/images/vendor保持，系统公共资源在common。template下只保留各主题，禁止创建_shared实体；旧共享URL仅按逐文件清单兼容。原主题字节不改、上传和已生成HTML不参与整理。
- AssetManifest维护逐文件canonical及legacy别名；组件HTML只按显式文件白名单，PHP/私有资料拒绝公开。禁止恢复public/admin、component、plugins、static、config、errpage旧实体目录。
- 源码包、资源清单、默认配置和字体归app/cms/resources；命令php webman cms:assets check|verify|build|restore。Node依赖、构建缓存和测试证据全部在项目外。
- 构建严格锁定输入和产物SHA，不覆盖已有不同内容。新资源恢复只允许format=2；旧包仅用于完整回退。ForeignPack只有指定生成文件可变，写操作不通过只读别名映射。
- 本轮协作：主agent负责清单、兼容及集成；后台agent负责视图/加载器；前台agent负责Tag/文件读取/语言包；验收agent负责构建恢复。共享文件修改先协调；不据此关闭EyouCMS兼容遗留项。

## 自维护后台边界

- 后台路由白名单位于 `app/cms/admin/config/routes.php`，CRUD登记位于 `generated-routes.php`；接入主路由，禁用默认控制器发现。HTTP入口仍 `/app/admin`，callback必须是原namespace有名薄类，不能用闭包或CMS实现类代替权限身份。
- `AdminEntry`只接受登记的控制器/动作，沿用AccessControl与原权限key；`Http::getPluginByClass`将这些兼容类视为主应用，实体搬迁不改变实例的static::class、反射身份、模型链或角色字符串。
- `AdminView`显式读取模块view，编译缓存在runtime/cms/admin/views。模块配置在admin/config，由主config/cms_admin.php接入；database中的plugin.admin.mysql/cms仅为历史连接别名。
- 禁止重新依赖webman/admin；Composer声明conflict，后台必要组件已为直接依赖且版本锁定。业务插件市场入口已关闭。
- Web CRUD 和其他 PHP 代码生成入口已关闭；现有业务 CRUD 保留。

````
