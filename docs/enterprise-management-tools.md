# 企业站图片、robots、城市与数据库维护

本文描述本轮维护工具的使用约定。代码已于2026-11-09交付8787，匿名入口、资源及用户正常登录后的只读页面复验通过；隔离浏览器、HTTP和数据库证据分层记录，具体范围见 enterprise-completion-acceptance.md。本文不表示 EyouCMS 严格兼容全部完成。原 EyouCMS 和 8793 不在本轮修改范围。

## 图片设置与新上传处理

入口为“站点设置 → 基本设置 → 图片设置”。后台沿用原 `water`、`thumb` 配置键，不新增另一套图片配置。配置由系统主语言提供；主语言按最早语言 ID 确定，不固定为 `cn`。共享字段只能在主语言、具有全部受影响语言范围及原独立保存权限的情况下修改，保存同步到现有语言。

| 设置 | 原配置键 | 作用 |
| --- | --- | --- |
| 新上传图片水印、类型 | `is_mark`、`mark_type` | 文字或本站图片水印 |
| 最小宽高 | `mark_width`、`mark_height` | 处理后图片宽、高都严格超过阈值才加水印 |
| 水印文字、字号、颜色 | `mark_txt`、`mark_txt_size`、`mark_txt_color` | 使用项目私有字体；颜色为六位十六进制 |
| 本地水印图片 | `mark_img` | 已有上传文件，不下载外站文件 |
| 不透明度、JPEG 质量、位置 | `mark_degree`、`mark_quality`、`mark_sel` | 0～100%、1～100、九宫格位置 |
| 模板缩略图水印 | `is_thumb_mark` | 新生成缩略图是否使用水印 |
| 缩略图默认规则 | `thumb_open`、`thumb_width`、`thumb_height`、`thumb_mode`、`thumb_color` | 原模板缩略图函数的默认值，模板显式参数优先 |

处理范围为后台本次新上传的图像及图像附件，不批量修改旧上传、不处理头像。静态上传原图保留既有 1170×1170 等比边界，不能把缩略图默认 300×300 理解为上传原图尺寸。模板缩略图配置单独生效，未知旧缩略图处理方式不会阻断普通上传。

GIF、APNG 和动画 WebP 走原字节保留路径；当前 GIF 均保留，不拆帧。文字水印保留 PNG 透明度；透明原图遇到图片水印时跳过合成，返回 `transparency_preserved`，避免把透明底压成实色。当前水印输出支持 JPEG、PNG、WebP、BMP；其他允许上传的格式保留并返回处理原因。JPEG 处理保留 EXIF。上传仍先执行实际图片解码、格式及既有接收大小检查。

图片水印只允许本站 `public/uploads` 内普通本地文件，兼容旧上传 URL 映射；拒绝网络 URL、越界和链接路径，图片限 JPEG/PNG/WebP/GIF，宽高不超过 2000px。文字水印依赖 GD 的字体支持和 `app/cms/resources/fonts/hgzb.ttf`，字体不改字节。配置页不替运维自动安装扩展或转换表引擎；多语言图片设置保存要求 `config`、`language` 为 InnoDB。

缩略图文件名加入原图 SHA 与处理配方签名。原图、尺寸、水印或相关规则变化后生成新 URL，旧衍生文件保留。新旧缓存可并存，不能手工批量删除上传目录来“刷新缩略图”。

恢复方式：保存前记录原配置，失败时输入保留；重新保存原配置只影响之后的处理。水印已写入本次上传文件，关闭开关不会逆向移除，需使用独立保存的原图重新上传。没有自动恢复已有图片原字节的承诺。上传处理失败只清理本次失败上传，不删除既有文件。

实现：`BasicSettingsService`、`ImageUploadPolicyService`、`UploadedImageService`、后台 `UploadController` 与 `support/upstream/common.php` 的 `thumb_img()`。

隔离后台已实际保存图片配置；随后通过已认证的真实 HTTP multipart 分别调用图像附件 `insert` 和 `image` 上传接口，25 项断言通过，包含水印局部像素变化、响应大小与实际文件一致、附件记录／图像尺寸、公开 URL 原字节读取及测试文件清理。原图片配置和附件记录已精确恢复，本轮操作日志保留。该 HTTP 验证使用外部 CLI 按普通会话流程建立测试会话，不是浏览器文件选择器上传。

Chrome 扩展未允许本地文件 URL，浏览器 filechooser 验证被阻断；没有通过修改权限或替代操作把它记为完整浏览器上传通过。该缺项与已通过的图片设置保存、HTTP multipart 结果分别记录。

