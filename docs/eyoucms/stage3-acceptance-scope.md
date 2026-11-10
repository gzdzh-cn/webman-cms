# S3 有限验收范围与待执行清单

更新：2026-11-04，Asia/Shanghai。本文是文档 agent 对既有文件的只读审计与后续执行清单，没有新建数据库夹具、启动服务或操作浏览器，也没有改写历史报告。阶段定义来自 [路线图](roadmap.md) 的“后台与多语言”和 [40 项验收计划](acceptance-plan.json)；本文细化有限门槛，并按各自范围引用主agent已执行的有限增量；不替代主 agent 的阶段判定。

**S3 仍未通过，但不能以“所有组合尚未穷尽”为唯一理由无限延长。** 已有有限服务、真实数据库、认证 HTTP、管理员 UI 和公开页面证据可以复用。应补齐下面列明的生命周期、具体功能缺口和真实角色 UI；不同语言名、任意 HTML、全部主题与无限属性组合属于另列的采样边界。

## 2026-11-04 全新Composer有限验收通过

[第二轮真实安装14](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/fresh-composer-second.json)在无vendor、无旧cache、无.env/旧runtime的所属副本中，启用root package scripts实际安装84个锁定公共包；12项平台要求成功，6类autoload、重复install metadata及4266项应用输入的字节/链接保持均通过，包含定制`support/bootstrap.php`。当前应用输入与执行时一致；所属副本、Composer home/cache全部清理，无DB/服务/UI操作。[首轮失败](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/fresh-composer-first.json)及其输入清单保留：末项发现官方hook覆写bootstrap；[首轮工具v1](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/fresh-composer-tool-source-v1.py)另存且与首轮input.json记录SHA一致，但未保存被覆写后bootstrap原始字节或全部源快照；覆盖结论依赖执行工具的preserved=false与命令摘要，不能完整独立重放。包装器仅保护六个应用自带包的脚手架hook，其它插件原样转发；[stub35](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/composer-hooks-no-database-first.json)与真实安装14分层，不合并为业务兼容率。

本项只关闭S6全新锁定依赖安装分区；已有站MyISAM迁移、最终冻结综合回归、新管理员真实登录与移动交互仍开放，S3/S6 gate均为false。S3列表139、Article详情52、custom单页165与S6安装是不同证据层；当前源/8文档/工具/报告由[新增绑定](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/stage3-dynamic2-custom-composer-evidence-v1.json)统一保存，旧manifest、失败报告和快照不改。

## 2026-11-04 后续增量：dyn2 Article详情实际HTTP

[详情第二轮52](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-dynamic2-view-http-second.json)以一次真实后台Article插入POST和一次重开GET、15公开请求，验证原PC/mobile `arclist.arcurl`生成/跟随、PC path2、mobile原query及复用PC原URL的mobile path2详情；aid/typeid/标题/双端不同正文、SEO/query sentinel及target home_lang Cookie正确，显式target200/main404、未知/无效lang404。17响应/1SQL/221源保存，164原表/UI/6表owned及4模板/4所属编译文件精确清理。[首轮](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-dynamic2-view-http-first.json)在0HTTP/0写入时被过宽孤儿预检拒绝；[工具v2](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-dynamic2-view-tool-binding-v2.json)只收窄未来aid候选保护并修无journal的cleanup，保留原行和计数器。此为独立准备目标的有限Article详情扩展，不是语言复制resolver、全模型/全主题或真实UI验收；S3 gate=false。

## 2026-11-04 后续增量：custom 单页实际HTTP闭环

[第二轮165](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-single-custom-http-second.json)已完成10认证请求（8POST/2GET）与12公开GET：copy_documents0/1按SQL DEFAULT/NULL初始化，singlePage保存标量custom和双正文、getContent重开，两次sync保持人工空串/0/非空值和双正文；原PC/mobile字段变量及显式`aid="$eyou.field.aid"`的`field addfields`读到对应值。首轮缺显式aid为夹具假设错误，原版list/single不推断请求aid；[工具v2说明](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-single-custom-http-tool-binding-v2.json)保留源观察/失败及修正，无生产修改。164原表行列、single原结构/索引/原行投影、UI与所属数据/模板精确恢复。此为API/SQL/公开模板证据，无真实UI、扩展dtype矩阵、严格1364重复或全主题证明；S3 active/gate=false，详见[生命周期记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-lifecycle-verification.md)。

## 2026-11-04 后续增量：dyn2 列表入口可用性扩展

[本轮浏览器复查](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/stage3-browser-availability-20261104-v2.json)于2026-11-04按正式入口执行inventory、existing-tab bind及文档允许的direct tabs.get，仍browser.user unavailable、UI动作0；U01–U06/R01–R04维持pending_environment，既有API有效不等于UI验收，无需重新登录。[同应用原生入口](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/stage3-browser-native-availability-20261104-v1.json)随后亦被工具限制（com.openai.codex不可供Computer Use使用），返回界面前即拒绝，UI动作仍为0；只记环境阻塞，不换浏览器或绕过。

[dyn2实际HTTP第二轮139](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-dynamic2-http-second.json)完成4认证/43公开，PC原生成path2链接与mobile原生成query链接及复用同分区PC原链接的mobile path2入口均返回正确lang/owner/SEO和home_lang Cookie；显式query目标200、主语言404、未知/无效pathlang404，正常query/source模式保留。[入口范围memory28](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/dynamic2-path-scope-review-v1.json)单列验证限定条件和上下文对象身份。此为有意的可用性扩展：原third404与PHP7缓存顺序oracle继续保留，不宣称逐字复现原损坏入口。本139批只验lists；后续Article view52另列，不扩大到全路由/真实UI；S3 active/gate=false。首轮mobile URL形态工具失败及清理保留，详见[生命周期记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-lifecycle-verification.md)。

## 2026-11-04 单页新增量

[custom SQL默认值](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/single-page-custom-defaults.md)已分层记录原方法17/实际DB40/独立只读63；[显式历史结构修复](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/single-page-structure-repair.md)有memory54/独立关系与CLI53/实际DB28及独立raw receipt。新结构使用SQL DEFAULT/NULL，既有人工值与正文保留；strict1364和晚写入失败原子回滚。历史修复由选ID及指纹保护的CLI执行，普通语言sync不自动修复；未知孤儿及完整迁移仍待单独核查。新DB批次原157行列map恢复、所属表/workspace归零。详见[生命周期增量](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-lifecycle-verification.md)，各层检查数不累计兼容率。dyn2其它模型/入口采样边界、U01–U06/R01–R04真实UI、T17、未提供主题及后续阶段仍开放；S3 active/gate=false。

## 结果分类

| 分类 | 当前发现 | 处理方式 |
| --- | --- | --- |
| 审计时缺少、现有候选待验 | 审计时 `EnterpriseDataService::delete()` 明确拒绝 `language`，专用删除入口尚未提供；原版有语言删除和关联清理。随后已有 `LanguageDeletionService`、专用控制器及界面；导航、会员留言、跨 owner 引用及 UI 文件缓存保护已修到候选，另补排队 UI 写入的语言 ID 复核。 | 保留缺失历史；已有最终 DB72、认证 HTTP29、JS10、提交6、缓存26、队列13及提交后异常两组6项有限证据；服务/DB/HTTP分区已有依据，真实 UI 仍待验，不以源码存在宣布通过。见 L05。 |
| 已修实际缺陷 | 目标语言目录占用曾使真实 UI 返回 1062；后续服务已改为中文提示并整体回滚。 | 保留旧 UI 失败与新 24 项 HTTP / 5 项独立异常证据；L11后续已补三种真实writer锁竞争35项，中文提示真实 UI 分支仍待环境。见 L07、L11、R04。 |
| 有限原合同已补、具体分支仍待验 | L04已修新语言栏目/legacy参数/表单字段/广告友链名称前缀，原方法3/候选8/对照12及实际持久化29通过；list_id=0/custom/legacy附加值保留为明确扩展。 | 配置18/19/16与DB20、资格内存25/DB25已补有限分区；正常query链接第五轮75已过有限范围；单页有限结构/metadata已验；非空自定义字段与显式结构修复已有后续有限增量；必要真实UI仍待验。见L04。 |
| 有限必验缺证据 | B07 成功浏览器覆盖；B10/B11/B14 成功浏览器永久删除；B14 清空自定参数；B09–B15 受限角色真实浏览器。 | 功能已有服务/API，缺少的是指定层次的执行证据。不能写成尚未实现，也不能用确认取消或 CLI 清理代替。见 U01–U06、R01–R04。 |
| 环境阻塞 | 主 agent 本轮报告：能列出现有 IAB tab 29，但 tab 控制报 `browser.user unavailable`；native Codex surface 被工具禁用。 | 本轮没有创建新角色夹具、没有浏览器业务操作。新 UI 项为 `pending_environment`；既有 HTTP 不升级为 UI。恢复可用后继续原清单；该沟通记录不算新的浏览器业务验收。 |
| 不要求穷尽的边界 | 所有语言/主题/模型/HTML/输入/错误/设备组合。 | 记录已选分区、未覆盖输入与发现的新缺陷；不把无限笛卡尔积设为 S3 门槛。 |

语言删除的只读依据：[候选通用服务](../../app/cms/service/EnterpriseDataService.php)、[候选语言服务](../../app/cms/service/LanguageService.php)、[候选控制器](../../plugin/admin/app/controller/LanguageController.php)；原 [Language::del](../../../EyouCMS-V1.7.7-UTF8-SP1/application/admin/controller/Language.php) 和 [Language::afterDel / delLangData](../../../EyouCMS-V1.7.7-UTF8-SP1/application/common/model/Language.php)。这是审计时点观察；随后新增的 [专用删除候选](../../app/cms/service/LanguageDeletionService.php) 已有下文最终 DB与认证 HTTP等分层有限证据，尚未完成真实 UI 验收，保留本次缺口记录。

## 有限夹具分区

一次受控批次使用三个已登记语言：最早 ID 的主语言 M、启用目标 A、禁用目标 B。名称不必固定为 `cn/en`；涉及原版固定中文源或目录字典时另用明确的 `cn/en` 分支。共享模式与独立模式各选一个有效配置，不遍历每种布尔编码。

结构夹具包括：两层普通栏目、一份单页、一个产品及旧/新/自定展示参数；广告位及广告、友链组及链接、自由表单及字段、栏目留言字段各一组。目标 A 保留人工翻译，目标 B 验证“已登记但禁用”仍参与原结构规则。原 source ID、目标 ID、canonical 名称、字段归属、PC/mobile 正文和原资源 URL 分开核对。会员和商城业务不进入正常复制/删除链；遇到现存范围外数据应按明确保护规则拒绝，不能静默损坏。

