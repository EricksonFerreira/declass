<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Novas permissoes:
     *  - operacao.timesheet.copiar: habilita o botao "Copiar semana anterior"
     *  - escopo.proprio / escopo.equipe / escopo.todos: informam ATE ONDE o
     *    usuario enxerga dados de colaboradores (dele, da equipe que lidera
     *    ou de todos).
     */
    public function up(): void
    {
        $defs = [
            ['name' => 'operacao.timesheet.copiar', 'grupo' => 'operacao',
             'descricao' => 'Copiar horas da semana anterior no Timesheet'],
            ['name' => 'escopo.proprio', 'grupo' => 'escopo',
             'descricao' => 'Ve apenas os proprios dados'],
            ['name' => 'escopo.equipe', 'grupo' => 'escopo',
             'descricao' => 'Ve os proprios dados e os das equipes que lidera'],
            ['name' => 'escopo.todos', 'grupo' => 'escopo',
             'descricao' => 'Ve os dados de todos os colaboradores da empresa'],
        ];

        $ids = [];
        foreach ($defs as $def) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $def['name'], 'guard_name' => 'web'],
                ['grupo' => $def['grupo'], 'descricao' => $def['descricao'],
                 'created_at' => now(), 'updated_at' => now()]
            );
            $ids[$def['name']] = (int) DB::table('permissions')
                ->where('name', $def['name'])->where('guard_name', 'web')
                ->value('id');
        }

        // administrador -> tudo; usuario -> proprio+equipe (preserva o
        // comportamento atual: lider ve a equipe, membro comum ve so a si);
        // visualizador -> todos.
        $matriz = [
            'administrador' => ['escopo.proprio', 'escopo.equipe', 'escopo.todos', 'operacao.timesheet.copiar'],
            'usuario'       => ['escopo.proprio', 'escopo.equipe', 'operacao.timesheet.copiar'],
            'visualizador'  => ['escopo.todos'],
        ];

        $roles = DB::table('roles')
            ->where('guard_name', 'web')
            ->get(['id', 'name']);

        $pivots = [];
        foreach ($roles as $role) {
            $nomes = $matriz[mb_strtolower($role->name)] ?? null;
            if (! $nomes) {
                continue;
            }
            foreach ($nomes as $nome) {
                $pid = $ids[$nome] ?? null;
                if ($pid) {
                    $pivots[] = ['role_id' => $role->id, 'permission_id' => $pid];
                }
            }
        }

        foreach (array_chunk($pivots, 500) as $chunk) {
            DB::table('role_has_permissions')->insertOrIgnore($chunk);
        }
    }

    public function down(): void
    {
        $ids = DB::table('permissions')
            ->whereIn('name', ['operacao.timesheet.copiar', 'escopo.proprio', 'escopo.equipe', 'escopo.todos'])
            ->pluck('id');

        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();
        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
};
