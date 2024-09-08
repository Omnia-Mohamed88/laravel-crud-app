<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $permissionId = $this->permission->id; 

        return [
            'name' => 'required|string|unique:permissions,name,' . $permissionId,
        ];
    }
}
