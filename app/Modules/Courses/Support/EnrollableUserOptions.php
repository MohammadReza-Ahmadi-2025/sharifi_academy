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
            ->limit($limit)
            ->get(['id', 'name', 'family', 'mobile'])
            ->map(fn (User $user) => [
                'value' => $user->id,
                'label' => trim("{$user->name} {$user->family}")." ({$user->mobile})",
            ])
            ->values()
            ->all();
    }

    /**
     * @return Builder<User>
     */
    public static function eligibleQuery(): Builder
    {
        return User::query()
            ->where('status', 1)
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->whereNotNull('family')
            ->where('family', '!=', '')
            ->whereHas('profile', fn (Builder $query) => $query->whereNotNull('province_city_id'));
    }
}
