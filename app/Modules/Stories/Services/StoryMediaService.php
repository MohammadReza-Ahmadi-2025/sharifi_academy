<?php

namespace App\Modules\Stories\Services;

use App\Modules\Files\Models\File;
use App\Modules\Stories\Models\Story;
use App\Support\Storage\OptimizedImageStore;
use Illuminate\Http\UploadedFile;

class StoryMediaService
{
    public function __construct(
        private readonly OptimizedImageStore $images,
    ) {}

    public function attachUpload(Story $story, UploadedFile $upload, string $type = 'story'): File
    {
        $stored = $this->images->store($upload, 'stories/'.$story->id);

        $file = $story->files()->create([
            'name' => $stored['name'],
            'path' => $stored['path'],
            'type' => $type,
            'extension' => $stored['extension'],
            'size' => $stored['size'],
            'status' => 1,
            'sort' => 1,
        ]);

        $story->update(['file_id' => $file->id]);

        return $file;
    }

    public function attachThumbnail(Story $story, UploadedFile $upload): File
    {
        $stored = $this->images->store($upload, 'stories/'.$story->id.'/thumbnails');

        $file = $story->files()->create([
            'name' => $stored['name'],
            'path' => $stored['path'],
            'type' => 'story_thumbnail',
            'extension' => $stored['extension'],
            'size' => $stored['size'],
            'status' => 1,
            'sort' => 0,
        ]);

        $story->update(['thumbnail_file_id' => $file->id]);

        return $file;
    }
}
