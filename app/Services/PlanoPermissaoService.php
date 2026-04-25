<?php

namespace App\Services;

use App\Models\Empresa;
use App\Models\Permissao;
use App\Models\Plano;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PlanoPermissaoService
{
    public function planoAtivoDaEmpresa(?Empresa $empresa): ?Plano
    {
        if (! $empresa) {
            return null;
        }

        return $empresa->planoAtivo()?->with('plano.permissoes')->first()?->plano;
    }

    public function permissoesPermitidasDoPlano(Plano $plano): Collection
    {
        return $plano->permissoes()
            ->where('guard_name', 'web')
            ->orderBy('grupo')
            ->orderBy('name')
            ->get();
    }

    public function idsPermitidosDoPlano(Plano $plano): array
    {
        $ids = $this->permissoesPermitidasDoPlano($plano)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (!empty($ids)) {
            return $ids;
        }

        return Permissao::query()
            ->where('guard_name', 'web')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function quantidadeMaximaPorRegra(Plano $plano): ?int
    {
        return $plano->limite_permissoes_por_regra !== null
            ? (int) $plano->limite_permissoes_por_regra
            : null;
    }

    public function empresaPodeUsarPermissao(?Empresa $empresa, string $permissionName): bool
    {
        if ($permissionName === '') {
            return false;
        }

        $plano = $this->planoAtivoDaEmpresa($empresa);
        if (! $plano) {
            return false;
        }

        if (! $plano->permissoes()->exists()) {
            return true;
        }

        return $plano->permissoes()
            ->where('guard_name', 'web')
            ->where('name', $permissionName)
            ->exists();
    }

    public function usuarioPodeUsarPermissao(User $user, string $permissionName): bool
    {
        return $this->empresaPodeUsarPermissao($user->empresa, $permissionName);
    }

    public function validarSelecaoDePermissoes(User $user, array $permissionIds): array
    {
        $permissionIds = collect($permissionIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $plano = $this->planoAtivoDaEmpresa($user->empresa);
        if (! $plano) {
            return ['ok' => false, 'message' => 'A empresa precisa ter um plano ativo para configurar perfis de acesso.'];
        }

        $permitidos = $this->idsPermitidosDoPlano($plano);
        $invalidos = array_values(array_diff($permissionIds, $permitidos));

        if (! empty($invalidos)) {
            return ['ok' => false, 'message' => 'O plano atual nao permite conceder uma ou mais permissoes selecionadas.'];
        }

        $limite = $this->quantidadeMaximaPorRegra($plano);
        if ($limite !== null && count($permissionIds) > $limite) {
            return ['ok' => false, 'message' => "O plano atual permite no maximo {$limite} permissoes por perfil."];
        }

        return ['ok' => true, 'message' => null];
    }

    public function gruposDePermissoesParaTela(Collection $permissoes): array
    {
        return $permissoes
            ->groupBy(fn (Permissao $permissao) => $permissao->grupo_efetivo ?: '_outros')
            ->sortKeys()
            ->all();
    }
}
