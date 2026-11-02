<?php

namespace App\Modules\Profile\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Modules\Profile\Http\Controllers\Concerns\ManagesOwnResume;

class ResumeController extends Controller
{
    use ManagesOwnResume;

    protected function resumeInertiaPage(): string
    {
        return 'Student/Resume/Index';
    }

    protected function resumeIndexRoute(): string
    {
        return 'student.resume.index';
    }

    protected function requiredResumePermission(): string
    {
        return 'student-resume-manage';
    }

    protected function resumePanelMeta(): array
    {
        return [
            'base_path' => '/student/resume',
            'panel' => 'student',
            'public_visibility' => false,
        ];
    }
}
