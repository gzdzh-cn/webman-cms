# EyouCMS 常用模板语法兼容验收

日期：2026-11-10。范围为现有 default、1003、1004、3144 的企业官网模板，以及 webman-default 回归。此记录不关闭严格兼容 T16 总体、T17 和其它历史遗留项。

## 实现及边界

公共编译器新增 `TemplateCapabilities` 和 `TemplateRuntime`，源检查、编译结果 AST 检查及受控适配共用能力定义。原主题不改字节。

| 能力 | 结果与限制 |
| --- | --- |
| `$Request.param.<固定键路径>` | 输出、条件、标签属性共用只读适配，沿用当前请求 param 语义；m/c/a 不改为导航含义。整包、env、session、任意方法禁止。 |
| assign | Eyou、Webman 和无前缀写法；普通变量与固定数组路径，字符串由 var_export 编码；保留变量、内部标签对象、动态路径和语句禁止。 |
| 过滤器及函数 | 保留已有日期、截取等白名单；thumb_img、url、equal_pop_login 走固定适配。缺失值的 html_msubstr 按空文本处理。 |
| thumb_img | 仅本站上传及当前主题图片；拒绝越界和符号链接。源文件最多20MiB、源图1600万像素、边长4096、总工作像素2400万；远程图片只返回原址，不发起请求，GIF 保留原址。受控 GD 输出 PNG 到 uploads/thumb/template；不调用原无限制缩略图及水印链，填充模式使用透明背景。 |
| url | 参数受控，复用现有 UrlGenerator，不增加公开路由。 |
| 展示属性 | 已知循环 empty、position/crumb 的 symbol/style、收藏展示文字使用安全字面量；不全局放开分号、反斜线或冒号表达式。 |
| 会员、收藏及交易 | 已知标签输出空，关闭商城开关，equal_pop_login 固定空；成对标签的内部仍完整检查与编译，危险语法不能藏在禁用分支。likearticle 是相关文档，仍保留。 |
| 原生 PHP、插件、可视化 | 继续拒绝。空的默认 weapp 挂钩保持既有禁用行为，不授权执行插件。固定 users 与支付模板目录保留非阻断诊断，直接业务入口仍拒绝。 |

default PC 原 footer 保留一个无可达入口的隐藏登录对话框，DOM 中仍有隐藏的注册和找回链接；浏览器确认不可见，未加载会员或交易脚本。没有为此改写原 HTML。公开正文、附件和留言仍使用既有业务处理；受限内容不因移除付费标签而放行。

独立编译版本为 `enterprise-syntax-v1`，前台缓存版本升级到 v17，纳入文件、Redis 和生成任务缓存身份；不改变 `dialect-v3`、模板模式、会话代际或已发布 HTML 的签名。生成首页时，目录与 index.html 复用一次编译/渲染和同一发布 SHA，避免包含随机组件标识的两次渲染相互覆盖；仅在新生成成功后替换相同物理文件的旧别名记录。

## 逐模板结论

| 模板 | 官网文件实际扫描并编译 | 终端及验证页面 | 明确缺失或占位 |
| --- | --- | --- | --- |
| default | 57，全部通过 | PC/mobile 首页、单页、文章列表/详情/分页、产品、下载、搜索、预览与静态生成 | mobile lists_guestbook.htm、view_special.htm 为空。 |
| 1003 | 12，全部通过 | 仅 PC；首页、单页、文章列表/详情/分页、搜索、预览与静态生成 | 没有 mobile、产品及下载模板；不伪造跨主题补页。 |
| 1004 | 41，全部通过 | PC/mobile 首页、单页、文章/产品列表及详情、分页、下载列表、搜索、预览与静态生成 | PC/mobile view_download.htm 仅为原包占位提示，不能视为完整下载详情功能。 |
| 3144 | 45，全部通过 | PC/mobile 首页、单页、文章/产品列表及详情、分页、预览与静态生成 | 没有搜索及下载模板；lists_guestbook.htm 双端为空。 |
| webman-default | 52，全部通过 | 原生模式 PC/mobile 首页、文章、下载、搜索等回归 | 不扩大此次 Eyou 兼容声明。 |

共207个官网文件，排除固定旧会员/支付诊断目录；这些诊断目录仍由主题检查扫描。四套 Eyou 主题的 `supported=true`、`blocking_issues=[]`，后台和安装候选扫描一致。全部诊断分别为 default 111、1003 5、1004 8、3144 102 条；其中包含禁用功能、固定旧目录危险语法、空页及占位提示，不等同于官网阻断项。原有 `supported/issues/blocking_issues` 结构保持，无新增数据库字段。

逐文件原始问题见 [诊断清单](template-syntax-inspection-20261110.json)。隔离数据库原有的 lists_single_fuwu、lists_single_1 等栏目名称可能与某主题文件不匹配，后台仍显示“待适配当前站点”，允许启用并列出具体缺页。验收使用独立栏目9001、9004及匹配模板，未修改原包或运行库栏目。

