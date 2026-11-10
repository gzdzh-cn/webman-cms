# 数据库初始化与维护

2026-11-09：恢复资料增加显式清理预检及原任务续跑，MySQL5.7.44/8.4.8已有真实有限验证；容量仍有限制，见文末及[本轮记录](enterprise-final-acceptance.md)。旧段落的库名均为当时历史，不授权重装当前运行库。

2026-11-07：统一前缀、双安装模式和精确冲突重装已交付8787。实际安装仅在独立测试库运行；原webman_admin保持168表9826行及逐表内容SHA，webman_cms_new仍0表，8793未操作。见[验收及边界](install-dual-mode-acceptance.md)。

新安装只填写一个 `db_prefix`，默认 `wa_`。CMS表直接加该前缀（`wa_archives`），后台加固定 `backend_`（`wa_backend_admins`），内部表为 `wa_cms_state_records` 等。`.env` 写 `DB_PREFIX` 与 `DB_TABLE_LAYOUT=unified-backend-v1`；已有双前缀站沿用旧布局读取，不直接改表名、业务ID或角色授权。

## 基线、模式与上传导入

| 文件／记录 | 职责 |
| --- | --- |
| `install/database/cms-empty.sql` | 独立维护的157张CMS兼容表结构与必要配置、语言、模型、字段等种子；没有栏目、文档或提交数据。SHA-256 `8170534abd56c2d37043dc0339be11c66ef83c6eecd2cd1d110652fff91bba6d`。 |
| `install/database/cms-internal.sql` | 三张持久业务状态表；不含安装标记表。SHA-256 `6bde9be5f051261a791a21ce22e74e6640e4b2f8382295c59d9692240e892ce0`。 |
| `install/database/webman-admin.sql` | 原7张后台表基线，原字节保留；目标重映射为统一前缀加backend_。 |
| `install/database/eyoucms-1.7.7.sql` | 保留的原始157表资料，SHA-256 `5803d03040bd6a1cc056c0c81103b8f5506cb04a9c6ab0df421f5416f123d8fe`；默认空站不读取它导入。 |
| `<前缀>backend_options.cms_template_mode` | 可信配置行：普通字符串webman或eyou，上传原站SQL不能覆盖；历史缺项按eyou解释。 |
| `<前缀>backend_options.cms_site_signature` | 本次安装的随机普通字符串；与管理员、模式同事务写入，续装保持，用于生成状态隔离。 |

默认Webman空站与EyouCMS兼容空站共用167张基线表，区别是严格标签／根变量模式和默认主题；不维护两套业务数据库。兼容模式才允许进一步勾选SQL导入，源前缀自动识别，统一映射到输入值，保留原站ID、关联、正文与配置。后台及内部表来自可信基线，不接受上传SQL定义这些保留表。

上传只支持完整单个SQL（≤50MiB）；业务表引擎转换InnoDB默认开启，关闭保留受支持的源MyISAM/InnoDB。后台及内部表固定InnoDB。项目后续升级由代码内 `app/cms/database/Migrations.php` 等负责，不依赖安装目录。

## 精确冲突和持久安装进度

标准install存在且install/install.lock缺失即允许主动安装；恢复标准目录并删除锁后，旧数据库有数据不再作为第二把安装锁。**不创建或依赖cms_install_state。**旧版本遗留该表时保留，不因本轮规则变更擅自删除。

安装前完整解析SQL、校验核心结构／种子和数据行，预检按目标完整表名列出冲突。提交带有效plan_token；复核结构、内容摘要和清单。仅替换将创建的精确同名表，改前缀不冲突的原表及同前缀无关表保留，不按前缀清库。外键引用等未支持依赖拒绝自动处理。

先完整建立、导入并校验任务专属暂存表，再复核原表、删除冲突并逐表原子发布。语法、DDL或数据导入失败发生在删原表之前。开始删除后的DDL不承诺整库回滚，须保留私有journal和暂存表续装；MyISAM批次中断可按指纹重建仅归本任务所有的暂存表，避免重复插入。

预检和进度在私有config记录，绑定模式、数据库、统一前缀、源SQL SHA及引擎选项，不存管理员明文密码，不依赖runtime。历史记录保存在config/cms-install-history。成功后写环境、本地完成记录、install.lock，再清理上传并封存；数据库已完成时只补收尾，不重新导入。具体命令与恢复边界见[安装](installation.md)。

`cms:database-init --apply`已禁用，防止绕过模式及冲突审阅；只读预演读取空站、后台与内部基线。新装统一走cms:install，日常升级走cms:migrate。

## 持久业务状态与可删除runtime

