<?php
declare(strict_types=1);
namespace app\cms\service;

/** Retired compatibility identity: this class has no HTTP client or provider URL. */
class OfficialLanguagePackClient
{
    public function __construct(array $options = []) {}
    public function provider(): string { return 'local'; }
    public function fetch(string $action,array $payload): mixed
    {
        throw new \InvalidArgumentException('官方语言包联网功能已停用，请使用本地JSON语言包管理');
    }
}
