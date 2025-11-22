<?php

namespace App\Enums\Match;

enum MatchOutcomeEnum: string
{
    case HOME = 'home';
    case DRAW = 'draw';
    case AWAY = 'away';

    public function label(): string
    {
        return match ($this) {
            self::HOME => 'Home Win',
            self::DRAW => 'Draw',
            self::AWAY => 'Away Win',
        };
    }
}