## 实际验收记录

独立目录为 `/Volumes/disk/site/php/dzhcms/template-compat-20261110-v1/candidate`；HTTP8799，两个 HTTP worker，独立 MySQL13328/数据库 cms_template_compat，独立 Redis16410/DB2/专用前缀。未操作受保护8793或用户 Redis16379。该任务目录包含私有测试配置和会话，仅作本机证据，不打包发布。

| 检查 | 实际结果 | 本机证据文件 |
| --- | --- | --- |
| 语法与安全行为 | 89项通过；双模式请求、赋值、中文、引号、反斜线、CSS分号、过滤器、默认值、嵌套条件及危险反例 | behavior-results.json |
| 原版可执行语义对照 | 6组输出一致：请求m/c/a及嵌套键、assign、固定路径、过滤器链、默认值、条件 | semantic-results.json、original-results.json |
| 完整源扫描和实际编译 | 上表207文件全部通过 | corpus-results.json |
| 主题与页面 HTTP | 102项通过；四主题真实启用、现有终端页面、预览、首页静态及别名、禁用接口 | full-http-results.json |
| 栏目/文档静态生成 | 32项通过，含匹配栏目、文档、分页及正文检查 | static-pages-results.json |
| 模式回归 | 87项通过；Eyou四套运营/Redis页面、Webman双端三种缓存模式；开发模式另由完整HTTP覆盖 | mode-pages-results.json |
| 缓存和并发 | 19项通过；命中、include依赖变化与还原、24并发覆盖两个worker、语言/设备/主题隔离，Redis不写runtime缓存 | cache-results.json |
| 签名与站点隔离 | 修改6个测试编译缓存payload而保留旧HMAC后拒绝并重编译；文件/Redis两种站点签名上下文隔离，清理保留其它前缀哨兵 | tamper-results.json、site-isolation-results.json |
| Redis故障 | 5项通过；停止任务自有Redis后公开响应内存编译、会话503、无runtime缓存/会话写入、恢复正常 | fault-results.json |
| 受限内容 | 3项通过；受限文章转拒绝登录、无正文；受限下载明确拒绝且无附件地址；直接注册页404 | restricted-results.json |
| 模板保存 | 3项服务层检查通过；兼容语法可保存，禁用片段内系统调用及插件标签拒绝且旧文件保持 | save-results.json |
| 安装扫描 | 四套候选均无阻断，与后台共用检查；未提交新一轮完整安装 | theme-inspection-results.json |
| 浏览器 | 七组实际终端首页预览；无未展开标签、可操作会员/收藏/交易入口或相关脚本src；1004缺页详情正确显示 | browser-results.json、browser-*.png |
| PHP语法 | 本次14个PHP文件全部通过 lint | syntax-results.json |

原版对照是从本地 EyouCMS1.7.7 源码独立子进程运行的有限子集，使用当前PHP8.4，并非原PHP7整站等价验收。浏览器逐套检查的是首页预览；其它列表、详情、搜索和静态任务由HTTP/API验证，不冒充浏览器逐页操作。测试认证使用隔离的签名fixture会话，未重跑正常密码与验证码登录。站点隔离为同部署的两个站点签名上下文，不等于完整第二站浏览器部署。没有重跑所有角色、所有会员支付旧接口、Redis超时组合或全量旧静态任务恢复。

## 资源事故与恢复

14:06:36 原运行数据库容器 mysql57 被 Docker OOM 停止，随后运行站首页500。本轮最初隔离数据库未设内存限制，曾占约1.7GiB，这是本轮资源控制遗漏。发现后先停隔离库，再直接启动原 mysql57 及原数据卷恢复，未导入、回退或替换运行库。运行站恢复HTTP200。隔离数据库重建容器复用任务自有卷，限制512MiB内存/512MiB总swap及1CPU，关闭performance_schema并缩小缓冲池，实际占用约72MiB。旧任务容器改名并保持停止，不可再次无限额启动。

证据保留在任务根 `oom-container-before.json`（私有）、`mysql57-before-recovery.log` 和 `candidate-mysql-volume.txt`。不能将本轮称为无中断发布。

## 发布、保护与回退

发布状态：已于 2026-11-10T15:21:51.226988+08:00 备份并部署至8787，完整停止旧master 26032 后启动新master 54977，两个HTTP worker及生成worker正常。首页、单页、产品列表、产品详情HTTP200，会员登录403；原管理员会话仍可读取主题列表，四套官网语法无阻断。运行站Eyou/3144/缓存模式2、配置表摘要、站点签名及缓存/会话状态摘要与发布前一致。私有配置、原主题/上传、已发布HTML和当前Git索引/暂存条目逐项核验保持；详见任务根live-http-results.json、live-state-before/after.json、postdeployment-protection.json及live-browser-theme-list.png。

