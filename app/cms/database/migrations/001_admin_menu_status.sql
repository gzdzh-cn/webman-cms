-- Applied conditionally by app/cms/database/Migrations.php; prefix is configured.
-- The local RuleController supports menu visibility, absent in upstream install.sql.
-- Additive only; preserve all existing menu rows.
ALTER TABLE `{{admin_prefix}}rules` ADD COLUMN `status` tinyint NOT NULL DEFAULT 1 COMMENT '菜单状态';
