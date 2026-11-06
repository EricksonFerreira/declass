<?php

namespace App\Services;

use App\Models\Livro;
use Illuminate\Database\Eloquent\Model;

class LivroService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Livro $livro)
    {
        $this->model = $livro;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Livro) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Livro');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
