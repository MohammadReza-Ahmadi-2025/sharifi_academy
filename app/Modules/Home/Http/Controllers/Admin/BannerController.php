<?php

namespace App\Modules\Home\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Files\Http\Concerns\SyncsMorphFilesFromRequest;
use App\Modules\Home\Enums\BannerLocation;
use App\Modules\Home\Http\Requests\Admin\StoreBannerRequest;
use App\Modules\Home\Http\Requests\Admin\UpdateBannerRequest;
use App\Modules\Home\Models\Banner;
use App\Modules\Home\Support\BannerPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BannerController extends Controller
{
    use SyncsMorphFilesFromRequest;

    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('home-banners-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = Banner::query()->with('file')->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('subtitle', 'like', "%{$search}%");
            });
        }

        $banners = $query->paginate($perPage)->withQueryString()
            ->through(fn (Banner $banner) => BannerPresenter::forList($banner));

        $actor = $request->user();

        return Inertia::render('Admin/Home/Banners/Index', [
            'banners' => $banners,
            'filters' => ['search' => $search, 'per_page' => $perPage],
            'abilities' => [
                'create' => $actor?->can('home-banners-create') ?? false,
                'update' => $actor?->can('home-banners-update') ?? false,
                'delete' => $actor?->can('home-banners-delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('home-banners-create'), 403);

        return Inertia::render('Admin/Home/Banners/Create', [
            'locationOptions' => $this->locationOptions(),
        ]);
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $banner = Banner::query()->create([
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'button_text' => $data['button_text'] ?? null,
            'button_link' => $data['button_link'] ?? null,
            'location' => $data['location'],
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ]);

        $this->syncSingleImageFromRequest($banner, $request, 'image');

        return redirect()
            ->route('admin.home-banners.index')
            ->with('success', 'بنر ایجاد شد.');
    }

    public function edit(Request $request, Banner $banner): Response
    {
        abort_unless($request->user()?->can('home-banners-update'), 403);

        $banner->load('file');

        return Inertia::render('Admin/Home/Banners/Edit', [
            'banner' => BannerPresenter::forForm($banner),
            'locationOptions' => $this->locationOptions(),
        ]);
    }

    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        $data = $request->validated();

        $banner->update([
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'button_text' => $data['button_text'] ?? null,
            'button_link' => $data['button_link'] ?? null,
            'location' => $data['location'],
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ]);

        $this->syncSingleImageFromRequest($banner, $request, 'image');

        return redirect()
            ->route('admin.home-banners.edit', $banner)
            ->with('success', 'بنر به‌روزرسانی شد.');
    }

    public function destroy(Request $request, Banner $banner): RedirectResponse
    {
        abort_unless($request->user()?->can('home-banners-delete'), 403);

        $banner->delete();

        return redirect()
            ->route('admin.home-banners.index')
            ->with('success', 'بنر حذف شد.');
    }

    /**
     * @return array<string, string>
     */
    protected function locationOptions(): array
    {
        $options = [];

        foreach (BannerLocation::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
