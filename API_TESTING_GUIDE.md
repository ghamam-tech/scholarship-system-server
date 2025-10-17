# 🚀 Scholarship System API Testing Guide

## 📋 Current Database Status

### Available Data:

-   **Active Scholarships**: 3+ scholarships with 2025 dates
-   **Users**: 27 total (including sponsors, admin, and applicants)
-   **Countries**: 29 countries
-   **Universities**: 96 universities
-   **Sponsors**: 26 sponsors

### Test Credentials:

-   **Admin**: `admin@test.com` / `password123`
-   **Sponsors**: `sponsor.microsoft@example.com` / `password123`
-   **Applicants**: `applicant1@example.com` / `password123` (if seeded)

---

## 🔧 API Endpoints Testing

### 1. Authentication & User Management

#### Login (Get Token)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@test.com",
    "password": "password123"
  }'
```

#### Register New User

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "newuser@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "applicant"
  }'
```

---

### 2. Scholarship Endpoints

#### Get Public Scholarships (No Auth Required)

```bash
curl -X GET http://127.0.0.1:8000/api/v1/scholarships \
  -H "Accept: application/json"
```

#### Get All Scholarships (Admin Only)

```bash
curl -X GET http://127.0.0.1:8000/api/v1/scholarships/admin/all \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### Get Single Scholarship (Public)

```bash
curl -X GET http://127.0.0.1:8000/api/v1/scholarships/1 \
  -H "Accept: application/json"
```

---

### 3. Applicant Profile Management

#### Complete Applicant Profile

```bash
curl -X POST http://127.0.0.1:8000/api/v1/applicant/complete-profile \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "ar_name": "أحمد محمد علي",
    "en_name": "Ahmed Mohamed Ali",
    "nationality": "Saudi",
    "gender": "male",
    "place_of_birth": "Riyadh",
    "phone": "+966501234567",
    "passport_number": "A12345678",
    "date_of_birth": "2000-01-15",
    "parent_contact_name": "Mohamed Ahmed",
    "parent_contact_phone": "+966501234568",
    "residence_country": "Saudi Arabia",
    "language": "Arabic",
    "is_studied_in_saudi": true,
    "tahseeli_percentage": 85.5,
    "qudorat_percentage": 78.2
  }'
```

---

### 4. Qualification Management

#### Get Qualifications

```bash
curl -X GET http://127.0.0.1:8000/api/v1/qualifications \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### Create Qualification

```bash
curl -X POST http://127.0.0.1:8000/api/v1/qualifications \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "qualification_type": "bachelor",
    "institute_name": "King Saud University",
    "year_of_graduation": 2023,
    "cgpa": 3.8,
    "cgpa_out_of": 4.0,
    "language_of_study": "Arabic",
    "specialization": "Computer Science"
  }'
```

---

### 5. Application Management

#### Submit Complete Application (Main Endpoint)

