<?php

namespace App\Modules\AdobeConnect\Http\Requests\Admin;

use App\Modules\Courses\Models\ClassSession;
use Illuminate\Foundation\Http\FormRequest;

class StoreAdobeRecordingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = $this->route('classSession');

        return $session instanceof ClassSession
            && ($this->user()?->can('syncAdobeRecordings', $session) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sco_id' => ['required', 'string', 'max:64', 'unique:adobe_recordings,sco_id'],
            'name' => ['nullable', 'string', 'max:255'],
            'playback_url' => ['required', 'string', 'max:500', 'url:http,https'],
            'duration_seconds' => ['nullable', 'integer', 'min:0'],
            'size_bytes' => ['nullable', 'integer', 'min:0'],
            'is_public' => ['boolean'],
            'is_passcode_protected' => ['boolean'],
            'passcode' => ['nullable', 'string', 'max:255'],
        ];
    }
}
