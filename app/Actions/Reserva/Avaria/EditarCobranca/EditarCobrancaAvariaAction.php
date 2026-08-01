<?php

namespace App\Actions\Reserva\Avaria\EditarCobranca;

use App\Models\AvariaCobranca;

class EditarCobrancaAvariaAction
{
    public function __construct(
        private readonly EditarCobrancaAvariaReservaComumAction $reservaComum,
        private readonly EditarCobrancaAvariaMotoristaAppAction $motoristaApp,
    ) {}

    public function handle(int $cobrancaId, array $dados): AvariaCobranca
    {
        $cobranca = AvariaCobranca::query()->findOrFail($cobrancaId);

        return match (true) {
            ! empty($cobranca->reserva_codigo_id) => $this->reservaComum->handle($cobranca, $dados),
            default => $this->motoristaApp->handle($cobranca, $dados),
        };
    }
}
