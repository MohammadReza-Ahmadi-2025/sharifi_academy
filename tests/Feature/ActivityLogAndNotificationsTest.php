<?php

namespace Tests\Feature;

use App\Modules\Roles\Models\Permission;
use App\Modules\Roles\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ActivityLogAndNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('notifications'));
        $this->assertTrue(Schema::hasColumns('notifications', [
            'id',
            'type',
            'notifiable_type',
            'notifiable_id',
            'data',
            'read_at',
        ]));
    }

    public function test_activity_logs_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('activity_logs'));
        $this->assertTrue(Schema::hasColumns('activity_logs', [
            'user_id',
            'module',
            'action',
            'subject_type',
            'subject_id',
            'description',
        ]));
    }

    public function test_activity_log_permissions_are_granted_to_admin_roles(): void
    {
        $this->assertTrue(Permission::query()->where('name', 'activity-logs-index')->exists());
        $this->assertTrue(Permission::query()->where('name', 'activity-logs-show')->exists());

        $superAdmin = Role::query()->where('name', 'super-admin')->firstOrFail();
        $admin = Role::query()->where('name', 'admin')->firstOrFail();

        $this->assertTrue($superAdmin->hasPermissionTo('activity-logs-index'));
        $this->assertTrue($admin->hasPermissionTo('activity-logs-show'));
    }

    public function test_user_can_receive_database_notification(): void
    {
        $user = User::factory()->create();

        $user->notify(new class extends Notification
        {
            public function via(object $notifiable): array
            {
                return ['database'];
            }

            /** @return array<string, string> */
            public function toDatabase(object $notifiable): array
            {
                return ['message' => 'تست'];
            }
        });

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
        ]);
    }
}
