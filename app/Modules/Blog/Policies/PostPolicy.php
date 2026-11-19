<?php

namespace App\Modules\Blog\Policies;

use App\Models\User;
use App\Modules\Blog\Enums\PostKind;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Support\CommunityPostingEligibility;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        // List page is always reachable in panel/account; create stays gated.
        // Controllers only show the current user's community posts.
        return true;
    }

    public function view(User $user, Post $post): bool
    {
        if ($user->can('blog-posts-index')) {
            return true;
        }

        return $post->kind === PostKind::Community
            && (int) $post->author_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return CommunityPostingEligibility::canCreate($user);
    }

    public function update(User $user, Post $post): bool
    {
        if ($user->can('blog-posts-update')) {
            return true;
        }

        // Articles (blog) are admin-only; authors may only edit their community posts.
        return $post->kind === PostKind::Community
            && (int) $post->author_id === (int) $user->id;
    }

    public function delete(User $user, Post $post): bool
    {
        if ($user->can('blog-posts-delete')) {
            return true;
        }

        return $post->kind === PostKind::Community
            && (int) $post->author_id === (int) $user->id;
    }

    public function comment(?User $user, Post $post): bool
    {
        return $this->canEngageWithPublishedPost($user, $post);
    }

    public function react(?User $user, Post $post): bool
    {
        return $this->canEngageWithPublishedPost($user, $post);
    }

    private function canEngageWithPublishedPost(?User $user, Post $post): bool
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