```bash
curl -X POST http://127.0.0.1:8000/api/v1/applications/submit-complete \
  -H "Content-Type: multipart/form-data" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -F 'personal_info[ar_name]=أحمد محمد' \
  -F 'personal_info[en_name]=Ahmed Mohamed' \
  -F 'personal_info[nationality]=Saudi' \
  -F 'personal_info[gender]=male' \
  -F 'personal_info[place_of_birth]=Riyadh' \
  -F 'personal_info[phone]=+966501234567' \
  -F 'personal_info[passport_number]=A12345678' \
  -F 'personal_info[date_of_birth]=2000-01-15' \
  -F 'personal_info[parent_contact_name]=Mohamed Ahmed' \
  -F 'personal_info[parent_contact_phone]=+966501234568' \
  -F 'personal_info[residence_country]=Saudi Arabia' \
  -F 'personal_info[language]=Arabic' \
  -F 'personal_info[is_studied_in_saudi]=true' \
  -F 'personal_info[tahseeli_percentage]=85.5' \
  -F 'personal_info[qudorat_percentage]=78.2' \
  -F 'academic_info[qualifications][0][qualification_type]=high_school' \
  -F 'academic_info[qualifications][0][institute_name]=Al Nahda School' \
  -F 'academic_info[qualifications][0][year_of_graduation]=2019' \
  -F 'academic_info[qualifications][0][cgpa]=98.5' \
  -F 'academic_info[qualifications][0][cgpa_out_of]=99.99' \
  -F 'academic_info[qualifications][0][language_of_study]=Arabic' \
  -F 'academic_info[qualifications][0][specialization]=Science' \
  -F 'academic_info[qualifications][0][document_file]=@/path/to/document.pdf' \
  -F 'academic_info[qualifications][1][qualification_type]=bachelor' \
  -F 'academic_info[qualifications][1][institute_name]=King Saud University' \
  -F 'academic_info[qualifications][1][year_of_graduation]=2023' \
  -F 'academic_info[qualifications][1][cgpa]=3.8' \
  -F 'academic_info[qualifications][1][cgpa_out_of]=4.0' \
  -F 'academic_info[qualifications][1][language_of_study]=Arabic' \
  -F 'academic_info[qualifications][1][specialization]=Computer Science' \
  -F 'academic_info[qualifications][1][document_file]=@/path/to/document.pdf' \
  -F 'program_details[scholarship_ids][0]=1' \
  -F 'program_details[specialization_1]=Computer Science' \
  -F 'program_details[specialization_2]=Data Science' \
  -F 'program_details[specialization_3]=AI' \
  -F 'program_details[university_name]=Stanford University' \
  -F 'program_details[country_name]=USA' \
  -F 'program_details[tuition_fee]=50000' \
  -F 'program_details[has_active_program]=true' \
  -F 'program_details[current_semester_number]=2' \
  -F 'program_details[cgpa]=3.75' \
  -F 'program_details[cgpa_out_of]=4.0' \
  -F 'program_details[terms_and_condition]=true' \
  -F 'passport_copy=@/path/to/passport.pdf' \
  -F 'personal_image=@/path/to/image.jpg' \
  -F 'secondary_school_certificate=@/path/to/certificate.pdf' \
  -F 'secondary_school_transcript=@/path/to/transcript.pdf' \
  -F 'volunteering_certificate=@/path/to/volunteering.pdf' \
  -F 'offer_letter=@/path/to/offer.pdf'
```

#### Get Applications

```bash
curl -X GET http://127.0.0.1:8000/api/v1/applications \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### Get Single Application

```bash
curl -X GET http://127.0.0.1:8000/api/v1/applications/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

#### Update Program Details

```bash
curl -X PUT http://127.0.0.1:8000/api/v1/applications/1/program-details \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "specialization_2": "Machine Learning",
    "university_name": "MIT",
    "tuition_fee": 60000
  }'
```

---

### 6. Admin Endpoints

#### Get All Applications (Admin)

```bash
curl -X GET http://127.0.0.1:8000/api/v1/admin/applications \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ADMIN_TOKEN_HERE"
```

#### Update Application Status (Admin)

```bash
curl -X PUT http://127.0.0.1:8000/api/v1/applications/1/status \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer ADMIN_TOKEN_HERE" \
  -d '{
    "status_name": "approved",
    "comment": "Application approved after review"
  }'
```

#### Get Statistics (Admin)

```bash
curl -X GET http://127.0.0.1:8000/api/v1/admin/statistics \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ADMIN_TOKEN_HERE"
```

---

## 🧪 Testing Scripts

### PowerShell Testing Script

```powershell
# Set base URL
$baseUrl = "http://127.0.0.1:8000/api/v1"

# Login and get token
$loginResponse = Invoke-RestMethod -Uri "$baseUrl/auth/login" -Method POST -ContentType "application/json" -Body '{
    "email": "admin@test.com",
    "password": "password123"
}'

$token = $loginResponse.token
$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
}

# Test public scholarships
Write-Host "Testing public scholarships..."
$scholarships = Invoke-RestMethod -Uri "$baseUrl/scholarships" -Method GET -Headers @{"Accept" = "application/json"}
Write-Host "Found $($scholarships.data.Count) public scholarships"

# Test admin scholarships
Write-Host "Testing admin scholarships..."
$adminScholarships = Invoke-RestMethod -Uri "$baseUrl/scholarships/admin/all" -Method GET -Headers $headers
Write-Host "Found $($adminScholarships.data.Count) total scholarships (admin view)"

# Test applications
Write-Host "Testing applications..."
$applications = Invoke-RestMethod -Uri "$baseUrl/applications" -Method GET -Headers $headers
Write-Host "Found $($applications.Count) applications"
```

