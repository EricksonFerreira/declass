<?php

namespace App\Services;

use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Model;

class VeiculoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Veiculo $veiculo)
    {
        $this->model = $veiculo;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Veiculo) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Veiculo');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
