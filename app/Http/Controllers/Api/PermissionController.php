<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PermissionController extends Controller
{
    public function index() : JsonResponse
    {
        $query = Permission::query();
        if (request()->per_page) {
            $query = $query->paginate(request()->per_page);
        } else {
            $query = $query->get();
        }
        return $this->respond($query, "Permission List");
    }

    public function store(StorePermissionRequest $request) : JsonResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $permission = Permission::create($validated); 
            DB::commit();
            return $this->respondCreated($permission, 'Permission created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondError($e->getMessage(), "Failed to store Permission");
        }
    }

    public function show(Permission $permission) : JsonResponse
    {
        return $this->respond($permission, 'Permission Data');
    }

    public function update(UpdatePermissionRequest $request, Permission $permission) : JsonResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $permission->update($validated); 
            DB::commit();
            return $this->respondCreated($permission, 'Permission updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondError($e->getMessage(), "Failed to update Permission");
        }
    }

    public function destroy(Permission $permission) : JsonResponse
    {
        DB::beginTransaction();
        try {
            $permission->delete();
            DB::commit();
            return $this->respondSuccess("Permission deleted successfully.");
        } catch (\Exception $e) {
            DB::rollback();
            return $this->respondError($e->getMessage(), "Failed to delete the Permission.");
        }
    }
}