## robots.txt 编辑与版本恢复

候选页面 `/app/admin/robots` 管理唯一的 `public/robots.txt`，不接受任意文件路径。页面接入 SEO 读取及独立保存权限，写入还检查完整语言范围、同源与 CSRF；只读角色不能通过接口保存或恢复。导航最终位置以主集成结果为准。

读取返回内容、SHA 与原文件是否存在。保存时一并提交，服务端在互斥锁内再次核对；文件被另一管理员或命令行修改后，本次保存拒绝覆盖，页面保留输入供人工比较。文本限制为 256KiB UTF-8，拒绝控制字符、符号链接、硬链接及异常文件类型。

每次实际变更先在数据库持久状态 `cms_state_records` 的 `robots-revisions` 范围写入 `prepared` 回执，包含私有前镜像；文件发布完成后标记 `complete`。不把旧内容备份写入公开目录。公开目录仅可能短暂出现不可预测的 `.robots-*` 新内容发布临时文件；HTTP 拒绝点目录／点文件仍需由集成验收确认，代理也应遵循现有静态访问约束。

页面列出最近 30 条回执。恢复要求：

1. 版本是已完成回执。
2. 当前文件内容及存在状态仍等于该版本发布后的状态。
3. 当前客户端提交的 SHA、存在状态也一致。

满足时恢复该次变更之前的内容，并新建恢复回执；如果原来没有 robots.txt，则恢复到“不存在”。不能用旧版本按钮覆盖之后的修改。操作期间发生普通回执写入失败时，仅在当前文件仍属于本次发布版本的情况下补偿原文件；检测到后续改动就停止并报告。

进程中断留下 `prepared` 回执时不自动猜测文件状态。先保存当前文件与回执的外部快照，核对前后 SHA、存在状态和运维操作记录，再由维护人员处理；不能把未完成回执手工改成完成来绕过检查。清除 runtime 不会丢失回执，但本地文件和数据库均需纳入独立备份。

实现：`RobotsService`、具名 `RobotsController`、旧命名空间薄类和 `admin/view/cms/robots.html`。

隔离浏览器已经验证实际保存、过期 SHA 冲突拒绝以及通过历史版本恢复原文件字节。冲突状态和恢复后的手机页面截图分别保留在项目外。此闭环不替代全部角色、所有进程中断点或代理公开文件保护的验收。

## 城市分站、系统主语言与多语言互斥

城市设置沿用 `web_citysite_open`、`site_template`、`site_default_home`、`site_seoset` 及三个 SEO 配置键，统一从系统主语言读取。前台上下文也采用这组全站城市配置。前台默认语言或后台编辑语言变化不改变系统主语言。

多语言总开关仍位于功能地图搜索下方；城市开关仍在城市分站设置，两者不合并成新开关。服务端使用同一数据库互斥锁保护两个方向：城市开启时拒绝再开多语言，多语言开启时拒绝再开城市。允许显式关闭任一功能。城市开启还要求至少保留一个启用地区。

历史库可能已经同时开启两者。读取页面不自动改配置、不关闭某一开关；页面显示冲突，运行路由在多语言开启时不启用城市选择。管理员先确认使用的模式，再显式关闭一个开关。不要通过修改数据库两个值绕过互斥。

旧站城市配置如果写在 `cn`，而系统主语言是其他语言，可使用城市设置中的“旧 cn 配置迁移”差异预览。迁移仅补齐主语言缺失键，保留原 `cn` 行。相同值保留，重复配置、已删除目标或不同值冲突会拒绝整批迁移。预览绑定原记录指纹，提交时重查；预览后有变更必须重新预览。它不复制城市、文档、语言包，也不按标题猜测关联。

显式迁移要求 `language`、`config`、`cms_state_records` 为 InnoDB，不自动转引擎。迁移收据保存在持久状态 `city-settings-migration`。没有自动撤销按钮；需要回退时根据外部快照与回执逐项比较，只撤销仍等于本次迁移值的新增目标记录，不能覆盖迁移后的人工修改，原 `cn` 来源保留。

实现：`SiteModulePolicy`、`CitysiteService`、`CitysiteSettingsMigration`、`LanguagePolicy`、`CmsContext`、`CityContext` 及城市页面。

## 终端别名

`DeviceSelector` 统一解释 `goto=pc`、`goto=m`、`goto=mobile`；`m` 与 `mobile` 均表示手机端，支持大小写和首尾斜线／空白归一。数组、未知字符串等不强制选择终端，继续按原终端策略判断。

