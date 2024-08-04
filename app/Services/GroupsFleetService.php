<?php

namespace App\Services;

use App\Models\GroupsFleet;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class GroupsFleetService extends AbstractBaseService
{
    protected $model;

    public function setModel(Model $model): void {
        if (!$model instanceof GroupsFleet) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\GroupsFleet");
        }
        $this->model = $model;
    }

    public function getModel(): Model {
        return $this->model;
    }
}
