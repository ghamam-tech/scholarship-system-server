<?php

echo "=== QUICK FILE UPLOAD TEST ===\n";

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Storage;

echo "✅ Laravel loaded successfully\n";

// Test S3 connection
echo "📤 Testing S3 upload...\n";

$testFile = 'test_files/passport_copy.pdf';
if (file_exists($testFile)) {
    echo "✅ Test file exists: {$testFile}\n";

    try {
        $path = Storage::disk('s3')->putFile('passport/', $testFile);
        echo "✅ Upload successful: {$path}\n";

        // Check if file exists
        if (Storage::disk('s3')->exists($path)) {
            echo "✅ File confirmed in S3\n";
        } else {
            echo "❌ File not found in S3\n";
        }
    } catch (Exception $e) {
        echo "❌ Upload failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Test file not found: {$testFile}\n";
}

echo "=== TEST COMPLETE ===\n";
