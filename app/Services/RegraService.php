<?php

namespace App\Services;

use App\Models\Regra;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class RegraService extends AbstractBaseService
{
    protected Model $model;

    protected function shouldApplyEmpresaScope(): bool
    {
        if (!Schema::hasColumn($this->model->getTable(), 'empresa_id')) {
            return false;
        }

        if (request()?->routeIs('tipo_usuario.*') || request()?->routeIs('usuario.*')) {
            return true;
        }

        return !Auth::user()?->isSuperAdmin();
    }

    protected function shouldApplyEstacaoScope(): bool
    {
        return false;
    }

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

    public function getAllTiposUsuarios()
    {
        $query = $this->model->newQuery();

        if ($this->shouldApplyEmpresaScope()) {
            $query->where('empresa_id', $this->currentEmpresaId());
        }

        $data = $query
            ->select('id', 'name')
            ->distinct()
            ->orderBy('name')
            ->get();

        return $this->formatArraySelect($data);
    }

    public function getClienteRoleId(): ?int
    {
        return $this->model->newQuery()
            ->whereNull('empresa_id')
            ->whereRaw('LOWER(name) = ?', ['cliente'])
            ->value('id');
    }
}
