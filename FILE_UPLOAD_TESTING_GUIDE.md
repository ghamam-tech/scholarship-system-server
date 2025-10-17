# 📁 File Upload Testing Guide

## 🎯 Test Files Available

The following test files have been created in the `test_files/` directory:

-   `passport_copy.pdf` - For passport document uploads
-   `personal_image.jpg` - For personal photo uploads
-   `secondary_school_certificate.pdf` - For school certificate uploads
-   `secondary_school_transcript.pdf` - For school transcript uploads
-   `volunteering_certificate.pdf` - For volunteering certificate uploads
-   `offer_letter.pdf` - For offer letter uploads
-   `qualification_doc1.pdf` - For qualification document 1
-   `qualification_doc2.pdf` - For qualification document 2
-   `tahsili_file.pdf` - For Tahsili exam certificate
-   `qudorat_file.pdf` - For Qudorat exam certificate

---

## 🚀 Complete Application Submission with Files

### Method 1: Using curl (Command Line)

```bash
# First, get your authentication token
TOKEN=$(curl -s -X POST "http://127.0.0.1:8000/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@test.com", "password": "password123"}' | \
  jq -r '.token')

# Submit complete application with all files
curl -X POST http://127.0.0.1:8000/api/v1/applications/submit-complete \
  -H "Authorization: Bearer $TOKEN" \
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
  -F 'academic_info[qualifications][0][document_file]=@test_files/qualification_doc1.pdf' \
  -F 'academic_info[qualifications][1][qualification_type]=bachelor' \
  -F 'academic_info[qualifications][1][institute_name]=King Saud University' \
  -F 'academic_info[qualifications][1][year_of_graduation]=2023' \
  -F 'academic_info[qualifications][1][cgpa]=3.8' \
  -F 'academic_info[qualifications][1][cgpa_out_of]=4.0' \
  -F 'academic_info[qualifications][1][language_of_study]=Arabic' \
  -F 'academic_info[qualifications][1][specialization]=Computer Science' \
  -F 'academic_info[qualifications][1][document_file]=@test_files/qualification_doc2.pdf' \
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
  -F 'passport_copy=@test_files/passport_copy.pdf' \
  -F 'personal_image=@test_files/personal_image.jpg' \
  -F 'secondary_school_certificate=@test_files/secondary_school_certificate.pdf' \
  -F 'secondary_school_transcript=@test_files/secondary_school_transcript.pdf' \
  -F 'volunteering_certificate=@test_files/volunteering_certificate.pdf' \
  -F 'offer_letter=@test_files/offer_letter.pdf'
```

### Method 2: Using PowerShell

