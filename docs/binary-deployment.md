# 二进制构建与部署

实体目录已改为入口目录下的固定结构，取消 paths 配置。变更及实际验证见[固定实体目录](fixed-directory-layout.md)。

源码版保留网页安装。二进制仅部署已安装站点，关闭所有网页、CLI 安装及安装交接入口，不带 SQL、安装页面或新安装资料。运行、后台重启和维护不依赖服务器的 PHP、Composer、Node、Python 或本地 key。

## 构建参数

在具备 Composer 锁定依赖及 PHP 扩展的源码目录执行：

```sh
php webman cms:build-bin
```

默认配置如下；相对路径均相对源码入口目录，不依赖当前工作目录。修改 output 不会改变 build-runtime。

```yaml
build:
  output: bin
  binary_name: cms
  target: []
  build-runtime: bin/build-runtime
  embedded_resources: []
```

列表的行内数组和分行写法等价，也可混用：

```yaml
build:
  output: /srv/releases
  target: [linux-aarch64, linux-x86_64]
  build-runtime: bin/build-runtime
  embedded_resources:
    - public
    - template
    - config/manifest/config.yaml
```

支持 linux-aarch64、linux-x86_64、darwin-aarch64、darwin-x86_64。空 target 按实际构建进程操作系统和 CPU 选择；macOS ARM64 为 darwin-aarch64，ARM64 Linux 容器为 linux-aarch64。target 兼容单个平台字符串。output、target、build-runtime 缺省、null 或空字符串采用默认值；embedded_resources 缺省或 null 视为 []，非空必须为字符串列表。

参数覆盖顺序为 CLI、环境、所选 YAML、默认值。CLI 支持 --output、--binary-name、重复 --target、--build-runtime、--embedded-resources（JSON 数组）及旧 --runtime（锁定 micro.sfx 文件，仅单平台）。环境变量为 CMS_BUILD_OUTPUT、CMS_BUILD_BINARY_NAME、CMS_BUILD_TARGET、CMS_BUILD_RUNTIME、CMS_BUILD_EMBEDDED_RESOURCES，列表环境变量须为 JSON 数组。

`binary_name` 控制生成的可执行文件名，默认 `cms`，缺省、null 或空字符串均使用默认值；英文字母开头，1～64 位，仅含字母、数字、点、下划线或短横线。拒绝路径和配置、资源目录及发布资料保留名称。平台目录保持 `cms-<平台>`。例如 `binary_name: dzhcms` 生成 `cms-linux-x86_64/dzhcms`，部署启动使用 `./dzhcms start -d`。名称纳入构建身份、内置及外部发布摘要，更新名称后旧文件随平台目录替换清理。

```sh
php webman cms:build-bin --target=darwin-aarch64 --target=linux-aarch64
php webman cms:build-bin --embedded-resources='["public","template"]'
php webman cms:build-bin --binary-name=dzhcms
```

运行时按平台缓存于 build-runtime/<平台>/。校验已有 micro；缺少时从 binary-runtime.catalog.json 锁定的 HTTPS 成品来源下载并核对归档与运行时 SHA256、平台、PHP、扩展及 INI。下载失败明确停止，不使用普通 PHP 替代 micro，不自动安装宿主机软件。已验证缓存可离线复用；旧版源码构建记录仍保留作历史追溯，不自动漂移其摘要。

平台输出使用固定目录，重复构建自动整体替换对应平台产物：

```text
bin/
├── build-runtime/<平台>/micro.sfx
├── cms-darwin-aarch64/cms
└── cms-linux-aarch64/cms
```

每个平台另带 release.json、runtime.lock.json 和许可证。同批版本号仍使用 UTC 时间戳及输入摘要前 12 位，保存在摘要和二进制内，不附加到目录名。所有目标成功后才发布，以 `.cms-publish.lock` 串行替换目录；发布期间暂存上一批产物，失败恢复，成功清理旧产物。极端情况下恢复失败会保留并报告暂存备份路径，便于人工恢复。未参与本批的平台、有效运行时缓存和历史带版本号目录保留。

运行时缓存不能放进平台发布目录，以免重复构建覆盖缓存。默认不复制 public、template 或 YAML 到发布目录，运维须按入口目录下的固定结构放置现有站点资源；平台目录是构建产物目录，不用于保存站点业务文件。

## 内置与外置资源

代码、Composer 依赖和后台/系统模板始终内置。可选内置项严格仅允许 public、template、config.yaml、config/config.yaml、config/manifest/config.yaml。指定哪份 YAML 就收录哪份，不替换为构建时选中的其他文件。uploads、runtime 及其子路径明确禁止，构建在下载、暂存前以非零状态退出。

