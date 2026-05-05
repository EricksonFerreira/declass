<?php

namespace App\Services;

use App\Models\Checklist;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class ChecklistService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Checklist $checklist)
    {
        $this->model = $checklist;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Checklist) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Checklist");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
