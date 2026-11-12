现行目录与二进制规则：默认上传实体为站点根 `uploads/`；外置 YAML 三路径选一、内置配置固定、资源整根优先及无安装二进制见[构建部署说明](binary-deployment.md)。以下历史验收不扩大本批实际验证范围。

# 安装与数据库维护

2026-11-11 修复运行中将 YAML 移到根目录后安装交接失败的问题。安装配置检查、数据库参数保存和重启交接重新按 `config.yaml`、`config/config.yaml`、`config/manifest/config.yaml` 选择第一份现有文件；显式 CMS_CONFIG_FILE 和内置只读规则保留。安装阶段的选取不改变旧 worker 的数据库启动快照，保存后完整重启生效。根 YAML 无效时不回退，其他配置字段保留。重启过渡期状态返回可重试的 503，页面继续等待。实际证据与本次已完成安装的处理见[根目录 YAML 安装修复](install-root-yaml-acceptance.md)。

2026-11-09 修复 MySQL dump 独立 `--` 注释被误判为 SQL 的问题。3144原站备份已通过独立库完整数据安装；迁移时勾选“导入初始化数据”、选择3144主题。该旧结构的仅结构策略仍因缺少空站种子字段而明确拒绝，范围与证据见[3144导入验收](install-3144-sql-acceptance.md)。

2026-11-09 安装续装检查新增受限的InnoDB自增计数恢复：仅本任务表、完整行摘要不变且代回原值可精确还原旧schema SHA时提高计数；原冲突表校验不放宽，新检查点保存实际十进制计数。旧FORMAT2不变，原值无法证明仍拒绝。操作、故障与本批发布状态见[安装恢复验收](install-resume-acceptance.md)。

2026-11-09 企业正文来源服务接入新安装目标库，可信基线明确记录原始/编码字段来源；仍使用安装任务目标PDO，不向运行库批量补写或猜测历史unknown来源。安装模式、初始化、SQL上传、冲突表规则及进度页面均保持。数据库备份还原是独立维护功能，不能代替安装向导执行任意上传SQL；使用与限制见[维护工具](enterprise-management-tools.md)。本批未在8787提交重装。

2026-11-08：安装数据策略升级为 FORMAT3。EyouCMS 模式必须上传 SQL，初始化选项决定是否导入其中的数据；Webman 模式使用内置结构及可选示例。当前规则覆盖旧版“SQL 与初始化互斥”和“兼容模式不上传 SQL”的行为。交付范围见[本轮验收](install-policy-v3-acceptance.md)，历史边界见[双模式验收](install-dual-mode-acceptance.md)。不代用户提交开发站重装，原 webman_admin 与8793保留。

安装业务在 `app/cms/install`，SQL 解析与增量升级在 `app/cms/database`，命令实现在 `app/cms/console`；Web 与 CLI 共用服务。安装页面及初始数据位于标准 `install/`，成功后整体封存。

## 安装方式和标签模式

| 选择 | 建站数据 | 前台语法 | 默认主题 |
| --- | --- | --- | --- |
| Webman，不勾选初始化 | 内置结构及必要基础配置，空站 | `{webman:*}`、`{$webman.*}` | `webman-default` |
| Webman，勾选初始化 | 内置结构及企业示例数据 | `{webman:*}`、`{$webman.*}` | `webman-default` |
| EyouCMS，不勾选初始化 | 必须上传 SQL；使用其结构和可信基础种子，不导入原站内容 | `{eyou:*}`、`{$eyou.*}` | 自动使用唯一候选；多套时明确选择 |
| EyouCMS，勾选初始化 | 必须上传 SQL；导入其中的原站数据，不混入内置示例 | `{eyou:*}`、`{$eyou.*}` | 同上，覆盖全部导入语言的旧主题路径 |

