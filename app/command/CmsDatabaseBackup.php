<?php
namespace app\command;
#[\Symfony\Component\Console\Attribute\AsCommand('cms:database-backup')]
final class CmsDatabaseBackup extends \app\cms\console\DatabaseBackupCommand {}
