<?php

namespace App\Enums;

enum TipoReservaEnum: string
{
    case COMUM = 'reserva';
    case MOTORISTA_APP = 'motorista_app';

    public function label(): string
    {
        return match ($this) {
            self::COMUM => 'Reserva',
            self::MOTORISTA_APP => 'Motorista App',
        };
    }
}
