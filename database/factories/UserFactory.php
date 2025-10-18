<?php

namespace Database\Factories;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => fake()->randomElement([UserRole::APPLICANT->value, UserRole::SPONSOR->value, UserRole::ADMIN->value]),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create an applicant user
     */
    public function applicant(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::APPLICANT->value,
        ]);
    }

    /**
     * Create a sponsor user
     */
    public function sponsor(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::SPONSOR->value,
        ]);
    }

    /**
     * Create an admin user
     */
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::ADMIN->value,
        ]);
    }
}
