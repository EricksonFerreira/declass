<?php

namespace App\Services;

use App\Models\DisponibilidadeInicialVeiculo;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class DisponibilidadeInicialVeiculoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(DisponibilidadeInicialVeiculo $disponibilidade_inicial_veiculo)
    {
        $this->model = $disponibilidade_inicial_veiculo;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof DisponibilidadeInicialVeiculo) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\DisponibilidadeInicialVeiculo");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}