<?php

namespace App\Services;

use App\Models\Aluno;
use App\Models\Avaliacao;
use App\Models\Matricula;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Boletim consolidado: medias por disciplina e frequencia
 * calculadas sobre avaliacoes/notas e diarios/frequencias ja
 * registrados. Professor so acessa turmas onde leciona.
 */
class BoletimService
{
    /** Turmas que o usuario pode consultar no boletim. */
    public function turmasVisiveis(User $user): Collection
    {
        $query = Turma::query()->with(['escola', 'anoLetivo', 'etapa'])->orderBy('nome');

        if ($user->isProfessor()) {
            $query->whereHas('atribuicoes', fn ($q) => $q->where('user_id', $user->id)->where('ativo', 'S'));
        }

        return $query->get();
    }

    public function podeVerTurma(Turma $turma, User $user): bool
    {
        if (! $user->isProfessor()) {
            return true;
        }

        return $turma->atribuicoes()
            ->where('user_id', $user->id)
            ->where('ativo', 'S')
            ->exists();
    }

    /**
     * Dados da grade da turma:
     *  - matriculas ativas (ordenadas por chamada)
     *  - disciplinas ofertadas
     *  - grade[matricula_id][disciplina_id] = media|null
     *  - frequencias[matricula_id] = resumo de presenca
     */
    public function dadosTurma(Turma $turma): array
    {
        $matriculas = Matricula::query()
            ->with('aluno.pessoa')
            ->where('turma_id', $turma->id)
            ->where('status', 'ativa')
            ->orderBy('numero_chamada')
            ->orderBy('id')
            ->get();

        $disciplinas = $turma->disciplinas()->orderBy('nome')->get();

        // medias: AVG(nota) por matricula+disciplina nas avaliacoes da turma
        $medias = DB::table('notas')
            ->join('avaliacoes', 'notas.avaliacao_id', '=', 'avaliacoes.id')
            ->where('avaliacoes.turma_id', $turma->id)
            ->whereNull('avaliacoes.deleted_at')
            ->whereNull('notas.deleted_at')
            ->whereNotNull('notas.nota')
            ->whereIn('notas.matricula_id', $matriculas->pluck('id'))
            ->groupBy('notas.matricula_id', 'avaliacoes.disciplina_id')
            ->selectRaw('notas.matricula_id, avaliacoes.disciplina_id, AVG(notas.nota) as media')
            ->get()
            ->mapWithKeys(fn ($r) => ["{$r->matricula_id}:{$r->disciplina_id}" => round((float) $r->media, 1)]);

        // frequencia: chamadas registradas nos diarios da turma
        $frequencias = DB::table('frequencias')
            ->join('diarios', 'frequencias.diario_id', '=', 'diarios.id')
            ->where('diarios.turma_id', $turma->id)
            ->whereNull('diarios.deleted_at')
            ->whereNull('frequencias.deleted_at')
            ->whereIn('frequencias.matricula_id', $matriculas->pluck('id'))
            ->groupBy('frequencias.matricula_id', 'frequencias.status')
            ->selectRaw('frequencias.matricula_id, frequencias.status, COUNT(*) as total')
            ->get()
            ->groupBy('matricula_id')
            ->map(function ($rows) {
                $por = $rows->pluck('total', 'status');
                $presente = (int) ($por['presente'] ?? 0);
                $justificada = (int) ($por['justificada'] ?? 0);
                $falta = (int) ($por['falta'] ?? 0);
                $total = $presente + $justificada + $falta;

                return [
                    'presente' => $presente,
                    'justificada' => $justificada,
                    'falta' => $falta,
                    'total' => $total,
                    'pct' => $total > 0 ? round((($presente + $justificada) / $total) * 100, 1) : null,
                ];
            });

        $totalAulas = DB::table('diarios')
            ->where('turma_id', $turma->id)
            ->whereNull('deleted_at')
            ->count();

        return [
            'matriculas' => $matriculas,
            'disciplinas' => $disciplinas,
            'medias' => $medias,
            'frequencias' => $frequencias,
            'total_aulas' => $totalAulas,
        ];
    }

