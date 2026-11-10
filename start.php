#!/usr/bin/env php
<?php
chdir(__DIR__);
require_once __DIR__ . '/vendor/autoload.php';
\app\cms\config\Environment::initialize();
\app\cms\secure\App::run();
