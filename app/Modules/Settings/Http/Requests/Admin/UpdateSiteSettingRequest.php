<?php

namespace App\Modules\Settings\Http\Requests\Admin;

use App\Modules\Settings\Models\SiteSetting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('site-settings-update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var SiteSetting $setting */
        $setting = $this->route('siteSetting');

        return match ($setting->type) {
            'textarea' => [
                'value' => ['nullable', 'string', 'max:50000'],
            ],
            'number' => [
                'value' => ['nullable', 'numeric'],
            ],
            'color' => [
                'value' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            ],
            'switch' => [
                'value' => ['boolean'],
            ],
            'image' => [
                'image' => ['nullable', 'image', 'max:5120'],
            ],
            default => [
                'value' => ['nullable', 'string', 'max:2000'],
            ],
        };
    }

    protected function prepareForValidation(): void
    {
        /** @var SiteSetting $setting */
        $setting = $this->route('siteSetting');

        if ($setting->type === 'switch') {
            $this->merge([
                'value' => $this->boolean('value'),
            ]);

            return;
        }

        if ($this->has('value') && is_string($this->input('value'))) {
            $this->merge([
                'value' => trim($this->input('value')),
            ]);
        }
    }
}
