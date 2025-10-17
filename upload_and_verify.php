<?php

echo "=== UPLOAD FILES AND VERIFY IN DATABASE ===\n";

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Applicant;
use App\Models\Scholarship;
use App\Models\ApplicantApplication;
use App\Models\Qualification;
use Illuminate\Support\Facades\Storage;

echo "✅ Laravel loaded successfully\n";

// Get test user
$user = User::where('role', 'applicant')->first();
if (!$user) {
    echo "❌ No applicant user found\n";
    exit(1);
}

$applicant = $user->applicant;
if (!$applicant) {
    echo "❌ No applicant profile found\n";
    exit(1);
}

echo "✅ Using applicant: {$applicant->en_name} (ID: {$applicant->applicant_id})\n";

// Get scholarship
$scholarship = Scholarship::where('is_active', true)->first();
if (!$scholarship) {
    echo "❌ No active scholarship found\n";
    exit(1);
}

echo "✅ Using scholarship: {$scholarship->title} (ID: {$scholarship->id})\n";

// Create token
$token = $user->createToken('test')->plainTextToken;
echo "✅ Created auth token\n";

echo "\n=== UPLOADING FILES TO S3 ===\n";

// Upload files directly to S3
$uploads = [
    'passport_copy.pdf' => 'passport/',
    'personal_image.jpg' => 'personal image/',
    'tahsili_file.pdf' => 'tahsili/',
    'qudorat_file.pdf' => 'qudorat/',
    'volunteering_certificate.pdf' => 'volunteering certificate/',
    'offer_letter.pdf' => 'Good conduct/',
    'qualification_doc1.pdf' => 'acadimic qualification/'
];

$uploadedFiles = [];

foreach ($uploads as $filename => $folder) {
    $filePath = "test_files/{$filename}";

    if (file_exists($filePath)) {
        try {
            echo "📤 Uploading {$filename} to {$folder}...\n";
            $uploadedPath = Storage::disk('s3')->putFile($folder, $filePath);
            $uploadedFiles[$filename] = $uploadedPath;
            echo "✅ Success: {$uploadedPath}\n";
        } catch (Exception $e) {
            echo "❌ Failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⏭️  Skipping {$filename} - file not found\n";
    }
}

echo "\n=== UPDATING DATABASE WITH FILE PATHS ===\n";

// Update applicant with file paths
$applicant->update([
    'passport_copy_img' => $uploadedFiles['passport_copy.pdf'] ?? null,
    'personal_image' => $uploadedFiles['personal_image.jpg'] ?? null,
    'tahsili_file' => $uploadedFiles['tahsili_file.pdf'] ?? null,
    'qudorat_file' => $uploadedFiles['qudorat_file.pdf'] ?? null,
    'volunteering_certificate_file' => $uploadedFiles['volunteering_certificate.pdf'] ?? null,
]);

echo "✅ Updated applicant with file paths\n";

// Create application
$application = ApplicantApplication::create([
    'applicant_id' => $applicant->applicant_id,
    'scholarship_id_1' => $scholarship->id,
    'specialization_1' => 'Computer Science',
    'university_name' => 'Test University',
    'country_name' => 'USA',
    'tuition_fee' => 50000,
    'has_active_program' => true,
    'terms_and_condition' => true,
    'offer_letter_file' => $uploadedFiles['offer_letter.pdf'] ?? null,
]);

echo "✅ Created application (ID: {$application->application_id})\n";

// Create qualification
$qualification = Qualification::create([
    'applicant_id' => $applicant->applicant_id,
    'qualification_type' => 'high_school',
    'institute_name' => 'Test School',
    'year_of_graduation' => 2019,
    'cgpa' => 98.5,
    'cgpa_out_of' => 99.99,
    'language_of_study' => 'Arabic',
    'specialization' => 'Science',
    'document_file' => $uploadedFiles['qualification_doc1.pdf'] ?? null,
]);

echo "✅ Created qualification (ID: {$qualification->id})\n";

echo "\n=== VERIFYING DATABASE ===\n";

// Check applicant files
$applicant = $applicant->fresh();
echo "👤 Applicant: {$applicant->en_name}\n";
echo "📁 Passport: " . ($applicant->passport_copy_img ?: 'NULL') . "\n";
echo "📁 Personal Image: " . ($applicant->personal_image ?: 'NULL') . "\n";
echo "📁 Tahsili: " . ($applicant->tahsili_file ?: 'NULL') . "\n";
echo "📁 Qudorat: " . ($applicant->qudorat_file ?: 'NULL') . "\n";
echo "📁 Volunteering: " . ($applicant->volunteering_certificate_file ?: 'NULL') . "\n";

// Check application files
$application = $application->fresh();
echo "\n📋 Application: {$application->application_id}\n";
echo "📁 Offer Letter: " . ($application->offer_letter_file ?: 'NULL') . "\n";

// Check qualification files
$qualification = $qualification->fresh();
echo "\n📚 Qualification: {$qualification->id}\n";
echo "📁 Document: " . ($qualification->document_file ?: 'NULL') . "\n";

echo "\n=== SUMMARY ===\n";
$totalFiles = 0;
$savedFiles = 0;

if ($applicant->passport_copy_img) $savedFiles++;
if ($applicant->personal_image) $savedFiles++;
if ($applicant->tahsili_file) $savedFiles++;
if ($applicant->qudorat_file) $savedFiles++;
if ($applicant->volunteering_certificate_file) $savedFiles++;
if ($application->offer_letter_file) $savedFiles++;
if ($qualification->document_file) $savedFiles++;

$totalFiles = 7;

echo "📊 Files uploaded: {$savedFiles}/{$totalFiles}\n";

if ($savedFiles > 0) {
    echo "✅ SUCCESS: Files are now saved in the database!\n";
    echo "🔍 Check your S3 bucket to see the uploaded files\n";
} else {
    echo "❌ FAILED: No files were saved\n";
}

echo "\n=== NEXT STEPS ===\n";
echo "1. Check your S3 bucket for the uploaded files\n";
echo "2. The database should now show file paths instead of NULL\n";
echo "3. Test the API endpoint to confirm it works\n";
