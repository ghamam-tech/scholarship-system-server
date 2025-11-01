<?php

namespace App\Enums;

enum AnnouncementStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case EXPIRED = 'expired';
}
