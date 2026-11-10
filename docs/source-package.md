# 历史源码包与资源恢复

2026-11-10 系统资源统一：公开系统资源统一在 `public/admin-assets`，共享资源迁至 `public/admin-assets/common`；`template` 仅存主题，禁止创建 `_shared` 实体或软链接。全部登记旧地址保留逐文件别名，动态语言 JS 继续使用文件／Redis 派生缓存，不写公开目录。format=2 保持，当前 revision 为 `2026-11-10-unified-system-assets-v1`；配套包、隔离验收和发布状态见[统一资源验收](unified-system-assets-acceptance.md)。本段取代下方历史共享资源布局与旧配套包约定。

2026-11-09 企业官网补齐：打包须使用revision=`2026-11-08-enterprise-v1`的源码和资源清单，配套资源包 `resource-assets-enterprise-v1.tar.gz`，校验信息见[资源维护](resource-maintenance.md)。排除config/cms-database-traffic.lock、cms-database-maintenance.json、项目外私有备份/密钥、恢复任务、前镜像、验收夹具及runtime。不能复制开发库的角色范围/正文来源记录作为空站数据；新站使用可信安装基线。

## 当前安装与资源包规则（2026-11-08）

FORMAT3 安装规则以[安装说明](installation.md)和[本轮验收](install-policy-v3-acceptance.md)为准；不替用户提交真实重装。本节和下方更新后的制作步骤覆盖历史记录中的“独立设置 CMS／后台前缀”“已有数据一律拒绝重装”及数据库安装标记表规则。当前改动已完成隔离验收并交付开发站8787，开发库仍待用户安装；8793不变。实际范围见[本轮验收](install-dual-mode-acceptance.md)。下方原有交付日期仅代表对应历史版本。

- 新安装只设置一个前缀，默认 `wa_`：CMS 表为 `wa_archives` 等，后台表为 `wa_backend_admins` 等，内部持久表为 `wa_cms_state_records` 等。新环境的受管 PHP 配置记录 `DB_TABLE_LAYOUT=unified-backend-v1` 和 `DB_PREFIX`；历史分离前缀运行站仍由兼容配置读取，不为制作新包修改旧数据库。
- 默认 Webman 模式使用 `webman-default`、`{webman:*}` 与 `$webman`。在下拉框选择兼容 EyouCMS 模式后使用 `{eyou:*}` 与 `$eyou`，必须上传原站 SQL，并从本地 Eyou 标签主题候选中选择；唯一主题自动使用，多套需明确选择；两种模式拒绝另一套标签及根变量，后台配置只读展示已安装模式。缺少模式配置的旧站按 EyouCMS 处理。
- Webman 使用项目维护的 `cms-empty.sql` 结构及必要种子，可选择内置示例。EyouCMS 必须使用用户上传的单个完整 `.sql`：不勾选初始化仅取结构并植入可信基础种子，勾选则保留源站数据，不混入内置示例；上限 50 MiB、2 MiB 分片；源前缀自动识别，目标统一使用安装输入前缀。转换为 InnoDB 默认开启；关闭时业务表保留受支持的源引擎，后台及内部表始终为 InnoDB。
- 安装入口只由标准 `install` 及 `install/install.lock` 门控；目录缺失或锁存在时关闭。恢复标准目录并主动删除其中锁文件后允许重装，历史配置或已有表不会继续阻止该入口。完整 SQL 预检、目标表清单及冲突指纹通过后，只删除本次将创建的完整同名表；修改前缀后不冲突的旧表保留，不按前缀批量清空。上传文件中的删表、切库、建库指令不直接执行。
- 不创建、不依赖 `cms_install_state`。业务结构及必要种子用于核验安装结果；本地私有安装进度绑定模式、数据策略、主题文件签名、前缀、SQL SHA、引擎及目标清单，续装不重复清理已完成表。模式保存在可信后台配置，导入数据不得覆盖。
- 成功后写 `install/install.lock`，再封存为 `install_<12位小写字母>`。收尾失败只补做锁和封存，不重复导入；清空 runtime 不改变模式、安装锁及进度。重装不删除主题、上传或已有静态 HTML，模式／站点签名不符的旧产物拒绝复用。

