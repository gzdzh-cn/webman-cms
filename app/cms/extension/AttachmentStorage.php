<?php
declare(strict_types=1);
namespace app\cms\extension;

interface AttachmentStorage
{
    /** Implementations must not delete the local source or overwrite an object. */
    public function upload(array $config, string $path, string $object, string $mime, ?\Closure $transport = null): array;
}
