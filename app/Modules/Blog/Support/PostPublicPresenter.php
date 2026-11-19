<?php

namespace App\Modules\Blog\Support;

use App\Models\User;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;
use App\Modules\Comments\Support\PublicCommentThreads;
use App\Modules\Settings\Services\SiteSettingsService;
use App\Modules\Profile\Support\UserPublicSlug;
use App\Modules\Users\Support\UserAvatarMedia;
use App\Support\PersianDate;
use App\Support\Search\SiteSearchUrls;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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
     * Community feed card — author-first (user posts, not article grid).
     *
     * @return array<string, mixed>
     */
    /**
     * @param  array<string, mixed>|null  $reactions
     * @param  list<array<string, mixed>>|null  $commentsPreview
     * @return array<string, mixed>
     */
    public static function forFeedCard(
        Post $post,
        ?User $viewer = null,
        ?array $reactions = null,
        ?array $commentsPreview = null,
    ): array {
        $author = $post->relationLoaded('author') ? $post->author : null;
        $authorName = $author
            ? trim(($author->name ?? '').' '.($author->family ?? ''))
            : null;

        $reactions ??= PublicReactions::forPost($post, $viewer);

        return [
            ...self::forCard($post),
            'url' => SiteSearchUrls::post($post),
            'author_name' => $authorName !== '' && $authorName !== null ? $authorName : null,
            'author_avatar' => UserAvatarMedia::payload($author),
            'author_profile_url' => $author ? UserPublicSlug::profileUrl($author) : null,
            'published_at_label' => PersianDate::formatDateTime($post->published_at),
            'preview_html' => self::feedPreviewHtml($post),
            'reactions' => $reactions,
            'reactions_total' => (int) ($reactions['total'] ?? $post->reactions_count ?? 0),
            'comments_preview' => $commentsPreview ?? [],
            'comments_count' => (int) ($post->comments_count ?? count($commentsPreview ?? [])),
        ];
    }

    /**
     * @param  LengthAwarePaginator<int, Post>  $paginator
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public static function paginatedFeedCards(LengthAwarePaginator $paginator, ?User $viewer = null): LengthAwarePaginator
    {
        $paginator->setCollection(self::feedCards($paginator->getCollection(), $viewer));

        return $paginator;
    }

    /**
     * @param  Collection<int, Post>  $posts
     * @return Collection<int, array<string, mixed>>
     */
    public static function feedCards(Collection $posts, ?User $viewer = null): Collection
    {
        if ($posts->isEmpty()) {
            return collect();
        }

        $reactionsMap = PublicReactions::mapForPosts($posts, $viewer);
        $commentsMap = PublicCommentThreads::previewsFor(
            $posts->first()->getMorphClass(),
            $posts->pluck('id')->map(fn ($id) => (int) $id)->all(),
            2,
        );

        return $posts->map(fn (Post $post) => self::forFeedCard(
            $post,
            $viewer,
            $reactionsMap[$post->id] ?? null,
            $commentsMap[$post->id] ?? [],
        ));
    }

    private static function feedPreviewHtml(Post $post): ?string
    {
        if (filled($post->excerpt)) {
            return $post->excerpt;
        }

        $text = trim(html_entity_decode(strip_tags((string) $post->content), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($text === '') {
            return null;
        }

        if (mb_strlen($text) > 320) {
            $text = rtrim(mb_substr($text, 0, 320)).'…';
        }

        return '<p>'.e($text).'</p>';
    }

    /**
     * @return array<string, mixed>
     */
    public static function forPage(Post $post, SiteSettingsService $settings, string $appName, ?User $viewer = null): array
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
            'reactions' => PublicReactions::forPost($post, $viewer),
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
