<?php

namespace App\Enums;

/**
 * IDs conforme tabela status_reserva no banco:
 *   1 = Criada | 2 = Confirmada | 3 = Cancelada | 4 = Em Andamento
 *   5 = Concluída | 6 = Atrasada | 11 = Expirada
 */
enum StatusReservaEnum: int
{
    case Pendente    = 1;  // Criada (alias mantido para compatibilidade)
    case Confirmada  = 2;
    case Cancelada   = 3;
    case Checkin     = 4;  // Em Andamento
    case Checkout    = 5;  // Concluída
    case NoShow      = 6;  // Atrasada
    case Expirada    = 11;

    public function label(): string
    {
        return match ($this) {
            self::Pendente   => 'Criada',
            self::Confirmada => 'Confirmada',
            self::Cancelada  => 'Cancelada',
            self::Checkin    => 'Em Andamento',
            self::Checkout   => 'Concluída',
            self::NoShow     => 'Atrasada',
            self::Expirada   => 'Expirada',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::Pendente   => '#f59e0b',
            self::Confirmada => '#3b82f6',
            self::Cancelada  => '#ef4444',
            self::Checkin    => '#8b5cf6',
            self::Checkout   => '#10b981',
            self::NoShow     => '#f97316',
            self::Expirada   => '#6b7280',
        };
    }
}
