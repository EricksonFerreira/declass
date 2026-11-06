<?php

namespace App\Services;

use App\Models\Matricula;
use App\Support\SchemaCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class MatriculaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Matricula $matricula)
    {
        $this->model = $matricula;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Matricula) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Matricula');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Paginacao com filtros da sessao. 'nome' busca no aluno/pessoa;
     * 'ano_letivo_id' e coluna propria (denormalizada da turma).
     */
    public function paginateComFiltros(array $filters, int $perPage): LengthAwarePaginator
    {
        $filters = $this->sanitizeEscolaContextFilter($filters);

        $query = $this->applyEmpresaScope($this->model->newQuery())
            ->with(['aluno.pessoa', 'turma.escola', 'turma.anoLetivo', 'turma.etapa']);

        foreach ($filters as $field => $filter) {
            if (! is_array($filter) || ! array_key_exists('valor', $filter)) {
                continue;
            }

            $valor = $filter['valor'];
            if ($valor === null || $valor === '') {
                continue;
            }

            $typeWhere = $filter['typeWhere'] ?? 'where';

            if ($field === 'nome') {
                $query->whereHas('aluno.pessoa', function ($q) use ($valor) {
                    $q->where('nome', 'like', '%'.$valor.'%')
                        ->orWhere('nome_social', 'like', '%'.$valor.'%');
                });

                continue;
            }

            if ($field === 'numero_chamada') {
                $query->where('numero_chamada', (int) $valor);

                continue;
            }

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

        return $query->orderBy('numero_chamada')->orderBy('id')->paginate($perPage);
    }

    /**
     * Cria a matricula resolvendo ano_letivo_id a partir da turma
     * e validando que o par aluno+turma ainda nao existe.
     */
    public function create(array $data): Matricula
    {
        $turma = \App\Models\Turma::query()->find($data['turma_id'] ?? 0);
        abort_if(! $turma, 422, 'Turma não encontrada.');

        $data['ano_letivo_id'] = $turma->ano_letivo_id;

        $existe = $this->applyEmpresaScope($this->model->newQuery())
            ->where('aluno_id', $data['aluno_id'])
            ->where('turma_id', $data['turma_id'])
            ->exists();

        if ($existe) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'aluno_id' => 'Este aluno já possui matrícula nesta turma.',
            ]);
        }

        return parent::create($data);
    }
}
