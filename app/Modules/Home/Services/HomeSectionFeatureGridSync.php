<?php

namespace App\Modules\Home\Services;

use App\Support\Storage\PublicStorageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class HomeSectionFeatureGridSync
{
    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{label: string, text: string, image_url: ?string}>
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

            $merged[] = [
                'label' => (string) ($row['label'] ?? ''),
                'text' => (string) ($row['text'] ?? ''),
                'image_url' => $imageUrl,
            ];
        }

        return array_slice($merged, 0, 3);
    }
}
