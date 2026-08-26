<?php

namespace App\Enums;

enum AchievementLevel: string
{
    case BRONZE = 'bronze';
    case SILVER = 'silver';
    case GOLD = 'gold';
    case DIAMOND = 'diamond';
    case MASTER = 'master';

    public function label(): string
    {
        return match ($this) {
            self::BRONZE => 'Bronce',
            self::SILVER => 'Plata',
            self::GOLD => 'Oro',
            self::DIAMOND => 'Diamante',
            self::MASTER => 'Maestro Chambapp',
        };
    }

    public function rank(): int
    {
        return match ($this) {
            self::BRONZE => 1,
            self::SILVER => 2,
            self::GOLD => 3,
            self::DIAMOND => 4,
            self::MASTER => 5,
        };
    }
}
