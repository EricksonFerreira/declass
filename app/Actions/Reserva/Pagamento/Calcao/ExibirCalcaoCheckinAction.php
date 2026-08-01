<?php

namespace App\Actions\Reserva\Pagamento\Calcao;

use App\Models\ConfiguracaoCalcao;
use App\Models\Reserva;
use App\Services\ClienteStripeService;
use App\Services\Pagamentos\GatewayEmpresaConfigService;

class ExibirCalcaoCheckinAction
{
    public function __construct(
        private ClienteStripeService $clienteStripeService,
        private GatewayEmpresaConfigService $gatewayEmpresaConfigService,
    ) {}

    public function handle(Reserva $reserva): array
    {
        $calcaoExistente = $reserva->calcao;

        $calculo = $this->calcularValor($reserva);

        $empresa = $this->gatewayEmpresaConfigService->empresaAtual();
        $stripePublicKey = $empresa?->stripe_public_key ?? '';
        $metodosPagamento = [];
        $user = $reserva->usuario;

        if ($empresa && ! empty($user?->stripe_id) && $empresa->stripe_secret) {
            try {
                $metodosPagamento = $this->clienteStripeService->getPaymentMethods(
                    $user->stripe_id,
                    $empresa->stripe_secret
                );
            } catch (\Throwable $e) {
                $metodosPagamento = [];
            }
        }

        return [
            'calcaoExistente' => $calcaoExistente,
            'calculo' => $calculo,
            'stripePublicKey' => $stripePublicKey,
            'metodosPagamento' => $metodosPagamento,
            'empresa' => $empresa,
        ];
    }

    private function calcularValor(Reserva $reserva): array
    {
        $config = $this->resolverConfiguracao($reserva);

        if (! $config) {
            $valorMotoristaApp = $this->valorCalcaoMotoristaApp($reserva);
            if ($valorMotoristaApp > 0) {
                return [
                    'valor_calculado' => $valorMotoristaApp,
                    'tipo_regra_aplicada' => 'fixo',
                    'origem_regra_aplicada' => 'motorista_app',
                    'configuracao_calcao_id' => null,
                    'configuracao' => null,
                ];
            }

            return [
                'valor_calculado' => 0,
                'tipo_regra_aplicada' => null,
                'origem_regra_aplicada' => null,
                'configuracao_calcao_id' => null,
                'configuracao' => null,
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
            'valor_calculado' => round($valor, 2),
            'tipo_regra_aplicada' => $config->tipo_regra_valor,
            'origem_regra_aplicada' => $config->nivel,
            'configuracao_calcao_id' => $config->id,
            'configuracao' => $config,
        ];
    }

    private function resolverConfiguracao(Reserva $reserva): ?ConfiguracaoCalcao
    {
        $empresaId = $reserva->empresa_id;
        $grupoId = $reserva->grupo_frota_id;
        $modeloId = $reserva->modelo_id;

        if ($modeloId) {
            $config = ConfiguracaoCalcao::where('empresa_id', $empresaId)
                ->where('modelo_id', $modeloId)
                ->where('ativo', true)
                ->first();
            if ($config) {
                return $config;
            }
        }

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
