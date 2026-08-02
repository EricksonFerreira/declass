<?php

namespace App\Services;

use App\Models\ConfiguracaoChecklist;
use App\Models\TipoChecklist;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;
use Illuminate\Support\Facades\DB;

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

    public function create(array $data)
    {
        $itemChecklistIds = array_values(array_filter($data['item_checklist_id'] ?? [], fn ($value) => (string) $value !== ''));
        unset($data['item_checklist_id']);

        return DB::transaction(function () use ($data, $itemChecklistIds) {
            $tipoChecklist = parent::create($data);

            $this->syncItensChecklist($tipoChecklist, $itemChecklistIds);

            return $tipoChecklist;
        });
    }

    public function update($id, array $data)
    {
        $itemChecklistIds = array_values(array_filter($data['item_checklist_id'] ?? [], fn ($value) => (string) $value !== ''));
        unset($data['item_checklist_id']);

        return DB::transaction(function () use ($id, $data, $itemChecklistIds) {
            $tipoChecklist = parent::update($id, $data);

            if ($tipoChecklist) {
                $this->syncItensChecklist($tipoChecklist, $itemChecklistIds);
            }

            return $tipoChecklist;
        });
    }

    protected function syncItensChecklist(TipoChecklist $tipoChecklist, array $itemChecklistIds): void
    {
        ConfiguracaoChecklist::query()
            ->where('tipo_checklist_id', $tipoChecklist->id)
            ->delete();

        foreach ($itemChecklistIds as $itemChecklistId) {
            ConfiguracaoChecklist::create([
                'tipo_checklist_id' => $tipoChecklist->id,
                'item_checklist_id' => (int) $itemChecklistId,
                'ativo' => 'S',
            ]);
        }
    }
}
