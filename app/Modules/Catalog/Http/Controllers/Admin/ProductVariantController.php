<?php

namespace App\Modules\Catalog\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Http\Requests\Admin\GenerateProductVariantsRequest;
use App\Modules\Catalog\Http\Requests\Admin\StoreProductVariantRequest;
use App\Modules\Catalog\Http\Requests\Admin\UpdateProductVariantRequest;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Models\ProductVariant;
use App\Modules\Catalog\Services\AdminProductVariantService;
use App\Modules\Catalog\Services\ProductVariantCombinationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function __construct(
        private readonly AdminProductVariantService $variantService,
        private readonly ProductVariantCombinationService $combinationService,
    ) {}

    public function store(StoreProductVariantRequest $request, Product $product): RedirectResponse
    {
        $this->variantService->create($product, $request->validated(), $request->user()?->id);

        return redirect()
            ->route('admin.catalog.products.edit', $product)
            ->with('success', 'تنوع محصول اضافه شد.');
    }

    public function update(UpdateProductVariantRequest $request, Product $product, ProductVariant $variant): RedirectResponse
    {
        $this->ensureVariantBelongsToProduct($product, $variant);

        $this->variantService->update($variant, $request->validated(), $request->user()?->id);

        return redirect()
            ->route('admin.catalog.products.edit', $product)
            ->with('success', 'تنوع به‌روزرسانی شد.');
    }

    public function generate(GenerateProductVariantsRequest $request, Product $product): RedirectResponse
    {
        $created = $this->combinationService->generate(
            $product,
            $request->validated('selections'),
            $request->user()?->id,
        );

        $message = $created > 0
            ? "{$created} تنوع جدید ساخته شد."
            : 'ترکیب جدیدی برای ساخت نبود (احتمالاً قبلاً ثبت شده‌اند).';

        return redirect()
            ->route('admin.catalog.products.edit', $product)
            ->with('success', $message);
    }

    public function reorder(Request $request, Product $product): RedirectResponse
    {
        abort_unless($request->user()?->can('catalog-products-update'), 403);

        $ids = $request->validate([
            'variant_ids' => ['required', 'array', 'min:1'],
            'variant_ids.*' => ['integer', 'exists:product_variants,id'],
        ])['variant_ids'];

        foreach (array_values($ids) as $index => $variantId) {
            ProductVariant::query()
                ->where('product_id', $product->id)
                ->whereKey($variantId)
                ->update(['sort' => $index + 1]);
        }

        return back()->with('success', 'ترتیب تنوع‌ها به‌روزرسانی شد.');
    }

    public function destroy(Product $product, ProductVariant $variant): RedirectResponse
    {
        abort_unless(auth()->user()?->can('catalog-products-update'), 403);
        $this->ensureVariantBelongsToProduct($product, $variant);

        if ($product->variants()->count() <= 1) {
            return redirect()
                ->route('admin.catalog.products.edit', $product)
                ->withErrors(['variant' => 'حداقل یک تنوع برای فروش الزامی است.']);
        }

        $variant->delete();

        return redirect()
            ->route('admin.catalog.products.edit', $product)
            ->with('success', 'تنوع حذف شد.');
    }

    private function ensureVariantBelongsToProduct(Product $product, ProductVariant $variant): void
    {
        if ($variant->product_id !== $product->id) {
            abort(404);
        }
    }
}
