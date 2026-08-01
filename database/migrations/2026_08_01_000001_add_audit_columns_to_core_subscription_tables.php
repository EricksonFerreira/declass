<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['empresas', 'planos', 'empresa_planos', 'empresa_plano_pagamentos'];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                if (! Schema::hasColumn($table, 'usuinc')) {
                    $blueprint->foreignId('usuinc')->nullable()->after('id')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn($table, 'datainc')) {
                    $blueprint->timestamp('datainc')->nullable()->after('usuinc');
                }
                if (! Schema::hasColumn($table, 'usuatu')) {
                    $blueprint->foreignId('usuatu')->nullable()->after('datainc')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn($table, 'datatu')) {
                    $blueprint->timestamp('datatu')->nullable()->after('usuatu');
                }
            });
        }

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $columns = Schema::getColumnListing($table);
            $hasCreatedAt = in_array('created_at', $columns, true);
            $hasUpdatedAt = in_array('updated_at', $columns, true);

            $payload = [];
            if (in_array('datainc', $columns, true) && $hasCreatedAt) {
                $payload['datainc'] = DB::raw('COALESCE(created_at, NOW())');
            }
            if (in_array('datatu', $columns, true)) {
                if ($hasUpdatedAt) {
                    $payload['datatu'] = DB::raw('COALESCE(updated_at, created_at, NOW())');
                } elseif ($hasCreatedAt) {
                    $payload['datatu'] = DB::raw('COALESCE(created_at, NOW())');
                }
            }

            if ($payload) {
                DB::table($table)->update($payload);
            }
        }
    }

    public function down(): void
    {
        $tables = ['empresas', 'planos', 'empresa_planos', 'empresa_plano_pagamentos'];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table): void {
                if (Schema::hasColumn($table, 'usuatu')) {
                    $blueprint->dropConstrainedForeignId('usuatu');
                }
                if (Schema::hasColumn($table, 'datatu')) {
                    $blueprint->dropColumn('datatu');
                }
                if (Schema::hasColumn($table, 'usuinc')) {
                    $blueprint->dropConstrainedForeignId('usuinc');
                }
                if (Schema::hasColumn($table, 'datainc')) {
                    $blueprint->dropColumn('datainc');
                }
            });
        }
    }
};
