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
use Illuminate\Validation\ValidationException;
use App\Http\Requests\SendResetLinkEmailNewRequest;
use Illuminate\Support\Str;
use App\Models\EmailToken;
use App\Http\Requests\ResetNewRequest;
use Mail;

class AuthController extends Controller
{
    // Register a new user
    public function register(RegisterRequest $request)
    {
        \DB::beginTransaction();
        try{
            $user = User::create($request->validated());   
            $user->assignRole('user');
            $user["token"] = $user->createToken('Personal Access Token')->accessToken;
            \DB::commit();
        }catch(\Exception $e){
            \DB::rollback();
            return response()->json(["data" => "error in registration","error" => $e->getMessage()],400);
        }

        // return response()->json([
        //     "message" => "User Created Successfully.",
        //     'data' => $user
        // ], 201);

        return $this->respondCreated($user,"User Created Successfully");
    }



    public function login(LoginRequest $request)
    {
    $user = User::whereEmail($request->email)->first();
    if($user && Hash::check($request->password,$user->password))
    {
        $user["token"] = $user->createToken('Personal Access Token')->accessToken;
        $user["roles"] = $user->getRoleNames();

        return $this->respond($user,'Logged in Successfully!');
        
    }

    // return response()->json([
    //     'error' => [
    //         'message' => 'Invalid email or password. Please check your credentials and try again.'
    //     ]
    // ], 400);

    $errors = [
        'credentials' => 'Invalid email or password. Please check your credentials and try again.'
    ];

    return $this->respondError($errors,"Login Failed!");

    }
    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
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

    public function sendResetLinkEmailNew(SendResetLinkEmailNewRequest $request)
    {
    $token = rand(1111,9999).Str::random(10);
    EmailToken::create(["email" => $request->email ,"token" =>  $token]);

    Mail::raw(env("FRONTEND_URL").'/reset-password?token='.$token, function ($message) use($request) {
        $message->to($request->email)->subject('Reset Your Password!');
        });

    return response()->json(["message" => "Mail sent successfully"]);
    }

    public function resetNew(ResetNewRequest $request)
    {

    $email = EmailToken::whereToken($request->token)->first()->email;

    User::whereEmail($email)->first()->update([
        "password" => bcrypt($request->password)
    ]);

    return response()->json(["message" => "Password has been reset successfully"]);
    }
}