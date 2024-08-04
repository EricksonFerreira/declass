<?php

namespace App\Services;

use App\Services\Interfaces\BaseServiceInterface;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractBaseService implements BaseServiceInterface
{
    protected Model $model;

    public function getAll()
    {
        return $this->model->all();
    }

    public function getAllWithFiltersAndPagination($filters, $perPage = 15)
    {
        $query = $this->model->query();

        foreach ($filters as $field => $value) {
            $query->where($field, $value);
        }

        return $query->paginate($perPage);
    }

    public function getById($id)
    {
        return $this->model->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $item = $this->model->find($id);
        if ($item) {
            $item->update($data);
            return $item;
        }
        return null;
    }

    public function delete($id)
    {
        $item = $this->model->find($id);
        if ($item) {
            return $item->delete();
        }
        return false;
    }

    // Método abstrato para definir o modelo nas classes filhas
    abstract public function setModel(Model $model): void;
}
