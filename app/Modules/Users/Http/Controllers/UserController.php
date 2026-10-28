<?php

namespace App\Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Roles\Models\Role;
use App\Modules\Roles\Support\PermissionCatalog;
use App\Modules\Users\Http\Requests\StoreUserRequest;
use App\Modules\Users\Http\Requests\UpdateUserRequest;
use App\Modules\Users\Support\UserAvatarMedia;
use App\Modules\Users\Support\UserGuard;
use App\Modules\Users\Support\UserPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('users-index'), 403);

        $actor = $request->user();
        $search = trim((string) $request->query('search', ''));
        $role = trim((string) $request->query('role', ''));
        $status = $request->query('status');
        $perPage = (int) $request->query('per_page', 25);
        $perPage = in_array($perPage, [25, 50, 75, 100], true) ? $perPage : 25;

        $query = User::query()
            ->with(['roles', 'media'])
            ->orderByDesc('created_at');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('family', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role !== '') {
            $query->whereHas('roles', fn ($builder) => $builder->where('name', $role));
        }

        if ($status !== null && $status !== '' && in_array((int) $status, [0, 1], true)) {
            $query->where('status', (int) $status);
        }

        $paginator = $query->paginate($perPage)->withQueryString();

        $users = $paginator->through(fn (User $user) => UserPresenter::forList($user, $actor));

        $roleFilters = Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'value' => $role->name,
                'label' => PermissionCatalog::labelForRole($role->name),
            ])
            ->values()
            ->all();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'role' => $role,
                'status' => $status === null || $status === '' ? '' : (string) (int) $status,
                'per_page' => $perPage,
            ],
            'roleFilters' => $roleFilters,
            'abilities' => [
                'create' => $actor?->can('users-create') ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->can('users-create'), 403);

        $actor = $request->user();

        return Inertia::render('Admin/Users/Create', [
            'roleOptions' => UserPresenter::roleOptions($actor),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $roles = UserGuard::filterAssignableRoles($actor, $request->validated('roles', []));
        $data = $request->safe()->except([
            'roles',
            'password',
            'avatar',
            'avatar_alt',
            'avatar_title',
            'remove_avatar',
        ]);

        $user = new User($data);
        $user->password = Str::password(32);
        $user->save();

        $user->syncRoles($roles);
        UserAvatarMedia::syncFromRequest($user, $request);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'کاربر جدید ایجاد شد.');
    }

    public function edit(Request $request, User $user): Response
    {
        $actor = $request->user();
        abort_unless($actor && UserGuard::canUpdate($actor, $user), 403);

        $user->load(['roles', 'media']);

        return Inertia::render('Admin/Users/Edit', [
            'user' => UserPresenter::forList($user, $actor),
            'roleOptions' => UserPresenter::roleOptions($actor),
            'selectedRoles' => $user->getRoleNames()->values()->all(),
            'abilities' => [
                'update' => true,
                'delete' => UserGuard::canDelete($actor, $user),
            ],
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        $roles = UserGuard::filterAssignableRoles($actor, $request->validated('roles', []));
        $data = $request->safe()->except([
            'roles',
            'password',
            'avatar',
            'avatar_alt',
            'avatar_title',
            'remove_avatar',
        ]);

        $mobileChanged = isset($data['mobile']) && $data['mobile'] !== $user->mobile;
        $emailChanged = array_key_exists('email', $data) && $data['email'] !== $user->email;

        $user->fill($data);

        if ($mobileChanged) {
            $user->mobile_verified_at = null;
        }

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();
        $user->syncRoles($roles);
        UserAvatarMedia::syncFromRequest($user, $request);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', 'کاربر به‌روزرسانی شد.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        abort_unless($actor && UserGuard::canDelete($actor, $user), 403);

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'کاربر حذف شد.');
    }
}
