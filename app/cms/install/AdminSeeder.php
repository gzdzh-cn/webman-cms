<?php
declare(strict_types=1);

namespace app\cms\install;

use PDO;
use RuntimeException;

/** Fresh-install accounts only. Never used by migrations or normal application startup. */
final class AdminSeeder
{
    private const RECORD = 'cms_install_accounts_v1';

    /** Numeric grants, with excluded nodes and every descendant removed. */
    public static function contentRules(array $rows): string
    {
        $controllers = ['Upload', 'Downloadfile', 'Mediafile', 'Filemanager', 'Rule', 'Language', 'Languagepack', 'Citysite', 'Channelfield'];
        $excluded = ['cms-assets'];
        foreach ($controllers as $controller) $excluded[] = 'plugin\\admin\\app\\controller\\' . $controller . 'Controller';
        $excluded[] = 'plugin\\admin\\app\\controller\\LanguageController@delete';
        $blocked = [];
        foreach ($rows as $row) if (in_array(strtolower((string)$row['key']), array_map('strtolower', $excluded), true)) $blocked[(int)$row['id']] = true;
        do {
            $count = count($blocked);
            foreach ($rows as $row) if (isset($blocked[(int)$row['pid']])) $blocked[(int)$row['id']] = true;
        } while ($count !== count($blocked));
        $ids = [];
        foreach ($rows as $row) if (!isset($blocked[(int)$row['id']])) $ids[] = (int)$row['id'];
        sort($ids, SORT_NUMERIC);
        return implode(',', $ids);
    }

    /** Caller owns the finalization transaction, including both accounts and bindings. */
    public static function initialize(PDO $pdo, string $prefix, string $username, string $password): int
    {
        self::prefix($prefix);
        if (!$pdo->inTransaction()) throw new RuntimeException('安装账号必须在初始化事务内创建');
        if (strcasecmp($username, 'king') === 0) throw new RuntimeException('king 为默认内容管理账号，请为超级管理员使用其他账号');
        $id = self::administrator($pdo, $prefix, $username, $password);
        $q = $pdo->prepare('SELECT value FROM `'.$prefix.'options` WHERE name=?');
        $q->execute([self::RECORD]);
        if ($q->fetchColumn() !== false) return $id; // A committed finalization retry was validated above.
        if (!$id) {
            $pdo->prepare('INSERT INTO `'.$prefix.'admins` (username,nickname,password,created_at,updated_at,status) VALUES (?,?,?,NOW(),NOW(),0)')
                ->execute([$username, '管理员', password_hash($password, PASSWORD_DEFAULT)]);
            $id = (int)$pdo->lastInsertId();
            $pdo->prepare('INSERT INTO `'.$prefix.'admin_roles` (role_id,admin_id) VALUES(1,?)')->execute([$id]);
        }
        if ((int)$pdo->query('SELECT COUNT(*) FROM `'.$prefix.'roles` WHERE name=\'内容管理\'')->fetchColumn() !== 0) throw new RuntimeException('默认内容管理角色已存在，安装状态不一致');
        $rules = self::contentRules($pdo->query('SELECT id,pid,`key` FROM `'.$prefix.'rules`')->fetchAll(PDO::FETCH_ASSOC));
        $pdo->prepare('INSERT INTO `'.$prefix.'roles` (name,rules,pid,created_at,updated_at) VALUES (?,?,1,NOW(),NOW())')->execute(['内容管理', $rules]);
        $role = (int)$pdo->lastInsertId();
        $pdo->prepare('INSERT INTO `'.$prefix.'admins` (username,nickname,password,created_at,updated_at,status) VALUES (?,?,?,NOW(),NOW(),0)')
            ->execute(['king', '内容管理', password_hash('king1234', PASSWORD_DEFAULT)]);
        $content = (int)$pdo->lastInsertId();
        $pdo->prepare('INSERT INTO `'.$prefix.'admin_roles` (role_id,admin_id) VALUES(?,?)')->execute([$role, $content]);
        $pdo->prepare('INSERT INTO `'.$prefix.'options` (name,value,created_at,updated_at) VALUES (?,?,NOW(),NOW())')
            ->execute([self::RECORD, json_encode(['version'=>1, 'administrator'=>$id, 'content_admin'=>$content, 'content_role'=>$role], JSON_THROW_ON_ERROR)]);
        return $id;
    }

    /** Identity-based recovery also accepts old FORMAT3 single-account installations. */
    public static function administrator(PDO $pdo, string $prefix, string $username, string $password): int
    {
        self::prefix($prefix);
        $rows = $pdo->query('SELECT id,username,password,status FROM `'.$prefix.'admins`')->fetchAll(PDO::FETCH_ASSOC);
        $q = $pdo->prepare('SELECT value FROM `'.$prefix.'options` WHERE name=?');$q->execute([self::RECORD]);$value = $q->fetchColumn();
        $record = $value === false ? null : json_decode((string)$value, true, 512, JSON_THROW_ON_ERROR);
        if ($value !== false && !is_array($record)) throw new RuntimeException('安装账号记录无效');
        if (!$rows) {
            if ($record !== null) throw new RuntimeException('管理员状态与本次安装不一致，请使用相同账号密码续装');
            return 0;
        }
        $admin = null;$content = null;
        foreach ($rows as $row) {if ($row['username'] === $username) $admin = $row;if ($row['username'] === 'king') $content = $row;}
        $valid = $admin && (int)$admin['status'] === 0 && password_verify($password, $admin['password']);
        if ($record === null) $valid = $valid && count($rows) === 1;
        else {
            $valid = $valid && count($rows) === 2 && ($record['version'] ?? null) === 1 && (int)($record['administrator'] ?? 0) === (int)$admin['id']
                && $content && (int)($record['content_admin'] ?? 0) === (int)$content['id'] && (int)$content['status'] === 0 && password_verify('king1234', $content['password']);
            $q = $pdo->prepare('SELECT name,rules,pid FROM `'.$prefix.'roles` WHERE id=?');$q->execute([(int)($record['content_role'] ?? 0)]);$role = $q->fetch(PDO::FETCH_ASSOC);
            $expected = self::contentRules($pdo->query('SELECT id,pid,`key` FROM `'.$prefix.'rules`')->fetchAll(PDO::FETCH_ASSOC));
            $valid = $valid && $role && $role['name'] === '内容管理' && (int)$role['pid'] === 1 && $role['rules'] === $expected;
            if ($content) {$q = $pdo->prepare('SELECT role_id FROM `'.$prefix.'admin_roles` WHERE admin_id=? ORDER BY role_id');$q->execute([$content['id']]);$valid = $valid && array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN)) === [(int)($record['content_role'] ?? 0)];}
        }
        if ($admin) {$q = $pdo->prepare('SELECT role_id FROM `'.$prefix.'admin_roles` WHERE admin_id=? ORDER BY role_id');$q->execute([$admin['id']]);$valid = $valid && array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN)) === [1];}
        if (!$valid) throw new RuntimeException('管理员状态与本次安装不一致，请使用相同账号密码续装');
        return (int)$admin['id'];
    }

    private static function prefix(string $prefix): void
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/D', $prefix)) throw new RuntimeException('后台表前缀无效');
    }
}
