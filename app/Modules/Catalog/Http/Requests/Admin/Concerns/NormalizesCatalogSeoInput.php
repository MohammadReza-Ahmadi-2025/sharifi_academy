<?php

namespace App\Modules\Catalog\Http\Requests\Admin\Concerns;

use App\Support\SeoSlug;

trait NormalizesCatalogSeoInput
{
    protected function mergeCatalogSeoFields(): void
    {
        $this->merge([
            'slug' => $this->filled('slug')
                ? SeoSlug::fromOptional((string) $this->input('slug'))
                : null,
            'canonical' => $this->filled('canonical') ? $this->input('canonical') : null,
            'meta_title' => $this->filled('meta_title') ? $this->input('meta_title') : null,
            'meta_description' => $this->filled('meta_description') ? $this->input('meta_description') : null,
            'meta_keywords' => $this->normalizeMetaKeywords($this->input('meta_keywords')),
        ]);
    }

    /**
     * @return array<int, string>|null
     */
    protected function normalizeMetaKeywords(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            $keywords = collect($value)
                ->map(fn ($item) => is_string($item) ? trim($item) : '')
                ->filter()
                ->values()
                ->all();

            return $keywords === [] ? null : $keywords;
        }

        if (! is_string($value)) {
            return null;
        }

        $keywords = collect(preg_split('/[,،]/u', $value) ?: [])
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();

        return $keywords === [] ? null : $keywords;
    }

    /**
     * @return array<string, mixed>
     */
    protected function catalogSeoRules(): array
    {
        return [
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'meta_keywords' => ['nullable', 'array'],
            'meta_keywords.*' => ['string', 'max:100'],
            'canonical' => ['nullable', 'string', 'max:255'],
        ];
    }
}
