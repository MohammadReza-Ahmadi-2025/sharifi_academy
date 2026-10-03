<?php

namespace App\Observers;

use App\Support\Activity\ActivityLogger;
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
        $this->logger->log($model, 'created');
    }

    public function updating(Model $model): void
    {
        self::$beforeSnapshots[$this->stashKey($model)] = $this->logger->snapshotFromOriginal($model);
    }

    public function updated(Model $model): void
    {
        $key = $this->stashKey($model);
        $before = self::$beforeSnapshots[$key] ?? null;
        unset(self::$beforeSnapshots[$key]);

        if ($before === null) {
            return;
        }

        $this->logger->log($model, 'updated', $before);
    }

    public function deleted(Model $model): void
    {
        $key = $this->stashKey($model);
        $before = self::$beforeSnapshots[$key] ?? $this->logger->snapshot($model);
        unset(self::$beforeSnapshots[$key]);

        $this->logger->log($model, 'deleted', $before);
    }

    public function restored(Model $model): void
    {
        $this->logger->log($model, 'restored');
    }

    private function stashKey(Model $model): string
    {
        return $model::class.':'.$model->getKey();
    }
}
