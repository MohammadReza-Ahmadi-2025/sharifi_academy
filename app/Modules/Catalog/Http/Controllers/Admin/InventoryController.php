<?php

namespace App\Modules\Catalog\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Http\Requests\Admin\AdjustInventoryRequest;
use App\Modules\Catalog\Models\Inventory;
use App\Modules\Catalog\Models\Warehouse;
use App\Modules\Catalog\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('catalog-inventory-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $warehouseId = $request->query('warehouse_id');
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = Inventory::query()
            ->with(['warehouse', 'variant.product'])
            ->orderByDesc('updated_at');

        if (is_numeric($warehouseId)) {
            $query->where('warehouse_id', (int) $warehouseId);
        }

        if ($search !== '') {
            $query->whereHas('variant', function ($variantQuery) use ($search) {
                $variantQuery->where('sku', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($productQuery) => $productQuery->where('title', 'like', "%{$search}%"));
            });
        }

        $items = $query->paginate($perPage)->withQueryString()
            ->through(function (Inventory $inventory) {
                $variant = $inventory->variant;
                $product = $variant?->product;

                return [
                    'id' => $inventory->id,
                    'warehouse' => $inventory->warehouse?->name,
                    'warehouse_id' => $inventory->warehouse_id,
                    'variant_id' => $inventory->variant_id,
                    'sku' => $variant?->sku,
                    'product_title' => $product?->title,
                    'stock' => (int) $inventory->stock,
                    'reserved' => (int) $inventory->reserved,
                    'available' => $inventory->available,
                ];
            });

        return Inertia::render('Admin/Catalog/Inventory/Index', [
            'items' => $items,
            'warehouses' => Warehouse::query()->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'search' => $search,
                'warehouse_id' => is_numeric($warehouseId) ? (string) $warehouseId : '',
                'per_page' => $perPage,
            ],
            'abilities' => [
                'adjust' => $request->user()?->can('catalog-inventory-adjust') ?? false,
            ],
        ]);
    }

    public function adjust(AdjustInventoryRequest $request, InventoryService $inventoryService): RedirectResponse
    {
        $data = $request->validated();

        $inventoryService->setStock(
            (int) $data['variant_id'],
            (int) $data['stock'],
            isset($data['warehouse_id']) ? (int) $data['warehouse_id'] : null,
            $request->user()?->id,
        );

        return back()->with('success', 'موجودی به‌روزرسانی شد.');
    }
}
