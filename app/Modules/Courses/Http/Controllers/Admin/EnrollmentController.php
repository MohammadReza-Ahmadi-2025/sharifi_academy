<?php

namespace App\Modules\Courses\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\AdobeConnect\Services\SyncAdobeConnectEnrollmentService;
use App\Modules\AdobeConnect\Support\AdobeEnrollmentSyncResult;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Http\Requests\Admin\StoreEnrollmentRequest;
use App\Modules\Courses\Http\Requests\Admin\SyncClassEnrollmentsAdobeRequest;
use App\Modules\Courses\Http\Requests\Admin\SyncEnrollmentAdobeRequest;
use App\Modules\Courses\Http\Requests\Admin\UpdateEnrollmentRequest;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\CourseClass;
use App\Modules\Courses\Models\Enrollment;
use App\Modules\Courses\Services\AdminEnrollmentService;
use App\Modules\Courses\Services\EnrollmentStudentAccessService;
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

        $created = $enrollmentService->createForClass($courseClass, $data);
        $enrollment = $created['enrollment'];
        $flash = "ثبت‌نام #{$enrollment->id} ایجاد شد.";
        if ($created['adobe'] instanceof AdobeEnrollmentSyncResult) {
            $flash = $created['adobe']->appendToFlash($flash);
        }

        return redirect()
            ->route('admin.courses.classes.edit', [$course, $courseClass])
            ->with('success', $flash);
    }

    public function update(
        UpdateEnrollmentRequest $request,
        Course $course,
        CourseClass $courseClass,
        Enrollment $enrollment,
        EnrollmentStudentAccessService $studentAccess,
    ): RedirectResponse {
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

        $adobe = null;
        if (! $wasConfirmed && $enrollment->isConfirmed()) {
            $enrollment->loadMissing('user');
            $adobe = $studentAccess->grantStudentRoleIfConfirmed($enrollment);
            InstructorModerationNotifier::enrollmentConfirmed($enrollment);
        }

        $flash = 'ثبت‌نام به‌روزرسانی شد.';
        if ($adobe !== null) {
            $flash = $adobe->appendToFlash($flash);
        }

        return redirect()
            ->route('admin.courses.classes.edit', [$course, $courseClass])
            ->with('success', $flash);
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

    public function syncAdobe(
        SyncEnrollmentAdobeRequest $request,
        Course $course,
        CourseClass $courseClass,
        Enrollment $enrollment,
        EnrollmentStudentAccessService $studentAccess,
    ): RedirectResponse {
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);

        if ($enrollment->course_class_id !== $courseClass->id) {
            abort(404);
        }

        if (! $enrollment->isConfirmed()) {
            return redirect()
                ->route('admin.courses.classes.edit', [$course, $courseClass])
                ->with('error', 'فقط ثبت‌نام قطعی را می‌توان با Adobe همگام کرد.');
        }

        $enrollment->loadMissing('user');
        $result = $studentAccess->grantStudentRoleIfConfirmed($enrollment);
        $flash = 'همگام‌سازی Adobe انجام شد.';
        if ($result instanceof AdobeEnrollmentSyncResult) {
            $flash = $result->appendToFlash($flash);
        }

        $key = $result?->status === 'failed' ? 'error' : 'success';

        return redirect()
            ->route('admin.courses.classes.edit', [$course, $courseClass])
            ->with($key, $flash);
    }

    public function syncAdobeBulk(
        SyncClassEnrollmentsAdobeRequest $request,
        Course $course,
        CourseClass $courseClass,
        SyncAdobeConnectEnrollmentService $adobeEnrollmentSync,
    ): RedirectResponse {
        $courseClass = $this->courseClassOrFail($course, $courseClass->id);
        $onlyMissing = $request->boolean('only_missing', true);
        $stats = $adobeEnrollmentSync->syncConfirmedForClass($courseClass, $onlyMissing);

        $flash = sprintf(
            'همگام‌سازی Adobe کلاس: %d وصل، %d ساخته‌شده، %d ناموفق، %d ردشده.',
            $stats['linked'],
            $stats['created'],
            $stats['failed'],
            $stats['skipped'],
        );

        $key = $stats['failed'] > 0 ? 'error' : 'success';

        return redirect()
            ->route('admin.courses.classes.edit', [$course, $courseClass])
            ->with($key, $flash);
    }
}
