# Testing the Logout Endpoint

## The Issue

The logout endpoint at `http://127.0.0.1:8000/api/v1/logout` wasn't working because it requires authentication.

## The Solution

I've added the `auth:sanctum` middleware to the logout route, which means:

1. **You need to be logged in first** - Get a token from the login endpoint
2. **Include the token in your request** - Add the Authorization header

## How to Test

### Step 1: Login to get a token

```bash
curl -X POST http://127.0.0.1:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "your-email@example.com",
    "password": "your-password"
  }'
```

### Step 2: Use the token to logout

```bash
curl -X POST http://127.0.0.1:8000/api/v1/logout \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## What I Fixed

1. **Added authentication middleware** to `/logout` endpoint
2. **Added authentication middleware** to `/me` endpoint
3. **Added authentication middleware** to `/profile` endpoints

## Protected Endpoints (require authentication)

-   `POST /api/v1/logout`
-   `GET /api/v1/me`
-   `GET /api/v1/profile`
-   `PUT /api/v1/profile`

## Public Endpoints (no authentication required)

-   `POST /api/v1/login`
-   `POST /api/v1/applicant/register`
