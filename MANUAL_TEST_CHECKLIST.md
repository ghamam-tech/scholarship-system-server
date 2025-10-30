# Manual Test Checklist - Certificate Token Management

## Quick Manual Testing Guide

Use this checklist to manually test the certificate token management functionality.

---

## Setup
- [ ] Laravel server is running (`php artisan serve`)
- [ ] Database is seeded
- [ ] You have an admin bearer token
- [ ] You have a test opportunity with `opportunity_status = 'completed'` (e.g., Opportunity 7)
- [ ] You have a test program with `program_status = 'completed'` (e.g., Program 4)

---

## Test 1: Certificate Token Generation (Opportunity)

### Steps:
1. **Invite a student to a completed opportunity**
   ```
   POST /api/v1/admin/opportunities/7/invite
   Body: { "student_ids": [4] }
   ```
   - [ ] Response: HTTP 200
   - [ ] Application created with status `'invite'`
   - [ ] No `certificate_token` yet ✓

2. **Update status to 'accepted'**
   ```
   PATCH /api/v1/admin/opportunities/7/applications/status
   Body: { "applications": [{ "application_id": "opp_XXXXXXX", "status": "accepted" }] }
   ```
   - [ ] Response: HTTP 200
   - [ ] Status changed to `'accepted'`
   - [ ] No `certificate_token` generated ✓

3. **Update status to 'attend'**
   ```
   PATCH /api/v1/admin/opportunities/7/applications/status
   Body: { "applications": [{ "application_id": "opp_XXXXXXX", "status": "attend" }] }
   ```
   - [ ] Response: HTTP 200
   - [ ] Status changed to `'attend'`
   - [ ] **Certificate token IS generated** ✓
   - [ ] Token is a 32-character random string

4. **Verify token persists**
   ```
   GET /api/v1/admin/opportunities/7/applications
   ```
   - [ ] Response includes the application
   - [ ] `certificate_token` field is present and matches previous value ✓

---

## Test 2: Certificate Token Removal (Opportunity)

### Steps:
1. **Start with application in 'attend' status with token** (from Test 1)
   - [ ] Confirm application has `certificate_token` ✓

2. **Change status back to 'accepted'**
   ```
   PATCH /api/v1/admin/opportunities/7/applications/status
   Body: { "applications": [{ "application_id": "opp_XXXXXXX", "status": "accepted" }] }
   ```
   - [ ] Response: HTTP 200
   - [ ] Status changed to `'accepted'`
   - [ ] **Certificate token IS removed** (null) ✓

3. **Verify token is gone**
   ```
   GET /api/v1/admin/opportunities/7/applications
   ```
   - [ ] Response includes the application
   - [ ] `certificate_token` field is `null` ✓

---

## Test 3: Token Regeneration (Opportunity)

### Steps:
1. **Start with application in 'accepted' status without token** (from Test 2)
   - [ ] Confirm `certificate_token` is `null` ✓

2. **Change status back to 'attend'**
   ```
   PATCH /api/v1/admin/opportunities/7/applications/status
   Body: { "applications": [{ "application_id": "opp_XXXXXXX", "status": "attend" }] }
   ```
   - [ ] Response: HTTP 200
   - [ ] Status changed to `'attend'`
   - [ ] **New certificate token IS generated** ✓
   - [ ] Token is different from the first one (was regenerated) ✓

---

## Test 4: Token Not Generated for Non-Completed Opportunity

### Steps:
1. **Create or use an opportunity with status 'active'**
   - [ ] Opportunity status is NOT `'completed'` ✓

2. **Invite a student and set status to 'attend'**
   ```
   POST /api/v1/admin/opportunities/{activeOpportunityId}/invite
   Body: { "student_ids": [4] }
   
   PATCH /api/v1/admin/opportunities/{activeOpportunityId}/applications/status
   Body: { "applications": [{ "application_id": "opp_XXXXXXX", "status": "attend" }] }
   ```
   - [ ] Status changed to `'attend'`
   - [ ] **NO certificate token generated** ✓
   - [ ] `certificate_token` remains `null` ✓

---

## Test 5: Manual Certificate Generation (Opportunity)

### Steps:
1. **Complete the opportunity from Test 4**
   ```
   PATCH /api/v1/admin/opportunities/{opportunityId}
   Body: { "opportunity_status": "completed" }
   ```
   - [ ] Opportunity status changed to `'completed'` ✓

2. **Use generate certificates endpoint**
   ```
   POST /api/v1/admin/opportunities/{opportunityId}/generate-certificates
   ```
   - [ ] Response: HTTP 200
   - [ ] `updated_count` shows number of tokens generated ✓
   - [ ] `updated_applications` array contains application details ✓

3. **Verify tokens were generated**
   ```
   GET /api/v1/admin/opportunities/{opportunityId}/applications
   ```
   - [ ] All applications with `'attend'` status now have `certificate_token` ✓

---

## Test 6: Certificate Token Generation (Program)

