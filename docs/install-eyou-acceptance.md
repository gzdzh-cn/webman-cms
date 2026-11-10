# 安装入口对齐 EyouCMS 验收

日期：2026-11-07。范围为安装入口、完成锁、原版库识别和封存接入，不代表全部 EyouCMS 数据库、账号与模板兼容已完成。

## 当前状态

8787于2026-11-07 17:57:25停机发布本批11个代码/说明文件并重启，安装入口、前台双端和匿名后台页面HTTP复查通过。浏览器原管理员会话仍有效，工作台信息正常，旧 `/install/index.php` 显示“网站已安装，安装入口已关闭”。初次刷新工作台出现此前记录的 iframe 空白，再次正常刷新恢复；本轮未改后台JS，未关闭该既有观察项。没有重新执行验证码登录或真实业务保存。8793未操作。

最终只读审计：168表9826行、表结构、下一自增值与账号/角色/权限全行保持。主题1408、上传153、后台资源1383个文件和3份SQL保持SHA；public/html原无实体，本轮未生成。现有归档 `install_pkfvcenmwift` 的3个文件及私有journal完整匹配，未注入lock、未再次改名；Git索引保持。

## 原版依据与实施

原版根 `index.php` 只有在标准 `install/` 存在且 `install/install.lock` 不存在时引导安装。原版安装器以普通文件锁阻止重复进入，成功时创建锁；站点及版本配置保存在数据库，安装日期/序列号另外写入配置，没有本项目的 `cms_install_state` 标记。

- 本项目首先判断标准目录和普通锁文件。目录不存在或锁存在即关闭入口，不要求数据库标记，不为这个判断连接数据库。锁内容不参与判断；符号链接与错误路径类型拒绝处理。
- 增加 `/install/index.php` GET/POST，复用 `/install` 的同一控制器、会话和 CSRF。未安装页面转向旧地址；API 返回未安装 JSON，不转发原提交。
- 标准目录无锁时，继续核验配置、空库、可信续装和已有库。只读识别原版核心结构及种子，不要求 `wa_*`、CMS 内部表或本地 JSON；原版库关闭重装，未知业务库及连接错误保持阻塞。
- 新装完成先写标准文件锁，再保存本地配置锁并封存；数据库完成后的文件失败提示封存待处理，不重做数据库。封存命令也可核验纯原版库，且不为此向原库添加完成标记。
- 已有封存清单不注入新文件，原归档及 journal 保持原字节。新安装包先校验归档，再仅从独立副本移除 install/install.lock；不触碰源归档。

## 保留差异

原版后台后续可将安装目录改为20位混合字母数字后缀；本项目继续采用用户已确认的安装后立即封存、12位随机小写字母。原版安装可能删除同名表，本项目拒绝覆盖已有数据库。封存只用精确私有记录和 SHA，不扫描任意 install_*。这些措施不改变原版文件锁的入口语义。

识别原版库仅用于阻止误安装、支持资料封存，不会自动生成 Webman 后台账号、权限及业务迁移。本轮不宣称“直接导入任意 EyouCMS 数据库与模板即可全部兼容”。

## 证据与回退

| 验证范围 | 本轮证据及结果 |
| --- | --- |
| 文件入口及API分类 | `verification/independent-file-gate-results.json`：标准目录缺失、普通文件锁、未配置无锁、页面跳转、API JSON和私有路径拒绝。 |
| 原版库识别及封存 | `independent-legacy-install-results.json`：仅157张原版业务表、7918行，无wa或CMS内部表；识别、预演、实际封存和重复执行通过，结构/行SHA保持，未新增表。 |
| 真实HTTP新装及缓存重建 | `independent-http-install-results.json`：Cookie/CSRF预检与安装成功，空站168表且栏目/文档为0，管理员密码哈希核验；锁纳入归档SHA、SQL原字节、重复拒绝；停机整删runtime并重启后PC/mobile、登录页及验证码正常，数据库与1561个主题/上传/HTML文件SHA保持。未冒充浏览器完成新管理员登录。 |
| 锁文件失败与重试 | `legacy-lock-write-results.json`：空锁及权限、已有锁保留、重复写、旧prepared清单不变、无效类型/链接拒绝和不可写目录失败。 |
| 数据库配置分支 | `configuration-state-results.json`：已完成库无本地锁仍关闭、错误前缀未知表阻塞及准确文案、连接不可用保持关闭；文件锁及目录缺失分支无需连接数据库。 |
| 独立识别复核 | `legacy-inspector-stub-results.json`、`legacy-inspector-integration-review.json`：独立提取原SQL结构，校验缺字段/类型/主键/种子及自定义前缀；版本可选且输出受限。真实数据库证据以上项为准。 |
| 已装克隆及开发站 | `candidate-http-health.json`覆盖旧入口GET/HEAD，`live-cutover/http-health.json`核对8787；`live-database-after-cutover.json`、`live-protected-after-cutover.json`及`live-after-cutover-freeze.json`核对数据、资产、归档与Git。后台截图为`live-backend-after.jpg`。 |

所有上表相对路径均位于下述本轮项目外证据目录。检查数量不表示模板兼容率。隔离8788/8789服务已停，新装、纯原版测试库和本轮克隆库均已清理，隔离凭据已移除；清理记录为`independent-database-cleanup.json`及`cleanup.json`。原版源码及SQL未修改。

源码基线、私有数据库快照、Git 索引和隔离证据保存在项目外：

```text
/Volumes/disk/site/php/dzhcms/webman-cms-preserved-20261107-v1/install-eyou-20261107-v1/
```

回退仅恢复本轮变更代码、路由和说明，不恢复或覆盖业务数据、上传、主题、公开 HTML、现有封存目录及 Git 索引。开发站原归档 `install_pkfvcenmwift` 不需要再次改名或补锁；原目录不存在已经满足关闭入口的条件。
