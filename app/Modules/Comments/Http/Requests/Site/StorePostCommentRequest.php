<?php

namespace App\Modules\Comments\Http\Requests\Site;

use App\Modules\Blog\Models\Post;
use Illuminate\Foundation\Http\FormRequest;

class StorePostCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post instanceof Post && ($this->user()?->can('comment', $post) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:3', 'max:5000'],
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
        ];
    }
}
