# Webman 企业官网重构路线

更新：2026-11-04，Asia/Shanghai。整体进行中；先交付可用的企业官网闭环，再扩大严格兼容与主题范围。本文件记录阶段与门槛，不宣称未来验收已经通过。

目标仍是：普通企业官网使用 EyouCMS 标签的原逻辑和用法，直接读取复制来的 PC/mobile 模板及 `template`、`uploads`、`upload`、`public` 资源。会员和商城业务不实现，保留原表数据及明确的禁用行为边界。原版源码、SQL和上游版权不改。

## 2026-11-04 有界离线MyISAM引擎迁移与恢复

[第五轮33检查/19次真实CLI](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/table-engine-migration-fifth.json)在所属旧站副本选中166表：157张原CMS MyISAM、7张wa InnoDB及2张自定义MyISAM，共159张待转换。PDO::exec第二次ALTER故障注入前恰一张表真实转换；已完成DDL保留，变更数据后的同计划续跑被拒绝，恢复该测试输入后由未修改生产CLI在新PHP进程续跑，最终166表均为InnoDB。重复执行、跨表实际rollback、离线备份校验及篡改拒绝通过；按恢复合同将原始备份写入另一个空所属库，166表原schema/data/engine、ID/default/index/自增和零ID、NULL、binary、UTC TIMESTAMP、重复行及9001等边界均核对。

原开发库164表before/after完整摘要相等；两所属库、私有workspace及原始备份已清理，7项执行源与[第五轮源快照](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/table-engine-migration-fifth-source)保持一致。原始备份/行值未公开保存，保存的逐表摘要和过程断言依赖执行工具，不能独立完整重放原始行。前四轮失败及各自源快照保留：前两轮修生产缺陷，第三/第四轮修测试假设，第四轮实际6次DDL不能写成恰一次；详细分层见[数据库说明](../database.md)。本轮不执行公开HTTP/浏览器，也不验在线writer安全或完整历史关系重建。

这是S6有限offline引擎CLI增量，完整已有站迁移、前台/原模板资源与业务语义、最终冻结综合回归、新管理员真实UI仍开放；S3/S6 gate=false。保留40任务和22 L/U/R编号及顺序，各层计数不相加为兼容率。上一[HTTP/Composer冻结](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/stage3-dynamic2-custom-composer-evidence-v1.json)及历史报告/快照保持原字节；本轮源与文档由[单一新增绑定](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/stage6-table-engine-migration-evidence-v1.json)保存。

[独立只读复核](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/table-engine-migration-independent-review-v1.json)核对保存的源SHA、计划指纹和before/partial/after/restore摘要；执行为未保存命令的inline Python，不声称可完整重放。零ID/UTC/转义实际值依赖冻结SQL断言，清理只核保存记录，未新连DB或执行HTTP/UI。

## 2026-11-04 全新Composer有限验收通过

[第二轮真实安装14](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/fresh-composer-second.json)在无vendor、无旧cache、无.env/旧runtime的所属副本中，启用root package scripts实际安装84个锁定公共包；12项平台要求成功，6类autoload、重复install metadata及4266项应用输入的字节/链接保持均通过，包含定制`support/bootstrap.php`。当前应用输入与执行时一致；所属副本、Composer home/cache全部清理，无DB/服务/UI操作。[首轮失败](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/fresh-composer-first.json)及其输入清单保留：末项发现官方hook覆写bootstrap；[首轮工具v1](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/fresh-composer-tool-source-v1.py)另存且与首轮input.json记录SHA一致，但未保存被覆写后bootstrap原始字节或全部源快照；覆盖结论依赖执行工具的preserved=false与命令摘要，不能完整独立重放。包装器仅保护六个应用自带包的脚手架hook，其它插件原样转发；[stub35](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/composer-hooks-no-database-first.json)与真实安装14分层，不合并为业务兼容率。

本项只关闭S6全新锁定依赖安装分区；有界offline引擎CLI后续另列通过，完整已有站迁移、最终冻结综合回归、新管理员真实登录与移动交互仍开放，S3/S6 gate均为false。S3列表139、Article详情52、custom单页165与S6安装是不同证据层；该批冻结源/8文档/工具/报告由[当批绑定](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/stage3-dynamic2-custom-composer-evidence-v1.json)统一保存，旧manifest、失败报告和快照不改。

## 2026-11-04 后续增量：dyn2 Article详情实际HTTP

[详情第二轮52](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-dynamic2-view-http-second.json)以一次真实后台Article插入POST和一次重开GET、15公开请求，验证原PC/mobile `arclist.arcurl`生成/跟随、PC path2、mobile原query及复用PC原URL的mobile path2详情；aid/typeid/标题/双端不同正文、SEO/query sentinel及target home_lang Cookie正确，显式target200/main404、未知/无效lang404。17响应/1SQL/221源保存，164原表/UI/6表owned及4模板/4所属编译文件精确清理。[首轮](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-dynamic2-view-http-first.json)在0HTTP/0写入时被过宽孤儿预检拒绝；[工具v2](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-dynamic2-view-tool-binding-v2.json)只收窄未来aid候选保护并修无journal的cleanup，保留原行和计数器。此为独立准备目标的有限Article详情扩展，不是语言复制resolver、全模型/全主题或真实UI验收；S3 gate=false。

## 2026-11-04 后续增量：custom 单页实际HTTP闭环

[第二轮165](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-single-custom-http-second.json)已完成10认证请求（8POST/2GET）与12公开GET：copy_documents0/1按SQL DEFAULT/NULL初始化，singlePage保存标量custom和双正文、getContent重开，两次sync保持人工空串/0/非空值和双正文；原PC/mobile字段变量及显式`aid="$eyou.field.aid"`的`field addfields`读到对应值。首轮缺显式aid为夹具假设错误，原版list/single不推断请求aid；[工具v2说明](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-single-custom-http-tool-binding-v2.json)保留源观察/失败及修正，无生产修改。164原表行列、single原结构/索引/原行投影、UI与所属数据/模板精确恢复。此为API/SQL/公开模板证据，无真实UI、扩展dtype矩阵、严格1364重复或全主题证明；S3 active/gate=false，详见[生命周期记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-lifecycle-verification.md)。

## 2026-11-04 后续增量：dyn2 列表入口可用性扩展

