<?php

namespace App\Services;

use App\Models\PlanoMensalidade;
use Illuminate\Database\Eloquent\Model;

class PlanoMensalidadeService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(PlanoMensalidade $plano)
    {
        $this->model = $plano;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof PlanoMensalidade) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\PlanoMensalidade');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
