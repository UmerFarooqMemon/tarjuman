<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->registerCrudPermissions('roles');
    }

    public function index()
    {
        $guard = config('admin_permissions.guard', 'admin');
        $roles = Role::where('guard_name', $guard)
            ->withCount('users')
            ->with('permissions')
            ->orderBy('name')
            ->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $groupedPermissions = $this->groupedPermissions();

        return view('admin.roles.create', compact('groupedPermissions'));
    }

    public function store(StoreRoleRequest $request)
    {
        $guard = config('admin_permissions.guard', 'admin');

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => $guard,
        ]);

        $role->syncPermissions($this->permissionsWithRequiredView($request->input('permissions', [])));
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', __('general.role_has_been_created_successfully'));
    }

    public function edit($id)
    {
        $guard = config('admin_permissions.guard', 'admin');
        $role = Role::where('guard_name', $guard)->findOrFail($id);
        $groupedPermissions = $this->groupedPermissions();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('admin.roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        $guard = config('admin_permissions.guard', 'admin');
        $role = Role::where('guard_name', $guard)->findOrFail($id);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($this->permissionsWithRequiredView($request->input('permissions', [])));
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', __('general.permissions_has_been_synced_with_role'));
    }

    public function destroy($id)
    {
        $guard = config('admin_permissions.guard', 'admin');
        $role = Role::where('guard_name', $guard)->findOrFail($id);

        if ($role->name === config('admin_permissions.default_role', 'Super Admin')) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', __('general.you_can_not_delete_default_role'));
        }

        if ($role->users()->count() > 0) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', __('general.role_is_assigned_to_admins'));
        }

        $role->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', __('general.role_was_removed_successfully'));
    }

    /**
     * Ensure module.view is included whenever create/edit/delete is assigned.
     *
     * @param  array<int, string>  $permissions
     * @return array<int, string>
     */
    protected function permissionsWithRequiredView(array $permissions): array
    {
        $dependentActions = ['create', 'edit', 'delete'];
        $permissions = array_values(array_unique($permissions));

        foreach ($permissions as $permission) {
            if (! str_contains($permission, '.')) {
                continue;
            }

            [$module, $action] = explode('.', $permission, 2);

            if (in_array($action, $dependentActions, true)) {
                $viewPermission = "{$module}.view";

                if (! in_array($viewPermission, $permissions, true)) {
                    $permissions[] = $viewPermission;
                }
            }
        }

        return $permissions;
    }

    /**
     * @return array<string, \Illuminate\Support\Collection<int, \App\Models\Permission>>
     */
    protected function groupedPermissions(): array
    {
        $guard = config('admin_permissions.guard', 'admin');
        $modules = config('admin_permissions.modules', []);
        $permissions = Permission::where('guard_name', $guard)->get()->keyBy('name');
        $grouped = [];

        foreach ($modules as $module => $actions) {
            $grouped[$module] = collect($actions)
                ->map(fn (string $action) => $permissions->get("{$module}.{$action}"))
                ->filter();
        }

        return $grouped;
    }
}
