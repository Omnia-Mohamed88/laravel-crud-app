<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\PasswordResetRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use App\Notifications\CustomResetPasswordNotification;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SendResetLinkEmailNewRequest;
use Illuminate\Support\Str;
use App\Models\EmailToken;
use App\Http\Requests\ResetNewRequest;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = User::create($request->validated());
            $user->assignRole('user');
            $user["token"] = $user->createToken('Personal Access Token')->accessToken;
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            return $this->respondError($e->getMessage(), "error in registration");
        }

        return $this->respondCreated($user, "User Created Successfully");
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::whereEmail($request->email)->first();
        if ($user && Hash::check($request->password, $user->password)) {
            $user["token"] = $user->createToken('Personal Access Token')->accessToken;
            $role =  $user->roles()->first();
            $user["role_id"] =$role->id;
            $user["role_name"] = $role->name;
            return $this->respond($user, 'Logged in Successfully!');
        }
        $errors = [
            'credentials' => 'Invalid email or password. Please check your credentials and try again.'
        ];
        return $this->respondError($errors, "Login Failed!",422);

    }

    public function logout(): JsonResponse
    {
        auth()->user()->tokens()->delete();
        return $this->respondSuccess("Logged out successfully");
    }

    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
        ]);

        $response = Password::sendResetLink($request->only('email'), function ($user, $token) {
            $user->notify(new CustomResetPasswordNotification($token));
        });
        return $response == Password::RESET_LINK_SENT
            ? $this->respondSuccess("Password reset link sent")
            : $this->respondError("Unable to send password reset link");
    }

    public function reset(PasswordResetRequest $request): JsonResponse
    {
        $response = Password::reset($request->only('email', 'token', 'password', 'password_confirmation'), function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        });
        return $response == Password::PASSWORD_RESET
            ? $this->respondSuccess("Password has been reset")
            : $this->respondError("Unable to reset password");
    }

    public function sendResetLinkEmailNew(SendResetLinkEmailNewRequest $request): JsonResponse
    {
        $token = rand(1111, 9999) . Str::random(10);
        EmailToken::create(["email" => $request->email, "token" => $token]);

        Mail::raw(env("FRONTEND_URL") . '/reset-password?token=' . $token, function ($message) use ($request) {
            $message->to($request->email)->subject('Reset Your Password!');
        });

        return $this->respondSuccess("Mail sent successfully");

    }

    public function resetNew(ResetNewRequest $request): JsonResponse
    {
        $email = EmailToken::whereToken($request->token)->first()->email;
        User::whereEmail($email)->first()->update([
            "password" => bcrypt($request->password)
        ]);
        return $this->respondSuccess("Password has been reset successfully");

    }
    public function verifyResetToken(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'token' => 'required|string|exists:emails_tokens,token',
            ]);
    
            return $this->respondSuccess("Token is valid", 200);
    
        } catch (Exception $e) {
            return $this->respondError($e->getMessage(), "invalid token");
    }
    
}
}