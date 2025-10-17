# Complete API Endpoints Documentation

## Base URL

```
http://localhost:8000/api/v1
```

## Authentication

All endpoints require authentication via Laravel Sanctum. Include the Bearer token in the Authorization header:

```
Authorization: Bearer {your_token}
```

---

## 1. APPLICANT PROFILE ENDPOINTS

### 1.1 Complete Applicant Profile

**POST** `/applicant/complete-profile`

**Description:** Complete the applicant profile with personal information and file uploads.

**Request Body (multipart/form-data):**

```json
{
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
  "qudorat_percentage": 78.2,
  "passport_copy": [FILE],
  "personal_image": [FILE],
  "secondary_school_certificate": [FILE],
  "secondary_school_transcript": [FILE],
  "volunteering_certificate": [FILE],
  "tahsili_file": [FILE],
  "qudorat_file": [FILE]
}
```

**Response (201 Created):**

```json
{
    "success": true,
    "message": "Applicant profile completed successfully",
    "data": {
        "applicant_id": 1,
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
        "qudorat_percentage": 78.2,
        "passport_copy_img": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/passport/1760703536_Ahmed_Mohamed_Ali_fake_passport.pdf",
        "personal_image": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/personal-images/1760703536_Ahmed_Mohamed_Ali_fake_personal_image.jpg",
        "secondary_school_certificate_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/certificates/1760703536_Ahmed_Mohamed_Ali_fake_certificate.pdf",
        "secondary_school_transcript_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/transcripts/1760703536_Ahmed_Mohamed_Ali_fake_transcript.pdf",
        "volunteering_certificate_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/volunteering/1760703536_Ahmed_Mohamed_Ali_fake_volunteering.pdf",
        "tahsili_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/tahsili/1760703536_Ahmed_Mohamed_Ali_fake_tahsili.pdf",
        "qudorat_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/qudorat/1760703536_Ahmed_Mohamed_Ali_fake_qudorat.pdf",
        "user_id": 1,
        "created_at": "2025-10-17T12:18:58.000000Z",
        "updated_at": "2025-10-17T12:18:58.000000Z"
    }
}
```

### 1.2 Get Applicant Profile

**GET** `/applicant/profile`

**Response (200 OK):**

```json
{
    "success": true,
    "data": {
        "applicant_id": 1,
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
        "qudorat_percentage": 78.2,
        "passport_copy_img": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/passport/1760703536_Ahmed_Mohamed_Ali_fake_passport.pdf",
        "personal_image": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/personal-images/1760703536_Ahmed_Mohamed_Ali_fake_personal_image.jpg",
        "secondary_school_certificate_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/certificates/1760703536_Ahmed_Mohamed_Ali_fake_certificate.pdf",
        "secondary_school_transcript_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/transcripts/1760703536_Ahmed_Mohamed_Ali_fake_transcript.pdf",
        "volunteering_certificate_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/volunteering/1760703536_Ahmed_Mohamed_Ali_fake_volunteering.pdf",
        "tahsili_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/tahsili/1760703536_Ahmed_Mohamed_Ali_fake_tahsili.pdf",
        "qudorat_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/qudorat/1760703536_Ahmed_Mohamed_Ali_fake_qudorat.pdf",
        "user_id": 1,
        "created_at": "2025-10-17T12:18:58.000000Z",
        "updated_at": "2025-10-17T12:18:58.000000Z",
        "qualifications": [
            {
                "qualification_id": 1,
                "qualification_type": "high_school",
                "institute_name": "Al-Nahda High School",
                "year_of_graduation": "2019",
                "cgpa": "95.50",
                "cgpa_out_of": "99.99",
                "language_of_study": "Arabic",
                "specialization": "Science",
                "research_title": null,
                "document_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/qualifications/1760702704_Ahmed_Mohamed_Ali_fake_qualification.pdf",
                "applicant_id": 1,
                "created_at": "2025-10-17T12:05:06.000000Z",
                "updated_at": "2025-10-17T12:05:06.000000Z"
            }
        ]
    }
}
```

### 1.3 Update Applicant Profile

**PUT** `/applicant/profile`

**Request Body (multipart/form-data):**

```json
{
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
  "qudorat_percentage": 78.2,
  "passport_copy": [FILE],
  "personal_image": [FILE],
  "secondary_school_certificate": [FILE],
  "secondary_school_transcript": [FILE],
  "volunteering_certificate": [FILE],
  "tahsili_file": [FILE],
  "qudorat_file": [FILE]
}
```

