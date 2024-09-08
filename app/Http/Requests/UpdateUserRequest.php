<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        $userId = $this->user->id; 
        
        $rules = [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $userId,
            'password' => 'nullable|string|min:8|confirmed', 
            'role' => 'sometimes|string|in:user,admin,superadmin', 
        ];

        return $rules;
    }
}
