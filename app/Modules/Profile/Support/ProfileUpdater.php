<?php

namespace App\Modules\Profile\Support;

use App\Models\User;
use App\Modules\Profile\Enums\ResumeModerationStatus;
use App\Modules\Users\Support\UserAvatarMedia;
use Illuminate\Http\Request;

class ProfileUpdater
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public static function updateUserAndProfile(User $user, array $validated, Request $request): void
    {
        if ($validated['mobile'] !== $user->mobile) {
            $validated['mobile_verified_at'] = null;
        }

        if (($validated['email'] ?? null) !== $user->email) {
            $validated['email_verified_at'] = null;
        }

        $userFields = collect($validated)->except([
            ...UserProfileSync::attributeKeys(),
            'avatar',
            'avatar_alt',
            'avatar_title',
            'remove_avatar',
        ])->all();

        $user->fill($userFields);
        $user->save();

        UserProfileSync::sync($user, $validated);
        UserAvatarMedia::syncFromRequest($user, $request);

        // Public resume fields changed — send approved profiles back to review.
        if (ResumeModeration::status($user->fresh()) === ResumeModerationStatus::Approved) {
            ResumeModeration::markPending($user);
        }
    }
}
