<?php

namespace App\Enums;

enum QuoteStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
    case SUPERSEDED = 'superseded';
}
