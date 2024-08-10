<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    // Allow all users to access this request (or implement custom authorization logic)
    public function authorize()
    {
        return true;
    }

    // Define the validation rules
    public function rules()
    {
        return [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ];
    }

    // Define custom error messages
    public function messages()
    {
        return [
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
        ];
    }

    // Handle validation failure
    protected function failedValidation(Validator $validator)
    {
        // Throw a custom HTTP response exception
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
        ], 422)); // 422 Unprocessable Entity
    }
}