```powershell
# Set base URL and get token
$baseUrl = "http://127.0.0.1:8000/api/v1"
$loginResponse = Invoke-RestMethod -Uri "$baseUrl/auth/login" -Method POST -ContentType "application/json" -Body '{
    "email": "admin@test.com",
    "password": "password123"
}'
$token = $loginResponse.token

# Create form data for file upload
$form = @{
    'personal_info[ar_name]' = 'أحمد محمد'
    'personal_info[en_name]' = 'Ahmed Mohamed'
    'personal_info[nationality]' = 'Saudi'
    'personal_info[gender]' = 'male'
    'personal_info[place_of_birth]' = 'Riyadh'
    'personal_info[phone]' = '+966501234567'
    'personal_info[passport_number]' = 'A12345678'
    'personal_info[date_of_birth]' = '2000-01-15'
    'personal_info[parent_contact_name]' = 'Mohamed Ahmed'
    'personal_info[parent_contact_phone]' = '+966501234568'
    'personal_info[residence_country]' = 'Saudi Arabia'
    'personal_info[language]' = 'Arabic'
    'personal_info[is_studied_in_saudi]' = 'true'
    'personal_info[tahseeli_percentage]' = '85.5'
    'personal_info[qudorat_percentage]' = '78.2'
    'academic_info[qualifications][0][qualification_type]' = 'high_school'
    'academic_info[qualifications][0][institute_name]' = 'Al Nahda School'
    'academic_info[qualifications][0][year_of_graduation]' = '2019'
    'academic_info[qualifications][0][cgpa]' = '98.5'
    'academic_info[qualifications][0][cgpa_out_of]' = '99.99'
    'academic_info[qualifications][0][language_of_study]' = 'Arabic'
    'academic_info[qualifications][0][specialization]' = 'Science'
    'academic_info[qualifications][1][qualification_type]' = 'bachelor'
    'academic_info[qualifications][1][institute_name]' = 'King Saud University'
    'academic_info[qualifications][1][year_of_graduation]' = '2023'
    'academic_info[qualifications][1][cgpa]' = '3.8'
    'academic_info[qualifications][1][cgpa_out_of]' = '4.0'
    'academic_info[qualifications][1][language_of_study]' = 'Arabic'
    'academic_info[qualifications][1][specialization]' = 'Computer Science'
    'program_details[scholarship_ids][0]' = '1'
    'program_details[specialization_1]' = 'Computer Science'
    'program_details[specialization_2]' = 'Data Science'
    'program_details[specialization_3]' = 'AI'
    'program_details[university_name]' = 'Stanford University'
    'program_details[country_name]' = 'USA'
    'program_details[tuition_fee]' = '50000'
    'program_details[has_active_program]' = 'true'
    'program_details[current_semester_number]' = '2'
    'program_details[cgpa]' = '3.75'
    'program_details[cgpa_out_of]' = '4.0'
    'program_details[terms_and_condition]' = 'true'
}

# Add files to form
$files = @{
    'academic_info[qualifications][0][document_file]' = Get-Item 'test_files/qualification_doc1.pdf'
    'academic_info[qualifications][1][document_file]' = Get-Item 'test_files/qualification_doc2.pdf'
    'passport_copy' = Get-Item 'test_files/passport_copy.pdf'
    'personal_image' = Get-Item 'test_files/personal_image.jpg'
    'secondary_school_certificate' = Get-Item 'test_files/secondary_school_certificate.pdf'
    'secondary_school_transcript' = Get-Item 'test_files/secondary_school_transcript.pdf'
    'volunteering_certificate' = Get-Item 'test_files/volunteering_certificate.pdf'
    'offer_letter' = Get-Item 'test_files/offer_letter.pdf'
}

# Submit application
$response = Invoke-RestMethod -Uri "$baseUrl/applications/submit-complete" -Method POST -Form $form -InFile $files -Headers @{
    "Authorization" = "Bearer $token"
}

Write-Host "Application submitted successfully!"
Write-Host "Application ID: $($response.application_id)"
```

### Method 3: Using Postman

1. **Set Method**: POST
2. **URL**: `http://127.0.0.1:8000/api/v1/applications/submit-complete`
3. **Headers**:
    - `Authorization: Bearer YOUR_TOKEN`
4. **Body**: Select `form-data`
5. **Add Fields**:

| Key                                                    | Type | Value                            |
| ------------------------------------------------------ | ---- | -------------------------------- |
| `personal_info[ar_name]`                               | Text | أحمد محمد                        |
| `personal_info[en_name]`                               | Text | Ahmed Mohamed                    |
| `personal_info[nationality]`                           | Text | Saudi                            |
| `personal_info[gender]`                                | Text | male                             |
| `personal_info[place_of_birth]`                        | Text | Riyadh                           |
| `personal_info[phone]`                                 | Text | +966501234567                    |
| `personal_info[passport_number]`                       | Text | A12345678                        |
| `personal_info[date_of_birth]`                         | Text | 2000-01-15                       |
| `personal_info[parent_contact_name]`                   | Text | Mohamed Ahmed                    |
| `personal_info[parent_contact_phone]`                  | Text | +966501234568                    |
| `personal_info[residence_country]`                     | Text | Saudi Arabia                     |
| `personal_info[language]`                              | Text | Arabic                           |
| `personal_info[is_studied_in_saudi]`                   | Text | true                             |
| `personal_info[tahseeli_percentage]`                   | Text | 85.5                             |
| `personal_info[qudorat_percentage]`                    | Text | 78.2                             |
| `academic_info[qualifications][0][qualification_type]` | Text | high_school                      |
| `academic_info[qualifications][0][institute_name]`     | Text | Al Nahda School                  |
| `academic_info[qualifications][0][year_of_graduation]` | Text | 2019                             |
| `academic_info[qualifications][0][cgpa]`               | Text | 98.5                             |
| `academic_info[qualifications][0][cgpa_out_of]`        | Text | 99.99                            |
| `academic_info[qualifications][0][language_of_study]`  | Text | Arabic                           |
| `academic_info[qualifications][0][specialization]`     | Text | Science                          |
| `academic_info[qualifications][0][document_file]`      | File | qualification_doc1.pdf           |
| `academic_info[qualifications][1][qualification_type]` | Text | bachelor                         |
| `academic_info[qualifications][1][institute_name]`     | Text | King Saud University             |
| `academic_info[qualifications][1][year_of_graduation]` | Text | 2023                             |
| `academic_info[qualifications][1][cgpa]`               | Text | 3.8                              |
| `academic_info[qualifications][1][cgpa_out_of]`        | Text | 4.0                              |
| `academic_info[qualifications][1][language_of_study]`  | Text | Arabic                           |
| `academic_info[qualifications][1][specialization]`     | Text | Computer Science                 |
| `academic_info[qualifications][1][document_file]`      | File | qualification_doc2.pdf           |
| `program_details[scholarship_ids][0]`                  | Text | 1                                |
| `program_details[specialization_1]`                    | Text | Computer Science                 |
| `program_details[specialization_2]`                    | Text | Data Science                     |
| `program_details[specialization_3]`                    | Text | AI                               |
| `program_details[university_name]`                     | Text | Stanford University              |
| `program_details[country_name]`                        | Text | USA                              |
| `program_details[tuition_fee]`                         | Text | 50000                            |
| `program_details[has_active_program]`                  | Text | true                             |
| `program_details[current_semester_number]`             | Text | 2                                |
| `program_details[cgpa]`                                | Text | 3.75                             |
| `program_details[cgpa_out_of]`                         | Text | 4.0                              |
| `program_details[terms_and_condition]`                 | Text | true                             |
| `passport_copy`                                        | File | passport_copy.pdf                |
| `personal_image`                                       | File | personal_image.jpg               |
| `secondary_school_certificate`                         | File | secondary_school_certificate.pdf |
| `secondary_school_transcript`                          | File | secondary_school_transcript.pdf  |
| `volunteering_certificate`                             | File | volunteering_certificate.pdf     |
| `offer_letter`                                         | File | offer_letter.pdf                 |

