<?php

namespace App\Enums;

enum RequestStatus: string
{
    case SUBMITTED = 'submitted';
    case REVIEWING = 'reviewing';
    case PROCESSING = 'processing';
    case DOCUMENT_REQUESTED = 'document_requested';
    case DOCUMENT_SUBMITTED = 'document_submitted';
    case MEETING_REQUESTED = 'meeting_requested';
    case MEETING_SCHEDULED = 'meeting_scheduled';
    case TRANSFERRING = 'transferring';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