| 表（以下均加CMS前缀） | 职责 |
| --- | --- |
| `cms_state_records` | 按scope/name保存任务、manifest、归属hash、撤回、更新、维护回执、限流和迁移审计；带SHA校验。 |
| `cms_state_objects` | 按SHA去重保存HTML/XML/TXT内容快照。 |
| `cms_state_object_refs` | 保存各范围对内容快照的引用。 |

可视化继续存原ui_config表，数据库为唯一有效来源；生成任务使用数据库锁与事务，文件继续原子写入和hash保护。备份包含三张业务状态表、ui_config、可信options、私有安装／环境记录与匹配的公开文件。管理员只存密码哈希，不依赖runtime/local-admin.json。

停全部worker后整删runtime会丢失会话和历史日志，不改变模式、业务、安装锁、任务及public/html。模式、编译版本与本次安装签名进入相关产物和任务校验，不能把原public/html还在误判为新站可直接复用。

## 维护命令

所有项目PHP入口统一为`php webman cms:*`，命令清单可由`php webman list --raw`查看：

| 命令 | 用途 |
| --- | --- |
| `cms:install` | 向导对应CLI；支持check、preflight令牌、安装及seal收尾。 |
| `cms:database-init` | 空站基线只读预演，apply写入已停用。 |
| `cms:migrate` | 已有站增量结构迁移，默认预演。 |
| `cms:state-migrate` | 旧文件状态审计／入库。 |
| `cms:cache-clear` | 停机清缓存，`--all`清整个runtime。 |
| `cms:admin-bootstrap` | 从私有JSON建立明确账号，已有账号保留。 |
| `cms:sync-menu` | 默认预演，apply只补缺失key，保留原节点与授权。 |
| `cms:migrate-navigation` | 导航计划、指纹应用与具名回执回滚。 |
| `cms:repair-single-pages` | 指定栏目缺失结构的有界修复。 |
| `cms:migrate-engines` | 有界MyISAM到InnoDB维护。 |

admin-bootstrap使用绝对0600私有JSON文件，字段为username、password；不生成明文密码文件。输入由操作者安全保管／清除，不作为可公开恢复资料。菜单应用前快照存数据库，不使用旧同步脚本默认删除流程。

导航默认预演，应用需`--apply --expect=<当前指纹>`；回滚使用`--rollback=<原应用指纹>`并重新核对当前expect。回执位于数据库`backups:navigation`范围，旧runtime/backups的回执由状态迁移导入；数据库、前缀和原指纹仍须匹配，不是整表覆盖恢复。

`cms:repair-single-pages --ids=12,34 --admin-lang=cn`只检查明确栏目；应用加`--apply --fingerprint=<检查值>`，至多100个不同栏目。只补缺失空结构、拒绝冲突，保留正文、翻译、ID与关系，不复制源正文。

## 有界MyISAM引擎维护

初始化不转换已有MyISAM。webman cms:migrate-engines只维护既有库的引擎和保存合同，不建库、导入基线、改列或修复未知关系。

inspect默认使用配置前缀，可显式--prefixes=ey_,wa_；所有匹配表含已有InnoDB进入计划，其它前缀基本表排除。计划绑定服务器、库、完整表集合、结构、列／索引、自增、collation／comment与无序全量行摘要。

上限512张匹配表、每表200000行、总base64行编码256MiB，完整读取并在内存整理。仅支持普通MyISAM／InnoDB结构；库内view、trigger、routine、event，以及涉及匹配表的外键、分区、generated／invisible列、非BTREE／表达式／禁用索引、不支持选项或分组复合自增等会拒绝。不要为迁移擅删对象，应另设计方案。

### 检查与私有计划

```sh
"$PHP_BIN" webman cms:migrate-engines
"$PHP_BIN" webman cms:migrate-engines --prefixes=ey_,wa_ --plan=/absolute/private/engine-plan.json
```

默认inspect只读数据库和设置临时会话，不建文件；--plan才创建私有JSON。指纹在result.fingerprint或计划fingerprint。数据变化会使apply拒绝旧计划，inspect不是一致性备份。

父目录须真实私有，计划未存在、非链接、当前UID持有、组／其它用户无权限且≤16MiB。备份首次目录也必须不存在，工具建0700目录和0600文件。计划含结构／服务器资料，备份含完整业务行，均不能公开。

### 停写、备份与应用

先完成站点外部备份和副本验证。从最终检查前停止全部应用写入与DDL，包括Webman、旧站、队列、定时脚本和其它客户端，保持到复核或人工恢复结束。--maintenance-confirmed只是声明，不替你停服务或阻止外部写入。advisory lock仅排除同类迁移；备份WRITE表锁不跨后续ALTER。

