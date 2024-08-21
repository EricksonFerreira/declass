<?php

namespace App\Services;

use App\Models\TermoConsentimento;
use Illuminate\Database\Eloquent\Model;
use App\Services\AbstractBaseService;

class TermoConsentimentoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(TermoConsentimento $termo_consentimento)
    {
        $this->model = $termo_consentimento;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof TermoConsentimento) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\TermoConsentimento");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}