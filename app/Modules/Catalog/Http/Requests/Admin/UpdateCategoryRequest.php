<?php

namespace App\Modules\Catalog\Http\Requests\Admin;

use App\Modules\Catalog\Http\Requests\Admin\Concerns\NormalizesCatalogSeoInput;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Support\AdminCategoryTypes;
use App\Modules\Files\Http\Requests\Concerns\ValidatesGalleryUploads;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    use NormalizesCatalogSeoInput;
    use ValidatesGalleryUploads;

    protected function prepareForValidation(): void
    {
        $this->mergeCatalogSeoFields();
    }

    public function authorize(): bool
    {
        $category = $this->route('category');

        if (! $category instanceof Category) {
            return false;
        }

        return AdminCategoryTypes::canUpdate($this->user(), $category->type);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $category = $this->route('category');
        $type = $category instanceof Category ? $category->type : Category::TYPE_PRODUCT;

        return [
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('type', $type),
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
