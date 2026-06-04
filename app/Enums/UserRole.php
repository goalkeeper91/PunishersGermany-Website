<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case ORGA = 'orga';
    case LEADER = 'leader';
    case COACH = 'coach';
    case PLAYER = 'player';
    case MEMBER = 'member';

    // Ein kleiner Helfer, um schöne Namen im Admin-Dashboard anzuzeigen
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::ORGA => 'Orga-Manager',
            self::LEADER => 'Team Leader',
            self::COACH => 'Coach',
            self::PLAYER => 'Spieler',
            self::MEMBER => 'Member',
        };
    }
}
