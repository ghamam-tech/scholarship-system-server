# API Routes Structure

This directory contains all API routes organized by functionality for better maintainability.

## Directory Structure

```
routes/api/v1/
├── index.php                 # Main entry point for all v1 routes
├── public.php               # Public and authenticated routes
├── admin/
│   ├── index.php           # Main admin routes entry point
│   ├── admin.php           # Admin resource routes
│   ├── applicant.php       # Applicant management
│   ├── country.php         # Country management
│   ├── scholarship.php     # Scholarship management
│   ├── specialization.php  # Specialization management
│   ├── university.php      # University management
│   └── sponsor/
│       ├── resource.php    # Sponsor CRUD operations
│       └── create.php      # Sponsor creation
├── auth/
│   ├── login.php          # User login
│   ├── logout.php         # User logout
│   └── me.php             # Get current user
├── applicant/
│   └── register.php       # Applicant registration
└── user/
    └── profile.php        # User profile management
```

## Usage

All routes are automatically prefixed with `/api/v1/` and accessible at:

-   `http://127.0.0.1:8000/api/v1/login`
-   `http://127.0.0.1:8000/api/v1/applicant`
-   `http://127.0.0.1:8000/api/v1/country`
-   etc.

To include all routes, use the main index file:

```php
require_once __DIR__ . '/routes/api/v1/index.php';
```

Or include specific route groups:

```php
// Public routes only
require_once __DIR__ . '/routes/api/v1/public.php';

// Admin routes only
require_once __DIR__ . '/routes/api/v1/admin/index.php';
```

## Benefits

-   **Easy to find**: Each endpoint has its own file
-   **Better organization**: Related routes are grouped together
-   **Maintainable**: Changes to specific endpoints don't affect others
-   **Scalable**: Easy to add new endpoints without cluttering existing files
