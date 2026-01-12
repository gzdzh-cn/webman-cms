<?php
// 测试常量在bootstrap.php中的加载效果

// 直接引用common.php
require_once __DIR__ . '/app/common.php';

// 测试常量
$constants = ['UPLOAD_PATH', 'ROOT_PATH', 'ROOT_DIR'];

echo "<h1>常量访问测试结果:</h1>";
foreach ($constants as $constant) {
    if (defined($constant)) {
        $value = constant($constant);
        echo "<p><strong>{$constant}</strong>: " . htmlspecialchars($value) . "</p>";
    } else {
        echo "<p><strong>{$constant}</strong>: <span style='color: red;'>未定义</span></p>";
    }
}

// 测试通过functions.php访问
echo "<hr>";
echo "<h2>通过functions.php访问:</h2>";
require_once __DIR__ . '/app/functions.php';

foreach ($constants as $constant) {
    if (defined($constant)) {
        $value = constant($constant);
        echo "<p><strong>{$constant}</strong>: " . htmlspecialchars($value) . "</p>";
    } else {
        echo "<p><strong>{$constant}</strong>: <span style='color: red;'>未定义</span></p>";
    }
}

echo "<hr>";
echo "<h2>测试完成</h2>";
