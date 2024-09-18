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

    public function test_update_product_with_admin()
    {
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $admin = User::factory()->create();
        $admin->assignRole('admin'); 

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'title' => 'Old Product',
            'description' => 'Old Description',
            'price' => '49.99',
            'category_id' => $category->id
        ]);

        $updatedProductData = [
            'title' => 'Updated Product',
            'description' => 'Updated Description',
            'price' => '79.99',
            'category_id' => $category->id
        ];

        Passport::actingAs($admin, ['update-products']);

        $response = $this->putJson("/api/products/{$product->id}", $updatedProductData);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'message' => 'Product updated successfully.',
            'title' => 'Updated Product',
            'description' => 'Updated Description',
            'price' => '79.99',
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
            'id' => $product->id,
            'title' => 'Updated Product',
            'description' => 'Updated Description',
            'price' => '79.99',
            'category_id' => $category->id,
        ]);
    }
    public function test_show_product()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'title' => 'Product Test',
            'description' => 'Desription Test',
            'price' => 100.00, 
            'category_id' => $category->id
        ]);
    
        $response = $this->getJson("/api/products/{$product->id}");
    
        $response->assertStatus(200);
    
        $response->assertJsonFragment([
            'message' => 'Product Data',
            'title' => 'Product Test',
            'description' => 'Desription Test',
            'price' => 100.00, 
        ]);
    
        $response->assertJsonStructure([
            'message',
            'data' => [
                'data' => [
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
        ]);
    }
    

    public function test_delete_product_with_admin()
    {
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $admin = User::factory()->create();
        $admin->assignRole('admin'); 

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'title' => 'Delete Product',
            'description' => 'This product will be deleted.',
            'price' => '49.99',
            'category_id' => $category->id
        ]);

        Passport::actingAs($admin, ['delete-products']);

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'message' => 'Product deleted successfully.'
        ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
            'title' => 'Delete Product'
        ]);
    }
}
