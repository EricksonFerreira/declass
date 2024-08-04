<?php

namespace App\Services;

use App\Models\CustomersConcent;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class CustomersConcentService extends AbstractBaseService
{
    protected $model;

    public function setModel(Model $model): void {
        if (!$model instanceof CustomersConcent) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\CustomersConcent");
        }
        $this->model = $model;
    }

    public function getModel(): Model {
        return $this->model;
    }
}