显式 `pc` 优先于手机域名或路径的默认推断。旧 URL 辅助函数内部需要 `m` 时，由 `legacyGoto()` 转换；不修改模板文件、已有数据库 URL 或已有生成 HTML。终端选择结果仍需与站点、模式、主题和语言共同隔离缓存。

此归一化是本项目保留的输入规则，不代表原版所有非常规值都相同。当前原 PHP7 与候选 PHP8 路由方法复核也记录了 hostname 大小写、IP、单段域名及 IPv6 防护差异；不要把普通域名有限匹配解释成任意 URL 完全兼容。

## 本地数据库备份与恢复

命令为 `php webman cms:database-backup`，共用 `DatabaseBackup` 服务。隔离后台已通过“167 表、7769 行备份 → 完整差异预检 → 确认恢复完成”的实际浏览器闭环，手机完成状态截图已保存；运行库没有执行恢复，8787发布另见主验收记录。HTTP 操作仅允许拥有全部数据／语言范围的超级管理员；保存和恢复需要同源、CSRF 及明确确认。CLI 依赖运行命令的系统账号权限。

这是本项目本地认证备份格式，不是 EyouCMS SQL 导入、通用 `.sql` 执行器、跨项目迁站格式或文件备份。恢复仅接受完整本地包，核验当前项目目录、数据库／服务器及表前缀身份。创建时纳入当前数据库中匹配 CMS／后台实际前缀的所有实体表，并非只备份后台当前可见的内容模块；同前缀历史表也可能属于备份范围。运行前应核对这组前缀实际覆盖的表。其他数据库、其他前缀、视图及服务端账号／权限不属于备份范围。站点原主题、上传文件、生成 HTML、环境文件及安装资料不包含在数据库包中，须独立备份。

### 私有目录与密钥

`CMS_BACKUP_PATH` 可配置项目外绝对私有目录；未设置时使用项目父目录下带项目路径摘要的专属目录。目录不得解析回项目内，不使用公开下载入口。目录权限 0700、包文件 0600；`.key` 是 32 字节本地随机密钥，用于清单和任务 HMAC 校验。

迁移或备份私有目录时必须一并保存 `.key`，单独复制分卷不足以通过认证。不要把密钥、包、任务记录纳入公开资源恢复包或版本库。该格式提供完整性认证，不加密：行内容使用 Base64 编码，获得私有包仍可读取业务数据。磁盘加密、系统备份权限及密钥副本由运维管理。

首版仅支持站点前缀范围内的 InnoDB 实体表，拒绝视图、触发器、外键（包括其他表的入向外键）、特殊生成列／检查约束／分区等未支持结构。单条编码记录上限 16MiB，按数量和字节量分卷。拒绝的情况不能以跳过表冒充完整备份。

### 外键字典与资源限制

入向外键核验按服务器版本读取 InnoDB 字典：MySQL 5.7 使用 `information_schema.INNODB_SYS_FOREIGN`，MySQL 8.x 使用 `information_schema.INNODB_FOREIGN`。按当前数据库的精确 `REF_NAME` 前缀筛选，转义 LIKE 中的下划线等通配字符，再校验库名／表名及 `lower_case_table_names`，因此也检查其他库指向本次目标表的外键。无法安全映射的名称、未知服务器版本或缺少字典读取所需 `PROCESS` 权限都会拒绝；不得遇到权限错误后回退为泛扫 `KEY_COLUMN_USAGE` 或假定“没有外键”。工具不自动授予 `PROCESS`。MySQL 8.x 目前只有代码分支，尚未实际运行验证，不作为已验环境；MariaDB 等其他版本不能冒称已支持。

当前实现会把单张表的查询结果取入 PHP 内存，并在备份／指纹核验时保留逐行摘要数组。分卷写入不等于数据库游标流式读取，16MiB 单行限制也不是单表或全任务内存上限。首轮真实测试规模为 167 表、7769 行，不能推导大型内容库也适用。大表、超长正文／BLOB 或内存紧张环境应先在独立且有限额的容器测量峰值内存与时间；没有这组容量证据时不要在共享数据库服务器上直接运行全库测试。操作需要的数据库暂存、前镜像及磁盘空间也应单独估算。

### 命令顺序

下列 `<备份ID>`、`<任务token>` 必须替换为前一步实际返回的编号；不要复制占位符直接执行。

