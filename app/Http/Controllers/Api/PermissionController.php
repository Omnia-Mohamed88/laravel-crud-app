<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $query = Permission::query();
        if (request()->per_page) {
            $query = $query->paginate(request()->per_page);
        } else {
            $query = $query->get();
        }
        return $this->respond($query, "Permission List");
    }

    public function store(StorePermissionRequest $request): JsonResponse
    {
        $permission = Permission::create($request->validated());
        return $this->respondCreated($permission, 'Permission created successfully.');
    }

    public function show(Permission $permission): JsonResponse
    {
        return $this->respond($permission, 'Permission Data');
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        $permission->update($request->validated());
        return $this->respondCreated($permission, 'Permission updated successfully.');
    }

    public function destroy(Permission $permission): JsonResponse
    {
        $permission->delete();
        return $this->respondSuccess("Permission deleted successfully.");
    }
}
