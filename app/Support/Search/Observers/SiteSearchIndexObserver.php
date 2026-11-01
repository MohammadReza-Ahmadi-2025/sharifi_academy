<?php

namespace App\Support\Search\Observers;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Laravel\Scout\Searchable;
use Throwable;

class SiteSearchIndexObserver
{
    protected bool $forceSaving = false;

    public function saved(Model $model): void
    {
        if (! $this->usesSearchable($model)) {
            return;
        }

        if (! $this->forceSaving && method_exists($model, 'searchIndexShouldBeUpdated') && ! $model->searchIndexShouldBeUpdated()) {
            return;
        }

        if (! $model->shouldBeSearchable()) {
            if (method_exists($model, 'wasSearchableBeforeUpdate') && $model->wasSearchableBeforeUpdate()) {
                $this->safely('unsearchable', $model, fn () => $model->unsearchable());
            }

            return;
        }

        $this->safely('searchable', $model, fn () => $model->searchable());
    }

    public function deleted(Model $model): void
    {
        if (! $this->usesSearchable($model)) {
            return;
        }

        if (method_exists($model, 'wasSearchableBeforeDelete') && ! $model->wasSearchableBeforeDelete()) {
            return;
        }

        if ($this->usesSoftDelete($model) && $model->isForceDeleting()) {
            return;
        }

        if (config('scout.soft_delete', false) && $this->usesSoftDelete($model)) {
            $this->whileForcingUpdate(function () use ($model): void {
                $this->saved($model);
            });

            return;
        }

        $this->safely('unsearchable', $model, fn () => $model->unsearchable());
    }

    public function restored(Model $model): void
    {
        if (! $this->usesSearchable($model)) {
            return;
        }

        $this->whileForcingUpdate(function () use ($model): void {
            $this->saved($model);
        });
    }

    public function forceDeleted(Model $model): void
    {
        if (! $this->usesSearchable($model)) {
            return;
        }

        $this->safely('unsearchable', $model, fn () => $model->unsearchable());
    }

    protected function usesSearchable(Model $model): bool
    {
        return in_array(Searchable::class, class_uses_recursive($model), true)
            && method_exists($model, 'shouldBeSearchable');
    }

    protected function usesSoftDelete(Model $model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model), true);
    }

    protected function whileForcingUpdate(Closure $callback): void
    {
        $this->forceSaving = true;

        try {
            $callback();
        } finally {
            $this->forceSaving = false;
        }
    }

    /**
     * Search outages must not break admin CRUD (e.g. category image replace).
     */
    protected function safely(string $action, Model $model, Closure $callback): void
    {
        try {
            $callback();
        } catch (Throwable $e) {
            Log::warning('Scout index sync failed; request continues.', [
                'action' => $action,
                'model' => $model::class,
                'id' => $model->getKey(),
                'driver' => config('scout.driver'),
                'message' => $e->getMessage(),
            ]);
        }
    }
}