两种模式使用同一套业务表与标签执行语义，不复制后台。当前模式之外的标签及真实根变量引用编译报错；`$field`、`$vo` 等局部变量不改名。EyouCMS 模式直接显示必填上传区域，不再有额外导入开关。切换为 Webman 模式清除文件及上传状态；服务端在数据库变更前拒绝伪造的导入参数。

站点模式以普通字符串保存于可信后台 `options` 的 `cms_template_mode`，值仅为 `webman` 或 `eyou`；不从上传 SQL 的站点配置决定该值。旧站缺少该配置时按 EyouCMS 模式解释。本轮安装后只读显示，重新选择模式通过显式重装完成。原版主题不做运行时改写。

## 新站安装

数据库设置按键解析：存在的 `.env` 项优先，其次是显式进程环境变量、`config/manifest/config.yaml` 的 database 字段、内置默认值。空密码、`false` 和 `0` 均是显式值。安装原子合并写入 YAML 的 database 区段，保留其他区段及私有前镜像；已有 `.env` 数据库项沿用安装同步规则，其他配置保持。未由本地文件覆盖的进程数据库项与表单冲突时须先修改启动环境。生产关闭调试及 SQL 日志；安装、启动均不需要 key 文件。

1. 安装 Composer 依赖并配置 Webman 监听。PHP ≥8.2，启用 PDO MySQL、mbstring、GD、pcntl、posix；安装阶段所需目录可写，预构建资源完整。安装完成后收回运行账号对源码和 `config` 及其父目录的写权限；runtime、上传及生成目录按业务授权。
2. 新包包含标准 `install/`，且没有 `install/install.lock`。启动后首页或后台页面自动进入 `/install/index.php`，`/install`、`/install/` 作为别名。页面请求进入向导，业务 POST/API 返回明确的未安装响应。
3. 填写数据库连接及**一个表前缀**，默认 `wa_`。CMS 表为 `wa_archives`，后台表为 `wa_backend_admins`，内部状态表为 `wa_cms_state_records`。不能单独设置两套前缀。
4. 选择模式和是否导入初始化数据，默认 Webman 空站。Webman 使用 `cms-empty.sql`；EyouCMS 必须提供完整 SQL，不勾选初始化时跳过源 INSERT，通过明确字段列表写入可信模型、字段、语言和基础配置。空站没有栏目、文章、产品、广告或留言等业务内容；站名、网址由安装者填写。
5. EyouCMS 模式上传一个完整 `.sql`，最大 50 MiB，采用 2 MiB 分片。源前缀自动识别，目标统一使用输入前缀。勾选初始化后保留原站数据、ID、关联及已有站名、网址和 SEO 配置，新管理员由安装者设置。“转换为 InnoDB”默认勾选；关闭时保留受支持的源 InnoDB/MyISAM，后台及内部表固定 InnoDB。选择或清除文件、切换模式或数据策略后均须重新预检。
6. 检查数据库，再做完整安装预检。核对目标表和冲突清单；存在冲突时按钮为“删除冲突表并重新安装”。仅提交当前清单对应的令牌才会开始安装。
7. 正式安装中显示全屏半透明蒙版，中央白色面板展示转圈与真实阶段/数量；禁止背景操作、滚动和 Esc 关闭。数据库、环境及安装锁完成后自动完整重启当前项目，蒙版保持并改为重启提示。成功页每秒检查服务，确认新主进程及新配置后关闭蒙版、启用“进入后台”。由安装者点击后，以本次创建的管理员建立会话并进入后台；不自动离开成功页。失败或超时关闭蒙版并显示恢复操作，重试时重新显示；数据库检查、预检和 SQL 上传保留页内展示。数据库完成而配置或锁未完成时，只补做收尾。

### 初始化数据（可选）

网站模式白卡内、下拉框下方仅提供一个“导入初始化数据”选项，默认不勾选；两种模式共用，切换模式保留选择。SQL 上传成功后仍可自由勾选，不会自动清空或禁用。CLI 的 `import_demo: true|false` 统一表示是否带数据：Webman 为内置示例，EyouCMS 为上传文件中的原站数据，省略即为 `false`。`import_sql` 表示使用上传文件，EyouCMS 必须为 true，Webman 必须为 false。

