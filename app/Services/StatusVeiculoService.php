<?php

namespace App\Services;

use App\Models\StatusVeiculo;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class StatusVeiculoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(StatusVeiculo $status_veiculo)
    {
        $this->model = $status_veiculo;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof StatusVeiculo) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\StatusVeiculo");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
