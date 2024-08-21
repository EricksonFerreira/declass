<?php

namespace App\Services;

use App\Models\Frota;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class FrotaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Frota $frota)
    {
        $this->model = $frota;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Frota) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Frota");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
