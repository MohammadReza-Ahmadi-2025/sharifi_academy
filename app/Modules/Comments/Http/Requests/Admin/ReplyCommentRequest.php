<?php

namespace App\Modules\Comments\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReplyCommentRequest extends FormRequest
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
            'body' => ['required', 'string', 'max:2000'],
            'display_name' => ['nullable', 'string', 'max:191'],
        ];
    }
}
