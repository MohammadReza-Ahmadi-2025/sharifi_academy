<?php

namespace App\Modules\Profile\Models;

use App\Models\Concerns\HasMediaFiles;
use App\Models\User;
use App\Modules\Profile\Support\ResumeAttachmentMedia;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;

#[Fillable([
    'user_id',
    'type',
    'title',
    'organization',
    'start_date',
    'end_date',
    'is_current',
    'link',
    'description',
    'display_order',
])]
class Resume extends Model implements HasMedia
{
    use HasMediaFiles;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(ResumeAttachmentMedia::COLLECTION);
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