Webman 初始化数据为项目自有企业示例：10 个栏目、6 个产品、9 篇资讯及方案、3 个单页、6 条产品图库和3个留言字段。本地图片位于 `template/webman-default/assets/demo`，不请求外部图片服务；示例联系方式不代表真实服务信息，正式上线前请修改。EyouCMS 初始化不使用这些示例。

预检摘要展示 `empty`、`builtin_demo`、`source_schema` 或 `source_full` 对应策略。只导入结构时，预检检查可信种子所需字段、类型、长度、枚举和额外必填字段；无法容纳基础数据则在删表前拒绝。安装使用同一暂存导入、SHA 校验、精确发布和恢复流程。该选项不是已安装站点的批量添加按钮。

### EyouCMS 模板准备

将含 EyouCMS 标签的模板放入 `template/<主题目录>/pc` 或 `mobile`。至少有一个终端的 `index.htm` 并检测到 EyouCMS 标签；纯静态公共片段可以存在。共享目录、软链接、Webman、混合及未识别主题不列为候选。只检查安装必需首页与标签类型，不要求列表、详情模板齐全。

唯一候选自动使用并显示名称和目录；多套候选必须明确选择；无候选、未选择或缺 SQL 时，在对应控件旁显示红字并阻止预检。可用“重新检测模板”读取最新目录。服务端预检与正式安装都重新检查主题；文件变化会使旧计划失效。选中的 `theme` 写入站点及所有导入语言的 `web_tpl_theme`，不沿用源 SQL 中旧路径。

维护中的安装页面位于 `app/cms/install/view/index.html`，仅在标准 `install` 存在且未锁定时使用；它不开放新的安装地址、不扫描归档。封存的历史安装文件保持原字节。新包复制和核验核心 SQL 后，将配套版本的私有安装页复制至包内 `install/index.html`，安装入口仍由标准目录及 `install.lock` 控制。

管理员账号为 3～32 个 ASCII 字符，以字母开头，可含数字和下划线；密码为 8～72 字节，必须输入两次并完全一致，不裁剪空格。前后端均校验，确认值不保存，管理员只存密码哈希。数据库密码、管理员密码及确认框默认隐藏，可用右侧眼睛按钮单独显示／隐藏；切换不会改变输入值或提交安装。数据库连接值原子写入启动时选中的私有 YAML（默认三路径选一，无文件时创建 `config/manifest/config.yaml`），保留其他区段和前镜像。安装器不创建 `.env`；已有文件中存在的数据库连接、布局及前缀项在数据库完成后同步更新，Redis等其他配置保持。检查连接和预检均不改写文件；已有覆盖项需要更新时，文件及所在目录必须可写。未由本地文件覆盖的显式进程数据库变量若与表单冲突，须先修改启动环境并重启。不产生 `runtime/local-admin.json`，命令输出不返回密码。

前缀以字母开头，仅含字母、数字及下划线，最长 32 字符；完整目标表名仍受 MySQL 64 字节限制。空站基线为 157 张 CMS 表、7 张后台表、3 张业务状态表，共167张；导入的自定义表可能增加数量。**不创建或依赖 `cms_install_state`。**

## 主动重装和删除范围

安装入口只用标准安装目录和其中的文件锁关闭：标准 `install` 不存在，或 `install/install.lock` 是普通文件时，入口关闭且无需连接数据库；符号链接及异常路径类型拒绝处理。

需要主动重装时，先按封存记录和 SHA 核对并恢复为标准 `install/`，再明确删除 `install/install.lock`。此时允许进入向导，历史本地完成记录、旧数据库内容及旧归档记录不再充当第二把安装锁。已有可信未完成任务须使用同一数据库、模式、前缀、源 SQL 与引擎选项续跑，不能通过改参数接管该任务。

