<?php

namespace App\Modules\Visits\Concerns;

use App\Modules\Visits\Models\Visit;
use App\Modules\Visits\Services\VisitService;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasVisits
{
    /**
     * @return MorphMany<Visit, $this>
     */
    public function visits(): MorphMany
    {
        return $this->morphMany(Visit::class, 'visitable');
    }

    public function recordVisit(): void
    {
        app(VisitService::class)->record($this);
    }
}
