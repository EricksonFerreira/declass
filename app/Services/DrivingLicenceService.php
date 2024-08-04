<?php

namespace App\Services;

use App\Models\DrivingLicence;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class DrivingLicenceService extends AbstractBaseService
{
    protected $model;

    public function setModel(Model $model): void {
        if (!$model instanceof DrivingLicence) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\DrivingLicence");
        }
        $this->model = $model;
    }

    public function getModel(): Model {
        return $this->model;
    }
}
