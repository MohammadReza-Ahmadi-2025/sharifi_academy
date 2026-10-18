<?php

namespace App\Modules\Comments\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Support\PostPublicPresenter;
use App\Modules\Comments\Enums\CommenterType;
use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Comments\Http\Requests\Site\StorePostCommentRequest;
use Illuminate\Http\RedirectResponse;

class PostCommentController extends Controller
{
    public function store(StorePostCommentRequest $request, Post $post): RedirectResponse
    {
        PostPublicPresenter::ensurePublished($post);

        $data = $request->validated();

        $post->comments()->create([
            'user_id' => $request->user()->id,
            'commenter_type' => CommenterType::User,
            'body' => $data['body'],
            'status' => CommentStatus::Pending,
            'is_anonymous' => false,
        ]);

        return back()->with('success', 'نظر شما ثبت شد و پس از تأیید نمایش داده می‌شود.');
    }
}
