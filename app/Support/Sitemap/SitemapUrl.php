<?php

namespace App\Support\Sitemap;

use Carbon\CarbonInterface;
use DateTimeInterface;

final class SitemapUrl
{
    public function __construct(
        public readonly string $loc,
        public readonly ?DateTimeInterface $lastmod = null,
        public readonly ?string $changefreq = null,
        public readonly ?string $priority = null,
    ) {}

    /**
     * @return array{loc: string, lastmod: ?string, changefreq: ?string, priority: ?string}
     */
    public function toArray(): array
    {
        $lastmod = $this->lastmod;

        if ($lastmod instanceof CarbonInterface) {
            $lastmod = $lastmod->toAtomString();
        } elseif ($lastmod instanceof DateTimeInterface) {
            $lastmod = $lastmod->format(DateTimeInterface::ATOM);
        }

        return [
            'loc' => $this->loc,
            'lastmod' => $lastmod,
            'changefreq' => $this->changefreq,
            'priority' => $this->priority,
        ];
    }
}
