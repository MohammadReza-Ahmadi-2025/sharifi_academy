<?php

namespace App\Modules\Files\Services;

use App\Modules\Files\Models\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MorphFileSyncService
{
    public const TYPE_GALLERY = 'gallery';

    public const TYPE_IMAGE = 'image';

    public const TYPE_FEATURED = 'featured';

    /**
     * @param  list<array{id?: int|null, sort?: int|null, alt?: string|null, title?: string|null}>  $manifest
     * @param  array<int, UploadedFile|null>  $uploadsByIndex
     */
    public function syncGallery(Model $owner, array $manifest, array $uploadsByIndex, string $type = self::TYPE_GALLERY): void
    {
        $keptIds = [];

        foreach (array_values($manifest) as $index => $row) {
            $sort = (int) ($row['sort'] ?? $index + 1);
            $upload = $uploadsByIndex[$index] ?? null;

            if ($upload instanceof UploadedFile) {
                $keptIds[] = $this->storeUpload($owner, $upload, $type, $sort, $row)->id;

                continue;
            }

            $existingId = isset($row['id']) ? (int) $row['id'] : 0;

            if ($existingId <= 0) {
                continue;
            }

            $existing = $this->findOwnedFile($owner, $existingId, $type);

            if ($existing === null) {
                continue;
            }

            $existing->update([
                'sort' => $sort,
                'alt' => $row['alt'] ?? null,
                'title' => $row['title'] ?? null,
            ]);

            $keptIds[] = $existing->id;
        }

        $this->deleteRemoved($owner, $type, $keptIds);
    }

    /**
     * @param  array{id?: int|null, sort?: int|null, alt?: string|null, title?: string|null}  $row
     */
    public function syncSingleImage(Model $owner, ?UploadedFile $upload, array $row, bool $remove, string $type = self::TYPE_IMAGE): void
    {
        if ($remove) {
            $this->deleteRemoved($owner, $type, []);

            return;
        }

        if ($upload instanceof UploadedFile) {
            $this->deleteRemoved($owner, $type, []);
            $this->storeUpload($owner, $upload, $type, 1, $row);

            return;
        }

        $existingId = isset($row['id']) ? (int) $row['id'] : 0;

        if ($existingId <= 0) {
            $this->deleteRemoved($owner, $type, []);

            return;
        }

        $existing = $this->findOwnedFile($owner, $existingId, $type);

        if ($existing === null) {
            return;
        }

        $existing->update([
            'alt' => $row['alt'] ?? null,
            'title' => $row['title'] ?? null,
        ]);

        $this->deleteRemoved($owner, $type, [$existing->id]);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function storeUpload(Model $owner, UploadedFile $upload, string $type, int $sort, array $meta): File
    {
        $path = $upload->store($owner->getTable().'/'.$owner->getKey(), 'public');

        return $owner->files()->create([
            'name' => $upload->getClientOriginalName(),
            'path' => $path,
            'type' => $type,
            'extension' => $upload->getClientOriginalExtension(),
            'size' => $upload->getSize(),
            'alt' => $meta['alt'] ?? null,
            'title' => $meta['title'] ?? null,
            'sort' => $sort,
            'status' => 1,
        ]);
    }

    private function findOwnedFile(Model $owner, int $id, string $type): ?File
    {
        return File::query()
            ->whereKey($id)
            ->where('fileable_type', $owner->getMorphClass())
            ->where('fileable_id', $owner->getKey())
            ->where('type', $type)
            ->first();
    }

    /**
     * @param  list<int>  $keptIds
     */
    private function deleteRemoved(Model $owner, string $type, array $keptIds): void
    {
        $query = File::query()
            ->where('fileable_type', $owner->getMorphClass())
            ->where('fileable_id', $owner->getKey())
            ->where('type', $type);

        if ($keptIds !== []) {
            $query->whereNotIn('id', $keptIds);
        }

        $query->get()->each(fn (File $file) => $this->deleteFile($file));
    }

    public function deleteFile(File $file): void
    {
        $path = $file->path;

        if (filled($path)) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $file->delete();
    }
}
