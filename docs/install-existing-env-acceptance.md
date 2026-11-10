# 已有 .env 时的安装验收

日期：2026-11-10。代码已reload至8787，实际HTTP与浏览器安装页环境检查通过；未在运行站提交安装或修改数据库、私有配置和安装记录。

## 2026-11-10 合法 .env 格式误报修复

用户报告的“.env 格式无效”已复现：Environment 在主进程启动时预加载，worker reload 继承旧类，不能获得后来新增的 Environment::parse 方法；InstallerEnvironment 捕获所有 Throwable 后将缺失方法错误误报为配置格式错误。当前运行站 .env 经独立CLI解析有效，未修改该文件。

解析提取到独立 EnvironmentParser，安装器通过原有 Environment::processEnvironment 取得启动环境快照，避免依赖旧主进程没有的新方法。Environment::parse 保留为委托入口供新进程使用。安装器取消 Throwable 格式错误包装；解析器只处理实际 dotenv 格式异常，去除可能含凭据的第三方异常文本，重复键和引号等错误继续拒绝。

本次证据位于项目外 `/Volumes/disk/site/php/dzhcms/install-env-format-20261110-v1`：预加载旧版 Environment 可稳定复现原误报；修复后29项父进程检查及实际fork子进程的inspect/synchronize/重复收尾通过，涵盖旧类继承、原配置解析一致性、Redis项保留和无凭据异常。已有95项安装配置检查和环境优先级回归通过，三个PHP文件语法通过。

8787仅reload，原master保持；12次真实HTTP action=check 使用合成参数指向本机关闭端口1，均通过配置校验并到达预期连接失败，未连接运行数据库、未执行预检/安装。运行站 .env、database.php、cms_cache.php、安装/封存记录、composer.lock与Git索引摘要保持。该HTTP证据证明格式误报修复，不宣称本轮完成真实安装或浏览器安装验收；此前独立完整安装证据见下文。

## 当前行为

安装向导与CLI继续使用本次输入的数据库参数。旧 .env 中存在数据库值不再阻止检查连接、预检或安装；这两个检查不改写文件。数据库完成后先写 config/database.php 固定受管块，再通过 InstallerEnvironment 原子同步 .env 中已有的 DB_HOST、DB_PORT、DB_NAME、DB_USER、DB_PASSWORD、DB_PREFIX、DB_TABLE_LAYOUT、CMS_DB_PREFIX、ADMIN_DB_PREFIX。不创建 .env，不添加原本不存在的键；Redis、监听地址及其他非数据库配置的原始字节保留。正常运行仍按本地文件、显式进程环境、PHP保存值的优先级读取。

新写入环境文件权限为0600。使用同目录临时文件、文件与目录身份检查、专用互斥锁、解析及值回读校验；正确处理export、引用键名、CRLF、无末尾换行、多行内容及特殊字符。预检拒绝不可更新的只读文件、链接、重复键、无效格式和已占用更新锁，错误不返回凭据。没有本地同名键覆盖的显式进程变量若与输入冲突，须修改启动环境并重启；本地存在同名键时可在收尾更新并覆盖旧进程值。

配置保存失败时保留database_complete和逐表检查点，修复权限后使用原设置重试收尾。两个配置文件分别原子保存，不宣称跨文件事务；安装锁及自动重启只在配置完成后继续。冻结FORMAT2及无配置存储版本的旧FORMAT3任务保留原实现。

## 独立证据

全部位于项目外 `/Volumes/disk/site/php/dzhcms/install-existing-env-20261110`。独立MySQL5.7容器 cms-install-env-20261110，端口13326；专用库 install_env_webman2、install_env_eyou，HTTP8798/8799各双worker。Redis指向不可用测试端口1，安装不依赖Redis。

- environment-install-test.php / unit-results.json：95项配置和文件检查，包括完整/部分/空配置、多行值、假赋值行、转义、字节保留、只读、链接、重复键、锁占用及重试幂等；process-env-test.php另验证进程冲突与本地覆盖。
- http-install.py / http-results.json：11项真实HTTP验证。旧 .env 的九项数据库配置全部与表单不同、旧数据库不可用，仍能检查及预检；错误表单凭据被拒绝且文件不变。Webman演示安装完成167表，更新参数、保留Redis项、自动重启、一次免密交接、后台及首页均通过。
- http-eyou-install.py / http-eyou-install.json：9项真实HTTP验证。部分旧配置与空数据库密码，通过SQL分片上传、Eyou仅结构安装167表、配置同步、自动重启、后台交接及首页。SQL采用可信cms-empty.sql副本，选择原3144主题；不是重新导入原3144业务数据。
- finish-retry-test.php / finish-retry-results.log：12项收尾方法夹具检查。数据库配置锁占用、.env只读时维持pending；修复后配置更新，原表检查点不变。该项为方法夹具，不冒充真实HTTP故障恢复或断电演练。
- environment-regression.php、database-settings-regression.php及其日志：原环境优先级与PHP保存值回归通过。改动PHP语法、部署脚本AST通过。
- 8787 /install 和 /install/status 仅GET，浏览器环境项全部通过，预检按钮可用；没有填写或提交真实站安装表单。.env、database.php、cms_cache.php、安装身份/封存记录、composer.lock和Git索引SHA保持。8793、原主题、上传和SQL未操作。

早期夹具用错误长度测试密钥导致worker启动失败，修正后不计入通过结果；第一轮候选用start.php启动，与既有交接的webman进程身份约束不符，数据库安装成功但未完成自动交接。随后用干净installer副本、专用新库和标准php webman start -d重新完整验收通过。早期candidate及其专用库仅为失败证据保留。

## 发布与回退

运行站只reload加载代码，未重启或清缓存、未同步测试配置；私有配置和Git索引核验保持。本次改动及摘要见外部changes.json，已有文件备份在before目录，新InstallerEnvironment.php为本次新增。按各文件摘要核对后成组恢复本次代码和文档；不得覆盖用户后来修改或恢复测试配置。私有数据库设置、安装输入和测试密钥不进入版本库。新安装包排除config/.cms-install-env.lock。

独立8798/8799及本轮MySQL容器均已停止，数据库与证据保留；运行站8787保持运行。
