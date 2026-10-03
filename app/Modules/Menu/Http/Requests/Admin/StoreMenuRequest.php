<?php

namespace App\Modules\Menu\Http\Requests\Admin;

use App\Modules\Menu\Enums\MenuItemType;
use App\Modules\Menu\Enums\MenuLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('menus-create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:100'],
            'location' => ['required', Rule::enum(MenuLocation::class)],
            'sort' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'status' => ['nullable', 'boolean'],
            'items' => ['nullable', 'array'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.type' => ['required', Rule::enum(MenuItemType::class)],
            'items.*.url' => ['nullable', 'string', 'max:500'],
            'items.*.reference_id' => ['nullable', 'integer'],
            'items.*.sort' => ['nullable', 'integer', 'min:0'],
            'items.*.status' => ['nullable', 'boolean'],
            'items.*.target_blank' => ['nullable', 'boolean'],
        ];
    }
}
