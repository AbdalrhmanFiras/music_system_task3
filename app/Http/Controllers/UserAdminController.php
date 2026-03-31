<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use App\Http\Requests\RoleRequest;
use App\Http\Requests\UpdatePermissionRequet;
use App\Http\Requests\UpdateRoleRequet;
use App\Http\Resources\RoleResource;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:user_admin');
    }

    public function find($userId)
    {
        $user = User::find($userId);
        if (! $user) {
            return abort(404, 'user not found');
        }

        return $user;
    }

    public function getUserRole($userId)
    {
        $user = User::with('roles.permissions')->find($userId);
        if (! $user) {
            return abort(404, 'user not found');
        }

        return RoleResource::collection($user->roles);
    }

    public function assignRole(RoleRequest $request, $userId)
    {
        $user = $this->find($userId);
        $role = Role::findByName($request->validated('role'));
        $user->assignRole($role);

        return response()->json(['message' => 'Role added to user'], 200);
    }

    public function assignPermission(PermissionRequest $request, $userId)
    {
        $user = $this->find($userId);
        $permission = Permission::findByName($request->validated('permission'));
        $user->givePermissionTo($permission);

        return response()->json(['message' => 'Permission added to user'], 200);
    }

    public function updatePermission(UpdatePermissionRequet $request, $userId)
    {
        $user = $this->find($userId);

        $user->syncPermissions($request->validated('permissions'));

        return response()->json(['message' => 'Permission updated successfully'], 200);
    }

    public function updateRole(UpdateRoleRequet $request, $userId)
    {
        $user = $this->find($userId);

        $user->syncRoles($request->validated('role'));

        return response()->json(['message' => 'Role updated successfully'], 200);
    }
}
