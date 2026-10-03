<?php

namespace App\Modules\AdobeConnect\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdobeRecordingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('adobe-recordings-create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sco_id' => ['required', 'string', 'max:64', 'unique:adobe_recordings,sco_id'],
            'name' => ['nullable', 'string', 'max:255'],
            'playback_url' => ['required', 'string', 'max:500'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'size_bytes' => ['nullable', 'integer', 'min:0'],
            'is_public' => ['boolean'],
            'is_passcode_protected' => ['boolean'],
            'passcode' => ['nullable', 'string', 'max:255'],
        ];
    }
}
