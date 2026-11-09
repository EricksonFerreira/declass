<?php

namespace App\Services;

use App\Models\Atribuicao;
use App\Models\Diario;
use App\Models\Disciplina;
use App\Models\Turma;
use App\Models\User;
use App\Support\SchemaCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DiarioService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Diario $diario)
    {
        $this->model = $diario;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Diario) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Diario');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /** Perfis que so enxergam/regem os proprios diarios. */
    public const PERFIS_PROFESSOR = ['professor', 'professor_substituto'];

    public static function ehProfessor(User $user): bool
    {
        return $user->isProfessor();
    }

    public function paginateComFiltros(array $filters, int $perPage): LengthAwarePaginator
    {
        $filters = $this->sanitizeEscolaContextFilter($filters);

        $query = $this->applyEmpresaScope($this->model->newQuery())
            ->with(['turma.escola', 'turma.anoLetivo', 'disciplina', 'professor'])
            ->withCount('frequencias');

        $user = auth()->user();
        if ($user && self::ehProfessor($user)) {
            // Mesmo criterio de podeGerenciar: professor comum ve so os
            // proprios diarios; regente (atribuicao sem disciplina) ve
            // todos os das turmas que rege.
            $query->whereHas('turma.atribuicoes', fn ($q) => $q
                ->where('user_id', $user->id)
                ->where('ativo', 'S')
                ->where(function ($w) {
                    $w->whereNull('disciplina_id')
                        ->orWhereColumn('diarios.user_id', 'atribuicoes.user_id');
                }));
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

    /**
     * Cria o diario validando que a disciplina e ofertada na turma
     * e que ainda nao existe aula para (turma, disciplina, data).
     */
    public function create(array $data): Diario
    {
        $user = auth()->user();

        $this->garantirProfessorTurma((int) $data['turma_id']);
        $this->garantirProfessorDisciplina((int) $data['turma_id'], (int) $data['disciplina_id']);

        $ofertada = DB::table('turma_disciplina')
            ->where('turma_id', $data['turma_id'])
            ->where('disciplina_id', $data['disciplina_id'])
            ->exists();

        if (! $ofertada) {
            throw ValidationException::withMessages([
                'disciplina_id' => 'Esta disciplina não está ofertada nesta turma.',
            ]);
        }

        $existe = $this->applyEmpresaScope($this->model->newQuery())
            ->where('turma_id', $data['turma_id'])
            ->where('disciplina_id', $data['disciplina_id'])
            ->whereDate('data', $data['data'])
            ->exists();

        if ($existe) {
            throw ValidationException::withMessages([
                'data' => 'Já existe um registro de aula para esta disciplina nesta data.',
            ]);
        }

        $data['user_id'] = $user?->id;

        return parent::create($data);
    }

    /**
     * Professor gerencia so os proprios diarios; regente (atribuicao
     * sem disciplina) gerencia todos os da turma.
     */
    public function podeGerenciar(Diario $diario, ?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            return false;
        }

        if (! self::ehProfessor($user)) {
            return true;
        }

        $atribuicoes = $diario->turma?->atribuicoes()
            ->where('user_id', $user->id)
            ->where('ativo', 'S')
            ->get(['disciplina_id']) ?? collect();

        if ($atribuicoes->isEmpty()) {
            return false;
        }

        return $atribuicoes->contains(fn ($a) => $a->disciplina_id === null)
            || $diario->user_id === $user->id;
    }

    public function update($id, array $data)
    {
        $diario = $this->getById($id);
        abort_if(! $diario || ! $this->podeGerenciar($diario), 403, 'Sem acesso a este diário.');

        if (! empty($data['turma_id'])) {
            $this->garantirProfessorTurma((int) $data['turma_id']);
        }

        return parent::update($id, $data);
    }

    public function delete($id)
    {
        $diario = $this->getById($id);
        abort_if(! $diario || ! $this->podeGerenciar($diario), 403, 'Sem acesso a este diário.');

        return parent::delete($id);
    }

    /**
     * Professor com atribuicoes por disciplina so registra aula nas
     * que leciona; atribuicao sem disciplina (regente) libera todas
     * as ofertadas na turma. No-op para demais perfis.
     */
    protected function garantirProfessorDisciplina(int $turmaId, int $disciplinaId): void
    {
        $user = auth()->user();
        if (! $user?->isProfessor()) {
            return;
        }

        $atribuicoes = Atribuicao::query()
            ->where('turma_id', $turmaId)
            ->where('user_id', $user->id)
            ->where('ativo', 'S')
            ->get(['disciplina_id']);

        $regente = $atribuicoes->contains(fn (Atribuicao $a) => $a->disciplina_id === null);
        if ($regente || $atribuicoes->pluck('disciplina_id')->contains($disciplinaId)) {
            return;
        }

        throw ValidationException::withMessages([
            'disciplina_id' => 'Você não está atribuído a esta disciplina nesta turma.',
        ]);
    }

    /** Matriculas ativas da turma do diario (base da chamada). */
    public function matriculasDaTurma(Diario $diario)
    {
        return $this->matriculasAtivasDaTurma($diario->turma_id);
    }

    /** Matriculas ativas de uma turma (base da chamada unificada). */
    public function matriculasAtivasDaTurma(int $turmaId)
    {
        return \App\Models\Matricula::query()
            ->with('aluno.pessoa')
            ->where('turma_id', $turmaId)
            ->where('status', 'ativa')
            ->orderBy('numero_chamada')
            ->orderBy('id')
            ->get();
    }

    /**
     * Contexto da chamada unificada (turma + disciplina + data):
     * valida acesso do usuario e devolve o diario ja existente para
     * a combinacao, quando houver (para pre-preencher a tela).
     */
    public function contextoChamada(int $turmaId, int $disciplinaId, string $data): array
    {
        $empresaId = $this->currentEmpresaId();
        $turma = Turma::query()
            ->when($empresaId !== null, fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($turmaId);
        $disciplina = Disciplina::query()
            ->when($empresaId !== null, fn ($q) => $q->where('empresa_id', $empresaId))
            ->findOrFail($disciplinaId);

        $this->garantirProfessorTurma($turmaId);
        $this->garantirProfessorDisciplina($turmaId, $disciplinaId);

        $ofertada = DB::table('turma_disciplina')
            ->where('turma_id', $turmaId)
            ->where('disciplina_id', $disciplinaId)
            ->exists();

        if (! $ofertada) {
            throw ValidationException::withMessages([
                'disciplina_id' => 'Esta disciplina não está ofertada nesta turma.',
            ]);
        }

        $user = auth()->user();
        $diario = $this->applyEmpresaScope($this->model->newQuery())
            ->where('turma_id', $turmaId)
            ->where('disciplina_id', $disciplinaId)
            ->whereDate('data', $data)
            ->when($user?->isProfessor(), fn ($q) => $q->where('user_id', $user->id))
            ->orderByDesc('id')
            ->first();

        return ['turma' => $turma, 'disciplina' => $disciplina, 'diario' => $diario];
    }
}
