<?php

namespace App\Modules\Courses\Http\Requests\Admin;

use App\Modules\Courses\Models\Enrollment;
use Illuminate\Foundation\Http\FormRequest;

class SyncEnrollmentAdobeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $enrollment = $this->route('enrollment');

        return ($this->user()?->can('enrollments-update') ?? false)
            && $enrollment instanceof Enrollment;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
