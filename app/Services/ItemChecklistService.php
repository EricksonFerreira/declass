<?php

namespace App\Services;

use App\Models\ItemChecklist;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class ItemChecklistService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(ItemChecklist $item_checklist)
    {
        $this->model = $item_checklist;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof ItemChecklist) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\ItemChecklist");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}