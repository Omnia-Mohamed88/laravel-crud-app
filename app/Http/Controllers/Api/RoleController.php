<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function index() : JsonResponse
    {
        $query = Role::query();
        if (request()->per_page) {
            $query = $query->paginate(request()->per_page);
        } else {
            $query = $query->get();
        }
        return $this->respond($query, "Role List");
    }

    public function store(StoreRoleRequest $request) : JsonResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $role = Role::create($validated);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondError($e->getMessage(), "Failed to store Role");
        }
        return $this->respondCreated($role, 'Role created successfully.');
    }

    public function show(Role $role) : JsonResponse
    {
        return $this->respond($role, 'Role Data');
    }

    public function update(UpdateRoleRequest $request, Role $role) : JsonResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $role->update($validated);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondError($e->getMessage(), "Failed to update Role");
        }
        return $this->respondCreated($role, 'Role updated successfully.');
    }
    

    public function destroy(Role $role) : JsonResponse
    {
        DB::beginTransaction();
        try {
            $role->delete();
            DB::commit();
            return $this->respondSuccess("Role deleted successfully.");
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondError($e->getMessage(), "Failed to delete the Role.");
        }
    }

    public function addPermission(Request $request, Role $role) : JsonResponse
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->givePermissionTo($validated['permissions']);

        return $this->respondSuccess("Permissions added to the role successfully.");
    }

    public function removePermission(Request $request, Role $role) : JsonResponse
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role->revokePermissionTo($validated['permissions']);

        return $this->respondSuccess("Permissions removed from the role successfully.");
    }
}
