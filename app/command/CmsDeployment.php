<?php
declare(strict_types=1);
namespace app\command;
#[\Symfony\Component\Console\Attribute\AsCommand('cms:deployment')]
class CmsDeployment extends \app\cms\console\DeploymentCommand {}
