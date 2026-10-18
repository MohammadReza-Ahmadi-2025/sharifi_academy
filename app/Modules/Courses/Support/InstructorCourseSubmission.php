<?php

namespace App\Modules\Courses\Support;

use App\Modules\Courses\Enums\CourseStatus;

final class InstructorCourseSubmission
{
    /**
     * استاد نمی‌تواند مستقیم منتشر کند؛ درخواست انتشار → در انتظار تایید.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalizeStatusForInstructor(array $data): array
    {
        $status = (string) ($data['status'] ?? CourseStatus::Draft->value);

        if ($status === CourseStatus::Published->value) {
            $data['status'] = CourseStatus::PendingReview->value;
        }

        if ($status === CourseStatus::Archived->value) {
            $data['status'] = CourseStatus::Draft->value;
        }

        return $data;
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function statusOptionsForInstructor(): array
    {
        return [
            ['value' => CourseStatus::Draft->value, 'label' => CourseStatus::Draft->label()],
            [
                'value' => CourseStatus::PendingReview->value,
                'label' => 'ارسال برای انتشار (تایید ادمین)',
            ],
        ];
    }
}
