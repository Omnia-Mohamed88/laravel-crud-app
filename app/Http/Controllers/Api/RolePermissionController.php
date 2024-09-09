<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function assignPermissions(Request $request): JsonResponse
    {
        $role = Role::findByName($request->role_name);
        $permissions = $request->permissions;

        foreach ($permissions as $permission) {
            $permission = Permission::findByName($permission);
            $role->givePermissionTo($permission);
        }

        return response()->json(['message' => 'Permissions assigned successfully']);
    }

    public function revokePermissions(Request $request): JsonResponse
    {
        $role = Role::findByName($request->role_name);
        $permissions = $request->permissions;

        foreach ($permissions as $permission) {
            $permission = Permission::findByName($permission);
            $role->revokePermissionTo($permission);
        }

        return response()->json(['message' => 'Permissions revoked successfully']);
    }
}