**Response (200 OK):**

```json
{
    "success": true,
    "message": "Applicant profile updated successfully",
    "data": {
        "applicant_id": 1,
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
        "qudorat_percentage": 78.2,
        "passport_copy_img": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/passport/1760703536_Ahmed_Mohamed_Ali_fake_passport.pdf",
        "personal_image": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/personal-images/1760703536_Ahmed_Mohamed_Ali_fake_personal_image.jpg",
        "secondary_school_certificate_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/certificates/1760703536_Ahmed_Mohamed_Ali_fake_certificate.pdf",
        "secondary_school_transcript_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/transcripts/1760703536_Ahmed_Mohamed_Ali_fake_transcript.pdf",
        "volunteering_certificate_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/volunteering/1760703536_Ahmed_Mohamed_Ali_fake_volunteering.pdf",
        "tahsili_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/tahsili/1760703536_Ahmed_Mohamed_Ali_fake_tahsili.pdf",
        "qudorat_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/qudorat/1760703536_Ahmed_Mohamed_Ali_fake_qudorat.pdf",
        "user_id": 1,
        "created_at": "2025-10-17T12:18:58.000000Z",
        "updated_at": "2025-10-17T12:18:58.000000Z"
    }
}
```

---

## 2. QUALIFICATION ENDPOINTS

### 2.1 Add Qualification

**POST** `/applicant/qualifications`

**Request Body (multipart/form-data):**

```json
{
  "qualification_type": "bachelor",
  "institute_name": "King Saud University",
  "year_of_graduation": "2023",
  "cgpa": "3.80",
  "cgpa_out_of": "4.00",
  "language_of_study": "Arabic",
  "specialization": "Computer Science",
  "research_title": "Machine Learning Applications",
  "document_file": [FILE]
}
```

**Response (201 Created):**

```json
{
    "success": true,
    "message": "Qualification added successfully",
    "data": {
        "qualification_id": 2,
        "qualification_type": "bachelor",
        "institute_name": "King Saud University",
        "year_of_graduation": "2023",
        "cgpa": "3.80",
        "cgpa_out_of": "4.00",
        "language_of_study": "Arabic",
        "specialization": "Computer Science",
        "research_title": "Machine Learning Applications",
        "document_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/qualifications/1760702704_Ahmed_Mohamed_Ali_fake_qualification.pdf",
        "applicant_id": 1,
        "created_at": "2025-10-17T12:05:06.000000Z",
        "updated_at": "2025-10-17T12:05:06.000000Z"
    }
}
```

### 2.2 Update Qualification

**PUT** `/applicant/qualifications/{qualificationId}`

**Request Body (multipart/form-data):**

```json
{
  "qualification_type": "bachelor",
  "institute_name": "King Saud University",
  "year_of_graduation": "2023",
  "cgpa": "3.85",
  "cgpa_out_of": "4.00",
  "language_of_study": "Arabic",
  "specialization": "Computer Science",
  "research_title": "Advanced Machine Learning Applications",
  "document_file": [FILE]
}
```

**Response (200 OK):**

```json
{
    "success": true,
    "message": "Qualification updated successfully",
    "data": {
        "qualification_id": 2,
        "qualification_type": "bachelor",
        "institute_name": "King Saud University",
        "year_of_graduation": "2023",
        "cgpa": "3.85",
        "cgpa_out_of": "4.00",
        "language_of_study": "Arabic",
        "specialization": "Computer Science",
        "research_title": "Advanced Machine Learning Applications",
        "document_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/qualifications/1760702704_Ahmed_Mohamed_Ali_fake_qualification.pdf",
        "applicant_id": 1,
        "created_at": "2025-10-17T12:05:06.000000Z",
        "updated_at": "2025-10-17T12:18:58.000000Z"
    }
}
```

### 2.3 Delete Qualification

**DELETE** `/applicant/qualifications/{qualificationId}`

**Response (200 OK):**

```json
{
    "success": true,
    "message": "Qualification deleted successfully"
}
```

---

## 3. APPLICATION ENDPOINTS

### 3.1 Create Application

**POST** `/applications`

**Request Body (JSON):**

