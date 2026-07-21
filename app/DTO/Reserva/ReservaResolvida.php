<?php

namespace App\DTO\Reserva;

use App\Enums\TipoReservaEnum;
use App\Models\Reserva;
use App\Models\ReservaMotoristaApp;

final readonly class ReservaResolvida
{
    public function __construct(
        public string $codigo,
        public TipoReservaEnum $tipo,
        public Reserva|ReservaMotoristaApp $registro,
    ) {}

    public function id(): int
    {
        return (int) $this->registro->id;
    }

    public function ehComum(): bool
    {
        return $this->tipo === TipoReservaEnum::COMUM;
    }

    public function ehMotoristaApp(): bool
    {
        return $this->tipo === TipoReservaEnum::MOTORISTA_APP;
    }

    public function empresaId(): ?int
    {
        return $this->registro->empresa_id ?? null;
    }

    public function reservaComum(): Reserva
    {
        if (! $this->ehComum()) {
            throw new \LogicException('Esta reserva não é do tipo comum.');
        }

        return $this->registro;
    }

    public function reservaMotoristaApp(): ReservaMotoristaApp
    {
        if (! $this->ehMotoristaApp()) {
            throw new \LogicException('Esta reserva não é do tipo motorista de aplicativo.');
        }

        return $this->registro;
    }
}
