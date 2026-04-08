<?php

namespace App\Services;

use App\Models\Feriado;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class FeriadoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Feriado $feriado)
    {
        $this->model = $feriado;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Feriado) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Feriado");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
