<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Applicant;
use App\Models\Scholarship;
use Illuminate\Support\Facades\Storage;

echo "=== TESTING FILE UPLOAD TO YOUR S3 FOLDERS ===\n";

// Check if test files exist
$testFiles = [
    'passport_copy' => 'test_files/passport_copy.pdf',
    'personal_image' => 'test_files/personal_image.jpg',
    'tahsili' => 'test_files/tahsili_file.pdf',
    'qudorat' => 'test_files/qudorat_file.pdf',
    'volunteering' => 'test_files/volunteering_certificate.pdf',
    'qualification' => 'test_files/qualification_doc1.pdf',
    'offer_letter' => 'test_files/offer_letter.pdf'
];

echo "📁 Checking test files...\n";
foreach ($testFiles as $type => $file) {
    if (file_exists($file)) {
        echo "✅ {$type}: {$file} (" . filesize($file) . " bytes)\n";
    } else {
        echo "❌ {$type}: {$file} - NOT FOUND\n";
    }
}

echo "\n=== TESTING S3 UPLOADS ===\n";

// Test upload to each folder
$uploadTests = [
    'passport_copy.pdf' => 'passport/',
    'personal_image.jpg' => 'personal image/',
    'tahsili_file.pdf' => 'tahsili/',
    'qudorat_file.pdf' => 'qudorat/',
    'volunteering_certificate.pdf' => 'volunteering certificate/',
    'qualification_doc1.pdf' => 'acadimic qualification/',
    'offer_letter.pdf' => 'Good conduct/'
];

foreach ($uploadTests as $filename => $folder) {
    $filePath = "test_files/{$filename}";

    if (file_exists($filePath)) {
        try {
            echo "📤 Uploading {$filename} to {$folder}...\n";
            $uploadedPath = Storage::disk('s3')->putFile($folder, $filePath);
            echo "✅ Success: {$uploadedPath}\n";

            // Verify file exists
            if (Storage::disk('s3')->exists($uploadedPath)) {
                echo "✅ Verified: File exists in S3\n";
            } else {
                echo "❌ Error: File not found in S3\n";
            }
        } catch (Exception $e) {
            echo "❌ Upload failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⏭️  Skipping {$filename} - file not found\n";
    }
    echo "\n";
}

echo "=== CHECKING S3 CONFIGURATION ===\n";
$s3Config = config('filesystems.disks.s3');
echo "S3 Driver: " . ($s3Config['driver'] ?? 'Not set') . "\n";
echo "S3 Bucket: " . ($s3Config['bucket'] ?? 'Not set') . "\n";
echo "S3 Region: " . ($s3Config['region'] ?? 'Not set') . "\n";

if (isset($s3Config['key']) && $s3Config['key']) {
    echo "S3 Key: Set ✅\n";
} else {
    echo "S3 Key: Not set ❌\n";
}

if (isset($s3Config['secret']) && $s3Config['secret']) {
    echo "S3 Secret: Set ✅\n";
} else {
    echo "S3 Secret: Not set ❌\n";
}

echo "\n=== NEXT STEPS ===\n";
echo "1. Check your S3 bucket to see if files were uploaded\n";
echo "2. If uploads work, test the API endpoint\n";
echo "3. The files should now be stored in your existing folder structure\n";
