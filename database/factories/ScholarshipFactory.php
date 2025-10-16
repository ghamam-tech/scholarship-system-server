<?php

namespace Database\Factories;

use App\Models\Scholarship;
use App\Models\Sponsor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Scholarship>
 */
class ScholarshipFactory extends Factory
{
    protected $model = Scholarship::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scholarship_name' => $this->faker->sentence(3),
            'scholarship_type' => $this->faker->randomElement(['Full', 'Partial', 'Merit-based', 'Need-based']),
            'allowed_program' => $this->faker->randomElement(['Computer Science', 'Engineering', 'Medicine', 'Business', 'Arts']),
            'total_beneficiaries' => $this->faker->numberBetween(1, 100),
            'opening_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'closing_date' => $this->faker->dateTimeBetween('now', '+90 days'),
            'description' => $this->faker->paragraph(3),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'is_hided' => $this->faker->boolean(20), // 20% chance of being hidden
            'sponsor_id' => Sponsor::factory(),
        ];
    }

    /**
     * Indicate that the scholarship is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the scholarship is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the scholarship is hidden.
     */
    public function hidden(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_hided' => true,
        ]);
    }

    /**
     * Indicate that the scholarship is visible.
     */
    public function visible(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
            'is_hided' => false,
        ]);
    }

    /**
     * Indicate that the scholarship is expired.
     */
    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'closing_date' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }
}
