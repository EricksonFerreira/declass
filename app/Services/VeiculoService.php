<?php

namespace App\Services;

use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class VeiculoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Veiculo $veiculo)
    {
        $this->model = $veiculo;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Veiculo) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Veiculo");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    // overide

    /**
     * Summary of create

     * @param array $data
     * @override
     * @return Model
     */
    public function create(array $data)
    {
        $veiculo = $this->model->create($data);
        $veiculo->nome = 'vei'. str_pad($veiculo->id, 4, '0', STR_PAD_LEFT);
        $veiculo->save();

        return $veiculo;
    }
}
