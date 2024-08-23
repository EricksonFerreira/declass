<?php

namespace App\Services;

use App\Models\Opcional;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class OpcionalService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Opcional $opcional)
    {
        $this->model = $opcional;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Opcional) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Opcional");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
