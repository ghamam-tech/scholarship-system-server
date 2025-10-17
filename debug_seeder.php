<?php

echo "=== DEBUGGING SEEDER ISSUE ===\n";

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Applicant;
use App\Enums\UserRole;

echo "✅ Laravel loaded successfully\n";

echo "\n=== TESTING USER CREATION ===\n";

// Test creating a single user
$user = User::create([
    'name' => 'Test User',
    'email' => 'test.user@example.com',
    'password' => bcrypt('password123'),
    'role' => UserRole::APPLICANT->value
]);

echo "✅ Created user: {$user->name} (ID: {$user->id})\n";

echo "\n=== TESTING APPLICANT CREATION ===\n";

// Test creating applicant for this user
$applicant = Applicant::create([
    'user_id' => $user->id,
    'ar_name' => 'مستخدم تجريبي',
    'en_name' => 'Test User',
    'nationality' => 'Saudi',
    'gender' => 'male',
    'place_of_birth' => 'Riyadh',
    'phone' => '+966501234567',
    'passport_number' => 'TEST123456',
    'date_of_birth' => '1998-05-15',
    'parent_contact_name' => 'Test Parent',
    'parent_contact_phone' => '+966501234568',
    'residence_country' => 'Saudi Arabia',
    'language' => 'Arabic',
    'is_studied_in_saudi' => true,
    'tahseeli_percentage' => 85.5,
    'qudorat_percentage' => 78.2
]);

echo "✅ Created applicant: {$applicant->en_name} (ID: {$applicant->applicant_id})\n";

echo "\n=== TESTING FILE UPLOAD ===\n";

use Illuminate\Support\Facades\Storage;

// Test file upload
try {
    $timestamp = time();
    $filename = $timestamp . '_test_file.pdf';
    $filePath = 'applicant-documents/passport/' . $filename;

    $content = "Test file content for {$applicant->en_name}";
    Storage::disk('s3')->put($filePath, $content);

    echo "✅ Uploaded test file: {$filePath}\n";

    // Update applicant with file path
    $applicant->update(['passport_copy_img' => $filePath]);
    echo "✅ Updated applicant with file path\n";

    // Verify
    $applicant = $applicant->fresh();
    echo "📁 Applicant passport path: " . ($applicant->passport_copy_img ?: 'NULL') . "\n";
} catch (Exception $e) {
    echo "❌ File upload failed: " . $e->getMessage() . "\n";
}

echo "\n=== CLEANUP ===\n";

// Clean up test data
$applicant->delete();
$user->delete();
echo "🗑️ Cleaned up test data\n";

echo "\n✅ Debug test completed successfully!\n";
