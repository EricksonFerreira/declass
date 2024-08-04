<?php

namespace App\Services;

use App\Models\Consent;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class ConsentService extends AbstractBaseService
{
    protected $model;

    public function setModel(Model $model): void {
        if (!$model instanceof Consent) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Consent");
        }
        $this->model = $model;
    }

    public function getModel(): Model {
        return $this->model;
    }
}