### Steps:
1. **Invite a student to a completed program**
   ```
   POST /api/v1/admin/programs/4/invite
   Body: { "student_ids": [4] }
   ```
   - [ ] Response: HTTP 200
   - [ ] Application created ✓

2. **Update status to 'attend'**
   ```
   PATCH /api/v1/admin/programs/4/applications/status
   Body: { "applications": [{ "application_id": "prog_XXXXXXX", "status": "attend" }] }
   ```
   - [ ] Response: HTTP 200
   - [ ] Status changed to `'attend'`
   - [ ] **Certificate token IS generated** (if program is completed) ✓

3. **Change status back to 'accepted'**
   ```
   PATCH /api/v1/admin/programs/4/applications/status
   Body: { "applications": [{ "application_id": "prog_XXXXXXX", "status": "accepted" }] }
   ```
   - [ ] Response: HTTP 200
   - [ ] **Certificate token IS removed** ✓

---

## Test 7: ID Format Support

### Steps:
1. **Test with numeric ID**
   ```
   DELETE /api/v1/admin/opportunities/applications/12
   ```
   - [ ] Request processed correctly (200 or 404) ✓

2. **Test with formatted ID (short)**
   ```
   DELETE /api/v1/admin/opportunities/applications/opp_0000012
   ```
   - [ ] Request processed correctly (200 or 404) ✓
   - [ ] ID parsed to numeric `12` ✓

3. **Test with formatted ID (long)**
   ```
   DELETE /api/v1/admin/opportunities/applications/opp_120000000000000030450
   ```
   - [ ] Request processed correctly (404 expected) ✓
   - [ ] ID parsed to numeric `120000000000000030450` ✓
   - [ ] No server error (500) ✓

---

## Test 8: Error Handling

### Steps:
1. **Try to update non-existent application**
   ```
   PATCH /api/v1/admin/opportunities/7/applications/status
   Body: { "applications": [{ "application_id": "opp_9999999", "status": "attend" }] }
   ```
   - [ ] Response: HTTP 422 or errors in response ✓
   - [ ] Error message is clear ✓

2. **Try to delete non-existent application**
   ```
   DELETE /api/v1/admin/opportunities/applications/opp_9999999
   ```
   - [ ] Response: HTTP 404 ✓
   - [ ] Error message: "Application not found" ✓

3. **Try to generate certificates for non-completed opportunity**
   ```
   POST /api/v1/admin/opportunities/{activeOpportunityId}/generate-certificates
   ```
   - [ ] Response: HTTP 400 ✓
   - [ ] Error message: "Opportunity must be completed to generate certificates" ✓

---

## Test 9: Attendance Endpoints

### Steps:
1. **Get opportunity attendance**
   ```
   GET /api/v1/admin/opportunities/7/attendance
   ```
   - [ ] Response: HTTP 200 ✓
   - [ ] `applications` array includes only `'accepted'` and `'attend'` statuses ✓
   - [ ] `statistics` object includes counts ✓

2. **Get program attendance**
   ```
   GET /api/v1/admin/programs/4/attendance
   ```
   - [ ] Response: HTTP 200 ✓
   - [ ] `applications` array includes only `'accepted'` and `'attend'` statuses ✓
   - [ ] `statistics` object includes counts ✓

---

## Test 10: Excuse Management

### Steps:
1. **Get excuse details**
   ```
   GET /api/v1/admin/opportunities/applications/{applicationId}/excuse
   ```
   - [ ] Response: HTTP 200 (if excuse exists) ✓
   - [ ] Response: HTTP 400 (if no excuse) ✓

2. **Approve excuse**
   ```
   PATCH /api/v1/admin/opportunities/applications/{applicationId}/approve-excuse
   ```
   - [ ] Response: HTTP 200 ✓
   - [ ] Status changed to `'approved_excuse'` ✓
   - [ ] No certificate token (status not 'attend') ✓

3. **Reject excuse**
   ```
   PATCH /api/v1/admin/opportunities/applications/{applicationId}/reject-excuse
   ```
   - [ ] Response: HTTP 200 ✓
   - [ ] Status changed to `'rejected_excuse'` ✓
   - [ ] No certificate token (status not 'attend') ✓

---

## Summary

**Total Tests**: 10 test suites
**Key Features Tested**:
- ✅ Automatic token generation when status → 'attend' (completed opportunity/program)
- ✅ Automatic token removal when status changes from 'attend'
- ✅ Manual token generation endpoint
- ✅ Token not generated for non-completed opportunities/programs
- ✅ ID format parsing (numeric and formatted IDs)
- ✅ Error handling for non-existent resources
- ✅ Attendance tracking
- ✅ Excuse management

**Expected Pass Rate**: 100%

---

## Notes

- Replace `{opportunityId}`, `{programId}`, `{applicationId}` with actual IDs from your database
- Use opportunity 7 and program 4 if following the seeded data
- All endpoints require admin authentication (Bearer token in Authorization header)
- Tests can be run in any order, but some tests depend on previous test data

