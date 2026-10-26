<?php

namespace App\Modules\AdobeConnect\Http\Requests\Admin;

use App\Modules\AdobeConnect\Models\AdobeRoom;
use Illuminate\Foundation\Http\FormRequest;

class SyncAdobeRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $room = $this->route('adobeRoom');

        return $room instanceof AdobeRoom
            && ($this->user()?->can('update', $room) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
