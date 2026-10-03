<?php

namespace App\Modules\Courses\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Http\Requests\Admin\StoreEnrollmentRequest;
use App\Modules\Courses\Http\Requests\Admin\UpdateEnrollmentRequest;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Support\EnsuresNestedCourseAccess;
use Illuminate\Http\RedirectResponse;

class EnrollmentController extends Controller
{
    use EnsuresNestedCourseAccess;

    public function store(StoreEnrollmentRequest $request, Course $course, CourseClass $courseClass): RedirectResponse
    {
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $data = $request->validated();

        $enrollment = Enrollment::query()->create([
            'course_class_id' => $courseClass->id,
            'user_id' => $data['user_id'],
            'status' => $data['status'],
            'price_amount' => $data['price_amount'] ?? $courseClass->price ?? 0,
            'admin_note' => $data['admin_note'] ?? null,
            'confirmed_at' => $data['status'] === EnrollmentStatus::Confirmed->value ? now() : null,
            'cancelled_at' => $data['status'] === EnrollmentStatus::Cancelled->value ? now() : null,
        ]);

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
