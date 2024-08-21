<?php

namespace App\Services;

use App\Models\Marca;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class MarcaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Marca $marca)
    {
        $this->model = $marca;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Marca) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Marca");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