[dyn2实际HTTP第二轮139](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-dynamic2-http-second.json)完成4认证/43公开，PC原生成path2链接与mobile原生成query链接及复用同分区PC原链接的mobile path2入口均返回正确lang/owner/SEO和home_lang Cookie；显式query目标200、主语言404、未知/无效pathlang404，正常query/source模式保留。[入口范围memory28](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/dynamic2-path-scope-review-v1.json)单列验证限定条件和上下文对象身份。此为有意的可用性扩展：原third404与PHP7缓存顺序oracle继续保留，不宣称逐字复现原损坏入口。本139批只验lists；后续Article view52另列，不扩大到全路由/真实UI；S3 active/gate=false。首轮mobile URL形态工具失败及清理保留，详见[生命周期记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-lifecycle-verification.md)。

## 2026-11-04 单页新增量

[custom SQL默认值](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/single-page-custom-defaults.md)已分层记录原方法17/实际DB40/独立只读63；[显式历史结构修复](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/single-page-structure-repair.md)有memory54/独立关系与CLI53/实际DB28及独立raw receipt。新结构使用SQL DEFAULT/NULL，既有人工值与正文保留；strict1364和晚写入失败原子回滚。历史修复由选ID及指纹保护的CLI执行，普通语言sync不自动修复；未知孤儿及完整迁移仍待单独核查。新DB批次原157行列map恢复、所属表/workspace归零。详见[生命周期增量](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-lifecycle-verification.md)，各层检查数不累计兼容率。dyn2其它模型/入口采样边界、U01–U06/R01–R04真实UI、T17、未提供主题及后续阶段仍开放；S3 active/gate=false。

## 阶段、责任与依赖

| 阶段 | 负责人 | 依赖 | 交付与通过门槛 | 当前状态 |
| --- | --- | --- | --- | --- |
| S1 基线与任务重整 | 主 agent；文档 agent 维护清单，各实现 agent 核对源码 | 无 | 对齐当前源码与旧证据，区分已验有限分支、真实缺陷、待验交互、外部依赖；落实任务、责任和后续门槛。核实可启动入口、配置与数据库现状，记录所用版本。 | 已完成基线与任务重整；实际运行基线见下文，属于启动/清单门槛，不等于业务全验。 |
| S2 可用官网闭环 | 主 agent；后台 agent 支持内容编辑，模板 agent 处理阻断标签 | S1 基线可追踪 | 从后台栏目/内容创建编辑、保存重开，到原模板 PC/mobile 首页、列表、详情、导航、搜索、表单、下载及原资源 GET，形成一次可复验闭环；实际 HTTP/UI、持久化和夹具清理有证据。 | 有限可用官网门槛已通过；三轮实际 UI/DB/原模板与现有搜索、表单、下载、资源证据组成闭环。详见 stage2-website-loop.md，不等于全官方主题或后台组合全验。 |
| S3 后台与多语言 | 后台 agent；主 agent 负责路由/实际 UI，文档 agent 核对边界 | S2；先确认语言与共享/独立模式契约 | 完成 B16/B17 创建生命周期与目录冲突，核对 B01/B02/B07–B15 的必要待验分支；正常及受限角色、翻译保留、跨语言绑定、失败回滚和跨 worker 读取按受影响范围验收。 | 当前阶段，进行中；恢复、字段、helper、语言删除及UI缓存保护有分层证据。后续L04新语言标题/参数实际持久化29、L05业务语言删除公开终态26、L11真实writer35及L12双worker更新传播66均通过有限分区，见 stage3-lifecycle-verification.md。L04配置/资格已有有限方法与DB证据；正常query链接HTTP已有第五轮有限证据；单页有限结构/双正文初始化已获DB33与HTTP85；非空自定义字段与显式结构修复已有后续有限证据；dyn2其它模型/入口采样边界、未知孤儿迁移及必要真实UI仍开放。 |
| S4 严格标签兼容 | 模板 agent；主 agent 集成，文档 agent 复核差异分类 | S3；冻结受影响共享接口 | 处理 E03 可工作原行为差异及 T17；补未覆盖的企业标签默认值、属性优先级、作用域、嵌套与分页语义。每项区分真实原版匹配、明确修复扩展和保护边界；真实差异未解决时不能宣布严格兼容通过。 | 未通过；`eyou:in` range 别名已完成有限原版差分及正常路由 HTTP；T17 仍开放。 |
| S5 主题与资源综合验收 | 主 agent；模板/后台 agent 修复阻断，文档 agent 维护语料 | S4 受验源码冻结；实际主题文件可用 | 在同一冻结版本验已提供企业主题 PC/mobile、资源 URL、搜索/表单/下载、后台修改后失效、双 worker 上下文隔离；逐项登记缺失资源、替代资源、外部依赖和设备限制。新增官方主题取得实际文件后另列清单验收。 | 待当前源码综合验收；218 源模板清单及既有有限证据不是所有官方可下载主题证明。 |
| S6 独立部署与交付 | 主 agent；后台 agent 验初始化/迁移，文档 agent 编写交付说明 | S5 已提供语料门槛；部署输入完整 | 在干净目录、独立数据库完成依赖安装、配置、初始化、原模板/资源复制、启动、S2 闭环、重启及迁移重复执行；交付版本清单、部署与回滚说明及未解决事项。不得用现有开发库启动成功代替独立部署。 | 有限隔离部署演练已通过17步及精确清理；新库/重复初始化、双worker原模板、重启与165表备份恢复有证据。前两轮端口探测失败保留。后续全新Composer14和有界offline引擎CLI33已另列通过；完整已有站点迁移、最终冻结综合回归、新管理员真实UI仍待验，阶段未通过。 |

主 agent 控制阶段推进；其它 agent 可并行准备互不冲突的接口、代码审查和有界测试，但不能跳过阶段门槛或长期用边缘语义调查替代 S2 官网闭环。后续代码修改的结果按实际证据更新，不在计划中预报通过。

## S2 可用官网里程碑与当前边界

三轮证据见 [独立闭环复核](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage2-website-loop.md)。首轮实际编辑文章/产品/单页、栏目、搜索、导航分页、前台招聘表单、后台回复和实际附件下载；图库intro真实失败保留。第二轮修复后验证多行注释、单页栏目改名与双端原模板输出，发现并随后修正空文档模板被固化的问题。第三轮真实新建栏目/文章、保存重开，原PC/mobile读取各自正文；产品父标题、真正LF注释和空模板继承亦保存重开，DB与双端HTTP独立核对。

[当前集成](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/backend-enterprise-structure-http-third.json)67/67，含25认证与18公开请求；[真实UI](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-structure-http-007ab14b2299/main-ui-observations.json)单列，不把runner替代浏览器。19表原行/列摘要恢复、所属数据归零，4临时模板及6所属编译/缓存文件移除。此前两个原模板语料批次各46项及双端资源通过；后续只对新改动执行必要专项，而非宣称重跑了所有旧场景。

