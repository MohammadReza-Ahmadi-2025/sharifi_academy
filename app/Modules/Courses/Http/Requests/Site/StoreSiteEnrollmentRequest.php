<?php

namespace App\Modules\Courses\Http\Requests\Site;

use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Services\SiteEnrollmentRequestService;
use Illuminate\Foundation\Http\FormRequest;

class StoreSiteEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $courseClass = $this->resolveCourseClass();

        if ($user === null || $courseClass === null) {
            return false;
        }

        return $this->user()->can('requestEnrollment', $courseClass);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'course_class_id' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'course_class_id.required' => 'انتخاب کلاس الزامی است.',
        ];
    }

    public function courseClass(): CourseClass
    {
        $courseClass = $this->resolveCourseClass();

        abort_if($courseClass === null, 404);

        return $courseClass;
    }

    private function resolveCourseClass(): ?CourseClass
    {
        $course = $this->route('course');

        if (! $course instanceof Course) {
            return null;
        }

        $classId = $this->input('course_class_id');
        if (! is_numeric($classId)) {
            return null;
        }

        return app(SiteEnrollmentRequestService::class)->findRequestableClass(
            $course,
            (int) $classId,
        );
    }
}
