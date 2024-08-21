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

    public function getAllWithFiltersAndPagination($filters = [], $perPage = 15, $with = [], $orderBy = 'id', $order = 'asc')
    {

        $query = $this->model->with($with);

        if (!empty($filters)) {
            foreach ($filters as $field => $value) {
                if(isset($value['typeWhere']) && $value['typeWhere'] == 'where')
                    $query->where($field, $value['valor']);
                else
                    $query->where($field, 'like', '%' . $value['valor'] . '%');
            }
        }

        $query->orderBy($orderBy, $order);

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

    public function formatArraySelect($data, string $idField = 'id', string $nameField = 'name'): array
    {
        $arrayData = $data->toArray();
        return array_column($arrayData, $nameField, $idField);
    }


    public function getAllAtivos($id='id', $nome='nome'){
        $data = $this->model->select($id, $nome)->where('ativo','S')->distinct()->get();
        return $this->formatArraySelect($data,$id,$nome);
    }
}
