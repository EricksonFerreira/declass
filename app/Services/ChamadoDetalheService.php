<?php

namespace App\Services;

use App\Models\ChamadoDetalhe;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class ChamadoDetalheService extends AbstractBaseService
{
    protected $model;

    public function setModel(Model $model): void {
        if (!$model instanceof ChamadoDetalhe) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\ChamadoDetalhe");
        }
        $this->model = $model;
    }

    public function getModel(): Model {
        return $this->model;
    }
}
