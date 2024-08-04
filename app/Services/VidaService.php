<?php

namespace App\Services;

use App\Models\Vida;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class VidaService extends AbstractBaseService
{
    protected $model;

    public function setModel(Model $model): void {
        if (!$model instanceof Vida) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Vida");
        }
        $this->model = $model;
    }

    public function getModel(): Model {
        return $this->model;
    }
}
