<?php

namespace App\Services;

use App\Models\OpcionalGrupoVeiculo;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class OpcionalGrupoVeiculoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(OpcionalGrupoVeiculo $opcional_grupo_veiculo)
    {
        $this->model = $opcional_grupo_veiculo;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof OpcionalGrupoVeiculo) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\OpcionalGrupoVeiculo");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}