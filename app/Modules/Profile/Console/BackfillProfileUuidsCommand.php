<?php

namespace App\Modules\Profile\Console;

use App\Modules\Profile\Models\UserProfile;
use App\Modules\Profile\Support\InstructorPublicUuid;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BackfillProfileUuidsCommand extends Command
{
    protected $signature = 'profile:backfill-uuids
                            {--dry-run : فقط تعداد رکوردهای نیازمند UUID را نشان بده}';

    protected $description = 'یک‌بارمصرف: برای پروفایل‌هایی که uuid ندارند مقدار یکتا بساز';

    public function handle(): int
    {
        if (! Schema::hasColumn('user_profiles', 'uuid')) {
            $this->components->error('ستون uuid روی user_profiles نیست. اول migrate را اجرا کن.');

            return self::FAILURE;
        }

        $candidates = UserProfile::query()
            ->with('user')
            ->orderBy('id')
            ->get(['id', 'user_id', 'uuid'])
            ->filter(function (UserProfile $profile): bool {
                $uuid = is_string($profile->uuid) ? trim($profile->uuid) : '';

                return $uuid === '' || ! Str::isUuid($uuid);
            })
            ->values();

        $total = $candidates->count();

        if ($total === 0) {
            $this->components->info('همه پروفایل‌ها UUID دارند.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->components->info("{$total} پروفایل بدون UUID معتبر — هنوز چیزی ذخیره نشد.");

            return self::SUCCESS;
        }

        $filled = 0;

        foreach ($candidates as $profile) {
            $user = $profile->user;
            if ($user === null) {
                continue;
            }

            InstructorPublicUuid::ensureFor($user);
            $filled++;
        }

        $this->components->info("{$filled} پروفایل UUID گرفت.");

        return self::SUCCESS;
    }
}
