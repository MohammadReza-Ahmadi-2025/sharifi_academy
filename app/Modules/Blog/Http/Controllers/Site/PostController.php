<?php

namespace App\Modules\Blog\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Support\PostPublicPresenter;
use App\Modules\Blog\Support\PostSeo;
use App\Modules\Settings\Services\SiteSettingsService;
use App\Support\Http\CanonicalSeoSlugRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $settings = app(SiteSettingsService::class);
        $appName = (string) ($settings->get('site_name') ?: config('app.name'));

        $posts = Post::query()
            ->published()
            ->with(['featuredImage', 'categories'])
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withQueryString();

        $cards = PostPublicPresenter::paginatedCards($posts);
        $seo = PostSeo::indexMeta($settings, $appName);
        $canonical = $seo['canonical'];

        return Inertia::render('Public/Blog/Index', [
            'page' => PostPublicPresenter::indexHeader($settings, $appName),
            'seo' => $seo,
            'posts' => $cards,
            'structuredData' => [
                'breadcrumb' => PostSeo::indexBreadcrumbJsonLd($settings),
                'webPage' => PostSeo::indexWebPageJsonLd($seo, $appName),
                'itemList' => PostSeo::indexItemListJsonLd($cards->getCollection(), $canonical),
            ],
        ]);
    }

    public function show(Request $request, Post $post): Response|RedirectResponse
    {
        PostPublicPresenter::ensurePublished($post);

        $redirect = CanonicalSeoSlugRedirect::awayIfMismatched(
            $request,
            $post,
            'blog.show',
            'post',
            2,
        );
        if ($redirect !== null) {
            return $redirect;
        }

        $canonicalSlug = $post->publicSlug();
        $requestedSlug = rawurldecode((string) $request->segment(2));
        if ($requestedSlug !== '' && $requestedSlug !== $canonicalSlug) {
            $url = route('blog.show', ['post' => $canonicalSlug]);
            $query = $request->getQueryString();
            if ($query !== null && $query !== '') {
                $url .= '?'.$query;
            }

            return redirect()->to($url, 301);
        }

        $post->load(['featuredImage', 'categories', 'author']);
        $post->recordVisit();

        $settings = app(SiteSettingsService::class);
        $appName = (string) ($settings->get('site_name') ?: config('app.name'));
        $payload = PostPublicPresenter::forPage($post, $settings, $appName);

        $blogListLabel = filled($settings->get('blogs_header_title'))
            ? (string) $settings->get('blogs_header_title')
            : 'وبلاگ';

        return Inertia::render('Public/Blog/Show', [
            'post' => $payload,
            'blog_list_label' => $blogListLabel,
            'structuredData' => [
                'breadcrumb' => PostSeo::postBreadcrumbJsonLd($post, $settings),
                'blogPosting' => PostSeo::blogPostingJsonLd($post, $payload['seo'], $appName),
                'webPage' => PostSeo::postWebPageJsonLd($payload['seo'], $appName),
            ],
        ]);
    }
}
