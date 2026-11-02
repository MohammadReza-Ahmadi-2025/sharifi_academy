<?php

namespace App\Modules\Profile\Policies;

use App\Models\User;
use App\Modules\Profile\Models\Resume;

class ResumePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManageOwnResume($user);
    }

    public function view(User $user, Resume $resume): bool
    {
        return $this->canManageOwnResume($user) && (int) $resume->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $this->canManageOwnResume($user);
    }

    public function update(User $user, Resume $resume): bool
    {
        return $this->canManageOwnResume($user) && (int) $resume->user_id === (int) $user->id;
    }

    public function delete(User $user, Resume $resume): bool
    {
        return $this->canManageOwnResume($user) && (int) $resume->user_id === (int) $user->id;
    }

    public function syncSkills(User $user): bool
    {
        return $this->viewAny($user);
    }

    private function canManageOwnResume(User $user): bool
    {
        return $user->can('instructor-resume-manage') || $user->can('student-resume-manage');
    }
}
