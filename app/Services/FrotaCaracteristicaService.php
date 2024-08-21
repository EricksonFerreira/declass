<?php

namespace App\Services;

use App\Models\FrotaCaracteristica;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class FrotaCaracteristicaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(FrotaCaracteristica $frotaCaracteristica)
    {
        $this->model = $frotaCaracteristica;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof FrotaCaracteristica) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\FrotaCaracteristica");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
