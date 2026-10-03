<?php

namespace App\Modules\Cart\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
    ];

    public static function sessionKey(): string
    {
        return (string) config('cart.session_key', 'cart');
    }

    public static function forUser(int $userId): self
    {
        return static::query()->firstOrCreate(['user_id' => $userId]);
    }

    public static function totalQtyForUser(int $userId): int
    {
        return (int) CartItem::query()
            ->whereHas('cart', fn ($query) => $query->where('user_id', $userId))
            ->sum('quantity');
    }

    public static function currentQty(): int
    {
        if (! auth()->check()) {
            return (int) collect(session(static::sessionKey(), []))->sum('quantity');
        }

        return static::totalQtyForUser((int) auth()->id());
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
