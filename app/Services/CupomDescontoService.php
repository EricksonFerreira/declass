<?php

namespace App\Services;

use App\Models\CupomDesconto;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class CupomDescontoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(CupomDesconto $cupom_desconto)
    {
        $this->model = $cupom_desconto;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof CupomDesconto) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\CupomDesconto");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}