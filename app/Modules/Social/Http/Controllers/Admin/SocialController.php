<?php

namespace App\Modules\Social\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Social\Http\Requests\Admin\StoreSocialRequest;
use App\Modules\Social\Http\Requests\Admin\UpdateSocialRequest;
use App\Modules\Social\Models\Social;
use App\Modules\Social\Support\AdminSocialTypeOptions;
use App\Modules\Social\Support\SocialPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SocialController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('socials-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $active = $request->query('active');
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = Social::query()->orderBy('sort')->orderBy('id');

        if ($active === '1' || $active === '0') {
            $query->where('is_active', $active === '1');
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('type', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%")
                    ->orWhere('label', 'like', "%{$search}%");
            });
        }

        $socials = $query->paginate($perPage)->withQueryString()
            ->through(fn (Social $social) => SocialPresenter::forList($social));

        $actor = $request->user();

        return Inertia::render('Admin/Social/Index', [
            'socials' => $socials,
            'filters' => [
                'search' => $search,
                'active' => is_string($active) ? $active : '',
                'per_page' => $perPage,
            ],
            'abilities' => [
                'create' => $actor?->can('socials-create') ?? false,
                'update' => $actor?->can('socials-update') ?? false,
                'delete' => $actor?->can('socials-delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('socials-create'), 403);

        return Inertia::render('Admin/Social/Create', [
            'typeOptions' => AdminSocialTypeOptions::all(),
        ]);
    }

    public function store(StoreSocialRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Social::query()->create([
            'type' => $data['type'],
            'value' => $data['value'],
            'label' => $data['label'] ?? null,
            'target_blank' => $request->boolean('target_blank'),
            'is_active' => $request->boolean('is_active', true),
            'sort' => (int) Social::query()->max('sort') + 1,
        ]);

        return redirect()
            ->route('admin.socials.index')
            ->with('success', 'آیتم شبکه اجتماعی ایجاد شد.');
    }

    public function edit(Request $request, Social $social): Response
    {
        abort_unless($request->user()?->can('socials-update'), 403);

        return Inertia::render('Admin/Social/Edit', [
            'social' => SocialPresenter::forForm($social),
            'typeOptions' => AdminSocialTypeOptions::all(),
        ]);
    }

    public function update(UpdateSocialRequest $request, Social $social): RedirectResponse
    {
        $data = $request->validated();

        $social->update([
            'type' => $data['type'],
            'value' => $data['value'],
            'label' => $data['label'] ?? null,
            'target_blank' => $request->boolean('target_blank'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.socials.edit', $social)
            ->with('success', 'آیتم به‌روزرسانی شد.');
    }

    public function destroy(Request $request, Social $social): RedirectResponse
    {
        abort_unless($request->user()?->can('socials-delete'), 403);

        $social->delete();

        return redirect()
            ->route('admin.socials.index')
            ->with('success', 'آیتم حذف شد.');
    }
}
