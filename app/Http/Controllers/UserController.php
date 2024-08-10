<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; // Ensure this is imported
use App\Http\Resources\UserResource;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function assignRoleToUser(Request $request, $userId)
    {
        // Ensure the user is authenticated and authorized
        if (!Auth::user()->hasRole('superadmin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        // Validate request data
        $request->validate([
            'role' => 'required|string', // Adjust validation if necessary
        ]);
    
        // Find the user by ID
        $user = User::findOrFail($userId);
    
        // Update the role directly
        $user->role = $request->role;
        $user->save();
    
        return response()->json(['message' => 'Role assigned successfully']);
    }
    
    public function index()
    {
        $users = User::all();
        return UserResource::collection($users);
    }
   
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        return new UserResource($user);
    }

    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return new UserResource($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user->update($request->only(['name', 'email']));

        if ($request->has('password')) {
            $user->password = Hash::make($request->input('password'));
            $user->save();
        }

        return new UserResource($user);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }
}
