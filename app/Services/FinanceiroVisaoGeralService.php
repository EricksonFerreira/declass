<?php

namespace App\Services;

use App\Models\ContaReceber;
use App\Models\Despesa;
use App\Models\Mensalidade;
use Illuminate\Support\Facades\DB;

/**
 * Consolida os numeros do painel "Visão Geral do Financeiro" para um
 * ano letivo (ano do vencimento) e, opcionalmente, uma escola. Os
 * models ja aplicam o escopo de empresa (tenant) via global scope.
 */
class FinanceiroVisaoGeralService
{
    /**
     * @return array{
     *   saidas_ano: float, a_receber_ano: float, recebido_hoje: float,
     *   inadimplencia_total: float, por_turma: \Illuminate\Support\Collection,
     *   maiores_atrasos: \Illuminate\Support\Collection,
     *   mensal: array<int, array{previsto: float, realizado: float}>,
     *   previsto_ano: float, realizado_ano: float
     * }
     */
    public function dados(int $ano, ?int $escolaId = null): array
    {
        $hoje = today();

        $mensalidades = Mensalidade::query()->whereYear('vencimento', $ano);
        $contas = ContaReceber::query()->whereYear('data_vencimento', $ano);
        $despesas = Despesa::query()->whereYear('data_vencimento', $ano);

        if ($escolaId) {
            $mensalidades->where('escola_id', $escolaId);
            $contas->where('escola_id', $escolaId);
            $despesas->where('escola_id', $escolaId);
        }

        $saidasAno = (float) (clone $despesas)->where('status', 'paga')->sum('valor');

        $aReceberAno = (float) (clone $mensalidades)->where('status', 'pendente')->sum('valor')
            + (float) (clone $contas)->where('status', 'prevista')->sum('valor');

        $recebidoHoje = (float) (clone $mensalidades)->where('status', 'paga')
            ->whereDate('data_pagamento', $hoje)->sum('valor')
            + (float) (clone $contas)->where('status', 'recebida')
                ->whereDate('data_recebimento', $hoje)->sum('valor');

        $inadimplenciaTotal = (float) (clone $mensalidades)->where('status', 'pendente')
            ->whereDate('vencimento', '<', $hoje)->sum('valor')
            + (float) (clone $contas)->where('status', 'prevista')
                ->whereDate('data_vencimento', '<', $hoje)->sum('valor');

        $porTurma = $this->inadimplenciaPorTurma($ano, $escolaId, $hoje);
        $maioresAtrasos = $this->maioresAtrasos($ano, $escolaId, $hoje);
        $mensal = $this->receitaMensal($ano, $escolaId);

        $previstoAno = array_sum(array_column($mensal, 'previsto'));
        $realizadoAno = array_sum(array_column($mensal, 'realizado'));

        return [
            'saidas_ano' => $saidasAno,
            'a_receber_ano' => $aReceberAno,
            'recebido_hoje' => $recebidoHoje,
            'inadimplencia_total' => $inadimplenciaTotal,
            'por_turma' => $porTurma,
            'maiores_atrasos' => $maioresAtrasos,
            'mensal' => $mensal,
            'previsto_ano' => $previstoAno,
            'realizado_ano' => $realizadoAno,
        ];
    }

    /** Anos com movimentacao financeira (para o select do filtro). */
    public function anosDisponiveis(): array
    {
        $anos = collect()
            ->merge(Mensalidade::query()->selectRaw('(SUBSTR(vencimento, 1, 4) + 0) as a')->pluck('a'))
            ->merge(ContaReceber::query()->selectRaw('(SUBSTR(data_vencimento, 1, 4) + 0) as a')->pluck('a'))
            ->merge(Despesa::query()->selectRaw('(SUBSTR(data_vencimento, 1, 4) + 0) as a')->pluck('a'))
            ->filter()->map(fn ($a) => (int) $a)->unique();

        $anos->push((int) date('Y'));

        return $anos->unique()->sortDesc()->values()->all();
    }

