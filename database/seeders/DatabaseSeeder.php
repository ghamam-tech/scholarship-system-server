<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            CountrySeeder::class,
            UniversitySeeder::class,
            SponsorSeeder::class,
            ScholarshipSeeder::class,
            AdminSeeder::class,
            // Add other seeders here
        ]);
    }
}