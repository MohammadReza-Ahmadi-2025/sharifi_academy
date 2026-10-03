<?php

namespace App\Modules\Cart\Services;

use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Catalog\Enums\VariantDisplayMode;
use App\Modules\Catalog\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function __construct(
        private readonly CartPricingService $pricing,
    ) {}

    /**
     * @return array{success: true, message: string, totalQty: int}
     */
    public function add(int $variantId, int $quantity, ?User $user): array
    {
        $quantity = max(1, $quantity);

        $variant = ProductVariant::query()
            ->with($this->variantPricingRelations())
            ->findOrFail($variantId);

        $this->assertSellable($variant, $quantity);

        if ($user === null) {
            return $this->addToSessionCart($variant, $quantity);
        }

        return $this->addToDatabaseCart($variant, $quantity, $user);
    }

    /**
     * @return Collection<int, object>
     */
    public function lines(?User $user): Collection
    {
        if ($user === null) {
            return $this->sessionLines();
        }

        $cart = Cart::forUser($user->id);
        $this->syncAuthenticatedCartPrices($cart, $user->id);

        return $this->databaseLines($cart, $user->id);
    }

    /**
     * @return array{qty: int, price: int}
     */
    public function increase(int|string $id, ?User $user): array
    {
        if ($user === null) {
            return $this->increaseSessionItem((string) $id);
        }

        return $this->increaseDatabaseItem((int) $id, $user->id);
    }

    /**
     * @return array{qty: int, price: int}
     */
    public function decrease(int|string $id, ?User $user): array
    {
        if ($user === null) {
            return $this->decreaseSessionItem((string) $id);
        }

        return $this->decreaseDatabaseItem((int) $id, $user->id);
    }

    public function remove(int|string $id, ?User $user): void
    {
        if ($user === null) {
            $cart = session()->get(Cart::sessionKey(), []);
            unset($cart[(string) $id]);
            session()->put(Cart::sessionKey(), $cart);

            return;
        }

        CartItem::query()->whereKey((int) $id)->delete();
    }

    public function mergeSessionIntoUserCart(User $user): void
    {
        $sessionCart = session()->get(Cart::sessionKey(), []);

        if ($sessionCart === []) {
            return;
        }

        $cart = Cart::forUser($user->id);

        $variantIds = collect($sessionCart)->pluck('variant_id')->map(fn ($id) => (int) $id)->all();

        $variants = ProductVariant::query()
            ->with(['inventories', 'currentPrice', 'product'])
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        foreach ($sessionCart as $item) {
            $variant = $variants->get((int) $item['variant_id']);

            if (! $variant || ! $this->isVariantListed($variant)) {
                continue;
            }

            $available = $variant->total_available;

            if ($available < 1) {
                continue;
            }

            $quantity = min((int) $item['quantity'], $available);
            $unitPrice = $this->pricing->resolveSellPrice($variant, $quantity, $user->id)['unit'];

            $cartItem = $cart->items()->where('variant_id', $variant->id)->first();

            if ($cartItem) {
                $nextQty = min($cartItem->quantity + $quantity, $available);
                $unitPrice = $this->pricing->resolveSellPrice($variant, $nextQty, $user->id)['unit'];
                $cartItem->update([
                    'quantity' => $nextQty,
                    'price' => $unitPrice,
                ]);
            } else {
                $cart->items()->create([
                    'variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                ]);
            }
        }

        session()->forget(Cart::sessionKey());
    }

    public function syncAuthenticatedCartPrices(Cart $cart, ?int $userId): void
    {
        $cart->items()
            ->with(['variant.currentPrice'])
            ->get()
            ->each(function (CartItem $item) use ($userId): void {
                $variant = $item->variant;

                if (! $variant) {
                    return;
                }

                $unitPrice = $this->pricing->resolveSellPrice($variant, $item->quantity, $userId)['unit'];

                if ($item->price !== $unitPrice) {
                    $item->update(['price' => $unitPrice]);
                }
            });
    }

    /**
     * @return list<string>
     */
    public function variantPricingRelations(): array
    {
        return [
            'product',
            'inventory',
            'currentPrice',
        ];
    }

    /**
     * @return array{success: true, message: string, totalQty: int}
     */
    private function addToSessionCart(ProductVariant $variant, int $quantity): array
    {
        $cart = session()->get(Cart::sessionKey(), []);
        $variantKey = (string) $variant->id;

        $currentQty = (int) ($cart[$variantKey]['quantity'] ?? 0);
        $available = $variant->total_available;

        if (($currentQty + $quantity) > $available) {
            throw ValidationException::withMessages([
                'quantity' => 'موجودی این کالا کافی نیست',
            ]);
        }

        $nextQty = $currentQty + $quantity;
        $unitPrice = $this->pricing->resolveSellPrice($variant, $nextQty, null)['unit'];

        $cart[$variantKey] = [
            'variant_id' => $variant->id,
            'product_id' => $variant->product_id,
            'product_title' => $variant->product?->title,
            'quantity' => $nextQty,
            'price' => $unitPrice,
        ];

        session()->put(Cart::sessionKey(), $cart);

        return [
            'success' => true,
            'message' => 'به سبد خرید اضافه شد',
            'totalQty' => (int) collect($cart)->sum('quantity'),
        ];
    }

    /**
     * @return array{success: true, message: string, totalQty: int}
     */
    private function addToDatabaseCart(ProductVariant $variant, int $quantity, User $user): array
    {
        $cart = Cart::forUser($user->id);

        $item = $cart->items()->where('variant_id', $variant->id)->first();
        $currentQty = $item?->quantity ?? 0;
        $available = $variant->total_available;

        if (($currentQty + $quantity) > $available) {
            throw ValidationException::withMessages([
                'quantity' => 'موجودی این کالا کافی نیست',
            ]);
        }

        $nextQty = $currentQty + $quantity;
        $unitPrice = $this->pricing->resolveSellPrice($variant, $nextQty, $user->id)['unit'];

        if ($item) {
            $item->update([
                'quantity' => $nextQty,
                'price' => $unitPrice,
            ]);
        } else {
            $cart->items()->create([
                'variant_id' => $variant->id,
                'quantity' => $nextQty,
                'price' => $unitPrice,
            ]);
        }

        return [
            'success' => true,
            'message' => 'به سبد خرید اضافه شد',
            'totalQty' => (int) $cart->items()->sum('quantity'),
        ];
    }

    /**
     * @return Collection<int, object>
     */
    private function sessionLines(): Collection
    {
        $sessionCart = session()->get(Cart::sessionKey(), []);
        $variantIds = array_keys($sessionCart);

        $variants = ProductVariant::query()
            ->with($this->variantPricingRelations())
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        $items = collect();

        foreach ($sessionCart as $key => $item) {
            $variant = $variants->get((int) $item['variant_id']);

            if (! $variant || ! $this->isVariantListed($variant)) {
                unset($sessionCart[$key]);

                continue;
            }

            $available = $variant->total_available;

            if ($available <= 0) {
                unset($sessionCart[$key]);

                continue;
            }

            if ($item['quantity'] > $available) {
                $item['quantity'] = $available;
            }

            $item['price'] = $this->pricing->resolveSellPrice($variant, (int) $item['quantity'], null)['unit'];
            $sessionCart[$key] = $item;

            $items->push($this->formatCartLine(
                $item['variant_id'],
                $variant,
                (int) $item['quantity'],
                (int) $item['price'],
                null,
            ));
        }

        session()->put(Cart::sessionKey(), $sessionCart);

        return $items;
    }

    /**
     * @return Collection<int, object>
     */
    private function databaseLines(Cart $cart, int $userId): Collection
    {
        $items = $cart->items()
            ->with([
                'variant.product',
                'variant.currentPrice',
                'variant.inventory',
            ])
            ->get();

        $cleaned = collect();

        foreach ($items as $item) {
            $variant = $item->variant;

            if (! $variant || ! $variant->product) {
                $item->delete();

                continue;
            }

            if (! $this->isVariantListed($variant)) {
                $item->delete();

                continue;
            }

            $available = $variant->total_available;

            if ($available <= 0) {
                $item->delete();

                continue;
            }

            if ($item->quantity > $available) {
                $item->quantity = $available;
                $item->save();
            }

            $cleaned->push($this->formatCartLine(
                $item->id,
                $variant,
                $item->quantity,
                $item->price,
                $userId,
            ));
        }

        return $cleaned;
    }

    /**
     * @return array{qty: int, price: int}
     */
    private function increaseSessionItem(string $variantId): array
    {
        $cart = session()->get(Cart::sessionKey(), []);

        if (! isset($cart[$variantId])) {
            return ['qty' => 0, 'price' => 0];
        }

        $variant = ProductVariant::query()
            ->with($this->variantPricingRelations())
            ->find($variantId);

        if ($variant && $cart[$variantId]['quantity'] < $variant->total_available) {
            $cart[$variantId]['quantity']++;
            $cart[$variantId]['price'] = $this->pricing->resolveSellPrice(
                $variant,
                (int) $cart[$variantId]['quantity'],
                null,
            )['unit'];
            session()->put(Cart::sessionKey(), $cart);
        }

        return [
            'qty' => (int) ($cart[$variantId]['quantity'] ?? 0),
            'price' => (int) ($cart[$variantId]['price'] ?? 0),
        ];
    }

    /**
     * @return array{qty: int, price: int}
     */
    private function increaseDatabaseItem(int $cartItemId, int $userId): array
    {
        $item = CartItem::query()
            ->with(['variant' => fn ($query) => $query->with($this->variantPricingRelations())])
            ->findOrFail($cartItemId);

        $variant = $item->variant;

        if (! $variant) {
            $item->delete();

            return ['qty' => 0, 'price' => 0];
        }

        $available = $variant->total_available;

        if ($item->quantity < $available) {
            $item->increment('quantity');
            $item->refresh();

            $unitPrice = $this->pricing->resolveSellPrice($variant, $item->quantity, $userId)['unit'];
            $item->update(['price' => $unitPrice]);
        }

        return [
            'qty' => $item->quantity,
            'price' => (int) $item->price,
        ];
    }

    /**
     * @return array{qty: int, price: int}
     */
    private function decreaseSessionItem(string $variantId): array
    {
        $cart = session()->get(Cart::sessionKey(), []);

        if (isset($cart[$variantId]) && $cart[$variantId]['quantity'] > 1) {
            $cart[$variantId]['quantity']--;
            $variant = ProductVariant::query()
                ->with($this->variantPricingRelations())
                ->find($variantId);

            if ($variant) {
                $cart[$variantId]['price'] = $this->pricing->resolveSellPrice(
                    $variant,
                    (int) $cart[$variantId]['quantity'],
                    null,
                )['unit'];
            }

            session()->put(Cart::sessionKey(), $cart);
        }

        return [
            'qty' => (int) ($cart[$variantId]['quantity'] ?? 0),
            'price' => (int) ($cart[$variantId]['price'] ?? 0),
        ];
    }

    /**
     * @return array{qty: int, price: int}
     */
    private function decreaseDatabaseItem(int $cartItemId, int $userId): array
    {
        $item = CartItem::query()
            ->with(['variant.currentPrice'])
            ->findOrFail($cartItemId);

        if ($item->quantity > 1) {
            $item->decrement('quantity');
            $item->refresh();

            $unitPrice = $this->pricing->resolveSellPrice($item->variant, $item->quantity, $userId)['unit'];
            $item->update(['price' => $unitPrice]);
        }

        return [
            'qty' => $item->quantity,
            'price' => (int) $item->price,
        ];
    }

    private function formatCartLine(
        int|string $id,
        ProductVariant $variant,
        int $quantity,
        int $price,
        ?int $userId,
    ): object {
        $unitPrice = (int) $price;
        $discount = $this->pricing->cartLineDiscountMeta($variant, $quantity, $unitPrice, $userId);

        return (object) [
            'id' => $id,
            'variant_id' => $variant->id,
            'product_id' => $variant->product_id,
            'product_title' => $variant->product?->title,
            'quantity' => $quantity,
            'price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
            'compare_unit' => $discount['compare_unit'],
            'discount_percent' => $discount['discount_percent'],
            'discount_label' => $discount['discount_label'],
        ];
    }

    private function assertSellable(ProductVariant $variant, int $quantity): void
    {
        if (! $this->isVariantListed($variant)) {
            throw ValidationException::withMessages([
                'variant_id' => 'این کالا برای خرید در دسترس نیست',
            ]);
        }

        if ($variant->display_mode === VariantDisplayMode::CALL_FOR_PRICE) {
            throw ValidationException::withMessages([
                'variant_id' => 'برای این کالا باید تماس بگیرید',
            ]);
        }

        if ($variant->total_available < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'این کالا ناموجود است',
            ]);
        }
    }

    private function isVariantListed(ProductVariant $variant): bool
    {
        $product = $variant->product;

        if (! $product) {
            return false;
        }

        if (! $variant->status || ! $product->status || ! $product->is_visible) {
            return false;
        }

        if ($variant->display_mode === VariantDisplayMode::OUT_OF_STOCK) {
            return false;
        }

        return true;
    }
}
