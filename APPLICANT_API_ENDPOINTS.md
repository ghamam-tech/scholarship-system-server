# Applicant, Application & Qualification API Endpoints

## Base URL

```
http://localhost:8000/api/v1
```

## Authentication

All endpoints require `Authorization: Bearer {token}` header.

---

## 1. APPLICANT PROFILE ENDPOINTS

### 1.1 Complete Applicant Profile

**POST** `/applicant/complete-profile`

**Request (multipart/form-data):**

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
  "volunteering_certificate": [FILE]
}
```

**Response:**

```json
{
    "message": "Profile completed successfully",
    "applicant": {
        "applicant_id": 11,
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
        "is_studied_in_saudi": 1,
        "tahseeli_percentage": 85.5,
        "qudorat_percentage": 78.2,
        "passport_copy_img": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/passport/1760704070_Ahmed_Mohamed_Ali_passport.pdf",
        "personal_image": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/personal-images/1760704070_Ahmed_Mohamed_Ali_image.jpg",
        "tahsili_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/tahsili/1760704070_Ahmed_Mohamed_Ali_certificate.pdf",
        "qudorat_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/qudorat/1760704070_Ahmed_Mohamed_Ali_transcript.pdf",
        "volunteering_certificate_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/volunteering/1760704070_Ahmed_Mohamed_Ali_volunteering.pdf",
        "user_id": 38,
        "created_at": "2025-10-17T07:10:52.000000Z",
        "updated_at": "2025-10-17T12:18:58.000000Z"
    }
}
```

---

## 2. QUALIFICATION ENDPOINTS

### 2.1 Get Applicant's Qualifications

**GET** `/qualifications`

**Response:**

```json
[
    {
        "qualification_id": 18,
        "qualification_type": "high_school",
        "institute_name": "Al-Nahda High School",
        "year_of_graduation": "2019",
        "cgpa": "95.50",
        "cgpa_out_of": "99.99",
        "language_of_study": "Arabic",
        "specialization": "Science",
        "research_title": null,
        "document_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/qualifications/1760704070_Ahmed_Mohamed_Ali_certificate.pdf",
        "applicant_id": 11,
        "created_at": "2025-10-17T08:56:15.000000Z",
        "updated_at": "2025-10-17T08:56:15.000000Z"
    },
    {
        "qualification_id": 19,
        "qualification_type": "bachelor",
        "institute_name": "King Saud University",
        "year_of_graduation": "2023",
        "cgpa": "3.80",
        "cgpa_out_of": "4.00",
        "language_of_study": "Arabic",
        "specialization": "Computer Science",
        "research_title": null,
        "document_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/qualifications/1760704070_Ahmed_Mohamed_Ali_degree.pdf",
        "applicant_id": 11,
        "created_at": "2025-10-17T12:05:06.000000Z",
        "updated_at": "2025-10-17T12:05:06.000000Z"
    }
]
```

### 2.2 Add New Qualification

**POST** `/qualifications`

**Request (multipart/form-data):**

```json
{
  "qualification_type": "bachelor",
  "institute_name": "King Saud University",
  "year_of_graduation": 2023,
  "cgpa": 3.8,
  "cgpa_out_of": 4.0,
  "language_of_study": "Arabic",
  "specialization": "Computer Science",
  "research_title": "Machine Learning Applications",
  "document_file": [FILE]
}
```

**Response:**

```json
{
    "message": "Qualification added successfully",
    "qualification": {
        "qualification_id": 20,
        "qualification_type": "bachelor",
        "institute_name": "King Saud University",
        "year_of_graduation": "2023",
        "cgpa": "3.80",
        "cgpa_out_of": "4.00",
        "language_of_study": "Arabic",
        "specialization": "Computer Science",
        "research_title": "Machine Learning Applications",
        "document_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/qualifications/1760704070_Ahmed_Mohamed_Ali_degree.pdf",
        "applicant_id": 11,
        "created_at": "2025-10-17T12:05:06.000000Z",
        "updated_at": "2025-10-17T12:05:06.000000Z"
    }
}
```

### 2.3 Update Qualification

**PUT** `/qualifications/{id}`

**Request (multipart/form-data):**

```json
{
  "qualification_type": "master",
  "institute_name": "MIT",
  "year_of_graduation": 2025,
  "cgpa": 3.9,
  "cgpa_out_of": 4.0,
  "language_of_study": "English",
  "specialization": "Artificial Intelligence",
  "research_title": "Deep Learning in Healthcare",
  "document_file": [FILE]
}
```

**Response:**

```json
{
    "message": "Qualification updated successfully",
    "qualification": {
        "qualification_id": 20,
        "qualification_type": "master",
        "institute_name": "MIT",
        "year_of_graduation": "2025",
        "cgpa": "3.90",
        "cgpa_out_of": "4.00",
        "language_of_study": "English",
        "specialization": "Artificial Intelligence",
        "research_title": "Deep Learning in Healthcare",
        "document_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/qualifications/1760704070_Ahmed_Mohamed_Ali_master_degree.pdf",
        "applicant_id": 11,
        "created_at": "2025-10-17T12:05:06.000000Z",
        "updated_at": "2025-10-17T12:20:15.000000Z"
    }
}
```

### 2.4 Delete Qualification

**DELETE** `/qualifications/{id}`

**Response:**

```json
{
    "message": "Qualification deleted successfully"
}
```

---

## 3. APPLICATION ENDPOINTS

### 3.1 Submit Complete Application

**POST** `/applications/submit-complete`

**Request (multipart/form-data):**

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
        "institute_name": "Al-Nahda School",
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
    "scholarship_ids": [101, 205, 308],
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

**Response:**

```json
{
    "message": "Application submitted successfully",
    "application_id": 25,
    "application": {
        "application_id": 25,
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
        "offer_letter_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/offer-letters/1760704070_Ahmed_Mohamed_Ali_offer_letter.pdf",
        "applicant_id": 11,
        "scholarship_id_1": 101,
        "scholarship_id_2": 205,
        "scholarship_id_3": 308,
        "created_at": "2025-10-17T12:18:58.000000Z",
        "updated_at": "2025-10-17T12:18:58.000000Z",
        "current_status": {
            "applicationStatus_id": 12,
            "application_id": 25,
            "status_name": "enrolled",
            "date": "2025-10-17T12:18:58.000000Z",
            "comment": "Complete application submitted",
            "created_at": "2025-10-17T12:18:58.000000Z",
            "updated_at": "2025-10-17T12:18:58.000000Z"
        },
        "scholarship1": {
            "scholarship_id": 101,
            "title": "Computer Science Scholarship",
            "description": "Full scholarship for CS students",
            "amount": "50000.00",
            "currency": "USD",
            "opening_date": "2025-01-01T00:00:00.000000Z",
            "closing_date": "2025-12-31T23:59:59.000000Z",
            "is_active": true,
            "is_hided": false
        },
        "scholarship2": {
            "scholarship_id": 205,
            "title": "Data Science Scholarship",
            "description": "Scholarship for Data Science programs",
            "amount": "45000.00",
            "currency": "USD",
            "opening_date": "2025-01-01T00:00:00.000000Z",
            "closing_date": "2025-12-31T23:59:59.000000Z",
            "is_active": true,
            "is_hided": false
        },
        "scholarship3": {
            "scholarship_id": 308,
            "title": "AI Research Scholarship",
            "description": "Research scholarship for AI students",
            "amount": "60000.00",
            "currency": "USD",
            "opening_date": "2025-01-01T00:00:00.000000Z",
            "closing_date": "2025-12-31T23:59:59.000000Z",
            "is_active": true,
            "is_hided": false
        },
        "applicant": {
            "applicant_id": 11,
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
            "is_studied_in_saudi": 1,
            "tahseeli_percentage": 85.5,
            "qudorat_percentage": 78.2,
            "passport_copy_img": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/passport/1760704070_Ahmed_Mohamed_Ali_passport.pdf",
            "personal_image": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/personal-images/1760704070_Ahmed_Mohamed_Ali_image.jpg",
            "volunteering_certificate_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/volunteering/1760704070_Ahmed_Mohamed_Ali_volunteering.pdf",
            "tahsili_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/tahsili/1760704070_Ahmed_Mohamed_Ali_certificate.pdf",
            "qudorat_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/qudorat/1760704070_Ahmed_Mohamed_Ali_transcript.pdf",
            "user_id": 38,
            "created_at": "2025-10-17T07:10:52.000000Z",
            "updated_at": "2025-10-17T12:18:58.000000Z",
            "qualifications": [
                {
                    "qualification_id": 21,
                    "qualification_type": "high_school",
                    "institute_name": "Al-Nahda School",
                    "year_of_graduation": "2019",
                    "cgpa": "98.50",
                    "cgpa_out_of": "99.99",
                    "language_of_study": "Arabic",
                    "specialization": "Science",
                    "research_title": null,
                    "document_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/qualifications/1760704070_Ahmed_Mohamed_Ali_certificate.pdf",
                    "applicant_id": 11,
                    "created_at": "2025-10-17T12:18:58.000000Z",
                    "updated_at": "2025-10-17T12:18:58.000000Z"
                },
                {
                    "qualification_id": 22,
                    "qualification_type": "bachelor",
                    "institute_name": "King Saud University",
                    "year_of_graduation": "2023",
                    "cgpa": "3.80",
                    "cgpa_out_of": "4.00",
                    "language_of_study": "Arabic",
                    "specialization": "Computer Science",
                    "research_title": null,
                    "document_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/qualifications/1760704070_Ahmed_Mohamed_Ali_degree.pdf",
                    "applicant_id": 11,
                    "created_at": "2025-10-17T12:18:58.000000Z",
                    "updated_at": "2025-10-17T12:18:58.000000Z"
                }
            ]
        }
    }
}
```

### 3.2 Create Simple Application

**POST** `/applications`

**Request:**

```json
{
    "scholarship_ids": [101, 205, 308]
}
```

**Response:**

```json
{
    "message": "Application created successfully",
    "application": {
        "application_id": 26,
        "applicant_id": 11,
        "scholarship_id_1": 101,
        "scholarship_id_2": 205,
        "scholarship_id_3": 308,
        "specialization_1": null,
        "specialization_2": null,
        "specialization_3": null,
        "university_name": null,
        "country_name": null,
        "tuition_fee": null,
        "has_active_program": null,
        "current_semester_number": null,
        "cgpa": null,
        "cgpa_out_of": null,
        "terms_and_condition": null,
        "offer_letter_file": null,
        "created_at": "2025-10-17T12:25:10.000000Z",
        "updated_at": "2025-10-17T12:25:10.000000Z",
        "current_status": {
            "applicationStatus_id": 13,
            "application_id": 26,
            "status_name": "enrolled",
            "date": "2025-10-17T12:25:10.000000Z",
            "comment": "Application created",
            "created_at": "2025-10-17T12:25:10.000000Z",
            "updated_at": "2025-10-17T12:25:10.000000Z"
        },
        "scholarship1": {
            "scholarship_id": 101,
            "title": "Computer Science Scholarship",
            "description": "Full scholarship for CS students",
            "amount": "50000.00",
            "currency": "USD",
            "opening_date": "2025-01-01T00:00:00.000000Z",
            "closing_date": "2025-12-31T23:59:59.000000Z",
            "is_active": true,
            "is_hided": false
        },
        "scholarship2": {
            "scholarship_id": 205,
            "title": "Data Science Scholarship",
            "description": "Scholarship for Data Science programs",
            "amount": "45000.00",
            "currency": "USD",
            "opening_date": "2025-01-01T00:00:00.000000Z",
            "closing_date": "2025-12-31T23:59:59.000000Z",
            "is_active": true,
            "is_hided": false
        },
        "scholarship3": {
            "scholarship_id": 308,
            "title": "AI Research Scholarship",
            "description": "Research scholarship for AI students",
            "amount": "60000.00",
            "currency": "USD",
            "opening_date": "2025-01-01T00:00:00.000000Z",
            "closing_date": "2025-12-31T23:59:59.000000Z",
            "is_active": true,
            "is_hided": false
        }
    }
}
```

### 3.3 Get All Applications (Applicant)

**GET** `/applications`

**Response:**

```json
[
    {
        "application_id": 25,
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
        "offer_letter_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/offer-letters/1760704070_Ahmed_Mohamed_Ali_offer_letter.pdf",
        "applicant_id": 11,
        "scholarship_id_1": 101,
        "scholarship_id_2": 205,
        "scholarship_id_3": 308,
        "created_at": "2025-10-17T12:18:58.000000Z",
        "updated_at": "2025-10-17T12:18:58.000000Z",
        "scholarship1": {
            "scholarship_id": 101,
            "title": "Computer Science Scholarship",
            "description": "Full scholarship for CS students",
            "amount": "50000.00",
            "currency": "USD",
            "opening_date": "2025-01-01T00:00:00.000000Z",
            "closing_date": "2025-12-31T23:59:59.000000Z",
            "is_active": true,
            "is_hided": false
        },
        "scholarship2": {
            "scholarship_id": 205,
            "title": "Data Science Scholarship",
            "description": "Scholarship for Data Science programs",
            "amount": "45000.00",
            "currency": "USD",
            "opening_date": "2025-01-01T00:00:00.000000Z",
            "closing_date": "2025-12-31T23:59:59.000000Z",
            "is_active": true,
            "is_hided": false
        },
        "scholarship3": {
            "scholarship_id": 308,
            "title": "AI Research Scholarship",
            "description": "Research scholarship for AI students",
            "amount": "60000.00",
            "currency": "USD",
            "opening_date": "2025-01-01T00:00:00.000000Z",
            "closing_date": "2025-12-31T23:59:59.000000Z",
            "is_active": true,
            "is_hided": false
        },
        "current_status": {
            "applicationStatus_id": 12,
            "application_id": 25,
            "status_name": "enrolled",
            "date": "2025-10-17T12:18:58.000000Z",
            "comment": "Complete application submitted",
            "created_at": "2025-10-17T12:18:58.000000Z",
            "updated_at": "2025-10-17T12:18:58.000000Z"
        },
        "applicant": {
            "applicant_id": 11,
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
            "is_studied_in_saudi": 1,
            "tahseeli_percentage": 85.5,
            "qudorat_percentage": 78.2,
            "passport_copy_img": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/passport/1760704070_Ahmed_Mohamed_Ali_passport.pdf",
            "personal_image": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/personal-images/1760704070_Ahmed_Mohamed_Ali_image.jpg",
            "volunteering_certificate_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/volunteering/1760704070_Ahmed_Mohamed_Ali_volunteering.pdf",
            "tahsili_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/tahsili/1760704070_Ahmed_Mohamed_Ali_certificate.pdf",
            "qudorat_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/qudorat/1760704070_Ahmed_Mohamed_Ali_transcript.pdf",
            "user_id": 38,
            "created_at": "2025-10-17T07:10:52.000000Z",
            "updated_at": "2025-10-17T12:18:58.000000Z",
            "qualifications": [
                {
                    "qualification_id": 21,
                    "qualification_type": "high_school",
                    "institute_name": "Al-Nahda School",
                    "year_of_graduation": "2019",
                    "cgpa": "98.50",
                    "cgpa_out_of": "99.99",
                    "language_of_study": "Arabic",
                    "specialization": "Science",
                    "research_title": null,
                    "document_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/qualifications/1760704070_Ahmed_Mohamed_Ali_certificate.pdf",
                    "applicant_id": 11,
                    "created_at": "2025-10-17T12:18:58.000000Z",
                    "updated_at": "2025-10-17T12:18:58.000000Z"
                }
            ]
        }
    }
]
```

### 3.4 Get Application Details

**GET** `/applications/{id}`

**Response:**

```json
{
    "application_id": 25,
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
    "offer_letter_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/offer-letters/1760704070_Ahmed_Mohamed_Ali_offer_letter.pdf",
    "applicant_id": 11,
    "scholarship_id_1": 101,
    "scholarship_id_2": 205,
    "scholarship_id_3": 308,
    "created_at": "2025-10-17T12:18:58.000000Z",
    "updated_at": "2025-10-17T12:18:58.000000Z",
    "applicant": {
        "user": {
            "id": 38,
            "name": "Ahmed Mohamed Ali",
            "email": "ahmed@example.com",
            "role": "applicant",
            "email_verified_at": null,
            "created_at": "2025-10-17T07:10:52.000000Z",
            "updated_at": "2025-10-17T07:10:52.000000Z"
        },
        "applicant_id": 11,
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
        "is_studied_in_saudi": 1,
        "tahseeli_percentage": 85.5,
        "qudorat_percentage": 78.2,
        "passport_copy_img": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/passport/1760704070_Ahmed_Mohamed_Ali_passport.pdf",
        "personal_image": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/personal-images/1760704070_Ahmed_Mohamed_Ali_image.jpg",
        "volunteering_certificate_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/volunteering/1760704070_Ahmed_Mohamed_Ali_volunteering.pdf",
        "tahsili_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/tahsili/1760704070_Ahmed_Mohamed_Ali_certificate.pdf",
        "qudorat_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/qudorat/1760704070_Ahmed_Mohamed_Ali_transcript.pdf",
        "user_id": 38,
        "created_at": "2025-10-17T07:10:52.000000Z",
        "updated_at": "2025-10-17T12:18:58.000000Z",
        "qualifications": [
            {
                "qualification_id": 21,
                "qualification_type": "high_school",
                "institute_name": "Al-Nahda School",
                "year_of_graduation": "2019",
                "cgpa": "98.50",
                "cgpa_out_of": "99.99",
                "language_of_study": "Arabic",
                "specialization": "Science",
                "research_title": null,
                "document_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/qualifications/1760704070_Ahmed_Mohamed_Ali_certificate.pdf",
                "applicant_id": 11,
                "created_at": "2025-10-17T12:18:58.000000Z",
                "updated_at": "2025-10-17T12:18:58.000000Z"
            }
        ]
    },
    "scholarship1": {
        "scholarship_id": 101,
        "title": "Computer Science Scholarship",
        "description": "Full scholarship for CS students",
        "amount": "50000.00",
        "currency": "USD",
        "opening_date": "2025-01-01T00:00:00.000000Z",
        "closing_date": "2025-12-31T23:59:59.000000Z",
        "is_active": true,
        "is_hided": false
    },
    "scholarship2": {
        "scholarship_id": 205,
        "title": "Data Science Scholarship",
        "description": "Scholarship for Data Science programs",
        "amount": "45000.00",
        "currency": "USD",
        "opening_date": "2025-01-01T00:00:00.000000Z",
        "closing_date": "2025-12-31T23:59:59.000000Z",
        "is_active": true,
        "is_hided": false
    },
    "scholarship3": {
        "scholarship_id": 308,
        "title": "AI Research Scholarship",
        "description": "Research scholarship for AI students",
        "amount": "60000.00",
        "currency": "USD",
        "opening_date": "2025-01-01T00:00:00.000000Z",
        "closing_date": "2025-12-31T23:59:59.000000Z",
        "is_active": true,
        "is_hided": false
    },
    "statuses": [
        {
            "applicationStatus_id": 12,
            "application_id": 25,
            "status_name": "enrolled",
            "date": "2025-10-17T12:18:58.000000Z",
            "comment": "Complete application submitted",
            "created_at": "2025-10-17T12:18:58.000000Z",
            "updated_at": "2025-10-17T12:18:58.000000Z"
        }
    ],
    "current_status": {
        "applicationStatus_id": 12,
        "application_id": 25,
        "status_name": "enrolled",
        "date": "2025-10-17T12:18:58.000000Z",
        "comment": "Complete application submitted",
        "created_at": "2025-10-17T12:18:58.000000Z",
        "updated_at": "2025-10-17T12:18:58.000000Z"
    }
}
```

### 3.5 Update Program Details

**PUT** `/applications/{id}/program-details`

**Request (multipart/form-data):**

```json
{
  "specialization_1": "Computer Science",
  "specialization_2": "Machine Learning",
  "specialization_3": "Data Analytics",
  "university_name": "MIT",
  "country_name": "USA",
  "tuition_fee": 75000,
  "has_active_program": true,
  "current_semester_number": 3,
  "cgpa": 3.9,
  "cgpa_out_of": 4.0,
  "terms_and_condition": true,
  "offer_letter_file": [FILE]
}
```

**Response:**

```json
{
    "message": "Program details updated successfully",
    "application": {
        "application_id": 25,
        "specialization_1": "Computer Science",
        "specialization_2": "Machine Learning",
        "specialization_3": "Data Analytics",
        "university_name": "MIT",
        "country_name": "USA",
        "tuition_fee": "75000.00",
        "has_active_program": true,
        "current_semester_number": 3,
        "cgpa": "3.90",
        "cgpa_out_of": "4.00",
        "terms_and_condition": true,
        "offer_letter_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/offer-letters/1760704070_Ahmed_Mohamed_Ali_mit_offer_letter.pdf",
        "applicant_id": 11,
        "scholarship_id_1": 101,
        "scholarship_id_2": 205,
        "scholarship_id_3": 308,
        "created_at": "2025-10-17T12:18:58.000000Z",
        "updated_at": "2025-10-17T12:30:45.000000Z"
    }
}
```

---

## 4. ADMIN ENDPOINTS

### 4.1 Get All Applications (Admin)

**GET** `/admin/applications`

**Response:**

```json
[
    {
        "application_id": 25,
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
        "offer_letter_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/offer-letters/1760704070_Ahmed_Mohamed_Ali_offer_letter.pdf",
        "applicant_id": 11,
        "scholarship_id_1": 101,
        "scholarship_id_2": 205,
        "scholarship_id_3": 308,
        "created_at": "2025-10-17T12:18:58.000000Z",
        "updated_at": "2025-10-17T12:18:58.000000Z",
        "applicant": {
            "user": {
                "id": 38,
                "name": "Ahmed Mohamed Ali",
                "email": "ahmed@example.com",
                "role": "applicant",
                "email_verified_at": null,
                "created_at": "2025-10-17T07:10:52.000000Z",
                "updated_at": "2025-10-17T07:10:52.000000Z"
            },
            "applicant_id": 11,
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
            "is_studied_in_saudi": 1,
            "tahseeli_percentage": 85.5,
            "qudorat_percentage": 78.2,
            "passport_copy_img": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/passport/1760704070_Ahmed_Mohamed_Ali_passport.pdf",
            "personal_image": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/personal-images/1760704070_Ahmed_Mohamed_Ali_image.jpg",
            "volunteering_certificate_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/volunteering/1760704070_Ahmed_Mohamed_Ali_volunteering.pdf",
            "tahsili_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/tahsili/1760704070_Ahmed_Mohamed_Ali_certificate.pdf",
            "qudorat_file": "https://irfad-test-2.s3.amazonaws.com/applicant-documents/qudorat/1760704070_Ahmed_Mohamed_Ali_transcript.pdf",
            "user_id": 38,
            "created_at": "2025-10-17T07:10:52.000000Z",
            "updated_at": "2025-10-17T12:18:58.000000Z",
            "qualifications": [
                {
                    "qualification_id": 21,
                    "qualification_type": "high_school",
                    "institute_name": "Al-Nahda School",
                    "year_of_graduation": "2019",
                    "cgpa": "98.50",
                    "cgpa_out_of": "99.99",
                    "language_of_study": "Arabic",
                    "specialization": "Science",
                    "research_title": null,
                    "document_file": "https://irfad-test-2.s3.amazonaws.com/application-documents/qualifications/1760704070_Ahmed_Mohamed_Ali_certificate.pdf",
                    "applicant_id": 11,
                    "created_at": "2025-10-17T12:18:58.000000Z",
                    "updated_at": "2025-10-17T12:18:58.000000Z"
                }
            ]
        },
        "scholarship1": {
            "scholarship_id": 101,
            "title": "Computer Science Scholarship",
            "description": "Full scholarship for CS students",
            "amount": "50000.00",
            "currency": "USD",
            "opening_date": "2025-01-01T00:00:00.000000Z",
            "closing_date": "2025-12-31T23:59:59.000000Z",
            "is_active": true,
            "is_hided": false
        },
        "scholarship2": {
            "scholarship_id": 205,
            "title": "Data Science Scholarship",
            "description": "Scholarship for Data Science programs",
            "amount": "45000.00",
            "currency": "USD",
            "opening_date": "2025-01-01T00:00:00.000000Z",
            "closing_date": "2025-12-31T23:59:59.000000Z",
            "is_active": true,
            "is_hided": false
        },
        "scholarship3": {
            "scholarship_id": 308,
            "title": "AI Research Scholarship",
            "description": "Research scholarship for AI students",
            "amount": "60000.00",
            "currency": "USD",
            "opening_date": "2025-01-01T00:00:00.000000Z",
            "closing_date": "2025-12-31T23:59:59.000000Z",
            "is_active": true,
            "is_hided": false
        },
        "current_status": {
            "applicationStatus_id": 12,
            "application_id": 25,
            "status_name": "enrolled",
            "date": "2025-10-17T12:18:58.000000Z",
            "comment": "Complete application submitted",
            "created_at": "2025-10-17T12:18:58.000000Z",
            "updated_at": "2025-10-17T12:18:58.000000Z"
        }
    }
]
```

### 4.2 Update Application Status (Admin)

**PUT** `/applications/{id}/status`

**Request:**

```json
{
    "status": "first_approval",
    "comment": "Application meets initial requirements"
}
```

**Response:**

```json
{
    "message": "Application status updated successfully",
    "current_status": {
        "applicationStatus_id": 14,
        "application_id": 25,
        "status_name": "first_approval",
        "date": "2025-10-17T12:35:20.000000Z",
        "comment": "Application meets initial requirements",
        "created_at": "2025-10-17T12:35:20.000000Z",
        "updated_at": "2025-10-17T12:35:20.000000Z"
    }
}
```

### 4.3 Get Application Statistics (Admin)

**GET** `/admin/statistics`

**Response:**

```json
{
    "total_applications": 15,
    "enrolled": 8,
    "first_approval": 3,
    "second_approval": 2,
    "final_approval": 1,
    "rejected": 1
}
```

---

## 5. ERROR RESPONSES

### 5.1 Validation Error

**Status:** 422

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "personal_info.en_name": [
            "The personal info.en name field is required."
        ],
        "academic_info.qualifications.0.document_file": [
            "The academic info.qualifications.0.document file field is required."
        ]
    }
}
```

