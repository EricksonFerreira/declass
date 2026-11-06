<?php

namespace App\Services;

use App\Models\Turma;
use App\Support\SchemaCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class TurmaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Turma $turma)
    {
        $this->model = $turma;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Turma) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Turma');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Paginacao com filtros da sessao, eager loading das relacoes
     * de exibicao (escola, ano letivo, etapa).
     */
    public function paginateComFiltros(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = $this->applyEmpresaScope($this->model->newQuery())
            ->with(['escola', 'anoLetivo', 'etapa']);

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
                $query->where('nome', 'like', '%'.$valor.'%');

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

        return $query->orderBy('nome')->orderBy('id')->paginate($perPage);
    }
}
