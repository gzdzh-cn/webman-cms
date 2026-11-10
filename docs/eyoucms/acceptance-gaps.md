# Concrete enterprise acceptance backlog

The finite tasks below are the executable backlog. An attribute appearing in a passing fixture is input evidence, not proof of every value or branch. The adjacent JSON ledger records exact observed values, case IDs and report hashes.

Input ledger: 80 in-scope canonical tags; 524 effective tag/attribute pairs; 511 pairs explicitly occur in successful captured fixtures; 13 have no captured explicit input. These numbers are not a compatibility percentage.

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

## Current execution stages (2026-11-04)

Follow the [six-stage roadmap](roadmap.md): S1 baseline/backlog → S2 usable website loop → S3 backend/multilingual lifecycle → S4 strict tag semantics → S5 themes/resources → S6 independent deployment. S1 baseline/backlog is complete: PHP8.4.23, webman_admin dry-run164 preserved/0 created/migrations=[], matching original/local SQL SHA and two entry HTTP200 results. The main agent executed dry-run/HTTP; documentation independently checked PHP/SQL only. S2 has now passed its finite usable website loop: actual category/document creation, save/reopen, DB and unchanged PC/mobile output, plus prior search/form/download/resource evidence. S3 is active; no strict compatibility or later stage gate is declared passed.

The original 38 task IDs remain; B16/B17 add multilingual creation/binding and language-reserved directory conflicts, bringing this backlog to 40 tasks. The 826 archived files and 79-document manual remain unchanged. Historical finite passes, current code changes pending acceptance, real defects and unavailable external inputs are distinct states; report/attribute counts are not compatibility percentages.

B02 current source is f2b3: finite real UI7e3c9a16 now records timeout/retry select locking, ready pointer/keyboard, actual edit/save/toast/reopen, two original-template body outputs and 19 asset GETs; loading-keyboard and originally-disabled-select real UI remain unverified. The e107/v4 browser evidence remains historical. E03 range alias now has finite PHP7 differential and actual normal-route HTTP acceptance; T17 remains open. The original planning-only update performed no business tests; the later bounded E03 fixture execution and exact cleanup are recorded below.


Current S2 follow-up: [stage2-website-loop.md](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage2-website-loop.md) records the initial real gallery persistence failure, subsequent correction and original PHP7 contract checks. Current insert/update preserve raw empty template inheritance; product gallery title follows the parent document and intro retains LF. [Current HTTP/UI integration](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/backend-enterprise-structure-http-third.json) passed, with [actual UI observations](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-structure-http-007ab14b2299/main-ui-observations.json). Ad/adv NULL and quote defaults now match finite PHP7 cases in [19 real-method comparisons](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/ad-nullable-intro-comparison-v2.json). The historical target dirname collision raised 1062 with complete rollback, outside the 67 runner passes. A later [server follow-up](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-directory-collision-http-second.json) passed 24 checks / 10 authenticated requests with Chinese rejection and 19-table cleanup; [five independent exception cases](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-directory-conflict-independent-v1.json) passed. It preserves explicit directories, IDs and translations; no new browser/public-template or real concurrency acceptance is claimed.

Independent deployment preparation: [third-round finite rehearsal](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/standalone-deployment/5962a2dde18d/report.json) passed all 17 steps and [owned cleanup](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/standalone-deployment/5962a2dde18d/cleanup-verification.json), including fresh/repeated initialization, original PC/mobile HTTP with two workers, restart persistence and 165-table backup/restore. The earlier two port-probe failures remain. Existing frozen vendor was copied in that historical rehearsal; the separate November 4 fresh Composer14 increment above now covers locked dependency installation. Full existing-site migration, new-admin browser login and the final frozen regression remain outside both finite proofs. S6 remains open. See [deployment](../deployment.md).

Current S3 increment: [lifecycle and deletion verification](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-lifecycle-verification.md) records restore entry, multilingual fields/helpers, deletion and UI-cache protections. Later [L11 writer35](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-writer-concurrency-second.json) covers three controlled real InnoDB lock-wait orderings; [L12 worker66](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-workers-http-second.json) covers stable PIDs before/after actual writes in both languages and PC/mobile list/detail partitions. [L04 persistence29](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-persistence-second.json) adds finite original-method3/candidate8/comparison12 title/parameter contracts, actual late-trigger rollback, alias/translation retry and four tag assertions/eight auto+newattr outputs. Its later LanguageService diff is reviewed separately from L11's old frozen source. [L05 public route26](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-public-deletion-http-second.json) proves actual business-language deletion, target list/detail404, main200, direct language-row/counter validation and164-table restoration. The UI baseline was empty with no new UI file; nonempty cache preservation remains covered only by prior wrapper26/DB72 in their own scopes. No new browser acceptance is claimed. [The 22-item checklist](stage3-acceptance-scope.md) now has finite config/eligibility method and owned157 DB evidence; normal query-link HTTP now has fifth-round finite evidence. Single-page finite initialization now has separate memory/DB33/HTTP85 evidence; nonempty custom defaults and explicit historical repair now have a separate finite November 4 increment; unknown orphan migration, broader dynamic2 models/routes outside finite lists139/Article view52 and required UI gates remain open. Browser UI remains pending_environment. Existing broader task statuses and S3 remain open; [new follow-up binding](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/stage3-followup-evidence-v1.json) leaves v1 snapshots/reports unchanged.


Current config/eligibility follow-up (single finite closure and binding below): [original18](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-config-independent-original-v1.json), [candidate19](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-config-independent-candidate-v2.json), [copy contracts16](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-config-independent-comparison-v2.json) and [actual DB20](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-config-persistence-first.json) cover finite afterAdd web_name/SEO/config metadata. [Eligibility memory25](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-copy-eligibility-current-binding-v2.json) and [actual incremental/eligibility DB25](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/new-language-incremental-eligibility-first.json) cover category qualification and L01 new-source-article→sync twice with translations retained. Both DB batches restore original157 row/column maps and remove owned tables/workspace. Recycle omission, atomic orphan rejection and operation authorization instead of actor-specific tree cropping are explicit safety differences, not exact original parity.

[Config HTTP third](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-config-http-third.json) records1 authenticated/2 public requests: actual save/SQL/PC query tags pass, generated dynamic-format2 link returns404 and finally restores164 tables exactly. [Original method order13](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-dynamic2-path-order-original-v2.json) reproduces the late path-language/cached-home category miss under finite PHP7 memory adapters, not original final HTTP404. The separate [normal query-link fifth round](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/language-config-http-fifth.json) passed75 with4 authenticated/16 public requests on frozen2359 source,20 response hashes and exact164 cleanup; dynamic-format2 failure remains explicit. Later single edits have a separate read-only v3 source-path review24 and actual current-source single DB33/HTTP85; old configuration business tests were not rerun. [Single structure/metadata before](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-language-copy-contract-before-v2.json) retains the real pre-repair contract failure; finite repaired memory/DB33/HTTP85 evidence follows separately. First/second tool failures and exact cleanup receipts remain in [the lifecycle record](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/stage3-lifecycle-verification.md); S3 stays active/gate=false and no new browser acceptance is claimed.

## Executable tasks

### T01 [P1] finite_branch_differential_and_http_passed

Owner: template

Tags: specnode, specnodelist, pagespecnode

Attributes: `isauto`, `typeid`, `aidlist`, `keyword`, `title`, `limit`, `row`, `loop`, `titlelen`, `bodylen`, `infolen`, `thumb`, `key`, `mod`, `empty`, `listitem`, `listsize`

Fixture: 专题含手选节点和自动节点；两个栏目、五条文档；页容量2；有匹配/无匹配关键字。

- isauto=0手选顺序与aidlist显式覆盖
- isauto=1按typeid及keyword筛选
- row/loop/limit优先与截断/缩略图字段
- pagespecnode首页/第二页/末页/空节点，listitem与listsize

Evidence: `evidence/nonempty-branch-differential.json`, `evidence/nonempty-branch-cases.json`, `evidence/tag-related-branches-differential.json`, `evidence/tag-related-branches-cases.json`

Target: tests/compat/special_branch_cases.json + actual isolated original differential; transaction candidate fixture if original data cannot represent a branch

Pass criterion: 非空文档ID顺序、内容/图片字段及分页href逐项断言；原版差异保留明确分类。

### T02 [P1] finite_branch_and_http_passed

Owner: template

Tags: screening

Attributes: `addfields`, `addfieldids`, `alltxt`, `anchor`, `currentclass`, `currentstyle`, `id`, `key`, `mod`, `empty`

Fixture: 产品分类含两个可筛选自定义字段，每字段至少两个值，配置顺序与ID不同。

- addfields名称与addfieldids ID两种入口
- 单字段选择、两字段组合、全部选项清除单条件
- 保留keywords/排序/语言/城市参数，anchor保留
- 当前值高亮、自定义key/mod/id及空值

Evidence: `evidence/nonempty-branch-differential.json`, `evidence/nonempty-branch-cases.json`, `evidence/screening-http.json`

Target: tests/compat/screening_branch_cases.json + real HTTP generated filter links

Pass criterion: 选项链接实际HTTP返回正确文档集合；URL参数不丢失、不重复编码。

### T03 [P1] finite_branch_and_http_passed_with_explicit_original_differences

Owner: template

Tags: tag, tagarclist, hotwords

Attributes: `getall`, `sort`, `typeid`, `type`, `name`, `row`, `loop`, `offset`, `limit`, `style`, `keyword`, `subday`, `maxlength`, `screen`, `orderby`, `ordermode`, `orderway`, `orderWay`

Fixture: 三层栏目、跨栏目同TAG与独立TAG，分离week/month/total计数和创建时间；搜索词有过期/屏蔽/不同热度。

- tags sort=new/month/week排序，getall=0/1与当前aid
- type=self/son/top/sonself/first范围
- tagarclist匹配/无匹配、分页/截断与自定义字段
- hotwords hot/new/sort_order、subday/maxlength/screen；随机排序只比集合

Evidence: `evidence/nonempty-branch-differential.json`, `evidence/nonempty-branch-cases.json`, `evidence/tag-related-branches-differential.json`, `evidence/tag-related-branches-cases.json`, `evidence/tagarclist-http.json`

Target: tests/compat/tag_search_branch_cases.json + readonly original differential

Pass criterion: TAG与文档ID集合/顺序和标签链接明确；随机值不作固定顺序断言。

### T04 [P1] finite_branch_and_http_passed_with_explicit_original_differences

Owner: template

Tags: likearticle

Attributes: `byabs`, `mytypeid`, `typeid`, `modelid`, `channelid`, `limit`, `row`, `loop`, `titlelen`, `bodylen`, `infolen`, `thumb`, `empty`, `id`, `key`, `mod`, `name`

Fixture: 当前文章与四条同/异栏目、关键词重合/不重合、不同模型的公开文档。

- byabs两种策略，当前aid排除
- mytypeid/typeid和modelid/channelid范围/优先
- 无相关结果empty，row/loop/limit，正文截断和thumb
- name数据集与嵌套变量作用域

Evidence: `evidence/nonempty-branch-differential.json`, `evidence/nonempty-branch-cases.json`, `evidence/tag-related-branches-differential.json`, `evidence/tag-related-branches-cases.json`, `evidence/tagarclist-http.json`

Limits:

- Six likearticle name dataset cases repair an original PHP7.2 ParseError. Equivalent original volist data/window/scope assertions and candidate HTTP prove intended behavior; they are not direct original likearticle matches.

Target: tests/compat/likearticle_branch_cases.json + readonly original differential

Pass criterion: 明确期望文档ID及顺序，不以非空HTML代替相关性断言。

### T05 [P1] finite_branch_http_and_template_browser_passed

Owner: template

Tags: formreply

Attributes: `typeid`, `aid`, `page`, `pagesize`, `row`, `limit`, `ordermode`, `id`, `key`, `mod`, `empty`

Fixture: 三条已审、一条未审留言；含区域9、时间10、图片5/11、附件8字段。

- 只显示examine=1，asc/desc，page=1/2/越界与pagesize
- 字段转换为实际区域名、格式时间、图片HTML和附件URL
- 空栏目及自定义id/key/mod
- 明确核对注册但运行未读取的aid/row/limit是否原版忽略

Evidence: `evidence/nonempty-branch-differential.json`, `evidence/nonempty-branch-cases.json`, `evidence/formreply-http.json`, `evidence/browser-formreply-more.json`, `evidence/language-api-context.json`, `evidence/browser-mobile-formreply-more.json`

Limits:

- Mobile template browser interaction uses 390x844 with desktop navigator.userAgent; original contextless API correctly appends PC fragments. Actual iPhone User-Agent/mobile fragments are verified separately by HTTP56. This is combined evidence, not a real iPhone browser claim.

Target: tests/compat/formreply_branch_cases.json + transaction nonempty fixture + HTTP attachment fetch

Pass criterion: 页内留言ID/字段值及附件字节明确；未审记录不泄漏。

### T06 [P1] finite_branch_differential_passed

Owner: template

Tags: arclist, list

Attributes: `idrange`, `aid`, `arcrank`, `release`, `joinaid`, `type`, `channelid`, `bodylen`, `infolen`, `thumb`, `orderway`, `orderWay`, `key`, `mod`, `titlelen`

Fixture: 同模型不同栏目，公开/待审/隐藏文档、同排序值、包含附表的中文正文与图片。

- idrange、aid、idlist与typeid/flag/keyword互斥优先
- arcrank/release分支及joinaid附表
- orderby多字段与两种orderway拼写
- 中文title/body/infolen截断，thumb路径，key/mod嵌套

Evidence: `evidence/document-navigation-branches-differential.json`, `evidence/document-navigation-branches-cases.json`

Target: tests/compat/nonempty_branch_fixture.php document-navigation + document_navigation_cases.php

Pass criterion: 只补现有16参数差分未覆盖的值分支；保留已验证SQL limit后compiler offset的原版双偏移行为。

### T07 [P2] finite_branch_differential_passed

Owner: template

Tags: type, arcview, field, diyfield, position, prenext

Attributes: `dirname`, `type`, `addfields`, `addtable`, `joinaid`, `symbol`, `style`, `typeid`, `titlelen`, `limit`, `empty`, `id`, `key`, `mod`

Fixture: 三层栏目、相邻三条文档、自定义栏目附表和文档附表。

