# 安装与部署

2026-11-10 统一系统资源已备份发布8787并完整停启：686个共享实体迁至public/admin-assets/common，template/_shared实体不存在；2132实体完整性、10656新旧URL隔离HTTP、三缓存模式/五主题及运行站只读复验通过。当前default/eyou/开发模式2、私有配置、业务、原会话代际及Git暂存保持。浏览器夹具同主机Cookie污染已恢复原有效会话，实际进入后台；准确范围和回退见[统一资源验收](unified-system-assets-acceptance.md)。

2026-11-10 系统资源统一：公开系统资源统一在 `public/admin-assets`，共享资源迁至 `public/admin-assets/common`；`template` 仅存主题，禁止创建 `_shared` 实体或软链接。全部登记旧地址保留逐文件别名，动态语言 JS 继续使用文件／Redis 派生缓存，不写公开目录。format=2 保持，当前 revision 为 `2026-11-10-unified-system-assets-v1`；配套包、隔离验收和发布状态见[统一资源验收](unified-system-assets-acceptance.md)。本段取代下方历史共享资源布局与旧配套包约定。

2026-11-07 资源整理：后台发布资源集中于 `public/admin-assets`，前台共用资源在 `template/_shared/assets`；私有配置、字体和源码不公开。旧资源地址按 format=2 清单兼容。当前构建／安装／恢复流程见[资源维护](resource-maintenance.md)，历史 format=1 包只用于外部回退。

2026-11-07：开发站 8787 已完成本轮停机迁移并重启，新架构已运行。旧状态已逐项核验入库，整个 runtime 已清除并自动重建；8793 保留副本未改动、未同步。迁移后 HTTP 复查与当前管理员登录后的主要页面读取、筛选和打开／返回验收已通过。本轮架构迁移验收完成；未扩大为全部角色、业务写入或 EyouCMS 严格兼容验收。

## 环境和运行文件

使用 PHP CLI ≥ 8.2 与 composer.lock，Webman 需要 pcntl、posix，MySQL 需要 pdo_mysql；安装向导还检查 mbstring、gd 和目录写权限。构建、资源维护及部署预检均使用PHP，不需要外部脚本解释器。运行不依赖相邻 EyouCMS 源码。

```sh
PHP_BIN=/Users/lizheng/Library/FlyEnv/env/php/bin/php
COMPOSER_BIN=/absolute/path/to/composer.phar
"$PHP_BIN" "$COMPOSER_BIN" install --no-dev --prefer-dist
"$PHP_BIN" "$COMPOSER_BIN" check-platform-reqs --no-dev
```

其它机器替换路径。Composer 和 worker 使用同一 PHP。保留 app、config、plugin、support、vendor、template、public、启动入口和 Composer 文件；新站另带 install。docs 为可选开发文档，`.git` 为受保护的版本库。不要直接修改 vendor；Composer 发布 hook 的定制保护不代替依赖升级审查。

数据库正式配置保存在 `config/database.php` 的固定受管块，包含敏感信息，仅授权部署账号和服务账号读取。配置按每个键依次使用：存在的 `.env` 项、显式进程环境变量、PHP 正式值；空值、`false` 和 `0` 是显式值。安装器不创建 `.env`；已有文件中存在的数据库连接、布局及前缀项在数据库完成后按本次表单参数同步更新，Redis等其他配置保持。检查连接和预检均不改写文件；已有覆盖项需要更新时，文件及所在目录必须可写。未由本地文件覆盖的显式进程数据库变量若与表单冲突，须先修改启动环境并重启。部署前检查已有覆盖项与预期连接及表前缀一致；不应使用示例文件覆盖已有站点配置。现行配置统一保存于 `config/manifest/config.yaml`，`.env` 优先逐字段覆盖；安装、启动和构建不依赖本地 key 文件。调试与 SQL 日志默认关闭。

安装时对配置文件和父目录临时授予必要写权限；安装完成后撤销服务账号对源码、配置文件及其父目录的写权限，避免目录重命名替换配置。服务账号仅按业务需要写 runtime、uploads 和 public/html。新站使用同一个 CMS／后台表前缀，默认 `wa_`；历史分离前缀站点保留其既有配置。

## 新站安装

1. 启动 Webman，访问 `/install`，完成环境检查和数据库连接。
2. 设置统一表前缀；默认空站，只有明确勾选才导入演示内容。
3. 设置网站名称、根网址及管理员账号／密码。密码为8至72字节，由安装者自行保管，数据库只存哈希。
4. 完成后按提示重启 worker，再访问 `/app/admin`。

安装预检列明同前缀冲突表，只有显式确认且重新核验后才处理这些表；升级走迁移命令。安装在 config 记录所属目标与逐表进度，数据库记录安装检查点；只有同一未完成安装可安全续跑。DDL可能隐式提交，不承诺整个安装事务回滚。安装失败应保留日志和所属记录，使用相同数据库、前缀、演示选项及已设置密码重试，不能删记录伪装空库。

完成标记为数据库 `<CMS前缀>cms_install_state` 和 `config/cms-installed.json`。只删本地锁、删 runtime 或数据库连接暂不可用，均不会开放已有站覆盖安装。完成后可删除 install 和 docs；后续 `cms:migrate` 使用代码内迁移，不读取初始 SQL。需要再次新建站时应使用新的代码副本和空库。

