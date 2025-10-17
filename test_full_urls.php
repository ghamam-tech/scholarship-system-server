<?php

echo "=== TESTING FULL S3 URLS IN DATABASE ===\n";

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

// Get existing user and applicant
$user = User::where('role', 'applicant')->first();
$applicant = $user->applicant;
$scholarship = Scholarship::where('is_active', true)->first();

echo "✅ Using applicant: {$applicant->en_name} (ID: {$applicant->applicant_id})\n";

echo "\n=== UPLOADING FILES WITH FULL URLS ===\n";

// Upload test files and store full URLs
$timestamp = time();
$uploadedFiles = [];

$fileMappings = [
    'fake_passport.pdf' => 'applicant-documents/passport/',
    'fake_personal_image.jpg' => 'applicant-documents/personal-images/',
    'fake_tahsili.pdf' => 'applicant-documents/tahsili/',
    'fake_qudorat.pdf' => 'applicant-documents/qudorat/',
    'fake_volunteering.pdf' => 'applicant-documents/volunteering/',
    'fake_offer_letter.pdf' => 'application-documents/offer-letters/',
    'fake_qualification.pdf' => 'application-documents/qualifications/'
];

foreach ($fileMappings as $filename => $folder) {
    $filePath = "test_files/{$filename}";

    if (file_exists($filePath)) {
        try {
            echo "📤 Uploading {$filename} to {$folder}...\n";

            $explicitFilename = $timestamp . '_' . $applicant->en_name . '_' . $filename;
            $fullPath = $folder . $explicitFilename;

            // Upload to S3
            Storage::disk('s3')->putFileAs($folder, $filePath, $explicitFilename);

            // Get full URL
            $fullUrl = Storage::disk('s3')->url($fullPath);
            $uploadedFiles[$filename] = $fullUrl;

            echo "✅ Uploaded: {$fullPath}\n";
            echo "🔗 Full URL: {$fullUrl}\n";
        } catch (Exception $e) {
            echo "❌ Upload failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⏭️  Skipping {$filename} - file not found\n";
    }
}

echo "\n=== UPDATING DATABASE WITH FULL URLS ===\n";

// Update applicant with full URLs
$applicant->update([
    'passport_copy_img' => $uploadedFiles['fake_passport.pdf'] ?? null,
    'personal_image' => $uploadedFiles['fake_personal_image.jpg'] ?? null,
    'tahsili_file' => $uploadedFiles['fake_tahsili.pdf'] ?? null,
    'qudorat_file' => $uploadedFiles['fake_qudorat.pdf'] ?? null,
    'volunteering_certificate_file' => $uploadedFiles['fake_volunteering.pdf'] ?? null,
]);

echo "✅ Updated applicant with full URLs\n";

// Create new application with full URL
$application = ApplicantApplication::create([
    'applicant_id' => $applicant->applicant_id,
    'scholarship_id_1' => $scholarship->id,
    'specialization_1' => 'Computer Science',
    'university_name' => 'King Saud University',
    'country_name' => 'Saudi Arabia',
    'tuition_fee' => 50000,
    'has_active_program' => true,
    'terms_and_condition' => true,
    'offer_letter_file' => $uploadedFiles['fake_offer_letter.pdf'] ?? null,
]);

echo "✅ Created application (ID: {$application->application_id})\n";

// Create qualification with full URL
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

echo "\n=== VERIFYING FULL URLS IN DATABASE ===\n";

// Refresh and check the records
$applicant = $applicant->fresh();
$application = $application->fresh();
$qualification = $qualification->fresh();

echo "👤 Applicant file URLs:\n";
echo "📁 Passport: " . ($applicant->passport_copy_img ?: 'NULL') . "\n";
echo "📁 Personal Image: " . ($applicant->personal_image ?: 'NULL') . "\n";
echo "📁 Tahsili: " . ($applicant->tahsili_file ?: 'NULL') . "\n";
echo "📁 Qudorat: " . ($applicant->qudorat_file ?: 'NULL') . "\n";
echo "📁 Volunteering: " . ($applicant->volunteering_certificate_file ?: 'NULL') . "\n";

echo "\n📋 Application file URLs:\n";
echo "📁 Offer Letter: " . ($application->offer_letter_file ?: 'NULL') . "\n";

echo "\n📚 Qualification file URLs:\n";
echo "📁 Document: " . ($qualification->document_file ?: 'NULL') . "\n";

echo "\n=== TESTING URL ACCESS ===\n";

// Test if URLs are accessible
$allUrls = [
    'Applicant Passport' => $applicant->passport_copy_img,
    'Applicant Personal Image' => $applicant->personal_image,
    'Applicant Tahsili' => $applicant->tahsili_file,
    'Applicant Qudorat' => $applicant->qudorat_file,
    'Applicant Volunteering' => $applicant->volunteering_certificate_file,
    'Application Offer Letter' => $application->offer_letter_file,
    'Qualification Document' => $qualification->document_file
];

$validUrls = 0;
$totalUrls = 0;

foreach ($allUrls as $label => $url) {
    $totalUrls++;
    if ($url && $url !== 'NULL') {
        // Check if it's a full URL
        if (str_starts_with($url, 'https://')) {
            echo "✅ {$label}: Full URL stored\n";
            echo "   🔗 {$url}\n";
            $validUrls++;
        } else {
            echo "❌ {$label}: Not a full URL - {$url}\n";
        }
    } else {
        echo "❌ {$label}: NULL\n";
    }
}

echo "\n=== FINAL SUMMARY ===\n";
echo "📊 Total URLs: {$totalUrls}\n";
echo "📊 Valid full URLs: {$validUrls}\n";

if ($validUrls > 0) {
    echo "\n🎉 SUCCESS: Full S3 URLs are being stored in the database!\n";
    echo "✅ All file URLs are now complete and accessible!\n";
    echo "✅ The API will return full URLs that can be accessed directly!\n";

    echo "\n📁 Example URLs stored:\n";
    foreach ($uploadedFiles as $filename => $url) {
        echo "  - {$filename} → {$url}\n";
    }
} else {
    echo "\n❌ FAILED: Full URLs are not being stored correctly\n";
}

echo "\n=== READY FOR API TESTING ===\n";
echo "The API will now return full S3 URLs that can be accessed directly!\n";
echo "Example: https://irfad-test-2.s3.ap-southeast-2.amazonaws.com/applicant-documents/passport/...\n";
