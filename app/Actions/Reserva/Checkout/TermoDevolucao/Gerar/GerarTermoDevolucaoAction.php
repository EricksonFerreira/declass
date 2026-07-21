<?php

namespace App\Actions\Reserva\Checkout\TermoDevolucao\Gerar;

use App\Services\Reserva\ReservaResolver;

class GerarTermoDevolucaoAction
{
    public function __construct(
        private GerarTermoDevolucaoReservaComumAction $comum,
        private GerarTermoDevolucaoMotoristaAppAction $motoristaApp,
        private ReservaResolver $reservaResolver,
    ) {}

    public function handle(string $codigo): string
    {
        $reservaResolvida = $this->reservaResolver->resolver($codigo);

        return match (true) {
            $reservaResolvida->ehComum() => $this->comum->handle($reservaResolvida->registro),
            $reservaResolvida->ehMotoristaApp() => $this->motoristaApp->handle($reservaResolvida->registro),
            default => throw new \LogicException('Tipo de reserva não suportado para geração do termo de devolução.'),
        };
    }
}