预检按**完整目标表名**计算冲突，只替换这次将创建的同名普通表。不按前缀批量删除，不清库，不删除其它前缀及同前缀无关表。修改前缀后无冲突的原表完整保留；部分冲突只替换重名部分。提交时重核结构、行数及内容摘要，出现变化即停止。涉及冲突表的外键依赖目前拒绝自动处理，须另行评估。

上传文件先解析与验证，忽略其中的 DROP、切库、建库及备份包装指令，不原样执行整份文件。只接受支持的 CREATE 与字面值 INSERT；检查核心字段、模型／语言／配置种子、行字段数量、保留表名与引擎。每张表先在本任务专属暂存表完整执行并校验，全部成功且原目标复核通过后才删除冲突表，再原子发布各表。导入语法、DDL 或数据失败不会先删原表。

DDL 无法承诺整体事务回滚。开始精确删除后中断，保留私有 journal 与暂存表，按同一任务续跑；不要手动清理暂存表或删除进度。模板、上传和已有 `public/html` 不随数据库重装删除。

## 命令行安装和预检

用公开目录外、权限 `0600` 的绝对路径 JSON 传递敏感配置。Webman 空站示例：

```json
{
  "host": "127.0.0.1",
  "port": 3306,
  "database": "webman_cms",
  "user": "cms_user",
  "password": "由部署者填写数据库密码",
  "db_prefix": "wa_",
  "compatibility": false,
  "import_sql": false,
  "import_demo": false,
  "convert_innodb": true,
  "admin_username": "admin",
  "admin_password": "由安装者设置8至72字节的密码",
  "admin_password_confirm": "由安装者设置8至72字节的密码",
  "site_name": "我的网站",
  "base_url": "https://example.com"
}
```

```sh
php webman cms:install
php webman cms:install --input=/private/path/install.json --check
php webman cms:install --input=/private/path/install.json --preflight
# 核对预检清单，将返回的 plan_token 写入同一私有 JSON，再执行：
php webman cms:install --input=/private/path/install.json
php webman restart -d
```

`--check` 检查连接及环境；`--preflight` 完整检查 SQL 和目标冲突，保存有时效的私有计划，不删除数据库表。正式安装没有有效 `plan_token` 会拒绝。CLI 的 EyouCMS 模式须设置 `compatibility: true`、`import_sql: true`、`import_demo: true|false` 和 `theme`；多候选必须填具体目录名。`sql_file` 须指向标准安装目录内已核验的私有 SQL，可提供 `sql_sha256` 校验值；不支持绕过预检从任意路径整份执行 SQL。临时凭据文件由部署者私密保管，完成后删除。

## 持久进度和收尾

`config/cms-install-plan.json` 保存预检令牌及精确冲突指纹，`config/cms-installing.json` 保存任务身份、阶段、暂存表和数据指纹。FORMAT3 任务绑定数据库、模式、数据策略、是否带数据、所选主题及其文件 SHA、前缀、源 SQL SHA、源前缀、引擎及基线版本；不保存管理员明文密码。文件权限为0600，不放 runtime。历史已完成记录转入私有 `config/cms-install-history/`，不静默丢弃。

旧 FORMAT2 未完成任务委托冻结的旧安装实现，保持原数据语义、签名和步骤；新页面禁止用新规则接管，须通过 CLI 使用原参数续装，不添加新 `theme`。已完成的旧任务不阻挡新版本安装。

执行阶段为 `loading → dropping → publishing → finalizing → database_complete → complete`。MyISAM 暂存写入中断后的校验不符时，仅重建本任务拥有的暂存表；已发布表按指纹核验，不重复插入。服务使用项目文件互斥及数据库命名锁，DDL 不等同整库事务。

