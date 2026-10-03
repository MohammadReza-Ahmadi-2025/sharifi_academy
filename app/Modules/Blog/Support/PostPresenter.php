<?php

namespace App\Modules\Blog\Support;

use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Support\ProductPresenter;
use App\Modules\Files\Support\FilePresenter;

class PostPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(Post $post): array
    {
        $post->loadMissing(['author', 'categories']);

        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'status' => $post->status instanceof PostStatus ? $post->status->value : $post->status,
            'status_label' => $post->status instanceof PostStatus ? $post->status->label() : $post->status,
            'author_name' => $post->author?->name,
            'category_titles' => $post->categories->pluck('title')->all(),
            'published_at' => $post->published_at?->toIso8601String(),
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
