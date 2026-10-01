<?php

namespace App\Modules\Users\Http\Requests\Concerns;

use App\Modules\Auth\Support\MobileNormalizer;
use App\Modules\Users\Support\UserAvatarMedia;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

trait ValidatesUserFields
{
    protected function prepareUserFieldsForValidation(): void
    {
        if ($this->input('gender') === '') {
            $this->merge(['gender' => null]);
        }

        if ($this->has('email') && $this->input('email') === '') {
            $this->merge(['email' => null]);
        }

        if ($this->has('mobile')) {
            $this->merge([
                'mobile' => MobileNormalizer::normalize($this->input('mobile')),
            ]);
        }

        if ($this->has('remove_avatar')) {
            $this->merge([
                'remove_avatar' => $this->boolean('remove_avatar'),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function avatarRules(): array
    {
        return UserAvatarMedia::validationRules();
    }

    public function withUserMobileValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $mobile = $this->input('mobile');

            if (! is_string($mobile) || $mobile === '') {
                return;
            }

            if (! MobileNormalizer::isValidIranMobile($mobile)) {
                $validator->errors()->add(
                    'mobile',
                    'شماره موبایل معتبر نیست (مثال: 09123456789).',
                );
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function baseUserRules(?int $userId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'family' => ['nullable', 'string', 'max:120'],
            'mobile' => [
                'required',
                'string',
                'size:11',
                Rule::unique('users', 'mobile')->ignore($userId),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'status' => ['required', 'integer', Rule::in([0, 1])],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'max:64'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function userFieldAttributes(): array
    {
        return [
            'name' => 'نام',
            'family' => 'نام خانوادگی',
            'mobile' => 'شماره موبایل',
            'email' => 'ایمیل',
            'gender' => 'جنسیت',
            'status' => 'وضعیت',
            'roles' => 'نقش‌ها',
            'password' => 'رمز عبور',
            'avatar' => 'تصویر آواتار',
            'avatar_alt' => 'متن جایگزین آواتار',
            'avatar_title' => 'عنوان آواتار',
        ];
    }
}
