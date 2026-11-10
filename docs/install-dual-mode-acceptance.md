# 安装双模式与精确冲突重装验收

日期：2026-11-07，Asia/Shanghai。状态：已完成隔离验收并于2026-11-07停机部署、重启8787，开发站仍待用户安装。本轮不替用户提交真实安装，不操作原库及8793。

## 已实现范围

- 默认Webman空站，严格`webman`标签／根变量，独立`webman-default`起步主题；勾选兼容后使用`eyou`，可创建空站或上传完整原站SQL。
- 单前缀默认`wa_`，后台固定`backend_`分组；157业务表、7后台表、3状态表。无安装标记表。旧双前缀站保持兼容读取。
- 50MiB上限、2MiB分片、私有会话绑定上传；SQL只执行预检通过的结构及字面值数据，不执行上传的DROP／切库／建库。
- 精确目标表预检、内容摘要及令牌绑定，先完整暂存再删除冲突并逐表发布；不同前缀及同前缀无关表保留。DDL不宣称整库事务回滚。
- config持久进度、完成锁及12位小写随机目录封存；主动恢复标准install并去锁可重装，原数据及历史完成配置不额外阻止。
- 可信后台配置保存模式与随机站点签名，上传不能覆盖。模式安装后只读；编译／生成／Sitemap／可视化含模式及版本，生成另含站点签名。
- 当前编译版本`dialect-v3`。不再使用绕过源检查的整页输出缓存。`public/html`中的HTM／HTML不能通过普通静态资源通道绕过生成签名；旧文件字节保留。

## 隔离实际验证

| 范围 | 结果及证据 |
| --- | --- |
| 三种安装组合 | Webman与Eyou空站各167表、0篇文档；原SQL通过真实HTTP上传、预检及分步安装。`install-webman.json`、`install-eyou.json`、`install-import-http.json`。 |
| 原站导入／引擎 | 开启转换的157张业务表均InnoDB；关闭转换均MyISAM，后台和内部始终InnoDB。两份导入逐表核对原157表、7291行原值与ID，包含42篇文档。`import-byte-values.json`。 |
| 上传与错误参数 | 2MiB分片、相同重复片、追加中断恢复、不同owner／篡改／过大拒绝、关闭安装不重建目录；非兼容伪造导入及密码不匹配在删表前拒绝。`upload-check.json`、`install-webman.json`。 |
| 重装及恢复 | 19项真实数据库检查：167表同前缀、异前缀保留、部分冲突、无关哨兵保留、MyISAM未记进度批次、drop及rename后未记checkpoint、环境／锁／封存失败后只补收尾。无残留暂存表。`recovery-results.json`。 |
| 并发互斥 | 9项补验通过：独立PHP进程的本地flock与独立MySQL连接的GET_LOCK分别拒绝第二个安装执行；无新目标／暂存、335表全库指纹不变，环境目录故障无Warning。`recovery-lock-results.json`。 |
| 严格词法及共享渲染 | 19词法、14渲染、11边界、9动态片段；15组表达式反例，23项PHP根变量检查，357模板扫描。覆盖两方向、include／继承／布局、literal、标签库、空格标签、数组／过滤器／循环／PHP插值及静态变量变量；错误含原始行号。`dialect-*.json`、`independent-expression-regression.json`、`independent-php-roots.json`。 |
| 主题及后台示例 | 实际主题启用拒绝错误模式与混合，缺页仍允许启用，_shared不列为主题；恢复原选中主题。基本设置、自定义变量及FAQ／语言／字段调用原始视图按Webman输出。`theme-mode-integration.json`、`native-backend-check.json`。 |
| 原生主题 | 20个PC/mobile HTTP样本：首页、文章列表／详情、单页、产品、搜索、留言、Sitemap和AI索引；验证双端正文、导航、产品参数，留言真实HTTP token提交后持久化。`native-http.json`、`native-guestbook-submit.json`、`native-product-parameters.json`。 |
| Eyou主题与系统页 | 原版default在导入数据下双端首页、列表、详情及Sitemap响应；AI HTTP遵循原站关闭状态返回404，独立内存开启后系统模板实际渲染66350字节／81链接，不修改导入配置。`eyou-http-final.json`、`eyou-ai-template.json`。 |
| 生成隔离 | 29项公开HTML通道边界；8组实际服务门禁：错误模式、旧编译版本、错误站点签名在render／generate前拒绝。`independent-generated-boundary.json`、`independent-task-signatures.json`。 |
| 删除runtime | 停止隔离worker、删除整个runtime、重新启动；双端首页、Sitemap、AI正常，持久配置SHA不变，未登记旧HTML的GET／HEAD均404，实体原字节保留到测试清理。`runtime-reset-http.json`。 |
| 资源恢复 | 两项标签脚本锁定重建，2129产物完整核验、干净恢复及重复恢复通过；其余2127实体SHA不变。`tag-resource-verification.json`。 |