S3后续已处理第三轮真实UI发现的目标语言同目录1062：当前服务在绑定/克隆前检查所有目标语言（含禁用、回收记录），给出中文冲突提示并完整回滚，不改原目录或ID，也不复用无关栏目。[专项HTTP/DB](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-directory-collision-http-second.json)24/24、10认证请求，19表原行/列摘要恢复；[独立异常注入](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-directory-conflict-independent-v1.json)5/5确认其它异常原样抛出和后序失败回滚。该增强没有新增浏览器或公开模板请求；旧1062观察与测试解析空data数组的首轮失败均保留。原目录规则保留language_mark字典，custom注册mark的同名目录属于明确扩展输入边界。B16/B17有限成功不能关闭其它后台角色/生命周期、T17及未取得主题。Ad/adv NULL解码和ENT_COMPAT已有19项原PHP7/PHP8真实方法逐字对照及正常HTTP，本轮未改T17比较器。

## S6 有限独立部署演练

[第三轮报告](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/standalone-deployment/5962a2dde18d/report.json)17步通过：复制9423个冻结输入到没有原EyouCMS兄弟目录的隔离应用，使用新库初始化164张业务/后台表及两项迁移，初始化1个管理员和70项菜单；两次dry-run/实际重复初始化均0创建、164保留，全165表（含所属标记表）的结构/行摘要不变。首次和重启后各12个原PC/mobile页面、62个本地资产GET及两项upload/uploads路径读取通过，实际各观察两个worker。PDO备份恢复后165表结构与全行摘要相同。

[独立清理核对](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/standalone-deployment/5962a2dde18d/cleanup-verification.json)确认两个所属数据库、隔离应用及私有环境/备份已移除，8793无监听，冻结源码摘要一致。前两轮停止后的端口探测因TIME_WAIT失败，原失败报告保留；工具依据独立socket实验加入SO_REUSEADDR与10秒有界等待，未修改业务代码。详见[部署说明](../deployment.md)。

本次使用现有冻结vendor副本，未验证公网全新Composer安装；页面以HTTP和goto参数验证，不是新管理员实际登录UI或真实手机触摸。备份仅验无视图、存储过程、事件、触发器、外键的所属InnoDB库。页面仅断言HTTP200、对应marker及无残余eyou标签，不等于逐字或视觉验收；两worker来自专用PC probe，不证明每页跨worker上下文隔离。62资源仅HTML直接src/href，不含CSS递归；普通资产断言200非空并记录hash，两个所属上传路径另核对预期SHA。恢复库核对全表摘要，未重新启动恢复库站点。完整已有站点迁移及最终冻结版本综合回归仍开放；后续S3源码更改不能自动继承本次冻结摘要。S6阶段和全部官方企业主题仍未宣布通过。

## S1 实际基线

2026-11-03，主 agent 已确认显式 PHP 为 8.4.23；`init_database --dry-run` 对开发数据库 `webman_admin` 报告 164 张保留、0 创建、`migrations=[]`；实际 `/` 与 `/app/admin` 均 HTTP 200。文档 agent 另行只读核对 PHP 版本及两份 SQL 的 SHA256：原版 `install/eyoucms.sql` 与本地 `sql/baseline/eyoucms-1.7.7.sql` 均为 `5803d03040bd6a1cc056c0c81103b8f5506cb04a9c6ab0df421f5416f123d8fe`。未重新执行初始化或写数据库。

这使 S1 的运行基线与任务重整门槛闭合；入口 200 和 dry-run 不证明后台操作、标签全量语义或整个 S2 官网闭环通过。

## 现状与新增任务

本轮 S3 实现及证据见[恢复、字段与语言删除](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-lifecycle-verification.md)，[阶段有限清单](stage3-acceptance-scope.md)保持22项并区分已验分支和环境待验。HTTP81、删除DB72/HTTP29与独立异常测试属于不同层，不能相加为兼容率或阶段通过。

后续 [L11真实writer35](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-writer-concurrency-second.json) 通过相同目录创建及创建/同步两种顺序的真实进程、连接与 InnoDB 锁等待；使用外层事务屏障，保留人工译文，157原表行/列摘要不变。[L12双worker HTTP66](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-workers-http-second.json) 以5认证、40公开请求验证两个稳定PID在主/目标语言×PC/mobile×列表/详情更新前后读取正确栏目、标题和各端正文；19表恢复及所属模板/编译缓存清理完成。复用连接对象并逐请求验PID，不声明TCP socket身份完整持续。

[L04实际持久化29](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-persistence-second.json) 承接原方法3/候选8/对照12的有限标题/参数合同：新建语言加mark前缀、增量不加，已有人工译文和canonical别名保留；157表所属InnoDB副本、晚SQL触发器完整回滚和四个device×product标签断言/八输出通过。list_id=0/custom/legacy值保留为明确扩展；该标题/参数历史批次未验配置/资格；后续有限证据见下节。L11的旧LanguageService摘要保留，[差异复核](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-persistence-source-review-v1.json)说明本次改动及同步锁/事务边界未改，不宣称旧并发测试已重跑当前源。[L05公开终态26](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-public-deletion-http-second.json) 用4认证/16公开验证有文章双端正文的目标语言删除，目标四页404/主体四页200，直接核语言行、另一语言和计数，164表行列恢复；UI基线文件0、未新增UI文件，非非空缓存保留证明。原首轮25只缺直接语言行/计数断言，保留报告/工具；没有生产缺陷。

新批次由[follow-up清单](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/stage3-followup-evidence-v1.json)另行绑定，旧v1快照/报告不改写；各工具首轮失败继续保留。浏览器重试仍 `browser.user unavailable`，管理员/受限角色UI保持待验，S3未关闭。

