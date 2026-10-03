<?php

namespace App\Modules\Menu\Enums;

enum MenuLocation: string
{
    case Header = 'header';
    case Footer = 'footer';
    case Mobile = 'mobile';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Header->value => 'هدر',
            self::Footer->value => 'فوتر',
            self::Mobile->value => 'موبایل',
        ];
    }

    public function label(): string
    {
        return self::options()[$this->value] ?? $this->value;
    }
}
