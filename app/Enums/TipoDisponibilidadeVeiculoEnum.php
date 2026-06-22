<?php

namespace App\Enums;

enum TipoDisponibilidadeVeiculoEnum: string
{
    case Disponivel = 'ok';
    case Parcial = 'partial';
    case Reservado = 'reserved';
    case ReservaNaoFinalizada = 'overdue';
    case Indisponivel = 'off';
    case SemLocal = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::Disponivel => 'Disponível',
            self::Parcial => 'Parcialmente disponível',
            self::Reservado => 'Reservado',
            self::ReservaNaoFinalizada => 'Reserva não finalizada',
            self::Indisponivel => 'Indisponível',
            self::SemLocal => 'Sem local',
        };
    }

    public function prioridade(): int
    {
        return match ($this) {
            self::ReservaNaoFinalizada => 60,
            self::Indisponivel => 50,
            self::Reservado => 40,
            self::Parcial => 30,
            self::SemLocal => 20,
            self::Disponivel => 10,
        };
    }
}