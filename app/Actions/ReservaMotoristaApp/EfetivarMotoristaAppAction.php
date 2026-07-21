<?php

namespace App\Actions\ReservaMotoristaApp;

use App\Models\ReservaMotoristaApp;
use App\Models\ReservaMotoristaAppPagamento;
use Carbon\Carbon;

class EfetivarMotoristaAppAction
{
    public function handle(
        ReservaMotoristaApp $reserva,
        array $dados,
        callable $aplicarPeriodo,
        callable $rotuloPeriodo
    ): ReservaMotoristaApp {
        $plano = $reserva->planoMotoristaApp;
        if (! $plano) {
            throw new \RuntimeException('Plano não encontrado para efetivação.');
        }

        $periodoContrato = (string) ($reserva->periodo_contrato ?: ($plano->periodo ?? 'semana'));
        $dataInicio = ! empty($dados['data_inicio']) ? Carbon::parse($dados['data_inicio']) : now();
        $dataFim = $aplicarPeriodo($dataInicio->copy(), $periodoContrato, 1);

        $reserva->update([
            'status' => 'ativa',
            'contrato_efetivado_em' => now(),
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
        ]);

        return $reserva;
    }
}
