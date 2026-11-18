# 002 企业短信投递日志

由 `app/cms/database/Migrations.php` 根据列是否存在及长度执行，可重复运行，支持自定义 CMS 前缀。

- `sms_log.unique_id varchar(100) NOT NULL DEFAULT ''`：留言通知业务去重标识。
- `sms_log.mobile` 扩展至 `varchar(32)`：保存国际号码；保留旧值。
- `sms_log.ip` 扩展至 `varchar(45)`：保存完整 IPv6 地址；保留旧值。

该迁移不重建表、不删除旧日志、不修改原始 EyouCMS SQL。数据库升级先备份并使用 `webman cms:database-init --dry-run` 检查。
