<?php

namespace App\Services;

use App\Models\TipoValorCaracteristicaFrota;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class TipoValorCaracteristicaFrotaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(TipoValorCaracteristicaFrota $tipo_valor_caracteristica_frota)
    {
        $this->model = $tipo_valor_caracteristica_frota;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof TipoValorCaracteristicaFrota) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\TipoValorCaracteristicaFrota");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getAllValores(){
        $data = $this->model->select('id', 'nome')->distinct()->get();
        return $this->formatArraySelect($data,'id','nome');
    }
}
