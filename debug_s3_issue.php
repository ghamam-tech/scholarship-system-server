<?php

echo "=== DEBUGGING S3 ISSUE ===\n";

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Storage;

echo "✅ Laravel loaded successfully\n";

// Check S3 configuration
$s3Config = config('filesystems.disks.s3');
echo "📁 S3 Bucket: " . ($s3Config['bucket'] ?? 'Not set') . "\n";
echo "📁 S3 Region: " . ($s3Config['region'] ?? 'Not set') . "\n";

echo "\n=== TESTING DIRECT S3 UPLOAD ===\n";

// Test uploading to root level first
try {
    echo "📤 Testing upload to root level...\n";
    $testContent = "Test file for root level upload";
    $rootPath = "test-root-file.txt";

    Storage::disk('s3')->put($rootPath, $testContent);
    echo "✅ Root level upload successful\n";
} catch (Exception $e) {
    echo "❌ Root level upload failed: " . $e->getMessage() . "\n";
}

// Test uploading to new folder structure
try {
    echo "📤 Testing upload to new folder structure...\n";
    $testContent = "Test file for new folder structure";
    $newPath = "applicant-documents/test-file.txt";

    Storage::disk('s3')->put($newPath, $testContent);
    echo "✅ New folder upload successful\n";
} catch (Exception $e) {
    echo "❌ New folder upload failed: " . $e->getMessage() . "\n";
}

echo "\n=== CREATING FOLDERS WITH DIFFERENT APPROACH ===\n";

// Try creating folders with a different approach
$folders = [
    'applicant-documents/',
    'application-documents/',
    'applicant-documents/passport/',
    'applicant-documents/personal-images/',
    'applicant-documents/tahsili/',
    'applicant-documents/qudorat/',
    'applicant-documents/volunteering/',
    'application-documents/offer-letters/',
    'application-documents/qualifications/'
];

foreach ($folders as $folder) {
    try {
        echo "📁 Creating folder: {$folder}\n";
        $placeholderPath = $folder . "folder-created.txt";
        Storage::disk('s3')->put($placeholderPath, "Folder created at " . date('Y-m-d H:i:s'));
        echo "✅ Created: {$placeholderPath}\n";
    } catch (Exception $e) {
        echo "❌ Failed to create {$folder}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== UPLOADING REAL FILES TO NEW STRUCTURE ===\n";

// Upload real files to the new structure
$realFiles = [
    'fake_passport.pdf' => 'applicant-documents/passport/',
    'fake_personal_image.jpg' => 'applicant-documents/personal-images/',
    'fake_tahsili.pdf' => 'applicant-documents/tahsili/',
    'fake_qudorat.pdf' => 'applicant-documents/qudorat/',
    'fake_volunteering.pdf' => 'applicant-documents/volunteering/',
    'fake_offer_letter.pdf' => 'application-documents/offer-letters/',
    'fake_qualification.pdf' => 'application-documents/qualifications/'
];

$timestamp = time();

foreach ($realFiles as $filename => $folder) {
    $filePath = "test_files/{$filename}";

    if (file_exists($filePath)) {
        try {
            echo "📤 Uploading {$filename} to {$folder}...\n";

            $explicitFilename = $timestamp . '_' . $filename;
            $fullPath = $folder . $explicitFilename;

            // Use putFileAs method
            Storage::disk('s3')->putFileAs($folder, $filePath, $explicitFilename);
            echo "✅ Success: {$fullPath}\n";
        } catch (Exception $e) {
            echo "❌ Failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⏭️  Skipping {$filename} - file not found\n";
    }
}

echo "\n=== CHECKING WHAT'S IN S3 ===\n";

// Try to list files (this might fail due to permissions)
try {
    echo "📋 Attempting to list S3 contents...\n";
    $files = Storage::disk('s3')->allFiles();
    echo "📊 Total files found: " . count($files) . "\n";

    if (count($files) > 0) {
        echo "📋 Files in S3:\n";
        foreach ($files as $file) {
            echo "  - {$file}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Cannot list files (permission issue): " . $e->getMessage() . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "✅ New organized folders should now be created in your S3 bucket\n";
echo "📁 Look for these folders in your S3 console:\n";
echo "  - applicant-documents/\n";
echo "  - application-documents/\n";
echo "  - test-root-file.txt (at root level)\n";
echo "\n🔍 Refresh your S3 bucket page to see the changes!\n";
