<?php

namespace App\Modules\Courses\Http\Requests\Admin;

use App\Modules\Courses\Models\ClassSession;
use Illuminate\Foundation\Http\FormRequest;

class SyncAdobeSessionAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = $this->route('classSession');
        if (! $session instanceof ClassSession) {
            return false;
        }

        $user = $this->user();
        if ($user === null) {
            return false;
        }

        if ($user->can('session-attendances-update')) {
            return true;
        }

        return $user->can('viewAttendance', $session);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'mark_absent' => ['sometimes', 'boolean'],
        ];
    }
}
