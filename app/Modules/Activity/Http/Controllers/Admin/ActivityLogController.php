<?php

namespace App\Modules\Activity\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\Activity\ActivityLogPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('activity-logs-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $module = trim((string) $request->query('module', ''));
        $action = trim((string) $request->query('action', ''));
        $userId = $request->query('user_id');
        $perPage = in_array((int) $request->query('per_page', 25), [25, 50, 100], true)
            ? (int) $request->query('per_page', 25)
            : 25;

        $query = ActivityLog::query()
            ->with('user')
            ->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('description', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%");
            });
        }

        if ($module !== '') {
            $query->where('module', $module);
        }

        if ($action !== '' && array_key_exists($action, ActivityLogPresenter::actionLabels())) {
            $query->where('action', $action);
        }

        if (is_numeric($userId)) {
            $query->where('user_id', (int) $userId);
        }

        $logs = $query->paginate($perPage)->withQueryString()
            ->through(fn (ActivityLog $log) => ActivityLogPresenter::forList($log));

        $actorOptions = User::query()
            ->whereHas('roles')
            ->orderBy('name')
            ->get(['id', 'name', 'mobile'])
            ->map(fn (User $user) => [
                'value' => (string) $user->id,
                'label' => trim($user->name.' — '.$user->mobile),
            ])
            ->all();

        return Inertia::render('Admin/ActivityLogs/Index', [
            'logs' => $logs,
            'filters' => [
                'search' => $search,
                'module' => $module,
                'action' => $action,
                'user_id' => is_numeric($userId) ? (string) $userId : '',
                'per_page' => $perPage,
            ],
            'moduleOptions' => collect(ActivityLogPresenter::moduleLabels())
                ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
                ->values()
                ->all(),
            'actionOptions' => collect(ActivityLogPresenter::actionLabels())
                ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
                ->values()
                ->all(),
            'userOptions' => $actorOptions,
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
