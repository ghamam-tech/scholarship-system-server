<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Applicant;
use App\Models\Qualification;
use App\Enums\UserRole;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

echo "🔍 Debugging Complete Profile Endpoint\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Create test user
$testEmail = 'debug.test.' . time() . '@example.com';
$user = User::create([
    'email' => $testEmail,
    'password' => bcrypt('password123'),
    'role' => UserRole::APPLICANT->value
]);

echo "✅ Created test user: {$testEmail}\n";

// Create test files
Storage::fake('s3');

$testFiles = [
    'passport_copy' => UploadedFile::fake()->create('test_passport.pdf', 100, 'application/pdf'),
    'personal_image' => UploadedFile::fake()->create('test_personal.jpg', 100, 'image/jpeg'),
    'secondary_school_certificate' => UploadedFile::fake()->create('test_certificate.pdf', 100, 'application/pdf'),
    'secondary_school_transcript' => UploadedFile::fake()->create('test_transcript.pdf', 100, 'application/pdf'),
    'volunteering_certificate' => UploadedFile::fake()->create('test_volunteering.pdf', 100, 'application/pdf'),
    'qualification_doc1' => UploadedFile::fake()->create('test_qualification1.pdf', 100, 'application/pdf'),
    'qualification_doc2' => UploadedFile::fake()->create('test_qualification2.pdf', 100, 'application/pdf')
];

echo "✅ Created test files\n\n";

// Create a mock request
$request = new \Illuminate\Http\Request();
$request->setUserResolver(function () use ($user) {
    return $user;
});

// Set the request data with the correct structure
$requestData = [
    'personal_info' => [
        'ar_name' => 'أحمد محمد علي',
        'en_name' => 'Ahmed Mohamed Ali',
        'nationality' => 'Saudi',
        'gender' => 'male',
        'place_of_birth' => 'Riyadh',
        'phone' => '+966501234567',
        'passport_number' => 'A12345678',
        'date_of_birth' => '2000-01-15',
        'parent_contact_name' => 'Mohamed Ahmed',
        'parent_contact_phone' => '+966501234568',
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
                'institute_name' => 'Al-Nahda High School',
                'year_of_graduation' => 2019,
                'cgpa' => 98.5,
                'cgpa_out_of' => 99.99,
                'language_of_study' => 'Arabic',
                'specialization' => 'Science',
                'research_title' => ''
            ],
            [
                'qualification_type' => 'bachelor',
                'institute_name' => 'King Saud University',
                'year_of_graduation' => 2023,
                'cgpa' => 3.8,
                'cgpa_out_of' => 4.0,
                'language_of_study' => 'Arabic',
                'specialization' => 'Computer Science',
                'research_title' => 'Machine Learning Applications'
            ]
        ]
    ]
];

$request->merge($requestData);

// Add files to request with the correct structure
$request->files->set('passport_copy', $testFiles['passport_copy']);
$request->files->set('personal_image', $testFiles['personal_image']);
$request->files->set('secondary_school_certificate', $testFiles['secondary_school_certificate']);
$request->files->set('secondary_school_transcript', $testFiles['secondary_school_transcript']);
$request->files->set('volunteering_certificate', $testFiles['volunteering_certificate']);

// Add qualification files with the correct nested structure
$request->files->set('academic_info.qualifications.0.document_file', $testFiles['qualification_doc1']);
$request->files->set('academic_info.qualifications.1.document_file', $testFiles['qualification_doc2']);

echo "🔍 DEBUGGING REQUEST STRUCTURE:\n";
echo "Request data keys: " . implode(', ', array_keys($request->all())) . "\n";
echo "Request files keys: " . implode(', ', array_keys($request->allFiles())) . "\n\n";

echo "📋 REQUEST DATA:\n";
echo json_encode($request->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "📁 REQUEST FILES:\n";
foreach ($request->allFiles() as $key => $file) {
    echo "  {$key}: " . $file->getClientOriginalName() . "\n";
}

echo "\n🔄 TESTING FILE ACCESS:\n";
echo "Has academic_info.qualifications.0.document_file: " . ($request->hasFile('academic_info.qualifications.0.document_file') ? 'YES' : 'NO') . "\n";
echo "Has academic_info.qualifications.1.document_file: " . ($request->hasFile('academic_info.qualifications.1.document_file') ? 'YES' : 'NO') . "\n";

echo "\n🔄 PROCESSING REQUEST...\n";

try {
    // Call the controller method directly
    $controller = new \App\Http\Controllers\ApplicantController();
    $response = $controller->completeProfile($request);

    echo "📥 RESPONSE DETAILS:\n";
    echo "HTTP Status Code: {$response->getStatusCode()}\n\n";

    $responseData = $response->getData(true);
    echo "📄 RESPONSE BODY:\n";
    echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n";

// Check database to verify data was saved
echo "🔍 DATABASE VERIFICATION:\n";

// Check if applicant was created
$applicant = Applicant::where('user_id', $user->user_id)->first();
if ($applicant) {
    echo "✅ Applicant record created in database:\n";
    echo "  - Applicant ID: {$applicant->applicant_id}\n";
    echo "  - Arabic Name: {$applicant->ar_name}\n";
    echo "  - English Name: {$applicant->en_name}\n";
    echo "  - Nationality: {$applicant->nationality}\n";
    echo "  - Phone: {$applicant->phone}\n";
    echo "  - Passport: {$applicant->passport_number}\n";
    echo "  - Tahseeli: {$applicant->tahseeli_percentage}%\n";
    echo "  - Qudorat: {$applicant->qudorat_percentage}%\n";

    // Check file paths
    echo "\n  📁 FILE PATHS:\n";
    echo "  - Passport Copy: {$applicant->passport_copy_img}\n";
    echo "  - Personal Image: {$applicant->personal_image}\n";
    echo "  - Certificate: {$applicant->secondary_school_certificate_file}\n";
    echo "  - Transcript: {$applicant->secondary_school_transcript_file}\n";
    echo "  - Volunteering: {$applicant->volunteering_certificate_file}\n";
    echo "  - Tahsili: {$applicant->tahsili_file}\n";
    echo "  - Qudorat: {$applicant->qudorat_file}\n";
} else {
    echo "❌ No applicant record found in database\n";
}

// Check qualifications
$qualifications = Qualification::where('applicant_id', $applicant->applicant_id ?? 0)->get();
if ($qualifications->count() > 0) {
    echo "\n✅ Qualifications created in database:\n";
    foreach ($qualifications as $index => $qual) {
        echo "  Qualification " . ($index + 1) . ":\n";
        echo "    - ID: {$qual->qualification_id}\n";
        echo "    - Type: {$qual->qualification_type}\n";
        echo "    - Institute: {$qual->institute_name}\n";
        echo "    - Year: {$qual->year_of_graduation}\n";
        echo "    - CGPA: {$qual->cgpa}/{$qual->cgpa_out_of}\n";
        echo "    - Language: {$qual->language_of_study}\n";
        echo "    - Specialization: {$qual->specialization}\n";
        echo "    - Research Title: {$qual->research_title}\n";
        echo "    - Document: {$qual->document_file}\n";
    }
} else {
    echo "\n❌ No qualifications found in database\n";
}

echo "\n";

// Clean up test user
$user->delete();
echo "✅ Deleted test user\n";

echo "\n🎉 Debug completed!\n";
echo "=" . str_repeat("=", 50) . "\n";
