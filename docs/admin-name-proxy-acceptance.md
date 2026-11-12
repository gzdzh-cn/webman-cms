# 后台名称及宝塔代理来源校验修复

日期：2026-11-11。

## 行为与原因

- 后台浏览器标题及左上角名称读取基本设置 `web_name`，不再使用后台外观中的默认 `Webman Cms`。已登录时使用当前编辑语言，登录页使用系统主语言；空名称显示“网站后台”。网站信息保存成功后，使用服务端返回的值同步当前后台名称。
- 名称作为文本显示：PHP 模板转义，Pear 的 HTML 字段只在响应边界编码，保存后更新使用 `textContent`。不迁改外观记录、网站设置或数据库结构。
- 线上宝塔“其他项目”站点 `cms` 的实际代理为 `other_cms.conf`，公开域名为 `www.jnguangju.com`，后端监听 3144。代理传入 `X-Scheme`，没有传入程序原先识别的 `X-Forwarded-Proto`；HTTPS 提交因协议不一致被来源校验拒绝。
- 新增 loopback 代理的 `X-Scheme` 协议兼容；`X-Forwarded-Proto` 保持优先。外部客户端的 `X-Scheme` 不作为协议依据，权限、POST 方法、来源域名及其他原有校验继续执行。未修改线上 Nginx 配置。

## 验证范围

- 七个 PHP/模板文件语法通过；53 项行为检查覆盖宝塔 HTTP/HTTPS、标准代理优先级、IPv6 loopback、直接请求、自定义端口、跨站/跨协议/无来源/非法协议拒绝、POST/权限边界、中文名称及 HTML 特殊字符编码。
- 生成入口测试调用真实 `GeneratedModuleController::writeAction`，仅用无数据库操作替代生成回调，确认合法来源到达操作、非法来源仍为 403。未宣称执行完整页面生成链路。
- 本机 8787 完整重启后，浏览器确认后台标题和左上角显示“济南光炬文化传媒有限公司”，基本设置字段名称一致，脚本无控制台错误。未保存运行站配置。
- 2,132 个资源实体校验通过，仅本批 `cms-basic-settings.js` 资源记录发生字节变化，构建输入、清单及恢复包同步到 `2026-11-11-admin-branding-v4`。
- 构建 Linux x86_64 自包含程序，版本 `20261111T123506Z-0e458a0c8e31`，文件名保持 `webman_cms`，不内置可选资源。二进制 SHA256 为 `7e7a455b2e6e66e92e52b3968eb73184b3e9975c6b2bd33cc3ff5bc998f1859e`。
- 在实际 Linux x86_64 服务器执行新版 `cms:runtime --check`：PHP 8.4.27，缺失扩展及 INI 差异均为空。发布前旧部署预检通过；成组替换二进制与改动脚本后，新版 `cms:deployment --check` 通过，使用 `www` 账号完整重启准确目标。
- 内部带宝塔头的 HTTP 和公开 HTTPS 登录页返回正常；线上浏览器标题显示“济南光炬文化传媒有限公司 · 登录”。未使用线上管理员身份提交配置或生成任务，线上完整静态任务仍需管理员登录后实际执行确认。

## 发布与回退

仅替换 `/www/wwwroot/3144.web.gzdazhihui.cn/webman_cms` 和 `public/admin-assets/js/cms-basic-settings.js`，配置摘要在发布前后保持一致。未修改主题、缓存模式、上传、已生成 HTML 或数据库业务记录，未操作用户 Redis 或 8793。Git 暂存清单 SHA256 保持 `ffbc2eb7bdbc99604e34c8b593c90f8a2df0730b90690a4a013e4d940dc34b6d`。

本机代码前镜像、检查结果、旧发布文件及截图位于 `/private/tmp/cms-online-fixes-20261111-4`；服务器前镜像及预检/启动记录位于 `/www/backup/cms-admin-origin-20261111-s0cAv0`。回退时停止准确目标，成组恢复该备份中的二进制与脚本，再以 `www` 启动；不恢复配置或旧 runtime。
