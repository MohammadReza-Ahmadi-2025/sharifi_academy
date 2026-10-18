<?php

namespace App\Modules\Blog\Services;

use App\Modules\Blog\Models\Post;
use App\Modules\Blog\Support\PostPublicPresenter;
use App\Modules\Catalog\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class BlogCategoryBrowseService
{
    /**
     * @return array{sort: string}
     */
    public function filtersFromRequest(Request $request): array
    {
        $sort = (string) $request->query('sort', 'latest');

        if (! in_array($sort, ['latest', 'oldest'], true)) {
            $sort = 'latest';
        }

        return ['sort' => $sort];
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public function sortOptions(): array
    {
        return [
            ['value' => 'latest', 'label' => 'جدیدترین'],
            ['value' => 'oldest', 'label' => 'قدیمی‌ترین'],
        ];
    }

    /**
     * @return LengthAwarePaginator<int, Post>
     */
    public function paginatePostsInCategory(Category $category, array $filters): LengthAwarePaginator
    {
        $query = $category->posts()
            ->published()
            ->with(['featuredImage', 'categories']);

        if ($filters['sort'] === 'oldest') {
            $query->orderBy('published_at');
        } else {
            $query->orderByDesc('published_at');
        }

        return $query->paginate(12)->withQueryString();
    }

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function paginateCardsInCategory(Category $category, array $filters): LengthAwarePaginator
    {
        return PostPublicPresenter::paginatedCards(
            $this->paginatePostsInCategory($category, $filters),
        );
    }

    /**
     * @return list<array{id: int, title: string, slug: string, items_count: int}>
     */
    public function subcategories(Category $category): array
    {
        $category->load([
            'image',
            'activeChildren' => function ($query) {
                $query->where('type', Category::TYPE_BLOG)->withCount([
                    'posts as items_count' => fn ($postQuery) => $postQuery->published(),
                ]);
            },
        ]);

        return $category->activeChildren
            ->filter(fn (Category $child) => (int) $child->items_count > 0)
            ->map(fn (Category $child) => [
                'id' => $child->id,
                'title' => $child->title,
                'slug' => $child->publicSlug(),
                'items_count' => (int) $child->items_count,
            ])
            ->values()
            ->all();
    }
}
