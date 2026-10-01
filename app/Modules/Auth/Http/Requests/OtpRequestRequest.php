<?php

namespace App\Modules\Auth\Http\Requests;

use App\Modules\Auth\Support\MobileNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class OtpRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:20'],
        ];
    }

    public function mobile(): string
    {
        return MobileNormalizer::normalize($this->input('phone'));
    }
}
