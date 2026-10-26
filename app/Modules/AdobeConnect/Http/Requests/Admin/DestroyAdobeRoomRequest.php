<?php

namespace App\Modules\AdobeConnect\Http\Requests\Admin;

use App\Modules\AdobeConnect\Models\AdobeRoom;
use Illuminate\Foundation\Http\FormRequest;

class DestroyAdobeRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        $room = $this->route('adobeRoom');

        return $room instanceof AdobeRoom
            && ($this->user()?->can('delete', $room) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
