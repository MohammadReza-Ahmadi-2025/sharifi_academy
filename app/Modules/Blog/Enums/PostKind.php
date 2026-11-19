<?php

namespace App\Modules\Blog\Enums;

enum PostKind: string
{
    /** Long-form site articles (/blog) */
    case Article = 'article';

    /** User community posts (/posts feed) */
    case Community = 'community';

    public function label(): string
    {
        return match ($this) {
            self::Article => 'مقاله وبلاگ',
            self::Community => 'پست کاربری',
        };
    }
}
