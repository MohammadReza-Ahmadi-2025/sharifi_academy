<?php

namespace Tests\Feature\Cart;

use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Cart\Services\CartService;
use App\Modules\Catalog\Models\Inventory;
use App\Modules\Catalog\Models\Price;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Catalog\Models\Warehouse;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private function createSellableVariant(int $stock = 5, int $finalPrice = 1_200_000): ProductVariant
    {
        $product = Product::query()->create([
            'title' => 'کتاب لاراول',
            'slug' => 'laravel-book-'.uniqid(),
            'status' => true,
            'is_visible' => true,
        ]);

        $variant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'sku' => 'SKU-'.uniqid(),
            'status' => true,
        ]);

        Price::query()->create([
            'variant_id' => $variant->id,
            'price' => 1_500_000,
            'final_price' => $finalPrice,
        ]);

        Inventory::query()->create([
            'variant_id' => $variant->id,
            'warehouse_id' => Warehouse::defaultId(),
            'stock' => $stock,
            'reserved' => 0,
        ]);

        return $variant->fresh(['product', 'currentPrice', 'inventory']);
    }

    public function test_guest_can_add_items_to_session_cart(): void
    {
        $variant = $this->createSellableVariant();

        $response = $this->postJson(route('cart.add'), [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('totalQty', 2);

        $this->assertSame(2, Cart::currentQty());
        $this->assertCount(1, app(CartService::class)->lines(null));
    }

    public function test_authenticated_user_stores_cart_in_database(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('mobile', '09120000003')->firstOrFail();
        $variant = $this->createSellableVariant();

        $response = $this->actingAs($user)->postJson(route('cart.add'), [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response->assertOk()->assertJsonPath('totalQty', 1);

        $this->assertDatabaseHas('cart_items', [
            'variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 1_200_000,
        ]);
    }

    public function test_merge_cart_after_login_listener_combines_session_and_database(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('mobile', '09120000003')->firstOrFail();
        $variantA = $this->createSellableVariant(stock: 10);
        $variantB = $this->createSellableVariant(stock: 10);

        $cart = Cart::forUser($user->id);
        $cart->items()->create([
            'variant_id' => $variantA->id,
            'quantity' => 1,
            'price' => 1_200_000,
        ]);

        session()->put(Cart::sessionKey(), [
            (string) $variantA->id => [
                'variant_id' => $variantA->id,
                'quantity' => 2,
                'price' => 1_200_000,
            ],
            (string) $variantB->id => [
                'variant_id' => $variantB->id,
                'quantity' => 1,
                'price' => 1_200_000,
            ],
        ]);

        app(CartService::class)->mergeSessionIntoUserCart($user);

        $this->assertSame([], session(Cart::sessionKey(), []));
        $this->assertSame(4, CartItem::query()->where('cart_id', $cart->id)->sum('quantity'));
    }

    public function test_cart_clear_expired_items_command_removes_stale_items(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('mobile', '09120000001')->firstOrFail();
        $variant = $this->createSellableVariant();

        $cart = Cart::forUser($user->id);
        $cart->items()->create([
            'variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 1_200_000,
        ]);

        $cart->forceFill(['updated_at' => now()->subHour()])->save();

        $this->artisan('cart:clear-expired-items')->assertSuccessful();

        $this->assertDatabaseMissing('cart_items', [
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
        ]);
    }

    public function test_login_event_triggers_cart_merge_listener(): void
    {
        $this->seed(DatabaseSeeder::class);
        $user = User::where('mobile', '09120000003')->firstOrFail();
        $variant = $this->createSellableVariant();

        session()->put(Cart::sessionKey(), [
            (string) $variant->id => [
                'variant_id' => $variant->id,
                'quantity' => 1,
                'price' => 1_200_000,
            ],
        ]);

        event(new Login('web', $user, false));

        $this->assertSame([], session(Cart::sessionKey(), []));
        $this->assertDatabaseHas('cart_items', [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);
    }
}
