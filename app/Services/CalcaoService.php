<?php

namespace App\Services;

use App\Models\Calcao;
use App\Models\ConfiguracaoCalcao;
use App\Models\ContratoCheckin;
use App\Models\Reserva;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class CalcaoService
{
    public function __construct(
        private readonly GatewayEmpresaConfigService $gatewayConfig
    ) {}

    // ──────────────────────────────────────────────────────
    // CÁLCULO DO VALOR
    // ──────────────────────────────────────────────────────

    /**
     * Resolve a configuração de calção com prioridade: modelo > grupo > empresa.
     */
    public function resolverConfiguracao(Reserva $reserva): ?ConfiguracaoCalcao
    {
        $empresaId    = $reserva->empresa_id;
        $grupoId      = $reserva->grupo_frota_id;
        $modeloId     = $reserva->modelo_id;

        // 1. Por modelo
        if ($modeloId) {
            $config = ConfiguracaoCalcao::where('empresa_id', $empresaId)
                ->where('modelo_id', $modeloId)
                ->where('ativo', true)
                ->first();
            if ($config) {
                return $config;
            }
        }

        // 2. Por grupo
        if ($grupoId) {
            $config = ConfiguracaoCalcao::where('empresa_id', $empresaId)
                ->where('grupo_frota_id', $grupoId)
                ->whereNull('modelo_id')
                ->where('ativo', true)
                ->first();
            if ($config) {
                return $config;
            }
        }

        // 3. Por empresa (regra padrão)
        return ConfiguracaoCalcao::where('empresa_id', $empresaId)
            ->whereNull('grupo_frota_id')
            ->whereNull('modelo_id')
            ->where('ativo', true)
            ->first();
    }

    /**
     * Calcula o valor do calção para a reserva.
     * Retorna array com: valor_calculado, tipo_regra, origem_regra, configuracao_id.
     */
    public function calcularValor(Reserva $reserva): array
    {
        $config = $this->resolverConfiguracao($reserva);

        if (!$config) {
            return [
                'valor_calculado'       => 0,
                'tipo_regra_aplicada'   => null,
                'origem_regra_aplicada' => null,
                'configuracao_calcao_id' => null,
                'configuracao'          => null,
            ];
        }

        $valorBase = (float) ($reserva->valor_total_final ?? $reserva->valor_total_com_desconto ?? 0);

        if ($config->tipo_regra_valor === ConfiguracaoCalcao::TIPO_PERCENTUAL) {
            $valor = $valorBase * ((float) $config->valor / 100);
        } else {
            $valor = (float) $config->valor;
        }

        // Aplica limites
        if ($config->valor_minimo !== null) {
            $valor = max($valor, (float) $config->valor_minimo);
        }
        if ($config->valor_maximo !== null) {
            $valor = min($valor, (float) $config->valor_maximo);
        }

        return [
            'valor_calculado'        => round($valor, 2),
            'tipo_regra_aplicada'    => $config->tipo_regra_valor,
            'origem_regra_aplicada'  => $config->nivel,
            'configuracao_calcao_id' => $config->id,
            'configuracao'           => $config,
        ];
    }

    // ──────────────────────────────────────────────────────
    // STRIPE — PRÉ-AUTORIZAÇÃO (HOLD)
    // ──────────────────────────────────────────────────────

    /**
     * Cria a pré-autorização no Stripe e persiste o calção.
     */
    public function autorizarCalcao(
        Reserva $reserva,
        ContratoCheckin $checkin,
        string $paymentMethodId,
        ?string $stripeCustomerId = null
    ): Calcao {
        $empresa = $this->gatewayConfig->empresaAtual();
        if (!$empresa) {
            throw new \RuntimeException('Empresa nao identificada para operacao de calcao.');
        }

        $stripeSecret = $empresa->stripe_secret;
        if (!$stripeSecret) {
            throw new \RuntimeException('Configuracao do Stripe nao encontrada para esta empresa.');
        }

        $calculo = $this->calcularValor($reserva);

        if ((float) $calculo['valor_calculado'] <= 0) {
            throw new \RuntimeException('Valor do calcao invalido ou nao configurado para esta reserva.');
        }

        $valorEmCentavos = (int) round($calculo['valor_calculado'] * 100);
        $moeda = strtolower($this->gatewayConfig->moeda($empresa) ?? 'brl');

        $stripe  = new StripeClient($stripeSecret);
        $payload = [
            'amount'               => $valorEmCentavos,
            'currency'             => $moeda,
            'payment_method'       => $paymentMethodId,
            'capture_method'       => 'manual',   // << HOLD, não captura
            'confirm'              => true,
            'automatic_payment_methods' => [
                'enabled'          => true,
                'allow_redirects'  => 'never',
            ],
            'metadata' => [
                'reserva_id'  => $reserva->id,
                'checkin_id'  => $checkin->id,
                'empresa_id'  => $empresa->id,
                'tipo'        => 'calcao',
            ],
        ];

        if ($stripeCustomerId) {
            $payload['customer'] = $stripeCustomerId;
        }

        try {
            $intent = $stripe->paymentIntents->create($payload);
        } catch (\Stripe\Exception\CardException $e) {
            $msg = $e->getError()?->message ?? 'Cartao recusado para o calcao.';
            Log::warning('CalcaoService: CartaoError', ['message' => $msg, 'reserva_id' => $reserva->id]);
            throw new \RuntimeException($msg);
        } catch (\Throwable $e) {
            Log::error('CalcaoService: StripeError', ['message' => $e->getMessage(), 'reserva_id' => $reserva->id]);
            throw new \RuntimeException('Falha ao criar pre-autorizacao do calcao no Stripe.');
        }

        $status = match ($intent->status) {
            'requires_capture' => Calcao::STATUS_AUTORIZADO,
            default            => Calcao::STATUS_FALHOU,
        };

        $calcao = Calcao::create([
            'empresa_id'             => $empresa->id,
            'reserva_id'             => $reserva->id,
            'contrato_checkin_id'    => $checkin->id,
            'cliente_id'             => $reserva->user_id,
            'payment_intent_id'      => $intent->id,
            'payment_method_id'      => $paymentMethodId,
            'stripe_customer_id'     => $stripeCustomerId,
            'valor_calculado'        => $calculo['valor_calculado'],
            'valor_autorizado'       => $calculo['valor_calculado'],
            'tipo_regra_aplicada'    => $calculo['tipo_regra_aplicada'],
            'origem_regra_aplicada'  => $calculo['origem_regra_aplicada'],
            'configuracao_calcao_id' => $calculo['configuracao_calcao_id'],
            'status'                 => $status,
            'data_hora_autorizacao'  => $status === Calcao::STATUS_AUTORIZADO ? now() : null,
            'usuario_responsavel_id' => Auth::id(),
            'stripe_response'        => $intent->toArray(),
        ]);

        if ($status === Calcao::STATUS_FALHOU) {
            throw new \RuntimeException('Pre-autorizacao criada mas nao confirmada pelo Stripe. Status: ' . $intent->status);
        }

        return $calcao;
    }

    // ──────────────────────────────────────────────────────
    // STRIPE — CAPTURA
    // ──────────────────────────────────────────────────────

    /**
     * Captura o valor (total ou parcial) do calção.
     */
    public function capturar(Calcao $calcao, float $valorCaptura, string $justificativa): Calcao
    {
        if (!$calcao->podeCapturar()) {
            throw new \RuntimeException('Este calcao nao pode ser capturado no status atual: ' . $calcao->status);
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
            Log::error('CalcaoService: CapturaError', ['message' => $e->getMessage(), 'calcao_id' => $calcao->id]);
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

        return $calcao->fresh();
    }

    // ──────────────────────────────────────────────────────
    // STRIPE — CANCELAR / LIBERAR HOLD
    // ──────────────────────────────────────────────────────

    /**
     * Cancela o hold (libera o calção sem cobrar).
     */
    public function cancelar(Calcao $calcao, string $observacao = ''): Calcao
    {
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
            Log::error('CalcaoService: CancelError', ['message' => $e->getMessage(), 'calcao_id' => $calcao->id]);
            throw new \RuntimeException('Falha ao cancelar o calcao no Stripe: ' . $e->getMessage());
        }

        $calcao->update([
            'status'                   => Calcao::STATUS_CANCELADO,
            'data_hora_cancelamento'   => now(),
            'observacao'               => $observacao,
            'usuario_responsavel_id'   => Auth::id(),
            'stripe_response'          => $intent->toArray(),
        ]);

        return $calcao->fresh();
    }
}