同一夹具可串联多个操作，但每一步保留独立前后状态、实际请求和清理归属，不把 API、DB、UI 的重叠断言相加为独立业务案例。所有会永久删除或覆盖的操作仅针对本批登记对象；执行窗口与主 agent 协调。

## 生命周期与服务/HTTP门槛

状态 `existing_finite` 表示已有报告直接覆盖所述有限分支；共享代码改动后仍应执行受影响的最小回归。`pending` 是有限待补项，不表示要重跑所有历史套件。

| ID / 对应任务 | 必需操作与成功判据 | 现有证据与当前状态 |
| --- | --- | --- |
| L01 / B07、B14 | **先有数据再新增语言**：复制两层栏目、字段、PC/mobile 正文、广告/友链/表单和参数；新 ID 的 owner 与 canonical 映射正确，共享资源路径不变。重复增量同步不增重复；人工翻译不被覆盖；再加一篇源文档只复制一次。 | [语言服务 14 项](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/backend-language-services.json)、[语言 HTTP 15 项](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/backend-language-http.json)：`existing_finite`。[真实增量/资格DB25](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-incremental-eligibility-first.json)已补创建target→实际ContentService新增status0源文章→sync两次仅一份/译文保持；157原表行列恢复。该链是DB层，后续single源变化仍按受影响范围补验，不要求全部模型重做。 |
| L02 / B16 | **先登记语言再新增共享数据**：主语言新建父子栏目/单页、广告位/广告、友链组、自由表单；A/B 都有正确克隆和绑定，广告 pid / 子栏目 parent_id 指向各自目标 owner；普通编辑保留目标人工翻译。 | [第三轮 67 项](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/backend-enterprise-structure-http-third.json)覆盖创建、25 次认证/18 次公开请求及禁用目标；[26 项事务复核](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-structure-phase3-review-db-first.json)覆盖后续生命周期：`existing_finite`。 |
| L03 / B16、B14 | **独立模式与非主语言**：各取一次非主语言本地创建和独立模式创建；不应克隆时无克隆，不应扩散回收/编辑时保持其它语言；原版特殊 legacy 产品参数自动全语种规则单列，不能套统一规则。 | [原方法95项](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-language-structure-phase3-v1/gallery-parent-title-memory-v2/original.json)与[候选145项](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-language-structure-phase3-v1/gallery-parent-title-memory-v2/candidate.json)的 `split_does_not_fanout`、`nonmain_does_not_fanout`、`earliest_id_defines_main_language` 已通过内存检查，候选另有独立广告位删除保留peer。[B14 freeze v2](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/product-parameter-multilanguage-backend-freeze-manifest-v2.json)和[UI25](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/product-parameter-multilanguage-ui-a862c14f9d03-final.json)有参数有限规则。历史 `pending_DB_partition` 已由 [生命周期 HTTP81](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-lifecycle-http-first.json) 补齐：独立模式主/非主栏目本地创建、非主字段本地创建、独立字段回收及广告位删除保留其它语言 owner。当前为 `existing_finite`，不扩成全模块×全模式。 |
| L04 / B14、语言创建 | **新语言参数、配置、栏目资格与单页初始化契约**：同一源中放一条 legacy 定义、一条新组内定义、一条 `list_id=0` 定义/自定产品值；逐项确认是否复制、名称前缀及 owner。迁移既有原 canonical 名称不得造另一组重复绑定；目标人工译文保留。配置按原afterAdd，栏目资格按原启用树；single在copy_documents=0仍有结构，=1只初始化双正文并保留stub metadata。 | [原方法3](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-parameters-independent-original-v3.json)/[候选8](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-parameters-independent-candidate-v3.json)/[对照12](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-parameters-independent-comparison-v2.json)及[实际持久化29](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-persistence-second.json)通过：新建名称prefix修复，增量不加prefix，原canonical别名/人工译文/重复sync保持；四个device×product标签断言各含auto/newattr，共八输出。后续配置原18/候选19/合同16/DB20、资格内存25/DB25见[生命周期增量](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-lifecycle-verification.md)。当前 `existing_finite_config_eligibility_query_single_custom_defaults_explicit_repair / pending_unknown_orphans_UI_with_dyn2_broader_sampling_boundaries`；dyn2生成链接third404与原有限缓存顺序分区保留；正常query第五轮75已有冻结源证据，single结构/metadata有限修复已由内存对照28、DB33及HTTP85验证；非空自定义字段及显式结构修复已有后续有限增量；未知孤儿迁移仍待验。list_id=0/custom/legacy附加值保留为候选扩展，不宣称完整创建等价。 |
| L05 / 语言生命周期 | **删除目标语言**：拒绝主语言、前台默认语言和混有受保护 ID 的整批请求；删除独立目标 A 后仅清理 A 的企业数据、附表、字段绑定、canonical 目标映射、语言变量等，M/B 及共享物理资源保持。至少一个有数据目标、一个重复/不存在 ID、一个中途故障整体回滚。同步 `system_langnum` 与实际公开语言列表。 | 历史 `pending_implementation` 由专用服务及[最终DB72](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-deletion-db-final-v1.json)/[空语言认证HTTP29](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-deletion-http-second.json)承接；提交后补偿与排队UI复活已有独立修复证据。后续[业务语言公开终态26](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-public-deletion-http-second.json)含4认证/16公开，删前8×200、删后目标4×404/主体4×200，直接核target语言消失、另一owned语言保留和counter。当前 `existing_finite_service_DB_HTTP_public_routes / pending_environment_UI`。本次HTTP81不包含语言删除；保留全局nav/会员商城/跨owner/物理资源保护及文件补偿范围，不扩成全部业务模型。 |
| L06 / B07 | **正文覆盖与随后增量同步**：覆盖目标活跃企业文档；单页 aid/metadata 保留，只换双端正文；目标栏目/配置/广告/回收文档保留；无确认/同源/POST、无授权、缺 owner 映射和删除后故障均拒绝或回滚。随后同步不复活旧文档，不重复覆盖第三语言翻译。 | [服务 50 项](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/backend-language-reset-service.json)、[HTTP 64 项](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/backend-language-reset-http.json)、[精确清理](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/backend-language-reset-cleanup-audit.json)：`existing_finite`；成功浏览器链单列 U02。 |
| L07 / B17 | **目录有限分区**：显式字典保留目录 `cn/en` 拒绝；留空自动目录另测，不把显式输入伪装为自动 suffix；普通根/子目录新增编辑可达。一个不在原字典的自定义注册 mark 保持明确的语言首页优先/源语言栏目 URL 语义。目标 A 已占用、回收占用及后序禁用 B 占用均中文拒绝且全部回滚。 | [第三轮路由/目录](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/backend-enterprise-structure-http-third.json)、[碰撞第二轮 24 项](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-directory-collision-http-second.json)、[独立异常 5 项](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-directory-conflict-independent-v1.json)：`existing_finite`。后一专项没有新 UI/公开请求；后续[custom mark增量](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/custom-language-directory-routing.md)完成原标签typeurl原样跟随、bare语言首页及显式默认语言路由，子树过滤另有真实方法内存证据。历史74e43混合子树实库81/HTTP160分层通过；当前f5b1继承另有原版前缀memory31、array9、子树408、混合模型实库82及Article HTTP280（含真实重开/继承列表模板）。后续Single方法比较28/各side72/合同44、实际SQL146与HTTP272补齐有限empty_logic继承和双端正文链；普通.htm Article继承详情后续HTTP185有限通过；grade/其它SEO与真实U/R UI仍待验；代表并发见 L11，新提示 UI 见 U06、R04。 |
| L08 / B16、B03 | **栏目树回收→恢复→永久删除**：主语言对应族与子树范围正确，手动独立回收状态不被无意恢复；保留目标语言独立子节点时永久删除原子拒绝；允许删除时清理对应绑定及自动单页，原资源保留。共享广告位删除连广告、友链组连链接、空表单连字段/绑定。 | [26 项 DB 事务复核](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-structure-phase3-review-db-first.json)、[既有内容 HTTP](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/backend-content-lifecycle-http.json)：`existing_finite`。[生命周期 HTTP81](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-lifecycle-http-first.json) 新增共享整树回收、独立目标子节点保留、叶恢复激活祖先但保留祖先文档回收状态、active/missing/mixed 恢复原子拒绝及双端 404/正文保持。原版可能留下孤儿而候选拒绝，是保护增强；不要求复制损坏。新 UI 门槛只补未走通的成功终态，不要求每语言×每模型都再点一遍。 |
| L09 / B10、B11、B14 | **专属回收与删除**：同名多语言变量整组回收/恢复/永久删除；Guestbook 与自由表单字段各保留历史/ID/options，永久删除只清理对应归属；产品参数删组精确级联并拒绝旧 preview。空 custom 参数经显式确认后移除独占定义和值，其它产品保留。 | [变量 HTTP](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/backend-custom-variable-recycle-http-frozen.json)、[字段 HTTP](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/backend-form-field-recycle-http-frozen.json)、[参数级联 HTTP](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/product-parameter-group-cascade-http-first.json)、[B14 freeze](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/product-parameter-multilanguage-backend-freeze-manifest-v2.json)：`existing_finite`；[生命周期 HTTP81](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-lifecycle-http-first.json) 又直接覆盖两种新字段 canonical 创建、共享回收/恢复保留译文及独立本地分支。[字段/恢复独立18](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/form-field-and-restore-independent-v2.json) 补有限异常与重试；它是内存服务层，不是新 DB/HTTP。新成功 UI 终态见 U03–U05。 |
| L10 / B09、B12、B13、B15 | **普通编辑链可复用**：文件源码/替图保存和原 URL 读取；FAQ 状态与公开输出；专题手选/自动/排序与分页；TAG rename 同事务更新关联并被文章编辑器读到，计数与 IDs 保留。 | [B09 管理员 UI](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/admin-ui-20261103-observations.json)、[FAQ UI](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/admin-ui-faq-20261103-observations.json)、[专题 UI](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/admin-ui-specialnode-20261103-observations.json)、[TAG UI](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/admin-ui-tag-20261103-observations.json)：`existing_finite`。不重新要求无限文本/自动条件/主题组合。 |
| L11 / 共享写入回归 | **失败原子性与真实竞争**：一个缺目标 owner/后序写失败，主行/目标克隆/绑定全回滚；两个独立连接/进程同时创建相同目录或 canonical 族，一个成功另一个可识别拒绝/幂等，最终仅一族；一个 shared 新建与语言增量同步交错不重复、不丢译文。 | [结构 DB 回滚](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-structure-phase3-review-db-first.json)、[目录异常注入](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-directory-conflict-independent-v1.json)已证顺序故障。历史 `pending_concurrency` 已由 [真实writer第二轮35](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-writer-concurrency-second.json) 补齐：不同PHP进程/连接，实际InnoDB语言行LOCK WAIT；same-dir、create-before-sync、sync-before-create均保持单一族/绑定和人工译文。当前 `existing_finite_real_writer`；这是外层事务屏障的三个受控锁顺序，不是全部writer或UI并发。 |
| L12 / B16、B17、A01/A03 | **最终当前源码公开联动**：同一原 ID 在 M/A 双端分别通过 `type/channel`、`ad/adv`、`flink`、自由表单及栏目 `guestbookform` 读取正确 owner；实际 GET 正文/表单 action 与字段正确。选择一个共享编辑状态，在两个真实 worker 观察最新内容；已有改动不串语言/设备，清理精确归零。 | 第三轮公开检查覆盖 `field/channel/ad/flink/form` 和产品图库；直接 `type/adv/栏目 guestbookform` 已由 [生命周期 HTTP81](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-lifecycle-http-first.json) 的 18 次双端GET补齐，包含禁用目标、独立模式main-ID原版诊断及local-ID正常输出。历史 `pending_worker_invalidation` 已由 [双worker第二轮66](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-workers-http-second.json) 补齐：5认证/40公开、两个稳定PID在两语言×PC/mobile×列表详情更新前后读取正确TYPE/SELF/TITLE与正文。当前 `existing_finite_two_worker_HTTP`；复用连接对象并逐请求验PID，不证明TCP socket身份完整持续，非全主题或浏览器。 |

