<?php

echo "=== CHECKING S3 BUCKET CONTENTS ===\n";

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Storage;

echo "✅ Laravel loaded successfully\n";

// Check S3 configuration
$s3Config = config('filesystems.disks.s3');
echo "📁 S3 Bucket: " . ($s3Config['bucket'] ?? 'Not set') . "\n";
echo "📁 S3 Region: " . ($s3Config['region'] ?? 'Not set') . "\n";

echo "\n=== CHECKING EACH FOLDER ===\n";

$folders = [
    'passport/',
    'personal image/',
    'tahsili/',
    'qudorat/',
    'volunteering certificate/',
    'Good conduct/',
    'acadimic qualification/'
];

foreach ($folders as $folder) {
    echo "📁 Checking folder: {$folder}\n";
    
    try {
        $files = Storage::disk('s3')->files($folder);
        echo "  📋 Files found: " . count($files) . "\n";
        
        foreach ($files as $file) {
            echo "    - {$file}\n";
        }
        
    } catch (Exception $e) {
        echo "  ❌ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== TESTING FILE UPLOAD WITH EXPLICIT PATH ===\n";

// Test upload with explicit filename
$testFile = 'test_files/passport_copy.pdf';
if (file_exists($testFile)) {
    try {
        echo "📤 Uploading with explicit filename...\n";
        $path = Storage::disk('s3')->putFileAs('passport/', $testFile, 'test_passport_' . time() . '.pdf');
        echo "✅ Upload successful: {$path}\n";
        
        // Try to get file info
        if (Storage::disk('s3')->exists($path)) {
            echo "✅ File confirmed in S3\n";
            $size = Storage::disk('s3')->size($path);
            echo "📁 File size: {$size} bytes\n";
        } else {
            echo "❌ File not found in S3\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Upload failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Test file not found: {$testFile}\n";
}

echo "\n=== CHECKING ALL FILES IN BUCKET ===\n";

try {
    $allFiles = Storage::disk('s3')->allFiles();
    echo "📋 Total files in bucket: " . count($allFiles) . "\n";
    
    if (count($allFiles) > 0) {
        echo "📋 Recent files:\n";
        foreach (array_slice($allFiles, -10) as $file) {
            echo "  - {$file}\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error listing all files: " . $e->getMessage() . "\n";
}
