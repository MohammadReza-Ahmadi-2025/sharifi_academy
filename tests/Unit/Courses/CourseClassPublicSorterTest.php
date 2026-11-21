<?php

namespace Tests\Unit\Courses;

use App\Modules\Courses\Enums\CourseClassDeliveryType;
use App\Modules\Courses\Enums\CourseClassOnlinePlatform;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Enums\CourseStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Support\CourseClassPublicSorter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseClassPublicSorterTest extends TestCase
{
    use RefreshDatabase;

    public function test_open_cheaper_classes_rank_above_closed_and_finished(): void
    {
        $course = Course::query()->create([
            'title' => 'Sort Course',
            'slug' => 'sort-course',
            'status' => CourseStatus::Published,
        ]);

        $finished = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'تمام‌شده',
            'code' => 'FIN-1',
            'status' => CourseClassStatus::Completed,
            'delivery_type' => CourseClassDeliveryType::Online,
            'online_platform' => CourseClassOnlinePlatform::AdobeConnect,
            'price' => 1_000_000,
            'is_public' => true,
            'starts_at' => now()->subMonths(3),
            'ends_at' => now()->subMonth(),
            'registration_ends_at' => now()->subMonths(2),
        ]);

        $closedExpensive = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'ثبت‌نام بسته',
            'code' => 'CLOSED-1',
            'status' => CourseClassStatus::Upcoming,
            'delivery_type' => CourseClassDeliveryType::Online,
            'online_platform' => CourseClassOnlinePlatform::AdobeConnect,
            'price' => 500_000,
            'is_public' => true,
            'starts_at' => now()->addMonth(),
            'ends_at' => now()->addMonths(3),
            'registration_ends_at' => now()->subDay(),
        ]);

        $openExpensive = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'باز گران',
            'code' => 'OPEN-HI',
            'status' => CourseClassStatus::Upcoming,
            'delivery_type' => CourseClassDeliveryType::Online,
            'online_platform' => CourseClassOnlinePlatform::AdobeConnect,
            'price' => 3_000_000,
            'discount_percent' => 0,
            'is_public' => true,
            'starts_at' => now()->addWeeks(2),
            'ends_at' => now()->addMonths(2),
        ]);

        $openCheap = CourseClass::query()->create([
            'course_id' => $course->id,
            'name' => 'باز ارزان',
            'code' => 'OPEN-LO',
            'status' => CourseClassStatus::Upcoming,
            'delivery_type' => CourseClassDeliveryType::Online,
            'online_platform' => CourseClassOnlinePlatform::AdobeConnect,
            'price' => 2_000_000,
            'discount_percent' => 50,
            'is_public' => true,
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addMonths(2),
        ]);

        $sorted = CourseClassPublicSorter::sort(
            collect([$finished, $closedExpensive, $openExpensive, $openCheap]),
        );

        $this->assertSame(
            [$openCheap->id, $openExpensive->id, $closedExpensive->id, $finished->id],
            $sorted->pluck('id')->all(),
        );
    }
}
