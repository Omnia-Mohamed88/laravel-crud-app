<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $list = [
            [
                "category_id"=> 1,
                 "title" => "Product Test",
                 "description" => "Desription Test",
                  "price" => "100.00"
            ],
            [
                 "category_id"=> 1,
                 "title" => "Product Test 2",
                 "description" => "Desription Test 2",
                  "price" => "120.00"
            ],
            [
                 "category_id"=> 1,
                 "title" => "Product Test 3",
                 "description" => "Desription Test 3",
                  "price" => "130.00"
            ],
        ];
        foreach($list as $item)
        {
            Product::create($item);
        }
    }
}
