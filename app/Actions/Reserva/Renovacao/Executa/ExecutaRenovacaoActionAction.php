<?php

namespace App\Actions\Reserva\Renovacao\Executa;

use App\Services\Reserva\ReservaResolver;

class ExecutaRenovacaoActionAction
{
    public function __construct(
        private ReservaResolver $reservaResolver,
        private ExecutaRenovacaoReservaComumAction $comum,
        private ExecutaRenovacaoReservaMotoristaAppAction $motoristaApp,
    ) {}

    public function handle(string $codigo): mixed
    {
        $reservaResolvida = $this->reservaResolver->resolver($codigo);

        return match (true) {
            $reservaResolvida->ehComum() => $this->comum->handle($reservaResolvida),
            $reservaResolvida->ehMotoristaApp() => $this->motoristaApp->handle($reservaResolvida),
            default => throw new \LogicException('Tipo de reserva não suportado para exibição.'),
        };
    }
}
