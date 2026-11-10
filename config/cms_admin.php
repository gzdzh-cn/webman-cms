<?php
/** CMS-owned backend settings. The legacy namespace is an identity, not a plugin. */
$settings = [];
foreach (['app', 'database', 'thinkorm', 'view', 'translation', 'log', 'redis', 'event', 'dependence'] as $name) {
    $settings[$name] = require base_path("app/cms/admin/config/$name.php");
}
return $settings;
