<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:user_admin');
    }

    public function find($roleId)
    {

        $role = Role::find($roleId);
        if (! $role) {
            return abort(404, 'Role not found');
        }

        return $role;
    }

    public function index()
    {
        $roles = Role::paginate(10);

        return RoleResource::collection($roles);
    }

    public function store(StoreRoleRequest $request)
    {
        $data = $request->validated();
        $role = Role::create(['name' => $data['name']]);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->validated()['permissions']);
        }

        return response()->json(['message' => 'Role created successfully'], 201);
    }

    public function show($roleId)
    {
        $role = Role::with('permissions')->find($roleId);
        if (! $role) {
            return abort(404, 'role not found');
        }

        return response()->json(['data' => new RoleResource($role)], 200);
    }

    public function update(UpdateRoleRequest $request, $roleId)
    {
        $role = $this->find($roleId);
        $data = $request->validated();
        if (isset($data['name'])) {
            $role->update(['name' => $data['name']]);
        }
        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return response()->json(['message' => 'Role updated successfully'], 200);
    }

    public function delete($roleId)
    {
        $this->find($roleId)->delete();

        return response()->json(['message' => 'Role deleted successfully'], 200);
    }
}
