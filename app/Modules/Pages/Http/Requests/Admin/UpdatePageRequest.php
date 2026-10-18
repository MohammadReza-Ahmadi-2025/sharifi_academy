<?php

namespace App\Modules\Pages\Http\Requests\Admin;

use App\Modules\Catalog\Http\Requests\Admin\Concerns\NormalizesCatalogSeoInput;
use App\Modules\Pages\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    use NormalizesCatalogSeoInput;

    protected function prepareForValidation(): void
    {
        $this->mergeCatalogSeoFields();
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->can('pages-update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Page $page */
        $page = $this->route('page');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages', 'slug')->ignore($page->id),
            ],
            'content' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            ...$this->catalogSeoRules(),
        ];
    }
}
