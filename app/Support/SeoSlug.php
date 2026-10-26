<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SeoSlug
{
    public static function fromOptional(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        return self::normalize($value);
    }

    public static function normalize(string $value): string
    {
        $value = trim(urldecode($value));
        if ($value === '') {
            return '';
        }

        $slug = Str::slug($value);

        return $slug !== '' ? $slug : Str::slug(Str::ascii($value));
    }

    public static function forUrl(string $storedSlug): string
    {
        return self::normalize($storedSlug);
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return TModel|null
     */
    public static function resolveModel(Builder $query, string $value, string $field = 'slug'): ?Model
    {
        $decoded = urldecode($value);
        $normalized = self::normalize($decoded);

        $record = (clone $query)->where($field, $decoded)->first();
        if ($record !== null) {
            return $record;
        }

        if ($normalized !== '' && $normalized !== $decoded) {
            $record = (clone $query)->where($field, $normalized)->first();
            if ($record !== null) {
                return $record;
            }
        }

        if ($normalized === '') {
            return null;
        }

        $column = $query->getGrammar()->wrap($field);
        $needle = strtolower($normalized);

        return (clone $query)
            ->whereRaw("LOWER(REPLACE({$column}, ' ', '-')) = ?", [$needle])
            ->first();
    }
}
