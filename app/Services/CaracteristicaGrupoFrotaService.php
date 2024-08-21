<?php

namespace App\Services;

use App\Models\CaracteristicaGrupoFrota;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class CaracteristicaGrupoFrotaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(CaracteristicaGrupoFrota $caracteristicaGrupoFrota)
    {
        $this->model = $caracteristicaGrupoFrota;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof CaracteristicaGrupoFrota) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\CaracteristicaGrupoFrota");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
