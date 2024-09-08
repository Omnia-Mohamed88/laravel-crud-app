<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize()
    {
        return true; 
    }

    public function rules()
    {
        $roleId = $this->role->id; 
        
        return [
            'name' => 'required|string|unique:roles,name,' . $roleId,
        ];
    }
}