CLI 可检查安装状态；自动部署可使用权限0600的私有 JSON 输入，字段与向导一致，输入文件由操作者妥善清除：

```sh
"$PHP_BIN" webman cms:install
"$PHP_BIN" webman cms:install --input=/absolute/private/new-site.json --check
"$PHP_BIN" webman cms:install --input=/absolute/private/new-site.json
```

## 旧站状态迁移与切换

本轮已先完成隔离演练，再对8787停机迁移并重启；不重导业务基线、不改原业务ID、不重置密码。8793保留副本未参与。以下为同类站点的迁移操作顺序。

1. 在应用外保存数据库一致备份、当前源码和私有配置、旧runtime持久状态、主题、上传与公开生成实体。保存记录数、业务标识和文件SHA清单；不要将备份放runtime或public。
2. 核对目标配置、现有 `.env`／进程覆盖项及前缀。在隔离副本预演以下命令，检查迁移范围、UI冲突及来源SHA。
3. 开发站切换时停止全部worker、定时器和其他数据库写入者；核验没有残留进程，再取最终备份与预演。
4. 应用增量结构、旧状态迁移并登记已有站，核对全部来源已导入、数据库记录／对象hash、公开文件以及未改变的账号和业务数据。
5. 验证新站后才删除旧路径、临时迁移来源及可重建runtime。保留外部回退资料，重启并复核前后台与生成状态。

```sh
"$PHP_BIN" webman cms:migrate --dry-run
"$PHP_BIN" webman cms:state-migrate --source=/absolute/private/legacy-runtime
# 以下仅在已停机并核验目标后执行
"$PHP_BIN" webman cms:migrate --apply
"$PHP_BIN" webman cms:state-migrate --source=/absolute/private/legacy-runtime --apply --register-installed
"$PHP_BIN" webman cms:state-migrate --source=/absolute/private/legacy-runtime
```

`cms:state-migrate` 不删除源文件。相同来源重复执行跳过；已迁移来源字节变化会拒绝覆盖数据库。旧可视化文件独有值和冲突按原文件优先行为入库，差异及覆盖前值保留在数据库。源快照、manifest与对象不一致时停止，不以自动补文件掩盖问题。

本轮8787迁移回退快照保存在应用外`/Volumes/disk/site/php/dzhcms/webman-cms-preserved-20261107-v1/architecture-refactor-20261107-v1/live-cutover`，含私有配置与数据库资料，不可公开。

## 整删 runtime

**此流程只适用于已完成上述迁移的新架构。** 旧部署不能在迁移前直接整删runtime。

```sh
"$PHP_BIN" start.php stop
# 确认全部 worker 及其他任务进程已停止
"$PHP_BIN" webman cms:cache-clear --all
"$PHP_BIN" start.php start
```

不带 `--all` 时仅清数据／模板／视图缓存；带 `--all` 清整个runtime。工具检查本部署PID和遗留持久状态，但不能代替操作者确认所有外部进程停机。启动和首次访问幂等重建目录；需要重新登录，旧日志不恢复。数据库内任务、manifest、SHA对象、归属、可视化和限流状态保留；public/html、模板和附件不动，不要求重新生成静态页面。

## 主题、上传和代理

主题为`template/<theme>/pc|mobile`，数据库web_tpl_theme优先于CMS_THEME。迁移主题及皮肤原字节，不跨主题静默补页。上传实体固定为根目录 uploads，旧/upload、/uploads及/public别名由兼容层保留，不改数据库URL。迁移同名资源先核对SHA，不静默覆盖；不创建根目录upload或public/upload。

内置HTM在私有app/view/cms/system。模板源、PHP、配置、docs、安装SQL及runtime不可公开。公开生成HTML固定在public/html，URL的/html显示开关不改变实体目录，手机文件在__mobile。

受管HTML须回Webman检查状态、模式、设备、签名和hash。以下为全量代理示例，实际替换端口、TLS和部署前缀；本轮未实测Nginx：

```nginx
location / {
    proxy_pass http://127.0.0.1:8787;
    proxy_set_header Host $http_host;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
}
```

不能用try_files直接输出受管HTML，也不承诺整站脱离Webman运行。备份和迁移应将数据库与公开实体保持一致；只复制public不能恢复发布归属和任务。外来、人工修改文件继续受hash保护。

## 回退与独立部署工具

切换前保留完整外部快照。需要回退时先停新worker及写入，按同一快照恢复原代码／配置、数据库、旧持久runtime和匹配public/html，再核对记录数与SHA并启动原版本；不要将旧runtime整体覆盖到仍运行的新版本。切换后若已产生新业务写入，先备份并评估差异，不能直接用旧库回滚丢失用户数据。

部署预检使用PHP命令，不访问数据库、不创建副本、不启动服务或占用端口：

```sh
php webman cms:deployment --check --json
```

它汇总统一配置校验、目录权限、PHP运行时和资源完整性检查。旧固定8793的自动演练及清理入口已退役，不接管历史保留环境。新安装、二进制及独立部署按[当前部署说明](binary-deployment.md)操作。

保留8793位于`/Volumes/disk/site/php/dzhcms/webman-cms-preserved-20261107-v1/state/runtime`，当前未同步。其历史manager已有SHA基线失配，不能视作可用入口或擅补SHA；详见[维护说明](maintenance-archive.md)。
