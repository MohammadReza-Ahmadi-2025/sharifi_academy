<?php

namespace App\Modules\Blog\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogSeoSettingsRequest extends FormRequest
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
            'blogs_meta_title' => ['nullable', 'string', 'max:255'],
            'blogs_meta_description' => ['nullable', 'string', 'max:1000'],
            'blogs_meta_keywords' => ['nullable', 'string', 'max:500'],
            'blogs_header_title' => ['nullable', 'string', 'max:255'],
            'blogs_subheader_title' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'blogs_meta_title' => 'عنوان متا',
            'blogs_meta_description' => 'توضیحات متا',
            'blogs_meta_keywords' => 'کلمات کلیدی',
            'blogs_header_title' => 'عنوان هدر',
            'blogs_subheader_title' => 'زیرعنوان',
        ];
    }
}
