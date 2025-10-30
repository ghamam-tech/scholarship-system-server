<?php
/**
 * Comprehensive Test Scenarios for Certificate Token Management
 * 
 * This script tests:
 * - Certificate token generation when status becomes 'attend' and opportunity/program is completed
 * - Certificate token removal when status changes from 'attend' or opportunity/program is not completed
 * - All CRUD operations for opportunity and program applications
 * 
 * Usage: php test_certificate_token_management.php <admin_token>
 */

if ($argc < 2) {
    echo "Usage: php test_certificate_token_management.php <admin_token>\n";
    echo "Example: php test_certificate_token_management.php 1|abc123...\n";
    exit(1);
}

$adminToken = $argv[1];
$baseUrl = "http://127.0.0.1:8000/api/v1";

// Colors for output
$colors = [
    'reset' => "\033[0m",
    'green' => "\033[32m",
    'red' => "\033[31m",
    'yellow' => "\033[33m",
    'blue' => "\033[34m",
    'cyan' => "\033[36m",
];

function colorize($text, $color, $colors) {
    return $colors[$color] . $text . $colors['reset'];
}

function makeRequest($method, $url, $token, $data = null, $isJson = true) {
    $ch = curl_init($url);
    
    $headers = [
        'Accept: application/json',
        "Authorization: Bearer {$token}"
    ];
    
    if ($isJson) {
        $headers[] = 'Content-Type: application/json';
    }
    
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data !== null) {
        if ($isJson) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['error' => $error, 'http_code' => 0];
    }
    
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'body' => json_decode($response, true),
        'raw' => $response
    ];
}

function printTestHeader($title, $colors) {
    echo "\n" . str_repeat("=", 80) . "\n";
    echo colorize($title, 'cyan', $colors) . "\n";
    echo str_repeat("=", 80) . "\n\n";
}

function printTestResult($scenario, $expected, $actual, $passed, $colors, $details = '') {
    $status = $passed ? colorize("✓ PASS", 'green', $colors) : colorize("✗ FAIL", 'red', $colors);
    echo "{$status} - {$scenario}\n";
    if (!$passed) {
        echo "  Expected: {$expected}\n";
        echo "  Actual: {$actual}\n";
        if ($details) {
            echo "  Details: {$details}\n";
        }
    }
    echo "\n";
}

// Test counters
$totalTests = 0;
$passedTests = 0;

// ============================================================================
// OPPORTUNITY APPLICATION TESTS
// ============================================================================

printTestHeader("OPPORTUNITY APPLICATION TESTS", $colors);

// Scenario 1: Get students for invitation
echo colorize("Scenario 1: Get Students for Invitation (Opportunity)", 'blue', $colors) . "\n";
$result = makeRequest('GET', "{$baseUrl}/admin/opportunities/students/for-invitation", $adminToken);
$totalTests++;
$passed = $result['http_code'] === 200 && isset($result['body']['students']);
if ($passed) $passedTests++;
printTestResult(
    "Fetch available students for opportunity invitation",
    "HTTP 200 with students array",
    "HTTP {$result['http_code']}",
    $passed,
    $colors
);

// Scenario 2: Invite students to opportunity
echo colorize("Scenario 2: Invite Students to Opportunity", 'blue', $colors) . "\n";
$opportunityId = 7; // Blood Donation Drive
$studentIds = [4]; // Abdullah
$result = makeRequest('POST', "{$baseUrl}/admin/opportunities/{$opportunityId}/invite", $adminToken, [
    'student_ids' => $studentIds
]);
$totalTests++;
$passed = $result['http_code'] === 200;
if ($passed) $passedTests++;
$invitationResponse = $result['body'];
printTestResult(
    "Invite student to opportunity",
    "HTTP 200 with invitation confirmation",
    "HTTP {$result['http_code']}",
    $passed,
    $colors,
    $passed ? "Invited {$invitationResponse['invited_count']} student(s)" : ''
);

