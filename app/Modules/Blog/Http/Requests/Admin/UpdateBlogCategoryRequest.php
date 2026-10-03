<?php

namespace App\Modules\Blog\Http\Requests\Admin;

use App\Modules\Catalog\Http\Requests\Admin\Concerns\NormalizesCatalogSeoInput;
use App\Modules\Catalog\Models\Category;
use App\Modules\Files\Http\Requests\Concerns\ValidatesGalleryUploads;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBlogCategoryRequest extends FormRequest
{
    use NormalizesCatalogSeoInput;
    use ValidatesGalleryUploads;

    protected function prepareForValidation(): void
    {
        $this->mergeCatalogSeoFields();
    }

    public function authorize(): bool
    {
        return $this->user()?->can('blog-categories-update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('type', Category::TYPE_BLOG),
                Rule::notIn([$category?->id]),
            ],
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($category?->id),
            ],
            'description' => ['nullable', 'string'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'boolean'],
            'show_in_menu' => ['nullable', 'boolean'],
            ...$this->catalogSeoRules(),
            ...$this->singleImageRules('image'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->assertMinimumOneImage($validator, 'image');
    }
}
