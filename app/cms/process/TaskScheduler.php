<?php
declare(strict_types=1);
namespace app\cms\process;

/** One non-preemptible unit per turn; deterministic closures also support regressions. */
final class TaskScheduler
{
    private bool $sitemapNext = false;
    public function __construct(private ?\Closure $push, private ?\Closure $generate, private ?\Closure $sitemap) {}
    public function turn(): bool
    {
        if ($this->push && ($this->push)()) return true;
        $first = $this->sitemapNext ? $this->sitemap : $this->generate;
        $second = $this->sitemapNext ? $this->generate : $this->sitemap;
        $this->sitemapNext = !$this->sitemapNext;
        if ($first && $first()) return true;
        return $second ? (bool)$second() : false;
    }
}
