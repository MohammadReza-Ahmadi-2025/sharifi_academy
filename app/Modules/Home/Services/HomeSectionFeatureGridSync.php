<?php

namespace App\Modules\Home\Services;

use App\Support\Storage\PublicStorageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class HomeSectionFeatureGridSync
{
    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{label: string, text: string, image_url: ?string, image_alt: string, image_title: string}>
     */
    public function mergeImages(Request $request, int $tabIndex, array $rows): array
    {
        $merged = [];

        foreach (array_values($rows) as $featureIndex => $row) {
            if (! is_array($row)) {
                continue;
            }

            $key = "tabs.{$tabIndex}.settings.features.{$featureIndex}.image";
            $removeKey = 'remove_'.str_replace('.', '_', $key);
            $manifest = $request->input($key, []);
            $meta = is_array($manifest) && isset($manifest[0]) && is_array($manifest[0]) ? $manifest[0] : [];

            $imageUrl = filled($row['image_url'] ?? null)
                ? PublicStorageUrl::normalize((string) $row['image_url'])
                : null;

            if ($request->boolean($removeKey)) {
                $imageUrl = null;
            } else {
                $upload = $request->file("{$key}.0.file");
                if ($upload instanceof UploadedFile) {
                    $path = $upload->store('home-sections/feature-grid', 'public');
                    $imageUrl = PublicStorageUrl::forPath($path);
                }
            }

            $imageAlt = (string) ($meta['alt'] ?? $row['image_alt'] ?? '');
            $imageTitle = (string) ($meta['title'] ?? $row['image_title'] ?? '');

            if ($imageUrl === null) {
                $imageAlt = '';
                $imageTitle = '';
            }

            $merged[] = [
                'label' => (string) ($row['label'] ?? ''),
                'text' => (string) ($row['text'] ?? ''),
                'image_url' => $imageUrl,
                'image_alt' => $imageAlt,
                'image_title' => $imageTitle,
            ];
        }

        return array_slice($merged, 0, 3);
    }
}
