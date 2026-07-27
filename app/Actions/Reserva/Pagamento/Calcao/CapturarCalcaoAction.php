<?php

namespace App\Actions\Reserva\Pagamento\Calcao;

use App\Actions\Financeiro\RegistrarReceitaPagamentoReservaAction;
use App\Models\Calcao;
use App\Models\Transacao;
use App\Models\Reserva;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\ReservaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class CapturarCalcaoAction
{
    public function __construct(
        private GatewayEmpresaConfigService $gatewayConfig,
        private ReservaService $reservaService,
        private RegistrarReceitaPagamentoReservaAction $registrarReceitaPagamentoReservaAction,
    ) {}

    public function handle(Reserva $reserva, float $valorCaptura, ?string $justificativa): Calcao
    {
        $calcao = $reserva->calcao;
        if (!$calcao) {
            throw new \RuntimeException('Calção não encontrado para esta reserva.');
        }

        if (!$calcao->podeCapturar()) {
            throw new \RuntimeException('Este calcao nao pode ser capturado no status atual: ' . $calcao->status);
        }

        if ($calcao->data_hora_captura || (float) $calcao->valor_capturado > 0) {
            throw new \RuntimeException('Este calcao ja foi capturado anteriormente e nao pode ser capturado novamente.');
        }

        $empresa = $this->gatewayConfig->empresaAtual();
        $stripeSecret = $empresa?->stripe_secret;
        if (!$stripeSecret) {
            throw new \RuntimeException('Configuracao do Stripe nao encontrada.');
        }

        $stripe = new StripeClient($stripeSecret);
        $valorEmCentavos = (int) round($valorCaptura * 100);

        try {
            $intent = $stripe->paymentIntents->capture($calcao->payment_intent_id, [
                'amount_to_capture' => $valorEmCentavos,
            ]);
        } catch (\Throwable $e) {
            Log::error('CapturarCalcaoAction: CapturaError', ['message' => $e->getMessage(), 'calcao_id' => $calcao->id]);
            throw new \RuntimeException('Falha ao capturar o calcao no Stripe: ' . $e->getMessage());
        }

        $valorAutorizado = (float) $calcao->valor_autorizado;
        $novoStatus      = abs($valorCaptura - $valorAutorizado) < 0.01
            ? Calcao::STATUS_CAPTURADO
            : Calcao::STATUS_PARCIALMENTE_CAPTURADO;

        $calcao->update([
            'status'                => $novoStatus,
            'valor_capturado'       => $valorCaptura,
            'data_hora_captura'     => now(),
            'justificativa_captura' => $justificativa,
            'usuario_responsavel_id' => Auth::id(),
            'stripe_response'       => $intent->toArray(),
        ]);

        $calcaoCap = $calcao->fresh();

        $transacao = Transacao::create([
            'user_id' => $reserva->user_id,
            'pago_por_user_id' => Auth::id(),
            'reserva_id' => $reserva->id,
            'valor' => $valorCaptura,
            'moeda' => 'BRL',
            'metodo_pagamento' => 'stripe',
            'gateway_transaction_id' => $intent->id ?? $calcao->payment_intent_id,
            'status' => $intent->status ?? 'succeeded',
            'info_pagamento' => [
                'tipo' => 'calcao',
                'acao' => 'capturado_stripe',
                'calcao_id' => $calcaoCap->id,
            ],
        ]);

        $this->registrarReceitaPagamentoReservaAction->handle(
            $reserva,
            $valorCaptura,
            'stripe',
            now(),
            (string) ($transacao->gateway_transaction_id ?: 'calcao_'.$calcaoCap->id),
            Auth::id(),
            'calcao_'.$calcaoCap->id
        );

        $this->reservaService->adicionaLog($reserva->id, Auth::id(), json_encode([
            'acao'            => 'Calcao capturado',
            'calcao_id'       => $calcaoCap->id,
            'valor_capturado' => $calcaoCap->valor_capturado,
        ]));

        return $calcaoCap;
    }
}
