<?php

namespace App\Modules\Profile\Support;

use App\Modules\Profile\Models\Resume;
use App\Support\PersianDate;

final class ResumeEntryPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function forPublic(Resume $resume): array
    {
        return [
            'id' => $resume->id,
            'type' => $resume->type,
            'type_label' => ResumeTypeOptions::labelFor($resume->type),
            'title' => $resume->title,
            'organization' => $resume->organization,
            'period' => self::periodLabel($resume),
            'description' => $resume->description,
            'link' => $resume->link,
            'is_current' => (bool) $resume->is_current,
            'attachments' => ResumeAttachmentMedia::payloads($resume),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forInstructor(Resume $resume): array
    {
        return [
            ...self::forPublic($resume),
            'start_date' => $resume->start_date?->format('Y-m-d'),
            'end_date' => $resume->end_date?->format('Y-m-d'),
            'display_order' => $resume->display_order,
        ];
    }

    public static function periodLabel(Resume $resume): ?string
    {
        $start = PersianDate::formatDate($resume->start_date, 'Y/m');
        $end = $resume->is_current
            ? 'اکنون'
            : PersianDate::formatDate($resume->end_date, 'Y/m');

        if ($start === null && $end === null) {
            return null;
        }

        return trim(($start ?? '—').' تا '.($end ?? '—'));
    }
}
