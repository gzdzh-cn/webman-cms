# 构建与启动命令

以下命令在项目根目录执行。`php` 应为 PHP 8.2 或更高版本；源码环境需要安装 `composer.lock` 锁定的依赖。构建及维护无需 Node、Python 或 CMS key。

本机 PHP 路径为 `/Users/lizheng/Library/FlyEnv/env/php/bin/php`；如果终端默认 PHP 版本不符，将命令中的 `php` 替换为该完整路径。

## 1. 源码启动

```sh
# 前台运行，终端保持占用
php webman start

# 后台运行
php webman start -d

# 查看运行状态
php webman status

# 完整重启
php webman restart -d -g

# 停止
php webman stop
```

默认监听 `http://0.0.0.0:8787`，本机访问 `http://127.0.0.1:8787/`，后台为 `/web-admin`。修改监听地址使用 YAML 的 `server.listen` 或环境变量 `WEBMAN_LISTEN`，完整重启后生效。新站先用源码版完成安装，二进制不提供安装功能。

## 2. 构建二进制

```sh
# 默认：构建当前平台，输出到 bin，运行时缓存放 bin/build-runtime
php webman cms:build-bin

# 本机 macOS ARM64
php webman cms:build-bin --target=darwin-aarch64

# Linux ARM64，供本机 Docker ARM64 容器运行
php webman cms:build-bin --target=linux-aarch64

# 同时构建多个平台
php webman cms:build-bin --target=linux-aarch64 --target=linux-x86_64

# 自定义输出和运行时缓存目录
php webman cms:build-bin --output=/absolute/releases --build-runtime=/absolute/build-runtime

# 自定义二进制文件名，生成 bin/cms-<平台>/dzhcms
php webman cms:build-bin --binary-name=dzhcms

# 将系统公开资源及主题内置，配置继续外置
php webman cms:build-bin --target=linux-aarch64 --embedded-resources='["public","template"]'

# 查看全部构建参数
php webman cms:build-bin --help
```

支持的平台：`linux-aarch64`、`linux-x86_64`、`darwin-aarch64`、`darwin-x86_64`。默认平台按执行构建的进程识别：本机 macOS ARM64 为 `darwin-aarch64`，Linux ARM64 容器内为 `linux-aarch64`。指定目标平台不代表成品已完成该平台的实际运行验收。

运行时按平台下载并校验，已有有效缓存会复用。构建不会自动安装宿主机软件；运行时准备失败时停止并显示原因。

构建期间终端显示带时间和耗时的阶段日志：资源校验、输入扫描、文件暂存、各平台运行时准备、PHAR 打包、二进制合并和整批发布。下载约每 2 秒显示已下载量、总量及百分比（服务器提供总量时）、平均速度和耗时；同时提示缓存命中、续传尝试及失败清理。跨平台构建明确提示未在本机执行目标运行时验证，不显示虚假的运行验收结果。

阶段日志写入 STDERR，成功结果仍以 JSON 写入 STDOUT，便于分别保存：

```sh
php webman cms:build-bin > build-result.json 2> build-progress.log
```

使用 `--quiet` 可关闭常规进度输出。已经启动的旧构建继续使用其已加载代码，新增进度从下一次构建生效。

此次进度功能的实际验证范围见 [构建终端进度验收](docs/build-progress-acceptance.md)。

也可在所选 YAML 中设置默认构建参数：

```yaml
build:
  output: bin
  binary_name: cms # 自定义文件名，例如 dzhcms
  target: [] # 当前平台；多平台示例：[linux-aarch64, linux-x86_64]
  build-runtime: bin/build-runtime
  embedded_resources: []
```

两个列表字段都支持分行写法，例如：

```yaml
build:
  target:
    - linux-aarch64
    - linux-x86_64
  embedded_resources:
    - public
    - template
```

构建参数优先级为 CLI > 环境变量 > 所选 YAML > 默认值。对应环境变量为 `CMS_BUILD_OUTPUT`、`CMS_BUILD_BINARY_NAME`、`CMS_BUILD_TARGET`、`CMS_BUILD_RUNTIME`、`CMS_BUILD_EMBEDDED_RESOURCES`，列表环境变量必须写成 JSON 数组。相对输出路径和运行时缓存路径相对源码入口目录解析；修改输出目录不联动修改缓存目录。

