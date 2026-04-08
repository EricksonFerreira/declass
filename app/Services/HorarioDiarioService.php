<?php

namespace App\Services;

use App\Models\HorarioDiario;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class HorarioDiarioService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(HorarioDiario $horario_diario)
    {
        $this->model = $horario_diario;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof HorarioDiario) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\HorarioDiario");
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
