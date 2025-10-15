<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Models\Sponsor;
use App\Models\Applicant;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
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

    public function test_admin_can_create_user()
    {
        $userData = [
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => UserRole::APPLICANT->value,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->postJson('/api/v1/users', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'user_id',
                    'email',
                    'role',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'role' => UserRole::APPLICANT->value,
        ]);
    }

    public function test_admin_can_view_all_users()
    {
        User::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonCount(4); // 3 created + 1 admin
    }

    public function test_admin_can_view_specific_user()
    {
        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->getJson("/api/v1/users/{$user->user_id}");

        $response->assertStatus(200)
            ->assertJson([
                'user_id' => $user->user_id,
                'email' => $user->email,
            ]);
    }

    public function test_admin_can_update_user()
    {
        $user = User::factory()->create();

        $updateData = [
            'email' => 'updated@example.com',
            'role' => UserRole::SPONSOR->value,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->putJson("/api/v1/users/{$user->user_id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'email' => 'updated@example.com',
            'role' => UserRole::SPONSOR->value,
        ]);
    }

    public function test_admin_can_delete_user()
    {
        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->adminToken,
        ])->deleteJson("/api/v1/users/{$user->user_id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User deleted successfully',
            ]);

        $this->assertDatabaseMissing('users', [
            'user_id' => $user->user_id,
        ]);
    }

    public function test_non_admin_cannot_access_user_management()
    {
        $user = User::factory()->create(['role' => UserRole::APPLICANT->value]);
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/users');

        $response->assertStatus(403);
    }
}
