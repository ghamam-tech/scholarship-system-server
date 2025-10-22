#!/bin/bash

# Student Invitation System - CURL Test Commands
# Replace the tokens and IDs with your actual values

BASE_URL="http://localhost:8000/api/v1"
ADMIN_TOKEN="YOUR_ADMIN_TOKEN_HERE"
STUDENT_TOKEN="YOUR_STUDENT_TOKEN_HERE"
PROGRAM_ID="1"
STUDENT_ID="1"
APPLICATION_ID="1"

echo "=== Student Invitation System CURL Tests ==="
echo "Base URL: $BASE_URL"
echo ""

echo "1. Create a Program (Admin)"
echo "curl -X POST $BASE_URL/admin/programs \\"
echo "  -H \"Authorization: Bearer $ADMIN_TOKEN\" \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{\"title\": \"Test Workshop\", \"date\": \"2025-11-15\", \"location\": \"Test Location\", \"enable_qr_attendance\": true}'"
echo ""

echo "2. Create a Student (Admin)"
echo "curl -X POST $BASE_URL/admin/students \\"
echo "  -H \"Authorization: Bearer $ADMIN_TOKEN\" \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{\"email\": \"teststudent@example.com\", \"password\": \"password123\"}'"
echo ""

echo "3. Invite Student to Program (Admin)"
echo "curl -X POST $BASE_URL/admin/programs/$PROGRAM_ID/invite \\"
echo "  -H \"Authorization: Bearer $ADMIN_TOKEN\" \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{\"student_id\": $STUDENT_ID}'"
echo ""

echo "4. Student Accepts Invitation"
echo "curl -X PATCH $BASE_URL/student/applications/$APPLICATION_ID/accept \\"
echo "  -H \"Authorization: Bearer $STUDENT_TOKEN\""
echo ""

echo "5. Student Rejects with Excuse"
echo "curl -X PATCH $BASE_URL/student/applications/$APPLICATION_ID/reject \\"
echo "  -H \"Authorization: Bearer $STUDENT_TOKEN\" \\"
echo "  -F \"excuse_reason=I have a medical appointment\" \\"
echo "  -F \"excuse_file=@/path/to/excuse.pdf\""
echo ""

echo "6. Admin Approves Excuse"
echo "curl -X PATCH $BASE_URL/admin/applications/$APPLICATION_ID/approve-excuse \\"
echo "  -H \"Authorization: Bearer $ADMIN_TOKEN\""
echo ""

echo "7. Student QR Attendance"
echo "curl -X PATCH $BASE_URL/student/applications/$APPLICATION_ID/attendance \\"
echo "  -H \"Authorization: Bearer $STUDENT_TOKEN\" \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{\"email\": \"teststudent@example.com\", \"password\": \"password123\"}'"
echo ""

echo "8. View Program Applications (Admin)"
echo "curl -X GET $BASE_URL/admin/programs/$PROGRAM_ID/applications \\"
echo "  -H \"Authorization: Bearer $ADMIN_TOKEN\""
echo ""

echo "9. View My Applications (Student)"
echo "curl -X GET $BASE_URL/student/applications \\"
echo "  -H \"Authorization: Bearer $STUDENT_TOKEN\""
echo ""

echo "10. Invite Multiple Students"
echo "curl -X POST $BASE_URL/admin/programs/$PROGRAM_ID/invite-multiple \\"
echo "  -H \"Authorization: Bearer $ADMIN_TOKEN\" \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{\"student_ids\": [1, 2, 3]}'"
echo ""

echo "=== Testing Instructions ==="
echo "1. Replace YOUR_ADMIN_TOKEN_HERE with actual admin token"
echo "2. Replace YOUR_STUDENT_TOKEN_HERE with actual student token"
echo "3. Update PROGRAM_ID, STUDENT_ID, APPLICATION_ID with actual values"
echo "4. Run the commands in order"
echo "5. Check responses for success/error messages"
echo ""

echo "=== Expected Status Flow ==="
echo "invite → accepted → attend (Happy Path)"
echo "invite → excuse → approved_excuse (Excuse Approved)"
echo "invite → excuse → doesn_attend (Excuse Rejected)"
echo ""
