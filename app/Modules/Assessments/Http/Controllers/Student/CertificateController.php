<?php

namespace App\Modules\Assessments\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Assessments\Http\Requests\Student\ClaimCertificateRequest;
use App\Modules\Assessments\Models\Certificate;
use App\Modules\Assessments\Services\CertificateIssueService;
use App\Modules\Courses\Enums\EnrollmentStatus;
use App\Modules\Courses\Models\Course;
use App\Modules\Courses\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    public function index(Request $request, CertificateIssueService $issuer): Response
    {
        $user = $request->user();
        abort_unless($user !== null, 403);
        $this->authorize('viewAny', Certificate::class);

        $certificates = Certificate::query()
            ->where('user_id', $user->id)
            ->with('course')
            ->orderByDesc('issued_at')
            ->get()
            ->map(fn (Certificate $certificate) => [
                'id' => $certificate->id,
                'code' => $certificate->code,
                'title' => $certificate->title,
                'course_title' => $certificate->course?->title,
                'issued_at' => $certificate->issued_at?->toIso8601String(),
                'show_url' => route('student.certificates.show', $certificate),
            ])
            ->values()
            ->all();

        $courseIds = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('status', EnrollmentStatus::Confirmed)
            ->with('courseClass')
            ->get()
            ->pluck('courseClass.course_id')
            ->filter()
            ->unique();

        $eligibleCourses = Course::query()
            ->whereIn('id', $courseIds)
            ->where('certificate_enabled', true)
            ->orderBy('title')
            ->get()
            ->filter(fn (Course $course) => ! Certificate::query()
                ->where('course_id', $course->id)
                ->where('user_id', $user->id)
                ->exists())
            ->map(function (Course $course) use ($issuer, $user): array {
                $eligibility = $issuer->eligibility($course, $user);

                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'eligible' => $eligibility['eligible'],
                    'reasons' => $eligibility['reasons'],
                    'claim_url' => route('student.certificates.claim', $course),
                ];
            })
            ->values()
            ->all();

        return Inertia::render('Student/Certificates/Index', [
            'certificates' => $certificates,
            'eligibleCourses' => $eligibleCourses,
        ]);
    }

    public function show(Certificate $certificate): Response
    {
        $this->authorize('view', $certificate);
        $certificate->load(['course', 'user']);

        return Inertia::render('Student/Certificates/Show', [
            'certificate' => [
                'id' => $certificate->id,
                'code' => $certificate->code,
                'title' => $certificate->title,
                'issued_at' => $certificate->issued_at?->toIso8601String(),
                'course_title' => $certificate->course?->title,
                'student_name' => trim(($certificate->user?->name ?? '').' '.($certificate->user?->family ?? '')),
                'verify_url' => route('certificates.verify', $certificate->code),
            ],
        ]);
    }

    public function claim(
        ClaimCertificateRequest $request,
        Course $course,
        CertificateIssueService $issuer,
    ): RedirectResponse {
        $certificate = $issuer->issue($course, $request->user());

        return redirect()
            ->route('student.certificates.show', $certificate)
            ->with('success', 'گواهینامه صادر شد.');
    }
}
