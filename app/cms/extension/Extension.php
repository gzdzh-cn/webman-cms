<?php
declare(strict_types=1);
namespace app\cms\extension;

/** Only reviewed implementations registered in Registry may implement this contract. */
interface Extension
{
    public function manifest(): array;
    public function defaults(): array;
    public function configure(array $input, array $previous): array;
    public function publicConfig(array $config): array;
    public function assertReady(array $config): void;
    public function disable(array $config): void;
    public function remove(array $config): void;
}