```json
{
  "scholarship_id": 1,
  "specialization_1": "Computer Science",
  "specialization_2": "Data Science",
  "specialization_3": "Artificial Intelligence",
  "university_name": "King Saud University",
  "country_name": "Saudi Arabia",
  "tuition_fee": 50000,
  "has_active_program": true,
  "current_semester_number": 1,
  "cgpa": 3.80,
  "cgpa_out_of": 4.00,
  "terms_and_condition": true,
  "offer_letter": [FILE]
}
```

**Response (201 Created):**

```json
{
    "success": true,
    "message": "Application created successfully",
    "data": {
        "application_id": 1,
        "scholarship_id": 1,
        "specialization_1": "Computer Science",
        "specialization_2": "Data Science",
        "specialization_3": "Artificial Intelligence",
        "university_name": "King Saud University",
        "country_name": "Saudi Arabia",
        "tuition_fee": "50000.00",
        "has_active_program": true,
        "current_semester_number": 1,
        "cgpa": "3.80",
        "cgpa_out_of": "4.00",
        "terms_and_condition": true,
        "offer_letter_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/offer-letters/1760702704_Ahmed_Mohamed_Ali_fake_offer_letter.pdf",
        "applicant_id": 1,
        "created_at": "2025-10-17T12:05:06.000000Z",
        "updated_at": "2025-10-17T12:05:06.000000Z",
        "scholarship": {
            "scholarship_id": 1,
            "title": "Computer Science Scholarship",
            "description": "Full scholarship for Computer Science students",
            "amount": 50000,
            "is_active": true,
            "is_hided": false,
            "closing_date": "2025-12-31T23:59:59.000000Z"
        },
        "current_status": {
            "applicationStatus_id": 1,
            "application_id": 1,
            "status_name": "submitted",
            "date": "2025-10-17T12:05:06.000000Z",
            "comment": "Application submitted successfully",
            "created_at": "2025-10-17T12:05:06.000000Z",
            "updated_at": "2025-10-17T12:05:06.000000Z"
        }
    }
}
```

### 3.2 Submit Complete Application

**POST** `/applications/submit-complete`

**Request Body (multipart/form-data):**

```json
{
  "personal_info": {
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
  },
  "academic_info": {
    "qualifications": [
      {
        "qualification_type": "high_school",
        "institute_name": "Al Nahda School",
        "year_of_graduation": 2019,
        "cgpa": 98.5,
        "cgpa_out_of": 99.99,
        "language_of_study": "Arabic",
        "specialization": "Science",
        "document_file": [FILE]
      },
      {
        "qualification_type": "bachelor",
        "institute_name": "King Saud University",
        "year_of_graduation": 2023,
        "cgpa": 3.8,
        "cgpa_out_of": 4.0,
        "language_of_study": "Arabic",
        "specialization": "Computer Science",
        "document_file": [FILE]
      }
    ]
  },
  "program_details": {
    "scholarship_id": 1,
    "specialization_1": "Computer Science",
    "specialization_2": "Data Science",
    "specialization_3": "AI",
    "university_name": "Stanford University",
    "country_name": "USA",
    "tuition_fee": 50000,
    "has_active_program": true,
    "current_semester_number": 2,
    "cgpa": 3.75,
    "cgpa_out_of": 4.0,
    "terms_and_condition": true
  },
  "passport_copy": [FILE],
  "personal_image": [FILE],
  "secondary_school_certificate": [FILE],
  "secondary_school_transcript": [FILE],
  "volunteering_certificate": [FILE],
  "offer_letter": [FILE]
}
```

**Response (201 Created):**

