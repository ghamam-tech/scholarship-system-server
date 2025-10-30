# Opportunity & Application Opportunity API Documentation

## Base URL

```
http://127.0.0.1:8000/api/v1
```

## Authentication

Most endpoints require authentication using Bearer token. Include the token in the Authorization header:

```
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## 🎯 **OPPORTUNITY ENDPOINTS**

### **1. Get All Opportunities (Admin Only)**

**GET** `/admin/opportunities` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "opportunities": [
        {
            "opportunity_id": 1,
            "title": "Community Service Event",
            "date": "2025-02-15",
            "category": "Volunteering",
            "country": "Saudi Arabia",
            "status": "active",
            "invitations_count": 25,
            "location": "Riyadh",
            "volunteer_role": "Event Coordinator",
            "volunteering_hours": 8
        }
    ]
}
```

---

### **2. Create Opportunity (Admin Only)**

**POST** `/admin/opportunities` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: multipart/form-data
```

**Request Body (Form Data):**

```
title: "Community Service Event"
discription: "Help organize community service activities"
date: "2025-02-15"
location: "Riyadh"
country: "Saudi Arabia"
category: "Volunteering"
image_file: [FILE] (optional, max 5MB, jpeg/png/jpg/gif/webp)
opportunity_coordinatior_name: "John Doe"
opportunity_coordinatior_phone: "+966501234567"
opportunity_coordinatior_email: "coordinator@example.com"
start_date: "2025-02-15"
end_date: "2025-02-16"
volunteer_role: "Event Coordinator"
volunteering_hours: 8
```

**Response (201):**

```json
{
    "message": "Opportunity created successfully",
    "opportunity": {
        "opportunity_id": 1,
        "title": "Community Service Event",
        "discription": "Help organize community service activities",
        "date": "2025-02-15",
        "location": "Riyadh",
        "country": "Saudi Arabia",
        "category": "Volunteering",
        "opportunity_status": "active",
        "enable_qr_attendance": true,
        "generate_certificates": true,
        "opportunity_coordinatior_name": "John Doe",
        "opportunity_coordinatior_phone": "+966501234567",
        "opportunity_coordinatior_email": "coordinator@example.com",
        "start_date": "2025-02-15",
        "end_date": "2025-02-16",
        "volunteer_role": "Event Coordinator",
        "volunteering_hours": 8,
        "image_file": "opportunities/images/filename.jpg",
        "image_url": "http://127.0.0.1:8000/storage/opportunities/images/filename.jpg",
        "qr_url": "random32charactertoken",
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

---

### **3. Get Single Opportunity (Admin Only)**

**GET** `/admin/opportunities/{id}` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "opportunity": {
        "opportunity_id": 1,
        "discription": "Help organize community service activities",
        "title": "Community Service Event",
        "date": "2025-02-15",
        "category": "Volunteering",
        "country": "Saudi Arabia",
        "status": "active",
        "location": "Riyadh",
        "invitations_count": 25,
        "enable_qr_attendance": true,
        "generate_certificates": true,
        "opportunity_coordinatior_name": "John Doe",
        "opportunity_coordinatior_phone": "+966501234567",
        "opportunity_coordinatior_email": "coordinator@example.com",
        "start_date": "2025-02-15",
        "end_date": "2025-02-16",
        "volunteer_role": "Event Coordinator",
        "volunteering_hours": 8,
        "image_file": "opportunities/images/filename.jpg",
        "image_url": "http://127.0.0.1:8000/storage/opportunities/images/filename.jpg",
        "qr_url": "random32charactertoken",
        "applications": [
            {
                "application_id": "opp_0000001",
                "student_id": 1,
                "status": "accepted"
            }
        ]
    }
}
```

---

### **4. Update Opportunity (Admin Only)**

**PUT/PATCH** `/admin/opportunities/{id}` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json
```

**Request Body (JSON):**

```json
{
    "title": "Updated Community Service Event",
    "discription": "Updated description",
    "date": "2025-02-20",
    "location": "Jeddah",
    "country": "Saudi Arabia",
    "category": "Volunteering",
    "opportunity_coordinatior_name": "Jane Smith",
    "opportunity_coordinatior_phone": "+966501234568",
    "opportunity_coordinatior_email": "jane@example.com",
    "start_date": "2025-02-20",
    "end_date": "2025-02-21",
    "opportunity_status": "active",
    "volunteer_role": "Senior Coordinator",
    "volunteering_hours": 10
}
```

**Response (200):**

```json
{
    "message": "Opportunity updated successfully",
    "opportunity": {
        "opportunity_id": 1,
        "title": "Updated Community Service Event",
        "discription": "Updated description",
        "date": "2025-02-20",
        "location": "Jeddah",
        "country": "Saudi Arabia",
        "category": "Volunteering",
        "opportunity_status": "active",
        "enable_qr_attendance": true,
        "generate_certificates": true,
        "opportunity_coordinatior_name": "Jane Smith",
        "opportunity_coordinatior_phone": "+966501234568",
        "opportunity_coordinatior_email": "jane@example.com",
        "start_date": "2025-02-20",
        "end_date": "2025-02-21",
        "volunteer_role": "Senior Coordinator",
        "volunteering_hours": 10,
        "image_file": "opportunities/images/filename.jpg",
        "image_url": "http://127.0.0.1:8000/storage/opportunities/images/filename.jpg",
        "qr_url": "random32charactertoken",
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

---

### **5. Delete Opportunity (Admin Only)**

**DELETE** `/admin/opportunities/{id}` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "message": "Opportunity deleted successfully"
}
```

---

### **6. Change Opportunity Status (Admin Only)**

**PATCH** `/admin/opportunities/{id}/status` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json
```

**Request Body:**

```json
{
    "opportunity_status": "completed"
}
```

**Response (200):**

```json
{
    "message": "Opportunity status updated successfully",
    "opportunity": {
        "opportunity_id": 1,
        "title": "Community Service Event",
        "opportunity_status": "completed",
        "enable_qr_attendance": true,
        "generate_certificates": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

---

### **7. Get Opportunity Statistics (Admin Only)**

**GET** `/admin/opportunities/statistics` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "statistics": {
        "total_opportunities": 10,
        "active_opportunities": 5,
        "completed_opportunities": 3,
        "cancelled_opportunities": 2,
        "total_applications": 150,
        "pending_applications": 25,
        "approved_applications": 100
    }
}
```

---

### **8. QR Code Scan (Public)**

**GET** `/opportunities/qr/{token}` 🌐

**Response (200):**

```json
{
    "message": "QR code scanned successfully",
    "opportunity": {
        "opportunity_id": 1,
        "title": "Community Service Event",
        "description": "Help organize community service activities",
        "date": "2025-02-15",
        "location": "Riyadh",
        "country": "Saudi Arabia",
        "category": "Volunteering",
        "opportunity_status": "active",
        "qr_token": "random32charactertoken",
        "enable_qr_attendance": true,
        "generate_certificates": true,
        "coordinator_name": "John Doe",
        "coordinator_phone": "+966501234567",
        "coordinator_email": "coordinator@example.com",
        "volunteer_role": "Event Coordinator",
        "volunteering_hours": 8,
        "image_file": "opportunities/images/filename.jpg",
        "image_url": "http://127.0.0.1:8000/storage/opportunities/images/filename.jpg"
    }
}
```

---

## 🎯 **APPLICATION OPPORTUNITY ENDPOINTS**

---

### **1. Get Students for Invitation (Admin Only)**

**GET** `/admin/opportunities/students/for-invitation` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "students": [
        {
            "student_id": 1,
            "name": "أحمد محمد",
            "email": "ahmed@example.com",
            "scholarship_id": 1,
            "scholarship_name": "King Abdullah Scholarship"
        }
    ]
}
```

---

### **2. Invite Students to Opportunity (Admin Only)**

**POST** `/admin/opportunities/{opportunityId}/invite` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json
```

**Request Body:**

```json
{
    "student_ids": [1, 2, 3, 4, 5]
}
```

**Response (201):**

```json
{
    "message": "Invitations sent successfully",
    "invited_count": 3,
    "already_invited_count": 2,
    "applications": [
        {
            "application_opportunity_id": "opp_0000001",
            "student_id": 1,
            "ar_name": "أحمد محمد",
            "email": "ahmed@example.com",
            "status": "invite"
        }
    ],
    "already_invited_student_ids": [
        {
            "application_opportunity_id": "opp_0000002",
            "student_id": 2,
            "ar_name": "فاطمة علي",
            "email": "fatima@example.com",
            "status": "invite"
        }
    ],
    "all_opportunity_applications": [
        {
            "application_opportunity_id": "opp_0000001",
            "student_id": 1,
            "ar_name": "أحمد محمد",
            "email": "ahmed@example.com",
            "status": "invite"
        }
    ]
}
```

---

### **3. Accept Invitation (Student Only)**

**PATCH** `/student/opportunities/applications/{applicationId}/accept` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "message": "Invitation accepted successfully",
    "application": {
        "application_opportunity_id": "opp_0000001",
        "application_status": "accepted",
        "certificate_token": null,
        "comment": null,
        "excuse_reason": null,
        "excuse_file": null,
        "attendece_mark": null,
        "student_id": 1,
        "opportunity_id": 1,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

---

### **4. Reject Invitation with Excuse (Student Only)**

**POST** `/student/opportunities/applications/{applicationId}/reject` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: multipart/form-data
```

**Request Body (Form Data):**

```
excuse_reason: "I have a prior commitment on that date"
excuse_file: [FILE] (optional, max 5MB, pdf/doc/docx/jpg/jpeg/png)
```

**Response (200):**

```json
{
    "message": "Invitation rejected with excuse",
    "application": {
        "application_opportunity_id": "opp_0000001",
        "application_status": "excuse",
        "certificate_token": null,
        "comment": null,
        "excuse_reason": "I have a prior commitment on that date",
        "excuse_file": "opportunity_applications/excuses/filename.pdf",
        "attendece_mark": null,
        "student_id": 1,
        "opportunity_id": 1,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

---

### **5. Mark QR Attendance (Student Only)**

**PATCH** `/student/opportunities/applications/{applicationId}/attendance` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json
```

**Request Body:**

```json
{
    "email": "student@example.com",
    "password": "password123"
}
```

**Response (200):**

```json
{
    "message": "Attendance marked successfully! Certificate is now available.",
    "application": {
        "application_opportunity_id": "opp_0000001",
        "application_status": "attend",
        "certificate_token": "random32charactertoken",
        "comment": null,
        "excuse_reason": null,
        "excuse_file": null,
        "attendece_mark": null,
        "student_id": 1,
        "opportunity_id": 1,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    },
    "certificate_token": "random32charactertoken"
}
```

---

### **6. Get My Applications (Student Only)**

**GET** `/student/opportunities/applications` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "applications": [
        {
            "application_opportunity_id": "opp_0000001",
            "application_status": "accepted",
            "certificate_token": null,
            "comment": null,
            "excuse_reason": null,
            "excuse_file": null,
            "attendece_mark": null,
            "student_id": 1,
            "opportunity_id": 1,
            "created_at": "2025-01-13T10:00:00.000000Z",
            "updated_at": "2025-01-13T10:00:00.000000Z",
            "opportunity": {
                "opportunity_id": 1,
                "title": "Community Service Event",
                "date": "2025-02-15",
                "location": "Riyadh"
            }
        }
    ]
}
```

---

### **7. Get My Opportunities (Student Only)**

**GET** `/student/opportunities` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "student": {
        "student_id": 1,
        "name": "أحمد محمد",
        "email": "ahmed@example.com"
    },
    "opportunities": [
        {
            "opportunity_id": 1,
            "title": "Community Service Event",
            "description": "Help organize community service activities",
            "date": "2025-02-15",
            "location": "Riyadh",
            "country": "Saudi Arabia",
            "category": "Volunteering",
            "opportunity_status": "active",
            "start_date": "2025-02-15",
            "end_date": "2025-02-16",
            "volunteer_role": "Event Coordinator",
            "volunteering_hours": 8,
            "image_file": "opportunities/images/filename.jpg",
            "image_url": "http://127.0.0.1:8000/storage/opportunities/images/filename.jpg",
            "enrollment_text": "25 enrolled",
            "application_status": "accepted",
            "application_id": "opp_0000001"
        }
    ],
    "total_opportunities": 1
}
```

---

### **8. Get Opportunity by ID (Authenticated Users)**

**GET** `/opportunities/{opportunityId}` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "opportunity": {
        "opportunity_id": 1,
        "title": "Community Service Event",
        "description": "Help organize community service activities",
        "date": "2025-02-15",
        "location": "Riyadh",
        "country": "Saudi Arabia",
        "category": "Volunteering",
        "opportunity_status": "active",
        "start_date": "2025-02-15",
        "end_date": "2025-02-16",
        "volunteer_role": "Event Coordinator",
        "volunteering_hours": 8,
        "enable_qr_attendance": true,
        "generate_certificates": true,
        "coordinator": {
            "name": "John Doe",
            "phone": "+966501234567",
            "email": "coordinator@example.com"
        },
        "image_file": "opportunities/images/filename.jpg",
        "image_url": "http://127.0.0.1:8000/storage/opportunities/images/filename.jpg",
        "qr_url": "random32charactertoken",
        "enrollment_count": 25,
        "total_applications": 30,
        "enrollment_text": "25 enrolled",
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

---

### **9. Get Opportunity Applications (Admin Only)**

**GET** `/admin/opportunities/{opportunityId}/applications` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "opportunity": {
        "opportunity_id": 1,
        "title": "Community Service Event"
    },
    "applications": [
        {
            "application_id": "opp_0000001",
            "student_id": 1,
            "name": "أحمد محمد",
            "email": "ahmed@example.com",
            "status": "accepted",
            "scholarship_id": 1,
            "scholarship_name": "King Abdullah Scholarship"
        }
    ]
}
```

---

### **10. Get My Opportunity Application (Student Only)**

**GET** `/student/opportunities/{opportunityId}/my-application` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "application": {
        "application_id": "opp_0000001",
        "student_id": 1,
        "opportunity_id": 1,
        "application_status": "accepted",
        "excuse_reason": null,
        "excuse_file": null,
        "excuse_file_url": null,
        "certificate_token": null,
        "comment": null,
        "attendece_mark": null,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z",
        "student": {
            "student_id": 1,
            "name": "أحمد محمد",
            "email": "ahmed@example.com",
            "specialization": "Computer Science",
            "scholarship_name": "King Abdullah Scholarship"
        },
        "opportunity": {
            "opportunity_id": 1,
            "title": "Community Service Event",
            "description": "Help organize community service activities",
            "date": "2025-02-15",
            "location": "Riyadh",
            "country": "Saudi Arabia",
            "category": "Volunteering",
            "opportunity_status": "active",
            "start_date": "2025-02-15",
            "end_date": "2025-02-16",
            "volunteer_role": "Event Coordinator",
            "volunteering_hours": 8,
            "enable_qr_attendance": true,
            "generate_certificates": true,
            "coordinator_name": "John Doe",
            "coordinator_phone": "+966501234567",
            "coordinator_email": "coordinator@example.com",
            "image_file": "opportunities/images/filename.jpg",
            "image_url": "http://127.0.0.1:8000/storage/opportunities/images/filename.jpg",
            "qr_url": "random32charactertoken"
        }
    }
}
```

---

### **11. QR Attendance with Token (Student Only)**

**POST** `/opportunities/qr/{token}/attendance` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json
```

**Request Body:**

```json
{
    "student_id": 1
}
```

**Response (200):**

```json
{
    "message": "Attendance marked successfully! Certificate is now available.",
    "application": {
        "application_opportunity_id": "opp_0000001",
        "application_status": "attend",
        "certificate_token": "random32charactertoken",
        "comment": null,
        "excuse_reason": null,
        "excuse_file": null,
        "attendece_mark": null,
        "student_id": 1,
        "opportunity_id": 1,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    },
    "student": {
        "student_id": 1,
        "name": "أحمد محمد",
        "email": "ahmed@example.com"
    },
    "certificate_token": "random32charactertoken"
}
```

---

### **12. Mark Attendance via QR (Student Only)**

**POST** `/opportunities/qr/{token}/mark-attendance` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "success": true,
    "message": "Attendance marked successfully! Welcome to the opportunity.",
    "opportunity": {
        "opportunity_id": 1,
        "title": "Community Service Event",
        "date": "2025-02-15",
        "location": "Riyadh",
        "volunteer_role": "Event Coordinator"
    },
    "student": {
        "student_id": 1,
        "name": "أحمد محمد",
        "email": "ahmed@example.com"
    },
    "application": {
        "application_id": "opp_0000001",
        "status": "attend",
        "marked_at": "2025-01-13T10:00:00.000000Z"
    },
    "certificate_token": "random32charactertoken"
}
```

---

### **13. Get Opportunity Attendance (Admin Only)**

**GET** `/admin/opportunities/{opportunityId}/attendance` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "opportunity": {
        "opportunity_id": 1,
        "title": "Community Service Event",
        "opportunity_status": "active",
        "date": "2025-02-15",
        "location": "Riyadh",
        "volunteer_role": "Event Coordinator"
    },
    "applications": [
        {
            "application_id": "opp_0000001",
            "student_id": 1,
            "name": "أحمد محمد",
            "email": "ahmed@example.com",
            "university": "King Saud University",
            "status": "attend",
            "scholarship_name": "King Abdullah Scholarship",
            "created_at": "2025-01-13T10:00:00.000000Z",
            "updated_at": "2025-01-13T10:00:00.000000Z"
        }
    ],
    "statistics": {
        "total_accepted": 20,
        "total_attended": 15,
        "total_eligible": 20
    }
}
```

---

### **14. Update Application Status (Admin Only)**

**PATCH** `/admin/opportunities/{opportunityId}/applications/status` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
Content-Type: application/json
```

