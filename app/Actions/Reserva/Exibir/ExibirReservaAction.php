<?php

namespace App\Actions\Reserva\Exibir;

use App\Services\Reserva\ReservaResolver;

class ExibirReservaAction
{
    public function __construct(
        private ReservaResolver $reservaResolver,
        private ExibirReservaComumAction $comum,
        private ExibirReservaMotoristaAppAction $motoristaApp,
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
