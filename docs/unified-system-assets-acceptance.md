# 统一系统资源目录验收（2026-11-10）

本批将系统公共资源从 `template/_shared/assets` 迁至 `public/admin-assets/common`，后台既有 css/js/images/vendor 保持位置；主题自有文件、上传和生成 HTML 保持原字节。当前运行站发布情况以本文末节为准。

## 布局和实现

```text
public/admin-assets/
├── css、js、images、vendor/
└── common/
    ├── plugins/
    ├── static/
    ├── compat/
    ├── error-pages/
    └── generated/lang/
template/<theme>/
```

`Paths` 集中维护系统公开 URL 和物理根目录。AssetResolver 继续逐文件授权，AssetCompatibility/FileResponse 使用新兼容补丁位置；LanguageAssetService 只读取随包语言基线，数据库派生 JS 沿用文件／Redis／请求内存缓存和签名验证，不写公开实体。ForeignPack 识别新脚本 URL 并继续支持旧 URL 注入。

新 canonical 使用 `/admin-assets/common/...`。format=2 的全部原别名保留，包含 `/template/_shared/assets/...` 和 `/public/static/...`；直接返回对应字节，不跳转、不创建旧目录或软链接。组件结构、第三方版本及资源字节未修改，模板源码、PHP、私有资料继续拒绝公开。

构建、恢复和维护校验只接受新公开目标 `public/admin-assets/`；旧布局 format=2 包也不能用当前恢复器写回旧目录。旧包只用于匹配版本的完整外部回退。保留 ThemeCatalog/InstallerThemes 对 `_shared` 的保留目录排除规则。

## 数量、清单与资源包

- 迁移 686 个共享实体；清单对应 736 条记录（多个来源共用产物）。全部 2,132 个唯一受管实体、2,210 条记录的源码 SHA/产物 SHA 保持，只有目标及 canonical、别名和 revision 调整。
- revision=`2026-11-10-unified-system-assets-v1`，format=2。
- 配套包在项目外 `/Volumes/disk/site/php/dzhcms/unified-assets-20261110-v1/resource-assets-unified-system-v1.tar.gz`，15,198,107 字节，SHA256 `91f25402e77aca1de27a9f4edc71597277f73e082ab7fdf8204999c76907a7cf`。
- 私有源码归档 `asset-build-inputs.tar.xz` SHA256 `2d764f06a4ff7ba4d0f64115b367237238bb5ece24ddbe5b563bfd24f65380a3`，原字节不变。归档保存原始来源身份，没有旧 `_shared` 目标；新输出位置由配方与清单控制，完整重建已验证，无需重写原来源字节。

## 独立验收

证据和脚本均在上述项目外私有工作目录，未放入应用或 runtime。隔离 HTTP 8801，双 HTTP worker；独立数据库 `cms_assets_layout` 使用有 512MiB 上限的测试容器端点 13328；Redis 专用 16411、DB 5、64MiB 容器限制，未使用运行站 Redis 或保护环境 8793。

| 验证 | 实际结果 |
| --- | --- |
| 完整性、锁定构建、干净恢复 | 三方 2,132 实体全部 SHA 一致；重建清单不变；重复恢复无操作；冲突文件拒绝覆盖且原字节保留 |
| URL 合同 | 根部署及 `/cms` 子目录各 13,007 项通过，各覆盖 10,784 条别名记录；查询参数、片段、版本和目录模块基路径保持 |
| 实际资源 HTTP | 10,656 个唯一新旧地址 HEAD 均 200、ETag 匹配；代表性新旧 GET 字节一致，304、Range 206、If-Range 及版本缓存通过 |
| 只读文件兼容 | 298 条登记读取记录及新旧绝对/相对路径、语言派生字节，共 305 项通过；无旧目录生成 |
| 主题和模式 | 806 项页面/资源/预览/后台/故障检查通过；default、1003、1004、3144、webman-default × 运营/开发/Redis；1003 仅其已有 PC，其余 PC/mobile |
| 后台浏览器 | 独立夹具会话实际打开文章编辑页，中文 TinyMCE 和正文 iframe、公式编辑弹窗完整显示，封面上传按钮打开原生图片选择器并取消，未保存正文或实际上传 |
| Redis 断线与恢复 | 页面及语言 JS 200，会话明确 503，恢复后页面/会话 200；反复访问及断线均未更新文件缓存/会话，未创建旧目录 |
| 子目录实际 HTTP | `/cms` 首页、新旧图片/脚本/语言 GET 与 HEAD 12 项通过，首页系统 URL 带部署前缀 |
| 安装资源预检 | 独立干净资源包与安装副本的 InstallerService 环境检查 ready=true、assets_ready=true；未提交实际安装或重装 |
| 访问边界 | 未登记文件、私有 PHP、模板源码、路径越界、登记文件被换为符号链接均拒绝；主题列表无公共目录 |