## 管理员真实 UI 的有限补验

以下动作成功后必须重开页面，并由独立 DB/API/原模板检查确认实际终态。浏览器看到成功 toast、确认弹窗或请求按钮存在均不够。所有项目当前为 `pending_environment`；已有历史取消、CLI 或 API 成功保留为独立层。若同一个 UI 使用通用回收站，复用其登录/导航流程即可，各类型仍须各有一次成功终态。

| ID / 对应任务 | 一次实际浏览器流程 | 现有起点与所缺证据 |
| --- | --- | --- |
| U01 / B01 | 在本批 owned UI 区域保存→重开→确认清除→重开，原页面返回该区域默认/清除状态；另一区域保持。 | 既有 UI 只有取消，CLI 清除后读取不算成功浏览器 reset。这里只验一个区域，地图有效 AK 属外部项。 |
| U02 / B07 | 选合法源/目标，勾确认并成功提交覆盖；原 PC/mobile 文章与单页显示新正文，重开管理页；再做一次增量同步保留人工译文。 | [原 UI 观察](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/admin-ui-20261103-observations.json)仅确认阻止/取消；[HTTP64](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/backend-language-reset-http.json)已有业务依据。 |
| U03 / B10 | 新建本批多语言变量→回收→从回收站成功永久删除→重开确认不在 active/recycle，原 global 读值按删除规则变化。 | [回收恢复/history](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/admin-ui-recycle-d01103b10b11-final-history-report.json)未含成功 purge；完整回收语言组保护仍保留。 |
| U04 / B11 | Guestbook 和自由表单字段各一条：回收→成功永久删除→重开；各自原 ID/history 与实际清理规则一致，另一父表单保留。 | [同批 UI history](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/admin-ui-recycle-d01103b10b11-final-history-report.json)已有编辑/回收/恢复，缺两个 `form_type` 的成功 purge。 |
| U05 / B14 | 对一份 owned 产品清空自定参数并确认提交；另将一个 owned 参数组回收，查看精确影响后确认永久删除。重开产品/定义，独占定义和值归零，其它参数/产品保留。 | [多语言 UI25](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/product-parameter-multilanguage-ui-a862c14f9d03-final.json)含级联预览取消，不含成功提交。 |
| U06 / B16/B17、语言删除 | 一个目标目录占用真实提交→中文拒绝且无新行→改正常目录成功保存重开；L05 实现后，对一个独立非默认语言实际确认删除并重开语言列表、旧目标 URL 不再作为启用站点可达，M/B 页面保留。 | 原第三轮 1062 留存；[新专项](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-directory-collision-http-second.json)只有认证 API/DB。语言删除已有最终服务/DB及认证HTTP有限证据，本项真实 UI 仍未闭合。 |

## 受限角色真实 UI 的有限门槛

已有 [正常验证码独立受限账号 HTTP 报告](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/restricted-role-http-7c8a9d2e4f61-v1.json)完成 371 检查、127 实际请求；[完成报告](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/evidence/restricted-role-completion-7c8a9d2e4f61-v1.json)确认 38/38 exact actions 正向成功、正常 logout 后 401、原 157 业务表/7802 行摘要稳定及精确清理。它明确 `restricted_role_browser_acceptance=false`，足以保留动作级 HTTP 覆盖，不能替代以下真实界面。

以 [现有权限矩阵](../../../webman-cms-archive/20261106-architecture-only-v2/files/scripts/testing/restricted_role_matrix.json) 为基础，使用独立非超级管理员账号实际完成验证码登录；不得注入 root cookie、读取服务端 session 冒充登录。一个账号可按受控流程调整规则并正常刷新/重新登录；记录实际角色规则和 UI 状态，避免为每动作再建一套身份。

| ID | 有限必验流程 | 成功/拒绝判据与状态 |
| --- | --- | --- |
| R01 | 只读角色打开 B09–B15 各模块实际页面并读到 owned 数据；一个无规则模块直接 URL 访问被拒绝。通过真实 UI 正常退出，旧受保护页面/后续请求不可继续操作。 | 区分页面加载与数据接口；无权限必须是权限拒绝而非输入错误。`pending_environment`。 |
| R02 | 授予有限确切写 action，B09 文件源码保存、B10 变量保存、B11 字段保存、B12 FAQ 保存、B13 节点保存、B14 参数/产品值保存、B15 TAG rename 各一次；每项真实点击成功、重开并持久化。 | 七个模块各一个代表成功入口即可；API 已有的全部 38 action 不再全部重复浏览器。B14 选择一次产品保存以串联所需页面依赖。`pending_environment`。 |
| R03 | 保持模块可读但移除一项写权限：一次普通 Crud 写（变量或 TAG）、一次内部独立写策略（FAQ/文件/节点任一）、一次 B14 组合权限（有 archive 写但无 `saveProduct`），以及一次永久删除动作被拒绝。 | UI 隐藏/禁用及实际动作无修改分开记录；若 UI 无提交入口，由既有逐动作 HTTP 证明后端不可绕过，不强行用 DevTools 注入调用当 UI。刷新/重新登录后规则生效。`pending_environment`。 |
| R04 | B16/B17 栏目/语言/广告/表单是旧 B09–B15 矩阵之外：各权限族选一个代表可读/授权新增保存及未授权写拒绝；语言覆盖或删除未授权必须拒绝。至少一项通过真实页面确认中文冲突而不是 SQL 报错。 | 复用 R01–R03 的登录，不把旧矩阵推导为新增 action 已覆盖。角色规则/持久化/清理和 L07、U06 共享证据可交叉引用。`pending_environment`。 |

正常权限验证的原 API 证据继续有效。`B11 limits` 等旧文字中“受限角色 HTTP 未验”属于更早阶段描述，应以随后完成报告的确切动作覆盖为准；不得因此要求重新证明已实际成功的同一历史 HTTP 批次。

## 已有证据的复用与最终回归

本次只读核对 JSON：B07 服务 50、HTTP64 均 `pass=true` 且 `browser_verified=false`；B10/B11 frozen HTTP 各134通过；FAQ22、专题29、产品参数56、B14后续17与级联18、TAG25均通过，但它们各自不是浏览器报告。B16第三轮67通过与独立17条UI观察分层；目录专项24通过，不含新浏览器/公开模板。不要把重叠计数累计成兼容率。

对现有 manifest 的有限文件摘要比对显示：B14 freeze v2 的13份文件在本次读取时全部匹配；B10/B11和B15的 `EnterpriseDataService` 摘要已改变，B09文件服务/菜单、专题共用内容/后台入口等也存在后续版本。摘要改变只说明旧报告绑定旧输入，**不是新失败，也不是新版本通过**。保留未受影响的原方法、专属服务和UI历史；最终变更冻结后，沿实际修改的写入口与公开读入口补最小链路，不因一处共享文件变化重跑所有标签/主题。

阶段结束报告至少绑定：最终相关源文件 SHA、必验 ID 的实际报告/观察/截图路径、每项状态、公开页面与 worker 证据、原行/列摘要和 owned 行/文件/身份清理。纯计划、源码审阅、截图存在性检查不新增业务通过。后续代码变化需说明受影响分支，不用不断嵌套审计报告替代执行。

## 2026-11-03 追加的有限证据复核

本次只读复核 [生命周期 HTTP81](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-lifecycle-http-first.json) 为 81/81、42 次认证 POST、18 次公开 GET，`browser_UI_verified=false`；复用了已有授权 root API cookie，没有新登录或浏览器动作。创建字段使用已有登记语言，验证 canonical 主字段名称、正确 owner、目标人工译文、禁用目标参与、两种字段生命周期、共享树恢复及独立分区。普通路由上的探针使用原标签语法，不能称作全部未改官方整页模板。18 份保存 HTML 已实际阅读，没有残留 `{eyou:`，独立模式 main-ID 的 type/adv/guestbookform 报错为原行为，local-ID 输出正常；四份回收 404 与恢复双端正文也相符。报告没有逐响应 SHA 绑定，不把当前 HTML 摘要当成执行时绑定。

[证据 manifest](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-lifecycle-evidence-v1.json) 的 10 个报告 SHA 全部与文件相符。HTTP81 的前后 185 份源码 SHA 相同，且复核时全部与当前源码及 [185 份冻结副本](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-lifecycle-final-source-v1) 相符；该清单明确不包含本轮另行开发的语言删除服务/控制器/界面/菜单。19 张表的 owned 行归零、原行/列摘要恢复、探针模板删除均为真；记录的 8 个新建标记编译/缓存文件现均不存在，既有非标记缓存保留。没有新业务操作或 DB 重跑。

