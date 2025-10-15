<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test admin user
        $user = User::create([
            'email' => 'admin@test.com',
            'password' => 'password123',
            'role' => UserRole::ADMIN->value
        ]);

        // Create admin profile
        Admin::create([
            'user_id' => $user->user_id,
            'name' => 'Test Admin'
        ]);

        $this->command->info('Test admin created:');
        $this->command->info('Email: admin@test.com');
        $this->command->info('Password: password123');
        $this->command->info('User ID: ' . $user->user_id);
    }
}
