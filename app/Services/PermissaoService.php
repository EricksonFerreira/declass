<?php

namespace App\Services;

use App\Models\Permissao;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class PermissaoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Permissao $permissao)
    {
        $this->model = $permissao;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Permissao) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Permissao");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Permissões são globais — não aplicar escopo de empresa.
     */
    protected function shouldApplyEmpresaScope(): bool
    {
        return false;
    }

    protected function shouldApplyEstacaoScope(): bool
    {
        return false;
    }

    /**
     * Retorna todas as permissões globais, marcando quais estão na regra.
     */
    public function getAllWithChecked($regraPermissoes)
    {
        $permissoes = $this->model->newQuery()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        $permissoesDaRegra = $regraPermissoes->pluck('id')->toArray();

        foreach ($permissoes as $permissao) {
            $permissao->checked = in_array($permissao->id, $permissoesDaRegra) ? 'checked' : '';
        }

        return $permissoes;
    }

    /**
     * Retorna todas as permissões globais web ordenadas.
     */
    public function getAll($where = [])
    {
        // Mantemos compatibilidade com a assinatura de AbstractBaseService::getAll($where = [])
        // Ignoramos o filtro $where pois permissões são globais, mas aceitamos o parâmetro.
        return $this->model->newQuery()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();
    }
}
