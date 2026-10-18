<?php

namespace App\Modules\Profile\Support;

use App\Models\User;
use App\Modules\Profile\Enums\ResumeModerationStatus;
use App\Modules\Profile\Models\UserProfile;

final class ResumeModeration
{
    public static function ensureProfile(User $user): UserProfile
    {
        return $user->profile()->firstOrCreate(
            ['user_id' => $user->id],
            ['resume_moderation_status' => ResumeModerationStatus::Draft->value],
        );
    }

    public static function status(User $user): ResumeModerationStatus
    {
        $raw = $user->profile?->resume_moderation_status;

        if ($raw instanceof ResumeModerationStatus) {
            return $raw;
        }

        if (is_string($raw) && ResumeModerationStatus::tryFrom($raw) !== null) {
            return ResumeModerationStatus::from($raw);
        }

        return ResumeModerationStatus::Draft;
    }

    public static function isPubliclyVisible(User $user): bool
    {
        return self::status($user) === ResumeModerationStatus::Approved;
    }

    public static function markPending(User $user): void
    {
        $profile = self::ensureProfile($user);
        $profile->update([
            'resume_moderation_status' => ResumeModerationStatus::Pending->value,
            'resume_submitted_at' => now(),
            'resume_reviewed_at' => null,
            'resume_moderation_note' => null,
        ]);
    }

    public static function markApproved(User $user, ?User $reviewer = null): void
    {
        $profile = self::ensureProfile($user);
        $profile->update([
            'resume_moderation_status' => ResumeModerationStatus::Approved->value,
            'resume_reviewed_at' => now(),
            'resume_reviewed_by' => $reviewer?->id,
            'resume_moderation_note' => null,
        ]);
    }

    public static function markRejected(User $user, ?string $note, ?User $reviewer = null): void
    {
        $profile = self::ensureProfile($user);
        $profile->update([
            'resume_moderation_status' => ResumeModerationStatus::Rejected->value,
            'resume_reviewed_at' => now(),
            'resume_reviewed_by' => $reviewer?->id,
            'resume_moderation_note' => filled($note) ? trim($note) : null,
        ]);
    }
}
