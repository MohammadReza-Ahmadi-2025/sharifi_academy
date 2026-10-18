<?php

namespace App\Modules\Admin\Services;

use App\Models\User;
use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Blog\Models\Post;
use App\Modules\Catalog\Models\Product;
use App\Modules\Comments\Enums\CommentStatus;
use App\Modules\Comments\Models\Comment;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Pages\Models\Page;
use App\Modules\Stories\Models\Story;
use App\Modules\Visits\Models\Visit;
use Illuminate\Support\Carbon;

class DashboardStatsService
{
    /**
     * @return list<array{id: string, title: string, stats: list<array{id: string, label: string, value: int, description: string, href: string|null}>}>
     */
    public function statGroups(?User $actor = null): array
    {
        $visitsWeek = Visit::query()
            ->where('created_at', '>=', Carbon::now()->subDays(7)->startOfDay())
            ->count();

        $visitsToday = Visit::query()
            ->whereDate('created_at', Carbon::today())
            ->count();

        $groups = [
            [
                'id' => 'people',
                'title' => 'کاربران',
                'stats' => [
                    $this->stat('users', 'کاربران', User::query()->count(), 'همه حساب‌های ثبت‌شده', '/admin/users'),
                    $this->stat('students', 'دانشجوها', User::role('student')->count(), 'نقش student', '/admin/users'),
                    $this->stat('instructors', 'اساتید', User::role('instructor')->count(), 'نقش instructor', '/admin/users'),
                    $this->stat('admins', 'ادمین‌ها', User::role(['super-admin', 'admin'])->count(), 'super-admin و admin', '/admin/users'),
                ],
            ],
            [
                'id' => 'academy',
                'title' => 'آموزش',
                'stats' => [
                    $this->stat('courses', 'دوره‌های منتشرشده', Course::query()->published()->count(), 'دوره فعال در سایت', '/admin/courses'),
                    $this->stat('classes', 'کلاس‌ها', CourseClass::query()->count(), 'گروه‌های برگزاری دوره', '/admin/courses'),
                    $this->stat('enrollments', 'ثبت‌نام تأیید‌شده', Enrollment::query()->confirmed()->count(), 'وضعیت confirmed', '/admin/course-enrollments'),
                ],
            ],
            [
                'id' => 'content',
                'title' => 'محتوا و تعامل',
                'stats' => [
                    $this->stat('posts', 'پست وبلاگ', Post::query()->where('status', PostStatus::Published)->count(), 'منتشر شده', '/admin/posts'),
                    $this->stat('pages', 'صفحات فعال', Page::query()->active()->count(), 'صفحات ثابت سایت', '/admin/pages'),
                    $this->stat('stories', 'استوری‌ها', Story::query()->count(), 'همه وضعیت‌ها', '/admin/stories'),
                    $this->stat('products', 'محصولات', Product::query()->count(), 'کاتالوگ فروشگاه', '/admin/catalog/products'),
                    $this->stat('comments_pending', 'نظر در انتظار', Comment::query()->where('status', CommentStatus::Pending)->count(), 'نیاز به بررسی', '/admin/comments'),
                ],
            ],
            [
                'id' => 'traffic',
                'title' => 'بازدید',
                'stats' => [
                    $this->stat('visits_today', 'بازدید امروز', $visitsToday, 'ثبت در سیستم ردیابی', '/admin/visits'),
                    $this->stat('visits_week', 'بازدید ۷ روز اخیر', $visitsWeek, 'جمع بازدیدها', '/admin/visits'),
                ],
            ],
        ];

        if ($actor?->can('instructor-moderation-index')) {
            $queue = InstructorModerationQueue::counts();
            $groups[] = [
                'id' => 'moderation',
                'title' => 'تایید اساتید',
                'stats' => [
                    $this->stat(
                        'courses_pending_review',
                        'دوره در انتظار',
                        $queue['courses_pending'],
                        'درخواست انتشار از پنل استاد',
                        '/admin/courses?status=pending_review',
                    ),
                    $this->stat(
                        'resumes_pending',
                        'رزومه در انتظار',
                        $queue['resumes_pending'],
                        'ارسال‌شده برای بررسی',
                        '/admin/instructor-moderation',
                    ),
                ],
            ];
        }

        return $groups;
    }

    /**
     * @return array{id: string, label: string, value: int, description: string, href: string|null}
     */
    private function stat(string $id, string $label, int $value, string $description, ?string $href): array
    {
        return [
            'id' => $id,
            'label' => $label,
            'value' => $value,
            'description' => $description,
            'href' => $href,
        ];
    }

    /**
     * @return array<int, array{day: string, count: int}>
     */
    public function visitsDaily(int $days = 7): array
    {
        $points = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);

            $points[] = [
                'day' => $day->toDateString(),
                'count' => Visit::query()->whereDate('created_at', $day)->count(),
            ];
        }

        return $points;
    }

    /**
     * @return array<int, array{month: string, count: int}>
     */
    public function usersMonthlyRegistrations(int $months = 6): array
    {
        $points = [];

        for ($i = $months - 1; $i >= 0; $i--) {
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

    /**
     * @return list<array{name: string, value: int, color: string}>
     */
    public function rolesBreakdown(): array
    {
        return [
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
        ];
    }
}
