<?php

namespace App\Modules\Courses\Http\Requests\Admin\Concerns;

trait NormalizesCourseSeoInput
{
    /**
     * @return array<string, mixed>
     */
    protected function seoRules(): array
    {
        return [
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'canonical' => ['nullable', 'string', 'max:500', 'url'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mergeSeoAttributes(array $data): array
    {
        return [
            'meta_title' => filled($data['meta_title'] ?? null) ? $data['meta_title'] : null,
            'meta_description' => filled($data['meta_description'] ?? null) ? $data['meta_description'] : null,
            'meta_keywords' => filled($data['meta_keywords'] ?? null) ? $data['meta_keywords'] : null,
            'canonical' => filled($data['canonical'] ?? null) ? $data['canonical'] : null,
        ];
    }
}
