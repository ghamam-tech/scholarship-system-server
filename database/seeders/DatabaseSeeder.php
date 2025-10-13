<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// ⬇️ imports go HERE (top of file, after namespace)
use App\Models\User;
use App\Enums\UserRole;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create ONE admin user (idempotent)
        User::updateOrCreate(
            ['email' => 'admin@irfad.com'],
            [
                'password' => 'Admin@12345',         // auto-hashed if you have 'password' => 'hashed' cast
                'role'     => UserRole::ADMIN->value // or just 'admin' if you aren't using the enum
            ]
        );
    }
}