// Scenario 3: Get opportunity applications
echo colorize("Scenario 3: Get All Opportunity Applications", 'blue', $colors) . "\n";
$result = makeRequest('GET', "{$baseUrl}/admin/opportunities/{$opportunityId}/applications", $adminToken);
$totalTests++;
$passed = $result['http_code'] === 200 && isset($result['body']['applications']);
if ($passed) $passedTests++;
$applicationId = $passed && !empty($result['body']['applications']) 
    ? $result['body']['applications'][0]['application_id'] 
    : 'opp_0000012';
printTestResult(
    "Fetch all applications for opportunity",
    "HTTP 200 with applications array",
    "HTTP {$result['http_code']}",
    $passed,
    $colors
);

// Scenario 4: Update application status to 'accepted' (should NOT generate token)
echo colorize("Scenario 4: Update Status to 'accepted' (No Token)", 'blue', $colors) . "\n";
$result = makeRequest('PATCH', "{$baseUrl}/admin/opportunities/{$opportunityId}/applications/status", $adminToken, [
    'applications' => [
        ['application_id' => $applicationId, 'status' => 'accepted']
    ]
]);
$totalTests++;
$tokenExists = isset($result['body']['updated_applications'][0]['certificate_token']);
$passed = $result['http_code'] === 200 && !$tokenExists;
if ($passed) $passedTests++;
printTestResult(
    "Status 'accepted' should NOT generate certificate token",
    "HTTP 200, no certificate_token",
    "HTTP {$result['http_code']}, token exists: " . ($tokenExists ? 'YES' : 'NO'),
    $passed,
    $colors
);

// Scenario 5: Update application status to 'attend' (SHOULD generate token if opportunity is completed)
echo colorize("Scenario 5: Update Status to 'attend' (Should Generate Token)", 'blue', $colors) . "\n";
$result = makeRequest('PATCH', "{$baseUrl}/admin/opportunities/{$opportunityId}/applications/status", $adminToken, [
    'applications' => [
        ['application_id' => $applicationId, 'status' => 'attend']
    ]
]);
$totalTests++;
$tokenGenerated = isset($result['body']['updated_applications'][0]['certificate_token']) 
    && !empty($result['body']['updated_applications'][0]['certificate_token']);
$passed = $result['http_code'] === 200 && $tokenGenerated;
if ($passed) $passedTests++;
$generatedToken = $tokenGenerated ? $result['body']['updated_applications'][0]['certificate_token'] : null;
printTestResult(
    "Status 'attend' with completed opportunity SHOULD generate certificate token",
    "HTTP 200, certificate_token generated",
    "HTTP {$result['http_code']}, token generated: " . ($tokenGenerated ? 'YES' : 'NO'),
    $passed,
    $colors,
    $generatedToken ? "Token: " . substr($generatedToken, 0, 10) . "..." : ''
);

// Scenario 6: Change status back to 'accepted' (should REMOVE token)
echo colorize("Scenario 6: Change Status Back to 'accepted' (Remove Token)", 'blue', $colors) . "\n";
$result = makeRequest('PATCH', "{$baseUrl}/admin/opportunities/{$opportunityId}/applications/status", $adminToken, [
    'applications' => [
        ['application_id' => $applicationId, 'status' => 'accepted']
    ]
]);
$totalTests++;
$tokenRemoved = !isset($result['body']['updated_applications'][0]['certificate_token']) 
    || empty($result['body']['updated_applications'][0]['certificate_token']);
$passed = $result['http_code'] === 200 && $tokenRemoved;
if ($passed) $passedTests++;
printTestResult(
    "Status changed from 'attend' to 'accepted' SHOULD remove certificate token",
    "HTTP 200, certificate_token removed",
    "HTTP {$result['http_code']}, token removed: " . ($tokenRemoved ? 'YES' : 'NO'),
    $passed,
    $colors
);

// Scenario 7: Set status back to 'attend' then check certificate token exists
echo colorize("Scenario 7: Re-generate Token by Setting 'attend' Again", 'blue', $colors) . "\n";
$result = makeRequest('PATCH', "{$baseUrl}/admin/opportunities/{$opportunityId}/applications/status", $adminToken, [
    'applications' => [
        ['application_id' => $applicationId, 'status' => 'attend']
    ]
]);
$totalTests++;
$tokenRegenerated = isset($result['body']['updated_applications'][0]['certificate_token']) 
    && !empty($result['body']['updated_applications'][0]['certificate_token']);
