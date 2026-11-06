<?php

namespace App\Services;

use App\Models\ContaReceber;
use App\Models\Despesa;
use App\Models\Mensalidade;

/**
 * DRE (Demonstrativo de Resultado) por competencia, em regime de
 * competencia:
 * - receitas: mensalidades pagas agrupadas pela competencia cobrada;
 *   receita bruta = valor do plano, deducoes = bolsa/desconto
 *   (diferenca plano x valor cobrado), receita liquida = valor pago;
 *   demais receitas = contas a receber recebidas por mes de vencimento;
 * - despesas: contas a pagar por categoria, pelo mes de vencimento,
 *   exceto canceladas;
 * - resultado = receita liquida - despesas do mes.
 */
class DreService
{
    /**
     * Cada serie tem os meses 1-12 + chave 'total'.
     *
     * @return array{bruta: array, descontos: array, outras: array, liquida: array, despesas: array, despesasTotais: array, resultado: array}
     */
    public function demonstrativo(int $ano, ?int $escolaId, int $empresaId): array
    {
        $mensal = $this->agregarReceitas($ano, $escolaId, $empresaId);
        $outrasReceitas = $this->agregarOutrasReceitas($ano, $escolaId, $empresaId);
        $despesasCat = $this->agregarDespesas($ano, $escolaId, $empresaId);

        $bruta = $this->serieVazia();
        $descontos = $this->serieVazia();
        $outras = $this->serieVazia();
        $liquida = $this->serieVazia();
        $resultado = $this->serieVazia();
        $despesasTotais = $this->serieVazia();

        foreach (range(1, 12) as $mes) {
            $m = $mensal[$mes] ?? ['bruta' => 0.0, 'liquida' => 0.0];
            $bruta[$mes] = $m['bruta'];
            $outras[$mes] = $outrasReceitas[$mes] ?? 0.0;
            $liquida[$mes] = $m['liquida'] + $outras[$mes];
            $descontos[$mes] = max(0.0, $m['bruta'] - $m['liquida']);
        }

        foreach ($despesasCat as $categoria => $serie) {
            foreach (range(1, 12) as $mes) {
                $despesasTotais[$mes] += $serie[$mes] ?? 0.0;
            }
        }

        foreach (range(1, 12) as $mes) {
            $resultado[$mes] = $liquida[$mes] - $despesasTotais[$mes];
        }

        foreach (['bruta', 'descontos', 'outras', 'liquida', 'resultado', 'despesasTotais'] as $k) {
            $$k = $this->comTotal($$k);
        }

        $despesasComTotal = [];
        foreach ($despesasCat as $categoria => $serie) {
            $despesasComTotal[$categoria] = $this->comTotal($serie);
        }

        return [
            'bruta' => $bruta,
            'descontos' => $descontos,
            'outras' => $outras,
            'liquida' => $liquida,
            'despesas' => $despesasComTotal,
            'despesasTotais' => $despesasTotais,
            'resultado' => $resultado,
        ];
    }

    /** Mensalidades pagas: bruta (plano) e liquida (valor pago) por mes de competencia. */
    private function agregarReceitas(int $ano, ?int $escolaId, int $empresaId): array
    {
        $rows = Mensalidade::query()
            ->join('matriculas', 'mensalidades.matricula_id', '=', 'matriculas.id')
            ->join('turmas', 'matriculas.turma_id', '=', 'turmas.id')
            ->leftJoin('planos_mensalidade', 'mensalidades.plano_mensalidade_id', '=', 'planos_mensalidade.id')
            ->where('mensalidades.empresa_id', $empresaId)
            ->where('mensalidades.status', 'paga')
            ->where('mensalidades.competencia', 'like', $ano.'-%')
            ->whereNull('matriculas.deleted_at')
            ->whereNull('turmas.deleted_at')
            ->when($escolaId, fn ($q) => $q->where('turmas.escola_id', $escolaId))
            ->selectRaw('
                (SUBSTR(mensalidades.competencia, 6, 2) + 0) as mes,
                SUM(COALESCE(planos_mensalidade.valor, mensalidades.valor)) as bruta,
                SUM(mensalidades.valor) as liquida
            ')
            ->groupBy('mes')
            ->get();

        $resultado = [];
        foreach ($rows as $row) {
            $resultado[(int) $row->mes] = [
                'bruta' => (float) $row->bruta,
                'liquida' => (float) $row->liquida,
            ];
        }

        return $resultado;
    }

    /**
     * Outras receitas: contas a receber recebidas, agrupadas pelo
     * mes de vencimento (competencia da cobranca).
     *
     * @return array<int, float>
     */
    private function agregarOutrasReceitas(int $ano, ?int $escolaId, int $empresaId): array
    {
        $rows = ContaReceber::query()
            ->where('empresa_id', $empresaId)
            ->where('status', 'recebida')
            ->whereYear('data_vencimento', $ano)
            ->when($escolaId, fn ($q) => $q->where('escola_id', $escolaId))
            ->selectRaw('(SUBSTR(data_vencimento, 6, 2) + 0) as mes, SUM(valor) as total')
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $resultado = [];
        foreach ($rows as $mes => $total) {
            $resultado[(int) $mes] = (float) $total;
        }

        return $resultado;
    }

    /** Despesas nao canceladas por categoria e mes de vencimento. */
    private function agregarDespesas(int $ano, ?int $escolaId, int $empresaId): array
    {
        $rows = Despesa::query()
            ->where('empresa_id', $empresaId)
            ->where('status', '!=', 'cancelada')
            ->whereYear('data_vencimento', $ano)
            ->when($escolaId, fn ($q) => $q->where('escola_id', $escolaId))
            ->selectRaw('categoria, (SUBSTR(data_vencimento, 6, 2) + 0) as mes, SUM(valor) as total')
            ->groupBy('categoria', 'mes')
            ->get();

        $resultado = [];
        foreach ($rows as $row) {
            $cat = $row->categoria ?: 'outros';
            $resultado[$cat][(int) $row->mes] = (float) $row->total;
        }

        // garante serie completa 1-12 por categoria
        foreach ($resultado as $cat => $serie) {
            foreach (range(1, 12) as $mes) {
                $resultado[$cat][$mes] = $resultado[$cat][$mes] ?? 0.0;
            }
            ksort($resultado[$cat]);
        }

        uksort($resultado, fn ($a, $b) => array_search($a, array_keys(Despesa::CATEGORIAS)) <=> array_search($b, array_keys(Despesa::CATEGORIAS)));

        return $resultado;
    }

    private function serieVazia(): array
    {
        $serie = [];
        foreach (range(1, 12) as $mes) {
            $serie[$mes] = 0.0;
        }

        return $serie;
    }

    private function comTotal(array $serie): array
    {
        $serie['total'] = array_sum($serie);

        return $serie;
    }
}
