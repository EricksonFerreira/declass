<?php

namespace App\Actions\Reserva\Checkout\Realizar;

use App\Services\Reserva\ReservaResolver;
use Illuminate\Http\Request;

class RealizarCheckoutAction
{
    public function __construct(
        private RealizarCheckoutReservaComumAction $comum,
        private RealizarCheckoutMotoristaAppAction $motoristaApp,
        private ReservaResolver $reservaResolver,
    ) {}

    public function handle(string $codigo, array $dados, Request $request): mixed
    {
        $reservaResolvida = $this->reservaResolver->resolver($codigo);

        return match (true) {
            $reservaResolvida->ehComum() => $this->comum->handle($reservaResolvida->registro, $dados, $request),
            $reservaResolvida->ehMotoristaApp() => $this->motoristaApp->handle($reservaResolvida->registro, $dados, $request),
            default => throw new \LogicException('Tipo de reserva não suportado para realização do checkout.'),
        };
    }
}
