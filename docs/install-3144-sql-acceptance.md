# 3144 MySQL 备份导入修复

日期：2026-11-09，Asia/Shanghai。

## 原因与修改

源文件为工作区 `3144/3144_web_gzdazhi_2026-11-09_09-59-54_mysql_data_2e57M.sql`。MySQL dump 使用独立的 `--` 行分隔章节；旧 SqlImporter 仅识别后接普通空格的 `-- `，将这些行保留成 SQL，触发 `Unsupported statement in source SQL`。

仅调整 SqlImporter 的引号外行注释识别：两个横线后必须有空白或控制字符，行结束支持 LF、CRLF、CR。字符串字节、可执行注释限制、CREATE/INSERT 白名单和严格数据校验继续沿用。源 SQL 不做文本替换或覆盖；被冻结的 InstallerLegacyV2 文件未修改。

## 验证

证据和工具位于项目外 `/Volumes/disk/site/php/dzhcms/sql-3144-import-20261109-101710`。

- 21 项词法与拒绝规则检查通过，包含独立横线、制表符、三种换行、引号内注释字符、转义引号、非注释双减号、不支持语句和 INSERT 表达式。
- 三份内置基线 SQL 的完整解析语句数组在修复前后相同，见 `baseline-regression.json`。
- 原文件严格解析为源前缀 `ey_`、150 表、7,134 行。新建独立 MySQL 5.7 容器串行验证完整安装目录：保留原引擎和转换 InnoDB 均真实导入167表、7,147行。逐表行数相符，栏目、文章、正文和配置逐字段值相符；这里尚未执行安装收尾产生的配置变更。
- 同一独立端点的新空库通过 InstallerService 的公开 preflight/install 流程：167表，journal=complete，模式eyou，主题3144，新管理员密码哈希核验和安装封存完成。无运行实例，handoff=not_running；这不是浏览器首次登录或整站前台兼容验收。
- 仅结构策略的两种引擎设置均在连接数据库之前按预期拒绝：`源结构缺少空站基础字段：wa_archives_flag.ifsystem`。这是源站旧结构与当前可信空站种子的兼容边界，本次未放宽或伪造字段。

辅助验证脚本初次因测试字符串转义错误中止，修正工具后通过。首次四组合运行暴露上述仅结构拒绝，随后明确将其列为拒绝分支，未报告为成功导入。保留原运行结果语义，不将两次真实导入或单次安装冒充四种策略全部安装成功。

## 使用与发布

迁移3144原站选择“兼容 EyouCMS 模式”，勾选“导入初始化数据”，上传原 SQL 并选择主题3144，再重新预检、核对冲突表后提交。该选项在兼容模式下导入上传 SQL 的原站数据。

8787已执行 `php webman reload --graceful` 加载修复，安装状态HTTP仍为unlocked且环境检查通过，主题3144的PC/mobile均被识别。第一次使用Workerman原始`-g`参数被Webman控制台拒绝，改用其公开`--graceful`参数后成功。未在运行站提交安装或数据库导入；源SQL、`.env`、Git索引、当前journal及plan与修复前SHA相同。8793和原EyouCMS未操作。独立测试容器已停止并保留证据。

源码回退仅需恢复项目外 `SqlImporter.before.php` 到原类文件后重新加载服务，不回退环境、主题、上传或数据库。
