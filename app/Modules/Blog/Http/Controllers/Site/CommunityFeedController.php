<?php

namespace App\Modules\Blog\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Enums\PostKind;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Support\PostPublicPresenter;
use App\Modules\Blog\Support\PostSeo;
use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Settings\Services\SiteSettingsService;
use App\Support\Http\CanonicalIdSlugRedirect;
use App\Support\MobileTabBar;
use App\Support\Search\SiteSearchUrls;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommunityFeedController extends Controller
{
    public function index(Request $request): Response
    {
        $settings = app(SiteSettingsService::class);
        $appName = (string) ($settings->get('site_name') ?: config('app.name'));
        $tabBar = MobileTabBar::forUser($request->user());

        $posts = Post::query()
            ->community()
            ->published()
            ->with(['featuredImage', 'categories', 'author'])
            ->withCount([
                'comments as comments_count' => fn ($query) => $query
                    ->where('status', CommentStatus::Approved)
                    ->whereNull('parent_id'),
                'reactions as reactions_count',
            ])
            ->orderByDesc('published_at')
            ->paginate(15)
            ->withQueryString();

        $headerTitle = filled($settings->get('posts_header_title'))
            ? (string) $settings->get('posts_header_title')
            : 'پست‌ها';
        $subheader = filled($settings->get('posts_subheader_title'))
            ? (string) $settings->get('posts_subheader_title')
            : 'آخرین پست‌هایی که کاربران نوشته‌اند و تأیید شده‌اند.';
        $metaTitle = filled($settings->get('posts_meta_title'))
            ? (string) $settings->get('posts_meta_title')
            : "{$headerTitle} | {$appName}";
        $description = filled($settings->get('posts_meta_description'))
            ? (string) $settings->get('posts_meta_description')
            : "پست‌های تأییدشده کاربران {$appName}";
        $keywords = filled($settings->get('posts_meta_keywords'))
            ? (string) $settings->get('posts_meta_keywords')
            : null;
        $canonical = url('/posts');

        return Inertia::render('Public/Posts/Index', [
            'page' => [
                'header_title' => $headerTitle,
                'subheader' => $subheader,
            ],
            'seo' => [
                'title' => $metaTitle,
                'description' => $description,
                'keywords' => $keywords,
                'canonical' => $canonical,
                'og_image' => null,
                'og_type' => 'website',
            ],
            'posts' => PostPublicPresenter::paginatedFeedCards($posts, $request->user()),
            'create_post_url' => $tabBar['create_post_url'],
            'can_create_post' => $tabBar['can_create_post'],
            'needs_profile' => $tabBar['needs_profile'],
            'needs_enrollment' => $tabBar['needs_enrollment'],
        ]);
    }

    public function show(Request $request, Post $post, string $slug): Response|RedirectResponse
    {
        PostPublicPresenter::ensurePublished($post);

        if ($post->kind !== PostKind::Community) {
            return redirect()->to(url(SiteSearchUrls::post($post)), 301);
        }

        $redirect = CanonicalIdSlugRedirect::awayIfSlugMismatched(
            $request,
            $post,
            'posts.show',
            'post',
            $slug,
        );
        if ($redirect !== null) {
            return $redirect;
        }

        $post->load(['featuredImage', 'categories', 'author']);
        $post->recordVisit();

        $settings = app(SiteSettingsService::class);
        $appName = (string) ($settings->get('site_name') ?: config('app.name'));
        $payload = PostPublicPresenter::forPage($post, $settings, $appName, $request->user());
        $feedCard = PostPublicPresenter::forFeedCard($post, $request->user());

        return Inertia::render('Public/Posts/Show', [
            'post' => [
                ...$payload,
                'author_profile_url' => $feedCard['author_profile_url'] ?? null,
                'author_avatar' => $feedCard['author_avatar'] ?? null,
            ],
            'seo' => $payload['seo'],
            'structuredData' => [
                'breadcrumb' => [
                    '@context' => 'https://schema.org',
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        [
                            '@type' => 'ListItem',
                            'position' => 1,
                            'name' => 'خانه',
                            'item' => url('/'),
                        ],
                        [
                            '@type' => 'ListItem',
                            'position' => 2,
                            'name' => 'پست‌ها',
                            'item' => url('/posts'),
                        ],
                        [
                            '@type' => 'ListItem',
                            'position' => 3,
                            'name' => $post->title,
                            'item' => $payload['seo']['canonical'] ?? url('/posts'),
                        ],
                    ],
                ],
                'webPage' => PostSeo::postWebPageJsonLd($payload['seo'], $appName),
            ],
        ]);
    }
}
