<?php

namespace App\Modules\Stories\Support;

use Illuminate\Support\Carbon;

class StorySchedule
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{0: Carbon, 1: ?Carbon}
     */
    public static function fromRequestData(array $data): array
    {
        $starts = Carbon::parse((string) $data['display_starts_at']);

        $endsRaw = $data['display_ends_at'] ?? null;
        $ends = filled($endsRaw) ? Carbon::parse((string) $endsRaw) : null;

        return [$starts, $ends];
    }

    public static function toFormValue(?Carbon $value): string
    {
        return $value?->format('Y-m-d\TH:i') ?? '';
    }
}
