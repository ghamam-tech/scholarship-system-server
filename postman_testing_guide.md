# Postman Testing Guide for Complete Profile Endpoint

## Endpoint Details

-   **Method**: POST
-   **URL**: `http://localhost:8000/api/v1/applicant/complete-profile`
-   **Headers**:
    -   `Authorization: Bearer YOUR_TOKEN`
    -   `Content-Type: multipart/form-data` (Postman will set this automatically)

## Step-by-Step Postman Setup

### 1. Set Request Method and URL

-   Method: `POST`
-   URL: `http://localhost:8000/api/v1/applicant/complete-profile`

### 2. Set Headers

-   Go to **Headers** tab
-   Add: `Authorization` = `Bearer YOUR_TOKEN_HERE`

### 3. Set Body (Form Data)

-   Go to **Body** tab
-   Select **form-data** (NOT raw or x-www-form-urlencoded)

### 4. Add Form Fields

#### Personal Info Fields:

| Key                                   | Type | Value                |
| ------------------------------------- | ---- | -------------------- |
| `personal_info[ar_name]`              | Text | `أحمد محمد علي`      |
| `personal_info[en_name]`              | Text | `Ahmed Mohammed Ali` |
| `personal_info[nationality]`          | Text | `Saudi`              |
| `personal_info[gender]`               | Text | `male`               |
| `personal_info[place_of_birth]`       | Text | `Riyadh`             |
| `personal_info[phone]`                | Text | `+966501234567`      |
| `personal_info[passport_number]`      | Text | `A1234567`           |
| `personal_info[date_of_birth]`        | Text | `1995-06-15`         |
| `personal_info[parent_contact_name]`  | Text | `Mohammed Ali Ahmed` |
| `personal_info[parent_contact_phone]` | Text | `+966501234568`      |
| `personal_info[residence_country]`    | Text | `Saudi Arabia`       |
| `personal_info[language]`             | Text | `Arabic`             |
| `personal_info[is_studied_in_saudi]`  | Text | `true`               |
| `personal_info[tahseeli_percentage]`  | Text | `85.5`               |
| `personal_info[qudorat_percentage]`   | Text | `90.0`               |

#### Qualification 1 Fields:

| Key                                                    | Type | Value                           |
| ------------------------------------------------------ | ---- | ------------------------------- |
| `academic_info[qualifications][0][qualification_type]` | Text | `bachelor`                      |
| `academic_info[qualifications][0][institute_name]`     | Text | `King Saud University`          |
| `academic_info[qualifications][0][year_of_graduation]` | Text | `2020`                          |
| `academic_info[qualifications][0][cgpa]`               | Text | `3.8`                           |
| `academic_info[qualifications][0][cgpa_out_of]`        | Text | `4.0`                           |
| `academic_info[qualifications][0][language_of_study]`  | Text | `Arabic`                        |
| `academic_info[qualifications][0][specialization]`     | Text | `Computer Science`              |
| `academic_info[qualifications][0][research_title]`     | Text | `Machine Learning Applications` |

#### Qualification 2 Fields:

| Key                                                    | Type | Value                         |
| ------------------------------------------------------ | ---- | ----------------------------- |
| `academic_info[qualifications][1][qualification_type]` | Text | `master`                      |
| `academic_info[qualifications][1][institute_name]`     | Text | `MIT`                         |
| `academic_info[qualifications][1][year_of_graduation]` | Text | `2022`                        |
| `academic_info[qualifications][1][cgpa]`               | Text | `3.9`                         |
| `academic_info[qualifications][1][cgpa_out_of]`        | Text | `4.0`                         |
| `academic_info[qualifications][1][language_of_study]`  | Text | `English`                     |
| `academic_info[qualifications][1][specialization]`     | Text | `Artificial Intelligence`     |
| `academic_info[qualifications][1][research_title]`     | Text | `Deep Learning in Healthcare` |

#### File Fields (IMPORTANT - Set Type to "File"):

| Key                                               | Type     | Value                        |
| ------------------------------------------------- | -------- | ---------------------------- |
| `passport_copy`                                   | **File** | Select a PDF file            |
| `personal_image`                                  | **File** | Select a JPG/PNG image       |
| `tahsili_file`                                    | **File** | Select a PDF file            |
| `qudorat_file`                                    | **File** | Select a PDF file            |
| `volunteering_certificate`                        | **File** | Select a PDF file (optional) |
| `academic_info[qualifications][0][document_file]` | **File** | Select a PDF file            |
| `academic_info[qualifications][1][document_file]` | **File** | Select a PDF file            |

## Common Issues and Solutions

### Issue 1: Boolean Field Error

**Error**: "The personal info.is studied in saudi field must be true or false."

**Solution**: Make sure `personal_info[is_studied_in_saudi]` is set to exactly `true` or `false` (lowercase, no quotes in Postman)

### Issue 2: File Upload Errors

**Error**: "The passport copy field is required."

**Solutions**:

1. Make sure you select **File** type (not Text) for all file fields
2. Click the dropdown next to the key name and select "File"
3. Click "Select Files" and choose your files
4. Make sure file extensions are: .pdf, .jpg, .jpeg, .png

### Issue 3: Array Field Format

Make sure qualification fields use the exact format:

-   `academic_info[qualifications][0][field_name]`
-   `academic_info[qualifications][1][field_name]`

## Expected Response

### Success (201):

```json
{
    "message": "Profile completed successfully",
    "applicant": {
        "applicant_id": 123,
        "ar_name": "أحمد محمد علي",
        "en_name": "Ahmed Mohammed Ali",
        "qualifications": [
            {
                "qualification_id": 456,
                "qualification_type": "bachelor",
                "institute_name": "King Saud University"
            },
            {
                "qualification_id": 457,
                "qualification_type": "master",
                "institute_name": "MIT"
            }
        ]
    }
}
```

### Validation Error (422):

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

## Test Files

Create these test files for testing:

1. `passport.pdf` - Any PDF file
2. `personal.jpg` - Any JPG image
3. `tahsili.pdf` - Any PDF file
4. `qudorat.pdf` - Any PDF file
5. `volunteering.pdf` - Any PDF file
6. `bachelor_cert.pdf` - Any PDF file
7. `master_cert.pdf` - Any PDF file

## Database Check

After successful request, check:

```sql
SELECT * FROM applicants WHERE user_id = YOUR_USER_ID;
SELECT * FROM qualifications WHERE applicant_id = (SELECT applicant_id FROM applicants WHERE user_id = YOUR_USER_ID);
```
