<?php

namespace App\Services;

use App\Models\Diario;
use App\Models\Frequencia;
use App\Models\Matricula;
use App\Support\EmpresaContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FrequenciaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Frequencia $frequencia)
    {
        $this->model = $frequencia;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Frequencia) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Frequencia');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Grava a chamada em lote (upsert por matricula+diario).
     * Rejeita matriculas que nao pertencem a turma do diario.
     */
    public function salvarChamada(Diario $diario, array $itens): void
    {
        $matriculaIds = collect($itens)->pluck('matricula_id')->map('intval')->unique()->values();

        $validas = DB::table('matriculas')
            ->whereIn('id', $matriculaIds)
            ->where('turma_id', $diario->turma_id)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->map('intval');

        $invalidas = $matriculaIds->diff($validas);
        if ($invalidas->isNotEmpty()) {
            throw ValidationException::withMessages([
                'frequencias' => 'Uma ou mais matrículas não pertencem a esta turma.',
            ]);
        }

        DB::transaction(function () use ($diario, $itens) {
            foreach ($itens as $item) {
                $valores = [
                    'empresa_id' => $diario->empresa_id,
                    'status' => $item['status'],
                ];
                // So atualiza a justificativa quando o campo veio no payload:
                // chamada do professor nao renderiza o campo e nao pode apagar
                // texto existente.
                if (array_key_exists('justificativa', $item)) {
                    $valores['justificativa'] = $item['justificativa'];
                }

                $this->model->updateOrCreate(
                    [
                        'diario_id' => $diario->id,
                        'matricula_id' => $item['matricula_id'],
                    ],
                    $valores
                );
            }
        });
    }

    /** Frequencias ja registradas do diario, indexadas por matricula_id. */
    public function porMatricula(Diario $diario)
    {
        return $this->model->newQuery()
            ->where('diario_id', $diario->id)
            ->get()
            ->keyBy('matricula_id');
    }

    /**
     * Relatorio consolidado de faltas: uma linha por matricula ativa
     * com contagens de presenca/falta/justificativa, ordenado pelos
     * piores índices de frequencia. Filtros: escola_id, ano_letivo_id,
     * turma_id e apenas_risco (frequencia < 75%).
     */
    public function consolidadoFaltas(array $filtros = [], int $perPage = 25)
    {
        $query = $this->queryConsolidado($filtros);

        return $query->paginate($perPage)->withQueryString();
    }

    /** Totais do relatorio (independentes da paginacao). */
    public function resumoFaltas(array $filtros = []): array
    {
        $base = $this->queryConsolidado($filtros)
            ->reorder()
            ->select(DB::raw('
                COUNT(*) as alunos,
                SUM(CASE WHEN COALESCE(f.total, 0) > 0 THEN 1 ELSE 0 END) as com_frequencia,
                AVG(CASE WHEN COALESCE(f.total, 0) > 0
                    THEN (COALESCE(f.total, 0) - COALESCE(f.falta, 0)) * 100.0 / f.total END) as media_pct,
                SUM(CASE WHEN COALESCE(f.total, 0) > 0
                    AND (COALESCE(f.total, 0) - COALESCE(f.falta, 0)) * 100.0 / f.total < 75
                    THEN 1 ELSE 0 END) as em_risco
            '))
            ->first();

        return [
            'alunos' => (int) ($base->alunos ?? 0),
            'com_frequencia' => (int) ($base->com_frequencia ?? 0),
            'media_pct' => $base->media_pct !== null ? round((float) $base->media_pct, 1) : null,
            'em_risco' => (int) ($base->em_risco ?? 0),
        ];
    }

    private function queryConsolidado(array $filtros)
    {
        $freq = DB::table('frequencias')
            ->join('diarios', 'frequencias.diario_id', '=', 'diarios.id')
            ->whereNull('diarios.deleted_at')
            ->whereNull('frequencias.deleted_at')
            ->groupBy('frequencias.matricula_id')
            ->selectRaw("
                frequencias.matricula_id,
                SUM(CASE WHEN frequencias.status = 'presente' THEN 1 ELSE 0 END) as presente,
                SUM(CASE WHEN frequencias.status IN ('justificada', 'abonada') THEN 1 ELSE 0 END) as justificada,
                SUM(CASE WHEN frequencias.status = 'falta' THEN 1 ELSE 0 END) as falta,
                SUM(CASE WHEN frequencias.status <> 'abonada' THEN 1 ELSE 0 END) as total
            ");

        $query = Matricula::query()
            ->join('alunos', 'matriculas.aluno_id', '=', 'alunos.id')
            ->join('pessoas', 'alunos.pessoa_id', '=', 'pessoas.id')
            ->join('turmas', 'matriculas.turma_id', '=', 'turmas.id')
            ->join('escolas', 'turmas.escola_id', '=', 'escolas.id')
            ->leftJoinSub($freq, 'f', 'f.matricula_id', '=', 'matriculas.id')
            ->where('matriculas.status', 'ativa')
            ->whereNull('matriculas.deleted_at')
            ->select([
                'matriculas.id as matricula_id',
                'matriculas.numero_chamada',
                'pessoas.nome as aluno',
                'turmas.nome as turma',
                'escolas.nome as escola',
                DB::raw('COALESCE(f.presente, 0) as presente'),
                DB::raw('COALESCE(f.justificada, 0) as justificada'),
                DB::raw('COALESCE(f.falta, 0) as falta'),
                DB::raw('COALESCE(f.total, 0) as total'),
            ])
            ->orderByRaw('COALESCE(f.falta, 0) DESC')
            ->orderBy('turmas.nome')
            ->orderBy('matriculas.numero_chamada');

        $empresaId = $this->empresaIdRelatorio();
        if ($empresaId !== null) {
            $query->where('matriculas.empresa_id', $empresaId);
        }

        if (! empty($filtros['escola_id'])) {
            $query->where('turmas.escola_id', (int) $filtros['escola_id']);
        }
        if (! empty($filtros['ano_letivo_id'])) {
            $query->where('matriculas.ano_letivo_id', (int) $filtros['ano_letivo_id']);
        }
        if (! empty($filtros['turma_id'])) {
            $query->where('matriculas.turma_id', (int) $filtros['turma_id']);
        }
        if (! empty($filtros['apenas_risco'])) {
            $query->whereRaw('
                COALESCE(f.total, 0) > 0
                AND (COALESCE(f.total, 0) - COALESCE(f.falta, 0)) * 100.0 / f.total < 75
            ');
        }

        return $query;
    }

    /** Mesmo criterio de tenant do AbstractBaseService, com coluna qualificada. */
    private function empresaIdRelatorio(): ?int
    {
        if (Auth::user()?->isSuperAdmin()) {
            return app(EmpresaContext::class)->empresaId();
        }

        $empresaId = Auth::user()?->empresa_id;

        return $empresaId !== null ? (int) $empresaId : null;
    }
}