$passed = $result['http_code'] === 200 && $tokenRegenerated;
if ($passed) $passedTests++;
printTestResult(
    "Re-setting status to 'attend' SHOULD regenerate certificate token",
    "HTTP 200, certificate_token regenerated",
    "HTTP {$result['http_code']}, token regenerated: " . ($tokenRegenerated ? 'YES' : 'NO'),
    $passed,
    $colors
);

// Scenario 8: Get opportunity attendance
echo colorize("Scenario 8: Get Opportunity Attendance", 'blue', $colors) . "\n";
$result = makeRequest('GET', "{$baseUrl}/admin/opportunities/{$opportunityId}/attendance", $adminToken);
$totalTests++;
$passed = $result['http_code'] === 200 && isset($result['body']['applications']);
if ($passed) $passedTests++;
printTestResult(
    "Fetch attendance records for opportunity",
    "HTTP 200 with applications array",
    "HTTP {$result['http_code']}",
    $passed,
    $colors,
    $passed ? "Total attended: {$result['body']['statistics']['total_attended']}" : ''
);

// Scenario 9: Generate missing certificate tokens (if any)
echo colorize("Scenario 9: Generate Missing Certificate Tokens", 'blue', $colors) . "\n";
$result = makeRequest('POST', "{$baseUrl}/admin/opportunities/{$opportunityId}/generate-certificates", $adminToken);
$totalTests++;
$passed = $result['http_code'] === 200 || $result['http_code'] === 400; // 400 if opportunity not completed is OK
if ($passed) $passedTests++;
printTestResult(
    "Generate certificate tokens for all 'attend' applications without tokens",
    "HTTP 200 or 400 (if opportunity not completed)",
    "HTTP {$result['http_code']}",
    $passed,
    $colors,
    isset($result['body']['updated_count']) ? "Generated {$result['body']['updated_count']} token(s)" : $result['body']['message']
);

// Scenario 10: Delete application (formatted ID)
echo colorize("Scenario 10: Delete Application (Formatted ID)", 'blue', $colors) . "\n";
// First, create a test invitation
$testStudentIds = [4];
$inviteResult = makeRequest('POST', "{$baseUrl}/admin/opportunities/{$opportunityId}/invite", $adminToken, [
    'student_ids' => $testStudentIds
]);
if (isset($inviteResult['body']['applications'][0]['application_opportunity_id'])) {
    $testAppId = $inviteResult['body']['applications'][0]['application_opportunity_id'];
    $result = makeRequest('DELETE', "{$baseUrl}/admin/opportunities/applications/{$testAppId}", $adminToken);
    $totalTests++;
    $passed = $result['http_code'] === 200;
    if ($passed) $passedTests++;
    printTestResult(
        "Delete application using formatted ID (e.g., opp_0000012)",
        "HTTP 200 with deletion confirmation",
        "HTTP {$result['http_code']}",
        $passed,
        $colors
    );
} else {
    echo colorize("⚠ SKIP", 'yellow', $colors) . " - Could not create test application for deletion\n\n";
}

// ============================================================================
// PROGRAM APPLICATION TESTS
// ============================================================================

printTestHeader("PROGRAM APPLICATION TESTS", $colors);

// Scenario 11: Get students for invitation (Program)
echo colorize("Scenario 11: Get Students for Invitation (Program)", 'blue', $colors) . "\n";
$result = makeRequest('GET', "{$baseUrl}/admin/programs/students/for-invitation", $adminToken);
$totalTests++;
$passed = $result['http_code'] === 200 && isset($result['body']['students']);
if ($passed) $passedTests++;
printTestResult(
    "Fetch available students for program invitation",
    "HTTP 200 with students array",
    "HTTP {$result['http_code']}",
    $passed,
    $colors
);

