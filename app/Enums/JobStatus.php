<?php

namespace App\Enums;

enum JobStatus: string
{
    case PENDING = 'pending';
    case SEARCHING = 'searching';
    case MATCHED = 'matched';
    case AWAITING_QUOTE = 'awaiting_quote';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case AWAITING_PAYMENT = 'awaiting_payment';
    case PAID = 'paid';
    case ON_THE_WAY = 'on_the_way';
    case ARRIVED = 'arrived';
    case IN_PROGRESS = 'in_progress';
    case AWAITING_CONFIRMATION = 'awaiting_confirmation';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
    case DISPUTED = 'disputed';
}
