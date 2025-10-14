<?php

use Illuminate\Support\Facades\Route;

// Include separate endpoint files for better organization
require_once __DIR__ . '/auth/login.php';
require_once __DIR__ . '/applicant/register.php';
require_once __DIR__ . '/auth/logout.php';
require_once __DIR__ . '/auth/me.php';
require_once __DIR__ . '/user/profile.php';
