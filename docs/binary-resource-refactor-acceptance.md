# 二进制、资源目录与配置加载验收

日期：2026-11-11。实现与破坏性测试使用项目外独立副本、独立 MySQL / Redis，未操作 8793 或用户 Redis。测试夹具和内置测试配置未发布到运行站。

## 实现范围

- 上传默认实体为站点根 uploads；自定义映射不变，历史 URL 通过适配器兼容，无软链接。
- 后台公开资源仍统一在 public/admin-assets；2,132 个唯一实体、2,210 个清单记录完整校验，未修改资源字节。
- YAML 选择最浅的现有文件，不合并；外置配置支持 .env / 进程覆盖；内置 YAML 固定，拒绝写入并完全忽略外部部署参数。
- public/template 整根内置优先，不从外部补齐；上传、HTML、runtime 始终外置。内置模板编辑及公开文件修改有 UI 提示及接口拒绝。
- 默认 bin 输出、四平台运行时缓存、列表两种 YAML 写法、JSON 环境列表、重复 CLI target、批次发布、锁定摘要及许可证均已接入。
- 二进制缺少 YAML/主题/系统资源或有效已安装数据库时，在 worker 和后台化前失败；同时报告终端和启动日志。安装 HTTP404，安装、数据库初始化及管理员初始化 CLI 拒绝。
- 构建与维护使用 PHP，不引入 Node、Python、CMS key 或服务器系统 PHP / Composer 依赖。

## 实际通过项目

| 项目 | 实际证据 |
| --- | --- |
| 配置、列表、文件优先级及非法输入 | config-tests.txt：33 项；extended-config-tests.txt 补测 JSON 环境列表及输出目录边界 |
| 所有 8 种可选资源组合、固定配置与外部失效配置隔离 | phar-tests.txt：19 项；真实内置 ARM64 成品另做 HTTP / API 验证 |
| 默认无参数构建、不同工作目录 | build-default.json：默认 darwin-aarch64、bin 及独立 bin/build-runtime；无资源/安装资料副本 |
| 四目标同批构建 | build-final.json；4 份成品使用同一版本；SHA256、平台头及锁定运行时摘要校验 |
| 运行时解包、离线复用、损坏拒绝 | runtime-tests.txt；原始官方运行时归档 SHA256 均验证通过 |
| 禁止项及构建失败撤回 | builder-tests.txt：15 项；batch-failure-tests.txt 验证第二目标失败不发布本批，旧发布和有效缓存保留 |
| macOS ARM64 原生执行 | native-final-http.txt：33 项；真实二进制提供前后台、附件别名、HEAD / Range / ETag、语言 JS、登录和模板预览 |
| Linux ARM64 Docker 实际执行 | linux-final-http.txt：33 项；debian:bookworm-slim，容器无系统 PHP / Composer；PHP 8.4.27、扩展及 INI 实测 |
| 内置 public / template / YAML | embedded-api-tests.txt：40 项；无外置副本也能运行、模板编辑和新建拒绝，真实上传写外置 uploads |
| 静态生成 | static-tests.txt：36 项；内置主题生成首页、发布外置 HTML、签名校验响应并清理，仅隔离数据库 |
| 运营缓存模板依赖及内置根隔离 | dependency-tests.txt；外部模板修改立即生效，内置主题忽略同名外部修改 |
| 三种缓存模式 | cache-tests.txt；每模式执行 HTTP 用例，Redis 模式文件缓存/会话的 SHA 和 mtime 不变 |
| 普通及完整清理 | clear-tests.txt；普通清理只改变缓存代际，--all 同时改变会话代际 |
| Redis 中断和恢复 | redis-outage.txt；公开页面 200、会话 503、故障时旋转代际，恢复后旧登录不复活 |
| 启动失败与重启保护 | strict-final-tests.txt：22 项，含缺失、不可读、无效数据库及日志写入失败；restart-failure.txt 确认旧 PID 与服务保留 |
| 子目录及禁止安装 | prefix-tests.txt；前台、canonical / 旧资源 URL、动态语言 JS 的部署前缀保持；后台沿用 /app/admin 入口 |
| 模板模式 | eyou-mode-tests.txt：18 个官网页面检查；两套现存主题 default（Eyou）与 webman-default 均测试 |
| 浏览器 | 原生二进制官网与后台登录页实际显示，含内置资源版本；运行站发布后只读复验另记 |

