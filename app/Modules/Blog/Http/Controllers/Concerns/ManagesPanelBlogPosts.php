<?php

namespace App\Modules\Blog\Http\Controllers\Concerns;

use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Http\Requests\Panel\StorePanelPostRequest;
use App\Modules\Blog\Http\Requests\Panel\UpdatePanelPostRequest;
use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Support\PanelBlogEnumOptions;
use App\Modules\Blog\Support\PostPresenter;
use App\Modules\Catalog\Models\Category;
use App\Modules\Files\Http\Concerns\SyncsMorphFilesFromRequest;
use App\Support\SeoSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

trait ManagesPanelBlogPosts
{
    use SyncsMorphFilesFromRequest;

    abstract protected function blogInertiaPrefix(): string;

    abstract protected function blogRoutePrefix(): string;

    abstract protected function blogPanelMeta(): array;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Post::class);

        $user = $request->user();
        abort_if($user === null, 403);

        $posts = Post::query()
            ->where('author_id', $user->id)
            ->with(['categories'])
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Post $post) => PostPresenter::forList($post));

        return Inertia::render($this->blogInertiaPrefix().'/Index', [
            'posts' => $posts,
            'panel' => $this->blogPanelMeta(),
            'abilities' => [
                'create' => $user->can('create', Post::class),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Post::class);

        return Inertia::render($this->blogInertiaPrefix().'/Create', [
            'categoryOptions' => $this->blogCategoryOptions(),
            'statusOptions' => PanelBlogEnumOptions::postStatusOptions(),
            'panel' => $this->blogPanelMeta(),
        ]);
    }

    public function store(StorePanelPostRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $slug = $this->uniquePanelPostSlug($data['slug'] ?? $data['title']);
        $status = PostStatus::from($data['status']);

        $post = Post::query()->create([
            'author_id' => $request->user()?->id,
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $this->normalizePlainContent((string) $data['content']),
            'status' => $status,
            'published_at' => null,
        ]);

        $post->categories()->sync($data['category_ids']);
        $this->syncFeaturedImageFromRequest($post, $request);

        return redirect()
            ->route($this->blogRoutePrefix().'.edit', $post)
            ->with('success', $status === PostStatus::PendingReview
                ? 'پست برای تایید ادمین ارسال شد.'
                : 'پیش‌نویس ذخیره شد.');
    }

    public function edit(Request $request, Post $post): Response
    {
        $this->authorize('update', $post);

        $form = PostPresenter::forForm($post);
        $form['content'] = $this->plainTextFromStoredContent((string) ($form['content'] ?? ''));
        $form['excerpt'] = $this->plainTextFromStoredContent((string) ($form['excerpt'] ?? ''));

        return Inertia::render($this->blogInertiaPrefix().'/Edit', [
            'post' => $form,
            'categoryOptions' => $this->blogCategoryOptions(),
            'statusOptions' => PanelBlogEnumOptions::postStatusOptions(),
            'panel' => $this->blogPanelMeta(),
        ]);
    }

    public function update(UpdatePanelPostRequest $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $data = $request->validated();
        $slug = $this->uniquePanelPostSlug(
            (string) ($data['slug'] ?? $post->slug),
            $post->id,
        );
        $status = PostStatus::from($data['status']);

        // Panel authors cannot keep/set published; editing a published post returns it to review/draft.
        if ($post->status === PostStatus::Published) {
            $status = $status === PostStatus::Draft
                ? PostStatus::Draft
                : PostStatus::PendingReview;
        }

        $post->update([
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $this->normalizePlainContent((string) $data['content']),
            'status' => $status,
            'published_at' => $status === PostStatus::Published ? $post->published_at : null,
        ]);

        $post->categories()->sync($data['category_ids']);
        $this->syncFeaturedImageFromRequest($post, $request);

        return redirect()
            ->route($this->blogRoutePrefix().'.edit', $post)
            ->with('success', 'پست به‌روزرسانی شد.');
    }

    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->categories()->detach();
        $post->delete();

        return redirect()
            ->route($this->blogRoutePrefix().'.index')
            ->with('success', 'پست حذف شد.');
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    private function blogCategoryOptions(): array
    {
        return Category::query()
            ->blog()
            ->where('status', true)
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

    private function uniquePanelPostSlug(string $slug, ?int $ignoreId = null): string
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

    /**
     * Panel authors use a plain textarea; convert to safe HTML for public SiteRichHtml.
     */
    private function normalizePlainContent(string $content): string
    {
        $trimmed = trim($content);
        if ($trimmed === '') {
            return '';
        }

        $escaped = e($trimmed);
        $blocks = preg_split("/\R{2,}/", $escaped) ?: [$escaped];

        return collect($blocks)
            ->map(fn (string $block) => '<p>'.nl2br(trim($block), false).'</p>')
            ->implode('');
    }

    private function plainTextFromStoredContent(string $html): string
    {
        $text = str_ireplace(['<br />', '<br/>', '<br>'], "\n", $html);
        $text = preg_replace('/<\/p>\s*<p>/i', "\n\n", $text) ?? $text;
        $text = strip_tags($text);

        return trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}

