<?php

namespace App\Services;

use App\Models\StatusMovimentacao;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class StatusMovimentacaoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(StatusMovimentacao $status_movimentacao)
    {
        $this->model = $status_movimentacao;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof StatusMovimentacao) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\StatusMovimentacao");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}