历史记录：2026-11-07 新包安装锁处理和 EyouCMS 入口判定曾交付8787，隔离新装及保护审核通过。以下打包规则取代上一轮未明确清除“副本内 install.lock”的说明；源站资料保护与历史交付记录保留。实际范围见[本轮安装验收](install-eyou-acceptance.md)。

2026-11-07 资源整理：后台发布资源集中于 `public/admin-assets`，前台共用资源在 `template/_shared/assets`；私有配置、字体和源码不公开。旧资源地址按 format=2 清单兼容。当前构建／安装／恢复流程见[资源维护](resource-maintenance.md)，历史 format=1 包只用于外部回退。

2026-11-07：开发站 8787 已完成本轮停机迁移并重启，新架构已运行。旧状态已逐项核验入库，整个 runtime 已清除并自动重建；8793 保留副本未改动、未同步。迁移后 HTTP 复查与当前管理员登录后的主要页面读取、筛选和打开／返回验收已通过。本轮架构迁移验收完成；未扩大为全部角色、业务写入或 EyouCMS 严格兼容验收。

## 全新安装包与运行站备份

上一轮安装自动引导与资料封存已交付8787，历史验收与边界见[封存记录](install-seal-acceptance.md)。当前新站安装先写 `install/install.lock`，再立即把根 `install` 改名为 `install_<12位小写字母>`；这是站点资料封存，不是新安装包入口。原 EyouCMS 的20位混合随机后缀与本项目12位小写后缀是保留差异。私有 `config/cms-installer-archive.json` 记录确切目标和完整 SHA，保存在 runtime 外；旧 journal 已固定的归档不补入 lock、不改清单。

制作**全新安装包**须使用独立打包目录。源码包仍需独立制作；部署目录的去敏工具只向标准输出提供无凭据配置模板，不会读取、写入或识别目标目录：

1. 将源码和必要运行资源放入打包副本；原项目 `.git`、凭据、安装锁及归档保持原位。需要从已封存资料恢复安装包时，依据私有记录精确定位并核验完整 SHA/大小及目录清单，复制到打包副本的标准 `install/`，并对复制结果再次完整核验。不得扫描相似名称猜测来源，也不能改名运行站的目录。
2. 新包的安装界面以同版 `app/cms/install/view/index.html` 为维护真源。核验并复制安装资料后，仅在新包内以该文件覆盖 `install/index.html`，记录两者相同 SHA；不得修改源站封存页或重算源归档记录。新包带 `install/index.html` 及 `install/database/cms-empty.sql`、`cms-internal.sql`、`webman-admin.sql` 三份可信基线，随配套源码核验。原始 `eyoucms-1.7.7.sql` 若作为历史资料保留则保持原字节，但不是空站安装输入，也不能替代用户上传的原站 SQL。归档已删除时从可信同版安装包补齐，不能用运行站数据库导出替代空站基线。
3. 完成上述完整核验后，仅删除**打包副本**的 `install/install.lock`（若存在），从副本排除源站 `.env`、`config/cms-installed.json`、`config/cms-installing*`、`config/cms-install-handoff*`、`config/cms-install-history/`、`config/cms-installer-archive.json`、`config/.cms-install-env.lock`、runtime、临时安装凭据与站点专用私密资料。若提供环境示例，仅保留无密码、无现有站数据库绑定的示例配置。包内不带原站随机归档目录。
4. 在项目外新建私有打包副本，使用下述工具生成无凭据的完整 `database.php` 模板，再由操作员仅复制到已核验的打包副本；命令输出重定向不可指向运行站或其链接。模板是同版固定配置，并不保留站点对受管块外的定制；需要的通用定制须在副本单独审查后应用，禁止带入凭据。工具不判断目标是不是活动站，因此目标归属由操作员确认。

   ```sh
   php app/cms/maintenance/deployment/sanitize_package_config.php > /private/staging/database.php
   php -l /private/staging/database.php
   # 核对打包副本路径及其非链接后，复制模板到该副本的 config/database.php
   ```

   核验受管块为空、源站文件摘要未变化，且包内不含 `.env`、数据库凭据、密钥文件或项目外私有资料。示例配置不得包含站点凭据，调试和 SQL 日志应关闭。不要在日志或终端输出数据库真值。
