<?php

namespace App\Actions\Reserva\Pagamento\Calcao;

use App\Models\Calcao;
use App\Models\ConfiguracaoCalcao;
use App\Models\Reserva;
use App\Services\Pagamentos\GatewayEmpresaConfigService;
use App\Services\ReservaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class AutorizarCalcaoAction
{
    public function __construct(
        private GatewayEmpresaConfigService $gatewayConfig,
        private ReservaService $reservaService,
    ) {}

    public function handle(Reserva $reserva, ?string $paymentMethodId): Calcao
    {
        $motoristaApp = (string) ($reserva->origem_cadastro ?? '') === 'motorista_app';

        if (!$motoristaApp && !$reserva->contratoCheckin) {
            throw new \RuntimeException('Contrato de check-in não encontrado.');
        }

        if (!$motoristaApp && $reserva->contratoCheckin->assinado !== 'S') {
            throw new \InvalidArgumentException('Contrato deve estar assinado antes do calção.');
        }

        $calcaoExistente = $reserva->calcao;
        if ($calcaoExistente && $calcaoExistente->status === Calcao::STATUS_AUTORIZADO) {
            return $calcaoExistente;
        }

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

        $stripeCustomerId = $reserva->usuario?->stripe_id ?? null;

        $stripe  = new StripeClient($stripeSecret);
        $payload = [
            'amount'               => $valorEmCentavos,
            'currency'             => $moeda,
            'payment_method'       => $paymentMethodId,
            'capture_method'       => 'manual',
            'confirm'              => true,
            'automatic_payment_methods' => [
                'enabled'          => true,
                'allow_redirects'  => 'never',
            ],
            'metadata' => [
                'reserva_id'  => $reserva->id,
                'checkin_id'  => $reserva->contratoCheckin?->id,
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
            Log::warning('AutorizarCalcaoAction: CartaoError', ['message' => $msg, 'reserva_id' => $reserva->id]);
            throw new \RuntimeException($msg);
        } catch (\Throwable $e) {
            Log::error('AutorizarCalcaoAction: StripeError', ['message' => $e->getMessage(), 'reserva_id' => $reserva->id]);
            throw new \RuntimeException('Falha ao criar pre-autorizacao do calcao no Stripe.');
        }

        $status = match ($intent->status) {
            'requires_capture' => Calcao::STATUS_AUTORIZADO,
            default            => Calcao::STATUS_FALHOU,
        };

        $calcao = Calcao::updateOrCreate(
            ['reserva_id' => $reserva->id],
            [
                'empresa_id'             => $empresa->id,
                'contrato_checkin_id'    => $reserva->contratoCheckin?->id,
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
            ]
        );

        $reserva->forceFill([
            'calcao_registrado_em' => now(),
        ])->save();

        if ($status === Calcao::STATUS_FALHOU) {
            throw new \RuntimeException('Pre-autorizacao criada mas nao confirmada pelo Stripe. Status: ' . $intent->status);
        }

        $this->reservaService->adicionaLog($reserva->id, Auth::id(), json_encode([
            'acao'        => 'Calcao autorizado',
            'calcao_id'   => $calcao->id,
            'valor'       => $calcao->valor_autorizado,
            'payment_intent' => $calcao->payment_intent_id,
        ]));

        return $calcao;
    }

    private function calcularValor(Reserva $reserva): array
    {
        $config = $this->resolverConfiguracao($reserva);

        if (!$config) {
            $valorMotoristaApp = $this->valorCalcaoMotoristaApp($reserva);
            if ($valorMotoristaApp > 0) {
                return [
                    'valor_calculado'        => $valorMotoristaApp,
                    'tipo_regra_aplicada'    => 'fixo',
                    'origem_regra_aplicada'  => 'motorista_app',
                    'configuracao_calcao_id' => null,
                    'configuracao'           => null,
                ];
            }

            return [
                'valor_calculado'        => 0,
                'tipo_regra_aplicada'    => null,
                'origem_regra_aplicada'  => null,
                'configuracao_calcao_id' => null,
                'configuracao'           => null,
            ];
        }

        $valorBase = (float) ($reserva->valor_total_final ?? $reserva->valor_total_com_desconto ?? 0);

        if ($config->tipo_regra_valor === ConfiguracaoCalcao::TIPO_PERCENTUAL) {
            $valor = $valorBase * ((float) $config->valor / 100);
        } else {
            $valor = (float) $config->valor;
        }

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

    private function resolverConfiguracao(Reserva $reserva): ?ConfiguracaoCalcao
    {
        $empresaId = $reserva->empresa_id;
        $grupoId   = $reserva->grupo_frota_id;
        $modeloId  = $reserva->modelo_id;

        if ($modeloId) {
            $config = ConfiguracaoCalcao::where('empresa_id', $empresaId)
                ->where('modelo_id', $modeloId)
                ->where('ativo', true)
                ->first();
            if ($config) return $config;
        }

        if ($grupoId) {
            $config = ConfiguracaoCalcao::where('empresa_id', $empresaId)
                ->where('grupo_frota_id', $grupoId)
                ->whereNull('modelo_id')
                ->where('ativo', true)
                ->first();
            if ($config) return $config;
        }

        return ConfiguracaoCalcao::where('empresa_id', $empresaId)
            ->whereNull('grupo_frota_id')
            ->whereNull('modelo_id')
            ->where('ativo', true)
            ->first();
    }

    private function valorCalcaoMotoristaApp(Reserva $reserva): float
    {
        if ((string) ($reserva->origem_cadastro ?? '') !== 'motorista_app') {
            return 0;
        }

        return round((float) ($reserva->valor_total_final ?? $reserva->valor_total_com_desconto ?? 0), 2);
    }
}