| 有限报告 | 核对结论与边界 | 当前文件 SHA256 |
| --- | --- | --- |
| [HTTP81](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-lifecycle-http-first.json) | 81/81；真实认证 API/公开双端 HTTP，非浏览器、非并发、没有 worker PID。 | `5a34344b851984bf6f14389d0fc62a4e07bbd4fa8ed5e36bc1018a7e52884e7f` |
| [独立字段/恢复18](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/form-field-and-restore-independent-v2.json) | 18/18；7 个当前源 SHA 相符，实际候选服务配有限内存适配器；补部分 canonical 重试、错误 owner/重复 metadata/alias/独立 peer 与恢复拒绝。 | `1b6dccac53cb025737318b3e21cebe2f023dc6a7df142bd03ebd605cbd4a9e21` |
| [helper11](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-helper-independent-v1.json) | 11/11；每例新进程，原 PHP7.2.34 与候选 PHP8.4.23 的结果对象全相等，原/候选 helper SHA 均相符。有限内存上下文，未执行 `CmsContext::load()`、DB 或 HTTP。 | `d5d1044b859e9acec0045917f6b90e1d28b3f673b521e3f86ddc4f9a9180f80d` |
| [标签生命周期25](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-tag-lifecycle-comparison-v1.json) | 25/25；共享、独立 main/local-ID、更换前台默认、仅启用 en 五分区，实际原/候选方法及有限查询/模型适配器；双方5份 source SHA 相符，未执行上下文 load 或真实 SQL/HTTP。 | `fcdbfd13268a02c516fecdc3f3ff11db768fdc4636021394b25dcfdcc68c3f21` |

[字段原方法36](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/form-field-language-original-second.json)、[候选服务60](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/form-field-language-candidate-second.json) 与 [共同结果对照2](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/form-field-language-comparison-v1.json) 同样通过；两份原源及七份候选 SHA 与当前文件相符。原方法来自 GuestbookAttribute/FormLogic 的精确提取，依赖有限内存适配器；这不是原应用完整启动、原 DB/HTTP 或真实锁并发的证明。不要把这些重叠断言相加为兼容率。

失败历史继续区分：[候选恢复首轮0/2](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-restore-entry-candidate-first.json) 是实际 active/missing 恢复被接受的代码缺陷，[修后2/2](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-restore-entry-candidate-second.json) 与本批真实 HTTP 覆盖其有限修复。独立18的 [首轮16/18](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/form-field-and-restore-independent-v1.json) 两个重复定义失败则是夹具错误地清 `id` 而非主键 `attr_id`，见 [说明](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/form-field-and-restore-independent-history-v1.json)，属于测试修正。标签25保留的六项 before 差异是真实选错语言：更换默认/仅启用 en 时 Type/Adv/Guestbookform 读到了主 cn；当前有限对照已相等。原 harness 的静态 root_dir 声明及中途候选适配器错误另保留为测试失败，不能当生产诊断。上述结果本身没有补齐 L04、语言删除 L05、writer L11、worker 传播 L12 或任何待验 UI；语言删除的新证据另见下节。

## 语言删除与 UI 缓存的最终有限复核

语言删除的四项原问题已修：原 schema 的 26 张按语言删除表都有 `lang`，14 张固定文档附表都有 `aid`；`nav_list/nav_position` 原本没有 `lang`，当前仅拒绝目标栏目仍被全局导航引用，保留导航及位置。目标会员留言、会员/商城模型或相关记录、保留文档 `attrlist_id`、跨语言广告/友链/表单/TAG/留言 owner 引用均有明确拒绝。事务引擎预检避免导入 MyISAM 的部分提交，企业自定正文表按实际模型补入。这些保护增强与原版删除范围外数据的行为分开说明。

| 证据 | 只读核对与边界 |
| --- | --- |
| [最终实际 DB72](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-deletion-db-final-v1.json) / [执行源码](../../../webman-cms-archive/20261106-architecture-only-v2/files/tests/compat/verify_language_deletion_database.php) | 72/72、errors空，5份source SHA全部当前。实际服务、ThinkORM、InnoDB；正常单/双目标删除、默认/混合批保护、会员/公共导航/跨 owner/上传关联拒绝、晚 SQL trigger 故障、缓存改写后 precommit 故障及坏缓存回滚。按原 SQL 的157张表名单 `CREATE TABLE LIKE` 复制**当前站点表**，不是原安装157张 MyISAM表原样运行。157份保护行摘要前后映射相等；owned表归零、工作目录移除且本次实查不存在。最终版本保留语言cn/yy逐行比较（配置计数另验），全局canonical/nav及其它语言UI保留。非原版运行、非HTTP/浏览器/并发。 |
| [认证 HTTP29](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-deletion-http-second.json) / [源绑定](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-deletion-http-binding-v1.json) | 29/29；12次认证删除相关请求、1次匿名、4次正常认证请求。独立非超级账号正常验证码登录，bare controller权限403，精确delete规则成功删1个owned空语言；root方法/Origin/确认/空/缺失/畸形/保护/混批/重复请求拒绝。164张CMS+框架表原行/列恢复、身份/语言归零、现场UI字节不变。15份HTTP冻结源与执行SHA相符；当前唯一主体差异是catch内best-effort日志保护，正常HTTP路径不变，该异常由下述两个6项独立报告承接，没有重跑破坏HTTP。非有业务目标删除、非浏览器/并发。 |
| [删除对话框第三10](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-delete-dialog-third.json) / [测试源码](../../../webman-cms-archive/20261106-architecture-only-v2/files/tests/compat/verify_language_delete_dialog.cjs) | 10/10；实际生产JS完整解析及函数在最小DOM执行，当前页面SHA相符。覆盖确认初始不选、目标替换、纯文本标题、取消及既有提交路径；warning显示分支仍另由下一报告执行。不是原生浏览器校验、fetch、权限或持久化验收。 |
| [独立提交6](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-delete-submit-independent-v1.json) | 6/6，当前页面SHA相符；实际提取生产post/onsubmit，以fake fetch/FormData和延迟load验证等待刷新后warning仍显示、业务拒绝不关闭不刷新、默认save保持。无DB/真实HTTP/浏览器/复选框原生约束校验。 |
| [缓存 wrapper 后续26](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/ui-language-deletion-cache-independent-followup-v1.json) | 26/26，当前3份source SHA相符。实际owned文件覆盖目标键或payload清理、其它语言及无语言legacy保留、未变文件字节保持、operation/commit/第二文件失败恢复、坏JSON/header/trailing PHP/符号链接/超大文件/新未锁主题拒绝。DB/advisory与提交失败为确定性适配器，非真实锁或并发。 |
| [排队 UI 写入修后13](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/ui-language-queue-independent-after-v2.json) / [测试源码](../../../webman-cms-archive/20261106-architecture-only-v2/files/tests/compat/review_ui_language_queue.php) | 13/13；实际save/clear与wrapper使用owned文件，严格内存DB和确定性GET_LOCK交错使删除先完成。删除/同mark新ID/正常三分区各走save和clear，保留其它语言payload且拒绝后字节/inode不变。生产2份源及队列脚本与当前相符；引用的通用review适配器整文件随后为新caller补事务适配而变化，保留报告原摘要，不伪称4份都当前。非真实MySQL/多进程/HTTP/完整删除服务链。 |
| [提交后缓存异常6](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-deletion-postcommit-independent-after-v2.json)、[日志也异常6](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-deletion-postcommit-independent-logger-v1.json) / [对照说明](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-deletion-postcommit-independent-comparison-v1.json) | 各6/6，4份source SHA均当前；实际完整删除服务与wrapper、严格schema内存DB和owned文件。generic Cache清理抛错时，已提交删除与目标UI清除保持、其它语言payload不变，返回`cache_invalidated=false/cache_warning`；logger也抛错仍保留成功结果，无补偿回滚。非实际MySQL提交/锁、真实缓存/日志写入、HTTP或并发。 |

[主体独立候选31](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-deletion-independent-candidate-v5.json) 及 [原/候选对照9](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-deletion-independent-comparison-v3.json) 的最终服务SHA为 `8f795d3902bfc6e38977319c827eb19ba6b17d305991ea738be4f5a22faa20fb`。原PHP7.2的6项精确方法有限适配器基线复用，候选重新执行；普通企业清理的26语言表、14附表与9个保留表相符，候选严格保护/自定正文是单列增强。不是原版完整启动/真实DB/缓存文件或并发证明。

两项后续真实缺陷保留已修轨迹。[排队旧版重现](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/ui-language-queue-independent-before-v2.json) 记录scope初查后等待删除，save会重建1条孤儿UI及目标缓存，clear也会无效重写缓存；修复记录初查`_language_id`，在同主题advisory之后事务第一步FOR UPDATE复核mark和ID，目标已删/同mark新ID均写前拒绝。[提交后错误补偿重现](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/ui-postcommit-cache-independent-before-v1.json) 为 `pass=false/reproduced=true`：DB提交后generic Cache清理抛错被外层当作事务失败而恢复旧UI。修复让connection事务处于UI补偿器内，generic Cache清理放到补偿器成功返回后，失败只返回已提交警告；日志为best effort，真实后台脚本await load后再显示warning。两个修复的有限报告均已通过；不把确定性交错升级成L11真实writer。

历史 [DB第三轮72](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-deletion-db-third.json) 只比较cn保留行、没有保存157份逐表摘要或随机prefix，且摘要属于旧输入；保留历史，以最终批次新增cn/yy检查、prefix与前后摘要映射承接。[HTTP首轮](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-deletion-http-first.json) 是验证码识别失败，没有成功登录或删除，164表/文件清理仍完成；不能当生产失败或业务通过。旧dialog9仍是旧输入层，不覆盖最终warning路径。

关键报告SHA：最终DB72 `132b61a0cc48b41f0f59e9a1c0877281a4d35c63a603958a0d1eaff4b02f4bf0`；HTTP29 `838a0d606556379e9182773d61226c775330fddad0f74e747a129ab5e6f3e992`；JS10 `ac86500c5c6a517a6dc6de9121b7b8be2a87459b77720122632ed92723d3abec`；提交后6 `95715fd1100b9d2c8338666b42740b4f8cdf3be5b769254044d9dbc27de434d2`；日志异常6 `f8a58d06364439210050cbaf6a1d53ba3b85bae1a68f6b77a36f453f7d5d4fbd`。本次文档agent未新做DB/HTTP/浏览器操作；L05服务/DB/HTTP有限证据已具备，真实管理员/受限角色UI仍按U06/R04保持待验，S3尚未因此关闭。

## 后续 L11/L12 有限增量

下列报告在原 [S3 v1 manifest](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/stage3-final-evidence-v1.json) 冻结后追加，原16生产/7文档/11工具快照及34绑定报告不改写。只读复核其摘要全部匹配，原 [文档audit](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/stage3-final-documentation-audit-v1.json) 为118检查、213本地链接、缺失0；当前文档允许随新证据变化。后续总绑定由主agent另建，不要求当前文件永远等于旧快照。