- [验收计划](acceptance-plan.json)保留原 38 个任务 ID，新增 B16（共享数据创建后的多语言克隆/绑定）和 B17（栏目目录与语言标记冲突），共 40 项。详细待验分支见[验收缺口](acceptance-gaps.md)。新任务已有有限原PHP7方法、候选内存/25表回滚、认证HTTP与真实UI证据；不等于原完整应用HTTP差分或所有生命周期验收。
- B16 覆盖“语言已存在，再创建栏目、广告位/广告、友情链接组、自由表单”的顺序；既有“先建数据，再新增语言”的复制证据不能替代此顺序。先完成语言保留目录规则 B17，再综合验栏目和共享数据的创建绑定；实现工作可在接口确认后并行。
- B02 当前 `singlePage.html` SHA256 为 `f2b3c16e1c4b604091844f40027d13785ff488c2e7be2e070e12f3f184bad056`。继[冻结输入与分层报告](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-page-select-disabled-final-v1/manifest.json)后，新增[真实 UI 38 检查](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-page-select-ui-7e3c9a16-final.json)：单个 silver 依赖 held107.536秒，观察超时 error 和同文档 retry-loading 的 select/代理禁用；释放后 ready，鼠标选 B、键盘选 A，再选 B 并实际修改 PC 正文，捕获“操作成功”，唯一 POST200 后直接 8787 双端重开。原 PC/mobile 模板 DOM/HTTP 验相应正文，19 个唯一 CSS/JS 真实 GET200，6 个精确清理 URL404；8 类 owned 数据归零、12 类原行哈希稳定、schema 恢复。独立只读审阅复核 18 个绑定输入/工具/源码 SHA，未新增请求或重跑脚本。
- 本次 B02 仅一个 select、两个系统编辑器、一个依赖和一次重试；`held_initial` 实际已是超时，不能称初始 loading 时刻。loading 键盘尝试因 retry 已隐藏而拒绝，失败历史保留且不算通过；原显式 disabled select 的真实 UI、实例 identity、其它故障/多次重试等继续开放。孤立语言未绑定原 footer 的 type/flink，不能称整页无错。旧 e107/v4 证据保留，本新批次也不代表 S2 或全模板综合验收完成。
- S1 初始审查记录 E03 的 `eyou:in` 可工作输出差异：`value=b`、`value='a,b'` 时原版空输出，候选输出 `IN`，不是原版 ParseError。本轮已按原版完整 range 别名分支修复，291 项差分零差异，另有 60 比较控制与 52 parseAttr 契约；[正常路由 HTTP](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/e03-range-alias-http-v1.json)两种入口、PC/mobile 共四请求逐字匹配两次 PHP7 原 compiler（6/6）。8行、2源模板和4编译文件精确清零，原行/源码哈希不变；旧失败保留。范围限已执行输入，不关闭 T17 或其它主题。equal/notequal、likearticle 等原版损坏语法的修复扩展继续单列。
- T17 真实 Eyou/Cx compare 静态引用场景仍为原版 PHP7 `yes:2`、候选 PHP8 `no:2`；被反例否定的方案保持历史，不算修复。没有所有官方主题兼容的结论。

## 证据复用与重新验收

已提供语料的CSS递归并非空白：[PC phase2](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/template-page-assets-pc-phase2-current-20261103a.json)与[mobile phase2](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/template-page-assets-mobile-phase2-current-20261103a.json)已有有限递归资源依据；16CSS的31条依赖边/43个唯一资源均在两报告内，当前无@import/外链。原包/既有替代身份另见[资源清单](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/asset-inventory-post-comparison-v12.json)及[替代manifest](../../resources/compat-assets/manifest.json)。这不扩大成当前全部冻结主题集成、视觉、真实触摸或未提供主题验收；旧部署62直接资产批次仍保留它自己的非递归范围。

79 份官方文档、102 个主标签加 include、218 份原模板是当前清单；93 partial/10 unverified、511/524 显式属性输入及 826 份历史归档均不是兼容率或独立通过数。标签手册、兼容矩阵与归档本轮保持原字节，矩阵旧快照不能自动覆盖后续源码变化。

原有有限通过分支在源码、依赖、输入和环境仍适用时可引用。修改编译器、语言/URL/仓储服务或缓存契约后，重新验受影响语义和集成；B02 页面脚本变更需真实页面状态切换和交互验收。源码哈希、文档核对、适配器、实际 HTTP、浏览器与数据库持久化属于不同证据层，不能互相冒充。只运行受改动影响的必要检查，不因规划重跑全部历史矩阵。

SMTP/SMS 实际投递、有效地图 AK、官方语言包外网等按真实环境记录；未取得的官方主题按输入缺口登记。它们与已确认代码差异分别跟踪，不靠未提供主题扩大本地闭环任务，也不提前宣布全量兼容。历史失败、旧快照和清理记录保持原样。[进度记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/progress.md)保留实施时序。

## S3 配置、栏目资格与单页有限增量

[原配置18](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-config-independent-original-v1.json)、[候选19](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-config-independent-candidate-v2.json)、[逐项配置合同16](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-config-independent-comparison-v2.json)及[真实DB20](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-config-persistence-first.json)通过各自有限层：web_name加mark、SEO2按第二登记语言（含disabled）取值、各空值默认1、回收/同名跨组配置元数据保留。DB使用所属157空InnoDB结构并验证晚trigger回滚、增量译文保持；原157表行列恢复、owned表/工作目录0，不是原完整应用或HTTP证明。

[资格内存25](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-copy-eligibility-current-binding-v2.json)与[真实增量/资格DB25](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-incremental-eligibility-first.json)补L01创建目标后新增源文章、同步两次仅一份且保留译文的有限链。status0栏目树不复制，hidden根保留；回收排除、孤儿原子拒绝及权限不裁剪数据树为明确保护差异。单页copy_documents=0缺结构、copy_documents=1错误复制源metadata/附表时间是[新合同差异](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-language-copy-contract-before-v2.json)，修复后的有限实际DB33/HTTP85见下方，非空自定义字段与显式结构修复的后续有限证据见2026-11-04增量；普通sync不自动修复。

[配置HTTP第三轮](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-config-http-third.json)的1认证/2公开中，保存、SQL、PC query原标签和正确生成的动态格式2链接通过前置检查，跟随链接404；164表及所属文件精确恢复。[原方法顺序13](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-dynamic2-path-order-original-v2.json)在PHP7有限只读内存层确认App预路由缓存home_lang后，Route的path lang不能改写已缓存语言，Article查不到目标；未执行原最终HTTP404。保留原失败分区，不提前改全局Index/query语言顺序。正常query链接第五轮75/75已有冻结2359源有限证据，单页有限DB33/HTTP85结果已到齐，非空自定义字段及显式结构修复的后续有限证据见2026-11-04增量，S3 gate=false；失败历史与工具修复见[生命周期记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-lifecycle-verification.md)。

[配置正常query链第五轮75](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-config-http-fifth.json)完成4认证/16公开与20响应SHA，PC/mobile query及生成链接均200、164原表行列恢复/owned0；second SEO[1,1,4]/source[1,1,1]区分取值。此批冻结LanguageService2359/Controllerc2ad；single后续源改动已由source-path-review-v3只读24项说明有限适用性，并另有当前0e41的single DB33/HTTP85；旧配置链未重跑。[第四轮](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-config-http-fourth.json)保留工具dyn1路径/goto断言失败与精确清理，third dyn2失败不抹去。

## 单页有限修复与当前源码收口（2026-11-03）

