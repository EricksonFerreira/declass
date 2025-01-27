<?php

namespace App\Services;

use App\Models\Modelo;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class ModeloService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Modelo $modelo)
    {
        $this->model = $modelo;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Modelo) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Modelo");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }


    public function getAllAtivosConcatenado($id = 'id', $orderBy = null, $orderDirection = 'asc')
    {
        // Seleciona e concatena as colunas marca, modelo e segmento
        $query = $this->model->where('ativo', 'S');

        if ($orderBy) {
            $query->orderBy($orderBy, $orderDirection);
        }

        $data = $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'nome' => $item->concatenado
            ];
        });

        return $this->formatArraySelect($data, $id, 'nome');
    }

    public function getModelosPorGrupoFrota($grupoFrotaId)
    {
        return $this->model->where('grupo_frota_id', $grupoFrotaId)->get();
    }

    public function obtemModelosPorGrupoFrota($grupoFrotaId)
    {
        return $this->model
            ->join('grupo_frota_modelo', 'modelos.id', '=', 'grupo_frota_modelo.modelo_id')
            ->where('grupo_frota_modelo.grupo_frota_id', $grupoFrotaId)
            ->select('modelos.*')
            ->get();
    }
}
