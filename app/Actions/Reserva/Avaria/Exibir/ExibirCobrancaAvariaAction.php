<?php

namespace App\Actions\Reserva\Avaria\Exibir;

use App\Services\Reserva\ReservaResolver;

class ExibirCobrancaAvariaAction
{
    public function __construct(
        private readonly ReservaResolver $reservaResolver,
        private readonly ExibirCobrancaAvariaReservaComumAction $reservaComum,
        private readonly ExibirCobrancaAvariaMotoristaAppAction $motoristaApp,
    ) {}

    public function handle(string $codigo): array
    {
        $reservaResolvida = $this->reservaResolver->resolver($codigo);

        return match (true) {
            $reservaResolvida->ehComum() => $this->reservaComum->handle(
                $reservaResolvida
            ),

            $reservaResolvida->ehMotoristaApp() => $this->motoristaApp->handle(
                $reservaResolvida
            ),

            default => throw new \LogicException(
                'Tipo de reserva não suportado para cobrança de avarias.'
            ),
        };
    }
}
