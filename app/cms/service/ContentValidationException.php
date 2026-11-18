<?php
declare(strict_types=1);
namespace app\cms\service;
final class ContentValidationException extends \InvalidArgumentException
{
    public function __construct(public readonly array $fields) { parent::__construct((string)reset($fields)); }
}
