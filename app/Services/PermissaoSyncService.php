<?php

namespace App\Services;

use App\Models\Permissao;
use App\Models\Regra;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class PermissaoSyncService
{
    /**
     * Sincroniza permissões (por rotas nomeadas) e perfis padrão (roles) no modo global.
     *
     * @return array{permissions:int, roles:int, pivots:int}
     */
    public function sync(): array
    {
        $permissionNames = $this->collectPermissionNamesFromRoutes();
        if (empty($permissionNames)) {
            return ['permissions' => 0, 'roles' => 0, 'pivots' => 0];
        }

        $createdPermissions = $this->syncPermissionsGlobal($permissionNames);
        [$createdRoles, $pivotCount] = $this->syncRolesGlobal($permissionNames);

        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));

        return ['permissions' => $createdPermissions, 'roles' => $createdRoles, 'pivots' => $pivotCount];
    }

    /**
     * Roda sync somente se estiver vazio (tela não mostra nada / regras sem permissões).
     */
    public function syncIfNeeded(): void
    {
        $hasPermissions = Permissao::query()->where('guard_name', 'web')->exists();
        $hasAnyPivot = DB::table('role_has_permissions')->exists();
        $adminRoleId = (int) (Regra::query()
            ->where('guard_name', 'web')
            ->whereRaw('LOWER(name) = ?', ['administrador'])
            ->value('id') ?? 0);

        $adminHasPivot = $adminRoleId > 0
            ? DB::table('role_has_permissions')->where('role_id', $adminRoleId)->exists()
            : false;

        if (! $hasPermissions || ! $hasAnyPivot || ! $adminHasPivot) {
            $this->sync();
        }
    }

    /**
     * @return array<int, string>
     */
    private function collectPermissionNamesFromRoutes(): array
    {
        $names = [];
        $ignoredPrefixes = [
            'login',
            'logout',
            'register',
            'password.',
            'verification.',
            'sanctum.',
            'ignition.',
            'debugbar.',
            'generated::',
        ];

        foreach (Route::getRoutes() as $route) {
            $name = $route->getName();
            if (! $name) {
                continue;
            }

            $ignore = false;
            foreach ($ignoredPrefixes as $prefix) {
                if (str_starts_with($name, $prefix)) {
                    $ignore = true;
                    break;
                }
            }

            if ($ignore) {
                continue;
            }

            $names[] = $name;
        }

        $names = array_values(array_unique($names));
        sort($names);

        return $names;
    }

    /**
     * @param array<int, string> $permissionNames
     */
    private function syncPermissionsGlobal(array $permissionNames): int
    {
        $created = 0;

        foreach ($permissionNames as $permissionName) {
            $existing = Permissao::query()
                ->where('name', $permissionName)
                ->where('guard_name', 'web')
                ->first();

            if ($existing) {
                continue;
            }

            Permissao::query()->create([
                'name'       => $permissionName,
                'guard_name' => 'web',
                'descricao'  => null,
                'grupo'      => $this->grupoFromName($permissionName),
            ]);
            $created++;
        }

        return $created;
    }

    /**
     * @param array<int, string> $permissionNames
     * @return array{0:int, 1:int}
     */
    private function syncRolesGlobal(array $permissionNames): array
    {
        $allPermissions = Permissao::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $permissionNames)
            ->get();

        $createdRoles = 0;
        $pivotCount = 0;

        foreach ($this->buildRolePermissionFilters() as $roleName => $filter) {
            $role = Regra::query()->firstOrCreate(
                [
                    'name' => $roleName,
                    'guard_name' => 'web',
                ],
                []
            );

            if ($role->wasRecentlyCreated) {
                $createdRoles++;
            }

            $permissionsForRole = $allPermissions
                ->filter(fn (Permissao $permission) => $filter($permission->name))
                ->pluck('id')
                ->all();

            $role->permissoes()->sync($permissionsForRole);
            $pivotCount += count($permissionsForRole);
        }

        return [$createdRoles, $pivotCount];
    }

    /**
     * @return array<string, \Closure(string): bool>
     */
    private function buildRolePermissionFilters(): array
    {
        return [
            'administrador' => fn (string $permissionName) => true,

            // Gerente: opera o sistema operacional/financeiro; sem acesso a gestão de plataforma.
            'gerente' => fn (string $permissionName) =>
                ! $this->isDangerousAction($permissionName)
                && ! $this->isPlatformModule($permissionName)
                && (
                    ! $this->isSecurityModule($permissionName)
                    || $this->isReservationStaffPermission($permissionName)
                ),

            // Colaborador chefe: foco operacional com leitura ampla; sem plataforma.
            'colaborador_chefe' => fn (string $permissionName) =>
                ! $this->isPlatformModule($permissionName)
                && (
                    (
                        $this->isOperationalAction($permissionName)
                        && $this->isOperationalModule($permissionName)
                    )
                    || $this->isReservationStaffPermission($permissionName)
                ),

            // Colaborador administrativo: leitura + rotinas operacionais básicas; sem plataforma.
            'colaborador_administrativo' => fn (string $permissionName) =>
                ! $this->isSecurityModule($permissionName)
                && ! $this->isPlatformModule($permissionName)
                && (
                    $this->isReadOnlyAction($permissionName)
                    || ($this->isOperationalAction($permissionName) && $this->isOperationalModule($permissionName))
                    || $this->isReservationStaffPermission($permissionName)
                ),

            // Cliente: acesso mínimo.
            'cliente' => fn (string $permissionName) => $this->isReservationClientPermission($permissionName),

            // Compatibilidade (bases antigas).
            'gestor' => fn (string $permissionName) =>
                ! $this->isDangerousAction($permissionName)
                && ! $this->isPlatformModule($permissionName)
                && (
                    ! $this->isSecurityModule($permissionName)
                    || $this->isReservationStaffPermission($permissionName)
                ),
            'operador' => fn (string $permissionName) =>
                ! $this->isPlatformModule($permissionName)
                && (
                    (
                        $this->isOperationalAction($permissionName)
                        && $this->isOperationalModule($permissionName)
                    )
                    || $this->isReservationStaffPermission($permissionName)
                ),
            'financeiro' => fn (string $permissionName) =>
                ! $this->isPlatformModule($permissionName)
                && (
                    $this->isReadOnlyAction($permissionName)
                    || $this->isFinancialModule($permissionName)
                    || $this->isReservationFinancialPermission($permissionName)
                ),
            'visualizador' => fn (string $permissionName) =>
                ! $this->isPlatformModule($permissionName)
                && $this->isReadOnlyAction($permissionName),
        ];
    }

    private function actionFromPermissionName(string $permissionName): string
    {
        if (! str_contains($permissionName, '.')) {
            return $permissionName;
        }

        return (string) str($permissionName)->afterLast('.');
    }

    private function isDangerousAction(string $permissionName): bool
    {
        $action = $this->actionFromPermissionName($permissionName);

        return in_array($action, ['destroy', 'excluirMetodoPagamento'], true);
    }

    private function isReadOnlyAction(string $permissionName): bool
    {
        $action = $this->actionFromPermissionName($permissionName);

        return in_array($action, ['index', 'show', 'filtro', 'setPerPage', 'removerFiltro', 'home', 'dashboard'], true);
    }

    private function isOperationalAction(string $permissionName): bool
    {
        $action = $this->actionFromPermissionName($permissionName);

        return in_array($action, [
            'index',
            'show',
            'create',
            'store',
            'edit',
            'update',
            'filtro',
            'setPerPage',
            'removerFiltro',
            'alterarSenha',
            'salvarNovaSenha',
            'metodoPagamento',
            'adicionarMetodoPagamento',
        ], true);
    }

    private function grupoFromName(string $name): string
    {
        if (!str_contains($name, '.')) {
            return $name;
        }
        return (string) str($name)->before('.');
    }

    private function modulePrefix(string $permissionName): string
    {
        if (! str_contains($permissionName, '.')) {
            return $permissionName;
        }

        return (string) str($permissionName)->before('.');
    }

    private function isSecurityModule(string $permissionName): bool
    {
        return in_array($this->modulePrefix($permissionName), ['permissao', 'tipo_usuario'], true);
    }

    /**
     * Módulos de gestão de plataforma: exclusivos do superadmin.
     * Nenhum usuário de empresa cliente deve ter acesso a estes módulos.
     */
    private function isPlatformModule(string $permissionName): bool
    {
        // Módulos de status são somente-leitura para empresa; gerenciamento é do superadmin.
        if ($this->isStatusManagementAction($permissionName)) {
            return true;
        }

        return in_array($this->modulePrefix($permissionName), [
            'empresa',
            'replicacao_base',
            'regra',
            'permissao',
            'tipo_usuario',
            'plano',
            'planos',
            'assinatura',
            // Cadastros globais da plataforma
            'job',
            'sitemap',
            'pais',
            'dia_util',
            'dias_uteis',
            'horario_diario',
        ], true);
    }

    /**
     * Ações de gestão (criar/editar/excluir) nos módulos de status.
     * Usuários de empresa só podem visualizar esses registros, não gerenciá-los.
     */
    private function isStatusManagementAction(string $permissionName): bool
    {
        $statusModules = ['status_veiculo', 'status_movimentacao', 'status_reserva', 'status_veiculos'];
        $prefix = $this->modulePrefix($permissionName);

        if (!in_array($prefix, $statusModules, true)) {
            return false;
        }

        // Ações de leitura são permitidas para empresa — apenas gestão é bloqueada
        $action = $this->actionFromPermissionName($permissionName);
        return !in_array($action, ['index', 'show', 'filtro', 'setPerPage', 'removerFiltro'], true);
    }

    private function isFinancialModule(string $permissionName): bool
    {
        return in_array($this->modulePrefix($permissionName), ['financeiro', 'transacao', 'cupom', 'calcao'], true);
    }

    private function isReservationStaffPermission(string $permissionName): bool
    {
        return in_array($permissionName, $this->reservationStaffPermissions(), true);
    }

    private function isReservationFinancialPermission(string $permissionName): bool
    {
        return in_array($permissionName, $this->reservationFinancialPermissions(), true);
    }

    private function isReservationClientPermission(string $permissionName): bool
    {
        return in_array($permissionName, $this->reservationClientPermissions(), true);
    }

    private function isOperationalModule(string $permissionName): bool
    {
        return in_array($this->modulePrefix($permissionName), [
            'reserva',
            'movimentacao',
            'veiculo',
            'checklist',
            'estacao',
            'dano',
            'sinistro',
            'multa',
        ], true);
    }

    /**
     * @return array<int, string>
     */
    private function reservationStaffPermissions(): array
    {
        return [
            'reserva.index',
            'reserva.show',
            'reserva.create',
            'reserva.store',
            'reserva.edit',
            'reserva.update',
            'reserva.filtro',
            'reserva.setPerPage',
            'reserva.removerFiltro',
            'reserva.horarios_diarios',
            'reserva.salvar_horarios_diarios',
            'reserva.cancelar',
            'reserva.historico',
            'reserva.obterOpcionais',
            'reserva.veiculosDisponiveis',
            'reserva.estacoesDisponiveisNoPeriodo',
            'reserva.estacoesDisponiveisNaDataHora',
            'reserva.datasRetiradaDisponiveisPorEstacao',
            'reserva.agendaDisponivelPorEstacao',
            'reserva.validarFuncionamentoEstacao',
            'reserva.dadosEdicaoNoShow',
            'reserva.atualizarNoShow',
            'reserva.gerenciar_opcionais',
            'reserva.editar_no_show',
            'reserva.checkin',
            'reserva.checkin.statusUpgrade',
            'reserva.checkin.veiculosDisponiveis',
            'reserva.checkin.veiculosModal',
            'reserva.checkin.excluirContrato',
            'reserva.criarContratoCheckin',
            'reserva.assinarContratoCheckin',
            'reserva.salvarAssinarContratoCheckIn',
            'reserva.salvar.assinatura',
            'reserva.verContratoCheckin',
            'reserva.adicionarDanoVeiculo',
            'reserva.checkout.vistoria',
            'reserva.checkout.finalizar',
            'reserva.efetuarPagamento',
            'reserva.processarPagamento',
            'reserva.confirmarPagamentoDinheiro',
            'reserva.emitirNfse',
            'reserva.calcao',
            'reserva.calcao.autorizar',
            'reserva.calcao.capturar',
            'reserva.calcao.liberar',
            'reserva.calcao.presencial',
            'reserva.calcao.presencial.devolver',
            'reserva.avarias.cobranca',
            'reserva.avarias.cobrar',
            'avaria.cobrar',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function reservationFinancialPermissions(): array
    {
        return [
            'reserva.index',
            'reserva.show',
            'reserva.filtro',
            'reserva.setPerPage',
            'reserva.removerFiltro',
            'reserva.historico',
            'reserva.efetuarPagamento',
            'reserva.processarPagamento',
            'reserva.confirmarPagamentoDinheiro',
            'reserva.emitirNfse',
            'reserva.avarias.cobranca',
            'reserva.avarias.cobrar',
            'avaria.cobrar',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function reservationClientPermissions(): array
    {
        return [
            'reserva.minhas',
            'reserva.show',
            'reserva.efetuarPagamento',
            'reserva.processarPagamento',
            'site.apresentacao',
            'site.apresentacao.estacoes',
            'site.reserva.index',
            'site.reserva.create',
            'site.reserva.store',
            'site.reserva.disponibilidade',
            'site.reserva.estacoesDisponiveis',
            'site.reserva.pagamento',
            'site.reserva.pagamento.metodo',
            'site.reserva.processarPagamento',
            'site.reserva.success',
            'site.reserva.login',
            'site.reserva.verificarCupom',
            'cancel',
            'success',
            'stripe.payment',
            'stripe.success',
            'stripe.failure',
            'stripe.processPayment',
            'cashier.payment',
            'cashier.webhook',
            'subscription-checkout',
        ];
    }
}
