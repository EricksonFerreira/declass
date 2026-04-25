<?php

namespace App\Services;

use App\Models\Permissao;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

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

    /**
     * Prefixos de módulo que são exclusivos do superadmin da plataforma.
     * Nunca devem aparecer na tela de edição de perfil de usuários de empresa.
     */
    private array $modulosPlataforma = [
        'empresa',
        'replicacao_base',
        'regra',
        'permissao',
        'tipo_usuario',
        'plano',
        'planos',
        'assinatura',
        'migrations',
        'fipe',
    ];

    public function getAllForUserContext(?User $user)
    {
        if (!$user || $user->isSuperAdmin()) {
            return $this->getAll();
        }

        $query = $user->empresa?->planoAtivo();
        if (!$query) {
            return collect();
        }

        if (Schema::hasTable('plano_permissao')) {
            $query->with('plano.permissoes');
        } else {
            $query->with('plano');
        }

        $plano = $query->first()?->plano;
        if (!$plano) {
            return collect();
        }

        // Se a tabela plano_permissao existe e o plano tem permissões vinculadas,
        // usa esse conjunto como base — garantindo que o plano restringe o que é exibido.
        if (Schema::hasTable('plano_permissao') && $plano->permissoes()->exists()) {
            $permissoes = $plano->permissoes()
                ->where('guard_name', 'web')
                ->orderByRaw("CASE WHEN grupo IS NULL OR grupo = '' THEN 1 ELSE 0 END")
                ->orderBy('grupo')
                ->orderBy('name')
                ->get();
        } else {
            // Fallback: todas as permissões, mas filtra módulos pelo plano contratado.
            $permissoes = Permissao::query()
                ->where('guard_name', 'web')
                ->orderBy('grupo')
                ->orderBy('name')
                ->get();

            $permissoes = $permissoes->filter(function (Permissao $p) use ($plano) {
                return $this->permissaoPermitidaPorPlano($p->name, $plano);
            })->values();
        }

        // Sempre remove permissões de módulos de plataforma independente do plano.
        return $permissoes->filter(function (Permissao $p) {
            $prefixo = strtolower(explode('.', $p->name)[0]);
            return !in_array($prefixo, $this->modulosPlataforma, true);
        })->values();
    }

    /**
     * Verifica se uma permissão deve ser exibida considerando os módulos do plano.
     */
    private function permissaoPermitidaPorPlano(string $permissionName, $plano): bool
    {
        $prefixo = strtolower(explode('.', $permissionName)[0]);

        if (in_array($prefixo, $this->modulosPlataforma, true)) {
            return false;
        }

        // Módulos opcionais: só exibe se o plano inclui.
        if (in_array($prefixo, ['multa'], true) && !$plano->modulo_multas) {
            return false;
        }
        if (in_array($prefixo, ['nfse'], true) && !$plano->modulo_nfse) {
            return false;
        }
        if (str_starts_with($permissionName, 'empresa.site-apresentacao') && !$plano->site_vendas) {
            return false;
        }
        if (str_starts_with($permissionName, 'site.') && !$plano->site_vendas) {
            return false;
        }
        if (in_array($prefixo, ['api', 'webhook'], true) && !$plano->api_integracao) {
            return false;
        }

        return true;
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
