<?php

namespace App\Enums;

enum DisciplinaryAppealStatus: string
{
    case SUBMITTED = 'submitted';
    case UNDER_REVIEW = 'under_review';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::SUBMITTED => 'Enviada (Pendiente de revisión)',
            self::UNDER_REVIEW => 'En revisión administrativa',
            self::ACCEPTED => 'Aceptada (Sanción revocada)',
            self::REJECTED => 'Rechazada (Sanción ratificada)',
        };
    }
}
