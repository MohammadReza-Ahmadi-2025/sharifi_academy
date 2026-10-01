<?php

namespace App\Modules\Profile\Http\Requests;

use App\Modules\Auth\Support\MobileNormalizer;
use App\Modules\Profile\Support\IranNationalCode;
use App\Modules\Users\Support\UserAvatarMedia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
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

        if ($this->input('national_code') === '') {
            $this->merge(['national_code' => null]);
        }

        if ($this->input('province_city_id') === '' || $this->input('province_city_id') === null) {
            $this->merge(['province_city_id' => null]);
        }

        if ($this->has('is_public')) {
            $this->merge(['is_public' => $this->boolean('is_public')]);
        }
    }

    public function withValidator(Validator $validator): void
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

            $nationalCode = $this->input('national_code');
            if (is_string($nationalCode) && $nationalCode !== '' && ! IranNationalCode::isValid($nationalCode)) {
                $validator->errors()->add('national_code', 'کد ملی معتبر نیست.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;
        $profileId = $this->user()?->profile?->id;

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
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'national_code' => [
                'nullable',
                'string',
                'size:10',
                Rule::unique('user_profiles', 'national_code')->ignore($profileId),
            ],
            'province_city_id' => [
                'nullable',
                'integer',
                Rule::exists('province_cities', 'id')->whereNotNull('parent_id'),
            ],
            'is_public' => ['sometimes', 'boolean'],
            ...UserAvatarMedia::validationRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'نام',
            'family' => 'نام خانوادگی',
            'mobile' => 'شماره موبایل',
            'email' => 'ایمیل',
            'gender' => 'جنسیت',
            'avatar' => 'تصویر آواتار',
            'avatar_alt' => 'متن جایگزین آواتار',
            'avatar_title' => 'عنوان آواتار',
            'headline' => 'تیتر حرفه‌ای',
            'bio' => 'بیوگرافی',
            'birth_date' => 'تاریخ تولد',
            'national_code' => 'کد ملی',
            'province_city_id' => 'شهر',
            'is_public' => 'نمایش عمومی پروفایل',
        ];
    }
}
