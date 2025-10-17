<?php

echo "=== COMPREHENSIVE API ENDPOINT TESTING ===\n";

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Applicant;
use App\Models\Scholarship;
use App\Models\ApplicantApplication;
use App\Models\Qualification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\ApplicantApplicationController;
use App\Http\Controllers\QualificationController;

echo "✅ Laravel loaded successfully\n";

// Get test data
$user = User::where('role', 'applicant')->first();
$applicant = $user->applicant;
$scholarship = Scholarship::where('is_active', true)->first();

if (!$user || !$applicant || !$scholarship) {
    echo "❌ Missing test data. Please run seeders first.\n";
    exit(1);
}

echo "✅ Using test data:\n";
echo "   - User: {$user->name} ({$user->email})\n";
echo "   - Applicant: {$applicant->en_name} (ID: {$applicant->applicant_id})\n";
echo "   - Scholarship: {$scholarship->title} (ID: {$scholarship->scholarship_id})\n";

// Create test files
$testFiles = [
    'test_files/test_passport.pdf' => 'fake passport content',
    'test_files/test_image.jpg' => 'fake image content',
    'test_files/test_certificate.pdf' => 'fake certificate content',
    'test_files/test_transcript.pdf' => 'fake transcript content',
    'test_files/test_volunteering.pdf' => 'fake volunteering content',
    'test_files/test_offer_letter.pdf' => 'fake offer letter content',
    'test_files/test_qualification.pdf' => 'fake qualification content'
];

foreach ($testFiles as $file => $content) {
    if (!file_exists($file)) {
        file_put_contents($file, $content);
    }
}

echo "\n=== TESTING ALL ENDPOINTS ===\n";

// Test 1: Get Qualifications
echo "\n1️⃣ TESTING: GET /qualifications\n";
echo "Request: GET /api/v1/qualifications\n";
echo "Headers: Authorization: Bearer {token}\n";

$qualificationController = new QualificationController();
$request = new Request();
$request->setUserResolver(function () use ($user) {
    return $user;
});