上述数量来自各独立用例，部分有共享基础步骤，不相加推算覆盖率。PHP 语法检查覆盖本批修改及新增的 PHP 文件。

## 验证边界

- darwin-x86_64、linux-x86_64 成品完成构建、摘要及平台头验证，未作原生运行验收；不能等同于 x86 生产验收。
- 未在真实 systemd 主机验收服务启停；本机原生 standalone 与 Linux ARM64 Docker 监护入口实际运行通过。
- 当前工作站只有 default、webman-default 两套主题。本批不补造已移除主题，不宣称通过不存在的 1003/1004/3144 验收。
- 本批静态生成验证首页及其外置发布链路，未穷举全部业务栏目/终端/语言组合；当前站点数据及主题保持原样。
- 锁定成品运行时可自动下载，网络或摘要失败明确停止；本批未引入自动安装宿主工具或跨平台源码编译回退。

## 发布与回退记录

本批源文件前镜像、变更清单、上传 SHA 清单及详细测试输出保存在项目外私有验收目录。发布仅覆盖清单内文件；私有 YAML/.env、模板、业务数据库、会话和 Git 暂存不发布测试版本。

发布版本及现场复验写在下节。回退须先停准确目标，按 changed-files.json 恢复存在前镜像的文件、移除本批新增文件；确认旧 public/uploads 无冲突后将根 uploads 原子移回，再完整启动。不能恢复整份旧 runtime 或覆盖业务文件。

## 本批成品

版本：`20261111T063428Z-dbe8f475ba82`；可选内置资源为 `[]`。

| 平台 | 二进制 SHA256 |
| --- | --- |
| darwin-aarch64 | `55f5f3291a10f6bb16a2c37aabd6eca316206d063c3dc87c69c0801ce21b3e75` |
| linux-aarch64 | `5db409ea71ec9a17c9370154df473dc6b05245cf8f73f0512ff8e829d8d7483d` |
| darwin-x86_64 | `337aa902e17fdb5b2c223103a9694db66f296e91b2c8fc56a2fea0a757c1a271` |
| linux-x86_64 | `d47b2f24654c1d4066044d05536bd66d6e803975d6be8266793196a4e1e595da` |

详细证据目录：`/private/tmp/cms-binary-refactor-20261111-rGPi4V/evidence`；精确前镜像：`/private/tmp/cms-binary-refactor-20261111-rGPi4V/before`。此目录包含私有验收状态，不作为公开资源发布。

## 现场发布结果

2026-11-11 14:45（Asia/Shanghai）完成 8787 源码服务的完整重启，原 master 70951 停止，新 master 85976。按精确清单发布 98 个文件，默认 public/uploads 原子改名为 uploads，共 163 个文件，逐文件 SHA256、大小和权限一致。

发布后只读检查确认：主题文件 623 个不变，主题 default、Eyou 模板模式、缓存模式 2 不变；私有配置与安装状态摘要不变，已生成 HTML 不变。Git 暂存摘要仍为 `d2e92d1dad32e8943cc95ed8cb1ec621d96a430f97f8a36a035ca70156b52e5e`。未清理运行站会话或旋转其代际。

官网及后台登录 HTTP200；12 个原有上传文件的三种历史 URL 内容一致；资源清单再次通过 2,132 个实体校验。浏览器实际显示官网轮播及产品图，页面 33 个 img 均无损坏；后台登录页显示正常。运行站仍以原 PHP 源码方式运行，未自动换成测试二进制。

四平台无可选资源的正式成品及已校验运行时缓存已放入项目 bin；各版本目录只有 cms、发布摘要及许可证，不含测试 YAML、上传、安装资料或可选资源副本。精确 SHA 见上表和各平台 release.json。

本批隔离 HTTP worker 已停止；仅移除本批创建的 cms-binary-refactor-app/db/redis-20261111 容器及其临时数据。其他服务、8793 和用户 Redis 保持原状。回退前镜像及私有验收证据继续保留在上述项目外目录。

本批临时测试二进制、PHAR 夹具、运行时下载/解包副本及构建暂存已清理；正式四平台成品和运行时缓存仅保留在项目 bin。源码工作副本、精确前镜像与验收证据保留在私有验收目录。
