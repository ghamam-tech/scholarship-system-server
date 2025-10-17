<?php

echo "=== CREATING FAKE FILES FOR TESTING ===\n";

// Create test_files directory if it doesn't exist
if (!is_dir('test_files')) {
    mkdir('test_files', 0755, true);
    echo "✅ Created test_files directory\n";
}

// Create fake files
$fakeFiles = [
    'fake_passport.pdf' => 'This is a fake passport document for testing purposes.',
    'fake_personal_image.jpg' => 'This is a fake personal image for testing purposes.',
    'fake_tahsili.pdf' => 'This is a fake tahsili certificate for testing purposes.',
    'fake_qudorat.pdf' => 'This is a fake qudorat certificate for testing purposes.',
    'fake_volunteering.pdf' => 'This is a fake volunteering certificate for testing purposes.',
    'fake_offer_letter.pdf' => 'This is a fake offer letter for testing purposes.',
    'fake_qualification.pdf' => 'This is a fake qualification document for testing purposes.',
];

foreach ($fakeFiles as $filename => $content) {
    $filePath = "test_files/{$filename}";
    file_put_contents($filePath, $content);
    echo "✅ Created: {$filename} (" . filesize($filePath) . " bytes)\n";
}

echo "\n=== FAKE FILES CREATED SUCCESSFULLY ===\n";
echo "📁 All fake files are ready in test_files/ directory\n";
echo "📋 Files created:\n";
foreach ($fakeFiles as $filename => $content) {
    echo "  - {$filename}\n";
}
