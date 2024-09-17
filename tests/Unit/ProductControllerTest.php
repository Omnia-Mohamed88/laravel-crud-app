<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_list()
    {
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'title' => 'microwave',
            'description' => 'this is test image',
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'message',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'price',
                        'created_at',
                        'updated_at',
                        'category' => [
                            'id',
                            'title',
                            'created_at',
                            'updated_at',
                            'attachments' => [
                                '*' => [
                                    'id',
                                    'file_path',
                                    'created_at',
                                    'updated_at'
                                ]
                            ]
                        ],
                        'attachments' => [
                            '*' => [
                                'id',
                                'file_path',
                                'created_at',
                                'updated_at'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $response->assertJsonFragment([
            'title' => 'microwave',
            'description' => 'this is test image'
        ]);
    }
    public function test_store_product_with_admin()
    {
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
    
        $admin = User::factory()->create();
        $admin->assignRole('admin'); 
    
        $category = Category::factory()->create();
    
        $productData = [
            'title' => 'New Product',
            'description' => 'This is a new product.',
            'price' => '99.99',
            'category_id' => $category->id
        ];
    
        Passport::actingAs($admin, ['create-products']); 
    
        $response = $this->postJson('/api/products', $productData);
    
        $response->assertStatus(201); 
    
        $response->assertJsonFragment([
            'message' => 'Product created successfully.',
            'title' => 'New Product',
            'description' => 'This is a new product.',
            'price' => '99.99',
        ]);
    
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'title',
                'description',
                'price',
                'created_at',
                'updated_at',
                'attachments',
            ]
        ]);
    
        $this->assertDatabaseHas('products', [
            'title' => 'New Product',
            'description' => 'This is a new product.',
            'price' => '99.99',
            'category_id' => $category->id
        ]);
    }
    public function test_update_product_with_admin(){
        
    }
}    
