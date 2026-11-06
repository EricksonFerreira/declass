<?php

namespace App\Services;

use App\Models\Disciplina;
use Illuminate\Database\Eloquent\Model;

class DisciplinaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Disciplina $disciplina)
    {
        $this->model = $disciplina;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Disciplina) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Disciplina');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
