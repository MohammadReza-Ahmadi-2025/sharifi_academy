<?php

namespace App\Modules\Cart\Console;

use App\Modules\Cart\Models\Cart;
use Illuminate\Console\Command;

class ClearExpiredCartItems extends Command
{
    protected $signature = 'cart:clear-expired-items';

    protected $description = 'Clear items of carts inactive for more than the configured TTL';

    public function handle(): int
    {
        $expiredAt = now()->subMinutes((int) config('cart.expired_after_minutes', 30));

        $clearedCartsCount = 0;
        $clearedItemsCount = 0;

        Cart::query()
            ->where('updated_at', '<=', $expiredAt)
            ->whereHas('items')
            ->chunkById(100, function ($carts) use (&$clearedCartsCount, &$clearedItemsCount): void {
                foreach ($carts as $cart) {
                    $itemsCount = $cart->items()->count();

                    if ($itemsCount > 0) {
                        $cart->items()->delete();
                        $clearedCartsCount++;
                        $clearedItemsCount += $itemsCount;
                    }
                }
            });

        $this->info("Cleared carts: {$clearedCartsCount}");
        $this->info("Deleted cart items: {$clearedItemsCount}");

        return self::SUCCESS;
    }
}
