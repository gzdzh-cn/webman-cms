<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;

/** Read the shared public-error switch directly; never retain it across requests. */
class ErrorDisplaySettings
{
    public const KEY = 'seo_native_error';

    public function __construct(private ?CmsRepository $repository = null) { $this->repository ??= new CmsRepository(); }

    public function nativeEnabled(): bool
    {
        try {
            $languages = $this->repository->table('language')->order('id asc')->select()->toArray();
            if (!$languages || !is_string($languages[0]['mark'] ?? null)) return false;
            $rows = $this->repository->table('config')->where([
                'lang' => $languages[0]['mark'], 'name' => self::KEY, 'is_del' => 0,
            ])->select()->toArray();
            return count($rows) === 1 && in_array($rows[0]['value'] ?? null, ['1', 1], true);
        } catch (\Throwable) {
            // The error page must still work when its database caused the error.
            return false;
        }
    }
}
