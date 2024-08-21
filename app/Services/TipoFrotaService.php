<?php

namespace App\Services;

use App\Models\TipoFrota;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class TipoFrotaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(TipoFrota $tipo_frota)
    {
        $this->model = $tipo_frota;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof TipoFrota) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\TipoFrota");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

     /**
     * Update .
     *
     * @override
     * @param $id
     * @param array $data
     */
    public function update($id, array $data)
    {
        $tipoFrota = $this->model->find($id);
        if ($tipoFrota) {
            $tipoFrota->update($data);
        }
        if(isset($data['caracteristicas']) && !empty($data['caracteristicas'])){
            $this->adicionacaracteristicas($data['caracteristicas'], $id);
        }
        return $tipoFrota;
    }

    public function adicionacaracteristicas($caracteristica, $tipoFrotaId)
    {
        // Encontrar a estação pelo ID
        $tipoFrota = TipoFrota::find($tipoFrotaId);

        // Deletar todos os dias úteis da estação
        $tipoFrota->caracteristicas()->detach();

        if ($tipoFrota) {
            // Adicionar os dias úteis na tabela pivot
            $tipoFrota->caracteristicas()->attach($caracteristica, ['created_at' => now()]);
        } else {
            // Lidar com o caso em que a estação não é encontrada
            throw new \Exception("Característica não encontrada");
        }
    }

}
