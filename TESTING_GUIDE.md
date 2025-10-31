# Certificate Token Management - Testing Guide

## Overview
This guide provides comprehensive test scenarios for the Opportunity and Program application endpoints with a focus on certificate token management.

## Test Scenarios Covered

### Opportunity Application Tests (Scenarios 1-10)
1. ✅ Get students available for invitation
2. ✅ Invite students to opportunity
3. ✅ Get all opportunity applications
4. ✅ Update status to 'accepted' (should NOT generate token)
5. ✅ Update status to 'attend' (SHOULD generate token if completed)
6. ✅ Change status back to 'accepted' (SHOULD remove token)
7. ✅ Re-set status to 'attend' (SHOULD regenerate token)
8. ✅ Get opportunity attendance records
9. ✅ Generate missing certificate tokens endpoint
10. ✅ Delete application using formatted ID

### Program Application Tests (Scenarios 11-17)
11. ✅ Get students available for program invitation
12. ✅ Invite students to program
13. ✅ Get all program applications
14. ✅ Update program status to 'attend' (generate token)
15. ✅ Generate missing certificate tokens for program
16. ✅ Change program status to 'accepted' (remove token)
17. ✅ Delete program application using formatted ID

### Edge Cases & Error Handling (Scenarios 18-21)
18. ✅ Invite to non-existent opportunity (404 expected)
19. ✅ Update non-existent application (422 expected)
20. ✅ Delete non-existent application (404 expected)
21. ✅ Handle very long formatted IDs (correct parsing)

## Running the Tests

### Prerequisites
1. Laravel server must be running: `php artisan serve`
2. Database must be seeded with test data
3. You need a valid admin bearer token

### Get Admin Token
Login as admin to get the token:
```bash
curl -X POST http://127.0.0.1:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

The response will contain a `token` field. Use this as your admin token.

### Run All Tests
```bash
php test_certificate_token_management.php YOUR_ADMIN_TOKEN
```

Replace `YOUR_ADMIN_TOKEN` with the token from the login response (e.g., `1|abc123def456...`).

### Example Output
```
================================================================================
OPPORTUNITY APPLICATION TESTS
================================================================================

Scenario 1: Get Students for Invitation (Opportunity)
✓ PASS - Fetch available students for opportunity invitation

Scenario 2: Invite Students to Opportunity
✓ PASS - Invite student to opportunity
  Details: Invited 1 student(s)

...

================================================================================
TEST SUMMARY
================================================================================
Total Tests: 21
Passed: 21
Failed: 0
Pass Rate: 100.00%

🎉 ALL TESTS PASSED! 🎉
```

## Certificate Token Behavior

### When Tokens are Generated
Certificate tokens are **automatically generated** when:
- ✅ Application status is `'attend'`
- ✅ Opportunity/Program status is `'completed'`
- ✅ `generate_certificates` is enabled (always true for opportunities)

### When Tokens are Removed
Certificate tokens are **automatically removed** when:
- ❌ Application status changes from `'attend'` to any other status
- ❌ Opportunity/Program status changes from `'completed'` to any other status

### Manual Token Generation
If you have existing applications with `'attend'` status but no tokens (because they were marked attend before the opportunity/program was completed), use:

**For Opportunities:**
```bash
POST /api/v1/admin/opportunities/{opportunityId}/generate-certificates
```

**For Programs:**
```bash
POST /api/v1/admin/programs/{programId}/generate-certificates
```

## API Endpoints Reference

### Opportunity Application Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/opportunities/students/for-invitation` | Get students available for invitation |
| POST | `/admin/opportunities/{opportunityId}/invite` | Invite students to opportunity |
| GET | `/admin/opportunities/{opportunityId}/applications` | Get all applications |
| PATCH | `/admin/opportunities/{opportunityId}/applications/status` | Update application statuses |
| GET | `/admin/opportunities/{opportunityId}/attendance` | Get attendance records |
| DELETE | `/admin/opportunities/applications/{applicationId}` | Delete application |
| POST | `/admin/opportunities/{opportunityId}/generate-certificates` | Generate missing tokens |
| GET | `/admin/opportunities/applications/{applicationId}/excuse` | Get excuse details |
| PATCH | `/admin/opportunities/applications/{applicationId}/approve-excuse` | Approve excuse |
| PATCH | `/admin/opportunities/applications/{applicationId}/reject-excuse` | Reject excuse |

### Program Application Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/programs/students/for-invitation` | Get students available for invitation |
| POST | `/admin/programs/{programId}/invite` | Invite students to program |
| GET | `/admin/programs/{programId}/applications` | Get all applications |
| PATCH | `/admin/programs/{programId}/applications/status` | Update application statuses |
| GET | `/admin/programs/{programId}/attendance` | Get attendance records |
| DELETE | `/admin/programs/applications/{applicationId}` | Delete application |
| POST | `/admin/programs/{programId}/generate-certificates` | Generate missing tokens |
| GET | `/admin/programs/applications/{applicationId}/excuse` | Get excuse details |
| PATCH | `/admin/programs/applications/{applicationId}/approve-excuse` | Approve excuse |
| PATCH | `/admin/programs/applications/{applicationId}/reject-excuse` | Reject excuse |

## ID Format Support

Both opportunity and program endpoints support:
- **Numeric IDs**: `12`, `43`
- **Formatted IDs**: `opp_0000012`, `prog_0000043`
- **Very Long IDs**: `opp_120000000000000030450`

The system automatically parses and normalizes all ID formats.

## Troubleshooting

### Test Fails with 401 Unauthorized
- Your admin token has expired or is invalid
- Get a new token by logging in again

### Test Fails with 404 Not Found
- Opportunity/Program/Application doesn't exist
- Update the test script with valid IDs from your database

### Token Not Generated for 'attend' Status
- Check that the opportunity/program status is `'completed'`
- Check that `generate_certificates` is enabled
- Use the `/generate-certificates` endpoint to manually generate missing tokens

### Token Not Removed When Status Changes
- The model boot method runs on every `update()`
- If you're using raw SQL queries, tokens won't be managed automatically
- Always use Eloquent's `update()` or `save()` methods

## Database Verification

To manually check certificate tokens in the database:

```sql
-- Check opportunity applications
SELECT 
    application_opportunity_id,
    student_id,
    opportunity_id,
    application_status,
    certificate_token,
    created_at,
    updated_at
FROM application_opportunities
WHERE opportunity_id = 7;

-- Check program applications
SELECT 
    application_program_id,
    student_id,
    program_id,
    application_status,
    certificate_token,
    created_at,
    updated_at
FROM program_applications
WHERE program_id = 4;
```

## Notes

- All tests require admin authentication
- Tests create and delete temporary data
- Some tests depend on existing seeded data (opportunity 7, program 4, student 4)
- Adjust IDs in the test script if your database uses different values

