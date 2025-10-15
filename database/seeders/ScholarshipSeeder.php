<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Scholarship;
use App\Models\Country;
use App\Models\University;
use App\Models\Sponsor;

class ScholarshipSeeder extends Seeder
{
    public function run()
    {
        // Get actual country IDs from the database
        $us = Country::where('country_code', 'US')->first();
        $ca = Country::where('country_code', 'CA')->first();
        $uk = Country::where('country_code', 'UK')->first();
        $au = Country::where('country_code', 'AU')->first();
        $de = Country::where('country_code', 'DE')->first();
        $fr = Country::where('country_code', 'FR')->first();
        $jp = Country::where('country_code', 'JP')->first();
        $kr = Country::where('country_code', 'KR')->first();
        $cn = Country::where('country_code', 'CN')->first();
        $in = Country::where('country_code', 'IN')->first();
        $br = Country::where('country_code', 'BR')->first();
        $mx = Country::where('country_code', 'MX')->first();
        $it = Country::where('country_code', 'IT')->first();
        $es = Country::where('country_code', 'ES')->first();
        $nl = Country::where('country_code', 'NL')->first();
        $se = Country::where('country_code', 'SE')->first();
        $ch = Country::where('country_code', 'CH')->first();
        $sg = Country::where('country_code', 'SG')->first();
        $my = Country::where('country_code', 'MY')->first();
        $ae = Country::where('country_code', 'AE')->first();
        $sa = Country::where('country_code', 'SA')->first();
        $eg = Country::where('country_code', 'EG')->first();
        $za = Country::where('country_code', 'ZA')->first();
        $tr = Country::where('country_code', 'TR')->first();
        $ru = Country::where('country_code', 'RU')->first();
        $ua = Country::where('country_code', 'UA')->first();
        $ar = Country::where('country_code', 'AR')->first();
        $cl = Country::where('country_code', 'CL')->first();
        $nz = Country::where('country_code', 'NZ')->first();
        $ie = Country::where('country_code', 'IE')->first();

        // Get some universities for relationships
        $harvard = University::where('university_name', 'Harvard University')->first();
        $stanford = University::where('university_name', 'Stanford University')->first();
        $mit = University::where('university_name', 'Massachusetts Institute of Technology')->first();
        $caltech = University::where('university_name', 'California Institute of Technology')->first();
        $berkeley = University::where('university_name', 'University of California, Berkeley')->first();
        $yale = University::where('university_name', 'Yale University')->first();
        $princeton = University::where('university_name', 'Princeton University')->first();
        $columbia = University::where('university_name', 'Columbia University')->first();
        $chicago = University::where('university_name', 'University of Chicago')->first();
        $michigan = University::where('university_name', 'University of Michigan')->first();
        $toronto = University::where('university_name', 'University of Toronto')->first();
        $ubc = University::where('university_name', 'University of British Columbia')->first();
        $mcgill = University::where('university_name', 'McGill University')->first();
        $alberta = University::where('university_name', 'University of Alberta')->first();
        $waterloo = University::where('university_name', 'University of Waterloo')->first();
        $calgary = University::where('university_name', 'University of Calgary')->first();
        $oxford = University::where('university_name', 'University of Oxford')->first();
        $cambridge = University::where('university_name', 'University of Cambridge')->first();
        $imperial = University::where('university_name', 'Imperial College London')->first();
        $lse = University::where('university_name', 'London School of Economics')->first();
        $ucl = University::where('university_name', 'University College London')->first();
        $edinburgh = University::where('university_name', 'University of Edinburgh')->first();
        $manchester = University::where('university_name', 'University of Manchester')->first();
        $melbourne = University::where('university_name', 'University of Melbourne')->first();
        $anu = University::where('university_name', 'Australian National University')->first();
        $sydney = University::where('university_name', 'University of Sydney')->first();
        $queensland = University::where('university_name', 'University of Queensland')->first();
        $unsw = University::where('university_name', 'University of New South Wales')->first();
        $tum = University::where('university_name', 'Technical University of Munich')->first();
        $lmu = University::where('university_name', 'Ludwig Maximilian University of Munich')->first();
        $heidelberg = University::where('university_name', 'Heidelberg University')->first();
        $humboldt = University::where('university_name', 'Humboldt University of Berlin')->first();
        $freeBerlin = University::where('university_name', 'Free University of Berlin')->first();

        // Get sponsor IDs
        $microsoft = Sponsor::where('name', 'Microsoft Corporation')->first();
        $google = Sponsor::where('name', 'Google LLC')->first();
        $aws = Sponsor::where('name', 'Amazon Web Services')->first();
        $samsung = Sponsor::where('name', 'Samsung Electronics')->first();
        $siemens = Sponsor::where('name', 'Siemens AG')->first();
        $toyota = Sponsor::where('name', 'Toyota Motor Corporation')->first();
        $hsbc = Sponsor::where('name', 'HSBC Holdings')->first();
        $lvmh = Sponsor::where('name', 'LVMH Moët Hennessy')->first();
        $usDeptEd = Sponsor::where('name', 'US Department of Education')->first();
        $britishCouncil = Sponsor::where('name', 'British Council')->first();
        $daad = Sponsor::where('name', 'DAAD German Academic Exchange')->first();
        $gates = Sponsor::where('name', 'Bill & Melinda Gates Foundation')->first();
        $ford = Sponsor::where('name', 'Ford Foundation')->first();
        $afdb = Sponsor::where('name', 'African Development Bank')->first();
        $erasmus = Sponsor::where('name', 'European Commission Erasmus+')->first();
        $defunct = Sponsor::where('name', 'Defunct Tech Inc.')->first();
        $closed = Sponsor::where('name', 'Closed Foundation')->first();

        $scholarships = [
            // STEM Scholarships
            [
                'scholarship_name' => 'Microsoft Tech Scholarship Program',
                'scholarship_type' => 'Merit-based',
                'allowed_program' => 'Computer Science, Engineering',
                'total_beneficiaries' => 50,
                'opening_date' => '2024-01-15',
                'closing_date' => '2024-03-31',
                'description' => 'Full scholarship for outstanding students pursuing degrees in computer science and engineering fields.',
                'is_active' => true,
                'is_hided' => false,
                'sponsor_id' => $microsoft->sponsor_id,
                'country_ids' => [$us->country_id, $ca->country_id, $uk->country_id, $au->country_id, $de->country_id],
                'university_ids' => [$harvard->university_id, $stanford->university_id, $mit->university_id, $caltech->university_id, $toronto->university_id, $ubc->university_id, $oxford->university_id, $cambridge->university_id]
            ],
            [
                'scholarship_name' => 'Google Women in Tech Scholarship',
                'scholarship_type' => 'Need-based',
                'allowed_program' => 'Computer Science, Data Science',
                'total_beneficiaries' => 30,
                'opening_date' => '2024-02-01',
                'closing_date' => '2024-04-30',
                'description' => 'Scholarship dedicated to supporting women in technology fields across global universities.',
                'is_active' => true,
                'is_hided' => false,
                'sponsor_id' => $google->sponsor_id,
                'country_ids' => [$us->country_id, $ca->country_id, $uk->country_id, $jp->country_id, $kr->country_id, $cn->country_id],
                'university_ids' => [$harvard->university_id, $stanford->university_id, $mit->university_id, $caltech->university_id, $berkeley->university_id, $oxford->university_id, $cambridge->university_id, $tum->university_id, $lmu->university_id]
            ],
            [
                'scholarship_name' => 'AWS Cloud Computing Scholarship',
                'scholarship_type' => 'Merit-based',
                'allowed_program' => 'Cloud Computing, IT',
                'total_beneficiaries' => 25,
                'opening_date' => '2024-03-01',
                'closing_date' => '2024-05-15',
                'description' => 'Scholarship for students interested in cloud computing and AWS technologies.',
                'is_active' => true,
                'is_hided' => false,
                'sponsor_id' => $aws->sponsor_id,
                'country_ids' => [$us->country_id, $ca->country_id, $au->country_id, $sg->country_id, $ae->country_id],
                'university_ids' => [$harvard->university_id, $stanford->university_id, $mit->university_id, $toronto->university_id, $ubc->university_id, $melbourne->university_id, $anu->university_id]
            ],

            // Engineering Scholarships
            [
                'scholarship_name' => 'Siemens Engineering Excellence Award',
                'scholarship_type' => 'Merit-based',
                'allowed_program' => 'Mechanical Engineering, Electrical Engineering',
                'total_beneficiaries' => 20,
                'opening_date' => '2024-01-20',
                'closing_date' => '2024-04-20',
                'description' => 'Scholarship for exceptional engineering students with focus on innovation and sustainability.',
                'is_active' => true,
                'is_hided' => false,
                'sponsor_id' => $siemens->sponsor_id,
                'country_ids' => [$de->country_id, $us->country_id, $uk->country_id, $ch->country_id],
                'university_ids' => [$tum->university_id, $lmu->university_id, $harvard->university_id, $stanford->university_id, $oxford->university_id, $cambridge->university_id]
            ],

            // Government Scholarships
            [
                'scholarship_name' => 'US Department of Education STEM Initiative',
                'scholarship_type' => 'Need-based',
                'allowed_program' => 'STEM Fields',
                'total_beneficiaries' => 100,
                'opening_date' => '2024-02-01',
                'closing_date' => '2024-06-30',
                'description' => 'Government initiative to support students in science, technology, engineering, and mathematics.',
                'is_active' => true,
                'is_hided' => false,
                'sponsor_id' => $usDeptEd->sponsor_id,
                'country_ids' => [$us->country_id],
                'university_ids' => [$harvard->university_id, $stanford->university_id, $mit->university_id, $caltech->university_id, $berkeley->university_id, $yale->university_id, $princeton->university_id, $columbia->university_id, $chicago->university_id, $michigan->university_id]
            ],
            [
                'scholarship_name' => 'British Council International Scholarship',
                'scholarship_type' => 'Merit-based',
                'allowed_program' => 'All Programs',
                'total_beneficiaries' => 75,
                'opening_date' => '2024-03-01',
                'closing_date' => '2024-05-31',
                'description' => 'International scholarship program for outstanding students worldwide to study in UK universities.',
                'is_active' => true,
                'is_hided' => false,
                'sponsor_id' => $britishCouncil->sponsor_id,
                'country_ids' => [$uk->country_id],
                'university_ids' => [$oxford->university_id, $cambridge->university_id, $imperial->university_id, $lse->university_id, $ucl->university_id, $edinburgh->university_id, $manchester->university_id]
            ],

            // Foundation Scholarships
            [
                'scholarship_name' => 'Gates Foundation Global Health Scholarship',
                'scholarship_type' => 'Research-based',
                'allowed_program' => 'Public Health, Medicine, Biology',
                'total_beneficiaries' => 35,
                'opening_date' => '2024-02-20',
                'closing_date' => '2024-06-15',
                'description' => 'Scholarship for students dedicated to global health challenges and medical research.',
                'is_active' => true,
                'is_hided' => false,
                'sponsor_id' => $gates->sponsor_id,
                'country_ids' => [$us->country_id, $uk->country_id, $au->country_id, $se->country_id, $za->country_id],
                'university_ids' => [$harvard->university_id, $stanford->university_id, $oxford->university_id, $cambridge->university_id, $melbourne->university_id, $anu->university_id]
            ],

            // Regional Scholarships
            [
                'scholarship_name' => 'African Development Bank Scholarship',
                'scholarship_type' => 'Merit-based',
                'allowed_program' => 'Development Studies, Economics, Engineering',
                'total_beneficiaries' => 50,
                'opening_date' => '2024-01-30',
                'closing_date' => '2024-05-30',
                'description' => 'Scholarship for African students pursuing studies in development-related fields.',
                'is_active' => true,
                'is_hided' => false,
                'sponsor_id' => $afdb->sponsor_id,
                'country_ids' => [$eg->country_id, $za->country_id, $my->country_id, $br->country_id, $ar->country_id],
                'university_ids' => [$toronto->university_id, $ubc->university_id, $mcgill->university_id, $alberta->university_id]
            ],

            // Inactive/Closed Scholarships (for testing)
            [
                'scholarship_name' => 'Legacy Tech Scholarship 2023',
                'scholarship_type' => 'Merit-based',
                'allowed_program' => 'Computer Science',
                'total_beneficiaries' => 20,
                'opening_date' => '2023-01-15',
                'closing_date' => '2023-03-31',
                'description' => 'Previous year scholarship program (now closed).',
                'is_active' => false,
                'is_hided' => true,
                'sponsor_id' => $defunct->sponsor_id,
                'country_ids' => [$ca->country_id],
                'university_ids' => [$toronto->university_id, $ubc->university_id, $mcgill->university_id]
            ],
        ];

        foreach ($scholarships as $scholarshipData) {
            // Extract relationship data
            $countryIds = $scholarshipData['country_ids'];
            $universityIds = $scholarshipData['university_ids'];
            
            // Remove relationship data from main scholarship data
            unset($scholarshipData['country_ids']);
            unset($scholarshipData['university_ids']);

            // Create scholarship
            $scholarship = Scholarship::create($scholarshipData);

            // Attach countries
            $scholarship->countries()->attach($countryIds);

            // Attach universities
            $scholarship->universities()->attach($universityIds);
        }

        $this->command->info('✅ ' . count($scholarships) . ' scholarships created successfully!');
        $this->command->info('🌍 Scholarships include relationships with countries and universities');
    }
}