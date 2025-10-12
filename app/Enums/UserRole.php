<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = "admin";
    case SPONSOR = "sponsor";
    case APPLICANT = "applicant";
    case STUDENT = "student";
}
