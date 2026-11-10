<?php
declare(strict_types=1);
namespace app\cms\install;

/** Field-addressable installer input error; never includes database credentials. */
final class InstallerValidationException extends \InvalidArgumentException
{
    public function __construct(public readonly array $errors, string $message = '请检查标红的安装参数')
    {
        parent::__construct($message);
    }
}
