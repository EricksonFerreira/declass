<?php

namespace App\Actions\Reserva\Checkin\Contrato\Assinar;

use App\Services\Reserva\ReservaResolver;

class AssinarContratoCheckinAction
{
    public function __construct(
        private AssinarContratoCheckinReservaComumAction $comum,
        private AssinarContratoCheckinMotoristaAppAction $motoristaApp,
        private ReservaResolver $reservaResolver,
    ) {}

    public function handle(string $codigo, string $assinaturaBase64): void
    {
        $reservaResolvida = $this->reservaResolver->resolver($codigo);

        if ($reservaResolvida->ehComum()) {
            $this->comum->handle($reservaResolvida->registro, $assinaturaBase64);
        } else {
            $this->motoristaApp->handle($reservaResolvida->registro, $assinaturaBase64);
        }
    }
}
