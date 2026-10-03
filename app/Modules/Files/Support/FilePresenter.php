<?php

namespace App\Modules\Files\Support;

use App\Modules\Files\Models\File;

class FilePresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forUploader(File $file): array
    {
        return [
            'id' => $file->id,
            'url' => $file->publicUrl(),
            'name' => $file->name,
            'size' => $file->size,
            'alt' => $file->alt,
            'title' => $file->title,
            'sort' => $file->sort,
        ];
    }
}
