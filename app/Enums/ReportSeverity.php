<?php

namespace App\Enums;

enum ReportSeverity: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Baja (Leve)',
            self::MEDIUM => 'Media (Moderada)',
            self::HIGH => 'Alta (Grave)',
            self::CRITICAL => 'Crítica (Urgente / Muy grave)',
        };
    }
}
