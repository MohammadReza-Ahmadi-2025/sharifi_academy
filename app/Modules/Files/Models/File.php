<?php

namespace App\Modules\Files\Models;

use App\Support\Storage\PublicStorageUrl;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name',
    'path',
    'type',
    'extension',
    'size',
    'alt',
    'title',
    'description',
    'status',
    'sort',
])]
class File extends Model
{
    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'status' => 'integer',
            'sort' => 'integer',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    public function publicUrl(string $variant = 'original'): string
    {
        $cacheKey = $this->id.':'.$variant;

        static $cache = [];

        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $path = $this->path;

        if (blank($path)) {
            return $cache[$cacheKey] = '';
        }

        if (Storage::disk('public')->exists($path)) {
            return $cache[$cacheKey] = PublicStorageUrl::forPath($path);
        }

        return $cache[$cacheKey] = '';
    }
}
