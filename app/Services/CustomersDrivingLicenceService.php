<?php

namespace App\Services;

use App\Models\CustomersDrivingLicence;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class CustomersDrivingLicenceService extends AbstractBaseService
{
    protected $model;

    public function setModel(Model $model): void {
        if (!$model instanceof CustomersDrivingLicence) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\CustomersDrivingLicence");
        }
        $this->model = $model;
    }

    public function getModel(): Model {
        return $this->model;
    }
}
