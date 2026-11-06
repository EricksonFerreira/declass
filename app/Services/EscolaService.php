<?php

namespace App\Services;

use App\Models\Escola;
use Illuminate\Database\Eloquent\Model;

class EscolaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Escola $escola)
    {
        $this->model = $escola;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Escola) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Escola');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
