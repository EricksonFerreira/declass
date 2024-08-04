<?php

namespace App\Services;

use App\Models\Time;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class TimeService extends AbstractBaseService
{
    protected $model;

    public function setModel(Model $model): void {
        if (!$model instanceof Time) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Time");
        }
        $this->model = $model;
    }

    public function getModel(): Model {
        return $this->model;
    }
}
