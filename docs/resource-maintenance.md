# 资源布局、构建与恢复

2026-11-10 系统资源统一：公开系统资源统一在 `public/admin-assets`，共享资源迁至 `public/admin-assets/common`；`template` 仅存主题，禁止创建 `_shared` 实体或软链接。全部登记旧地址保留逐文件别名，动态语言 JS 继续使用文件／Redis 派生缓存，不写公开目录。format=2 保持，当前 revision 为 `2026-11-10-unified-system-assets-v1`；配套包、隔离验收和发布状态见[统一资源验收](unified-system-assets-acceptance.md)。本段取代下方历史共享资源布局与旧配套包约定。

2026-11-10 顶部下拉浅色高亮：当前配套恢复包位于项目外 `topbar-soft-highlight-20261110/resource-assets-topbar-soft-v1.tar.gz`，大小 15191491 字节，SHA256 `87557c992e4cfa39e49e790cee1083c35848534c2d85a75cbd227957b94b2780`；源码归档 SHA256 `2d764f06a4ff7ba4d0f64115b367237238bb5ece24ddbe5b563bfd24f65380a3`。仅修改workspace的CSS和JS，覆盖Layui选中整行底色，菜单使用浅蓝圆角高亮，并移除单语言数量提示。下方旧包仅用于对应版本回退。

2026-11-10 Redis 错误提示去重：当前配套恢复包位于项目外 `settings-error-single-20261110/resource-assets-settings-error-single-v1.tar.gz`，大小 15191466 字节，SHA256 `ebf974b76090f383df7fa3eea4dfe8c38f5c5d56daa989ef0335a602623562b6`；源码归档 SHA256 `1c99370bc4c5875b9ee326e4f890ad75b04d40c59b012ad5e854208127dac22d`。仅修改基本设置脚本，Redis 错误只在网站模式行保留一处红字，2秒弹窗保持。下方旧包仅用于对应版本回退。

2026-11-09 后台顶栏与模式反馈：当前配套恢复包位于项目外 `admin-topbar-20261109-222432/resource-assets-admin-topbar-v1.tar.gz`，大小 15191457 字节，SHA256 `d193563da81aacfc5c1ecf9a47dbbcc4949d7c29ed91230dacc5305d966ba8e6`；源码归档 SHA256 `5ef68ebc3d3e53384bb8243cb92e6a7027347ba1bfa2532c8e57869a20d0aac1`。6项自研资源变更，2,132个实体在工作区、锁定重建和干净恢复中逐项一致，旧包仅用于对应版本整版回退。见[验收](admin-topbar-acceptance.md)。

2026-11-09 Redis 模式后台设置资源更新：当前配套恢复包位于项目外 `redis-cache-20261109-1650/assets/resource-assets-enterprise-v1.tar.gz`，大小 15,192,283 字节，SHA256 `30092142844ab797623c4adf5265787902bcbd3ceeefde032a3728ec2ea82b8f`；源码归档 SHA256 `3555c638c28719ac14dc05bf84155acab4e78d85893ea16bf4812fbb9279347d`。锁定构建与干净恢复的 2,132 个实体均逐项匹配当前清单。


2026-11-09 企业官网补齐时的历史配套资源为项目外 `enterprise-completion-20261108-222026/resource-assets-enterprise-v1.tar.gz`，清单revision=`2026-11-08-enterprise-v1`。2132个发布实体在work、锁定build和干净restore三方SHA一致；3个JS修改与私有源码归档同步。包15,185,519字节，SHA256 `0d9334a447a0430b74e9ce95e8000a2f8c100b4d618f70248b8b89d7b4935557`；源码归档SHA256 `82a1a3b0a63d5b66283bb19f0709440fcca128be05999a19a85d3eb291aa3acd`。以下更早包只用于相应历史整版回退，不与当前清单混用。发布及验收见[本批记录](enterprise-completion-acceptance.md)。

2026-11-08 多语言修订：当前配套包为项目外 `multilingual-20261108-124545/resource-assets-multilingual-v1.tar.gz`，2,130个唯一实体；后台新增统一编辑语言上下文脚本。包 SHA-256 为 `50880f7e4b2c25a3f08a0317aefc3826235da152b8bfa04685506bf246016454`，15,182,503字节；私有源码归档 SHA-256 为 `93ae3a1b299be89b898382702d0439d79149fd4915e9f83291eeb1d3b8158bdd`。下方侧栏包及更早包仅用于对应历史整版回退。验收和发布状态见[多语言验收](multilingual-acceptance.md)。

2026-11-07 双模式标签提示与配套资源修订已交付8787；2129项产物核验、锁定重建与恢复通过，其余2127项原产物SHA保持。开发库仍待安装，8793不变。范围见[本轮验收](install-dual-mode-acceptance.md)，下方原资源迁移记录保留其历史范围。

本轮只整理应用维护的资源。原主题、上传文件、已有生成 HTML 不参与压缩；权限节点、HTTP 业务接口及数据库中的资源地址不变。旧 URL 由逐文件清单内部读取新实体，不重定向，不建立旧目录或软链接。

