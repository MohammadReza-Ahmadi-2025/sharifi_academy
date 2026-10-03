<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Catalog\Models\Category;
use App\Modules\Courses\Enums\CourseClassDeliveryType;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseLevel;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use Illuminate\Database\Seeder;

class CourseDemoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('mobile', '09120000001')->value('id');

        $category = Category::query()->firstOrCreate(
            ['slug' => 'programming', 'type' => Category::TYPE_COURSE],
            [
                'title' => 'برنامه‌نویسی',
                'status' => 1,
                'sort' => 1,
            ],
        );

        $courses = [
            [
                'title' => 'لاراول از صفر تا صد',
                'slug' => 'laravel-zero-to-hero',
                'code' => 'LRN-101',
                'short_description' => 'آشنایی با فریم‌ورک لاراول و ساخت API و پنل مدیریت.',
                'level' => CourseLevel::Beginner,
                'status' => CourseStatus::Published,
                'is_featured' => true,
                'display_order' => 1,
            ],
            [
                'title' => 'ری‌اکت و Inertia',
                'slug' => 'react-inertia',
                'code' => 'FE-201',
                'short_description' => 'توسعه فرانت‌اند مدرن با React و Inertia.js.',
                'level' => CourseLevel::Intermediate,
                'status' => CourseStatus::Published,
                'is_featured' => false,
                'display_order' => 2,
            ],
            [
                'title' => 'معماری ماژولار در لاراول',
                'slug' => 'laravel-modular',
                'code' => 'ARC-301',
                'short_description' => 'ساختاردهی پروژه‌های بزرگ با ماژول‌های دامنه‌محور.',
                'level' => CourseLevel::Advanced,
                'status' => CourseStatus::Draft,
                'is_featured' => false,
                'display_order' => 3,
            ],
        ];

        foreach ($courses as $payload) {
            $course = Course::query()->updateOrCreate(
                ['slug' => $payload['slug']],
                [
                    ...$payload,
                    'created_by' => $adminId,
                    'published_at' => $payload['status'] === CourseStatus::Published ? now() : null,
                ],
            );

            $course->categories()->syncWithoutDetaching([$category->id]);

            CourseClass::query()->firstOrCreate(
                [
                    'course_id' => $course->id,
                    'code' => $payload['code'].'-G1',
                ],
                [
                    'name' => 'گروه پاییز ۱۴۰۵',
                    'status' => CourseClassStatus::Upcoming,
                    'delivery_type' => CourseClassDeliveryType::Online,
                    'capacity' => 30,
                    'price' => 3_500_000,
                    'is_public' => true,
                    'created_by' => $adminId,
                ],
            );
        }
    }
}
