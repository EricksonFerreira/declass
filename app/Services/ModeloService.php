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


    public function getAllAtivosConcatenado($id = 'id')
    {
        // Seleciona e concatena as colunas marca, modelo e segmento
        $data = $this->model->where('ativo', 'S')
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'nome' => $item->concatenado
            ];
        });

        return $this->formatArraySelect($data, $id, 'nome');
    }
}
