# 顶部菜单、模式反馈和程序重启验收

日期：2026-11-09。代码已加载至运行站 8787；该站保留 Redis 模式、私有参数及登录会话。本轮只通过 CLI reload 加载代码，运行站按钮的确认窗口已打开并取消；实际重启在独立站 8797 执行。8793、原主题、附件、业务配置及 Git 暂存未修改。

## 改动

- 顶部语言入口支持悬停、点击、键盘及授权语言列表，末项进入“编辑语言”；多语言关闭时仍可进入语言维护。官网预览为带名称和提示的图标。
- 管理员菜单合并个人资料、刷新、全屏、外观、重启及退出。重启仅超级管理员可见及执行；手机隐藏姓名，保留入口。
- 基本设置在保存模式变更时先确认，进入或退出 Redis 提示重新登录。验证/保存期间锁定表单，显示旋转进度，成功显示完成反馈；失败保留输入。
- Redis 验证失败使用2秒红色浮层，并在网站模式行保留详细红字、定位及聚焦；后续修改模式清除旧字段错误。没有更改 Redis 验证或配置优先级。
- 公共确认、进度及提示使用原生 dialog 和已有 CmsUI；淡入、缩放、背景模糊、执行动画及成功反馈遵循减少动态效果偏好。

## 重启实现

服务位于 `app/cms/service/AdminRestartService.php`，独立 CLI 执行器为 `app/cms/maintenance/admin-restart.php`。接口沿用 IndexController 原权限身份并显式登记：

| 接口 | 方法 | 用途 |
| --- | --- | --- |
| `/app/admin/index/restartinfo` | GET | 登录后读取权限和 CSRF；普通管理员的 allowed=false |
| `/app/admin/index/restartstart` | POST | 超级管理员、同源及 CSRF 校验后安排重启 |
| `/app/admin/index/restartstatus?id=...` | GET | 超级管理员及任务所属账号查询实际状态 |

只执行固定项目内的 PHP `start.php restart -d`，客户端不能指定命令、路径或 PID。校验本项目 PID 文件、完整主进程标题及 worker 的父进程，独立执行器使用进程脱离及双层互斥。任务在 runtime/locks，输出在 runtime/logs，均属于运行控制；不使用 cms/views 缓存目录。重启请求排除通用内容发布、缓存失效后处理，单独记录维护日志。

阶段为 scheduled、restarting、ready、failed。只有新主进程的已认证 HTTP 请求实际进入 status，才报告 ready。前端连接失败会等待恢复，不以公开首页200作为完成证据；失败或超时停止动画、恢复按钮并显示错误。前端保存短时任务ID以便刷新后继续观察。保存/上传在途阻止重启，有未保存内容时提示刷新影响。

需要本机 POSIX/pcntl/proc_open 和项目进程控制权限；不支持的环境明确报错。本轮在 macOS、PHP8.4.23、Webman2.1.4/Workerman5.1.8 验证，未声称 Windows 原生重启可用。

## 验证证据

本轮外部资料：`/Volumes/disk/site/php/dzhcms/admin-topbar-20261109-222432`。