    /** Mensalidades vencidas nao pagas agrupadas por turma (maiores primeiro). */
    private function inadimplenciaPorTurma(int $ano, ?int $escolaId, $hoje)
    {
        return Mensalidade::query()
            ->join('matriculas', 'mensalidades.matricula_id', '=', 'matriculas.id')
            ->join('turmas', 'matriculas.turma_id', '=', 'turmas.id')
            ->where('mensalidades.status', 'pendente')
            ->whereDate('mensalidades.vencimento', '<', $hoje)
            ->whereYear('mensalidades.vencimento', $ano)
            ->when($escolaId, fn ($q) => $q->where('mensalidades.escola_id', $escolaId))
            ->groupBy('turmas.id', 'turmas.nome')
            ->select('turmas.nome', DB::raw('SUM(mensalidades.valor) as total'), DB::raw('COUNT(*) as qtd'))
            ->orderByDesc('total')
            ->limit(8)
            ->get();
    }

    /** Maiores parcelas vencidas em aberto (aluno/turma/valor). */
    private function maioresAtrasos(int $ano, ?int $escolaId, $hoje)
    {
        return Mensalidade::query()
            ->join('matriculas', 'mensalidades.matricula_id', '=', 'matriculas.id')
            ->join('alunos', 'matriculas.aluno_id', '=', 'alunos.id')
            ->join('pessoas', 'alunos.pessoa_id', '=', 'pessoas.id')
            ->leftJoin('turmas', 'matriculas.turma_id', '=', 'turmas.id')
            ->where('mensalidades.status', 'pendente')
            ->whereDate('mensalidades.vencimento', '<', $hoje)
            ->whereYear('mensalidades.vencimento', $ano)
            ->when($escolaId, fn ($q) => $q->where('mensalidades.escola_id', $escolaId))
            ->select(
                'pessoas.nome as aluno',
                'turmas.nome as turma',
                'mensalidades.valor',
                'mensalidades.vencimento'
            )
            ->orderByDesc('mensalidades.valor')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $item->dias_atraso = $item->vencimento ? \Illuminate\Support\Carbon::parse($item->vencimento)->diffInDays(now()) : 0;

                return $item;
            });
    }

    /** Receita prevista x realizada por mes — mensalidades + contas a receber. */
    private function receitaMensal(int $ano, ?int $escolaId): array
    {
        $previstoMens = $this->porMes(
            Mensalidade::query()->where('status', '!=', 'cancelada'),
            'vencimento', $ano, $escolaId
        );
        $realizadoMens = $this->porMes(
            Mensalidade::query()->where('status', 'paga'),
            'vencimento', $ano, $escolaId
        );
        $previstoContas = $this->porMes(
            ContaReceber::query()->where('status', '!=', 'cancelada'),
            'data_vencimento', $ano, $escolaId
        );
        $realizadoContas = $this->porMes(
            ContaReceber::query()->where('status', 'recebida'),
            'data_vencimento', $ano, $escolaId
        );

        $mensal = [];
        for ($m = 1; $m <= 12; $m++) {
            $mensal[$m] = [
                'previsto' => (float) ($previstoMens[$m] ?? 0) + (float) ($previstoContas[$m] ?? 0),
                'realizado' => (float) ($realizadoMens[$m] ?? 0) + (float) ($realizadoContas[$m] ?? 0),
            ];
        }

        return $mensal;
    }

    /** @return array<int, float> mes => soma dos valores do ano */
    private function porMes($query, string $coluna, int $ano, ?int $escolaId): array
    {
        return $query
            ->whereYear($coluna, $ano)
            ->when($escolaId, fn ($q) => $q->where('escola_id', $escolaId))
            ->selectRaw("(SUBSTR($coluna, 6, 2) + 0) as mes, SUM(valor) as total")
            ->groupBy('mes')
            ->pluck('total', 'mes')
            ->all();
    }
}
