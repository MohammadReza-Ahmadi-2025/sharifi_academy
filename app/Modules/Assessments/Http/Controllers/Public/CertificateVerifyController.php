<?php

namespace App\Modules\Assessments\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Modules\Assessments\Models\Certificate;
use Inertia\Inertia;
use Inertia\Response;

class CertificateVerifyController extends Controller
{
    public function __invoke(string $code): Response
    {
        $certificate = Certificate::query()
            ->where('code', strtoupper($code))
            ->with(['course', 'user'])
            ->firstOrFail();

        return Inertia::render('Public/CertificateVerify', [
            'certificate' => [
                'code' => $certificate->code,
                'title' => $certificate->title,
                'issued_at' => $certificate->issued_at?->toIso8601String(),
                'course_title' => $certificate->course?->title,
                'student_name' => trim(($certificate->user?->name ?? '').' '.($certificate->user?->family ?? '')),
                'valid' => true,
            ],
        ]);
    }
}
