<?php

namespace App\Services;

use App\Models\Pais;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class PaisService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Pais $pais)
    {
        $this->model = $pais;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Pais) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Pais");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getAllForSelectId(){
        $data = $this->model->select('id', 'nome')->distinct()->get();
        return $this->formatArraySelect($data,'id','nome');
    }

    public function getAllForSelectSigla(): array
    {
        $query = $this->applyEmpresaScope($this->model->newQuery())
            ->select('sigla', 'nome')
            ->distinct();

        if (method_exists($this->model, 'getTable') && \Illuminate\Support\Facades\Schema::hasColumn($this->model->getTable(), 'ativo')) {
            $query->where('ativo', 'S');
        }

        $data = $query
            ->orderBy('nome', 'asc')
            ->get();

        return $this->formatArraySelect($data, 'sigla', 'nome');
    }

    public function getPaisBrasilId(): ?int
    {
        return $this->model->newQuery()
            ->where(function ($query) {
                $query->whereRaw('LOWER(sigla) = ?', ['br'])
                    ->orWhereRaw('LOWER(nome) like ?', ['%brasil%'])
                    ->orWhereRaw('LOWER(nome) like ?', ['%brazil%']);
            })
            ->value('id');
    }

}
