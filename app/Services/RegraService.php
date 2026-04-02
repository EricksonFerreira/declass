<?php

namespace App\Services;

use App\Models\Regra;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class RegraService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Regra $regra)
    {
        $this->model = $regra;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Regra) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Regra");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getAllTiposUsuarios(){
        $data = $this->applyEmpresaScope($this->model->newQuery())
            ->select('id', 'name')
            ->distinct()
            ->get();
        return $this->formatArraySelect($data);
    }

    public function getClienteRoleId(): ?int
    {
        return $this->applyEmpresaScope($this->model->newQuery())
            ->whereRaw('LOWER(name) = ?', ['cliente'])
            ->value('id');
    }
}
