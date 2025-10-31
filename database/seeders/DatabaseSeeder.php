<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Add your seeders in the correct order
            CountrySeeder::class,           // If you have countries
            UniversitySeeder::class,        // If you have universities  
            SponsorSeeder::class,           // Sponsors must come first
            ScholarshipSeeder::class,       // Then scholarships
            AdminSeeder::class,             // Then admin users
            ApplicantApplicationSeeder::class, // Then applicants and applications
            ApprovedApplicantApplicationSeeder::class, // Create approved applications
            StudentSeeder::class,           // Create students from approved applicants
            ProgramSeeder::class,           // Create programs
            OpportunitySeeder::class,       // Create opportunities (cleans and seeds)
        ]);
    }
}
