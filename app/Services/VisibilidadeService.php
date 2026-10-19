<?php

namespace App\Services;

use App\Models\ClockifyProjeto;
use App\Models\ClockifyTime;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Escopo de visibilidade de dados por usuario.
 *
 * A permissao de rota (role_has_permissions) decide SE o usuario abre a tela.
 * Este service decide QUAIS registros ele ve dentro dela:
 *
 *  - superadmin / administrador -> null (sem restricao, todo o tenant)
 *  - lider de equipe            -> ele mesmo + membros dos times que lidera
 *  - colaborador comum          -> apenas ele mesmo
 */
class VisibilidadeService
{
    /**
     * Ids dos membros dos times liderados pelo usuario.
     *
     * @return int[]
     */
    public function idsEquipeLiderada(User $user): array
    {
        if (! $user->id) {
            return [];
        }

        return DB::table('clockify_time_user')
            ->join('clockify_times', 'clockify_times.id', '=', 'clockify_time_user.clockify_time_id')
            ->where('clockify_times.lider_id', $user->id)
            ->pluck('clockify_time_user.user_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * O usuario lidera ao menos um time ou responde por ao menos um projeto.
     */
    public function isLider(User $user): bool
    {
        if (! $user->id) {
            return false;
        }

        return ClockifyTime::where('lider_id', $user->id)->exists()
            || ClockifyProjeto::where('responsavel_id', $user->id)->exists();
    }

    /**
     * A empresa limita membros a enxergar apenas os proprios dados
     * (visao_restrita = true). Quando desligada, todo membro ve os
     * dados de todos os colaboradores e projetos do tenant.
     */
    public function visaoRestrita(User $user): bool
    {
        return (bool) ($user->empresa?->visao_restrita ?? true);
    }

    /**
     * Ids de usuarios cujos registros o usuario pode ver.
     * Retorna null quando nao ha restricao (admin/superadmin).
     *
     * @return int[]|null
     */
    public function idsVisiveis(User $user): ?array
    {
        if ($user->isAdministrador()) {
            return null;
        }

        // Empresa sem visao restrita: membros veem dados de todos.
        if (! $this->visaoRestrita($user)) {
            return null;
        }

        $roleName = strtolower(trim((string) ($user->regra?->name ?? '')));

        // Visualizador enxerga todos os dados do tenant, mas somente em leitura
        // (as rotas de escrita nao estao no papel dele).
        if ($roleName === 'visualizador') {
            return null;
        }

        return collect([$user->id])
            ->merge($this->idsEquipeLiderada($user))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Usuarios cujos registros o usuario pode ver (objetos, para selects).
     */
    public function usuariosVisiveis(User $user)
    {
        $ids = $this->idsVisiveis($user);

        $query = User::orderBy('nome');
        if ($user->empresa_id) {
            $query->where('empresa_id', $user->empresa_id);
        }
        if ($ids !== null) {
            $query->whereIn('id', $ids);
        }

        return $query->get();
    }

    public function podeVerUsuario(User $user, ?int $targetUserId): bool
    {
        if ($targetUserId === null || $user->isAdministrador()) {
            return true;
        }

        return in_array((int) $targetUserId, $this->idsVisiveis($user) ?? [], true);
    }

    /**
     * Pode aprovar/rejeitar/gerenciar registros de OUTRO usuario
     * (admin sempre; lider somente sobre membros dos times que lidera).
     *
     * Nao inclui o proprio usuario: cada um gerencia os proprios
     * registros via verificacao de dono, e aprovacao de si mesmo
     * exige um administrador.
     */
    public function podeGerenciarUsuario(User $user, ?int $targetUserId): bool
    {
        if ($targetUserId === null) {
            return false;
        }

        if ($user->isAdministrador()) {
            return true;
        }

        return in_array((int) $targetUserId, $this->idsEquipeLiderada($user), true);
    }

    /**
     * Pode editar/excluir o time (admin sempre; lider apenas o proprio time).
     */
    public function podeGerenciarTime(User $user, ClockifyTime $time): bool
    {
        return $user->isAdministrador() || (int) $time->lider_id === (int) $user->id;
    }

    /**
     * Pode editar o projeto (admin sempre; responsavel apenas o proprio projeto).
     */
    public function podeGerenciarProjeto(User $user, ClockifyProjeto $projeto): bool
    {
        return $user->isAdministrador() || (int) $projeto->responsavel_id === (int) $user->id;
    }

    /**
     * Pode ver dados do projeto (admin sempre; responsavel, membro do
     * projeto ou lider cuja equipe aponta horas nele).
     */
    public function podeVerProjeto(User $user, ClockifyProjeto $projeto): bool
    {
        if ($user->isAdministrador()) {
            return true;
        }

        if (! $this->visaoRestrita($user)) {
            return true;
        }

        if ((int) $projeto->responsavel_id === (int) $user->id) {
            return true;
        }

        if ($projeto->users()->where('users.id', $user->id)->exists()) {
            return true;
        }

        // Projeto privado: fora responsavel/membro/admin, ninguem mais ve.
        if (! $projeto->publico) {
            return false;
        }

        $idsEquipe = $this->idsEquipeLiderada($user);
        if ($idsEquipe !== []
            && $projeto->trackers()->whereIn('user_id', $idsEquipe)->exists()) {
            return true;
        }

        // Colaborador que apontou horas no projeto tambem o ve.
        return $projeto->trackers()->where('user_id', $user->id)->exists();
    }

    /**
     * Pode usar o projeto para apontar horas/alocar (admin sempre;
     * projeto publico qualquer usuario; privado so responsavel e membros).
     */
    public function podeUsarProjeto(User $user, ClockifyProjeto $projeto): bool
    {
        if ($user->isAdministrador() || $projeto->publico) {
            return true;
        }

        if ((int) $projeto->responsavel_id === (int) $user->id) {
            return true;
        }

        return $projeto->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Restringe uma query de projetos aos que o usuario pode ver/usar:
     * admin ve tudo; demais veem publicos + os que respondem ou sao membros.
     */
    public function projetosVisiveis(User $user, $query)
    {
        if ($user->isAdministrador() || ! $this->visaoRestrita($user)) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('publico', true)
                ->orWhere('responsavel_id', $user->id)
                ->orWhereHas('users', fn ($m) => $m->where('users.id', $user->id));
        });
    }

    /**
     * Quem passa a liderar um time precisa do papel "lider" para ter as
     * permissoes de rota correspondentes (aprovar folgas, gerenciar a
     * equipe, relatorios de custo). Nao rebaixa administrador nem quem
     * ja e lider; a democao manual fica a cargo do admin.
     */
    public function promoverLiderDeTime(?int $liderId): void
    {
        if (! $liderId) {
            return;
        }

        $empresaId = (int) DB::table('users')->where('id', $liderId)->value('empresa_id');
        if (! $empresaId) {
            return;
        }

        $roleIds = DB::table('roles')->where('empresa_id', $empresaId)->pluck('id', 'name');
        $liderRoleId = $roleIds['lider'] ?? null;
        $administradorRoleId = $roleIds['administrador'] ?? null;

        if (! $liderRoleId) {
            return;
        }

        $roleAtual = (int) DB::table('users')->where('id', $liderId)->value('role_id');

        if ($roleAtual !== (int) $liderRoleId && $roleAtual !== (int) $administradorRoleId) {
            DB::table('users')->where('id', $liderId)->update(['role_id' => $liderRoleId]);
        }
    }
}
