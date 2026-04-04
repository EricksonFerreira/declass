<?php

namespace App\Services;

use App\Models\CaracteristicaFrota;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class CaracteristicaFrotaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(CaracteristicaFrota $caracteristicaFrota)
    {
        $this->model = $caracteristicaFrota;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof CaracteristicaFrota) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\CaracteristicaFrota");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getAllAtivosWithTipoValor()
    {
        $query = $this->applyEstacaoScope($this->applyEmpresaScope($this->model->newQuery()))
            ->with('tipoValorDescricao:id,nome,configuracao')
            ->where('ativo', 'S')
            ->orderBy('nome');

        return $query->get(['id', 'nome', 'tipo_valor_caracteristica_frota_id']);
    }
}
