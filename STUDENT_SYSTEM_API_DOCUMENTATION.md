# Student Promotion & Graduation System API Documentation

## Overview

This system implements the final approval → promote applicant to student and graduation → demote student to applicant workflows as specified in the requirements.

## Database Schema

### New Tables Created

1. **`students`** - Stores student profiles (copied from applicants)
2. **`approved_applicant_applications`** - Links approved applications to scholarships
3. **`applicants`** - Updated with archive fields (`is_archived`, `migrated_to_student_at`, `reactivated_from_student_at`)

### Key Relationships

-   `students.user_id` → `users.user_id` (one-to-one)
-   `students.approved_application_id` → `approved_applicant_applications.approved_application_id`
-   `approved_applicant_applications.application_id` → `applicant_applications.application_id`
-   `qualifications.user_id` → `users.user_id` (preserved throughout role changes)

## API Endpoints

### 1. Promote Applicant to Student

**POST** `/api/admin/applications/{applicationId}/assign-final-scholarship`

**Description:** Promotes an applicant with final approval status to a student.

**Authentication:** Admin only (`auth:sanctum`, `role:admin`)

**Request Body:**

```json
{
    "scholarship_id": 123
}
```

**Preconditions:**

-   Application must have `final_approval` status
-   User must be an applicant
-   Application must exist

**Response (201):**

```json
{
    "message": "Applicant successfully promoted to student",
    "approved_application_id": 456,
    "student_id": 789,
    "user_role": "student"
}
```

**Error Responses:**

-   `422` - Application doesn't have final_approval status
-   `422` - User is not an applicant
-   `500` - Database error

### 2. Graduate Student

**POST** `/api/admin/students/{studentId}/graduate`

**Description:** Graduates a student and demotes them back to applicant status.

**Authentication:** Admin only (`auth:sanctum`, `role:admin`)

**Request Body:** None

**Preconditions:**

-   Student must exist and not already graduated
-   Student must be active (not archived)

**Response (200):**

```json
{
    "message": "Student successfully graduated and demoted to applicant",
    "applicant_id": 123,
    "user_role": "applicant"
}
```

**Error Responses:**

-   `422` - Student already graduated
-   `404` - Student not found
-   `500` - Database error

### 3. List All Students

**GET** `/api/admin/students`

**Description:** Retrieves all active students with their related data.

**Authentication:** Admin only (`auth:sanctum`, `role:admin`)

**Response (200):**

```json
{
    "data": [
        {
            "student_id": 1,
            "user_id": 123,
            "ar_name": "أحمد محمد",
            "en_name": "Ahmed Mohammed",
            "nationality": "Saudi",
            "user": {
                "user_id": 123,
                "email": "ahmed@example.com",
                "role": "student"
            },
            "approved_application": {
                "approved_application_id": 456,
                "application_id": 789,
                "scholarship_id": 101
            }
        }
    ],
    "meta": {
        "total": 1,
        "active_count": 1,
        "graduated_count": 0
    }
}
```

### 4. Get Specific Student

**GET** `/api/admin/students/{studentId}`

**Description:** Retrieves detailed information about a specific student.

**Authentication:** Admin only (`auth:sanctum`, `role:admin`)

**Response (200):**

```json
{
    "student_id": 1,
    "user_id": 123,
    "ar_name": "أحمد محمد",
    "en_name": "Ahmed Mohammed",
    "nationality": "Saudi",
    "gender": "male",
    "date_of_birth": "1995-01-15",
    "phone": "+966501234567",
    "user": {
        "user_id": 123,
        "email": "ahmed@example.com",
        "role": "student"
    },
    "approved_application": {
        "approved_application_id": 456,
        "application": {
            "application_id": 789,
            "university_name": "King Saud University",
            "specialization_1": "Computer Science"
        },
        "scholarship": {
            "scholarship_id": 101,
            "scholarship_name": "Excellence Scholarship"
        }
    },
    "qualifications": [
        {
            "qualification_id": 1,
            "qualification_type": "Bachelor",
            "institute_name": "King Saud University",
            "year_of_graduation": 2020
        }
    ]
}
```

## Business Rules

### Promotion Rules

1. **Status Check:** Application must have `final_approval` status
2. **Role Check:** User must be an `applicant`
3. **Idempotent:** Can be run multiple times safely (uses `firstOrCreate`)
4. **Atomic:** All operations in a single database transaction
5. **Archive:** Original applicant record is archived, not deleted

### Graduation Rules

1. **Status Check:** Student must not already be graduated
2. **Idempotent:** Can be run multiple times safely
3. **Atomic:** All operations in a single database transaction
4. **Profile Copy:** Student profile data is copied back to applicant
5. **Archive:** Student record is archived with graduation timestamp

### Data Integrity

1. **Qualifications:** Always remain linked to `user_id` (never moved)
2. **No Hard Deletes:** All records use `is_archived` flag
3. **Timestamps:** Track migration and reactivation dates
4. **Role Switching:** User role changes between `applicant` and `student`
5. **Unique Constraints:** Prevent duplicate student records per user

## Implementation Details

### Database Transactions

All operations use `DB::transaction()` with `lockForUpdate()` to prevent race conditions:

```php
DB::transaction(function () use ($applicationId, $data) {
    $application = ApplicantApplication::with(['applicant.user'])
        ->lockForUpdate()
        ->findOrFail($applicationId);
    // ... rest of the logic
});
```

### Idempotent Operations

Uses `firstOrCreate()` to ensure operations can be safely repeated:

```php
$student = Student::firstOrCreate(
    ['user_id' => $user->user_id],
    [/* profile data */]
);
```

### Error Handling

-   Comprehensive validation with `ValidationException`
-   Proper HTTP status codes
-   Detailed error messages
-   Database rollback on failures

## Security

### Authentication

-   All endpoints require `auth:sanctum` middleware
-   Admin-only access with `role:admin` middleware

### Authorization

-   Role-based access control
-   Input validation on all requests
-   SQL injection protection through Eloquent ORM

### Data Protection

-   No hard deletes (audit trail preserved)
-   Atomic transactions prevent data corruption
-   Lock mechanisms prevent concurrent modifications

## Testing

### Manual Testing

1. Create an applicant with completed profile
2. Submit an application
3. Set application status to `final_approval`
4. Call promotion endpoint
5. Verify student record created
6. Call graduation endpoint
7. Verify applicant record reactivated

### Automated Testing

The system includes comprehensive error handling and validation that can be tested with:

-   Invalid application IDs
-   Non-final-approval applications
-   Already graduated students
-   Concurrent operations

## Migration Notes

### Existing Data

-   All existing applicant data is preserved
-   Archive fields added to `applicants` table
-   New tables created without affecting existing data
-   Qualifications remain linked to `user_id` throughout

### Rollback

All migrations include proper `down()` methods for rollback if needed.

## Support

For issues or questions regarding the student promotion and graduation system, refer to:

-   Controller: `app/Http/Controllers/StudentController.php`
-   Models: `app/Models/Student.php`, `app/Models/ApprovedApplicantApplication.php`
-   Routes: `routes/api/v1/student.php`
-   Migrations: `database/migrations/2025_10_20_*`

