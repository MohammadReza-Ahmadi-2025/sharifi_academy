<?php

namespace App\Modules\Home\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Models\Course;
use App\Modules\Home\Http\Requests\Admin\ReorderHomeSectionsRequest;
use App\Modules\Home\Http\Requests\Admin\StoreHomeSectionRequest;
use App\Modules\Home\Http\Requests\Admin\UpdateHomeSectionRequest;
use App\Modules\Home\Models\HomeSection;
use App\Modules\Home\Models\HomeSectionTab;
use App\Modules\Home\Services\HomeSectionSyncService;
use App\Modules\Home\Support\AdminHomeEnumOptions;
use App\Modules\Home\Support\BannerPresenter;
use App\Modules\Home\Support\HomeSectionPresenter;
use App\Modules\Home\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class HomeSectionController extends Controller
{
    public function __construct(
        private readonly HomeSectionSyncService $sync,
    ) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('home-sections-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = HomeSection::query()
            ->with(['tabs' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort')
            ->orderBy('id');

        if ($search !== '') {
            $query->where('title', 'like', "%{$search}%");
        }

        $sections = $query->paginate($perPage)->withQueryString()
            ->through(fn (HomeSection $section) => HomeSectionPresenter::forList($section));

        $actor = $request->user();

        return Inertia::render('Admin/Home/Sections/Index', [
            'sections' => $sections,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
            'abilities' => [
                'create' => $actor?->can('home-sections-create') ?? false,
                'update' => $actor?->can('home-sections-update') ?? false,
                'delete' => $actor?->can('home-sections-delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('home-sections-create'), 403);

        return Inertia::render('Admin/Home/Sections/Create', $this->formOptions($request));
    }

    public function store(StoreHomeSectionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $section = DB::transaction(function () use ($validated) {
            $section = HomeSection::query()->create([
                'title' => $validated['title'],
                'status' => (bool) ($validated['status'] ?? true),
                'sort' => (int) ($validated['sort'] ?? ((int) HomeSection::query()->max('sort') + 10)),
                'settings' => $validated['settings'] ?? [],
            ]);

            $this->persistTabs($section, $validated['tabs']);

            return $section;
        });

        return redirect()
            ->route('admin.home-sections.edit', $section)
            ->with('success', 'سکشن صفحه اصلی ایجاد شد.');
    }

    public function edit(Request $request, HomeSection $home_section): Response
    {
        abort_unless($request->user()?->can('home-sections-update'), 403);

        $home_section->load([
            'tabs' => fn ($q) => $q->orderBy('sort_order'),
            'tabs.items.item',
        ]);

        return Inertia::render('Admin/Home/Sections/Edit', array_merge(
            $this->formOptions($request),
            ['section' => HomeSectionPresenter::forForm($home_section)],
        ));
    }

    public function update(UpdateHomeSectionRequest $request, HomeSection $home_section): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $home_section) {
            $home_section->update([
                'title' => $validated['title'],
                'status' => (bool) ($validated['status'] ?? true),
                'sort' => (int) ($validated['sort'] ?? $home_section->sort),
                'settings' => array_merge($home_section->settings ?? [], $validated['settings'] ?? []),
            ]);

            $this->persistTabs($home_section, $validated['tabs']);
        });

        return redirect()
            ->route('admin.home-sections.edit', $home_section)
            ->with('success', 'سکشن صفحه اصلی به‌روزرسانی شد.');
    }

    public function destroy(Request $request, HomeSection $home_section): RedirectResponse
    {
        abort_unless($request->user()?->can('home-sections-delete'), 403);

        $home_section->delete();

        return redirect()
            ->route('admin.home-sections.index')
            ->with('success', 'سکشن حذف شد.');
    }

    public function reorder(ReorderHomeSectionsRequest $request): RedirectResponse
    {
        foreach ($request->validated('sections') as $row) {
            HomeSection::query()
                ->whereKey($row['id'])
                ->update(['sort' => (int) $row['sort']]);
        }

        return back()->with('success', 'ترتیب سکشن‌ها ذخیره شد.');
    }

    public function searchItems(Request $request): JsonResponse
    {
        abort_unless(
            $request->user()?->can('home-sections-update') || $request->user()?->can('home-sections-create'),
            403,
        );

        $type = (string) $request->query('type', '');
        $q = trim((string) $request->query('q', ''));

        if ($q === '' || mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $items = match ($type) {
            'product' => Product::query()
                ->active()
                ->visible()
                ->select('id', 'title', 'slug')
                ->where(fn ($query) => $query->where('title', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"))
                ->orderBy('title')
                ->limit(15)
                ->get(),

            'course' => Course::query()
                ->published()
                ->select('id', 'title', 'slug')
                ->where(fn ($query) => $query->where('title', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"))
                ->orderBy('title')
                ->limit(15)
                ->get(),

            'post' => Post::query()
                ->published()
                ->select('id', 'title', 'slug')
                ->where(fn ($query) => $query->where('title', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"))
                ->orderByDesc('published_at')
                ->limit(15)
                ->get(),

            'brand' => Brand::query()
                ->where('status', true)
                ->select('id', 'title', 'slug')
                ->where(fn ($query) => $query->where('title', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"))
                ->orderBy('title')
                ->limit(15)
                ->get(),

            'category' => Category::query()
                ->active()
                ->product()
                ->select('id', 'title', 'slug')
                ->where(fn ($query) => $query->where('title', 'like', "%{$q}%")->orWhere('slug', 'like', "%{$q}%"))
                ->orderBy('title')
                ->limit(15)
                ->get(),

            default => collect(),
        };

        return response()->json(
            $items->map(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug ?? null,
            ])->values(),
        );
    }

    /**
     * @param  list<array<string, mixed>>  $tabs
     */
    protected function persistTabs(HomeSection $section, array $tabs): void
    {
        $keptTabIds = [];

        foreach (array_values($tabs) as $index => $tabData) {
            $payload = [
                'title' => $tabData['title'] ?? 'default',
                'content_type' => $tabData['content_type'],
                'source_type' => $tabData['content_type'] === 'banner' ? 'manual' : $tabData['source_type'],
                'limit' => (int) ($tabData['limit'] ?? 8),
                'sort_order' => (int) ($tabData['sort_order'] ?? $index),
                'is_active' => (bool) ($tabData['is_active'] ?? true),
                'settings' => $this->sync->normalizedTabSettings($tabData),
            ];

            if (! empty($tabData['id'])) {
                $tab = HomeSectionTab::query()
                    ->where('section_id', $section->id)
                    ->whereKey($tabData['id'])
                    ->firstOrFail();
                $tab->update($payload);
            } else {
                $tab = $section->tabs()->create($payload);
            }

            $keptTabIds[] = $tab->id;

            if ($tab->content_type === 'banner') {
                $this->sync->syncBannerItems($tab, $tabData['banner_ids'] ?? []);
            } elseif ($tab->source_type === 'manual') {
                $this->sync->syncManualItems($tab, $tab->content_type, $tabData['manual_item_ids'] ?? []);
            } else {
                $this->sync->clearItems($tab);
            }
        }

        $section->tabs()->whereNotIn('id', $keptTabIds)->delete();
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(Request $request): array
    {
        $banners = Banner::query()
            ->with('file')
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->map(fn (Banner $banner) => BannerPresenter::forPicker($banner))
            ->values()
            ->all();

        $productCategories = Category::query()
            ->active()
            ->product()
            ->orderBy('sort')
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (Category $c) => ['id' => $c->id, 'title' => $c->title])
            ->values()
            ->all();

        $courseCategories = Category::query()
            ->active()
            ->course()
            ->orderBy('sort')
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (Category $c) => ['id' => $c->id, 'title' => $c->title])
            ->values()
            ->all();

        $brands = Brand::query()
            ->where('status', true)
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (Brand $b) => ['id' => $b->id, 'title' => $b->title])
            ->values()
            ->all();

        return [
            'options' => [
                'content_types' => AdminHomeEnumOptions::contentTypes(),
                'source_types' => AdminHomeEnumOptions::sourceTypes(),
                'section_layouts' => AdminHomeEnumOptions::sectionLayouts(),
                'banner_layouts' => AdminHomeEnumOptions::bannerLayouts(),
            ],
            'lookups' => [
                'banners' => $banners,
                'product_categories' => $productCategories,
                'course_categories' => $courseCategories,
                'brands' => $brands,
            ],
        ];
    }
}
