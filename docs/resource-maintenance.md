现行目录与二进制规则：默认上传实体为站点根 `uploads/`；外置 YAML 三路径选一、内置配置固定、资源整根优先及无安装二进制见[构建部署说明](binary-deployment.md)。以下历史验收不扩大本批实际验证范围。

# 系统资源维护

现行规则（2026-11-11）：资源校验、重建、打包和恢复全部使用 PHP 8.2+ 及 zlib，不调用外部解释器或归档程序。以下规则取代历史资源压缩配方与外部工具安装步骤。旧验收记录保留原历史口径。

公开资源保持 `public/admin-assets`，共同资源在 `common`；主题目录只包含各主题。旧 URL、部署前缀、版本摘要和逐文件公开授权继续由资源清单管理。

## PHP 维护入口

```sh
php webman cms:assets check
php webman cms:assets verify
php webman cms:assets build
php webman cms:assets build --apply --output /absolute/empty-output
php webman cms:assets pack --archive /absolute/release/resource-assets.tar.gz --apply
php webman cms:assets restore --archive /absolute/release/resource-assets.tar.gz
php webman cms:assets restore --archive /absolute/release/resource-assets.tar.gz --apply
```

`check` 检查清单及资源存在性，`verify` 核对大小及 SHA256。`build` 默认只读核验，加 `--apply` 才生成资源；默认从私有 `app/cms/resources/asset-build-inputs.tar.gz` 读取输入，也可用 `--input /absolute/expanded-input` 提供展开源文件。两种输入均严格校验原始字节。构建按 `php-copy-v1` 配方原字节复制，不做外部压缩、变量改名、路径重定位或 CSS 导入；保留原注释和版权。未修改的二进制资源原字节保留。

源文件变更须在隔离副本审核、更新清单的源/目标大小和摘要、提升 revision，再重新打包输入及发布资源；旧清单不会默许源文件变化。不得在运行站使用构建命令覆盖修改后的文件。构建到空目录可验证完整重现性。

`pack` 生成同版 format=2 tar.gz 资源包及 `<archive>.manifest.json.gz` 配套恢复清单。归档条目排序固定，所有者、时间和模式固定，便于核验重复打包结果。源码发行时将配套清单作为 `app/cms/resources/source-package-assets.json.gz` 随同版本发布，不能混用其他版本清单。普通操作不自动替换运行站清单。

`restore` 使用程序内同版恢复清单校验整包与完整成员表。默认仅预检，加 `--apply` 才恢复缺失文件；相同内容跳过，不同内容拒绝覆盖。所有成员校验完成后，先私有暂存，再同目录原子发布；失败只撤销本次新建且身份仍匹配的文件。拒绝重复成员、越界、符号/硬链接、特殊文件、损坏归档和未登记文件。

这些命令不导入数据库、不改安装记录、不恢复用户上传或生成 HTML。二进制允许 PHP 内置资源校验；构建、打包和恢复限源码环境，二进制升级/恢复使用同版本发布包。普通安装、后台及网站运行均不执行资源构建。

## 当前发布

资源清单 format=2，revision `2026-11-12-dashboard-label-v8`，2,210条记录、2,132个实体。来源归档是 gzip tar；以前 xz 归档及外部压缩工具配置不再使用。相应验收见 [PHP维护验收](php-maintenance-acceptance.md)。历史资源包及旧配方只能用于相应旧版成组回退。

旧版 YAML 升级时，先备份并删除已退役的 `tools` 区段，其他配置保持；旧 `CMS_ASSET_*` 工具环境变量不再使用。配置仍严格拒绝未知字段，不会静默忽略旧工具参数。本次运行站发布已将该区段纳入原子迁移。

## 语言资源

语言 JS 由 PHP 从数据库及已验证语言数据生成，沿用文件/Redis派生缓存；不改写固定公开资源。主题、上传及受管静态 HTML 维持各自存储和授权边界。

## 历史记录

## 语言与城市UI资源批次（2026-11-08）

本批只修改cms-workspace.js/css发布资源，维护源码在asset-build-inputs.tar.xz，清单revision为2026-11-08-language-city-v1。外部资源包resource-assets-language-city-v1.tar.gz，SHA256为8824507b592112156682fb36363d085e8ed7d76f5b42d685153fb65fb0ae39b8；包与本批快照位于项目外admin-language-city-20261108-202343。2132个唯一实体锁定重建和恢复一致，冲突拒绝覆盖，旧目录不重建。不要使用旧包覆盖本批清单。
