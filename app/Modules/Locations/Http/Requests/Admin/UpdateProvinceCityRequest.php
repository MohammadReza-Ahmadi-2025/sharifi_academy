<?php

namespace App\Modules\Locations\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProvinceCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('locations-update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cities' => ['required', 'array'],
            'cities.*.id' => ['required', 'integer'],
            'cities.*.title' => ['required', 'string', 'max:255'],
            'cities.*.sort' => ['nullable', 'integer', 'min:0', 'max:99999'],
        ];
    }
}
