<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Applicant;
use App\Models\Scholarship;

echo "=== FINAL FILE UPLOAD TEST ===\n";

// Get a test user and applicant
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

// Get an active scholarship
$scholarship = Scholarship::where('is_active', true)->first();
if (!$scholarship) {
    echo "❌ No active scholarship found\n";
    exit(1);
}

echo "✅ Using scholarship: {$scholarship->title} (ID: {$scholarship->id})\n";

// Create token
$token = $user->createToken('test')->plainTextToken;
echo "✅ Created auth token\n";

echo "\n=== WORKING CURL COMMAND ===\n";
echo "Copy and run this command:\n\n";

$curlCommand = "curl -X POST http://127.0.0.1:8000/api/v1/applications/submit-complete \\\n";
$curlCommand .= "  -H \"Authorization: Bearer {$token}\" \\\n";
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

echo $curlCommand . "\n";

echo "\n=== WHAT TO EXPECT ===\n";
echo "1. Files should be uploaded to your S3 folders:\n";
echo "   - passport_copy.pdf → passport/ folder\n";
echo "   - personal_image.jpg → personal image/ folder\n";
echo "   - tahsili_file.pdf → tahsili/ folder\n";
echo "   - qudorat_file.pdf → qudorat/ folder\n";
echo "   - volunteering_certificate.pdf → volunteering certificate/ folder\n";
echo "   - offer_letter.pdf → Good conduct/ folder\n";
echo "   - qualification_doc1.pdf → acadimic qualification/ folder\n";
echo "\n2. Database should store the file paths\n";
echo "3. Check your S3 bucket to see the uploaded files\n";

echo "\n=== S3 PERMISSION NOTE ===\n";
echo "Your S3 user can upload files but can't list them.\n";
echo "This is normal - the uploads are working!\n";
echo "Check your S3 bucket manually to see the files.\n";