## 目录职责

| 位置 | 内容 |
| --- | --- |
| `public/admin-assets/css、js、images` | 后台应用资源的发布产物 |
| `public/admin-assets/vendor` | Pear、各版本 Layui、TinyMCE、JSONEditor 和后台历史组件，保留组件内部结构 |
| `public/admin-assets/common/plugins、static` | 前台组件及跨端资源，含 Ueditor、Layer、Laydate |
| `public/admin-assets/common/compat` | 原主题 SHA 命中后才使用的兼容补丁产物 |
| `public/admin-assets/common/error-pages` | 公共错误页面 |
| `public/admin-assets/common/generated/lang/foreign_global.js` | 历史基线实体；公开新旧地址改为读取当前缓存模式中的数据库派生JS，不再业务写入主题目录 |
| `app/cms/resources/defaults、fonts` | Pear 默认配置、PHP 水印字体；不公开 |
| `app/view/cms/legacy-template` | 原公共目录中的模板片段和跳转模板；不公开 |
| `app/cms/resources/legacy-assets` | 暂无法确认删除依据的组件说明、示例和历史适配器；PHP 原件以 `.php.txt` 私有存档，不执行 |
| `app/cms/resources/asset-build-inputs.tar.xz` | 可维护的压缩前源码，包括原定制组件源；只在外部构建目录解包 |
| `app/cms/maintenance` | 锁定构建配方、npm 锁文件、核验和恢复工具 |

`template` 只存放各主题；系统公共资源位于 `public/admin-assets/common`，旧 `_shared` 仅作为登记的 URL/只读别名保留，不存在实体目录。`default、1003、1004` 原目录和字节保持。1003 仍仅 PC。新增独立原生主题 `webman-default`，不通过压缩构建或运行时改写原主题来转换标签。`public/uploads、public/html` 保持原用途；根目录的 favicon、robots、站点关闭页面及既有头像入口保留。

## 清单与访问边界

`app/cms/resources/asset-manifest.json` 的 format=2 逐项记录原路径、用途、引用、动态依赖、版本或定制标识、原源码及产物 SHA、产物路径、大小与处理动作。该 JSON 采用紧凑序列化，可用格式化查看器阅读。不得手工添加未经验证的整目录公开规则。

`AssetManifest::url()` 生成 canonical URL 和产物版本，保留查询参数、片段和部署前缀。`AssetResolver::resolve()` 保留原接口，接入登记的新旧 URL。旧 PHP 文件读取由 `ResourceFilesystem` 映射，写入目标不经过资源映射。旧目录不存在时依然正常读取。

后台接口如 `/app/admin/config/get` 不受资源路由接管。组件 HTML 只有清单中逐文件允许的对话框、公式编辑器、地图和错误页可读；原模板 HTML、PHP、安装资料、配置及私有源码仍不可读。带 `/public` 的旧地址、组件相对图片／字体／模块地址也有明确登记。

新旧地址按实际内容 SHA 生成一致 ETag，支持 HEAD、Range、If-Range 和 304。旧地址使用重新验证缓存；有正确版本的固定资源可长缓存。可变语言文件仍重新验证，缓存生成路径由输入 SHA 确定。原主题补丁适用条件仍检查原文件 SHA。

## 维护命令

使用 PHP 8.2 及以上，在项目目录执行：

```sh
php webman cms:assets check
php webman cms:assets verify
php webman cms:assets build
php webman cms:assets restore --archive /absolute/path/resource-assets-unified-system-v1.tar.gz
php webman cms:assets restore --archive /absolute/path/resource-assets-unified-system-v1.tar.gz --apply
```

`check` 检查清单、路径和文件存在性；`verify` 额外检查固定产物大小与 SHA。唯一语言生成文件报告实际与基线 SHA，允许正常业务变更，但不存在或链接仍失败。`build` 默认只核验，不写入。`restore` 默认预演，只有 `--apply` 才写；现有不同内容（包括语言生成文件）一律拒绝覆盖。

正常运行及安装不需要 Node，不联网构建。安装器预检所有必要资源完整性；新站应使用预构建资源或先恢复与源码配套的 format=2 包。安装模式不改变资源地址、上传路径或资源清单访问边界；基本设置与自定义变量复制按钮使用服务端返回的模式标签。

当前安装只有一个前缀，默认 `wa_`，后台表自动使用 `wa_backend_`；不创建、不依赖 `cms_install_state`。标准 `install` 无锁时允许预检和主动重装，仅替换完整目标清单中的同名冲突表；不同前缀的旧表及清单外表保留。完整 SQL 预检通过前不得删除，进度与模式、SQL SHA、前缀和引擎绑定。成功写安装锁后封存目录，收尾恢复不重复导入。资源恢复命令不操作这些数据库安装步骤，详细安装包规则见[源码包说明](source-package.md)。

## 外部重建

将 `app/cms/maintenance/package.json` 和 `package-lock.json` 复制到项目外构建目录后执行 `npm ci --ignore-scripts`，依赖固定 Terser 5.44.1、clean-css 5.3.3。不要在项目根或运行站创建 node_modules。

