<?php

namespace App\Services;

use App\Models\Pessoa;
use App\Models\Responsavel;
use App\Support\SchemaCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ResponsavelService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Responsavel $responsavel)
    {
        $this->model = $responsavel;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Responsavel) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Responsavel');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Paginacao com filtros da sessao. 'nome'/'email' buscam na
     * pessoa relacionada; 'profissao'/'ativo' sao colunas proprias.
     */
    public function paginateComFiltros(array $filters, int $perPage): LengthAwarePaginator
    {
        $filters = $this->sanitizeEscolaContextFilter($filters);

        $query = $this->applyEmpresaScope($this->model->newQuery())
            ->with('pessoa');

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
                $query->whereHas('pessoa', function ($q) use ($valor) {
                    $q->where('nome', 'like', '%'.$valor.'%')
                        ->orWhere('nome_social', 'like', '%'.$valor.'%');
                });

                continue;
            }

            if ($field === 'email') {
                $query->whereHas('pessoa', function ($q) use ($valor) {
                    $q->where('email', 'like', '%'.$valor.'%');
                });

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

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * Cria a Pessoa (dados pessoais) e o Responsavel em transacao.
     */
    public function createWithPessoa(array $pessoaData, array $responsavelData): Responsavel
    {
        $this->injectEmpresaId($pessoaData);
        $this->injectEmpresaId($responsavelData);

        return DB::transaction(function () use ($pessoaData, $responsavelData) {
            $pessoa = Pessoa::create($pessoaData);
            $responsavelData['pessoa_id'] = $pessoa->id;

            return Responsavel::create($responsavelData);
        });
    }

    /**
     * Atualiza Pessoa e Responsavel do registro informado.
     */
    public function updateWithPessoa($id, array $pessoaData, array $responsavelData): ?Responsavel
    {
        $responsavel = $this->applyEmpresaScope($this->model->newQuery())
            ->with('pessoa')
            ->find($id);

        if (! $responsavel) {
            return null;
        }

        $this->injectEmpresaId($responsavelData, true);

        return DB::transaction(function () use ($responsavel, $pessoaData, $responsavelData) {
            if ($responsavel->pessoa) {
                $responsavel->pessoa->update($pessoaData);
            }

            $responsavel->update($responsavelData);

            return $responsavel->fresh('pessoa');
        });
    }
}
