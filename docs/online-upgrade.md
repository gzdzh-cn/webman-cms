# 在线升级和发布维护

1.0.7 加入在线升级能力。现有 1.0.6 必须先通过正常部署安装 1.0.7，之后才能从后台升级。CMS 版本为 x.y.z；二进制的时间戳版本及 build_id 是独立构建身份。

从 1.0.10 起，后台向固定 Gitee 仓库 `gzdzh/webman-cms` 的正式 Releases 检查更新并下载附件，排除草稿、预发布及非正式版本标签。检查在独立进程完成，每站缓存 15 分钟；Gitee 缓存与旧 GitHub 缓存分开，网络失败保留同一来源上次成功结果。超管每次登录首次发现新版会弹窗，同次登录刷新不重复，工作台版本旁可随时打开。普通管理员可看信息，启动升级必须通过服务端超管和 CSRF 检查。

运行端只访问 Gitee 公开 API 及 `foruda.gitee.com` 官方附件域名，无需令牌。下载入口由固定仓库的 Release ID 和附件 ID 生成，不采纳浏览器或清单提供的任意地址；每次重定向均校验 HTTPS 和明确主机白名单，包大小、SHA-256、逐文件身份和平台仍独立核验。清单中的 `repository=gzdzh-cn/webman-cms` 是格式 1 的项目身份，继续保留以兼容已发布清单，不代表下载来源。执行中既有 GitHub 任务沿其已冻结执行器恢复，新任务统一使用 Gitee，不自动回退其他镜像。

旧版二进制的升级地址已经编译在程序内，向 Git 仓库推送代码或更改本地源码无法改变线上旧进程；首次迁移到 Gitee 需要正常部署一次 1.0.10，并完整停止/启动准确目标。不要修改旧任务、伪造检查缓存或覆盖已发布 v1.0.9 资产及标签。

## 制作发布资料

在同版完整源码、composer.lock 对应的 vendor、资源清单及恢复资料齐全时执行。所有路径必须为绝对路径；输出目录必须尚不存在。维护统一使用 PHP，无需外部压缩器。

```sh
php webman cms:release pack --output /private/releases/1.0.7
php webman cms:build-bin --target darwin-aarch64 --target linux-x86_64 --binary-name cms
php webman cms:release pack --output /private/releases/1.0.7-full \
  --binary /absolute/project/bin/cms-darwin-aarch64 \
  --binary /absolute/project/bin/cms-linux-x86_64
```

构建二进制时 CMS 版本写入内嵌构建资料；构建后修改版本或系统资源，需要重新构建。外置系统资源的二进制须另外以 `--embed-admin-assets=false` 构建，打包工具将同版系统资源加入升级包。`--no-source` 仅打包指定二进制。首次发布包也可用于正常部署，但不是全新安装包，不包含安装 SQL、主题和站点 YAML。

`cms-upgrade.json` 记录版本、最小/最大来源版本、PHP 要求、向后兼容增量迁移声明，以及每包大小、SHA-256、平台、资源内嵌方式、构建身份和逐文件摘要/模式。源码包含锁定依赖，目标服务器无需 Composer。标准二进制升级只使用自带 PHP 运行时，保留部署中的可执行文件名。不能将可执行文件替换为另一平台，也不能用通用包替换内嵌配置、主题或站点 public 资源的定制二进制。

上传和正式发布是两个独立操作，凭据只在发布端环境变量中提供：

```sh
php webman cms:release publish --output /private/releases/1.0.7-full --notes /absolute/project/docs/release-1.0.7.md
# 默认 Gitee：publish 创建预览版，核对说明与附件后显式转为正式版：
php webman cms:release finalize --id 123456789
# GitHub 镜像使用相同包，明确指定发布中心：
php webman cms:release publish --provider github --output /private/releases/1.0.10 --notes /absolute/project/docs/release-1.0.10.md
php webman cms:release finalize --provider github --id 123456789
```

默认 `publish` 需要发布端 `GITEE_TOKEN`。Gitee API 没有 GitHub 的草稿属性，因此先创建 `prerelease=true` 的预览版，上传失败保留预览版供检查；后台忽略预览版。`finalize` 下载核对全部附件大小及 SHA-256，核对标签、清单和当前程序版本后才设置 `prerelease=false`。GitHub 的 `--provider github` 使用发布端 `GITHUB_TOKEN`，仍先创建草稿，显式 finalize 后公开。标签必须预先推送到对应仓库，两个仓库使用同一代码提交和同一批包。生成的清单为等价紧凑 JSON，减少检查下载流量。运行站不配置发布凭据。不要把站点配置、上传、主题、HTML、安装记录、数据库备份、私有任务资料放进发布包。

## 执行与恢复

后台只提交服务端确认的发布身份，不接受下载地址、目录或命令。启动时重新确认正式发布，随后独立执行下载、完整校验、环境预检、备份、停服、逐文件切换、兼容迁移、完整启动及新 HTTP worker 确认。成功必须同时确认新 master、CMS 版本、二进制 build_id、系统资源和数据库连接。包中二进制的内嵌身份也在停服前由候选程序自身报告并核验。

执行器、旧二进制运行时、下载包、逐文件备份和恢复日志放在项目父目录的 `.cms-upgrades-<站点摘要>/<任务ID>`，权限 0700，内部凭据文件 0600。任务记录放本站数据库 StateStore，runtime 只放互斥锁和可重建日志。浏览器断线或刷新后继续查询相同任务；服务进程停止不影响执行器。生成、安装和重启与切换互斥。未知文件不删除，已有受管源文件修改先备份再覆盖。

执行器遇到切换或启动错误会恢复原文件并完整启动旧版本；不会恢复整库或覆盖业务数据。迁移必须保持旧版本可继续运行。执行子进程意外退出时由监督进程恢复；全部执行器被终止后，后台状态查询可重新启动原任务恢复，服务器重启且站点不能启动时使用本任务私有执行器：

```sh
php webman cms:update status --id <任务ID>
php webman cms:update recover --id <任务ID>
# 若部署程序本身受损：源码用原 PHP 运行私有 runner.php；二进制用私有 executor
/private/.cms-upgrades-.../<任务ID>/executor cms:update-executor /private/.cms-upgrades-.../<任务ID>/runner.php
```

只运行本站可信备份中的执行器，不改 job.json、恢复日志和备份文件。回退发现文件被外部改变时停止覆盖并保留资料，需维护人员核对。回退失败会在状态中保留失败原因。备份保留至人工确认，不自动清理。

当前在线执行支持 standalone 与 `cms:container`。systemd 的默认 cgroup 清理会终止独立执行器，且自动重启会与切换冲突，因此启动接口明确拒绝 systemd 部署；此环境需要在服务器维护窗口部署，不能将后台显示的更新信息视作已获在线升级支持。
