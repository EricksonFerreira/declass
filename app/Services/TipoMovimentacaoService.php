<?php

namespace App\Services;

use App\Models\TipoMovimentacao;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class TipoMovimentacaoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(TipoMovimentacao $tipo_movimentacao)
    {
        $this->model = $tipo_movimentacao;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof TipoMovimentacao) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\TipoMovimentacao");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}