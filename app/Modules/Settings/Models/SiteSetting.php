<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'autoload',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'autoload' => 'boolean',
        ];
    }

    public function castValue(): mixed
    {
        return self::deserializeValue($this->value, $this->type);
    }

    public static function serializeValue(mixed $value, string $type): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($type) {
            'switch' => $value ? '1' : '0',
            'number' => (string) $value,
            default => is_string($value) ? $value : (string) $value,
        };
    }

    public static function deserializeValue(?string $value, string $type): mixed
    {
        if ($value === null || $value === '') {
            return match ($type) {
                'switch' => false,
                'number' => null,
                default => null,
            };
        }

        return match ($type) {
            'switch' => in_array($value, ['1', 'true', 'on'], true),
            'number' => is_numeric($value) ? $value + 0 : null,
            default => $value,
        };
    }
}
