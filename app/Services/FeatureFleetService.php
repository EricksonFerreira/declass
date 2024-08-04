<?php

namespace App\Services;

use App\Models\FeatureFleet;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class FeatureFleetService extends AbstractBaseService
{
    protected $model;

    public function setModel(Model $model): void {
        if (!$model instanceof FeatureFleet) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\FeatureFleet");
        }
        $this->model = $model;
    }

    public function getModel(): Model {
        return $this->model;
    }
}
