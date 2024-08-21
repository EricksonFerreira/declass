<?php

namespace App\Services;

use App\Models\Pais;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class PaisService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Pais $pais)
    {
        $this->model = $pais;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Pais) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Pais");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
    public function getAllForSelect(){
        $data = $this->model->select('sigla', 'nome')->distinct()->get();
        return $this->formatArraySelect($data,'sigla','nome');
    }
    public function getAllForSelectId(){
        $data = $this->model->select('id', 'nome')->distinct()->get();
        return $this->formatArraySelect($data,'id','nome');
    }

}
