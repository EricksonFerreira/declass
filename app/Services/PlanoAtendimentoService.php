<?php

namespace App\Services;

use App\Models\PlanoAtendimento;
use Illuminate\Database\Eloquent\Model;

class PlanoAtendimentoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(PlanoAtendimento $plano)
    {
        $this->model = $plano;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof PlanoAtendimento) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\PlanoAtendimento');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
