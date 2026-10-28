<?php

namespace App\Modules\Courses\Support;

use App\Models\User;

class InstructorUserOptions
{
    /**
     * @return list<array{value: int, label: string}>
     */
    public static function forSelect(int $limit = 500): array
    {
        return User::query()
            ->role('instructor')
            ->where('status', 1)
            ->orderBy('name')
            ->orderBy('family')
            ->limit($limit)
            ->get(['id', 'name', 'family', 'mobile'])
            ->map(fn (User $user) => [
                'value' => $user->id,
                'label' => self::label($user),
            ])
            ->values()
            ->all();
    }

    private static function label(User $user): string
    {
        $name = trim("{$user->name} {$user->family}");

        if ($name === '') {
            return (string) $user->mobile;
        }

        return $name.' ('.$user->mobile.')';
    }
}