```json
{
    "success": true,
    "message": "Complete application submitted successfully",
    "data": {
        "application_id": 1,
        "scholarship_id": 1,
        "specialization_1": "Computer Science",
        "specialization_2": "Data Science",
        "specialization_3": "AI",
        "university_name": "Stanford University",
        "country_name": "USA",
        "tuition_fee": "50000.00",
        "has_active_program": true,
        "current_semester_number": 2,
        "cgpa": "3.75",
        "cgpa_out_of": "4.00",
        "terms_and_condition": true,
        "offer_letter_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/offer-letters/1760703536_Ahmed_Mohamed_Ali_fake_offer_letter.pdf",
        "applicant_id": 1,
        "created_at": "2025-10-17T12:18:58.000000Z",
        "updated_at": "2025-10-17T12:18:58.000000Z",
        "scholarship": {
            "scholarship_id": 1,
            "title": "Computer Science Scholarship",
            "description": "Full scholarship for Computer Science students",
            "amount": 50000,
            "is_active": true,
            "is_hided": false,
            "closing_date": "2025-12-31T23:59:59.000000Z"
        },
        "current_status": {
            "applicationStatus_id": 1,
            "application_id": 1,
            "status_name": "submitted",
            "date": "2025-10-17T12:18:58.000000Z",
            "comment": "Application submitted successfully",
            "created_at": "2025-10-17T12:18:58.000000Z",
            "updated_at": "2025-10-17T12:18:58.000000Z"
        },
        "applicant": {
            "applicant_id": 1,
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
            "qudorat_percentage": 78.2,
            "passport_copy_img": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/passport/1760703536_Ahmed_Mohamed_Ali_fake_passport.pdf",
            "personal_image": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/personal-images/1760703536_Ahmed_Mohamed_Ali_fake_personal_image.jpg",
            "secondary_school_certificate_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/certificates/1760703536_Ahmed_Mohamed_Ali_fake_certificate.pdf",
            "secondary_school_transcript_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/transcripts/1760703536_Ahmed_Mohamed_Ali_fake_transcript.pdf",
            "volunteering_certificate_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/volunteering/1760703536_Ahmed_Mohamed_Ali_fake_volunteering.pdf",
            "tahsili_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/tahsili/1760703536_Ahmed_Mohamed_Ali_fake_tahsili.pdf",
            "qudorat_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/qudorat/1760703536_Ahmed_Mohamed_Ali_fake_qudorat.pdf",
            "user_id": 1,
            "created_at": "2025-10-17T12:18:58.000000Z",
            "updated_at": "2025-10-17T12:18:58.000000Z",
            "qualifications": [
                {
                    "qualification_id": 1,
                    "qualification_type": "high_school",
                    "institute_name": "Al Nahda School",
                    "year_of_graduation": "2019",
                    "cgpa": "98.50",
                    "cgpa_out_of": "99.99",
                    "language_of_study": "Arabic",
                    "specialization": "Science",
                    "research_title": null,
                    "document_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/qualifications/1760703536_Ahmed_Mohamed_Ali_fake_qualification.pdf",
                    "applicant_id": 1,
                    "created_at": "2025-10-17T12:18:58.000000Z",
                    "updated_at": "2025-10-17T12:18:58.000000Z"
                },
                {
                    "qualification_id": 2,
                    "qualification_type": "bachelor",
                    "institute_name": "King Saud University",
                    "year_of_graduation": "2023",
                    "cgpa": "3.80",
                    "cgpa_out_of": "4.00",
                    "language_of_study": "Arabic",
                    "specialization": "Computer Science",
                    "research_title": null,
                    "document_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/qualifications/1760703536_Ahmed_Mohamed_Ali_fake_qualification.pdf",
                    "applicant_id": 1,
                    "created_at": "2025-10-17T12:18:58.000000Z",
                    "updated_at": "2025-10-17T12:18:58.000000Z"
                }
            ]
        }
    }
}
```

### 3.3 Get User's Applications

**GET** `/applications`

**Response (200 OK):**

```json
{
    "success": true,
    "data": [
        {
            "application_id": 1,
            "scholarship_id": 1,
            "specialization_1": "Computer Science",
            "specialization_2": "Data Science",
            "specialization_3": "AI",
            "university_name": "Stanford University",
            "country_name": "USA",
            "tuition_fee": "50000.00",
            "has_active_program": true,
            "current_semester_number": 2,
            "cgpa": "3.75",
            "cgpa_out_of": "4.00",
            "terms_and_condition": true,
            "offer_letter_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/offer-letters/1760703536_Ahmed_Mohamed_Ali_fake_offer_letter.pdf",
            "applicant_id": 1,
            "created_at": "2025-10-17T12:18:58.000000Z",
            "updated_at": "2025-10-17T12:18:58.000000Z",
            "scholarship": {
                "scholarship_id": 1,
                "title": "Computer Science Scholarship",
                "description": "Full scholarship for Computer Science students",
                "amount": 50000,
                "is_active": true,
                "is_hided": false,
                "closing_date": "2025-12-31T23:59:59.000000Z"
            },
            "current_status": {
                "applicationStatus_id": 1,
                "application_id": 1,
                "status_name": "submitted",
                "date": "2025-10-17T12:18:58.000000Z",
                "comment": "Application submitted successfully",
                "created_at": "2025-10-17T12:18:58.000000Z",
                "updated_at": "2025-10-17T12:18:58.000000Z"
            }
        }
    ],
    "meta": {
        "total": 1,
        "per_page": 15,
        "current_page": 1,
        "last_page": 1
    }
}
```

