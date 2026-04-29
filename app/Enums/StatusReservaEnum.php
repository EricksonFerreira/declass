<?php

namespace App\Enums;

enum StatusReservaEnum: int
{
    case Pendente   = 1;
    case Confirmada = 2;
    case Checkin    = 3;
    case Checkout   = 4;
    case Cancelada  = 5;
    case NoShow     = 6;

    public function label(): string
    {
        return match ($this) {
            self::Pendente   => 'Pendente',
            self::Confirmada => 'Confirmada',
            self::Checkin    => 'Check-in',
            self::Checkout   => 'Check-out',
            self::Cancelada  => 'Cancelada',
            self::NoShow     => 'No-show',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::Pendente   => '#f59e0b',
            self::Confirmada => '#3b82f6',
            self::Checkin    => '#8b5cf6',
            self::Checkout   => '#10b981',
            self::Cancelada  => '#ef4444',
            self::NoShow     => '#6b7280',
        };
    }
}
