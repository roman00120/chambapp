<?php

namespace App\Enums;

enum UserRole: string
{
    case CLIENT = 'client';
    case PROFESSIONAL = 'professional';
    case ADMIN = 'admin';
}
