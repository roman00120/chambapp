<?php

namespace App\Enums;

enum AvailabilityStatus: string
{
    case AVAILABLE = 'available';
    case BUSY = 'busy';
}