| 报告 | 已验有限范围、清理与限制 |
| --- | --- |
| [L11 writer35](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-writer-concurrency-second.json)、[9源绑定](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-writer-concurrency-binding-v1.json)、[释放检查](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-writer-concurrency-release-v1.json) | 35/35；157现场schema空副本及真实InnoDB/实际service，含禁用注册语言。holder在外层事务中完成实际操作但不提交，独立第二进程阻塞后由observer确认真实LOCK WAIT再释放；同目录一成一中文拒绝，创建/同步两顺序均不重复且目标人工译文整行保持。9源前后及冻结SHA相同，157原表行/列摘要映射相等、owned表0、工作目录移除；释放报告记录6连接无遗留事务。非原MyISAM/HTTP/UI，也未穷尽全部writer交错。 |
| [L12 worker66](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-workers-http-second.json) | 66/66；5认证、40公开（8建连探测、16before、16after），PID73557/73561在每个两语言×设备×列表详情分区及更新前后稳定；目标栏目名称、文档标题和各端正文更新，主语言内容保持，真实持久化双正文另验。175源前后及复核时当前SHA相同，40份HTML摘要与观察相符；19表原行/列恢复、owned0、4模板和8所属编译/缓存移除。保留证据HTML目录。复用连接对象/逐请求稳定PID，未完整追踪TCP socket身份；非浏览器、逐字原HTTP或所有主题/部署。 |

[L11首轮](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-writer-concurrency-first.json) 在测试运行目录隔离守卫失败，0业务断言；只改bootstrap配置加载顺序，原表及owned清理完成。[L12首轮](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-workers-http-first.json) 有3认证/81公开，内容断言均正确，但目标mobile列表24次短连接采样只有一个PID，未满足该分区双worker；19表/所属文件清理完成。第二轮只改采样工具，保留原失败，不能把首轮当双worker通过。

L11报告SHA `55c8b8f05b487abd7dcb73c9b9b1bca8f3f644f3910988fe227eca9be2f4ef85`；L12报告SHA `d4cf7a8c9eba77a669bdfeb3fbd54b677307dc9f6bdac6c7a21c64a459b7b1ba`。本次文档复核未新做DB/HTTP/浏览器操作。L04后续有限分区见下节；浏览器重试仍 `browser.user unavailable`，U01–U06/R01–R04保持 `pending_environment`；S3继续active。

## 后续 L04/L05 持久化及公开终态

| 报告 | 已验范围与限制 |
| --- | --- |
| [L04持久化第二轮29](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-persistence-second.json)、[13源绑定](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-persistence-binding-v1.json) | 29/29；实际LanguageService save(copy_documents=1)/synchronize、157所属空InnoDB结构和晚SQL trigger失败整库行列回滚；新建栏目/legacy栏目定义、表单字段/广告友链加mark，现代组/定义不加；目标owner/parent/topid/双正文/资源路径、原canonical别名和人工译文/两次sync保持。PC/mobile×两产品为四个断言、各含auto与newattr共八输出。13源前后/快照/复核时当前SHA一致，157原表行列map相等、owned表/workspace归零。原方法3/候选8/对照12使用实际原方法与严格内存适配器；未启动原完整应用/原DB。list_id=0/custom/legacy/standalone值保留是候选扩展，此历史批次未验afterAdd配置/资格；新有限证据见下节。 |
| [L05业务公开终态第二轮26](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-public-deletion-http-second.json) | 26/26；4认证POST、16公开GET，一个目标有栏目/文章/双端正文，删前8页200，删后目标列表详情双端4页404，主体4页200且原行/正文逐字保持；直接核target语言行已删、另一owned语言保留及system计数。205源前后及复核时当前SHA相同，16份HTML SHA相符；164原CMS/框架表行列map相等、owned0、4模板/8所属编译缓存移除。UI基线文件数0，确认未新增UI文件，不能作为非空UI缓存保留证据；非空缓存仍由既有wrapper26/DB72各自范围承接。复用已授权root API session，非新受限角色登录、真实确认UI、全模型/全主题或全部语言URL部署入口。 |

LanguageService当前SHA `e9e2a0b6cefc94f62b0dd0cf856973dc7b53e6909f5cea2c01a4e2984822ff75`；L11仍保留旧 `7ef8b4a16bb2007472a7c2dd0cdc5bae746e0bc0d715007282d3d8e1b86cdf80`。[源码差异复核](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-persistence-source-review-v1.json)与实际快照diff相同：save新语言传true、增量默认false，只对新插入显示名变更，排序语言锁、事务、绑定查询和document copy结构未改。不把只读差异复核冒充当前源重跑并发。

[L04首轮](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-persistence-first.json)已过21个DB检查后，测试render漏Runtime::boot导致常量未定义；所属清理完成，第二轮只修测试bootstrap，无生产缺陷。[L05首轮25](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-public-deletion-http-first.json)原断言均通过，但未直接核语言行/counter；保留报告及[当时两工具](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-public-deletion-http-first-source-v1)，第二轮补验证器到26，不改写为生产失败。原真实前缀不一致和各测试失败仍在独立对照报告留存。

L04报告SHA `83d2a938bf633e7c6b308350f6d6dc598a40c5a77875d962ad8e7551250b5e36`；L05报告SHA `f17822b43921c00ab89b916c2acd2941ad32d94ef27fd1ed5f059fa476cb94dc`。本批由[follow-up清单](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/stage3-followup-evidence-v1.json)及新快照绑定，旧v1不覆盖。文档agent只读复核未新跑DB/HTTP/浏览器。

## 不阻断本地 S3 的采样边界

已提供语料的CSS递归并非空白：[PC phase2](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/template-page-assets-pc-phase2-current-20261103a.json)与[mobile phase2](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/template-page-assets-mobile-phase2-current-20261103a.json)已有有限递归资源依据；16CSS的31条依赖边/43个唯一资源均在两报告内，当前无@import/外链。原包/既有替代身份另见[资源清单](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/asset-inventory-post-comparison-v12.json)及[替代manifest](../../resources/compat-assets/manifest.json)。这不扩大成当前全部冻结主题集成、视觉、真实触摸或未提供主题验收；旧部署62直接资产批次仍保留它自己的非递归范围。

- 不要求所有任意语言名、HTML字符、目录拼写、自动条件、模型×主题×设备×角色×错误组合全部枚举；采用上面的共享/独立、主/非主、启用/禁用、合法/冲突/失败等有限分区。新出现的真实缺陷仍必须单独登记与解决。
- 未提供官方主题、所供语料之外的CSS/资源、真实手机触摸归 S5/S6；S3 使用现有未改原 PC/mobile 模板和实际资源链。不以“所有主题未验”否定已经走通的后台生命周期。
- 有效地图 AK、真实官方语言包外网、生产 SMTP/SMS 属外部依赖验收，保留 B01/B08/E01 状态、配置要求与未验证事实；本地 loopback、变量编辑或参数保存不升级为真实外部服务通过。本地 S3 门槛不要求等待不可得的外部服务，但不能因此宣布整个项目交付完成。
- T17、任意严格类型表达式、所有标签属性组合属于 S4严格兼容；已知会破坏本章正常生命周期的原/候选差异不能借此移走。L04的标题/参数合同已有有限依据，配置/资格已获有限分层证据；single有限结构/metadata及正常query链接已验；非空自定义single与显式结构修复已有后续有限增量，dyn2其它模型/入口采样边界及未知孤儿迁移继续在L04核对。
- 原版会产生孤儿/跨归属损坏、MyISAM局部写入等行为，允许保留已明确的事务/归属保护增强，要求可解释拒绝和原数据不变；不把复制数据损坏设为通过条件。

## S3 有限关闭条件

L01–L12 的必需分区须有当前适用证据；L04标题/参数与实际持久化29、L05业务删除公开终态26、L11真实writer三个受控顺序、L12双worker更新传播均有后续有限报告；此前DB72/HTTP29与HTTP81保留各自范围。L04配置/资格有限方法及DB已补；正常query链接第五轮75已过有限冻结源；single有限结构/metadata已获DB33/HTTP85；非空自定义字段及显式结构修复已有后续有限增量，dyn2失败及原有限顺序观察保留；L05/U06/R04真实删除UI及U01–U06、R01–R04每条必需的浏览器成功/拒绝和精确清理仍待环境。原业务缺陷、待补执行和环境阻塞分开记录；不将HTTP代替UI，不以无限组合穷尽另设门槛；S3继续active。

达到这份有限清单可由主 agent 判定本地 S3 门槛闭合，并进入 S4；所有阶段外边界继续保留。本文没有宣布 S3、全部官方模板兼容或项目完成。

## 配置/资格本轮状态（未最终冻结）

[配置DB20](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-config-persistence-first.json)、[资格/新增文档DB25](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-incremental-eligibility-first.json)各自通过，都是157所属空InnoDB结构、真实晚trigger回滚与原157行列恢复/owned0，不是新原完整应用/浏览器证据。[配置原18](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-config-independent-original-v1.json)/[候选19](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-config-independent-candidate-v2.json)/[16合同](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-config-independent-comparison-v2.json)与[资格内存25](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-copy-eligibility-current-binding-v2.json)分层记录，不相加为独立case总数或兼容率。

[HTTP first](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-config-http-first.json)是闭包warning工具失败/164恢复；[second](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-config-http-second.json)是URL断言与attr_id工具缺陷，[同token清理receipt](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-config-http-second-cleanup-repair-v1.json)精确恢复164。[third](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-config-http-third.json)1认证/2公开已过SQL和PC query标签，正确生成dyn2链接后跟随404，finally164恢复/owned0/模板移除。无效cookie、main fallback、对照与mobile未到达。[原顺序13](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-dynamic2-path-order-original-v2.json)是PHP7实际方法+memory层的晚pathlang缓存miss，不是原最终HTTP404；正常query第五轮75已过有限冻结源，[single修前结构/metadata](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-language-copy-contract-before-v2.json)保留失败；修后有限DB33/HTTP85见下方，非空自定义及显式结构修复的后续有限证据见2026-11-04增量。40任务与22本章ID/order不改，S3仍gate=false。

[正常query链第五轮75](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-config-http-fifth.json)完成四个resolver/source分区、4认证/16公开、PC/mobile原global/type与生成链接200，20响应SHA匹配，164行列恢复/owned0，两模板/两所属编译文件移除；UI基线0、非非空缓存证明。该批冻结LanguageService2359/Controllerc2ad，single变化后的有限适用性见source-path-review-v3只读24项，旧配置链未重跑；[第四轮工具失败](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-config-http-fourth.json)与第三轮dyn2失败均保留。有限配置链不替代后续custom/显式修复增量或真实UI门槛。

## 单页有限修复与当前源码收口（2026-11-03）