```sh
php webman cms:database-backup list
php webman cms:database-backup create
php webman cms:database-backup preview --id=<备份ID>
php webman cms:database-backup restore --id=<任务token> --apply
php webman cms:database-backup status --id=<任务token>
```

预检会创建私有任务、列出每张表的当前和目标结构／行数／数据摘要；它不替换业务表。新预检有效期 30 分钟。确认前核对数据库和完整目标表清单；非备份范围的表不会被批量删除。预检后目标有变化，恢复拒绝覆盖并要求重新预检。

恢复先在任务所属暂存表载入并核验分卷，完成后进入维护状态，再获取发布排他锁，复核目标指纹并原子改名发布。原目标保留为任务所属 `cmsrestore_<任务前缀>_bN` 前镜像表，归属、恢复阶段保存在私有签名任务；这些不是可公开访问的备份文件，不应由普通业务接口编辑。DDL 不宣称事务回滚。

恢复前必须停止外部 CLI、定时任务、手工 SQL 和其他绕过项目发布锁的数据库写入，也不要并发改表。HTTP／已接入后台任务的共享锁和维护状态只能协调参与该协议的请求，不能阻止任意外部 MySQL 客户端写入。执行前保留独立备份并确认磁盘与数据库空间足够容纳暂存表和前镜像。

备份使用 InnoDB 一致性读，但不应与外部 DDL 并发；恢复预检到发布期间尤其要停止外部 writer。目标摘要变化后工具拒绝替换，不代表它已经冻结了整个 MySQL 实例。外部工具若不遵守停写窗口，仍可能使恢复无法继续，不能用不断重新预检代替停写。

### 中断与回退

中断后先查询原任务 `status`，保留原 token、分卷、签名清单、暂存表和前镜像。恢复阶段仅重试原 `restore --id=<任务token> --apply`，不要删除维护标识强行开放请求，不要手工修改阶段或复用另一任务的表。

已经发布且没有后续编辑时，可预演回退，再明确执行：

```sh
php webman cms:database-backup rollback --id=<任务token>
php webman cms:database-backup rollback --id=<任务token> --apply
```

回退再次核对当前发布数据、前镜像及暂存表归属；恢复后存在人工修改就拒绝覆盖。不能承诺“随时一键回退”并抹去后续数据。完成与已回退任务重复执行仅补清理缓存／维护收尾，不重新导入。主进程、缓存和站点文件是否需要额外运维操作，以主集成验收与实际错误提示为准。

## 证据分层与当前边界

本轮 system 验证资料位于项目外 `enterprise-completion-20261108-222026/verification/system`，浏览器／HTTP 资料位于同级 `verification/browser`，不打入运行目录；以下计数是有限断言，不是兼容率：

| 证据 | 已观察内容 | 不代表 |
| --- | --- | --- |
| `pure-results.json`，32 项 | 设备归一、模板源码策略、城市迁移计划 | 全应用 HTTP／浏览器通过 |
| `db-results.json`，18 项 | 独立测试库，非 cn 主语言、双向互斥、迁移保护、robots 发布保护；回滚摘要一致 | 生产库业务修改或全部角色验收 |
| `image-results.json`，19 项 | 真实图像、透明度、动画帧／延时、1170 边界、EXIF 与输入拒绝 | 所有格式、所有图片及浏览器上传穷举 |
| `thumbnail-results.json`，5 项 | 实际辅助函数的尺寸优先、新旧 URL 与原图 SHA | 所有原主题都已渲染 |
| `final-db-results.json` | 第二轮 12 项图片配置／robots 恢复行为通过 | 最后一项“全部数据库回滚”断言通过 |
| `final-db-diagnostic.json` | 配置／语言摘要不变；唯一残留是独立 PDO 新建的空 `generated:public-static/manifest.json` 状态行 | 所有持久状态都在 ThinkORM 外层事务内 |
| `t16-route-results.json`，36 例 | 实际原 Request/PHP7 与候选/PHP8 的有限方法比较；24 输出、32 诊断匹配，差异完整列出 | 原版完整 HTTP、历史 65 例城市 registry 或 T16 总门槛关闭 |
| `php-lint-final.json`、`ui-syntax.json` | 本分工 19 个 PHP 文件语法与两个页面内联 JS 语法检查 | 真实浏览器布局、权限与交互通过 |
| `browser/backup-mobile-complete.png` | 隔离浏览器完成 167 表／7769 行备份、预检、恢复闭环后的手机状态 | 原开发库恢复或 8787 已交付 |
| `browser/robots-conflict-mobile.png`、`browser/robots-restored-mobile.png` | 隔离浏览器 SHA 冲突拒绝和版本恢复原字节 | 所有角色、所有崩溃点及反向代理保护通过 |
| `browser/image-upload-http-results.json`，25 项 | 图片配置已保存；已认证真实 multipart 两条上传链、水印与记录／文件核验、精确清理 | 浏览器 filechooser 上传通过 |

