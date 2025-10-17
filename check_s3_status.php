<?php

echo "=== CHECKING S3 STATUS ===\n";

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Storage;

echo "✅ Laravel loaded successfully\n";

// Check S3 configuration
$s3Config = config('filesystems.disks.s3');
echo "📁 S3 Bucket: " . ($s3Config['bucket'] ?? 'Not set') . "\n";
echo "📁 S3 Region: " . ($s3Config['region'] ?? 'Not set') . "\n";

echo "\n=== TESTING S3 CONNECTION ===\n";

// Test if we can upload a simple file
try {
    $testContent = "This is a test file to verify S3 upload functionality.";
    $testPath = "test-connection.txt";

    echo "📤 Testing S3 upload...\n";
    Storage::disk('s3')->put($testPath, $testContent);
    echo "✅ S3 upload test successful\n";

    // Try to read it back
    if (Storage::disk('s3')->exists($testPath)) {
        echo "✅ File exists in S3\n";
        $content = Storage::disk('s3')->get($testPath);
        echo "📄 Content: " . substr($content, 0, 50) . "...\n";

        // Clean up
        Storage::disk('s3')->delete($testPath);
        echo "🗑️ Test file deleted\n";
    } else {
        echo "❌ File not found in S3\n";
    }
} catch (Exception $e) {
    echo "❌ S3 test failed: " . $e->getMessage() . "\n";
}

echo "\n=== CREATING NEW FOLDERS MANUALLY ===\n";

// Create the new folder structure by uploading placeholder files
$newFolders = [
    'applicant-documents/passport/placeholder.txt',
    'applicant-documents/personal-images/placeholder.txt',
    'applicant-documents/tahsili/placeholder.txt',
    'applicant-documents/qudorat/placeholder.txt',
    'applicant-documents/volunteering/placeholder.txt',
    'application-documents/offer-letters/placeholder.txt',
    'application-documents/qualifications/placeholder.txt'
];

foreach ($newFolders as $folderPath) {
    try {
        echo "📁 Creating folder: {$folderPath}\n";
        Storage::disk('s3')->put($folderPath, "This is a placeholder file to create the folder structure.");
        echo "✅ Created: {$folderPath}\n";
    } catch (Exception $e) {
        echo "❌ Failed to create {$folderPath}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== UPLOADING REAL FILES TO NEW FOLDERS ===\n";

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

            Storage::disk('s3')->putFileAs($folder, $filePath, $explicitFilename);
            echo "✅ Success: {$fullPath}\n";
        } catch (Exception $e) {
            echo "❌ Failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "⏭️  Skipping {$filename} - file not found\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "✅ New folder structure created:\n";
echo "📂 applicant-documents/\n";
echo "  ├── passport/\n";
echo "  ├── personal-images/\n";
echo "  ├── tahsili/\n";
echo "  ├── qudorat/\n";
echo "  └── volunteering/\n";
echo "📂 application-documents/\n";
echo "  ├── offer-letters/\n";
echo "  └── qualifications/\n";

echo "\n🔍 Check your S3 bucket now - you should see the new organized folders!\n";
