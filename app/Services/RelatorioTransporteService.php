<?php

namespace App\Services;

use App\Models\RotaTransporte;
use Illuminate\Support\Collection;

/**
 * Relatorio de ocupacao do transporte: uma linha por rota
 * com veiculo, capacidade, passageiros ativos e % de ocupacao.
 */
class RelatorioTransporteService
{
    /**
     * Rotas com metricas de ocupacao. Filtros: escola_id,
     * situacao (sem_veiculo|sem_capacidade|superlotada|lotada|ok).
     */
    public function relatorio(array $filtros = []): Collection
    {
        $query = RotaTransporte::query()
            ->with(['escola:id,nome', 'veiculo:id,placa,modelo,capacidade,motorista_nome'])
            ->withCount([
                'paradas',
                'passageiros as passageiros_ativos_count' => fn ($q) => $q->where('ativo', 'S'),
            ]);

        // Escopo de tenant (modelos nao tem global scope — filtro explicito)
        $user = auth()->user();
        $empresaId = $user?->isSuperAdmin()
            ? app(\App\Support\EmpresaContext::class)->empresaId()
            : $user?->empresa_id;
        if ($empresaId !== null) {
            $query->where('rotas_transporte.empresa_id', (int) $empresaId);
        }

        $linhas = $query
            ->when(! empty($filtros['escola_id']), fn ($q) => $q->where('escola_id', (int) $filtros['escola_id']))
            ->when(! empty($filtros['apenas_ativas']), fn ($q) => $q->where('ativo', 'S'))
            ->orderBy('nome')
            ->get()
            ->map(fn (RotaTransporte $rota) => $this->linha($rota));

        if (! empty($filtros['situacao'])) {
            $linhas = $linhas->where('situacao', $filtros['situacao'])->values();
        }

        return $linhas;
    }

    /** Totais do relatorio respeitando os mesmos filtros. */
    public function resumo(array $filtros = []): array
    {
        $linhas = $this->relatorio($filtros);

        $comCapacidade = $linhas->filter(fn ($l) => $l['pct'] !== null);

        return [
            'rotas' => $linhas->count(),
            'passageiros' => (int) $linhas->sum('passageiros'),
            'veiculos' => $linhas->whereNotNull('veiculo_id')->unique('veiculo_id')->count(),
            'ocupacao_media' => $comCapacidade->isNotEmpty()
                ? round($comCapacidade->avg('pct'), 1)
                : null,
            'lotadas' => $linhas->whereIn('situacao', ['lotada', 'superlotada'])->count(),
            'superlotadas' => $linhas->where('situacao', 'superlotada')->count(),
            'sem_veiculo' => $linhas->where('situacao', 'sem_veiculo')->count(),
        ];
    }

    private function linha(RotaTransporte $rota): array
    {
        $capacidade = $rota->veiculo?->capacidade;
        $passageiros = (int) $rota->passageiros_ativos_count;

        $pct = ($capacidade && $capacidade > 0)
            ? round(($passageiros / $capacidade) * 100, 1)
            : null;

        $situacao = match (true) {
            $rota->veiculo_id === null => 'sem_veiculo',
            $pct === null => 'sem_capacidade',
            $pct > 100 => 'superlotada',
            $pct >= 90 => 'lotada',
            default => 'ok',
        };

        return [
            'rota' => $rota,
            'escola' => $rota->escola?->nome,
            'veiculo' => $rota->veiculo,
            'capacidade' => $capacidade,
            'passageiros' => $passageiros,
            'paradas' => (int) $rota->paradas_count,
            'vagas_livres' => $capacidade !== null ? max(0, $capacidade - $passageiros) : null,
            'pct' => $pct,
            'situacao' => $situacao,
        ];
    }
}
