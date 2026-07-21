<?php

namespace App\Actions\Reserva\Pagamento\Calcao;

use App\Models\Calcao;
use App\Models\Reserva;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\ReservaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class LiberarCalcaoAction
{
    public function __construct(
        private GatewayEmpresaConfigService $gatewayConfig,
        private ReservaService $reservaService,
    ) {}

    public function handle(Reserva $reserva, ?string $observacao): Calcao
    {
        $calcao = $reserva->calcao;
        if (!$calcao) {
            throw new \RuntimeException('Calção não encontrado para esta reserva.');
        }

        if (!$calcao->podeLiberar()) {
            throw new \RuntimeException('Este calcao nao pode ser liberado no status atual: ' . $calcao->status);
        }

        $empresa = $this->gatewayConfig->empresaAtual();
        $stripeSecret = $empresa?->stripe_secret;
        if (!$stripeSecret) {
            throw new \RuntimeException('Configuracao do Stripe nao encontrada.');
        }

        $stripe = new StripeClient($stripeSecret);

        try {
            $intent = $stripe->paymentIntents->cancel($calcao->payment_intent_id);
        } catch (\Throwable $e) {
            Log::error('LiberarCalcaoAction: CancelError', ['message' => $e->getMessage(), 'calcao_id' => $calcao->id]);
            throw new \RuntimeException('Falha ao cancelar o calcao no Stripe: ' . $e->getMessage());
        }

        $calcao->update([
            'status'                   => Calcao::STATUS_CANCELADO,
            'data_hora_cancelamento'   => now(),
            'observacao'               => $observacao ?? '',
            'usuario_responsavel_id'   => Auth::id(),
            'stripe_response'          => $intent->toArray(),
        ]);

        $calcaoLib = $calcao->fresh();

        $this->reservaService->adicionaLog($reserva->id, Auth::id(), json_encode([
            'acao'      => 'Calcao liberado (cancelado)',
            'calcao_id' => $calcaoLib->id,
        ]));

        return $calcaoLib;
    }
}
