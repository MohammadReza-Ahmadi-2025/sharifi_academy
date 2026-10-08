<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Inventory;
use App\Modules\Catalog\Models\InventoryLog;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Catalog\Models\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function defaultWarehouseId(): int
    {
        return Warehouse::defaultId();
    }

    public function availableQuantity(int $variantId): int
    {
        $totals = Inventory::query()
            ->where('variant_id', $variantId)
            ->selectRaw('COALESCE(SUM(stock), 0) as total_stock, COALESCE(SUM(reserved), 0) as total_reserved')
            ->first();

        return max(0, (int) ($totals->total_stock ?? 0) - (int) ($totals->total_reserved ?? 0));
    }

    public function availableQuantities(array $variantIds): Collection
    {
        $variantIds = array_values(array_unique(array_map('intval', $variantIds)));

        if ($variantIds === []) {
            return collect();
        }

        $totals = Inventory::query()
            ->whereIn('variant_id', $variantIds)
            ->groupBy('variant_id')
            ->selectRaw('variant_id, COALESCE(SUM(stock), 0) - COALESCE(SUM(reserved), 0) as available')
            ->pluck('available', 'variant_id');

        return collect($variantIds)->mapWithKeys(function (int $variantId) use ($totals) {
            $available = $totals->get($variantId) ?? $totals->get((string) $variantId) ?? 0;

            return [$variantId => max(0, (int) $available)];
        });
    }

    public function getOrCreate(int $variantId, ?int $warehouseId = null): Inventory
    {
        $warehouseId = $warehouseId ?: $this->defaultWarehouseId();

        $inventory = Inventory::query()
            ->where('variant_id', $variantId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($inventory) {
            return $inventory;
        }

        ProductVariant::query()->findOrFail($variantId);

        return Inventory::create([
            'variant_id' => $variantId,
            'warehouse_id' => $warehouseId,
            'stock' => 0,
            'reserved' => 0,
            'min_stock' => 0,
        ]);
    }

    public function increaseStock(
        int $variantId,
        int $quantity,
        ?int $warehouseId = null,
        string $logType = 'purchase',
        ?string $description = null,
        ?object $reference = null,
        ?int $userId = null,
    ): Inventory {
        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'تعداد نامعتبر است.']);
        }

        return DB::transaction(function () use ($variantId, $quantity, $warehouseId, $logType, $description, $reference, $userId) {
            $inventory = $this->getOrCreate($variantId, $warehouseId);
            $inventory = Inventory::query()->whereKey($inventory->id)->lockForUpdate()->firstOrFail();

            $inventory->increment('stock', $quantity);
            $inventory->refresh();

            $this->logChange($inventory, $quantity, $logType, $description, $reference, $userId);

            return $inventory;
        });
    }

    public function reserve(int $variantId, int $quantity): void
    {
        if ($quantity < 1) {
            return;
        }

        DB::transaction(function () use ($variantId, $quantity) {
            if ($this->availableQuantity($variantId) < $quantity) {
                throw ValidationException::withMessages([
                    'items' => 'موجودی کافی نیست.',
                ]);
            }

            $remaining = $quantity;

            $inventories = Inventory::query()
                ->where('variant_id', $variantId)
                ->orderByRaw('warehouse_id = ? DESC', [$this->defaultWarehouseId()])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($inventories as $inventory) {
                if ($remaining <= 0) {
                    break;
                }

                $free = max(0, $inventory->stock - $inventory->reserved);

                if ($free <= 0) {
                    continue;
                }

                $take = min($free, $remaining);
                $inventory->increment('reserved', $take);
                $remaining -= $take;
            }

            if ($remaining > 0) {
                throw ValidationException::withMessages([
                    'items' => 'موجودی کافی نیست.',
                ]);
            }
        });
    }

    public function releaseReserved(int $variantId, int $quantity): void
    {
        if ($quantity < 1) {
            return;
        }

        DB::transaction(function () use ($variantId, $quantity) {
            $remaining = $quantity;

            $inventories = Inventory::query()
                ->where('variant_id', $variantId)
                ->where('reserved', '>', 0)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($inventories as $inventory) {
                if ($remaining <= 0) {
                    break;
                }

                $release = min((int) $inventory->reserved, $remaining);
                $inventory->decrement('reserved', $release);
                $remaining -= $release;
            }
        });
    }

    public function consumeStock(
        int $variantId,
        int $quantity,
        string $logType = 'sale',
        ?string $description = null,
        ?object $reference = null,
        ?int $userId = null,
    ): void {
        if ($quantity < 1) {
            return;
        }

        DB::transaction(function () use ($variantId, $quantity, $logType, $description, $reference, $userId) {
            $remaining = $quantity;

            $inventories = Inventory::query()
                ->where('variant_id', $variantId)
                ->orderByRaw('warehouse_id = ? DESC', [$this->defaultWarehouseId()])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($inventories as $inventory) {
                if ($remaining <= 0) {
                    break;
                }

                $fromReserved = min((int) $inventory->reserved, $remaining);

                if ($fromReserved > 0) {
                    $inventory->decrement('reserved', $fromReserved);
                    $inventory->decrement('stock', $fromReserved);
                    $remaining -= $fromReserved;
                    $inventory->refresh();
                    $this->logChange($inventory, -$fromReserved, $logType, $description, $reference, $userId);
                }

                if ($remaining <= 0) {
                    break;
                }

                $freeStock = max(0, (int) $inventory->stock);

                if ($freeStock > 0) {
                    $fromStock = min($freeStock, $remaining);
                    $inventory->decrement('stock', $fromStock);
                    $remaining -= $fromStock;
                    $inventory->refresh();
                    $this->logChange($inventory, -$fromStock, $logType, $description, $reference, $userId);
                }
            }

            if ($remaining > 0) {
                throw ValidationException::withMessages([
                    'items' => 'موجودی کافی نیست.',
                ]);
            }
        });
    }

    public function setStock(int $variantId, int $stock, ?int $warehouseId = null, ?int $userId = null): Inventory
    {
        return $this->setStockAndMin($variantId, $stock, null, $warehouseId, $userId);
    }

    public function setStockAndMin(
        int $variantId,
        int $stock,
        ?int $minStock = null,
        ?int $warehouseId = null,
        ?int $userId = null,
    ): Inventory {
        $warehouseId = $warehouseId ?: $this->defaultWarehouseId();
        $stock = max(0, $stock);
        $minStock = $minStock === null ? null : max(0, $minStock);

        return DB::transaction(function () use ($variantId, $stock, $minStock, $warehouseId, $userId) {
            $inventory = $this->getOrCreate($variantId, $warehouseId);
            $inventory = Inventory::query()->whereKey($inventory->id)->lockForUpdate()->firstOrFail();

            $hadLogs = InventoryLog::query()
                ->where('variant_id', $variantId)
                ->exists();

            $change = $stock - (int) $inventory->stock;
            $updates = ['stock' => $stock];

            if ($minStock !== null) {
                $updates['min_stock'] = $minStock;
            }

            $inventory->update($updates);

            if ($change !== 0) {
                $this->logChange($inventory, $change, 'adjustment', 'تنظیم موجودی از پنل ادمین', null, $userId);
            } elseif (! $hadLogs) {
                $this->logChange($inventory, 0, 'initial', 'ثبت موجودی اولیه از پنل ادمین', null, $userId);
            }

            return $inventory->refresh();
        });
    }

    private function logChange(
        Inventory $inventory,
        int $change,
        string $type,
        ?string $description,
        ?object $reference,
        ?int $userId,
    ): void {
        InventoryLog::create([
            'variant_id' => $inventory->variant_id,
            'user_id' => $userId,
            'change' => $change,
            'balance' => $inventory->stock,
            'type' => $type,
            'reference_id' => $reference?->id,
            'reference_type' => $reference ? $reference::class : null,
            'description' => $description,
        ]);
    }
}
