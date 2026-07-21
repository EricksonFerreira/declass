<?php

namespace App\Actions\Reserva\Avaria\CriarCobranca;

use App\Models\AvariaCobranca;
use App\Services\Reserva\ReservaResolver;

class CriarCobrancaAvariaAction
{
    public function __construct(
        private readonly ReservaResolver $reservaResolver,
        private readonly CriarCobrancaAvariaReservaComumAction $reservaComum,
        private readonly CriarCobrancaAvariaMotoristaAppAction $motoristaApp,
    ) {}

    public function handle(string $codigo, array $dados): AvariaCobranca
    {
        $reservaResolvida = $this->reservaResolver->resolver($codigo);

        return match (true) {
            $reservaResolvida->ehComum() => $this->reservaComum->handle(
                $reservaResolvida,
                $dados
            ),

            $reservaResolvida->ehMotoristaApp() => $this->motoristaApp->handle(
                $reservaResolvida,
                $dados
            ),

            default => throw new \LogicException(
                'Tipo de reserva não suportado para cobrança de avarias.'
            ),
        };
    }
}
