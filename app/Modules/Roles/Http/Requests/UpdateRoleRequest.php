<?php

namespace App\Modules\Roles\Http\Requests;

use App\Modules\Roles\Models\Role;
use App\Modules\Roles\Support\PermissionCatalog;
use App\Modules\Roles\Support\RoleGuard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->route('role');

        if (! $role instanceof Role) {
            return false;
        }

        $user = $this->user();

        return $user !== null && RoleGuard::canUpdate($user, $role);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $allowed = PermissionCatalog::allSlugs();

        return [
            'permissions' => ['array'],
            'permissions.*' => ['string', Rule::in($allowed)],
        ];
    }
}
