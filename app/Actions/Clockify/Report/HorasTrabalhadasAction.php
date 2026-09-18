<?php

namespace App\Actions\Clockify\Report;

use App\Models\ClockifyTimeTracker;

class HorasTrabalhadasAction
{
    /**
     * Horas apontadas agrupadas por projeto x colaborador.
     */
    public function handle(): array
    {
        $linhas = ClockifyTimeTracker::query()
            ->selectRaw('clockify_projeto_id, user_id, SUM(TIMESTAMPDIFF(SECOND, inicio, COALESCE(fim, NOW()))) as segundos')
            ->whereNotNull('inicio')
            ->groupBy('clockify_projeto_id', 'user_id')
            ->havingRaw('SUM(TIMESTAMPDIFF(SECOND, inicio, COALESCE(fim, NOW()))) > 0')
            ->with(['projeto:id,nome', 'user:id,nome'])
            ->get()
            ->sortByDesc('segundos')
            ->values();

        $porProjeto = $linhas
            ->groupBy('clockify_projeto_id')
            ->map(fn ($g) => [
                'nome' => $g->first()->projeto?->nome ?? 'Sem projeto',
                'horas' => round($g->sum('segundos') / 3600, 2),
            ])
            ->sortByDesc('horas')
            ->take(8);

        return [
            'linhas' => $linhas,
            'totais' => [
                'horas' => round($linhas->sum('segundos') / 3600, 2),
                'projetos' => $linhas->pluck('clockify_projeto_id')->unique()->count(),
                'colaboradores' => $linhas->pluck('user_id')->unique()->count(),
            ],
            'chart' => [
                'labels' => $porProjeto->pluck('nome')->values()->all(),
                'data' => $porProjeto->pluck('horas')->values()->all(),
            ],
        ];
    }
}
