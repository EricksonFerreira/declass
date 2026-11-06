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
        if (! $model instanceof Permissao) {
            throw new \InvalidArgumentException('Modelo esperado App\\Models\\Permissao');
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

    /**
     * Modulos exclusivos da plataforma — nunca aparecem na tela de
     * edicao de perfil de usuarios de empresa (tenant).
     */
    private array $modulosPlataforma = [
        'empresa',
        'migrations',
        'comandos',
        'limpar-cache',
        'sitemap',
        'cron',
        'site',
        'manifest',
        'pwa',
        'log-viewer',
        'darkmode',
        'adminlte',
        'cadastro',
        'empresa-contexto',
        'empresa_context',
    ];

    public function getAllForUserContext(?User $user)
    {
        if (! $user || $user->isSuperAdmin()) {
            return $this->getAll();
        }

        // Sem gate de plano: o tenant gerencia todos os modulos
        // operacionais; apenas os modulos de plataforma ficam de fora.
        return $this->getAll()->filter(function (Permissao $p) {
            $prefixo = strtolower(explode('.', $p->name)[0]);

            return ! in_array($prefixo, $this->modulosPlataforma, true)
                && ! Permissao::isNomeInterno($p->name);
        })->values();
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