`build.binary_name` 默认 `cms`，缺省、`null` 或空字符串也使用默认值。只改变平台目录内的二进制文件名，目录仍为 `cms-<平台>`。名称需以英文字母开头，1～64 位，仅含字母、数字、点、下划线或短横线，不允许路径及配置、资源目录和发布资料保留名称。

例如 YAML 设置 `binary_name: dzhcms`，Linux x86_64 产物为 `bin/cms-linux-x86_64/dzhcms`，部署后用 `./dzhcms start -d` 启动；发布摘要及终端显示对应名称。修改名称后重新构建，会整体替换平台目录并清除旧文件名。

默认产物结构：

```text
bin/
├── build-runtime/
│   └── <平台>/micro.sfx
├── cms-linux-aarch64/
│   ├── cms
│   ├── release.json
│   ├── runtime.lock.json
│   └── 许可证等配套文件
└── cms-linux-x86_64/
    └── ...
```

平台目录固定为 `cms-<平台>`，例如 `cms-linux-x86_64`，重复构建自动整体替换对应目录。所有目标构建成功后才发布，发布失败会恢复此前产物；其他平台目录和运行时缓存保留。输出根目录的 `.cms-publish.lock` 用于避免并发发布冲突。

版本号仍由 UTC 时间戳和打包输入摘要组成，保存在发布摘要和二进制内部，不拼入目录名。历史带版本号目录不自动清理。

覆盖、回退及并发发布的实际验证见 [固定构建目录验收](docs/fixed-build-output-acceptance.md)。

## 3. 二进制部署目录

默认只打包程序代码、依赖及后台和系统模板，不复制外置资源到发布目录。部署者需要将匹配版本的系统资源、主题、配置及已有站点数据放到二进制旁边：

```text
站点目录/
├── cms
├── config.yaml                 # 推荐的外置配置位置
├── .env                        # 可选，覆盖外置 YAML
├── config/                     # 站点私有状态
├── public/
│   ├── admin-assets/           # 与程序版本匹配的系统资源
│   └── html/                   # 已生成页面
├── template/                   # 官网主题
├── uploads/                    # 上传文件
└── runtime/                    # 日志、锁及当前模式允许的缓存
```

实体目录固定相对二进制所在目录，不使用自定义 `paths`。程序连接已安装数据库，不会将数据库内容打进成品，也不会初始化数据库。部署及升级时保留配置、私有状态、上传、主题、生成页面和必要运行状态。

可内置项只有 `public`、`template`、`config.yaml`、`config/config.yaml`、`config/manifest/config.yaml`。`uploads`、`runtime` 及其子目录禁止内置，填写后构建会报错停止。

内置 `public` 或 `template` 后，整个对应资源根使用内置版本，外置文件不参与补齐；对应资源只读。上传和生成 HTML 仍然外置。未内置的 YAML、主题或系统资源缺失时，二进制启动失败，错误同时输出到终端和 `runtime/logs/bootstrap.log`。

## 4. 配置读取

没有内置 YAML 时，程序相对入口目录依次查找：

1. `config.yaml`
2. `config/config.yaml`
3. `config/manifest/config.yaml`

只读取第一份现有文件，不合并；该文件格式错误时直接报错。外置字段优先级为 `.env` > 显式进程环境 > 所选 YAML > 默认值。可通过 `CMS_CONFIG_FILE` 指定外置 YAML，通过进程环境 `CMS_ENV_FILE` 指定 `.env`。

如需内置根配置，构建时使用：

```sh
php webman cms:build-bin --embedded-resources='["public","template","config.yaml"]'
```

内置 YAML 后配置固定，外置 YAML、`.env` 和进程部署参数都不能覆盖，修改需重新构建。真实配置中的数据库密码等也会进入成品，应妥善保管二进制。配置示例见 [config.example.yaml](config/manifest/config.example.yaml)。

## 5. 二进制启动

在部署目录中执行，与源码版使用相同动作，不需要系统 PHP 或 Composer：

```sh
# 启动前检查配置、资源和已安装数据库
./cms cms:deployment --check

# 前台运行
./cms start

# 独立后台运行
./cms start -d

# 查看运行状态
./cms status

# 完整重启：先预检，检查通过后停止旧进程并启动
./cms restart -d -g

# 停止
./cms stop
```