第二轮全库回滚断言失败原报告保留：`PublicStaticPublisher` 使用独立 `StateStore` PDO，空 manifest 写入不随测试外层 ThinkORM 回滚；未见测试图片配置或 robots 回执残留，没有删除该行掩盖差异。城市／robots 持久状态与公开文件仍需分别备份和核验。

### 共享 MySQL 事件及隔离措施

2026-11-08 本轮早期外键核验曾对 `KEY_COLUMN_USAGE` 作泛扫描，导致共享 `mysql57` 容器 OOM。主 agent 重启原容器后，对原开发库的 167 张表逐项只读复核：166 张表的原读取顺序摘要一致；`wa_users_level` 的 3 行仅返回顺序变化，按主键排序后的全部字段逐行完全一致。该事件属于实际环境故障，不能因数据复核一致而省略，也不能写成“生产写入验收通过”或“全库事务回滚成功”。

事件后的全部写测试迁至独立、带资源限额的 MySQL 5.7 容器 `cms-enterprise-mysql57-20261108`，端点 `127.0.0.1:13319`；测试库仍是本轮专用 `cms_enterprise_20261108_222026`。专用容器资源限额现已调整为 768MiB 内存、Docker `memory-swap` 上限 1GiB；这是本轮隔离配置，不是对生产库容量的保证。共享原服务器不再承担后续写入测试。代码改为前述精确 InnoDB 外键字典核验，保留无 `PROCESS` 权限时失败拒绝的真实验证，不继续使用泛扫描。

事件与后续测试证据均保存在项目外：

- [原库 167 表复核](</Volumes/disk/site/php/dzhcms/enterprise-completion-20261108-222026/verification/source-recovery.json>)记录 166 张原顺序摘要一致及唯一表差异；[users_level 排序逐行复核](</Volumes/disk/site/php/dzhcms/enterprise-completion-20261108-222026/verification/source-users-level-recovery.json>)确认三行全部字段一致。
- [MySQL 5.7 字典验证](</Volumes/disk/site/php/dzhcms/enterprise-completion-20261108-222026/verification/backup-dictionary-results.json>)的 5 项包含跨库入向外键、阻止替换、缺少 `PROCESS` 拒绝以及字典刷新；不含 MySQL 8.x 实测。
- [独立容器备份／恢复检查](</Volumes/disk/site/php/dzhcms/enterprise-completion-20261108-222026/verification/backup-db-results.json>)记录 15 项、167 表／7769 行的创建、差异、完整恢复、精确回退、损坏与后续改动拒绝；[中断恢复检查](</Volumes/disk/site/php/dzhcms/enterprise-completion-20261108-222026/verification/backup-interrupt-results.json>)另记录 13 项。这些是独立测试库的有限服务行为，不是原库实际恢复，也不是任意规模容量证明。
- [隔离浏览器备份恢复完成](</Volumes/disk/site/php/dzhcms/enterprise-completion-20261108-222026/verification/browser/backup-mobile-complete.png>)、[robots 冲突拒绝](</Volumes/disk/site/php/dzhcms/enterprise-completion-20261108-222026/verification/browser/robots-conflict-mobile.png>)及[恢复原字节后的页面](</Volumes/disk/site/php/dzhcms/enterprise-completion-20261108-222026/verification/browser/robots-restored-mobile.png>)是实际页面观察；不把截图单独当作全部数据摘要或全部权限证明。
- [真实 multipart 水印验证](</Volumes/disk/site/php/dzhcms/enterprise-completion-20261108-222026/verification/browser/image-upload-http-results.json>)保留 25 项断言、两条上传响应、输出 SHA、局部水印变化及精确清理。报告明确记录 Chrome 扩展本地文件 URL 权限导致 filechooser 阻断，不能据此宣称完整浏览器上传闭环。

未在上文列明通过的维护门禁、全部权限、资源发布、冷启动、两种模板模式及浏览器桌面／手机覆盖仍以主验收记录为准。MySQL 8.x 尚未实测；8787代码已交付并完成用户登录后只读复验，没有在运行库执行恢复。原有服务级失败报告、共享 MySQL 故障和浏览器上传阻断均保留，不能用后续通过项覆盖。
