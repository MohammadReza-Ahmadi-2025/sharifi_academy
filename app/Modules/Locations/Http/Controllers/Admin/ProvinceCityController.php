<?php

namespace App\Modules\Locations\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Locations\Http\Requests\Admin\UpdateProvinceCityRequest;
use App\Modules\Locations\Models\ProvinceCity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProvinceCityController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('locations-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = ProvinceCity::query()
            ->whereNull('parent_id')
            ->withCount('children')
            ->orderBy('sort')
            ->orderBy('title');

        if ($search !== '') {
            $query->where('title', 'like', "%{$search}%");
        }

        $provinces = $query->paginate($perPage)->withQueryString()
            ->through(fn (ProvinceCity $province) => [
                'id' => $province->id,
                'title' => $province->title,
                'cities_count' => $province->children_count,
            ]);

        return Inertia::render('Admin/Locations/Index', [
            'provinces' => $provinces,
            'filters' => ['search' => $search, 'per_page' => $perPage],
            'abilities' => [
                'update' => $request->user()?->can('locations-update') ?? false,
            ],
        ]);
    }

    public function edit(Request $request, ProvinceCity $province): Response
    {
        abort_unless($request->user()?->can('locations-update'), 403);
        abort_unless($province->isProvince(), 404);

        $cities = $province->children()
            ->orderBy('sort')
            ->orderBy('title')
            ->get(['id', 'title', 'sort'])
            ->map(fn (ProvinceCity $city) => [
                'id' => $city->id,
                'title' => $city->title,
                'sort' => $city->sort,
            ]);

        return Inertia::render('Admin/Locations/Edit', [
            'province' => [
                'id' => $province->id,
                'title' => $province->title,
            ],
            'cities' => $cities,
        ]);
    }

    public function update(UpdateProvinceCityRequest $request, ProvinceCity $province): RedirectResponse
    {
        abort_unless($province->isProvince(), 404);

        foreach ($request->validated('cities') as $row) {
            ProvinceCity::query()
                ->where('parent_id', $province->id)
                ->whereKey($row['id'])
                ->update([
                    'title' => $row['title'],
                    'sort' => (int) $row['sort'],
                ]);
        }

        return back()->with('success', 'شهرها به‌روزرسانی شدند.');
    }
}
