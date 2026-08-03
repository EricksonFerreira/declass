<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'reservas' => 'data_criacao',
            'veiculos' => 'data_compra',
            'movimentacoes' => 'data_registro',
            'categorias_financeiras' => 'origem',
            'transacoes' => 'info_pagamento',
        ];

        foreach ($tables as $tableName => $afterColumn) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName, $afterColumn) {
                if (!Schema::hasColumn($tableName, 'datainc')) {
                    $table->dateTime('datainc')->nullable()->after($afterColumn);
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
            'reservas',
            'veiculos',
            'movimentacoes',
            'categorias_financeiras',
            'transacoes',
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
};
