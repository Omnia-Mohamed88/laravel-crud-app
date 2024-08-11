<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Category;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('db:seed', ['--class' => 'WebRolesSeeder']);

        $user = User::factory()->create();
        $user->assignRole('admin'); 

        $this->actingAs($user, 'api');
    }

    /** @test */
    public function can_list_categories()
    {
        $response = $this->getJson('/api/categories');
        $response->assertStatus(200);
    }

    /** @test */
    public function can_create_category()
    {
        $response = $this->postJson('/api/categories', [
            'title' => 'New Category',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('categories', ['title' => 'New Category']);
    }

    /** @test */
    public function can_show_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $category->id,
                'title' => $category->title,
            ],
        ]);
    }

    /** @test */
    public function can_update_category()
    {
        $category = Category::factory()->create();

        $response = $this->putJson("/api/categories/{$category->id}", [
            'title' => 'Updated Category',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('categories', ['title' => 'Updated Category']);
    }

    /** @test */
    public function can_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /** @test */
    public function can_import_categories()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('categories.xlsx', 1024, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->postJson('/api/categories/import', [
            'file' => $file,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['message' => 'Categories imported successfully']);

        Storage::disk('local')->assertExists('categories.xlsx');
    }
}
