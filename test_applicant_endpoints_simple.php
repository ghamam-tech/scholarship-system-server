<?php

echo "=== ApplicantController Endpoint Testing Guide ===\n\n";

echo "Available Endpoints:\n";
echo "===================\n\n";

echo "1. POST /api/v1/applicant/complete-profile\n";
echo "   - Complete applicant profile with personal info, academic info, and file uploads\n";
echo "   - Requires: Authentication token\n";
echo "   - Files: passport_copy, personal_image, tahsili_file, qudorat_file, volunteering_certificate\n";
echo "   - Academic: At least 1 qualification with document_file\n\n";

echo "2. PUT /api/v1/applicant/profile\n";
echo "   - Update applicant profile (partial update)\n";
echo "   - Requires: Authentication token\n";
echo "   - Optional: personal_info fields, document files\n\n";

echo "3. GET /api/v1/applicant/profile\n";
echo "   - Get applicant profile with qualifications\n";
echo "   - Requires: Authentication token\n";
echo "   - Returns: Complete applicant data with relationships\n\n";

echo "4. POST /api/v1/applicant/qualifications\n";
echo "   - Add new qualification\n";
echo "   - Requires: Authentication token\n";
echo "   - Required: qualification_type, institute_name, year_of_graduation, document_file\n\n";

echo "5. PUT /api/v1/applicant/qualifications/{qualificationId}\n";
echo "   - Update existing qualification\n";
echo "   - Requires: Authentication token\n";
echo "   - Optional: All qualification fields including new document_file\n\n";

echo "6. DELETE /api/v1/applicant/qualifications/{qualificationId}\n";
echo "   - Delete qualification and its associated file\n";
echo "   - Requires: Authentication token\n\n";

echo "7. GET /api/v1/applicants (Admin only)\n";
echo "   - List all applicants with relationships\n";
echo "   - Requires: Admin authentication token\n\n";

echo "8. GET /api/v1/applicants/{applicant} (Admin only)\n";
echo "   - Get specific applicant details\n";
echo "   - Requires: Admin authentication token\n\n";

echo "9. DELETE /api/v1/applicants/{applicant} (Admin only)\n";
echo "   - Delete applicant and all associated files\n";
echo "   - Requires: Admin authentication token\n\n";

echo "=== Test Data Examples ===\n\n";

echo "Complete Profile Request Body:\n";
echo "{\n";
echo '  "personal_info": {' . "\n";
echo '    "ar_name": "أحمد محمد",' . "\n";
echo '    "en_name": "Ahmed Mohammed",' . "\n";
echo '    "nationality": "Saudi",' . "\n";
echo '    "gender": "male",' . "\n";
echo '    "place_of_birth": "Riyadh",' . "\n";
echo '    "phone": "+966501234567",' . "\n";
echo '    "passport_number": "A1234567",' . "\n";
echo '    "date_of_birth": "1995-01-01",' . "\n";
echo '    "parent_contact_name": "Mohammed Ahmed",' . "\n";
echo '    "parent_contact_phone": "+966501234568",' . "\n";
echo '    "residence_country": "Saudi Arabia",' . "\n";
echo '    "language": "Arabic",' . "\n";
echo '    "is_studied_in_saudi": true,' . "\n";
echo '    "tahseeli_percentage": 85.5,' . "\n";
echo '    "qudorat_percentage": 90.0' . "\n";
echo '  },' . "\n";
echo '  "academic_info": {' . "\n";
echo '    "qualifications": [' . "\n";
echo '      {' . "\n";
echo '        "qualification_type": "bachelor",' . "\n";
echo '        "institute_name": "King Saud University",' . "\n";
echo '        "year_of_graduation": 2020,' . "\n";
echo '        "cgpa": 3.8,' . "\n";
echo '        "cgpa_out_of": 4.0,' . "\n";
echo '        "language_of_study": "Arabic",' . "\n";
echo '        "specialization": "Computer Science",' . "\n";
echo '        "research_title": "Machine Learning Applications"' . "\n";
echo '      }' . "\n";
echo '    ]' . "\n";
echo '  }' . "\n";
echo "}\n\n";

echo "=== cURL Test Commands ===\n\n";

echo "# 1. Complete Profile (with files)\n";
echo "curl -X POST \"http://localhost:8000/api/v1/applicant/complete-profile\" \\\n";
echo "  -H \"Authorization: Bearer YOUR_TOKEN\" \\\n";
echo "  -H \"Content-Type: multipart/form-data\" \\\n";
echo "  -F \"personal_info[ar_name]=أحمد محمد\" \\\n";
echo "  -F \"personal_info[en_name]=Ahmed Mohammed\" \\\n";
echo "  -F \"personal_info[nationality]=Saudi\" \\\n";
echo "  -F \"personal_info[gender]=male\" \\\n";
echo "  -F \"personal_info[passport_number]=A1234567\" \\\n";
echo "  -F \"personal_info[date_of_birth]=1995-01-01\" \\\n";
echo "  -F \"personal_info[phone]=+966501234567\" \\\n";
echo "  -F \"personal_info[place_of_birth]=Riyadh\" \\\n";
echo "  -F \"personal_info[parent_contact_name]=Mohammed Ahmed\" \\\n";
echo "  -F \"personal_info[parent_contact_phone]=+966501234568\" \\\n";
echo "  -F \"personal_info[residence_country]=Saudi Arabia\" \\\n";
echo "  -F \"personal_info[language]=Arabic\" \\\n";
echo "  -F \"personal_info[is_studied_in_saudi]=true\" \\\n";
echo "  -F \"academic_info[qualifications][0][qualification_type]=bachelor\" \\\n";
echo "  -F \"academic_info[qualifications][0][institute_name]=King Saud University\" \\\n";
echo "  -F \"academic_info[qualifications][0][year_of_graduation]=2020\" \\\n";
echo "  -F \"academic_info[qualifications][0][cgpa]=3.8\" \\\n";
echo "  -F \"academic_info[qualifications][0][cgpa_out_of]=4.0\" \\\n";
echo "  -F \"academic_info[qualifications][0][language_of_study]=Arabic\" \\\n";
echo "  -F \"academic_info[qualifications][0][specialization]=Computer Science\" \\\n";
echo "  -F \"academic_info[qualifications][0][document_file]=@/path/to/certificate.pdf\" \\\n";
echo "  -F \"passport_copy=@/path/to/passport.pdf\" \\\n";
echo "  -F \"personal_image=@/path/to/photo.jpg\" \\\n";
echo "  -F \"tahsili_file=@/path/to/tahsili.pdf\" \\\n";
echo "  -F \"qudorat_file=@/path/to/qudorat.pdf\"\n\n";

