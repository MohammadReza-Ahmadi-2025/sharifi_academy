<?php

namespace App\Modules\Blog\Policies;

use App\Models\User;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;

class PostPolicy
{
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
