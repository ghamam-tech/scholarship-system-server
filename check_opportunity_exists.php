<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Opportunity;

echo "=== Checking if Opportunity ID 1 exists ===\n";

try {
    $opportunity = Opportunity::find(1);
    if ($opportunity) {
        echo "Opportunity 1 found: " . $opportunity->title . "\n";
        echo "Opportunity ID: " . $opportunity->opportunity_id . "\n";
    } else {
        echo "Opportunity 1 not found\n";
    }

    echo "\nAll opportunities in database:\n";
    $all = Opportunity::all();
    echo "Total opportunities: " . $all->count() . "\n";
    foreach ($all as $opp) {
        echo "ID: " . $opp->opportunity_id . ", Title: " . $opp->title . "\n";
    }

    // Check if there are any opportunities with ID 1 using raw query
    echo "\nChecking with raw query:\n";
    $raw = DB::table('opportunities')->where('opportunity_id', 1)->first();
    if ($raw) {
        echo "Raw query found opportunity 1: " . $raw->title . "\n";
    } else {
        echo "Raw query did not find opportunity 1\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "=== End Check ===\n";