echo "# 2. Get Profile\n";
echo "curl -X GET \"http://localhost:8000/api/v1/applicant/profile\" \\\n";
echo "  -H \"Authorization: Bearer YOUR_TOKEN\"\n\n";

echo "# 3. Update Profile\n";
echo "curl -X PUT \"http://localhost:8000/api/v1/applicant/profile\" \\\n";
echo "  -H \"Authorization: Bearer YOUR_TOKEN\" \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"personal_info\":{\"ar_name\":\"محمد أحمد\",\"phone\":\"+966501234569\"}}'\n\n";

echo "# 4. Add Qualification\n";
echo "curl -X POST \"http://localhost:8000/api/v1/applicant/qualifications\" \\\n";
echo "  -H \"Authorization: Bearer YOUR_TOKEN\" \\\n";
echo "  -H \"Content-Type: multipart/form-data\" \\\n";
echo "  -F \"qualification_type=master\" \\\n";
echo "  -F \"institute_name=MIT\" \\\n";
echo "  -F \"year_of_graduation=2022\" \\\n";
echo "  -F \"cgpa=3.9\" \\\n";
echo "  -F \"cgpa_out_of=4.0\" \\\n";
echo "  -F \"language_of_study=English\" \\\n";
echo "  -F \"specialization=Artificial Intelligence\" \\\n";
echo "  -F \"research_title=Deep Learning in Healthcare\" \\\n";
echo "  -F \"document_file=@/path/to/master_cert.pdf\"\n\n";

echo "=== Validation Rules ===\n\n";
echo "Personal Info:\n";
echo "- ar_name: required, string, max:255\n";
echo "- en_name: required, string, max:255\n";
echo "- nationality: required, string, max:100\n";
echo "- gender: required, in:male,female\n";
echo "- passport_number: required, string, max:50, unique\n";
echo "- date_of_birth: required, date\n";
echo "- phone: required, string, max:20\n";
echo "- tahseeli_percentage: nullable, numeric, min:0, max:100\n";
echo "- qudorat_percentage: nullable, numeric, min:0, max:100\n\n";

echo "Qualifications:\n";
echo "- qualification_type: required, in:high_school,diploma,bachelor,master,phd,other\n";
echo "- institute_name: required, string, max:255\n";
echo "- year_of_graduation: required, integer, min:1900, max:current_year+5\n";
echo "- cgpa: nullable, numeric, min:0\n";
echo "- cgpa_out_of: nullable, numeric, min:0\n";
echo "- document_file: required, file, mimes:jpeg,png,jpg,pdf, max:10240KB\n\n";

echo "File Uploads:\n";
echo "- passport_copy: required, file, mimes:jpeg,png,jpg,pdf, max:10240KB\n";
echo "- personal_image: required, file, mimes:jpeg,png,jpg, max:5120KB\n";
echo "- tahsili_file: required, file, mimes:jpeg,png,jpg,pdf, max:10240KB\n";
echo "- qudorat_file: required, file, mimes:jpeg,png,jpg,pdf, max:10240KB\n";
echo "- volunteering_certificate: nullable, file, mimes:jpeg,png,jpg,pdf, max:10240KB\n\n";

echo "=== Test Scenarios ===\n\n";
echo "1. ✓ Valid complete profile with all required fields\n";
echo "2. ✓ Valid complete profile with multiple qualifications\n";
echo "3. ✗ Missing required personal info fields\n";
echo "4. ✗ Invalid file types (txt, doc, etc.)\n";
echo "5. ✗ Files too large (>10MB for documents, >5MB for images)\n";
echo "6. ✗ Duplicate passport number\n";
echo "7. ✗ Invalid qualification type\n";
echo "8. ✗ Future graduation year\n";
echo "9. ✗ Unauthenticated requests\n";
echo "10. ✗ Non-admin accessing admin endpoints\n\n";

echo "=== Expected Responses ===\n\n";
echo "Success (201):\n";
echo '{"message":"Profile completed successfully","applicant":{...}}' . "\n\n";

echo "Success (200):\n";
echo '{"message":"Profile updated successfully","applicant":{...}}' . "\n\n";

echo "Validation Error (422):\n";
echo '{"message":"The given data was invalid.","errors":{"personal_info.ar_name":["The personal info.ar name field is required."]}}' . "\n\n";

echo "Not Found (404):\n";
echo '{"message":"Applicant profile not found"}' . "\n\n";

echo "Unauthorized (401):\n";
echo '{"message":"Unauthenticated."}' . "\n\n";

echo "=== Testing Complete ===\n";
