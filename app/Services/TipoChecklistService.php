<?php

namespace App\Services;

use App\Models\TipoChecklist;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class TipoChecklistService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(TipoChecklist $tipo_checklist)
    {
        $this->model = $tipo_checklist;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof TipoChecklist) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\TipoChecklist");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}