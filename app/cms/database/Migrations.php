<?php
declare(strict_types=1);
namespace app\cms\database;

use PDO;

final class Migrations
{
    /** Additive migrations only. Existing values are preserved. */
    public static function apply(PDO $pdo, string $adminPrefix, bool $dryRun = false, string $cmsPrefix = 'ey_'): array
    {
        foreach ([$adminPrefix, $cmsPrefix] as $prefix) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/D', $prefix)) throw new \InvalidArgumentException('Invalid database prefix');
        }
        $pending = [];
        $columns = static function (string $table) use ($pdo): array {
            $query = $pdo->prepare('SELECT COLUMN_NAME,CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');
            $query->execute([$table]);
            return $query->fetchAll(PDO::FETCH_KEY_PAIR);
        };
        $table = $adminPrefix . 'rules';
        if (!array_key_exists('status', $columns($table))) {
            if (!$dryRun) $pdo->exec('ALTER TABLE `' . $table . '` ADD COLUMN `status` tinyint NOT NULL DEFAULT 1 COMMENT \'菜单状态\'');
            $pending[] = '001_admin_menu_status';
        }
        $table = $cmsPrefix . 'sms_log'; $sms = $columns($table); $changes = [];
        if (!array_key_exists('unique_id', $sms)) $changes[] = "ADD COLUMN `unique_id` varchar(100) NOT NULL DEFAULT '' COMMENT '业务通知去重标识'";
        if ((int)($sms['mobile'] ?? 0) < 32) $changes[] = "MODIFY COLUMN `mobile` varchar(32) NOT NULL DEFAULT '' COMMENT '手机号'";
        if ((int)($sms['ip'] ?? 0) < 45) $changes[] = "MODIFY COLUMN `ip` varchar(45) DEFAULT '' COMMENT 'IP地址'";
        if ($changes) {
            if (!$dryRun) $pdo->exec('ALTER TABLE `' . $table . '` ' . implode(', ', $changes));
            $pending[] = '002_enterprise_sms_delivery';
        }
        // Eyou category guestbooks predate free forms and device attribution.
        // A zero form_type preserves their ownership as category guestbooks.
        $guestbookColumns = [
            'guestbook' => [
                'form_type' => 'tinyint NOT NULL DEFAULT 0',
                'users_id' => 'int unsigned NOT NULL DEFAULT 0',
                'is_star' => 'tinyint NOT NULL DEFAULT 0',
                'source' => 'tinyint NOT NULL DEFAULT 0',
                'examine' => 'tinyint NOT NULL DEFAULT 1',
            ],
            'guestbook_attr' => ['form_type' => 'tinyint NOT NULL DEFAULT 0'],
            'guestbook_attribute' => ['form_type' => 'tinyint NOT NULL DEFAULT 0'],
        ];
        $guestbookChanged = false;
        foreach ($guestbookColumns as $suffix => $definitions) {
            $table = $cmsPrefix . $suffix; $existing = $columns($table);
            if (!$existing) continue;
            $additions = [];
            foreach ($definitions as $name => $definition) {
                if (!array_key_exists($name, $existing)) $additions[] = 'ADD COLUMN `' . $name . '` ' . $definition;
            }
            if ($additions) {
                if (!$dryRun) $pdo->exec('ALTER TABLE `' . $table . '` ' . implode(', ', $additions));
                $guestbookChanged = true;
            }
        }
        if ($guestbookChanged) $pending[] = '003_legacy_guestbook_ownership';
        $table=$adminPrefix.'uploads';$upload=$columns($table);$changes=[];
        if ($upload) {
            if (!array_key_exists('storage_meta',$upload)) $changes[]='ADD COLUMN `storage_meta` text NULL COMMENT \'附件存储身份，不含凭据\'';
            if ((int)($upload['url']??0)<2048) $changes[]='MODIFY COLUMN `url` varchar(2048) NOT NULL DEFAULT \'\'';
            if ($changes) {
                if (!$dryRun) $pdo->exec('ALTER TABLE `'.$table.'` '.implode(', ',$changes));
                $pending[]='004_attachment_storage_metadata';
            }
        }
        return $pending;
    }
}
