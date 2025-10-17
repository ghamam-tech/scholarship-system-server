<?php

echo "=== FIXING SCHOLARSHIP DATES ===\n";

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Scholarship;

echo "✅ Laravel loaded successfully\n";

// Get all scholarships
$scholarships = Scholarship::all();

echo "📊 Found {$scholarships->count()} scholarships\n";

$updated = 0;
foreach ($scholarships as $scholarship) {
    echo "📅 Updating scholarship: {$scholarship->title}\n";
    echo "   - Old opening_date: {$scholarship->opening_date}\n";
    echo "   - Old closing_date: {$scholarship->closing_date}\n";
    
    // Set dates to 2025 (current year)
    $scholarship->update([
        'opening_date' => '2025-01-01 00:00:00',
        'closing_date' => '2025-12-31 23:59:59',
        'is_active' => true,
        'is_hided' => false
    ]);
    
    echo "   - New opening_date: {$scholarship->opening_date}\n";
    echo "   - New closing_date: {$scholarship->closing_date}\n";
    echo "   - is_active: " . ($scholarship->is_active ? 'true' : 'false') . "\n";
    echo "   - is_hided: " . ($scholarship->is_hided ? 'true' : 'false') . "\n";
    echo "   ✅ Updated successfully\n\n";
    
    $updated++;
}

echo "=== SUMMARY ===\n";
echo "📊 Total scholarships: {$scholarships->count()}\n";
echo "✅ Updated scholarships: {$updated}\n";

// Verify active scholarships
$activeScholarships = Scholarship::where('is_active', true)
    ->where('is_hided', false)
    ->where('closing_date', '>', now())
    ->get();

echo "🎯 Active scholarships available: {$activeScholarships->count()}\n";

if ($activeScholarships->count() >= 3) {
    echo "✅ SUCCESS: Now you have enough active scholarships for seeding applications!\n";
    echo "🚀 You can now run: php artisan db:seed --class=ApplicantApplicationSeeder\n";
} else {
    echo "❌ Still not enough active scholarships\n";
}

echo "\n=== READY FOR APPLICATION SEEDING ===\n";