管理员、角色绑定、`cms_template_mode` 和 `cms_site_signature` 在同一事务完成。`cms_site_signature` 是本次安装随机生成、journal 续装固定的普通字符串，用于阻止同库同前缀重装后复用旧站任务或生成结果；历史无值使用 legacy 回退。清空 runtime 不改变这两个配置。

数据库完成后依次原子保存 YAML 的 database 区段、本地完成记录、映射安装目录中的 `install.lock`，清理 SQL 上传暂存，再在同一父目录封存安装资料，最后将 journal 标为 complete。写 YAML 配置或锁失败且入口仍未关闭时，以同一表单/CLI参数补做收尾，不重新导入；锁已写入时用下面的封存命令。

```sh
php webman cms:install --seal --check
php webman cms:install --seal
```

显式封存核验实际核心结构、必要基础数据、后台管理员与前缀。存在本轮 journal 时还核对实际数据库中的模式及站点签名；环境仍指向旧库时拒绝封存，须先用原安装参数补写 YAML 配置。`--check` 不写锁、不改名；`--seal` 不得与 `--input` 或 `--preflight` 并用。数据库阶段未完成时拒绝提前封存。

安装完成同级改名为 `install_` 加12位随机小写字母。`config/cms-installer-archive.json` 保存唯一目标、完整文件 SHA/大小、目录清单和 prepared/complete 状态；中断沿用同一目标，不扫描随机目录，不覆盖目标或改写清单。改名不修改安装资料原字节。随机目录不是新安装网址，SQL 和安装资料仍禁止公开读取。

安装资料可删除，正常运行与 `cms:migrate` 不依赖归档。全新安装包须在独立打包副本恢复标准 `install`，核验完整清单后移除**副本**中的安装锁、源站凭据、进度及封存记录；不要为打包修改运行站。详见[源码包](source-package.md)。

## 维护命令和边界

| 命令 | 行为 |
| --- | --- |
| `cms:install --input=… --preflight` | 完整预检并输出精确替换清单和 plan_token |
| `cms:install --input=…` | 使用有效令牌执行同一可恢复安装服务 |
| `cms:install --seal --check` / `--seal` | 核验已完成数据库，预演或补做封存 |
| `cms:migrate --dry-run` / `--apply` | 检查或应用已知增量升级，保留业务数据 |
| `cms:database-init --dry-run` | 只读检查空站／后台／内部基线；依赖可验证安装资料 |
| `cms:database-init --apply` | 已禁用，提示改用带模式和冲突预检的 cms:install |
| `cms:sync-menu --dry-run` / `--apply --missing-only` | 检查或补缺菜单，保留原节点与授权 |
| `cms:state-migrate` | 旧运行状态审计／迁移，按专门方案执行 |
| `cms:cache-clear --all` | 全部 worker 停止后清理可重建 runtime |

旧 `eyoucms-1.7.7.sql` 及 `webman-admin.sql` 原字节保留；新装空站读取独立 `cms-empty.sql`、`cms-internal.sql` 与可信后台基线。原版识别、旧后台账号迁移和全部主题兼容并非本轮安装结果可以自动证明。后台仍由 `app/cms/admin` 自维护，不恢复 `plugin/admin` 实体。

## 历史验证记录（此前版本，不作为本轮重装规则）

以下保留原架构迁移阶段的事实；其中“双前缀、演示数据、未知库拒绝、删锁仍拒绝”的规则已被本页当前实现取代。对应证据见[原版安装逻辑验收](install-eyou-acceptance.md)、[安装封存验收](install-seal-acceptance.md)，不得将它们当作本轮双模式、冲突重装或真实浏览器验证结果。

### 原架构迁移验证记录（历史）

隔离数据库完成空站及演示安装、自定义双前缀、原密码哈希验证、未知已有库拒绝、重复安装拒绝以及丢失本地安装锁仍保持关闭的检查。

中断演练在完成 10 张表后终止安装进程，再以同一配置恢复，最终表数完整且管理员与角色绑定均只有一条。