[单页分层绑定](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-language-copy-repair-binding-v1.json)分别记录原方法64、候选对应64、增量保留15、关系映射12；[规范化逐项比较28](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-language-copy-repaired-comparison-v1.json)、资格回归29、配置回归19各为独立有限层，不相加为兼容率。当前`LanguageService`为`0e41fc44bdd7bf2af5736002bc21a36fccc2b00e231cb3cd788ee3509e1a21d2`。[真实DB第二轮33](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-single-persistence-second.json)和[真实HTTP第二轮85](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-single-http-second.json)已分别通过。新语言docs0仍初始化单页结构，docs1只初始化PC/mobile正文，保留目标stub metadata/editor/时间；后续增量保留人工或空正文、既有日志/绑定，并映射普通joinaid及专题引用。DB使用所属157当前空结构，原157行列摘要恢复、所属表/workspace0；HTTP10认证（8POST/2GET）与12公开、22响应、221源快照，原164行列恢复、11表owned0、两模板/两所属编译移除。API保存/重开属于接口证据；本轮UI基线0。

[只读源码路径复核v3 24](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-language-source-path-review-v3.json)说明历史2359配置HTTP75/配置DB20及article-only资格DB25的有限适用性：config方法逐字未改、其输入不进入新channel6路径；没有重跑旧业务或writer测试。旧资格memory25包含single，受影响路径以新single证据承接。L11旧输入无single，不能推出全部并发单页通过。[HTTP独立审计v2 139](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-single-http-second-independent-audit-v2.json)只核保存字节/SQL/清理，不发新请求；v1遗漏runner文章数量断言变化，失败保留。原日志已清理，仅journal的8日志ID及actor保留，不能独立重放原log行；missing-counter补建分支本轮allowed_pairs为空，未触发。

dyn2其它模型/入口采样边界、U01–U06/R01–R04真实UI、未知孤儿及完整历史迁移、T17和未提供官方主题仍开放。非空自定义字段与显式历史结构修复已有2026-11-04有限增量，见[生命周期记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-lifecycle-verification.md)；普通语言同步不会自动修复历史目标。多活跃single owner仍原子拒绝；不复制原all-single-site条件bug，也不把候选关系安全重映射称为原损坏跨语言ID的逐字等价。S3 active/gate=false，40任务和22有限ID/order保留。旧final/followup/config-single清单、快照和失败报告保持原字节，本次另作新增量绑定。

[DB独立审阅26](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-single-persistence-independent-review-v1.json)只读核对8源、两个SQL trigger marker、同对象重试及157原表清理raw maps；中间owned157回滚/幂等maps、人工最终行与special节点raw没有保存在JSON，这些过程结论依赖绑定工具已执行的boolean断言，不能称为原始行可完整重放。初始stub、marker及原157清理maps则有直接保存证据。


## 自定义语言目录与子树路径增量（2026-11-04）

本节保留前一冻结批次的范围；当时的混合子树页面待验状态由后面的“混合子树与继承后续增量”有限更新。

已修复注册语言根提前首页返回和父栏目更新全部后代的差异，见[分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/custom-language-directory-routing.md)。原PHP7路由/URL有限对照与候选修后inlet0/1各12项通过；实际HTTP修后91项通过（2认证POST/16公开GET），原type/channel生成8条PC/mobile链接原样跟随；修前两项显式默认语言200/404差异保留。子树真实方法内存408与另行执行的[实库81](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-subtree-persistence-first.json)分层通过：6所属表/16节点、停用根、真实晚SQL故障全回滚及同服务重试，原157表摘要一致，所属表/trigger/workspace清理。混合子树更新后的页面链路仍待验。HTTP两轮各164原表行/列和默认cn的3SEO原行恢复、10表owned0、两模板/两所属编译文件/两条已保存原日志清理完成，UI基线0。S3 active/gate=false，实际UI、T17、未提供主题与最终门槛不变；不重复旧67批次或聚合为兼容率。


## 混合子树与继承后续增量（2026-11-04）

[当前分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/custom-language-directory-routing.md)保留两批输入：mixed-chain HTTP160/旧74e43（1POST/20公开）与继承HTTP280/当前f5b1（2POST＋1子栏目重开GET/30公开）。后者原type/channel生成24条href原样跟随，PC/mobile实际呈现继承列表模板，12排除完整行及人工DIY保持；164原表摘要恢复、10owned表0、4模板/4编译/2原始日志清理，UI基线0。原前缀before31是缺陷诊断；after31、array9、当前子树408、混合模型实库82各保留独立层。上一冻结时Single旧empty_logic仅memory；该历史缺口由下节有限SQL146/HTTP272承接，该历史冻结的继承tempview详情待验由后续Article增量承接；grade、更广SEO、实际U/R UI、T17、未提供主题及最终门槛仍开放，S3 gate=false。八文档冻结后由主agent生成新的stage3-subtree-inheritance绑定，旧冻结不改。


## Single 继承实际SQL与双端读取增量（2026-11-04）

[分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/custom-language-directory-routing.md)现补齐上一冻结时仅memory的有限Single范围：原PHP7/候选PHP8方法各72、第三轮比较28与来源合同44；[实际SQL146](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-category-inherit-persistence-first.json)使用8张所属InnoDB表、16节点和15份既有双端正文，父posted0/子孙继承old1、异模型仅权限、12排除行与停用根、晚SQLSTATE45000/1644整体回滚及同对象重试通过，14来源与原157表摘要保持、所属表/trigger/workspace清理。

[实际HTTP272](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-single-inherit-http-first.json)有3POST＋18重开GET＋20公开GET、41响应/3SQL/241来源/16条原type href原样跟随；child/grandchild由实际singlePage从空写入，继承后child双端选grandchild但保留请求typeid和原附表，root/second非空正文为seed前提。164表摘要恢复、10owned表0、4模板/4编译/3条原始日志清理、cookie不变，UI基线0。探针并非官方整页，API并非浏览器；该冻结时继承tempview的Article详情由后续增量有限承接，grade/其它SEO、权限执行与实际U/R UI、T17及最终门槛仍开放，S3 gate=false。新绑定为 `runtime/compat/stage3-single-inheritance-evidence-v1.json`，历史冻结不改。

[Single SQL独立离线复核359](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-category-inherit-persistence-independent-review-v1.json)重算64份所属raw行摘要、核三阶段15对archive/body完整行保护与14份来源；359/64是复核与完整性检查数，不是新增业务数。原columns及原157表raw记录未保存，只能比较已存摘要；数据库事件与清理依据冻结执行证据，不称完整原raw独立重放。

