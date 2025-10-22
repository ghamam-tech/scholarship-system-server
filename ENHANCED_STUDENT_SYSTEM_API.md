# 🎓 Enhanced Student System API Documentation

## **New Status Flow:**

```
enrolled → first_approval → second_approval → final_approval → [student promotion] → graduate/scholarship_suspension
```

## **New API Endpoints**

### **1. Final Approval with Scholarship Assignment (NEW)**

**POST** `/api/admin/applications/{applicationId}/final-approval-with-scholarship`

**Description:** Single endpoint that grants final approval AND assigns scholarship in one operation.

**Request:**

```json
{
    "scholarship_id": 123,
    "comment": "Final approval granted with scholarship assignment"
}
```

**Response (201):**

```json
{
    "message": "Application granted final approval and applicant promoted to student",
    "approved_application_id": 456,
    "student_id": 789,
    "user_role": "student",
    "application_status": "final_approval"
}
```

**What it does:**

1. ✅ Adds `final_approval` status to `applicant_application_statuses`
2. ✅ Creates `approved_applicant_applications` record
3. ✅ Creates `students` record (copies profile from applicant)
4. ✅ Changes user role from `applicant` to `student`
5. ✅ Archives applicant record

---

### **2. Graduate Student (ENHANCED)**

**POST** `/api/admin/students/{studentId}/graduate`

**Request:**

```json
{}
```

**Response (200):**

```json
{
    "message": "Student successfully graduated and demoted to applicant",
    "applicant_id": 123,
    "user_role": "applicant"
}
```

**What it does:**

1. ✅ Archives student record with `graduated_at` timestamp
2. ✅ Adds `graduate` status to original application in `applicant_application_statuses`
3. ✅ Creates/unarchives applicant record (copies profile from student)
4. ✅ Changes user role from `student` to `applicant`

---

### **3. Suspend Student Scholarship (NEW)**

**POST** `/api/admin/students/{studentId}/suspend`

**Request:**

```json
{
    "reason": "Academic performance below standards",
    "comment": "Student needs to improve grades before scholarship renewal"
}
```

**Response (200):**

```json
{
    "message": "Student scholarship suspended and demoted to applicant",
    "applicant_id": 123,
    "user_role": "applicant",
    "suspension_reason": "Academic performance below standards"
}
```

**What it does:**

1. ✅ Archives student record (without graduation timestamp)
2. ✅ Adds `scholarship_suspension` status to original application
3. ✅ Creates/unarchives applicant record (copies profile from student)
4. ✅ Changes user role from `student` to `applicant`

---

## **Complete Status Tracking**

### **Application Status Trail Example:**

```json
{
    "status_trail": [
        {
            "status_name": "enrolled",
            "date": "2025-10-20T09:00:00Z",
            "comment": "Application submitted"
        },
        {
            "status_name": "first_approval",
            "date": "2025-10-20T14:30:00Z",
            "comment": "Initial review completed"
        },
        {
            "status_name": "second_approval",
            "date": "2025-10-21T08:15:00Z",
            "comment": "Secondary review approved"
        },
        {
            "status_name": "final_approval",
            "date": "2025-10-21T10:30:00Z",
            "comment": "Final approval granted with scholarship assignment"
        },
        {
            "status_name": "graduate",
            "date": "2025-12-15T16:45:00Z",
            "comment": "Student graduated successfully"
        }
    ]
}
```

### **Suspension Status Trail Example:**

```json
{
    "status_trail": [
        {
            "status_name": "enrolled",
            "date": "2025-10-20T09:00:00Z",
            "comment": "Application submitted"
        },
        {
            "status_name": "first_approval",
            "date": "2025-10-20T14:30:00Z",
            "comment": "Initial review completed"
        },
        {
            "status_name": "final_approval",
            "date": "2025-10-21T10:30:00Z",
            "comment": "Final approval granted with scholarship assignment"
        },
        {
            "status_name": "scholarship_suspension",
            "date": "2025-11-15T14:20:00Z",
            "comment": "Scholarship suspended: Academic performance below standards"
        }
    ]
}
```

