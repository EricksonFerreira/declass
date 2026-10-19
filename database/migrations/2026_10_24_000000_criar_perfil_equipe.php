<?php

use Database\Seeders\PapeisPermissoesSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Cria o perfil 'equipe' em cada empresa: mesma matriz do 'usuario' +
     * escopo.equipe (lideres veem os dados das equipes que lideram).
     * O perfil 'usuario' passa a ser "Apenas dele" (sem escopo.equipe).
     */
    public function up(): void
    {
        $permissaoIds = DB::table('permissions')
            ->where('guard_name', 'web')
            ->pluck('id', 'name');

        $idsEquipe = collect(PapeisPermissoesSeeder::permissoesEquipe())
            ->map(fn ($nome) => $permissaoIds[$nome] ?? null)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $now = now();

        // Cria o perfil 'equipe' nas empresas que ainda nao o tem e vincula
        // a matriz padrao apenas na criacao (nao sobrescreve edicoes).
        foreach (DB::table('empresas')->pluck('id') as $empresaId) {
            $existe = DB::table('roles')
                ->where('name', 'equipe')
                ->where('guard_name', 'web')
                ->where('empresa_id', $empresaId)
                ->exists();

            if ($existe) {
                continue;
            }

            $roleId = DB::table('roles')->insertGetId([
                'name' => 'equipe',
                'guard_name' => 'web',
                'empresa_id' => $empresaId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $pivots = $idsEquipe
                ->map(fn ($pid) => ['permission_id' => $pid, 'role_id' => $roleId])
                ->all();

            if ($pivots !== []) {
                DB::table('role_has_permissions')->insert($pivots);
            }
        }

        // usuario volta a ser "Apenas dele": escopo de equipe e exclusivo
        // do novo perfil.
        $equipePermIds = collect(['escopo.equipe'])
            ->map(fn ($nome) => $permissaoIds[$nome] ?? null)
            ->filter();

        if ($equipePermIds->isNotEmpty()) {
            $usuarioRoleIds = DB::table('roles')->where('name', 'usuario')->pluck('id');

            DB::table('role_has_permissions')
                ->whereIn('permission_id', $equipePermIds)
                ->whereIn('role_id', $usuarioRoleIds)
                ->delete();
        }
    }

    public function down(): void
    {
        DB::table('roles')->where('name', 'equipe')->delete();
    }
};
