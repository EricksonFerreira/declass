<?php

namespace App\Actions\Reserva\Pagamento\Calcao;

use App\Models\Calcao;
use App\Models\ConfiguracaoCalcao;
use App\Models\Reserva;
use App\Models\Transacao;
use App\Services\ReservaService;
use App\Support\SchemaCache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegistrarCalcaoPresencialAction
{
    public function __construct(
        private ReservaService $reservaService,
    ) {}

    public function handle(Reserva $reserva, string $metodo, ?float $valorInput, ?string $observacaoInput): Calcao
    {
        $motoristaApp = (string) ($reserva->origem_cadastro ?? '') === 'motorista_app';

        if (!$motoristaApp && (!$reserva->contratoCheckin || $reserva->contratoCheckin->assinado !== 'S')) {
            throw new \InvalidArgumentException('O contrato de check-in deve estar assinado antes do calção.');
        }

        if ($reserva->calcao) {
            throw new \RuntimeException('Já existe um calção registrado para esta reserva.');
        }

        $calculo = $this->calcularValor($reserva);
        $valorCalculado = (float) ($calculo['valor_calculado'] ?? 0);
        if ($valorCalculado <= 0) {
            throw new \InvalidArgumentException('Esta reserva não requer calção.');
        }

        $valorPago = (float) ($valorInput ?: $valorCalculado);
        $observacao = trim((string) ($observacaoInput ?? ''));

        return DB::transaction(function () use ($reserva, $calculo, $valorCalculado, $valorPago, $metodo, $observacao) {
            $calcao = Calcao::create([
                'empresa_id' => $reserva->empresa_id ?? null,
                'reserva_id' => (int) $reserva->id,
                'contrato_checkin_id' => $reserva->contratoCheckin?->id,
                'cliente_id' => $reserva->user_id,
                'valor_calculado' => $valorCalculado,
                'valor_capturado' => $valorPago,
                'status' => Calcao::STATUS_CAPTURADO,
                'data_hora_captura' => now(),
                'usuario_responsavel_id' => Auth::id(),
                'observacao' => $observacao !== '' ? $observacao : ('Calção pago presencialmente via ' . $metodo),
                'tipo_regra_aplicada' => $calculo['tipo_regra_aplicada'] ?? null,
                'origem_regra_aplicada' => $calculo['origem_regra_aplicada'] ?? null,
                'configuracao_calcao_id' => $calculo['configuracao_calcao_id'] ?? null,
                'stripe_response' => [
                    'origem' => 'presencial',
                    'metodo' => $metodo,
                ],
            ]);

            $transacaoPayload = [
                'user_id' => $reserva->user_id,
                'pago_por_user_id' => Auth::id(),
                'reserva_id' => $reserva->id,
                'valor' => $valorPago,
                'moeda' => 'BRL',
                'metodo_pagamento' => $metodo,
                'status' => 'approved',
                'info_pagamento' => [
                    'tipo' => 'calcao',
                    'acao' => 'recebido_presencial',
                    'calcao_id' => $calcao->id,
                ],
            ];
            if (SchemaCache::hasColumn('transacoes', 'confirmado_em')) {
                $transacaoPayload['confirmado_em'] = now();
            }
            if (SchemaCache::hasColumn('transacoes', 'confirmado_por_user_id')) {
                $transacaoPayload['confirmado_por_user_id'] = Auth::id();
            }
            if (SchemaCache::hasColumn('transacoes', 'empresa_id')) {
                $transacaoPayload['empresa_id'] = $reserva->empresa_id ?? null;
            }

            Transacao::create($transacaoPayload);

            $this->reservaService->adicionaLog((int) $reserva->id, Auth::id(), json_encode([
                'acao' => 'Calção presencial registrado',
                'calcao_id' => $calcao->id,
                'metodo' => $metodo,
                'valor' => $valorPago,
            ]));

            return $calcao;
        });
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
