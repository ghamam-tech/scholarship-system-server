# 🎓 Student Management System API Documentation

## **Overview**

Complete student tracking system with status trails and semester management.

## **New Database Tables**

### **1. student_status_trails**

-   `status_trail_id` (Primary Key)
-   `student_id` (Foreign Key → students.student_id)
-   `status_name` (String: active, first_warning, second_warning, request_meeting, graduate_student, suspended, terminated)
-   `date` (DateTime)
-   `comment` (Text)
-   `changed_by` (String - Admin who made the change)
-   `created_at`, `updated_at`

### **2. semesters**

-   `semester_id` (Primary Key)
-   `student_id` (Foreign Key → students.student_id)
-   `semester_no` (Integer: 1, 2, 3, etc.)
-   `courses` (Integer: Number of courses)
-   `credits` (Integer: Total credits)
-   `start_date` (Date)
-   `end_date` (Date)
-   `cgpa` (Decimal: 4.00 format)
-   `status` (String: active, completed, failed, withdrawn)
-   `transcript` (String: File path)
-   `notes` (Text)
-   `created_at`, `updated_at`

## **New Enums**

### **StudentStatus Enum:**

```php
ACTIVE = 'active'
FIRST_WARNING = 'first_warning'
SECOND_WARNING = 'second_warning'
REQUEST_MEETING = 'request_meeting'
GRADUATE_STUDENT = 'graduate_student'
SUSPENDED = 'suspended'
TERMINATED = 'terminated'
```

---

## **API Endpoints**

### **Student Status Trail Management**

#### **1. Get Student Status Trail**

**GET** `/api/admin/students/{studentId}/status-trail`

**Response (200):**

```json
{
    "student_id": 1,
    "student_name": "أحمد محمد",
    "status_trail": [
        {
            "status_trail_id": 1,
            "student_id": 1,
            "status_name": "active",
            "date": "2025-10-21T10:00:00Z",
            "comment": "Student enrolled successfully",
            "changed_by": "admin@example.com"
        },
        {
            "status_trail_id": 2,
            "student_id": 1,
            "status_name": "first_warning",
            "date": "2025-10-25T14:30:00Z",
            "comment": "Academic performance below standards",
            "changed_by": "admin@example.com"
        }
    ],
    "current_status": {
        "status_trail_id": 2,
        "status_name": "first_warning",
        "date": "2025-10-25T14:30:00Z",
        "comment": "Academic performance below standards"
    },
    "total_status_changes": 2
}
```

#### **2. Add Student Status**

**POST** `/api/admin/students/{studentId}/status`

**Request:**

```json
{
    "status_name": "second_warning",
    "comment": "Continued poor academic performance",
    "date": "2025-10-30T09:00:00Z"
}
```

**Response (201):**

```json
{
    "message": "Student status updated successfully",
    "status_trail": {
        "status_trail_id": 3,
        "student_id": 1,
        "status_name": "second_warning",
        "date": "2025-10-30T09:00:00Z",
        "comment": "Continued poor academic performance",
        "changed_by": "admin@example.com"
    }
}
```

#### **3. Get Students by Status**

**GET** `/api/admin/students/by-status/{status}`

**Response (200):**

```json
{
    "status": "first_warning",
    "students": [
        {
            "student_id": 1,
            "ar_name": "أحمد محمد",
            "en_name": "Ahmed Mohammed",
            "user": {
                "user_id": 123,
                "email": "ahmed@example.com",
                "role": "student"
            },
            "current_status": {
                "status_name": "first_warning",
                "date": "2025-10-25T14:30:00Z"
            }
        }
    ],
    "count": 1
}
```

#### **4. Get Students with Warnings**

**GET** `/api/admin/students/with-warnings`

**Response (200):**

```json
{
    "students_with_warnings": [
        {
            "student_id": 1,
            "ar_name": "أحمد محمد",
            "current_status": {
                "status_name": "first_warning"
            }
        },
        {
            "student_id": 2,
            "ar_name": "سارة أحمد",
            "current_status": {
                "status_name": "second_warning"
            }
        }
    ],
    "first_warning_count": 1,
    "second_warning_count": 1,
    "total": 2
}
```

#### **5. Get Students Requesting Meetings**

**GET** `/api/admin/students/requesting-meetings`

**Response (200):**

```json
{
    "students_requesting_meetings": [
        {
            "student_id": 1,
            "ar_name": "أحمد محمد",
            "current_status": {
                "status_name": "request_meeting",
                "comment": "Student requested academic counseling"
            }
        }
    ],
    "count": 1
}
```

---

### **Semester Management**

#### **6. Get Student Semesters**

**GET** `/api/admin/students/{studentId}/semesters`

**Response (200):**

```json
{
    "student_id": 1,
    "student_name": "أحمد محمد",
    "semesters": [
        {
            "semester_id": 1,
            "student_id": 1,
            "semester_no": 1,
            "courses": 5,
            "credits": 15,
            "start_date": "2025-09-01",
            "end_date": "2025-12-15",
            "cgpa": 3.25,
            "status": "completed",
            "transcript": "https://s3.../transcript1.pdf",
            "notes": "Good performance"
        },
        {
            "semester_id": 2,
            "student_id": 1,
            "semester_no": 2,
            "courses": 4,
            "credits": 12,
            "start_date": "2026-01-15",
            "end_date": "2026-05-15",
            "cgpa": null,
            "status": "active",
            "transcript": null,
            "notes": "Current semester"
        }
    ],
    "total_semesters": 2,
    "active_semester": {
        "semester_id": 2,
        "semester_no": 2,
        "status": "active"
    },
    "completed_semesters": 1
}
```

