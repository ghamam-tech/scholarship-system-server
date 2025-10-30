<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking Database Structure ===\n";

try {
    // Check opportunities table structure
    echo "1. Opportunities table structure:\n";
    $result = DB::select('SHOW CREATE TABLE opportunities');
    echo $result[0]->{'Create Table'} . "\n\n";

    // Check application_opportunities table structure
    echo "2. Application_opportunities table structure:\n";
    $result = DB::select('SHOW CREATE TABLE application_opportunities');
    echo $result[0]->{'Create Table'} . "\n\n";

    // Check for any data integrity issues
    echo "3. Checking for data integrity issues:\n";
    
    // Check if there are any orphaned records
    $orphaned = DB::table('application_opportunities')
        ->leftJoin('opportunities', 'application_opportunities.opportunity_id', '=', 'opportunities.opportunity_id')
        ->whereNull('opportunities.opportunity_id')
        ->count();
    echo "Orphaned application_opportunities records: " . $orphaned . "\n";

    // Check if there are any duplicate opportunity_ids in opportunities table
    $duplicates = DB::table('opportunities')
        ->select('opportunity_id', DB::raw('COUNT(*) as count'))
        ->groupBy('opportunity_id')
        ->having('count', '>', 1)
        ->get();
    echo "Duplicate opportunity_ids: " . $duplicates->count() . "\n";

    // Check if opportunity_id is properly set as primary key
    $keys = DB::select("SHOW KEYS FROM opportunities WHERE Key_name = 'PRIMARY'");
    echo "Primary key info:\n";
    foreach ($keys as $key) {
        echo "  Column: " . $key->Column_name . ", Type: " . $key->Index_type . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "=== End Check ===\n";