### 3.4 Get Specific Application

**GET** `/applications/{id}`

**Response (200 OK):**

```json
{
    "success": true,
    "data": {
        "application_id": 1,
        "scholarship_id": 1,
        "specialization_1": "Computer Science",
        "specialization_2": "Data Science",
        "specialization_3": "AI",
        "university_name": "Stanford University",
        "country_name": "USA",
        "tuition_fee": "50000.00",
        "has_active_program": true,
        "current_semester_number": 2,
        "cgpa": "3.75",
        "cgpa_out_of": "4.00",
        "terms_and_condition": true,
        "offer_letter_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/offer-letters/1760703536_Ahmed_Mohamed_Ali_fake_offer_letter.pdf",
        "applicant_id": 1,
        "created_at": "2025-10-17T12:18:58.000000Z",
        "updated_at": "2025-10-17T12:18:58.000000Z",
        "scholarship": {
            "scholarship_id": 1,
            "title": "Computer Science Scholarship",
            "description": "Full scholarship for Computer Science students",
            "amount": 50000,
            "is_active": true,
            "is_hided": false,
            "closing_date": "2025-12-31T23:59:59.000000Z"
        },
        "current_status": {
            "applicationStatus_id": 1,
            "application_id": 1,
            "status_name": "submitted",
            "date": "2025-10-17T12:18:58.000000Z",
            "comment": "Application submitted successfully",
            "created_at": "2025-10-17T12:18:58.000000Z",
            "updated_at": "2025-10-17T12:18:58.000000Z"
        },
        "applicant": {
            "applicant_id": 1,
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
            "qudorat_percentage": 78.2,
            "passport_copy_img": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/passport/1760703536_Ahmed_Mohamed_Ali_fake_passport.pdf",
            "personal_image": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/personal-images/1760703536_Ahmed_Mohamed_Ali_fake_personal_image.jpg",
            "secondary_school_certificate_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/certificates/1760703536_Ahmed_Mohamed_Ali_fake_certificate.pdf",
            "secondary_school_transcript_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/transcripts/1760703536_Ahmed_Mohamed_Ali_fake_transcript.pdf",
            "volunteering_certificate_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/volunteering/1760703536_Ahmed_Mohamed_Ali_fake_volunteering.pdf",
            "tahsili_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/tahsili/1760703536_Ahmed_Mohamed_Ali_fake_tahsili.pdf",
            "qudorat_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/qudorat/1760703536_Ahmed_Mohamed_Ali_fake_qudorat.pdf",
            "user_id": 1,
            "created_at": "2025-10-17T12:18:58.000000Z",
            "updated_at": "2025-10-17T12:18:58.000000Z",
            "qualifications": [
                {
                    "qualification_id": 1,
                    "qualification_type": "high_school",
                    "institute_name": "Al Nahda School",
                    "year_of_graduation": "2019",
                    "cgpa": "98.50",
                    "cgpa_out_of": "99.99",
                    "language_of_study": "Arabic",
                    "specialization": "Science",
                    "research_title": null,
                    "document_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/qualifications/1760703536_Ahmed_Mohamed_Ali_fake_qualification.pdf",
                    "applicant_id": 1,
                    "created_at": "2025-10-17T12:18:58.000000Z",
                    "updated_at": "2025-10-17T12:18:58.000000Z"
                }
            ]
        }
    }
}
```

### 3.5 Update Program Details

**PUT** `/applications/{id}/program-details`

**Request Body (multipart/form-data):**

```json
{
  "specialization_1": "Computer Science",
  "specialization_2": "Data Science",
  "specialization_3": "Machine Learning",
  "university_name": "MIT",
  "country_name": "USA",
  "tuition_fee": 60000,
  "has_active_program": true,
  "current_semester_number": 3,
  "cgpa": 3.85,
  "cgpa_out_of": 4.00,
  "terms_and_condition": true,
  "offer_letter": [FILE]
}
```

**Response (200 OK):**

