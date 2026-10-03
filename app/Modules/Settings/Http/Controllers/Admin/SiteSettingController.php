<?php

namespace App\Modules\Settings\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Http\Requests\Admin\UpdateSiteSettingRequest;
use App\Modules\Settings\Models\SiteSetting;
use App\Modules\Settings\Support\SiteSettingDefinitions;
use App\Modules\Settings\Support\SiteSettingPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SiteSettingController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('site-settings-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $group = trim((string) $request->query('group', ''));
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = SiteSetting::query()->orderBy('group')->orderBy('key');

        if ($group !== '' && array_key_exists($group, config('site_settings.groups', []))) {
            $query->where('group', $group);
        }

        if ($search !== '') {
            $matchingKeys = collect(SiteSettingDefinitions::all())
                ->filter(function (array $definition, string $key) use ($search) {
                    return str_contains($key, $search)
                        || str_contains($definition['label'], $search);
                })
                ->keys()
                ->all();

            $query->where(function ($builder) use ($search, $matchingKeys) {
                $builder->where('key', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%");

                if ($matchingKeys !== []) {
                    $builder->orWhereIn('key', $matchingKeys);
                }
            });
        }

        $settings = $query->paginate($perPage)->withQueryString()
            ->through(fn (SiteSetting $setting) => SiteSettingPresenter::forList($setting));

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
            'groupOptions' => SiteSettingDefinitions::groupOptions(),
            'filters' => [
                'search' => $search,
                'group' => $group,
                'per_page' => $perPage,
            ],
            'abilities' => [
                'update' => $request->user()?->can('site-settings-update') ?? false,
            ],
        ]);
    }

    public function edit(Request $request, SiteSetting $siteSetting): Response
    {
        abort_unless($request->user()?->can('site-settings-update'), 403);

        return Inertia::render('Admin/Settings/Edit', [
            'setting' => SiteSettingPresenter::forForm($siteSetting),
        ]);
    }

    public function update(UpdateSiteSettingRequest $request, SiteSetting $siteSetting): RedirectResponse
    {
        $type = $siteSetting->type;
        $value = $request->input('value');

        if ($type === 'image') {
            if ($request->hasFile('image')) {
                $old = $siteSetting->value;
                if (is_string($old) && $old !== '' && Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }

                $value = $request->file('image')->store('settings', 'public');
            } else {
                $value = $siteSetting->value;
            }
        } elseif ($type === 'switch') {
            $value = $request->boolean('value') ? '1' : '0';
        } else {
            $value = SiteSetting::serializeValue($value, $type);
        }

        $siteSetting->update([
            'value' => $value,
        ]);

        return redirect()
            ->route('admin.settings.index', ['group' => $siteSetting->group])
            ->with('success', 'تنظیم ذخیره شد.');
    }
}
