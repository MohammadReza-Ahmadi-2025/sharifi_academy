<?php

namespace App\Modules\Files\Http\Concerns;

use App\Modules\Files\Services\MorphFileSyncService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

trait SyncsMorphFilesFromRequest
{
    protected function syncGalleryFromRequest(Model $owner, FormRequest $request, string $key = 'gallery'): void
    {
        $manifest = $request->input($key, []);

        if (! is_array($manifest)) {
            $manifest = [];
        }

        app(MorphFileSyncService::class)->syncGallery(
            $owner,
            $manifest,
            $request->galleryUploadsByIndex($key),
            MorphFileSyncService::TYPE_GALLERY,
        );
    }

    protected function syncSingleImageFromRequest(Model $owner, FormRequest $request, string $key = 'image'): void
    {
        $manifest = $request->input($key, []);
        $row = is_array($manifest) && isset($manifest[0]) && is_array($manifest[0]) ? $manifest[0] : [];

        app(MorphFileSyncService::class)->syncSingleImage(
            $owner,
            $request->file("{$key}.0.file"),
            $row,
            $request->boolean('remove_'.$key),
            MorphFileSyncService::TYPE_IMAGE,
        );
    }

    protected function syncFeaturedImageFromRequest(Model $owner, FormRequest $request, string $key = 'featured'): void
    {
        $manifest = $request->input($key, []);
        $row = is_array($manifest) && isset($manifest[0]) && is_array($manifest[0]) ? $manifest[0] : [];

        app(MorphFileSyncService::class)->syncSingleImage(
            $owner,
            $request->file("{$key}.0.file"),
            $row,
            $request->boolean('remove_'.$key),
            MorphFileSyncService::TYPE_FEATURED,
        );
    }
}
