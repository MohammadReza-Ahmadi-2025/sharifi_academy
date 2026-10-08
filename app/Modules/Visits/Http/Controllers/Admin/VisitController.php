<?php

namespace App\Modules\Visits\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Visits\Models\Visit;
use App\Modules\Visits\Services\VisitReportService;
use App\Modules\Visits\Support\VisitPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VisitController extends Controller
{
    public function __construct(
        private VisitReportService $report,
    ) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('visits-index'), 403);

        $filters = $this->report->resolveFilters($request);

        $summary = $this->report->summary($filters);
        $byType = $this->report->countsByType($filters);
        $topRows = $this->report->topVisited($filters, 25);
        $resolved = $this->report->resolveVisitables($topRows);
        $dailyTrend = $this->report->dailyTrend($filters, 14);

        $topVisited = $topRows->map(function ($row) use ($resolved) {
            $model = $resolved[$row->visitable_type][$row->visitable_id] ?? null;

            return [
                'visitable_type_label' => $this->report->typeLabelForClass($row->visitable_type),
                'visitable_id' => (int) $row->visitable_id,
                'visits_count' => (int) $row->visits_count,
                'title' => $this->report->titleFor($model, $row->visitable_type, (int) $row->visitable_id),
                'admin_url' => $this->report->adminUrlFor($row->visitable_type, (int) $row->visitable_id, $model),
            ];
        })->values();

        $recentVisits = $this->report->filteredQuery($filters)
            ->with([
                'visitor:id,name,family,mobile',
                'visitable',
            ])
            ->latest('id')
            ->paginate($filters['per_page'])
            ->withQueryString()
            ->through(fn (Visit $visit) => VisitPresenter::forRecent($visit));

        return Inertia::render('Admin/Visits/Index', [
            'filters' => $filters,
            'summary' => $summary,
            'byType' => collect($byType)
                ->map(fn (int $count, string $label) => ['label' => $label, 'count' => $count])
                ->values(),
            'topVisited' => $topVisited,
            'dailyTrend' => $dailyTrend->map(fn ($row) => [
                'day' => (string) $row->day,
                'total' => (int) $row->total,
            ])->values(),
            'recentVisits' => $recentVisits,
            'typeOptions' => [
                ['value' => '', 'label' => 'همه انواع'],
                ['value' => 'product', 'label' => 'محصولات'],
                ['value' => 'post', 'label' => 'مقالات'],
                ['value' => 'category', 'label' => 'دسته‌بندی‌ها'],
                ['value' => 'brand', 'label' => 'برندها'],
                ['value' => 'course', 'label' => 'دوره‌ها'],
            ],
        ]);
    }
}
