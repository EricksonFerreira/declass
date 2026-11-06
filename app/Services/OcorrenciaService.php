<?php

namespace App\Services;

use App\Models\Ocorrencia;
use Illuminate\Database\Eloquent\Model;

class OcorrenciaService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Ocorrencia $ocorrencia)
    {
        $this->model = $ocorrencia;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Ocorrencia) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Ocorrencia');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data)
    {
        $data['user_id'] = $data['user_id'] ?? auth()->id();

        return parent::create($data);
    }
}
