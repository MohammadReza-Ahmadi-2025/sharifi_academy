<?php

namespace App\Modules\Menu\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Menu\Enums\MenuLocation;
use App\Modules\Menu\Http\Requests\Admin\StoreMenuRequest;
use App\Modules\Menu\Http\Requests\Admin\UpdateMenuRequest;
use App\Modules\Menu\Models\Menu;
use App\Modules\Menu\Services\MenuSyncService;
use App\Modules\Menu\Support\AdminMenuEnumOptions;
use App\Modules\Menu\Support\MenuPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function __construct(
        private readonly MenuSyncService $sync,
    ) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('menus-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $location = trim((string) $request->query('location', ''));
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = Menu::query()->withCount('allItems')->orderBy('sort')->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%");
            });
        }

        if ($location !== '') {
            $query->where('location', $location);
        }

        $menus = $query->paginate($perPage)->withQueryString()
            ->through(fn (Menu $menu) => MenuPresenter::forList($menu));

        $actor = $request->user();

        return Inertia::render('Admin/Menus/Index', [
            'menus' => $menus,
            'filters' => [
                'search' => $search,
                'location' => $location,
                'per_page' => $perPage,
            ],
            'locationOptions' => AdminMenuEnumOptions::locations(),
            'abilities' => [
                'create' => $actor?->can('menus-create') ?? false,
                'update' => $actor?->can('menus-update') ?? false,
                'delete' => $actor?->can('menus-delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('menus-create'), 403);

        return Inertia::render('Admin/Menus/Create', [
            'locationOptions' => AdminMenuEnumOptions::locations(),
            'itemTypeOptions' => AdminMenuEnumOptions::itemTypes(),
        ]);
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $menu = Menu::query()->create([
            'title' => $validated['title'],
            'url' => $validated['url'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'location' => $validated['location'],
            'sort' => (int) ($validated['sort'] ?? ((int) Menu::query()->max('sort') + 1)),
            'status' => (bool) ($validated['status'] ?? true),
        ]);

        $this->sync->syncItems($menu, $validated['items'] ?? []);

        return redirect()->route('admin.menus.index')->with('success', 'منو ایجاد شد.');
    }

    public function edit(Request $request, Menu $menu): Response
    {
        abort_unless($request->user()?->can('menus-update'), 403);

        $menu->load('allItems');

        return Inertia::render('Admin/Menus/Edit', [
            'menu' => MenuPresenter::forForm($menu),
            'locationOptions' => AdminMenuEnumOptions::locations(),
            'itemTypeOptions' => AdminMenuEnumOptions::itemTypes(),
        ]);
    }

    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $validated = $request->validated();

        $menu->update([
            'title' => $validated['title'],
            'url' => $validated['url'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'location' => $validated['location'],
            'sort' => (int) ($validated['sort'] ?? $menu->sort),
            'status' => (bool) ($validated['status'] ?? true),
        ]);

        $this->sync->syncItems($menu, $validated['items'] ?? []);

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'منو به‌روزرسانی شد.');
    }

    public function destroy(Request $request, Menu $menu): RedirectResponse
    {
        abort_unless($request->user()?->can('menus-delete'), 403);

        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', 'منو حذف شد.');
    }
}
