<?php

namespace App\Modules\Social\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'type',
    'value',
    'label',
    'target_blank',
    'sort',
    'is_active',
])]
class Social extends Model
{
    protected function casts(): array
    {
        return [
            'target_blank' => 'boolean',
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function getUrlAttribute(): string
    {
        return match ($this->type) {
            'phone' => 'tel:'.$this->value,
            'email' => 'mailto:'.$this->value,
            default => $this->value,
        };
    }
}