- PHP 视图、控制器、路由、中间件、服务和执行器语法通过；改动 JS 的语法通过。`settings-feedback-test.cjs` 验证失败反馈、保留输入、取消不请求、成功跳转。
- 独立 MySQL13325、Redis16389、双worker8797 实际 HTTP 验证重启、并发拒绝、主进程变化、Redis 会话保留、公开页面恢复；原始执行脚本副本为 `restart-http-verify.py`，阶段子任务记录在 `admin-restart-tests-20261109`。另有进程夹具验证任务归属、错误任务ID、超时和旧PID拒绝，不冒充HTTP验证。
- `security-http-results.json`：超级管理员可见；错误 CSRF、Origin、GET重启及未知任务拒绝；错误请求未更换 master 或代际、未产生 cms/views。
- `limited-http-results.json`：匿名请求拒绝；实际临时普通账号可登录，但重启入口权限为false，重启和状态均403，无进程变化；临时账号/角色已按精确ID删除。
- 真实浏览器使用正常HTTP登录建立的独立会话验收，未把登录表单输入作为本次验收项。错误 Redis 密码保存：确认动画可见，2秒红色提示自动消失，模式行红字保留且滚动定位、输入保留；数据库模式仍为2。仅修正独立 .env 后再次保存成功，出现验证进度并进入登录页；master保持不变。
- 重新登录后实际点击重启：显示执行动画，master由53947变为55530，任务ready，原浏览器会话仍可打开后台，自动重新载入，公开页面200；浏览器无控制台警告/错误。详见 `browser-restart-results.json`。切换至Redis后将已有文件模式缓存移到外部保留，重启及访问未新建 cms/views。
- 源站语言菜单在关闭多语言时仍可展开；独立站末项“编辑语言”正确打开语言列表。浏览器验证桌面、1024px和390px；390px顶部及菜单无横向溢出，确认窗宽358px、左右各16px。方向键和Escape可操作菜单。当前真实测试站仅一种语言；多项渲染沿用现有授权语言集合，本轮未新增运行站语言或执行真实内容同步。
- 源站只读观察到已有 runtime/cms、runtime/views，最近修改22:08，早于本轮22:24快照；本轮未更新这些内容，也未删除已有目录。独立Redis重启验证的“不生成”针对干净的候选缓存路径。
- `.env`、`config/cms_cache.php`、`config/database.php`、Composer锁、vendor启动文件及 Git 索引 SHA 与本轮前一致。运行站 `cms:cache-mode --check` 显示模式3且Redis健康。

## 资源、收尾和回退

六项资源为 cms-basic-settings JS/CSS、cms-workspace JS/CSS、cms-ui JS/CSS。可读源码归档、发布产物、资产清单及恢复清单已同步。2,132个唯一实体在工作区、锁定重建和干净恢复三方SHA一致；Ueditor原有顶层return解析例外仍保留原字节，未扩大豁免。

配套包 `resource-assets-admin-topbar-v1.tar.gz` 为15,191,457字节，SHA256 `d193563da81aacfc5c1ecf9a47dbbcc4949d7c29ed91230dacc5305d966ba8e6`；源码归档SHA256 `5ef68ebc3d3e53384bb8243cb92e6a7027347ba1bfa2532c8e57869a20d0aac1`。见 `package-result.json`、`asset-verification.json`。

独立站结束后停止，.env及原模式2恢复，已有文件模式缓存归还；临时登录桥和本轮启动的自有Redis/MySQL容器停止。用户Redis16379及运行站服务未停止，未改8793。恢复状态见 `cleanup-results.json`。

回退时成组恢复外部 before 中的视图、六项资源、资产清单及源码/恢复归档，移除本轮重启服务和执行器，并只撤销 IndexController、路由及 AccessControl 的重启接入后reload。不要覆盖此前业务变更、私有配置或Git暂存；不要使用旧资源包匹配新清单。

## 2026-11-10 错误提示去重

Redis 保存失败清空顶部状态文本，只在网站模式行保留详细红字，继续显示2秒提示并滚动聚焦。其他保存错误仍显示顶部错误。项目外 `settings-error-single-20261110/settings-feedback-test.cjs` 验证单处持久提示、其他错误、成功跳转及取消行为；本次未修改 Redis 配置或提交运行站设置。

本次脚本语法及交互检查通过；2,132个资源在工作区、锁定重建与干净恢复中SHA一致，8787实际HTTP脚本匹配新清单。已reload加载资源版本；私有配置和Git索引SHA保持。未执行真实Redis故障或模式切换。

## 2026-11-10 顶部下拉高亮及语言提示

管理员菜单显式覆盖Layui的dd.layui-this整行底色，选中、悬停、键盘聚焦改用浅蓝背景及圆角，聚焦保留柔和边框；语言菜单统一同色。删除单语言列表底部数量提示和同类title文案。8787浏览器确认选中行背景透明、项目背景rgb(241,245,251)，实际个人资料选中与键盘聚焦效果正确，语言下拉不再出现数量提示。仅打开菜单及读取个人资料，未保存业务数据。截图及构建资料在项目外 `topbar-soft-highlight-20261110`。

本次JS语法、2,132项工作区/重建/恢复资源SHA、两项新资源HTTP字节检查均通过；8787已reload，私有配置与Git索引保持。
