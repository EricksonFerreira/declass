<?php

namespace App\Services;

use App\Models\StatusItemChecklist;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class StatusItemChecklistService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(StatusItemChecklist $status_item_checklist)
    {
        $this->model = $status_item_checklist;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof StatusItemChecklist) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\StatusItemChecklist");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
