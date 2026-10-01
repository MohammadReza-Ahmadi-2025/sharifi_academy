<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Roles\Support\DefaultRolePermissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DefaultRolePermissions::sync();

        $admin = User::query()->firstOrCreate(
            ['mobile' => '09120000001'],
            [
                'name' => 'مدیر',
                'family' => 'سیستم',
                'email' => 'admin@sharifi.test',
                'gender' => 'male',
                'status' => 1,
                'password' => 'password',
                'email_verified_at' => now(),
                'mobile_verified_at' => now(),
            ],
        );
        $admin->syncRoles(['super-admin']);

        $instructor = User::query()->firstOrCreate(
            ['mobile' => '09120000002'],
            [
                'name' => 'استاد',
                'family' => 'نمونه',
                'email' => 'instructor@sharifi.test',
                'gender' => 'male',
                'status' => 1,
                'password' => 'password',
                'email_verified_at' => now(),
                'mobile_verified_at' => now(),
            ],
        );
        $instructor->syncRoles(['instructor']);

        $student = User::query()->firstOrCreate(
            ['mobile' => '09120000003'],
            [
                'name' => 'دانشجو',
                'family' => 'نمونه',
                'email' => 'student@sharifi.test',
                'gender' => 'female',
                'status' => 1,
                'password' => 'password',
                'email_verified_at' => now(),
                'mobile_verified_at' => now(),
            ],
        );
        $student->syncRoles(['student']);

        $this->call(ProvinceCitySeeder::class);
    }
}
