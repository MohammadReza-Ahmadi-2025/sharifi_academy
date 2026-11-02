<?php

namespace App\Modules\Profile\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Modules\Profile\Http\Controllers\Concerns\ManagesOwnResume;

class ResumeController extends Controller
{
    use ManagesOwnResume;

    protected function resumeInertiaPage(): string
    {
        return 'Instructor/Resume/Index';
    }

    protected function resumeIndexRoute(): string
    {
        return 'instructor.resume.index';
    }

    protected function requiredResumePermission(): string
    {
        return 'instructor-resume-manage';
    }

    protected function resumePanelMeta(): array
    {
        return [
            'base_path' => '/instructor/resume',
            'panel' => 'instructor',
            'public_visibility' => true,
        ];
    }
}
