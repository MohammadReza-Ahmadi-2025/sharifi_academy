<?php

namespace App\Modules\Blog\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Http\Requests\Admin\UpdateBlogSeoSettingsRequest;
use App\Modules\Settings\Models\SiteSetting;
use App\Modules\Settings\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogSeoSettingsController extends Controller
{
    /** @var list<string> */
    private const KEYS = [
        'blogs_meta_title',
        'blogs_meta_description',
        'blogs_meta_keywords',
        'blogs_header_title',
        'blogs_subheader_title',
    ];

    public function edit(Request $request, SiteSettingsService $settings): Response
    {
        abort_unless(
            $request->user()?->can('blog-posts-update') || $request->user()?->can('site-settings-update'),
            403,
        );

        return Inertia::render('Admin/Blog/Settings', [
            'settings' => [
                'blogs_meta_title' => (string) ($settings->get('blogs_meta_title') ?? ''),
                'blogs_meta_description' => (string) ($settings->get('blogs_meta_description') ?? ''),
                'blogs_meta_keywords' => (string) ($settings->get('blogs_meta_keywords') ?? ''),
                'blogs_header_title' => (string) ($settings->get('blogs_header_title') ?? ''),
                'blogs_subheader_title' => (string) ($settings->get('blogs_subheader_title') ?? ''),
            ],
        ]);
    }

    public function update(UpdateBlogSeoSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $definitions = config('site_settings.definitions', []);

        foreach (self::KEYS as $key) {
            $definition = $definitions[$key] ?? null;
            if (! is_array($definition)) {
                continue;
            }

            $type = (string) ($definition['type'] ?? 'text');
            $raw = $data[$key] ?? null;
            $serialized = SiteSetting::serializeValue(
                is_string($raw) ? trim($raw) : $raw,
                $type,
            );

            SiteSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $serialized,
                    'type' => $type,
                    'group' => (string) ($definition['group'] ?? 'seo'),
                    'autoload' => (bool) ($definition['autoload'] ?? true),
                ],
            );
        }

        return redirect()
            ->route('admin.posts.settings.edit')
            ->with('success', 'تنظیمات سئوی وبلاگ ذخیره شد.');
    }
}