完整 HTTP 副本在 `127.0.0.1:8789` 验证环境页面、CSRF、数据库检查、安装、服务重启、后台登录页面及空站首页；重复访问安装入口返回 403，重复安装提交返回 400，缺 CSRF 返回 403。

该副本停止全部 worker 后移除整个 `runtime`、`install`、`docs`，重新启动自动重建 `runtime`；PC 与手机首页及后台登录页均返回 200，安装入口仍关闭。此次 HTTP 检查不包含管理员完成验证码后的交互验收，也不替代原 EyouCMS 全标签兼容验证。

安装会初始化兼容版本标记；新站首次 `cms:cache-clear --all` 已验证成功。该命令仍须先停止全部 worker。




## 安装交接、自动重启与首次登录（2026-11-08）

`app/cms/install/InstallerHandoff` 管理本次任务的独立收尾状态；`app/cms/maintenance/install-restart.php` 是固定参数 CLI 执行器。只有数据库配置、完成记录和安装锁均已写入，才安排 `PHP_BINARY <当前项目>/webman restart -d`。执行器脱离 HTTP worker，清除旧数据库环境值并保留本实例监听地址和 worker 数；仅核验属于当前项目的主进程。浏览器关闭不取消任务。

`config/cms-install-handoff.json` 及同名前缀互斥文件属于私有持久资料，0600，不放 runtime。记录任务、站点签名、环境 SHA、管理员 ID/密码哈希指纹、浏览器 owner 哈希和执行状态；不保存明文密码。不能公开、提交或放入新安装包。安装进度、资格和数据库内容均不能通过重试收尾重新创建。

状态为 `scheduled → restarting → ready`，异常为 `failed`；无当前运行实例为 `not_running`，不主动启动，也不提供浏览器登录资格。配置或锁写入失败为 `finishing_required`，原表单的“重试安装收尾”仅重试完成阶段；封存单独失败仍允许重启并显示提醒。`--seal --check` 保持只读；实际 `--seal` 在核验同一已完成任务后补做封存并同步交接，不能补发旧浏览器资格。

浏览器通过精确任务接口 `GET /install/handoff/status`、`POST /install/handoff/retry` 和 `POST /install/handoff/login` 工作。后两个要求原安装会话与 CSRF；接口不接受任意命令、任意管理员或目标数据库。重新检查只查状态；失败重试核验原主进程与任务，重复请求不重复重启、不重导入。

ready 必须同时确认主进程更换、当前 HTTP worker 属于新主进程、加载的环境和后台数据库/前缀正确，并读取到本任务站点签名。页面等待超过60秒提示超时，可重新检查或在明确失败后重试；只有自动执行失败时显示手工 `php webman restart -d`。诊断日志在 `runtime/logs/installer-restart.log`。

首次登录资格绑定安装浏览器、任务、站点及新管理员。仅服务就绪后可用，有效期为 ready 后10分钟且不晚于安装完成后30分钟。兑换时重新核验账号状态与密码哈希指纹，复用 `AdminLoginSession` 更新登录时间和角色、保存并验证新会话、销毁旧会话，再消费资格。URL 不携带凭据。重复点击/响应丢失在同一已登录会话中恢复；成功页刷新仍有显式进入按钮。正常退出后不得重放，失效或账号变更提供正常登录入口。

停止服务并清空 runtime 会清除登录会话，须正常登录；不会重新生成一次性资格、重装或丢失持久任务状态。CLI 安装也共用重启协调，无浏览器 owner 时不产生可兑换资格。旧 FORMAT2 仍走冻结旧逻辑；旧 FORMAT3 已完成数据库记录缺少管理员 ID 时，仅在原用户名、密码、模式和站点签名全部匹配后补齐，不从新的浏览器补发 owner。

本轮证据和范围见 [安装交接验收](install-handoff-acceptance.md)。