[Single HTTP独立离线复核195](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-single-inherit-http-independent-review-v1.json)重新核对241来源的前后副本/当前字节、41响应、3份SQL共27个所属分区raw摘要、18次API重开、16次原href跟随及正文/aid/UA记录、164表摘要与3条原始日志清理凭据。195/27是复核与完整性检查，不能追加成业务用例；离线不能重新观察历史网络header或重查已删除owned行，状态/UA/数据库清理仍依据保存执行记录。该层不替代16节点排除分支SQL，也不扩大seed正文或UI/官方整页范围。


## Article 继承详情模板字段增量（2026-11-04）

[本轮分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/custom-language-directory-routing.md)补普通`.htm` Article详情：修前文件选对但 `field.tempview` 未填栏目值；Controller `7e014dfb` 在详情合并后填继承值，并按最终字段选择模板。[修前HTTP185](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-article-view-inherit-http-before-v1.json)仅4处有效字段断言失败；[修后HTTP185](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-article-view-inherit-http-after-v1.json)通过。各3POST＋12重开GET＋12公开GET、27响应/3SQL/244来源/8条原arclist href原样跟随：两篇实际插入文档，空模板BASE→INHERITED、显式模板恒OVERRIDE，双端正文及API原始空值/aid/typeid保持。各164表摘要恢复、10owned表0、10模板/10编译/3原始日志清理、cookie不变、UI基线0。

[表达式修后28](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/article-tempview-choice-after-v1/report.json)与[离线96](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/article-tempview-choice-independent-review-v1.json)只核12普通输入的填值/合并/选择，4异常后缀差异另保留；不是完整原控制器或原HTTP。[源码差异14](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/article-template-source-delta-v1.json)证明改动限于renderArchive，旧Single HTTP没有在新Controller整文件SHA上重跑。新探针不是官方整页/API不是浏览器；其它模型、grade/SEO、权限/UI、T17及最终门槛仍开放，S3 gate=false。上一冻结的继承tempview待验由本节有限更新，新绑定为 `runtime/compat/stage3-article-template-inheritance-evidence-v1.json`。

[Article HTTP独立离线复核273](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/article-detail-inheritance-http-independent-review-v1.json)核两轮各244来源、27响应、3SQL和8条原href，重算六份SQL快照共54个所属raw分区摘要；确认修前仅4个字段失配、修后0。273/54是复核/完整性数，不追加为业务数。原164表raw/完整列记录与编译文件raw字节未保存，原表摘要与清理依据冻结记录；离线未执行DB/HTTP，不能重观察历史header或重查已删除行。NULL/字符串0仅表达式层，4异常后缀未修且未进入HTTP；10份新探针不是未修改官方整页，两轮token/ID不同，不是同ID或原框架HTTP差分。


## T17 原生捕获隔离调查（2026-11-04）

[调查记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/t17-native-capture-investigation.md)保存匹配PHP8.4.23 ABI的测试构建及有限表达式结果：27个预期检查含26个匹配和1个明确反例，12个旧GC阈值输入的捕获窗口实际析构为0。字面类自动加载场景拒绝延迟CV描述符，普通属性名参数匹配该例。六份生产来源不变，未接实际标签或Webman；T17和S4门槛仍开放。准备/首轮时序判断失败、raw进程证据及离线复核261均保留，不能以调查通过替代生产修复。


## 普通栏目提升与重新挂载增量（2026-11-04）

[本轮分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/custom-language-directory-routing.md)修复 Product 子栏目提升为顶级时 `channeltype` 被错误改为自身模型的问题：当前 `CategoryService=5711001f` 在顶级编辑保留原组值，创建顶级仍取自身模型，挂到非顶级仍取新父组值。原完整提取 `updateData/getHasChildren/arctype_options` 与候选真实完整 `save` 的[修前74](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-move-save-memory-before-v2/report.json)为诊断通过/parity=false，仅混合模型提升的编辑节点＋4合格后代有5处channeltype差异；[修后74](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-move-save-memory-after-v1/report.json)五个正常场景parity=true。[独立内存复核141](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-move-save-memory-independent-review-v1.json)确认各14节点、4合格/6排除后代、5文档和Article/Product/Single双端附表保护。该层用严格内存适配器，不是实库或完整原控制器；首轮namespace工具失败保留。

[HTTP修前544](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-promotion-http-before-v1.json)保留18个提升阶段channeltype传播断言失败；[修后544](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-promotion-http-after-v1.json)通过。各2次仅 `id,parent_id` 的真实POST、12次API重开GET和24公开GET，38响应/3SQL/247来源；四节点混合树经seed→提升→挂新Product根，18条PC/mobile原type/channel href原样跟随，显式原field标签和变量核grade/topid/channeltype/current_channel/dirpath/DIY。两根完整行、模型及人工DIY/模板/规则保持，无文档写入。各164表摘要恢复、10owned分区0、4模板/4编译/2原始日志清理、cookie不变/UI基线0。[独立HTTP复核493](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-promotion-http-independent-review-v1.json)重算总54个所属raw分区摘要；复核数不追加业务数，原164raw/完整列及移除编译raw未保存，历史header/数据库清理依赖执行记录。

[来源差异3](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-promotion-source-delta-v1.json)确认仅顶级编辑组值选择改变；旧Article/Single HTTP未在新整服务SHA上重跑。上述只有限承接旧“grade移动待验”的普通一致树分区；损坏/陈旧grade、排除祖先跨越、深度限制与真实UI、更广SEO、受限角色、并发/回滚仍待验。4新语法探针不代表未修改官方整页或全主题；T17原生捕获调查和缺陷保持、S3 gate=false。新绑定为 `runtime/compat/stage3-category-promotion-evidence-v1.json`，旧冻结及40任务/22个L/U/R顺序保持。


## T17 实际标签隔离原型（2026-11-04）

[分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/t17-native-pipeline-investigation.md)保留首轮失败：120个标签匹配PHP7、2个缺失属性错误文本不同，4个原PHP/strict边界另列；追加4个switch/生命周期匹配。修正GC准备后，候选6次活动期析构均保留写入，PHP8直接表达式同期0次，包装器仍改变时机；外部prelude未执行PHP7。生产未改，T17/S4仍开放，后续避免包装分配的方案仅在新隔离文件试验。


## T17 native-v2 有限拒绝与证据冻结（2026-11-04）

[合并分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/t17-native-pipeline-investigation.md)保留实际标签首轮失败：122个标签中120匹配PHP7、2个缺失静态属性错误文本不同，4个raw/strict原生边界另列；追加4个switch/生命周期匹配。外部prelude六组候选活动期各析构一次并保留slot7，PHP8直接表达式同期0次，证明仍有时机差异；未执行PHP7阈值对照。此前GC准备与过强复核失败保留，独立receipt-v2的1240项是保存证据完整性检查。

