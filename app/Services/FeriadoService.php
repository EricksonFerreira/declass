<?php

namespace App\Services;

use App\Models\Feriado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FeriadoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Feriado $feriado)
    {
        $this->model = $feriado;
    }

    public function setModel(Model $model): void
    {
        if (! $model instanceof Feriado) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Feriado');
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Lista feriados do mais recente ao mais antigo, com escola
     * (nulo = toda a rede).
     */
    public function listarComEscola(array $filtros = []): Collection
    {
        return Feriado::query()
            ->with('escola:id,nome')
            ->when(! empty($filtros['escola_id']), fn ($q) => $q->where(fn ($w) => $w->whereNull('escola_id')->orWhere('escola_id', (int) $filtros['escola_id'])))
            ->when(! empty($filtros['ano']), fn ($q) => $q->whereYear('data_inicio', (int) $filtros['ano']))
            ->orderByDesc('data_inicio')
            ->get();
    }
}
