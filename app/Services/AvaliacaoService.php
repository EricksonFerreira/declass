<?php

namespace App\Services;

use App\Models\Avaliacao;
use App\Models\Nota;
use App\Models\User;
use App\Support\SchemaCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AvaliacaoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Avaliacao $avaliacao)
    {
        $this->model = $avaliacao;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Avaliacao) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Avaliacao');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function paginateComFiltros(array $filters, int $perPage): LengthAwarePaginator
    {
        $filters = $this->sanitizeEscolaContextFilter($filters);

        $query = $this->applyEmpresaScope($this->model->newQuery())
            ->with(['turma.escola', 'turma.anoLetivo', 'disciplina', 'professor']);

        $user = auth()->user();
        if ($user instanceof User && $user->isProfessor()) {
            $query->whereHas('turma.atribuicoes', fn ($q) => $q
                ->where('user_id', $user->id)
                ->where('ativo', 'S'));
        }

        foreach ($filters as $field => $filter) {
            if (! is_array($filter) || ! array_key_exists('valor', $filter)) {
                continue;
            }

            $valor = $filter['valor'];
            if ($valor === null || $valor === '') {
                continue;
            }

            $typeWhere = $filter['typeWhere'] ?? 'where';

            if (! SchemaCache::hasColumn($this->model->getTable(), (string) $field)) {
                continue;
            }

            if ($typeWhere === 'like') {
                $query->where($field, 'like', '%'.$valor.'%');
            } elseif (is_array($valor)) {
                $query->whereIn($field, $valor);
            } else {
                $query->where($field, $valor);
            }
        }

        return $query->orderByDesc('data')->orderByDesc('id')->paginate($perPage);
    }

    /** Disciplina precisa estar ofertada na turma. */
    public function create(array $data): Avaliacao
    {
        $user = auth()->user();

        $this->garantirProfessorTurma((int) $data['turma_id']);

        $ofertada = DB::table('turma_disciplina')
            ->where('turma_id', $data['turma_id'])
            ->where('disciplina_id', $data['disciplina_id'])
            ->exists();

        if (! $ofertada) {
            throw ValidationException::withMessages([
                'disciplina_id' => 'Esta disciplina não está ofertada nesta turma.',
            ]);
        }

        $data['user_id'] = $user?->id;
        $this->sincronizarPeriodo($data);

        return parent::create($data);
    }

    /** Professor gerencia avaliacoes das turmas onde tem atribuicao ativa. */
    public function podeGerenciar(Avaliacao $avaliacao, ?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            return false;
        }

        if (! $user->isProfessor()) {
            return true;
        }

        return $avaliacao->turma?->atribuicoes()
            ->where('user_id', $user->id)
            ->where('ativo', 'S')
            ->exists() ?? false;
    }

    public function update($id, array $data)
    {
        $avaliacao = $this->getById($id);
        abort_if(! $avaliacao || ! $this->podeGerenciar($avaliacao), 403, 'Sem acesso a esta avaliação.');

        if (! empty($data['turma_id'])) {
            $this->garantirProfessorTurma((int) $data['turma_id']);
        }

        $this->sincronizarPeriodo($data);

        return parent::update($id, $data);
    }

    /**
     * O rotulo texto 'periodo' e mantido em sincronia com o
     * periodo letivo vinculado (fonte da verdade e o FK).
     */
    private function sincronizarPeriodo(array &$data): void
    {
        if (! empty($data['periodo_letivo_id'])) {
            $nome = \App\Models\PeriodoLetivo::query()
                ->whereKey($data['periodo_letivo_id'])
                ->value('nome');
            if ($nome) {
                $data['periodo'] = $nome;
            }
        }
    }

    public function delete($id)
    {
        $avaliacao = $this->getById($id);
        abort_if(! $avaliacao || ! $this->podeGerenciar($avaliacao), 403, 'Sem acesso a esta avaliação.');

        return parent::delete($id);
    }

    /** Matriculas ativas da turma (base da grade de notas). */
    public function matriculasDaTurma(Avaliacao $avaliacao)
    {
        return \App\Models\Matricula::query()
            ->with('aluno.pessoa')
            ->where('turma_id', $avaliacao->turma_id)
            ->where('status', 'ativa')
            ->orderBy('numero_chamada')
            ->orderBy('id')
            ->get();
    }

    /**
     * Media simples do aluno na disciplina: media das notas
     * lancadas nas avaliacoes da mesma turma+disciplina.
     * Retorna [matricula_id => media|null].
     */
    public function mediasDaTurmaDisciplina(Avaliacao $avaliacao): array
    {
        return Nota::query()
            ->join('avaliacoes', 'notas.avaliacao_id', '=', 'avaliacoes.id')
            ->where('avaliacoes.turma_id', $avaliacao->turma_id)
            ->where('avaliacoes.disciplina_id', $avaliacao->disciplina_id)
            ->whereNull('avaliacoes.deleted_at')
            ->whereNull('notas.deleted_at')
            ->whereNotNull('notas.nota')
            ->groupBy('notas.matricula_id')
            ->selectRaw('notas.matricula_id, AVG(notas.nota) as media')
            ->pluck('media', 'matricula_id')
            ->map(fn ($m) => round((float) $m, 2))
            ->all();
    }
}
