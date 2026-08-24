<?php

namespace App\Enums;

enum ProfessionalCredentialStatus: string
{
    case NOT_STARTED = 'not_started';
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
}
