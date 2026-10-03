<?php

namespace App\Modules\Comments\Support;

use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Comments\Models\Comment;

class CommentPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(Comment $comment): array
    {
        $comment->loadMissing(['user', 'moderator', 'commentable', 'childrenRecursive']);

        return [
            'id' => $comment->id,
            'body' => $comment->body,
            'author_name' => $comment->author_name,
            'status' => $comment->status instanceof CommentStatus ? $comment->status->value : (int) $comment->status,
            'status_label' => $comment->status instanceof CommentStatus
                ? $comment->status->label()
                : CommentStatus::from((int) $comment->status)->label(),
            'rating' => $comment->rating,
            'commentable_type' => $comment->commentable_type,
            'commentable_type_label' => $comment->commentableTypeLabel(),
            'commentable_title' => $comment->commentableTitle(),
            'created_at' => $comment->created_at?->toIso8601String(),
            'children' => $comment->childrenRecursive
                ->map(fn (Comment $child) => self::forChild($child))
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forChild(Comment $comment): array
    {
        $comment->loadMissing(['user', 'moderator']);

        return [
            'id' => $comment->id,
            'body' => $comment->body,
            'author_name' => $comment->author_name,
            'status' => $comment->status instanceof CommentStatus ? $comment->status->value : (int) $comment->status,
            'status_label' => $comment->status instanceof CommentStatus
                ? $comment->status->label()
                : CommentStatus::from((int) $comment->status)->label(),
            'created_at' => $comment->created_at?->toIso8601String(),
            'children' => $comment->relationLoaded('childrenRecursive')
                ? $comment->childrenRecursive->map(fn (Comment $child) => self::forChild($child))->values()->all()
                : [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forForm(Comment $comment): array
    {
        $comment->loadMissing('commentable');

        return [
            'id' => $comment->id,
            'display_name' => $comment->display_name ?? '',
            'body' => $comment->body,
            'status' => $comment->status instanceof CommentStatus ? $comment->status->value : (int) $comment->status,
            'commentable_type_label' => $comment->commentableTypeLabel(),
            'commentable_title' => $comment->commentableTitle(),
        ];
    }
}
