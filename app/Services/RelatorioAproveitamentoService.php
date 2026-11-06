<?php

namespace App\Services;

use App\Support\EmpresaContext;
use Illuminate\Support\Facades\DB;

/**
 * Relatorio de aproveitamento academico: consolidado por
 * turma com quantidade de alunos, media geral (media das
 * medias por aluno), percentual de aprovados (media >= 7)
 * e cobertura de notas lancadas.
 */
class RelatorioAproveitamentoService
{
    public const NOTA_APROVACAO = 7.0;

    /**
     * Linhas por turma (ordenadas por escola/turma) + resumo.
     * Filtros: escola_id, ano_letivo_id, etapa_id, turma_id.
     */
    public function porTurma(array $filtros): array
    {
        $empresaId = app(EmpresaContext::class)->empresaId();

        // Media por (turma, matricula): base do calculo
        $mediasAluno = DB::table('notas')
            ->join('avaliacoes', 'notas.avaliacao_id', '=', 'avaliacoes.id')
            ->join('matriculas', 'notas.matricula_id', '=', 'matriculas.id')
            ->where('matriculas.status', 'ativa')
            ->whereNull('avaliacoes.deleted_at')
            ->whereNull('notas.deleted_at')
            ->whereNull('matriculas.deleted_at')
            ->whereNotNull('notas.nota')
            ->when($empresaId !== null, fn ($q) => $q->where('matriculas.empresa_id', $empresaId))
            ->when(! empty($filtros['escola_id']), fn ($q) => $q->whereExists(
                fn ($s) => $s->select(DB::raw(1))->from('turmas')
                    ->whereColumn('turmas.id', 'matriculas.turma_id')
                    ->where('turmas.escola_id', $filtros['escola_id'])
            ))
            ->when(! empty($filtros['ano_letivo_id']), fn ($q) => $q->where('matriculas.ano_letivo_id', $filtros['ano_letivo_id']))
            ->when(! empty($filtros['etapa_id']), fn ($q) => $q->whereExists(
                fn ($s) => $s->select(DB::raw(1))->from('turmas')
                    ->whereColumn('turmas.id', 'matriculas.turma_id')
                    ->where('turmas.etapa_id', $filtros['etapa_id'])
            ))
            ->when(! empty($filtros['turma_id']), fn ($q) => $q->where('matriculas.turma_id', $filtros['turma_id']))
            ->groupBy('matriculas.turma_id', 'notas.matricula_id')
            ->selectRaw('matriculas.turma_id, notas.matricula_id, AVG(notas.nota) as media')
            ->get();

        // Total de alunos ativos por turma (mesmos filtros)
        $alunosPorTurma = DB::table('matriculas')
            ->join('turmas', 'matriculas.turma_id', '=', 'turmas.id')
            ->leftJoin('escolas', 'turmas.escola_id', '=', 'escolas.id')
            ->leftJoin('etapas', 'turmas.etapa_id', '=', 'etapas.id')
            ->leftJoin('anos_letivos', 'turmas.ano_letivo_id', '=', 'anos_letivos.id')
            ->where('matriculas.status', 'ativa')
            ->whereNull('matriculas.deleted_at')
            ->when($empresaId !== null, fn ($q) => $q->where('matriculas.empresa_id', $empresaId))
            ->when(! empty($filtros['escola_id']), fn ($q) => $q->where('turmas.escola_id', $filtros['escola_id']))
            ->when(! empty($filtros['ano_letivo_id']), fn ($q) => $q->where('matriculas.ano_letivo_id', $filtros['ano_letivo_id']))
            ->when(! empty($filtros['etapa_id']), fn ($q) => $q->where('turmas.etapa_id', $filtros['etapa_id']))
            ->when(! empty($filtros['turma_id']), fn ($q) => $q->where('matriculas.turma_id', $filtros['turma_id']))
            ->groupBy('matriculas.turma_id', 'turmas.nome', 'turmas.ano_letivo_id', 'turmas.etapa_id', 'turmas.escola_id', 'escolas.nome', 'etapas.nome', 'anos_letivos.ano')
            ->selectRaw('matriculas.turma_id, turmas.nome as turma_nome, escolas.nome as escola_nome, etapas.nome as etapa_nome, anos_letivos.ano as ano_letivo, COUNT(matriculas.id) as alunos')
            ->orderBy('escolas.nome')
            ->orderBy('turmas.nome')
            ->get()
            ->keyBy('turma_id');

        // Notas lancadas por turma (cobertura) — so as turmas do
        // conjunto filtrado sao consultadas depois
        $turmaIds = $alunosPorTurma->keys();
        $cobertura = DB::table('notas')
            ->join('avaliacoes', 'notas.avaliacao_id', '=', 'avaliacoes.id')
            ->join('matriculas', 'notas.matricula_id', '=', 'matriculas.id')
            ->where('matriculas.status', 'ativa')
            ->whereNull('avaliacoes.deleted_at')
            ->whereNull('notas.deleted_at')
            ->whereNotNull('notas.nota')
            ->whereIn('matriculas.turma_id', $turmaIds)
            ->groupBy('matriculas.turma_id')
            ->selectRaw('matriculas.turma_id, COUNT(*) as notas, COUNT(DISTINCT notas.matricula_id) as alunos_com_nota')
            ->get()
            ->keyBy('turma_id');

        // Agrega medias por turma em PHP
        $porTurma = $mediasAluno->groupBy('turma_id')->map(function ($linhas) {
            $medias = $linhas->pluck('media')->map(fn ($m) => (float) $m);

            return [
                'alunos_com_media' => $linhas->pluck('matricula_id')->unique()->count(),
                'media_geral' => $medias->isNotEmpty() ? round($medias->avg(), 1) : null,
                'aprovados' => $linhas->filter(fn ($l) => (float) $l->media >= self::NOTA_APROVACAO)
                    ->pluck('matricula_id')->unique()->count(),
            ];
        });

        $linhas = $alunosPorTurma->map(function ($turma) use ($porTurma, $cobertura) {
            $agg = $porTurma->get($turma->turma_id);
            $cob = $cobertura->get($turma->turma_id);
            $alunos = (int) $turma->alunos;
            $comMedia = $agg['alunos_com_media'] ?? 0;
            $aprovados = $agg['aprovados'] ?? 0;

            return [
                'turma_id' => $turma->turma_id,
                'turma' => $turma->turma_nome,
                'escola' => $turma->escola_nome,
                'etapa' => $turma->etapa_nome,
                'ano_letivo' => $turma->ano_letivo,
                'alunos' => $alunos,
                'alunos_com_media' => $comMedia,
                'media_geral' => $agg['media_geral'] ?? null,
                'aprovados' => $aprovados,
                'pct_aprovacao' => $comMedia > 0 ? round(($aprovados / $comMedia) * 100, 1) : null,
                'notas_lancadas' => (int) ($cob->notas ?? 0),
                'alunos_com_nota' => (int) ($cob->alunos_com_nota ?? 0),
            ];
        })->values();

        // Resumo geral
        $totalAlunos = $linhas->sum('alunos');
        $totalComMedia = $linhas->sum('alunos_com_media');
        $totalAprovados = $linhas->sum('aprovados');
        $somaMedias = $linhas->sum(fn ($l) => ($l['media_geral'] ?? 0) * $l['alunos_com_media']);

        return [
            'linhas' => $linhas,
            'resumo' => [
                'turmas' => $linhas->count(),
                'alunos' => $totalAlunos,
                'alunos_com_media' => $totalComMedia,
                'media_geral' => $totalComMedia > 0 ? round($somaMedias / $totalComMedia, 1) : null,
                'aprovados' => $totalAprovados,
                'pct_aprovacao' => $totalComMedia > 0 ? round(($totalAprovados / $totalComMedia) * 100, 1) : null,
            ],
        ];
    }
}
