# 维护与外部归档

2026-11-09 企业官网补齐的候选、原基线、独立库验证、资源包和本批发布快照集中保存于 `/Volumes/disk/site/php/dzhcms/enterprise-completion-20261108-222026`。private包含敏感配置/快照，不公开；恢复只处理本批文件，不整库覆盖后来内容。共享mysql57 OOM及恢复复核、13319隔离措施和剩余差异必须随[验收记录](enterprise-completion-acceptance.md)保留。

2026-11-07：开发站 8787 已完成本轮停机迁移并重启，新架构已运行。旧状态已逐项核验入库，整个 runtime 已清除并自动重建；8793 保留副本未改动、未同步。迁移后 HTTP 复查与当前管理员登录后的主要页面读取、筛选和打开／返回验收已通过。本轮架构迁移验收完成；未扩大为全部角色、业务写入或 EyouCMS 严格兼容验收。

## 当前目录和保护规则

运行代码集中app/cms，工具归app/cms/maintenance和console；初始SQL在install/database。原根data、resources、scripts、sql已在隔离及8787代码中完成归类。docs保留当前说明、标签手册、来源和任务矩阵；不把历史截图、测试工具、备份或副本重新塞回应用根或runtime。

.git原位保留。完整运行版本不套用历史10MB源码包指标；vendor是锁定运行依赖，不能当作可直接删除的运行缓存。资源恢复清单与组件源码属于私有资源，见[源码包说明](source-package.md)。

迁移完成后，runtime只放可重建运行文件，支持停全部worker后整删；任务、manifest、SHA对象、归属、撤回、限流和维护回执在数据库，可视化在ui_config，安装状态在数据库与config。清理后登录会话失效、旧日志不恢复，public/html和业务数据不动。旧架构迁移前的runtime仍有真实状态，须先预演／迁移／核验。

## 本轮架构隔离目录

```text
/Volumes/disk/site/php/dzhcms/webman-cms-preserved-20261107-v1/
├── public-assets.tar.gz
├── assets-manifest.json
├── state/                         历史真实状态和8793保留环境
└── architecture-refactor-20261107-v1/
    ├── workspace/                 本轮隔离工作副本
    ├── verification/              本轮工具、证据及检查结果
    └── live-cutover/               8787停机切换快照与核对资料
```

该目录为私有维护资料，不参与网站运行，也不放入public或未经审查的提交。本轮8787切换前快照及保护清单在live-cutover；按其中实际文件及SHA核验恢复，不能仅凭存在工作副本推断备份完整。

代码归类已独立核验58个原入口契约、17个私有资源原字节、2148项公共资源和相关HTTP；验证范围见当前交接。新架构8787停机切换和受保护文件核对已完成；最终HTTP与当前管理员登录后的主要页面读取、筛选和打开／返回验收已通过。

本轮迁移核对：原164张表共8153行逐行无变化，仅新增4张内部表；561个旧状态文件共15,442,498字节逐项核验入库，另迁移1个版本标记。停机清除整个runtime共1648个文件，重启已自动重建所需目录。Git索引、template、uploads、.env及public原文件hash保持；8793未改动。495个PHP文件语法检查无错误。

隔离整删runtime演练中，已完成的4个首页任务未重跑，待执行的6个PC/mobile文档任务自动续完。该隔离演练与开发站HTTP、登录后页面验收分别记录，不以一类证据替代另一类。

## 8793保留环境

8793副本和manager在项目外：

```text
/Volumes/disk/site/php/dzhcms/webman-cms-preserved-20261107-v1/state/runtime/standalone-deployment/a647e20c5f39/
/Volumes/disk/site/php/dzhcms/webman-cms-preserved-20261107-v1/state/runtime/compat/retained-delivery-20261105-v1/
```

库为wmcms_verify_a647e20c5f39，保留停止状态，开发修改不自动同步；本轮不启停、不改库、不覆盖、不cleanup。这里名称含runtime但它是外部独立环境，不属于开发站可清runtime。

历史manager存在先于本轮的基线SHA失配：预期验证器`9b0ffd08e6a842607b79299d2ea0ca46cbf8fe52bdd7a6ae993bca8f7d017c02`，当时实际`410bdffe55e3c02326d89a6a2e6ed58e83177bd35c9fc45987d95f9efe94fb07`，导入即拒绝。二者是历史审计值，不是本轮新工具SHA；不要改预期值绕过保护。保留副本完整不代表manager当前可用。

新独立部署工具在`app/cms/maintenance/deployment`，run／cleanup要求显式应用外work-dir；它不接管旧8793环境。旧patch依赖自己的候选源码与输入，不能用当前开发文件代替批准版本。

## 两批旧归档及逻辑索引

以下历史位置在本轮准备时已不在现场，原因未确定，不能声称可恢复：

```text
/Volumes/disk/site/php/dzhcms/dzhcms002/webman-cms-archive/
```

| 历史批次 | 原职责 |
| --- | --- |
| `20261106-structure-cleanup-v1` | 旧runtime、备份、调试和废弃实现，按files/原相对路径保存。 |
| `20261106-architecture-only-v2` | 历史文档、826份证据、测试及开发工具。 |
| `v2/originals` | 精简前12份README、AGENTS和当前文档原件。 |

恢复取证前必须先确认批次实际存在并核对manifest、路径及SHA。不能创建空文件冒充归档，也不为修链接重写冻结报告内部路径和hash。历史运行文件可能含业务、会话或凭据，保持私有。

labels.md保留79篇已读文档正文，sources.json保留来源和hash。compatibility.json中的826条evidence_files是历史逻辑索引，不断言原位文件存在；原定位规则为v2/files/docs/eyoucms/<值>。40任务和22项L/U/R的ID、顺序、失败与要求不因目录迁移改变。

历史目录整理曾迁出4771文件，后续清理82个重建产物释放约25.64MiB；精简源码包又移出资源及依赖，之后按用户要求恢复运行。这些是不同时间的执行口径，不能累加为本轮节省容量或兼容率。旧报告内容按原来源保留，不在当前应用内重复生成。

## 维护回执和回退

导航回执现存数据库backups:navigation，旧runtime/backups按状态迁移导入；回滚仍核对数据库、前缀、原应用指纹和当前计划，不支持整表覆盖。引擎计划／备份为外部私有文件，不应写runtime；引擎DDL不是原子事务，保留其journal及恢复合同。

旧工具不保证透明重放；需要时先核对来源、依赖及实际支持的输入，在隔离工作目录准备，禁止直接运行旧在线写库／发布／清理夹具。详细操作见[数据库](database.md)和[部署](deployment.md)。

切换前保存数据库、源码、私有配置、旧持久文件和匹配公开实体；恢复必须停机、同一快照成组校验。切换后有新数据时先备份并评估差异，不能直接覆盖用户新内容。不得把旧runtime整份覆盖到仍运行的新架构。