- type按dirname及top/parent/son语义，附表字段和缺失栏目
- arcview joinaid/empty与field附表
- position自定义symbol/style/显式typeid
- prenext首/中/尾和标题截断；diyfield多类型及空值
- 新增PC/mobile arcview缺附表正常补行、重复读取唯一行和设备正文，候选补行后与只读原版稳态逐字匹配。
- 官方doc8812实际view_logic：产品/图集/下载PC/mobile非空及空输出，10断言=9真实原版完整HTML匹配+1native READ ONLY guard；5个download form和1helper script另断言。

Evidence: `evidence/document-navigation-branches-differential.json`, `evidence/document-navigation-branches-cases.json`, `evidence/template-execution-edges-http.json`, `evidence/view-fields-differential.json`, `evidence/view-fields-cases.json`, `evidence/view-fields-post-ast-differential.json`, `evidence/view-fields-post-ast-cases.json`

Limits:

- 历史只读两边拒绝补行证据保留；新增候选正常可写补行及持久化有限证据。原版oracle仍只读，逐字比较仅覆盖候选补行后的稳态，不是原版数据库写入差分。
- doc8812是实际模型/view_logic/compiler CLI字段输出；没有文件HTTP读取、下载动作或后台浏览器。post-ast仅为该阶段源码快照。

Target: tests/compat/nonempty_branch_fixture.php document-navigation + document_navigation_cases.php

Pass criterion: 输出具体字段和上下篇ID/href，附表缺失应遵循原版结果或明确报错。

### T08 [P2] finite_branch_differential_passed

Owner: template

Tags: channel, channelartlist, citysite, navigation

Attributes: `loop`, `limit`, `row`, `offset`, `titlelen`, `pid`, `name`, `empty`, `key`, `mod`, `currentclass`, `currentstyle`, `orderby`, `ordermode`, `orderway`, `alltxt`

Fixture: 已有城市/栏目主分支证据保留；补一棵兄弟至少3项、深度3的树，排序与ID不同。

- channelartlist模型别名、row/loop与内层arclist上下文恢复
- tree row/limit/offset边界及currentclass/currentstyle别名
- navigation alltxt与显式排序
- citysite pid、截断和空分支，不重复18+18城市路由测试

Evidence: `evidence/tree-branches-differential.json`, `evidence/tree-branches-cases.json`

Target: tests/compat/nonempty_branch_fixture.php tree-branches + tree_cases.php

Pass criterion: 节点ID/顺序/高亮及嵌套后外层变量准确；不把单进程树测试称作城市HTTP路由测试。

### T09 [P2] finite_branch_differential_passed

Owner: template

Tags: foreach, volist, for, compare, case, function, if, elseif, switch, include

Attributes: `offset`, `length`, `limit`, `row`, `loop`, `empty`, `key`, `mod`, `comparison`, `step`, `type`, `break`, `use`, `expression`, `file`

Fixture: 带非连续关联key的数据集，空集，闭包捕获变量，正/负步长。

- foreach/volist窗口、别名优先、自定义key/mod及嵌套外层恢复
- for step=-1/2与比较运算边界
- case break=0穿透与compare显式type
- function use捕获及返回结果
- if/elseif/switch原始expression与include动态file已有案例先映射来源；仅补无独立断言的分支。

Evidence: `evidence/syntax-branches.json`, `evidence/syntax-branches-differential.json`, `evidence/syntax.json`, `evidence/template-expansion-after-warning.json`

Target: tests/compat/syntax_branch_cases.json + actual isolated original syntax differential

Pass criterion: 字节输出与作用域一致；既有equal/notequal/in原版缺陷保持单列，不重计匹配通过。

### T10 [P2] finite_branch_differential_and_http_passed

Owner: template

Tags: sql, load, static, url, diyurl, lang, language

Attributes: `cachetime`, `empty`, `id`, `key`, `mod`, `file`, `type`, `value`, `basepath`, `lang`, `href`, `code`, `site`, `domain`, `suffix`, `seo_inlet`, `seo_pseudo`, `seo_pseudo_format`, `const`, `limit`, `currentclass`

Fixture: 两语言、两个域名、部署base路径、无副作用SELECT及实际CSS/JS文件。

- sql cachetime=0/正值在更新后失效、空结果与key/mod
- load/static本地/远程、lang/site/code属性及显式href优先
- url/diyurl显式domain/suffix/inlet覆写；已有12 URL模式保留
- lang const与缺失键，language row/limit/高亮
- static code/lang/site真实TagStatic/Base/helpers/compiler44严格HTML字节匹配，PC/mobile及root//cms，version=false；保留早期CRLF/LF失败及normalize语义阶段。

Evidence: `evidence/helper-branches.json`, `evidence/helper-branches-differential.json`, `evidence/helper-language-cases.json`, `evidence/helper-language.json`, `evidence/helper-language-differential.json`, `evidence/helper-branches-http.json`, `helper-verification.md`, `evidence/remaining-attribute-compile-contract.json`, `evidence/remaining-static-attributes-differential.json`, `evidence/remaining-static-attributes-semantic-differential.json`, `evidence/remaining-static-attributes-byte-fixed-differential.json`

Limits:

- 16compiler契约不是16业务渲染；static44为声明service context，不是完整城市路由或HTTP资源取回。511/524台账冻结，13项新证据另列。

Target: tests/compat/helper_branch_cases.json + helper_branch_fixture.php + verify_helper_branches_http.py

Pass criterion: 缓存更新由另一worker读取；本地资源实际GET，不以URL字符串存在代替可读。

### T11 [P2] finite_branch_differential_and_http_passed

Owner: template

Tags: ad, adv, flink, faq, searchform, guestbookform, videoplay, videolist, arcclick, attribute, asklist

Attributes: `where`, `orderby`, `loop`, `limit`, `titlelen`, `empty`, `currentstyle`, `currentclass`, `flag`, `noflag`, `notypeid`, `modelid`, `before`, `beforeSubmit`, `o`, `key`, `mod`, `type`, `value`, `offset`, `orderway`, `row`

Fixture: 保留广告/友链/FAQ/媒体和17项参数既有证据，补空组、排序冲突、表单钩子与公开计数。

- adv/flink/faq where/排序/窗口与空组
- searchform组合筛选隐藏字段，guestbookform before/beforeSubmit别名回调
- videoplay/videolist空aid/无文件与自定义key/mod
- arcclick type/value和attribute显式offset是否按原版忽略/采用
- asklist非空row/orderway及自定义key/mod；双参数limit原版编译缺陷保持单独差异。

Evidence: `evidence/misc-branches-before.json`, `evidence/misc-branches-differential.json`, `evidence/misc-branch-cases.json`, `evidence/http-interactions.json`, `evidence/tag-attribute-branches.json`, `evidence/public-counters-http.json`

Limits:

- Public counter HTTP tests assert current-language ordinary enterprise documents. They do not assert original site-wide analytics writes or exposure rules for foreign-language/recycled documents.

Target: tests/compat/misc_branch_fixture.php + runtime/compat/misc-branch-cases.json + public endpoint HTTP boundaries

Pass criterion: 只补具体未覆盖分支；不重开已通过的短信验证码/媒体授权/下载字节主流程。

### T12 [P2] finite_extension_contract_passed

Owner: template

Tags: weapp, weapplist, weapptaglib

Attributes: `currentclass`, `currentstyle`, `id`, `key`, `mod`, `length`, `loop`, `offset`, `row`

Fixture: 仅本地测试扩展入口与函数数组，不安装或启用第三方业务插件；两个以上结果用于窗口和当前项断言。

- 缺失插件/位置返回原版空态，不运行未知插件代码。
- weapplist当前项样式、自定义id/key/mod及无结果。
- weapptaglib函数数据集offset/length/row/loop优先级，嵌套上下文恢复。

Evidence: `evidence/extension-contract-differential.json`, `evidence/extension-contract-cases.json`, `evidence/differential-extended.json`

Target: tests/compat/extension_contract_fixture.php + run_extension_contracts.py

Pass criterion: 仅扩展加载契约与标签输出有明确ID/顺序/样式断言；不声明第三方插件业务兼容。

### A01 [P1] finite_attribute_gap_batch_passed

Owner: template

Tags: arclist, channelartlist, citysite, diyurl, faq, guestbookform, hotwords, likearticle, list, navigation, searchform, tag, tagarclist, uiarclist, uichannel, uicode, uihtml, uitext, uitype, uiupload

Fixture: tests/compat/attribute_gap_cases.php

- 33项：31真实原版输出逐字一致、1原版只读保护、1原生arcmulti保护写拒绝；expected guard不作为正常渲染或原版写入成功。
- 模型别名/排除、窗口优先级、自定义UI变量、自动表单null/有效地区、class别名与热词空结果；navigation.loop按原版不影响数量。
- 原生arclist真实HTTP12与auto表单HTTP6通过；arclist metadata属性/hash/SQL为源码契约与候选持久化断言，8个AJAX回包另作原版等价模板比较，不声明原版数据库写入差分。
- tagarclist18与tree28回归通过，DB/模板/上传夹具清零。

Evidence: `evidence/attribute-gap-branches-differential.json`, `evidence/attribute-gap-branches-cases.json`, `evidence/attribute-gap-branches-differential-before.json`, `evidence/attribute-gap-branches-cases-before.json`, `evidence/arclist-http.json`, `evidence/guestbook-auto-http.json`

Target: Initial 50-pair source audit and concrete missing explicit-input partitions

Pass criterion: 保留初始源码审计、确切case与输入；新增观察不等于所有取值/默认值/嵌套组合兼容；guard、unused与真实原版匹配分开。

### A02 [P1] finite_cross_stage_http_passed

Owner: template

Tags: tagarclist, arclist

Attributes: `name`, `offset`

Fixture: tests/integration/tagarclist_phases_http.php

- PC/mobile control name空与name=:array()，两者offset=2的初始ID相同（4项）。
- 原生raw属性/hash持久化契约4项：name/offset均被记录，初始查询不作数据集或offset窗口。
- 实际AJAX4项与PHP7.2原版等价arclist输出逐字一致：name传入后才启用dataset；offset/row被两版Ajax显式跳过，computed limit生效；dataset空时lastpage仍按SQL总数为0。
- 所有DB/附表/arcmulti/临时模板清零。before仅为fixture tagid超过varchar60错误，不是产品缺陷。

Evidence: `evidence/tagarclist-phases-http.json`, `evidence/tagarclist-phases-http-before.json`

Target: Initial compiler, serialized metadata and reconstructed AJAX tag semantics

Pass criterion: 不能以初始compiler未读取属性推断全路径unused；原版等价AJAX输出与候选metadata持久化契约分开标明。

### T13 [P1] finite_differential_http_concurrency_passed

Owner: template

Tags: include, arcview

- 城市modern/legacy Eyou include优先级原版真实compiler差分8/8；before 6/8暴露两个误选BASE真实缺陷，已修。原版适配器拒绝DB类，候选仅文件模板。
- 城市include实际HTTP59/59：PC/mobile、现代/legacy/base、冷暖配置和双worker、语言模式往返、绝对/遍历/symlink越界拒绝后上下文恢复。
- 执行边界52/52：24并发HTTP动态include/extend + 1双worker；16独立PHP进程layout冷暖 + 1临时文件清理；10个PC/mobile arcview补行/重复/设备/只读原版稳态断言。
- 旧32项单进程扩展/layout回归32/32，不计额外原版差分。独立审计早期expand_43062a3bda精确清理2编译文件/2oracle目录，所属DB残留0、无关cache未动。
- 此前post-compat原包HTTP46/46，资源PC原缺CUR导致非零的报告冻结保留。本轮post-runtime重新渲染46/46，PC22页71资源/mobile22页72资源均0失败、incomplete=false；所有所属夹具0、模板写入0。
- include.file另8项真实原compiler/相同所属文件输出匹配，city/legacy/off/扩展名回退；无DB、无controller HTTP。

Evidence: `evidence/template-execution-edges-http.json`, `evidence/include-resolution-before.json`, `evidence/include-resolution.json`, `evidence/template-expansion-city-regression.json`, `evidence/city-include-http.json`, `evidence/template-execution-edges-cache-cleanup.json`, `evidence/template-pages-post-compat.json`, `evidence/template-page-assets-pc-post-compat.json`, `evidence/template-page-assets-mobile-post-compat.json`, `evidence/template-pages-post-runtime.json`, `evidence/template-page-assets-pc-post-runtime.json`, `evidence/template-page-assets-mobile-post-runtime.json`, `evidence/asset-inventory-post-runtime.json`, `evidence/remaining-include-file-differential.json`

Limits:

- 动态include/extend并发来自真实Webman HTTP；layout共享配置并发来自独立PHP进程，不混称HTTP layout。
- 候选arcview正常写补行有证据，原版仍只读；原版逐字比较仅候选补行后稳态。
- 原包44企业独立页（其中2空mobile按PC fallback）+2个AJAX追加片段形成46渲染断言，6公共片段随父模板；不证明浏览器交互、所有属性组合或未提供官方主题。

Target: tests/compat execution-edge/city include runners + template_pages.php

Pass criterion: 有限原版优先级/候选并发与原包HTTP回归通过；当前资源请求通过，历史失败及补充资源来源另见E02。

### T14 [P1] finite_original_differential_http_passed

Owner: root

Tags: language

- 实际原switch_language/cookie(lang_info)与原compiler逐进程只读差分：单语言无合成globals；24组合覆盖dynamic/rewrite、inlet开关、根/子目录、默认/h/j语言和显式绝对URL。
- 完整150断言=50 CLI原版/候选及native cookie断言+96根部署PC/mobile HTTP+4双worker上下文；所属语言/config/源及编译模板/oracle cache全部0。CLI-only50为同套子集，不累计为独立语义覆盖。
- 真实原switch_language query54/path60覆盖disabled/case/empty/zero/mobile/entry；最终post-city选择60/60及cookie根HTTP50/50双worker，fixture0。

Evidence: `evidence/language-globals-cli.json`, `evidence/language-globals.json`, `evidence/language-globals-before-bootstrap.json`, `evidence/language-globals-before-empty-map.json`, `evidence/language-selection-before.json`, `evidence/language-selection-expanded.json`, `evidence/language-selection-expanded-after.json`, `evidence/language-path-selection-before.json`, `evidence/language-path-selection.json`, `evidence/language-selection-http.json`, `evidence/language-selection-http-after-middleware.json`, `evidence/language-selection-http-before-home-ajax.json`, `evidence/language-selection-http-final.json`, `evidence/language-selection-http-final-stable.json`, `evidence/language-globals-post-ast-http.json`, `evidence/language-path-selection-post-city-stages.json`, `evidence/language-selection-http-post-city-stages.json`