### 5.2 Unauthorized

**Status:** 401

```json
{
    "message": "Unauthenticated."
}
```

### 5.3 Forbidden (Admin Only)

**Status:** 403

```json
{
    "message": "Forbidden. Insufficient permissions."
}
```

### 5.4 Not Found

**Status:** 404

```json
{
    "message": "Application not found"
}
```

### 5.5 Server Error

**Status:** 500

```json
{
    "message": "Failed to submit application",
    "error": "Database connection failed"
}
```

---

## 6. APPLICATION STATUS VALUES

The following status values are available:

-   `enrolled` - Initial status when application is created
-   `first_approval` - First level approval
-   `second_approval` - Second level approval
-   `final_approval` - Final approval
-   `rejected` - Application rejected

---

## 7. QUALIFICATION TYPES

The following qualification types are available:

-   `high_school` - High school certificate
-   `diploma` - Diploma certificate
-   `bachelor` - Bachelor's degree
-   `master` - Master's degree
-   `phd` - PhD degree
-   `other` - Other qualification

---

## 8. FILE UPLOAD REQUIREMENTS

### Supported File Types:

-   **Images:** jpeg, png, jpg
-   **Documents:** pdf

### File Size Limits:

-   **Images:** 5MB max
-   **Documents:** 10MB max

### File Storage:

All files are stored in S3 with the following structure:

-   **Applicant Documents:** `applicant-documents/{type}/`
-   **Application Documents:** `application-documents/{type}/`
-   **Qualification Documents:** `application-documents/qualifications/`

### URL Format:

All file URLs follow this format:

```
https://irfad-test-2.s3.amazonaws.com/{folder}/{timestamp}_{sanitized_filename}
```

Example:

```
https://irfad-test-2.s3.amazonaws.com/applicant-documents/passport/1760704070_Ahmed_Mohamed_Ali_passport.pdf
```