[单页分层绑定](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-language-copy-repair-binding-v1.json)分别记录原方法64、候选对应64、增量保留15、关系映射12；[规范化逐项比较28](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-language-copy-repaired-comparison-v1.json)、资格回归29、配置回归19各为独立有限层，不相加为兼容率。当前`LanguageService`为`0e41fc44bdd7bf2af5736002bc21a36fccc2b00e231cb3cd788ee3509e1a21d2`。[真实DB第二轮33](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-single-persistence-second.json)和[真实HTTP第二轮85](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-single-http-second.json)已分别通过。新语言docs0仍初始化单页结构，docs1只初始化PC/mobile正文，保留目标stub metadata/editor/时间；后续增量保留人工或空正文、既有日志/绑定，并映射普通joinaid及专题引用。DB使用所属157当前空结构，原157行列摘要恢复、所属表/workspace0；HTTP10认证（8POST/2GET）与12公开、22响应、221源快照，原164行列恢复、11表owned0、两模板/两所属编译移除。API保存/重开属于接口证据；本轮UI基线0。

[只读源码路径复核v3 24](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-language-source-path-review-v3.json)说明历史2359配置HTTP75/配置DB20及article-only资格DB25的有限适用性：config方法逐字未改、其输入不进入新channel6路径；没有重跑旧业务或writer测试。旧资格memory25包含single，受影响路径以新single证据承接。L11旧输入无single，不能推出全部并发单页通过。[HTTP独立审计v2 139](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-single-http-second-independent-audit-v2.json)只核保存字节/SQL/清理，不发新请求；v1遗漏runner文章数量断言变化，失败保留。原日志已清理，仅journal的8日志ID及actor保留，不能独立重放原log行；missing-counter补建分支本轮allowed_pairs为空，未触发。

dyn2其它模型/入口采样边界、U01–U06/R01–R04真实UI、未知孤儿及完整历史迁移、T17和未提供官方主题仍开放。非空自定义字段与显式历史结构修复已有2026-11-04有限增量，见[生命周期记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-lifecycle-verification.md)；普通语言同步不会自动修复历史目标。多活跃single owner仍原子拒绝；不复制原all-single-site条件bug，也不把候选关系安全重映射称为原损坏跨语言ID的逐字等价。S3 active/gate=false，40任务和22有限ID/order保留。旧final/followup/config-single清单、快照和失败报告保持原字节，本次另作新增量绑定。

[DB独立审阅26](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-single-persistence-independent-review-v1.json)只读核对8源、两个SQL trigger marker、同对象重试及157原表清理raw maps；中间owned157回滚/幂等maps、人工最终行与special节点raw没有保存在JSON，这些过程结论依赖绑定工具已执行的boolean断言，不能称为原始行可完整重放。初始stub、marker及原157清理maps则有直接保存证据。


## 自定义语言目录与子树路径增量（2026-11-04）

本节保留前一冻结批次的范围；当时的混合子树页面待验状态由后面的“混合子树与继承后续增量”有限更新。

已修复注册语言根提前首页返回和父栏目更新全部后代的差异，见[分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/custom-language-directory-routing.md)。原PHP7路由/URL有限对照与候选修后inlet0/1各12项通过；实际HTTP修后91项通过（2认证POST/16公开GET），原type/channel生成8条PC/mobile链接原样跟随；修前两项显式默认语言200/404差异保留。子树真实方法内存408与另行执行的[实库81](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-subtree-persistence-first.json)分层通过：6所属表/16节点、停用根、真实晚SQL故障全回滚及同服务重试，原157表摘要一致，所属表/trigger/workspace清理。混合子树更新后的页面链路仍待验。HTTP两轮各164原表行/列和默认cn的3SEO原行恢复、10表owned0、两模板/两所属编译文件/两条已保存原日志清理完成，UI基线0。S3 active/gate=false，实际UI、T17、未提供主题与最终门槛不变；不重复旧67批次或聚合为兼容率。


## 混合子树与继承后续增量（2026-11-04）

L07/B17 的有限公开路径与继承缺口由[分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/custom-language-directory-routing.md)承接：历史HTTP160绑定74e43，当前Article HTTP280绑定f5b1，不能混作同一源码通过。实库82另覆盖异模型Product仅继承权限与晚SQL故障回滚；在该历史冻结时Single旧empty_logic仅有原方法前缀/候选save内存对照；下节以SQL146/HTTP272补齐有限继承实库/HTTP。更广SEO、grade移动、实际UI及最终门槛保留；22个L/U/R ID和顺序不变，S3 gate=false。


## Single 继承实际SQL与双端读取增量（2026-11-04）

[分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/custom-language-directory-routing.md)现补齐上一冻结时仅memory的有限Single范围：原PHP7/候选PHP8方法各72、第三轮比较28与来源合同44；[实际SQL146](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-category-inherit-persistence-first.json)使用8张所属InnoDB表、16节点和15份既有双端正文，父posted0/子孙继承old1、异模型仅权限、12排除行与停用根、晚SQLSTATE45000/1644整体回滚及同对象重试通过，14来源与原157表摘要保持、所属表/trigger/workspace清理。

[实际HTTP272](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-single-inherit-http-first.json)有3POST＋18重开GET＋20公开GET、41响应/3SQL/241来源/16条原type href原样跟随；child/grandchild由实际singlePage从空写入，继承后child双端选grandchild但保留请求typeid和原附表，root/second非空正文为seed前提。164表摘要恢复、10owned表0、4模板/4编译/3条原始日志清理、cookie不变，UI基线0。探针并非官方整页，API并非浏览器；该冻结时继承tempview的Article详情由后续增量有限承接，grade/其它SEO、权限执行与实际U/R UI、T17及最终门槛仍开放，S3 gate=false。新绑定为 `runtime/compat/stage3-single-inheritance-evidence-v1.json`，历史冻结不改。

[Single SQL独立离线复核359](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-category-inherit-persistence-independent-review-v1.json)重算64份所属raw行摘要、核三阶段15对archive/body完整行保护与14份来源；359/64是复核与完整性检查数，不是新增业务数。原columns及原157表raw记录未保存，只能比较已存摘要；数据库事件与清理依据冻结执行证据，不称完整原raw独立重放。

[Single HTTP独立离线复核195](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-single-inherit-http-independent-review-v1.json)重新核对241来源的前后副本/当前字节、41响应、3份SQL共27个所属分区raw摘要、18次API重开、16次原href跟随及正文/aid/UA记录、164表摘要与3条原始日志清理凭据。195/27是复核与完整性检查，不能追加成业务用例；离线不能重新观察历史网络header或重查已删除owned行，状态/UA/数据库清理仍依据保存执行记录。该层不替代16节点排除分支SQL，也不扩大seed正文或UI/官方整页范围。


## Article 继承详情模板字段增量（2026-11-04）

[本轮分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/custom-language-directory-routing.md)补普通`.htm` Article详情：修前文件选对但 `field.tempview` 未填栏目值；Controller `7e014dfb` 在详情合并后填继承值，并按最终字段选择模板。[修前HTTP185](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-article-view-inherit-http-before-v1.json)仅4处有效字段断言失败；[修后HTTP185](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-article-view-inherit-http-after-v1.json)通过。各3POST＋12重开GET＋12公开GET、27响应/3SQL/244来源/8条原arclist href原样跟随：两篇实际插入文档，空模板BASE→INHERITED、显式模板恒OVERRIDE，双端正文及API原始空值/aid/typeid保持。各164表摘要恢复、10owned表0、10模板/10编译/3原始日志清理、cookie不变、UI基线0。

[表达式修后28](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/article-tempview-choice-after-v1/report.json)与[离线96](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/article-tempview-choice-independent-review-v1.json)只核12普通输入的填值/合并/选择，4异常后缀差异另保留；不是完整原控制器或原HTTP。[源码差异14](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/article-template-source-delta-v1.json)证明改动限于renderArchive，旧Single HTTP没有在新Controller整文件SHA上重跑。新探针不是官方整页/API不是浏览器；其它模型、grade/SEO、权限/UI、T17及最终门槛仍开放，S3 gate=false。上一冻结的继承tempview待验由本节有限更新，新绑定为 `runtime/compat/stage3-article-template-inheritance-evidence-v1.json`。

[Article HTTP独立离线复核273](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/article-detail-inheritance-http-independent-review-v1.json)核两轮各244来源、27响应、3SQL和8条原href，重算六份SQL快照共54个所属raw分区摘要；确认修前仅4个字段失配、修后0。273/54是复核/完整性数，不追加为业务数。原164表raw/完整列记录与编译文件raw字节未保存，原表摘要与清理依据冻结记录；离线未执行DB/HTTP，不能重观察历史header或重查已删除行。NULL/字符串0仅表达式层，4异常后缀未修且未进入HTTP；10份新探针不是未修改官方整页，两轮token/ID不同，不是同ID或原框架HTTP差分。


## 普通栏目提升与重新挂载增量（2026-11-04）

[本轮分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/custom-language-directory-routing.md)修复 Product 子栏目提升为顶级时 `channeltype` 被错误改为自身模型的问题：当前 `CategoryService=5711001f` 在顶级编辑保留原组值，创建顶级仍取自身模型，挂到非顶级仍取新父组值。原完整提取 `updateData/getHasChildren/arctype_options` 与候选真实完整 `save` 的[修前74](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-move-save-memory-before-v2/report.json)为诊断通过/parity=false，仅混合模型提升的编辑节点＋4合格后代有5处channeltype差异；[修后74](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-move-save-memory-after-v1/report.json)五个正常场景parity=true。[独立内存复核141](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-move-save-memory-independent-review-v1.json)确认各14节点、4合格/6排除后代、5文档和Article/Product/Single双端附表保护。该层用严格内存适配器，不是实库或完整原控制器；首轮namespace工具失败保留。

[HTTP修前544](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-promotion-http-before-v1.json)保留18个提升阶段channeltype传播断言失败；[修后544](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-promotion-http-after-v1.json)通过。各2次仅 `id,parent_id` 的真实POST、12次API重开GET和24公开GET，38响应/3SQL/247来源；四节点混合树经seed→提升→挂新Product根，18条PC/mobile原type/channel href原样跟随，显式原field标签和变量核grade/topid/channeltype/current_channel/dirpath/DIY。两根完整行、模型及人工DIY/模板/规则保持，无文档写入。各164表摘要恢复、10owned分区0、4模板/4编译/2原始日志清理、cookie不变/UI基线0。[独立HTTP复核493](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-promotion-http-independent-review-v1.json)重算总54个所属raw分区摘要；复核数不追加业务数，原164raw/完整列及移除编译raw未保存，历史header/数据库清理依赖执行记录。

