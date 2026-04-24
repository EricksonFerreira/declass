<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Garante que as novas permissões das 4 funcionalidades existam e sejam
 * atribuídas aos perfis de funcionários de cada empresa.
 *
 * Perfis considerados "funcionário": qualquer regra cujo nome contenha
 * "funcionario", "operador", "atendente" ou "admin" (case-insensitive).
 * Ajuste o array $perfilFuncionario conforme sua nomenclatura real.
 */
class PermissoesNovasFuncionalidadesSeeder extends Seeder
{
    private array $permissoes = [
        // Feature 2: Editar reserva no show
        'reserva.editar_no_show',
        'reserva.gerenciar_opcionais',

        // Feature 3: Multas
        'multa.index',
        'multa.create',
        'multa.edit',
        'multa.delete',
        'multa.export',

        // Feature 4: Calção
        'calcao.autorizar',
        'calcao.capturar',
        'calcao.cancelar',
        'calcao.visualizar',

        // Feature 5: CobranÃ§a por avarias (pÃ³s-checkout)
        'avaria.cobrar',
    ];

    /** Fragmentos de nome (lower) que identificam perfis de funcionário. */
    private array $perfilFuncionario = [
        'funcionario',
        'operador',
        'atendente',
        'gerente',
        'admin',
    ];

    public function run(): void
    {
        $tableNames        = config('permission.table_names');
        $permissionsTable  = $tableNames['permissions']        ?? 'permissions';
        $rolesTable        = $tableNames['roles']              ?? 'roles';
        $roleHasPermTable  = $tableNames['role_has_permissions'] ?? 'role_has_permissions';

        if (!Schema::hasTable($permissionsTable) || !Schema::hasTable('empresas')) {
            $this->command->warn('Tabelas de permissoes nao encontradas.');
            return;
        }

        $permissionHasEmpresa = Schema::hasColumn($permissionsTable, 'empresa_id');
        $roleHasEmpresa       = Schema::hasColumn($rolesTable, 'empresa_id');
        $empresaIds           = DB::table('empresas')->pluck('id');

        foreach ($empresaIds as $empresaId) {
            // 1. Garante que as permissões existam
            $permissionIds = [];
            foreach ($this->permissoes as $name) {
                $query = DB::table($permissionsTable)
                    ->where('name', $name)
                    ->where('guard_name', 'web');
                if ($permissionHasEmpresa) {
                    $query->where('empresa_id', $empresaId);
                }
                $perm = $query->first();

                if (!$perm) {
                    $data = ['name' => $name, 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()];
                    if ($permissionHasEmpresa) $data['empresa_id'] = $empresaId;
                    try {
                        $permId = DB::table($permissionsTable)->insertGetId($data);
                    } catch (\Illuminate\Database\QueryException $e) {
                        // Registro já existe (corrida ou índice único sem empresa_id) — busca novamente
                        $fallback = DB::table($permissionsTable)
                            ->where('name', $name)
                            ->where('guard_name', 'web')
                            ->first();
                        if (!$fallback) throw $e;
                        $permId = $fallback->id;
                    }
                } else {
                    $permId = $perm->id;
                }

                $permissionIds[$name] = $permId;
            }

            // 2. Atribui ao(s) perfil(is) de funcionário
            $rolesQuery = DB::table($rolesTable)->where('guard_name', 'web');
            if ($roleHasEmpresa) {
                $rolesQuery->where('empresa_id', $empresaId);
            }
            $roles = $rolesQuery->get();

            foreach ($roles as $role) {
                $nomeRole = strtolower((string) $role->name);
                $ehFuncionario = collect($this->perfilFuncionario)
                    ->contains(fn ($frag) => str_contains($nomeRole, $frag));

                if (!$ehFuncionario) {
                    continue;
                }

                foreach ($permissionIds as $name => $permId) {
                    // Não duplicar
                    $existe = DB::table($roleHasPermTable)
                        ->where('permission_id', $permId)
                        ->where('role_id', $role->id)
                        ->exists();

                    if (!$existe) {
                        DB::table($roleHasPermTable)->insert([
                            'permission_id' => $permId,
                            'role_id'       => $role->id,
                        ]);
                        $this->command->info("Permissao '{$name}' atribuida ao perfil '{$role->name}' (empresa #{$empresaId})");
                    }
                }
            }
        }

        // Limpa cache de permissões
        app('cache')
            ->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));

        $this->command->info('PermissoesNovasFuncionalidadesSeeder concluido.');
    }
}
