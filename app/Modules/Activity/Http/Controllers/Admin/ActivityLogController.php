<?php

namespace App\Modules\Activity\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Support\Activity\ActivityLogIndexFilters;
use App\Support\Activity\ActivityLogPresenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function __construct(
        private readonly ActivityLogIndexFilters $filters,
    ) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('activity-logs-index'), 403);

        $resolved = $this->filters->resolve($request);

        $logs = ActivityLog::query()
            ->with('user:id,name,family,mobile')
            ->tap(fn (Builder $query) => $this->filters->apply($query, $resolved))
            ->orderByDesc('id')
            ->paginate($resolved['per_page'])
            ->withQueryString()
            ->through(fn (ActivityLog $log) => ActivityLogPresenter::forList($log));

        $moduleOptionsMap = $this->filters->moduleOptionsFromDatabase();
        $staffUsers = $this->filters->staffUsersForFilter();
        $actionStats = $this->filters->actionStats($resolved);

        return Inertia::render('Admin/ActivityLogs/Index', [
            'logs' => $logs,
            'filters' => [
                'search' => $resolved['search'],
                'module' => $resolved['module'] ?? '',
                'action' => $resolved['action'] ?? '',
                'user_id' => $resolved['user_id'] ? (string) $resolved['user_id'] : '',
                'period' => $resolved['period'],
                'from' => $resolved['from'] ?? '',
                'to' => $resolved['to'] ?? '',
                'per_page' => $resolved['per_page'],
            ],
            'moduleOptions' => collect($moduleOptionsMap)
                ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
                ->values()
                ->all(),
            'actionOptions' => collect(ActivityLogPresenter::actionLabels())
                ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
                ->values()
                ->all(),
            'actionStats' => $actionStats,
            'userOptions' => $staffUsers
                ->map(fn ($user) => [
                    'value' => (string) $user->id,
                    'label' => trim($user->name.' '.$user->family).' — '.$user->mobile,
                ])
                ->all(),
            'hasActiveFilters' => $this->filters->hasActiveFilters($resolved),
            'activeFilterLabels' => $this->filters->activeFilterLabels($resolved, $staffUsers, $moduleOptionsMap),
            'abilities' => [
                'show' => $request->user()?->can('activity-logs-show') ?? false,
            ],
        ]);
    }

    public function show(Request $request, ActivityLog $activityLog): Response
    {
        abort_unless($request->user()?->can('activity-logs-show'), 403);

        $activityLog->load('user');

        return Inertia::render('Admin/ActivityLogs/Show', [
            'log' => ActivityLogPresenter::forShow($activityLog),
        ]);
    }
}
