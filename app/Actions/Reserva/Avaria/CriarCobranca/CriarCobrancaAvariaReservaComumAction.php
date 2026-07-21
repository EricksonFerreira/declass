<?php

namespace App\Actions\Reserva\Avaria\CriarCobranca;

use App\DTO\Reserva\ReservaResolvida;
use App\Models\AvariaCobranca;
use App\Services\AvariaCobrancaService;
use App\Services\ReservaService;
use Illuminate\Support\Facades\Auth;

class CriarCobrancaAvariaReservaComumAction
{
    public function __construct(
        private readonly AvariaCobrancaService $avariaCobrancaService,
        private readonly ReservaService $reservaService,
    ) {}

    public function handle(
        ReservaResolvida $reservaResolvida,
        array $dados
    ): AvariaCobranca {
        $reserva = $reservaResolvida->reservaComum();

        $reserva->loadMissing([
            'contratoCheckout',
            'codigoReserva',
            'usuario',
            'veiculo',
            'calcao',
        ]);

        if (! $reserva->contratoCheckout) {
            throw new \DomainException(
                'Finalize o check-out antes de registrar cobrança por avarias.'
            );
        }

        $cobranca = $this->avariaCobrancaService->criarECobrar(
            $reservaResolvida,
            $dados['dano_veiculo_ids'] ?? [],
            (float) $dados['valor'],
            (string) $dados['descricao'],
            $dados['metodo'] ?? null,
            $dados['payment_method_id'] ?? null,
        );

        $this->reservaService->adicionaLog(
            $reserva->id,
            Auth::id(),
            [
                'acao' => 'Cobrança de avaria registrada',
                'reserva_codigo' => $reservaResolvida->codigo,
                'cobranca_avaria_id' => $cobranca->id,
                'status' => $cobranca->status,
                'metodo' => $cobranca->metodo,
                'valor' => $cobranca->valor,
                'moeda' => $cobranca->moeda,
                'gateway_payment_intent_id' => $cobranca->gateway_payment_intent_id,
            ]
        );

        return $cobranca;
    }
}
