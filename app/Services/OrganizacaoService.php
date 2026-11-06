<?php

namespace App\Services;

use App\Models\Organizacao;
use Illuminate\Database\Eloquent\Model;

class OrganizacaoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Organizacao $organizacao)
    {
        $this->model = $organizacao;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Organizacao) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Organizacao');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
