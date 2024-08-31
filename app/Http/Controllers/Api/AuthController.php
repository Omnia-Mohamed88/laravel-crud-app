<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; 

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\PasswordResetRequest;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use App\Notifications\CustomResetPasswordNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;



class AuthController extends Controller
{
    // Register a new user
    // public function register(RegisterRequest $request)
    // {

    //     $user = User::create([
    //         'name' => $request->input('name'),
    //         'email' => $request->input('email'),
    //         'password' => Hash::make($request->input('password')),
    //     ]);

    //     $user->assignRole('user');

    //     $token = $user->createToken('Personal Access Token')->accessToken;

    //     // return response()->json(['token' => $token], 201);
    //     return response()->json(['user' => $user], 201);

    // }
    // Register a new user
public function register(RegisterRequest $request)
{
    $user = User::create([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'password' => Hash::make($request->input('password')),
    ]);

    $user->assignRole('user');

    $token = $user->createToken('Personal Access Token')->accessToken;

    return response()->json([
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]
    ], 201);
}

    // Login a user
    // public function login(LoginRequest $request)
    // {
    //     $validated = $request->validated();

    //     $credentials = $request->only('email', 'password');

    //     if (!Auth::attempt($credentials)) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $user = Auth::user();
    //     $token = $user->createToken('Personal Access Token')->accessToken;

    //     return response()->json(['token' => $token], 200);
    // }
//     public function login(LoginRequest $request)
// {
//     $validated = $request->validated();

//     $credentials = $request->only('email', 'password');

//     if (!Auth::attempt($credentials)) {
//         return response()->json(['error' => 'Unauthorized'], 401);
//     }

//     $user = Auth::user();
//     $token = $user->createToken('Personal Access Token')->accessToken;

//     // Load roles for the user
//     $roles = $user->getRoleNames(); 

//     return response()->json([
//         'token' => $token,
//         'user' => [
//             'id' => $user->id,
//             'name' => $user->name,
//             'email' => $user->email,
//             'roles' => $roles, 
//             'created_at' => $user->created_at,
//             'updated_at' => $user->updated_at,
//         ]
//     ], 200);
// }


public function login(LoginRequest $request)
{
    $validated = $request->validated();

    $credentials = $request->only('email', 'password');

    if (!Auth::attempt($credentials)) {
        return response()->json(['error' => ['status' => 401, 'message' => 'Unauthorized']], 401);
    }

    $user = Auth::user();
    $token = $user->createToken('Personal Access Token')->accessToken;

    $roles = $user->getRoleNames(); 

    return response()->json([
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $roles, 
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]
    ], 200);
}



    // Logout a user
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

  // Send password reset link
  public function sendResetLinkEmail(Request $request)
{
    $request->validate([
        'email' => 'required|string|email|max:255',
    ]);

    $response = Password::sendResetLink($request->only('email'), function ($user, $token) {
        $user->notify(new CustomResetPasswordNotification($token));
    });

    return $response == Password::RESET_LINK_SENT
                ? response()->json(['message' => 'Password reset link sent'], 200)
                : response()->json(['error' => 'Unable to send password reset link'], 500);
}

  // Reset password
  public function reset(PasswordResetRequest $request)
  {
      // $validated = $request->validated();

      $response = Password::reset($request->only('email', 'token', 'password', 'password_confirmation'), function ($user, $password) {
          $user->password = Hash::make($password);
          $user->save();
      });

      return $response == Password::PASSWORD_RESET
                  ? response()->json(['message' => 'Password has been reset'], 200)
                  : response()->json(['error' => 'Unable to reset password'], 500);
  }
}