```json
{
    "success": true,
    "message": "Program details updated successfully",
    "data": {
        "application_id": 1,
        "scholarship_id": 1,
        "specialization_1": "Computer Science",
        "specialization_2": "Data Science",
        "specialization_3": "Machine Learning",
        "university_name": "MIT",
        "country_name": "USA",
        "tuition_fee": "60000.00",
        "has_active_program": true,
        "current_semester_number": 3,
        "cgpa": "3.85",
        "cgpa_out_of": "4.00",
        "terms_and_condition": true,
        "offer_letter_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/offer-letters/1760703536_Ahmed_Mohamed_Ali_fake_offer_letter.pdf",
        "applicant_id": 1,
        "created_at": "2025-10-17T12:18:58.000000Z",
        "updated_at": "2025-10-17T12:25:30.000000Z"
    }
}
```

---

## 4. ADMIN ENDPOINTS (Requires Admin Role)

### 4.1 Get All Applications (Admin)

**GET** `/admin/applications`

**Response (200 OK):**

```json
{
    "success": true,
    "data": [
        {
            "application_id": 1,
            "scholarship_id": 1,
            "specialization_1": "Computer Science",
            "specialization_2": "Data Science",
            "specialization_3": "AI",
            "university_name": "Stanford University",
            "country_name": "USA",
            "tuition_fee": "50000.00",
            "has_active_program": true,
            "current_semester_number": 2,
            "cgpa": "3.75",
            "cgpa_out_of": "4.00",
            "terms_and_condition": true,
            "offer_letter_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/offer-letters/1760703536_Ahmed_Mohamed_Ali_fake_offer_letter.pdf",
            "applicant_id": 1,
            "created_at": "2025-10-17T12:18:58.000000Z",
            "updated_at": "2025-10-17T12:18:58.000000Z",
            "scholarship": {
                "scholarship_id": 1,
                "title": "Computer Science Scholarship",
                "description": "Full scholarship for Computer Science students",
                "amount": 50000,
                "is_active": true,
                "is_hided": false,
                "closing_date": "2025-12-31T23:59:59.000000Z"
            },
            "current_status": {
                "applicationStatus_id": 1,
                "application_id": 1,
                "status_name": "submitted",
                "date": "2025-10-17T12:18:58.000000Z",
                "comment": "Application submitted successfully",
                "created_at": "2025-10-17T12:18:58.000000Z",
                "updated_at": "2025-10-17T12:18:58.000000Z"
            },
            "applicant": {
                "applicant_id": 1,
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
                "qudorat_percentage": 78.2,
                "passport_copy_img": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/passport/1760703536_Ahmed_Mohamed_Ali_fake_passport.pdf",
                "personal_image": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/personal-images/1760703536_Ahmed_Mohamed_Ali_fake_personal_image.jpg",
                "secondary_school_certificate_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/certificates/1760703536_Ahmed_Mohamed_Ali_fake_certificate.pdf",
                "secondary_school_transcript_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/transcripts/1760703536_Ahmed_Mohamed_Ali_fake_transcript.pdf",
                "volunteering_certificate_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/volunteering/1760703536_Ahmed_Mohamed_Ali_fake_volunteering.pdf",
                "tahsili_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/tahsili/1760703536_Ahmed_Mohamed_Ali_fake_tahsili.pdf",
                "qudorat_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/qudorat/1760703536_Ahmed_Mohamed_Ali_fake_qudorat.pdf",
                "user_id": 1,
                "created_at": "2025-10-17T12:18:58.000000Z",
                "updated_at": "2025-10-17T12:18:58.000000Z",
                "qualifications": [
                    {
                        "qualification_id": 1,
                        "qualification_type": "high_school",
                        "institute_name": "Al Nahda School",
                        "year_of_graduation": "2019",
                        "cgpa": "98.50",
                        "cgpa_out_of": "99.99",
                        "language_of_study": "Arabic",
                        "specialization": "Science",
                        "research_title": null,
                        "document_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/qualifications/1760703536_Ahmed_Mohamed_Ali_fake_qualification.pdf",
                        "applicant_id": 1,
                        "created_at": "2025-10-17T12:18:58.000000Z",
                        "updated_at": "2025-10-17T12:18:58.000000Z"
                    }
                ]
            }
        }
    ],
    "meta": {
        "total": 1,
        "per_page": 15,
        "current_page": 1,
        "last_page": 1
    }
}
```

### 4.2 Update Application Status (Admin)

**PUT** `/admin/applications/{id}/status`

**Request Body (JSON):**

```json
{
    "status": "under_review",
    "comment": "Application is under review by the committee"
}
```

