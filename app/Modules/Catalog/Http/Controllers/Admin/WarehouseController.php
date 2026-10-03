<?php

namespace App\Modules\Catalog\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Http\Requests\Admin\StoreWarehouseRequest;
use App\Modules\Catalog\Http\Requests\Admin\UpdateWarehouseRequest;
use App\Modules\Catalog\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('catalog-warehouses-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = Warehouse::query()->orderByDesc('is_default')->orderBy('name');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $warehouses = $query->paginate($perPage)->withQueryString()
            ->through(fn (Warehouse $warehouse) => [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'code' => $warehouse->code,
                'is_default' => (bool) $warehouse->is_default,
                'is_active' => (bool) $warehouse->is_active,
            ]);

        $actor = $request->user();

        return Inertia::render('Admin/Catalog/Warehouses/Index', [
            'warehouses' => $warehouses,
            'filters' => ['search' => $search, 'per_page' => $perPage],
            'abilities' => [
                'create' => $actor?->can('catalog-warehouses-create') ?? false,
                'update' => $actor?->can('catalog-warehouses-update') ?? false,
                'delete' => $actor?->can('catalog-warehouses-delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('catalog-warehouses-create'), 403);

        return Inertia::render('Admin/Catalog/Warehouses/Create');
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        Warehouse::query()->create([
            'name' => $request->validated('name'),
            'code' => $request->validated('code'),
            'address' => $request->validated('address'),
            'phone' => $request->validated('phone'),
            'is_default' => $request->boolean('is_default'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.catalog.warehouses.index')->with('success', 'انبار ایجاد شد.');
    }

    public function edit(Request $request, Warehouse $warehouse): Response
    {
        abort_unless($request->user()?->can('catalog-warehouses-update'), 403);

        return Inertia::render('Admin/Catalog/Warehouses/Edit', [
            'warehouse' => [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'code' => $warehouse->code,
                'address' => $warehouse->address ?? '',
                'phone' => $warehouse->phone ?? '',
                'is_default' => (bool) $warehouse->is_default,
                'is_active' => (bool) $warehouse->is_active,
            ],
        ]);
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update([
            'name' => $request->validated('name'),
            'code' => $request->validated('code'),
            'address' => $request->validated('address'),
            'phone' => $request->validated('phone'),
            'is_default' => $request->boolean('is_default'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.catalog.warehouses.edit', $warehouse)->with('success', 'انبار به‌روزرسانی شد.');
    }

    public function destroy(Request $request, Warehouse $warehouse): RedirectResponse
    {
        abort_unless($request->user()?->can('catalog-warehouses-delete'), 403);

        if ($warehouse->inventories()->exists()) {
            return back()->withErrors(['warehouse' => 'این انبار موجودی دارد و قابل حذف نیست.']);
        }

        if ($warehouse->is_default) {
            return back()->withErrors(['warehouse' => 'انبار پیش‌فرض را نمی‌توان حذف کرد.']);
        }

        $warehouse->delete();

        return redirect()->route('admin.catalog.warehouses.index')->with('success', 'انبار حذف شد.');
    }
}