**Request Body:**

```json
{
    "applications": [
        {
            "application_id": "opp_0000001",
            "status": "attend"
        },
        {
            "application_id": "opp_0000002",
            "status": "accepted"
        }
    ]
}
```

**Response (200):**

```json
{
    "message": "Application statuses updated successfully",
    "updated_count": 2,
    "error_count": 0,
    "updated_applications": [
        {
            "application_id": "opp_0000001",
            "student_id": 1,
            "name": "أحمد محمد",
            "email": "ahmed@example.com",
            "old_status": "accepted",
            "new_status": "attend",
            "updated_at": "2025-01-13T10:00:00.000000Z",
            "certificate_token": "random32charactertoken",
            "certificate_generated": true
        }
    ],
    "errors": []
}
```

---

### **15. Generate Missing Certificate Tokens (Admin Only)**

**POST** `/admin/opportunities/{opportunityId}/generate-certificates` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "message": "Certificate tokens generated successfully",
    "opportunity_id": 1,
    "opportunity_title": "Community Service Event",
    "updated_count": 5,
    "updated_applications": [
        {
            "application_id": "opp_0000001",
            "student_id": 1,
            "certificate_token": "random32charactertoken",
            "updated_at": "2025-01-13T10:00:00.000000Z"
        }
    ]
}
```

---

### **16. Get Excuse Details (Admin Only)**

**GET** `/admin/opportunities/applications/{applicationId}/excuse` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "application": {
        "application_id": "opp_0000001",
        "excuse_reason": "I have a prior commitment on that date",
        "excuse_file": "opportunity_applications/excuses/filename.pdf",
        "excuse_file_url": "http://127.0.0.1:8000/storage/opportunity_applications/excuses/filename.pdf",
        "email": "ahmed@example.com",
        "ar_name": "أحمد محمد",
        "status": "excuse",
        "opportunity_title": "Community Service Event",
        "submitted_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

---

### **17. Approve Excuse (Admin Only)**

**PATCH** `/admin/opportunities/applications/{applicationId}/approve-excuse` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "message": "Excuse approved successfully",
    "application": {
        "application_opportunity_id": "opp_0000001",
        "application_status": "approved_excuse",
        "certificate_token": null,
        "comment": null,
        "excuse_reason": "I have a prior commitment on that date",
        "excuse_file": "opportunity_applications/excuses/filename.pdf",
        "attendece_mark": null,
        "student_id": 1,
        "opportunity_id": 1,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

---

### **18. Reject Excuse (Admin Only)**

**PATCH** `/admin/opportunities/applications/{applicationId}/reject-excuse` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "message": "Excuse rejected successfully",
    "application": {
        "application_opportunity_id": "opp_0000001",
        "application_status": "rejected_excuse",
        "certificate_token": null,
        "comment": null,
        "excuse_reason": "I have a prior commitment on that date",
        "excuse_file": "opportunity_applications/excuses/filename.pdf",
        "attendece_mark": null,
        "student_id": 1,
        "opportunity_id": 1,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

---

### **19. Delete Application (Admin Only)**

**DELETE** `/admin/opportunities/applications/{applicationId}` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "message": "Application deleted successfully",
    "deleted_application": {
        "application_opportunity_id": "opp_0000001",
        "student_id": 1,
        "ar_name": "أحمد محمد",
        "email": "ahmed@example.com",
        "opportunity_title": "Community Service Event"
    }
}
```

