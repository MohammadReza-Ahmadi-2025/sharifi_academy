<?php

namespace App\Modules\AdobeConnect\Http\Requests\Admin;

use App\Modules\AdobeConnect\Enums\AdobeRoomAccessType;
use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use App\Modules\Courses\Models\ClassSession;
use App\Modules\Courses\Models\CourseClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdobeRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $session = $this->route('classSession');
        if ($session instanceof ClassSession) {
            return $this->user()?->can('createAdobeRoom', $session) ?? false;
        }

        $courseClass = $this->route('courseClass');

        return $courseClass instanceof CourseClass
            && ($this->user()?->can('createAdobeRoom', $courseClass) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sco_id' => ['required', 'string', 'max:64', 'unique:adobe_rooms,sco_id'],
            'url_path' => ['nullable', 'string', 'max:255', 'unique:adobe_rooms,url_path'],
            'meeting_url' => ['nullable', 'string', 'max:500', 'url:http,https'],
            'access_type' => ['required', Rule::enum(AdobeRoomAccessType::class)],
            'passcode' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(AdobeRoomStatus::class)],
        ];
    }
}
