<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Models\Permissao;
use App\Models\Regra;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class SincronizarPermissoesPorTelaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissoes:sincronizar-telas {--empresa_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria permissoes por tela/acao e sincroniza perfis por empresa';

    public function handle(): int
    {
        $permissionNames = $this->collectPermissionNamesFromRoutes();
        if (empty($permissionNames)) {
            $this->warn('Nenhuma rota nomeada encontrada para gerar permissoes.');
            return self::SUCCESS;
        }

        $empresaId = $this->option('empresa_id');
        $empresaIds = $empresaId
            ? Empresa::query()->whereKey($empresaId)->pluck('id')->all()
            : Empresa::query()->pluck('id')->all();
        if (empty($empresaIds)) {
            $this->warn('Nenhuma empresa encontrada. Crie ao menos uma empresa para sincronizar permissoes.');
            return self::SUCCESS;
        }

        $this->info('Sincronizando permissoes e regras por empresa...');

        foreach ($empresaIds as $empresaIdAtual) {
            $this->syncPermissionsForEmpresa((int) $empresaIdAtual, $permissionNames);
            $this->syncRolesForEmpresa((int) $empresaIdAtual, $permissionNames);
        }

        $this->info('Permissoes e regras sincronizadas com sucesso.');

        return self::SUCCESS;
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
            if (!$name) {
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
    private function syncPermissionsForEmpresa(int $empresaId, array $permissionNames): void
    {
        foreach ($permissionNames as $permissionName) {
            Permissao::query()->firstOrCreate(
                [
                    'empresa_id' => $empresaId,
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ],
                []
            );
        }
    }

    /**
     * @param array<int, string> $permissionNames
     */
    private function syncRolesForEmpresa(int $empresaId, array $permissionNames): void
    {
        $allPermissions = Permissao::query()
            ->where('empresa_id', $empresaId)
            ->whereIn('name', $permissionNames)
            ->get();

        $roles = $this->buildRolePermissionFilters();

        foreach ($roles as $roleName => $filter) {
            $role = Regra::query()->firstOrCreate(
                [
                    'empresa_id' => $empresaId,
                    'name' => $roleName,
                    'guard_name' => 'web',
                ],
                []
            );

            $permissionsForRole = $allPermissions
                ->filter(fn (Permissao $permission) => $filter($permission->name))
                ->pluck('id')
                ->all();

            $role->permissoes()->sync($permissionsForRole);
        }
    }

    /**
     * @return array<string, \Closure(string): bool>
     */
    private function buildRolePermissionFilters(): array
    {
        return [
            'administrador' => fn (string $permissionName) => true,

            // Gestor: opera praticamente todo o sistema, sem exclusoes e sem gestao de seguranca.
            'gestor' => fn (string $permissionName) =>
                !$this->isDangerousAction($permissionName)
                && !$this->isSecurityModule($permissionName),

            // Operador: foco operacional (reserva, movimentacao, veiculo/checklist) sem administracao.
            'operador' => fn (string $permissionName) =>
                $this->isOperationalAction($permissionName)
                && $this->isOperationalModule($permissionName),

            // Financeiro: pagamento/cupom/transacoes + leitura de contexto.
            'financeiro' => fn (string $permissionName) =>
                $this->isReadOnlyAction($permissionName)
                || $this->isFinancialModule($permissionName),

            // Visualizador: apenas leitura.
            'visualizador' => fn (string $permissionName) => $this->isReadOnlyAction($permissionName),
        ];
    }

    private function actionFromPermissionName(string $permissionName): string
    {
        if (!str_contains($permissionName, '.')) {
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

    private function moduleFromPermissionName(string $permissionName): string
    {
        if (!str_contains($permissionName, '.')) {
            return $permissionName;
        }

        return (string) str($permissionName)->before('.');
    }

    private function isSecurityModule(string $permissionName): bool
    {
        $module = $this->moduleFromPermissionName($permissionName);

        return in_array($module, ['regra', 'permissao', 'tipo_usuario', 'empresa', 'usuario'], true);
    }

    private function isOperationalModule(string $permissionName): bool
    {
        $module = $this->moduleFromPermissionName($permissionName);

        return in_array($module, [
            'home',
            'dashboard',
            'reserva',
            'movimentacao',
            'status_movimentacao',
            'status_reserva',
            'veiculo',
            'frota',
            'grupo_frota',
            'checklist',
            'item_checklist',
            'status_item_checklist',
            'tipo_checklist',
            'configuracao_checklist',
            'termo_consentimento',
            'estacao',
            'dia_util',
            'horario_diario',
        ], true);
    }

    private function isFinancialModule(string $permissionName): bool
    {
        $module = $this->moduleFromPermissionName($permissionName);

        return in_array($module, [
            'pagamento',
            'stripe',
            'cupom_desconto',
            'tipotransacao',
            'tipo_transacao',
            'transacao',
            'reserva',
        ], true) && !$this->isDangerousAction($permissionName);
    }
}
