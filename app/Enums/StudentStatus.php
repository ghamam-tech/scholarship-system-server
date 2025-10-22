<?php

namespace App\Enums;

enum StudentStatus: string
{
    case ACTIVE = 'active';
    case FIRST_WARNING = 'first_warning';
    case SECOND_WARNING = 'second_warning';
    case REQUEST_MEETING = 'request_meeting';
    case GRADUATE_STUDENT = 'graduate_student';
    case SUSPENDED = 'suspended';
    case TERMINATED = 'terminated';
}