Limits:

- 早期globals before为bootstrap及空map验证器错误；query/path/disabled选择及cookie传播before保留真实生产缺陷。
- 路径差分只比较原switch_language在Route前的选择，route status仅诊断；城市路由另见T16。
- global150的CLI50为重叠子集；子目录globals仍CLI，文档级切换、浏览器及所有外部主题未验证。

Target: tests/compat/verify_language_globals.py

Pass criterion: 官方doc11886的lang_title/lang_url/lang_logo真实HTML和原cookie合并语义匹配；有限证据不升级为完整语言功能验收。

### T15 [P1] finite_scalar_evaluation_differential_http_passed

Owner: template

Tags: include

- 原20标量路径及5保护，最终deferred上下文147=144渲染+3相同native错误；35项container/key/RHS/private/dynamic变量求值渲染匹配。
- 最终生命周期/隔离44通过，含18冷暖、外部key异常恢复、引用逃逸及GC，目标残留0/fixtures_removed。
- 最新实际HTTP1190断言/400请求：384成功字节匹配+16helper故障500；192原PHP7观察=184正常+8故障，46成功分支、2稳定worker/8lane、400入口depth1、六cleanup0。
- 原暖编译依赖3；候选依赖31/语法24及23/四进程14+32+34+7保留，不能混为原版渲染。
- 旧147后container/key/RHS仍有新缺陷；ArrayAccess漏ReturnTypeWillChange及弱引用未GC预期错误归harness，before不改写。

Evidence: `evidence/scalar-path-probe-before.json`, `evidence/scalar-path-strict-before.json`, `evidence/scalar-path-warning-fix.json`, `evidence/scalar-warning-boundary-regression.json`, `evidence/include-dependency-before-input.json`, `evidence/include-dependency-before.json`, `evidence/include-dependency-differential.json`, `evidence/template-dependency-boundaries-before-input.json`, `evidence/template-dependency-boundaries.json`, `evidence/template-external-alias.json`, `evidence/template-expansion-alias-regression.json`, `evidence/scalar-paths-alias-http-before-recovery.json`, `evidence/scalar-paths-alias-http.json`, `evidence/scalar-path-initial-cache-cleanup.json`, `evidence/scalar-path-ast-temporary-fixed.json`, `evidence/scalar-path-context-ast-temporary-fixed.json`, `evidence/scalar-path-context-deferred-final.json`, `evidence/offset-evaluation-deferred-final.json`, `evidence/scalar-offset-lifetime-deferred-final.json`, `evidence/scalar-offset-contexts-http-deferred-final.json`, `evidence/offset-review-container-evaluation.json`, `evidence/offset-review-key-order-before.json`, `evidence/offset-evaluation-before.json`, `evidence/offset-evaluation-deferred-expanded.json`, `evidence/offset-evaluation-assignment-expanded.json`, `evidence/template-dependency-ast-final.json`, `evidence/syntax-ast-final.json`, `evidence/syntax-branches-ast-final.json`, `evidence/offset-ast-regression-final.json`, `evidence/scalar-offset-contexts-http-native-filters-final.json`

Limits:

- 3个匹配native错误不是成功渲染；helper warning故障仅算隔离检查。
- 有限表达式不证明全部PHP7语法/签名/常量表达式/外部主题；旧280/344请求批次与最终400重叠，不累计独立业务。

Target: tests/compat scalar paths / dependency / external alias runners

Pass criterion: 有限标量/求值差异已逐项修复且保留业务异常隔离；整体目标按E03及其它未完成项继续。

### T16 [P1] finite_native_source_contract_controller_http_passed

Owner: root

Tags: citysite, include, static, url

- 真实原PHP7 Request/Route/完整registry/抽取Common共65例，memory city/config repository；重复source逐对象相同。
- 候选controller65/65、实际root HTTP65/65及明确/cms部署HTTP65/65，fixture0/配置恢复/根部署恢复。before各15/65及首轮after59/65保留。
- 分别验switch缓存/Route参数/Common模板；registry匹配后可覆盖query site，Host/subDomain/empty/zero/default按原源，非无条件query>path。

Evidence: `evidence/route-context-original-city-stages-source.json`, `evidence/route-context-original-city-stages-reproduced-source.json`, `evidence/route-context-before-city-stages-controller.json`, `evidence/route-context-before-city-stages-http.json`, `evidence/route-context-after-city-stages-controller.json`, `evidence/route-context-after-city-stages-final-controller.json`, `evidence/route-context-after-city-stages-final-http.json`, `evidence/route-context-after-city-stages-subdirectory-http.json`

Limits:

- source oracle为memory repositories，非原应用真实DB/HTTP；65例不穷尽URL/外部主题。
- 旧switch stand-in9/12及候选29/30为历史有限断言；dispatch33旧switch_status误含Common404，65新报告已分阶段。

Target: tests/compat/original_city_dispatch_cases.py + tests/integration/city_dispatch_stages.php

Pass criterion: 按原真实路由source分阶段期望验候选controller/root及实际子目录HTTP。

### T17 [P1] finite_php7_tag_comparison_passed_static_reference_defect_open

Owner: template

Tags: if, compare, switch, case, range

