<?php

namespace App\Modules\Blog\Support;

use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;
use App\Modules\Comments\Support\PublicCommentThreads;
use App\Modules\Settings\Services\SiteSettingsService;
use App\Support\PersianDate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostPublicPresenter
{
    public static function ensurePublished(Post $post): void
    {
        if ($post->status !== PostStatus::Published || $post->published_at === null || $post->published_at->isFuture()) {
            throw new NotFoundHttpException;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function forCard(Post $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->publicSlug(),
            'excerpt' => $post->excerpt,
            'published_at' => $post->published_at?->toIso8601String(),
            'published_at_label' => PersianDate::formatDate($post->published_at),
            'image_url' => $post->relationLoaded('featuredImage') && $post->featuredImage
                ? $post->featuredImage->publicUrl()
                : null,
            'categories' => $post->relationLoaded('categories')
                ? $post->categories->map(fn ($cat) => [
                    'id' => $cat->id,
                    'title' => $cat->title,
                    'slug' => $cat->publicSlug(),
                ])->values()->all()
                : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forPage(Post $post, SiteSettingsService $settings, string $appName): array
    {
        $seo = PostSeo::metaForPost($post, $appName);
        $comments = PublicCommentThreads::approvedFor($post);

        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->publicSlug(),
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'published_at' => $post->published_at?->toIso8601String(),
            'published_at_label' => PersianDate::formatDate($post->published_at),
            'image_url' => $post->relationLoaded('featuredImage') && $post->featuredImage
                ? $post->featuredImage->publicUrl()
                : null,
            'categories' => $post->relationLoaded('categories')
                ? $post->categories->map(fn ($cat) => [
                    'id' => $cat->id,
                    'title' => $cat->title,
                    'slug' => $cat->publicSlug(),
                ])->values()->all()
                : [],
            'author_name' => $post->relationLoaded('author') && $post->author
                ? trim($post->author->name.' '.$post->author->family)
                : null,
            'seo' => $seo,
            'comments' => $comments['items'],
            'comments_summary' => $comments['summary'],
        ];
    }

    /**
     * @return array{header_title: string, subheader: string|null}
     */
    public static function indexHeader(SiteSettingsService $settings, string $appName): array
    {
        return [
            'header_title' => filled($settings->get('blogs_header_title'))
                ? (string) $settings->get('blogs_header_title')
                : 'وبلاگ',
            'subheader' => filled($settings->get('blogs_subheader_title'))
                ? (string) $settings->get('blogs_subheader_title')
                : "مقالات و مطالب آموزشی {$appName}",
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, Post>  $paginator
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public static function paginatedCards(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $paginator->setCollection(
            $paginator->getCollection()->map(fn (Post $post) => self::forCard($post)),
        );

        return $paginator;
    }
}
