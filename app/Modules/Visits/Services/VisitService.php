<?php

namespace App\Modules\Visits\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VisitService
{
    protected int $cooldownMinutes = 30;

    public function record(Model $model): void
    {
        if (! method_exists($model, 'visits')) {
            return;
        }

        $userAgent = request()->userAgent();

        if ($this->isBot($userAgent)) {
            return;
        }

        if ($this->alreadyVisitedInSession($model)) {
            return;
        }

        $ip = request()->ip();
        $visitorId = auth()->id();

        $query = $model->visits()
            ->where('created_at', '>=', now()->subMinutes($this->cooldownMinutes));

        if ($visitorId) {
            $query->where('visitor_id', $visitorId);
        } else {
            $query->where('ip_address', $ip);
        }

        if ($query->exists()) {
            $this->markVisitedInSession($model);

            return;
        }

        $model->visits()->create([
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'visitor_id' => $visitorId,
        ]);

        if (in_array('views', $model->getFillable(), true)) {
            $model->increment('views');
        }

        $this->markVisitedInSession($model);
    }

    protected function alreadyVisitedInSession(Model $model): bool
    {
        $key = $this->sessionKey($model);
        $lastVisitedAt = session($key);

        if (! $lastVisitedAt) {
            return false;
        }

        return now()->diffInMinutes($lastVisitedAt) < $this->cooldownMinutes;
    }

    protected function markVisitedInSession(Model $model): void
    {
        session([$this->sessionKey($model) => now()->toDateTimeString()]);
    }

    protected function sessionKey(Model $model): string
    {
        return 'visited:'.str_replace('\\', '_', $model::class).':'.$model->getKey();
    }

    protected function isBot(?string $userAgent): bool
    {
        if (! $userAgent) {
            return false;
        }

        $ua = Str::lower($userAgent);

        foreach (['bot', 'crawl', 'spider', 'slurp', 'googlebot', 'bingbot', 'yandex'] as $bot) {
            if (Str::contains($ua, $bot)) {
                return true;
            }
        }

        return false;
    }
}
