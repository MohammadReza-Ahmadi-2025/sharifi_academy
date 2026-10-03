<?php

namespace App\Modules\Cart;

use App\Modules\Cart\Console\ClearExpiredCartItems;
use App\Modules\Cart\Http\Controllers\CartController;
use App\Modules\Cart\Listeners\MergeCartAfterLogin;
use App\Modules\Cart\Services\CartPricingService;
use App\Modules\Cart\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CartServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CartPricingService::class);
        $this->app->singleton(CartService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ClearExpiredCartItems::class,
            ]);
        }

        Event::listen(Login::class, MergeCartAfterLogin::class);

        Route::middleware('web')
            ->group(function (): void {
                Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
                Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
                Route::post('/cart/{id}/increase', [CartController::class, 'increase'])->name('cart.increase');
                Route::post('/cart/{id}/decrease', [CartController::class, 'decrease'])->name('cart.decrease');
                Route::delete('/cart/{id}/remove', [CartController::class, 'remove'])->name('cart.remove');
            });
    }
}
