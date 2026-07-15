<?php

namespace App\Services;

use App\Models\PlanoMotoristaApp;
use Illuminate\Database\Eloquent\Model;

class PlanoMotoristaAppService extends AbstractBaseService
{
    public function __construct(PlanoMotoristaApp $planoMotoristaApp)
    {
        $this->model = $planoMotoristaApp;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof PlanoMotoristaApp) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\PlanoMotoristaApp');
        }

        $this->model = $model;
    }
}
