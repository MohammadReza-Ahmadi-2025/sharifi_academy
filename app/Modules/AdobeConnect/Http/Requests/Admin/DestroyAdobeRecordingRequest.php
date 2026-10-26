<?php

namespace App\Modules\AdobeConnect\Http\Requests\Admin;

use App\Modules\AdobeConnect\Models\AdobeRecording;
use Illuminate\Foundation\Http\FormRequest;

class DestroyAdobeRecordingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $recording = $this->route('adobeRecording');

        return $recording instanceof AdobeRecording
            && ($this->user()?->can('delete', $recording) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