5. 在独立库验证 Webman 空站／示例、EyouCMS 上传 SQL 仅结构／含数据四种组合；首次访问进入 `/install/index.php`，`/install` 与 `/install/` 别名可用，成功后先写锁再封存并重启。重装另用隔离数据核对同前缀、不同前缀和部分冲突：完整预检失败时不删除，提交时重新核对完整表名、结构与数据指纹，清单外旧表不变。制作安装包或验证不授权替用户在真实站点提交重装。

标准 `install` 不存在或普通 `install.lock` 存在时，安装入口直接关闭，无须数据库标记，也不连接数据库。标准目录无锁时进入环境、上传、预检与可信续装流程，允许按明确冲突清单重装已有数据库。数据库不可用、SQL 无效或冲突指纹变化时不得进入删除阶段。打包过程仍绝不可删除、修改、注入锁或改名源站归档；主动重装与制作新包是两项不同操作。

运行站备份或同站点迁移保留匹配的数据库、私有安装状态和环境配置，不能套用新包的去锁步骤。封存中断后先用 `php webman cms:install --seal --check` 检查，再用 `--seal` 恢复同一目标；不要重复导入数据库。

普通 `cms:migrate` 升级使用 `app/cms/database`，不依赖原 install 或随机归档。归档可删除，私有记录保留；`cms:database-init` 维护和独立部署中如需基线，仅接受记录匹配的完整资料。独立部署工具允许安装资料缺省，但执行需要重建基线数据库的演练仍须具备基线，不能将“目录可选”解读成已验证所有无资料重建场景。

## 历史容量约定

2026-11-07曾按用户确认交付不直接运行的精简源码包，之后用户要求保留.git并恢复运行。历史源码预算排除docs、template、public/uploads和单独统计的.git，源码约8.43MB、磁盘占用约9.79MB；包含版本库或恢复运行依赖后均不满足旧10MB口径。**当前完整运行项目不继续套用该限制。**

.git已原样恢复到项目内实体目录，历史、HEAD、完整索引和4,154个文件当时核验一致，不得再当缓存搬移或删除。源码包历史清理核对15,976项保护文件，并对2,148项外置资源及49项压缩组件源码校验；这些数字不代表本轮架构回归范围。

用户要求启动后，8787已恢复84个锁定依赖包、2,148项公共资源及688项配套状态／可视化／公开实体；旧PID、会话和日志未恢复。此为本轮架构迁移前的历史恢复记录；新架构停机迁移、最终HTTP及当前管理员登录后的主要页面验收已完成；范围见当前交接。

## 当前资源清单与外部包

当前可维护源码为 `app/cms/resources/asset-build-inputs.tar.xz`，已经合并原定制源码包；不再使用旧 `admin-asset-sources.tar.xz`。format=2 恢复清单为 `app/cms/resources/source-package-assets.json.gz`。系统资源恢复到 `public/admin-assets`，其中共享资源恢复到 `public/admin-assets/common`，私有资源恢复到 CMS 目录。恢复过程不得创建 `template/_shared`。

当前配套资源包（必须与本版 format=2 清单成组使用）：

