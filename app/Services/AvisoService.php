<?php

namespace App\Services;

use App\Models\Aluno;
use App\Models\Aviso;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AvisoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Aviso $aviso)
    {
        $this->model = $aviso;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Aviso) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Aviso');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Avisos visiveis no portal para os alunos informados:
     * ativos, ja publicados, da escola do aluno (ou da turma
     * em que ele esta matriculado) e gerais ou da turma dele.
     */
    public function avisosParaAlunos(Collection $alunos): Collection
    {
        $alunoIds = $alunos->pluck('id');

        $turmaIds = \App\Models\Matricula::query()
            ->whereIn('aluno_id', $alunoIds)
            ->where('status', 'ativa')
            ->pluck('turma_id')
            ->unique();

        $escolaIds = $alunos->pluck('escola_id')->filter()->unique();

        if ($escolaIds->isEmpty() && $turmaIds->isEmpty()) {
            return collect();
        }

        if ($escolaIds->isNotEmpty()) {
            $turmaEscolaIds = \App\Models\Turma::query()
                ->whereIn('id', $turmaIds)
                ->pluck('escola_id');
            $escolaIds = $escolaIds->merge($turmaEscolaIds)->unique();
        }

        return Aviso::query()
            ->with(['escola', 'turma'])
            ->where('ativo', 'S')
            ->where('destino', 'familias')
            ->whereDate('data_publicacao', '<=', now()->toDateString())
            ->whereIn('escola_id', $escolaIds)
            ->where(fn ($q) => $q
                ->where(fn ($g) => $g->whereNull('turma_id')->whereDoesntHave('turmas'))
                ->orWhereIn('turma_id', $turmaIds)
                ->orWhereHas('turmas', fn ($t) => $t->whereIn('turmas.id', $turmaIds)))
            ->orderByDesc('data_publicacao')
            ->orderByDesc('id')
            ->get();
    }
}