```sh
"$PHP_BIN" webman cms:migrate-engines --apply --plan=/absolute/private/engine-plan.json --fingerprint=REPLACE_WITH_PLAN_SHA256 --backup-dir=/absolute/private/new-engine-backup --maintenance-confirmed
```

指纹替换为原计划64位小写值，apply从计划读前缀，不接受另传--prefixes。首次写journal并在表锁下重核计划，保存每表原SHOW CREATE、列顺序、base64单元格及独立NULL；manifest和备份核验通过才转换MyISAM，InnoDB保留复核。转换后检查数据、规范化结构、索引和自增，最后全范围复核。

DDL不是原子事务：ALTER可隐式提交，中断时前面成功转换保留，不自动回MyISAM或恢复数据。保持停写，保留计划、备份、manifest和journal，先检查已发生DDL。

### 验证、续跑与恢复

```sh
"$PHP_BIN" webman cms:migrate-engines --verify-backup --backup-dir=/absolute/private/new-engine-backup
```

此模式离线检查，不连数据库或返回行正文。manifest、hash、恢复合同、权限和journal均符合后，才可用原计划、指纹及同目录续跑。已转换表重新核对等价，未转换表仍须完全匹配，数据／结构／自增／服务器／范围变化会拒绝。完成计划重复apply仍写journal，不是纯只读。

缺manifest或不完整备份无自动修补，不能删journal伪装首次执行。工具不提供破坏性自动restore；人工恢复在另一个已审查空库按原CREATE、列序和单元格值进行，完整核验后再决定切站。

遵循备份restoration_contract：UTC、NO_ENGINE_SUBSTITUTION,NO_AUTO_VALUE_ON_ZERO、sql_quote_show_create=1及utf8mb4／utf8mb4_general_ci会话。base64还原非NULL并区别NULL，保留显式0自增ID，避免时区、转义和SQL mode改变TIMESTAMP或值。

## 限制

引擎维护不表示完整旧站迁移、关系修复、自定义模型、模板资源或阶段通过。原报告与失败从[归档](maintenance-archive.md)定位，新操作按当前代码与目标审查。主题、上传与生成状态需另迁，不能只搬数据库。

## 项目备份的恢复资料清理

数据库备份功能只读取本项目签名格式，不接受任意上传SQL。备份包、恢复任务及密钥在项目外私有目录（可用`CMS_BACKUP_PATH`指定）；正常runtime清理不删除它们。后台“数据库备份与还原 → 恢复资料管理”只向拥有全部数据和语言范围的超级管理员开放。

以下均在当前项目根目录执行，任务号和确认号从实际结果取得，不填写别的项目的编号：

```sh
php webman cms:database-backup tasks
php webman cms:database-backup cleanup --id=<恢复任务号>
php webman cms:database-backup cleanup --id=<恢复任务号> --plan=<预检确认号> --apply
```

第二条只预览数据库，另将确认收据保存到私有目录；不删除表。确认有效期30分钟。执行前逐项核对完整表名、来源、指纹和数据库统计占用，明确接受该任务将不能回退。清理只处理本任务完成后的前镜像或回退后的暂存表，原备份、任务收据保留，不按前缀扫描删除。

一旦持久化清理意图，该任务便不能再执行rollback。中断后重新预览同一任务并沿用原确认号继续，不重新恢复、不重建缺失对象、不删除收据伪装首次执行；原操作者、站点、数据库和清单仍须匹配。任务末记录已完成而收据未收尾时，列表保留“继续清理”入口，仅补收尾。

未完成恢复、维护期间、对象变化、未知归属、外键、备份损坏、站点/前缀/操作者变化均拒绝清理。外部SQL客户端及其他不参与应用流量锁的writer仍须停写。数据库DATA_LENGTH/INDEX_LENGTH是可能滞后的统计，不代表保证释放的磁盘容量。

MySQL8重放自身建表语句可能补出冗余字符集；本轮仅对字符列类型/字符集前缀做SHA规范化，DDL原文、默认值和注释不改，5.7旧摘要不变。旧8.x备份若与新算法不符会拒绝，应重新生成备份，不能手改签名。

容量有限试验为CLI显式`memory_limit=512M`、5000行×8192字节，峰值约272 MiB；该结果不批准HTTP128 MiB或更大数据库。当前整表读取和行摘要仍随数据增长，较大库需先在独立CLI副本验证内存、时间和磁盘，后续需要分批读取及有界排序，不能用SQL文件大小推导安全上限。
