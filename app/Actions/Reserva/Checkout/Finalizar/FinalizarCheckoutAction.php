<?php

namespace App\Actions\Reserva\Checkout\Finalizar;

use App\Services\Reserva\ReservaResolver;

class FinalizarCheckoutAction
{
    public function __construct(
        private FinalizarCheckoutReservaComumAction $comum,
        private FinalizarCheckoutMotoristaAppAction $motoristaApp,
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
