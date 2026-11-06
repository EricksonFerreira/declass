<?php

namespace App\Services;

use App\Models\ContaReceber;
use App\Models\Despesa;
use App\Models\Mensalidade;

/**
 * Fluxo de caixa anual: para cada mes do ano informado consolida
 * realizado (pago/recebido, por data_pagamento) e previsto
 * (a pagar/a receber, por vencimento), separado por receitas
 * (mensalidades) e despesas (contas a pagar).
 */
class FluxoCaixaService
{
    /**
     * @return array{meses: array<int, array>, totais: array}
     */
    public function anual(int $ano, ?int $escolaId, int $empresaId): array
    {
        $receitas = $this->agregarReceitas($ano, $escolaId, $empresaId);
        $despesas = $this->agregarDespesas($ano, $escolaId, $empresaId);

        $meses = [];
        $totais = [
            'rec_realizado' => 0.0, 'rec_previsto' => 0.0,
            'des_realizado' => 0.0, 'des_previsto' => 0.0,
            'saldo' => 0.0, 'saldo_projetado' => 0.0,
        ];

        for ($mes = 1; $mes <= 12; $mes++) {
            $rec = $receitas[$mes] ?? ['realizado' => 0.0, 'previsto' => 0.0];
            $des = $despesas[$mes] ?? ['realizado' => 0.0, 'previsto' => 0.0];

            $saldo = $rec['realizado'] - $des['realizado'];
            $projetado = ($rec['realizado'] + $rec['previsto']) - ($des['realizado'] + $des['previsto']);

            $meses[$mes] = [
                'rec_realizado' => $rec['realizado'],
                'rec_previsto' => $rec['previsto'],
                'des_realizado' => $des['realizado'],
                'des_previsto' => $des['previsto'],
                'saldo' => $saldo,
                'saldo_projetado' => $projetado,
            ];

            $totais['rec_realizado'] += $rec['realizado'];
            $totais['rec_previsto'] += $rec['previsto'];
            $totais['des_realizado'] += $des['realizado'];
            $totais['des_previsto'] += $des['previsto'];
            $totais['saldo'] += $saldo;
            $totais['saldo_projetado'] += $projetado;
        }

        return ['meses' => $meses, 'totais' => $totais];
    }

    /**
     * Receitas realizadas + previstas: mensalidades (pagas por
     * data_pagamento, pendentes por vencimento) + contas a receber
     * (recebidas por data_recebimento, previstas por vencimento).
     */
    private function agregarReceitas(int $ano, ?int $escolaId, int $empresaId): array
    {
        $fontes = [
            $this->agregarMensalidades($ano, $escolaId, $empresaId),
            $this->agregarContasReceber($ano, $escolaId, $empresaId),
        ];

        $resultado = [];
        foreach (range(1, 12) as $mes) {
            $resultado[$mes] = ['realizado' => 0.0, 'previsto' => 0.0];
            foreach ($fontes as $fonte) {
                $resultado[$mes]['realizado'] += $fonte[$mes]['realizado'] ?? 0.0;
                $resultado[$mes]['previsto'] += $fonte[$mes]['previsto'] ?? 0.0;
            }
        }

        return $resultado;
    }

    /** Receitas: mensalidades pagas por data_pagamento + pendentes por vencimento. */
    private function agregarMensalidades(int $ano, ?int $escolaId, int $empresaId): array
    {
        $base = Mensalidade::query()
            ->join('matriculas', 'mensalidades.matricula_id', '=', 'matriculas.id')
            ->join('turmas', 'matriculas.turma_id', '=', 'turmas.id')
            ->where('mensalidades.empresa_id', $empresaId)
            ->whereNull('matriculas.deleted_at')
            ->whereNull('turmas.deleted_at')
            ->when($escolaId, fn ($q) => $q->where('turmas.escola_id', $escolaId));

        $resultado = [];

        // realizado: pagas, agrupado pelo mes do pagamento
        $pagas = (clone $base)
            ->where('mensalidades.status', 'paga')
            ->whereYear('mensalidades.data_pagamento', $ano)
            ->selectRaw('(SUBSTR(mensalidades.data_pagamento, 6, 2) + 0) as mes, SUM(mensalidades.valor) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        // previsto: pendentes, agrupado pelo mes do vencimento
        $pendentes = (clone $base)
            ->where('mensalidades.status', 'pendente')
            ->whereYear('mensalidades.vencimento', $ano)
            ->selectRaw('(SUBSTR(mensalidades.vencimento, 6, 2) + 0) as mes, SUM(mensalidades.valor) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        foreach (range(1, 12) as $mes) {
            $resultado[$mes] = [
                'realizado' => (float) ($pagas[$mes] ?? 0),
                'previsto' => (float) ($pendentes[$mes] ?? 0),
            ];
        }

        return $resultado;
    }

    /** Contas a receber: recebidas por data_recebimento + previstas por vencimento. */
    private function agregarContasReceber(int $ano, ?int $escolaId, int $empresaId): array
    {
        $base = ContaReceber::query()
            ->where('empresa_id', $empresaId)
            ->when($escolaId, fn ($q) => $q->where('escola_id', $escolaId));

        $resultado = [];

        $recebidas = (clone $base)
            ->where('status', 'recebida')
            ->whereYear('data_recebimento', $ano)
            ->selectRaw('(SUBSTR(data_recebimento, 6, 2) + 0) as mes, SUM(valor) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $previstas = (clone $base)
            ->where('status', 'prevista')
            ->whereYear('data_vencimento', $ano)
            ->selectRaw('(SUBSTR(data_vencimento, 6, 2) + 0) as mes, SUM(valor) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        foreach (range(1, 12) as $mes) {
            $resultado[$mes] = [
                'realizado' => (float) ($recebidas[$mes] ?? 0),
                'previsto' => (float) ($previstas[$mes] ?? 0),
            ];
        }

        return $resultado;
    }

    /** Despesas: pagas por data_pagamento + previstas por vencimento. */
    private function agregarDespesas(int $ano, ?int $escolaId, int $empresaId): array
    {
        $base = Despesa::query()
            ->where('empresa_id', $empresaId)
            ->when($escolaId, fn ($q) => $q->where('escola_id', $escolaId));

        $resultado = [];

        $pagas = (clone $base)
            ->where('status', 'paga')
            ->whereYear('data_pagamento', $ano)
            ->selectRaw('(SUBSTR(data_pagamento, 6, 2) + 0) as mes, SUM(valor) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $previstas = (clone $base)
            ->where('status', 'prevista')
            ->whereYear('data_vencimento', $ano)
            ->selectRaw('(SUBSTR(data_vencimento, 6, 2) + 0) as mes, SUM(valor) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        foreach (range(1, 12) as $mes) {
            $resultado[$mes] = [
                'realizado' => (float) ($pagas[$mes] ?? 0),
                'previsto' => (float) ($previstas[$mes] ?? 0),
            ];
        }

        return $resultado;
    }
}
