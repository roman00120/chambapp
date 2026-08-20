<?php

namespace App\Enums;

enum ServiceMode: string
{
    case IMMEDIATE = 'immediate';
    case SCHEDULED = 'scheduled';
    case DIAGNOSTIC = 'diagnostic';
}
