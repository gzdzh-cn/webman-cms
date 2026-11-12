#!/usr/bin/env php
<?php
declare(strict_types=1);

/** Inspect selected historical single-page structures; writes require a matching plan. */
ini_set('zend.exception_ignore_args', '1');

$usage = <<<'HELP'
Usage:
  php webman cms:repair-single-pages --ids=12,34 [--admin-lang=cn]
  php webman cms:repair-single-pages --ids=12,34 [--admin-lang=cn] --apply --fingerprint=SHA256

Default: inspect only. Review plan/issues and retain the returned fingerprint.
Apply only inserts missing blank structures and refuses conflicts or changed plans.
It preserves existing content, translations, IDs and relationships; it does not copy source content.
Use the same selected IDs and administrator language for inspection and application.
HELP;

$options = [];
foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--help' || $argument === '-h') {
        echo $usage, "\n";
        exit(0);
    }
    if ($argument === '--apply') {
        $key = 'apply'; $value = true;
    } elseif (preg_match('/^--(ids|admin-lang|fingerprint)=(.+)$/D', $argument, $match)) {
        [, $key, $value] = $match;
    } else {
        fwrite(STDERR, "参数无效；使用 --help 查看用法。\n");
        exit(2);
    }
    if (array_key_exists($key, $options)) {
        fwrite(STDERR, "参数不能重复。\n");
        exit(2);
    }
    $options[$key] = $value;
}

if (!isset($options['ids']) || !preg_match('/^[1-9][0-9]*(?:,[1-9][0-9]*)*$/D', $options['ids'])) {
    fwrite(STDERR, "必须通过 --ids 指定逗号分隔的正整数栏目 ID。\n");
    exit(2);
}
$ids = [];
foreach (explode(',', $options['ids']) as $value) {
    if (strlen($value) > 10 || (int)$value > 4294967295) {
        fwrite(STDERR, "栏目 ID 超出范围。\n");
        exit(2);
    }
    $ids[] = (int)$value;
}
if (count($ids) > 100 || count($ids) !== count(array_unique($ids))) {
    fwrite(STDERR, "单次最多选择 100 个不同栏目。\n");
    exit(2);
}
$language = isset($options['admin-lang']) ? strtolower(trim($options['admin-lang'])) : null;
if ($language !== null && !preg_match('/^[a-z][a-z0-9_-]{1,9}$/D', $language)) {
    fwrite(STDERR, "管理员语言标识需为2至10位字母、数字、下划线或连字符，且以字母开头。\n");
    exit(2);
}
$apply = $options['apply'] ?? false;
if (($apply && !preg_match('/^[a-f0-9]{64}$/D', $options['fingerprint'] ?? ''))
    || (!$apply && isset($options['fingerprint']))) {
    fwrite(STDERR, "写入必须同时指定 --apply 和检查结果中的 --fingerprint。\n");
    exit(2);
}

try {
    $root = dirname(__DIR__, 4);
    require_once $root . '/vendor/autoload.php';
    \app\cms\config\Environment::initialize();
    \app\cms\secure\App::loadAllConfig(['route']);
    Webman\ThinkOrm\ThinkOrm::start(null);
    $service = new app\cms\service\SinglePageStructureRepairService();
    $result = $apply
        ? $service->repair($ids, $options['fingerprint'], $language)
        : $service->inspect($ids, $language);
    echo json_encode(['mode' => $apply ? 'apply' : 'inspect', 'result' => $result], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), "\n";
} catch (InvalidArgumentException $error) {
    fwrite(STDERR, json_encode(['error' => 'repair_refused', 'message' => $error->getMessage()], JSON_UNESCAPED_UNICODE) . "\n");
    exit(1);
} catch (Throwable $error) {
    // PDO/driver messages can contain SQL values or connection credentials.
    fwrite(STDERR, json_encode(['error' => 'repair_failed', 'type' => get_class($error)], JSON_UNESCAPED_SLASHES) . "\n");
    exit(1);
}
