<?php

echo "🔧 SIMPLE PROGRAM UPDATE TEST\n";
echo "============================\n\n";

echo "📋 TESTING STEPS:\n\n";

echo "1️⃣ FIRST - Check if you have programs:\n";
echo "GET /api/v1/admin/programs\n";
echo "Headers: Authorization: Bearer {your_admin_token}\n\n";

echo "2️⃣ SECOND - Try a simple update:\n";
echo "PUT /api/v1/admin/programs/1\n";
echo "Headers: Authorization: Bearer {your_admin_token}\n";
echo "Content-Type: application/json\n";
echo "Body: {\"title\": \"Updated Title\"}\n\n";

echo "3️⃣ THIRD - If that works, try full update:\n";
echo "PUT /api/v1/admin/programs/1\n";
echo "Headers: Authorization: Bearer {your_admin_token}\n";
echo "Content-Type: application/json\n";
echo "Body:\n";
echo "{\n";
echo "  \"title\": \"Updated Workshop\",\n";
echo "  \"discription\": \"Updated description\",\n";
echo "  \"date\": \"2025-12-01\",\n";
echo "  \"location\": \"Updated Location\",\n";
echo "  \"country\": \"Saudi Arabia\",\n";
echo "  \"category\": \"Workshop\",\n";
echo "  \"program_coordinatior_name\": \"John Doe\",\n";
echo "  \"program_coordinatior_phone\": \"+966501234567\",\n";
echo "  \"program_coordinatior_email\": \"john@example.com\",\n";
echo "  \"enable_qr_attendance\": true,\n";
echo "  \"generate_certificates\": false\n";
echo "}\n\n";

echo "🚨 COMMON ISSUES:\n\n";

echo "❌ 404 Not Found:\n";
echo "- Program doesn't exist\n";
echo "- Wrong program ID\n";
echo "- Solution: Check GET /api/v1/admin/programs first\n\n";

echo "❌ 403 Forbidden:\n";
echo "- Invalid admin token\n";
echo "- User is not admin\n";
echo "- Solution: Check authentication\n\n";

echo "❌ 422 Validation Error:\n";
echo "- Invalid data format\n";
echo "- Missing required fields\n";
echo "- Solution: Check request body\n\n";

echo "❌ 500 Server Error:\n";
echo "- Database issue\n";
echo "- Solution: Check Laravel logs\n\n";

echo "🔍 DEBUGGING:\n\n";

echo "Check Laravel logs:\n";
echo "tail -f storage/logs/laravel.log\n\n";

echo "Test with cURL:\n";
echo "curl -X PUT http://localhost:8000/api/v1/admin/programs/1 \\\n";
echo "  -H \"Authorization: Bearer YOUR_TOKEN\" \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"title\": \"Test Update\"}'\n\n";

echo "📞 WHAT ERROR ARE YOU GETTING?\n";
echo "Please share the exact error message or response you're seeing.\n\n";