[来源差异3](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-promotion-source-delta-v1.json)确认仅顶级编辑组值选择改变；旧Article/Single HTTP未在新整服务SHA上重跑。上述只有限承接旧“grade移动待验”的普通一致树分区；损坏/陈旧grade、排除祖先跨越、深度限制与真实UI、更广SEO、受限角色、并发/回滚仍待验。4新语法探针不代表未修改官方整页或全主题；T17原生捕获调查和缺陷保持、S3 gate=false。新绑定为 `runtime/compat/stage3-category-promotion-evidence-v1.json`，旧冻结及40任务/22个L/U/R顺序保持。


## 游客读取限制与关闭会员提示增量（2026-11-04）

[分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/public-read-access.md)补关闭会员时的读取权限：当前Controller0167在最终字段后检查列表typearcrank/page_limit1与详情自身arcrank或栏目page_limit2，关闭入口返回原首页目标/wait5。原方法修前34诊断parity=false，当前方法after-v2的34及独立离线280通过；六受限/七公开控制有限。Article草稿仍原成功提示200/候选404；普通列表缺arcurl默认原mask0 Notice报告后302、web_exception mask-1抛ErrorException，候选空值防warning是明确差异。

历史[修前HTTP408](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/public-read-access-http-before-first.json)保留68失败，[中间6245修后506](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/public-read-access-http-after-first.json)仅权限/正文/首页层通过，14保存提示均wait3而未断言wait。各11POST＋32API重开；8SQL/295来源/4原arclist href、46允许stage读取和20受限含10query伪装控制；修前78/修后102游客GET。各164原表摘要恢复、10owned分区0、4模板/4编译/11raw日志清理、cookie不变/UI0。独立离线1005核两轮raw并重算144所属分区摘要，不能重观历史header/原164raw或删行；复核器首轮自身失败保留。

当前0167相对6245仅显式wait5，[窄HTTP26](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/disabled-login-wait-http-first-v1/report.json)8只读匿名GET、PC/mobile×query/path×HTML/AJAX，实际wait5/home及原JSON元数据通过；[独立32](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/disabled-login-wait-http-independent-review-v1.json)验证8raw/8快照/295来源。未重跑历史43后台操作或所属语言链路。新探针/API不等于浏览器/未改官方整页；Single实际HTTP、预览guard、其它模型/SEO/角色/并发、真实U/R UI、T17和S3/S4/S6仍开放。40任务/22LUR顺序及其它39任务含T17不变；B17只追加新字段。新manifest/source为 `stage3-public-read-access-evidence-v1.json` / `stage3-public-read-access-source-v1/`，此前T17拒绝冻结及所有失败保持不变。检查数不累计成业务数或兼容率。


## Article模型草稿入口增量（2026-11-04）

[分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/draft-model-entry.md)承接access冻结时的Article草稿差异，当前Controller8352对显式企业模型view的rank-1返回成功提示/首页/wait1，直接公开View仍拒绝。原方法第二轮24在PHP7/8各36模型＋4guard输入；候选47输入×before/after、245内部＋6wrapper，真实提取dispatch/view/viewPage/readAccess，context/query/Jump/renderArchive为内存边界。其它五模型无实际HTTP，整文件delta以外及真实preview/renderArchive/readAccess字节未改；首wrongdirname测试假设失败保留。

实际Article[修前220](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/model-draft-entry-http-before-first.json)58失败，[修后220](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/model-draft-entry-http-after-first.json)通过：各4POST、6API重开＋2认证预览GET、44匿名GET/56响应、3SQL/298来源、4原arclist href生成并原样跟随8次，发布/退稿双端正文/计数保持。非cnHTML显示success、wait1/root；修后6AJAX完整对象含data空串由[独立2292](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/model-draft-entry-http-independent-review-v2/report.json)核实，总112raw/6SQL/54所属分区摘要/1192source copies；其v1四个审计假设失败保留。各164摘要恢复、10owned0、4模板4编译4raw日志清理、cookie不变/UI0；离线不能重观历史header/删行，原164raw/完整columns及删编译raw缺失。

旧access文档及0167/506层原样保留；新manifest/source为 `stage3-model-draft-entry-evidence-v1.json` / `stage3-model-draft-entry-source-v1/`。Single原success(msg)不传url，null目标由HTTP_REFERER/Url::build(null)决定且wait1，候选wait3/back合同仍开放，不写原Single固定首页。其它模型HTTP、正权限预览/会话角色/更广SEO/真实UI、T17与S3/S4/S6门槛不关闭；40任务/22LUR顺序及其它39任务不变，B17只追加新字段，检查数不计兼容率。


## Single草稿提示默认过滤链与真实HTTP增量（2026-11-04）

[分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/single-draft-dispatch.md)承接8352的Single wait3/back差异，当前ae8d局部采用原strip_sql/PHP7 ENT_COMPAT，保留显式空Referer，缺失时按传入规范化MCA构造无tid目标并wait1。原App默认过滤链不可从convention空filter推断；旧unfiltered source828120/second198保留为其有限配置观察。新filtered原PHP7第二轮120输入/1206检查涵盖安装关闭、缺失内建开启及合成开启LF；首轮120stderr适配器边界失败保留。候选140/1041重用120原观察＋20新原输入，仅执行Request/filter/url局部内存策略；[源码复核122](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-draft-dispatch-source-review-first.json)65来源确认整文件只替换lists的Single草稿分支，其它19方法及JumpResponse不变，不把token/lint当生产控制器执行。

实际Single[修前909](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-draft-dispatch-http-before-first.json)保留76失败（24HTML目标/24wait/28完整JSON），[修后909](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-draft-dispatch-http-after-first.json)通过。各3POST、9认证GET重开、60匿名GET/72响应、3SQL/329来源每phase，54原PHP7进程含2纯预检。一个所属语言/无限制Single实际草稿创建→发布→退稿，双端正文/aid/typeid保持；两入口×两端×三Referer×HTML/AJAX两轮48响应，另4身份查询AJAX、4直接View404、4发布探针。HTML rawhref逐字匹配原过滤值、解析href只解码一次；JSON保留实体及data空串。不跟随提示目标，也没有本批生成导航链接跟随验收；真实HTTP只观察安装默认关闭SQL过滤，开启/缺失配置只在方法层。

各轮164摘要恢复、10owned0、2模板2编译3raw日志清理、cookie不变/UI0；离线不能重观历史header/删行，原164raw/完整columns及删编译raw缺失。[独立HTTP离线3795](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-draft-dispatch-http-independent-review-v2/report.json)通过，1806唯一绑定、144raw/6SQL/108原进程/104期望对应、1316来源副本与54所属行序列化摘要重算；只读证据不重观DB/HTTP。[首审50来源数假设失败](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-draft-dispatch-http-independent-review-v1/report.json)及[修正凭据](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-draft-dispatch-http-independent-review-v1/failure-receipt-v1.json)保留：每轮25HTML各30来源、29AJAX各31来源，AJAX仅多Json.php，原哈希均匹配。新绑定/source为 `stage3-single-draft-dispatch-evidence-v1.json` / `stage3-single-draft-dispatch-source-v1/`，旧Article/access及所有失败冻结不改。更广Request.input默认链留T16独立审查且不改其字段；40任务/22LUR及其它39任务不变。真实UI/完整原框架/子目录HTTP/其它模型/更广SEO权限会话并发、T17、未提供主题和S3/S4/S6门槛仍开放；检查数不是业务用例或兼容率。


## 栏目字段三类型有限增量（2026-11-04；有限范围冻结）

[栏目字段分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/category-custom-fields.md)关联B04/B16/B17，不新增或重排22个L/U/R，也不将B09改作字段验收。三类新-99字段checkboxs/radios/img的实际HTTP644及独立offline1656有限通过：已有Article父子、全局0/child绑定、真实addon编辑/重开、稳定选项ID、radio改名、省略保值、外来/未绑定拒绝、显式空/0、正常删除和双端Lists/View/Article选定字段。一个API创建文档有真实双端正文；栏目本身是已准备的existing行，不能算栏目新增addon验收。

当前1783方法/NULL fresh802和后台v3来源分层保留，旧706只是冻结ae8d+内存设计。HTTP children只有现存parent1/child0；没有回收/恢复转换或生成导航href跟随。59raw/24原PHP7实SQL期待/5SQL/336来源每phase与offline876bindings有保存；164表行摘要/fullcolumns/结构indexes、13owned0、8模板8编译收据4备份12日志清理，UI0。offline不重观网络或已删行；原164基线raw rows未全保存。

template独立语义2108/绑定复核249与main晚DDL实库third35有限通过；157owned及原164摘要/清理、同对象重试有保存执行证据，异常raw消息/失败SQL和strict/lock原标量未另存，保留复核边界。first setup冲突及second索引排序工具失败不改，独立DDL247已通过，六业务表108raw摘要/六备份可复核，其余151owned/原164仅digest maps；成功delete保留typename索引尾，不还原初始全schema。首审错误要求delete有SET保留，新审只改期望。本增量冻结到新的category-custom-fields manifest/source；旧Single及全部失败冻结不改。legacy media完整编辑为明确源码缺口，新增栏目addon、跨模型/语言实写、受限角色真实UI、并发/worker/子目录和完整官方主题仍待。[24小时清单](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/delivery-priorities-24h.md)仅安排实际剩余工作，不将环境未提供写成代码通过；S3/S4/S6 gate=false，T16/T17及40任务状态不据此整体关闭。


## 导入legacy media编辑有限增量（2026-11-04）

[栏目字段记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/category-custom-fields.md)追加既有media完整编辑、scalar保存、省略保值及双端选定输出：DB52/独立543，第二轮实际HTTP674（69raw/8SQL/24原期待）及独立离线2837在各自有限范围通过。首轮seed遗漏ifcontrol的HTTP失败与首审假设失败均保留；第二轮只改seed不改生产。新增media、两向dtype转换保守拒绝是原版兼容差异；media物理改名仅DB，HTTP保留radio改名。匿名本地mp4 Range206只证明128字节读取。旧三类型冻结不改，本项承接其legacy编辑缺口；新增栏目/更广语言模型/角色UI/完整主题仍开放。40任务、22L/U/R、B09及T16/T17不改，S3/S4/S6false，详见分层记录。


## 当前来源独立部署与供给主题HTTP增量（2026-11-04）

[分层记录及操作入口](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/standalone-corpus-2026-11-04.md)：第三轮独立目录/新DB/8793实际运行44页HTTP（22PC/22mobile），递归资源71/72失败0；fresh164、重复初始化两次、双worker探针、重启12页探针、所属165表备份恢复/清理、来源未变有限通过。独立保存文件2396/59bindings核44raw与比较输入，资源raw/历史DB与清理仍为收据。两个原mobile零字节页显式PC fallback。两项指定历史tagid的AJAX仍pending，报告缺pagesize原因已源码否定，追加更正不改报告；首22/第二44页失败保留。历史44浏览器与当前44HTTP分层，完整旧站迁移仍候选，U/R UI/T16/T17/未供主题与S3/S4/S6false保持。


