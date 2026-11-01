<?php

namespace Tests\Feature\Resilience;

use App\Modules\Catalog\Models\Category;
use App\Support\Activity\ActivityLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ActivityLogSoftFailTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_save_succeeds_when_activity_logger_fails(): void
    {
        $this->mock(ActivityLogger::class, function ($mock): void {
            $mock->shouldReceive('snapshotFromOriginal')->andThrow(new RuntimeException('activity log down'));
            $mock->shouldReceive('log')->andThrow(new RuntimeException('activity log down'));
            $mock->shouldReceive('snapshot')->andThrow(new RuntimeException('activity log down'));
        });

        $category = Category::withoutSyncingToSearch(fn () => Category::query()->create([
            'type' => Category::TYPE_COURSE,
            'title' => 'دسته تست لاگ',
            'slug' => 'activity-soft-fail',
            'status' => true,
            'show_in_menu' => true,
        ]));

        $category->title = 'دسته تست لاگ به‌روز';
        $category->save();

        $this->assertSame('دسته تست لاگ به‌روز', $category->fresh()->title);
    }
}
