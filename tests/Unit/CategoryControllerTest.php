<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Role;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    protected function createRoles()
    {
        if (!Role::where('name', 'superadmin')->where('guard_name', 'api')->exists()) {
            Role::create(['name' => 'superadmin', 'guard_name' => 'api']);
        }

        if (!Role::where('name', 'admin')->where('guard_name', 'api')->exists()) {
            Role::create(['name' => 'admin', 'guard_name' => 'api']);
        }
    }

    public function test_index_categories()
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $category = Category::factory()->create();

        $response = $this->actingAs($superadmin)
                         ->getJson('/api/categories');

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => $category->title]);
    }

    public function test_store_category()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin'); 
    
        $categoryData = [
            'title' => 'New Category',
        ];
    
        Passport::actingAs($admin, ['create-categories']); 
    
        $response = $this->postJson('/api/categories', $categoryData);
    
        $response->assertStatus(201);
    
        $response->assertJsonFragment([
            'message' => 'Category created successfully.',
        ]);
    
        $response->assertJsonFragment([
            'title' => 'New Category',
        ]);
    
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'title',
                'created_at',
                'updated_at',
                'attachments' => []
            ]
        ]);
    
        $this->assertDatabaseHas('categories', [
            'title' => 'New Category',
        ]);
    }
    
    public function test_show_category()
    {
        // Create a category
        $category = Category::factory()->create();

        // Show category
        $response = $this->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => $category->title]);
    }

    public function test_update_category()
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $category = Category::factory()->create();

        Storage::fake('public');
        $file = UploadedFile::fake()->image('updated_attachment.jpg');

        $response = $this->actingAs($superadmin)
                         ->putJson("/api/categories/{$category->id}", [
                             'title' => 'Updated Category',
                             'attachments' => [
                                 'create' => [
                                     ['file_path' => $file->store('attachments', 'public')]
                                 ],
                                 'delete' => [],
                             ],
                         ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Updated Category']);
    }

    public function test_destroy_category()
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $category = Category::factory()->create();

        $response = $this->actingAs($superadmin)
                         ->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'category deleted successfully.']);
    }

    public function test_import_categories()
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        Storage::fake('public');
        $file = UploadedFile::fake()->create('categories.xlsx', 1000, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        Excel::fake();

        $response = $this->actingAs($superadmin)
                         ->postJson('/api/categories/import', [
                             'file' => $file,
                         ]);

        $response->assertStatus(201)
                 ->assertJson(['message' => 'Categories imported successfully']);
    }
}
