<?php

namespace App\Actions\Reserva\Renovacao\Exibir;

use App\Services\Reserva\ReservaResolver;
use App\Actions\Reserva\Renovacao\Exibir\ExibirRenovacaoReservaComumAction;
use App\Actions\Reserva\Renovacao\Exibir\ExibirRenovacaoReservaMotoristaAppAction;

class ExibirRenovacaoActionAction
{
    public function __construct(
        private ReservaResolver $reservaResolver,
        private ExibirRenovacaoReservaComumAction $comum,
        private ExibirRenovacaoReservaMotoristaAppAction $motoristaApp,
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