新版二进制在 `start`、`restart` 和 `cms:container` 读取配置前自动整理本站受管外置文件权限，终端显示检查数量和调整数量。后台重启也先在新进程修复权限，再预检，失败时保留旧主进程。源码启动不自动修改权限。

YAML 不强制为 `600`：原有 `400`、`600`、`640`、`660` 均可使用，前提是运行账号可读。自动整理会删除所选 YAML 和有效 `.env` 的其他用户权限及执行位，例如 `644` 改为 `640`；不修改文件内容或所有者。新写入的私有配置仍使用 `600`。

| 受管位置 | 自动处理 |
| --- | --- |
| 所选外置 YAML、有效 `.env` | 保留原有所有者/组的读写位，删除其他用户权限及执行位，确保所有者可读 |
| `config` 私有状态、`runtime` 现有内容 | 目录 `700`，普通文件 `600`；未选中的三个候选 YAML 不调整 |
| `uploads`、外置 `public/html` | 目录 `755`，普通文件 `644`，补齐必要目录 |
| 外置 `template`、`public/admin-assets` | 确保可读和目录可遍历，移除特殊位、文件执行位及组/其他用户写入位；保留已有只读状态 |
| 站点根及必要父目录 | 仅整理受管路径的父目录；为创建必要子目录时补足所有者写入权限 |

内置资源及被内置配置忽略的外置 YAML、`.env` 不参与修复。不会创建空主题、系统资源、安装、文件缓存或会话目录；不遍历站点根的备份、构建目录和其他未知文件。链接、硬链接和特殊文件明确拒绝。

也可以单独执行，默认只检查：

```sh
./cms cms:permissions --check
./cms cms:permissions --fix
# 修复后检查完整部署
./cms cms:deployment --check
```

`cms:deployment --check`、`cms:config --check` 等检查命令保持只读，不自动改权限。请用实际服务账号启动；程序无法替其他文件所有者修改权限，也无法绕过只读挂载或 ACL。失败会提示具体路径，启动失败仍写入 `runtime/logs/bootstrap.log`。二进制本身在执行前就必须有执行权限，初次部署缺失时需先 `chmod +x cms`。

文件权限不能代替 Nginx 的私有文件访问限制，尤其 Nginx 与 CMS 使用同一运行账号时。站点响应应经过 Webman，禁止 Nginx 直接提供配置文件；示例见 [代理与权限说明](docs/security-hardening.md)。权限功能实际验证范围见 [启动权限验收](docs/binary-permissions-acceptance.md)。

其他只读检查命令：

```sh
./cms cms:config --check --json
./cms cms:paths --check --json
./cms cms:runtime --check
```

独立运行默认使用 `server.process_manager: standalone`。systemd 托管时配置为 `systemd`，用 `./cms start` 前台运行并由服务管理器拉起，不加 `-d`，重启使用 systemd 服务命令。

## 6. 本机 Docker 运行 Linux ARM64 成品

先按第 3 节准备独立部署目录，将 Linux ARM64 的 `cms` 放入其中。以下 `/absolute/site` 替换为该部署目录的绝对路径：

```sh
docker run --name webman-cms-arm64 \
  --platform=linux/arm64 \
  --init \
  --publish 8787:8787 \
  --mount type=bind,source=/absolute/site,target=/srv/cms \
  --workdir /srv/cms \
  debian:bookworm-slim \
  ./cms cms:container
```

容器使用 `standalone` 进程模式及 `cms:container` 监护入口，以支持后台“重启程序”更换主进程。监听地址需要为容器可访问的地址，例如默认 `http://0.0.0.0:8787`；如端口已占用，修改宿主端口映射。容器内连接宿主机数据库或 Redis 时，配置可从容器访问的主机地址；Docker Desktop 通常使用 `host.docker.internal`。

```sh
# 查看日志
docker logs -f webman-cms-arm64

# 停止或再次启动同一容器
docker stop webman-cms-arm64
docker start webman-cms-arm64
```

详细构建、配置及升级规则见 [二进制构建与部署](docs/binary-deployment.md)，实际平台验收范围见 [资源与构建重构验收](docs/binary-resource-refactor-acceptance.md)。
