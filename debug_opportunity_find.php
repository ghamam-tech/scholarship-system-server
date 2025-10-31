<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Opportunity;

echo "=== Debugging Opportunity::find() ===\n";

try {
    $opportunityId = 1;
    
    echo "Looking for opportunity ID: " . $opportunityId . "\n";
    
    // Test different ways to find the opportunity
    echo "\n1. Using Opportunity::find():\n";
    $opportunity1 = Opportunity::find($opportunityId);
    echo "Type: " . get_class($opportunity1) . "\n";
    echo "Is collection: " . (is_a($opportunity1, 'Illuminate\Database\Eloquent\Collection') ? 'Yes' : 'No') . "\n";
    if ($opportunity1) {
        echo "Found: " . $opportunity1->title . "\n";
    } else {
        echo "Not found\n";
    }
    
    echo "\n2. Using Opportunity::where():\n";
    $opportunity2 = Opportunity::where('opportunity_id', $opportunityId)->first();
    echo "Type: " . get_class($opportunity2) . "\n";
    echo "Is collection: " . (is_a($opportunity2, 'Illuminate\Database\Eloquent\Collection') ? 'Yes' : 'No') . "\n";
    if ($opportunity2) {
        echo "Found: " . $opportunity2->title . "\n";
    } else {
        echo "Not found\n";
    }
    
    echo "\n3. Using Opportunity::where()->get():\n";
    $opportunity3 = Opportunity::where('opportunity_id', $opportunityId)->get();
    echo "Type: " . get_class($opportunity3) . "\n";
    echo "Is collection: " . (is_a($opportunity3, 'Illuminate\Database\Eloquent\Collection') ? 'Yes' : 'No') . "\n";
    echo "Count: " . $opportunity3->count() . "\n";
    
    echo "\n4. All opportunities:\n";
    $allOpportunities = Opportunity::all();
    echo "Total opportunities: " . $allOpportunities->count() . "\n";
    foreach ($allOpportunities as $opp) {
        echo "  - ID: " . $opp->opportunity_id . ", Title: " . $opp->title . "\n";
    }
    
    // Test the specific case that's failing
    echo "\n5. Testing the failing case:\n";
    $opportunity = Opportunity::find($opportunityId);
    if ($opportunity) {
        echo "Opportunity found: " . $opportunity->title . "\n";
        echo "Type: " . get_class($opportunity) . "\n";
        echo "Is collection: " . (is_a($opportunity, 'Illuminate\Database\Eloquent\Collection') ? 'Yes' : 'No') . "\n";
        
        if (is_a($opportunity, 'Illuminate\Database\Eloquent\Collection')) {
            echo "ERROR: find() returned a collection instead of a model!\n";
        } else {
            echo "SUCCESS: find() returned a model as expected.\n";
            try {
                echo "opportunity_id: " . $opportunity->opportunity_id . "\n";
            } catch (Exception $e) {
                echo "Error accessing opportunity_id: " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "Opportunity not found\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "=== End Debug ===\n";

