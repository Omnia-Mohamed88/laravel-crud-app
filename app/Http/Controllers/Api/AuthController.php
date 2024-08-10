<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; // Ensure this line is present

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\PasswordResetRequest;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    // Register a new user
    public function register(RegisterRequest $request)
    {
        // If validation fails, the response will be handled by the Form Request class

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        $token = $user->createToken('Personal Access Token')->accessToken;

        // return response()->json(['token' => $token], 201);
        return response()->json(['user' => $user], 201);

    }
    // Login a user
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('Personal Access Token')->accessToken;

        return response()->json(['token' => $token], 200);
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

        $response = Password::sendResetLink($request->only('email'));

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
