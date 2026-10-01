<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                [
                    'id' => 'users',
                    'label' => 'کاربران',
                    'value' => User::query()->count(),
                ],
                [
                    'id' => 'students',
                    'label' => 'دانشجوها',
                    'value' => User::role('student')->count(),
                ],
                [
                    'id' => 'instructors',
                    'label' => 'اساتید',
                    'value' => User::role('instructor')->count(),
                ],
                [
                    'id' => 'admins',
                    'label' => 'ادمین‌ها',
                    'value' => User::role(['super-admin', 'admin'])->count(),
                ],
            ],
            'charts' => [
                'usersMonthly' => $this->usersMonthlyRegistrations(),
                'rolesBreakdown' => [
                    [
                        'name' => 'دانشجو',
                        'value' => User::role('student')->count(),
                        'color' => '#3b82f6',
                    ],
                    [
                        'name' => 'استاد',
                        'value' => User::role('instructor')->count(),
                        'color' => '#10b981',
                    ],
                    [
                        'name' => 'ادمین',
                        'value' => User::role(['super-admin', 'admin'])->count(),
                        'color' => '#00489e',
                    ],
                ],
            ],
        ]);
    }

    /**
     * @return array<int, array{month: string, count: int}>
     */
    private function usersMonthlyRegistrations(): array
    {
        $points = [];

        for ($i = 5; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $points[] = [
                'month' => $start->toDateString(),
                'count' => User::query()
                    ->whereBetween('created_at', [$start, $end])
                    ->count(),
            ];
        }

        return $points;
    }
}
