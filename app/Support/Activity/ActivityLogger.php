<?php

namespace App\Support\Activity;

use App\Models\ActivityLog;
use App\Models\User;
use App\Modules\Admin\Support\AdminNavigation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>|null  $beforeSnapshot
     */
    public function log(Model $model, string $action, ?array $beforeSnapshot = null): void
    {
        $user = auth()->user();

        if (! $user instanceof User || ! $user->roles()->exists()) {
            return;
        }

        if ($model instanceof ActivityLog) {
            return;
        }

        $afterSnapshot = $this->snapshot($model);
        $properties = match ($action) {
            'created' => [
                'snapshot' => $afterSnapshot,
            ],
            'updated' => [
                'before' => $beforeSnapshot ?? [],
                'after' => $afterSnapshot,
                'changes' => $this->diff($beforeSnapshot ?? [], $afterSnapshot),
            ],
            'deleted' => [
                'snapshot' => $beforeSnapshot ?? $afterSnapshot,
            ],
            'restored' => [
                'snapshot' => $afterSnapshot,
            ],
            default => [],
        };

        if ($action === 'updated' && ($properties['changes'] ?? []) === []) {
            return;
        }

        ActivityLog::query()->create([
            'user_id' => $user->id,
            'module' => $this->moduleFor($model),
            'action' => $action,
            'subject_type' => $model::class,
            'subject_id' => $model->getKey(),
            'description' => $this->description($model, $action),
            'properties' => $properties,
            'route_name' => Request::route()?->getName(),
            'ip_address' => Request::ip(),
            'user_agent' => Str::limit((string) Request::userAgent(), 512, ''),
            'created_at' => now(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshotFromOriginal(Model $model): array
    {
        return $this->normalizeValues($model->getOriginal());
    }

    public function snapshot(Model $model): array
    {
        $attributes = $model->getAttributes();
        $hidden = array_merge(
            config('activity_log.hidden_attributes', []),
            $model->getHidden(),
        );

        foreach ($hidden as $key) {
            unset($attributes[$key]);
        }

        return $this->normalizeValues($attributes);
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return array<string, array{from: mixed, to: mixed}>
     */
    public function diff(array $before, array $after): array
    {
        $changes = [];

        foreach ($after as $key => $to) {
            if (in_array($key, ['updated_at', 'created_at', 'deleted_at'], true)) {
                continue;
            }

            $from = $before[$key] ?? null;

            if ($from != $to) {
                $changes[$key] = [
                    'from' => $from,
                    'to' => $to,
                ];
            }
        }

        return $changes;
    }

    private function moduleFor(Model $model): string
    {
        $class = $model::class;

        if (str_contains($class, '\\Modules\\')) {
            $parts = explode('\\', $class);
            $index = array_search('Modules', $parts, true);

            return strtolower($parts[$index + 1] ?? 'app');
        }

        return 'app';
    }

    private function description(Model $model, string $action): string
    {
        $label = $this->subjectLabel($model);
        $actionLabel = match ($action) {
            'created' => 'ایجاد',
            'updated' => 'ویرایش',
            'deleted' => 'حذف',
            'restored' => 'بازگردانی',
            default => $action,
        };

        return "{$actionLabel} {$label}";
    }

    private function subjectLabel(Model $model): string
    {
        foreach (['title', 'name', 'label', 'key', 'slug', 'mobile', 'email'] as $field) {
            if (isset($model->{$field}) && (string) $model->{$field} !== '') {
                return class_basename($model).' «'.(string) $model->{$field}.'»';
            }
        }

        return class_basename($model).' #'.$model->getKey();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function normalizeValues(array $attributes): array
    {
        return Arr::map($attributes, function ($value) {
            if (is_array($value)) {
                return $value;
            }

            if (is_bool($value)) {
                return $value;
            }

            if ($value === null) {
                return null;
            }

            if (is_object($value) && enum_exists($value::class)) {
                return $value->value ?? (string) $value;
            }

            return $value;
        });
    }
}
