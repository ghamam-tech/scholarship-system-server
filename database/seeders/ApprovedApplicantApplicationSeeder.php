<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApprovedApplicantApplication;
use App\Models\ApplicantApplication;
use App\Models\Scholarship;
use App\Models\User;

class ApprovedApplicantApplicationSeeder extends Seeder
{
    public function run(): void
    {
        // Get some existing applications
        $applications = ApplicantApplication::with(['applicant.user'])->take(3)->get();
        
        if ($applications->isEmpty()) {
            echo "No applications found. Please run ApplicantApplicationSeeder first.\n";
            return;
        }

        // Get a scholarship
        $scholarship = Scholarship::first();
        if (!$scholarship) {
            echo "No scholarships found. Please run ScholarshipSeeder first.\n";
            return;
        }

        foreach ($applications as $application) {
            // Create approved application
            ApprovedApplicantApplication::create([
                'benefits' => [
                    'tuition_coverage' => '100%',
                    'monthly_stipend' => '2000 SAR',
                    'accommodation' => 'Provided',
                    'health_insurance' => 'Included'
                ],
                'has_accepted_scholarship' => true,
                'scholarship_id' => $scholarship->scholarship_id,
                'application_id' => $application->application_id,
                'user_id' => $application->applicant->user_id,
            ]);
        }

        echo "Created " . $applications->count() . " approved applications.\n";
    }
}
