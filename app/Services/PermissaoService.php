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

    public function getAllWithChecked($regraPermissoes)
    {
        $permissoes = $this->getAll();
        $permissoesDaRegra = $regraPermissoes->pluck('id')->toArray();

        foreach ($permissoes as $permissao) {
            $permissao->checked = in_array($permissao->id, $permissoesDaRegra) ? 'checked' : '';
        }

        return $permissoes;
    }
}
