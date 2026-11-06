<?php

namespace App\Services;

use App\Models\Aluno;
use App\Models\Avaliacao;
use App\Models\Disciplina;
use App\Models\Matricula;
use Illuminate\Support\Collection;

/**
 * Historico escolar formal (boletim acumulado): grade de
 * disciplinas x periodos letivos de cada matricula, com media
 * por periodo, media final e frequencia. Base tanto para a
 * tela anual quanto para o PDF do historico completo.
 */
class HistoricoEscolarService
{
    /**
     * Grade escolar de uma matricula (qualquer status):
     * colunas = periodos letivos do ano letivo (+ "Sem período"
     * quando houver avaliacoes sem vinculo).
     *
     * @return array<string, mixed>
     */
    public function dadosMatricula(Matricula $matricula): array
    {
        $matricula->loadMissing([
            'turma.anoLetivo.periodos',
            'turma.etapa',
            'turma.escola',
            'turma.disciplinas',
        ]);

        $turma = $matricula->turma;
        $anoLetivo = $turma?->anoLetivo;
        $periodos = $anoLetivo?->periodos
            ?->sortBy('ordem')
            ->values() ?? collect();

        // avaliacoes da turma com a nota do aluno, agrupadas por disciplina
        $avaliacoes = Avaliacao::query()
            ->with(['notas' => fn ($q) => $q->where('matricula_id', $matricula->id)])
            ->where('turma_id', $turma->id)
            ->orderBy('data')
            ->orderBy('id')
            ->get()
            ->groupBy('disciplina_id');

        // existem avaliacoes sem periodo letivo? vira coluna extra
        $temSemPeriodo = $avaliacoes->flatten(1)->contains(
            fn (Avaliacao $av) => $av->periodo_letivo_id === null
        );

        // media em % (nota * 100 / nota_maxima) por disciplina+periodo
        $mediasPeriodo = $this->mediasPorPeriodo($matricula, $turma->id);
        $mediasGeral = $this->mediasGerais($matricula, $turma->id);

        // linhas: disciplinas ofertadas + extras que tenham avaliacao
        $disciplinas = $turma->disciplinas->keyBy('id');
        foreach ($avaliacoes->keys() as $disciplinaId) {
            if (! $disciplinas->has($disciplinaId)) {
                $disc = Disciplina::find($disciplinaId);
                if ($disc) {
                    $disciplinas->put($disc->id, $disc);
                }
            }
        }

        $linhas = $disciplinas->sortBy('nome')->map(function (Disciplina $disc) use (
            $turma, $avaliacoes, $mediasPeriodo, $mediasGeral, $temSemPeriodo, $periodos
        ) {
            $pivot = $turma->disciplinas->firstWhere('id', $disc->id)?->pivot;
            $avals = $avaliacoes->get($disc->id, collect());

            $medias = [];
            foreach ($periodos as $periodo) {
                $medias[$periodo->id] = $mediasPeriodo["{$disc->id}:{$periodo->id}"] ?? null;
            }
            if ($temSemPeriodo) {
                $medias['__sem_periodo__'] = $mediasPeriodo["{$disc->id}:__sem_periodo__"] ?? null;
            }

            return [
                'disciplina' => $disc,
                'carga_horaria' => $pivot?->carga_horaria_semanal,
                'medias' => $medias,
                'media_geral' => $mediasGeral[$disc->id] ?? null,
                'avaliacoes' => $avals->count(),
            ];
        })->values();

        return [
            'matricula' => $matricula,
            'turma' => $turma,
            'escola' => $turma?->escola ?? $matricula->aluno?->escola,
            'ano_letivo' => $anoLetivo,
            'etapa' => $turma?->etapa,
            'periodos' => $periodos,
            'tem_sem_periodo' => $temSemPeriodo,
            'linhas' => $linhas,
            'frequencia' => $this->frequencia($matricula),
        ];
    }

