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
        $visitable = $visit->visitable;

        return [
            'id' => $visit->id,
            'visitable_type' => class_basename($visit->visitable_type),
            'visitable_type_label' => self::typeLabel($visit->visitable_type),
            'visitable_id' => $visit->visitable_id,
            'visitable_title' => $visitable && isset($visitable->title) ? (string) $visitable->title : '—',
            'ip_address' => $visit->ip_address,
            'visitor_id' => $visit->visitor_id,
            'visitor_name' => $visit->visitor?->name,
            'created_at' => $visit->created_at?->toIso8601String(),
        ];
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