## 当前独立部署第五轮44页＋2AJAX有限通过（2026-11-04）

[部署分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/standalone-corpus-2026-11-04.md)追加第五轮aeb60：44原页＋双端AJAX实际通过，递归资源PC71/mobile73失败0。实际生成query/七attrs/PHP序列化md5/owned tid72/mobile发出hash及双typed code1/lastpage0由独立离线2468（112bindings，含第五46raw与第四45raw）核对。测试page2/size1，不是浏览器控件pagesize3或全分页状态。第四11文章的PC lastpage1断言失败/复扫不唯一保留，第五只改测试夹具为14文章及复用首次query，无生产变更；第三44＋pending和错误reason更正继续保持历史。fresh164/repeat/worker/restart12/165恢复清理有限通过，完整旧站迁移候选、真实UI/其它主题/T16/T17与S3/S4/S6false保持，40任务/22LUR不改。


## 当前已有站受控迁移有限通过（2026-11-04）

[迁移记录及入口](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/existing-site-migration-2026-11-04.md)追加第四e2fa实际24步骤：当前源164 InnoDB只读捕获/事后摘要不变，完整157CMS导入/初始化后精确对应，fresh wa、两次重复init、HTTP前165表备份恢复，原official单语言三公开模型2×12路线/各68资源与所属清理有限通过。每轮3资源path属2个明确compat替代，不称原bytes；无rawDB/DDL/HTTP保存，独立322/84bindings为digest-only/执行收据复核。前三失败及所有旧freeze保留。此受控演练清理副本，不发布生产；任意旧schema/全主题/真实UI/T16/T17及S3/S4/S6false继续保留，40任务/22LUR不改。


## Request默认过滤与当前集成有限增量（2026-11-04）

[Request记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/request-default-filter.md)绑定已装四来源（Request89e89），补原默认过滤/strict默认与loaded source has；实际三profile模板方法、has第二23分层及已装34/7有限证据保留。当前受影响Single HTTP909/独立1773通过；当前f30b fresh独立部署44原页＋2AJAX/资源71+73及Lists过滤20raw，两PID×双端四ABA序列、6表摘要/2模板2编译清理和总cleanup通过。独立283/119bindings核46+20raw与原期待，原框架/开启profile HTTP/lazy来源/UI/all themes仍未验。预filter迁移来源不改，本轮未重跑157迁移。只向T16/root追加具名增量，原任务字段/40顺序/22LUR不变，T16总体/T17及S3/S4/S6false保持。


## U01 / U02 实际管理员浏览器后续（2026-11-04；有限范围）

[本轮独立记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-ui-followup-20261104.md)补 U01/B01 和 U02/B07 为 `finite_required_path_verified`：这是新的实际 Chrome extension1 成功链，前文 `pending_environment` 属较早入口记录并保留历史；不新增或重排22个L/U/R行。U01真实保存→重开→确认清除→重开，36→27恰好删除A/PC九行、另27行逐字段不变；浏览器clear不传page，本夹具仅main。独立offline26/18bindings与六截图核对通过。

U02真实A→B覆盖及四份人工译文编辑/重开/增量同步通过：目标文章1521→1523、单页1522 ID及覆盖时metadata保持，随后译文/ID/映射稳定。后续8原模板别名＋236依赖复制后，目标B原PC/mobile Article/Single四张 `original-*-second` 页面显示正确译文；早期simple页面及错误 `v=mobile` 不作原版手机通过。两个测试语言缺basic的首次失败保留，补四条明确的list_symbol/basic_indexname后完成有限验证；未改生产标签。

[独立U02/最终比较476](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/admin-visual-4c9d08b2-browser/u02-independent-review-v1.json)绑定103文件/122唯一原来源，四原版截图人工查看；不是476业务case。四个精确日志1511–1514删除，B07九项/base实际十一项remaining全0；[最终164](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/admin-visual-4c9d08b2-browser/final-164-compare.json)无新增豁免地精确恢复8024原行多重集及schema（AUTO_INCREMENT计数除外）。离线在私有输入处重算基线，不复制raw；最终实时状态与删行仍为执行收据，不宣称重查live。

原PC footer sitemap把带lang/goto的global URL直接拼suffix，与原版分支同逻辑；仅记录，不判新生产缺陷或全导航通过。此前入口环境失败、guard初始化/缺typeid工具失败和首次missingbasic等均保留。具名增量只追加root/B01/B07，旧字段/旧freeze不改；U03–U06、R01–R04、有效地图/外部服务、其它主题、T16总体/T17及S3/S4/S6false继续保持，后续指定项由各自新证据更新。[本轮冻结](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/stage3-ui-U01-U02-evidence-v1.json)。


## 2026-11-05 U03/U04/U06 成功终态增量

本节追加后续实际结果，保留上面22项原编号、顺序和历史pending文字。U03/U04的469be1a025d7与U06的e930f5a41b68已完成指定正常管理员链及精确清理；不把历史阶段描述改成当时已通过。对应[U03/U04记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-ui-U03-U04-20261105.md)、[U06记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-ui-U06-20261105.md)和acceptance-plan具名增量。

U03变量整组、U04两form_type字段均在具体用户确认后实际永久删除，保存终态/原语法双端probe与SQL-derived快照一致，keepers、父对象及提交ID保留。初始B标签=A差异仍单独复核；父对象/提交只有ID，不能宣称完整行哈希保护。最终10所属分区0，原8024行保护、保留30条历史日志，exact=false。

U06先修复category-fields.js静态路径404，再实际提交目录冲突并中文拒绝、tracked行/state不变；正常目录保存重开形成含disabled语言的三语言族。实际确认删除独立活动语言A/ID4及15行后，A四公开路由404，主/B八路由200且正文/ID保持；24 raw HTTP与24浏览器DOM保存。19分区清理0，本批两日志精确清理，原8024保护、旧30日志保留，与batch-before multiset相同，exact=false。

R01–R04真实受限角色仍未据这些正常管理员操作通过；当前角色fixture已准备，需正常验证码登录后执行。S3/S4/S6 gate继续false，T16总体/T17及未取得主题不变。


### 初始标签语义的后续原PHP7复核

[四独立PHP7原方法执行](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/u03-u04-initial-label-php7-oracle-v1/REVIEW.md)已复现缺system_langnum/default1时A/B标签同取A，但B历史仍取B；显式3时B标签转B。初始fixture没有启counter而expected按B，是该有限分区的准备假设不一致，没有发现该点生产偏离原版。旧expected/初始DOM保持，不全局修改生产；原始完整类和is_language原函数字节实际执行，依赖严格内存query/主行最小适配器，非真实原数据库/框架HTTP或全类型证明。

### 独立公开采样与富文本确认缺口（2026-11-05 12:59）

[保留副本](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/retained-deployment-20261105.md)的标题保存/重开/双端/恢复及后续12原页、73直接资源、5私有路径检查已有实际证据和独立保存复核。首轮产品数字aid工具假设失败保留，第二轮只修工具；兼容资源与原物理字节分开，不外推全主题或受限角色。

[富文本编码调查](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/article-htmltext-codec-contract.md)确认原encoded与新raw混存，同字节不同来源无法自动判断，现有raw有5组实体额外解码反例。标题保真v2保留，正文加载/有意编辑仍是明确未修缺陷；runtime纯值原型未接入生产。R01–R04仍待当前角色验证码确认后的真实UI，S3/S4/S6 gate=false。原22项表格逐字/顺序不变，验收JSON仅追加两个具名增量，全部旧字段保留。


## 2026-11-05：R01–R04 有限证据后续与未关闭边界

本节依[受限角色正式记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-restricted-role-ui-20261105.md)追加当前结果，早先未登录/仅准备文字保留为历史；原22个L/U/R行、编号顺序、40任务及B09完整范围逐字不变。本轮不等于R01–R04全矩阵或全部官方模板兼容，S3/S4/S6 gate=false、T16总体/T17继续开放。

R01–R03正常登录后，B09–B15指定七模块完成保存与重开，reader读取/拒绝有有限实际点击。FAQ分组名仍可聚焦；B10/B11/B15通用编辑/保存按钮存在，真实点击显示“无权限”。archive.update未启用参数保存只核UI，不是saveProduct绕过请求；普通archive显示“编辑器加载中”没有提交。TAG初次解析错误保留；本轮cms/data.html仅共用POST fetch加Accept: application/json，未改权限政策。六wrapper owned集合拒绝前后10847字节相等，不含模板文件、全库/schema/session/logs。本轮无raw network，不从文案认定HTTP/JSON/重定向码。

R04四族cn/A开启/B关闭的广告位4/5/6、广告10/11/12、表单6/7/8、栏目1395/1396/1397实际deny→allowed创建→重开，family指向实际行且广告pid分语言匹配；17状态检查通过，各意图deny日志0/allowed1，owned_logs仅累计。前三重开编辑框，栏目只重开列表；不是全语言逐记录UI保存或上传。metadata.arctype_editor相对reader_all只增加ChanneltypeController@select与ArctypeController@getTemplates，原Arctype@select已在，未授写入/upload/wildcard；动作规则没有新增通用HTTP方法限制。初始模型失败仅主执行说明，无单存失败DOM/JPG/raw network。

language/save拒绝链target/logs0且快照不变；允许链从工具准备源21（仅两条web配置、无业务内容）新增22并重开status0、前后台默认0、copy_documents0，成功日志1557唯一，计数正常变化且canonical映射不变。本批未执行语言reset/delete、同步新增或覆盖文档及其拒绝/允许链，R04仍开放；按钮存在不代执行。

正常UI注销后原保护页回登录，auth清理报身份行0、删除五所属日志、private目录不存在，owned_sessions_removed0，未删浏览器session文件。157业务表/7836原行、四族19分区、语言157表清理收据分层，非rawDDL/AUTO_INCREMENT/共享cache字节恢复。最终原164表8024原行缺失/修改0，当前8054、额外30仅旧admin日志1515–1544，exact_baseline_restored=false；比较工具只读、cleanup_performed=false。不得删除旧30日志或把pass改写为精确整库恢复。

[R01–R03独立审阅](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/restricted-ui-ab34de901f62-browser/R01-R03-REVIEW.md)27项/57绑定与[R04独立审阅](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/r04-c8b2416ae507-browser/R04-REVIEW.md)52项/92绑定，各另查13张JPG，均为保存证据复核，无独立live DB/HTTP复跑。旧失败、初始等待、普通编辑器加载缺口及会话/全部主题未验边界均保留，本次只追加五份摘要，不改acceptance-plan、current-handoff或保留部署文档。
