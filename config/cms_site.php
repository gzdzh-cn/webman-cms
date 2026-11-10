<?php
/** Production settings. Local .env values override individual keys when present. */
use app\cms\config\Environment;
return [
    'base_path' => (string)Environment::value('CMS_BASE_PATH', ''),
    'base_url' => (string)Environment::value('CMS_BASE_URL', 'http://127.0.0.1:8787'),
    'theme' => (string)Environment::value('CMS_THEME', '3144'),
    'backup_path' => (string)Environment::value('CMS_BACKUP_PATH', ''),
    'asset_node_binary' => (string)Environment::value('CMS_ASSET_NODE_BINARY', 'node'),
    'asset_python_binary' => (string)Environment::value('CMS_ASSET_PYTHON_BINARY', 'python3'),
];
