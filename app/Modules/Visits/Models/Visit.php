<?php

namespace App\Modules\Visits\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Visit extends Model
{
    protected $fillable = [
        'ip_address',
        'user_agent',
        'visitor_id',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function visitable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function visitor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'visitor_id');
    }
}