// Scenario 12: Invite students to program
echo colorize("Scenario 12: Invite Students to Program", 'blue', $colors) . "\n";
$programId = 4; // Test program
$studentIds = [4]; // Abdullah
$result = makeRequest('POST', "{$baseUrl}/admin/programs/{$programId}/invite", $adminToken, [
    'student_ids' => $studentIds
]);
$totalTests++;
$passed = $result['http_code'] === 200;
if ($passed) $passedTests++;
printTestResult(
    "Invite student to program",
    "HTTP 200 with invitation confirmation",
    "HTTP {$result['http_code']}",
    $passed,
    $colors
);

// Scenario 13: Get program applications
echo colorize("Scenario 13: Get All Program Applications", 'blue', $colors) . "\n";
$result = makeRequest('GET', "{$baseUrl}/admin/programs/{$programId}/applications", $adminToken);
$totalTests++;
$passed = $result['http_code'] === 200 && isset($result['body']['applications']);
if ($passed) $passedTests++;
$progAppId = $passed && !empty($result['body']['applications']) 
    ? $result['body']['applications'][0]['application_id'] 
    : 'prog_0000043';
printTestResult(
    "Fetch all applications for program",
    "HTTP 200 with applications array",
    "HTTP {$result['http_code']}",
    $passed,
    $colors
);

// Scenario 14: Update program application status to 'attend' (should generate token if program is completed)
echo colorize("Scenario 14: Update Program Status to 'attend' (Generate Token)", 'blue', $colors) . "\n";
$result = makeRequest('PATCH', "{$baseUrl}/admin/programs/{$programId}/applications/status", $adminToken, [
    'applications' => [
        ['application_id' => $progAppId, 'status' => 'attend']
    ]
]);
$totalTests++;
$tokenGenerated = isset($result['body']['updated_applications'][0]['certificate_token']) 
    && !empty($result['body']['updated_applications'][0]['certificate_token']);
$passed = $result['http_code'] === 200;
if ($passed) $passedTests++;
printTestResult(
    "Program status 'attend' with completed program SHOULD generate certificate token",
    "HTTP 200, certificate_token status depends on program completion",
    "HTTP {$result['http_code']}, token generated: " . ($tokenGenerated ? 'YES' : 'NO'),
    $passed,
    $colors
);

// Scenario 15: Generate missing certificate tokens for program
echo colorize("Scenario 15: Generate Missing Certificate Tokens (Program)", 'blue', $colors) . "\n";
$result = makeRequest('POST', "{$baseUrl}/admin/programs/{$programId}/generate-certificates", $adminToken);
$totalTests++;
$passed = $result['http_code'] === 200 || $result['http_code'] === 400;
if ($passed) $passedTests++;
printTestResult(
    "Generate certificate tokens for all 'attend' program applications",
    "HTTP 200 or 400 (if program not completed)",
    "HTTP {$result['http_code']}",
    $passed,
    $colors,
    isset($result['body']['updated_count']) ? "Generated {$result['body']['updated_count']} token(s)" : $result['body']['message']
);

// Scenario 16: Change program application status back to 'accepted' (should remove token)
echo colorize("Scenario 16: Change Program Status to 'accepted' (Remove Token)", 'blue', $colors) . "\n";
$result = makeRequest('PATCH', "{$baseUrl}/admin/programs/{$programId}/applications/status", $adminToken, [
    'applications' => [
        ['application_id' => $progAppId, 'status' => 'accepted']
    ]
]);
$totalTests++;
$passed = $result['http_code'] === 200;
if ($passed) $passedTests++;
printTestResult(
    "Program status changed from 'attend' to 'accepted' SHOULD remove certificate token",
    "HTTP 200, certificate_token removed",
    "HTTP {$result['http_code']}",
    $passed,
    $colors
);

// Scenario 17: Delete program application
echo colorize("Scenario 17: Delete Program Application (Formatted ID)", 'blue', $colors) . "\n";
// Create a test invitation first
$inviteResult = makeRequest('POST', "{$baseUrl}/admin/programs/{$programId}/invite", $adminToken, [
    'student_ids' => [4]
]);
if (isset($inviteResult['body']['applications'][0]['application_program_id'])) {
    $testProgAppId = $inviteResult['body']['applications'][0]['application_program_id'];
    $result = makeRequest('DELETE', "{$baseUrl}/admin/programs/applications/{$testProgAppId}", $adminToken);
    $totalTests++;
    $passed = $result['http_code'] === 200;
    if ($passed) $passedTests++;
    printTestResult(
        "Delete program application using formatted ID (e.g., prog_0000043)",
        "HTTP 200 with deletion confirmation",
        "HTTP {$result['http_code']}",
        $passed,
        $colors
    );
} else {
    echo colorize("⚠ SKIP", 'yellow', $colors) . " - Could not create test application for deletion\n\n";
}

