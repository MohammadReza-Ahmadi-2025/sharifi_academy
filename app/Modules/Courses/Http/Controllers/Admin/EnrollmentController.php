<?php

namespace App\Modules\Courses\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Http\Requests\Admin\StoreEnrollmentRequest;
use App\Modules\Courses\Http\Requests\Admin\UpdateEnrollmentRequest;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Services\AdminEnrollmentService;
use App\Modules\Courses\Support\EnsuresNestedCourseAccess;
use App\Support\Notifications\InstructorModerationNotifier;
use Illuminate\Http\RedirectResponse;

class EnrollmentController extends Controller
{
    use EnsuresNestedCourseAccess;

    public function store(
        StoreEnrollmentRequest $request,
        Course $course,
        CourseClass $courseClass,
        AdminEnrollmentService $enrollmentService,
    ): RedirectResponse {
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $data = $request->validated();

        $enrollment = $enrollmentService->createForClass($courseClass, $data);

        return redirect()
            ->route('admin.courses.classes.edit', [$course, $courseClass])
            ->with('success', "ثبت‌نام #{$enrollment->id} ایجاد شد.");
    }

    public function update(UpdateEnrollmentRequest $request, Course $course, CourseClass $courseClass, Enrollment $enrollment): RedirectResponse
    {
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);

        if ($enrollment->course_class_id !== $courseClass->id) {
            abort(404);
        }

        $data = $request->validated();
        $wasConfirmed = $enrollment->isConfirmed();

        $enrollment->update([
            'status' => $data['status'],
            'price_amount' => $data['price_amount'] ?? $enrollment->price_amount,
            'admin_note' => $data['admin_note'] ?? null,
            'confirmed_at' => $data['status'] === EnrollmentStatus::Confirmed->value
                ? ($enrollment->confirmed_at ?? now())
                : null,
            'cancelled_at' => $data['status'] === EnrollmentStatus::Cancelled->value
                ? ($enrollment->cancelled_at ?? now())
                : null,
        ]);

        $enrollment->refresh();

        if (! $wasConfirmed && $enrollment->isConfirmed()) {
            InstructorModerationNotifier::enrollmentConfirmed($enrollment);
        }

        return redirect()
            ->route('admin.courses.classes.edit', [$course, $courseClass])
            ->with('success', 'ثبت‌نام به‌روزرسانی شد.');
    }

    public function destroy(Course $course, CourseClass $courseClass, Enrollment $enrollment): RedirectResponse
    {
        abort_unless(auth()->user()?->can('enrollments-delete'), 403);
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);

        if ($enrollment->course_class_id !== $courseClass->id) {
            abort(404);
        }

        $enrollment->delete();

        return redirect()
            ->route('admin.courses.classes.edit', [$course, $courseClass])
            ->with('success', 'ثبت‌نام حذف شد.');
    }
}