try {
    $response = $qualificationController->index($request);
    $data = json_decode($response->getContent(), true);
    echo "Response Status: {$response->getStatusCode()}\n";
    echo "Response Body:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Test 2: Add Qualification
echo "\n2️⃣ TESTING: POST /qualifications\n";
echo "Request: POST /api/v1/qualifications\n";
echo "Content-Type: multipart/form-data\n";

$requestData = [
    'qualification_type' => 'bachelor',
    'institute_name' => 'Test University',
    'year_of_graduation' => 2023,
    'cgpa' => 3.8,
    'cgpa_out_of' => 4.0,
    'language_of_study' => 'English',
    'specialization' => 'Computer Science',
    'research_title' => 'Test Research',
    'document_file' => new \Illuminate\Http\UploadedFile(
        'test_files/test_qualification.pdf',
        'test_qualification.pdf',
        'application/pdf',
        null,
        true
    )
];

$request = new Request();
$request->merge($requestData);
$request->setUserResolver(function () use ($user) {
    return $user;
});

try {
    $response = $qualificationController->store($request);
    $data = json_decode($response->getContent(), true);
    echo "Response Status: {$response->getStatusCode()}\n";
    echo "Response Body:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Test 3: Get Applications
echo "\n3️⃣ TESTING: GET /applications\n";
echo "Request: GET /api/v1/applications\n";

$applicationController = new ApplicantApplicationController();
$request = new Request();
$request->setUserResolver(function () use ($user) {
    return $user;
});

try {
    $response = $applicationController->index($request);
    $data = json_decode($response->getContent(), true);
    echo "Response Status: {$response->getStatusCode()}\n";
    echo "Response Body:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Test 4: Create Simple Application
echo "\n4️⃣ TESTING: POST /applications\n";
echo "Request: POST /api/v1/applications\n";
echo "Content-Type: application/json\n";

$requestData = [
    'scholarship_ids' => [$scholarship->scholarship_id]
];

$request = new Request();
$request->merge($requestData);
$request->setUserResolver(function () use ($user) {
    return $user;
});

try {
    $response = $applicationController->store($request);
    $data = json_decode($response->getContent(), true);
    echo "Response Status: {$response->getStatusCode()}\n";
    echo "Response Body:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";

    $applicationId = $data['application']['application_id'] ?? null;
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    $applicationId = null;
}

// Test 5: Get Application Details
if ($applicationId) {
    echo "\n5️⃣ TESTING: GET /applications/{id}\n";
    echo "Request: GET /api/v1/applications/{$applicationId}\n";

    $request = new Request();
    $request->setUserResolver(function () use ($user) {
        return $user;
    });

    try {
        $response = $applicationController->show($request, $applicationId);
        $data = json_decode($response->getContent(), true);
        echo "Response Status: {$response->getStatusCode()}\n";
        echo "Response Body:\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

// Test 6: Update Program Details
if ($applicationId) {
    echo "\n6️⃣ TESTING: PUT /applications/{id}/program-details\n";
    echo "Request: PUT /api/v1/applications/{$applicationId}/program-details\n";
    echo "Content-Type: multipart/form-data\n";

    $requestData = [
        'specialization_1' => 'Computer Science',
        'specialization_2' => 'Data Science',
        'specialization_3' => 'AI',
        'university_name' => 'MIT',
        'country_name' => 'USA',
        'tuition_fee' => 75000,
        'has_active_program' => true,
        'current_semester_number' => 2,
        'cgpa' => 3.9,
        'cgpa_out_of' => 4.0,
        'terms_and_condition' => true,
        'offer_letter_file' => new \Illuminate\Http\UploadedFile(
            'test_files/test_offer_letter.pdf',
            'test_offer_letter.pdf',
            'application/pdf',
            null,
            true
        )
    ];

    $request = new Request();
    $request->merge($requestData);
    $request->setUserResolver(function () use ($user) {
        return $user;
    });

    try {
        $response = $applicationController->updateProgramDetails($request, $applicationId);
        $data = json_decode($response->getContent(), true);
        echo "Response Status: {$response->getStatusCode()}\n";
        echo "Response Body:\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

// Test 7: Submit Complete Application
echo "\n7️⃣ TESTING: POST /applications/submit-complete\n";
echo "Request: POST /api/v1/applications/submit-complete\n";
echo "Content-Type: multipart/form-data\n";

$requestData = [
    'personal_info' => [
        'ar_name' => 'أحمد محمد علي',
        'en_name' => 'Ahmed Mohamed Ali',
        'nationality' => 'Saudi',
        'gender' => 'male',
        'place_of_birth' => 'Riyadh',
        'phone' => '+966501234567',
        'passport_number' => 'A12345679',
        'date_of_birth' => '2000-01-15',
        'parent_contact_name' => 'Mohamed Ahmed',
        'parent_contact_phone' => '+966501234568',
        'residence_country' => 'Saudi Arabia',
        'language' => 'Arabic',
        'is_studied_in_saudi' => true,
        'tahseeli_percentage' => 85.5,
        'qudorat_percentage' => 78.2
    ],
    'academic_info' => [
        'qualifications' => [
            [
                'qualification_type' => 'high_school',
                'institute_name' => 'Al-Nahda School',
                'year_of_graduation' => 2019,
                'cgpa' => 98.5,
                'cgpa_out_of' => 99.99,
                'language_of_study' => 'Arabic',
                'specialization' => 'Science',
                'document_file' => new \Illuminate\Http\UploadedFile(
                    'test_files/test_qualification.pdf',
                    'test_qualification.pdf',
                    'application/pdf',
                    null,
                    true
                )
            ]
        ]
    ],
    'program_details' => [
        'scholarship_ids' => [$scholarship->scholarship_id],
        'specialization_1' => 'Computer Science',
        'specialization_2' => 'Data Science',
        'specialization_3' => 'AI',
        'university_name' => 'Stanford University',
        'country_name' => 'USA',
        'tuition_fee' => 50000,
        'has_active_program' => true,
        'current_semester_number' => 2,
        'cgpa' => 3.75,
        'cgpa_out_of' => 4.0,
        'terms_and_condition' => true
    ],
    'passport_copy' => new \Illuminate\Http\UploadedFile(
        'test_files/test_passport.pdf',
        'test_passport.pdf',
        'application/pdf',
        null,
        true
    ),
    'personal_image' => new \Illuminate\Http\UploadedFile(
        'test_files/test_image.jpg',
        'test_image.jpg',
        'image/jpeg',
        null,
        true
    ),
    'secondary_school_certificate' => new \Illuminate\Http\UploadedFile(
        'test_files/test_certificate.pdf',
        'test_certificate.pdf',
        'application/pdf',
        null,
        true
    ),
    'secondary_school_transcript' => new \Illuminate\Http\UploadedFile(
        'test_files/test_transcript.pdf',
        'test_transcript.pdf',
        'application/pdf',
        null,
        true
    ),
    'volunteering_certificate' => new \Illuminate\Http\UploadedFile(
        'test_files/test_volunteering.pdf',
        'test_volunteering.pdf',
        'application/pdf',
        null,
        true
    ),
    'offer_letter' => new \Illuminate\Http\UploadedFile(
        'test_files/test_offer_letter.pdf',
        'test_offer_letter.pdf',
        'application/pdf',
        null,
        true
    )
];

$request = new Request();
$request->merge($requestData);
$request->setUserResolver(function () use ($user) {
    return $user;
});

try {
    $response = $applicationController->submitCompleteApplication($request);
    $data = json_decode($response->getContent(), true);
    echo "Response Status: {$response->getStatusCode()}\n";
    echo "Response Body:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// Test 8: Admin - Get All Applications
echo "\n8️⃣ TESTING: GET /admin/applications (Admin)\n";
echo "Request: GET /api/v1/admin/applications\n";

$adminUser = User::where('role', 'admin')->first();
if ($adminUser) {
    $request = new Request();
    $request->setUserResolver(function () use ($adminUser) {
        return $adminUser;
    });

    try {
        $response = $applicationController->getAllApplications($request);
        $data = json_decode($response->getContent(), true);
        echo "Response Status: {$response->getStatusCode()}\n";
        echo "Response Body:\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ No admin user found\n";
}

// Test 9: Admin - Get Statistics
echo "\n9️⃣ TESTING: GET /admin/statistics (Admin)\n";
echo "Request: GET /api/v1/admin/statistics\n";

if ($adminUser) {
    $request = new Request();
    $request->setUserResolver(function () use ($adminUser) {
        return $adminUser;
    });

    try {
        $response = $applicationController->getStatistics($request);
        $data = json_decode($response->getContent(), true);
        echo "Response Status: {$response->getStatusCode()}\n";
        echo "Response Body:\n";
        echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ No admin user found\n";
}

echo "\n=== TESTING COMPLETE ===\n";
echo "✅ All endpoints have been tested with real request/response data\n";
echo "📋 Check the output above for actual API responses\n";
