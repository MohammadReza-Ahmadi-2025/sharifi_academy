<?php

namespace App\Modules\Blog\Support;

use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Support\ProductPresenter;
use App\Modules\Files\Support\FilePresenter;
use App\Support\PersianDate;
use App\Support\Search\SiteSearchUrls;

class PostPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(Post $post): array
    {
        $post->loadMissing(['author', 'categories', 'featuredImage']);

        $author = $post->author;
        $authorName = $author
            ? trim(($author->name ?? '').' '.($author->family ?? ''))
            : null;

        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->publicSlug(),
            'status' => $post->status instanceof PostStatus ? $post->status->value : $post->status,
            'status_label' => $post->status instanceof PostStatus ? $post->status->label() : (string) $post->status,
            'author_name' => $authorName !== '' && $authorName !== null ? $authorName : null,
            'author_mobile' => $author?->mobile,
            'category_titles' => $post->categories->pluck('title')->values()->all(),
            'published_at' => $post->published_at?->toIso8601String(),
            'published_at_label' => PersianDate::formatDateTime($post->published_at),
            'created_at_label' => PersianDate::formatDateTime($post->created_at),
            'views' => (int) ($post->views ?? 0),
            'image_url' => $post->featuredImage?->publicUrl() ?: null,
            'public_url' => $post->status === PostStatus::Published
                ? SiteSearchUrls::post($post)
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forForm(Post $post): array
    {
        $post->loadMissing(['categories', 'featuredImage']);

        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt ?? '',
            'content' => $post->content ?? '',
            'status' => $post->status instanceof PostStatus ? $post->status->value : $post->status,
            'category_ids' => $post->categories->pluck('id')->all(),
            'meta_title' => $post->meta_title ?? '',
            'meta_description' => $post->meta_description ?? '',
            'meta_keywords' => ProductPresenter::metaKeywordsForInput($post->meta_keywords),
            'canonical' => $post->canonical ?? '',
            'featured' => $post->featuredImage ? FilePresenter::forUploader($post->featuredImage) : null,
        ];
    }
}
