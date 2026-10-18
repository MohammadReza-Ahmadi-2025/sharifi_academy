<?php

namespace App\Modules\Blog\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Services\BlogCategoryBrowseService;
use App\Modules\Blog\Support\PostSeo;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Support\CategoryPublicPresenter;
use App\Modules\Settings\Services\SiteSettingsService;
use App\Support\Http\CanonicalSeoSlugRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogCategoryController extends Controller
{
    public function __construct(private readonly BlogCategoryBrowseService $browse) {}

    public function show(Request $request, Category $category): Response|RedirectResponse
    {
        abort_unless($category->status && $category->type === Category::TYPE_BLOG, 404);

        $redirect = CanonicalSeoSlugRedirect::awayIfMismatched(
            $request,
            $category,
            'blog.categories.show',
            'category',
            3,
        );
        if ($redirect !== null) {
            return $redirect;
        }

        $canonicalSlug = $category->publicSlug();
        $requestedSlug = rawurldecode((string) $request->segment(3));
        if ($requestedSlug !== '' && $requestedSlug !== $canonicalSlug) {
            $url = route('blog.categories.show', ['category' => $canonicalSlug]);
            $query = $request->getQueryString();
            if ($query !== null && $query !== '') {
                $url .= '?'.$query;
            }

            return redirect()->to($url, 301);
        }

        if ((int) $request->query('page', 1) <= 1) {
            $category->recordVisit();
        }

        $settings = app(SiteSettingsService::class);
        $appName = (string) ($settings->get('site_name') ?: config('app.name'));
        $blogListLabel = filled($settings->get('blogs_header_title'))
            ? (string) $settings->get('blogs_header_title')
            : 'وبلاگ';

        $filters = $this->browse->filtersFromRequest($request);
        $posts = $this->browse->paginateCardsInCategory($category, $filters);
        $subcategories = $this->browse->subcategories($category);

        $categoryPayload = CategoryPublicPresenter::forPage($category);
        $seo = PostSeo::metaForBlogCategory($category);
        $canonical = (string) $seo['canonical'];

        return Inertia::render('Public/Blog/CategoryShow', [
            'blog_list_label' => $blogListLabel,
            'category' => array_merge($categoryPayload, ['seo' => $seo]),
            'posts' => $posts,
            'subcategories' => $subcategories,
            'filters' => $filters,
            'sortOptions' => $this->browse->sortOptions(),
            'structuredData' => [
                'breadcrumb' => PostSeo::blogCategoryBreadcrumbJsonLd($category, $settings),
                'webPage' => PostSeo::blogCategoryWebPageJsonLd($seo, $appName),
                'itemList' => PostSeo::indexItemListJsonLd($posts->getCollection(), $canonical),
            ],
        ]);
    }
}
