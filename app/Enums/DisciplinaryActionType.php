<?php

namespace App\Enums;

enum DisciplinaryActionType: string
{
    case YELLOW_CARD = 'yellow_card';
    case WARNING = 'warning';
    case TEMPORARY_SUSPENSION = 'temporary_suspension';
    case INDEFINITE_SUSPENSION = 'indefinite_suspension';
    case BAN = 'ban';

    public function label(): string
    {
        return match ($this) {
            self::YELLOW_CARD => 'Tarjeta Amarilla (Advertencia)',
            self::WARNING => 'Advertencia Formal',
            self::TEMPORARY_SUSPENSION => 'Suspensión Temporal',
            self::INDEFINITE_SUSPENSION => 'Suspensión Indefinida',
            self::BAN => 'Bloqueo Definitivo de Cuenta',
        };
    }
}
