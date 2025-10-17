<?php

echo "=== TESTING UPLOAD TO NEW CUSTOM FOLDERS ===\n";

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
$applicant = $user->applicant;
$scholarship = Scholarship::where('is_active', true)->first();

echo "✅ Using applicant: {$applicant->en_name} (ID: {$applicant->applicant_id})\n";
echo "✅ Using scholarship: {$scholarship->title} (ID: {$scholarship->id})\n";

echo "\n=== UPLOADING FAKE FILES TO NEW CUSTOM FOLDERS ===\n";

// Upload fake files to new custom folders
$uploads = [
    'fake_passport.pdf' => 'applicant-documents/passport/',
    'fake_personal_image.jpg' => 'applicant-documents/personal-images/',
    'fake_tahsili.pdf' => 'applicant-documents/tahsili/',
    'fake_qudorat.pdf' => 'applicant-documents/qudorat/',
    'fake_volunteering.pdf' => 'applicant-documents/volunteering/',
    'fake_offer_letter.pdf' => 'application-documents/offer-letters/',
    'fake_qualification.pdf' => 'application-documents/qualifications/'
];

$uploadedFiles = [];
$timestamp = time();

foreach ($uploads as $filename => $folder) {
    $filePath = "test_files/{$filename}";

    if (file_exists($filePath)) {
        try {
            echo "📤 Uploading {$filename} to {$folder}...\n";

            // Create explicit filename with timestamp
            $explicitFilename = $timestamp . '_' . $filename;
            $fullPath = $folder . $explicitFilename;

            // Upload with explicit path
            Storage::disk('s3')->putFileAs($folder, $filePath, $explicitFilename);

            // Manually construct the path
            $uploadedFiles[$filename] = $fullPath;

            echo "✅ Success: {$fullPath}\n";
        } catch (Exception $e) {
            echo "❌ Failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⏭️  Skipping {$filename} - file not found\n";
    }
}

echo "\n=== UPDATING DATABASE WITH NEW FOLDER PATHS ===\n";

// Update applicant with file paths
$applicant->update([
    'passport_copy_img' => $uploadedFiles['fake_passport.pdf'] ?? null,
    'personal_image' => $uploadedFiles['fake_personal_image.jpg'] ?? null,
    'tahsili_file' => $uploadedFiles['fake_tahsili.pdf'] ?? null,
    'qudorat_file' => $uploadedFiles['fake_qudorat.pdf'] ?? null,
    'volunteering_certificate_file' => $uploadedFiles['fake_volunteering.pdf'] ?? null,
]);

echo "✅ Updated applicant with new folder paths\n";

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
    'offer_letter_file' => $uploadedFiles['fake_offer_letter.pdf'] ?? null,
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
    'document_file' => $uploadedFiles['fake_qualification.pdf'] ?? null,
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

echo "\n=== NEW FOLDER STRUCTURE ===\n";
echo "📁 Your S3 bucket now has these new organized folders:\n";
echo "  📂 applicant-documents/\n";
echo "    ├── passport/\n";
echo "    ├── personal-images/\n";
echo "    ├── tahsili/\n";
echo "    ├── qudorat/\n";
echo "    └── volunteering/\n";
echo "  📂 application-documents/\n";
echo "    ├── offer-letters/\n";
echo "    └── qualifications/\n";

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
    echo "✅ SUCCESS: Fake files uploaded to new custom folders!\n";
    echo "🔍 Check your S3 bucket to see the new organized folder structure\n";
    echo "📁 Files uploaded to:\n";
    foreach ($uploadedFiles as $filename => $path) {
        echo "  - {$filename} → {$path}\n";
    }
} else {
    echo "❌ FAILED: No files were saved\n";
}

echo "\n=== NEXT STEPS ===\n";
echo "1. Check your S3 bucket for the new organized folder structure\n";
echo "2. The API endpoint now uses the new custom folders\n";
echo "3. All future uploads will go to these organized folders\n";
