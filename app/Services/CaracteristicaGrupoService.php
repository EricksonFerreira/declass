<?php

namespace App\Services;

use App\Models\CaracteristicaGrupo;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class CaracteristicaGrupoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(CaracteristicaGrupo $caracteristicaGrupo)
    {
        $this->model = $caracteristicaGrupo;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof CaracteristicaGrupo) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\CaracteristicaGrupo");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
