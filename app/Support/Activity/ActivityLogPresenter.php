<?php

namespace App\Support\Activity;

use App\Models\ActivityLog;

class ActivityLogPresenter
{
    /**
     * @return array<string, string>
     */
    public static function actionLabels(): array
    {
        return [
            'created' => 'ایجاد',
            'updated' => 'ویرایش',
            'deleted' => 'حذف',
            'restored' => 'بازگردانی',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function moduleLabels(): array
    {
        return [
            'catalog' => 'کاتالوگ',
            'courses' => 'دوره‌ها',
            'blog' => 'وبلاگ',
            'comments' => 'نظرات',
            'social' => 'شبکه‌های اجتماعی',
            'settings' => 'تنظیمات',
            'stories' => 'استوری‌ها',
            'roles' => 'نقش‌ها',
            'users' => 'کاربران',
            'app' => 'سیستم',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forList(ActivityLog $log): array
    {
        $user = $log->user;

        return [
            'id' => $log->id,
            'description' => $log->description,
            'action' => $log->action,
            'action_label' => self::actionLabels()[$log->action] ?? $log->action,
            'module' => $log->module,
            'module_label' => self::moduleLabels()[$log->module] ?? $log->module,
            'subject_type' => $log->subject_type,
            'subject_id' => $log->subject_id,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'mobile' => $user->mobile,
            ] : null,
            'created_at' => $log->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forShow(ActivityLog $log): array
    {
        $properties = $log->properties ?? [];
        $changes = [];

        foreach ($properties['changes'] ?? [] as $field => $pair) {
            if (! is_array($pair)) {
                continue;
            }

            $changes[] = [
                'field' => $field,
                'field_label' => ActivityLogFieldLabels::label($field),
                'from' => ActivityLogFieldLabels::formatValue($pair['from'] ?? null),
                'to' => ActivityLogFieldLabels::formatValue($pair['to'] ?? null),
            ];
        }

        $snapshot = $properties['snapshot'] ?? null;
        $before = $properties['before'] ?? null;
        $after = $properties['after'] ?? null;

        return [
            ...self::forList($log),
            'route_name' => $log->route_name,
            'ip_address' => $log->ip_address,
            'user_agent' => $log->user_agent,
            'changes' => $changes,
            'snapshot' => self::labeledKeyValues($snapshot),
            'before' => self::labeledKeyValues($before),
            'after' => self::labeledKeyValues($after),
            'properties_raw' => $properties,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $data
     * @return list<array{field: string, field_label: string, value: string}>
     */
    private static function labeledKeyValues(?array $data): array
    {
        if ($data === null || $data === []) {
            return [];
        }

        $rows = [];

        foreach ($data as $field => $value) {
            if (in_array($field, ['updated_at', 'created_at', 'deleted_at', 'password', 'remember_token'], true)) {
                continue;
            }

            $rows[] = [
                'field' => (string) $field,
                'field_label' => ActivityLogFieldLabels::label((string) $field),
                'value' => ActivityLogFieldLabels::formatValue($value),
            ];
        }

        return $rows;
    }
}
