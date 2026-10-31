<?php

namespace App\Modules\Assessments\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Modules\Assessments\Http\Requests\Instructor\UpdateCertificateSettingsRequest;
use App\Modules\Courses\Models\Course;
use Illuminate\Http\RedirectResponse;

class CertificateSettingsController extends Controller
{
    public function update(UpdateCertificateSettingsRequest $request, Course $course): RedirectResponse
    {
        $data = $request->validated();

        $course->update([
            'certificate_enabled' => (bool) ($data['certificate_enabled'] ?? false),
            'certificate_title' => $data['certificate_title'] ?? null,
            'certificate_min_attendance_percent' => $data['certificate_min_attendance_percent'] ?? null,
            'certificate_min_quiz_score' => $data['certificate_min_quiz_score'] ?? null,
        ]);

        return redirect()
            ->route('instructor.courses.quizzes.index', $course)
            ->with('success', 'تنظیمات گواهینامه ذخیره شد.');
    }
}
