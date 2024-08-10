<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreUpdateUserRequest;
use App\Http\Requests\AssignRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function assignRoleToUser(AssignRoleRequest $request, $userId)
    {
        // Ensure the user is authenticated and authorized
        if (!Auth::user()->hasRole('superadmin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
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
   
    public function store(StoreUpdateUserRequest $request)
{
    // Default role is 'user'
    $role = $request->input('role', 'user');

    $user = User::create([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'password' => Hash::make($request->input('password')),
        'role' => $role, // Set the role
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

    public function update(StoreUpdateUserRequest $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
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
