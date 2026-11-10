# PHP 正式配置与本地覆盖验收（2026-11-09）

2026-11-10 后续修订：现有 .env 的数据库覆盖项允许随本次安装表单在收尾阶段同步更新，其他项保留。下文“不修改 .env、冲突拒绝”的描述为当时验收行为；当前代码及独立证据见[已有环境配置安装验收](install-existing-env-acceptance.md)。

正式数据库保存值位于 config/database.php 固定受管块。每个键按本地 .env 存在项、启动时捕获的显式进程环境、PHP 保存值取值；空密码、false、0 不按空值回退。HTTP、CLI、前后台 ORM、PDO、静态生成和重启共用读取入口。正式默认关闭调试及 SQL 明细日志，密钥仍在项目外，缺失直接拒绝签名功能。

新 FORMAT3 安装写固定 PHP 受管块，不创建或修改 .env；只允许固定字段和字面量，语法/AST、权限、链接、文件身份、互斥锁、同目录临时文件及原子替换检查均保留。有效覆盖与表单冲突时先报具体键，再拒绝数据库操作。旧 FORMAT3 无存储版本的任务保留原收尾策略，FORMAT2 文件原字节不变。安装写此固定文件属于明确例外，其他 Web PHP 生成仍关闭。

证据全部保存在项目外 /Volumes/disk/site/php/dzhcms/php-config-20261109-144939，不进入源码包或 public。隔离 MySQL5.7 容器 cms-php-config-20261109、端口13325；HTTP8797/8798。隔离数据库 php_config_site 为运行站只读一致快照，167表、7431行；只修改隔离账号和业务记录作测试，并还原测试标题与主题源文件。

## 已通过

- Webman 演示模式无 .env 的 HTTP 新装、PHP 配置保存、167表完成、PHP 监听地址自动重启、同浏览器一次免密交接、后台登录和首页：http-install.json。
- 原3144 SQL 经真实 HTTP 分片上传后完整导入专用空库 php_config_eyou，无 .env 写入；PHP 配置保存、重启、交接及首页通过。带冲突 DB_NAME 的本地覆盖返回400并明确配置键，覆盖文件不改写；原 SQL SHA 保持：http-eyou-install.json。CLI 安装封存只读核验通过。
- PHP-only 实际连接检查：前台 Illuminate、后台历史 mysql/cms 别名、Think ORM、持久状态 PDO 和直接连接读取同一专用数据库及对应前缀：http-helper.php connections。
- PC、手机 UA 及明确 goto 的首页，栏目、详情；后台正常验证码登录、栏目/文档/语言维护读取及标题保存还原；普通模板读取、保存、还原；静态生成 worker 完成两终端4个首页变体且0失败：http-regression.py/http-extended.json。旧插件、可视化及 PHP 生成入口登录后仍拒绝。
- 配置隔离测试覆盖完整/部分覆盖、进程快照、空密码、布尔false、整数0、无效值和端口、特殊字符及含标记字符串的密码、块外字节保留、只读文件/目录、符号/硬链接、异常锁和并发占锁、未签名及篡改缓存/会话、站点身份变化、同值不同来源、缺密钥、新runtime：tests/database-settings.php、environment-check.php、writer-check.php、tests/security-config-isolation.php。
- 安装器配置检查、交接来源/有效值变化拒绝、旧交接兼容、PHP 监听地址读取，以及配置写失败后的 database_complete 收尾重试：installer-config-test.php、installer-process-conflict-test.php、installer-lock-retry-test.php。后者直接调用收尾方法并验证表检查点不变，未通过故障注入重跑完整数据库导入，不冒充全流程中断演练。
- 源码包固定空白数据库模板、权限与无凭据输出检查：package-check.php。未另行制作并发布新安装压缩包。

## 现有边界

3144 原主题缺少 pc/mobile 的 lists_search.htm；搜索仍按既有缺页返回错误，本轮不补写用户主题。此次验收为真实 HTTP/CLI 和文件隔离测试，未完成所有后台页面的浏览器视觉回归，也未验证断电/磁盘故障。本机进程与部署均为 lizheng，未建立独立服务账号的操作系统权限隔离。生产部署必须撤销服务账号对源码、config及其父目录的写权限，按 docs/deployment.md 配置；普通模板写权限仅开放授权主题范围。

缓存/认证会话的新站点身份绑定最终数据库配置，首次切换到 effective-config-v2 会使旧缓存和会话失效，需要重新登录。后续仅将同一有效值从 .env 移到 PHP 不再使它们失效；安装交接仍严格核对来源和生效值。

## 发布与回退

差异发布和独立逐文件备份由本目录外 release.py 执行，manifest记录发布前后 SHA 与模式。发布前逐一核对基线及 Git 索引，运行库不导入、不迁移、不重设账号；本地 .env、原 SQL、主题、附件、业务数据、外部密钥及8793保持。当前站有效值安全写入 PHP 保存块，候选测试数据库、端口和测试密钥禁止发布。

回退：先停止8787，确认 manifest 中每个已发布文件仍为发布 SHA；若用户已继续编辑则先保留差异。按 release-backup/ 源文件和清单恢复旧版，新增文件移除；保留 .env、数据库、用户主题与附件。旧运行目录只作私有证据，不整体覆盖新版本；建立空runtime再启动。回退不会恢复旧登录状态，需正常重新登录。发布结果另记本页末节。

## 本次发布结果

2026-11-09 已完成54文件差异备份并发布8787，服务正常重启。实际3144 PC/mobile首页及后台验证码接口HTTP200；发布后CLI检查PHP保存值与生效配置一致，前后台ORM、PDO均连接原运行库且外部密钥有效。原库167表7431行与发布前一致。全量源文件基线仅出现清单中的批准差异，.env、外部密钥、原主题/附件/SQL及Git暂存状态保持，8793未操作。隔离服务和本轮专用数据库容器已停止，证据和数据库保留。

备份：/Volumes/disk/site/php/dzhcms/php-config-20261109-144939/release-backup；清单：同目录release-manifest.json。按该目录release.py rollback可逐文件校验后回退；若发布文件已有后续用户修改会拒绝覆盖。旧会话需重新登录。
