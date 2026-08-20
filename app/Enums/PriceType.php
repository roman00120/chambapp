<?php

namespace App\Enums;

enum PriceType: string
{
    case FIXED = 'fixed';
    case STARTING_AT = 'starting_at';
    case QUOTE = 'quote';
}
