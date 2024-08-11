<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; 

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        return response()->json(Role::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        $role = Role::create(['name' => $request->name]);

        return response()->json($role, 201);
    }

    public function show(Role $role)
    {
        return response()->json($role);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $request->name]);

        return response()->json($role);
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json(['message' => 'Role deleted']);
    }

    public function addPermission(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->givePermissionTo($request->permissions);

        return response()->json(['message' => 'Permissions added to role']);
    }

    public function removePermission(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->revokePermissionTo($request->permissions);

        return response()->json(['message' => 'Permissions removed from role']);
    }
}
