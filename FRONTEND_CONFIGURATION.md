# Frontend Configuration Guide

## CORS Configuration ✅

The CORS configuration in `config/cors.php` already includes your frontend URL:

```php
'allowed_origins' => [
    'http://localhost:3000', 
    'http://127.0.0.1:3000',
    'http://localhost:3001',  // ✅ Your frontend URL
    'http://127.0.0.1:3001'
],
```

## Environment Configuration

### 1. Create/Update .env file

Create a `.env` file in your project root with the following configuration:

```env
APP_NAME="Scholarship Management System"
APP_ENV=local
APP_KEY=base64:your-app-key-here
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=scholarship_system
DB_USERNAME=root
DB_PASSWORD=

# Frontend URL Configuration
FRONTEND_URL=http://localhost:3001
```

### 2. Update CORS Configuration (if needed)

If you need to add more frontend URLs, update `config/cors.php`:

```php
'allowed_origins' => [
    'http://localhost:3000', 
    'http://127.0.0.1:3000',
    'http://localhost:3001',  // Your main frontend
    'http://127.0.0.1:3001',
    'https://your-production-domain.com',  // Add production URL
],
```

## API Endpoints for Frontend

### Base URL
- **Development**: `http://localhost:8000/api/v1`
- **Production**: `https://your-api-domain.com/api/v1`

### Key Endpoints for Frontend

#### Authentication
- `POST /api/v1/auth/login` - User login
- `POST /api/v1/auth/register` - User registration
- `POST /api/v1/auth/logout` - User logout

#### Student Endpoints
- `GET /api/v1/student/programs` - Get student's programs
- `GET /api/v1/programs/{programId}` - Get program details
- `GET /api/v1/programs/{programId}/my-application` - Get my application for a program
- `PATCH /api/v1/student/applications/{applicationId}/accept` - Accept invitation
- `POST /api/v1/student/applications/{applicationId}/reject` - Reject invitation

#### Admin Endpoints
- `GET /api/v1/admin/programs/{programId}/applications` - Get program applications
- `POST /api/v1/admin/programs/{programId}/invite` - Invite students
- `DELETE /api/v1/admin/applications/{applicationId}` - Delete application

## Frontend Integration

### 1. API Base URL Configuration

In your frontend (React/Vue/Angular), set the base URL:

```javascript
// config/api.js
const API_BASE_URL = 'http://localhost:8000/api/v1';

export default API_BASE_URL;
```

### 2. Axios Configuration Example

```javascript
// services/api.js
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add token to requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
```

### 3. Environment Variables for Frontend

Create a `.env` file in your frontend project:

```env
REACT_APP_API_URL=http://localhost:8000/api/v1
REACT_APP_FRONTEND_URL=http://localhost:3001
```

## Testing the Configuration

### 1. Test CORS
```bash
curl -H "Origin: http://localhost:3001" \
     -H "Access-Control-Request-Method: GET" \
     -H "Access-Control-Request-Headers: X-Requested-With" \
     -X OPTIONS \
     http://localhost:8000/api/v1/student/programs
```

### 2. Test API Endpoints
```bash
# Test login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "student1@example.com", "password": "password"}'

# Test with token
curl -X GET http://localhost:8000/api/v1/student/programs \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Troubleshooting

### Common Issues:

1. **CORS Error**: Make sure `http://localhost:3001` is in the `allowed_origins` array
2. **Token Issues**: Ensure the Authorization header is properly formatted
3. **Route Not Found**: Check that the API routes are properly registered
4. **Database Connection**: Verify database configuration in `.env`

### Debug Steps:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Test API endpoints with Postman/curl
3. Verify CORS headers in browser dev tools
4. Check network tab for request/response details

## Production Configuration

For production, update:

1. **APP_URL**: Set to your production domain
2. **CORS Origins**: Add your production frontend domain
3. **Database**: Update with production database credentials
4. **SSL**: Ensure HTTPS is properly configured

---

Your frontend at `http://localhost:3001` should now be able to communicate with the Laravel API at `http://localhost:8000/api/v1` without CORS issues.
