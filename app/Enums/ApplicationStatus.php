<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case ENROLLED = 'enrolled';
    case FIRST_APPROVAL = 'first_approval';
    case MEETING_SCHEDULED = 'meeting_scheduled';
    case SECOND_APPROVAL = 'second_approval';
    case FINAL_APPROVAL = 'final_approval';
    case REJECTED = 'rejected';
}