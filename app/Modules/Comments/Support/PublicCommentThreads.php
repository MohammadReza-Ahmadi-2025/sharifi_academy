<?php

namespace App\Modules\Comments\Support;

use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Comments\Models\Comment;
use Illuminate\Database\Eloquent\Model;

final class PublicCommentThreads
{
    /**
     * @return array{items: list<array<string, mixed>>, summary: array{count: int, average_rating: float|null}}
     */
    public static function approvedFor(Model $commentable): array
    {
        $roots = Comment::query()
            ->where('commentable_type', $commentable->getMorphClass())
            ->where('commentable_id', $commentable->getKey())
            ->where('status', CommentStatus::Approved)
            ->roots()
            ->with([
                'user',
                'children' => fn ($query) => $query
                    ->where('status', CommentStatus::Approved)
                    ->with(['user', 'children' => fn ($nested) => $nested
                        ->where('status', CommentStatus::Approved)
                        ->with('user')]),
            ])
            ->orderByDesc('created_at')
            ->get();

        $ratings = $roots->pluck('rating')->filter(fn ($rating) => is_numeric($rating) && $rating > 0);

        return [
            'items' => $roots->map(fn (Comment $comment) => CommentPresenter::forPublic($comment))->values()->all(),
            'summary' => [
                'count' => $roots->count(),
                'average_rating' => $ratings->isNotEmpty()
                    ? round((float) $ratings->avg(), 1)
                    : null,
            ],
        ];
    }
}
