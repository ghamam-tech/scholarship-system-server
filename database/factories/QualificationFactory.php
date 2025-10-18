<?php

namespace Database\Factories;

use App\Models\Qualification;
use App\Models\Applicant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Qualification>
 */
class QualificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'applicant_id' => Applicant::factory(),
            'qualification_type' => fake()->randomElement(['high_school', 'diploma', 'bachelor', 'master', 'phd', 'other']),
            'institute_name' => fake()->company() . ' University',
            'year_of_graduation' => fake()->numberBetween(2010, 2024),
            'cgpa' => fake()->randomFloat(2, 2.0, 4.0),
            'cgpa_out_of' => 4.0,
            'language_of_study' => fake()->randomElement(['Arabic', 'English', 'French', 'German']),
            'specialization' => fake()->randomElement(['Computer Science', 'Engineering', 'Medicine', 'Business', 'Arts']),
            'research_title' => fake()->sentence(6),
            'document_file' => null,
        ];
    }
}
