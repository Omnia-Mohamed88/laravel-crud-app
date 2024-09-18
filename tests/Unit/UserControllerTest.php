<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Passport\Passport;

class UserControllerTest extends TestCase
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

    public function test_store_user_with_superadmin()
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $response = $this->actingAs($superadmin)
                         ->postJson('/api/users', [
                             'name' => 'New User',
                             'email' => 'newuser12@example.com',
                             'password' => 'password',
                             'password_confirmation' => 'password',
                             'role' => 'admin', 
                         ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['message' => 'User Created Successfully']);
    }

    public function test_update_user_with_superadmin()
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $user = User::factory()->create();

        $response = $this->actingAs($superadmin)
                         ->putJson("/api/users/{$user->id}", [
                             'name' => 'Updated User',
                             'email' => 'updateduser@example.com',
                             'password' => 'newpassword',
                             'password_confirmation' => 'newpassword', 
                         ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'user Data']);
    }

    public function test_delete_user_with_superadmin()
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $user = User::factory()->create();

        $response = $this->actingAs($superadmin)
                         ->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'User deleted successfully.']);
    }

    public function test_show_user()
    {
        $superadmin = User::factory()->create();
        $superadmin->assignRole('superadmin');

        $user = User::factory()->create();

        $response = $this->actingAs($superadmin)
                         ->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'user Data']);
    }
}
