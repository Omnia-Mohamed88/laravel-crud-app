<?php
namespace App\Imports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CategoriesImport implements ToModel, WithValidation, WithHeadingRow
{
    /**
     * Transform the imported row data into a model instance.
     *
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        \Log::info('Import Row Data:', $row); 

        if (isset($row['title'])) {
            return new Category([
                'title' => $row['title'],
            ]);
        }

        return null;
    }

    /**
     * Define validation rules for the imported data.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:50',
        ];
    }
}
