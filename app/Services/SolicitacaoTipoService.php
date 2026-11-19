<?php

namespace App\Services;

use App\Models\SolicitacaoTipo;
use Illuminate\Database\Eloquent\Model;

class SolicitacaoTipoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(SolicitacaoTipo $solicitacaoTipo)
    {
        $this->model = $solicitacaoTipo;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof SolicitacaoTipo) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\SolicitacaoTipo');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
