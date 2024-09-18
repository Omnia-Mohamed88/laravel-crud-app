<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class UserController extends Controller
{
    // public function index()
    // {
    //     $users = User::all();
    //     return UserResource::collection($users);
    // }
    public function index(): JsonResponse
    {
        $query = User::query();
        if (request()->per_page) {
            $query = $query->paginate(request()->per_page);
        } else {
            $query = $query->get();
        }
        $data = UserResource::collection($query);
        return $this->respondForResource($data, "user List");
    }

  
    public function store(StoreUserRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = User::create($request->validated());
            $role = $request->validated()['role'] ?? 'user';
            $user->assignRole($role);
            DB::commit();

        } catch (Exception $e) {
            info($e);
            DB::rollback();
            return $this->respondError($e->getMessage(), "error in creating user");
        }
        return $this->respondCreated($user, "User Created Successfully");
    }

    public function show(User $user): JsonResponse
    {
        return $this->respondForResource(UserResource::make($user), 'user Data');
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        DB::beginTransaction();
        try {
            if ($request->has('password')) {
                $request["password"] = bcrypt($request->password);
            }

            $user->update($request->validated());

            if ($request->has('role')) {
                $user->syncRoles($request->validated()['role']);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            return $this->respondError($e->getMessage());
        }

        return $this->respondForResource(UserResource::make($user), 'user Data');
    }


    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return $this->respondSuccess("User deleted successfully.");
    }

    public function profile(): JsonResponse
    {
        $user = auth()->user();
        $role =  $user->roles()->first();
        $user["role_id"] = $role->id;
        $user["role_name"] = $role->name;
        return $this->respond($user,"User Data");
    }

}
