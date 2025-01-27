<?php

namespace App\Services;

use App\Models\Dano;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class DanoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Dano $dano)
    {
        $this->model = $dano;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Dano) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Dano");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getDanosPorCategoriaELocalizacao($categoria_id, $localizacao_id)
    {
        return $this->model->where('categoria_id', $categoria_id)
            ->where('localizacao', strtolower($localizacao_id))
            ->get();
    }


}
