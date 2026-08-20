<?php

namespace App\Enums;

enum InvitationStatus: string
{
    case PENDING = 'pending';
    case VIEWED = 'viewed';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case EXPIRED = 'expired';
    case LOST = 'lost';
}
