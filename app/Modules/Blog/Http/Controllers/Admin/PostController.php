<?php

namespace App\Modules\Blog\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Enums\PostKind;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Http\Requests\Admin\StorePostRequest;
use App\Modules\Blog\Http\Requests\Admin\UpdatePostRequest;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Support\AdminBlogEnumOptions;
use App\Modules\Blog\Support\PostPresenter;
use App\Modules\Catalog\Models\Category;
use App\Modules\Files\Http\Concerns\SyncsMorphFilesFromRequest;
use App\Support\SeoSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    use SyncsMorphFilesFromRequest;

    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('blog-posts-index'), 403);

        $search = trim((string) $request->query('search', ''));
        // Default: all statuses so pending community submissions are not hidden.
        $status = $request->query('status', 'all');
        if ($status === 'all') {
            $status = null;
        }
        $categoryId = $request->query('category_id');
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = Post::query()
            ->with(['categories', 'author', 'featuredImage'])
            ->latest('published_at')
            ->latest('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhereHas('author', function ($authorQuery) use ($search) {
                        $authorQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('family', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    });
            });
        }

        if (filled($status)) {
            $query->where('status', $status);
        }

        if (filled($categoryId)) {
            $query->whereHas('categories', fn ($q) => $q->where('categories.id', $categoryId));
        }

        $posts = $query->paginate($perPage)->withQueryString()
            ->through(fn (Post $post) => PostPresenter::forList($post));

        $actor = $request->user();

        return Inertia::render('Admin/Blog/Posts/Index', [
            'posts' => $posts,
            'filters' => [
                'search' => $search,
                'status' => $status ?? 'all',
                'category_id' => $categoryId ? (int) $categoryId : null,
                'per_page' => $perPage,
            ],
            'categoryOptions' => $this->categoryOptions(),
            'statusOptions' => [
                ['value' => 'all', 'label' => 'همه وضعیت‌ها'],
                ...AdminBlogEnumOptions::postStatusOptions(),
            ],
            'statusCounts' => [
                'all' => Post::query()->count(),
                'published' => Post::query()->where('status', PostStatus::Published)->count(),
                'pending_review' => Post::query()->where('status', PostStatus::PendingReview)->count(),
                'draft' => Post::query()->where('status', PostStatus::Draft)->count(),
            ],
            'abilities' => [
                'create' => $actor?->can('blog-posts-create') ?? false,
                'update' => $actor?->can('blog-posts-update') ?? false,
                'delete' => $actor?->can('blog-posts-delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('blog-posts-create'), 403);

        return Inertia::render('Admin/Blog/Posts/Create', [
            'categoryOptions' => $this->categoryOptions(),
            'statusOptions' => AdminBlogEnumOptions::postStatusOptions(),
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $slug = $this->uniquePostSlug($data['slug'] ?? $data['title']);
        $status = PostStatus::from($data['status']);

        $post = Post::query()->create([
            'author_id' => $request->user()?->id,
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'status' => $status,
            'kind' => PostKind::Article,
            'published_at' => $status === PostStatus::Published ? now() : null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'canonical' => $data['canonical'] ?? null,
        ]);

        $post->categories()->sync($data['category_ids']);
        $this->syncFeaturedImageFromRequest($post, $request);

        return redirect()
            ->route('admin.posts.edit', $post->id)
            ->with('success', 'پست ایجاد شد.');
    }

    public function edit(Request $request, Post $post): Response
    {
        abort_unless($request->user()?->can('blog-posts-update'), 403);

        return Inertia::render('Admin/Blog/Posts/Edit', [
            'post' => PostPresenter::forForm($post),
            'categoryOptions' => $this->categoryOptions(),
            'statusOptions' => AdminBlogEnumOptions::postStatusOptions(),
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $data = $request->validated();
        $slug = $this->uniquePostSlug(
            (string) ($data['slug'] ?? $post->slug),
            $post->id,
        );

        $status = PostStatus::from($data['status']);
        $publishedAt = $post->published_at;

        if ($status === PostStatus::Published && $publishedAt === null) {
            $publishedAt = now();
        }

        $post->update([
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'status' => $status,
            'published_at' => $publishedAt,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'canonical' => $data['canonical'] ?? null,
        ]);

        $post->categories()->sync($data['category_ids']);
        $this->syncFeaturedImageFromRequest($post, $request);

        return redirect()
            ->route('admin.posts.edit', $post->id)
            ->with('success', 'پست به‌روزرسانی شد.');
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        abort_unless($request->user()?->can('blog-posts-delete'), 403);

        $post->categories()->detach();
        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('success', 'پست حذف شد.');
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    private function categoryOptions(): array
    {
        return Category::query()
            ->blog()
            ->orderBy('sort')
            ->orderBy('title')
            ->get()
            ->map(fn (Category $category) => [
                'value' => $category->id,
                'label' => $category->title,
            ])
            ->values()
            ->all();
    }

    private function uniquePostSlug(string $slug, ?int $ignoreId = null): string
    {
        $slug = SeoSlug::normalize($slug);
        if ($slug === '') {
            $slug = 'post';
        }

        $baseSlug = $slug;
        $counter = 1;

        while (Post::query()
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        return $slug;
    }
}
