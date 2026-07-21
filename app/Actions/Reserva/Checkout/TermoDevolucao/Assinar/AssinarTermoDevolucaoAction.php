<?php

namespace App\Actions\Reserva\Checkout\TermoDevolucao\Assinar;

use App\Services\Reserva\ReservaResolver;

class AssinarTermoDevolucaoAction
{
    public function __construct(
        private AssinarTermoDevolucaoReservaComumAction $comum,
        private AssinarTermoDevolucaoMotoristaAppAction $motoristaApp,
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
