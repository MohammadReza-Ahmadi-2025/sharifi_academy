<?php

namespace App\Modules\Blog\Support;

use App\Models\User;
use App\Modules\Blog\Enums\ReactionType;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Models\Reaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PublicReactions
{
    /**
     * @return array{
     *     total: int,
     *     viewer_type: string|null,
     *     counts: list<array{type: string, label: string, emoji: string, count: int}>,
     *     types: list<array{value: string, label: string, emoji: string}>
     * }
     */
    public static function forPost(Post $post, ?User $viewer = null): array
    {
        return self::mapForPostIds([$post->id], $post->getMorphClass(), $viewer)[$post->id]
            ?? self::emptyPayload();
    }

    /**
     * @param  Collection<int, Post>|list<Post>  $posts
     * @return array<int, array{
     *     total: int,
     *     viewer_type: string|null,
     *     counts: list<array{type: string, label: string, emoji: string, count: int}>,
     *     types: list<array{value: string, label: string, emoji: string}>
     * }>
     */
    public static function mapForPosts(Collection|array $posts, ?User $viewer = null): array
    {
        $collection = Collection::wrap($posts)->values();
        if ($collection->isEmpty()) {
            return [];
        }

        /** @var Post $first */
        $first = $collection->first();

        return self::mapForPostIds(
            $collection->pluck('id')->map(fn ($id) => (int) $id)->all(),
            $first->getMorphClass(),
            $viewer,
        );
    }

    /**
     * @param  list<int>  $postIds
     * @return array<int, array{
     *     total: int,
     *     viewer_type: string|null,
     *     counts: list<array{type: string, label: string, emoji: string, count: int}>,
     *     types: list<array{value: string, label: string, emoji: string}>
     * }>
     */
    public static function mapForPostIds(array $postIds, string $morphClass, ?User $viewer = null): array
    {
        $postIds = array_values(array_unique(array_map('intval', $postIds)));
        $empty = self::emptyPayload();
        $result = [];

        foreach ($postIds as $id) {
            $result[$id] = $empty;
        }

        if ($postIds === []) {
            return $result;
        }

        $rows = Reaction::query()
            ->where('reactable_type', $morphClass)
            ->whereIn('reactable_id', $postIds)
            ->select('reactable_id', 'type', DB::raw('count(*) as aggregate'))
            ->groupBy('reactable_id', 'type')
            ->get();

        $byPost = [];
        foreach ($rows as $row) {
            $postId = (int) $row->reactable_id;
            $type = $row->type instanceof ReactionType ? $row->type->value : (string) $row->type;
            $byPost[$postId][$type] = (int) $row->aggregate;
        }

        $viewerTypes = [];
        if ($viewer !== null) {
            $viewerTypes = Reaction::query()
                ->where('reactable_type', $morphClass)
                ->whereIn('reactable_id', $postIds)
                ->where('user_id', $viewer->id)
                ->get(['reactable_id', 'type'])
                ->mapWithKeys(function (Reaction $reaction) {
                    $type = $reaction->type instanceof ReactionType
                        ? $reaction->type->value
                        : (string) $reaction->type;

                    return [(int) $reaction->reactable_id => $type];
                })
                ->all();
        }

        $types = ReactionType::options();

        foreach ($postIds as $postId) {
            $rawCounts = $byPost[$postId] ?? [];
            $counts = [];
            $total = 0;

            foreach (ReactionType::cases() as $type) {
                $count = (int) ($rawCounts[$type->value] ?? 0);
                if ($count === 0) {
                    continue;
                }

                $total += $count;
                $counts[] = [
                    'type' => $type->value,
                    'label' => $type->label(),
                    'emoji' => $type->emoji(),
                    'count' => $count,
                ];
            }

            $result[$postId] = [
                'total' => $total,
                'viewer_type' => $viewerTypes[$postId] ?? null,
                'counts' => $counts,
                'types' => $types,
            ];
        }

        return $result;
    }

    /**
     * @return array{
     *     total: int,
     *     viewer_type: string|null,
     *     counts: list<array{type: string, label: string, emoji: string, count: int}>,
     *     types: list<array{value: string, label: string, emoji: string}>
     * }
     */
    private static function emptyPayload(): array
    {
        return [
            'total' => 0,
            'viewer_type' => null,
            'counts' => [],
            'types' => ReactionType::options(),
        ];
    }
}
