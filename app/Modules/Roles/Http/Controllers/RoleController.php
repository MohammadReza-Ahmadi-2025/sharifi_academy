<?php

namespace App\Modules\Roles\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Roles\Models\Role;
use App\Models\User;
use App\Modules\Roles\Http\Requests\StoreRoleRequest;
use App\Modules\Roles\Http\Requests\UpdateRoleRequest;
use App\Modules\Roles\Support\PermissionCatalog;
use App\Modules\Roles\Support\RoleGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('roles-index'), 403);

        $search = trim((string) $request->query('search', ''));
        $user = $request->user();

        $query = Role::query()
            ->where('guard_name', 'web')
            ->withCount(['users', 'permissions'])
            ->orderBy('name');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%");
            });
        }

        $roles = $query->get()->map(fn (Role $role) => $this->transformRole($role, $user));

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'filters' => [
                'search' => $search,
            ],
            'abilities' => [
                'create' => $user?->can('roles-create') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('roles-create'), 403);

        return Inertia::render('Admin/Roles/Create', [
            'permissionGroups' => PermissionCatalog::groupsForUi(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $user = $request->user();
        $permissions = RoleGuard::filterAssignablePermissions(
            $user,
            $request->validated('permissions', []),
        );

        $role = Role::query()->create([
            'name' => $request->validated('name'),
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($permissions);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'نقش جدید ایجاد شد.');
    }

    public function edit(Request $request, Role $role): Response
    {
        abort_unless($role->guard_name === 'web', 404);

        $user = $request->user();
        abort_unless($user && RoleGuard::canUpdate($user, $role), 403);

        $role->load('permissions');

        return Inertia::render('Admin/Roles/Edit', [
            'role' => $this->transformRole($role, $user),
            'permissionGroups' => PermissionCatalog::groupsForUi(),
            'selectedPermissions' => $role->permissions->pluck('name')->values()->all(),
            'abilities' => [
                'update' => true,
                'delete' => RoleGuard::canDelete($user, $role),
            ],
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        abort_unless($role->guard_name === 'web', 404);

        $user = $request->user();
        $permissions = RoleGuard::filterAssignablePermissions(
            $user,
            $request->validated('permissions', []),
        );

        $role->syncPermissions($permissions);

        return redirect()
            ->route('admin.roles.edit', $role)
            ->with('success', 'نقش به‌روزرسانی شد.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        abort_unless($role->guard_name === 'web', 404);

        $user = $request->user();
        abort_unless($user && RoleGuard::canDelete($user, $role), 403);

        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'نقش حذف شد.');
    }

    /**
     * @return array<string, mixed>
     */
    private function transformRole(Role $role, ?User $user): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'label' => PermissionCatalog::labelForRole($role->name),
            'usersCount' => $role->users_count ?? $role->users()->count(),
            'permissionsCount' => $role->permissions_count ?? $role->permissions()->count(),
            'isSystem' => RoleGuard::isSystemRole($role->name),
            'canUpdate' => $user ? RoleGuard::canUpdate($user, $role) : false,
            'canDelete' => $user ? RoleGuard::canDelete($user, $role) : false,
        ];
    }
}
