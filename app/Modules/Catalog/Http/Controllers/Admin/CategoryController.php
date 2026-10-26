<?php

namespace App\Modules\Catalog\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Http\Requests\Admin\StoreCategoryRequest;
use App\Modules\Catalog\Http\Requests\Admin\UpdateCategoryRequest;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Services\SiteCourseCategoryNavService;
use App\Modules\Catalog\Support\AdminCategoryTypes;
use App\Modules\Catalog\Support\CategoryPresenter;
use App\Modules\Files\Http\Concerns\SyncsMorphFilesFromRequest;
use App\Services\Home\HomePageService;
use App\Support\SeoSlug;
use App\Support\SiteMenuCategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    use SyncsMorphFilesFromRequest;

    public function index(Request $request): Response
    {
        $type = $this->resolveType($request);
        abort_unless(AdminCategoryTypes::canIndex($request->user(), $type), 403);

        $search = trim((string) $request->query('search', ''));
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = Category::query()
            ->where('type', $type)
            ->with('parent')
            ->orderBy('sort')
            ->orderBy('title');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $categories = $query->paginate($perPage)->withQueryString()
            ->through(fn (Category $category) => CategoryPresenter::forList($category));

        $actor = $request->user();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'categoryType' => $type,
            'categoryTypeLabel' => AdminCategoryTypes::label($type),
            'typeOptions' => AdminCategoryTypes::optionsFor($actor),
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
                'type' => $type,
            ],
            'abilities' => [
                'create' => AdminCategoryTypes::canCreate($actor, $type),
                'update' => AdminCategoryTypes::canUpdate($actor, $type),
                'delete' => AdminCategoryTypes::canDelete($actor, $type),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $type = $this->resolveType($request);
        abort_unless(AdminCategoryTypes::canCreate($request->user(), $type), 403);

        return Inertia::render('Admin/Categories/Create', [
            'categoryType' => $type,
            'categoryTypeLabel' => AdminCategoryTypes::label($type),
            'creatableTypeOptions' => AdminCategoryTypes::creatableOptionsFor($request->user()),
            ...$this->formOptions($type),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $type = AdminCategoryTypes::resolveType($data['type'] ?? null);
        $slug = $this->uniqueCategorySlug($data['slug'] ?? $data['title']);

        $category = Category::query()->create([
            'parent_id' => $data['parent_id'] ?? null,
            'type' => $type,
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'sort' => $data['sort'] ?? 1,
            'status' => $data['status'] ? 1 : 0,
            'show_in_menu' => $data['show_in_menu'] ?? false,
            ...$this->categorySeoPayload($data),
        ]);

        $this->syncSingleImageFromRequest($category, $request);
        $this->bustSiteCaches();

        return redirect()
            ->route('admin.categories.edit', ['category' => $category, 'type' => $type])
            ->with('success', 'دسته‌بندی ایجاد شد.');
    }

    public function edit(Request $request, Category $category): Response
    {
        abort_unless(AdminCategoryTypes::canUpdate($request->user(), $category->type), 403);

        return Inertia::render('Admin/Categories/Edit', [
            'category' => CategoryPresenter::forForm($category),
            'categoryType' => $category->type,
            'categoryTypeLabel' => AdminCategoryTypes::label($category->type),
            'typeOptions' => AdminCategoryTypes::optionsFor($request->user()),
            ...$this->formOptions($category->type, $category->id),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $slug = $this->uniqueCategorySlug(
            (string) ($data['slug'] ?? $category->slug),
            $category->id,
        );

        $category->update([
            'parent_id' => $data['parent_id'] ?? null,
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'sort' => $data['sort'] ?? 1,
            'status' => $data['status'] ? 1 : 0,
            'show_in_menu' => $data['show_in_menu'] ?? false,
            ...$this->categorySeoPayload($data),
        ]);

        $this->syncSingleImageFromRequest($category, $request);
        $this->bustSiteCaches();

        return redirect()
            ->route('admin.categories.edit', ['category' => $category, 'type' => $category->type])
            ->with('success', 'دسته‌بندی به‌روزرسانی شد.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        abort_unless(AdminCategoryTypes::canDelete($request->user(), $category->type), 403);

        if ($category->children()->exists()) {
            return back()->withErrors(['category' => 'ابتدا زیردسته‌های این دسته را حذف یا منتقل کنید.']);
        }

        $blocked = match ($category->type) {
            Category::TYPE_PRODUCT => $category->products()->exists(),
            Category::TYPE_BLOG => $category->posts()->exists(),
            Category::TYPE_COURSE => $category->courses()->exists(),
            default => false,
        };

        if ($blocked) {
            return back()->withErrors(['category' => 'این دسته به محتوا متصل است و قابل حذف نیست.']);
        }

        $type = $category->type;
        $category->delete();
        $this->bustSiteCaches();

        return redirect()
            ->route('admin.categories.index', ['type' => $type])
            ->with('success', 'دسته‌بندی حذف شد.');
    }

    private function bustSiteCaches(): void
    {
        SiteMenuCategoryService::flush();
        SiteCourseCategoryNavService::flush();
        HomePageService::flush();
    }

    private function resolveType(Request $request): string
    {
        $requested = trim((string) $request->query('type', ''));
        $user = $request->user();

        if ($requested !== '') {
            $type = AdminCategoryTypes::resolveManagedType($requested);
            abort_unless(AdminCategoryTypes::canIndex($user, $type), 403);

            return $type;
        }

        foreach (AdminCategoryTypes::optionsFor($user) as $option) {
            return $option['value'];
        }

        abort(403);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function categorySeoPayload(array $data): array
    {
        return [
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'canonical' => $data['canonical'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(string $type, ?int $exceptId = null): array
    {
        $parents = Category::query()
            ->where('type', $type)
            ->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))
            ->orderBy('sort')
            ->orderBy('title')
            ->get()
            ->map(fn (Category $cat) => [
                'value' => $cat->id,
                'label' => $cat->title,
            ])
            ->values()
            ->all();

        return [
            'parentOptions' => $parents,
        ];
    }

    private function uniqueCategorySlug(string $slug, ?int $ignoreId = null): string
    {
        $slug = SeoSlug::normalize($slug);
        if ($slug === '') {
            $slug = 'category';
        }

        $baseSlug = $slug;
        $counter = 1;

        while (Category::query()
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        return $slug;
    }
}
