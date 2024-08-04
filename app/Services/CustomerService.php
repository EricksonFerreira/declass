<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class CustomerService extends AbstractBaseService
{
    protected $model;

    public function setModel(Model $model): void {
        if (!$model instanceof Customer) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Customer");
        }
        $this->model = $model;
    }

    public function getModel(): Model {
        return $this->model;
    }
}
