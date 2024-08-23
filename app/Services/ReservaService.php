<?php

namespace App\Services;

use App\Models\Reserva;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class ReservaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Reserva $reserva)
    {
        $this->model = $reserva;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Reserva) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Reserva");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}