<?php

require_once 'vendor/autoload.php';

use App\Models\ProgramApplication;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Current applications for program 2:\n";
$apps = ProgramApplication::where('program_id', 2)->get(['application_program_id', 'student_id', 'application_status']);
foreach($apps as $app) {
    echo $app->application_program_id . " - Student " . $app->student_id . " - Status: " . $app->application_status . "\n";
}

echo "\nTotal count: " . $apps->count() . "\n";