    /**
     * Boletim individual: matricula atual (ou do ano letivo informado)
     * do aluno, com avaliacoes por disciplina, notas e frequencia.
     */
    public function dadosAluno(Aluno $aluno, ?int $anoLetivoId = null): ?array
    {
        $query = Matricula::query()
            ->with(['turma.disciplinas', 'turma.anoLetivo', 'turma.etapa'])
            ->where('aluno_id', $aluno->id)
            ->where('status', 'ativa');

        if ($anoLetivoId) {
            $query->where('ano_letivo_id', $anoLetivoId);
        }

        $matricula = $query->orderByDesc('id')->first();
        if (! $matricula) {
            return null;
        }

        $turma = $matricula->turma;

        // avaliacoes da turma agrupadas por disciplina, com a nota do aluno
        $avaliacoes = Avaliacao::query()
            ->with(['notas' => fn ($q) => $q->where('matricula_id', $matricula->id)])
            ->where('turma_id', $turma->id)
            ->orderBy('data')
            ->orderBy('id')
            ->get()
            ->groupBy('disciplina_id');

        // media por disciplina
        $medias = DB::table('notas')
            ->join('avaliacoes', 'notas.avaliacao_id', '=', 'avaliacoes.id')
            ->where('avaliacoes.turma_id', $turma->id)
            ->where('notas.matricula_id', $matricula->id)
            ->whereNull('avaliacoes.deleted_at')
            ->whereNull('notas.deleted_at')
            ->whereNotNull('notas.nota')
            ->groupBy('avaliacoes.disciplina_id')
            ->selectRaw('avaliacoes.disciplina_id, AVG(notas.nota) as media')
            ->pluck('media', 'disciplina_id')
            ->map(fn ($m) => round((float) $m, 1));

        // frequencia do aluno na turma
        $freq = DB::table('frequencias')
            ->join('diarios', 'frequencias.diario_id', '=', 'diarios.id')
            ->where('diarios.turma_id', $turma->id)
            ->where('frequencias.matricula_id', $matricula->id)
            ->whereNull('diarios.deleted_at')
            ->whereNull('frequencias.deleted_at')
            ->selectRaw('frequencias.status, COUNT(*) as total')
            ->groupBy('frequencias.status')
            ->pluck('total', 'status');

        $presente = (int) ($freq['presente'] ?? 0);
        $justificada = (int) ($freq['justificada'] ?? 0);
        $falta = (int) ($freq['falta'] ?? 0);
        $freqTotal = $presente + $justificada + $falta;

        return [
            'matricula' => $matricula,
            'turma' => $turma,
            'disciplinas' => $turma->disciplinas,
            'avaliacoes' => $avaliacoes,
            'medias' => $medias,
            'frequencia' => [
                'presente' => $presente,
                'justificada' => $justificada,
                'falta' => $falta,
                'total' => $freqTotal,
                'pct' => $freqTotal > 0 ? round((($presente + $justificada) / $freqTotal) * 100, 1) : null,
            ],
            'total_aulas' => DB::table('diarios')->where('turma_id', $turma->id)->whereNull('deleted_at')->count(),
        ];
    }

    /** Alunos com matricula ativa (para o seletor do boletim individual). */
    public function alunosComMatricula(User $user): Collection
    {
        $query = Aluno::query()
            ->with(['pessoa', 'matriculas' => fn ($q) => $q->where('status', 'ativa')->with('turma')])
            ->whereHas('matriculas', fn ($q) => $q->where('status', 'ativa'))
            ->orderBy('id');

        if ($user->isProfessor()) {
            $query->whereHas('matriculas.turma.atribuicoes', fn ($q) => $q->where('user_id', $user->id)->where('ativo', 'S'));
        }

        return $query->get();
    }
}