动态语言新旧 URL 均从已验证缓存字节响应，保留 no-cache、ETag、HEAD/304。全部 URL HEAD 和页面内实际链接 GET 覆盖了 CSS 图片、字体、编辑器模块、公共脚本及默认图片等已登记依赖。原主题中的既有业务图片缺失不属于共享资源断链，未以占位替代原主题文件。

隔离测试的两个初始断言修正有保留记录：语言 URL 起初误期望 static 目录，实际规范 URL 为 common/generated/lang；编辑页起初猜测 archive/edit 返回 404，改用登记的 archive/update。均是验收夹具错误，没有据此改业务路由。锁定构建中原 scrawl.js 顶层 return 沿用原有回退产物，SHA 保持，不记为新增脚本修改。

浏览器会话为仅隔离环境创建的签名夹具，不冒称真实密码登录。临时 loopback 夹具入口已从候选路由文件移除；不发布任何隔离配置、账号、会话、密钥或 runtime。本轮聚焦目录和资源协议，没有重新执行所有业务写入、真实上传和完整新站安装，也不关闭历史 EyouCMS 语法兼容遗留项。

## 发布、保护与回退

发布前在项目外保留代码/文档/清单前镜像、旧 `_shared` 完整字节、受保护文件摘要、Git 暂存快照和只读数据库配置/缓存会话代际摘要。发布清单仅包含本批九个运行/维护文件、两个清单、文档与 AGENTS，以及迁移实体。私有配置、原主题、上传、生成 HTML、登录会话和业务数据不覆盖，不清缓存或改变代际。

发布使用目标 8787 的完整停止/启动，切换代码、清单和资源后新 master 重新加载；不能用 reload 代替。旧 `_shared` 原目录移到项目外部署备份，不保留旧实体或链接。运行复验后重新核对受保护文件及暂存、当前模式主题和代际。

回退先停止目标 master/worker，依据项目外 `deployment-manifest.json` 与 `deployment-backup` 成组恢复本批代码、清单、文档及旧 `_shared`；仅在新 common 文件仍与部署 SHA 相同时移到外部保留，然后完整启动。不要恢复整个旧 runtime、私有配置、业务库、上传或 HTML；发生人工文件变更时先保留差异，禁止强行覆盖。资源包和旧布局清单不能混用。

## 运行站发布记录

2026-11-10 已备份并对 8787 完整停机切换。原 master 1407 已停止，新 master 37569 加载新代码，两个 HTTP worker 正常。18 个代码/清单/文档文件和 686 个迁移实体按精确前镜像、SHA 成组发布；现场 `_shared` 原实体已移到项目外 `deployment-backup/template-shared`。

运行站 `cms:assets verify` 返回 ok=true：2,210 条记录、2,132 实体、10,656 个别名及 URL、298 个读取别名，全部校验无错误，语言公开基线仍匹配原 SHA。运行站 18 项实际只读 HTTP 通过，覆盖 PC/mobile 当前主题、新旧图片、动态语言、默认脚本、公共错误页、ETag、HEAD、304、Range 和私有路径拒绝。启动、请求和维护命令后 `template/_shared` 均不存在。

浏览器实际重新加载首页，系统脚本使用 `/admin-assets/common/...`，首页前八张图（含广告原图）均 complete 且 naturalWidth>0。后台使用原有服务端签名会话重新访问，工作台、导航、default 主题和业务统计正常显示；截图在外部 `live-admin.png`。本次浏览器隔离验收有一次 Cookie 污染：8801 和 8787 同用 127.0.0.1，夹具 PHPSID 覆盖了浏览器原值，导致新开后台显示登录页；已核对唯一仍有效的原会话及注册记录，通过浏览器恢复原值和配置规定的有效期后再次进入后台。没有新建管理员身份、重置密码、重建服务端会话或修改会话代际。后续独立浏览器验收应使用独立主机名或独立浏览器配置，避免跨端口共享 Cookie。

2,174 个受保护文件（主题、上传、HTML、私有配置及索引等）全部保持；Git 暂存快照一致。数据库全配置摘要、站点签名、模板模式及缓存/会话代际前后相同：当前 default、eyou、开发模式 2。本批未执行运行库业务写入、主题/模式切换或清缓存。未操作 8793 或用户 Redis 16379。

隔离 8801、专用 Redis 16411 及本批启动的有界测试 MySQL 13328 已停止，外部证据、资源包和回退前镜像保留。
