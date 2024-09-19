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
use OpenApi\Annotations as OA;


class AuthController extends Controller
{
    /**
 * @OA\Post(
 *     path="/api/register",
 *     summary="User registration",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="client"),
 *             @OA\Property(property="email", type="string", example="client@example.com"),
 *             @OA\Property(property="password", type="string", example="password"),
 *             @OA\Property(property="password_confirmation", type="string", example="password")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="User Created Successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="name", type="string", example="client"),
 *                 @OA\Property(property="email", type="string", example="client@example.com"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-18T08:08:27.847000Z"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-18T08:08:27.847000Z"),
 *                 @OA\Property(property="id", type="integer", example=4),
 *                 @OA\Property(property="role_id", type="integer", example=3),
 *                 @OA\Property(property="role_name", type="string", example="user"),
 *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Invalid input")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Server error")
 *         )
 *     )
 * )
 */

    public function register(RegisterRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = User::create($request->validated());
            $user->assignRole('user');
            $role =  $user->roles()->first();
            $user["role_id"] =$role->id;
            $user["role_name"] = $role->name;
            $user["token"] = $user->createToken('Personal Access Token')->accessToken;
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            return $this->respondError($e->getMessage(), "error in registration");
        }

        return $this->respondCreated($user, "User Created Successfully");
    }
  /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="User login",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="userpassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Server error")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Personal Access Token')->accessToken;
                $role = $user->roles()->first();

                $user["role_id"] = $role->id;
                $user["role_name"] = $role->name;
                $user["token"] = $token;

                return $this->respond($user, 'Logged in Successfully!');
            }

            return $this->respondError(
                ['credentials' => 'Invalid email or password. Please check your credentials and try again.'],
                "Login Failed!",
                422
            );
        } catch (Exception $e) {
            return $this->respondError(
                ['error' => 'An error occurred while trying to log in. Please try again later.'],
                'Login Failed!',
                500
            );
        }}
/**
 * @OA\Post(
 *     path="/api/logout",
 *     summary="User logout",
 *     tags={"Auth"},
 *     security={{"bearerAuth": {}}},
 *     @OA\Response(
 *         response=200,
 *         description="Logout successful",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Logged out successfully.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthorized")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Server error")
 *         )
 *     )
 * )
 */
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
/**
 * @OA\Post(
 *     path="/api/new-password/email",
 *     summary="Send a password reset link",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Password reset link sent successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Mail sent successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Invalid input")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Server error")
 *         )
 *     )
 * )
 */
    public function sendResetLinkEmailNew(SendResetLinkEmailNewRequest $request): JsonResponse
    {
        $token = rand(1111, 9999) . Str::random(10);
        EmailToken::create(["email" => $request->email, "token" => $token]);

        Mail::raw(env("FRONTEND_URL") . '/reset-password?token=' . $token, function ($message) use ($request) {
            $message->to($request->email)->subject('Reset Your Password!');
        });

        return $this->respondSuccess("Mail sent successfully");

    }
/**
 * @OA\Post(
 *     path="/api/new-password/reset",
 *     summary="Reset password using a new token",
 *     tags={"Auth"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="token", type="string", example="1234abcd5678efgh"),
 *             @OA\Property(property="password", type="string", format="password", example="newpassword"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Password has been reset successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Password has been reset successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input or token",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Invalid input or token")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Server error")
 *         )
 *     )
 * )
 */
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