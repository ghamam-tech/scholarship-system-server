<?php

use Illuminate\Support\Facades\Route;

// Include all v1 API routes for better organization
require_once __DIR__ . '/public.php';          // Public and authenticated routes
require_once __DIR__ . '/admin/index.php';     // All admin routes
