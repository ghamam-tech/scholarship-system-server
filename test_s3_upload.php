<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Storage;

echo "=== TESTING S3 UPLOAD WITH YOUR FOLDER STRUCTURE ===\n";

// Test file upload to your existing S3 folders
$testFile = 'test_files/passport_copy.pdf';

if (!file_exists($testFile)) {
    echo "❌ Test file not found: {$testFile}\n";
    exit(1);
}

echo "✅ Test file found: {$testFile}\n";

// Test upload to passport folder
try {
    $path = Storage::disk('s3')->putFile('passport/', $testFile);
    echo "✅ Upload successful to: {$path}\n";
    
    // Check if file exists
    if (Storage::disk('s3')->exists($path)) {
        echo "✅ File confirmed in S3\n";
    } else {
        echo "❌ File not found in S3\n";
    }
    
} catch (Exception $e) {
    echo "❌ Upload failed: " . $e->getMessage() . "\n";
}

// Test upload to personal image folder
try {
    $testImage = 'test_files/personal_image.jpg';
    if (file_exists($testImage)) {
        $path = Storage::disk('s3')->putFile('personal image/', $testImage);
        echo "✅ Image upload successful to: {$path}\n";
    }
} catch (Exception $e) {
    echo "❌ Image upload failed: " . $e->getMessage() . "\n";
}

// Test upload to tahsili folder
try {
    $testTahsili = 'test_files/tahsili_file.pdf';
    if (file_exists($testTahsili)) {
        $path = Storage::disk('s3')->putFile('tahsili/', $testTahsili);
        echo "✅ Tahsili upload successful to: {$path}\n";
    }
} catch (Exception $e) {
    echo "❌ Tahsili upload failed: " . $e->getMessage() . "\n";
}

echo "\n=== S3 CONFIGURATION CHECK ===\n";
$s3Config = config('filesystems.disks.s3');
echo "S3 Driver: " . ($s3Config['driver'] ?? 'Not set') . "\n";
echo "S3 Bucket: " . ($s3Config['bucket'] ?? 'Not set') . "\n";
echo "S3 Region: " . ($s3Config['region'] ?? 'Not set') . "\n";
echo "S3 Key: " . (isset($s3Config['key']) ? 'Set' : 'Not set') . "\n";
echo "S3 Secret: " . (isset($s3Config['secret']) ? 'Set' : 'Not set') . "\n";

echo "\n=== NEXT STEPS ===\n";
echo "1. If uploads work, test the API endpoint\n";
echo "2. Check your S3 bucket to see the uploaded files\n";
echo "3. The files should now be stored in your existing folder structure\n";
