<?php

namespace App\Modules\Comments\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:3', 'max:5000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.required' => 'متن نظر الزامی است.',
            'body.min' => 'نظر باید حداقل ۳ کاراکتر باشد.',
            'rating.min' => 'امتیاز باید بین ۱ تا ۵ باشد.',
            'rating.max' => 'امتیاز باید بین ۱ تا ۵ باشد.',
        ];
    }
}
