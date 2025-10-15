<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $adminToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $this->adminToken = $this->admin->createToken('test-token')->plainTextToken;
    }

    public function test_admin_can_create_admin()
    {
        $adminData = [
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'password' => 'password123',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/admins', $adminData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'user_id',
                    'email',
                    'role',
                ],
                'admin' => [
                    'admin_id',
                    'name',
                    'user_id',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@example.com',
            'role' => UserRole::ADMIN->value,
        ]);

        $this->assertDatabaseHas('admins', [
            'name' => 'New Admin',
        ]);
    }

    public function test_admin_can_view_all_admins()
    {
        $user1 = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $user2 = User::factory()->create(['role' => UserRole::ADMIN->value]);

        Admin::factory()->create(['user_id' => $user1->user_id]);
        Admin::factory()->create(['user_id' => $user2->user_id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/admins');

        $response->assertStatus(200)
            ->assertJsonCount(3); // 2 created + 1 setup admin
    }

    public function test_admin_can_view_specific_admin()
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $admin = Admin::factory()->create(['user_id' => $user->user_id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/v1/admins/{$admin->admin_id}");

        $response->assertStatus(200)
            ->assertJson([
                'admin_id' => $admin->admin_id,
                'name' => $admin->name,
            ]);
    }

    public function test_admin_can_update_admin()
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $admin = Admin::factory()->create(['user_id' => $user->user_id]);

        $updateData = [
            'name' => 'Updated Admin Name',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/v1/admins/{$admin->admin_id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Admin updated successfully',
            ]);

        $this->assertDatabaseHas('admins', [
            'admin_id' => $admin->admin_id,
            'name' => 'Updated Admin Name',
        ]);
    }

    public function test_admin_can_delete_admin()
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $admin = Admin::factory()->create(['user_id' => $user->user_id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/v1/admins/{$admin->admin_id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Admin deleted successfully',
            ]);

        $this->assertDatabaseMissing('admins', [
            'admin_id' => $admin->admin_id,
        ]);
    }

    public function test_admin_creation_requires_valid_data()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/admins', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
            ]);
    }

    public function test_admin_email_must_be_unique()
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $adminData = [
            'name' => 'Test Admin',
            'email' => 'existing@example.com',
            'password' => 'password123',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/admins', $adminData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