```sh
CMS_ASSET_NODE_MODULES=/absolute/build/node_modules \
CMS_ASSET_NODE_BINARY=/absolute/node \
php webman cms:assets build --apply --output /absolute/empty-output
```

重建使用私有源码包，未加工二进制使用已核验发布实体。完全缺失资源的站点应先用资源包恢复，再重建。提供 `--input /absolute/source-root` 可以使用已展开源码；其路径及源码 SHA 必须与审核后的清单一致。

构建先验证全部输入、路径及目标冲突，再编译到外部临时目录，与既定产物 SHA 校验后发布；失败撤销本次新增文件，不覆盖既有修改。源码或配方变化应在隔离目录审核并更新版本清单，不能用旧清单强行覆盖输出。原始主题文件绝不作为构建输入。

首轮关闭变量改名和 JS 激进优化，CSS 不自动导入和路径重定位。产物不公开 source map，保留版权声明。Ueditor `dialogs/scrawl/scrawl.js` 因原文件顶层 return 未通过压缩解析，保留原字节；不同版本或功能未经核验的同名 min 文件不直接替代。

## 可删除 runtime 与语言包

ForeignPack 从系统主语言读取外贸配置，外贸助手词库独立于普通语言变量。从数据库生成语言JS并与 `ey_global.js` 合成，派生内容经签名缓存存储，文件模式使用 `runtime/cache/assets`，Redis 模式使用 Redis；不业务改写固定公开实体。旧URL及PHP只读映射读取同一派生内容。普通语言词库来自数据库和私有JSON，缓存位于 `runtime/cms/languages`。停止全部 worker 后删除 runtime，再启动会按需重建。主题、上传、静态 HTML、清单和构建来源不在 runtime 中。

## 恢复及回退

format=2 恢复清单为 `app/cms/resources/source-package-assets.json.gz`，绑定资源包 SHA、完整成员表及每项 SHA／大小。拒绝旧 format、旧目标目录、路径穿越、链接、重复成员、损坏内容及冲突文件；验证所有成员后才写入，并在写入失败时回滚本次新增文件。

历史侧栏宽度调整配套包（2026-11-08，仅对应当时清单）：

```text
/Volumes/disk/site/php/dzhcms/webman-cms-preserved-20261107-v1/sidebar-width-20261108-v1/resource-assets-sidebar-200.tar.gz
```

- 包 SHA-256：`3b6bb851b58d5195395f58ae447db296656e03cb0afea5812c64200b33781ac0`；大小 15,183,192 字节。
- 私有 `asset-build-inputs.tar.xz` SHA-256：`676b0b40b00f703def87a96dd2c4d53b7d28d9750dd9579ad321f6c72985929d`。
- 基本设置、自定义变量两个 JS 的源码和发布产物已随清单更新；同版 Terser 5.44.1 全量重建、2129 个实体干净恢复及重复恢复在隔离环境核对通过，其余资源 SHA 保持。证据位于本轮外部 `verification/tag-resource-update.json` 与 `tag-resource-verification.json`。

2026-11-08：后台展开侧栏从 232px 收窄至 200px，折叠保持 64px；手机抽屉及 banner 布局共用展开宽度变量。8787 已实测展开／折叠、1280px 与 390px 抽屉，无横向溢出；安装进度页面未修改。仅更新工作区 CSS 的源码、产物和配套清单，其余资源记录保持；资源 verify 及新版包 2129 个实体只读校验通过。证据位于项目外 `sidebar-width-20261108-v1`。前一版 `install-dual-mode-20261107-205718/resource-assets-dual-mode.tar.gz` 仅与历史清单成组回退，不与当前清单混用。

历史工作区 `resource-layout-20261107-v1` 的 `resource-assets-v2.tar.gz` 对应旧清单，不与本轮清单混用。历史包及布局快照只作为成组回退资料；旧 format=1 不允许直接恢复到新布局运行站。

切换前记录当前文件 SHA，并保存代码、清单、资源快照。回退需停全部 worker，成组恢复本批代码、映射和资源后启动；不要覆盖用户新上传内容、数据库业务数据或已有静态 HTML。`.git` 与 Git 索引始终保留。

## 验收范围

历史资源整理结果详见同目录 `resource-migration-acceptance.md`。本次双模式资源修订的隔离检查见上方外部证据；发布结果由主 agent 单独填写。静态源码扫描不能证明外部地图服务、未取得主题或全部 EyouCMS 标签兼容；这些边界继续保留在原兼容清单中。

## 语言与城市UI资源批次（2026-11-08）

本批只修改cms-workspace.js/css发布资源，维护源码在asset-build-inputs.tar.xz，清单revision为2026-11-08-language-city-v1。外部资源包resource-assets-language-city-v1.tar.gz，SHA256为8824507b592112156682fb36363d085e8ed7d76f5b42d685153fb65fb0ae39b8；包与本批快照位于项目外admin-language-city-20261108-202343。2132个唯一实体锁定重建和恢复一致，冲突拒绝覆盖，旧目录不重建。不要使用旧包覆盖本批清单。
