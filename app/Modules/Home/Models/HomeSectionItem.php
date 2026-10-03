<?php

namespace App\Modules\Home\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class HomeSectionItem extends Model
{
    protected $fillable = [
        'tab_id',
        'item_type',
        'item_id',
        'sort_order',
    ];

    /**
     * @return BelongsTo<HomeSectionTab, $this>
     */
    public function tab(): BelongsTo
    {
        return $this->belongsTo(HomeSectionTab::class, 'tab_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function item(): MorphTo
    {
        return $this->morphTo();
    }
}
