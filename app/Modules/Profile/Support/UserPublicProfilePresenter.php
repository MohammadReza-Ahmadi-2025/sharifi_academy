<?php

namespace App\Modules\Profile\Support;

use App\Models\User;
use App\Modules\Blog\Enums\PostKind;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Support\PostPublicPresenter;
use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Users\Support\UserAvatarMedia;

final class UserPublicProfilePresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forPage(User $user): array
    {
        $base = InstructorPublicResumePresenter::forUser($user);
        $uuid = InstructorPublicUuid::routeKey($user);
        $slug = UserPublicSlug::forUser($user);
        $profileUrl = UserPublicSlug::profileUrl($user, $uuid);

        $showResume = ResumeModeration::isPubliclyVisible($user);
        if (! $showResume) {
            $base['sections'] = [];
            $base['skills'] = [];
        }

        $postsQuery = Post::query()
            ->where('author_id', $user->id)
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
            ->limit(30)
            ->get();

        $posts = PostPublicPresenter::feedCards($postsQuery, auth()->user())->values()->all();

        return [
            ...$base,
            'uuid' => $uuid,
            'slug' => $slug,
            'profile_url' => $profileUrl,
            'is_instructor' => $user->hasRole('instructor'),
            'show_resume' => $showResume,
            'posts' => $posts,
            'posts_count' => count($posts),
            'kind' => PostKind::Community->value,
        ];
    }
}
