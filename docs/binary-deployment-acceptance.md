# 二进制、目录映射与 YAML 配置验收

日期：2026-11-10。本轮按用户补充，优先验收本机 Docker 内的原生 Linux ARM64 二进制。主工作副本与证据位于项目外 `binary-yaml-20261110-v1`；运行站代码发布另记于该目录的发布回执，不以隔离环境成功代替运行站发布。

## 实现范围

部署参数由 `app/cms/config/ConfigSchema.php` 集中登记，外置 `config/manifest/config.yaml`；PHP 配置保留框架适配。优先级为本地 `.env` 存在键、显式进程环境、YAML、内置默认值。字段类型、范围、环境变量、生效时机和敏感性见[字段表](deployment-config-reference.md)，完整示例见 `config/manifest/config.example.yaml`。

代码、Composer依赖、后台及系统模板在二进制内；系统资源、主题、上传、生成HTML、配置、安装资料及runtime通过物理目录映射外置。URL沿用原地址；系统资源按清单授权，生成HTML仍经发布记录及签名验证。源码和二进制共用 `webman` 入口与维护服务。

新增 `cms:config`、`cms:paths`、`cms:runtime`、`cms:config-migrate`、`cms:build-bin` 和 Docker 监护入口 `cms:container`。Redis按请求重读配置并固定本请求快照，其余启动配置完整重启。systemd由普通用户前台运行，后台重启无需sudo。

## 构建与交付

ARM64运行时：PHP 8.4.27，StaticPHP固定提交 `84ff669ee513609bc75aa43a87e2f3a349b37109`，phpmicro固定提交 `fb6d497b6f4cf138ee3851a30c905d64b7b19aed`。micro SHA256为 `7336381591bc00240bd0ef424d6e77ce36d890944afd3eca8402492e68d7b1fe`，37个加载扩展；GD含FreeType、JPEG、PNG、WebP、GIF、BMP和AVIF。源码输入、提交、下载SHA、构建命令及许可证随运行时交付。ARM构建使用 `--target=linux-aarch64 --runtime=...`，拒绝不符合锁定SHA的运行时。

运行时源码输入包 `runtime-linux-aarch64/runtime-source-inputs.tar.gz` 为203,001,236字节，SHA256为 `81008b29233b38f1c2ed32ee935c69ae9724e140547fc0c1df58c743189037f4`；配套manifest记录下载输入逐项SHA、基础镜像摘要及构建机软件版本。构建使用 `--no-download`，不追踪浮动Git分支。重新构建结果需要重新核验和出具新锁，不承诺不同构建机产物逐字节相同。

最终应用二进制、资源和新安装资料的SHA及完整文件清单以交付目录 `release.json` 和项目外 `delivery.json` 为准。白名单排除运行站YAML、.env、密钥、安装记录、业务状态、上传、HTML、Git、测试产物及配置前镜像。现有源码站不切换主题或缓存模式。

## 实际结果

证据文件均相对项目外本轮目录的 `evidence/`。涉及业务写入的验收仅使用任务自建MySQL、Redis及部署。

| 项目 | 结果与证据 |
| --- | --- |
| 配置覆盖、非法值、脱敏 | 15组配置用例通过，含空密码、0、false、未知字段、重复键、执行标签；`config-cases.json`。 |
| 路径配置 | 7组路径用例通过，含统一根、逐项覆盖、相对路径、重叠、越界和链接拒绝；`path-cases.json`。 |
| 配置迁移 | 默认只读无写入；apply生成私有备份、拒绝覆盖已有YAML、拒绝自定义表达式、保留.env覆盖；`migration-cases.json`。9个有效数据库字段迁移前后一致；`migration-equality.json`。 |
| 原生ARM64运行 | Debian slim内没有系统PHP、Composer，从 `/tmp` 启动；二进制平台和37个扩展与锁一致；`runtime-arm64-probe.json`、`arm-paths-runtime.txt`。 |
| 网页安装与交接 | Webman示例站、EyouCMS SQL全数据站均完成网页预检、导入、YAML保存、自动重启、安装资料同父封存和点击进入后台；`arm-install-complete.json`、`arm-handoff-ready.json`、`arm-eyou-install-complete.json`、`arm-eyou-handoff-ready.json`。 |
| 外置目录及静态响应 | 系统资源只读，主题/上传/HTML/state/install/runtime分开映射，默认入口目录无意外重建；8类资源新旧URL字节/ETag相同，HEAD、Range、304和语言JS通过；`assets-http-arm64.json`。 |
| 实际业务 | JPEG与AVIF上传、头像4种缩略图、robots写入/恢复、外置模板编辑/依赖变化/还原、后台预览、静态生成及篡改HTML拒绝；`arm-business-http.json`。AVIF仅在测试站临时加入允许类型并恢复，不修改新装默认类型。 |
| 四套Eyou主题 | default、1003、1004、3144均无官网语法阻断，实际启用并渲染7组现有终端首页及主题资源，导入产品/文章路径正常；`arm-eyou-themes.json`。 |
| 三模式及语言/设备隔离 | 4主题×运营/开发/Redis×现有终端×cn/en，共42个页面通过，英文标记不泄漏到中文；Redis请求期间无缓存/会话文件写入；`arm-eyou-modes.json`。1003只验PC。 |
| Redis在线参数 | YAML库9→10、.env覆盖到11立即用于后续请求，不重启worker；无效库99验证失败且原模式保留；`arm-cache-http.json`。 |
| Redis故障与安全 | 暂停Redis产生真实读取超时，公开页面200且有正文，会话503；故障期间清理、恢复、普通清理保留登录、完整清理和模式往返不复活旧登录；421个缓存签名篡改及会话篡改被拒绝；18个并发公开请求通过；`arm-cache-http.json`。 |
| 站点隔离 | 两个独立数据库/部署故意共享同一Redis库与前缀，Eyou站清理不影响Webman站已有键；外部所有者哨兵保留；`arm-eyou-modes.json`、`arm-cache-http.json`。 |
| Docker后台重启 | 监护入口在master替换期间保持容器运行，后台重启4.6秒就绪且登录保留；容器停止退出码0；`arm-business-http.json`。 |
| 真实systemd容器 | ARM64 systemd252，服务以cms UID1000运行，无sudo；后台重启替换MainPID、自动拉起、登录保留，systemctl停启通过；`arm-systemd-http.json`。 |
| 浏览器 | 实际ARM64首页布局、图片和登录页显示正常，前台控制台无warn/error。认证后台业务经HTTP验收；未自动识别或绕过浏览器验证码。 |
| 资源重建/恢复 | format2，revision `2026-11-10-binary-yaml-v1`；2,132实体逐项SHA一致；`assets-rebuild-restore.json`。 |
| PHP语法 | 668个应用、框架适配及入口PHP文件通过；`lint-final.json`。 |

