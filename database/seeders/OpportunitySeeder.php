<?php

namespace Database\Seeders;

use App\Models\Opportunity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OpportunitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Remove existing opportunities (will cascade delete related application_opportunities via FK)
        DB::transaction(function () {
            // Use delete() instead of truncate() to respect FK constraints
            Opportunity::query()->delete();
        });

        // Seed fresh opportunities
        $now = now();

        $records = [
            [
                'title' => 'Community Clean-Up Day',
                'discription' => 'Join us to clean public parks and streets.',
                'date' => $now->copy()->addDays(7)->toDateString(),
                'location' => 'Riyadh',
                'country' => 'SA',
                'category' => 'Volunteering',
                'qr_url' => null,
                'opportunity_coordinatior_name' => 'Coordinator A',
                'opportunity_coordinatior_phone' => '+966500000001',
                'opportunity_coordinatior_email' => 'coord.a@example.com',
                'opportunity_status' => 'active',
                'start_date' => $now->copy()->addDays(7)->toDateString(),
                'end_date' => $now->copy()->addDays(7)->toDateString(),
                'volunteer_role' => 'Cleaner',
                'volunteering_hours' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Blood Donation Drive',
                'discription' => 'Support local hospitals with blood donations.',
                'date' => $now->copy()->addDays(14)->toDateString(),
                'location' => 'Jeddah',
                'country' => 'SA',
                'category' => 'Health',
                'qr_url' => null,
                'opportunity_coordinatior_name' => 'Coordinator B',
                'opportunity_coordinatior_phone' => '+966500000002',
                'opportunity_coordinatior_email' => 'coord.b@example.com',
                'opportunity_status' => 'active',
                'start_date' => $now->copy()->addDays(14)->toDateString(),
                'end_date' => $now->copy()->addDays(14)->toDateString(),
                'volunteer_role' => 'Volunteer',
                'volunteering_hours' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Tech Workshop Mentoring',
                'discription' => 'Mentor students in an intro to coding workshop.',
                'date' => $now->copy()->addDays(21)->toDateString(),
                'location' => 'Dammam',
                'country' => 'SA',
                'category' => 'Education',
                'qr_url' => null,
                'opportunity_coordinatior_name' => 'Coordinator C',
                'opportunity_coordinatior_phone' => '+966500000003',
                'opportunity_coordinatior_email' => 'coord.c@example.com',
                'opportunity_status' => 'active',
                'start_date' => $now->copy()->addDays(21)->toDateString(),
                'end_date' => $now->copy()->addDays(21)->toDateString(),
                'volunteer_role' => 'Mentor',
                'volunteering_hours' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        Opportunity::insert($records);
    }
}
