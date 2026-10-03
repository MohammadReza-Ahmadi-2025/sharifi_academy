<?php

namespace App\Modules\Cart\Listeners;

use App\Modules\Cart\Services\CartService;
use Illuminate\Auth\Events\Login;

class MergeCartAfterLogin
{
    public function __construct(
        private readonly CartService $cartService,
    ) {}

    public function handle(Login $event): void
    {
        $this->cartService->mergeSessionIntoUserCart($event->user);
    }
}
