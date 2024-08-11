<?php 
namespace App\Imports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;

class CategoriesImport implements ToModel, WithValidation
{
    public function model(array $row)
{
    \Log::info('Import Row Data:', $row); 

    if (isset($row[0])) {
        return new Category([
            'title' => $row[0],
        ]);
    }

    return null;
}


    public function rules(): array
    {
        return [
            '0' => 'required|string|max:50',
        ];
    }
}
