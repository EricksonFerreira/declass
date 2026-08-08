<?php

namespace App\Services;

use App\Models\Pais;
use Illuminate\Database\Eloquent\Model;

class PaisService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Pais $pais)
    {
        $this->model = $pais;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Pais) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Pais');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    protected function shouldApplyEmpresaScope(): bool
    {
        return false;
    }

    protected function shouldApplyEstacaoScope(): bool
    {
        return false;
    }

    public function getAllForSelectSigla(): array
    {
        $data = $this->model->newQuery()
            ->select('sigla', 'nome')
            ->orderBy('nome')
            ->get();

        return $this->formatArraySelect($data, 'sigla', 'nome');
    }
}
