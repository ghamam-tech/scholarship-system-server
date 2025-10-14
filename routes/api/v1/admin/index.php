<?php

use Illuminate\Support\Facades\Route;

// Include all admin endpoint files for better organization
require_once __DIR__ . '/../admin.php';        // Admin resource routes
require_once __DIR__ . '/applicant.php';       // Applicant management
require_once __DIR__ . '/country.php';         // Country management
require_once __DIR__ . '/scholarship.php';     // Scholarship management
require_once __DIR__ . '/specialization.php';  // Specialization management
require_once __DIR__ . '/university.php';      // University management
require_once __DIR__ . '/sponsor.php';         // Sponsor management
