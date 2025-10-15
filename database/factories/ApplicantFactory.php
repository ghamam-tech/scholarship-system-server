<?php

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Applicant>
 */
class ApplicantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->applicant(),
            'ar_name' => fake('ar_SA')->name(),
            'en_name' => fake()->name(),
            'nationality' => fake()->country(),
            'gender' => fake()->randomElement(['male', 'female']),
            'place_of_birth' => fake()->city(),
            'phone' => fake()->phoneNumber(),
            'passport_number' => fake()->unique()->regexify('[A-Z]{1}[0-9]{7}'),
            'date_of_birth' => fake()->date('Y-m-d', '2000-01-01'),
            'parent_contact_name' => fake()->name(),
            'parent_contact_phone' => fake()->phoneNumber(),
            'residence_country' => fake()->country(),
            'language' => fake()->randomElement(['Arabic', 'English', 'French']),
            'is_studied_in_saudi' => fake()->boolean(),
            'passport_copy_img' => null,
            'volunteering_certificate_file' => null,
            'tahsili_file' => null,
            'qudorat_file' => null,
        ];
    }
}
