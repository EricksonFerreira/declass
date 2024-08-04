<?php

namespace App\Services;

use App\Models\GroupFeature;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class GroupFeatureService extends AbstractBaseService
{
    protected $model;

    public function setModel(Model $model): void {
        if (!$model instanceof GroupFeature) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\GroupFeature");
        }
        $this->model = $model;
    }

    public function getModel(): Model {
        return $this->model;
    }
}