---

## 🧪 Individual File Upload Tests

### Test Passport Upload

```bash
curl -X POST http://127.0.0.1:8000/api/v1/applications/submit-complete \
  -H "Authorization: Bearer $TOKEN" \
  -F 'personal_info[en_name]=Test User' \
  -F 'personal_info[nationality]=Saudi' \
  -F 'personal_info[gender]=male' \
  -F 'personal_info[phone]=+966501234567' \
  -F 'personal_info[passport_number]=TEST123456' \
  -F 'personal_info[date_of_birth]=2000-01-15' \
  -F 'personal_info[residence_country]=Saudi Arabia' \
  -F 'personal_info[language]=Arabic' \
  -F 'personal_info[is_studied_in_saudi]=true' \
  -F 'personal_info[tahseeli_percentage]=85.5' \
  -F 'personal_info[qudorat_percentage]=78.2' \
  -F 'academic_info[qualifications][0][qualification_type]=high_school' \
  -F 'academic_info[qualifications][0][institute_name]=Test School' \
  -F 'academic_info[qualifications][0][year_of_graduation]=2019' \
  -F 'academic_info[qualifications][0][cgpa]=98.5' \
  -F 'academic_info[qualifications][0][cgpa_out_of]=99.99' \
  -F 'academic_info[qualifications][0][language_of_study]=Arabic' \
  -F 'academic_info[qualifications][0][specialization]=Science' \
  -F 'program_details[scholarship_ids][0]=1' \
  -F 'program_details[specialization_1]=Computer Science' \
  -F 'program_details[university_name]=Test University' \
  -F 'program_details[country_name]=USA' \
  -F 'program_details[tuition_fee]=50000' \
  -F 'program_details[has_active_program]=true' \
  -F 'program_details[terms_and_condition]=true' \
  -F 'passport_copy=@test_files/passport_copy.pdf'
```

### Test Personal Image Upload

```bash
curl -X POST http://127.0.0.1:8000/api/v1/applications/submit-complete \
  -H "Authorization: Bearer $TOKEN" \
  -F 'personal_info[en_name]=Test User' \
  -F 'personal_info[nationality]=Saudi' \
  -F 'personal_info[gender]=male' \
  -F 'personal_info[phone]=+966501234567' \
  -F 'personal_info[passport_number]=TEST123456' \
  -F 'personal_info[date_of_birth]=2000-01-15' \
  -F 'personal_info[residence_country]=Saudi Arabia' \
  -F 'personal_info[language]=Arabic' \
  -F 'personal_info[is_studied_in_saudi]=true' \
  -F 'personal_info[tahseeli_percentage]=85.5' \
  -F 'personal_info[qudorat_percentage]=78.2' \
  -F 'academic_info[qualifications][0][qualification_type]=high_school' \
  -F 'academic_info[qualifications][0][institute_name]=Test School' \
  -F 'academic_info[qualifications][0][year_of_graduation]=2019' \
  -F 'academic_info[qualifications][0][cgpa]=98.5' \
  -F 'academic_info[qualifications][0][cgpa_out_of]=99.99' \
  -F 'academic_info[qualifications][0][language_of_study]=Arabic' \
  -F 'academic_info[qualifications][0][specialization]=Science' \
  -F 'program_details[scholarship_ids][0]=1' \
  -F 'program_details[specialization_1]=Computer Science' \
  -F 'program_details[university_name]=Test University' \
  -F 'program_details[country_name]=USA' \
  -F 'program_details[tuition_fee]=50000' \
  -F 'program_details[has_active_program]=true' \
  -F 'program_details[terms_and_condition]=true' \
  -F 'personal_image=@test_files/personal_image.jpg'
```

