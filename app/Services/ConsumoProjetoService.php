<?php

namespace App\Services;

use App\Models\ClockifyProjeto;
use App\Models\ClockifyProjetoEstimativa;
use App\Models\ClockifyTimeTracker;
use App\Models\Funcao;
use App\Support\EmpresaContext;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Relatório "horas consumidas × horas estimadas" por projeto e por função.
 */
class ConsumoProjetoService
{
    public const SEM_FUNCAO = 0;

    /**
     * Consumo em segundos agrupado por projeto × função.
     *
     * @return Collection<int, object{clockify_projeto_id:int, funcao_id:int|null, funcao_nome:string|null, segundos:float}>
     */
    public function consumoPorFuncao(int $empresaId, ?int $projetoId = null): Collection
    {
        return DB::table('clockify_time_trackers as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('funcoes as f', 'f.id', '=', 'u.funcao_id')
            ->where('t.empresa_id', $empresaId)
            ->when($projetoId, fn ($q) => $q->where('t.clockify_projeto_id', $projetoId))
            ->whereNotNull('t.inicio')
            ->groupBy('t.clockify_projeto_id', 'u.funcao_id', 'f.nome')
            ->selectRaw('t.clockify_projeto_id, u.funcao_id, f.nome as funcao_nome, SUM(GREATEST(TIMESTAMPDIFF(SECOND, t.inicio, COALESCE(t.fim, NOW())), 0)) as segundos')
            ->get();
    }

    /**
     * Consumo em segundos agrupado por membro dentro de um projeto.
     * O custo/hora vem da função do colaborador.
     *
     * @return Collection<int, object{user_id:int, nome:string, email:string|null, funcao_id:int|null, funcao_nome:string|null, custo_hora:float, segundos:float}>
     */
    public function consumoPorMembro(ClockifyProjeto $projeto): Collection
    {
        return DB::table('clockify_time_trackers as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('funcoes as f', 'f.id', '=', 'u.funcao_id')
            ->where('t.clockify_projeto_id', $projeto->id)
            ->whereNotNull('t.inicio')
            ->groupBy('t.user_id', 'u.nome', 'u.email', 'u.funcao_id', 'f.nome', 'f.custo_hora')
            ->selectRaw('t.user_id, u.nome, u.email, u.funcao_id, f.nome as funcao_nome, COALESCE(f.custo_hora, 0) as custo_hora, SUM(GREATEST(TIMESTAMPDIFF(SECOND, t.inicio, COALESCE(t.fim, NOW())), 0)) as segundos')
            ->orderBy('u.nome')
            ->get();
    }

    /**
     * Estimativas agrupadas por projeto × função.
     *
     * @return Collection<int, object{clockify_projeto_id:int, funcao_id:int, funcao_nome:string|null, horas:float}>
     */
    public function estimativasPorFuncao(int $empresaId, ?int $projetoId = null): Collection
    {
        return ClockifyProjetoEstimativa::query()
            ->join('funcoes as f', 'f.id', '=', 'clockify_projeto_estimativas.funcao_id')
            ->where('clockify_projeto_estimativas.empresa_id', $empresaId)
            ->when($projetoId, fn ($q) => $q->where('clockify_projeto_estimativas.clockify_projeto_id', $projetoId))
            ->groupBy('clockify_projeto_estimativas.clockify_projeto_id', 'clockify_projeto_estimativas.funcao_id', 'f.nome')
            ->selectRaw('clockify_projeto_estimativas.clockify_projeto_id, clockify_projeto_estimativas.funcao_id, f.nome as funcao_nome, SUM(clockify_projeto_estimativas.horas_estimadas) as horas')
            ->get();
    }

    /**
     * Linhas "função × consumida × estimada × %" de um projeto (aba A02).
     *
     * @return array<int, array{funcao_id:int, nome:string, consumida:float, estimada:float|null, pct:float|null, custo:float}>
     */
    public function linhasPorFuncao(ClockifyProjeto $projeto): array
    {
        $empresaId = (int) $projeto->empresa_id;

        $consumo = $this->consumoPorFuncao($empresaId, $projeto->id)
            ->keyBy(fn ($r) => (int) ($r->funcao_id ?? self::SEM_FUNCAO));

        $estimadas = $this->estimativasPorFuncao($empresaId, $projeto->id)
            ->keyBy(fn ($r) => (int) $r->funcao_id);

        $funcoesIds = $consumo->keys()->merge($estimadas->keys())->unique()->sort()->values();

        return $funcoesIds->map(function (int $funcaoId) use ($consumo, $estimadas) {
            $cons = $consumo->get($funcaoId);
            $est = $estimadas->get($funcaoId);

            $consumida = $cons ? round(((float) $cons->segundos) / 3600, 2) : 0.0;
            $estimada = $est ? (float) $est->horas : null;
            $pct = $estimada !== null && $estimada > 0
                ? round(($consumida / $estimada) * 100, 2)
                : null;

            return [
                'funcao_id' => $funcaoId,
                'nome' => $cons?->funcao_nome ?? $est?->funcao_nome ?? 'Sem função',
                'consumida' => $consumida,
                'estimada' => $estimada,
                'pct' => $pct,
                'custo' => 0.0, // preenchido via membros quando necessário
            ];
        })->all();
    }

    /**
     * Resumo de um projeto para a aba A01 (totais por função + compilado).
     */
    public function resumoProjeto(ClockifyProjeto $projeto, ?Collection $consumoTodos = null, ?Collection $estimativasTodos = null): array
    {
        $empresaId = (int) $projeto->empresa_id;
        $consumoTodos = $consumoTodos ?? $this->consumoPorFuncao($empresaId);
        $estimativasTodos = $estimativasTodos ?? $this->estimativasPorFuncao($empresaId);

        $porFuncao = [];
        $totCons = 0.0;
        $totEst = 0.0;
        $temEstimativa = false;

        foreach ($consumoTodos->where('clockify_projeto_id', $projeto->id) as $c) {
            $fid = (int) ($c->funcao_id ?? self::SEM_FUNCAO);
            $horas = ((float) $c->segundos) / 3600;
            $porFuncao[$fid]['consumida'] = ($porFuncao[$fid]['consumida'] ?? 0) + $horas;
            $porFuncao[$fid]['nome'] = $c->funcao_nome ?? 'Sem função';
            $totCons += $horas;
        }

        foreach ($estimativasTodos->where('clockify_projeto_id', $projeto->id) as $e) {
            $fid = (int) $e->funcao_id;
            $porFuncao[$fid]['estimada'] = ($porFuncao[$fid]['estimada'] ?? 0) + (float) $e->horas;
            $porFuncao[$fid]['nome'] = $e->funcao_nome ?? 'Sem função';
            $totEst += (float) $e->horas;
            $temEstimativa = true;
        }

        foreach ($porFuncao as $fid => &$f) {
            $cons = $f['consumida'] ?? 0.0;
            $est = $f['estimada'] ?? null;
            $f['consumida'] = round($cons, 2);
            $f['pct'] = $est !== null && $est > 0 ? round(($cons / $est) * 100, 2) : null;
        }

        return [
            'por_funcao' => $porFuncao,
            'consumida' => round($totCons, 2),
            'estimada' => $temEstimativa ? round($totEst, 2) : null,
            'pct' => $temEstimativa && $totEst > 0 ? round(($totCons / $totEst) * 100, 2) : null,
        ];
    }

    /**
     * Série temporal do consumo do projeto (aba A03): horas por dia e acumulado.
     *
     * @return array{labels: array<int, string>, diaria: array<int, float>, acumulada: array<int, float>}
     */
    public function evolucaoConsumo(ClockifyProjeto $projeto): array
    {
        $porDia = DB::table('clockify_time_trackers')
            ->where('clockify_projeto_id', $projeto->id)
            ->whereNotNull('inicio')
            ->groupByRaw('DATE(inicio)')
            ->selectRaw('DATE(inicio) as dia, SUM(GREATEST(TIMESTAMPDIFF(SECOND, inicio, COALESCE(fim, NOW())), 0)) as segundos')
            ->orderBy('dia')
            ->pluck('segundos', 'dia');

        $labels = [];
        $diaria = [];
        $acumulada = [];
        $total = 0.0;

        foreach ($porDia as $dia => $segundos) {
            $horas = round(((float) $segundos) / 3600, 2);
            $total = round($total + $horas, 2);
            $labels[] = Carbon::parse($dia)->format('d/m');
            $diaria[] = $horas;
            $acumulada[] = $total;
        }

        return ['labels' => $labels, 'diaria' => $diaria, 'acumulada' => $acumulada];
    }

    /**
     * Matriz colaborador × projeto em segundos (aba B00).
     *
     * @param  array<int>|null  $projetoIds  null = todos os projetos da empresa
     * @return Collection<int, object{user_id:int, nome:string, email:string|null, funcao_nome:string|null, clockify_projeto_id:int, segundos:float}>
     */
    public function matrizMembroProjeto(int $empresaId, ?array $projetoIds = null, ?string $inicio = null, ?string $fim = null): Collection
    {
        return DB::table('clockify_time_trackers as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('funcoes as f', 'f.id', '=', 'u.funcao_id')
            ->where('t.empresa_id', $empresaId)
            ->when($projetoIds !== null, fn ($q) => $q->whereIn('t.clockify_projeto_id', $projetoIds))
            ->when($inicio, fn ($q) => $q->whereDate('t.inicio', '>=', $inicio))
            ->when($fim, fn ($q) => $q->whereDate('t.inicio', '<=', $fim))
            ->whereNotNull('t.inicio')
            ->groupBy('t.user_id', 'u.nome', 'u.email', 'f.nome', 't.clockify_projeto_id')
            ->selectRaw('t.user_id, u.nome, u.email, f.nome as funcao_nome, t.clockify_projeto_id, SUM(GREATEST(TIMESTAMPDIFF(SECOND, t.inicio, COALESCE(t.fim, NOW())), 0)) as segundos')
            ->orderBy('u.nome')
            ->get();
    }

    /**
     * Previsão de estouro da estimativa: média diária de consumo desde o primeiro
     * lançamento do projeto e dias restantes até consumir 100% da estimativa.
     *
     * @return array{estourado: bool, media_diaria: float|null, dias: int|null}|null
     */
    public function previsaoEstouro(ClockifyProjeto $projeto, float $consumida, ?float $estimada): ?array
    {
        if ($estimada === null || $estimada <= 0) {
            return null;
        }

        if ($consumida >= $estimada) {
            return ['estourado' => true, 'media_diaria' => null, 'dias' => 0];
        }

        $primeiroInicio = ClockifyTimeTracker::where('clockify_projeto_id', $projeto->id)
            ->whereNotNull('inicio')
            ->min('inicio');

        if (! $primeiroInicio) {
            return null;
        }

        $diasDesdeInicio = max(1, Carbon::parse($primeiroInicio)->startOfDay()->diffInDays(now()->startOfDay()) + 1);
        $mediaDiaria = $consumida / $diasDesdeInicio;

        if ($mediaDiaria <= 0) {
            return null;
        }

        $dias = (int) ceil(($estimada - $consumida) / $mediaDiaria);

        return ['estourado' => false, 'media_diaria' => round($mediaDiaria, 2), 'dias' => $dias];
    }

    /**
     * Lista de funções que têm consumo ou estimativa em qualquer projeto (colunas da A01).
     *
     * @return array<int, string> funcao_id => nome
     */
    public function funcoesEmUso(int $empresaId, ?Collection $consumo = null, ?Collection $estimativas = null): array
    {
        $consumo = $consumo ?? $this->consumoPorFuncao($empresaId);
        $estimativas = $estimativas ?? $this->estimativasPorFuncao($empresaId);

        $nomes = [];
        foreach ($consumo as $c) {
            $nomes[(int) ($c->funcao_id ?? self::SEM_FUNCAO)] = $c->funcao_nome ?? 'Sem função';
        }
        foreach ($estimativas as $e) {
            $nomes[(int) $e->funcao_id] = $e->funcao_nome ?? 'Sem função';
        }

        asort($nomes);

        return $nomes;
    }

    public function empresaId(): int
    {
        return (int) (app(EmpresaContext::class)->empresaId() ?? auth()->user()?->empresa_id ?? 0);
    }

    /**
     * Funções ativas da empresa para selects.
     */
    public function funcoesParaSelect(): array
    {
        return Funcao::where('empresa_id', $this->empresaId())
            ->where('ativo', true)->orderBy('nome')->pluck('nome', 'id')->all();
    }
}