嵌入 public 时只收录登记的系统资源和允许的根公开文件，排除上传残留、生成 HTML、PHP、私有文件。始终排除 .env、业务上传、生成 HTML、安装资料/记录、备份、Git、构建输出和缓存。

整个内置 public 或 template 根优先于外置根，缺少内置文件不会从外置目录补齐。内置主题支持预览和切换，文件只读；内置 public 对应文件修改同样拒绝。上传和生成 HTML 始终分别写入程序入口目录下的外置 uploads 和 public/html。

实体目录固定相对源码入口或二进制所在目录，不支持 paths 区段和原目录环境变量覆盖。上传实体为 uploads，URL 仍为 /uploads/...，历史 upload/public/uploads URL 继续适配，无软链接。HTML 固定为 public/html。私有状态在 config，主题在 template，安装资料在 install，日志与运行状态在 runtime。所有后台公开资源集中 public/admin-assets，模板源、私有字体、构建输入不公开。

## 配置选择与生效

没有内置 YAML 时，默认按二进制（源码版为入口）所在目录依次查找 config.yaml、config/config.yaml、config/manifest/config.yaml。只读取第一份现有文件，不合并，格式错误不回退。CMS_ENV_FILE 由显式进程环境选择 .env；.env 或进程环境中的 CMS_CONFIG_FILE 可指定外置 YAML，不递归查找。

外置字段优先级：本地 .env 存在键 > 显式进程环境 > 选中 YAML > 默认值；空密码、false、0 都是显式值。启动时固定文件选择，外置 Redis 每请求刷新已选文件和 .env；新增更高优先级文件必须完整重启。

源码安装流程有明确例外：安装配置检查、数据库保存及重启交接按最新文件优先级选取，允许服务启动后将 YAML 移到根目录。旧 worker 的数据库启动快照仍保持，安装后完整重启加载新配置；不把保存的数据写回已移走的低优先级文件。二进制仍关闭所有安装入口。

存在内置 YAML 时，仅使用内置 YAML 和程序默认值，完全忽略外置 YAML、.env、进程部署参数及 CMS_CONFIG_FILE；外置文件无效也不影响启动。内置多份 YAML 仍选最浅的一份；配置写入工具明确只读，修改必须重新构建。嵌入真实配置会把其中凭据包含进二进制，成品须按私有配置管理。

源码版无 YAML 可进入首次安装；二进制无 YAML 即失败，env 完整也不能绕过。检查命令为 cms:config --check --json、cms:paths --check --json、cms:runtime --check、cms:deployment --check。字段来源及 fixed 状态可见，密码脱敏。部署预检包含二进制已安装数据库只读验证。

## 启动与升级

未内置的主题和系统资源必须在固定外置目录存在且可读，YAML 必须按配置查找规则存在；系统资源须与逐文件清单匹配。失败同时写 STDERR 和启动日志，返回非零，不启动 worker，-d 在后台化前检查。早期日志固定写入口目录 runtime/logs/bootstrap.log；只创建日志目录，不自动创建空资源、缓存或会话目录。日志不可写时终端另行报告。

```sh
./cms cms:deployment --check
./cms start          # 前台
./cms start -d       # 独立后台
./cms restart -d -g  # 先验证新配置和资源，再停止旧进程
```

后台重启保留动画、互斥、超时及新 worker 就绪确认，并在新进程中预检后才停止旧 master。systemd 模式使用 server.process_manager: systemd，前台运行，由 Restart=always 拉起，无需 Web 用户 sudo。内置 YAML 如需 systemd，须在打包配置内指定，环境变量不能覆盖。

Docker ARM64 可使用 debian:bookworm-slim，--platform=linux/arm64 --init，运行 /srv/cms/cms cms:container；此监护入口允许后台重启更换 master。挂载二进制和内置资源只读，配置、外置资源和数据按程序目录结构挂载；Docker 的卷挂载可由部署者安排。宿主机数据库地址须从容器可达，不沿用容器内的 127.0.0.1。服务器运行无需系统 PHP 或 Composer。

发布前核对 SHA 并备份本次代码/二进制和资源；停止准确目标，迁移默认上传目录（目标存在即停止，不合并覆盖），替换程序与配套资源，完整重启。保留业务库、主题、模式、私有配置、会话及已生成 HTML。回退按本批清单成组恢复，不恢复整份旧 runtime 或业务数据。

实际执行范围与未验收项目见 binary-resource-refactor-acceptance.md。
