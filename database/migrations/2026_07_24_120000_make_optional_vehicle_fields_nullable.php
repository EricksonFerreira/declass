<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('veiculos')) {
            return;
        }

        $columns = [
            'chassi' => 'VARCHAR(17)',
            'numero_motor' => 'VARCHAR(50)',
            'data_compra' => 'DATE',
            'data_proxima_manutencao' => 'DATE',
            'data_proxima_inspecao' => 'DATE',
            'kms' => 'INTEGER',
            'limite_kms' => 'INTEGER',
            'proxima_revisao_kms' => 'INTEGER',
            'proxima_manutencao_kms' => 'INTEGER',
        ];

        foreach ($columns as $column => $type) {
            if (!Schema::hasColumn('veiculos', $column)) {
                continue;
            }

            DB::statement(sprintf(
                'ALTER TABLE veiculos MODIFY COLUMN %s %s NULL',
                DB::getQueryGrammar()->wrap($column),
                $type
            ));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('veiculos')) {
            return;
        }

        $columns = [
            'chassi' => 'VARCHAR(17)',
            'numero_motor' => 'VARCHAR(50)',
            'data_compra' => 'DATE',
            'data_proxima_manutencao' => 'DATE',
            'data_proxima_inspecao' => 'DATE',
            'kms' => 'INTEGER',
            'limite_kms' => 'INTEGER',
            'proxima_revisao_kms' => 'INTEGER',
            'proxima_manutencao_kms' => 'INTEGER',
        ];

        foreach ($columns as $column => $type) {
            if (!Schema::hasColumn('veiculos', $column)) {
                continue;
            }

            DB::statement(sprintf(
                'ALTER TABLE veiculos MODIFY COLUMN %s %s NOT NULL',
                DB::getQueryGrammar()->wrap($column),
                $type
            ));
        }
    }
};
