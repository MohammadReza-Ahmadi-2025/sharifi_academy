<?php

namespace App\Modules\Profile\Support;

use App\Modules\Profile\Models\Resume;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class ResumeAttachmentMedia
{
    public const COLLECTION = 'attachments';

    public const MAX_FILES = 5;

    public const MAX_KILOBYTES = 10240;

    /**
     * @return array<string, mixed>
     */
    public static function validationRules(): array
    {
        return [
            'attachments' => ['nullable', 'array', 'max:'.self::MAX_FILES],
            'attachments.*' => [
                'file',
                'mimes:pdf,jpeg,jpg,png,webp',
                'max:'.self::MAX_KILOBYTES,
            ],
            'remove_attachment_ids' => ['nullable', 'array'],
            'remove_attachment_ids.*' => ['integer'],
        ];
    }

    public static function syncFromRequest(Resume $resume, Request $request): void
    {
        $removeIds = $request->input('remove_attachment_ids', []);
        if (is_array($removeIds)) {
            foreach ($removeIds as $mediaId) {
                $media = $resume->getMedia(self::COLLECTION)->firstWhere('id', (int) $mediaId);
                $media?->delete();
            }
        }

        if (! $request->hasFile('attachments')) {
            return;
        }

        $files = $request->file('attachments');
        if (! is_array($files)) {
            $files = [$files];
        }

        $existingCount = $resume->getMedia(self::COLLECTION)->count();
        $slots = max(0, self::MAX_FILES - $existingCount);

        foreach (array_slice($files, 0, $slots) as $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $resume->addMedia($file)->toMediaCollection(self::COLLECTION);
            }
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function payloads(Resume $resume): array
    {
        return $resume->getMedia(self::COLLECTION)
            ->map(fn (Media $media) => self::mediaToArray($media))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function mediaToArray(Media $media): array
    {
        return [
            'id' => $media->id,
            'url' => $media->getUrl(),
            'name' => $media->file_name,
            'size' => $media->size,
            'mime_type' => $media->mime_type,
        ];
    }
}
