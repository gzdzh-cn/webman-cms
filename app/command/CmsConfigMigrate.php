<?php
namespace app\command;
#[\Symfony\Component\Console\Attribute\AsCommand('cms:config-migrate')]
class CmsConfigMigrate extends \app\cms\console\DeploymentCommand {}
