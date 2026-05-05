<?php

namespace App\Services;

use App\Models\CategoriaDano;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class CategoriaDanoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(CategoriaDano $categoria_dano)
    {
        $this->model = $categoria_dano;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof CategoriaDano) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\CategoriaDano");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
