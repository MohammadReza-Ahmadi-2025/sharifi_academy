<?php

namespace App\Modules\AdobeConnect\Http\Requests\Admin;

use App\Modules\AdobeConnect\Enums\AdobeRoomAccessType;
use App\Modules\AdobeConnect\Enums\AdobeRoomStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdobeRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('adobe-rooms-create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sco_id' => ['required', 'string', 'max:64', 'unique:adobe_rooms,sco_id'],
            'url_path' => ['required', 'string', 'max:255', 'unique:adobe_rooms,url_path'],
            'meeting_url' => ['required', 'string', 'max:500'],
            'access_type' => ['required', Rule::enum(AdobeRoomAccessType::class)],
            'passcode' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(AdobeRoomStatus::class)],
        ];
    }
}
