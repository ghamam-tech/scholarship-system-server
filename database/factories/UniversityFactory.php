<?php

namespace Database\Factories;

use App\Models\University;
use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\University>
 */
class UniversityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'university_name' => fake()->company() . ' University',
            'city' => fake()->city(),
            'is_active' => fake()->boolean(80), // 80% chance of being active
            'country_id' => Country::factory(),
        ];
    }
}
