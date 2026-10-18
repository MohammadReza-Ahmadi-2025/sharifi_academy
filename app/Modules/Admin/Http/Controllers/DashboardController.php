<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Services\DashboardStatsService;
use App\Modules\Admin\Services\InstructorModerationQueue;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardStatsService $stats): Response
    {
        $moderation = null;
        if ($request->user()?->can('instructor-moderation-index')) {
            $moderation = InstructorModerationQueue::counts();
        }

        return Inertia::render('Admin/Dashboard', [
            'statGroups' => $stats->statGroups($request->user()),
            'charts' => [
                'usersMonthly' => $stats->usersMonthlyRegistrations(),
                'rolesBreakdown' => $stats->rolesBreakdown(),
                'visitsDaily' => $stats->visitsDaily(),
            ],
            'moderation' => $moderation,
        ]);
    }
}
