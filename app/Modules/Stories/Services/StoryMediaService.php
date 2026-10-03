<?php

namespace App\Modules\Stories\Services;

use App\Modules\Files\Models\File;
use App\Modules\Stories\Models\Story;
use Illuminate\Http\UploadedFile;

class StoryMediaService
{
    public function attachUpload(Story $story, UploadedFile $upload, string $type = 'story'): File
    {
        $path = $upload->store('stories/'.$story->id, 'public');

        $file = $story->files()->create([
            'name' => $upload->getClientOriginalName(),
            'path' => $path,
            'type' => $type,
            'extension' => $upload->getClientOriginalExtension(),
            'size' => $upload->getSize(),
            'status' => 1,
            'sort' => 1,
        ]);

        $story->update(['file_id' => $file->id]);

        return $file;
    }
}