- 标记Eyou/Cx比较统一PHP7语义：helper9算子×4624 typed pairs=41616零差异；实际compiler family112、最终CV/switch30、实际worker18、统一range24分别验且重叠，不累计独立业务。
- 保留原alias bug、初始helper贴{触发模板解析、独立AST发现的引用可见性/magic dispatch/调用者scope/subject清理修前历史；修复仅覆盖编译器标记比较，strict和raw PHP继续native PHP8。
- 源码冻结comparison-family-final-frozen-v1，缓存version12。后续整包HTTP46/44唯一浏览器页面通过，无法关闭实际静态引用失败。
- 后续只读可行性调查8case×6策略/2metadata、9期望检查；v1对原PHP7/8原生引用生命周期期望错误保留，v2修期望。不改冻结生产；普通metadata未找到通用无副作用检测，有限支配AssignRef AST路线只属未来提案，真实失败继续开放。
- 新增28真实原PHP7/当前v12 PHP8/仅内存trial模板调查，6项调查/拒绝断言通过；紧邻AssignRef在同赋值中可触发旧对象析构重绑，trial修6目标loose但新增6析构反例回归，方案已拒绝，生产/缓存v12未改。before-v1用GLOBALS误判模板local CV的夹具scope历史保留。
- 新增slot migration仅内存原型调查，11JSON（10原始报告+review）及9测试源/6生产hash保留。无内部注入的精确原型有4同步GC丢写反例，actual-tag有10动态形式差异和4private/protected权限错误，方案拒绝进入生产，cache v12未变。调查期望满足不代表兼容通过。

Remaining explicit branches:

- static_property_existing_reference真实Eyou及Cx compare标签均失败：左static属性已=&外部变量，右操作数修改别名，原PHP7 yes:2、候选PHP8 no:2。
- 未穷尽所有扩展对象handler、PHP语义与全部可下载官方/第三方主题；原218模板+手册/source有限扫描无static命中不等于全主题无此写法。
- 新28模板中的6个targeted loose静态引用差异继续开放；另外2个strict及2个raw PHP lifetime差异保持native PHP8明确边界，不能把调查passed当修复。
- mutation slot迁移被同步GC、动态目标及权限反例拒绝；无生产修复，6个loose静态引用差异仍开放。

Evidence: `evidence/comparison-family-final-frozen-v1.json`, `evidence/loose-comparison-matrix-after-v1.json`, `evidence/comparison-family-inline-v1.json`, `evidence/comparison-family-final-switch-v1.json`, `evidence/comparison-worker-first-v1.json`, `evidence/range-membership-unified-comparison-v1.json`, `evidence/comparison-ast-independent-audit-fourth.json`, `evidence/comparison-inline-followup-first.json`, `evidence/comparison-reference-metadata-followup-confirmed.json`, `evidence/comparison-static-real-tags-first.json`, `evidence/comparison-static-source-inventory-v1.json`, `evidence/comparison-static-reference-feasibility-v1.json`, `evidence/comparison-static-reference-feasibility-v2.json`, `evidence/comparison-static-reference-proof-before-v1.json`, `evidence/comparison-static-reference-proof-before-v2.json`, `evidence/comparison-static-reference-proof-trial-v1.json`, `evidence/comparison-static-reference-proof-final-v1.json`, `evidence/comparison-static-reference-proof-freeze-v1.json`, `evidence/comparison-static-slot-migration-review-v1.json`, `evidence/comparison-static-slot-migration-gc-uninstrumented-v3.json`, `evidence/comparison-static-slot-migration-scopes-v2.json`

Limits:

- raw PHP函数/闭包体保留PHP8行为；比较器有限通过不代表全PHP7兼容。
- membership旧freeze生产hash先于family扩展，是历史快照。PHP7 throwing __toString致命错误以原始诊断归档，不计成功。
- 相邻AssignRef假设已经析构反例拒绝；更完整prefix/可析构值/reentrant效果分析未实现，不声称已证明其它AST优化安全。

Target: app/cms/compat ComparisonCompiler/ComparisonValue/Runtime + Eyou/Cx markers

Pass criterion: 所有有限比较分层保持通过且真实static引用缺陷明确开放；缺陷未解决前整体目标继续。

### T18 [P1] finite_language_channelartlist_readonly_stage_differentials_passed

Owner: docs

Tags: language, channelartlist

- Language64实际原PHP7/candidate PHP8只读compiler/DB渲染：16例×两owned语言×PC/mobile，currentclass空/字符串0、currentstyle fallback、默认type、row/loop0、limit与嵌套same/different id。
- Channelartlist72实际只读差分：currentclass空抑制fallback但字符串0保留；currentstyle0为空；parent链和child查询空/非空。原编译器hardcode channelartlist、忽略id，嵌套清理outer变量遵原，不发明scope恢复。
- 首份channelartlist72原候选字节已匹配，独立期望误保留相邻PHP块间空白导致overall false；第二份只修harness预期后72/72。owned语言/config/栏目/cache精确0，保护表和原/copied模板SHA稳定。
- 13-pair属性gap只读crosswalk：5对已有证据但旧collector未读取、7对原源码未用、1对searchform.empty正常不可达；不是13个缺实现，不更新511/524冻结输入台账。

Evidence: `evidence/language-style-default-scope-first.json`, `evidence/channelartlist-style-scope-stage-first.json`, `evidence/channelartlist-style-scope-stage-second.json`, `evidence/enterprise-attribute-next-batch-audit-20261103.json`

Limits:

- 这些是比较器v12之前的source-SHA-bound阶段，未声称用最终compiler重跑。
- 原/candidate使用候选导入库native READ ONLY事务，不启动原完整应用/HTTP；93partial/10unverified和511/524状态保持。

Target: tests/compat verify_language_style.py / verify_channelartlist_style.py / audit_attribute_gap_crosswalk.py

Pass criterion: 有限默认值、样式和scope差分及清理通过；更广值域与主题覆盖继续按其它验收项。

### B01 [P0] finite_http_actual_browser_recorded_reset_map_external_pending

Owner: root

Tags: ui, uitext, uihtml, uitype, uiarclist, uichannel, uiupload, uibackground, uimap, uicode

- 13个Uiset动作实现与9类submit保存
- 原JS弹层/回包、当前主题/设备/语言/页面隔离
- 后台认证/权限、上传、跨worker失效与fixture重置
- 已实际发现runtime/ui与runtime/cms/ui根不一致导致第二次保存仍读旧值；修复后三轮72次跨worker更新与24次clear后读取均已通过。
- 实际已认证浏览器完成九类PC可视编辑器与mobile文字、真实文件选择上传；重开值与公开页面一致。实际reset确认取消，另行CLI精确clear后浏览器读取默认值，不能计浏览器成功reset。

Remaining explicit branches:

- 浏览器成功不可逆reset；当前只有确认取消与独立CLI清空后的浏览器结果。
- 使用有效地图AK的外部地图服务；当前只验证参数保存与iframe hash。
- 所有mobile编辑类型、主题与动作级实际受限角色账号登录仍未穷尽。

Evidence: `evidence/backend-ui-services.json`, `evidence/backend-uiset-http.json`, `evidence/map-geocoder.json`, `evidence/admin-ui-20261103-observations.json`, `evidence/admin-ui-root-cleanup-final.json`, `evidence/admin-ui-root-cleanup-public-audit.json`, `evidence/admin-visual-d7a3c960-final-ui-persistence.json`, `evidence/admin-visual-d7a3c960-cleanup.json`

Target: docs/eyoucms/uiset-contract.md

Pass criterion: 有限实际浏览器与HTTP联动已记录；reset成功与有效地图外部服务独立保留。

### B02 [P1] finite_select_f2b3_actual_ui_save_reopen_passed_broader_variants_pending

Owner: admin

- 实际后台文章独立PC/mobile正文、编辑回显、软删恢复与浏览按钮。
- TinyMCE真实图片Upload/file chooser、alt保存、重开及原PC页面公开加载。
- 实际自定义checkbox/imgs/files上传、图集caption编辑、删除旧项、清空末项和保存重开。
- 第一方helper接入原TinyMCE链接地址浏览按钮和原upload/file，实际本地TXT选择回填URL/text/title；11初始化options（insert5/update3/single3）只属源码/适配执行，不能算11实际UI。single3路径补原images_upload_url；v5补pearPopup依赖，v4仅给两个permission引用加物理contentSHA query。
- 真实root浏览器update1390/insert1392/single1393各PC/mobile附件选择、保存、DOM重开与DB独立正文；6原模板附件实际点击返回正确TXT，单页PC真实PNG chooser并加载64×48。v5保存成功提示与timestamp窗口新console0，insert/update v4独立reload logs0；历史失败保留。
- HTTP93/93（含共享UI/DB断言）只执行6正文+7上传=13GET；final96/96复用同断言并17GET404（6旧正文route+4额外栏目+7上传）。两报告不能相加为独立case；13remaining0/7uploaded files removed/13protected原表hash相同。
- 本批实测1280×720，无viewport/UA override，精确navigator.userAgent未采集；mobile显式goto非真触摸。旧http-final误沿用390×844文字，http-scope-corrected只修scope、引用旧SHA/原93checks/13responses，新增请求0。
- 真实单页存库后success回调TypeError由缺pearPopup导致，before/adapter/final实际toast分层。JSON logical401错误期待HTTP401、EOF额外newline和cleanup-audit误查全局language_attribute.lang均保留harness失败，未改原档。
- 新增实际model6字段1901-1905/type1083/aid1394：singlePage补imgs说明/配对顺序/末项显式空值，files按附件行回填；三view标准options API用param.id/value与dfvalue/label，legacy历史值精确保留属候选扩展。
- 原完整PHP7 Field默认值矩阵72；最终options v3 author适配1236/独立611、date helper五TZ原生Date/jsdom706/事务176/独立150分层。NULL/missing为原NOT NULL之外鲁棒边界；v2独立扩展矩阵103失败和其余修前历史保留，不累加重叠回归。
- 真实root三次save/toast/reopen：no-op保留Beta文字与五字段原值，编辑canonical option25/图说明/附件/日期秒/开关，再各一次原生PNG+TXT chooser并重开。actual DOM及最终截图702x938，无viewport/UA override；早期1280x720截图为旧时刻。TXT行wait一次超时后观察原上传已完成，未重复上传。
- 实际UI/DB/assets active54/54仅6精确GET哈希，cleanup16/16仅8GET404；root CLI精确清理八类owned rows0/十二保护原表hash稳定/single FULL COLUMNS恢复/六seed+upload文件不存在。不可变阶段inputs/journal/runtime报告保留。
- 初始55/55及uploaded最终56/56为原PHP7/候选PHP8 PC-mobile native READ ONLY差分：真实五addon列、TagField imgs原serialize/files CSV字节、FieldLogic及compiler/volist一致；CLI上下文不等于完整controller HTTP，也不增加真实UI路径数。
- 历史e107阶段生产以options v3三view/helper、原字节date helper及未变controller/services/attachment helper组合为准；B02v5、singlev1、optionsv1/v2、datev1旧view SHA均为历史。文件审计2134对0变化与dry-run164preserved/0created/migrations=[]不冒充整包重跑。
- singlePage新增loading/error/ready门禁：4端点成功及model6/记录校验后等待真实Pear TinyMCE init回调，addon失败不再main-only fallback；save handler与native submit均拒绝未ready提交。30秒超时进error、retry generation忽略旧响应/编辑器回调并销毁旧实例，ready不允许retry丢编辑。
- before38安全期望有35失败，3个真实不安全payload由ContentService回滚事务重放35/35，实际证明若接收会清空PC/mobile/imgs/files且回滚精确恢复；before pass意为复现不安全行为。
- 最终适配107/107、single40/date706/default1236及复用8分层；e107最终CSS与b3功能阶段全部7脚本及style外文档逐字一致，CSS bridge9/独立布局审阅18支持复用，未重复全矩阵。
- 最终ff真实HTTP500→logical code1→retry→no-op save/toast→直接8787重开；50/50含6精确清理GET404。1280×720，status50高/footer67高/底720，PC和mobile tab可见段落文字，DB正文精确；无法读取raw iframe HTML。历史9f36功能断言与ff-v1四个过严height期望失败保留。
- 两fixture各8类owned0/12保护表hash稳定/FULL COLUMNS恢复/4seed不存在；代理停止、tab关闭。该历史阶段single源码e107覆盖optionsv3单页，insert/update/helper/date与services原字节保留。
- 新增完整TinyMCE真实silver theme延迟54.727秒：超过页面30秒deadline实际error且save/fieldset禁用；一次同document retry仍held，释放后两个wrapper与两个唯一预期iframe、PC/mobile真实段落恢复。未记录native旧新对象identity/destroy flags，不作直接实例身份结论。
- engine真实批次69/69含6精确清理GET404，独立只读review40/40；恢复后仅1POST200，DB除update_time全部addon原字节，12保护hash稳定；root真实CUA直接8787重开双端，DOM JSON未独立location.href证明origin。
- 本批误等保存成功，production实际操作成功toast已消失，未捕获toast、未重复提交；save-toast-observation-history和after_save_wait_timeout DOM均保留。1POST及DB支持保存，不声称读取响应JSON code0。
- engine fixture清理8类owned0/12原hash稳定/FULL COLUMNS恢复/4seed不存在，测试tab关闭、8791及8788停止，原后台tab10登录保留并handoff。该engine阶段22个v3生产source均未变，single仍e107；新文件审计2134对0变化、DB dry-run164保留/0创建/无migration，不冒称全矩阵或前端重跑。
- 当前singlePage源码f2b3c16e1c4b604091844f40027d13785ff488c2e7be2e070e12f3f184bad056：WeakMap记录每个select原显式disabled，loading/error及动态render禁用并同步Layui代理，ready恢复原状态。已有内存Layui适配及分层回归报告；后续7e3c9a16批次补单字段有限真实UI，范围另列。
- f2b3真实CUA批次7e3c9a16：silver单请求held107535.726ms；首次观察已是timeout error，不称初始loading。error原生/代理select禁用，retry-loading pointer不能打开，释放后ready鼠标Choice B/键盘Choice A，再B并编辑PC正文。
- 真实操作成功toast、唯一POST200在release后，直接8787 URL重开PC/mobile编辑器；select存47对应Choice B、PC正文精确UPDATED，mobile及其它addon列原字节不变，12类原行hash稳定。
- 该批原PC/mobile模板两个实际DOM和HTTP200验证对应正文；19唯一CSS/JS实际GET200，6精确清理URL404，8类owned0、12保护hash稳定、原FULL COLUMNS恢复、4seed不存在。38/38检查包含上述分层及4截图存在，不是38独立业务。
- 独立只读审阅核对18个绑定输入/工具/源码SHA、观察/trace/DB报告及saved截图；未重跑审计脚本或新增HTTP/DB。loading keyboard误操作hidden retry被拒绝的历史保留，不算覆盖；孤立语言footer type/flink原缺binding仍有notice。

Current source change: `f2b3c16e1c4b604091844f40027d13785ff488c2e7be2e070e12f3f184bad056`; real browser verified: true for the finite scope below; outside the historical 826 archive.

Current inputs: [manifest](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-page-select-disabled-final-v1/manifest.json), [focused memory report](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-page-select-disabled-current-final-v1.json), [real UI38 report](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/single-page-select-ui-7e3c9a16-final.json).

Actual UI scope: One selects field and two system editors; one real silver delay, timeout and retry; ready pointer/keyboard, actual PC edit and selected value save, toast and direct reopen. Not full B02 or S2 acceptance.

Remaining explicit branches:

- 所有编辑器/模型/附件及图片类型/错误/权限组合和动作级实际受限角色登录未穷尽；11options不是11实际UI流程。
- 隔离语言未绑定cn硬编码typeid/groupid，原arclist/type/flink notice实际保留；本次只验正文附件链路，不能称整页零错或全部官方主题兼容。
- 完整引擎仅有限silver主题依赖延迟及一次retry；native旧/新editor对象identity/destroy flags、资源404及plugin/skin故障、多次retry、额外htmltext和所有组合仍未验。7e3c9a16补f2b3单字段真实UI，不把e107/v4旧浏览器证据自动升级。
- 真实loading阶段键盘禁用、原本显式disabled select在ready仍保持禁用、多select/更多动态替换与模型/角色组合未穷尽；7e3c9a16的loading keyboard因hidden retry目标被拒绝，没有实际键盘动作。

Evidence: `evidence/backend-editor-resources.json`, `evidence/backend-editor-roundtrip-http.json`, `evidence/backend-uploaded-images.json`, `evidence/backend-editor-roundtrip-http-before.json`, `evidence/backend-editor-avatar-http-before.json`, `evidence/backend-content-fields-js-before.json`, `evidence/backend-content-fields-cua.json`, `evidence/backend-content-fields-fixture-pre-template-language.json`, `evidence/backend-content-fields-cua-current.json`, `evidence/backend-content-fields-cua-cleanup.json`, `evidence/backend-content-fields-service.json`, `evidence/admin-ui-20261103-observations.json`, `evidence/admin-ui-root-cleanup-final.json`, `evidence/admin-ui-root-cleanup-public-audit.json`, `evidence/admin-ui-content-fields-b021103ab12f-final-report.json`, `evidence/admin-ui-b02-fields-saved-dom.json`, `evidence/admin-ui-b02-fields-empty-dom.json`, `evidence/backend-content-image-captions.json`, `evidence/b02-language-dependencies-http.json`, `evidence/b02-language-dependencies-js.json`, `evidence/backend-editor-attachment-picker-freeze-manifest-v1.json`, `evidence/backend-editor-attachment-picker-freeze-manifest-v2.json`, `evidence/backend-editor-attachment-picker-freeze-manifest-v3.json`, `evidence/backend-editor-attachment-picker-freeze-manifest-v4.json`, `evidence/backend-editor-attachment-picker-freeze-manifest-v5.json`, `evidence/backend-editor-attachment-picker-js-toast-final.json`, `evidence/backend-editor-attachment-picker-independent-review-v5.json`, `evidence/backend-single-toast-adapter-final.json`, `evidence/richtext-attachment-ui-a74f9c21-observations.json`, `evidence/richtext-attachment-ui-a74f9c21-persistence-final.json`, `evidence/richtext-attachment-ui-a74f9c21-http-final.json`, `evidence/richtext-attachment-ui-a74f9c21-http-scope-corrected.json`, `evidence/richtext-attachment-ui-a74f9c21-browser-dimensions.json`, `evidence/richtext-attachment-ui-a74f9c21-single-popup-before.json`, `evidence/richtext-attachment-ui-a74f9c21-cleanup-audit-first.json`, `evidence/richtext-attachment-ui-a74f9c21-cleanup-audit.json`, `evidence/richtext-attachment-ui-a74f9c21-final.json`, `evidence/backend-single-dynamic-fields-freeze-manifest-v1.json`, `evidence/backend-field-options-freeze-manifest-v1.json`, `evidence/backend-field-options-freeze-manifest-v2.json`, `evidence/backend-field-options-freeze-manifest-v3.json`, `evidence/backend-field-options-independent-freeze-v3.json`, `evidence/backend-field-defaults-independent-original-matrix-v1.json`, `evidence/backend-field-options-independent-matrix-before-v3.json`, `evidence/backend-field-options-independent-views-v2.json`, `evidence/backend-field-options-independent-views-v3.json`, `evidence/backend-field-empty-defaults-adapter-first.json`, `evidence/backend-field-empty-defaults-adapter-second.json`, `evidence/backend-datetime-fields-freeze-manifest-v1.json`, `evidence/backend-datetime-fields-independent-review-v1.json`, `evidence/single-dynamic-fields-4ec2b7a9-options-before-observed.json`, `evidence/single-dynamic-fields-4ec2b7a9-observations.json`, `evidence/single-dynamic-fields-4ec2b7a9-ui-active-final.json`, `evidence/single-dynamic-fields-4ec2b7a9-ui-active-journal.json`, `evidence/single-dynamic-fields-4ec2b7a9-ui-cleaned-final.json`, `evidence/single-dynamic-fields-4ec2b7a9-ui-cleaned-journal.json`, `evidence/single-dynamic-fields-4ec2b7a9-initial-v1-differential.json`, `evidence/single-dynamic-fields-4ec2b7a9-initial-v1-input-preservation.json`, `evidence/single-dynamic-fields-4ec2b7a9-uploaded-v1-differential.json`, `evidence/single-dynamic-fields-4ec2b7a9-cleanup-audit.json`, `evidence/single-dynamic-fields-ui-cleanup-harness-independent-review-v2.json`, `evidence/asset-inventory-post-single-fields-v3-file-audit.json`, `evidence/database-post-single-fields-v3-dry-run.json`, `evidence/single-page-load-guard-before-adapter-v1.json`, `evidence/single-page-load-guard-current-final-v1.json`, `evidence/single-page-load-guard-service-first.json`, `evidence/single-page-load-guard-ff21ca73-ui-final-v2.json`, `evidence/single-page-load-guard-layout-script-bridge-v1.json`, `evidence/single-page-load-guard-layout-independent-review-v2.json`, `evidence/single-page-load-guard-ui-audit-height-history.json`, `evidence/single-page-load-guard-final--manifest.json`, `evidence/backend-single-dynamic-fields-load-guard-v4-final.json`, `evidence/backend-datetime-fields-load-guard-v4-final.json`, `evidence/backend-field-empty-defaults-load-guard-v4.json`, `evidence/single-page-load-guard-options-matrix-reuse-v1.json`, `evidence/asset-database-post-load-guard-v1-audit.json`, `evidence/database-post-load-guard-v1-dry-run.json`, `evidence/single-page-engine-2a86d4e1-ui-final-v1.json`, `evidence/single-page-engine-2a86d4e1-independent-review-v1.json`, `evidence/single-page-engine-2a86d4e1-observations-final.json`, `evidence/single-page-engine-2a86d4e1-save-toast-observation-history.json`, `evidence/single-page-engine-2a86d4e1-cleanup-audit.json`, `evidence/asset-database-post-single-page-engine-v1-audit.json`, `evidence/database-post-single-page-engine-v1-dry-run.json`

Limits:

- 原v5源码freeze的pending UI句子记录其冻结时刻，后续root UI及cleanup才证明上述有限流程通过。原before、原http错误scope和各harness失败不改写。
- 旧permission Account错误的历史响应体未采集，contentSHA query后的新console0不证明旧缓存机制。既有vendor image error handler会丢原msg的审阅边界未修改，附件helper错误分支另验。
- 当前options v3替代早期view freeze；v1/v2与date-v1历史pending-UI句子保持原时刻，由新single实际UI/cleanup补证。
- 实际UI只验本single五字段fixture；insert/update options/date三view路径为源码/纯DOM适配证据，不能推断同等新实际浏览器流程；跨TZ/DST和末项清空持久化另用adapter/回滚事务。
- 最终footer背景right1320超过1280viewport，按钮/status可见；不声称整个footer水平包含。b3功能freeze与9f截图为历史，其CSS证据来自e107及ff-v2历史阶段，当前f2b3脚本修改另列待验，不能借此宣布实际UI通过。

Verified finite scope: 资源/匿名授权21、认证multipart/两轮PC-mobile正文/附件及头像URL41、严格PHP8.4图片15通过。两个before报告为明确失败历史，不计成功。所有归属上传与DB夹具最终清零。 本轮自定义checkbox/imgs/files真实页面函数在CUA合成DOM18/18，真实ContentService/ArchiveController事务7/7；删除最后媒体显式提交空值，保留图集标题及附件路径。CUA未登录后台、未操作真实上传弹窗/编辑器；旧source-hash批次与最终current批次分开，所属两public夹具目录清零。 后续独立真实浏览器已完成正文图片上传、custom checkbox/imgs/files保存清空重开；31组总报告跨多模块，不作为31自动测试或B02独立case。这是早期阶段；后续实际attachment picker成功与单页图文/提示修复见本任务新增分层。 最终v5与独立review保留sourceSHA，真实root CUA和UI/DB/HTTP/cleanup分层；root执行清理写操作，审计与verifier本身不写DB。 新增single五字段实际三save/reopen及active54/6GET、cleanup16/8GET404；初始55/final56为独立native只读差分层。三view options/date adapter与旧B02实测保持分层，不能相加。 后续f2b3单select真实UI7e3c9a16为38检查，含一次实际修改保存、双端重开/原模板正文、19资产GET和6清理GET；与旧适配矩阵/历史UI分开，不累加兼容率。

Target: archive editors + single model6 addon fields + shared options/datetime helpers

Pass criterion: f2b3单select的error/retry-loading禁用、ready交互、实际正文与选择值保存/toast/8787重开及双端原模板正文有有限真实UI证据；loading键盘、原显式disabled select和其它模型/角色/故障/主题边界仍开放，不据此关闭S2。

### B03 [P1] closed_with_evidence

Owner: admin

Tags: 

- 多选文档批量移动
- 软删除→回收站→恢复
- 永久删除后附表/附件记录/TAG计数一致；错误归属整体回滚
- Final HTTP25 PASS: move PC/mobile body, recycle404, restore independent mobile body, purge TAG counter and download_file attachment metadata; fixtures removed.

Evidence: `evidence/backend-content-lifecycle-http.json`

Target: archive/recycle APIs + new transaction and HTTP fixture

Pass criterion: 逐表断言剩余IDs与计数，不删除站点现有内容。

### B04 [P1] closed_with_evidence

Owner: admin

Tags: 

- 字段类型收窄含不兼容数据
- 主动注入ALTER失败
- 备份恢复与schema刷新补偿
- Services21/HTTP12 PASS: actual narrowing/index ALTER failure, DROP metadata fault with column order/index/bytes/metadata restoration; prefix-index restoration after narrowing failure; HTTP rename overflow compensation and subsequent save/read.

Evidence: `evidence/backend-field-failures.json`, `evidence/backend-field-failures-http.json`

Limits:

- Very large tables and concurrent DDL are not covered by this finite injected-failure batch.

Target: channelfield service failure injection; existing successful rename tests retained

Pass criterion: 数据字节、字段元数据与下一次保存恢复一致；不把成功rename视作故障补偿通过。

### B05 [P1] closed_with_evidence

Owner: admin

Tags: 

- 受限角色读取Smsconfig/Smstemplate/Smslog
- 拒绝设置/模板写入和日志篡改
- 匿名拒绝；授权角色成功且密钥/验证码不显示
- Citysite restricted-role read/write permissions after newly added module
- Reader/writer role and anonymous actual HTTP42 PASS, captcha login, SMS config secrets/log masking, City read/write, immutable SMS logs, fixture cleanup.

Evidence: `evidence/backend-module-permissions-http.json`

Target: extend scripts/testing/verify_admin_permissions.py

Pass criterion: 真实登录角色HTTP验证；34服务与25 SMS HTTP主流程已完成不重复列待验。

### B06 [P1] closed_with_evidence

Owner: admin

Tags: 

- Citysite backend three-level tree add/move/disable/delete-reference protection
- Language/city mutually exclusive settings without silently disabling languages
- Backend updates reflected on PC/mobile routes across 72 HTTP requests and two workers

Target: evidence/backend-citysite-services.json (17), evidence/backend-citysite-http.json (22); city-context 18 + city-context-http 18

Pass criterion: Passed and fixtures restored; role-specific checks remain tracked separately under B05.

### B07 [P1] finite_service_http_browser_guard_recorded_browser_overwrite_pending

Owner: admin

Tags: 

- 原 Language/sync is_frm=1 与 ArchivesLogic::batch_copy_all 的目标语言正文覆盖契约：默认源 cn；重建非回收站非单页文档，仅覆写已绑定单页 PC/mobile 正文。
- 七种企业非单页模型及草稿、栏目和产品参数绑定、专题/joinaid/TAG/附件重映射，保留栏目/配置/广告/表单/回收文档和会员投稿。
- 确认、POST、同源、匿名/受限角色、中文/默认站保护，引用和缺映射预检、删除后故障回滚，增量同步复用新活跃文档并保留第三语言翻译。
- 服务50/50、真实HTTP64/64通过；原版PC/mobile文章/单页/招聘模板和导航可读，24次并发页面请求观察两个worker。39类所属数据库残留均0，HTTP原字段仅统计源测试模板；补充清理审计确认三个HTTP批次的源/编译模板、journal、语言及测试角色/账户均0。
- 数据库 canonical 绑定保持唯一并保留回收历史；language 标签仍返回语言首页，不改变为文档翻译切换。
- 实际浏览器选择源/目标语言，未勾确认被原生校验阻止并取消；随后显式授权CLI覆盖另有证据，8次原文章/单页PC-mobile读取与清理。

Remaining explicit branches:

- 实际浏览器成功提交不可逆语言覆盖；确认取消与CLI覆盖分层，不互相替代。

Evidence: `evidence/backend-language-reset-service.json`, `evidence/backend-language-reset-http.json`, `evidence/backend-language-reset-service-before.json`, `evidence/backend-language-reset-service-before-incremental-member.json`, `evidence/backend-language-reset-http-before.json`, `evidence/backend-language-reset-http-before-worker-observation.json`, `evidence/backend-language-reset-cleanup-audit.json`, `evidence/admin-ui-20261103-observations.json`, `evidence/admin-ui-root-cleanup-final.json`, `evidence/admin-ui-root-cleanup-public-audit.json`, `evidence/admin-visual-d7a3c960-b07-service-overwrite.json`, `evidence/admin-visual-d7a3c960-b07-public-after.json`, `evidence/admin-visual-d7a3c960-b07-cleanup.json`

Limits:

- 原版固定cn源；显式选择其它有效源语言是扩展。企业范围过滤、现存商城/会员引用拒绝、InnoDB与默认站保护是明确适配边界。
- 四份before仅保留失败历史：两份夹具不满足前提、一份增量复制会员文档的真实缺陷、一份顺序请求只观察一个worker；不计成功。
- 官方变量包B08已有本地变量实际UI，真实官方外网仍未验；原版流程无文章正文机器翻译。全部官方主题与属性组合未验收。

Verified finite scope: 服务50/HTTP64与实际浏览器源选择、确认阻止/取消分层；另外授权CLI覆盖+8实际公开读取不计成功浏览器覆盖。

Target: scripts/testing/verify_language_reset.php + scripts/testing/verify_language_reset_http.py

Pass criterion: 有限服务/API、原模板读取及实际浏览器保护交互已记录；浏览器成功覆盖保留。

### B08 [P1] finite_service_http_loopback_local_browser_recorded_official_external_pending

Owner: admin

Tags: 

- 本地变量组CRUD、名称与重复/保留名校验、各语言值/部分更新保留；官方变量值可编辑、名和删除受保护。
- single/list/check离线导入及固定HTTPS客户端；按变量名合并、明确中文回退、严格集合/游标/重复/总量校验；空包不推进游标，失败事务回滚且不发布缓存。
- 服务66/66、后台到前台HTTP95/95，实际loopback收到10请求，菜单missing-only 167→168且旧行未变；HTTP观察两个worker，所有所属DB/journal/账户/角色/源和编译模板清理为0。
- 批量界面使用服务返回的实际batch_languages列序；HTTP覆盖权限、POST/Origin/确认、离线导入及PC/mobile lang实际读取。
- 实际浏览器新增/查询本地变量，单改A语言后切B和cn确认保值；独立48次公开lang读取观察两个worker，所有owned行模板清零。

Remaining explicit branches:

- 真实官方服务外网请求与可用性认证；real_official_service_verified=false。
- 官方远程同步表单的成功浏览器提交；当前实际浏览器范围为本地变量新增/编辑/查询。

Evidence: `evidence/backend-language-pack-service.json`, `evidence/backend-language-pack-transport.json`, `evidence/backend-language-pack-menu.json`, `evidence/backend-language-pack-http.json`, `evidence/admin-ui-20261103-observations.json`, `evidence/admin-ui-root-cleanup-final.json`, `evidence/admin-ui-root-cleanup-public-audit.json`, `evidence/admin-visual-d7a3c960-b08-values-http.json`, `evidence/admin-visual-d7a3c960-b08-ui-saved-inspect.json`, `evidence/admin-visual-d7a3c960-b08-cleanup.json`

Limits:

- 远程HTTP动作只测试缺确认拒绝，无成功外网请求；成功协议/传输证据来自服务与显式本机loopback，UI HTML结构不等于浏览器交互。
- 签名数学匹配原UpgradeLogic，但公开domain归一化和空rawip字段不宣称完整原wire payload逐字一致；原版无文章正文机器翻译。

Target: scripts/testing/verify_language_pack.php + verify_language_pack_transport.py + verify_language_pack_http.py

Pass criterion: 有限本地业务、loopback和本地变量实际UI通过；真实官方外网及远程同步成功UI独立待验。

### B09 [P1] finite_service_HTTP_actual_browser_and_normal_role_HTTP_recorded_broader_variants_pending

Owner: admin

Tags: 

- 实现原Filemanager目录、源码编辑/新建、栅格替图及菜单；空activepath选template根。
- 服务87、真实原editFile14、权限37、发布补偿6、认证root HTTP160通过；初始/include同尺寸mtime/main同尺寸mtime各观察同2worker；所属DB/config/log/theme/journal/lock/compiler0，cn配置不变。
- 权限为真实规则及所属角色ID注入，非角色HTTP登录。独立动作权限、2MiB UTF8/10MiB栅格、containment/no symlink-hardlink、SHA并发/不覆盖、MIME/同扩展名和SVG不支持均为适配边界。
- 未发现可工作原企业ZIP安装action；ZIP导入仅可选扩展。
- 实际浏览器源码编辑/新建、真实文件选择替图、切语言目录、栏目高级模板选择保存重开；owned文件SHA与持久化、清理独立核对。
- 新增独立受限账号正常验证码登录HTTP逐动作：全矩阵371检查/127真实HTTP、38/38 exact actions code0与持久化成功，读写权限拒绝code2和验证code1区分。矩阵含B09文件管理、B10变量、B11表单字段、B12FAQ、B13专题、B14参数及archive bypass、B15TAG；不是受限角色实际浏览器验收。
- 完成报告确认157原业务表/7802行全表hash稳定、auth基线保留、owned身份0、16owned日志移除、正常logout后info code401、私有目录/journal/theme无残留；原HTTP报告auth cleanup pending保留，由完成报告补证。

Remaining explicit branches:

- 所有source editor输入、语言/设备/模型/主题组合未穷尽；受限角色浏览器交互未验。

Evidence: `evidence/backend-template-install-source-audit.json`, `evidence/backend-template-picker-js-before.json`, `evidence/backend-template-picker-js.json`, `evidence/backend-template-picker-http.json`, `evidence/backend-template-filemanager-service.json`, `evidence/backend-template-filemanager-source.json`, `evidence/backend-template-filemanager-permissions.json`, `evidence/backend-template-filemanager-publication.json`, `evidence/backend-template-filemanager-menu.json`, `evidence/backend-template-filemanager-http.json`, `evidence/backend-template-filemanager-empty-activepath-service.json`, `evidence/backend-template-filemanager-empty-activepath-http.json`, `evidence/admin-ui-20261103-observations.json`, `evidence/admin-ui-root-cleanup-final.json`, `evidence/admin-ui-root-cleanup-public-audit.json`, `evidence/admin-visual-d7a3c960-b09-files-sha.json`, `evidence/admin-visual-d7a3c960-b09-category-template-guard.json`, `evidence/b09-arctype-language-http.json`, `evidence/b09-arctype-language-js.json`, `evidence/restricted-role-http-7c8a9d2e4f61-v1.json`, `evidence/restricted-role-completion-7c8a9d2e4f61-v1.json`

Target: original application/admin/controller/Filemanager.php + FilemanagerLogic; candidate plugin/admin

Pass criterion: 原有限服务/HTTP/实际浏览器记录及新增正常受限角色逐动作HTTP通过；剩余浏览器和输入组合见remaining_cases。

### B10 [P1] finite_service_HTTP_actual_browser_and_normal_role_HTTP_recorded_broader_variants_pending_browser_purge_pending

Owner: admin

Tags: 

- 原System::customvar_del按name回收所有语言config并保留值/metadata，RecycleBin列表/恢复/永久删除；候选原先物理删config_attribute及值且回收站仅栏目/文档，为真实缺口。
- 当前按同名各语言组回收/恢复，recycle主键config id、编辑主键attr_id；active列表隐藏墓碑、回收后拒编辑/同名重建，websiteconfig通用删除遇变量整批拒绝，普通config仍物理删除。
- 真实原PHP7抽取方法+memory adapter11；候选service52、认证root HTTP134通过，5阶段原global标签实际GET同2worker，七类所属数据/文件0，cn配置hash不变。
- 重复name/lang、metadata/分组冲突、普通配置混入和孤儿拒绝且保值；purge仅允许完整回收语言组，partial active组拒绝为明确保护适配边界。
- 实际后台变量metadata/value编辑、新建、同名语言组回收与恢复，ID/旧值保留；没有成功浏览器永久purge。
- 新增独立受限账号正常验证码登录HTTP逐动作：全矩阵371检查/127真实HTTP、38/38 exact actions code0与持久化成功，读写权限拒绝code2和验证code1区分。矩阵含B09文件管理、B10变量、B11表单字段、B12FAQ、B13专题、B14参数及archive bypass、B15TAG；不是受限角色实际浏览器验收。
- 完成报告确认157原业务表/7802行全表hash稳定、auth基线保留、owned身份0、16owned日志移除、正常logout后info code401、私有目录/journal/theme无残留；原HTTP报告auth cleanup pending保留，由完成报告补证。

Remaining explicit branches:

- 实际浏览器永久purge；当前仅编辑/回收/恢复与history。
- 受限角色浏览器交互与所有变量/语言组合未验。

Evidence: `evidence/backend-custom-variable-recycle-source.json`, `evidence/backend-custom-variable-recycle-service-before.json`, `evidence/backend-custom-variable-recycle-config-delete-before.json`, `evidence/backend-custom-variable-recycle-service-initial-after.json`, `evidence/backend-custom-variable-recycle-service.json`, `evidence/backend-custom-variable-recycle-service-final.json`, `evidence/backend-custom-variable-recycle-service-frozen.json`, `evidence/backend-custom-variable-recycle-http.json`, `evidence/backend-custom-variable-recycle-http-final.json`, `evidence/backend-custom-variable-recycle-http-frozen.json`, `evidence/admin-ui-20261103-observations.json`, `evidence/admin-ui-root-cleanup-final.json`, `evidence/admin-ui-root-cleanup-public-audit.json`, `evidence/admin-ui-recycle-d01103b10b11-final-history-report.json`, `evidence/restricted-role-http-7c8a9d2e4f61-v1.json`, `evidence/restricted-role-completion-7c8a9d2e4f61-v1.json`

Limits:

- source11不是原应用完整DB/HTTP；source/service/HTTP及早期after5/45/50、HTTP131/133为不同层次/重叠批次，不累计独立case。
- 历史HTTP复用授权root API session，无新角色登录；后续实际浏览器edit/recycle/restore另有独立报告。沿用既有Recycle权限，不冒充新增动作角色HTTP验证。
- 完整回收组purge限制是保护适配边界；普通网站config删除与自定义变量回收明确分开。

Target: app/cms/service/CustomVariableService.php + EnterpriseDataService + plugin/admin RecycleController/view

Pass criterion: 原有限服务/HTTP/实际浏览器记录及新增正常受限角色逐动作HTTP通过；剩余浏览器和输入组合见remaining_cases。

### B11 [P1] finite_service_HTTP_actual_browser_and_normal_role_HTTP_recorded_broader_variants_pending_browser_purge_pending

Owner: admin

Tags: 

- 原真实Guestbook/Form/Recycle方法memory oracle22业务case/220观察，未启动原DB/HTTP。
- FormFieldService保持field IDs/options/metadata/history，按form_type区分Guestbook/自由表单、语言组/父级/模型归属、事务失败回滚和精确binding清理；恢复is_showlist=0。
- 候选最终service133/认证root HTTP134，前台PC-mobile表单多阶段与同worker/cache、所属9类DB和精确模板清理单列。
- 真实浏览器Guestbook/freeform字段编辑、回收、恢复、新建与history保留，原四个旧值独立核验；无浏览器成功永久purge。
- 新增独立受限账号正常验证码登录HTTP逐动作：全矩阵371检查/127真实HTTP、38/38 exact actions code0与持久化成功，读写权限拒绝code2和验证code1区分。矩阵含B09文件管理、B10变量、B11表单字段、B12FAQ、B13专题、B14参数及archive bypass、B15TAG；不是受限角色实际浏览器验收。
- 完成报告确认157原业务表/7802行全表hash稳定、auth基线保留、owned身份0、16owned日志移除、正常logout后info code401、私有目录/journal/theme无残留；原HTTP报告auth cleanup pending保留，由完成报告补证。

Remaining explicit branches:

- 实际后台浏览器永久purge。
- 受限角色浏览器交互与所有字段/语言/父级组合未验。

Evidence: `evidence/admin-ui-20261103-observations.json`, `evidence/admin-ui-root-cleanup-final.json`, `evidence/admin-ui-root-cleanup-public-audit.json`, `evidence/backend-form-field-recycle-source.json`, `evidence/backend-form-field-recycle-service-frozen.json`, `evidence/backend-form-field-recycle-http-frozen.json`, `evidence/admin-ui-recycle-d01103b10b11-final-history-report.json`, `evidence/restricted-role-http-7c8a9d2e4f61-v1.json`, `evidence/restricted-role-completion-7c8a9d2e4f61-v1.json`

Limits:

- 原22case/220观察不是220独立业务；抽取方法与memory adapter非原完整DB/HTTP。
- 早期source-only与候选before保留历史；service/HTTP/UI层互不替代，不累计独立case。
- 跨表单/陈旧绑定/缺父级损坏拒绝为明确保护适配；实际受限角色HTTP登录尚未单独验证。

Target: app/cms/service/FormFieldService.php + EnterpriseDataService + RecycleController/views + original form source contract

Pass criterion: 原有限服务/HTTP/实际浏览器记录及新增正常受限角色逐动作HTTP通过；剩余浏览器和输入组合见remaining_cases。

### B12 [P1] finite_service_HTTP_actual_browser_and_normal_role_HTTP_recorded_broader_variants_pending

Owner: admin

Tags: 

- FAQ专页管理原group/question IDs、语言、HTML、排序、status、事务与归属。
- 原PHP7抽取方法memory23；候选实际service/compiler/TagFaq41；root/anonymous HTTP22、实际DB Role/Rule26，分层不加总。
- 7组真实浏览器新增/编辑/HTML sandbox预览/排序/status/重开/取消；default TagFaq启用/禁用64公开PC-mobile请求同两个worker。
- group/questions/category/templates/compiler精确清理全0、保护数据/原copied模板SHA不变。
- 新增独立受限账号正常验证码登录HTTP逐动作：全矩阵371检查/127真实HTTP、38/38 exact actions code0与持久化成功，读写权限拒绝code2和验证code1区分。矩阵含B09文件管理、B10变量、B11表单字段、B12FAQ、B13专题、B14参数及archive bypass、B15TAG；不是受限角色实际浏览器验收。
- 完成报告确认157原业务表/7802行全表hash稳定、auth基线保留、owned身份0、16owned日志移除、正常logout后info code401、私有目录/journal/theme无残留；原HTTP报告auth cleanup pending保留，由完成报告补证。

Remaining explicit branches:

- 受限角色浏览器交互与所有输入组合未穷尽。

Evidence: `evidence/backend-faq-final-manifest.json`, `evidence/backend-faq-http.json`, `evidence/backend-faq-permissions.json`, `evidence/original-faq-source-contract-first.json`, `evidence/faq-service-tag-contract-first-complete-attempt.json`, `evidence/faq-service-tag-fixture-prerequisite-history.json`, `evidence/admin-ui-faq-20261103-observations.json`, `evidence/admin-ui-faq-d7a3c960-persistence.json`, `evidence/admin-ui-faq-d7a3c960-http-enabled.json`, `evidence/admin-ui-faq-d7a3c960-http-disabled.json`, `evidence/admin-ui-faq-d7a3c960-cleanup.json`, `evidence/admin-ui-faq-d7a3c960-http-cleanup.json`, `evidence/restricted-role-http-7c8a9d2e4f61-v1.json`, `evidence/restricted-role-completion-7c8a9d2e4f61-v1.json`

Limits:

- 原方法的双空新增残留组、同秒affected=0跳过问答、缺跨组/语言归属校验列为明确保护适配，不要求复制数据损坏。
- 准备工具或实际Role/Rule事务检查不等于角色账号HTTP登录；后台总体仍未完成。
- custom where遵循原override分支，不能声称所有自定义查询自动套用默认status过滤。

Target: app/cms/service/FaqService.php + plugin/admin FaqController/cms/faq.html + tests/compat FAQ source/tag contracts

Pass criterion: 原有限服务/HTTP/实际浏览器记录及新增正常受限角色逐动作HTTP通过；剩余浏览器和输入组合见remaining_cases。

### B13 [P1] finite_service_HTTP_actual_browser_and_normal_role_HTTP_recorded_broader_variants_pending

Owner: admin

Tags: specnode, specnodelist, pagespecnode

- source gap/source audit为实施前快照；当前专题手选/自动节点和回显、code/title/语言/排序/typeid/keywords/row已实现。
- 原PHP7 memory32；候选实际service/compiler/specnode/specnodelist/pagespecnode57，root/anonymous HTTP29、实际DB Role/Rule32。
- 8组实际浏览器手选picker和paired sorts、自动条件、标题/code/顺序、停用/重开及公开分页。补充160实际公开请求：initial32、manual32、auto与title/order合并状态32、同状态repeat32、disabled32。
- 专题回收/恢复保留节点、永久清理才删；原显式aid读取不新增父级可见性门槛，controller404与editor拒绝独立验证。
- exact 3archives/2categories/2nodes/body/templates/compiler/locks全0，原行/模板hash不变，6旧URL404。
- 新增独立受限账号正常验证码登录HTTP逐动作：全矩阵371检查/127真实HTTP、38/38 exact actions code0与持久化成功，读写权限拒绝code2和验证code1区分。矩阵含B09文件管理、B10变量、B11表单字段、B12FAQ、B13专题、B14参数及archive bypass、B15TAG；不是受限角色实际浏览器验收。
- 完成报告确认157原业务表/7802行全表hash稳定、auth基线保留、owned身份0、16owned日志移除、正常logout后info code401、私有目录/journal/theme无残留；原HTTP报告auth cleanup pending保留，由完成报告补证。

Remaining explicit branches:

- 受限角色浏览器交互、全部自动条件/主题/输入组合未穷尽。

Evidence: `evidence/admin-ui-specialnode-20261103-observations.json`, `evidence/admin-ui-special-b13d7a3c960-http-sort-saved.json`, `evidence/admin-ui-special-b13d7a3c960-manual-saved-inspect.json`, `evidence/admin-ui-special-b13d7a3c960-persistence-first.json`, `evidence/admin-ui-special-b13d7a3c960-setup.json`, `evidence/admin-ui-special-b13d7a3c960-cleanup-public.json`, `evidence/admin-ui-special-b13d7a3c960-sort-saved-inspect.json`, `evidence/admin-ui-special-b13d7a3c960-auto-saved-inspect.json`, `evidence/admin-ui-special-b13d7a3c960-http-manual-saved.json`, `evidence/admin-ui-special-b13d7a3c960-cleanup.json`, `evidence/admin-ui-special-b13d7a3c960-http-verifier-history.json`, `evidence/admin-ui-special-b13d7a3c960-http-auto-saved.json`, `evidence/admin-ui-special-b13d7a3c960-http-initial.json`, `evidence/admin-ui-special-b13d7a3c960-initial-inspect.json`, `evidence/admin-ui-special-b13d7a3c960-persistence.json`, `evidence/admin-ui-special-b13d7a3c960-disabled-saved-inspect.json`, `evidence/admin-ui-special-b13d7a3c960-http-disabled-saved.json`, `evidence/backend-special-node-source-gap.json`, `evidence/special-node-backend-source-audit-final.json`, `evidence/original-special-node-source-contract-final.json`, `evidence/backend-specialnode-final-manifest.json`, `evidence/special-node-service-tag-contract-final.json`, `evidence/special-node-service-tag-fixture-history.json`, `evidence/backend-specialnode-http.json`, `evidence/backend-specialnode-permissions.json`, `evidence/restricted-role-http-7c8a9d2e4f61-v1.json`, `evidence/restricted-role-completion-7c8a9d2e4f61-v1.json`

Limits:

- 原memory oracle不启动原应用/DB/HTTP；所有证据分层不累计独立case。
- auto-saved快照已含随后title/order编辑，auto/sort共64是同状态重复读取；UI每步单独观察。manual首报告16是假失败：原首页URL省page=1，保留原结果并零新请求重判。
- 只有manual到combined状态共享同worker，disable在重启后；原显式aid标签逻辑保留。

Target: original Special/SpecialNode vs candidate Archive forms + ContentService

Pass criterion: 原有限服务/HTTP/实际浏览器记录及新增正常受限角色逐动作HTTP通过；剩余浏览器和输入组合见remaining_cases。

### E01 [P2] external_environment

Owner: deployment

Tags: 

- 实际部署SMTP SSL/账号投递
- 实际阿里/腾讯短信账号投递

Target: deployment credentials and explicit external delivery authorization

Pass criterion: 本机网关签名/状态/SMTP传输已验证；真实提供商投递依赖部署环境，不作为未实现短信的证据。

### E02 [P2] partial_resource_recovery

Owner: root

Tags: 

- 恢复源缺DINCondensedC.svg/Owl播放图/PC MagicZoom光标
- 记录原正文两张远程HTTPS404图和MagicZoom隐藏src=none探测

Remaining explicit branches:

- 历史MagicZoom 2008光标原字节仍未知；四个旧URL当前使用同vendor Magic360 zoom-out CUR明确兼容替代，未验真实浏览器光标外观。
- 派生SVG轮廓/宽度/字距及HTTP字节验证不等于缺失原SVG字节恢复或浏览器字体视觉验收。
- 两张原正文远程HTTPS图仍404；真实手机触摸仍未验。当前同一127 origin版本化后DOM无none探测，历史缓存成因没有网络响应体证据。

Evidence: `evidence/asset-recovery.json`, `evidence/asset-recovery-write.json`, `evidence/asset-recovery-repeat.json`, `evidence/asset-recovery-home-pc.json`, `evidence/asset-recovery-home-mobile.json`, `evidence/corpus-assets-after-recovery.json`, `evidence/asset-inventory-after-recovery.json`, `evidence/asset-recovery-source-research.json`, `evidence/magiczoom-assets.json`, `evidence/magiczoom-browser.json`, `evidence/magiczoom-browser-before-selector.json`, `evidence/template-pages-post-compat.json`, `evidence/template-page-assets-pc-post-compat.json`, `evidence/template-page-assets-mobile-post-compat.json`, `evidence/asset-cursor-write.json`, `evidence/asset-cursor-repeat.json`, `evidence/asset-cursor-verification-before-mime.json`, `evidence/asset-cursor-verification.json`, `evidence/asset-cursor-http.json`, `evidence/corpus-assets-after-cursor.json`, `evidence/template-pages-post-runtime.json`, `evidence/template-page-assets-pc-post-runtime.json`, `evidence/template-page-assets-mobile-post-runtime.json`, `evidence/asset-inventory-post-runtime.json`, `evidence/mobile-magiczoom-browser-current.json`, `evidence/asset-inventory-post-routing.json`, `evidence/template-pages-post-ast-city-stages.json`, `evidence/template-page-assets-pc-post-ast-city-stages.json`, `evidence/template-page-assets-mobile-post-ast-city-stages.json`, `evidence/mobile-gallery-css-contract-browser.json`, `evidence/compat-script-url-gallery-controls-final.json`, `evidence/empty-product-gallery-browser-final.json`, `evidence/empty-product-gallery-http-final.json`, `evidence/empty-product-gallery-coverda65bf108ca2-cleanup.json`, `evidence/empty-product-gallery-http-cleanup-final.json`, `evidence/compat-script-url-html-context-final.json`, `evidence/template-pages-post-comparison-v12-final.json`, `evidence/template-page-assets-pc-post-comparison-v12-final.json`, `evidence/template-page-assets-mobile-post-comparison-v12-final.json`, `evidence/template-pages-post-comparison-v12-browser-first-observed.json`, `evidence/template-pages-post-comparison-v12-browser-final.json`, `evidence/template-pages-post-comparison-v12-browser-unique-final.json`, `evidence/asset-inventory-post-comparison-v12.json`

Limits:

- 旧mobile7仅inline visibility/left，不足以推断实际可见/viewport外；最新真实source/CUA12证实display:none、无rect/offset、390无横向溢出、14图片健康/缩略切换还原。原非空图集无MagicThumb，不适用lightbox关闭。真实触摸仍未验；空图集桌面/390视口MagicThumb现另有14项实际观察；截图只在工具观察，未持久化导出。

Verified finite scope: 原8份SVG/Owl补充资源及历史报告不改；新增4路径same-vendor CUR兼容替代，总补充12文件。25结构+12HTTP检查、21 GET/HEAD/Range/ETag/416通过；before真实MIME缺口由application/octet-stream修为image/x-icon。141旧URL重取141通过仅为重取；最终独立原包HTTP46/46、PC22页71资源和mobile22页72资源均0失败。原2134源文件逐字哈希全部一致。光标不是恢复2008原字节，远程2图仍缺失。 后续mobile-gallery CSS/browser12通过，纠正旧7项仅inline观察的可见性误判；不扩大为触摸/空图集lightbox验收。 空画廊实际PC/mobile打开与关闭14/14、响应边界36/36、双origin HTTP18/18及清理404四项通过；原mobile Close为0×0的缺陷由精确模板SHA限定的44×44补充CSS修复，复制模板与原JS物理字节不变。 后续HTML文本/引号属性边界收窄45/45，单独记录实施时序。 comparison v12及HTML脚本边界45冻结后最终重验：HTTP46/46、实际browser44unique/44通过（原52观察含8重复，首24中3个innerText验证器误判保留），PC22页72资源/mobile22页73资源均0失败。2134物理原资源逐字不变，owned fixture0、原模板写入0；不扩大为所有主题/完整交互。

Target: resource provenance/recovery decision + browser-corpus report

Pass criterion: 原模板及2134源资源字节不改，补充资源注明来源/派生/替代；当前请求通过不能关闭历史字节和真实浏览器/远程资源边界。

### E03 [P2] range_alias_php7_differential_and_http_passed_other_scope_pending

Owner: root

- 原equal/notequal及已列损坏语法的候选修复扩展，与可工作的eyou:in输出差异分开分类。
- 未提供的其它官方主题
- 官方变量包已完成B08有限服务66/HTTP95/loopback10验收；B07目标语言正文覆盖已完成有限服务/HTTP验收，二者浏览器仍待验，B08真实官方外网另待验。原版不存在文章正文机器翻译流程，不将其列为未实现需求
- Original likearticle name dataset produces ParseError through malformed array_slice PHP. Six candidate window/nesting/empty/dynamic-limit repairs remain explicit differences, never direct original matches.
- 阶段一初始审查的eyou:in可工作输出差异：{eyou:in name='value' value='a,b'}IN{/eyou:in}，value=b时原版空、候选IN；differential-syntax.json的syntax:range:alias为失败，不是ParseError。此为修复前历史；当前有限输入已修并有独立差分及实际HTTP验收。

Remaining explicit branches:

- 范围内 range 别名差异已修：291差分零差异、60比较控制、52 parseAttr契约；正常路由两入口/双端4次HTTP逐字匹配2次PHP7原compiler（6/6），详见 `../../runtime/compat/e03-range-alias-http-v1.json` 和 `../../runtime/compat/e03-range-alias-review-v1.json`。8行/2源模板/4编译文件已精确清理，3类原表逐行哈希及源码稳定。T17与更多输入保持开放。
- 其它已知修复扩展、禁用会员/商城行为及未提供主题继续明确分类，不推导全官方模板逐字一致。

Evidence: `evidence/tag-related-branches-differential.json`, `evidence/tagarclist-http.json`, `evidence/backend-language-reset-service.json`, `evidence/backend-language-reset-http.json`, `evidence/backend-language-pack-service.json`, `evidence/backend-language-pack-transport.json`, `evidence/backend-language-pack-menu.json`, `evidence/backend-language-pack-http.json`, `evidence/differential-syntax.json`

Target: compatibility.json known_baseline_bugs; future theme corpus/feature implementation

Pass criterion: eyou:in真实输出差异解决并有对应原版差分后才能关闭此缺陷；当前218源模板按实际清单验收，未取得主题另列输入。其它明确修复扩展及范围边界不能计为原版逐字一致。

### B14 [P1] finite_service_HTTP_actual_browser_and_normal_role_HTTP_recorded_broader_variants_pending_browser_purge_pending

Owner: admin

Tags: attribute, attr, screening

- 普通企业展示产品旧定义/值及新组/共享/自定义参数维护、原IDs、归属、soft recycle/restore及原表读值已实现；shop前缀展示数据属于范围。
- 原精确PHP7方法memory33；候选实际service/compiler两标签四读法77；ContentService normalized alias/ownership/authorization49、readonly8，root/anonymous HTTP56、DB Role/Rule154。早期67与77重叠。
- 8组实际浏览器定义、HTML、组/公共/自定参数、产品保存重开/编辑/顺序、旧定义回收恢复和新定义停用。160独立HTTP=初始及四保存状态各32、PC/mobile原attribute auto/default/newattr与attr。
- 持久化18/18、精确fixture/模板/compiler/locks全0、13类保护表hash及原/copied模板hash不变，旧产品URL404。
- 多语言增量已恢复原主语言legacy自动全语种克隆/回收，共享restore/purge及nonmain guard；最早注册语言为main，disabled语言也计入。主语言group+inline初始定义仅共享时克隆，standalone modern保持本地。精确join/clone归属与允许本地移动、保留译文分开。
- 原精确方法memory32含4项source-only完整入口/callsite guard；当前service review27/root HTTP17，后续cascade service16/HTTP18、syntax13与仅hidden CSS7为分层增量；早期7/42/15/25及before历史保留，不相加。
- 删除preview列出精确组/全部实际子定义及value IDs/owner aids/count，fingerprint在持有写事务锁时重算；新增/移动子定义及新增/换归属值会拒绝旧preview。原language_attribute标题不在edit时急更新，先前提案废弃。
- 新增真实root浏览器25/25：disabled语言、legacy自动clone、译文保留、组和初始定义克隆、精确级联预览取消与保存重开。CSS hidden被grid覆盖的真实问题仅加显式hidden规则修复。后续精确cleanup六类0、九类原表hash不变。
- 新增独立受限账号正常验证码登录HTTP逐动作：全矩阵371检查/127真实HTTP、38/38 exact actions code0与持久化成功，读写权限拒绝code2和验证code1区分。矩阵含B09文件管理、B10变量、B11表单字段、B12FAQ、B13专题、B14参数及archive bypass、B15TAG；不是受限角色实际浏览器验收。
- 完成报告确认157原业务表/7802行全表hash稳定、auth基线保留、owned身份0、16owned日志移除、正常logout后info code401、私有目录/journal/theme无残留；原HTTP报告auth cleanup pending保留，由完成报告补证。

Remaining explicit branches:

- 受限角色浏览器交互、浏览器成功永久purge/清空自定参数、所有字段与主题组合。
- screening主要走custom fields，不能把本次attribute/attr保存读取等同全部筛选交互新验收。

Evidence: `evidence/admin-ui-product-parameter-b14d7a3c960-cleanup-public.json`, `evidence/admin-ui-product-parameter-b14d7a3c960-cleanup.json`, `evidence/admin-ui-product-parameter-b14d7a3c960-edited-saved-inspect.json`, `evidence/admin-ui-product-parameter-b14d7a3c960-http-edited-saved.json`, `evidence/admin-ui-product-parameter-b14d7a3c960-http-initial.json`, `evidence/admin-ui-product-parameter-b14d7a3c960-http-recycled-group-saved.json`, `evidence/admin-ui-product-parameter-b14d7a3c960-http-restored-disabled-saved.json`, `evidence/admin-ui-product-parameter-b14d7a3c960-http-values-saved.json`, `evidence/admin-ui-product-parameter-b14d7a3c960-initial-inspect.json`, `evidence/admin-ui-product-parameter-b14d7a3c960-persistence.json`, `evidence/admin-ui-product-parameter-b14d7a3c960-recycled-group-saved-inspect.json`, `evidence/admin-ui-product-parameter-b14d7a3c960-restored-disabled-saved-inspect.json`, `evidence/admin-ui-product-parameter-b14d7a3c960-setup.json`, `evidence/admin-ui-product-parameter-b14d7a3c960-values-saved-inspect.json`, `evidence/admin-ui-productparameter-20261103-observations.json`, `evidence/backend-product-parameter-source-gap.json`, `evidence/product-parameter-backend-source-audit-final.json`, `evidence/original-product-parameter-source-contract-first.json`, `evidence/backend-productparameter-final-manifest.json`, `evidence/product-parameter-service-tag-contract-final.json`, `evidence/product-parameter-content-boundary-v2-first.json`, `evidence/product-parameter-final-readonly-review-v2.json`, `evidence/backend-productparameter-http.json`, `evidence/backend-productparameter-permissions.json`, `evidence/product-parameter-multilanguage-backend-freeze-manifest-v1.json`, `evidence/product-parameter-multilanguage-backend-freeze-manifest-v2.json`, `evidence/original-product-parameter-language-memory-fifth.json`, `evidence/product-parameter-language-review-followup-second.json`, `evidence/product-parameter-multilanguage-http-review-followup-first.json`, `evidence/product-parameter-group-cascade-plan-first.json`, `evidence/product-parameter-group-cascade-http-first.json`, `evidence/product-parameter-multilanguage-syntax-cascade-final.json`, `evidence/product-parameter-hidden-style-syntax-first.json`, `evidence/product-parameter-multilanguage-ui-a862c14f9d03-final.json`, `evidence/product-parameter-multilanguage-ui-a862c14f9d03-cleanup.json`, `evidence/restricted-role-http-7c8a9d2e4f61-v1.json`, `evidence/restricted-role-completion-7c8a9d2e4f61-v1.json`

Limits:

- source audit的zero-preserve为已放弃提案；最终遵原empty()/字符串0跳过、sort0保留。旧explicit source_id/current-language-only生命周期差异现由多语言增量取代；歧义/孤儿metadata拒绝。新语言LanguageService历史prefix/list_id0差异未全面修改。
- newattr原行为忽略group.status及definition.is_del而应用definition.status；停用值仍保留。custom清空与永久purge只打开确认并取消，未成功删除。
- initial/values/edit共享28734/28735，recycle/restore用重启后的58667/58668；不声称全阶段同worker。源/服务/HTTP/UI层不累计独立case，不含会员/交易。
- 原memory adapter不启动原完整应用/DB/HTTP；4项source-only guard与实际原方法case分列。controller15采用roleIds override+真实Role/Rule，不能替代受限角色HTTP登录。v1/v2 freeze与各阶段report保留原时间/SHA；v2当时active UI fixture由后续cleanup清零。

Target: original Product/ProductAttribute/ProductLogic vs candidate product forms + ContentService

Pass criterion: 原有限服务/HTTP/实际浏览器记录及新增正常受限角色逐动作HTTP通过；剩余浏览器和输入组合见remaining_cases。

### B15 [P1] finite_service_HTTP_actual_browser_and_normal_role_HTTP_recorded_broader_variants_pending

Owner: admin

Tags: tag

- TAG rename原先只更新tagindex，taglist旧名致当前文章getall=0与editor陈旧；现在同事务按精确tid+lang同步，ID/关系/counters/add_time不变，SEO部分更新保名。
- 原PHP7 extracted edit/TagTag memory7，candidate before2/7→final7；actual service/tag/editor38、root/anonymous HTTP25；后续锁序两进程两连接barrier10及当前delete outer rollback9单列。
- 5组实际浏览器rename、重开、仅操作SEO保存及article editor读取新名。初始/rename/SEO各32公开PC-mobile getall0/1实际请求，同75219/75227、持久化14/14。
- 精确TAG/关联/article/category/body/templates/compiler/locks全0，9类原行与原/copied模板SHA不变，2旧URL404。
- 新增独立受限账号正常验证码登录HTTP逐动作：全矩阵371检查/127真实HTTP、38/38 exact actions code0与持久化成功，读写权限拒绝code2和验证code1区分。矩阵含B09文件管理、B10变量、B11表单字段、B12FAQ、B13专题、B14参数及archive bypass、B15TAG；不是受限角色实际浏览器验收。
- 完成报告确认157原业务表/7802行全表hash稳定、auth基线保留、owned身份0、16owned日志移除、正常logout后info code401、私有目录/journal/theme无残留；原HTTP报告auth cleanup pending保留，由完成报告补证。

Remaining explicit branches:

- 受限角色浏览器交互及所有输入/主题组合未穷尽。

Evidence: `evidence/backend-tag-rename-final-manifest.json`, `evidence/backend-tag-rename-http-first.json`, `evidence/backend-tag-rename-lock-order-final.json`, `evidence/tag-rename-admin-6d969ab643ad.json`, `evidence/tag-rename-original-memory-final-v2.json`, `evidence/tag-rename-candidate-memory-before.json`, `evidence/tag-rename-candidate-memory-final-v2.json`, `evidence/tag-rename-service-tag-editor-first.json`, `evidence/tag-rename-evidence-history-final.json`, `evidence/admin-ui-tag-b15d7a3c960-cleanup-public.json`, `evidence/admin-ui-tag-b15d7a3c960-cleanup.json`, `evidence/admin-ui-tag-b15d7a3c960-http-initial.json`, `evidence/admin-ui-tag-b15d7a3c960-http-renamed-saved.json`, `evidence/admin-ui-tag-b15d7a3c960-http-seo-saved.json`, `evidence/admin-ui-tag-b15d7a3c960-initial-inspect.json`, `evidence/admin-ui-tag-b15d7a3c960-persistence.json`, `evidence/admin-ui-tag-b15d7a3c960-renamed-saved-inspect.json`, `evidence/admin-ui-tag-b15d7a3c960-seo-saved-inspect.json`, `evidence/admin-ui-tag-b15d7a3c960-setup.json`, `evidence/admin-ui-tag-20261103-observations.json`, `evidence/backend-tag-delete-lock-followup-final.json`, `evidence/backend-tag-rename-final-manifest-v2.json`, `evidence/restricted-role-http-7c8a9d2e4f61-v1.json`, `evidence/restricted-role-completion-7c8a9d2e4f61-v1.json`

Limits:

- HTTP25/service38先于锁序修复，10只证明增量竞争风险；delete9的engine拒绝仅source检查，非实际engine变化。原memory非完整应用/DB/HTTP。
- UI仅操作SEO仍提交当前tag/lang；API省略键语义由service/HTTP单列。首次全表单rename另归一化seo_description null为空串。
- 固定owner语言、同语言重名拒绝、损坏跨语言关联拒绝及InnoDB为保护边界；rename不重算total/count，delete API范围不新增language参数。

Target: EnterpriseDataService tagindex save/delete + original Tags/TagTag + current article editor

Pass criterion: 原有限服务/HTTP/实际浏览器记录及新增正常受限角色逐动作HTTP通过；剩余浏览器和输入组合见remaining_cases。

### B16 [P1] implemented_finite_original_rollback_HTTP_UI_passed_remaining_boundaries_open

Owner: admin

Tags: channel, type, adv, flink, guestbookform

Later directory follow-up: [24 checks / 10 authenticated requests](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-directory-collision-http-second.json), [five independent exception cases](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-directory-conflict-independent-v1.json), no new browser/public-template requests. Current earlier frozen evidence: 67/67 finite runner checks; 25 authenticated and 18 public HTTP requests; real UI recorded separately. All 19 protected tables restored and owned fixtures removed. See [HTTP result](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/backend-enterprise-structure-http-third.json), [binding](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-structure-final-evidence-v2.json), [UI](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-structure-http-007ab14b2299/main-ui-observations.json).

Cases:

- CategoryService创建主语言共享栏目时，对照原ArctypeLogic::syn_add_language_attribute克隆目标语言行并建立tid<ID>绑定，父级映射正确。
- EnterpriseDataService的adposition/ad/linksgroup/form创建路径对照原同步生命周期；广告目标pid映射到克隆广告位，绑定数据支持原ID标签读取。
- 验证共享/独立、主/非主语言及原版规定的注册语言处理边界；既有译文、配置和非归属数据保留。
- 相关写入在事务内完成；注入中途失败后主行、克隆行、字段与language_attr/language_attribute绑定无残留。
- 实际后台创建并重开，原PC/mobile模板通过主语言ID在目标语言读取；记录真实HTTP/持久化与精确清理，不用仅新增语言复制成功代替。

Remaining explicit branches:

- 目标语言dirname碰撞已补中文原子拒绝，专项24项/10认证HTTP和独立异常注入5项通过；19表原行/列摘要恢复。L11后续35项已覆盖三个代表writer锁顺序，L12后续66项已有双worker更新传播；L04标题/参数合同及实际持久化已有有限证据，配置/资格已有本轮有限方法/DB证据；正常query链接HTTP已有第五轮有限证据；single有限结构/metadata已获DB33/HTTP85，非空自定义字段及显式结构修复已有后续有限增量；新提示实际浏览器及必要角色UI仍待验，旧UI1062及测试首轮解析空数组失败保留。
- 共享/独立及全部新增、回收、恢复、并发故障与受限角色浏览器组合未穷尽；67项不是完整S3通过。

Limits:

- 原PHP7实际方法与候选内存/事务回滚、认证HTTP、真实UI分层记录；不是原完整应用数据库/HTTP差分。
- 禁用目标语言仍按原结构复制，已有译文保留；实际双端读取为有限已执行标签输入。

Target: CategoryService / EnterpriseDataService / shared multilingual creation and binding lifecycle

Pass criterion: 按真实原版对应共享/独立规则，已存在语言后的创建克隆、父级/广告位映射、绑定读取和回滚均有直接证据；实际后台到双端前台闭环通过，原数据和资源未改。

### B17 [P1] implemented_finite_original_rollback_HTTP_UI_passed_remaining_boundaries_open

Owner: admin

Tags: type, channel, url

Later directory follow-up: [24 checks / 10 authenticated requests](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-directory-collision-http-second.json), [five independent exception cases](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-directory-conflict-independent-v1.json), no new browser/public-template requests. Current earlier frozen evidence: 67/67 finite runner checks; 25 authenticated and 18 public HTTP requests; real UI recorded separately. All 19 protected tables restored and owned fixtures removed. See [HTTP result](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/backend-enterprise-structure-http-third.json), [binding](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-structure-final-evidence-v2.json), [UI](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/enterprise-structure-http-007ab14b2299/main-ui-observations.json).

Cases:

- 对照原ArctypeLogic::dirname_unique/get_dirname，使用language_mark字典及系统保留目录；显式冲突拒绝，自动生成冲突后缀。自定义注册mark但不在字典的扩展输入单列。
- 新增/改名、根/子栏目及共享语言克隆相关路径使用一致规则；正常目录、原有ID和URL契约保留。
- 真实后台创建/编辑并重开，实际GET区分语言首页与栏目页；/en/仍解析为语言首页，新栏目有效URL能读取正确栏目。
- 通过相同服务校验和事务/并发边界防止绕过；失败不留下栏目或绑定，夹具精确清理。

Remaining explicit branches:

- 原语言字典中的cn/en显式目录拒绝及普通目录新增/编辑、语言首页与栏目路由已实际HTTP/UI验证；L11三个代表锁顺序已通过，全部目录/SEO组合未穷尽。
- 原规则以language_mark字典为准，custom注册qa/qb不在字典时保留同名目录；不能声称任意注册语言标记都拒绝。目标语言目录占用的中文原子拒绝已有专项HTTP/DB证据，custom标记的原标签生成链接、双端跟随与显式默认语言根路由已有[有限新证据](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/custom-language-directory-routing.md)，混合子树页面已获历史74e43 HTTP160及当前f5b1 Article继承HTTP280有限证据，实库82另验异模型权限与回滚；Single旧empty_logic在上一冻结仅memory，现有下节SQL146/HTTP272有限证据。其它目录/SEO、grade移动、实际角色UI及并发组合待验；L11仅覆盖相同目录、创建/同步三种受控真实锁顺序。

Limits:

- 原版显式冲突拒绝与留空自动生成属于不同入口，不把显式输入伪造为自动suffix。
- 未修改原模板、禁用语言路由或改写原资源。

Target: CategoryService dirname validation/generation + language-aware public route contract (root coordinates route review)

Pass criterion: 目录冲突行为与原入口语义一致；实际后台新增/编辑和前台HTTP验证语言首页及栏目均可达，普通目录无回归，失败/清理无残留。

## Per-tag explicit input gaps

Missing attributes below mean no explicit captured input, not that an implementation is absent. Defaults, PHP boundary fixtures and page render evidence may exist separately. Use the named task and source defaults to choose finite value partitions.

| Tag | Not explicit in captured fixtures | No matching-original explicit input | Tasks |
|---|---|---|---|
| `php` |  |  |  |
| `channel` |  |  | T08 |
| `channelartlist` | id | id | T08, A01 |
| `arclist` |  | pagesize, siteall, tagid | T06, A01, A02 |
| `arcpagelist` |  |  |  |
| `list` |  | siteall | T06, A01 |
| `pagelist` |  |  |  |
| `position` |  |  | T07 |
| `type` |  |  | T07 |
| `arcview` |  |  | T07 |
| `arcclick` |  |  | T11 |
| `downcount` |  |  |  |
| `load` |  |  | T10 |
| `guestbookform` |  |  | T11, A01 |
| `formreply` |  |  | T05 |
| `assign` |  |  |  |
| `empty` |  |  |  |
| `notempty` |  |  |  |
| `foreach` |  |  | T09 |
| `volist` |  |  | T09 |
| `if` |  |  | T09 |
| `elseif` |  |  | T09 |
| `else` |  |  |  |
| `switch` |  |  | T09 |
| `case` |  |  | T09 |
| `default` |  |  |  |
| `compare` |  |  | T09 |
| `ad` |  |  | T11 |
| `adv` |  |  | T11 |
| `global` |  |  |  |
| `static` | code, lang, site | code, lang, site | T10 |
| `prenext` |  |  | T07 |
| `field` |  |  | T07 |
| `searchurl` |  |  |  |
| `searchform` | empty | empty | T11, A01 |
| `tag` | name | name | T03, A01 |
| `tagarclist` |  | name, offset, tagid | T03, A01, A02 |
| `flink` |  |  | T11 |
| `language` | currentstyle | currentstyle | T10 |
| `lang` |  |  | T10 |
| `ui` |  |  | B01 |
| `uitext` |  |  | A01, B01 |
| `uihtml` |  |  | A01, B01 |
| `uiupload` |  |  | A01, B01 |
| `uitype` |  |  | A01, B01 |
| `uiarclist` |  |  | A01, B01 |
| `uichannel` |  |  | A01, B01 |
| `uimap` |  | id | B01 |
| `uicode` |  |  | A01, B01 |
| `uibackground` | id | id | B01 |
| `sql` |  |  | T10 |
| `weapp` |  |  | T12 |
| `range` |  |  |  |
| `present` |  |  |  |
| `notpresent` |  |  |  |
| `defined` |  |  |  |
| `notdefined` |  | name |  |
| `define` |  | name, value |  |
| `for` |  |  | T09 |
| `url` |  |  | T10 |
| `function` |  |  | T09 |
| `diyfield` |  |  | T07 |
| `attribute` |  |  | T11 |
| `attr` |  | aid, name |  |
| `weapplist` |  |  | T12 |
| `screening` | empty, key, mod | empty, key, mod | T02 |
| `diyurl` |  |  | T10, A01 |
| `likearticle` |  | name | T04, A01 |
| `videoplay` |  | autoplay, id | T11 |
| `videolist` |  |  | T11 |
| `hotwords` |  |  | T03, A01 |
| `weapptaglib` |  |  | T12 |
| `asklist` |  | empty | T11 |
| `specnodelist` |  |  | T01 |
| `specnode` | name | name | T01 |
| `pagespecnode` |  |  | T01 |
| `citysite` |  | currentclass, nositeid | T08, A01 |
| `navigation` |  |  | T08, A01 |
| `faq` |  |  | T11, A01 |
| `include` | file | file | T09 |

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


## 普通栏目提升与重新挂载增量（2026-11-04）

[本轮分层记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/custom-language-directory-routing.md)修复 Product 子栏目提升为顶级时 `channeltype` 被错误改为自身模型的问题：当前 `CategoryService=5711001f` 在顶级编辑保留原组值，创建顶级仍取自身模型，挂到非顶级仍取新父组值。原完整提取 `updateData/getHasChildren/arctype_options` 与候选真实完整 `save` 的[修前74](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-move-save-memory-before-v2/report.json)为诊断通过/parity=false，仅混合模型提升的编辑节点＋4合格后代有5处channeltype差异；[修后74](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-move-save-memory-after-v1/report.json)五个正常场景parity=true。[独立内存复核141](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-move-save-memory-independent-review-v1.json)确认各14节点、4合格/6排除后代、5文档和Article/Product/Single双端附表保护。该层用严格内存适配器，不是实库或完整原控制器；首轮namespace工具失败保留。

[HTTP修前544](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-promotion-http-before-v1.json)保留18个提升阶段channeltype传播断言失败；[修后544](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-promotion-http-after-v1.json)通过。各2次仅 `id,parent_id` 的真实POST、12次API重开GET和24公开GET，38响应/3SQL/247来源；四节点混合树经seed→提升→挂新Product根，18条PC/mobile原type/channel href原样跟随，显式原field标签和变量核grade/topid/channeltype/current_channel/dirpath/DIY。两根完整行、模型及人工DIY/模板/规则保持，无文档写入。各164表摘要恢复、10owned分区0、4模板/4编译/2原始日志清理、cookie不变/UI基线0。[独立HTTP复核493](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-promotion-http-independent-review-v1.json)重算总54个所属raw分区摘要；复核数不追加业务数，原164raw/完整列及移除编译raw未保存，历史header/数据库清理依赖执行记录。

[来源差异3](../../../webman-cms-archive/20261106-structure-cleanup-v1/files/runtime/compat/category-promotion-source-delta-v1.json)确认仅顶级编辑组值选择改变；旧Article/Single HTTP未在新整服务SHA上重跑。上述只有限承接旧“grade移动待验”的普通一致树分区；损坏/陈旧grade、排除祖先跨越、深度限制与真实UI、更广SEO、受限角色、并发/回滚仍待验。4新语法探针不代表未修改官方整页或全主题；T17原生捕获调查和缺陷保持、S3 gate=false。新绑定为 `runtime/compat/stage3-category-promotion-evidence-v1.json`，旧冻结及40任务/22个L/U/R顺序保持。


## Search入口与原表单有限增量（2026-11-04）

[搜索记录](../../../webman-cms-archive/20261106-architecture-only-v2/files/docs/eyoucms/search-entry.md)绑定新SearchActions b175/Controller2fad：原lists方法68项（原第四只新1/复用67，66无warning、2业务相符但诊断不同）、实际选模28与当前owned8f2d的26HTTP分层通过。两端原form实际method1提交，同ID计数1→2/resultNum2；五不同词后第六限流、HTML/AJAX/过滤字段与13原PHP7 lists oracle相符。独立426/147bindings核raw与表快照、26监听归属收据/2模板还原/完整所属DB-copy清理；历史SQL/网络/进程删除仍是执行回执。新来源只重跑base两轮12路线＋Search26，旧44＋2和既有站迁移保持旧来源，不重标。68方法不含原完整框架HTTP，28选模不保证叶渲染，真实UI/并发/未供主题/T16/T17及S3/S4/S6false不变。只给root/T03/T16追加具名增量，40任务/22LUR及旧freeze/失败原字节保持。
