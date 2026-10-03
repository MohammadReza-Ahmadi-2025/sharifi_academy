<?php

namespace App\Modules\Profile\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Models\Category;
use App\Modules\Profile\Http\Requests\Admin\StoreSkillRequest;
use App\Modules\Profile\Http\Requests\Admin\UpdateSkillRequest;
use App\Modules\Profile\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SkillController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('skills-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = Skill::query()->with('category')->orderBy('name');

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $skills = $query->paginate($perPage)->withQueryString()
            ->through(fn (Skill $skill) => [
                'id' => $skill->id,
                'name' => $skill->name,
                'category' => $skill->category?->title,
            ]);

        $actor = $request->user();

        return Inertia::render('Admin/Skills/Index', [
            'skills' => $skills,
            'filters' => ['search' => $search, 'per_page' => $perPage],
            'abilities' => [
                'create' => $actor?->can('skills-create') ?? false,
                'update' => $actor?->can('skills-update') ?? false,
                'delete' => $actor?->can('skills-delete') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('skills-create'), 403);

        return Inertia::render('Admin/Skills/Create', [
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function store(StoreSkillRequest $request): RedirectResponse
    {
        Skill::query()->create([
            'name' => $request->validated('name'),
            'category_id' => $request->validated('category_id'),
        ]);

        return redirect()->route('admin.skills.index')->with('success', 'مهارت ایجاد شد.');
    }

    public function edit(Request $request, Skill $skill): Response
    {
        abort_unless($request->user()?->can('skills-update'), 403);

        return Inertia::render('Admin/Skills/Edit', [
            'skill' => [
                'id' => $skill->id,
                'name' => $skill->name,
                'category_id' => $skill->category_id,
            ],
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function update(UpdateSkillRequest $request, Skill $skill): RedirectResponse
    {
        $skill->update([
            'name' => $request->validated('name'),
            'category_id' => $request->validated('category_id'),
        ]);

        return redirect()->route('admin.skills.edit', $skill)->with('success', 'مهارت به‌روزرسانی شد.');
    }

    public function destroy(Request $request, Skill $skill): RedirectResponse
    {
        abort_unless($request->user()?->can('skills-delete'), 403);

        if ($skill->users()->exists()) {
            return back()->withErrors(['skill' => 'این مهارت به کاربران متصل است.']);
        }

        $skill->delete();

        return redirect()->route('admin.skills.index')->with('success', 'مهارت حذف شد.');
    }

    /**
     * @return list<array{value: int, label: string}>
     */
    private function categoryOptions(): array
    {
        return Category::query()
            ->where('type', Category::TYPE_COURSE)
            ->active()
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (Category $category) => ['value' => $category->id, 'label' => $category->title])
            ->all();
    }
}
