<?php

namespace App\Services;

use App\Models\DiaUtil;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class DiaUtilService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(DiaUtil $dia_util)
    {
        $this->model = $dia_util;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof DiaUtil) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\DiaUtil");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
