<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking for Duplicate Opportunity IDs ===\n";

try {
    // Check for duplicate opportunity IDs
    $duplicates = DB::table('opportunities')
        ->select('opportunity_id', DB::raw('COUNT(*) as count'))
        ->groupBy('opportunity_id')
        ->having('count', '>', 1)
        ->get();

    echo "Duplicate IDs found: " . $duplicates->count() . "\n";

    if ($duplicates->count() > 0) {
        foreach ($duplicates as $dup) {
            echo "ID " . $dup->opportunity_id . " appears " . $dup->count . " times\n";
            
            // Get details of duplicate records
            $records = DB::table('opportunities')
                ->where('opportunity_id', $dup->opportunity_id)
                ->get();
            
            echo "Details:\n";
            foreach ($records as $record) {
                echo "  - Record ID: " . $record->id . ", Title: " . $record->title . ", Created: " . $record->created_at . "\n";
            }
            echo "\n";
        }
    } else {
        echo "No duplicate opportunity IDs found.\n";
    }

    // Check total opportunities
    $total = DB::table('opportunities')->count();
    echo "Total opportunities: " . $total . "\n";

    // Check if opportunity_id is unique
    $uniqueIds = DB::table('opportunities')->distinct('opportunity_id')->count();
    echo "Unique opportunity IDs: " . $uniqueIds . "\n";

    if ($total !== $uniqueIds) {
        echo "WARNING: Total records (" . $total . ") != Unique IDs (" . $uniqueIds . ")\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "=== End Check ===\n";

