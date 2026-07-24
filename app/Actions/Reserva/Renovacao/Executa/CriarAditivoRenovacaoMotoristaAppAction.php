<?php

namespace App\Actions\Reserva\Renovacao\Executa;

use App\Models\ReservaMotoristaApp;
use App\Models\ReservaMotoristaAppAditivo;
use App\Models\ReservaMotoristaAppPagamento;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CriarAditivoRenovacaoMotoristaAppAction
{
    public function handle(
        ReservaMotoristaApp $reserva,
        ReservaMotoristaAppPagamento $pagamento,
        array $dados
    ): ReservaMotoristaAppAditivo {
        // Determine next aditivo number for this reservation
        $numeroAditivo = ReservaMotoristaAppAditivo::where('reserva_motorista_app_id', $reserva->id)
            ->lockForUpdate()
            ->max('numero_aditivo') + 1;

        $metadados = $pagamento->metadados ?? [];
        $planoNovoId = $metadados['plano_id'] ?? $reserva->plano_motorista_app_id;
        $planoAnteriorId = $metadados['plano_anterior_id'] ?? $reserva->plano_motorista_app_id;
        $valorRenovacao = $metadados['valor_unitario'] ?? 0;
        $valorOpcionais = $metadados['valor_opcionais_por_ciclo'] ?? 0;
        $valorDesconto = $metadados['desconto_cupom'] ?? 0;
        $valorTotal = $pagamento->valor_total;

        $dataInicioNova = $pagamento->data_inicio_prevista;
        $dataFimNova = $pagamento->data_fim_prevista;

        return ReservaMotoristaAppAditivo::create([
            'empresa_id'               => $reserva->empresa_id,
            'reserva_motorista_app_id' => $reserva->id,
            'pagamento_id'             => $pagamento->id,
            'contrato_checkin_id'      => $reserva->contratoCheckin()?->id,
            'numero_aditivo'           => $numeroAditivo,
            'data_inicio_anterior'     => $reserva->data_inicio,
            'data_fim_anterior'        => $reserva->data_fim,
            'data_inicio_nova'         => $dataInicioNova,
            'data_fim_nova'            => $dataFimNova,
            'plano_anterior_id'        => $planoAnteriorId,
            'plano_novo_id'            => $planoNovoId,
            'valor_renovacao'          => $valorRenovacao,
            'valor_opcionais'          => $valorOpcionais,
            'valor_desconto'           => $valorDesconto,
            'valor_total'              => $valorTotal,
            'status'                   => 'aguardando_pagamento',
            'snapshot_json' => [
                'reserva' => [
                    'id' => $reserva->id,
                    'codigo' => $reserva->codigoReserva?->codigo,
                ],
                'periodo_anterior' => [
                    'inicio' => $reserva->data_inicio,
                    'fim' => $reserva->data_fim,
                ],
                'novo_periodo' => [
                    'inicio' => $dataInicioNova,
                    'fim' => $dataFimNova,
                    'periodos' => $dados['periodos'] ?? null,
                ],
                'plano_anterior' => [
                    'id' => $planoAnteriorId,
                ],
                'plano_novo' => [
                    'id' => $planoNovoId,
                ],
                'valores' => [
                    'renovacao' => $valorRenovacao,
                    'opcionais' => $valorOpcionais,
                    'desconto' => $valorDesconto,
                    'total' => $valorTotal,
                ],
                'metadados_pagamento' => $metadados,
            ],
            'observacoes' => $dados['observacoes'] ?? null,
        ]);
    }
}
