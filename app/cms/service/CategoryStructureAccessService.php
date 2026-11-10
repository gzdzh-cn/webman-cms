<?php
declare(strict_types=1);
namespace app\cms\service;

use app\cms\repository\CmsRepository;
use InvalidArgumentException;

/** Original admin Arctype add/pseudo_del shared-language entry restriction. */
final class CategoryStructureAccessService
{
    public function __construct(private ?CmsRepository $repository = null)
    {
        $this->repository ??= new CmsRepository();
    }

    public function run(?string $currentLanguage, callable $operation, bool $write = false): mixed
    {
        // Keep the same language locks through the delegated category write.
        // Read-only insert-page access does not invalidate the CMS cache.
        $result = $this->repository->connection()->transaction(function () use ($currentLanguage, $operation) {
            $sync = new EnterpriseLanguageSyncService($this->repository);
            $sync->lock();
            $main = $this->repository->table('language')->order('id asc')->find();
            $currentLanguage ??= (string)$main['mark'];
            (new LanguageAccessService($this->repository))->assertLanguages([$currentLanguage]);
            $languagePolicy = new LanguagePolicy($this->repository);
            if (!$languagePolicy->enabled() && $currentLanguage !== $languagePolicy->main()) throw new InvalidArgumentException('多语言已关闭，当前只能编辑系统主语言');
            $policy = $sync->policy($currentLanguage);
            if ($policy['shared'] && $currentLanguage !== $policy['main']) {
                throw new InvalidArgumentException('当前语言没有此功能，请切换到【' . $main['title'] . '】语言');
            }
            return $operation($this->repository);
        });
        // CategoryService may clear its nested transaction's cache before this
        // outer transaction commits. Invalidate again once the write is visible
        // to other workers, so they cannot retain a pre-commit refill.
        if ($write) \app\cms\compat\Cache::clear();
        return $result;
    }
}