[新native-v2控制](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/t17-native-capture-v2-controls-first.json) `pass=false`：18个PHP7数值/布尔引用控制匹配，但6个PHP8精确阈值对照中unlinked array/object两组反例均为原生0析构/0新GC、变体1析构/2新GC，最终slot由原值变7、独立alias变2，虽没有丢写仍不等价。[复核136](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/t17-native-capture-v2-receipt-review-first.json)确认拒绝证据；无PHP7 GC推断。该变体未接实际标签或第三方案/生产，Zend PHP8比较不覆盖通用PHP7松散转换。六生产来源和旧报告/模块不变，T17/S4仍未通过。新manifest/snapshot为 `stage4-t17-native-pipeline-rejection-evidence-v1.json` / `stage4-t17-native-pipeline-rejection-source-v1/`，不增加业务计数或兼容率，40任务/22LUR顺序及非T17任务保持。


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


## 栏目自定义字段有限增量与24小时交付优先级（2026-11-04）

[栏目字段记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/category-custom-fields.md)已具三类字段的实际HTTP644与独立离线1656：-99 checkboxs/radios/img从创建、绑定、已有栏目addon编辑与重开、稳定选项ID、radio列改名、空/0到正常删除，以及双端Lists/View/Article选定输出。当前1783方法与NULL helper有fresh802，后台v3的来源与窄GET权限别名分别绑定。706设计/旧后台版本及所有失败仍为历史，不能改称当前生产通过。

template独立语义2108/绑定复核249及main晚DDL实库third35已在其有限范围通过，DDL首setup冲突与second索引排序工具失败保留、生产不变；独立DDL247已通过，重核六业务表108raw摘要与六备份；其它151owned/原164仅digest maps，成功删除留typename索引尾，不能称初始完整schema恢复。共享文档和来源冻结到新的 `stage3-category-custom-fields-evidence-v1.json` /source-v1。既有40任务/22LUR顺序保持，只向B04/B16/B17及root追加具名增量，B09仍属文件/模板管理。

[24小时剩余任务清单](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/delivery-priorities-24h.md)优先处理已知代码缺口、可用站点交付和必要回归；真实浏览器/外部主题环境与部署交付分列。legacy media完整编辑、栏目新增addon、跨语言实写、浏览器角色UI、T16/T17和未提供主题仍开放；三dtype不关闭CF总体、S3/S4/S6，不聚合兼容率或继续无关报告扩增。


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


## Search入口与原表单有限增量（2026-11-04）

[搜索记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/search-entry.md)绑定新SearchActions b175/Controller2fad：原lists方法68项（原第四只新1/复用67，66无warning、2业务相符但诊断不同）、实际选模28与当前owned8f2d的26HTTP分层通过。两端原form实际method1提交，同ID计数1→2/resultNum2；五不同词后第六限流、HTML/AJAX/过滤字段与13原PHP7 lists oracle相符。独立426/147bindings核raw与表快照、26监听归属收据/2模板还原/完整所属DB-copy清理；历史SQL/网络/进程删除仍是执行回执。新来源只重跑base两轮12路线＋Search26，旧44＋2和既有站迁移保持旧来源，不重标。68方法不含原完整框架HTTP，28选模不保证叶渲染，真实UI/并发/未供主题/T16/T17及S3/S4/S6false不变。只给root/T03/T16追加具名增量，40任务/22LUR及旧freeze/失败原字节保持。


## 栏目首次新增 addon 有限增量（2026-11-04）

[首次新增记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/category-first-insert.md)补齐非主独立语言下 Article 父子栏目首次提交 checkboxs/radios/img 的实际保存、立即重开和 PC/mobile 原语法输出；真实30次HTTP及独立保存证据复核通过。本批没有生产修复，已清理所属数据并核164表摘要/结构恢复。Product、共享语言fanout、真实角色UI与其它主题保持待验；旧冻结不改，新记录为 `stage3-category-first-insert-evidence-v1.json`，阶段门槛不变。


## 普通下载语言入口有限修复（2026-11-04）

[下载记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/download-language.md)绑定 DocumentActions 033a69：普通附件首跳与已签发后续下载按文件归属定位发布文档，允许非默认语言链接沿用原模板无 lang 的导航，签名、cookie、文件绑定及回收/草稿/禁用保护保留。实际独立副本 20 HTTP／27 项检查通过，5 次完整文件 SHA 相符，来源为真实 ContentService 创建的英语下载及原 PC/mobile 模板；独立保存证据复核通过。该批不含后台页面操作或全站迁移，全部所属副本/数据库已清理；旧源清单只作执行收据。新冻结 `stage4-download-language-evidence-v1.json`，T16 总体、T17、未提供主题及 S3/S4/S6 门槛仍开放。


## U01 / U02 实际管理员浏览器有限验收（2026-11-04）

[U01/U02记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-ui-followup-20261104.md)补齐指定管理员成功链：U01真实文本保存、重开、确认清除再重开，36→27恰删A/PC九行、另27行保持；U02真实覆盖、双端人工译文编辑/重开与随后增量同步，四份译文及ID/映射保持，后续四个原PC/mobile Article/Single页面显示正确译文。U02及最终比较独立离线476项通过（含来源/摘要核对，不是476业务用例），[新冻结](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/stage3-ui-U01-U02-evidence-v1.json)保存143文件；164表、8024原行及schema除AUTO_INCREMENT计数外精确恢复为绑定guard执行收据，离线仅重算私有基线摘要，不冒充重查实时DB。首次missing-basic、错误v=mobile和工具失败保留，simple页面不计原版通过。U03–U06、R01–R04仍未验；有效地图/外部服务、完整导航/资源/全部主题、T16总体/T17及S3/S4/S6门槛继续开放。40任务、22L/U/R、旧字段和旧冻结不改。


## 详情扩展字段 HTTP 有限增量（2026-11-04）

[详情扩展字段记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/detail-extra-fields.md)补回原版 AI 声明与详情/Lists 移动域字段；实际 cn Article/Product 的 PC/mobile 创建、保存、重开与 HTTP 对照通过，AI on→off→on、移动域开关0→1→0均有保存证据，所属部署数据库/副本及私有env/备份清理收据通过。来源仅绑定本次执行及复核时间，旧53项冻结不能重标为随后 Request 修改后的当前来源；首轮 oracle 适配器失败保留。图片样本、后台/浏览器UI、完整原框架及全部主题未据此验收；只向root/T16追加具名增量，T16总体/T17及S3/S4/S6门槛继续开放，旧段与旧冻结不改。


## 显式子目录 baseFile 有限增量（2026-11-04）

