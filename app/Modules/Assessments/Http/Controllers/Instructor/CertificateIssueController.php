<?php

namespace App\Modules\Assessments\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Assessments\Http\Requests\Instructor\IssueCertificateRequest;
use App\Modules\Assessments\Services\CertificateIssueService;
use App\Modules\Courses\Models\Course;
use Illuminate\Http\RedirectResponse;

class CertificateIssueController extends Controller
{
    public function store(
        IssueCertificateRequest $request,
        Course $course,
        CertificateIssueService $issuer,
    ): RedirectResponse {
        $user = User::query()->findOrFail((int) $request->validated('user_id'));
        $force = (bool) ($request->validated('force') ?? false);

        $certificate = $issuer->issue($course, $user, $request->user(), $force);

        return redirect()
            ->route('instructor.courses.roster', $course)
            ->with('success', 'گواهینامه صادر شد: '.$certificate->code);
    }
}
