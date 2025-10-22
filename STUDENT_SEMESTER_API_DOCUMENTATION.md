# 🎓 Student Semester Management API Documentation

## **Overview**

Students can now manage their own semesters, while admins can manage any student's semesters.

## **Authorization Levels**

### **Student Access:**

-   ✅ Students can **create** their own semesters
-   ✅ Students can **view** their own semesters
-   ✅ Students can **edit** their own semesters
-   ✅ Students can **view** their own semester statistics

### **Admin Access:**

-   ✅ Admins can **view** any student's semesters
-   ✅ Admins can **create** semesters for any student
-   ✅ Admins can **edit** any student's semesters
-   ✅ Admins can **view** statistics for any student
-   ✅ Admins can **view** all active semesters across all students

---

## **Student Routes (Student Access Only)**

### **1. Get My Semesters**

**GET** `/api/student/semesters`

**Authentication:** `auth:sanctum`, `role:student`

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

### **2. Create My Semester**

**POST** `/api/student/semesters`

**Authentication:** `auth:sanctum`, `role:student`

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

### **3. Update My Semester**

**PUT** `/api/student/semesters/{semesterId}`

**Authentication:** `auth:sanctum`, `role:student`

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

### **4. Get My Semester Statistics**

**GET** `/api/student/semester-statistics`

**Authentication:** `auth:sanctum`, `role:student`

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

---

## **Admin Routes (Admin Access Only)**

### **5. Get Student Semesters (Admin View)**

**GET** `/api/admin/students/{studentId}/semesters`

**Authentication:** `auth:sanctum`, `role:admin`

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
        }
    ],
    "total_semesters": 1,
    "active_semester": null,
    "completed_semesters": 1
}
```

### **6. Create Semester for Student (Admin)**

**POST** `/api/admin/students/{studentId}/semesters`

**Authentication:** `auth:sanctum`, `role:admin`

**Request:**

```json
{
    "semester_no": 2,
    "courses": 4,
    "credits": 12,
    "start_date": "2026-01-15",
    "end_date": "2026-05-15",
    "cgpa": null,
    "status": "active",
    "notes": "Spring semester 2026"
}
```

**Response (201):**

```json
{
    "message": "Semester created successfully",
    "semester": {
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
        "notes": "Spring semester 2026"
    }
}
```

### **7. Update Any Semester (Admin)**

**PUT** `/api/admin/semesters/{semesterId}`

**Authentication:** `auth:sanctum`, `role:admin`

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

### **8. Get Student Semester Statistics (Admin)**

**GET** `/api/admin/students/{studentId}/semester-statistics`

**Authentication:** `auth:sanctum`, `role:admin`

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

### **9. Get All Active Semesters (Admin)**

**GET** `/api/admin/semesters/active`

**Authentication:** `auth:sanctum`, `role:admin`

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

## **Authorization Rules**

### **Student Access Rules:**

-   ✅ Students can only access their own semesters
-   ✅ Students can create semesters for themselves only
-   ✅ Students can edit only their own semesters
-   ✅ Students cannot access other students' data

### **Admin Access Rules:**

-   ✅ Admins can access any student's semesters
-   ✅ Admins can create semesters for any student
-   ✅ Admins can edit any student's semesters
-   ✅ Admins can view system-wide statistics

### **Security Features:**

-   ✅ Role-based access control enforced
-   ✅ Students can only modify their own data
-   ✅ Admins have full access to all student data
-   ✅ Proper authentication required for all endpoints

---

## **File Upload Support**

### **Transcript Upload:**

-   ✅ Students can upload transcripts for their semesters
-   ✅ Admins can upload transcripts for any student
-   ✅ Files stored in S3 with organized paths
-   ✅ Support for PDF, JPG, JPEG, PNG files
-   ✅ Maximum file size: 10MB

### **File Storage Path:**

```
s3://bucket/students/{student_id}/semesters/{semester_id}/transcripts/{filename}
```

---

## **Validation Rules**

### **Semester Creation:**

-   ✅ `semester_no` must be unique per student
-   ✅ `start_date` must be before `end_date`
-   ✅ `cgpa` must be between 0.00 and 4.00
-   ✅ `courses` and `credits` must be non-negative integers
-   ✅ `status` must be one of: active, completed, failed, withdrawn

### **File Upload:**

-   ✅ Only PDF, JPG, JPEG, PNG files allowed
-   ✅ Maximum file size: 10MB
-   ✅ Files automatically organized by student and semester

---

## **Error Handling**

### **403 Forbidden (Student trying to access other student's data):**

```json
{
    "message": "Forbidden. Insufficient permissions."
}
```

### **404 Not Found (Student profile not found):**

```json
{
    "message": "Student profile not found"
}
```

### **422 Validation Error:**

```json
{
    "message": "Validation failed",
    "errors": {
        "semester_no": ["Semester number already exists for this student"],
        "cgpa": ["The cgpa must be between 0 and 4"]
    }
}
```

---

## **Complete Workflow Examples**

### **Student Workflow:**

1. **Student logs in** → Gets their own semesters
2. **Student creates semester** → Adds new academic period
3. **Student updates semester** → Modifies their academic record
4. **Student uploads transcript** → Adds supporting documents
5. **Student views statistics** → Tracks their academic progress

### **Admin Workflow:**

1. **Admin logs in** → Can view any student's semesters
2. **Admin creates semester** → Can create for any student
3. **Admin updates semester** → Can modify any student's record
4. **Admin views statistics** → Can analyze any student's progress
5. **Admin monitors system** → Can view all active semesters

The system now provides flexible semester management with proper role-based access control! 🚀

