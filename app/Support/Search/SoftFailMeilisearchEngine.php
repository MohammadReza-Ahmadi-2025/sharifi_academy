<?php

namespace App\Support\Search;

use Illuminate\Support\Facades\Log;
use Laravel\Scout\Engines\MeilisearchEngine;
use Throwable;

/**
 * Meilisearch outages must not turn admin CRUD (category image, etc.) into HTTP 500.
 */
class SoftFailMeilisearchEngine extends MeilisearchEngine
{
    public function update($models)
    {
        try {
            parent::update($models);
        } catch (Throwable $e) {
            $this->reportFailure('update', $models, $e);
        }
    }

    public function delete($models)
    {
        try {
            parent::delete($models);
        } catch (Throwable $e) {
            $this->reportFailure('delete', $models, $e);
        }
    }

    private function reportFailure(string $action, $models, Throwable $e): void
    {
        $first = $models->first();

        Log::warning('Meilisearch index sync failed; request continues.', [
            'action' => $action,
            'index' => $first?->indexableAs(),
            'model' => $first ? $first::class : null,
            'count' => $models->count(),
            'message' => $e->getMessage(),
        ]);
    }
}