---

## 📋 File Upload Checklist

-   [ ] Passport copy uploaded successfully
-   [ ] Personal image uploaded successfully
-   [ ] Secondary school certificate uploaded successfully
-   [ ] Secondary school transcript uploaded successfully
-   [ ] Volunteering certificate uploaded successfully
-   [ ] Offer letter uploaded successfully
-   [ ] Qualification documents uploaded successfully
-   [ ] Files are stored in S3 (if configured)
-   [ ] File paths are returned in response
-   [ ] File validation works (size, type, etc.)

---

## 🚨 Common File Upload Issues

1. **File Not Found**: Ensure test files exist in `test_files/` directory
2. **Permission Denied**: Check file permissions
3. **File Too Large**: Check file size limits in validation
4. **Invalid File Type**: Ensure file extensions match allowed types
5. **S3 Configuration**: Check if S3 is properly configured for file storage

---

## 📊 Expected File Upload Response

```json
{
    "message": "Application submitted successfully",
    "application_id": 1,
    "application": {
        "application_id": 1,
        "applicant_id": 1,
        "passport_copy": "applications/1/passport_copy.pdf",
        "personal_image": "applications/1/personal_image.jpg",
        "secondary_school_certificate": "applications/1/secondary_school_certificate.pdf",
        "secondary_school_transcript": "applications/1/secondary_school_transcript.pdf",
        "volunteering_certificate": "applications/1/volunteering_certificate.pdf",
        "offer_letter": "applications/1/offer_letter.pdf",
        "applicant": {
            "applicant_id": 1,
            "en_name": "Ahmed Mohamed",
            "tahseeli_percentage": 85.5,
            "qudorat_percentage": 78.2,
            "qualifications": [
                {
                    "qualification_id": 1,
                    "qualification_type": "high_school",
                    "institute_name": "Al Nahda School",
                    "document_file": "applications/1/qualifications/qualification_doc1.pdf"
                }
            ]
        }
    }
}
```

---

## 🎯 Quick Test Commands

```bash
# Test with minimal data + files
curl -X POST http://127.0.0.1:8000/api/v1/applications/submit-complete \
  -H "Authorization: Bearer $TOKEN" \
  -F 'personal_info[en_name]=Test User' \
  -F 'personal_info[nationality]=Saudi' \
  -F 'personal_info[gender]=male' \
  -F 'personal_info[phone]=+966501234567' \
  -F 'personal_info[passport_number]=TEST123456' \
  -F 'personal_info[date_of_birth]=2000-01-15' \
  -F 'personal_info[residence_country]=Saudi Arabia' \
  -F 'personal_info[language]=Arabic' \
  -F 'personal_info[is_studied_in_saudi]=true' \
  -F 'personal_info[tahseeli_percentage]=85.5' \
  -F 'personal_info[qudorat_percentage]=78.2' \
  -F 'academic_info[qualifications][0][qualification_type]=high_school' \
  -F 'academic_info[qualifications][0][institute_name]=Test School' \
  -F 'academic_info[qualifications][0][year_of_graduation]=2019' \
  -F 'academic_info[qualifications][0][cgpa]=98.5' \
  -F 'academic_info[qualifications][0][cgpa_out_of]=99.99' \
  -F 'academic_info[qualifications][0][language_of_study]=Arabic' \
  -F 'academic_info[qualifications][0][specialization]=Science' \
  -F 'program_details[scholarship_ids][0]=1' \
  -F 'program_details[specialization_1]=Computer Science' \
  -F 'program_details[university_name]=Test University' \
  -F 'program_details[country_name]=USA' \
  -F 'program_details[tuition_fee]=50000' \
  -F 'program_details[has_active_program]=true' \
  -F 'program_details[terms_and_condition]=true' \
  -F 'passport_copy=@test_files/passport_copy.pdf' \
  -F 'personal_image=@test_files/personal_image.jpg'
```
