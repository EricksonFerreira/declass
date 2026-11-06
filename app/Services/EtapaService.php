<?php

namespace App\Services;

use App\Models\Etapa;
use Illuminate\Database\Eloquent\Model;

class EtapaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Etapa $etapa)
    {
        $this->model = $etapa;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Etapa) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Etapa');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
