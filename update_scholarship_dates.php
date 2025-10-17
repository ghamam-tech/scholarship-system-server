<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Updating scholarship dates to 2025...\n";

// Update all scholarships to have 2025 dates
DB::table('scholarships')->update([
    'opening_date' => '2025-01-01 00:00:00',
    'closing_date' => '2025-12-31 23:59:59',
    'is_active' => true,
    'is_hided' => false
]);

echo "✅ All scholarships updated to 2025 dates\n";

// Check active scholarships
$activeCount = DB::table('scholarships')
    ->where('is_active', true)
    ->where('is_hided', false)
    ->where('closing_date', '>', now())
    ->count();

echo "📊 Active scholarships: {$activeCount}\n";

if ($activeCount >= 3) {
    echo "✅ Ready for application seeding!\n";
} else {
    echo "❌ Still need more active scholarships\n";
}
