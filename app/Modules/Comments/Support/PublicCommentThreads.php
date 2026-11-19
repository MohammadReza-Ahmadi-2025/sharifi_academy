<?php

namespace App\Modules\Comments\Support;

use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Comments\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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

    /**
     * Recent approved root comments for feed cards (no nested replies).
     *
     * @param  list<int>  $commentableIds
     * @return array<int, list<array<string, mixed>>>
     */
    public static function previewsFor(string $morphClass, array $commentableIds, int $perItem = 2): array
    {
        $commentableIds = array_values(array_unique(array_map('intval', $commentableIds)));
        $result = [];

        foreach ($commentableIds as $id) {
            $result[$id] = [];
        }

        if ($commentableIds === []) {
            return $result;
        }

        $comments = Comment::query()
            ->where('commentable_type', $morphClass)
            ->whereIn('commentable_id', $commentableIds)
            ->where('status', CommentStatus::Approved)
            ->roots()
            ->with('user')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(fn (Comment $comment) => (int) $comment->commentable_id);

        foreach ($commentableIds as $id) {
            /** @var Collection<int, Comment> $group */
            $group = $comments->get($id, collect());
            $result[$id] = $group
                ->take($perItem)
                ->map(fn (Comment $comment) => [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'author_name' => $comment->author_name,
                    'created_at' => $comment->created_at?->toIso8601String(),
                ])
                ->values()
                ->all();
        }

        return $result;
    }
}
