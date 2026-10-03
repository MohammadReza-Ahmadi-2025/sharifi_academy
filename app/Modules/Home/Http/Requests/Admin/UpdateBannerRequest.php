<?php

namespace App\Modules\Home\Http\Requests\Admin;

use App\Modules\Home\Enums\BannerLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class UpdateBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('home-banners-update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'button_link' => ['nullable', 'string', 'max:500'],
            'location' => ['required', Rule::enum(BannerLocation::class)],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'remove_image' => ['nullable', 'boolean'],
            'image' => ['nullable', 'array'],
            'image.0.file' => ['nullable', File::image()->max(4096)],
        ];
    }
}