测试证据、专用工具、隔离副本及回退资料均在项目外：

```text
/Volumes/disk/site/php/dzhcms/webman-cms-preserved-20261107-v1/install-dual-mode-20261107-205718/
```

安装测试仅使用`cmsit_dual_*`与`cmsit_recovery_*`专用数据库，数据库测试串行。本轮PHP语法、资源包SHA及保护核对由主集成记录到同目录verification。

## 8787交付复验

逐文件部署113项代码／主题／基线／资源／文档，随后同步本页最终状态；隔离.env、进度、锁、测试数据均未部署。13项HTTP检查通过：向导默认值、环境11项、首页／后台跳转、伪造导入及字段错误、私有资料无原文暴露；资源完整性验证通过。浏览器实际操作兼容勾选、导入勾选和空字段数据库检查，确认区域依赖、InnoDB默认开启及错误文字显示在对应字段旁，最后恢复默认Webman空表单，未提交安装。

原webman_admin前后168表9826行及逐表SHA相同，新webman_cms_new仍0表；.env、Git索引及1561项既有保护文件SHA保持。证据`live-8787-http.json`、`protected-databases-before-deploy.json`／`after-deploy.json`、`protection-final.json`、`deployment-manifest.json`、`8787-installer-default.jpg`与`8787-installer-validation.jpg`。8788隔离worker已停止，8793未操作。

## 实际边界

- 50MiB是实际实现和限制校验，不冒充已上传50MiB业务站；实际完整SQL样本约1.39MB，验收基线为EyouCMS1.7.7，不认证任意版本／第三方扩展的任意dump。外键、触发器、存储过程及不支持语法会拒绝，不自动重写。
- 当前开发库仍待用户安装。新向导浏览器检查及隔离HTTP安装不能冒充用户数据库安装完成，也不能冒充六类角色浏览器回归。未变更后台权限协议；本轮后台标签输出用原始视图／实际服务验证。
- 原版PHP双引号复杂`"{$root['field']['title']}"`仍可能被旧模板语法二次解析；本轮验证其模式识别，不更改原有解释语义。任意计算生成变量名属于PHP动态执行，不宣称编译时通用静态推断。
- 本地和数据库锁经过独立进程／连接的竞争检查；不将该受控检查扩大为长时间高并发负载认证。静态发布全部历史失败用例未重新执行，任务门禁与多worker HTTP也不代替该范围。
- T17、未提供官方主题、外部地图／短信／邮件配置等旧兼容边界保持开放。本轮不关闭EyouCMS严格兼容项目。

## 部署与回退

主agent以逐文件清单部署；排除隔离.env、runtime、安装进度、凭据、锁及测试数据。原版主题、上传和已有静态HTML不改；Git索引原位保留。停8787全部worker后切换代码／清单／两项资源，再启动向导。恢复时先停worker，按外部`verification/deployment-manifest.json`和`private/pre-deploy`还原本批文件，新加文件仅按清单移除；不覆盖用户.env、上传或数据库。8793不自动同步。
