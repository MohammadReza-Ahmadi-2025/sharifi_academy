<?php

namespace App\Modules\Visits\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Visits\Models\Visit;
use App\Modules\Visits\Support\VisitPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VisitController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('visits-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $type = trim((string) $request->query('type', ''));
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = Visit::query()
            ->with(['visitable', 'visitor'])
            ->latest('id');

        if ($type !== '') {
            $query->where('visitable_type', 'like', "%\\{$type}");
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('visitor', fn ($q) => $q->where('name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%"));
            });
        }

        $visits = $query->paginate($perPage)->withQueryString()
            ->through(fn (Visit $visit) => VisitPresenter::forList($visit));

        $stats = [
            'total' => Visit::query()->count(),
            'today' => Visit::query()->whereDate('created_at', today())->count(),
            'week' => Visit::query()->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return Inertia::render('Admin/Visits/Index', [
            'visits' => $visits,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'type' => $type,
                'per_page' => $perPage,
            ],
            'typeOptions' => [
                ['value' => 'Product', 'label' => 'محصول'],
                ['value' => 'Post', 'label' => 'پست'],
                ['value' => 'Category', 'label' => 'دسته'],
                ['value' => 'Brand', 'label' => 'برند'],
                ['value' => 'Course', 'label' => 'دوره'],
            ],
        ]);
    }
}
