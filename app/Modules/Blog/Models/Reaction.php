<?php

namespace App\Modules\Blog\Models;

use App\Models\User;
use App\Modules\Blog\Enums\ReactionType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'reactable_type',
    'reactable_id',
    'user_id',
    'type',
])]
class Reaction extends Model
{
    protected function casts(): array
    {
        return [
            'type' => ReactionType::class,
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function reactable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