---

## **All Available Endpoints**

### **Student Management:**

-   `POST /api/admin/applications/{id}/final-approval-with-scholarship` - **NEW** Combined final approval + scholarship
-   `POST /api/admin/applications/{id}/assign-final-scholarship` - Legacy final approval
-   `POST /api/admin/students/{id}/graduate` - Graduate student
-   `POST /api/admin/students/{id}/suspend` - **NEW** Suspend scholarship
-   `GET /api/admin/students` - List all students
-   `GET /api/admin/students/{id}` - Get specific student

### **Application Management:**

-   `GET /api/admin/applications` - List all applications
-   `GET /api/admin/applications/{id}/details` - Get application details
-   `POST /api/admin/applications/{id}/status` - Update application status
-   `GET /api/admin/statistics` - Get application statistics

---

## **Business Rules**

### **Final Approval Rules:**

-   ✅ Application must have `first_approval` or `second_approval` status
-   ✅ User must be an `applicant`
-   ✅ Single atomic operation (status + promotion)
-   ✅ Idempotent (can be run multiple times safely)

### **Graduation Rules:**

-   ✅ Student must not already be graduated
-   ✅ Adds `graduate` status to original application
-   ✅ Preserves complete application history
-   ✅ Copies student profile back to applicant

### **Suspension Rules:**

-   ✅ Requires suspension reason
-   ✅ Adds `scholarship_suspension` status to original application
-   ✅ Prevents duplicate suspensions
-   ✅ Allows re-application after suspension

### **Status Validation:**

-   ✅ `graduate` and `scholarship_suspension` are mutually exclusive
-   ✅ Complete audit trail in `applicant_application_statuses`
-   ✅ All operations are atomic and reversible

---

## **Error Handling**

### **422 Validation Errors:**

```json
{
    "message": "Validation failed",
    "errors": {
        "application": [
            "Application must have second_approval or first_approval status to be granted final approval"
        ],
        "student": ["Student has already graduated"],
        "student": ["Student scholarship is already suspended"]
    }
}
```

### **404 Not Found:**

```json
{
    "message": "Student not found"
}
```

### **500 Server Error:**

```json
{
    "message": "Failed to grant final approval and promote applicant",
    "error": "Database connection failed"
}
```

---

## **Complete Workflow Examples**

### **Successful Student Journey:**

1. **Submit Application** → `enrolled`
2. **First Review** → `first_approval`
3. **Second Review** → `second_approval`
4. **Final Approval** → `final_approval` + Student promotion
5. **Graduation** → `graduate` + Back to applicant

### **Suspended Student Journey:**

1. **Submit Application** → `enrolled`
2. **First Review** → `first_approval`
3. **Final Approval** → `final_approval` + Student promotion
4. **Suspension** → `scholarship_suspension` + Back to applicant
5. **Re-application** → New application cycle

---

## **Database Impact**

### **Tables Affected:**

-   ✅ `applicant_application_statuses` - New statuses added
-   ✅ `approved_applicant_applications` - Created on final approval
-   ✅ `students` - Created on promotion, archived on graduation/suspension
-   ✅ `applicants` - Archived on promotion, unarchived on graduation/suspension
-   ✅ `users` - Role changes between `applicant` and `student`

### **Data Integrity:**

-   ✅ All operations are atomic (DB transactions)
-   ✅ Complete audit trail preserved
-   ✅ No hard deletes (archive flags used)
-   ✅ Qualifications remain linked to `user_id` throughout

---

## **Security & Authorization**

-   ✅ All endpoints require admin authentication
-   ✅ Role-based access control enforced
-   ✅ Input validation on all requests
-   ✅ Database locks prevent race conditions
-   ✅ Comprehensive error handling

The enhanced system now provides complete lifecycle management for scholarship applications with full status tracking! 🚀

