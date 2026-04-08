<?php

namespace App\Services;

use App\Models\StatusReserva;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class StatusReservaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(StatusReserva $status_reserva)
    {
        $this->model = $status_reserva;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof StatusReserva) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\StatusReserva");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    protected function shouldApplyEmpresaScope(): bool { return false; }
    protected function shouldApplyEstacaoScope(): bool { return false; }
}