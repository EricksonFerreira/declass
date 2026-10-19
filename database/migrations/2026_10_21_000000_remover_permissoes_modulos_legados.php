<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Modulos legados do sistema anterior (XpandCars/locacao) que nao fazem
     * mais parte do produto: avarias, caucao/calcao, multas e reservas.
     * As rotas ja foram removidas; restam permissões orfas no banco porque
     * o sync so insere, nunca remove.
     *
     * @var array<int, string>
     */
    private array $prefixosNome = [
        'avaria',
        'avarias',
        'avaria_cobrancas',
        'caucao',
        'calcao',
        'multa',
        'multas',
        'multas_analise',
        'reserva',
        'reservas',
        'site.reserva',
        'site.multa',
    ];

    /**
     * @var array<int, string>
     */
    private array $grupos = [
        'avaria',
        'avarias',
        'avaria_cobrancas',
        'caucao',
        'calcao',
        'multa',
        'multas',
        'reserva',
        'reservas',
    ];

    public function up(): void
    {
        $ids = DB::table('permissions')
            ->where('guard_name', 'web')
            ->where(function ($q): void {
                foreach ($this->prefixosNome as $prefixo) {
                    $q->orWhere('name', $prefixo)
                        ->orWhere('name', 'like', $prefixo.'.%')
                        ->orWhere('name', 'like', $prefixo.'_%');
                }
                foreach ($this->grupos as $grupo) {
                    $q->orWhere('grupo', $grupo);
                }
            })
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('role_has_permissions')->whereIn('permission_id', $ids)->delete();

        if (DB::getSchemaBuilder()->hasTable('plano_permissao')) {
            DB::table('plano_permissao')->whereIn('permission_id', $ids)->delete();
        }

        DB::table('permissions')->whereIn('id', $ids)->delete();
    }

    public function down(): void
    {
        // Permissoes removidas sao orfas de rotas inexistentes — sem rollback.
    }
};
