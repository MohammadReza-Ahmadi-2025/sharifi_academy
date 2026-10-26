<?php

namespace App\Modules\AdobeConnect\Http\Requests\Admin;

use App\Modules\Courses\Models\ClassSession;
use Illuminate\Foundation\Http\FormRequest;

class SyncAdobeSessionRecordingsRequest extends FormRequest
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
        return [];
    }
}