## 修复与验收边界

中间候选暴露了后台配置include变量污染：日志适配中的同名 `$settings` 覆盖外层聚合配置，普通登录因此返回内部错误。改为隔离include作用域，并让后台debug使用统一配置；修复后实际账号登录及上述Redis往返验收通过。最终冷启动复验再次出现信号4；已保留core并用匹配的未裁剪运行时符号定位到 `php_pcre2_jit_match + 216` 调用的动态执行区。ARM64锁现固定 `pcre.jit=0`，构建在应用启动前嵌入该INI项，运行时检查核验实际值。开启JIT的对照在第3次冷启动复现SIGILL；关闭后连续12次冷启动、60次HTTP请求均200，无core，见 `arm-pcre-jit-crash.txt`、`arm-cold-start-jitoff.json`。这项措施保留正则解释执行，不关闭正则或扩大配置权限。

systemd是真实systemd运行在隔离ARM64容器内，不代表独立Linux服务器的宿主systemd验收。Linux x86_64目标保留；ARM主机上的amd64容器仅完成仿真预验，尚无原生x86_64服务器验收。最终成品已完成二进制升级、回退上一候选、再升级最终版，配置、主题和上传保持，三次均以实际账号登录确认，见 `arm-upgrade-rollback.json`。

现有主题缺页保持原边界：1003没有mobile、产品和下载模板；1004下载详情为原包占位；3144缺搜索和下载模板，部分留言模板为空。导入数据的自定义栏目模板与主题不匹配时仍明确缺页，未修改原主题凑验收；本次不是补齐缺失页面的发布。

未触碰8793、用户Redis16379及其数据。测试Redis为任务自建实例18439，数据库及数据卷独立；测试不会用运行库夹具，也不恢复整份runtime覆盖业务状态。

## 运行站发布及最终交付

已按发布清单成组更新167个代码、资源和文档文件，发布前精确备份位于项目外 `backup-publication/`。数据库9个有效字段迁入私有YAML后保持一致；Composer按锁文件安装Symfony YAML，未手工修改vendor。准确目标master由37569完整重启为78760，6.0秒就绪；当前主题、模板模式、缓存模式、缓存与会话代际、.env、6个既有会话文件及Git暂存均保持，见 `publication-result.json`、`live-state-before.json` 和 `live-state-after.json`。

源码站继续按源码方式运行。只读复验首页及后台均200，系统JS、CSS、字体及旧URL字节一致，私有路径404，`template/_shared` 不存在，见 `live-readonly-http.json`。最终交付包为项目外 `webman-cms-linux-arm64-v1.tar.gz`，42,095,916字节，SHA256为 `8d361edc8386c3890287400b6dbe6dc01549c803433e148786cf27a1b897dd8d`；二进制SHA256为 `2b26096b45f5a99a13f6ed1e7cf2beb54706aa234dda9f737396bcf1f893f0e9`。2,751个发布产物摘要已逐项核对，详见项目外 `delivery.json` 与发布目录 `release.json`。最终包另经私有凭据及状态排除复验，见 `release-privacy-final.json`。