### Bash Testing Script

```bash
#!/bin/bash

BASE_URL="http://127.0.0.1:8000/api/v1"

echo "=== Testing Scholarship System API ==="

# Login
echo "1. Logging in..."
TOKEN=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@test.com", "password": "password123"}' | \
  jq -r '.token')

echo "Token: $TOKEN"

# Test public scholarships
echo "2. Testing public scholarships..."
curl -s -X GET "$BASE_URL/scholarships" \
  -H "Accept: application/json" | jq '.data | length'

# Test admin scholarships
echo "3. Testing admin scholarships..."
curl -s -X GET "$BASE_URL/scholarships/admin/all" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.data | length'

# Test applications
echo "4. Testing applications..."
curl -s -X GET "$BASE_URL/applications" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq 'length'

echo "=== Testing Complete ==="
```

---

## 📊 Expected Responses

### Successful Application Submission

```json
{
    "message": "Application submitted successfully",
    "application_id": 1,
    "application": {
        "application_id": 1,
        "applicant_id": 1,
        "scholarship_id_1": 1,
        "specialization_1": "Computer Science",
        "specialization_2": "Data Science",
        "university_name": "Stanford University",
        "country_name": "USA",
        "tuition_fee": 50000,
        "has_active_program": true,
        "current_semester_number": 2,
        "cgpa": 3.75,
        "cgpa_out_of": 4.0,
        "terms_and_condition": true,
        "applicant": {
            "applicant_id": 1,
            "ar_name": "أحمد محمد علي",
            "en_name": "Ahmed Mohamed Ali",
            "tahseeli_percentage": 85.5,
            "qudorat_percentage": 78.2,
            "qualifications": [
                {
                    "qualification_id": 1,
                    "qualification_type": "high_school",
                    "institute_name": "Al Nahda School",
                    "year_of_graduation": 2019,
                    "cgpa": 98.5,
                    "cgpa_out_of": 99.99,
                    "language_of_study": "Arabic",
                    "specialization": "Science"
                }
            ]
        },
        "scholarship1": {
            "scholarship_id": 1,
            "scholarship_name": "Microsoft Tech Scholarship Program",
            "scholarship_type": "Merit-based"
        }
    }
}
```

---

## 🔍 Database Verification

### Check Current Data

```sql
-- Check active scholarships
SELECT scholarship_id, scholarship_name, closing_date, is_active, is_hided
FROM scholarships
WHERE is_active = 1 AND is_hided = 0 AND closing_date > NOW();

-- Check applicants with qualifications
SELECT a.applicant_id, a.en_name, a.tahseeli_percentage, a.qudorat_percentage,
       COUNT(q.qualification_id) as qualification_count
FROM applicants a
LEFT JOIN qualifications q ON a.applicant_id = q.applicant_id
GROUP BY a.applicant_id;

-- Check applications
SELECT aa.application_id, a.en_name, s.scholarship_name
FROM applicant_applications aa
JOIN applicants a ON aa.applicant_id = a.applicant_id
JOIN scholarships s ON aa.scholarship_id_1 = s.scholarship_id;
```

---

## ✅ Testing Checklist

-   [ ] Authentication endpoints work
-   [ ] Public scholarship listing works
-   [ ] Admin scholarship listing works
-   [ ] Applicant profile creation works
-   [ ] Qualification management works
-   [ ] Complete application submission works
-   [ ] File uploads work
-   [ ] Application retrieval works
-   [ ] Admin endpoints are protected
-   [ ] Data relationships are correct (qualifications linked to applicants)
-   [ ] Tahseeli and Qudorat percentages are in applicant table (not qualifications)

---

## 🚨 Common Issues & Solutions

1. **Token Expired**: Re-login to get a new token
2. **File Upload Issues**: Ensure files exist and are accessible
3. **Validation Errors**: Check required fields and data types
4. **Permission Denied**: Ensure using correct role (admin vs applicant)
5. **Database Errors**: Check if migrations are up to date

---

## 📝 Notes

-   All endpoints require authentication except public scholarship listing
-   File uploads use multipart/form-data
-   Tahseeli and Qudorat percentages are stored in the applicant table (not qualifications)
-   Qualifications are linked to applicants (not applications)
-   The system follows the ERD structure exactly
