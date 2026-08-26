<?php

namespace App\Enums;

enum ReportCategory: string
{
    case FRAUD = 'fraud';
    case THREATS = 'threats';
    case VIOLENCE = 'violence';
    case HARASSMENT = 'harassment';
    case IDENTITY_IMPERSONATION = 'identity_impersonation';
    case THEFT = 'theft';
    case NO_SHOW = 'no_show';
    case PAYMENT_ISSUE = 'payment_issue';
    case ABUSIVE_BEHAVIOR = 'abusive_behavior';
    case UNSAFE_BEHAVIOR = 'unsafe_behavior';
    case FALSE_INFORMATION = 'false_information';
    case SERVICE_MISCONDUCT = 'service_misconduct';
    case PROPERTY_DAMAGE = 'property_damage';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FRAUD => 'Fraude o estafa',
            self::THREATS => 'Amenazas',
            self::VIOLENCE => 'Violencia o agresión',
            self::HARASSMENT => 'Acoso o intimidación',
            self::IDENTITY_IMPERSONATION => 'Suplantación de identidad',
            self::THEFT => 'Robo o extravío de pertenencias',
            self::NO_SHOW => 'No se presentó al servicio acordado',
            self::PAYMENT_ISSUE => 'Problema o intento de cobro irregular',
            self::ABUSIVE_BEHAVIOR => 'Comportamiento abusivo o irrespetuoso',
            self::UNSAFE_BEHAVIOR => 'Conducta peligrosa o negligencia',
            self::FALSE_INFORMATION => 'Información falsa o engañosa',
            self::SERVICE_MISCONDUCT => 'Mala conducta durante el servicio',
            self::PROPERTY_DAMAGE => 'Daño a propiedad o bienes',
            self::OTHER => 'Otro motivo',
        };
    }
}
