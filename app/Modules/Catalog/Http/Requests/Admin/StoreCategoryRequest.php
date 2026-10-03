<?php

namespace App\Modules\Catalog\Http\Requests\Admin;

use App\Modules\Catalog\Http\Requests\Admin\Concerns\NormalizesCatalogSeoInput;
use App\Modules\Catalog\Support\AdminCategoryTypes;
use App\Modules\Files\Http\Requests\Concerns\ValidatesGalleryUploads;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    use NormalizesCatalogSeoInput;
    use ValidatesGalleryUploads;

    protected function prepareForValidation(): void
    {
        $this->mergeCatalogSeoFields();

        if (! $this->filled('type')) {
            $this->merge([
                'type' => $this->query('type'),
            ]);
        }
    }

    public function authorize(): bool
    {
        $type = AdminCategoryTypes::resolveType($this->input('type'));

        return AdminCategoryTypes::canCreate($this->user(), $type);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $type = AdminCategoryTypes::resolveType($this->input('type'));

        return [
            'type' => ['required', 'string', Rule::in(AdminCategoryTypes::values())],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where('type', $type),
            ],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
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
