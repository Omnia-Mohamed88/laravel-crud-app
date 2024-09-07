<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $productId = $this->route('product');

        return [
            'title' => 'required|string|max:255|unique:products,title,' . $productId,
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'category_id' => 'nullable|exists:categories,id',
            'attachments.*.file_path' => 'nullable',
        ];
    }
}