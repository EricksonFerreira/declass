<?php

namespace App\Services;

use App\Models\AnoLetivo;
use App\Models\PeriodoLetivo;
use Illuminate\Database\Eloquent\Model;

class PeriodoLetivoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(PeriodoLetivo $periodo)
    {
        $this->model = $periodo;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof PeriodoLetivo) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\PeriodoLetivo');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data)
    {
        // escola_id e derivado do ano letivo — o formulario so
        // envia ano_letivo_id.
        $anoLetivo = AnoLetivo::query()->find($data['ano_letivo_id'] ?? 0);
        if ($anoLetivo) {
            $data['escola_id'] = $anoLetivo->escola_id;
        }

        return parent::create($data);
    }
}
