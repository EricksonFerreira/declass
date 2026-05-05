<?php

namespace App\Services;

use App\Models\Segmento;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class SegmentoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Segmento $segmento)
    {
        $this->model = $segmento;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Segmento) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Segmento");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
