<?php

namespace App\Modules\Visits\Support;

use App\Modules\Visits\Models\Visit;

class VisitPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forList(Visit $visit): array
    {
        return self::forRecent($visit);
    }

    /**
     * @return array<string, mixed>
     */
    public static function forRecent(Visit $visit): array
    {
        $visitable = $visit->visitable;

        return [
            'id' => $visit->id,
            'visitable_type' => class_basename($visit->visitable_type),
            'visitable_type_label' => self::typeLabel($visit->visitable_type),
            'visitable_id' => $visit->visitable_id,
            'visitable_title' => self::visitableTitle($visitable, $visit->visitable_id),
            'ip_address' => $visit->ip_address,
            'visitor_id' => $visit->visitor_id,
            'visitor_label' => self::visitorLabel($visit),
            'created_at' => $visit->created_at?->toIso8601String(),
        ];
    }

    protected static function visitableTitle(?object $visitable, int $visitableId): string
    {
        if (! $visitable) {
            return '#'.$visitableId;
        }

        if (isset($visitable->title)) {
            return (string) $visitable->title;
        }

        if (isset($visitable->name)) {
            return (string) $visitable->name;
        }

        return '#'.$visitableId;
    }

    protected static function visitorLabel(Visit $visit): string
    {
        $visitor = $visit->visitor;

        if (! $visitor) {
            return 'مهمان';
        }

        $name = trim(((string) ($visitor->name ?? '')).' '.((string) ($visitor->family ?? '')));

        if ($name !== '') {
            return $name;
        }

        return (string) ($visitor->mobile ?? 'مهمان');
    }

    public static function typeLabel(string $class): string
    {
        return match (class_basename($class)) {
            'Product' => 'محصول',
            'Post' => 'پست وبلاگ',
            'Category' => 'دسته‌بندی',
            'Brand' => 'برند',
            'Course' => 'دوره',
            default => class_basename($class),
        };
    }
}
