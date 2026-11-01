<?php

namespace App\Observers;

use App\Support\Activity\ActivityLogger;
use App\Support\SafeCallable;
use Illuminate\Database\Eloquent\Model;

class ActivityLogObserver
{
    /** @var array<string, array<string, mixed>> */
    private static array $beforeSnapshots = [];

    public function __construct(
        private readonly ActivityLogger $logger,
    ) {}

    public function created(Model $model): void
    {
        SafeCallable::run(
            'activity.created',
            fn () => $this->logger->log($model, 'created'),
            null,
        );
    }

    public function updating(Model $model): void
    {
        SafeCallable::run(
            'activity.updating',
            function () use ($model): void {
                self::$beforeSnapshots[$this->stashKey($model)] = $this->logger->snapshotFromOriginal($model);
            },
            null,
        );
    }

    public function updated(Model $model): void
    {
        SafeCallable::run(
            'activity.updated',
            function () use ($model): void {
                $key = $this->stashKey($model);
                $before = self::$beforeSnapshots[$key] ?? null;
                unset(self::$beforeSnapshots[$key]);

                if ($before === null) {
                    return;
                }

                $this->logger->log($model, 'updated', $before);
            },
            null,
        );
    }

    public function deleted(Model $model): void
    {
        SafeCallable::run(
            'activity.deleted',
            function () use ($model): void {
                $key = $this->stashKey($model);
                $before = self::$beforeSnapshots[$key] ?? $this->logger->snapshot($model);
                unset(self::$beforeSnapshots[$key]);

                $this->logger->log($model, 'deleted', $before);
            },
            null,
        );
    }

    public function restored(Model $model): void
    {
        SafeCallable::run(
            'activity.restored',
            fn () => $this->logger->log($model, 'restored'),
            null,
        );
    }

    private function stashKey(Model $model): string
    {
        return $model::class.':'.$model->getKey();
    }
}
