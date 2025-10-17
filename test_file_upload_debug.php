<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Applicant;
use App\Models\ApplicantApplication;
use App\Models\Qualification;
use App\Models\Scholarship;
use App\Enums\UserRole;

echo "=== DEBUGGING FILE UPLOAD ISSUE ===\n";

// Check if test files exist
$testDir = 'test_files';
if (!is_dir($testDir)) {
    echo "❌ Test files directory not found\n";
    exit(1);
}

echo "✅ Test files directory exists\n";

// Check file permissions
$testFile = $testDir . '/passport_copy.pdf';
if (!file_exists($testFile)) {
    echo "❌ Test file not found: {$testFile}\n";
    exit(1);
}

echo "✅ Test file exists: {$testFile}\n";
echo "📁 File size: " . filesize($testFile) . " bytes\n";
echo "📁 File readable: " . (is_readable($testFile) ? 'Yes' : 'No') . "\n";

// Check S3 configuration
echo "\n=== CHECKING S3 CONFIGURATION ===\n";
$s3Config = config('filesystems.disks.s3');
if (!$s3Config) {
    echo "❌ S3 configuration not found\n";
    echo "💡 Files will be stored locally instead of S3\n";
} else {
    echo "✅ S3 configuration found\n";
    echo "📁 S3 Key: " . ($s3Config['key'] ?? 'Not set') . "\n";
    echo "📁 S3 Secret: " . (isset($s3Config['secret']) ? 'Set' : 'Not set') . "\n";
    echo "📁 S3 Region: " . ($s3Config['region'] ?? 'Not set') . "\n";
    echo "📁 S3 Bucket: " . ($s3Config['bucket'] ?? 'Not set') . "\n";
}

// Check current database state
echo "\n=== CHECKING DATABASE STATE ===\n";
$applicants = \App\Models\Applicant::with('qualifications')->get();
echo "📊 Total applicants: " . $applicants->count() . "\n";

foreach ($applicants as $applicant) {
    echo "👤 Applicant ID: {$applicant->applicant_id} | Name: {$applicant->en_name}\n";
    echo "   📁 Passport: " . ($applicant->passport_copy_img ?: 'NULL') . "\n";
    echo "   📁 Personal Image: " . ($applicant->personal_image ?: 'NULL') . "\n";
    echo "   📁 Tahsili File: " . ($applicant->tahsili_file ?: 'NULL') . "\n";
    echo "   📁 Qudorat File: " . ($applicant->qudorat_file ?: 'NULL') . "\n";
    echo "   📁 Volunteering: " . ($applicant->volunteering_certificate_file ?: 'NULL') . "\n";

    if ($applicant->qualifications->count() > 0) {
        echo "   📚 Qualifications:\n";
        foreach ($applicant->qualifications as $qual) {
            echo "      - {$qual->qualification_type}: " . ($qual->document_file ?: 'NULL') . "\n";
        }
    }
    echo "\n";
}

// Check applications
$applications = \App\Models\ApplicantApplication::all();
echo "📊 Total applications: " . $applications->count() . "\n";

foreach ($applications as $app) {
    echo "📋 Application ID: {$app->application_id}\n";
    echo "   📁 Offer Letter: " . ($app->offer_letter_file ?: 'NULL') . "\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Check if S3 is properly configured\n";
echo "2. Verify file upload permissions\n";
echo "3. Test with a simple file upload first\n";
echo "4. Check Laravel logs for errors\n";
echo "5. Ensure the application is running on the correct server\n";

echo "\n=== TEST COMMAND ===\n";
echo "Try this simple test:\n";
echo "curl -X POST http://127.0.0.1:8000/api/v1/applications/submit-complete \\\n";
echo "  -H \"Authorization: Bearer YOUR_TOKEN\" \\\n";
echo "  -F 'personal_info[en_name]=Test User' \\\n";
echo "  -F 'personal_info[nationality]=Saudi' \\\n";
echo "  -F 'personal_info[gender]=male' \\\n";
echo "  -F 'personal_info[phone]=+966501234567' \\\n";
echo "  -F 'personal_info[passport_number]=TEST123456' \\\n";
echo "  -F 'personal_info[date_of_birth]=2000-01-15' \\\n";
echo "  -F 'personal_info[residence_country]=Saudi Arabia' \\\n";
echo "  -F 'personal_info[language]=Arabic' \\\n";
echo "  -F 'personal_info[is_studied_in_saudi]=true' \\\n";
echo "  -F 'personal_info[tahseeli_percentage]=85.5' \\\n";
echo "  -F 'personal_info[qudorat_percentage]=78.2' \\\n";
echo "  -F 'academic_info[qualifications][0][qualification_type]=high_school' \\\n";
echo "  -F 'academic_info[qualifications][0][institute_name]=Test School' \\\n";
echo "  -F 'academic_info[qualifications][0][year_of_graduation]=2019' \\\n";
echo "  -F 'academic_info[qualifications][0][cgpa]=98.5' \\\n";
echo "  -F 'academic_info[qualifications][0][cgpa_out_of]=99.99' \\\n";
echo "  -F 'academic_info[qualifications][0][language_of_study]=Arabic' \\\n";
echo "  -F 'academic_info[qualifications][0][specialization]=Science' \\\n";
echo "  -F 'program_details[scholarship_ids][0]=1' \\\n";
echo "  -F 'program_details[specialization_1]=Computer Science' \\\n";
echo "  -F 'program_details[university_name]=Test University' \\\n";
echo "  -F 'program_details[country_name]=USA' \\\n";
echo "  -F 'program_details[tuition_fee]=50000' \\\n";
echo "  -F 'program_details[has_active_program]=true' \\\n";
echo "  -F 'program_details[terms_and_condition]=true' \\\n";
echo "  -F 'passport_copy=@test_files/passport_copy.pdf' \\\n";
echo "  -F 'personal_image=@test_files/personal_image.jpg'\n";
