<?php

namespace App\Modules\Blog\Http\Requests\Site;

use App\Modules\Blog\Enums\ReactionType;
use App\Modules\Blog\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostReactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post instanceof Post && ($this->user()?->can('react', $post) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(ReactionType::values())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'نوع واکنش الزامی است.',
            'type.in' => 'نوع واکنش معتبر نیست.',
        ];
    }
}
