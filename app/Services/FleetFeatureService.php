<?php

namespace App\Services;

use App\Models\FleetFeature;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class FleetFeatureService extends AbstractBaseService
{
    protected $model;

    public function setModel(Model $model): void {
        if (!$model instanceof FleetFeature) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\FleetFeature");
        }
        $this->model = $model;
    }

    public function getModel(): Model {
        return $this->model;
    }
}