#### **7. Create New Semester**

**POST** `/api/admin/students/{studentId}/semesters`

**Request:**

```json
{
    "semester_no": 3,
    "courses": 6,
    "credits": 18,
    "start_date": "2026-09-01",
    "end_date": "2026-12-15",
    "cgpa": null,
    "status": "active",
    "notes": "Fall semester 2026"
}
```

**Response (201):**

```json
{
    "message": "Semester created successfully",
    "semester": {
        "semester_id": 3,
        "student_id": 1,
        "semester_no": 3,
        "courses": 6,
        "credits": 18,
        "start_date": "2026-09-01",
        "end_date": "2026-12-15",
        "cgpa": null,
        "status": "active",
        "transcript": null,
        "notes": "Fall semester 2026"
    }
}
```

#### **8. Update Semester**

**PUT** `/api/admin/semesters/{semesterId}`

**Request:**

```json
{
    "courses": 5,
    "credits": 15,
    "cgpa": 3.5,
    "status": "completed",
    "notes": "Semester completed successfully"
}
```

**Response (200):**

```json
{
    "message": "Semester updated successfully",
    "semester": {
        "semester_id": 1,
        "student_id": 1,
        "semester_no": 1,
        "courses": 5,
        "credits": 15,
        "cgpa": 3.5,
        "status": "completed",
        "notes": "Semester completed successfully"
    }
}
```

#### **9. Get Semester Statistics**

**GET** `/api/admin/students/{studentId}/semester-statistics`

**Response (200):**

```json
{
    "student_id": 1,
    "student_name": "أحمد محمد",
    "statistics": {
        "total_semesters": 3,
        "active_semesters": 1,
        "completed_semesters": 2,
        "failed_semesters": 0,
        "withdrawn_semesters": 0,
        "total_courses": 15,
        "total_credits": 45,
        "average_cgpa": 3.375,
        "highest_cgpa": 3.75,
        "lowest_cgpa": 3.0,
        "current_semester": {
            "semester_id": 3,
            "semester_no": 3,
            "status": "active"
        },
        "latest_semester": {
            "semester_id": 3,
            "semester_no": 3,
            "status": "active"
        }
    }
}
```

#### **10. Get All Active Semesters**

**GET** `/api/admin/semesters/active`

**Response (200):**

```json
{
    "active_semesters": [
        {
            "semester_id": 1,
            "student_id": 1,
            "semester_no": 2,
            "status": "active",
            "start_date": "2026-01-15",
            "end_date": "2026-05-15",
            "student": {
                "student_id": 1,
                "ar_name": "أحمد محمد",
                "user": {
                    "email": "ahmed@example.com"
                }
            }
        }
    ],
    "count": 1
}
```

---

## **Status Flow Examples**

### **Academic Warning Flow:**

```
active → first_warning → second_warning → request_meeting → active (if improved)
```

### **Graduation Flow:**

```
active → graduate_student → [system graduation process]
```

### **Termination Flow:**

```
active → first_warning → second_warning → terminated
```

---

## **Business Rules**

### **Status Trail Rules:**

-   ✅ Each status change is logged with timestamp and admin
-   ✅ Complete audit trail for all student status changes
-   ✅ Status changes are irreversible (new status added, not updated)
-   ✅ Current status is the most recent status entry

### **Semester Rules:**

-   ✅ Each student can have multiple semesters
-   ✅ Semester numbers must be unique per student
-   ✅ Only one active semester per student at a time
-   ✅ Transcript files stored in S3 with organized paths

### **Validation Rules:**

-   ✅ CGPA must be between 0.00 and 4.00
-   ✅ End date must be after start date
-   ✅ Semester number must be positive integer
-   ✅ File uploads limited to 10MB

---

## **File Management**

### **Transcript Storage:**

```
s3://bucket/students/{student_id}/semesters/{semester_id}/transcripts/{filename}
```

### **Supported File Types:**

-   PDF documents
-   JPG/JPEG images
-   PNG images
-   Maximum size: 10MB

---

## **Error Handling**

### **422 Validation Errors:**

```json
{
    "message": "Validation failed",
    "errors": {
        "semester_no": ["Semester number already exists for this student"],
        "cgpa": ["The cgpa must be between 0 and 4"]
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
    "message": "Failed to create semester",
    "error": "Database connection failed"
}
```

---

## **Complete Student Management Workflow**

1. **Student Promotion** → Create initial status trail entry
2. **Academic Monitoring** → Add warning statuses as needed
3. **Semester Management** → Create and track academic semesters
4. **Performance Tracking** → Update CGPA and status
5. **Graduation Process** → Mark as graduate_student
6. **Complete Audit Trail** → Full history of student journey

The system now provides comprehensive student lifecycle management with complete tracking and reporting capabilities! 🚀

