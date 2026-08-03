<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'empresas',
            'empresa_planos',
            'empresa_plano_pagamentos',
            'planos',
            'documentacoes_veiculo',
            'documentacao_veiculo_parcelas',
            'lancamentos_financeiros',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'datainc')) {
                    $table->dateTime('datainc')->nullable()->after($this->afterColumn($tableName));
                }

                if (!Schema::hasColumn($tableName, 'usuinc')) {
                    $table->unsignedBigInteger('usuinc')->nullable()->after('datainc');
                }

                if (!Schema::hasColumn($tableName, 'datatu')) {
                    $table->dateTime('datatu')->nullable()->after('usuinc');
                }

                if (!Schema::hasColumn($tableName, 'usuatu')) {
                    $table->unsignedBigInteger('usuatu')->nullable()->after('datatu');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'empresas',
            'empresa_planos',
            'empresa_plano_pagamentos',
            'planos',
            'documentacoes_veiculo',
            'documentacao_veiculo_parcelas',
            'lancamentos_financeiros',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['usuatu', 'datatu', 'usuinc', 'datainc'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function afterColumn(string $tableName): string
    {
        $candidates = [
            'categoria_financeira_id',
            'grupo_recorrencia_id',
            'status',
            'descricao',
            'ativo',
        ];

        foreach ($candidates as $column) {
            if (Schema::hasColumn($tableName, $column)) {
                return $column;
            }
        }

        return 'id';
    }
};
