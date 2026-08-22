<?php

namespace App\Enums;

enum PaymentKind: string
{
    case JOB = 'job';
    case TIP = 'tip';
}