显式部署15个实现文件及本次文档，不复制隔离配置、测试路由、会话、数据库、runtime、主题、上传或生成HTML。原文件备份位于任务根 before；精确SHA清单位于 deployment-files.json。备份必须与部署前当前文件一致，存在外部修改则停止覆盖。

部署前只读基线为 EyouCMS模式、3144主题、开发缓存模式2。保存配置表摘要、站点签名和缓存/会话状态摘要，发布后重新读取比较；不主动切主题、模式或清会话。私有.env、PHP配置、安装记录、原主题与上传均按字节核验。

本轮14:27的只读git status刷新过Git索引stat缓存，最初index文件SHA因此变化；没有执行git add/reset或改暂存条目。后续已保存当前索引 index-at-deployment 和 `git --no-optional-locks ls-files --stage -z` 结果 staged-before.bin，发布须同时保持两者。不能声称最初索引字节始终不变。

回退只处理本轮清单：停止8787目标master，恢复before中对应原文件，删除清单内两项新增PHP及新增文档，再以原PHP入口完整启动；不覆盖整目录，不回退数据库、环境、会话、主题或既有HTML。编译缓存有版本隔离，无需清理用户Redis。隔离验收已收尾：任务8799、任务MySQL及Redis停止，旧无限额任务MySQL仍停止；原mysql57保持运行。测试证据及任务专有卷保留供审阅。

## 2026-11-10 官方模板空图首页修复

本轮8787现场首页返回500，最新异常为 `TemplateRuntime::thumb()` 的“模板缩略图路径无效”。新现场内容的空图由 `get_default_pic()` 转为 AssetManifest 登记的共享 `not_adv.jpg?v=...`，原缩略图校验拒绝查询串，导致 `default/pc/index.htm` 渲染中断；本次错误与会员/支付标签无关。此前语法验收未覆盖此数据分支。

仅修改 `app/cms/template/TemplateRuntime.php`：精确匹配现有 manifest 生成的默认无图 URL 并原址输出，不解码、不缩放、不创建缩略图。不放开其它共享资源、任意查询串或文件路径；尺寸与模式校验仍先执行。无编译版本或缓存/会话代际变更。

项目外备份与证据：`/Volumes/disk/site/php/dzhcms/template-thumb-placeholder-20261110-v1`。原文件在根路径及两种子目录配置中复现拒绝（6项检查），候选51项定向行为检查通过（真实资源清单、隔离 base_path 配置；不连接测试数据库）。PHP语法通过，备份并比对preimage后部署单文件，完整重启8787。实际HTTP复验电脑首页两次、手机首页、当前单页、搜索、默认图GET及HEAD共7项均200，响应模板分别为default对应页面；浏览器首页显示公司、产品、新闻内容。未将此复验扩大记为全部栏目或全主题验收。

旧日志另有栏目指定 `lists_single_1.htm`、`lists_single_contact.htm` 在所选主题缺失，属于独立缺页诊断；本轮不改栏目配置、不跨主题补页。保留现场主题、模式、私有配置、数据库业务数据与Git暂存，不操作8793或用户Redis。

回退：从本轮before目录恢复唯一PHP文件并完整重启目标8787；本轮文档记录可单独保留。

## 2026-11-10 恢复 uploads 后广告仍显示占位图

现场原上传目录已恢复到 `public/uploads`，首页电脑广告两条数据库litpic与实体均存在、文件大小分别60011和97597字节，但HTML仍输出not_adv占位图。TagAdv把文件缺失时经get_default_pic处理后的结果整体缓存，恢复文件不会改变该缓存键；与模板、会员和支付无关。

本轮仅修改 `app/cms/compat/template/taglib/eyou/TagAdv.php`，缓存原始数据库行，将文件检查、URL/target/intro呈现移至每次返回前。该标签原始行缓存使用独立raw-v2键，避免把旧加工缓存误作原始数据；不清全站缓存、不修改缓存/会话代际。单广告TagAd本来每次处理，不改动。原模板、上传和数据库记录保持。

备份/候选/证据在项目外 `/Volumes/disk/site/php/dzhcms/ad-upload-restore-20261110-v1`。隔离缓存和查询适配器回归11项通过，覆盖缺图→恢复→缓存再次命中、旧加工键隔离、target和intro只转换一次及媒体保留；原文件复现恢复后仍占位。PHP语法通过。部署前比对单文件preimage，完整重启8787，PC/mobile首页各两次返回200，均输出各自两张原广告图，8次图片GET均为200 image/jpeg。初次HTTP脚本误把手机版不同广告组当作PC同组，修正断言为各端实际广告组后复验通过，不据此改业务。浏览器可见官方首页轮播广告已显示。私有配置、Git索引及暂存摘要核验保持，未操作用户Redis或8793。

回退：恢复本轮before中的唯一TagAdv.php并完整重启8787；文件缺失处理缓存行为将随原实现恢复。
