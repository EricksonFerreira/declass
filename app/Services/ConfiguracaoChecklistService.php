<?php

namespace App\Services;

use App\Models\ConfiguracaoChecklist;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class ConfiguracaoChecklistService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(ConfiguracaoChecklist $configuracaochecklist)
    {
        $this->model = $configuracaochecklist;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof ConfiguracaoChecklist) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\ConfiguracaoChecklist");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getAllWithFiltersAndPagination($filters = [], $perPage = 15, $with = [], $orderBy = 'id', $order = 'asc')
    {
        $resource = parent::getAllWithFiltersAndPagination($filters, $perPage, $with, $orderBy, $order);
        // Mantém a listagem resiliente quando algum relacionamento vier ausente.
        $resource->each(function ($item) {
            $item->tipoChecklistNome = $item->tipoChecklist?->nome ?? '-';
            $item->itemChecklistNome = $item->itemChecklist?->nome ?? '-';
        });
        return $resource;
    }

    public function create(array $data)
    {
        foreach ($data['item_checklist_id'] as $item) {
            $configuracaoChecklist = new ConfiguracaoChecklist;
            $configuracaoChecklist->tipo_checklist_id = $data['tipo_checklist_id'];
            $configuracaoChecklist->item_checklist_id = $item;
            $configuracaoChecklist->save();
        }
    }
}
