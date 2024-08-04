<?php

namespace App\Services;

use App\Models\Fleet;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class FleetService extends AbstractBaseService
{
    protected $model;

    public function setModel(Model $model): void {
        if (!$model instanceof Fleet) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Fleet");
        }
        $this->model = $model;
    }

    public function getModel(): Model {
        return $this->model;
    }
}
