<?php

namespace App\Modules\Comments\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Product;
use App\Modules\Comments\Enums\CommenterType;
use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Comments\Http\Requests\Admin\ReplyCommentRequest;
use App\Modules\Comments\Http\Requests\Admin\StoreCommentRequest;
use App\Modules\Comments\Http\Requests\Admin\UpdateCommentRequest;
use App\Modules\Comments\Models\Comment;
use App\Modules\Comments\Support\AdminCommentEnumOptions;
use App\Modules\Comments\Support\CommentPresenter;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommentController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('comments-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');
        $status = $status !== null && $status !== '' ? (int) $status : null;
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = Comment::query()
            ->with([
                'user',
                'moderator',
                'commentable' => fn (MorphTo $morphTo) => $morphTo->constrain([
                    Product::class => fn ($query) => $query->select('id', 'title', 'slug'),
                    Post::class => fn ($query) => $query->select('id', 'title', 'slug'),
                ]),
                'childrenRecursive.user',
                'childrenRecursive.moderator',
                'childrenRecursive.commentable' => fn (MorphTo $morphTo) => $morphTo->constrain([
                    Product::class => fn ($query) => $query->select('id', 'title', 'slug'),
                    Post::class => fn ($query) => $query->select('id', 'title', 'slug'),
                ]),
            ])
            ->roots()
            ->latest();

        if ($status !== null && in_array($status, [0, 1, 2], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('body', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%");
            });
        }

        $comments = $query->paginate($perPage)->withQueryString()
            ->through(fn (Comment $comment) => CommentPresenter::forList($comment));

        $actor = $request->user();

        return Inertia::render('Admin/Comments/Index', [
            'comments' => $comments,
            'filters' => [
                'search' => $search,
                'status' => $status !== null ? (string) $status : '',
                'per_page' => $perPage,
            ],
            'statusOptions' => AdminCommentEnumOptions::statusOptions(),
            'abilities' => [
                'create' => $actor?->can('comments-create') ?? false,
                'update' => $actor?->can('comments-update') ?? false,
                'delete' => $actor?->can('comments-delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('comments-create'), 403);

        return Inertia::render('Admin/Comments/Create', [
            'statusOptions' => AdminCommentEnumOptions::statusOptions(),
        ]);
    }

    public function store(StoreCommentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $commentable = match ($data['commentable_type']) {
            'product' => Product::query()->findOrFail($data['commentable_id']),
            'post' => Post::query()->findOrFail($data['commentable_id']),
        };

        $commentable->comments()->create([
            'body' => $data['body'],
            'display_name' => trim($data['display_name']),
            'user_id' => null,
            'commenter_type' => CommenterType::User,
            'rating' => $data['commentable_type'] === 'product' ? ($data['rating'] ?? null) : null,
            'status' => (int) $data['status'],
            'moderated_by' => $request->user()?->id,
            'moderated_at' => now(),
        ]);

        return redirect()
            ->route('admin.comments.index')
            ->with('success', 'نظر ثبت شد.');
    }

    public function edit(Request $request, Comment $comment): Response
    {
        abort_unless($request->user()?->can('comments-update'), 403);

        return Inertia::render('Admin/Comments/Edit', [
            'comment' => CommentPresenter::forForm($comment),
            'statusOptions' => AdminCommentEnumOptions::statusOptions(),
        ]);
    }

    public function update(UpdateCommentRequest $request, Comment $comment): RedirectResponse
    {
        $data = $request->validated();

        $comment->update([
            'display_name' => filled($data['display_name'] ?? null) ? trim($data['display_name']) : null,
            'body' => $data['body'],
            'status' => (int) $data['status'],
        ]);

        return redirect()
            ->route('admin.comments.index')
            ->with('success', 'نظر به‌روزرسانی شد.');
    }

    public function approve(Request $request, Comment $comment): RedirectResponse
    {
        abort_unless($request->user()?->can('comments-update'), 403);

        $comment->update([
            'status' => CommentStatus::Approved,
            'moderated_by' => $request->user()?->id,
            'moderated_at' => now(),
        ]);

        return back()->with('success', 'نظر تأیید شد.');
    }

    public function reject(Request $request, Comment $comment): RedirectResponse
    {
        abort_unless($request->user()?->can('comments-update'), 403);

        $comment->update([
            'status' => CommentStatus::Rejected,
            'moderated_by' => $request->user()?->id,
            'moderated_at' => now(),
        ]);

        return back()->with('success', 'نظر رد شد.');
    }

    public function reply(ReplyCommentRequest $request, Comment $comment): RedirectResponse
    {
        $data = $request->validated();

        $displayName = filled($data['display_name'] ?? null)
            ? trim($data['display_name'])
            : null;

        Comment::query()->create([
            'body' => $data['body'],
            'display_name' => $displayName,
            'user_id' => $displayName ? null : $request->user()?->id,
            'commenter_type' => $displayName ? CommenterType::User : CommenterType::Admin,
            'parent_id' => $comment->id,
            'commentable_id' => $comment->commentable_id,
            'commentable_type' => $comment->commentable_type,
            'status' => CommentStatus::Approved,
            'moderated_by' => $request->user()?->id,
            'moderated_at' => now(),
        ]);

        return back()->with('success', 'پاسخ ثبت شد.');
    }

    public function destroy(Request $request, Comment $comment): RedirectResponse
    {
        abort_unless($request->user()?->can('comments-delete'), 403);

        $comment->load('children');
        $this->deleteReplies($comment);

        return redirect()
            ->route('admin.comments.index')
            ->with('success', 'نظر و پاسخ‌های آن حذف شد.');
    }

    private function deleteReplies(Comment $comment): void
    {
        foreach ($comment->children as $reply) {
            $reply->load('children');
            $this->deleteReplies($reply);
        }

        $comment->delete();
    }
}
