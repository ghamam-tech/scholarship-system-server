<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Applicant;
use App\Models\Scholarship;
use Illuminate\Support\Facades\Storage;

echo "=== SIMPLE FILE UPLOAD TEST ===\n";

// Test direct S3 upload first
echo "📤 Testing direct S3 upload...\n";

$testFile = 'test_files/passport_copy.pdf';
if (file_exists($testFile)) {
    try {
        $path = Storage::disk('s3')->putFile('passport/', $testFile);
        echo "✅ Direct upload successful: {$path}\n";

        // Try to get the file
        if (Storage::disk('s3')->exists($path)) {
            echo "✅ File confirmed in S3\n";
            $size = Storage::disk('s3')->size($path);
            echo "📁 File size: {$size} bytes\n";
        } else {
            echo "❌ File not found in S3\n";
        }
    } catch (Exception $e) {
        echo "❌ Direct upload failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Test file not found: {$testFile}\n";
}

echo "\n=== TESTING API ENDPOINT ===\n";

// Get user and create token
$user = User::where('role', 'applicant')->first();
if (!$user) {
    echo "❌ No applicant user found\n";
    exit(1);
}

$token = $user->createToken('test')->plainTextToken;
echo "✅ Created token: " . substr($token, 0, 20) . "...\n";

// Get scholarship
$scholarship = Scholarship::where('is_active', true)->first();
if (!$scholarship) {
    echo "❌ No active scholarship found\n";
    exit(1);
}

echo "✅ Using scholarship ID: {$scholarship->id}\n";

// Create a simple test request using Guzzle
echo "\n📤 Testing API endpoint with file upload...\n";

try {
    $client = new \GuzzleHttp\Client();

    $response = $client->post('http://127.0.0.1:8000/api/v1/applications/submit-complete', [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
        ],
        'multipart' => [
            [
                'name' => 'personal_info[en_name]',
                'contents' => 'Test API User'
            ],
            [
                'name' => 'personal_info[nationality]',
                'contents' => 'Saudi'
            ],
            [
                'name' => 'personal_info[gender]',
                'contents' => 'male'
            ],
            [
                'name' => 'personal_info[phone]',
                'contents' => '+966501234567'
            ],
            [
                'name' => 'personal_info[passport_number]',
                'contents' => 'API123456'
            ],
            [
                'name' => 'personal_info[date_of_birth]',
                'contents' => '2000-01-15'
            ],
            [
                'name' => 'personal_info[residence_country]',
                'contents' => 'Saudi Arabia'
            ],
            [
                'name' => 'personal_info[language]',
                'contents' => 'Arabic'
            ],
            [
                'name' => 'personal_info[is_studied_in_saudi]',
                'contents' => 'true'
            ],
            [
                'name' => 'personal_info[tahseeli_percentage]',
                'contents' => '85.5'
            ],
            [
                'name' => 'personal_info[qudorat_percentage]',
                'contents' => '78.2'
            ],
            [
                'name' => 'academic_info[qualifications][0][qualification_type]',
                'contents' => 'high_school'
            ],
            [
                'name' => 'academic_info[qualifications][0][institute_name]',
                'contents' => 'Test School'
            ],
            [
                'name' => 'academic_info[qualifications][0][year_of_graduation]',
                'contents' => '2019'
            ],
            [
                'name' => 'academic_info[qualifications][0][cgpa]',
                'contents' => '98.5'
            ],
            [
                'name' => 'academic_info[qualifications][0][cgpa_out_of]',
                'contents' => '99.99'
            ],
            [
                'name' => 'academic_info[qualifications][0][language_of_study]',
                'contents' => 'Arabic'
            ],
            [
                'name' => 'academic_info[qualifications][0][specialization]',
                'contents' => 'Science'
            ],
            [
                'name' => 'program_details[scholarship_ids][0]',
                'contents' => $scholarship->id
            ],
            [
                'name' => 'program_details[specialization_1]',
                'contents' => 'Computer Science'
            ],
            [
                'name' => 'program_details[university_name]',
                'contents' => 'Test University'
            ],
            [
                'name' => 'program_details[country_name]',
                'contents' => 'USA'
            ],
            [
                'name' => 'program_details[tuition_fee]',
                'contents' => '50000'
            ],
            [
                'name' => 'program_details[has_active_program]',
                'contents' => 'true'
            ],
            [
                'name' => 'program_details[terms_and_condition]',
                'contents' => 'true'
            ],
            [
                'name' => 'passport_copy',
                'contents' => fopen('test_files/passport_copy.pdf', 'r'),
                'filename' => 'passport_copy.pdf'
            ],
            [
                'name' => 'personal_image',
                'contents' => fopen('test_files/personal_image.jpg', 'r'),
                'filename' => 'personal_image.jpg'
            ],
            [
                'name' => 'secondary_school_certificate',
                'contents' => fopen('test_files/tahsili_file.pdf', 'r'),
                'filename' => 'tahsili_file.pdf'
            ],
            [
                'name' => 'secondary_school_transcript',
                'contents' => fopen('test_files/qudorat_file.pdf', 'r'),
                'filename' => 'qudorat_file.pdf'
            ],
            [
                'name' => 'volunteering_certificate',
                'contents' => fopen('test_files/volunteering_certificate.pdf', 'r'),
                'filename' => 'volunteering_certificate.pdf'
            ],
            [
                'name' => 'offer_letter',
                'contents' => fopen('test_files/offer_letter.pdf', 'r'),
                'filename' => 'offer_letter.pdf'
            ],
            [
                'name' => 'academic_info[qualifications][0][document_file]',
                'contents' => fopen('test_files/qualification_doc1.pdf', 'r'),
                'filename' => 'qualification_doc1.pdf'
            ]
        ]
    ]);

    echo "✅ API request successful!\n";
    echo "📊 Status Code: " . $response->getStatusCode() . "\n";
    echo "📋 Response:\n";
    echo $response->getBody() . "\n";
} catch (Exception $e) {
    echo "❌ API request failed: " . $e->getMessage() . "\n";
    if ($e->hasResponse()) {
        echo "📋 Error Response: " . $e->getResponse()->getBody() . "\n";
    }
}

echo "\n=== CHECKING DATABASE ===\n";

// Check if files were saved in database
$applicant = $user->applicant;
if ($applicant) {
    echo "👤 Applicant: {$applicant->en_name}\n";
    echo "📁 Passport: " . ($applicant->passport_copy_img ?: 'NULL') . "\n";
    echo "📁 Personal Image: " . ($applicant->personal_image ?: 'NULL') . "\n";
    echo "📁 Tahsili: " . ($applicant->tahsili_file ?: 'NULL') . "\n";
    echo "📁 Qudorat: " . ($applicant->qudorat_file ?: 'NULL') . "\n";
    echo "📁 Volunteering: " . ($applicant->volunteering_certificate_file ?: 'NULL') . "\n";
}

// Check applications
$applications = \App\Models\ApplicantApplication::where('applicant_id', $applicant->applicant_id)->get();
echo "📋 Applications: " . $applications->count() . "\n";

foreach ($applications as $app) {
    echo "  - Application ID: {$app->application_id}\n";
    echo "    📁 Offer Letter: " . ($app->offer_letter_file ?: 'NULL') . "\n";
}

// Check qualifications
$qualifications = \App\Models\Qualification::where('applicant_id', $applicant->applicant_id)->get();
echo "📚 Qualifications: " . $qualifications->count() . "\n";

foreach ($qualifications as $qual) {
    echo "  - {$qual->qualification_type}: " . ($qual->document_file ?: 'NULL') . "\n";
}
