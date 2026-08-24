<?php

namespace App\Enums;

enum IdentityVerificationStatus: string
{
    case NOT_STARTED = 'not_started';
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
    case NEEDS_REVIEW = 'needs_review';
    case EXPIRED = 'expired';
}
