<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        Category::create(["title" => "Category Test"]);
    }
}
