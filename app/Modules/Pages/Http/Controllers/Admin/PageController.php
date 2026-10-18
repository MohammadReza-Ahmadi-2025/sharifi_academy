<?php

namespace App\Modules\Pages\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Pages\Http\Requests\Admin\StorePageRequest;
use App\Modules\Pages\Http\Requests\Admin\UpdatePageRequest;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Support\PagePresenter;
use App\Support\SeoSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('pages-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = Page::query()->latest('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $pages = $query->paginate($perPage)->withQueryString()
            ->through(fn (Page $page) => PagePresenter::forList($page));

        $actor = $request->user();

        return Inertia::render('Admin/Pages/Index', [
            'pages' => $pages,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
            'abilities' => [
                'create' => $actor?->can('pages-create') ?? false,
                'update' => $actor?->can('pages-update') ?? false,
                'delete' => $actor?->can('pages-delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('pages-create'), 403);

        return Inertia::render('Admin/Pages/Create');
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $slug = $this->uniquePageSlug($data['slug'] ?? $data['title']);

        Page::query()->create([
            'title' => $data['title'],
            'slug' => $slug,
            'content' => $data['content'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'meta_title' => $data['meta_title'] ?: $data['title'],
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'canonical' => $data['canonical'] ?? null,
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'صفحه با موفقیت ایجاد شد.');
    }

    public function edit(Request $request, Page $page): Response
    {
        abort_unless($request->user()?->can('pages-update'), 403);

        return Inertia::render('Admin/Pages/Edit', [
            'page' => PagePresenter::forForm($page),
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $data = $request->validated();
        $slug = SeoSlug::normalize($data['slug']);

        $page->update([
            'title' => $data['title'],
            'slug' => $this->uniquePageSlug($slug, $page->id),
            'content' => $data['content'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
            'meta_title' => $data['meta_title'] ?: $data['title'],
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'canonical' => $data['canonical'] ?? null,
        ]);

        return redirect()->route('admin.pages.index')->with('success', 'صفحه با موفقیت بروزرسانی شد.');
    }

    public function destroy(Request $request, Page $page): RedirectResponse
    {
        abort_unless($request->user()?->can('pages-delete'), 403);

        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'صفحه حذف شد.');
    }

    private function uniquePageSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = SeoSlug::normalize($base);
        $candidate = $slug;
        $suffix = 2;

        while (
            Page::query()
                ->when($ignoreId !== null, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $slug.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
