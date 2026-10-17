<?php

namespace App\Services;

use App\Models\Regra;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Support\SchemaCache;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RegraService extends AbstractBaseService
{
    protected Model $model;

    protected function shouldApplyEmpresaScope(): bool
    {
        return SchemaCache::hasColumn($this->model->getTable(), 'empresa_id');
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

    /**
     * Aplica escopo de empresa: perfis sao vinculados a empresa,
     * entao o tenant ve apenas os proprios.
     */
    protected function applyEmpresaScope($query)
    {
        if (!$this->shouldApplyEmpresaScope()) {
            return $query;
        }

        if ($this->model->getTable() === 'empresas') {
            return $query;
        }

        $empresaId = $this->currentEmpresaId();
        if ($empresaId === null) {
            $user = Auth::user();
            if ($user?->isSuperAdmin()) {
                return $query;
            }
            throw new AccessDeniedHttpException('Empresa nao identificada para a operacao.');
        }

        return $query->where($this->model->getTable() . '.empresa_id', $empresaId);
    }

    public function getAllTiposUsuarios()
    {
        $query = $this->model->newQuery();

        $query = $this->applyEmpresaScope($query);

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