---

### **20. Get Certificate (Public)**

**GET** `/certificates/{token}` 🌐

**Response (200):**

```json
{
    "certificate": {
        "application_id": "opp_0000001",
        "student_name": "أحمد محمد",
        "opportunity_title": "Community Service Event",
        "opportunity_date": "2025-02-15",
        "attendance_date": "2025-01-13T10:00:00.000000Z",
        "opportunity_location": "Riyadh",
        "opportunity_country": "Saudi Arabia",
        "volunteer_role": "Event Coordinator",
        "volunteering_hours": 8,
        "certificate_token": "random32charactertoken",
        "issued_at": "2025-01-13T10:00:00.000000Z",
        "opportunity_status": "completed"
    }
}
```

---

## 🔑 **Status Values**

### **Opportunity Status:**

-   `active` - Opportunity is currently active
-   `inactive` - Opportunity is temporarily inactive
-   `completed` - Opportunity has been completed
-   `cancelled` - Opportunity has been cancelled

### **Application Status:**

-   `invite` - Student has been invited
-   `accepted` - Student accepted the invitation
-   `excuse` - Student rejected with excuse
-   `approved_excuse` - Admin approved the excuse
-   `rejected_excuse` - Admin rejected the excuse
-   `attend` - Student marked attendance
-   `rejected` - Application was rejected