**Response (200 OK):**

```json
{
    "success": true,
    "message": "Application status updated successfully",
    "data": {
        "applicationStatus_id": 2,
        "application_id": 1,
        "status_name": "under_review",
        "date": "2025-10-17T12:30:00.000000Z",
        "comment": "Application is under review by the committee",
        "created_at": "2025-10-17T12:30:00.000000Z",
        "updated_at": "2025-10-17T12:30:00.000000Z"
    }
}
```

### 4.3 Get Statistics (Admin)

**GET** `/admin/statistics`

**Response (200 OK):**

```json
{
    "success": true,
    "data": {
        "total_applications": 25,
        "applications_by_status": {
            "submitted": 10,
            "under_review": 8,
            "approved": 5,
            "rejected": 2
        },
        "applications_by_scholarship": {
            "1": 15,
            "2": 7,
            "3": 3
        },
        "applications_by_month": {
            "2025-10": 25
        }
    }
}
```

### 4.4 Delete Application (Admin)

**DELETE** `/admin/applications/{id}`

**Response (200 OK):**

```json
{
    "success": true,
    "message": "Application deleted successfully"
}
```

---

## 5. APPLICATION STATUS ENUMS

### Available Status Values:

-   `submitted` - Application has been submitted
-   `under_review` - Application is being reviewed
-   `approved` - Application has been approved
-   `rejected` - Application has been rejected
-   `enrolled` - Student has been enrolled
-   `graduated` - Student has graduated
-   `withdrawn` - Application has been withdrawn

---

## 6. ERROR RESPONSES

### 400 Bad Request

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

### 401 Unauthorized

```json
{
    "success": false,
    "message": "Unauthenticated."
}
```

### 403 Forbidden

```json
{
    "success": false,
    "message": "Access denied. Admin role required."
}
```

### 404 Not Found

```json
{
    "success": false,
    "message": "Application not found."
}
```

### 422 Unprocessable Entity

```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "scholarship_id": ["The selected scholarship id is invalid."]
    }
}
```

### 500 Internal Server Error

```json
{
    "success": false,
    "message": "Server Error"
}
```

---

## 7. TESTING EXAMPLES

### Using cURL for File Upload:

```bash
curl -X POST "http://localhost:8000/api/v1/applications/submit-complete" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "personal_info[ar_name]=أحمد محمد علي" \
  -F "personal_info[en_name]=Ahmed Mohamed Ali" \
  -F "personal_info[nationality]=Saudi" \
  -F "personal_info[gender]=male" \
  -F "personal_info[place_of_birth]=Riyadh" \
  -F "personal_info[phone]=+966501234567" \
  -F "personal_info[passport_number]=A12345678" \
  -F "personal_info[date_of_birth]=2000-01-15" \
  -F "personal_info[parent_contact_name]=Mohamed Ahmed" \
  -F "personal_info[parent_contact_phone]=+966501234568" \
  -F "personal_info[residence_country]=Saudi Arabia" \
  -F "personal_info[language]=Arabic" \
  -F "personal_info[is_studied_in_saudi]=true" \
  -F "personal_info[tahseeli_percentage]=85.5" \
  -F "personal_info[qudorat_percentage]=78.2" \
  -F "academic_info[qualifications][0][qualification_type]=high_school" \
  -F "academic_info[qualifications][0][institute_name]=Al Nahda School" \
  -F "academic_info[qualifications][0][year_of_graduation]=2019" \
  -F "academic_info[qualifications][0][cgpa]=98.5" \
  -F "academic_info[qualifications][0][cgpa_out_of]=99.99" \
  -F "academic_info[qualifications][0][language_of_study]=Arabic" \
  -F "academic_info[qualifications][0][specialization]=Science" \
  -F "academic_info[qualifications][0][document_file]=@/path/to/document.pdf" \
  -F "program_details[scholarship_id]=1" \
  -F "program_details[specialization_1]=Computer Science" \
  -F "program_details[specialization_2]=Data Science" \
  -F "program_details[specialization_3]=AI" \
  -F "program_details[university_name]=Stanford University" \
  -F "program_details[country_name]=USA" \
  -F "program_details[tuition_fee]=50000" \
  -F "program_details[has_active_program]=true" \
  -F "program_details[current_semester_number]=2" \
  -F "program_details[cgpa]=3.75" \
  -F "program_details[cgpa_out_of]=4.0" \
  -F "program_details[terms_and_condition]=true" \
  -F "passport_copy=@/path/to/passport.pdf" \
  -F "personal_image=@/path/to/image.jpg" \
  -F "secondary_school_certificate=@/path/to/certificate.pdf" \
  -F "secondary_school_transcript=@/path/to/transcript.pdf" \
  -F "volunteering_certificate=@/path/to/volunteering.pdf" \
  -F "offer_letter=@/path/to/offer_letter.pdf"
```