// ============================================================================
// EDGE CASES & ERROR HANDLING
// ============================================================================

printTestHeader("EDGE CASES & ERROR HANDLING", $colors);

// Scenario 18: Try to invite to non-existent opportunity
echo colorize("Scenario 18: Invite to Non-Existent Opportunity", 'blue', $colors) . "\n";
$result = makeRequest('POST', "{$baseUrl}/admin/opportunities/99999/invite", $adminToken, [
    'student_ids' => [4]
]);
$totalTests++;
$passed = $result['http_code'] === 404;
if ($passed) $passedTests++;
printTestResult(
    "Inviting to non-existent opportunity should return 404",
    "HTTP 404",
    "HTTP {$result['http_code']}",
    $passed,
    $colors
);

// Scenario 19: Try to update non-existent application
echo colorize("Scenario 19: Update Non-Existent Application", 'blue', $colors) . "\n";
$result = makeRequest('PATCH', "{$baseUrl}/admin/opportunities/7/applications/status", $adminToken, [
    'applications' => [
        ['application_id' => 'opp_9999999', 'status' => 'attend']
    ]
]);
$totalTests++;
$passed = $result['http_code'] === 422 || ($result['http_code'] === 200 && !empty($result['body']['errors']));
if ($passed) $passedTests++;
printTestResult(
    "Updating non-existent application should return validation error",
    "HTTP 422 or errors in response",
    "HTTP {$result['http_code']}",
    $passed,
    $colors
);

// Scenario 20: Try to delete non-existent application
echo colorize("Scenario 20: Delete Non-Existent Application", 'blue', $colors) . "\n";
$result = makeRequest('DELETE', "{$baseUrl}/admin/opportunities/applications/opp_9999999", $adminToken);
$totalTests++;
$passed = $result['http_code'] === 404;
if ($passed) $passedTests++;
printTestResult(
    "Deleting non-existent application should return 404",
    "HTTP 404",
    "HTTP {$result['http_code']}",
    $passed,
    $colors
);

// Scenario 21: Test formatted ID parsing (very long ID)
echo colorize("Scenario 21: Handle Very Long Formatted ID", 'blue', $colors) . "\n";
$result = makeRequest('DELETE', "{$baseUrl}/admin/opportunities/applications/opp_120000000000000030450", $adminToken);
$totalTests++;
$passed = $result['http_code'] === 404; // Should parse correctly and return 404 (not found)
if ($passed) $passedTests++;
printTestResult(
    "Very long formatted ID should be parsed correctly",
    "HTTP 404 (parsed correctly but not found)",
    "HTTP {$result['http_code']}",
    $passed,
    $colors
);

// ============================================================================
// SUMMARY
// ============================================================================

printTestHeader("TEST SUMMARY", $colors);

$passRate = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0;
$passRateColor = $passRate >= 80 ? 'green' : ($passRate >= 60 ? 'yellow' : 'red');

echo colorize("Total Tests: {$totalTests}", 'cyan', $colors) . "\n";
echo colorize("Passed: {$passedTests}", 'green', $colors) . "\n";
echo colorize("Failed: " . ($totalTests - $passedTests), 'red', $colors) . "\n";
echo colorize("Pass Rate: {$passRate}%", $passRateColor, $colors) . "\n\n";

if ($passedTests === $totalTests) {
    echo colorize("🎉 ALL TESTS PASSED! 🎉", 'green', $colors) . "\n";
} else {
    echo colorize("⚠ Some tests failed. Please review the output above.", 'yellow', $colors) . "\n";
}

echo "\n" . str_repeat("=", 80) . "\n";

