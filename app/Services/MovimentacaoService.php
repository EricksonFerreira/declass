<?php

namespace App\Services;

use App\Models\Movimentacao;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class MovimentacaoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Movimentacao $movimentacao)
    {
        $this->model = $movimentacao;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Movimentacao) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Movimentacao");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}