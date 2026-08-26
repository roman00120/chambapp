<?php

namespace App\Enums;

enum DisciplinaryActionStatus: string
{
    case ACTIVE = 'active';
    case REVOKED = 'revoked';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Activa',
            self::REVOKED => 'Revocada (Apelación / Corrección)',
            self::EXPIRED => 'Expirada',
        };
    }
}
