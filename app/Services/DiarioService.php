<?php

namespace App\Services;

use App\Models\Diario;
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
            ->with(['turma.escola', 'turma.anoLetivo', 'disciplina', 'professor']);

        $user = auth()->user();
        if ($user && self::ehProfessor($user)) {
            $query->where('user_id', $user->id);
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

    /** Professor so edita/remove diarios que ele mesmo registrou. */
    public function podeGerenciar(Diario $diario, ?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            return false;
        }

        if (! self::ehProfessor($user)) {
            return true;
        }

        return (int) $diario->user_id === (int) $user->id;
    }

    public function update($id, array $data)
    {
        $diario = $this->getById($id);
        abort_if(! $diario || ! $this->podeGerenciar($diario), 403, 'Sem acesso a este diário.');

        return parent::update($id, $data);
    }

    public function delete($id)
    {
        $diario = $this->getById($id);
        abort_if(! $diario || ! $this->podeGerenciar($diario), 403, 'Sem acesso a este diário.');

        return parent::delete($id);
    }

    /** Matriculas ativas da turma do diario (base da chamada). */
    public function matriculasDaTurma(Diario $diario)
    {
        return \App\Models\Matricula::query()
            ->with('aluno.pessoa')
            ->where('turma_id', $diario->turma_id)
            ->where('status', 'ativa')
            ->orderBy('numero_chamada')
            ->orderBy('id')
            ->get();
    }
}
