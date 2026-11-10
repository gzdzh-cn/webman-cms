<?php
declare(strict_types=1);

namespace app\cms\support;

/** Composer dependencies use application-managed configuration; package events run no installers. */
final class ComposerScripts
{
    public static function install(object $event): void
    {
    }

    public static function uninstall(object $event): void
    {
    }
}
