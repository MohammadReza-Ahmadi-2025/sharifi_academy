<?php

namespace App\Modules\Blog\Enums;

enum ReactionType: string
{
    case Like = 'like';
    case Celebrate = 'celebrate';
    case Support = 'support';
    case Love = 'love';
    case Insightful = 'insightful';
    case Funny = 'funny';
    case Dislike = 'dislike';

    public function label(): string
    {
        return match ($this) {
            self::Like => 'پسندیدم',
            self::Celebrate => 'تبریک',
            self::Support => 'حمایت',
            self::Love => 'عالی',
            self::Insightful => 'آموختنی',
            self::Funny => 'خنده‌دار',
            self::Dislike => 'نپسندیدم',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::Like => '👍',
            self::Celebrate => '🎉',
            self::Support => '💪',
            self::Love => '❤️',
            self::Insightful => '💡',
            self::Funny => '😄',
            self::Dislike => '👎',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return list<array{value: string, label: string, emoji: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case) => [
                'value' => $case->value,
                'label' => $case->label(),
                'emoji' => $case->emoji(),
            ],
            self::cases(),
        );
    }
}
