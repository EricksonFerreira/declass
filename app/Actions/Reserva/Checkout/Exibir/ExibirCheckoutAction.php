<?php

namespace App\Actions\Reserva\Checkout\Exibir;

use App\Services\Reserva\ReservaResolver;
use Illuminate\Http\Request;

class ExibirCheckoutAction
{
    public function __construct(
        private ReservaResolver $reservaResolver,
        private ExibirCheckoutReservaComumAction $comum,
        private ExibirCheckoutMotoristaAppAction $motoristaApp,
    ) {}

    public function handle(string $codigo, Request $request): mixed
    {
        $reservaResolvida = $this->reservaResolver->resolver($codigo);

        return match (true) {
            $reservaResolvida->ehComum() => $this->comum->handle(
                $reservaResolvida,
                $request
            ),

            $reservaResolvida->ehMotoristaApp() => $this->motoristaApp->handle(
                $reservaResolvida,
                $request
            ),

            default => throw new \LogicException(
                'Tipo de reserva não suportado para exibição do checkout.'
            ),
        };
    }
}
