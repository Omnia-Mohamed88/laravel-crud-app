<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; // Ensure this line is present

use App\Http\Requests\StorePermissionRequest;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PermissionController extends Controller
{
    public function index()
    {
        return response()->json(Permission::all());
    }

    public function store(StorePermissionRequest $request)
    {
        try {
            // Create the permission using the validated data
            $permission = Permission::create(['name' => $request->name]);

            // Return a success response
            return response()->json([
                'success' => true,
                'data' => $permission
            ], 201);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error creating permission: ' . $e->getMessage());

            // Return a general error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Permission $permission)
    {
        return response()->json($permission);
    }

    public function update(StorePermissionRequest $request, Permission $permission)
    {
        try {
            // Validate the request
            $validated = $request->validated();

            // Update the permission with the validated data
            $permission->update(['name' => $validated['name']]);

            // Return a success response
            return response()->json($permission);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error updating permission: ' . $e->getMessage());

            // Return a general error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Permission $permission)
    {
        try {
            $permission->delete();

            return response()->json(['message' => 'Permission deleted']);

        } catch (\Exception $e) {
            // Log the error
            Log::error('Error deleting permission: ' . $e->getMessage());

            // Return a general error response
            return response()->json([
                'success' => false,
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
