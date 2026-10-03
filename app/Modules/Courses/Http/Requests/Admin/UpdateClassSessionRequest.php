<?php

namespace App\Modules\Courses\Http\Requests\Admin;

use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\CourseClass;
use Illuminate\Validation\Rule;

class UpdateClassSessionRequest extends StoreClassSessionRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('class-sessions-update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var CourseClass $courseClass */
        $courseClass = $this->route('courseClass');
        /** @var ClassSession $session */
        $session = $this->route('classSession');

        return [
            ...parent::rules(),
            'session_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('class_sessions', 'session_number')
                    ->where('course_class_id', $courseClass->id)
                    ->ignore($session->id),
            ],
        ];
    }
}
