<?php

namespace App\Modules\Roles\Http\Requests;

use App\Modules\Roles\Support\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('roles-create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $allowed = PermissionCatalog::allSlugs();

        return [
            'name' => [
                'required',
                'string',
                'max:64',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('roles', 'name')->where('guard_name', 'web'),
            ],
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in($allowed)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'نام نقش فقط حروف کوچک لاتین، عدد و خط تیره باشد (مثال: content-editor).',
            'name.unique' => 'این نام نقش قبلاً ثبت شده است.',
        ];
    }
}
