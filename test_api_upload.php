<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Applicant;
use App\Models\Scholarship;
use Illuminate\Support\Facades\Storage;

echo "=== TESTING API FILE UPLOAD ===\n";

// Get a test user and applicant
$user = User::where('role', 'applicant')->first();
if (!$user) {
    echo "❌ No applicant user found. Creating one...\n";
    $user = User::create([
        'name' => 'Test Applicant',
        'email' => 'testapplicant@example.com',
        'password' => bcrypt('password'),
        'role' => 'applicant'
    ]);
}

$applicant = $user->applicant;
if (!$applicant) {
    echo "❌ No applicant profile found. Creating one...\n";
    $applicant = Applicant::create([
        'user_id' => $user->id,
        'en_name' => 'Test Applicant',
        'ar_name' => 'مقدم طلب تجريبي',
        'nationality' => 'Saudi',
        'gender' => 'male',
        'phone' => '+966501234567',
        'passport_number' => 'TEST123456',
        'date_of_birth' => '2000-01-15',
        'residence_country' => 'Saudi Arabia',
        'language' => 'Arabic',
        'is_studied_in_saudi' => true,
        'tahseeli_percentage' => 85.5,
        'qudorat_percentage' => 78.2
    ]);
}

echo "✅ Using applicant: {$applicant->en_name} (ID: {$applicant->applicant_id})\n";

// Get an active scholarship
$scholarship = Scholarship::where('is_active', true)->first();
if (!$scholarship) {
    echo "❌ No active scholarship found\n";
    exit(1);
}

echo "✅ Using scholarship: {$scholarship->title} (ID: {$scholarship->id})\n";

// Create a test request
$testData = [
    'personal_info' => [
        'en_name' => 'Test API User',
        'ar_name' => 'مستخدم تجريبي',
        'nationality' => 'Saudi',
        'gender' => 'male',
        'phone' => '+966501234567',
        'passport_number' => 'API123456',
        'date_of_birth' => '2000-01-15',
        'residence_country' => 'Saudi Arabia',
        'language' => 'Arabic',
        'is_studied_in_saudi' => true,
        'tahseeli_percentage' => 85.5,
        'qudorat_percentage' => 78.2
    ],
    'academic_info' => [
        'qualifications' => [
            [
                'qualification_type' => 'high_school',
                'institute_name' => 'Test School',
                'year_of_graduation' => 2019,
                'cgpa' => 98.5,
                'cgpa_out_of' => 99.99,
                'language_of_study' => 'Arabic',
                'specialization' => 'Science'
            ]
        ]
    ],
    'program_details' => [
        'scholarship_ids' => [$scholarship->id],
        'specialization_1' => 'Computer Science',
        'university_name' => 'Test University',
        'country_name' => 'USA',
        'tuition_fee' => 50000,
        'has_active_program' => true,
        'terms_and_condition' => true
    ]
];

echo "\n=== TESTING FILE UPLOAD WITH CURL ===\n";

// Create curl command
$curlCommand = "curl -X POST http://127.0.0.1:8000/api/v1/applications/submit-complete \\\n";
$curlCommand .= "  -H \"Authorization: Bearer " . $user->createToken('test')->plainTextToken . "\" \\\n";
$curlCommand .= "  -F 'personal_info[en_name]=Test API User' \\\n";
$curlCommand .= "  -F 'personal_info[ar_name]=مستخدم تجريبي' \\\n";
$curlCommand .= "  -F 'personal_info[nationality]=Saudi' \\\n";
$curlCommand .= "  -F 'personal_info[gender]=male' \\\n";
$curlCommand .= "  -F 'personal_info[phone]=+966501234567' \\\n";
$curlCommand .= "  -F 'personal_info[passport_number]=API123456' \\\n";
$curlCommand .= "  -F 'personal_info[date_of_birth]=2000-01-15' \\\n";
$curlCommand .= "  -F 'personal_info[residence_country]=Saudi Arabia' \\\n";
$curlCommand .= "  -F 'personal_info[language]=Arabic' \\\n";
$curlCommand .= "  -F 'personal_info[is_studied_in_saudi]=true' \\\n";
$curlCommand .= "  -F 'personal_info[tahseeli_percentage]=85.5' \\\n";
$curlCommand .= "  -F 'personal_info[qudorat_percentage]=78.2' \\\n";
$curlCommand .= "  -F 'academic_info[qualifications][0][qualification_type]=high_school' \\\n";
$curlCommand .= "  -F 'academic_info[qualifications][0][institute_name]=Test School' \\\n";
$curlCommand .= "  -F 'academic_info[qualifications][0][year_of_graduation]=2019' \\\n";
$curlCommand .= "  -F 'academic_info[qualifications][0][cgpa]=98.5' \\\n";
$curlCommand .= "  -F 'academic_info[qualifications][0][cgpa_out_of]=99.99' \\\n";
$curlCommand .= "  -F 'academic_info[qualifications][0][language_of_study]=Arabic' \\\n";
$curlCommand .= "  -F 'academic_info[qualifications][0][specialization]=Science' \\\n";
$curlCommand .= "  -F 'program_details[scholarship_ids][0]={$scholarship->id}' \\\n";
$curlCommand .= "  -F 'program_details[specialization_1]=Computer Science' \\\n";
$curlCommand .= "  -F 'program_details[university_name]=Test University' \\\n";
$curlCommand .= "  -F 'program_details[country_name]=USA' \\\n";
$curlCommand .= "  -F 'program_details[tuition_fee]=50000' \\\n";
$curlCommand .= "  -F 'program_details[has_active_program]=true' \\\n";
$curlCommand .= "  -F 'program_details[terms_and_condition]=true' \\\n";
$curlCommand .= "  -F 'passport_copy=@test_files/passport_copy.pdf' \\\n";
$curlCommand .= "  -F 'personal_image=@test_files/personal_image.jpg' \\\n";
$curlCommand .= "  -F 'secondary_school_certificate=@test_files/tahsili_file.pdf' \\\n";
$curlCommand .= "  -F 'secondary_school_transcript=@test_files/qudorat_file.pdf' \\\n";
$curlCommand .= "  -F 'volunteering_certificate=@test_files/volunteering_certificate.pdf' \\\n";
$curlCommand .= "  -F 'offer_letter=@test_files/offer_letter.pdf' \\\n";
$curlCommand .= "  -F 'academic_info[qualifications][0][document_file]=@test_files/qualification_doc1.pdf'\n";

echo "📋 CURL Command:\n";
echo $curlCommand . "\n";

echo "\n=== CHECKING S3 BUCKET CONTENTS ===\n";
try {
    $files = Storage::disk('s3')->allFiles();
    echo "📁 Total files in S3: " . count($files) . "\n";

    if (count($files) > 0) {
        echo "📋 Recent files:\n";
        foreach (array_slice($files, -10) as $file) {
            echo "  - {$file}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error listing S3 files: " . $e->getMessage() . "\n";
}

echo "\n=== READY TO TEST ===\n";
echo "1. Copy the curl command above\n";
echo "2. Run it in your terminal\n";
echo "3. Check if files are uploaded to your S3 folders\n";
echo "4. Check the database to see if file paths are saved\n";