---

## 🔒 **Authentication Notes**

-   **🔒** = Requires Bearer Token Authentication
-   **🌐** = Public endpoint (no authentication required)
-   All admin endpoints require `role: admin`
-   All student endpoints require `role: student`
-   Base URL: `http://127.0.0.1:8000/api/v1`

---

## 📝 **Error Responses**

### **Validation Error (422)**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "title": ["The title field is required."],
        "date": ["The date must be a valid date."]
    }
}
```

### **Authentication Error (401)**

```json
{
    "message": "Unauthenticated."
}
```

### **Authorization Error (403)**

```json
{
    "message": "Only admins can view opportunities"
}
```

### **Not Found Error (404)**

```json
{
    "message": "Opportunity not found"
}
```

### **Server Error (500)**

```json
{
    "message": "Failed to create opportunity",
    "error": "Database connection failed"
}
```

---

## 🚀 **Quick Start Guide**

### **1. Authentication Flow:**

1. **POST** `/login` → Get token
2. Use token in `Authorization: Bearer TOKEN` header
3. **POST** `/logout` → Revoke token

### **2. Admin Workflow:**

1. Create opportunity: `POST /admin/opportunities`
2. Invite students: `POST /admin/opportunities/{id}/invite`
3. View applications: `GET /admin/opportunities/{id}/applications`
4. Manage attendance: `GET /admin/opportunities/{id}/attendance`

### **3. Student Workflow:**

1. View opportunities: `GET /student/opportunities`
2. Accept invitation: `PATCH /student/opportunities/applications/{id}/accept`
3. Mark attendance: `PATCH /student/opportunities/applications/{id}/attendance`
4. View certificate: `GET /certificates/{token}`

---

## 📋 **Common Headers**

```
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN_HERE
Accept: application/json
```

---

## 🔧 **File Upload Guidelines**

-   **Image files**: Max 5MB, formats: jpeg, png, jpg, gif, webp
-   **Document files**: Max 5MB, formats: pdf, doc, docx, jpg, jpeg, png
-   Use `multipart/form-data` for file uploads
-   Files are stored in `storage/app/public/` directory

