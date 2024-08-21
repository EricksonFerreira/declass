<?php

namespace App\Services;

use App\Models\GrupoFrota;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class GrupoFrotaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(GrupoFrota $grupoFrota)
    {
        $this->model = $grupoFrota;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof GrupoFrota) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\GrupoFrota");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
