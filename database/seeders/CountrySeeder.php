<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run()
    {
        $countries = [
            ['country_name' => 'United States', 'country_code' => 'US', 'is_active' => true],
            ['country_name' => 'Canada', 'country_code' => 'CA', 'is_active' => true],
            ['country_name' => 'United Kingdom', 'country_code' => 'UK', 'is_active' => true],
            ['country_name' => 'Australia', 'country_code' => 'AU', 'is_active' => true],
            ['country_name' => 'Germany', 'country_code' => 'DE', 'is_active' => true],
            ['country_name' => 'France', 'country_code' => 'FR', 'is_active' => true],
            ['country_name' => 'Japan', 'country_code' => 'JP', 'is_active' => true],
            ['country_name' => 'South Korea', 'country_code' => 'KR', 'is_active' => true],
            ['country_name' => 'China', 'country_code' => 'CN', 'is_active' => true],
            ['country_name' => 'India', 'country_code' => 'IN', 'is_active' => true],
            ['country_name' => 'Brazil', 'country_code' => 'BR', 'is_active' => true],
            ['country_name' => 'Mexico', 'country_code' => 'MX', 'is_active' => true],
            ['country_name' => 'Italy', 'country_code' => 'IT', 'is_active' => true],
            ['country_name' => 'Spain', 'country_code' => 'ES', 'is_active' => true],
            ['country_name' => 'Netherlands', 'country_code' => 'NL', 'is_active' => true],
            ['country_name' => 'Sweden', 'country_code' => 'SE', 'is_active' => true],
            ['country_name' => 'Switzerland', 'country_code' => 'CH', 'is_active' => true],
            ['country_name' => 'Singapore', 'country_code' => 'SG', 'is_active' => true],
            ['country_name' => 'Malaysia', 'country_code' => 'MY', 'is_active' => true],
            ['country_name' => 'United Arab Emirates', 'country_code' => 'AE', 'is_active' => true],
            ['country_name' => 'Saudi Arabia', 'country_code' => 'SA', 'is_active' => true],
            ['country_name' => 'Egypt', 'country_code' => 'EG', 'is_active' => true],
            ['country_name' => 'South Africa', 'country_code' => 'ZA', 'is_active' => true],
            ['country_name' => 'Turkey', 'country_code' => 'TR', 'is_active' => true],
            ['country_name' => 'Russia', 'country_code' => 'RU', 'is_active' => false], // Inactive
            ['country_name' => 'Ukraine', 'country_code' => 'UA', 'is_active' => false], // Inactive
            ['country_name' => 'Argentina', 'country_code' => 'AR', 'is_active' => true],
            ['country_name' => 'Chile', 'country_code' => 'CL', 'is_active' => true],
            ['country_name' => 'New Zealand', 'country_code' => 'NZ', 'is_active' => true],
            ['country_name' => 'Ireland', 'country_code' => 'IE', 'is_active' => true],
        ];

        foreach ($countries as $country) {
            DB::table('countries')->insert([
                'country_name' => $country['country_name'],
                'country_code' => $country['country_code'],
                'is_active' => $country['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}