[子目录记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/subdirectory-basefile.md)与[新冻结](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/stage4-subdirectory-basefile-evidence-v1.json)：修复 Request89e89→856c22 的一个 baseFile 返回表达式，加入当前 ROOT_DIR；其余46个相关来源及其它方法未变。显式 /cms 所属副本 before/after 各371检查、128GET，14原PC/mobile页面 hidden basefile 失败归零；另10实际href跟随、98 emitted references（95静态文件＋3动态API）、4所属别名读取通过。3次资源替换属于2个manifest家族，captcha/arcclick输出不算会员验收。独立保存复核每轮1076项通过，保留before应用false；新冻结507文件，28旧manifest及40任务/22LUR/B09/旧字段不改。根/app/admin200、/cms/app/admin404只属入口观察；无登录/保存/UI、代理转发前缀、city或全部主题验收。旧9494全量来源含两份后续清理的U03probe，只作执行时收据；DB/副本/env/备份清理true仍有历史执行收据边界。只给root/T16追加具名增量，T16总体/T17及S3/S4/S6门槛继续开放。


## U03 保存读取部分成功与环境阻塞记录（2026-11-04）

[U03短文](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-ui-U03-partial-20261104.md)与[有限冻结](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/stage3-ui-U03-partial-evidence-v1.json)：两隔离语言同名变量实际新增、不同值保存/重开与四个PC/mobile原global语法所属probe通过；它们不是完整官方主题。原生确认超时仅执行叙述，缺确认框截图/rawtrace，软回收/恢复/purge及U04均未通过，U03总体仍开放。独立63项一致性/27绑定通过，非63业务用例；54owned清理、10分区0、两模板两编译移除有收据。全164表保护原8024行/结构（除AUTO_INCREMENT计数），保留真实日志1515–1518，8028行且exact_baseline_restored=false；历史DB/清理为执行收据，私有基线早于U03。新冻结32文件，不含私有raw基线/秘密journal/U05执行资料；只向root/B10具名追加，40任务/22LUR/B09/旧字段/29旧freeze及阶段门槛保持。


## 详情富文本图片设置 HTTP 有限增量（2026-11-04）

[富文本记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/detail-richtext-images.md)与[有限冻结](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/stage4-detail-richtext-images-evidence-v1.json)：本批没有生产修复，Controller仍bc37/Request856c。cn Article/ProductPC/mobile四组设置off/style/autoalt/forcedalt经实际service保存重开，149检查/18HTTP（16原详情＋2PNG）/16原PHP7/9helper通过；尺寸、border、style、自动及强制alt/title有逐正文对照。最终独立614项/172绑定核56冻结来源及相等source-after、75流和18raw，不是614业务用例；first–third复核假设错误/fourth604/fifth614及c93内部uploads别名guard误拒绝0HTTP/0oracle/1helper均保留，不相加。父18步、165表备份恢复与所属DB/copy/env/backup清理是执行收据，clone absent可离线观察。旧9494全量摘要含当时U05两probe，未存完整清单，不称current；刚改后台HTML确认不在56相关来源。新冻结188文件，不含private/U05执行资料；只给root/T16具名追加，40任务/22LUR/B09/旧字段/30旧freeze和门槛保持，真实UI/会话/插件/远程或absolute/subdirectory图片/全部主题及T16总体/T17/S3/S4/S6继续开放。


## 2026-11-05 当前执行增量

U03/U04/U06指定正常管理员成功链及精确清理已有后续有限证据，见current-handoff.md与相应短文。S3继续推进R01–R04实际受限角色，S6独立保留副本正常新admin登录已完成、内容保存闭环执行中；发现的category-fields脚本404已修，文章title-only正文保真候选需真实UI验证。原40任务/22LUR、历史证据和S3/S4/S6 gate=false保持；T16总体/T17和未取得官方主题继续开放。

12:59后续：文章title-only v2在独立副本实际保存、重开、原PC/mobile生效及恢复已通过；最终12原页面、73直接资源、5私有路径检查和保存证据独立复核也完成，见[独立部署记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/retained-deployment-20261105.md)。这些有限成果不关闭阶段门槛。[富文本编码](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/article-htmltext-codec-contract.md)已确认混存且同字节存在歧义，仍未修；受限角色验证码确认、T17和未取得主题继续开放。当前执行以[交接页](current-handoff.md)为准。


## 2026-11-05：受限角色后续范围与执行次序

[本轮记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-restricted-role-ui-20261105.md)已补正常登录及R01–R03指定七模块保存/重开和拒绝点击，更新早先待验证码的当前状态，旧段仍是历史。B10/B11/B15按钮可见但实际提示“无权限”、FAQ分组名仍可聚焦；普通archive“编辑器加载中”且未提交，archive.update不启用参数保存仅核UI。唯一生产修复是cms/data.html共用POST fetch增加Accept: application/json，初次TAG解析失败保留，不改变ACL政策；本批无raw network，不外推状态码。

R04四族已实际deny→allowed创建→重开，覆盖注册cn/开启A/关闭B的family结构，前三重开编辑框、栏目仅重开列表。精确metadata.arctype_editor相对reader_all只补Channeltype@select与Arctype@getTemplates读取；初始模型失败没有独立保存DOM/JPG。语言只完成language/save拒绝与允许新增22，源21只有两条web配置、无业务内容；重开禁用、非默认、copy_documents0。下一步仍须新鲜所属夹具与证据完成R04语言reset/delete等未验链，再处理普通编辑器加载及有意正文编辑；不重复已清理的旧fixture，也不把已存在按钮当执行。

正常注销及所属身份/业务清理已有回执，session文件删除0；157业务/19四族/157语言范围分开。原164表8024原行保护、8054当前行与30旧日志1515–1544保留，exact_baseline_restored=false，非AUTO_INCREMENT/shared cache字节恢复。独立27/57与52/92均是保存证据离线检查/绑定，不是live复跑或兼容率。保留初次失败、旧pending、B09所有边界与40任务/22LUR顺序；全主题、跨worker会话、T16总体/T17及S3/S4/S6 gate=false仍开放。本摘要不改验收JSON、交接页或保留部署文档。


## 2026-11-05：后台导航与框架实施

已在8787落实[企业官网后台布局](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/admin-layout-20261105.md)：固定工作台、六组27个入口、39原页面和授权后模块页签。受控迁移、重复执行、隔离库失败回滚、四角色接口及39页HTML可达通过；正常管理员实际浏览器覆盖响应式、恢复、搜索和未保存保护。受限角色正常登录页面复验及新布局逐帧动画证明尚未完成，详见[验收范围](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/admin-layout-20261105-verification.md)。8793不在本次同步范围。此增量只属后台导航，不关闭既有阶段门槛或标签/全部官方模板工作。
