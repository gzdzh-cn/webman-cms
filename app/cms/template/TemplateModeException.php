<?php
declare(strict_types=1);
namespace app\cms\template;

final class TemplateModeException extends \RuntimeException
{
    public function __construct(public readonly string $templatePath, public readonly int $templateLine, public readonly string $reason)
    {
        parent::__construct($templatePath . ':' . $templateLine . '：' . $reason);
    }
}