### Using PowerShell for File Upload:

```powershell
$headers = @{
    "Authorization" = "Bearer YOUR_TOKEN"
    "Accept" = "application/json"
}

$form = @{
    "personal_info[ar_name]" = "أحمد محمد علي"
    "personal_info[en_name]" = "Ahmed Mohamed Ali"
    "personal_info[nationality]" = "Saudi"
    "personal_info[gender]" = "male"
    "personal_info[place_of_birth]" = "Riyadh"
    "personal_info[phone]" = "+966501234567"
    "personal_info[passport_number]" = "A12345678"
    "personal_info[date_of_birth]" = "2000-01-15"
    "personal_info[parent_contact_name]" = "Mohamed Ahmed"
    "personal_info[parent_contact_phone]" = "+966501234568"
    "personal_info[residence_country]" = "Saudi Arabia"
    "personal_info[language]" = "Arabic"
    "personal_info[is_studied_in_saudi]" = "true"
    "personal_info[tahseeli_percentage]" = "85.5"
    "personal_info[qudorat_percentage]" = "78.2"
    "academic_info[qualifications][0][qualification_type]" = "high_school"
    "academic_info[qualifications][0][institute_name]" = "Al Nahda School"
    "academic_info[qualifications][0][year_of_graduation]" = "2019"
    "academic_info[qualifications][0][cgpa]" = "98.5"
    "academic_info[qualifications][0][cgpa_out_of]" = "99.99"
    "academic_info[qualifications][0][language_of_study]" = "Arabic"
    "academic_info[qualifications][0][specialization]" = "Science"
    "academic_info[qualifications][0][document_file]" = Get-Item "C:\path\to\document.pdf"
    "program_details[scholarship_id]" = "1"
    "program_details[specialization_1]" = "Computer Science"
    "program_details[specialization_2]" = "Data Science"
    "program_details[specialization_3]" = "AI"
    "program_details[university_name]" = "Stanford University"
    "program_details[country_name]" = "USA"
    "program_details[tuition_fee]" = "50000"
    "program_details[has_active_program]" = "true"
    "program_details[current_semester_number]" = "2"
    "program_details[cgpa]" = "3.75"
    "program_details[cgpa_out_of]" = "4.0"
    "program_details[terms_and_condition]" = "true"
    "passport_copy" = Get-Item "C:\path\to\passport.pdf"
    "personal_image" = Get-Item "C:\path\to\image.jpg"
    "secondary_school_certificate" = Get-Item "C:\path\to\certificate.pdf"
    "secondary_school_transcript" = Get-Item "C:\path\to\transcript.pdf"
    "volunteering_certificate" = Get-Item "C:\path\to\volunteering.pdf"
    "offer_letter" = Get-Item "C:\path\to\offer_letter.pdf"
}

Invoke-RestMethod -Uri "http://localhost:8000/api/v1/applications/submit-complete" -Method Post -Headers $headers -Form $form
```

---

## 8. NOTES

1. **File Uploads**: All file uploads are stored in AWS S3 with organized folder structure:

    - Applicant documents: `applicant-documents/`
    - Application documents: `application-documents/`

2. **Authentication**: All endpoints require valid Sanctum token except for public scholarship endpoints.

3. **File Size Limits**: Default Laravel file upload limits apply (typically 2MB for images, 10MB for documents).

4. **File Types**: Supported file types include PDF, JPG, JPEG, PNG for documents and images.

5. **URLs**: All file URLs returned are full S3 URLs with sanitized filenames (spaces replaced with underscores).

6. **Validation**: All endpoints include comprehensive validation for required fields and data types.

7. **Error Handling**: Consistent error response format across all endpoints.

8. **Pagination**: List endpoints support pagination with `page` and `per_page` parameters.

9. **Filtering**: Admin endpoints support filtering by status, scholarship, date range, etc.

10. **Role-Based Access**: Admin endpoints require `role:admin` middleware.
