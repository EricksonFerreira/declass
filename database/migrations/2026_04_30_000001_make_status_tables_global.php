<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * status_reserva e status_movimentacao são catálogos globais do sistema —
 * não pertencem a nenhuma empresa específica.
 * Esta migration remove empresa_id dessas tabelas para evitar que o
 * filtro de tenant esconda os registros para novas empresas.
 */
return new class extends Migration
{
    private array $globalTables = [
        'status_reserva',
        'status_movimentacao',
        'tipos_movimentacao',
        'tipo_transacoes',
        'status_veiculos',
        'status_item_checklist',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ($this->globalTables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            // Garante que registros existentes com empresa_id != null
            // fiquem visíveis (zera o empresa_id deles)
            if (Schema::hasColumn($table, 'empresa_id')) {
                DB::table($table)->whereNotNull('empresa_id')->update(['empresa_id' => null]);

                Schema::table($table, function ($blueprint) {
                    $blueprint->dropColumn('empresa_id');
                });
            }
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Não restaura — empresa_id nessas tabelas foi design incorreto
    }
};
