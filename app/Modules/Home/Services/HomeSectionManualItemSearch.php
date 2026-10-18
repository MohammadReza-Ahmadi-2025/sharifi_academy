<?php

namespace App\Modules\Home\Services;

use App\Models\User;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Product;
use App\Modules\Courses\Models\Course;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HomeSectionManualItemSearch
{
    /**
     * @return Collection<int, object{id: int, title: string, slug: ?string}>
     */
    public function search(string $type, string $query, int $limit = 15): Collection
    {
        $q = trim($query);

        if ($q === '' || mb_strlen($q) < 3) {
            return collect();
        }

        return match ($type) {
            'product' => Product::query()
                ->active()
                ->select('id', 'title', 'slug')
                ->where(fn (Builder $builder) => $this->applyTitleSlugSearch($builder, $q))
                ->orderBy('title')
                ->limit($limit)
                ->get(),

            'course' => Course::query()
                ->select('id', 'title', 'slug')
                ->where(fn (Builder $builder) => $this->applyTitleSlugSearch($builder, $q))
                ->orderBy('title')
                ->limit($limit)
                ->get(),

            'post' => Post::query()
                ->published()
                ->select('id', 'title', 'slug')
                ->where(fn (Builder $builder) => $this->applyTitleSlugSearch($builder, $q))
                ->orderByDesc('published_at')
                ->limit($limit)
                ->get(),

            'brand' => Brand::query()
                ->where('status', true)
                ->select('id', 'title', 'slug')
                ->where(fn (Builder $builder) => $this->applyTitleSlugSearch($builder, $q))
                ->orderBy('title')
                ->limit($limit)
                ->get(),

            'instructor' => User::query()
                ->role('instructor')
                ->where('status', 1)
                ->select('id', 'name', 'family', 'mobile')
                ->where(function (Builder $inner) use ($q): void {
                    $like = '%'.$q.'%';
                    $inner->where('name', 'like', $like)
                        ->orWhere('family', 'like', $like)
                        ->orWhere('mobile', 'like', $like)
                        ->orWhereRaw("CONCAT(COALESCE(name, ''), ' ', COALESCE(family, '')) LIKE ?", [$like]);
                })
                ->orderBy('name')
                ->orderBy('family')
                ->limit($limit)
                ->get()
                ->map(function (User $user) {
                    $title = trim(($user->name ?? '').' '.($user->family ?? '')) ?: ('استاد #'.$user->id);

                    return (object) [
                        'id' => $user->id,
                        'title' => $title,
                        'slug' => null,
                    ];
                }),

            'category' => Category::query()
                ->active()
                ->whereIn('type', [Category::TYPE_PRODUCT, Category::TYPE_COURSE])
                ->select('id', 'title', 'slug', 'parent_id')
                ->where(fn (Builder $builder) => $this->applyTitleSlugSearch($builder, $q))
                ->orderBy('parent_id')
                ->orderBy('sort')
                ->orderBy('title')
                ->limit($limit)
                ->get()
                ->map(function (Category $category) {
                    return (object) [
                        'id' => $category->id,
                        'title' => $category->title,
                        'slug' => $category->slug,
                    ];
                }),

            default => collect(),
        };
    }

    private function applyTitleSlugSearch(Builder $builder, string $q): void
    {
        $like = '%'.$q.'%';
        $compact = $this->compactSearchTerm($q);
        $compactLike = $compact !== '' ? '%'.$compact.'%' : null;

        $builder->where(function (Builder $inner) use ($like, $compactLike): void {
            $inner->where('title', 'like', $like)
                ->orWhere('slug', 'like', $like);

            if ($compactLike !== null) {
                $inner->orWhereRaw(
                    "REPLACE(REPLACE(REPLACE(title, ' ', ''), '‌', ''), UNHEX('C2A0'), '') LIKE ?",
                    [$compactLike],
                );
            }
        });
    }

    private function compactSearchTerm(string $q): string
    {
        $normalized = preg_replace('/[\s\x{00A0}\x{200C}\x{200D}]+/u', '', $q) ?? $q;

        return trim($normalized);
    }
}