```text
/Volumes/disk/site/php/dzhcms/unified-assets-20261110-v1/resource-assets-unified-system-v1.tar.gz
```

包 SHA256：`91f25402e77aca1de27a9f4edc71597277f73e082ab7fdf8204999c76907a7cf`，大小 **15,198,107 字节**。清单 revision=`2026-11-10-unified-system-assets-v1`，2,210 条记录、2,132 个唯一实体。686 个共享实体仅迁移位置，全部资源字节保持。源码归档 SHA256 为 `2d764f06a4ff7ba4d0f64115b367237238bb5ece24ddbe5b563bfd24f65380a3`，原字节不变；归档保存原始来源身份，目标目录由清单与构建配方决定。完整锁定重建与干净恢复均只生成新目录，见[验收](unified-system-assets-acceptance.md)。

2026-11-08 的多语言包 `multilingual-20261108-124545/resource-assets-multilingual-v1.tar.gz` 属于历史整版回退资料，不与当前清单混用。

历史 `resource-layout-20261107-v1/resource-assets-v2.tar.gz`、原 `public-assets.tar.gz`、旧清单及外部 `state` 仅为历史整版回退资料，不能用当前恢复器恢复到新站。外部 state 中的 8793 是独立保留站点，不属于本项目可删除 runtime。外移保存不等于释放同卷空间。

## 恢复公共资源

在项目根使用 PHP ≥ 8.2 与 Python ≥ 3.9，先预演再应用：

```sh
php webman cms:assets restore --archive /Volumes/disk/site/php/dzhcms/unified-assets-20261110-v1/resource-assets-unified-system-v1.tar.gz
php webman cms:assets restore --archive /Volumes/disk/site/php/dzhcms/unified-assets-20261110-v1/resource-assets-unified-system-v1.tar.gz --apply
php webman cms:assets verify
```

工具检查整包与逐文件 SHA／大小，拒绝旧 format、旧目标目录、越界、重复成员、链接和特殊文件；已有相同文件跳过，不同文件拒绝覆盖。本轮包包含 2130 个实体，隔离完整恢复、重复恢复及锁定工具全量重建已核对 SHA；冲突及损坏包拒绝的原有机制保留，不将历史验收误记为本轮重复执行。没有配套资源包时，不能声称只用 Composer 可恢复完整公共资源。

依赖按 composer.lock 和[部署说明](deployment.md)恢复。恢复资源不授权重导数据库或重置管理员密码；构建及详细验收见[资源维护](resource-maintenance.md)。

## 旧状态接入新架构

旧源码包的runtime保存过任务、manifest、内容对象、归属和可视化文件，必须在新架构使用`cms:state-migrate --source=<旧runtime绝对路径>`预演、停机应用并核验，再清理旧状态。**不要将整份外部runtime复制回新运行站。**

状态迁移将任务与快照等写入三张 CMS 持久化表，可视化写入 `ui_config`；当前安装状态不创建或依赖数据库安装标记表，维护收尾使用本地私有记录、文件锁及实际业务结构核验。公开HTML仍需与迁移状态保持同一快照，固定在public/html。迁移完成后的runtime可停机整删、启动重建，管理员只保留数据库哈希、不恢复local-admin.json，不恢复旧PID、会话或日志。完整步骤和回退在[部署](deployment.md)。

8793副本与manager保持外部原样，未自动同步；manager原有SHA阻塞未解除，不保证从外部路径直接运行。

## 验证和资料可用性

历史恢复工具已在隔离目录覆盖全量恢复、重复执行、冲突、软链接和损坏包拒绝；本轮新路径的只读2148项资源核对通过。本轮迁移已由主执行核对template、uploads和public原文件hash保持，有限检查不扩大兼容结论。

旧同级webman-cms-archive在本轮准备时已不在现场，来源未确定；旧文档链接不能作为现存备份保证。上述本批资源包独立保留，历史资料位置见[维护说明](maintenance-archive.md)。