    /**
     * Historico escolar completo do aluno: todas as matriculas
     * (qualquer situacao) com suas grades, do ano mais antigo
     * ao mais recente.
     *
     * @return array{aluno: Aluno, anos: Collection<int, array<string, mixed>>}
     */
    public function dadosAluno(Aluno $aluno): array
    {
        $aluno->loadMissing(['pessoa', 'escola']);

        $matriculas = $aluno->matriculas()
            ->with(['turma.anoLetivo.periodos', 'turma.etapa', 'turma.escola', 'turma.disciplinas'])
            ->get()
            ->sortBy(fn (Matricula $m) => [
                $m->turma?->anoLetivo?->ano ?? 0,
                $m->turma?->anoLetivo?->data_inicio?->timestamp ?? 0,
                $m->id,
            ])
            ->values();

        return [
            'aluno' => $aluno,
            'anos' => $matriculas->map(fn (Matricula $m) => $this->dadosMatricula($m))->values(),
        ];
    }

    /**
     * Media (%) por disciplina+periodo letivo do aluno na turma.
     * Chave "disciplinaId:periodoId"; periodo nulo vira
     * "disciplinaId:__sem_periodo__".
     *
     * @return array<string, float>
     */
    private function mediasPorPeriodo(Matricula $matricula, int $turmaId): array
    {
        return $this->mediasQuery($matricula, $turmaId)
            ->selectRaw(
                'avaliacoes.disciplina_id, '.
                "COALESCE(avaliacoes.periodo_letivo_id, '__sem_periodo__') as periodo_key, ".
                'AVG(notas.nota * 100.0 / avaliacoes.nota_maxima) as media'
            )
            ->groupBy('avaliacoes.disciplina_id', 'avaliacoes.periodo_letivo_id')
            ->get()
            ->mapWithKeys(fn ($r) => [
                "{$r->disciplina_id}:{$r->periodo_key}" => round((float) $r->media, 1),
            ])
            ->all();
    }

    /**
     * Media (%) final por disciplina (todas as avaliacoes).
     *
     * @return array<int, float>
     */
    private function mediasGerais(Matricula $matricula, int $turmaId): array
    {
        return $this->mediasQuery($matricula, $turmaId)
            ->selectRaw('avaliacoes.disciplina_id, AVG(notas.nota * 100.0 / avaliacoes.nota_maxima) as media')
            ->groupBy('avaliacoes.disciplina_id')
            ->pluck('media', 'disciplina_id')
            ->map(fn ($m) => round((float) $m, 1))
            ->all();
    }

    private function mediasQuery(Matricula $matricula, int $turmaId)
    {
        return \DB::table('notas')
            ->join('avaliacoes', 'notas.avaliacao_id', '=', 'avaliacoes.id')
            ->where('avaliacoes.turma_id', $turmaId)
            ->where('notas.matricula_id', $matricula->id)
            ->whereNull('avaliacoes.deleted_at')
            ->whereNull('notas.deleted_at')
            ->whereNotNull('notas.nota')
            ->where('avaliacoes.nota_maxima', '>', 0);
    }

    /**
     * Frequencia do aluno na turma da matricula.
     *
     * @return array{presente: int, justificada: int, falta: int, total: int, pct: float|null}
     */
    private function frequencia(Matricula $matricula): array
    {
        $freq = \DB::table('frequencias')
            ->join('diarios', 'frequencias.diario_id', '=', 'diarios.id')
            ->where('diarios.turma_id', $matricula->turma_id)
            ->where('frequencias.matricula_id', $matricula->id)
            ->whereNull('diarios.deleted_at')
            ->whereNull('frequencias.deleted_at')
            ->selectRaw('frequencias.status, COUNT(*) as total')
            ->groupBy('frequencias.status')
            ->pluck('total', 'status');

        $presente = (int) ($freq['presente'] ?? 0);
        $justificada = (int) ($freq['justificada'] ?? 0);
        $falta = (int) ($freq['falta'] ?? 0);
        $total = $presente + $justificada + $falta;

        return [
            'presente' => $presente,
            'justificada' => $justificada,
            'falta' => $falta,
            'total' => $total,
            'pct' => $total > 0 ? round((($presente + $justificada) / $total) * 100, 1) : null,
        ];
    }
}
