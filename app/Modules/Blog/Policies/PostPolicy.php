<?php

namespace App\Modules\Blog\Policies;

use App\Models\User;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('blog-posts-index') || $user->can('panel-blog-posts-manage');
    }

    public function view(User $user, Post $post): bool
    {
        if ($user->can('blog-posts-index')) {
            return true;
        }

        return $user->can('panel-blog-posts-manage') && (int) $post->author_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('blog-posts-create') || $user->can('panel-blog-posts-manage');
    }

    public function update(User $user, Post $post): bool
    {
        if ($user->can('blog-posts-update')) {
            return true;
        }

        return $user->can('panel-blog-posts-manage') && (int) $post->author_id === (int) $user->id;
    }

    public function delete(User $user, Post $post): bool
    {
        if ($user->can('blog-posts-delete')) {
            return true;
        }

        return $user->can('panel-blog-posts-manage') && (int) $post->author_id === (int) $user->id;
    }

    public function comment(?User $user, Post $post): bool
    {
        if ($user === null) {
            return false;
        }

        return $post->status === PostStatus::Published
            && $post->published_at !== null
            && $post->published_at->lte(now())
            && ! $post->trashed();
    }
}
