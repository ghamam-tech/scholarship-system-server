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
    case FIRST_WARNING = 'first_warning';
    case SECOND_WARNING = 'second_warning';
    case TERMINATED = 'terminated';
    case GRADUATED = 'graduated';
    case ACCEPTED_SCHOLARSHIP = 'accepted_scholarship';
    case REJECTED_SCHOLARSHIP = 'rejected_scholarship';
    case MEETING_REQUESTED = 'meeting_requested';
    case ADDED_MANUALLY = 'added_manually';
}
