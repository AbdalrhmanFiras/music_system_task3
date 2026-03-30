<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:user_admin');
    }

    public function find($permissionId)
    {
        $per = Permission::find($permissionId);
        if (! $per) {
            return abort(404, 'Permission not found');
        }

        return $per;
    }

    public function index()
    {
        $permissions = Permission::paginate(10);

        return PermissionResource::collection($permissions);
    }

    public function store(StorePermissionRequest $request)
    {
        Permission::create($request->validated());

        return response()->json(['message' => 'Permission created successfully'], 201);
    }

    public function show($permissionId)
    {
        $permission = $this->find($permissionId);

        return response()->json(['data' => new PermissionResource($permission)], 200);
    }

    public function update(UpdatePermissionRequest $request, $permissionId)
    {
        $this->find($permissionId)->update($request->validated());

        return response()->json(['message' => 'Permission updated successfully'], 200);
    }

    public function delete($permissionId)
    {
        $this->find($permissionId)->delete();

        return response()->json(['message' => 'Permission deleted successfully'], 200);
    }
}
