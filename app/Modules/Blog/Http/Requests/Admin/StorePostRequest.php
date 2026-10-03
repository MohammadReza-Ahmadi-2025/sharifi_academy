<?php

namespace App\Modules\Blog\Http\Requests\Admin;

use App\Modules\Blog\Enums\PostStatus;
use App\Modules\Catalog\Http\Requests\Admin\Concerns\NormalizesCatalogSeoInput;
use App\Modules\Catalog\Models\Category;
use App\Modules\Files\Http\Requests\Concerns\ValidatesGalleryUploads;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    use NormalizesCatalogSeoInput;
    use ValidatesGalleryUploads;

    protected function prepareForValidation(): void
    {
        $this->mergeCatalogSeoFields();
    }

    public function authorize(): bool
    {
        return $this->user()?->can('blog-posts-create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:posts,slug'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => [
                'integer',
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('type', Category::TYPE_BLOG)),
            ],
            'status' => ['required', 'string', Rule::in(array_column(PostStatus::cases(), 'value'))],
            ...$this->catalogSeoRules(),
            ...$this->singleImageRules('featured'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->assertMinimumOneImage($validator, 'featured');
    }
}
