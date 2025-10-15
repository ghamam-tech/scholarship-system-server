<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Sponsor;
use App\Enums\UserRole;

class SponsorSeeder extends Seeder
{
    public function run()
    {
        $sponsors = [
            // Corporate Sponsors
            [
                'name' => 'Microsoft Corporation',
                'country' => 'United States',
                'email' => 'sponsor.microsoft@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Google LLC',
                'country' => 'United States',
                'email' => 'sponsor.google@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Amazon Web Services',
                'country' => 'United States',
                'email' => 'sponsor.aws@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Samsung Electronics',
                'country' => 'South Korea',
                'email' => 'sponsor.samsung@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Siemens AG',
                'country' => 'Germany',
                'email' => 'sponsor.siemens@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Toyota Motor Corporation',
                'country' => 'Japan',
                'email' => 'sponsor.toyota@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'HSBC Holdings',
                'country' => 'United Kingdom',
                'email' => 'sponsor.hsbc@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'LVMH Moët Hennessy',
                'country' => 'France',
                'email' => 'sponsor.lvmh@example.com',
                'is_active' => true,
            ],

            // Government Organizations
            [
                'name' => 'US Department of Education',
                'country' => 'United States',
                'email' => 'sponsor.used@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'British Council',
                'country' => 'United Kingdom',
                'email' => 'sponsor.britishcouncil@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'DAAD German Academic Exchange',
                'country' => 'Germany',
                'email' => 'sponsor.daad@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Ministry of Education, China',
                'country' => 'China',
                'email' => 'sponsor.moechina@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Saudi Arabian Cultural Mission',
                'country' => 'Saudi Arabia',
                'email' => 'sponsor.sacm@example.com',
                'is_active' => true,
            ],

            // Non-Profit Foundations
            [
                'name' => 'Bill & Melinda Gates Foundation',
                'country' => 'United States',
                'email' => 'sponsor.gatesfoundation@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Ford Foundation',
                'country' => 'United States',
                'email' => 'sponsor.fordfoundation@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Rockefeller Foundation',
                'country' => 'United States',
                'email' => 'sponsor.rockefeller@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Open Society Foundations',
                'country' => 'United States',
                'email' => 'sponsor.opensociety@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Wellcome Trust',
                'country' => 'United Kingdom',
                'email' => 'sponsor.wellcome@example.com',
                'is_active' => true,
            ],

            // Educational Institutions
            [
                'name' => 'Harvard University Scholarships',
                'country' => 'United States',
                'email' => 'sponsor.harvard@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Stanford University Financial Aid',
                'country' => 'United States',
                'email' => 'sponsor.stanford@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'University of Oxford Trusts',
                'country' => 'United Kingdom',
                'email' => 'sponsor.oxford@example.com',
                'is_active' => true,
            ],

            // Regional Sponsors
            [
                'name' => 'African Development Bank',
                'country' => 'Côte d\'Ivoire',
                'email' => 'sponsor.afdb@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'Asian Development Bank',
                'country' => 'Philippines',
                'email' => 'sponsor.adb@example.com',
                'is_active' => true,
            ],
            [
                'name' => 'European Commission Erasmus+',
                'country' => 'Belgium',
                'email' => 'sponsor.erasmus@example.com',
                'is_active' => true,
            ],

            // Inactive Sponsors (for testing)
            [
                'name' => 'Defunct Tech Inc.',
                'country' => 'Canada',
                'email' => 'sponsor.defunct@example.com',
                'is_active' => false,
            ],
            [
                'name' => 'Closed Foundation',
                'country' => 'Australia',
                'email' => 'sponsor.closed@example.com',
                'is_active' => false,
            ],
        ];

        foreach ($sponsors as $sponsorData) {
            // Create user account for sponsor
            $user = User::create([
                'email' => $sponsorData['email'],
                'password' => Hash::make('password123'), // Default password
                'role' => UserRole::SPONSOR->value,
            ]);

            // Create sponsor profile
            Sponsor::create([
                'user_id' => $user->user_id,
                'name' => $sponsorData['name'],
                'country' => $sponsorData['country'],
                'is_active' => $sponsorData['is_active'],
            ]);
        }

        $this->command->info('✅ ' . count($sponsors) . ' sponsors created successfully!');
        $this->command->info('📧 All sponsors have password: password123');
    }
}