<?php

namespace App\Modules\Courses\Http\Requests\Admin;

use App\Modules\Courses\Enums\ClassInstructorRole;
use App\Modules\Courses\Enums\CourseClassDeliveryType;
use App\Modules\Courses\Enums\CourseClassOnlinePlatform;
use App\Modules\Courses\Enums\CourseClassStatus;
use App\Modules\Courses\Support\CourseClassMeetingSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('course-classes-create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $payload = [];

        if ($this->has('code')) {
            $code = $this->input('code');
            $code = is_string($code) ? trim($code) : $code;
            $payload['code'] = $code === '' ? null : $code;
        }

        if ($this->exists('meeting_weekdays')) {
            $payload['meeting_weekdays'] = CourseClassMeetingSchedule::normalizeWeekdays(
                is_array($this->input('meeting_weekdays')) ? $this->input('meeting_weekdays') : [],
            );
        }

        if ($this->exists('meeting_starts_time')) {
            $payload['meeting_starts_time'] = CourseClassMeetingSchedule::normalizeTime(
                $this->input('meeting_starts_time'),
            );
        }

        if ($this->exists('meeting_ends_time')) {
            $payload['meeting_ends_time'] = CourseClassMeetingSchedule::normalizeTime(
                $this->input('meeting_ends_time'),
            );
        }

        if ($payload !== []) {
            $this->merge($payload);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:80', Rule::unique('course_classes', 'code')],
            'status' => ['required', Rule::enum(CourseClassStatus::class)],
            'delivery_type' => ['nullable', Rule::enum(CourseClassDeliveryType::class)],
            'online_platform' => ['nullable', Rule::enum(CourseClassOnlinePlatform::class)],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'price' => ['nullable', 'integer', 'min:0'],
            'registration_starts_at' => ['nullable', 'date'],
            'registration_ends_at' => ['nullable', 'date', 'after_or_equal:registration_starts_at'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'meeting_weekdays' => ['nullable', 'array'],
            'meeting_weekdays.*' => ['integer', 'between:0,6', 'distinct'],
            'meeting_starts_time' => ['nullable', 'date_format:H:i'],
            'meeting_ends_time' => [
                'nullable',
                'date_format:H:i',
                Rule::when(
                    filled($this->input('meeting_starts_time')),
                    ['after:meeting_starts_time'],
                ),
            ],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_public' => ['boolean'],
            'instructors' => ['nullable', 'array'],
            'instructors.*.user_id' => [
                'required',
                'integer',
                'exists:users,id',
                'distinct',
            ],
            'instructors.*.role' => ['required', Rule::enum(ClassInstructorRole::class)],
            'instructors.*.is_primary' => ['boolean'],
            'instructors.*.share_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'نام',
            'code' => 'کد',
            'status' => 'وضعیت',
            'meeting_weekdays' => 'روزهای برگزاری',
            'meeting_starts_time' => 'ساعت شروع جلسه',
            'meeting_ends_time' => 'ساعت پایان جلسه',
            'instructors.*.user_id' => 'استاد',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'instructors.*.user_id.distinct' => 'یک استاد را نمی‌توان دو بار به کلاس اضافه کرد.',
        ];
    }
}
