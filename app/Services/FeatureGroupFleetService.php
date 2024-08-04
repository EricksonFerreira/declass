<?php

namespace App\Services;

use App\Models\FeatureGroupFleet;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class FeatureGroupFleetService extends AbstractBaseService
{
    protected $model;

    public function setModel(Model $model): void {
        if (!$model instanceof FeatureGroupFleet) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\FeatureGroupFleet");
        }
        $this->model = $model;
    }

    public function getModel(): Model {
        return $this->model;
    }
}
