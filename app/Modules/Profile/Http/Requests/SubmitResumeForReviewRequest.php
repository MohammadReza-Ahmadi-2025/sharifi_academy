<?php

namespace App\Modules\Profile\Http\Requests;

use App\Modules\Profile\Models\Resume;
use Illuminate\Foundation\Http\FormRequest;

class SubmitResumeForReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', Resume::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
