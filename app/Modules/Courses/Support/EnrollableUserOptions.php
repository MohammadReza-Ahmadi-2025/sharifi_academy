<?php

namespace App\Modules\Courses\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class EnrollableUserOptions
{
    /**
     * @return list<array{value: int, label: string}>
     */
    public static function forSelect(int $limit = 500): array
    {
        return self::eligibleQuery()
            ->orderBy('name')
            ->orderBy('family')
            ->orderBy('id')
            ->limit($limit)
            ->get(['id', 'name', 'family', 'mobile'])
            ->map(fn (User $user) => [
                'value' => $user->id,
                'label' => self::label($user),
            ])
            ->values()
            ->all();
    }

    /**
     * @return Builder<User>
     */
    public static function eligibleQuery(): Builder
    {
        return User::query()->where('status', 1);
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
