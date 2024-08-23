<?php

namespace App\Services;

use App\Models\TipoOpcional;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class TipoOpcionalService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(TipoOpcional $tipo_opcional)
    {
        $this->model = $tipo_opcional;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof TipoOpcional) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\TipoOpcional");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}