<?php

namespace App\Modules\Blog\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Enums\ReactionType;
use App\Modules\Blog\Http\Requests\Site\StorePostReactionRequest;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Models\Reaction;
use App\Modules\Blog\Support\PostPublicPresenter;
use Illuminate\Http\RedirectResponse;

class PostReactionController extends Controller
{
    public function store(StorePostReactionRequest $request, Post $post, string $slug): RedirectResponse
    {
        PostPublicPresenter::ensurePublished($post);

        $type = ReactionType::from($request->validated('type'));
        $userId = (int) $request->user()->id;

        $existing = Reaction::query()
            ->where('reactable_type', $post->getMorphClass())
            ->where('reactable_id', $post->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing !== null && $existing->type === $type) {
            $existing->delete();

            return back();
        }

        Reaction::query()->updateOrCreate(
            [
                'reactable_type' => $post->getMorphClass(),
                'reactable_id' => $post->id,
                'user_id' => $userId,
            ],
            ['type' => $type],
        );

        return back();
    }
}
