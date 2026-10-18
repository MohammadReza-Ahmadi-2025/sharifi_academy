<?php

namespace App\Support\Concerns;

use App\Support\SeoSlug;
use Illuminate\Database\Eloquent\Model;

trait ResolvesSeoSlugRouteBinding
{
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $field = $field ?: $this->getRouteKeyName();

        if ($field === 'slug') {
            return SeoSlug::resolveModel(static::query(), (string) $value, $field);
        }

        return static::query()->where($field, $value)->first();
    }
}
