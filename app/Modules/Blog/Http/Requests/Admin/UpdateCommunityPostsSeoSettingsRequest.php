<?php

namespace App\Modules\Blog\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommunityPostsSeoSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return ($user?->can('blog-posts-update') ?? false)
            || ($user?->can('site-settings-update') ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'posts_meta_title' => ['nullable', 'string', 'max:255'],
            'posts_meta_description' => ['nullable', 'string', 'max:1000'],
            'posts_meta_keywords' => ['nullable', 'string', 'max:500'],
            'posts_header_title' => ['nullable', 'string', 'max:255'],
            'posts_subheader_title' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'posts_meta_title' => 'عنوان متا',
            'posts_meta_description' => 'توضیحات متا',
            'posts_meta_keywords' => 'کلمات کلیدی',
            'posts_header_title' => 'عنوان هدر',
            'posts_subheader_title' => 'زیرعنوان',
        ];
    }
}
