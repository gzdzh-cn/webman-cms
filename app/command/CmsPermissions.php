<?php
declare(strict_types=1);
namespace app\command;
#[\Symfony\Component\Console\Attribute\AsCommand('cms:permissions')]
class CmsPermissions extends \app\cms\console\PermissionsCommand {}
