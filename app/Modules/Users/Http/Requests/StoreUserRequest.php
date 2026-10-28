<?php

namespace App\Modules\Users\Http\Requests;

use App\Modules\Users\Http\Requests\Concerns\ValidatesUserFields;
use App\Modules\Users\Support\UserGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreUserRequest extends FormRequest
{
    use ValidatesUserFields;

    public function authorize(): bool
    {
        return $this->user()?->can('users-create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->prepareUserFieldsForValidation();
    }

    public function withValidator(Validator $validator): void
    {
        $this->withUserMobileValidator($validator);

        $validator->after(function (Validator $validator): void {
            $actor = $this->user();
            if ($actor === null) {
                return;
            }

            $roles = $this->input('roles', []);
            if (! is_array($roles)) {
                return;
            }

            $filtered = UserGuard::filterAssignableRoles($actor, $roles);
            if ($filtered === [] && $roles !== []) {
                $validator->errors()->add('roles', 'نقش انتخاب‌شده مجاز نیست.');
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $allowedRoles = UserGuard::assignableRoles($this->user())->pluck('name')->all();

        return [
            ...$this->baseUserRules(),
            ...$this->avatarRules(),
            'roles.*' => ['string', Rule::in($allowedRoles)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->userFieldAttributes();
    }
}
