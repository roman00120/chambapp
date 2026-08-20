<?php

namespace App\Enums;

enum JobDisputeStatus: string
{
    case OPEN = 'open';
    case REVIEWING = 'reviewing';
    case RESOLVED = 'resolved';
    case REJECTED = 'rejected';
}
