<?php

namespace App\Modules\Cart\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cart\Http\Requests\AddToCartRequest;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function index(Request $request, CartService $cartService): Response|JsonResponse
    {
        $items = $cartService->lines($request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'items' => $items->values(),
                'totalQty' => Cart::currentQty(),
            ]);
        }

        return Inertia::render('Cart/Index', [
            'items' => $items->values(),
            'totalQty' => Cart::currentQty(),
        ]);
    }

    public function add(AddToCartRequest $request, CartService $cartService): JsonResponse
    {
        $payload = $cartService->add(
            (int) $request->validated('variant_id'),
            (int) ($request->validated('quantity') ?? 1),
            $request->user(),
        );

        return response()->json($payload);
    }

    public function increase(Request $request, int|string $id, CartService $cartService): JsonResponse
    {
        return response()->json($cartService->increase($id, $request->user()));
    }

    public function decrease(Request $request, int|string $id, CartService $cartService): JsonResponse
    {
        return response()->json($cartService->decrease($id, $request->user()));
    }

    public function remove(Request $request, int|string $id, CartService $cartService): JsonResponse
    {
        $cartService->remove($id, $request->user());

        return response()->json([
            'success' => true,
            'totalQty' => Cart::currentQty(),
        ]);
    }
}
