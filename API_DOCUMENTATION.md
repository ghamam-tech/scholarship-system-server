# Scholarship System API Documentation

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

## 🔐 Authentication Endpoints

### 1. Login

**POST** `/login`

**Request Body:**

```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

**Response (200):**

```json
{
    "message": "Login successful",
    "user": {
        "user_id": 1,
        "email": "user@example.com",
        "role": "admin",
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    },
    "token": "1|abcdef123456789..."
}
```

### 2. Logout

**POST** `/logout` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "message": "Logout successful"
}
```

### 3. Get Current User

**GET** `/me` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "user_id": 1,
    "email": "user@example.com",
    "role": "admin",
    "created_at": "2025-01-13T10:00:00.000000Z",
    "updated_at": "2025-01-13T10:00:00.000000Z",
    "admin": {
        "admin_id": 1,
        "user_id": 1,
        "name": "Admin Name",
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

---

## 👤 User Profile Endpoints

### 1. Get Profile

**GET** `/profile` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Response (200):**

```json
{
    "user_id": 1,
    "email": "user@example.com",
    "role": "applicant",
    "created_at": "2025-01-13T10:00:00.000000Z",
    "updated_at": "2025-01-13T10:00:00.000000Z",
    "applicant": {
        "applicant_id": 1,
        "user_id": 1,
        "ar_name": "الاسم بالعربية",
        "en_name": "Name in English",
        "nationality": "Saudi",
        "gender": "male",
        "place_of_birth": "Riyadh",
        "phone": "+966501234567",
        "passport_number": "A1234567",
        "date_of_birth": "1995-01-01",
        "parent_contact_name": "Parent Name",
        "parent_contact_phone": "+966501234568",
        "residence_country": "Saudi Arabia",
        "passport_copy_url": "https://example.com/passport.pdf",
        "volunteering_certificate_url": "https://example.com/volunteer.pdf",
        "language": "Arabic",
        "is_studied_in_saudi": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

### 2. Update Profile

**PUT** `/profile` 🔒

**Headers:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**Request Body:**

```json
{
    "email": "newemail@example.com",
    "password": "newpassword123"
}
```

**Response (200):**

```json
{
    "message": "Profile updated successfully",
    "user": {
        "user_id": 1,
        "email": "newemail@example.com",
        "role": "applicant",
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

---

## 🎓 Applicant Endpoints

### 1. Register Applicant

**POST** `/applicant/register`

**Request Body:**

```json
{
    "email": "applicant@example.com",
    "password": "password123",
    "ar_name": "الاسم بالعربية",
    "en_name": "Name in English",
    "nationality": "Saudi",
    "gender": "male",
    "place_of_birth": "Riyadh",
    "phone": "+966501234567",
    "passport_number": "A1234567",
    "date_of_birth": "1995-01-01",
    "parent_contact_name": "Parent Name",
    "parent_contact_phone": "+966501234568",
    "residence_country": "Saudi Arabia",
    "passport_copy_url": "https://example.com/passport.pdf",
    "volunteering_certificate_url": "https://example.com/volunteer.pdf",
    "language": "Arabic",
    "is_studied_in_saudi": true
}
```

**Response (201):**

```json
{
    "message": "Applicant created successfully",
    "user": {
        "user_id": 2,
        "email": "applicant@example.com",
        "role": "applicant",
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    },
    "applicant": {
        "applicant_id": 1,
        "user_id": 2,
        "ar_name": "الاسم بالعربية",
        "en_name": "Name in English",
        "nationality": "Saudi",
        "gender": "male",
        "place_of_birth": "Riyadh",
        "phone": "+966501234567",
        "passport_number": "A1234567",
        "date_of_birth": "1995-01-01",
        "parent_contact_name": "Parent Name",
        "parent_contact_phone": "+966501234568",
        "residence_country": "Saudi Arabia",
        "passport_copy_url": "https://example.com/passport.pdf",
        "volunteering_certificate_url": "https://example.com/volunteer.pdf",
        "language": "Arabic",
        "is_studied_in_saudi": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

### 2. Get All Applicants

**GET** `/applicant`

**Response (200):**

```json
[
    {
        "applicant_id": 1,
        "user_id": 2,
        "ar_name": "الاسم بالعربية",
        "en_name": "Name in English",
        "nationality": "Saudi",
        "gender": "male",
        "place_of_birth": "Riyadh",
        "phone": "+966501234567",
        "passport_number": "A1234567",
        "date_of_birth": "1995-01-01",
        "parent_contact_name": "Parent Name",
        "parent_contact_phone": "+966501234568",
        "residence_country": "Saudi Arabia",
        "passport_copy_url": "https://example.com/passport.pdf",
        "volunteering_certificate_url": "https://example.com/volunteer.pdf",
        "language": "Arabic",
        "is_studied_in_saudi": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z",
        "user": {
            "user_id": 2,
            "email": "applicant@example.com",
            "role": "applicant",
            "created_at": "2025-01-13T10:00:00.000000Z",
            "updated_at": "2025-01-13T10:00:00.000000Z"
        }
    }
]
```

### 3. Get Single Applicant

**GET** `/applicant/{id}`

**Response (200):**

```json
{
    "applicant_id": 1,
    "user_id": 2,
    "ar_name": "الاسم بالعربية",
    "en_name": "Name in English",
    "nationality": "Saudi",
    "gender": "male",
    "place_of_birth": "Riyadh",
    "phone": "+966501234567",
    "passport_number": "A1234567",
    "date_of_birth": "1995-01-01",
    "parent_contact_name": "Parent Name",
    "parent_contact_phone": "+966501234568",
    "residence_country": "Saudi Arabia",
    "passport_copy_url": "https://example.com/passport.pdf",
    "volunteering_certificate_url": "https://example.com/volunteer.pdf",
    "language": "Arabic",
    "is_studied_in_saudi": true,
    "created_at": "2025-01-13T10:00:00.000000Z",
    "updated_at": "2025-01-13T10:00:00.000000Z",
    "user": {
        "user_id": 2,
        "email": "applicant@example.com",
        "role": "applicant",
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

### 4. Update Applicant

**PUT** `/applicant/{id}`

**Request Body:**

```json
{
    "email": "updated@example.com",
    "ar_name": "الاسم المحدث",
    "en_name": "Updated Name",
    "phone": "+966501234569"
}
```

**Response (200):**

```json
{
    "message": "Applicant updated successfully",
    "applicant": {
        "applicant_id": 1,
        "user_id": 2,
        "ar_name": "الاسم المحدث",
        "en_name": "Updated Name",
        "nationality": "Saudi",
        "gender": "male",
        "place_of_birth": "Riyadh",
        "phone": "+966501234569",
        "passport_number": "A1234567",
        "date_of_birth": "1995-01-01",
        "parent_contact_name": "Parent Name",
        "parent_contact_phone": "+966501234568",
        "residence_country": "Saudi Arabia",
        "passport_copy_url": "https://example.com/passport.pdf",
        "volunteering_certificate_url": "https://example.com/volunteer.pdf",
        "language": "Arabic",
        "is_studied_in_saudi": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z",
        "user": {
            "user_id": 2,
            "email": "updated@example.com",
            "role": "applicant",
            "created_at": "2025-01-13T10:00:00.000000Z",
            "updated_at": "2025-01-13T10:00:00.000000Z"
        }
    }
}
```

### 5. Delete Applicant

**DELETE** `/applicant/{id}`

**Response (200):**

```json
{
    "message": "Applicant deleted successfully"
}
```

---

## 👨‍💼 Admin Endpoints

### 1. Get All Admins

**GET** `/admins`

**Response (200):**

```json
[
    {
        "admin_id": 1,
        "user_id": 1,
        "name": "Admin Name",
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z",
        "user": {
            "user_id": 1,
            "email": "admin@example.com",
            "role": "admin",
            "created_at": "2025-01-13T10:00:00.000000Z",
            "updated_at": "2025-01-13T10:00:00.000000Z"
        }
    }
]
```

### 2. Create Admin

**POST** `/admins`

**Request Body:**

```json
{
    "name": "New Admin",
    "email": "newadmin@example.com",
    "password": "password123"
}
```

**Response (201):**

```json
{
    "message": "Admin created successfully",
    "user": {
        "user_id": 3,
        "email": "newadmin@example.com",
        "role": "admin",
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    },
    "admin": {
        "admin_id": 2,
        "user_id": 3,
        "name": "New Admin",
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

### 3. Get Single Admin

**GET** `/admins/{id}`

**Response (200):**

```json
{
    "admin_id": 1,
    "user_id": 1,
    "name": "Admin Name",
    "created_at": "2025-01-13T10:00:00.000000Z",
    "updated_at": "2025-01-13T10:00:00.000000Z",
    "user": {
        "user_id": 1,
        "email": "admin@example.com",
        "role": "admin",
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

### 4. Update Admin

**PUT** `/admins/{id}`

**Request Body:**

```json
{
    "name": "Updated Admin Name",
    "email": "updatedadmin@example.com"
}
```

**Response (200):**

```json
{
    "message": "Admin updated successfully",
    "admin": {
        "admin_id": 1,
        "user_id": 1,
        "name": "Updated Admin Name",
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z",
        "user": {
            "user_id": 1,
            "email": "updatedadmin@example.com",
            "role": "admin",
            "created_at": "2025-01-13T10:00:00.000000Z",
            "updated_at": "2025-01-13T10:00:00.000000Z"
        }
    }
}
```

### 5. Delete Admin

**DELETE** `/admins/{id}`

**Response (200):**

```json
{
    "message": "Admin deleted successfully"
}
```

---

## 🏢 Sponsor Endpoints

### 1. Get All Sponsors

**GET** `/sponsors`

**Response (200):**

```json
[
    {
        "sponsor_id": 1,
        "user_id": 4,
        "name": "Sponsor Company",
        "country": "Saudi Arabia",
        "is_active": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z",
        "user": {
            "user_id": 4,
            "email": "sponsor@example.com",
            "role": "sponsor",
            "created_at": "2025-01-13T10:00:00.000000Z",
            "updated_at": "2025-01-13T10:00:00.000000Z"
        }
    }
]
```

### 2. Create Sponsor

**POST** `/sponsors`

**Request Body:**

```json
{
    "name": "New Sponsor",
    "country": "UAE",
    "is_active": true,
    "email": "newsponsor@example.com",
    "password": "password123"
}
```

**Response (201):**

```json
{
    "message": "Sponsor created successfully",
    "user": {
        "user_id": 5,
        "email": "newsponsor@example.com",
        "role": "sponsor",
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    },
    "sponsor": {
        "sponsor_id": 2,
        "user_id": 5,
        "name": "New Sponsor",
        "country": "UAE",
        "is_active": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

### 3. Create Sponsor (Alternative)

**POST** `/sponsor/create`

**Request Body:**

```json
{
    "name": "Alternative Sponsor",
    "country": "Kuwait",
    "is_active": true,
    "email": "altsponsor@example.com",
    "password": "password123"
}
```

**Response (201):**

```json
{
    "message": "Sponsor created successfully",
    "user": {
        "user_id": 6,
        "email": "altsponsor@example.com",
        "role": "sponsor",
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    },
    "sponsor": {
        "sponsor_id": 3,
        "user_id": 6,
        "name": "Alternative Sponsor",
        "country": "Kuwait",
        "is_active": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

### 4. Get Single Sponsor

**GET** `/sponsors/{id}`

**Response (200):**

```json
{
    "sponsor_id": 1,
    "user_id": 4,
    "name": "Sponsor Company",
    "country": "Saudi Arabia",
    "is_active": true,
    "created_at": "2025-01-13T10:00:00.000000Z",
    "updated_at": "2025-01-13T10:00:00.000000Z",
    "user": {
        "user_id": 4,
        "email": "sponsor@example.com",
        "role": "sponsor",
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

### 5. Update Sponsor

**PUT** `/sponsors/{id}`

**Request Body:**

```json
{
    "name": "Updated Sponsor",
    "country": "Qatar",
    "is_active": false,
    "email": "updatedsponsor@example.com"
}
```

**Response (200):**

```json
{
    "message": "Sponsor updated successfully",
    "sponsor": {
        "sponsor_id": 1,
        "user_id": 4,
        "name": "Updated Sponsor",
        "country": "Qatar",
        "is_active": false,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z",
        "user": {
            "user_id": 4,
            "email": "updatedsponsor@example.com",
            "role": "sponsor",
            "created_at": "2025-01-13T10:00:00.000000Z",
            "updated_at": "2025-01-13T10:00:00.000000Z"
        }
    }
}
```

### 6. Delete Sponsor

**DELETE** `/sponsors/{id}`

**Response (200):**

```json
{
    "message": "Sponsor deleted successfully"
}
```

---

## 🌍 Country Endpoints

### 1. Get All Countries

**GET** `/country`

**Response (200):**

```json
[
    {
        "country_id": 1,
        "country_name": "Saudi Arabia",
        "country_code": "SA",
        "is_active": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    },
    {
        "country_id": 2,
        "country_name": "United Arab Emirates",
        "country_code": "AE",
        "is_active": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
]
```

### 2. Create Country

**POST** `/country`

**Request Body:**

```json
{
    "country_name": "Kuwait",
    "country_code": "KW",
    "is_active": true
}
```

**Response (201):**

```json
{
    "message": "Country created successfully",
    "country": {
        "country_id": 3,
        "country_name": "Kuwait",
        "country_code": "KW",
        "is_active": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

### 3. Get Single Country

**GET** `/country/{id}`

**Response (200):**

```json
{
    "country_id": 1,
    "country_name": "Saudi Arabia",
    "country_code": "SA",
    "is_active": true,
    "created_at": "2025-01-13T10:00:00.000000Z",
    "updated_at": "2025-01-13T10:00:00.000000Z",
    "universities": [
        {
            "university_id": 1,
            "university_name": "King Saud University",
            "city": "Riyadh",
            "country_id": 1,
            "is_active": true,
            "created_at": "2025-01-13T10:00:00.000000Z",
            "updated_at": "2025-01-13T10:00:00.000000Z"
        }
    ]
}
```

### 4. Update Country

**PUT** `/country/{id}`

**Request Body:**

```json
{
    "country_name": "Kingdom of Saudi Arabia",
    "is_active": false
}
```

**Response (200):**

```json
{
    "message": "Country updated successfully",
    "country": {
        "country_id": 1,
        "country_name": "Kingdom of Saudi Arabia",
        "country_code": "SA",
        "is_active": false,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

### 5. Delete Country

**DELETE** `/country/{id}`

**Response (200):**

```json
{
    "message": "Country deleted successfully"
}
```

---

## 🏫 University Endpoints

### 1. Get All Universities

**GET** `/university`

**Response (200):**

```json
[
    {
        "university_id": 1,
        "university_name": "King Saud University",
        "city": "Riyadh",
        "country_id": 1,
        "is_active": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z",
        "country": {
            "country_id": 1,
            "country_name": "Saudi Arabia",
            "country_code": "SA",
            "is_active": true,
            "created_at": "2025-01-13T10:00:00.000000Z",
            "updated_at": "2025-01-13T10:00:00.000000Z"
        }
    }
]
```

### 2. Create University

**POST** `/university`

**Request Body:**

```json
{
    "university_name": "King Abdulaziz University",
    "city": "Jeddah",
    "country_id": 1,
    "is_active": true
}
```

**Response (201):**

```json
{
    "message": "University created successfully",
    "university": {
        "university_id": 2,
        "university_name": "King Abdulaziz University",
        "city": "Jeddah",
        "country_id": 1,
        "is_active": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z",
        "country": {
            "country_id": 1,
            "country_name": "Saudi Arabia",
            "country_code": "SA",
            "is_active": true,
            "created_at": "2025-01-13T10:00:00.000000Z",
            "updated_at": "2025-01-13T10:00:00.000000Z"
        }
    }
}
```

### 3. Get Single University

**GET** `/university/{id}`

**Response (200):**

```json
{
    "university_id": 1,
    "university_name": "King Saud University",
    "city": "Riyadh",
    "country_id": 1,
    "is_active": true,
    "created_at": "2025-01-13T10:00:00.000000Z",
    "updated_at": "2025-01-13T10:00:00.000000Z",
    "country": {
        "country_id": 1,
        "country_name": "Saudi Arabia",
        "country_code": "SA",
        "is_active": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

### 4. Update University

**PUT** `/university/{id}`

**Request Body:**

```json
{
    "university_name": "King Saud University - Updated",
    "city": "Riyadh",
    "is_active": false
}
```

**Response (200):**

```json
{
    "message": "University updated successfully",
    "university": {
        "university_id": 1,
        "university_name": "King Saud University - Updated",
        "city": "Riyadh",
        "country_id": 1,
        "is_active": false,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z",
        "country": {
            "country_id": 1,
            "country_name": "Saudi Arabia",
            "country_code": "SA",
            "is_active": true,
            "created_at": "2025-01-13T10:00:00.000000Z",
            "updated_at": "2025-01-13T10:00:00.000000Z"
        }
    }
}
```

### 5. Delete University

**DELETE** `/university/{id}`

**Response (200):**

```json
{
    "message": "University deleted successfully"
}
```

---

## 📚 Specialization Endpoints

### 1. Get All Specializations

**GET** `/specialization`

**Response (200):**

```json
[
    {
        "specialization_id": 1,
        "specialization_name": "Computer Science",
        "faculty_name": "College of Computer and Information Sciences",
        "is_active": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    },
    {
        "specialization_id": 2,
        "specialization_name": "Medicine",
        "faculty_name": "College of Medicine",
        "is_active": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
]
```

### 2. Create Specialization

**POST** `/specialization`

**Request Body:**

```json
{
    "specialization_name": "Engineering",
    "faculty_name": "College of Engineering",
    "is_active": true
}
```

**Response (201):**

```json
{
    "message": "Specialization created successfully",
    "specialization": {
        "specialization_id": 3,
        "specialization_name": "Engineering",
        "faculty_name": "College of Engineering",
        "is_active": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

### 3. Get Single Specialization

**GET** `/specialization/{id}`

**Response (200):**

```json
{
    "specialization_id": 1,
    "specialization_name": "Computer Science",
    "faculty_name": "College of Computer and Information Sciences",
    "is_active": true,
    "created_at": "2025-01-13T10:00:00.000000Z",
    "updated_at": "2025-01-13T10:00:00.000000Z"
}
```

### 4. Update Specialization

**PUT** `/specialization/{id}`

**Request Body:**

```json
{
    "specialization_name": "Computer Science and Engineering",
    "faculty_name": "College of Computer and Information Sciences"
}
```

**Response (200):**

```json
{
    "message": "Specialization updated successfully",
    "specialization": {
        "specialization_id": 1,
        "specialization_name": "Computer Science and Engineering",
        "faculty_name": "College of Computer and Information Sciences",
        "is_active": true,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

### 5. Delete Specialization

**DELETE** `/specialization/{id}`

**Response (200):**

```json
{
    "message": "Specialization deleted successfully"
}
```

---

## 🎓 Scholarship Endpoints

### 1. Get All Scholarships

**GET** `/scholarship`

**Response (200):**

```json
[
    {
        "scholarship_id": 1,
        "scholarship_name": "King Abdullah Scholarship Program",
        "scholarship_type": "Full Scholarship",
        "allowed_program": "Bachelor's Degree",
        "total_beneficiaries": 100,
        "opening_date": "2025-01-01",
        "closing_date": "2025-12-31",
        "description": "Full scholarship for undergraduate students",
        "is_active": true,
        "is_hidden": false,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
]
```

### 2. Create Scholarship

**POST** `/scholarship`

**Request Body:**

```json
{
    "scholarship_name": "Merit Scholarship",
    "scholarship_type": "Partial Scholarship",
    "allowed_program": "Master's Degree",
    "total_beneficiaries": 50,
    "opening_date": "2025-02-01",
    "closing_date": "2025-11-30",
    "description": "Partial scholarship for graduate students",
    "is_active": true,
    "is_hidden": false
}
```

**Response (201):**

```json
{
    "message": "Scholarship created successfully",
    "scholarship": {
        "scholarship_id": 2,
        "scholarship_name": "Merit Scholarship",
        "scholarship_type": "Partial Scholarship",
        "allowed_program": "Master's Degree",
        "total_beneficiaries": 50,
        "opening_date": "2025-02-01",
        "closing_date": "2025-11-30",
        "description": "Partial scholarship for graduate students",
        "is_active": true,
        "is_hidden": false,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

### 3. Get Single Scholarship

**GET** `/scholarship/{id}`

**Response (200):**

```json
{
    "scholarship_id": 1,
    "scholarship_name": "King Abdullah Scholarship Program",
    "scholarship_type": "Full Scholarship",
    "allowed_program": "Bachelor's Degree",
    "total_beneficiaries": 100,
    "opening_date": "2025-01-01",
    "closing_date": "2025-12-31",
    "description": "Full scholarship for undergraduate students",
    "is_active": true,
    "is_hidden": false,
    "created_at": "2025-01-13T10:00:00.000000Z",
    "updated_at": "2025-01-13T10:00:00.000000Z"
}
```

### 4. Update Scholarship

**PUT** `/scholarship/{id}`

**Request Body:**

```json
{
    "scholarship_name": "King Abdullah Scholarship Program - Updated",
    "total_beneficiaries": 150,
    "description": "Updated full scholarship for undergraduate students"
}
```

**Response (200):**

```json
{
    "message": "Scholarship updated successfully",
    "scholarship": {
        "scholarship_id": 1,
        "scholarship_name": "King Abdullah Scholarship Program - Updated",
        "scholarship_type": "Full Scholarship",
        "allowed_program": "Bachelor's Degree",
        "total_beneficiaries": 150,
        "opening_date": "2025-01-01",
        "closing_date": "2025-12-31",
        "description": "Updated full scholarship for undergraduate students",
        "is_active": true,
        "is_hidden": false,
        "created_at": "2025-01-13T10:00:00.000000Z",
        "updated_at": "2025-01-13T10:00:00.000000Z"
    }
}
```

### 5. Delete Scholarship

**DELETE** `/scholarship/{id}`

**Response (200):**

```json
{
    "message": "Scholarship deleted successfully"
}
```

---

## 📝 Error Responses

### Validation Error (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

### Authentication Error (401)

```json
{
    "message": "Unauthenticated."
}
```

### Not Found Error (404)

```json
{
    "message": "No query results for model [App\\Models\\Applicant] 1"
}
```

### Server Error (500)

```json
{
    "message": "Server Error"
}
```

---

## 🔑 Legend

-   🔒 = Requires Authentication (Bearer Token)
-   🌐 = Public Endpoint (No Authentication Required)
-   📝 = All fields marked as `sometimes` in validation are optional for updates
-   🆔 = Replace `{id}` with the actual ID number in the URL

---

## 📋 Quick Reference

### Authentication Flow:

1. **POST** `/login` → Get token
2. Use token in `Authorization: Bearer TOKEN` header
3. **POST** `/logout` → Revoke token

### User Roles:

-   `admin` - Full system access
-   `applicant` - Student applying for scholarships
-   `sponsor` - Organization providing scholarships

### Common Headers:

```
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN_HERE
```
