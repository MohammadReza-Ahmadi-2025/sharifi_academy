<?php

namespace App\Modules\Comments\Http\Requests\Admin;

use App\Modules\Comments\Enums\CommentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('comments-update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'display_name' => ['nullable', 'string', 'max:191'],
            'body' => ['required', 'string', 'max:2000'],
            'status' => ['required', 'integer', Rule::in(array_column(CommentStatus::cases(), 'value'))],
        ];
    }
}
