<?php

namespace App\Services;

use App\Models\Atribuicao;
use Illuminate\Database\Eloquent\Model;

class AtribuicaoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Atribuicao $atribuicao)
    {
        $this->model = $atribuicao;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Atribuicao) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Atribuicao');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
