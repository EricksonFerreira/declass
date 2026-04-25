<?php

namespace App\Services;

use App\Models\Permissao;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PermissaoService extends AbstractBaseService
{
    protected Model $model;

    public function __construct(Permissao $permissao)
    {
        $this->model = $permissao;
    }

    public function setModel(Model $model): void
    {
        if (!$model instanceof Permissao) {
            throw new \InvalidArgumentException("Modelo esperado App\\Models\\Permissao");
        }
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    protected function shouldApplyEmpresaScope(): bool
    {
        return false;
    }

    protected function shouldApplyEstacaoScope(): bool
    {
        return false;
    }

    public function getAllWithChecked($regraPermissoes)
    {
        $permissoes = $this->getAll();
        $permissoesDaRegra = $regraPermissoes->pluck('id')->toArray();

        foreach ($permissoes as $permissao) {
            $permissao->checked = in_array($permissao->id, $permissoesDaRegra) ? 'checked' : '';
        }

        return $permissoes;
    }

    public function getAll($where = [])
    {
        return $this->model->newQuery()
            ->where('guard_name', 'web')
            ->orderByRaw("CASE WHEN grupo IS NULL OR grupo = '' THEN 1 ELSE 0 END")
            ->orderBy('grupo')
            ->orderBy('name')
            ->get();
    }

    public function getAllForUserContext(?User $user)
    {
        if (!$user || $user->isSuperAdmin()) {
            return $this->getAll();
        }

        $plano = $user->empresa?->planoAtivo()?->with('plano.permissoes')->first()?->plano;
        if (!$plano) {
            return collect();
        }

        if (!$plano->permissoes()->exists()) {
            return $this->getAll();
        }

        return $plano->permissoes()
            ->where('guard_name', 'web')
            ->orderByRaw("CASE WHEN grupo IS NULL OR grupo = '' THEN 1 ELSE 0 END")
            ->orderBy('grupo')
            ->orderBy('name')
            ->get();
    }

    public function getAllWithCheckedForUserContext($regraPermissoes, ?User $user)
    {
        $permissoes = $this->getAllForUserContext($user);
        $permissoesDaRegra = $regraPermissoes->pluck('id')->map(fn ($id) => (int) $id)->all();

        foreach ($permissoes as $permissao) {
            $permissao->checked = in_array((int) $permissao->id, $permissoesDaRegra, true) ? 'checked' : '';
        }

        return $permissoes;
    }
}
