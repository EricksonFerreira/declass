<?php

namespace App\Services;

use App\Models\AvariaCobranca;
use App\Models\Calcao;
use App\Models\Reserva;
use App\Models\VeiculoDanos;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class AvariaCobrancaService
{
    public function __construct(
        private readonly GatewayEmpresaConfigService $gatewayConfig,
        private readonly CalcaoService $calcaoService,
    ) {}

    /**
     * Cria e executa uma cobrança por avarias no contexto pós-checkout.
     *
     * Regras:
     * - Se houver calção em status capturável, prefere capturar o calção.
     * - Caso contrário, tenta cobrar off-session no Stripe (se possível).
     * - Se não for possível, registra como "manual" (pendente) para controle.
     */
    public function criarECobrar(
        Reserva $reserva,
        array $danoVeiculoIds,
        float $valor,
        string $descricao,
        ?string $metodoPreferido = null,
        ?string $paymentMethodId = null,
    ): AvariaCobranca {
        $danoVeiculoIds = collect($danoVeiculoIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($valor <= 0) {
            throw new \RuntimeException('Informe um valor válido para a cobrança.');
        }

        $empresa = $this->gatewayConfig->empresaPorId((int) ($reserva->empresa_id ?? 0))
            ?? $this->gatewayConfig->empresaAtual();
        $moeda = $empresa ? $this->gatewayConfig->moeda($empresa) : 'BRL';
        $gateway = $empresa ? $this->gatewayConfig->gateway($empresa) : 'stripe';

        $cliente = $reserva->usuario;
        if (!$cliente) {
            throw new \RuntimeException('Cliente da reserva não encontrado.');
        }

        $danos = collect();
        if ($danoVeiculoIds) {
            $danos = VeiculoDanos::query()
                ->where('reserva_id', $reserva->id)
                ->whereIn('id', $danoVeiculoIds)
                ->get();
        }

        DB::beginTransaction();
        try {
            $cobranca = AvariaCobranca::create([
                'empresa_id' => $reserva->empresa_id ?? null,
                'reserva_id' => (int) $reserva->id,
                'veiculo_id' => $reserva->veiculo_id ?? null,
                'cliente_id' => $cliente->id ?? null,
                'calcao_id' => $reserva->calcao?->id ?? null,
                'metodo' => AvariaCobranca::METODO_MANUAL,
                'status' => AvariaCobranca::STATUS_PROCESSANDO,
                'moeda' => $moeda,
                'valor' => $valor,
                'descricao' => $descricao,
                'dano_veiculo_ids' => $danoVeiculoIds,
                'usuario_responsavel_id' => Auth::id(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        // Executa fora da transação principal para evitar lock longo.
        try {
            // 1) Preferência explícita do operador
            if ($metodoPreferido === AvariaCobranca::METODO_CALCAO) {
                return $this->capturarCalcao($reserva, $cobranca, $valor, $descricao);
            }
            if ($metodoPreferido === AvariaCobranca::METODO_STRIPE) {
                return $this->cobrarStripe($reserva, $cobranca, $valor, $descricao, $paymentMethodId);
            }

            // 2) Regra padrão: calção capturável primeiro
            if ($reserva->calcao && $reserva->calcao->podeCapturar()) {
                return $this->capturarCalcao($reserva, $cobranca, $valor, $descricao);
            }

            // 3) Fallback: Stripe (off-session)
            if ($gateway === 'stripe') {
                return $this->cobrarStripe($reserva, $cobranca, $valor, $descricao, $paymentMethodId);
            }

            // 4) Manual
            $cobranca->update([
                'metodo' => AvariaCobranca::METODO_MANUAL,
                'status' => AvariaCobranca::STATUS_PENDENTE,
            ]);

            return $cobranca->fresh();
        } catch (\Throwable $e) {
            Log::warning('AvariaCobrancaService: falha ao cobrar', [
                'reserva_id' => $reserva->id,
                'cobranca_id' => $cobranca->id,
                'message' => $e->getMessage(),
            ]);

            $cobranca->update([
                'status' => AvariaCobranca::STATUS_FALHOU,
                'gateway_response' => [
                    'error' => $e->getMessage(),
                ],
            ]);

            return $cobranca->fresh();
        }
    }

    private function capturarCalcao(Reserva $reserva, AvariaCobranca $cobranca, float $valor, string $descricao): AvariaCobranca
    {
        /** @var Calcao|null $calcao */
        $calcao = $reserva->calcao;
        if (!$calcao) {
            throw new \RuntimeException('A reserva não possui calção para captura.');
        }

        $calcaoAtualizado = $this->calcaoService->capturar($calcao, $valor, $descricao);

        $cobranca->update([
            'metodo' => AvariaCobranca::METODO_CALCAO,
            'status' => AvariaCobranca::STATUS_COBRADO,
            'calcao_id' => $calcaoAtualizado->id,
            'gateway_payment_intent_id' => $calcaoAtualizado->payment_intent_id,
            'gateway_response' => $calcaoAtualizado->stripe_response,
            'cobrado_em' => now(),
        ]);

        return $cobranca->fresh();
    }

    private function cobrarStripe(
        Reserva $reserva,
        AvariaCobranca $cobranca,
        float $valor,
        string $descricao,
        ?string $paymentMethodId = null
    ): AvariaCobranca {
        $empresa = $this->gatewayConfig->empresaPorId((int) ($reserva->empresa_id ?? 0))
            ?? $this->gatewayConfig->empresaAtual();
        $stripeSecret = $empresa?->stripe_secret;

        if (!$stripeSecret) {
            throw new \RuntimeException('Configuração do Stripe não encontrada para esta empresa.');
        }

        $cliente = $reserva->usuario;
        $customerId = $reserva->calcao?->stripe_customer_id ?: ($cliente?->stripe_id ?? null);
        if (!$customerId) {
            throw new \RuntimeException('Cliente não possui Stripe Customer ID.');
        }

        $stripe = new StripeClient($stripeSecret);

        $paymentMethodId = $paymentMethodId ?: ($reserva->calcao?->payment_method_id ?: null);
        if (!$paymentMethodId) {
            $paymentMethodId = $this->resolverPaymentMethodPadrao($stripe, (string) $customerId);
        }
        if (!$paymentMethodId) {
            throw new \RuntimeException('Não foi possível determinar um método de pagamento para cobrar.');
        }

        $moeda = strtolower($this->gatewayConfig->moeda($empresa) ?? 'brl');
        $valorEmCentavos = (int) round($valor * 100);

        $intent = $stripe->paymentIntents->create([
            'amount' => $valorEmCentavos,
            'currency' => $moeda,
            'customer' => (string) $customerId,
            'payment_method' => (string) $paymentMethodId,
            'confirm' => true,
            'off_session' => true,
            'description' => $descricao,
            'metadata' => [
                'reserva_id' => (string) $reserva->id,
                'veiculo_id' => (string) ($reserva->veiculo_id ?? ''),
                'cobranca_avaria_id' => (string) $cobranca->id,
            ],
        ]);

        $cobranca->update([
            'metodo' => AvariaCobranca::METODO_STRIPE,
            'status' => AvariaCobranca::STATUS_COBRADO,
            'gateway_payment_intent_id' => $intent->id,
            'gateway_response' => $intent->toArray(),
            'cobrado_em' => now(),
        ]);

        return $cobranca->fresh();
    }

    private function resolverPaymentMethodPadrao(StripeClient $stripe, string $customerId): ?string
    {
        try {
            $customer = $stripe->customers->retrieve($customerId, []);
            $default = $customer?->invoice_settings?->default_payment_method ?? null;
            if (is_string($default) && $default !== '') {
                return $default;
            }
        } catch (\Throwable $e) {
            // ignora e tenta fallback
        }

        try {
            $methods = $stripe->paymentMethods->all([
                'customer' => $customerId,
                'type' => 'card',
                'limit' => 1,
            ]);

            $first = $methods->data[0] ?? null;
            return $first?->id ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}

