<?php

namespace App\Services;

use App\Models\AnoLetivo;
use Illuminate\Database\Eloquent\Model;

class AnoLetivoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(AnoLetivo $anoLetivo)
    {
        $this->model = $anoLetivo;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof AnoLetivo) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\AnoLetivo');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
