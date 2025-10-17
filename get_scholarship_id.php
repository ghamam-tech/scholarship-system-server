<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Scholarship;

$scholarship = Scholarship::where('is_active', true)->first();

if ($scholarship) {
    echo $scholarship->id;
} else {
    echo "1"; // fallback ID
}
