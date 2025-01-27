<?php

namespace App\Services;

use App\Models\ContratoCheckin;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class ContratoCheckinService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(ContratoCheckin $tipo_checklist)
    {
        $this->model = $tipo_checklist;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof ContratoCheckin) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\ContratoCheckin");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function assinarDocumento($id)
    {
        try {
            $registro = $this->model->where('id', $id)->first();
            $registro->assinado = 'S';
            $registro->save();

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
