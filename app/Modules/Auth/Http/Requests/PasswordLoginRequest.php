<?php

namespace App\Modules\Auth\Http\Requests;

use App\Modules\Auth\Support\MobileNormalizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PasswordLoginRequest extends FormRequest
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
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $mobile = MobileNormalizer::normalize($this->input('phone'));

            if ($mobile !== '' && ! MobileNormalizer::isValidIranMobile($mobile)) {
                $validator->errors()->add('phone', 'شماره موبایل معتبر نیست (مثال: 09123456789).');
            }
        });
    }

    public function mobile(): string
    {
        return MobileNormalizer::normalize($this->input('phone'));
    }
}
