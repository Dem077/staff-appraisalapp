<?php

namespace App\Http\Controllers;

use App\Support\AuthContext;
use App\Support\PermissionPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    protected array $protectedRoles = ['super_admin'];

    public function index(): Response
    {
        abort_unless(AuthContext::can('view_any_role'), 403);

        $roles = Role::query()
            ->withCount('permissions')
            ->orderBy('guard_name')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions_count' => $role->permissions_count,
                'protected' => in_array($role->name, $this->protectedRoles, true),
            ]);

        return Inertia::render('Roles/Index', ['roles' => $roles]);
    }

    public function create(): Response
    {
        abort_unless(AuthContext::can('create_role'), 403);

        return Inertia::render('Roles/Form', [
            'role' => null,
            'rolePermissions' => [],
            'permissionGroups' => [
                'web' => PermissionPresenter::groupedForGuard('web'),
                'staff' => PermissionPresenter::groupedForGuard('staff'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(AuthContext::can('create_role'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_]+$/'],
            'guard_name' => ['required', Rule::in(['web', 'staff'])],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'],
        ]);

        $this->syncPermissions($role, $data['permission_ids'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role): Response
    {
        abort_unless(AuthContext::can('update_role'), 403);

        return Inertia::render('Roles/Form', [
            'role' => $role->only(['id', 'name', 'guard_name']),
            'rolePermissions' => $role->permissions->pluck('id'),
            'protected' => in_array($role->name, $this->protectedRoles, true),
            'permissionGroups' => [
                $role->guard_name => PermissionPresenter::groupedForGuard($role->guard_name),
            ],
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_unless(AuthContext::can('update_role'), 403);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('roles', 'name')->where(fn ($q) => $q->where('guard_name', $role->guard_name))->ignore($role->id),
            ],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        if (! in_array($role->name, $this->protectedRoles, true)) {
            $role->update(['name' => $data['name']]);
        }

        $this->syncPermissions($role, $data['permission_ids'] ?? []);

        return redirect()->route('roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_unless(AuthContext::can('delete_role'), 403);
        abort_if(in_array($role->name, $this->protectedRoles, true), 403, 'This role cannot be deleted.');

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted.');
    }

    protected function syncPermissions(Role $role, array $permissionIds): void
    {
        $permissions = Permission::query()
            ->where('guard_name', $role->guard_name)
            ->whereIn('id', $permissionIds)
            ->pluck('name');

        $role->syncPermissions($permissions);
    }
}
