<?php

namespace App\Modules\Users\Http\Requests;

use App\Models\User;
use App\Modules\Users\Http\Requests\Concerns\ValidatesUserFields;
use App\Modules\Users\Support\UserGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    use ValidatesUserFields;

    public function authorize(): bool
    {
        $user = $this->route('user');
        $actor = $this->user();

        return $user instanceof User
            && $actor instanceof User
            && UserGuard::canUpdate($actor, $user);
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
        $user = $this->route('user');
        $userId = $user instanceof User ? $user->id : null;
        $allowedRoles = UserGuard::assignableRoles($this->user())->pluck('name')->all();

        return [
            ...$this->baseUserRules($userId),
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
