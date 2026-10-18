<?php

namespace App\Modules\Profile\Support;

use App\Models\User;
use App\Modules\Profile\Models\Resume;
use App\Modules\Users\Support\UserAvatarMedia;

final class InstructorPublicResumePresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forUser(User $user): array
    {
        $fullName = trim(($user->name ?? '').' '.($user->family ?? '')) ?: 'استاد';

        $grouped = [];
        foreach ($user->resumes as $resume) {
            if (! $resume instanceof Resume) {
                continue;
            }

            $type = (string) $resume->type;
            $grouped[$type] ??= [];
            $grouped[$type][] = ResumeEntryPresenter::forPublic($resume);
        }

        $sections = [];
        foreach (ResumeTypeOptions::forSelect() as $option) {
            $type = $option['value'];
            $items = $grouped[$type] ?? [];
            if ($items === []) {
                continue;
            }

            $sections[] = [
                'type' => $type,
                'label' => $option['label'],
                'items' => $items,
            ];
        }

        $skills = [];
        if ($user->relationLoaded('skills')) {
            $skills = $user->skills
                ->map(fn ($skill) => [
                    'id' => $skill->id,
                    'name' => $skill->name,
                    'level' => $skill->pivot?->level,
                ])
                ->values()
                ->all();
        }

        return [
            'id' => $user->id,
            'fullName' => $fullName,
            'headline' => $user->profile?->headline,
            'bio' => $user->profile?->bio,
            'avatar' => UserAvatarMedia::payload($user),
            'profile_url' => route('instructors.show', $user),
            'sections' => $sections,
            'skills' => $skills,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forApi(User $user): array
    {
        return self::forUser($user);
    }
}
