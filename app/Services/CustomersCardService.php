<?php

namespace App\Services;

use App\Models\CustomersCard;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class CustomersCardService extends AbstractBaseService
{
    protected $model;

    public function setModel(Model $model): void {
        if (!$model instanceof CustomersCard) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\CustomersCard");
        }
        $this->model = $model;
    }

    public function getModel(): Model {
        return $this->model;
    }
}
